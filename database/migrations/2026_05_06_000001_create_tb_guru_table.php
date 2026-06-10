<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_guru', function (Blueprint $table) {
            $table->bigIncrements('id_guru');

            // NIP dan email opsional untuk sekolah swasta
            $table->string('nip', 20)->unique()->nullable();
            $table->string('email', 100)->unique()->nullable();

            $table->string('nama_lengkap', 100);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 50)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('agama', 20)->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp', 15)->nullable();
            $table->string('mata_pelajaran', 100)->nullable();

            // Jabatan (Kepsek, Wakil Kurikulum, Walas, Bendahara, dll)
            $table->string('jabatan', 100)->nullable();

            $table->string('username', 50)->unique();
            $table->string('password');
            $table->string('foto', 255)->nullable();

            $table->string('barcode_token', 64)->unique()
                  ->comment('Token unik untuk barcode/QR absensi');

            $table->boolean('is_active')->default(true);

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_guru');
    }
};
