<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('paises', function (Blueprint $table) {
            if (!Schema::hasColumn('paises', 'nome')) {
                $table->string('nome')->after('id');
            }
            if (!Schema::hasColumn('paises', 'codigo_iso2')) {
                $table->string('codigo_iso2', 2)->nullable()->after('nome');
            }
        });
    }

    public function down(): void
    {
        Schema::table('paises', function (Blueprint $table) {
            if (Schema::hasColumn('paises', 'codigo_iso2')) $table->dropColumn('codigo_iso2');
            if (Schema::hasColumn('paises', 'nome')) $table->dropColumn('nome');
        });
    }
};
