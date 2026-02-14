<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('avd_ciclo_participantes', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('ciclo_id')->index();

            $table->unsignedBigInteger('colaborador_id')->index();
            $table->unsignedBigInteger('filial_id')->nullable()->index();

            $table->string('whatsapp', 30)->nullable();

            // gestor (se você vincula gestor por usuario ou por colaborador)
            $table->unsignedBigInteger('gestor_usuario_id')->nullable()->index();
            $table->unsignedBigInteger('gestor_colaborador_id')->nullable()->index();

            // tokens por tipo
            $table->string('token_auto', 80)->nullable()->unique();
            $table->string('token_gestor', 80)->nullable()->unique();
            $table->string('token_pares', 80)->nullable()->unique();
            $table->string('token_consenso', 80)->nullable()->unique();

            $table->string('status', 20)->default('pendente'); // pendente | respondido | divergente | consenso_ok | finalizado

            $table->decimal('nota_auto', 6, 2)->nullable();
            $table->decimal('nota_gestor', 6, 2)->nullable();
            $table->decimal('nota_pares', 6, 2)->nullable();
            $table->decimal('nota_final', 6, 2)->nullable();

            $table->boolean('divergente')->default(false);
            $table->boolean('consenso_necessario')->default(false);

            $table->timestamp('deleted_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['ciclo_id', 'colaborador_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avd_ciclo_participantes');
    }
};
