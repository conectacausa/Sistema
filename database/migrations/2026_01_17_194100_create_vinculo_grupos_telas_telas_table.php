<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grupos_telas', function (Blueprint $table) {
            $table->id();
            $table->string('nome_grupo', 180);
            $table->string('icone', 120)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('nome_grupo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupos_telas');
    }
};
