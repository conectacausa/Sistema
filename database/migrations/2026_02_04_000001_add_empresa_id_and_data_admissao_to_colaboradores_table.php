<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            // Data de admissão
            if (!Schema::hasColumn('colaboradores', 'data_admissao')) {
                $table->date('data_admissao')->nullable();
            }

            // Empresa (tenant)
            if (!Schema::hasColumn('colaboradores', 'empresa_id')) {
                $table->foreignId('empresa_id')
                    ->nullable()
                    ->constrained('empresas')
                    ->restrictOnDelete();

                $table->index('empresa_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            if (Schema::hasColumn('colaboradores', 'empresa_id')) {
                $table->dropForeign(['empresa_id']);
                $table->dropIndex(['empresa_id']);
                $table->dropColumn('empresa_id');
            }

            if (Schema::hasColumn('colaboradores', 'data_admissao')) {
                $table->dropColumn('data_admissao');
            }
        });
    }
};
