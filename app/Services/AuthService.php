<?php

namespace App\Services;

use App\Models\User;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function registerClient($data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'client'
        ]);
        Client::create([
            'user_id' => $user->id,
            'company_name' => $data['company_name'],
            'plan' => $data['plan'] ?? null,
        ]);
        return $user;
    }

    public function loginClient($data)
    {
        if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            throw new \Exception('Invalid credentials');
        }
        $user = Auth::user();
        return $user->createToken('client-token')->plainTextToken;
    }
}