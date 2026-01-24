<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();

            $table->string('titulo', 255);

            // "cbo" deve ser o ID de uma CBO previamente cadastrada
            $table->foreignId('cbo_id')
                ->constrained('cbos')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // status (1 = Ativo; 0 = Inativo)
            $table->boolean('status')->default(true)->index();

            // jovem aprendiz (1 = Sim; 0 = Não)
            $table->boolean('jovem_aprendiz')->default(false)->index();

            // Descrição Cargo
            $table->text('descricao_cargo')->nullable();

            // Revisão (data)
            $table->date('revisao')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['titulo']);
            $table->index(['cbo_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};
