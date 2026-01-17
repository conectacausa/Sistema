<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cidades', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 160);
            $table->unsignedInteger('ibge_id')->nullable();

            $table->foreignId('estado_id')->constrained('estados');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado_id', 'nome']);
            $table->unique(['estado_id', 'nome']);
            $table->unique('ibge_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cidades');
    }
};
