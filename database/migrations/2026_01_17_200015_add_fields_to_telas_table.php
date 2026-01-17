<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('telas', function (Blueprint $table) {
            // campos principais
            $table->string('nome_tela', 180)->nullable()->after('id');
            $table->date('data_release')->nullable()->after('nome_tela');
            $table->string('icone', 120)->nullable()->after('data_release');

            // relacionamento com módulos
            $table->unsignedBigInteger('modulo_id')->nullable()->after('icone');
        });

        // índices e FK
        Schema::table('telas', function (Blueprint $table) {
            $table->index('modulo_id');
            $table->foreign('modulo_id')->references('id')->on('modulos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('telas', function (Blueprint $table) {
            $table->dropForeign(['modulo_id']);
            $table->dropIndex(['modulo_id']);

            $table->dropColumn(['modulo_id', 'icone', 'data_release', 'nome_tela']);
        });
    }
};
