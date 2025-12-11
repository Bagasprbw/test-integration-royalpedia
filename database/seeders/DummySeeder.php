<?php

namespace Database\Seeders;   // <── WAJIB ADA

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummySeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        DB::table('users')->insert([
            'name' => 'Admin Sistem',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'role' => 'Admin',
            'balance' => 0,
            'whatsapp' => '0800000001'
        ]);

        // User Member
        DB::table('users')->insert([
            'name' => 'User Biasa',
            'username' => 'member1',
            'email' => 'member1@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'Member',
            'balance' => 50000,
            'whatsapp' => '08123456789'
        ]);

        // Kategori
        DB::table('kategoris')->insert([
            ['nama' => 'Mobile Legends', 'kode' => 'mlbb', 'server_id' => 1, 'status' => 'active'],
            ['nama' => 'Netflix Premium', 'kode' => 'netflix', 'server_id' => 0, 'status' => 'active'],
        ]);

        // Layanan
        DB::table('layanans')->insert([
            [
                'kategori_id' => 1,
                'layanan' => '86 Diamonds',
                'provider_id' => 'ML86',
                'provider' => 'vip',
                'harga' => 15000,
                'harga_member' => 14500,
                'harga_gold' => 14000,
                'harga_platinum' => 13500,
                'status' => 'available'
            ],
            [
                'kategori_id' => 2,
                'layanan' => 'Netflix 1 Bulan',
                'provider_id' => 'NF1',
                'provider' => 'vip',
                'harga' => 45000,
                'harga_member' => 43000,
                'harga_gold' => 42000,
                'harga_platinum' => 41000,
                'status' => 'available'
            ],
        ]);

        // Voucher
        DB::table('promos')->insert([
            [
                'kode' => 'DISKON10',
                'potongan' => 10,
                'tipe_potongan' => 'percent',
                'kuota' => 100,
                'expired_at' => now()->addDays(30),
                'status' => 'active'
            ]
        ]);
    }
}
