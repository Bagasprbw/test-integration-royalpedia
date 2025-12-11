<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * IT-01: Register User → Login
 * Feature Test (Web Route + DB + Session)
 * 
 * Tujuan: Memastikan user yang selesai registrasi bisa login 
 * menggunakan kredensial yang sama
 * 
 * Langkah: Register user → data tersimpan di DB → login → redirect ke landing page
 */
class IT01RegisterUserLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user dapat melakukan registrasi dan data tersimpan di database
     */
    public function test_user_can_register_and_data_saved_to_database(): void
    {
        // Data registrasi user baru
        $userData = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'whatsapp' => '081234567890'
        ];

        // Simulasi POST request ke route register
        $response = $this->post('/register', $userData);

        // Assert: Response redirect (biasanya ke home atau dashboard)
        $response->assertStatus(302);

        // Assert: User tersimpan di database
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'testuser@example.com',
            'whatsapp' => '081234567890'
        ]);

        // Assert: Password ter-hash dengan benar
        $user = User::where('username', 'testuser')->first();
        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('password123', $user->password));

        // Assert: Role default adalah Member
        $this->assertEquals('Member', $user->role);

        // Assert: Balance default adalah 0
        $this->assertEquals(0, $user->balance);
    }

    /**
     * Test user yang sudah registrasi dapat login dengan kredensial yang sama
     */
    public function test_registered_user_can_login_with_same_credentials(): void
    {
        // Step 1: Buat user melalui registrasi
        $userData = [
            'name' => 'Test User Login',
            'username' => 'testuserlogin',
            'email' => 'testuserlogin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'whatsapp' => '081234567890'
        ];

        // Registrasi user
        $registerResponse = $this->post('/register', $userData);
        $registerResponse->assertStatus(302);

        // Verify user exists in database
        $this->assertDatabaseHas('users', [
            'username' => 'testuserlogin',
            'email' => 'testuserlogin@example.com'
        ]);

        // Step 2: Logout (jika auto-login setelah register)
        $this->post('/logout');

        // Step 3: Login menggunakan kredensial yang sama
        $loginData = [
            'email' => 'testuserlogin@example.com',
            'password' => 'password123'
        ];

        $loginResponse = $this->post('/login', $loginData);

        // Assert: Login berhasil dan redirect
        $loginResponse->assertStatus(302);

        // Assert: User ter-autentikasi
        $this->assertAuthenticated();

        // Assert: User yang login adalah user yang benar
        $this->assertEquals('testuserlogin', auth()->user()->username);
    }

    /**
     * Test full flow: Register → Login → Redirect ke Landing Page
     */
    public function test_complete_register_login_flow_redirects_to_landing_page(): void
    {
        // Step 1: Register user baru
        $userData = [
            'name' => 'Complete Flow User',
            'username' => 'completeuser',
            'email' => 'completeuser@example.com',
            'password' => 'securepass123',
            'password_confirmation' => 'securepass123',
            'whatsapp' => '082345678901'
        ];

        $this->post('/register', $userData);

        // Verify registration
        $this->assertDatabaseHas('users', [
            'username' => 'completeuser',
            'email' => 'completeuser@example.com'
        ]);

        // Step 2: Logout
        $this->post('/logout');
        $this->assertGuest();

        // Step 3: Login
        $loginResponse = $this->post('/login', [
            'email' => 'completeuser@example.com',
            'password' => 'securepass123'
        ]);

        // Assert: Redirect ke landing page (home/dashboard)
        $loginResponse->assertRedirect('/');

        // Assert: User authenticated
        $this->assertAuthenticated();

        // Step 4: Akses landing page
        $landingResponse = $this->get('/');
        $landingResponse->assertStatus(200);
    }

    /**
     * Test login gagal dengan kredensial yang salah
     */
    public function test_login_fails_with_wrong_credentials(): void
    {
        // Buat user terlebih dahulu
        User::create([
            'name' => 'Existing User',
            'username' => 'existinguser',
            'email' => 'existing@example.com',
            'password' => Hash::make('correctpassword'),
            'role' => 'Member',
            'balance' => 0
        ]);

        // Coba login dengan password salah
        $loginResponse = $this->post('/login', [
            'email' => 'existing@example.com',
            'password' => 'wrongpassword'
        ]);

        // Assert: Login gagal
        $this->assertGuest();

        // Assert: Redirect kembali dengan error
        $loginResponse->assertSessionHasErrors();
    }

    /**
     * Test registrasi gagal jika username sudah ada
     */
    public function test_registration_fails_with_duplicate_username(): void
    {
        // Buat user pertama
        User::create([
            'name' => 'First User',
            'username' => 'duplicateuser',
            'email' => 'first@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0
        ]);

        // Coba registrasi dengan username yang sama
        $response = $this->post('/register', [
            'name' => 'Second User',
            'username' => 'duplicateuser', // duplicate
            'email' => 'second@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // Assert: Validation error
        $response->assertSessionHasErrors('username');
    }

    /**
     * Test registrasi gagal jika email sudah ada
     */
    public function test_registration_fails_with_duplicate_email(): void
    {
        // Buat user pertama
        User::create([
            'name' => 'First User',
            'username' => 'firstuser',
            'email' => 'duplicate@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 0
        ]);

        // Coba registrasi dengan email yang sama
        $response = $this->post('/register', [
            'name' => 'Second User',
            'username' => 'seconduser',
            'email' => 'duplicate@example.com', // duplicate
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        // Assert: Validation error
        $response->assertSessionHasErrors('email');
    }
}
