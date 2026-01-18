<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cidades', function (Blueprint $table) {
            if (!Schema::hasColumn('cidades', 'estado_id')) {
                $table->foreignId('estado_id')->nullable()->after('id')->constrained('estados')->cascadeOnUpdate()->restrictOnDelete();
            }
            if (!Schema::hasColumn('cidades', 'nome')) {
                $table->string('nome')->nullable()->after('estado_id');
            }

            $table->index(['estado_id']);
        });
    }

    public function down(): void
    {
        Schema::table('cidades', function (Blueprint $table) {
            if (Schema::hasColumn('cidades', 'nome')) $table->dropColumn('nome');
            if (Schema::hasColumn('cidades', 'estado_id')) $table->dropColumn('estado_id');
        });
    }
};
