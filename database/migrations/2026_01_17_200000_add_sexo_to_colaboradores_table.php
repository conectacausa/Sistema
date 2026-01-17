<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->enum('sexo', ['M', 'F', 'NI'])->default('NI')->after('cpf');
        });
    }

    public function down(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->dropColumn('sexo');
        });
    }
};
