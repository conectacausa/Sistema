<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permissoes', function (Blueprint $table) {
            $table->id();

            $table->string('nome_grupo', 180);
            $table->text('observacoes')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('salarios')->default(false);

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'nome_grupo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissoes');
    }
};
