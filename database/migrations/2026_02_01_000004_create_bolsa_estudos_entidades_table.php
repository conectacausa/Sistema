<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bolsa_estudos_entidades', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas');

            $table->string('nome', 255);
            $table->string('cnpj', 20)->nullable(); // permitir null e evoluir depois

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id']);
            $table->index(['cnpj']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bolsa_estudos_entidades');
    }
};
