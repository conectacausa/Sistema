<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('bolsa_estudos_entidades') && !Schema::hasColumn('bolsa_estudos_entidades', 'aprovado')) {
            Schema::table('bolsa_estudos_entidades', function (Blueprint $table) {
                $table->boolean('aprovado')->default(true)->after('cnpj');
            });
        }

        if (Schema::hasTable('bolsa_estudos_cursos') && !Schema::hasColumn('bolsa_estudos_cursos', 'aprovado')) {
            Schema::table('bolsa_estudos_cursos', function (Blueprint $table) {
                $table->boolean('aprovado')->default(true)->after('nome');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('bolsa_estudos_entidades') && Schema::hasColumn('bolsa_estudos_entidades', 'aprovado')) {
            Schema::table('bolsa_estudos_entidades', function (Blueprint $table) {
                $table->dropColumn('aprovado');
            });
        }

        if (Schema::hasTable('bolsa_estudos_cursos') && Schema::hasColumn('bolsa_estudos_cursos', 'aprovado')) {
            Schema::table('bolsa_estudos_cursos', function (Blueprint $table) {
                $table->dropColumn('aprovado');
            });
        }
    }
};
