<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vinculo_usuario_lotacao', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('usuario_id')->index();
            $table->unsignedBigInteger('filial_id')->index();
            $table->unsignedBigInteger('setor_id')->index();

            // opcional mas útil: ativar/desativar vínculo sem deletar
            $table->boolean('ativo')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('empresa_id')
                ->references('id')->on('empresas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('usuario_id')
                ->references('id')->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('filial_id')
                ->references('id')->on('filiais')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('setor_id')
                ->references('id')->on('setores')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // evita duplicidade do mesmo vínculo
            $table->unique(['empresa_id', 'usuario_id', 'filial_id', 'setor_id'], 'uniq_vinc_user_lot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculo_usuario_lotacao');
    }
};
