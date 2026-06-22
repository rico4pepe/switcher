<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TvValidationService;

class TvController extends Controller
{
    public function __construct(
        protected TvValidationService $service
    ) {
    }

    public function validateCustomer(
        Request $request
    ) {

        $data = $request->validate([
            'smart_card_no' => 'required|string',
            'provider' => 'required|string',
        ]);

        return response()->json(
            $this->service->validate(
                $data['smart_card_no'],
                $data['provider']
            )
        );
    }

    public function subscriptionStatus(
    Request $request
)
{
    $data = $request->validate([

        'smart_card_no' => 'required|string',

        'provider' => 'required|string',
    ]);

    return response()->json(

        $this->service->getSubscriptionStatus(

            $data['smart_card_no'],

            $data['provider']
        )
    );
}
public function addons(
    Request $request
)
{
    $data = $request->validate([

        'package_code' => 'required|string',
    ]);

    return response()->json(

        $this->service->fetchAddons(

            $data['package_code']
        )
    );
}

public function boxOffice(
    Request $request
)
{
    $data = $request->validate([

        'smart_card_no' => 'required|string',

        'provider' => 'required|string',
    ]);

    return response()->json(

        $this->service->checkBoxOffice(

            $data['smart_card_no'],

            $data['provider']
        )
    );
}
}
