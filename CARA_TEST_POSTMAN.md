# Cara Test Transaction API dengan Postman

Panduan step-by-step untuk test Transaction API (Transaksi Game & Netflix).

---

## 📋 Persiapan

### Langkah 1: Buka Terminal/CMD

1. Buka Terminal atau Command Prompt (CMD)
2. Masuk ke folder project:
   ```bash
   cd C:\laragon\www\test-integration-royalpedia
   ```

### Langkah 2: Jalankan Server Laravel

Di terminal, ketik:
```bash
php artisan serve
```

✅ **Tunggu sampai muncul:**
```
INFO  Server running on [http://127.0.0.1:8000].
```

⚠️ **PENTING:** Jangan tutup terminal ini! Biarkan tetap running.

---

## 🎮 TEST 1: Transaksi Game (Mobile Legends)

### Langkah 3: Buka Postman

1. Buka aplikasi **Postman**
2. Klik tombol **"New"** (pojok kiri atas)
3. Pilih **"HTTP Request"**

### Langkah 4: Setup Method & URL

1. **Method:** Pilih `POST` dari dropdown (default biasanya GET)
2. **URL:** Ketik:
   ```
   http://127.0.0.1:8000/api/transaction
   ```

### Langkah 5: Setup Headers

1. Klik tab **"Headers"** (di bawah URL bar)
2. Tambahkan 2 headers ini:

| Key | Value |
|-----|-------|
| `Content-Type` | `application/json` |
| `Accept` | `application/json` |

**Cara menambahkan:**
- Klik di kolom "Key", ketik: `Content-Type`
- Klik di kolom "Value", ketik: `application/json`
- Ulangi untuk `Accept`

### Langkah 6: Setup Body

1. Klik tab **"Body"** (di bawah URL bar)
2. Pilih **"raw"** (radio button)
3. Di dropdown sebelah kanan, pilih **"JSON"** (bukan "Text")
4. Copy-paste JSON ini ke kotak text area:

```json
{
    "order_id": "TRX-GAME-001",
    "username": "testuser",
    "layanan": "Mobile Legends Diamond 100",
    "harga": 50000,
    "user_id": "1234567890",
    "zone": "9876",
    "tipe_transaksi": "game"
}
```

### Langkah 7: Kirim Request

1. Klik tombol **"Send"** (biru, pojok kanan)
2. Tunggu beberapa detik

### Langkah 8: Lihat Response

Di bagian bawah, cek:

**Status:** Harus `201 Created` (warna hijau)

**Response Body:** Harus muncul seperti ini:
```json
{
    "success": true,
    "message": "Transaksi berhasil dibuat",
    "data": {
        "id": 1,
        "order_id": "TRX-GAME-001",
        "username": "testuser",
        "layanan": "Mobile Legends Diamond 100",
        "harga": 50000,
        "status": "Pending",
        "created_at": "2025-12-11T09:10:00.000000Z"
    }
}
```

✅ **Kalau sudah seperti ini, berarti BERHASIL!**

---

## 🎬 TEST 2: Transaksi Netflix

### Langkah 9: Buat Request Baru

**Cara cepat:**
1. Di request yang sama (TEST 1), langsung ganti **Body** saja
2. Atau buat request baru: Klik "New" → "HTTP Request"

### Langkah 10: Setup (sama seperti TEST 1)

**Method:** `POST`
**URL:** `http://127.0.0.1:8000/api/transaction`

**Headers:** (sama seperti TEST 1)
- `Content-Type: application/json`
- `Accept: application/json`

### Langkah 11: Ganti Body

Hapus body yang lama, copy-paste yang baru:

```json
{
    "order_id": "TRX-NETFLIX-001",
    "username": "netflixuser",
    "layanan": "Netflix Premium 1 Bulan",
    "harga": 186000,
    "user_id": "netflix@example.com",
    "tipe_transaksi": "subscription"
}
```

⚠️ **PENTING:** `order_id` harus berbeda dari TEST 1!

### Langkah 12: Kirim & Cek Response

1. Klik **"Send"**
2. Status harus `201 Created`
3. Response harus ada `"success": true`

✅ **BERHASIL!**

---

## 🔍 Verifikasi Database

Untuk memastikan data benar-benar masuk ke database:

### Langkah 13: Buka Terminal Baru

1. Buka terminal/CMD **baru** (jangan tutup yang serve!)
2. Masuk ke folder project lagi:
   ```bash
   cd C:\laragon\www\test-integration-royalpedia
   ```

### Langkah 14: Buka Tinker

```bash
php artisan tinker
```

Tunggu sampai muncul prompt `>`

### Langkah 15: Cek Transaksi

Copy-paste perintah ini satu per satu:

**Lihat semua transaksi:**
```php
App\Models\Pembelian::all();
```
Tekan Enter. Harus muncul 2 transaksi.

**Lihat transaksi game:**
```php
App\Models\Pembelian::where('username', 'testuser')->first();
```
Tekan Enter. Harus muncul data Mobile Legends.

**Lihat transaksi Netflix:**
```php
App\Models\Pembelian::where('username', 'netflixuser')->first();
```
Tekan Enter. Harus muncul data Netflix.

**Hitung total transaksi:**
```php
App\Models\Pembelian::count();
```
Tekan Enter. Harus muncul angka `2`.

### Langkah 16: Keluar dari Tinker

```php
exit
```
