<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('logs_auditoria', function (Blueprint $table) {

            // Quem / Onde
            if (!Schema::hasColumn('logs_auditoria', 'empresa_id')) {
                $table->unsignedBigInteger('empresa_id')->nullable()->index();
            }

            if (!Schema::hasColumn('logs_auditoria', 'usuario_id')) {
                $table->unsignedBigInteger('usuario_id')->nullable()->index();
            }

            // O que (tela/ação)
            if (!Schema::hasColumn('logs_auditoria', 'tela_id')) {
                $table->unsignedBigInteger('tela_id')->nullable()->index();
            }

            if (!Schema::hasColumn('logs_auditoria', 'tela_slug')) {
                $table->string('tela_slug', 220)->nullable()->index();
            }

            if (!Schema::hasColumn('logs_auditoria', 'acao')) {
                $table->string('acao', 80)->nullable()->index(); // ACESSO_PERMITIDO, ACESSO_NEGADO, CREATE, UPDATE etc
            }

            if (!Schema::hasColumn('logs_auditoria', 'descricao')) {
                $table->text('descricao')->nullable();
            }

            // Request info
            if (!Schema::hasColumn('logs_auditoria', 'metodo')) {
                $table->string('metodo', 10)->nullable();
            }

            if (!Schema::hasColumn('logs_auditoria', 'url')) {
                $table->text('url')->nullable();
            }

            if (!Schema::hasColumn('logs_auditoria', 'ip')) {
                $table->string('ip', 45)->nullable()->index();
            }

            if (!Schema::hasColumn('logs_auditoria', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }

            if (!Schema::hasColumn('logs_auditoria', 'status_code')) {
                $table->integer('status_code')->nullable();
            }

            // Dados extras (Postgres ideal: jsonb)
            if (!Schema::hasColumn('logs_auditoria', 'payload')) {
                $table->jsonb('payload')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('logs_auditoria', function (Blueprint $table) {
            $cols = [
                'empresa_id','usuario_id','tela_id','tela_slug','acao','descricao',
                'metodo','url','ip','user_agent','status_code','payload'
            ];

            foreach ($cols as $col) {
                if (Schema::hasColumn('logs_auditoria', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
