<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permissao_modulo_tela', function (Blueprint $table) {
            if (!Schema::hasColumn('permissao_modulo_tela', 'editar')) {
                $table->boolean('editar')->default(false)->index()->after('cadastro');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permissao_modulo_tela', function (Blueprint $table) {
            if (Schema::hasColumn('permissao_modulo_tela', 'editar')) {
                $table->dropColumn('editar');
            }
        });
    }
};
