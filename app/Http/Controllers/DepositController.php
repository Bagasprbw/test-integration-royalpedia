<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    /**
     * Display the deposit form
     */
    public function index()
    {
        return view('deposit.index');
    }

    /**
     * Handle deposit submission
     */
    public function store(Request $request)
    {
        $request->validate([
            'jumlah' => ['required', 'integer', 'min:10000'],
            'metode' => ['required', 'string'],
            'no_pembayaran' => ['nullable', 'string', 'max:255'],
        ]);

        $deposit = Deposit::create([
            'username' => Auth::user()->username,
            'jumlah' => $request->jumlah,
            'metode' => $request->metode,
            'status' => 'Pending',
            'no_pembayaran' => $request->no_pembayaran,
        ]);

        return redirect()->route('deposit.index')
            ->with('success', 'Deposit berhasil diajukan. Menunggu verifikasi admin.');
    }

    /**
     * Display deposit history for the authenticated user
     */
    public function history()
    {
        $deposits = Deposit::where('username', Auth::user()->username)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('deposit.history', compact('deposits'));
    }
}
