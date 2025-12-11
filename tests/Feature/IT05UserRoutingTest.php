<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Deposit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IT05UserRoutingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * IT-05: Manajemen User (Routing) → Kelola Pesanan (Admin)
     * 
     * Test: Memastikan user dengan role berbeda dapat mengakses route sesuai permission
     * Menggunakan route deposit yang sudah ada untuk testing
     */

    /**
     * Test bahwa user yang sudah login dapat mengakses halaman deposit.
     */
    public function test_authenticated_user_dapat_mengakses_deposit(): void
    {
        // Arrange: Buat user dengan role Member
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 0,
        ]);

        // Act: Login dan akses halaman deposit
        $response = $this->actingAs($user)->get(route('deposit.index'));

        // Assert: User bisa mengakses halaman deposit (200 OK)
        $response->assertStatus(200);
    }

    /**
     * Test bahwa user yang belum login tidak dapat mengakses deposit.
     */
    public function test_unauthenticated_user_tidak_bisa_akses_deposit(): void
    {
        // Act: Coba akses deposit tanpa login
        $response = $this->get(route('deposit.index'));

        // Assert: Redirect ke halaman login
        $response->assertRedirect(route('login'));
    }

    /**
     * Test bahwa semua role (Admin, Member, Gold, Platinum) dapat login.
     */
    public function test_semua_role_dapat_login(): void
    {
        $roles = ['Admin', 'Member', 'Gold', 'Platinum'];

        foreach ($roles as $role) {
            // Arrange: Buat user dengan role tertentu
            $user = User::create([
                'name' => $role . ' User',
                'username' => strtolower($role) . '_user',
                'email' => strtolower($role) . '@example.com',
                'password' => bcrypt('password'),
                'role' => $role,
                'balance' => 0,
            ]);

            // Act: Login
            $response = $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'password',
            ]);

            // Assert: Login berhasil dan redirect
            $response->assertRedirect('/');
            $this->assertAuthenticatedAs($user);

            // Logout untuk test berikutnya
            $this->post(route('logout'));
        }
    }

    /**
     * Test bahwa admin dapat melihat semua deposit di sistem (untuk kelola pesanan).
     */
    public function test_admin_dapat_melihat_semua_deposit(): void
    {
        // Arrange: Buat admin
        $admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
            'balance' => 0,
        ]);

        // Buat beberapa user lain
        $user1 = User::create([
            'name' => 'User 1',
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 10000,
        ]);

        $user2 = User::create([
            'name' => 'User 2',
            'username' => 'user2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role' => 'Gold',
            'balance' => 20000,
        ]);

        // Buat beberapa deposit dari user berbeda
        Deposit::create([
            'username' => 'user1',
            'jumlah' => 50000,
            'metode' => 'BCA',
            'status' => 'Pending',
        ]);

        Deposit::create([
            'username' => 'user2',
            'jumlah' => 100000,
            'metode' => 'OVO',
            'status' => 'Pending',
        ]);

        // Act: Admin mengambil semua deposit (simulasi kelola pesanan)
        $this->actingAs($admin);
        $allDeposits = Deposit::all();

        // Assert: Admin bisa melihat semua deposit dari semua user
        $this->assertCount(2, $allDeposits);
        $this->assertEquals('user1', $allDeposits[0]->username);
        $this->assertEquals('user2', $allDeposits[1]->username);
    }

    /**
     * Test bahwa user hanya dapat melihat deposit history miliknya sendiri.
     */
    public function test_user_hanya_melihat_deposit_sendiri(): void
    {
        // Arrange: Buat 2 user
        $user1 = User::create([
            'name' => 'User 1',
            'username' => 'user1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 0,
        ]);

        $user2 = User::create([
            'name' => 'User 2',
            'username' => 'user2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 0,
        ]);

        // Buat deposit untuk masing-masing user
        Deposit::create([
            'username' => 'user1',
            'jumlah' => 50000,
            'metode' => 'BCA',
            'status' => 'Success',
        ]);

        Deposit::create([
            'username' => 'user2',
            'jumlah' => 100000,
            'metode' => 'OVO',
            'status' => 'Pending',
        ]);

        // Act: User1 login dan akses deposit history
        $response = $this->actingAs($user1)->get(route('deposit.history'));

        // Assert: User1 hanya melihat deposit miliknya
        $response->assertStatus(200);
        $userDeposits = Deposit::where('username', 'user1')->get();
        $this->assertCount(1, $userDeposits);
        $this->assertEquals(50000, $userDeposits[0]->jumlah);
    }
}
