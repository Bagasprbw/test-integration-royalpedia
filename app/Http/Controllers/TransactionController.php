<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Create a new transaction via API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string|unique:pembelians,order_id',
            'username' => 'required|string',
            'layanan' => 'required|string',
            'harga' => 'required|integer|min:0',
            'user_id' => 'required|string',
            'zone' => 'nullable|string',
            'tipe_transaksi' => 'required|string|in:game,voucher,subscription',
            'voucher_id' => 'nullable|integer',
            'discount_amount' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Create transaction
        $transaction = Pembelian::create([
            'order_id' => $request->order_id,
            'username' => $request->username,
            'layanan' => $request->layanan,
            'harga' => $request->harga,
            'user_id' => $request->user_id,
            'zone' => $request->zone,
            'status' => 'Pending',
            'tipe_transaksi' => $request->tipe_transaksi,
            'voucher_id' => $request->voucher_id,
            'discount_amount' => $request->discount_amount ?? 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat',
            'data' => [
                'id' => $transaction->id,
                'order_id' => $transaction->order_id,
                'username' => $transaction->username,
                'layanan' => $transaction->layanan,
                'harga' => $transaction->harga,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at
            ]
        ], 201);
    }

    /**
     * Get transaction history
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        // Get transactions, filter by username if provided
        $query = Pembelian::query()->orderBy('created_at', 'desc');
        
        if ($request->has('username')) {
            $query->where('username', $request->username);
        }
        
        $transactions = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat transaksi berhasil diambil',
            'data' => $transactions->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'order_id' => $transaction->order_id,
                    'layanan' => $transaction->layanan,
                    'harga' => $transaction->harga,
                    'user_id' => $transaction->user_id,
                    'zone' => $transaction->zone,
                    'status' => $transaction->status,
                    'tipe_transaksi' => $transaction->tipe_transaksi,
                    'discount_amount' => $transaction->discount_amount,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at
                ];
            })
        ], 200);
    }
}
