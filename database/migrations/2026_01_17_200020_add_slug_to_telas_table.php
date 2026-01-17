<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('telas', function (Blueprint $table) {
            $table->string('slug', 220)->nullable()->after('nome_tela');
        });

        // (Opcional, mas recomendado) índice/unique para slug por módulo
        Schema::table('telas', function (Blueprint $table) {
            $table->unique(['modulo_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('telas', function (Blueprint $table) {
            $table->dropUnique(['modulo_id', 'slug']);
            $table->dropColumn('slug');
        });
    }
};
