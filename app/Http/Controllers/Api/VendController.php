<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VendService;
use Illuminate\Http\Request;
use App\Models\Transaction;
class VendController extends Controller
{

 protected VendService $service;

    public function __construct(VendService $service)
    {
        $this->service = $service;
    }
        public function vend(Request $request)
        {
            $data = $request->validate([
                'tracking_id' => 'required|string',
                'client_id' => 'required|exists:clients,id',
                'product_type' => 'required|string',
                'network' => 'required|string',
                'amount' => 'required|numeric',
                'beneficiary' => 'nullable|string',
            ]);

            return response()->json(
                $this->service->handle($data)
            );
        }

       public function oatek(Request $request)
        {
            // minimal validation
            $request->validate([
                'serviceCode' => 'required|string',
                'request_id' => 'required|string',
            ]);

            // 🔥 FIX: resolve network BEFORE building array
            $network = $request->input('network')
                ?? $this->inferNetwork($request->input('msisdn'))
                ?? 'MTN'; // fallback for now

            // 🔄 normalize Oatek → internal
            $normalized = [
                'tracking_id' => $request->input('request_id'),
                'client_id' => 1, // temporary

                'product_type' => $this->mapServiceCode(
                    $request->input('serviceCode')
                ),

                'network' => $network, // ✅ FIXED

                'amount' => $request->input('amount'),

                'beneficiary' => $request->input('msisdn')
                    ?? $request->input('meterNo')
                    ?? $request->input('smartCardNo')
                    ?? $request->input('account'),

                'meta' => $request->all(),
            ];

            $tx = $this->service->handle($normalized);

            return response()->json(
                $this->formatOatekResponse($tx)
            );
        }

        private function mapServiceCode(string $code): string
    {
                return match ($code) {
                'VAR'                   => 'airtime',
                'ADA'                   => 'data',
                'BDA'                   => 'data',        // bundle fetch still data
                'VAQ', 'QAD'            => 'requery',
                'P-TV', 'V-TV'          => 'tv',
                'P-ELECT', 'V-ELECT'    => 'electricity',
                'V-Internet', 'P-Internet' => 'internet',
                'SRV', 'SRP'            => 'internet',    // Smile recharge
                'BDV', 'BDP'            => 'betting',
                'WAV', 'WAB'            => 'education',
                'SHVAL', 'SHPAY'        => 'streaming',
                default                 => 'unknown',
            };
    }

                private function formatOatekResponse($tx): array
        {
            $statusMap = [
                'success' => '200',
                'pending' => '400',
                'failed' => '300',
            ];

            return [
                'status' => $statusMap[$tx['status']] ?? '300', // 🔥 FIX
                'message' => $tx['message'] ?? 'Unknown',
                'request_id' => $tx['reference'] ?? null,
                'trans_ref' => $tx['vendor_reference'] ?? null,
            ];
        }

        private function inferNetwork(?string $msisdn): ?string
            {
                if (!$msisdn) return null;

                return match (substr($msisdn, 0, 4)) {
                    '0803', '0703', '0903', '0813', '0816', '0810' => 'MTN',
                    '0805', '0705', '0905', '0815', '0811' => 'GLO',
                    '0802', '0701', '0902', '0812' => 'AIRTEL',
                    '0809', '0817', '0818', '0909' => '9MOBILE',
                    default => null,
                };
            }

    public function requery(Transaction $transaction)
    {
        return response()->json(
            $this->service->requery($transaction)
        );
    }
    
}
