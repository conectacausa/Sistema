<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permissoes', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable()->after('id');

            $table->string('nome_grupo', 160)->nullable()->after('empresa_id');
            $table->text('observacoes')->nullable()->after('nome_grupo');

            $table->boolean('status')->default(true)->after('observacoes');
            $table->boolean('salarios')->default(false)->after('status');

            $table->softDeletes()->after('updated_at');
        });

        Schema::table('permissoes', function (Blueprint $table) {
            $table->index('empresa_id');
            $table->index('status');

            $table->foreign('empresa_id')->references('id')->on('empresas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('permissoes', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropIndex(['empresa_id']);
            $table->dropIndex(['status']);

            $table->dropColumn([
                'empresa_id',
                'nome_grupo',
                'observacoes',
                'status',
                'salarios',
                'deleted_at',
            ]);
        });
    }
};
