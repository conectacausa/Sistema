<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_linha_custos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('linha_id')->constrained('transporte_linhas');

            $table->date('competencia'); // usar YYYY-MM-01
            $table->decimal('valor_total', 12, 2)->default(0);

            $table->string('origem', 50)->default('manual');
            $table->text('observacao')->nullable();

            $table->timestamps();

            $table->unique(['linha_id', 'competencia'], 'uniq_custo_linha_competencia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_linha_custos');
    }
};
