<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('colaboradores')) return;

        Schema::table('colaboradores', function (Blueprint $table) {
            if (!Schema::hasColumn('colaboradores', 'matricula')) {
                $table->string('matricula', 40)->nullable()->after('id');
            }
            if (!Schema::hasColumn('colaboradores', 'filial_id')) {
                $table->unsignedBigInteger('filial_id')->nullable()->after('empresa_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('colaboradores')) return;

        Schema::table('colaboradores', function (Blueprint $table) {
            if (Schema::hasColumn('colaboradores', 'filial_id')) $table->dropColumn('filial_id');
            if (Schema::hasColumn('colaboradores', 'matricula')) $table->dropColumn('matricula');
        });
    }
};
