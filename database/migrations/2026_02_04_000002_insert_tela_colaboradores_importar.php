<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ajuste os nomes das colunas se sua tabela "telas" for diferente.
        // Aqui estamos garantindo id=14 e slug=colaboradores/importar.
        $exists = DB::table('telas')->where('id', 14)->exists();

        if (!$exists) {
            DB::table('telas')->insert([
                'id' => 14,
                'slug' => 'colaboradores/importar',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('telas')
                ->where('id', 14)
                ->update([
                    'slug' => 'colaboradores/importar',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        DB::table('telas')->where('id', 14)->delete();
    }
};
