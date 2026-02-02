<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('bolsa_estudos_processos')) {
            return;
        }

        Schema::table('bolsa_estudos_processos', function (Blueprint $table) {
            if (!Schema::hasColumn('bolsa_estudos_processos', 'data_base')) {
                $table->date('data_base')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('bolsa_estudos_processos')) {
            return;
        }

        Schema::table('bolsa_estudos_processos', function (Blueprint $table) {
            if (Schema::hasColumn('bolsa_estudos_processos', 'data_base')) {
                $table->dropColumn('data_base');
            }
        });
    }
};
