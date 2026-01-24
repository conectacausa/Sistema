<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cbos', function (Blueprint $table) {
            if (!Schema::hasColumn('cbos', 'validacao')) {
                $table->string('validacao', 120)->nullable()->after('descricao');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cbos', function (Blueprint $table) {
            if (Schema::hasColumn('cbos', 'validacao')) {
                $table->dropColumn('validacao');
            }
        });
    }
};
