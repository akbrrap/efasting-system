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
        Schema::create('master_asset_external', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_asset', 50)->unique();
            $table->string('deskripsi_asset', 255);
            $table->string('serial_number', 100)->nullable();
            $table->string('cost_center', 50)->nullable();
            $table->integer('qty_buku');
            $table->date('cap_date')->nullable();
            $table->decimal('nilai_perolehan', 18, 2)->nullable();
            $table->decimal('akumulasi_depresiasi', 18, 2)->nullable();
            $table->decimal('nbv', 18, 2)->nullable();
            $table->string('allocation', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_asset_external');
    }
};
