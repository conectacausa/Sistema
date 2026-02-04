<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colaboradores_importacoes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('empresa_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('arquivo_path', 500);
            $table->string('arquivo_nome', 200)->nullable();

            $table->string('status', 30)->default('queued')->index(); // queued|processing|done|failed

            $table->integer('total_linhas')->nullable();
            $table->integer('importados')->default(0);
            $table->integer('ignorados')->default(0);

            $table->text('mensagem_erro')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colaboradores_importacoes');
    }
};
