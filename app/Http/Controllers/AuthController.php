<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
                'company_name' => 'required|string|max:255'
            ]);
            // dd($data); // Uncomment for debugging
            $user = $this->authService->registerClient($data);

            if ($request->expectsJson()) {
                return response()->json(['user' => $user], 201);
            }
            return redirect('/register')->with('success', 'Registration successful!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string'
            ]);
            if (!auth()->attempt($data)) {
                throw new \Exception('Invalid credentials');
            }
            $request->session()->regenerate();

            // Optionally, you can generate a token for API use
            $token = auth()->user()->createToken('client-token')->plainTextToken;

            if ($request->expectsJson()) {
                return response()->json(['token' => $token]);
            }
            return redirect()->route('dashboard')->with('success', 'Login successful!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 401);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
