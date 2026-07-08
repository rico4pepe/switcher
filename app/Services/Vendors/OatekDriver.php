<?php

namespace App\Services\Vendors;

use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\DataTransferObjects\TransactionRequestData;
use App\Data\Responses\NormalizedVendorResponse;
use Illuminate\Validation\ValidationException;
use App\Services\VendorProductResolver;
use App\Services\VendorRequestEncoder;

class OatekDriver extends BaseVendorDriver implements VendorInterface
{

    private string $baseUrl;

    private string $email;

    private string $password;

     private string $requeryUrl;

     private const DRIVER_KEY = 'oatek';

          public function __construct(
    protected VendorProductResolver $resolver,
     protected VendorRequestEncoder $requestEncoder
)
{
    $this->baseUrl = (string) config('services.oatek.base_url');

    $this->email = (string) config('services.oatek.email');

    $this->password = (string) config('services.oatek.password');

    $this->requeryUrl = (string) config('services.oatek.requery_url');
}
                public function vend(
                TransactionRequestData $payload
            ): NormalizedVendorResponse {

            $vendorPayload = match (
    strtolower($payload->product_type)
) {

    'airtime' => $this->buildAirtimePayload(
        $payload
    ),

    'data' => $this->buildDataPayload(
        $payload
    ),
     'tv' => $this->buildTvPayload(
        $payload
    ),

    'electricity' => $this->buildElectricityPayload(
    $payload
),
'betting' => $this->buildBettingPayload(
    $payload
),
    default => throw ValidationException::withMessages([
        'product_type' => [
            sprintf(
                'Product type [%s] is not supported.',
                $payload->product_type
            ),
        ],
    ]),
};

               $response = Http::withHeaders([

                'email' => $this->email,

                'password' => $this->password,

            ])->asJson()->post(

                $this->baseUrl . '/index.php',

                $vendorPayload
            );




                $data = $response->json();


                return new NormalizedVendorResponse(

                    status: $this->mapStatus(
                        $data['status'] ?? '500'
                    ),

                    code: (string) ($data['status'] ?? '500'),

                    message: $data['message']
                        ?? 'Unknown vendor response',

                    vendorReference: $data['ext_ref']
                            ?? $data['transId']
                            ?? null,

                    raw: $data,
                );
            }

            public function requery(
                Transaction $transaction
            ): NormalizedVendorResponse {

                $payload = [

                    'request_id' => $this->requestEncoder->encode(
                        self::DRIVER_KEY,
                        $transaction->id,
                        $transaction->ringo_reference
                    ),
                ];

                $response = Http::withHeaders([

                    'email' => $this->email,

                    'password' => $this->password,

                ])->asJson()->post(

                    $this->requeryUrl,

                    $payload
                );

                $data = $response->json();

                return new NormalizedVendorResponse(

                    status: $this->mapStatus(
                        $data['status'] ?? '500'
                    ),

                    code: (string) ($data['status'] ?? '500'),

                    message: $data['message']
                        ?? 'Unknown requery response',

                    vendorReference: $data['ext_ref']
                        ?? $data['transId']
                        ?? null,

                    raw: $data,
                );
            }

    private function mapStatus(
            string $vendorStatus
                ): string {

                    return match ($vendorStatus) {

                        '200' => 'success',

                        '400', '500' => 'pending',

                        default => 'failed',
                    };
                }

                private function resolveProductId(
            string $network
                ): string {

                    return match (strtolower($network)) {

                        'mtn' => 'MFIN-5-OR',
                        'airtel' => 'MFIN-1-OR',

                            'glo' => 'MFIN-6-OR',

                            '9mobile' => 'MFIN-2-OR',

                        default => throw new \Exception(
                            'Unsupported network'
                        ),
                    };
                }

                private function buildAirtimePayload(
                    TransactionRequestData $payload
                ): array {

                    return [

                        'serviceCode' => 'VAR',

                        'request_id' => $this->requestEncoder->encode(
    self::DRIVER_KEY,
    $payload->transaction_id,
    $payload->ringo_reference
),

                        'msisdn' => $payload->beneficiary,

                        'product_id' => $this->resolveProductId(
                            $payload->network
                        ),

                        'amount' => $payload->amount,
                    ];
                }

                private function buildDataPayload(
            TransactionRequestData $payload
        ): array {

      if (! $payload->product_code) {

    throw ValidationException::withMessages([
        'product_code' => [
            'Product code is required for data vending.'
        ],
    ]);
}

            if (! $payload->beneficiary) {

                throw ValidationException::withMessages([
                    'beneficiary' => [
                        'Beneficiary is required for data vending.'
                    ],
                ]);
            }


            return [

                'serviceCode' => 'ADA',

                'product_id' => $this->resolver->resolve(
                    self::DRIVER_KEY,
                    $payload->product_code
                ),

                'request_id' => $this->requestEncoder->encode(
                    self::DRIVER_KEY,
                    $payload->transaction_id,
                    $payload->ringo_reference
                ),

                'msisdn' => $payload->beneficiary,
            ];
        }


                public function fetchBundles(
                        string $network
                    ): array {

                        $response = Http::withHeaders([
                            'email' => $this->email,
                            'password' => $this->password,
                        ])->post(
                            $this->baseUrl . '/index.php',
                            [
                                'serviceCode' => 'DTA',
                                'network' => strtolower($network),
                            ]
                        );

                        return $this->normalizeBundles(
                            $response->json()
                        );
                    }

            private function normalizeBundles(
                array $bundles
            ): array {

                return collect($bundles)

                    ->map(function ($bundle) {

                        return [

                            'network' => strtoupper(
                                $bundle['network'] ?? ''
                            ),

                            'product_id' => $bundle['product_id'] ?? null,

                            'allowance' => $bundle['allowance'] ?? null,

                            'amount' => (float) (
                                $bundle['price'] ?? 0
                            ),

                            'validity' => $bundle['validity'] ?? null,

                            'category' => $bundle['category'] ?? null,
                        ];
                    })

                    ->values()

                    ->toArray();
            }

            public function validateTv(
                string $smartCardNo,
                string $provider
            ): array
            {

                if (empty($smartCardNo)) {

                throw ValidationException::withMessages([
                    'smart_card_no' => [
                        'Smart card number is required.'
                    ],
                ]);
            }

            if (empty($provider)) {

                throw ValidationException::withMessages([
                    'provider' => [
                        'TV provider is required.'
                    ],
                ]);
            }
                $response = Http::withHeaders([

                    'email' => $this->email,

                    'password' => $this->password,

                ])->post(

                    $this->baseUrl . '/index.php',

                    [
                        'serviceCode' => 'V-TV',
                        'smartCardNo' => $smartCardNo,
                        'type' => strtoupper($provider),
                    ]
                );

                return $response->json();
            }

            public function getTvSubscriptionStatus(
                string $smartCardNo,
                string $provider
            ): array
            {
                if (empty($smartCardNo)) {

                    throw ValidationException::withMessages([
                        'smart_card_no' => [
                            'Smart card number is required.'
                        ],
                    ]);
                }

                if (empty($provider)) {

                    throw ValidationException::withMessages([
                        'provider' => [
                            'TV provider is required.'
                        ],
                    ]);
                }

                $response = Http::withHeaders([

                    'email' => $this->email,

                    'password' => $this->password,

                ])->post(

                    $this->baseUrl . '/index.php',

                    [
                        'serviceCode' => 'MULTICHOICE',

                        'type' => strtoupper($provider),

                        'action' => 'GET_DUE_DATE_AMOUNT',

                        'smartCardNo' => $smartCardNo,
                    ]
                );

                return $response->json();
            }
            public function fetchTvAddons(
                string $packageCode
            ): array
            {
                //  dd([
                //     'method' => 'fetchTvAddons',
                //     'packageCode' => $packageCode,
                // ]);
                if (empty($packageCode)) {

                    throw ValidationException::withMessages([
                        'package_code' => [
                            'Package code is required.'
                        ],
                    ]);
                }

                $response = Http::withHeaders([

                    'email' => $this->email,

                    'password' => $this->password,

                ])->post(

                    $this->baseUrl . '/index.php',

                    [
                        'serviceCode' => 'MULTICHOICE',

                        'action' => 'GET_ADDONS',

                        'code' => $packageCode,
                    ]
                );

                return $response->json();
            }

            public function checkTvBoxOffice(
                string $smartCardNo,
                string $provider
            ): array
            {
                if (empty($smartCardNo)) {

                    throw ValidationException::withMessages([
                        'smart_card_no' => [
                            'Smart card number is required.'
                        ],
                    ]);
                }

                if (empty($provider)) {

                    throw ValidationException::withMessages([
                        'provider' => [
                            'TV provider is required.'
                        ],
                    ]);
                }

                $response = Http::withHeaders([

                    'email' => $this->email,

                    'password' => $this->password,

                ])->post(

                    $this->baseUrl . '/index.php',

                    [
                        'serviceCode' => 'MULTICHOICE',

                        'type' => strtoupper($provider),

                        'action' => 'CHECK_BOX_OFFICE',

                        'smartCardNo' => $smartCardNo,
                    ]
                );

                return $response->json();
            }



            private function buildTvPayload(
                TransactionRequestData $payload
            ): array {

                if (! $payload->beneficiary) {
                    throw ValidationException::withMessages([
                        'beneficiary' => ['Smart card number is required.'],
                    ]);
                }

                if (! $payload->network) {
                    throw ValidationException::withMessages([
                        'network' => ['TV provider is required.'],
                    ]);
                }

                if (! $payload->package_code) {
                    throw ValidationException::withMessages([
                        'package_code' => ['Package code is required.'],
                    ]);
                }

                if (! $payload->period) {
                    throw ValidationException::withMessages([
                        'period' => ['Subscription period is required.'],
                    ]);
                }

                $request = [
                    'serviceCode' => 'P-TV',
                    'smartCardNo' => $payload->beneficiary,
                    'name' => $payload->package_name,
                    'type' => strtoupper($payload->network),
                    'code' => $payload->package_code,
                    'period' => (string) $payload->period,
                   'request_id' => $this->requestEncoder->encode(
    self::DRIVER_KEY,
    $payload->transaction_id,
    $payload->ringo_reference
),
                    'hasAddon' => $payload->has_addon ? 'True' : 'False',
                ];

                if ($payload->has_addon) {

                    if (! $payload->addon_code) {
                        throw ValidationException::withMessages([
                            'addon_code' => ['Addon code is required.'],
                        ]);
                    }

                    $request['addondetails'] = [
                        'name' => $payload->addon_name,
                        'addoncode' => $payload->addon_code,
                    ];
                }

                return $request;
            }

            public function validateElectricity(
    string $meterNo,
    string $disco,
    string $type
): array
{
    $response = Http::withHeaders([

        'email' => $this->email,

        'password' => $this->password,

    ])->post(

        $this->baseUrl . '/index.php',

        [

            'serviceCode' => 'V-ELECT',

            'disco' => strtoupper($disco),

            'meterNo' => $meterNo,

            'type' => strtoupper($type),
        ]
    );

    return $response->json();
}


private function buildElectricityPayload(
    TransactionRequestData $payload
): array {

    if (! $payload->beneficiary) {

        throw ValidationException::withMessages([
            'beneficiary' => [
                'Meter number is required.'
            ],
        ]);
    }

    if (! $payload->phone_number) {

    throw ValidationException::withMessages([
        'phone_number' => [
            'Phone number is required.'
        ],
    ]);
}

    if (! $payload->network) {

        throw ValidationException::withMessages([
            'network' => [
                'Disco is required.'
            ],
        ]);
    }

    if (! $payload->meter_type) {

        throw ValidationException::withMessages([
            'meter_type' => [
                'Meter type is required.'
            ],
        ]);
    }

    if (! $payload->amount) {

        throw ValidationException::withMessages([
            'amount' => [
                'Amount is required.'
            ],
        ]);
    }

    return [

        'serviceCode' => 'P-ELECT',

        'disco' => strtoupper(
            $payload->network
        ),

        'meterNo' => $payload->beneficiary,

        'type' => strtoupper(
            $payload->meter_type
        ),

        'amount' => (string) $payload->amount,

       'phonenumber' => $payload->phone_number,

        'request_id' => $this->requestEncoder->encode(
            self::DRIVER_KEY,
            $payload->transaction_id,
            $payload->ringo_reference
        ),
    ];
}

public function validateBetting(
    string $customerId,
    string $biller
): array
{
    $response = Http::withHeaders([

        'email' => $this->email,

        'password' => $this->password,

    ])->post(

        $this->baseUrl . '/index.php',

        [

            'serviceCode' => 'BDV',

            'type' => 'BET',

            'biller' => $biller,

            'customerId' => $customerId,
        ]
    );

    return $response->json();
}



private function buildBettingPayload(
    TransactionRequestData $payload
): array {

    if (! $payload->beneficiary) {

        throw ValidationException::withMessages([
            'beneficiary' => [
                'Customer ID is required.'
            ],
        ]);
    }

    if (! $payload->network) {

        throw ValidationException::withMessages([
            'network' => [
                'Betting platform is required.'
            ],
        ]);
    }

    if (! $payload->customer_name) {

        throw ValidationException::withMessages([
            'customer_name' => [
                'Customer name is required.'
            ],
        ]);
    }

    if (! $payload->amount) {

        throw ValidationException::withMessages([
            'amount' => [
                'Amount is required.'
            ],
        ]);
    }

    return [

        'serviceCode' => 'BDP',

        'reference' => '',

        'amount' => (string) $payload->amount,

        'customerId' => $payload->beneficiary,

        'name' => $payload->customer_name,

        'type' => 'BET',

        'biller' => $payload->network,

        'request_id' => $this->requestEncoder->encode(
            self::DRIVER_KEY,
            $payload->transaction_id,
            $payload->ringo_reference
        ),
    ];
}

}
