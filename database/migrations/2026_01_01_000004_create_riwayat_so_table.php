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
        Schema::create('riwayat_so', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp')->useCurrent();
            $table->string('user', 100)->nullable();
            $table->string('nomor_asset', 50)->nullable()->index();
            $table->string('deskripsi_asset', 255)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->integer('qty_buku')->nullable();
            $table->integer('qty_fisik')->nullable();
            $table->integer('selisih')->nullable();
            $table->string('tagging', 50)->nullable();
            $table->string('status_penggunaan', 50)->nullable();
            $table->string('kondisi', 50)->nullable();
            $table->string('lokasi', 255)->nullable();
            $table->text('link_foto_fisik')->nullable();
            $table->text('link_tagging_asset')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_so');
    }
};
