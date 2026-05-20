<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            // Hapus unique constraint lama di kolom domain
            $table->dropUnique(['domain']);
            // Tambah composite unique: domain + provider
            $table->unique(['domain', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropUnique(['domain', 'provider']);
            $table->unique(['domain']);
        });
    }
};
