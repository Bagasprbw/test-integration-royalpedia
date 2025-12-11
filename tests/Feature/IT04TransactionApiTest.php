<?php

namespace Tests\Feature;

use App\Models\Pembelian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IT04TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_game_transaction(): void
    {
        // Transaction data - Mobile Legends Diamond
        $transactionData = [
            'order_id' => 'TRX-GAME-' . time(),
            'username' => 'testuser',
            'layanan' => 'Mobile Legends Diamond 100',
            'harga' => 5000,
            'user_id' => '1234567890',
            'zone' => '9876',
            'tipe_transaksi' => 'game'
        ];

        // Send API request (no authentication needed)
        $response = $this->postJson('/api/transaction', $transactionData);

        // Assert: Response success
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat'
        ]);

        // Assert: Response has required data structure
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'order_id',
                'username',
                'layanan',
                'harga',
                'status',
                'created_at'
            ]
        ]);

        // Assert: Data saved to database
        $this->assertDatabaseHas('pembelians', [
            'order_id' => $transactionData['order_id'],
            'username' => 'testuser',
            'layanan' => 'Mobile Legends Diamond 100',
            'harga' => 5000,
            'status' => 'Pending',
            'tipe_transaksi' => 'game'
        ]);
    }

    public function test_can_create_netflix_subscription(): void
    {
        // Transaction data - Netflix Premium
        $transactionData = [
            'order_id' => 'TRX-NETFLIX-' . time(),
            'username' => 'netflixuser',
            'layanan' => 'Netflix Premium 1 Bulan',
            'harga' => 16000,
            'user_id' => 'netflix@example.com',
            'tipe_transaksi' => 'subscription'
        ];

        // Send API request (no authentication needed)
        $response = $this->postJson('/api/transaction', $transactionData);

        // Assert: Response success
        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Transaksi berhasil dibuat'
        ]);

        // Assert: Data saved to database
        $this->assertDatabaseHas('pembelians', [
            'order_id' => $transactionData['order_id'],
            'username' => 'netflixuser',
            'layanan' => 'Netflix Premium 1 Bulan',
            'harga' => 16000,
            'status' => 'Pending',
            'tipe_transaksi' => 'subscription'
        ]);

        // Assert: Transaction can be retrieved
        $transaction = Pembelian::where('order_id', $transactionData['order_id'])->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('netflixuser', $transaction->username);
        $this->assertEquals('Pending', $transaction->status);
    }
}
