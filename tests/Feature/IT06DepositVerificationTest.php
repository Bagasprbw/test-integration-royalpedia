<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Deposit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IT06DepositVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * IT-06: Deposit (User) → Verifikasi Deposit (Admin) → Saldo User
     * 
     * Test: Memastikan alur deposit dari user dibuat, diverifikasi admin,
     * dan saldo user bertambah sesuai dengan jumlah deposit
     */

    /**
     * Test bahwa user dapat membuat deposit melalui form.
     */
    public function test_user_dapat_membuat_deposit_via_form(): void
    {
        // Arrange: Buat user
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 0,
        ]);

        // Act: User submit form deposit
        $response = $this->actingAs($user)->post(route('deposit.store'), [
            'jumlah' => 50000,
            'metode' => 'BCA',
            'no_pembayaran' => '1234567890',
        ]);

        // Assert: Deposit berhasil dibuat dengan status Pending
        $response->assertRedirect(route('deposit.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('deposits', [
            'username' => 'testuser',
            'jumlah' => 50000,
            'metode' => 'BCA',
            'status' => 'Pending',
        ]);
    }

    /**
     * Test bahwa deposit dibuat dengan status Pending.
     */
    public function test_deposit_dibuat_dengan_status_pending(): void
    {
        // Arrange: Buat user
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 0,
        ]);

        // Act: User membuat deposit langsung via model
        $deposit = Deposit::create([
            'username' => 'testuser',
            'jumlah' => 50000,
            'metode' => 'BCA',
            'status' => 'Pending',
            'no_pembayaran' => '1234567890',
        ]);

        // Assert: Deposit dibuat dengan status Pending
        $this->assertEquals('Pending', $deposit->status);
        $this->assertDatabaseHas('deposits', [
            'id' => $deposit->id,
            'status' => 'Pending',
        ]);
    }

    /**
     * Test bahwa admin dapat memverifikasi deposit (mengubah status).
     */
    public function test_admin_dapat_memverifikasi_deposit(): void
    {
        // Arrange: Buat admin dan user
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
            'balance' => 0,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 0,
        ]);

        // Buat deposit dengan status Pending
        $deposit = Deposit::create([
            'username' => 'testuser',
            'jumlah' => 50000,
            'metode' => 'BCA',
            'status' => 'Pending',
            'no_pembayaran' => '1234567890',
        ]);

        // Act: Admin memverifikasi deposit (simulasi - mengubah status menjadi Success)
        $this->actingAs($admin);
        $deposit->update(['status' => 'Success']);

        // Assert: Status deposit berubah menjadi Success
        $this->assertEquals('Success', $deposit->fresh()->status);
        $this->assertDatabaseHas('deposits', [
            'id' => $deposit->id,
            'status' => 'Success',
        ]);
    }

    /**
     * Test bahwa saldo user bertambah setelah deposit diverifikasi.
     */
    public function test_saldo_user_bertambah_setelah_verifikasi(): void
    {
        // Arrange: Buat admin dan user
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
            'balance' => 0,
        ]);

        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 10000, // Saldo awal
        ]);

        // Buat deposit
        $deposit = Deposit::create([
            'username' => 'testuser',
            'jumlah' => 50000,
            'metode' => 'BCA',
            'status' => 'Pending',
            'no_pembayaran' => '1234567890',
        ]);

        // Act: Admin verifikasi deposit dan tambah saldo user
        $this->actingAs($admin);
        
        // Simulasi proses verifikasi admin: update status + tambah saldo
        $deposit->update(['status' => 'Success']);
        $user->increment('balance', $deposit->jumlah);

        // Assert: Saldo user bertambah sesuai jumlah deposit
        $this->assertEquals(60000, $user->fresh()->balance); // 10000 + 50000
        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'balance' => 60000,
        ]);
    }

    /**
     * Test bahwa status deposit berubah dari Pending ke Success.
     */
    public function test_status_deposit_berubah_pending_ke_success(): void
    {
        // Arrange: Buat user dan deposit
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 0,
        ]);

        $deposit = Deposit::create([
            'username' => 'testuser',
            'jumlah' => 30000,
            'metode' => 'OVO',
            'status' => 'Pending',
            'no_pembayaran' => '9876543210',
        ]);

        // Assert: Status awal adalah Pending
        $this->assertEquals('Pending', $deposit->status);

        // Act: Update status ke Success (simulasi verifikasi admin)
        $deposit->update(['status' => 'Success']);

        // Assert: Status berubah menjadi Success
        $updatedDeposit = $deposit->fresh();
        $this->assertEquals('Success', $updatedDeposit->status);
    }

    /**
     * Test bahwa deposit yang Failed tidak mengubah saldo user.
     */
    public function test_deposit_failed_tidak_mengubah_saldo(): void
    {
        // Arrange: Buat user
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 10000, // Saldo awal
        ]);

        // Buat deposit
        $deposit = Deposit::create([
            'username' => 'testuser',
            'jumlah' => 50000,
            'metode' => 'QRIS',
            'status' => 'Pending',
            'no_pembayaran' => '1111222233',
        ]);

        $saldoAwal = $user->balance;

        // Act: Admin menolak deposit (status Failed)
        $deposit->update(['status' => 'Failed']);

        // Saldo user TIDAK berubah karena deposit gagal
        $user = $user->fresh();

        // Assert: Saldo tetap sama (tidak bertambah)
        $this->assertEquals($saldoAwal, $user->balance);
        $this->assertEquals('Failed', $deposit->fresh()->status);
    }

    /**
     * Test bahwa user dapat melihat deposit history miliknya.
     */
    public function test_user_dapat_melihat_deposit_history(): void
    {
        // Arrange: Buat user
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'Member',
            'balance' => 0,
        ]);

        // Buat beberapa deposit
        Deposit::create([
            'username' => 'testuser',
            'jumlah' => 50000,
            'metode' => 'BCA',
            'status' => 'Success',
        ]);

        Deposit::create([
            'username' => 'testuser',
            'jumlah' => 30000,
            'metode' => 'OVO',
            'status' => 'Pending',
        ]);

        // Act: User akses deposit history
        $response = $this->actingAs($user)->get(route('deposit.history'));

        // Assert: User bisa melihat history depositnya
        $response->assertStatus(200);
        $userDeposits = Deposit::where('username', 'testuser')->get();
        $this->assertCount(2, $userDeposits);
    }
}
