<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('estados', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->char('uf', 2);
            $table->unsignedInteger('ibge_id')->nullable();

            $table->foreignId('pais_id')->constrained('paises');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['pais_id', 'uf']);
            $table->unique(['pais_id', 'uf']);
            $table->unique('ibge_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados');
    }
};
