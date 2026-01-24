<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vinculo_cargo_lotacao', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('cargo_id')->index();
            $table->unsignedBigInteger('filial_id')->index();

            // situação do vínculo
            $table->boolean('ativo')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            // FKs
            $table->foreign('empresa_id')
                ->references('id')->on('empresas')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cargo_id')
                ->references('id')->on('cargos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('filial_id')
                ->references('id')->on('filiais')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // evita duplicar o mesmo cargo na mesma lotação
            $table->unique(
                ['empresa_id', 'cargo_id', 'filial_id'],
                'uniq_vinc_cargo_lotacao'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculo_cargo_lotacao');
    }
};
