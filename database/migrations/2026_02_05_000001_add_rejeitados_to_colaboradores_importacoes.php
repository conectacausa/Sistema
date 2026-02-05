<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('colaboradores_importacoes', function (Blueprint $table) {
            $table->string('rejeitados_path', 255)->nullable()->after('arquivo_path');
            $table->unsignedInteger('rejeitados_count')->default(0)->after('ignorados');
        });
    }

    public function down(): void
    {
        Schema::table('colaboradores_importacoes', function (Blueprint $table) {
            $table->dropColumn(['rejeitados_path', 'rejeitados_count']);
        });
    }
};
