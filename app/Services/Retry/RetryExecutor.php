<?php

namespace App\Services\Retry;

class RetryExecutor
{
    public function execute(
        callable $operation,
        callable $shouldRetry,
        int $maxRetries = 2
    ) {
        $attempt = 0;

        while (true) {

            try {

                $attempt++;

                $response = $operation($attempt);

                if (
                    $attempt <= $maxRetries &&
                    $shouldRetry($response)
                ) {
                    continue;
                }

                return $response;

            } catch (\Throwable $e) {

                if ($attempt <= $maxRetries) {
                    continue;
                }

                throw $e;
            }
        }
    }
}
