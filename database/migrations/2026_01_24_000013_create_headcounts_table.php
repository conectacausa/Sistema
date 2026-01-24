<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('headcounts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('filial_id');
            $table->unsignedBigInteger('setor_id');
            $table->unsignedBigInteger('cargo_id');

            $table->date('data_liberacao');
            $table->integer('quantidade');

            $table->timestamps();
            $table->softDeletes();

            /**
             * Foreign keys
             */
            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas');

            $table->foreign('filial_id')
                ->references('id')
                ->on('filiais');

            $table->foreign('setor_id')
                ->references('id')
                ->on('setores');

            $table->foreign('cargo_id')
                ->references('id')
                ->on('cargos');

            /**
             * Índices (performance + filtros futuros)
             */
            $table->index('empresa_id');
            $table->index(['empresa_id', 'filial_id']);
            $table->index(['empresa_id', 'filial_id', 'setor_id']);
            $table->index(['empresa_id', 'cargo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('headcounts');
    }
};
