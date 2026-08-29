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
        Schema::create('riwayat_retirement', function (Blueprint $table) {
            $table->id();
            $table->timestamp('created_at')->useCurrent();
            $table->string('petugas', 100)->nullable();
            $table->string('kategori_db', 50)->nullable(); // 'INTERNAL', 'EXTERNAL'
            $table->string('nomor_asset', 50)->nullable()->index();
            $table->string('deskripsi_asset', 255)->nullable();
            $table->integer('qty_disposal')->nullable();
            $table->decimal('nbv_disposal', 18, 2)->nullable();
            $table->string('dokumen_sap', 100)->nullable();
            $table->text('catatan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_retirement');
    }
};
