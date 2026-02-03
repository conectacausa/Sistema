<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bolsa_estudos_documentos')) return;

        Schema::table('bolsa_estudos_documentos', function (Blueprint $table) {
            if (!Schema::hasColumn('bolsa_estudos_documentos', 'expira_em')) {
                $table->date('expira_em')->nullable()->after('arquivo_path');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bolsa_estudos_documentos')) return;

        Schema::table('bolsa_estudos_documentos', function (Blueprint $table) {
            if (Schema::hasColumn('bolsa_estudos_documentos', 'expira_em')) {
                $table->dropColumn('expira_em');
            }
        });
    }
};
