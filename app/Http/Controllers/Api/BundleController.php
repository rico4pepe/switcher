<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\BundleService;
use App\Http\Controllers\Controller;

class BundleController extends Controller
{
    public function index(
        Request $request,
        BundleService $service
    ) {

        $request->validate([
            'network' => 'required|string',
        ]);

        return response()->json(

            $service->fetch(
                $request->network
            )

        );
    }
}
