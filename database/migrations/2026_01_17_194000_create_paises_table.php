<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('paises', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120);
            $table->char('iso2', 2)->nullable();
            $table->char('iso3', 3)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('iso2');
            $table->unique('iso3');
            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paises');
    }
};
