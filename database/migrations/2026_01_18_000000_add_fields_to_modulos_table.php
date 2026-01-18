<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            // Evita erro se rodar em ambientes diferentes
            if (!Schema::hasColumn('modulos', 'nome')) {
                $table->string('nome', 120)->nullable()->after('id');
            }

            if (!Schema::hasColumn('modulos', 'slug')) {
                $table->string('slug', 120)->nullable()->unique()->after('nome');
            }

            if (!Schema::hasColumn('modulos', 'icone')) {
                $table->string('icone', 60)->nullable()->after('slug');
            }

            if (!Schema::hasColumn('modulos', 'ordem')) {
                $table->unsignedInteger('ordem')->default(0)->after('icone');
            }

            if (!Schema::hasColumn('modulos', 'ativo')) {
                $table->boolean('ativo')->default(true)->after('ordem');
            }

            if (!Schema::hasColumn('modulos', 'descricao')) {
                $table->text('descricao')->nullable()->after('ativo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            // Remove na ordem inversa (e cuidando do índice unique)
            if (Schema::hasColumn('modulos', 'slug')) {
                $table->dropUnique(['slug']);
            }

            $cols = ['descricao', 'ativo', 'ordem', 'icone', 'slug', 'nome'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('modulos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
