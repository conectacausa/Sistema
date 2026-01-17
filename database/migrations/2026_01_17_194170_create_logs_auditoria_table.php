<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logs_auditoria', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();

            $table->foreignId('modulo_id')->nullable()->constrained('modulos')->nullOnDelete();
            $table->foreignId('tela_id')->nullable()->constrained('telas')->nullOnDelete();

            $table->string('acao', 120)->nullable();      // ex: "login", "criou_empresa"
            $table->string('rota', 190)->nullable();      // route name
            $table->string('metodo', 10)->nullable();     // GET/POST/PUT/DELETE
            $table->text('url')->nullable();

            $table->string('ip', 60)->nullable();
            $table->text('user_agent')->nullable();

            $table->integer('status_http')->nullable();   // resposta (200/500 etc)
            $table->integer('tempo_ms')->nullable();      // tempo de carregamento

            $table->json('payload')->nullable();          // dados relevantes (sem senha!)
            $table->text('observacoes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'created_at']);
            $table->index(['usuario_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_auditoria');
    }
};
