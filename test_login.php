<?php
// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== TESTING AUTHENTICATION ===\n\n";

// Test 1: Find user
echo "1. Cek user dengan email admin@viera.test:\n";
$user = User::where('email', 'admin@viera.test')->first();
if ($user) {
    echo "   ✓ User ditemukan\n";
    echo "   - ID: {$user->id}\n";
    echo "   - Name: {$user->name}\n";
    echo "   - Email: {$user->email}\n";
    echo "   - Role: {$user->role}\n";
} else {
    echo "   ✗ User tidak ditemukan\n";
    exit(1);
}

// Test 2: Verify password
echo "\n2. Verifikasi password 'password':\n";
if (Hash::check('password', $user->password)) {
    echo "   ✓ Password benar!\n";
} else {
    echo "   ✗ Password salah\n";
    echo "   - Stored hash: " . substr($user->password, 0, 20) . "...\n";
}

// Test 3: Attempt login
echo "\n3. Test Auth::attempt():\n";
$result = Auth::attempt(['email' => 'admin@viera.test', 'password' => 'password'], false);
if ($result) {
    echo "   ✓ Login berhasil!\n";
    echo "   - User yang login: " . Auth::user()->name . "\n";
    echo "   - Role: " . Auth::user()->role . "\n";
} else {
    echo "   ✗ Login gagal\n";
}

use Illuminate\Support\Facades\Hash;
