<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BettingValidationService;

class BettingController extends Controller
{
    //
      public function __construct(
        protected BettingValidationService $bettingValidationService
    ) {
    }


    public function validateBetting(
    Request $request
)
{
    $data = $request->validate([

        'biller' => 'required|string',

        'customer_id' => 'required|string',
    ]);

    return response()->json(

        $this->bettingValidationService
            ->validate(

                $data['customer_id'],

                $data['biller']
            )
    );
}
}
