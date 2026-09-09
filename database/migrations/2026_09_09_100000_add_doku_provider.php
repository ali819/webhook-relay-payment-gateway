<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ENUM hanya ada di MySQL/MariaDB. Di SQLite (test) enum jadi CHECK
        // constraint, jadi kolomnya cukup dilonggarkan menjadi string.
        if (DB::getDriverName() !== 'mysql') {
            Schema::table('domains', fn (Blueprint $t) => $t->string('provider')->change());
            Schema::table('webhook_logs', fn (Blueprint $t) => $t->string('provider')->change());

            return;
        }

        // Tambah 'doku' ke daftar provider.
        // Di webhook_logs juga ditambah 'unknown' — RelayController sudah menulis
        // nilai itu saat provider tak terdeteksi, tapi enum lama belum memuatnya
        // (insert gagal di MySQL strict mode).
        DB::statement("ALTER TABLE domains MODIFY provider ENUM('midtrans','xendit','doku') NOT NULL");
        DB::statement("ALTER TABLE webhook_logs MODIFY provider ENUM('midtrans','xendit','doku','unknown') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE domains MODIFY provider ENUM('midtrans','xendit') NOT NULL");
        DB::statement("ALTER TABLE webhook_logs MODIFY provider ENUM('midtrans','xendit') NOT NULL");
    }
};
