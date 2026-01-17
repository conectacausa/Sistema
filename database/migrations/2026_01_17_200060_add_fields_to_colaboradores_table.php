<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->string('cpf', 11)->nullable()->after('id');
            $table->string('nome', 255)->nullable()->after('cpf');

            $table->enum('sexo', ['M', 'F', 'NI'])->default('NI')->after('nome');

            $table->softDeletes()->after('updated_at');
        });

        Schema::table('colaboradores', function (Blueprint $table) {
            $table->unique('cpf');
            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::table('colaboradores', function (Blueprint $table) {
            $table->dropUnique(['cpf']);
            $table->dropIndex(['nome']);

            $table->dropColumn(['cpf', 'nome', 'sexo', 'deleted_at']);
        });
    }
};
