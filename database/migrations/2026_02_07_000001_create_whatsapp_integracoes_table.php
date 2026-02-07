<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_integracoes', function (Blueprint $table) {
            $table->bigIncrements('id');

            // 1 integração por empresa (por enquanto)
            $table->unsignedBigInteger('empresa_id')->unique();

            // Provider (futuro: permitir outros)
            $table->string('provider', 50)->default('evolution');

            // Evolution
            $table->string('base_url', 255)->nullable();     // ex: https://evolution.conecttarh.com.br
            $table->string('api_key', 255)->nullable();      // chave do Evolution (ideal criptografar depois)
            $table->string('instance_name', 150)->nullable();// nome da instância no Evolution

            // Status/controle
            $table->boolean('ativo')->default(true);
            $table->string('status', 50)->nullable();        // connected, disconnected, connecting...
            $table->string('telefone', 30)->nullable();      // número conectado (se disponível)
            $table->timestamp('connected_at')->nullable();
            $table->timestamp('last_sync_at')->nullable();

            // Webhook (para receber eventos)
            $table->string('webhook_url', 255)->nullable();
            $table->string('webhook_secret', 255)->nullable();

            // Extra para armazenar payloads/infos do provider
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // FK (se a tabela empresas existir)
            // Ajuste o nome da tabela caso seja diferente no seu banco.
            if (Schema::hasTable('empresas')) {
                $table->foreign('empresa_id')->references('id')->on('empresas');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_integracoes');
    }
};
