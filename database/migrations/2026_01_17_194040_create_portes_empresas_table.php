<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('portes_empresas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20);      // “código porte”
            $table->string('descricao', 255);  // “descrição porte”

            $table->timestamps();
            $table->softDeletes();

            $table->unique('codigo');
            $table->index('descricao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portes_empresas');
    }
};
