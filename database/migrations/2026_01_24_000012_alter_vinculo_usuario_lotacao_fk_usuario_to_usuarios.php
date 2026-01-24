<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vinculo_usuario_lotacao', function (Blueprint $table) {
            // remove FK antiga (provavelmente apontando para users)
            try { $table->dropForeign(['usuario_id']); } catch (\Throwable $e) {}
        });

        Schema::table('vinculo_usuario_lotacao', function (Blueprint $table) {
            // cria FK correta: usuarios(id)
            $table->foreign('usuario_id')
                ->references('id')
                ->on('usuarios')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('vinculo_usuario_lotacao', function (Blueprint $table) {
            try { $table->dropForeign(['usuario_id']); } catch (\Throwable $e) {}
        });

        // Se quiser reverter para users (opcional). Mantive para não quebrar rollback em ambiente sem users.
        Schema::table('vinculo_usuario_lotacao', function (Blueprint $table) {
            $table->foreign('usuario_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }
};
