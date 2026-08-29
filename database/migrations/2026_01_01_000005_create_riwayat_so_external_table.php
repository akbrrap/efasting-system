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
        Schema::create('riwayat_so_external', function (Blueprint $table) {
            $table->id();
            $table->timestamp('time_stamps')->useCurrent();
            $table->string('user', 100)->nullable();
            $table->string('nomor_asset', 50)->nullable()->index();
            $table->string('deskripsi_asset', 255)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('aktual_loc', 255)->nullable();
            $table->integer('book_qty')->nullable();
            $table->integer('physic_qty')->nullable();
            $table->integer('variance')->nullable();
            $table->string('kelengkapan_tagging', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('kondisi', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->text('foto_fisik')->nullable();
            $table->text('foto_tagging')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_so_external');
    }
};
