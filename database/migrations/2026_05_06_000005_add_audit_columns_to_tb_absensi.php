<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan kolom untuk audit trail input manual absensi.
 *
 * input_method  : 'scan' (default, via QR Code) atau 'manual' (diinput oleh Kepsek/Admin)
 * input_by_role : siapa yang menginput manual? ('kepsek' / 'admin' / null kalau scan)
 * input_by_id   : ID user yang menginput (id_kepsek atau id_admin)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_absensi', function (Blueprint $table) {
            $table->enum('input_method', ['scan', 'manual'])
                  ->default('scan')
                  ->after('keterangan');

            $table->enum('input_by_role', ['kepsek', 'admin'])
                  ->nullable()
                  ->after('input_method');

            $table->unsignedBigInteger('input_by_id')
                  ->nullable()
                  ->after('input_by_role');
        });
    }

    public function down(): void
    {
        Schema::table('tb_absensi', function (Blueprint $table) {
            $table->dropColumn(['input_method', 'input_by_role', 'input_by_id']);
        });
    }
};
