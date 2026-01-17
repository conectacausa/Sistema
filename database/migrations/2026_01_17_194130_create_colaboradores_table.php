<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->id();
            $table->string('cpf', 14)->unique();
            $table->string('nome', 180);

            $table->timestamps();
            $table->softDeletes();

            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colaboradores');
    }
};
