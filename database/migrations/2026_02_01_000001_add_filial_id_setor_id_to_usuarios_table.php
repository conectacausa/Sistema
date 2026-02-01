<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Se quiser posicionar após permissao_id (em MySQL funciona; no Postgres pode ignorar o after)
            $table->unsignedBigInteger('filial_id')->nullable();
            $table->unsignedBigInteger('setor_id')->nullable();

            $table->index('filial_id');
            $table->index('setor_id');

            // FKs (opcional, recomendado)
            $table->foreign('filial_id')->references('id')->on('filiais')->nullOnDelete();
            $table->foreign('setor_id')->references('id')->on('setores')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // remover FKs antes das colunas
            $table->dropForeign(['filial_id']);
            $table->dropForeign(['setor_id']);

            $table->dropIndex(['filial_id']);
            $table->dropIndex(['setor_id']);

            $table->dropColumn(['filial_id', 'setor_id']);
        });
    }
};
