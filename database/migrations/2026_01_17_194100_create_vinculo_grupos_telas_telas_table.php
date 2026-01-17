<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vinculo_grupos_telas_telas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tela_id')->constrained('telas')->cascadeOnDelete();
            $table->foreignId('grupo_tela_id')->constrained('grupos_telas')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tela_id', 'grupo_tela_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculo_grupos_telas_telas');
    }
};
