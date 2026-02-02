<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('colaboradores')) {
            return;
        }

        Schema::table('colaboradores', function (Blueprint $table) {
            // matricula
            if (!Schema::hasColumn('colaboradores', 'matricula')) {
                $table->string('matricula', 60)->nullable()->index();
            }

            // filial_id
            if (!Schema::hasColumn('colaboradores', 'filial_id')) {
                // Não estou usando constrained() para evitar dor com chaves/tenancy.
                $table->unsignedBigInteger('filial_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('colaboradores')) {
            return;
        }

        Schema::table('colaboradores', function (Blueprint $table) {
            if (Schema::hasColumn('colaboradores', 'filial_id')) {
                $table->dropColumn('filial_id');
            }
            if (Schema::hasColumn('colaboradores', 'matricula')) {
                $table->dropColumn('matricula');
            }
        });
    }
};
