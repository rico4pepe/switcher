<?php

namespace App\Services\Vendors;

use App\Models\Transaction;
use App\DataTransferObjects\TransactionRequestData;
use App\Data\Responses\NormalizedVendorResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;

class VendifyDriver extends BaseVendorDriver implements VendorInterface
{


   private string $baseUrl;

private string $clientId;

private string $secret;

    // private string $requeryUrl;

public function __construct()
{
    $this->baseUrl = (string) config(
        'services.vendy.base_url'
    );

    $this->clientId = (string) config(
        'services.vendy.client_id'
    );

    $this->secret = (string) config(
        'services.vendy.secret'
    );

}

private function resolveNetwork(
    string $network
): string
{
    return match (strtolower($network)) {

        'mtn' => 'mtn',

        'airtel' => 'airtel',

        'glo' => 'glo',

        '9mobile',
        'etisalat',
        't2' => 't2',

        default => throw ValidationException::withMessages([
            'network' => [
                "Unsupported network: {$network}"
            ]
        ]),
    };
}


private function headers(): array
{
    return [
        'X-ClientId' => $this->clientId,

        'X-Secret' => $this->secret,
    ];
}

private function normalizeBundles(
    array $response,
    string $network
): array {

    return collect(
        $response['plans'] ?? []
    )

        ->map(function ($bundle) use ($network) {

            return [

                'network' => strtoupper(
                    $network
                ),

                'product_id' => $bundle['tarrifTypeId'] ?? null,

                'allowance' => $bundle['productName'] ?? null,

                'amount' => (float) (
                    $bundle['price'] ?? 0
                ),

                'validity' => null,

                'category' => $bundle['productType'] ?? null,
            ];
        })

        ->values()

        ->toArray();
}

 public function vend(
    TransactionRequestData $payload
): NormalizedVendorResponse {

    return match (
        strtolower($payload->product_type)
    ) {

        'airtime' => $this->vendAirtime(
            $payload
        ),

        'data' => $this->vendData(
            $payload
        ),

        default => $this->failed(
            'Unsupported product type'
        ),
    };
}



   public function requery(
    Transaction $transaction
): NormalizedVendorResponse
{
    $payload = [

        'trackingId' => $transaction->tracking_id,
    ];

    $response = Http::withHeaders(
        $this->headers()
    )->asJson()->post(

        $this->baseUrl .
        '/airtime/vend/query',

        $payload
    );


    if (!$response->successful()) {

    return new NormalizedVendorResponse(
        status: 'failed',
        code: (string) $response->status(),
        message: 'Vendor request failed',
        vendorReference: null,
        raw: $response->json() ?? [],
    );
}

    $data = $response->json();

    return new NormalizedVendorResponse(

        status: $this->mapStatus(
            $data['responseCode'] ?? '01'
        ),

        code: (string) (
            $data['responseCode'] ?? '01'
        ),

        message: $data['responseMessage']
            ?? 'Unknown requery response',

        vendorReference: null,

        raw: $data,
    );
}

 public function fetchBundles(
    string $network
): array {

    $resolvedNetwork = $this->resolveNetwork(
        $network
    );

    $response = Http::withHeaders(
        $this->headers()
    )->get(
        $this->baseUrl .
        "/data/plans/{$resolvedNetwork}"
    );



    return $this->normalizeBundles(
        $response->json() ?? [],
        $resolvedNetwork
    );
}


private function vendAirtime(
    TransactionRequestData $payload
): NormalizedVendorResponse
{
    if (!$payload->network) {

        throw ValidationException::withMessages([
            'network' => [
                'Network is required.'
            ]
        ]);
    }

    $network = $this->resolveNetwork(
        $payload->network
    );

    $response = Http::withHeaders(
        $this->headers()
    )->asJson()->post(

        $this->baseUrl .
        "/airtime/vend/{$network}",

        $this->buildAirtimePayload(
            $payload
        )
    );

    if (!$response->successful()) {

    return new NormalizedVendorResponse(
        status: 'failed',
        code: (string) $response->status(),
        message: 'Vendor request failed',
        vendorReference: null,
        raw: $response->json() ?? [],
    );
}

    $data = $response->json();

    return new NormalizedVendorResponse(

        status: $this->mapStatus(
            $data['responseCode'] ?? '01'
        ),

        code: (string) (
            $data['responseCode'] ?? '01'
        ),

        message: $data['responseMessage']
            ?? 'Unknown vendor response',

        vendorReference: null,

        raw: $data,
    );
}

private function vendData(
    TransactionRequestData $payload
): NormalizedVendorResponse
{
    if (!$payload->network) {

        throw ValidationException::withMessages([
            'network' => [
                'Network is required.'
            ]
        ]);
    }

    $network = $this->resolveNetwork(
        $payload->network
    );

    $response = Http::withHeaders(
        $this->headers()
    )->asJson()->post(

        $this->baseUrl .
        "/data/vend/{$network}",

        $this->buildDataPayload(
            $payload
        )
    );

    $data = $response->json();

    return new NormalizedVendorResponse(

        status: $this->mapStatus(
            $data['responseCode'] ?? '01'
        ),

        code: (string) (
            $data['responseCode'] ?? '01'
        ),

        message: $data['responseMessage']
            ?? 'Unknown vendor response',

        vendorReference: null,

        raw: $data,
    );
}

private function mapStatus(
    string $status
): string
{
    return match ($status) {

        '00' => 'success',

        '02' => 'pending',

        default => 'failed',
    };
}

private function buildAirtimePayload(
    TransactionRequestData $payload
): array
{
    return [

        'beneficiaryMsisdn' => $payload->beneficiary,

        'amount' => (string) $payload->amount,

        'trackingId' => $payload->tracking_id,
    ];
}

private function buildDataPayload(
    TransactionRequestData $payload
): array
{
    return [

        'beneficiaryMsisdn' => $payload->beneficiary,

        'amount' => (string) $payload->amount,

        'tarrifTypeId' => $payload->product_id,

        'trackingId' => $payload->tracking_id,
    ];
}

public function validateTv(
    string $smartCardNo,
    string $provider
): array
{
    throw new \Exception(
        'TV validation not implemented'
    );
}
public function getTvSubscriptionStatus(
    string $smartCardNo,
    string $provider
): array
{
    throw new \Exception(
        'TV subscription status not implemented'
    );
}

public function fetchTvAddons(
    string $packageCode
): array
{
    throw new \Exception(
        'TV addons not implemented'
    );
}

public function checkTvBoxOffice(
    string $smartCardNo,
    string $provider
): array
{
    throw new \Exception(
        'TV box office check not implemented'
    );
}


            public function validateElectricity(
    string $meterNo,
    string $disco,
    string $type
): array
{
    throw new \Exception(
        'Electricity  has not been implemented'
    );
}


    public function validateBetting(
    string $customerId,
    string $biller
): array
{
    throw new \Exception(
        'Betting  has not been implemented'
    );
}

}
