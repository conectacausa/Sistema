<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos_telas', function (Blueprint $table) {

            if (!Schema::hasColumn('grupos_telas', 'nome')) {
                $table->string('nome', 150)->nullable()->after('id');
            }

            if (!Schema::hasColumn('grupos_telas', 'icone')) {
                $table->string('icone', 80)->nullable()->after('nome');
            }

            if (!Schema::hasColumn('grupos_telas', 'modulo_id')) {
                $table->unsignedBigInteger('modulo_id')->after('icone');
            }

            if (!Schema::hasColumn('grupos_telas', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('modulo_id');
            }

            if (!Schema::hasColumn('grupos_telas', 'ordem')) {
                $table->unsignedInteger('ordem')->default(0)->after('ativo');
            }

            // Foreign key (somente se a tabela existir)
            if (Schema::hasTable('modulos')) {
                $table->foreign('modulo_id')
                    ->references('id')
                    ->on('modulos')
                    ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grupos_telas', function (Blueprint $table) {

            try { $table->dropForeign(['modulo_id']); } catch (\Throwable $e) {}

            $cols = ['ordem', 'ativo', 'modulo_id', 'icone', 'nome'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('grupos_telas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
