<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_absensi', function (Blueprint $table) {
            $table->bigIncrements('id_absensi');

            $table->unsignedBigInteger('id_guru');
            $table->foreign('id_guru')
                  ->references('id_guru')
                  ->on('tb_guru')
                  ->onDelete('cascade');

            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_keluar')->nullable();

            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa'])
                  ->default('hadir');

            $table->text('keterangan')->nullable();

            // Mencegah absen ganda di hari yang sama untuk guru yang sama
            $table->unique(['id_guru', 'tanggal'], 'unique_absensi_harian');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_absensi');
    }
};
