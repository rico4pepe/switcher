<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ElectricityValidationService;

class ElectricityController extends Controller
{
    public function __construct(
        protected ElectricityValidationService $electricityValidationService
    ) {
    }

    public function validateElectricity(
        Request $request
    )
    {
        $data = $request->validate([

            'disco' => 'required|string',

            'meter_no' => 'required|string',

            'type' => 'required|string',
        ]);

        return response()->json(

            $this->electricityValidationService
                ->validate(

                    $data['meter_no'],

                    $data['disco'],

                    $data['type']
                )
        );
    }
}
