<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cnaes', function (Blueprint $table) {
            $table->id();

            // Mantive campos como texto para preservar zeros e formatações
            $table->string('sessao', 10)->nullable();
            $table->string('divisao', 10)->nullable();
            $table->string('grupo', 10)->nullable();
            $table->string('classe', 10)->nullable();
            $table->string('subclasse', 20)->nullable();

            // Extra útil (opcional) – ajuda MUITO no sistema
            $table->string('descricao', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['sessao', 'divisao', 'grupo', 'classe', 'subclasse']);
            $table->unique('subclasse');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cnaes');
    }
};
