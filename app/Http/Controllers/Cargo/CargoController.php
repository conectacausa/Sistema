<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('setores', function (Blueprint $table) {
            $table->id();

            $table->string('nome', 180);
            $table->text('descricao')->nullable();

            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('filial_id')->index();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('filial_id')
                ->references('id')
                ->on('filiais')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index(['empresa_id', 'filial_id', 'nome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setores');
    }
};
