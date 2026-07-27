# Switcher Architecture

**Switcher** is a Laravel-based transaction switching platform for digital-value products. It gives API clients one request model for airtime, data, TV, electricity, and betting transactions, while routing each request to an appropriate upstream vendor. The platform owns the transaction record, product catalogue, routing policy, and operational history; vendor-specific protocols stay behind driver adapters.

This document describes the architecture implemented in this repository as of July 2026. For the rationale behind the canonical catalogue, identifiers, and vendor abstraction, see [docs/Architectural_Decisions.md](docs/Architectural_Decisions.md).

## System context

```text
                         ┌─────────────────────────────┐
                         │ Operations users             │
                         │ Laravel auth + Blade UI       │
                         └──────────────┬──────────────┘
                                        │
API clients ── X-API-KEY ──> ┌──────────▼────────────────────────────────┐
                             │                 Switcher                   │
                             │  HTTP API · routing · transaction service   │
                             │  product catalogue · operations console     │
                             └───────┬───────────────────┬─────────────────┘
                                     │                   │
                              MySQL-compatible DB   Scheduler / Artisan
                                     │                   │
                                     │            pending requery, product sync
                                     │
                      ┌──────────────▼───────────────────┐
                      │ Vendor driver boundary            │
                      │ Oatek · Vendify · Mock            │
                      └──────────────┬───────────────────┘
                                     │ HTTPS
                       ┌─────────────▼──────────────┐
                       │ External vendor APIs        │
                       └────────────────────────────┘
```

The application is a Laravel 12 monolith running on PHP 8.2+. Server-rendered operational pages use Blade, Tailwind CSS, Alpine.js, and Vite. Vendor calls are synchronous HTTP calls made with Laravel's HTTP client. Laravel provides optional database-backed queue, cache, sessions, and Telescope support; the current transaction path does not dispatch jobs.

## Runtime entry points

| Surface | Purpose | Authentication |
| --- | --- | --- |
| `POST /api/vend` | Canonical vending request | Required `X-API-KEY` for an active client |
| `POST /api/requery` | Requery a transaction by `tracking_id` | None currently applied |
| `GET /api/bundles` | Active canonical data bundles by network | None currently applied |
| TV, electricity, and betting validation endpoints | Pre-vend validation and catalogue lookup | None currently applied |
| `POST /api/b2b` | Compatibility adapter for an Oatek-shaped request | None currently applied; client is hard-coded to ID 1 |
| `/operations/*` | Transactions, vendors, routing, and client administration | Laravel web login (`auth`) |
| `/dashboard`, `/profile`, `/auth/*` | Standard Breeze user workflows | Laravel session auth; dashboard also requires verified email |
| `GET /up` | Laravel health endpoint | None |

API routes are defined in [routes/api.php](routes/api.php); web and operations routes are in [routes/web.php](routes/web.php). Route middleware alias `client.auth` is registered in [bootstrap/app.php](bootstrap/app.php).

## Application layers

| Layer | Primary locations | Responsibility |
| --- | --- | --- |
| Delivery | `routes/`, `app/Http/Controllers/`, `app/Http/Middleware/` | HTTP routing, request validation, response formatting, and client-key lookup |
| Application services | `app/Services/`, `app/Actions/` | Transaction orchestration, routing, validation, catalogue access, retry, synchronization, and operations queries |
| Integration adapters | `app/Services/Vendors/` | Vendor-specific authentication, payload construction, HTTP calls, and response normalization |
| Domain/persistence | `app/Models/`, `app/Data*/`, `database/migrations/` | Eloquent records, request/response DTOs, state transitions, and relational persistence |
| Presentation | `resources/views/`, `resources/js/`, `resources/css/` | Operations console and supporting client-side behavior |
| Background/maintenance | `app/Console/Commands/`, `routes/console.php` | Pending transaction requery and product synchronization |

Controllers remain deliberately thin. `VendController` validates the canonical request, derives `client_id` from the authenticated client, and calls `VendService`. `VendService` is the orchestration boundary that persists the transaction and applies routing, retries, failover, and state transitions.

## Core transaction flow

```text
Client
  │ POST /api/vend (tracking_id, product_type, network, beneficiary, …)
  ▼
ClientApiKeyMiddleware ──> active Client attached to request
  ▼
VendController validates and adds client_id
  ▼
VendService
  ├─ normalizes product_type and network
  ├─ enriches data and TV requests from the canonical catalogue/validation result
  ├─ returns existing transaction for (client_id, tracking_id) [idempotency]
  ├─ resolves client-specific route, else global route
  ├─ creates a pending Transaction and timeline event
  ├─ resolves Vendor → driver_key → VendorInterface implementation
  ├─ builds TransactionRequestData and vendor-specific request identifier
  ├─ calls vendor (with retry in automatic mode)
  ├─ optionally calls the fallback vendor after a failed primary response
  └─ persists normalized outcome, raw payloads, latency, references, and events
  ▼
{ status, reference: ringo_reference, tracking_id, message }
```

### Routing and failover

`RoutingResolver` first looks for an active `ClientRoutingConfig` matching `(client, product_type, network)`. If none exists, it uses the active global `RoutingConfig` for `(product_type, network)`. Each route selects a primary vendor and may name a fallback vendor.

- **Manual mode:** one call to the primary vendor; no retry or failover.
- **Auto mode:** the primary call is retried for retryable normalized error codes (`TIMEOUT`, `NETWORK_ERROR`, or `UNKNOWN`). `maxRetries: 2` means at most three attempts in total. If the resulting status is `failed` and failover is enabled with a fallback vendor, Switcher calls the fallback and records that vendor as the one used.
- The route schema includes threshold, window, and minimum-sample fields for health-based failover policy. The current implementation uses only explicit `auto_failover_enabled` plus a fallback vendor; it does not calculate vendor health from those thresholds.

### Transaction state and audit trail

Transactions have three canonical states: `pending`, `success`, and `failed`. The `Transaction` model protects terminal transitions: a successful transaction cannot become failed, and a failed transaction cannot become successful or pending. Every attempt is recorded in `transaction_events`, including creation, vendor calls/responses/exceptions, failover, and requery events.

Switcher maintains three identifiers:

| Identifier | Created by | Role |
| --- | --- | --- |
| `tracking_id` | Client | Client reconciliation and idempotency key; unique per client |
| `ringo_reference` | Switcher | Immutable UUID transaction identity |
| `vendor_reference` | Vendor | Upstream reconciliation identifier, when returned |

Vendor request IDs are separately encoded by `VendorRequestEncoder`: Vendify receives a zero-padded numeric internal transaction ID; Oatek receives the `ringo_reference`. This keeps vendor constraints out of public identifiers.

### Pending resolution

`transactions:resolve-pending` runs every five minutes through Laravel Scheduler. It processes pending records with a vendor in chunks of 100 and re-queries each vendor through the same driver interface. Operations users can also trigger requery from the transaction console, and `POST /api/requery` exposes it to API callers.

## Vendor integration boundary

All vendors implement `VendorInterface`. It defines vending, requery, data-bundle retrieval, and TV/electricity/betting validation capabilities. Drivers return `NormalizedVendorResponse`, which translates heterogeneous upstream responses into the platform's `success`/`pending`/`failed` state, code, message, optional vendor reference, and raw response.

```text
Vendor database record (driver_key)
       │
VendorDriverResolver / VendorDriverRegistry
       │
VendorInterface
       ├── OatekDriver
       ├── VendifyDriver
       └── MockVendorDriver
```

`VendorDriverResolver` obtains the vendor record by ID, reads `driver_key`, and resolves the driver through Laravel's container. Vendor credentials and endpoint URLs are environment-backed configuration in [config/services.php](config/services.php), rather than persisted in the `vendors` table.

To add a vendor:

1. Implement every `VendorInterface` method, preferably extending `BaseVendorDriver` to use canonical response helpers.
2. Add the class and label to `VendorDriverRegistry`.
3. Add its credential configuration and environment variables.
4. Create an active `vendors` record with the matching `driver_key`.
5. Add product normalization/synchronization support and mappings for products it sells.
6. Configure global or client-specific routes, then add integration and failure-mode tests.

## Product catalogue

Switcher owns a canonical catalogue rather than exposing vendor product codes.

```text
Client requests product_code
        │
products (canonical product, price, allowance, validity)
        │ 1:N
vendor_product_mappings (vendor-specific code and metadata)
        │
Vendor driver resolves the upstream product code
```

`ProductCatalogService` returns active products by type and network. `BundleService` exposes active canonical data products from this catalogue. During a data vend, `VendService` confirms the submitted `product_code` belongs to the requested network and overwrites client-provided price with the catalogue amount.

`ProductSynchronizationService` imports a vendor's data bundles, upserts canonical `products`, and creates/updates `vendor_product_mappings` in one database transaction. Run it manually with:

```bash
php artisan switcher:sync-products oatek MTN
```

The command accepts a driver key and network and currently synchronizes `data` products only. Product-code generation is derived from product type, network, allowance, and validity, so upstream normalizers must provide stable values.

## Data model

| Entity | Key fields and relationships | Purpose |
| --- | --- | --- |
| `users` | Laravel users | Operations-console accounts |
| `clients` | `api_key`, `is_active`; 1:N transactions and client routes | API consumers and their authentication identity |
| `vendors` | `driver_key`, active flag; 1:N transactions and mappings | Configured upstream integration instances |
| `routing_config` | `(product_type, network)`, primary/fallback vendor, mode | Default routing policy |
| `client_routing_configs` | unique `(client_id, product_type, network)` | Per-client override of the default policy |
| `transactions` | three references, client/vendor FKs, status, raw request/response, latency | System of record for a vending request |
| `transaction_events` | transaction FK, event type, message, JSON metadata | Append-only-ish execution timeline |
| `products` | canonical unique `product_code`, type, network, price, validity, metadata | Public product catalogue |
| `vendor_product_mappings` | unique `(vendor_id, product_id)`, vendor product code | Translation from canonical product to vendor product |
| Laravel support tables | cache, jobs, failed jobs, sessions, personal tokens, Telescope tables | Framework services and diagnostics |

The transaction schema enforces a unique `(client_id, tracking_id)` pair and a globally unique `ringo_reference`. Relationships provide the operational console with the vendor, client, and chronological event history.

## Validation endpoints

TV, electricity, and betting validation services resolve an active **global** routing configuration for their product type and provider/disco/biller, then invoke the primary vendor driver. TV package selection calls validation first and finds the requested package in the returned vendor data before vending. These paths do not use client-specific routing at present.

## Security and observability

### Security boundaries

- `/api/vend` authenticates through `X-API-KEY`, looks up an active `Client`, and never accepts `client_id` directly from the public request.
- Operations routes require Laravel's `auth` middleware. The dashboard additionally requires email verification.
- Vendor credentials are read from environment variables via `config/services.php`; do not commit them.
- Database records include raw vendor request and response payloads. Treat database access, exports, backups, and Telescope data as potentially sensitive.

### Observability

- `transactions` records final status, vendor selection, latency, resolved time, and raw normalized vendor exchanges.
- `transaction_events` gives a transaction-level timeline for retry, failover, exception, and requery diagnosis.
- The operations UI supports transaction listing/detail/requery/export, vendor visibility/toggling, client-key lifecycle, and routing changes.
- Laravel Telescope is installed and enabled by default through environment configuration. It stores diagnostics in the configured database connection; production exposure should be explicitly controlled.
- Logging defaults to Laravel's `stack` channel, typically `storage/logs/laravel.log`.

## Deployment and operations

The application needs PHP 8.2+, Composer, Node/Vite for asset builds, and a configured database (MySQL/MariaDB defaults are available; Laravel also includes SQLite, PostgreSQL, and SQL Server connections). Database queues, cache, and sessions can be selected through the normal Laravel environment configuration.

Typical process responsibilities are:

| Process | Responsibility |
| --- | --- |
| Web/PHP runtime | Serves web and API requests |
| Scheduler (`php artisan schedule:work` or cron `schedule:run`) | Executes the five-minute pending-transaction requery |
| Queue worker, if queues are enabled | Processes framework/Telescope or future application jobs |
| Vite build | Produces versioned UI assets for production |

The provided Composer `dev` script starts Laravel's development server, queue listener, Pail logs, and Vite development server concurrently. Run migrations before serving the app and ensure vendor service credentials are supplied through the environment.

## Repository map

```text
app/
  Actions/                 Operations read/update use cases
  Console/Commands/        Product synchronization and pending resolution
  Data*/                   Transaction and normalized-response DTOs
  Http/                    Controllers, requests, API-key middleware
  Models/                  Eloquent domain records
  Services/                Orchestration, routing, catalogue, validation
    Vendors/               Vendor adapter implementations and registry
database/
  migrations/              Persistent schema
  seeders/                 Initial routing/data support
resources/
  views/operations/        Authenticated operations UI
  js/, css/                Vite-managed frontend assets
routes/                    API, web, auth, and scheduler definitions
config/                    Laravel and upstream-service configuration
docs/Architectural_Decisions.md  Design rationale / ADRs
```

## Current implementation constraints

These are observed implementation facts, not aspirational design:

- Only `POST /api/vend` is protected by `client.auth`. Catalogue, validation, requery, and `/api/b2b` endpoints currently have no client API-key middleware.
- `/api/b2b` is a compatibility adapter that sets `client_id` to `1`; it is not equivalent to the authenticated canonical API.
- Product mapping resolution uses `driver_key`, not the exact vendor ID selected by a route. Multiple configured vendor records with the same driver key therefore require careful mapping/selection management.
- Retry calls happen inline with no backoff or delay, and vendor HTTP timeout/retry configuration is not set in the drivers.
- The automatic failover decision is response-status based; route health threshold fields are not yet consumed.
- The scheduler requery route and API requery lookup use `tracking_id` without a client scope. Combined with public access, this should be reviewed before production exposure.
- In the manual transaction-creation path, an insert exception invokes Laravel's `dd()`, which is unsuitable for a production request path.
- `resolved_at` is written after both pending and terminal executions. Consumers should rely on `status` for finality.
- The included test suite is predominantly Laravel/Breeze scaffold coverage; transaction routing, vendor adapters, failover, and reconciliation need focused automated coverage.

## Change guidance

Keep the public API canonical. New client-facing product fields should be added to `TransactionRequestData` and the orchestration layer, while vendor-specific transformations belong only in drivers. Route and client changes should be made through the operations workflow or migrations/seeders, never hard-coded in a driver. Any change that affects status transitions, idempotency, or vendor request encoding should include tests for duplicate requests, upstream timeout/pending outcomes, requery, and fallback behavior.
