<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vinculo_empresas_cnae', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cnae_id')->constrained('cnaes');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'cnae_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculo_empresas_cnae');
    }
};
