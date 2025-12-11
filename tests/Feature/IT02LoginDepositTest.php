<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Deposit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * IT-02: Login → Deposit (User)
 * Feature Test (Auth + Session + DB)
 * 
 * Tujuan: Memastikan hanya user login bisa melakukan deposit 
 * dan user ID diteruskan ke modul deposit
 * 
 * Langkah: Login → buka halaman deposit → input nominal & metode → submit
 */
class IT02LoginDepositTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user yang belum login tidak bisa akses halaman deposit
     */
    public function test_guest_cannot_access_deposit_page(): void
    {
        // Coba akses halaman deposit tanpa login
        $response = $this->get('/deposit');

        // Assert: Redirect ke login page
        $response->assertRedirect('/login');

        // Assert: User tidak ter-autentikasi
        $this->assertGuest();
    }

    /**
     * Test user yang sudah login dapat mengakses halaman deposit
     */
    public function test_authenticated_user_can_access_deposit_page(): void
    {
        // Buat user dan login
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0,
            'whatsapp' => '081234567890'
        ]);

        // Login sebagai user
        $this->actingAs($user);

        // Akses halaman deposit
        $response = $this->get('/deposit');

        // Assert: Halaman deposit dapat diakses
        $response->assertStatus(200);

        // Assert: User ter-autentikasi
        $this->assertAuthenticated();
    }

    /**
     * Test user login dapat melakukan deposit dan data tersimpan di database
     */
    public function test_authenticated_user_can_create_deposit(): void
    {
        // Step 1: Buat user dan login
        $user = User::create([
            'name' => 'Deposit User',
            'username' => 'deposituser',
            'email' => 'deposituser@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0,
            'whatsapp' => '081234567890'
        ]);

        $this->actingAs($user);

        // Step 2: Data deposit
        $depositData = [
            'jumlah' => 50000,
            'metode' => 'BCA',
            'no_pembayaran' => '1234567890'
        ];

        // Step 3: Submit deposit
        $response = $this->post('/deposit', $depositData);

        // Assert: Response redirect (biasanya ke halaman konfirmasi atau riwayat)
        $response->assertStatus(302);

        // Assert: Deposit tersimpan di database
        $this->assertDatabaseHas('deposits', [
            'username' => 'deposituser',
            'jumlah' => 50000,
            'metode' => 'BCA',
            'status' => 'Pending',
            'no_pembayaran' => '1234567890'
        ]);

        // Assert: Deposit terhubung dengan user yang benar
        $deposit = Deposit::where('username', 'deposituser')->first();
        $this->assertNotNull($deposit);
        $this->assertEquals($user->username, $deposit->username);
    }

    /**
     * Test complete flow: Login → Buka Halaman Deposit → Input & Submit
     */
    public function test_complete_login_deposit_flow(): void
    {
        // Step 1: Buat user
        $user = User::create([
            'name' => 'Complete Flow User',
            'username' => 'flowuser',
            'email' => 'flowuser@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0,
            'whatsapp' => '082345678901'
        ]);

        // Step 2: Login
        $loginResponse = $this->post('/login', [
            'email' => 'flowuser@example.com',
            'password' => 'password123'
        ]);

        $loginResponse->assertStatus(302);
        $this->assertAuthenticated();

        // Step 3: Buka halaman deposit
        $depositPageResponse = $this->get('/deposit');
        $depositPageResponse->assertStatus(200);

        // Step 4: Input nominal & metode, kemudian submit
        $depositData = [
            'jumlah' => 100000,
            'metode' => 'Mandiri',
            'no_pembayaran' => '9876543210'
        ];

        $submitResponse = $this->post('/deposit', $depositData);
        $submitResponse->assertStatus(302);

        // Step 5: Verify deposit tersimpan dengan username yang benar
        $this->assertDatabaseHas('deposits', [
            'username' => 'flowuser',
            'jumlah' => 100000,
            'metode' => 'Mandiri',
            'status' => 'Pending'
        ]);

        // Verify user ID diteruskan ke modul deposit
        $deposit = Deposit::where('username', 'flowuser')->first();
        $this->assertEquals($user->username, $deposit->username);
        $this->assertEquals($user->username, $deposit->user->username);
    }

    /**
     * Test user yang belum login tidak bisa submit deposit
     */
    public function test_guest_cannot_submit_deposit(): void
    {
        // Coba submit deposit tanpa login
        $depositData = [
            'jumlah' => 50000,
            'metode' => 'BCA',
            'no_pembayaran' => '1234567890'
        ];

        $response = $this->post('/deposit', $depositData);

        // Assert: Redirect ke login
        $response->assertRedirect('/login');

        // Assert: Tidak ada deposit yang tersimpan
        $this->assertDatabaseCount('deposits', 0);
    }

    /**
     * Test deposit dengan berbagai metode pembayaran
     */
    public function test_user_can_deposit_with_different_payment_methods(): void
    {
        $user = User::create([
            'name' => 'Multi Method User',
            'username' => 'multiuser',
            'email' => 'multiuser@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0
        ]);

        $this->actingAs($user);

        // Test berbagai metode pembayaran
        $paymentMethods = ['BCA', 'Mandiri', 'BNI', 'BRI', 'GoPay', 'OVO', 'DANA'];

        foreach ($paymentMethods as $method) {
            $response = $this->post('/deposit', [
                'jumlah' => 25000,
                'metode' => $method,
                'no_pembayaran' => 'TEST' . rand(1000, 9999)
            ]);

            $response->assertStatus(302);
        }

        // Assert: Semua deposit tersimpan
        $this->assertDatabaseCount('deposits', count($paymentMethods));

        // Assert: Semua deposit milik user yang sama
        $deposits = Deposit::where('username', 'multiuser')->get();
        $this->assertEquals(count($paymentMethods), $deposits->count());
    }

    /**
     * Test deposit dengan nominal berbeda
     */
    public function test_user_can_deposit_with_different_amounts(): void
    {
        $user = User::create([
            'name' => 'Amount Test User',
            'username' => 'amountuser',
            'email' => 'amountuser@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0
        ]);

        $this->actingAs($user);

        $amounts = [10000, 50000, 100000, 500000, 1000000];

        foreach ($amounts as $amount) {
            $response = $this->post('/deposit', [
                'jumlah' => $amount,
                'metode' => 'BCA',
                'no_pembayaran' => 'AMT' . $amount
            ]);

            $response->assertStatus(302);

            // Verify deposit tersimpan dengan jumlah yang benar
            $this->assertDatabaseHas('deposits', [
                'username' => 'amountuser',
                'jumlah' => $amount,
                'metode' => 'BCA'
            ]);
        }
    }

    /**
     * Test status deposit default adalah Pending
     */
    public function test_new_deposit_has_pending_status(): void
    {
        $user = User::create([
            'name' => 'Status Test User',
            'username' => 'statususer',
            'email' => 'statususer@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0
        ]);

        $this->actingAs($user);

        $this->post('/deposit', [
            'jumlah' => 75000,
            'metode' => 'Mandiri',
            'no_pembayaran' => 'STAT123'
        ]);

        // Assert: Status default adalah Pending
        $deposit = Deposit::where('username', 'statususer')->first();
        $this->assertEquals('Pending', $deposit->status);
    }

    /**
     * Test relasi antara User dan Deposit
     */
    public function test_deposit_relationship_with_user(): void
    {
        $user = User::create([
            'name' => 'Relation Test User',
            'username' => 'relationuser',
            'email' => 'relationuser@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0
        ]);

        $this->actingAs($user);

        // Buat beberapa deposit
        $this->post('/deposit', [
            'jumlah' => 30000,
            'metode' => 'BCA',
            'no_pembayaran' => 'REL001'
        ]);

        $this->post('/deposit', [
            'jumlah' => 40000,
            'metode' => 'Mandiri',
            'no_pembayaran' => 'REL002'
        ]);

        // Test relasi User -> Deposits
        $userWithDeposits = User::where('username', 'relationuser')->first();
        $this->assertEquals(2, $userWithDeposits->deposits()->count());

        // Test relasi Deposit -> User
        $deposit = Deposit::where('username', 'relationuser')->first();
        $this->assertEquals('relationuser', $deposit->user->username);
        $this->assertEquals('Relation Test User', $deposit->user->name);
    }

    /**
     * Test multiple users dapat melakukan deposit secara independen
     */
    public function test_multiple_users_can_deposit_independently(): void
    {
        // Buat 3 user berbeda
        $user1 = User::create([
            'name' => 'User One',
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'username' => 'user2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0
        ]);

        $user3 = User::create([
            'name' => 'User Three',
            'username' => 'user3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0
        ]);

        // User 1 deposit
        $this->actingAs($user1);
        $this->post('/deposit', [
            'jumlah' => 10000,
            'metode' => 'BCA',
            'no_pembayaran' => 'U1001'
        ]);

        // User 2 deposit
        $this->actingAs($user2);
        $this->post('/deposit', [
            'jumlah' => 20000,
            'metode' => 'Mandiri',
            'no_pembayaran' => 'U2001'
        ]);

        // User 3 deposit
        $this->actingAs($user3);
        $this->post('/deposit', [
            'jumlah' => 30000,
            'metode' => 'BNI',
            'no_pembayaran' => 'U3001'
        ]);

        // Verify setiap user memiliki deposit yang benar
        $this->assertDatabaseHas('deposits', [
            'username' => 'user1',
            'jumlah' => 10000
        ]);

        $this->assertDatabaseHas('deposits', [
            'username' => 'user2',
            'jumlah' => 20000
        ]);

        $this->assertDatabaseHas('deposits', [
            'username' => 'user3',
            'jumlah' => 30000
        ]);

        // Verify total deposits
        $this->assertDatabaseCount('deposits', 3);
    }
}
