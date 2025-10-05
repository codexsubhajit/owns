<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
        <form method="POST" action="{{ route('web.login') }}" class="space-y-4" id="loginForm">
            @csrf
            <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring" />
            <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring" />
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Login</button>
        </form>
        @if ($errors->any())
            <div class="mt-4 text-red-600 text-sm">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('success'))
            @php
                $success = session('success');
                $token = null;
                if (Str::contains($success, 'Token:')) {
                    $token = trim(Str::after($success, 'Token:'));
                }
            @endphp
            <div class="mt-4 text-green-600 text-sm">
                {{ Str::before($success, 'Token:') }}
            </div>
            @if ($token)
                <div class="mt-2 p-2 bg-gray-100 border rounded text-xs break-all">
                    <span class="font-semibold text-gray-700">Your API Token:</span>
                    <br>
                    <span class="text-gray-800">{{ $token }}</span>
                </div>
            @endif
        @endif
        <div class="mt-4 text-center">
            <a href="/register" class="text-blue-600 hover:underline">Don't have an account? Register</a>
        </div>
    </div>
</body>
</html>
