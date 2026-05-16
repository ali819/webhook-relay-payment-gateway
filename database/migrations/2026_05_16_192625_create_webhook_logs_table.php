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
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('provider', ['midtrans', 'xendit']);
            $table->string('event_type')->nullable();
            $table->string('custom_field1')->nullable();
            $table->json('payload');
            $table->integer('response_code')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->enum('status', ['success', 'failed', 'invalid_signature', 'domain_not_found']);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_logs');
    }
};
