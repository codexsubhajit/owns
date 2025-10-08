<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\Client;
use Illuminate\Support\Facades\Http;

class BusinessController extends Controller
{
    public function dashboard(Request $request)
    {
        $client = \App\Models\Client::where('user_id', auth()->id())->first();
        $businesses = $client ? $client->businesses : [];
        return view('dashboard', compact('businesses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'business_type' => 'required|string|max:255',
        ]);

        $client = \App\Models\Client::where('user_id', auth()->id())->first();

        if (!$client) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Client not found.']);
        }

        $client->businesses()->create([
            'business_name' => $data['business_name'],
            'phone_number' => $data['phone_number'],
            'business_type' => $data['business_type'],
        ]);
        $this->generateQr($request, Business::latest()->first());

        return redirect()->route('dashboard')->with('success', 'Business added successfully!');
    }

    public function generateQr(Request $request, Business $business)
    {
        // The endpoint below should be implemented using whatsapp-web.js in a Node.js backend.
        // whatsapp-web.js will generate a WhatsApp login QR code and manage the session for messaging.
        try {
            $response = Http::post('http://localhost:3001/api/session/create', [
                'business_id' => $business->id,
                'phone_number' => $business->phone_number,
            ]);

            if ($response->ok()) {
                $business->session_token = $response->json('session_token');
                $business->qr_code = $response->json('qr'); // Should be base64 PNG from whatsapp-web.js
                $business->save();
                return redirect()->route('dashboard')->with('success', 'QR code generated. Scan with WhatsApp!');
            } else {
                return redirect()->route('dashboard')->withErrors(['error' => 'Failed to generate QR code.']);
            }
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->withErrors(['error' => 'Error generating QR code.']);
        }
    }
}
