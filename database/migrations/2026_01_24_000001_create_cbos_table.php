<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cbos', function (Blueprint $table) {
            $table->id();

            // Código CBO (geralmente numérico, mas mantenho como string por segurança)
            $table->string('cbo', 10)->unique();

            $table->string('titulo', 255);

            // "descrição" -> melhor prática: nome de coluna sem acento
            $table->text('descricao')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['titulo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbos');
    }
};
