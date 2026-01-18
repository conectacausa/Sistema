<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('estados', function (Blueprint $table) {
            if (!Schema::hasColumn('estados', 'pais_id')) {
                $table->foreignId('pais_id')->nullable()->after('id')->constrained('paises')->cascadeOnUpdate()->restrictOnDelete();
            }
            if (!Schema::hasColumn('estados', 'nome')) {
                $table->string('nome')->nullable()->after('pais_id');
            }
            if (!Schema::hasColumn('estados', 'sigla')) {
                $table->string('sigla', 2)->nullable()->after('nome');
            }

            $table->index(['pais_id']);
        });
    }

    public function down(): void
    {
        Schema::table('estados', function (Blueprint $table) {
            if (Schema::hasColumn('estados', 'pais_id')) {
                // drop FK automaticamente em PG exige nome; então remove só colunas se necessário
                // Se quiser reversão perfeita, me diga o nome da constraint gerada.
            }
            if (Schema::hasColumn('estados', 'sigla')) $table->dropColumn('sigla');
            if (Schema::hasColumn('estados', 'nome')) $table->dropColumn('nome');
            if (Schema::hasColumn('estados', 'pais_id')) $table->dropColumn('pais_id');
        });
    }
};
