<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transporte_linha_filiais', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('linha_id')->constrained('transporte_linhas')->cascadeOnDelete();
            $table->foreignId('filial_id')->constrained('filiais')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['linha_id', 'filial_id'], 'uniq_transporte_linha_filial');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transporte_linha_filiais');
    }
};
