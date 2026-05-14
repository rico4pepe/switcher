<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\RoutingConfig;
use Illuminate\Support\Str;
//use App\Services\Vendors\MockVendorDriver;
use App\Services\Vendors\VendorDriverResolver;

class VendService
{
    public function handle(array $data): array
    {
        // 1. IDEMPOTENCY CHECK
        $existing = Transaction::where('tracking_id', $data['tracking_id'])->first();

        if ($existing) {
            return $this->formatResponse($existing);
        }

        // 2. RESOLVE ROUTING
        $routing = RoutingConfig::where('product_type', $data['product_type'])
            ->where('network', $data['network'])
            ->where('is_active', true)
            ->firstOrFail();

        //$vendorId = $this->resolveVendor($routing);

        if ($routing->mode === 'manual') {
            return $this->manualFlow($routing, $data);
        }

        if ($routing->mode === 'auto') {
            return $this->autoFlow($routing, $data);
        }
    }

    private function resolveVendor($routing)
    {
        // 🔥 CORE RULE
        if ($routing->mode === 'manual') {
            return $routing->primary_vendor_id;
        }

        // Auto mode comes later
        return $routing->primary_vendor_id;
    }

  private function formatResponse(Transaction $tx): array
        {
            $vendorResponse = $tx->raw_vendor_response ?? [];

            return [
                'status' => $tx->status,
                'reference' => $tx->ringo_reference,
                'message' => $vendorResponse['message']
                    ?? $this->fallbackMessage($tx->status),
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

    private function shouldRetry(array $response): bool
        {
            return in_array($response['code'] ?? null, [
                'TIMEOUT',
                'NETWORK_ERROR',
                'UNKNOWN'
            ]);
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
                    $response['message'] ?? 'Vendor responded',
                    [
                        'status' => $response['status'] ?? null,
                        'code' => $response['code'] ?? null,
                    ]
                );

                // 3. FAILOVER CONDITION
                if (
                    $response['status'] === 'failed' &&
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
                        $response['message'] ?? 'Fallback vendor responded',
                        [
                            'status' => $response['status'] ?? null,
                            'code' => $response['code'] ?? null,
                        ]
                    );

                    // update vendor used
                    $transaction->vendor_id = $fallbackVendorId;
                }

                $end = microtime(true);

                // 4. Update transaction
                $transaction->update([
                    'status' => $response['status'],
                    'vendor_reference' => $response['vendor_reference'] ?? null,
                    'response_time_ms' => ($end - $start) * 1000,
                    'raw_vendor_response' => $response,
                    'resolved_at' => now(),
                ]);

                return $this->formatResponse($transaction->fresh());
            }

        private function executeTransaction(
    int $vendorId,
    array $data,
    bool $allowRetry
): array {

    // CREATE TRANSACTION
    $transaction = Transaction::create([
        'ringo_reference' => Str::uuid(),
        'tracking_id' => $data['tracking_id'],
        'client_id' => $data['client_id'],
        'vendor_id' => $vendorId,
        'product_type' => $data['product_type'],
        'network' => $data['network'],
        'beneficiary' => $data['beneficiary'] ?? null,
        'amount' => $data['amount'],
        'status' => 'pending',
        'raw_vendor_request' => $data,
    ]);

    // 🔥 EVENT: transaction created
    $this->logEvent(
        $transaction,
        'transaction_created',
        'Transaction initialized'
    );

    $resolver = new VendorDriverResolver();
    $driver = $resolver->resolve($vendorId);

    $start = microtime(true);

    $response = null;

    if ($allowRetry) {

        $maxAttempts = 3;
        $attempt = 0;

        while ($attempt < $maxAttempts) {

            $attempt++;

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

            try {

                $response = $driver->vend($data);

                // 🔥 EVENT: vendor response
                $this->logEvent(
                    $transaction,
                    'vendor_response',
                    $response['message'] ?? 'Vendor responded',
                    [
                        'status' => $response['status'] ?? null,
                        'code' => $response['code'] ?? null,
                        'attempt' => $attempt,
                    ]
                );

                if ($response['status'] === 'success') {
                    break;
                }

                if (!$this->shouldRetry($response)) {
                    break;
                }

                // 🔥 EVENT: retry triggered
                $this->logEvent(
                    $transaction,
                    'retry_attempted',
                    'Retry triggered',
                    [
                        'attempt' => $attempt,
                    ]
                );

            } catch (\Throwable $e) {

                $response = [
                    'status' => 'failed',
                    'code' => 'TIMEOUT',
                    'message' => $e->getMessage(),
                ];

                // 🔥 EVENT: vendor exception
                $this->logEvent(
                    $transaction,
                    'vendor_exception',
                    $e->getMessage(),
                    [
                        'attempt' => $attempt,
                    ]
                );
            }

            usleep(200000);
        }

    } else {

        // ✅ MANUAL MODE → single call only

        // 🔥 EVENT: vendor called
        $this->logEvent(
            $transaction,
            'vendor_called',
            'Calling vendor',
            [
                'vendor_id' => $vendorId,
            ]
        );

        try {

            $response = $driver->vend($data);

            // 🔥 EVENT: vendor response
            $this->logEvent(
                $transaction,
                'vendor_response',
                $response['message'] ?? 'Vendor responded',
                [
                    'status' => $response['status'] ?? null,
                    'code' => $response['code'] ?? null,
                ]
            );

        } catch (\Throwable $e) {

            $response = [
                'status' => 'failed',
                'code' => 'TIMEOUT',
                'message' => $e->getMessage(),
            ];

            // 🔥 EVENT: vendor exception
            $this->logEvent(
                $transaction,
                'vendor_exception',
                $e->getMessage()
            );
        }
    }

    $end = microtime(true);

    $transaction->update([
        'status' => $response['status'],
        'vendor_reference' => $response['vendor_reference'] ?? null,
        'response_time_ms' => ($end - $start) * 1000,
        'raw_vendor_response' => $response,
        'resolved_at' => now(),
    ]);

    return $this->formatResponse($transaction->fresh());
}

        private function executeRaw(
    Transaction $transaction,
    int $vendorId,
    array $data,
    bool $allowRetry
): array {

    $resolver = new VendorDriverResolver();
    $driver = $resolver->resolve($vendorId);

    $response = null;

    if ($allowRetry) {

        $maxAttempts = 3;
        $attempt = 0;

        while ($attempt < $maxAttempts) {

            $attempt++;

            try {

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

                $response = $driver->vend($data);

                // 🔥 EVENT: vendor response
                $this->logEvent(
                    $transaction,
                    'vendor_response',
                    $response['message'] ?? 'Vendor responded',
                    [
                        'status' => $response['status'] ?? null,
                        'code' => $response['code'] ?? null,
                        'attempt' => $attempt,
                    ]
                );

                // SUCCESS → stop
                if ($response['status'] === 'success') {
                    break;
                }

                // NOT RETRYABLE → stop
                if (!$this->shouldRetry($response)) {
                    break;
                }

                // 🔥 EVENT: retry triggered
                $this->logEvent(
                    $transaction,
                    'retry_attempted',
                    'Retry triggered',
                    [
                        'attempt' => $attempt,
                    ]
                );

            } catch (\Throwable $e) {

                $response = [
                    'status' => 'failed',
                    'code' => 'TIMEOUT',
                    'message' => $e->getMessage(),
                ];

                // 🔥 EVENT: vendor exception
                $this->logEvent(
                    $transaction,
                    'vendor_exception',
                    $e->getMessage(),
                    [
                        'attempt' => $attempt,
                    ]
                );
            }

            usleep(200000);
        }

    } else {

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

            $response = $driver->vend($data);

            // 🔥 EVENT: vendor response
            $this->logEvent(
                $transaction,
                'vendor_response',
                $response['message'] ?? 'Vendor responded',
                [
                    'status' => $response['status'] ?? null,
                    'code' => $response['code'] ?? null,
                ]
            );

        } catch (\Throwable $e) {

            $response = [
                'status' => 'failed',
                'code' => 'TIMEOUT',
                'message' => $e->getMessage(),
            ];

            // 🔥 EVENT: vendor exception
            $this->logEvent(
                $transaction,
                'vendor_exception',
                $e->getMessage()
            );
        }
    }

    return $response;
}

 public function requery(Transaction $transaction): array
{
    // only pending transactions should be requeried
    if ($transaction->status !== 'pending') {

        $this->logEvent(
            $transaction,
            'requery_rejected',
            'Transaction is not pending'
        );

        return [
            'status' => 'failed',
            'message' => 'Transaction is not pending',
        ];
    }

    // 🔥 EVENT: requery started
    $this->logEvent(
        $transaction,
        'requery_started',
        'Requery initiated'
    );

    $resolver = new VendorDriverResolver();

    $driver = $resolver->resolve($transaction->vendor_id);

    try {

        $response = $driver->requery($transaction);

        // 🔥 EVENT: requery response
        $this->logEvent(
            $transaction,
            'requery_response',
            $response['message'] ?? 'Requery completed',
            [
                'status' => $response['status'] ?? null,
                'code' => $response['code'] ?? null,
            ]
        );

    } catch (\Throwable $e) {

        $response = [
            'status' => 'failed',
            'code' => 'REQUERY_ERROR',
            'message' => $e->getMessage(),
        ];

        // 🔥 EVENT: requery exception
        $this->logEvent(
            $transaction,
            'requery_exception',
            $e->getMessage()
        );
    }

    // update transaction
    $transaction->update([
        'status' => $response['status'],
        'vendor_reference' => $response['vendor_reference']
            ?? $transaction->vendor_reference,
        'raw_vendor_response' => $response,
        'resolved_at' => now(),
    ]);

    // 🔥 EVENT: requery resolved
    $this->logEvent(
        $transaction,
        'requery_resolved',
        'Transaction updated after requery',
        [
            'final_status' => $response['status'] ?? null,
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


}
