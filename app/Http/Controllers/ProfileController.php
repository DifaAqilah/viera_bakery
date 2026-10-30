<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Tampilkan form edit profil.
     */
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    /**
     * Simpan perubahan profil.
     *
     * Email tidak boleh diubah oleh pelanggan, sehingga tidak divalidasi
     * dari request. Email lama diambil dari Auth::user().
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi hanya field yang boleh diubah pelanggan
        $rules = [
            'name'         => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'address'      => 'nullable|string|max:500',
        ];

        // Password hanya divalidasi jika diisi
        if ($request->filled('password')) {
            $rules['password']              = 'required|string|min:6|confirmed';
            $rules['password_confirmation'] = 'required';
        }

        $data = $request->validate($rules);

        $user->name         = $data['name'];
        // Email tetap menggunakan data lama — tidak diubah
        $user->phone_number = $data['phone_number'] ?? $user->phone_number;
        $user->address      = $data['address'] ?? $user->address;

        if ($request->filled('password')) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
