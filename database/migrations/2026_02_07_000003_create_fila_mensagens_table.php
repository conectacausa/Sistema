<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fila_mensagens', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('empresa_id')->index();

            // Canal de envio
            $table->string('canal', 20)->index(); // whatsapp|email|push

            // Identificação do destinatário
            $table->string('destinatario', 255)->index(); // telefone/email/device_token
            $table->string('destinatario_nome', 255)->nullable();

            // Conteúdo
            $table->string('assunto', 255)->nullable(); // útil p/ email/push
            $table->text('mensagem')->nullable();       // corpo “humano”
            $table->jsonb('payload')->nullable();       // template, variáveis, ids, etc.

            // Priorização e agenda
            $table->integer('prioridade')->default(5)->index(); // maior = primeiro
            $table->timestamp('available_at')->nullable()->index(); // quando pode sair
            $table->timestamp('scheduled_at')->nullable();          // quando foi agendado (opcional)

            // Status e controle de processamento
            $table->string('status', 20)->default('queued')->index(); // queued|processing|sent|failed|canceled
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('max_attempts')->default(3);
            $table->timestamp('sent_at')->nullable();
            $table->text('last_error')->nullable();

            // Lock para evitar dois workers pegarem a mesma mensagem
            $table->timestamp('locked_at')->nullable()->index();
            $table->string('locked_by', 100)->nullable();

            // Retorno do provedor
            $table->string('provider', 50)->nullable()->index(); // ex: meta, twilio, smtp, fcm
            $table->string('provider_message_id', 120)->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Índice composto para pegar “prontas” por prioridade
            $table->index(['empresa_id', 'status', 'prioridade', 'available_at'], 'fila_msg_pick_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fila_mensagens');
    }
};
