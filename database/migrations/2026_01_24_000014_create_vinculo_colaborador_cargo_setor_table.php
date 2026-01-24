<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vinculo_colaborador_cargo_setor', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('colaborador_id');
            $table->unsignedBigInteger('cargo_id');
            $table->unsignedBigInteger('setor_id');

            $table->date('data_inicio');
            $table->date('data_fim')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // FKs
            $table->foreign('colaborador_id')->references('id')->on('colaboradores');
            $table->foreign('cargo_id')->references('id')->on('cargos');
            $table->foreign('setor_id')->references('id')->on('setores');

            // Índices (consulta do quadro atual)
            $table->index(['cargo_id', 'setor_id']);
            $table->index(['setor_id', 'cargo_id']);
            $table->index('data_fim');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculo_colaborador_cargo_setor');
    }
};
