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

        // Vamos criar o unique depois (quando tivermos dados e certeza do padrão)
        // Se quiser, a gente cria uma migration futura:
        // unique(['modulo_id','slug']) quando modulo_id estiver sempre preenchido.
    }

    public function down(): void
    {
        Schema::table('telas', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
