<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telas', function (Blueprint $table) {
            $table->id();
            $table->string('nome_tela', 180);
            $table->date('data_release')->nullable();
            $table->string('icone', 120)->nullable();

            $table->foreignId('modulo_id')->constrained('modulos');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['modulo_id', 'nome_tela']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telas');
    }
};
