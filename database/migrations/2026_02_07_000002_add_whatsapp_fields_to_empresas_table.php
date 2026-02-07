<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            // Evolution / WhatsApp (1 instância por empresa)
            $table->string('wa_instance_name')->nullable();
            $table->uuid('wa_instance_id')->nullable();
            $table->string('wa_instance_apikey')->nullable(); // gerado no create (hash.apikey)
            $table->string('wa_phone')->nullable();           // telefone informado/confirmado
            $table->longText('wa_qrcode_base64')->nullable(); // base64 do QR (se você optar por webhook no futuro)
            $table->string('wa_connection_state')->nullable(); // ex: "open", "close"
            $table->timestamp('wa_connected_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('empresas', function (Blueprint $table) {
            $table->dropColumn([
                'wa_instance_name',
                'wa_instance_id',
                'wa_instance_apikey',
                'wa_phone',
                'wa_qrcode_base64',
                'wa_connection_state',
                'wa_connected_at',
            ]);
        });
    }
};
