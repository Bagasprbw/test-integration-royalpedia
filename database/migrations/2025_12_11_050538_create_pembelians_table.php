<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();
            $table->string('username');
            $table->string('layanan');
            $table->integer('harga');
            $table->string('user_id');  // email Netflix / game ID
            $table->string('zone')->nullable();
            $table->enum('status', ['Pending', 'Success', 'Failed', 'Batal'])->default('Pending');
            $table->string('provider_order_id')->nullable();
            $table->string('tipe_transaksi')->default('game');
            $table->unsignedBigInteger('voucher_id')->nullable();
            $table->integer('discount_amount')->default(0);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelians');
    }
};
