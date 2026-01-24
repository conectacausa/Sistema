<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permissao_modulo_tela', function (Blueprint $table) {
            if (!Schema::hasColumn('permissao_modulo_tela', 'cadastro')) {
                $table->boolean('cadastro')->default(false)->index()->after('ativo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permissao_modulo_tela', function (Blueprint $table) {
            if (Schema::hasColumn('permissao_modulo_tela', 'cadastro')) {
                $table->dropColumn('cadastro');
            }
        });
    }
};
