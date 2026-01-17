<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('naturezas_juridicas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20);      // “código natureza”
            $table->string('descricao', 255);  // “descrição natureza”

            $table->timestamps();
            $table->softDeletes();

            $table->unique('codigo');
            $table->index('descricao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('naturezas_juridicas');
    }
};
