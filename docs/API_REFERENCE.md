# Switcher API Reference

**Version:** Test / beta  
**Audience:** Switcher integration partners  
**Last updated:** 12 August 2026

Switcher provides one canonical API for airtime, data, TV, electricity, and betting transactions. This document describes the API currently available in the **test environment**. Do not use it for production transactions until Switcher has confirmed that your account has been enabled for live use.

## Environments

| Environment | Base URL | Use |
| --- | --- | --- |
| Test | `{{TEST_BASE_URL}}/api` | Integration, UAT, and non-production testing |
| Live | `{{LIVE_BASE_URL}}/api` | Real customer transactions; available after onboarding |

Replace the placeholders above with the URLs supplied by Switcher. Paths in this reference are relative to the environment base URL.

## Authentication

Every endpoint in this document requires your API key in the `X-API-KEY` request header.

```http
X-API-KEY: YOUR_API_KEY
Accept: application/json
Content-Type: application/json
```

Never send a `client_id`; Switcher derives the client identity from the API key. Keep keys server-side and never expose them in a mobile app, browser, repository, or support ticket.

An absent or invalid key returns `401`:

```json
{ "message": "API key missing." }
```

or

```json
{ "message": "Invalid API key." }
```

## Transaction lifecycle

1. Generate a unique `tracking_id` in your system for each intended purchase.
2. Call `POST /vend` once for that ID.
3. Treat `success` and `failed` as final. For `pending`, call `POST /requery` later using the same `tracking_id`.

`tracking_id` is the idempotency key and is unique per client. Re-sending the same ID returns the existing transaction rather than creating another purchase. Do not reuse it for a different purchase.

Every vend and requery response has this canonical shape:

```json
{
  "status": "success",
  "reference": "a0f5c6bf-b314-4d19-8569-0e43ec4e831f",
  "tracking_id": "merchant-order-100027",
  "message": "Transaction processed"
}
```

| Field | Meaning |
| --- | --- |
| `status` | `success`, `pending`, or `failed` |
| `reference` | Switcher's immutable transaction reference; retain it for support |
| `tracking_id` | Your original transaction ID |
| `message` | Human-readable result message |

## Vending

### `POST /vend`

Creates or returns a transaction. `product_type` determines which additional fields are required.

Common fields:

| Field | Type | Required | Description |
| --- | --- | --- |
| `tracking_id` | string | Yes | Your unique idempotency key |
| `product_type` | string | Yes | One of `airtime`, `data`, `tv`, `electricity`, `betting` |
| `network` | string | Yes | Network, TV provider, disco, or betting biller, as applicable |
| `beneficiary` | string | Yes | Phone number, smart-card number, meter number, or betting customer ID |
| `amount` | number | Airtime, electricity, betting | Transaction amount. For data and TV it is determined by Switcher from the selected product/package. |

#### Airtime

```json
{
  "tracking_id": "airtime-100001",
  "product_type": "airtime",
  "network": "MTN",
  "beneficiary": "08030000000",
  "amount": 100
}
```

#### Data

Get the catalogue first with `GET /bundles?network=MTN`, then submit its `product_code`. Do not submit an amount for a data vend; Switcher uses the catalogue amount.

```json
{
  "tracking_id": "data-100001",
  "product_type": "data",
  "network": "MTN",
  "beneficiary": "08030000000",
  "product_code": "DATA_MTN_1GB_30D"
}
```

#### TV

Validate the smart card with `POST /tv/validate`, select a returned package code, then vend. The package amount is determined from the validation result.

```json
{
  "tracking_id": "tv-100001",
  "product_type": "tv",
  "network": "DSTV",
  "beneficiary": "1234567890",
  "package_code": "COMPE36",
  "period": 1,
  "has_addon": false
}
```

For an add-on, set `has_addon` to `true` and include both `addon_code` and `addon_name`.

#### Electricity

```json
{
  "tracking_id": "electricity-100001",
  "product_type": "electricity",
  "network": "IKEDC",
  "beneficiary": "45001234567",
  "amount": 1000,
  "meter_type": "PREPAID",
  "phone_number": "08030000000"
}
```

#### Betting

Validate the customer first with `POST /betting/validate`, then submit the returned/confirmed customer name.

```json
{
  "tracking_id": "betting-100001",
  "product_type": "betting",
  "network": "BET9JA",
  "beneficiary": "customer-account-id",
  "customer_name": "Customer Name",
  "amount": 500
}
```

### `POST /requery`

Checks the latest result for one of your **pending** transactions. Do not requery a successful or failed transaction.

```json
{ "tracking_id": "data-100001" }
```

The response uses the canonical transaction response shape above. If the transaction is unknown, Laravel returns `404`; if it is no longer pending, Switcher returns `422`.

## Product and customer validation

Validation endpoints are useful before vending. Their current response bodies are passed through from the active upstream provider and may contain provider-specific fields. During beta, integrate against the documented request fields and use the returned product/customer data only after confirming it in your test account.

### `GET /bundles?network={network}`

Returns Switcher's active canonical data catalogue for a network.

```json
[
  {
    "product_code": "DATA_MTN_1GB_30D",
    "display_name": "1GB Data",
    "amount": 500,
    "validity": 30,
    "category": "daily"
  }
]
```

### `POST /tv/validate`

```json
{ "smart_card_no": "1234567890", "provider": "DSTV" }
```

### `POST /tv/subscription-status`

```json
{ "smart_card_no": "1234567890", "provider": "DSTV" }
```

### `POST /tv/addons`

```json
{ "package_code": "COMPE36" }
```

### `POST /electricity/validate`

```json
{ "disco": "IKEDC", "meter_no": "45001234567", "type": "PREPAID" }
```

### `POST /betting/validate`

```json
{ "biller": "BET9JA", "customer_id": "customer-account-id" }
```

## Errors

Validation errors use HTTP `422`:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "tracking_id": ["The tracking id field is required."]
  }
}
```

| HTTP status | Meaning | Client action |
| --- | --- | --- |
| `401` | Missing, inactive, or invalid API key | Check the environment and API key; contact Switcher if needed. |
| `404` | Requested transaction was not found for your client | Verify `tracking_id`; do not query another client's transaction. |
| `422` | Request validation failed or requery is not allowed | Correct the fields shown in `errors`; only requery `pending` transactions. |
| `500` | Unexpected Switcher or upstream error | Retain `tracking_id` and contact Switcher support before retrying a purchase. |

## Test-to-live rollout

**Recommended policy: issue a separate live API key for every client.** Keep the request format unchanged, but use a distinct live base URL and live credential. This is the safest option: test credentials can never accidentally create a live transaction, access can be revoked independently, and audit records remain clearly separated.

| Phase | Client action | Switcher action |
| --- | --- | --- |
| Test | Use test URL and test key | Enable test client and provide test products/routing |
| Go-live approval | Complete UAT and provide live callback/contact details if required | Approve account, configure live routing and limits |
| Live | Replace both base URL and API key in server-side configuration | Issue an active live API key and monitor initial transactions |
| Rollback | Switch configuration back to test; do not retry a live `tracking_id` in test | Suspend/revoke the live key if required |

Using the **same key** across test and live is possible only if Switcher implements an explicit environment-aware credential model. The current client record has a single `api_key` and no environment field, so it cannot safely distinguish a test key from a live key by itself. If a shared-key option is required later, enforce environment separation through different hostnames and server-side client/environment bindings, and reject a key that is not enabled for the target environment.

## Integration notes

- Use HTTPS in all environments.
- Set reasonable connection and response timeouts in your HTTP client.
- Persist both your `tracking_id` and Switcher's `reference`.
- A timeout from your HTTP client does **not** prove that a transaction failed. Requery with the same `tracking_id`; never create a second ID for the same intended purchase.
- The legacy `POST /api/b2b` endpoint is not part of this client API and must not be used for new integrations.

## Support handoff

When contacting Switcher, include the environment, `tracking_id`, Switcher `reference` (if received), endpoint, request time in UTC, HTTP status, and response body. Never include your full API key.
