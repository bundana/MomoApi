<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class MoMoController extends Controller
{
    private string $primaryKey;
    private string $secondaryKey;
    private string $X_Reference_Id;
    private string $providerCallbackHost;

    public function __construct()
    {
        $this->primaryKey = config('momo.primary_key');
        $this->secondaryKey = config('momo.secondary_key');
        $this->X_Reference_Id = config('momo.x_reference_id');
        $this->providerCallbackHost = config('momo.provider_callback_host');
    }

    private const CALLBACK = 'https://webhook.site/455d9327-4533-498d-aa16-5fd1f0c88d9b';

    public function createApiUser()
    {
        $uuid = $this->X_Reference_Id;
        $req = Http::withHeaders([
            'X-Reference-Id' => $uuid,
            'Ocp-Apim-Subscription-Key' => $this->primaryKey,
            'Cache-Control: no-cache',
            'Content-Type' => 'application/json'
        ])->post('https://sandbox.momodeveloper.mtn.com/v1_0/apiuser', [
            'providerCallbackHost' => $this->providerCallbackHost,
        ]);

        if ($req->status() == 409) {
            $body = collect($req->json());
            if (isset($body['code']) && $body['code'] == 'RESOURCE_ALREADY_EXIST') {
                return response()->json([
                    'status' => $req->status(),
                    'code' => $body['code'],
                    'message' => $body['message'],
                ]);
            }
        }


        if ($req->successful() && $req->status() == 201) {
            return response()->json([
                'code' => $req->reason(),
                'status' => $req->status(),
                'message' => 'Api User Created',
                'uuid' => $uuid,
            ]);
        }

        return response()->json([
            'code' => $req->reason(),
            'status' => $req->status(),
            'message' => 'Something went wrong',
            'uuid' => $uuid,
        ]);
    }

}
