<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Http;

class BusinessService
{
    public function createBusiness($client, $business_name, $phone_number)
    {
        $business = $client->businesses()->create([
            'business_name' => $business_name,
            'phone_number' => $phone_number,
        ]);

        try {
            $response = Http::post('http://localhost:3001/api/session/create', [
                'business_id' => $business->id,
                'phone_number' => $business->phone_number,
            ]);

            if ($response->ok()) {
                $business->session_token = $response->json('session_token');
                $business->qr_code = $response->json('qr');
                $business->save();
            }
        } catch (\Exception $e) {
            // Optionally log error
        }

        return $business;
    }
}
