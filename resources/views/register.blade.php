<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>
        <form method="POST" action="{{ route('web.register') }}" class="space-y-4" id="registerForm">
            @csrf
            <input type="text" name="name" placeholder="Name" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring" />
            <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring" />
            <input type="text" name="company_name" placeholder="Company Name" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring" />
            <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring" />
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring" />
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Register</button>
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
            <div class="mt-4 text-green-600 text-sm">
                {{ session('success') }}
            </div>
        @endif
        <div class="mt-4 text-center">
            <a href="/login" class="text-blue-600 hover:underline">Already have an account? Login</a>
        </div>
    </div>
</body>
</html>
<script>
            const result = await res.json();
            document.getElementById('result').innerText = result.error ? result.error : "Registration successful!";
        
    </script>
</body>
</html>
