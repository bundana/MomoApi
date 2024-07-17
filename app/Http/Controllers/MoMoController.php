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
        $this->primaryKey = config('momo-config.PRIMARY_KEY');
        $this->secondaryKey = config('momo-config.SECONDARY_KEY');
        $this->X_Reference_Id = config('momo-config.X-Reference-Id');
        $this->providerCallbackHost = config('momo-config.providerCallbackHost');
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

    public function getApiUser(Request $request)
    {
        $userKey = $request->id ?? $this->X_Reference_Id;

        $req = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->primaryKey,
            'Cache-Control: no-cache',
            'Content-Type' => 'application/json'
        ])->get("https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/{$userKey}");

        if ($req->status() == 400) {
            return response()->json([
                'status' => $req->status(),
                'code' => $req->status(),
                'message' => 'Not found, reference id not found or closed in sandbox',
            ]);
        }

        if ($req->status() == 404) {
            $body = collect($req->json());
            if (isset($body['code'])) {
                return response()->json([
                    'status' => $req->status(),
                    'code' => $body['code'],
                    'message' => $body['message'],
                ]);
            }
        }

        if ($req->successful() && $req->status() == 200) {
            $body = collect($req->json());
            return response()->json([
                'code' => $req->reason(),
                'status' => $req->status(),
                'message' => 'Api User Created',
                'data' => $body,
            ]);
        }

        return response()->json([
            'code' => $req->reason(),
            'status' => $req->status(),
            'message' => 'Something went wrong',
        ]);
    }

    public function createApiKey(Request $request)
    {
        $X_Reference_Id = $request->XReferenceId ?? $this->X_Reference_Id;
        $req = Http::withHeaders([
            'Ocp-Apim-Subscription-Key' => $this->primaryKey,
            'Cache-Control: no-cache',
            'Content-Type' => 'application/json'
        ])->post("https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/{$X_Reference_Id}/apikey");

        if ($req->status() == 400) {
            $body = collect($req->json());
            return response()->json([
                'status' => $req->status(),
                'code' => $req->status(),
                'message' => 'Bad request, e.g. invalid data was sent in the request.',
                'error' => $body
            ]);
        }

        if ($req->status() == 404) {
            $body = collect($req->json());
            return response()->json([
                'status' => $req->status(),
                'code' => $req->status(),
                'message' => 'Not found, reference id not found or closed in sandbox',
                'error' => $body,
            ]);
        }

        if ($req->successful() && $req->status() == 201) {
            $body = collect($req->json());
            return response()->json([
                'code' => $req->reason(),
                'status' => $req->status(),
                'message' => 'API key for an API user created',
                'data' => $body,
            ]);
        }

        return response()->json([
            'code' => $req->reason(),
            'status' => $req->status(),
            'message' => 'Something went wrong',
        ]);
    }

}
