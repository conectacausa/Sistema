<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->string('logo_horizontal_light', 255)->nullable();
            $table->string('logo_horizontal_dark', 255)->nullable();
            $table->string('logo_quadrado_light', 255)->nullable();
            $table->string('logo_quadrado_dark', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // 1 config por empresa (normalmente é isso que você quer)
            $table->unique('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
};
