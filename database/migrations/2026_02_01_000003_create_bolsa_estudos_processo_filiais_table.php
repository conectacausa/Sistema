<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bolsa_estudos_processo_filiais', function (Blueprint $table) {
            $table->id();

            $table->foreignId('processo_id')
                ->constrained('bolsa_estudos_processos')
                ->cascadeOnDelete();

            $table->foreignId('filial_id')
                ->constrained('filiais')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['processo_id', 'filial_id']);
            $table->index(['filial_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bolsa_estudos_processo_filiais');
    }
};
