<?php
// WhatsApp login QR codes must be provided by a WhatsApp API/server using whatsapp-web.js.
// Remove SimpleSoftwareIO\QrCode\Facades\QrCode usage.
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto py-8">
        <h2 class="text-3xl font-bold mb-6 text-center">Client Dashboard</h2>
        <div class="max-w-lg mx-auto bg-white p-6 rounded shadow mb-8">
            <h3 class="text-xl font-semibold mb-4">Add New Business</h3>
            <form method="POST" action="{{ route('business.store') }}" class="space-y-4">
                @csrf
                <input type="text" name="business_name" placeholder="Business Name" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring" />
                <input type="text" name="phone_number" placeholder="Business Phone Number" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring" />
                <input type="text" name="business_type" placeholder="Business Type" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring" />
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition">Add Business</button>
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
        </div>
        <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow">
            <h3 class="text-xl font-semibold mb-4">Your Businesses</h3>
            <table class="w-full table-auto border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Phone</th>
                        <th class="px-4 py-2">Type</th>
                        <th class="px-4 py-2">QR Code</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($businesses as $business)
                        <tr>
                            <td class="border px-4 py-2">{{ $business->business_name }}</td>
                            <td class="border px-4 py-2">{{ $business->phone_number }}</td>
                            <td class="border px-4 py-2">{{ $business->business_type }}</td>
                            <td class="border px-4 py-2">
                                @if (!empty($business->qr_code) && strlen($business->qr_code) > 100)
                                    <img src="data:image/png;base64,{{ $business->qr_code }}" alt="WhatsApp QR" class="mx-auto" />
                                    <div class="text-xs text-gray-500 mt-2">Scan with WhatsApp to login</div>
                                @else
                                    <span class="text-gray-400 text-xs">Not generated or invalid QR code</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">No businesses added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>