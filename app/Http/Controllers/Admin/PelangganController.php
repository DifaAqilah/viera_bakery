<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class PelangganController extends Controller
{
    /**
     * Daftar seluruh pelanggan (role = pelanggan).
     */
    public function index()
    {
        $pelanggan = User::where('role', 'pelanggan')
            ->withCount('orders')
            ->latest()
            ->paginate(20);

        return view('admin.pelanggan.index', compact('pelanggan'));
    }
}
