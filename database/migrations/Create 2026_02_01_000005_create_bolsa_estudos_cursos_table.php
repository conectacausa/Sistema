<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bolsa_estudos_cursos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->foreignId('entidade_id')
                ->constrained('bolsa_estudos_entidades')
                ->restrictOnDelete();

            $table->string('nome', 255);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['entidade_id', 'nome']);
            $table->index(['empresa_id', 'entidade_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bolsa_estudos_cursos');
    }
};
