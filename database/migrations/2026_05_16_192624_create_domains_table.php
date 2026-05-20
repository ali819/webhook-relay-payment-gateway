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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique(); // bisa 1 domain 2 url beda (update di file migrasi: change_domains_unique_to_composite)
            $table->enum('provider', ['midtrans', 'xendit']);
            $table->string('target_url');
            $table->string('secret_key');
            $table->boolean('is_active')->default(true);
            // ada kolom notes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
