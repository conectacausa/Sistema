<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();

            $table->string('nome_completo', 180);
            $table->string('cpf', 14)->unique();

            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('permissao_id')->constrained('permissoes');

            $table->string('email', 190)->unique();
            $table->string('telefone', 30)->nullable();

            $table->string('senha'); // hash bcrypt/argon (campo password)
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');

            $table->date('data_expiracao')->nullable();
            $table->boolean('salarios')->default(false);

            $table->foreignId('colaborador_id')->nullable()->constrained('colaboradores');

            $table->boolean('operador_whatsapp')->default(false);
            $table->string('foto', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('nome_completo');
            $table->index(['empresa_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
