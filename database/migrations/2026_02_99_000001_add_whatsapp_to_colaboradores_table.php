<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('avd_ciclos');

        Schema::create('avd_ciclos', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('empresa_id')->index();

            $table->string('titulo', 180);
            $table->timestamp('inicio_em')->nullable();
            $table->timestamp('fim_em')->nullable();

            $table->string('tipo', 3)->default('180'); // 180 | 360

            $table->string('divergencia_tipo', 10)->default('percent'); // percent | pontos
            $table->decimal('divergencia_valor', 8, 2)->default(0);

            $table->boolean('permitir_inicio_manual')->default(true);
            $table->boolean('permitir_reabrir')->default(false);

            $table->string('status', 20)->default('aguardando'); // aguardando | iniciada | encerrada | em_consenso

            $table->decimal('peso_auto', 5, 2)->default(30);
            $table->decimal('peso_gestor', 5, 2)->default(70);
            $table->decimal('peso_pares', 5, 2)->default(0);

            $table->text('msg_auto')->nullable();
            $table->text('msg_gestor')->nullable();
            $table->text('msg_pares')->nullable();
            $table->text('msg_consenso')->nullable();
            $table->text('msg_lembrete')->nullable();

            $table->integer('lembrete_cada_dias')->nullable();
            $table->boolean('parar_lembrete_apos_responder')->default(true);

            $table->timestamp('deleted_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avd_ciclos');
    }
};
