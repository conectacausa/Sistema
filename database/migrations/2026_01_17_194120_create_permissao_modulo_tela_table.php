<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permissao_modulo_tela', function (Blueprint $table) {
            $table->id();

            $table->foreignId('permissao_id')->constrained('permissoes')->cascadeOnDelete();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->foreignId('tela_id')->constrained('telas')->cascadeOnDelete();

            $table->boolean('pode_ler')->default(true);
            $table->boolean('pode_escrever')->default(false);
            $table->boolean('pode_deletar')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['permissao_id', 'modulo_id', 'tela_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissao_modulo_tela');
    }
};
