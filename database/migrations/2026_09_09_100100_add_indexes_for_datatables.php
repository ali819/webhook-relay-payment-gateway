<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Index untuk server-side DataTables: setiap kombinasi filter + sort
     * (selalu berakhir di created_at / id) harus bisa pakai index,
     * supaya paging tetap cepat saat log sudah jutaan baris.
     */
    public function up(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->index('created_at', 'wl_created_at_idx');
            $table->index(['domain_id', 'id'], 'wl_domain_id_idx');
            $table->index(['status', 'id'], 'wl_status_id_idx');
            $table->index(['provider', 'id'], 'wl_provider_id_idx');
            $table->index(['domain_id', 'status', 'id'], 'wl_domain_status_id_idx');
            $table->index('custom_field1', 'wl_custom_field1_idx');
            $table->index('event_type', 'wl_event_type_idx');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->index('name', 'dom_name_idx');
            $table->index(['is_active', 'id'], 'dom_active_id_idx');
            $table->index(['provider', 'id'], 'dom_provider_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            $table->dropIndex('wl_created_at_idx');
            $table->dropIndex('wl_domain_id_idx');
            $table->dropIndex('wl_status_id_idx');
            $table->dropIndex('wl_provider_id_idx');
            $table->dropIndex('wl_domain_status_id_idx');
            $table->dropIndex('wl_custom_field1_idx');
            $table->dropIndex('wl_event_type_idx');
        });

        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex('dom_name_idx');
            $table->dropIndex('dom_active_id_idx');
            $table->dropIndex('dom_provider_id_idx');
        });
    }
};
