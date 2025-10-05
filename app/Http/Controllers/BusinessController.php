<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BusinessService;

class BusinessController extends Controller
{
    protected $businessService;

    public function __construct(BusinessService $businessService)
    {
        $this->businessService = $businessService;
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'business_name' => 'required',
                'phone_number' => 'required',
            ]);

            $client = auth()->user()->client;
            $business = $this->businessService->createBusiness(
                $client,
                $request->business_name,
                $request->phone_number
            );

            return response()->json($business);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
