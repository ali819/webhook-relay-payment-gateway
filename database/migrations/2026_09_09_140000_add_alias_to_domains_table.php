<?php

use App\Models\Domain;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->char('alias', 3)->nullable()->after('domain');
        });

        // Isi alias untuk domain yang sudah ada sebelum kolomnya dijadikan unique.
        foreach (DB::table('domains')->pluck('id') as $id) {
            DB::table('domains')->where('id', $id)->update(['alias' => Domain::generateAlias()]);
        }

        Schema::table('domains', function (Blueprint $table) {
            $table->unique('alias');
        });
    }

    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropUnique(['alias']);
            $table->dropColumn('alias');
        });
    }
};
