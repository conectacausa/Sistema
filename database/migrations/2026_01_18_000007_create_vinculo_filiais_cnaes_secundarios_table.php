<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vinculo_filiais_cnaes_secundarios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('filial_id')
                ->constrained('filiais')
                ->cascadeOnUpdate()
                ->cascadeOnDelete(); // se apagar filial, apaga vínculos

            $table->foreignId('cnae_id')
                ->constrained('cnaes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamps();

            // Evita duplicar o mesmo CNAE secundário na mesma filial
            $table->unique(['empresa_id', 'filial_id', 'cnae_id'], 'uniq_emp_filial_cnae_sec');

            $table->index(['empresa_id']);
            $table->index(['filial_id']);
            $table->index(['cnae_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vinculo_filiais_cnaes_secundarios');
    }
};
