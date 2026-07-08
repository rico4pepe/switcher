<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\RoutingConfig;
use Illuminate\Support\Str;
//use App\Services\Vendors\MockVendorDriver;
use App\Services\Vendors\VendorDriverResolver;
use App\DataTransferObjects\TransactionRequestData;
use App\Services\Retry\RetryExecutor;
use App\Data\Responses\NormalizedVendorResponse;
use Illuminate\Validation\ValidationException;



class VendService
{

public function __construct(
    private RetryExecutor $retryExecutor,
    private BundleService $bundleService,
    private TvValidationService $tvService,
     protected RoutingResolver $routingResolver,
      protected VendorDriverResolver $resolver,
) {}

    public function handle(array $data): array
    {


            $data['product_type'] = strtolower(
            trim($data['product_type'])
        );

        $data['network'] = strtoupper(
            trim($data['network'])
        );

if (
    $data['product_type'] === 'data'
    && isset($data['product_code'])
) {

    $bundles = $this->bundleService
        ->fetch($data['network']);

    if (empty($bundles)) {

        throw ValidationException::withMessages([
            'network' => [
                'No bundles returned for the selected network.'
            ],
        ]);
    }

    $bundle = collect($bundles)
        ->firstWhere(
            'product_code',
            $data['product_code']
        );

    if (! $bundle) {

        throw ValidationException::withMessages([
            'product_code' => [
                'Selected bundle does not exist for this network.'
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Populate canonical transaction data
    |--------------------------------------------------------------------------
    */

    $data['amount'] = $bundle['amount'];

    $data['product_code'] = $bundle['product_code'];

    $data['package_name'] = $bundle['display_name'] ?? null;

    $data['period'] = $bundle['validity'] ?? null;
}

if (
    $data['product_type'] === 'tv'
    && isset($data['package_code'])
) {



    $package = $this->tvService
        ->findPackage(
            $data['network'],
            $data['beneficiary'],
            $data['package_code'],

        );




    $data['amount'] = $package['price']
        ?? $package['amount']
        ?? null;

        $data['package_name'] = $package['name'] ?? null;




    if (! $data['amount']) {

        throw ValidationException::withMessages([
            'package_code' => [
                'Unable to determine package amount.'
            ],
        ]);
    }
}


        // 1. IDEMPOTENCY CHECK
        $existing = Transaction::query()

    ->where(
        'client_id',
        $data['client_id']
    )

    ->where(
        'tracking_id',
        $data['tracking_id']
    )

    ->first();

        if ($existing) {
            return $this->formatResponse($existing);
        }


        // 2. RESOLVE ROUTING
       $routing = $this->routingResolver->resolve(
    clientId: (int) $data['client_id'],
    productType: $data['product_type'],
    network: $data['network']
);



    if (! $routing) {

    throw ValidationException::withMessages([
        'routing' => [
            sprintf(
                'No active routing configuration found for %s on %s.',
                $data['product_type'],
                $data['network']
            ),
        ],
    ]);
}


        //$vendorId = $this->resolveVendor($routing);
   if ($routing->mode === 'manual') {
            return $this->manualFlow($routing, $data);
        }

        if ($routing->mode === 'auto') {
            return $this->autoFlow($routing, $data);
        }
    }

    // private function resolveVendor($routing)
    // {
    //     // 🔥 CORE RULE
    //     if ($routing->mode === 'manual') {
    //         return $routing->primary_vendor_id;
    //     }

    //     // Auto mode comes later
    //     return $routing->primary_vendor_id;
    // }

            private function formatResponse(
                Transaction $transaction
            ): array {

                return [

                    'status' => $transaction->status,

                    'reference' => $transaction->ringo_reference,

                    'tracking_id' => $transaction->tracking_id,

                    'message' => data_get(
                        $transaction->raw_vendor_response,
                        'message',
                        'Transaction processed'
                    ),

                ];
            }
private function fallbackMessage(string $status): string
    {
        return match ($status) {
            'success' => 'Success',
            'failed' => 'Failed',
            'pending' => 'Processing',
            default => 'Unknown',
        };
    }

            private function shouldRetry(
                NormalizedVendorResponse $response
            ): bool {

                return $response->isRetryable();
            }


        private function manualFlow($routing, array $data): array
        {
            $vendorId = $routing->primary_vendor_id;



            return $this->executeTransaction($vendorId, $data, false);
        }

                    private function autoFlow($routing, array $data): array
            {
                $primaryVendorId = $routing->primary_vendor_id;

                // 1. Create transaction FIRST (single record)
                $transaction = Transaction::create([
                    'ringo_reference' => Str::uuid(),
                    'tracking_id' => $data['tracking_id'],
                    'client_id' => $data['client_id'],
                    'vendor_id' => $primaryVendorId,
                    'product_type' => $data['product_type'],
                    'network' => $data['network'],
                    'beneficiary' => $data['beneficiary'] ?? null,
                    'amount' => $data['amount'],
                    'status' => 'pending',
                    'raw_vendor_request' => $data,
                ]);

                // 🔥 Timeline: transaction created
                $this->logEvent(
                    $transaction,
                    'transaction_created',
                    'Transaction initialized'
                );

                $start = microtime(true);

                // 🔥 Timeline: primary vendor call
                $this->logEvent(
                    $transaction,
                    'vendor_called',
                    'Calling primary vendor',
                    [
                        'vendor_id' => $primaryVendorId,
                    ]
                );

                // 2. Try primary
                $response = $this->executeRaw(
                    $transaction,
                    $primaryVendorId,
                    $data,
                    true
                );

                // 🔥 Timeline: vendor response
                $this->logEvent(
                    $transaction,
                    'vendor_response',
                    $response->message() ?? 'Vendor responded',
                    [
                        'status' => $response->status() ?? null,
                        'code' => $response->code() ?? null,
                    ]
                );

                // 3. FAILOVER CONDITION
                if (
                    $response->status() === 'failed' &&
                    $routing->auto_failover_enabled &&
                    $routing->fallback_vendor_id
                ) {
                    $fallbackVendorId = $routing->fallback_vendor_id;

                    // 🔥 Timeline: failover triggered
                    $this->logEvent(
                        $transaction,
                        'failover_triggered',
                        'Switching to fallback vendor',
                        [
                            'from_vendor' => $primaryVendorId,
                            'to_vendor' => $fallbackVendorId,
                        ]
                    );

                    // 🔥 Timeline: fallback vendor call
                    $this->logEvent(
                        $transaction,
                        'vendor_called',
                        'Calling fallback vendor',
                        [
                            'vendor_id' => $fallbackVendorId,
                        ]
                    );

                    $response = $this->executeRaw(
                        $transaction,
                        $fallbackVendorId,
                        $data,
                        true
                    );

                    // 🔥 Timeline: fallback response
                    $this->logEvent(
                        $transaction,
                        'vendor_response',
                        $response->message() ?? 'Fallback vendor responded',
                        [
                         'status' => $response->status(),
                        'code' => $response->code(),
                        ]
                    );

                    // update vendor used
                    $transaction->vendor_id = $fallbackVendorId;
                }

                $end = microtime(true);

                 if ($response->status() === Transaction::STATUS_SUCCESS) {

        $transaction->markSuccessful();

    } elseif ($response->status() === Transaction::STATUS_FAILED) {

        $transaction->markFailed();

    } else {

        $transaction->markPending();
    }

                // 4. Update transaction
                $transaction->update([

                    'vendor_reference' => $response->vendorReference()?? null,
                    'response_time_ms' => ($end - $start) * 1000,
                    'raw_vendor_response' => $response->toArray(),

                    'resolved_at' => now(),

                ]);

                return $this->formatResponse($transaction->fresh());
            }

 private function executeTransaction(
    int $vendorId,
    array $data,
    bool $allowRetry
): array {


try {

    //code...
      $transaction = Transaction::create([
        'ringo_reference' => Str::uuid(),
        'tracking_id' => $data['tracking_id'],
        'client_id' => $data['client_id'],
        'vendor_id' => $vendorId,
        'product_type' => $data['product_type'],
        'network' => $data['network'],
        'beneficiary' => $data['beneficiary'],
       'amount' => $data['amount'],
        'status' => Transaction::STATUS_PENDING,
        'raw_vendor_request' => $data,
    ]);

} catch (\Throwable $e) {

    dd([
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ]);
}
    // CREATE TRANSACTION


    // 🔥 EVENT: transaction created
    $this->logEvent(
        $transaction,
        'transaction_created',
        'Transaction initialized'
    );



   $driver = $this->resolver->resolve(
    $vendorId
);

    $start = microtime(true);

    $payload = $this->buildPayload(
    $transaction,
    $data
);


    try {

        if ($allowRetry) {

            $response = $this->retryExecutor->execute(

                operation: function ($attempt) use (
                    $driver,
                    $payload,
                    $transaction,
                    $vendorId
                ) {

                    // 🔥 EVENT: vendor called
                    $this->logEvent(
                        $transaction,
                        'vendor_called',
                        'Calling vendor',
                        [
                            'vendor_id' => $vendorId,
                            'attempt' => $attempt,
                        ]
                    );

                    $response = $driver->vend($payload);

                    // 🔥 EVENT: vendor response
                    $this->logEvent(
                        $transaction,
                        'vendor_response',
                        $response->message(),
                        [
                            'status' => $response->status(),
                            'code' => $response->code(),
                            'attempt' => $attempt,
                        ]
                    );

                    return $response;
                },

                shouldRetry: function ($response) {

                    return $this->shouldRetry($response);
                },

                maxRetries: 2
            );

        } else {

            // 🔥 EVENT: vendor called
            $this->logEvent(
                $transaction,
                'vendor_called',
                'Calling vendor',
                [
                    'vendor_id' => $vendorId,
                ]
            );

            $response = $driver->vend($payload);

            // 🔥 EVENT: vendor response
            $this->logEvent(
                $transaction,
                'vendor_response',
                $response->message(),
                [
                    'status' => $response->status(),
                    'code' => $response->code(),
                ]
            );
        }

    } catch (\Throwable $e) {

        // 🔥 EVENT: vendor exception
        $this->logEvent(
            $transaction,
            'vendor_exception',
            $e->getMessage()
        );

        $response = new NormalizedVendorResponse(
            status: Transaction::STATUS_FAILED,
            code: 'TIMEOUT',
            message: $e->getMessage(),
        );
    }

    $end = microtime(true);

    // ✅ CANONICAL STATE TRANSITION
    if ($response->status() === Transaction::STATUS_SUCCESS) {

        $transaction->markSuccessful();

    } elseif ($response->status() === Transaction::STATUS_FAILED) {

        $transaction->markFailed();

    } else {

        $transaction->markPending();
    }

    // ✅ EXECUTION METADATA
    $transaction->update([
        'vendor_reference' => $response->vendorReference(),

        'response_time_ms' => ($end - $start) * 1000,

        'raw_vendor_response' => $response->toArray(),


    'resolved_at' => now(),
    ]);

    return $this->formatResponse(
        $transaction->fresh()
    );
}

  protected function executeRaw(
    Transaction $transaction,
    int $vendorId,
    array $data,
    bool $allowRetry = true
): NormalizedVendorResponse {

    //$resolver = new VendorDriverResolver();

    $driver = $this->resolver->resolve($vendorId);

    $payload = $this->buildPayload(
    $transaction,
    $data
);

    if ($allowRetry) {

        return $this->retryExecutor->execute(

            operation: function ($attempt) use (
                $driver,
                $payload,
                $transaction,
                $vendorId
            ) {

                // 🔥 EVENT: vendor called
                $this->logEvent(
                    $transaction,
                    'vendor_called',
                    'Calling vendor',
                    [
                        'vendor_id' => $vendorId,
                        'attempt' => $attempt,
                    ]
                );

                $response = $driver->vend($payload);
                /** @var \App\Data\Responses\NormalizedVendorResponse $response */

                // 🔥 EVENT: vendor response
                $this->logEvent(
                    $transaction,
                    'vendor_response',
                    $response->message()?? 'Vendor responded',
                    [
                        'status' => $response->status()?? null,
                        'code' => $response->code() ?? null,
                        'attempt' => $attempt,
                    ]
                );

                return $response;
            },

            shouldRetry: function ($response) {

                return $this->shouldRetry($response);
            },

            maxRetries: 2
        );
    }

    // ✅ MANUAL MODE → single attempt only

    try {

        // 🔥 EVENT: vendor called
        $this->logEvent(
            $transaction,
            'vendor_called',
            'Calling vendor',
            [
                'vendor_id' => $vendorId,
            ]
        );

        $response = $driver->vend($payload);
        /** @var \App\Data\Responses\NormalizedVendorResponse $response */

        // 🔥 EVENT: vendor response
        $this->logEvent(
            $transaction,
            'vendor_response',
           $response->message() ?? 'Vendor responded',
            [
                'status' => $response->status() ?? null,
                'code' => $response->code() ?? null,
            ]
        );

        return $response;

    } catch (\Throwable $e) {

        // 🔥 EVENT: vendor exception
        $this->logEvent(
            $transaction,
            'vendor_exception',
            $e->getMessage()
        );

        return new NormalizedVendorResponse(
            status: 'failed',
            code: 'TIMEOUT',
            message: $e->getMessage(),
        );
    }
}
 public function requery(Transaction $transaction): array
{
    // only pending transactions should be requeried
    if ($transaction->status !== 'pending') {

         $this->logEvent(
        $transaction,
        'requery_rejected',
        'Transaction is not pending',
    );

        $response = new NormalizedVendorResponse(
            status: 'failed',
            code: 'INVALID_STATE',
            message: 'Transaction is not pending',
        );


    }

    // 🔥 EVENT: requery started
    $this->logEvent(
        $transaction,
        'requery_started',
        'Requery initiated'
    );

    // $resolver = new VendorDriverResolver();

    $driver = $this->resolver->resolve($transaction->vendor_id);

    try {

        $response = $driver->requery($transaction);

        // 🔥 EVENT: requery response
        $this->logEvent(
            $transaction,
            'requery_response',
            $response->message() ?? 'Requery completed',
            [
                'status' => $response->status() ?? null,
                'code' => $response->code() ?? null,
            ]
        );

    } catch (\Throwable $e) {

        $response = new NormalizedVendorResponse(
            status: 'failed',
            code: 'REQUERY_ERROR',
            message: $e->getMessage(),
        );

        // 🔥 EVENT: requery exception
        $this->logEvent(
            $transaction,
            'requery_exception',
            $e->getMessage()
        );
    }

     if ($response->status() === Transaction::STATUS_SUCCESS) {

        $transaction->markSuccessful();

    } elseif ($response->status() === Transaction::STATUS_FAILED) {

        $transaction->markFailed();

    } else {

        $transaction->markPending();
    }

    // update transaction
   $transaction->update([

    'vendor_reference' => $response->vendorReference()
        ?? $transaction->vendor_reference,

    'raw_vendor_response' => $response->toArray(),



    'resolved_at' => now(),
]);

    // 🔥 EVENT: requery resolved
    $this->logEvent(
        $transaction,
        'requery_resolved',
        'Transaction updated after requery',
        [
            'final_status' => $response->status() ?? null,
        ]
    );

    return $this->formatResponse($transaction->fresh());
}

        private function logEvent(
            Transaction $transaction,
            string $type,
            ?string $message = null,
            array $meta = []
        ): void {
            $transaction->events()->create([
                'event_type' => $type,
                'message' => $message,
                'meta' => $meta,
            ]);
        }


private function buildPayload(
    Transaction $transaction,
    array $data
): TransactionRequestData {

    return new TransactionRequestData(

        ringo_reference: $transaction->ringo_reference,

        transaction_id: $transaction->id,

        tracking_id: $data['tracking_id'],

        client_id: $data['client_id'],

        product_type: $data['product_type'],

        network: $data['network'] ?? null,

        beneficiary: $data['beneficiary'] ?? null,

        amount: $data['amount'] ?? null,

        // ✅ Canonical Switcher product
        product_code: $data['product_code'] ?? null,

        // Temporary for backward compatibility
        product_id: $data['product_id'] ?? null,

        package_code: $data['package_code'] ?? null,

        package_name: $data['package_name'] ?? null,

        period: $data['period'] ?? null,

        has_addon: $data['has_addon'] ?? false,

        addon_code: $data['addon_code'] ?? null,

        addon_name: $data['addon_name'] ?? null,

        meter_type: $data['meter_type'] ?? null,

        phone_number: $data['phone_number'] ?? null,

        customer_name: $data['customer_name'] ?? null,

        meta: $data['meta'] ?? [],
    );
}


}
