<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_paradas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('linha_id')->constrained('transporte_linhas')->cascadeOnDelete();

            $table->string('nome', 191);
            $table->string('endereco', 255)->nullable();

            $table->time('horario')->nullable();
            $table->decimal('valor', 10, 2)->default(0);

            $table->integer('ordem')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['linha_id', 'ordem']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_paradas');
    }
};
