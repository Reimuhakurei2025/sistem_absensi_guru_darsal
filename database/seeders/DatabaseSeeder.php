<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Kepsek;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder berdasarkan data asli SMP Terpadu Darussalam.
     * Sumber: Banner "Data Keadaan Guru dan Pegawai" di sekolah.
     *
     * Catatan:
     *  - Pak Syamsudin (Kepsek) di tabel tb_kepsek, BUKAN di tb_guru
     *  - Sekolah swasta: NIP dan email tidak wajib, sehingga banyak yang null
     *  - Username di-generate dari nama pertama (lowercase, no gelar)
     */
    public function run(): void
    {
        // ============================================
        // 1. KEPALA SEKOLAH
        // ============================================
        Kepsek::create([
            'nip'           => null, // sekolah swasta - NIP opsional
            'username'      => 'kepsek',
            'password'      => Hash::make('kepsek123'),
            'nama_lengkap'  => 'Syamsudin, S.E',
            'email'         => null,
            'no_hp'         => null,
            'is_active'     => true,
        ]);

        // ============================================
        // 2. ADMIN / TATA USAHA
        // ============================================
        Admin::create([
            'username'     => 'admin',
            'password'     => Hash::make('admin123'),
            'nama_lengkap' => 'Tata Usaha SMP Darussalam',
            'email'        => null,
            'no_hp'        => null,
            'is_active'    => true,
        ]);

        // ============================================
        // 3. DATA 20 GURU & PEGAWAI (dari banner)
        // ============================================
        $dataGuru = [
            [
                'nama_lengkap'   => 'Triyoga Haji K, S.Pd',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Bogor',
                'tgl_lahir'      => '1970-05-08',
                'jabatan'        => 'Guru/W.Kurikulum',
                'mata_pelajaran' => 'IPS',
                'username'       => 'triyoga',
            ],
            [
                'nama_lengkap'   => 'Utsman Ali S, S.E',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Jakarta',
                'tgl_lahir'      => '1982-04-16',
                'jabatan'        => 'Guru/W.Kesiswaan',
                'mata_pelajaran' => 'MTK',
                'username'       => 'utsman',
            ],
            [
                'nama_lengkap'   => 'Maryanto, S.S',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Sukabumi',
                'tgl_lahir'      => '1989-06-15',
                'jabatan'        => 'Guru/Operator',
                'mata_pelajaran' => 'B.Sunda',
                'username'       => 'maryanto',
            ],
            [
                'nama_lengkap'   => 'Agus Seputra, S.Pd',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Tanjung Bulan',
                'tgl_lahir'      => '1971-04-19',
                'jabatan'        => 'Guru/K.Sapras',
                'mata_pelajaran' => 'B.Inggris',
                'username'       => 'agus.seputra',
            ],
            [
                'nama_lengkap'   => 'Sri Hastuti, S.Pd',
                'jenis_kelamin'  => 'P',
                'tempat_lahir'   => 'Sleman',
                'tgl_lahir'      => '1969-08-28',
                'jabatan'        => 'Guru/Walas IX.A',
                'mata_pelajaran' => 'B.Indo',
                'username'       => 'sri.hastuti',
            ],
            [
                'nama_lengkap'   => 'Fahrul Rozi, S.Pd.I',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Bogor',
                'tgl_lahir'      => '1981-03-08',
                'jabatan'        => 'Guru/Walas IX.B/TU',
                'mata_pelajaran' => 'IPA',
                'username'       => 'fahrul.rozi',
            ],
            [
                'nama_lengkap'   => 'Irpan Pahrezi, S.Pd',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Jakarta',
                'tgl_lahir'      => '1982-10-07',
                'jabatan'        => 'Guru/Walas VIII',
                'mata_pelajaran' => 'Tahfidz',
                'username'       => 'irpan',
            ],
            [
                'nama_lengkap'   => 'Siti Fadilah, S.Sos.I',
                'jenis_kelamin'  => 'P',
                'tempat_lahir'   => 'Bogor',
                'tgl_lahir'      => '1997-01-07',
                'jabatan'        => 'Guru/Walas VII.A',
                'mata_pelajaran' => 'Tahfidz',
                'username'       => 'siti.fadilah',
            ],
            [
                'nama_lengkap'   => 'Desi Aprianti, S.E, S.Pd',
                'jenis_kelamin'  => 'P',
                'tempat_lahir'   => 'Bogor',
                'tgl_lahir'      => '1984-03-31',
                'jabatan'        => 'Guru/Walas VII.B',
                'mata_pelajaran' => 'Qurdis, PKn',
                'username'       => 'desi.aprianti',
            ],
            [
                'nama_lengkap'   => 'Dedi Irawan',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Bogor',
                'tgl_lahir'      => '1985-12-16',
                'jabatan'        => 'Guru/P.Admin',
                'mata_pelajaran' => 'PJOK',
                'username'       => 'dedi.irawan',
            ],
            [
                'nama_lengkap'   => 'Abdul Rojak, S.Ag',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Sukabumi',
                'tgl_lahir'      => '1970-10-12',
                'jabatan'        => 'Guru BP / BK',
                'mata_pelajaran' => 'Fiqh, Tahfiz',
                'username'       => 'abdul.rojak',
            ],
            [
                'nama_lengkap'   => 'Wida Widianingsih, S.Pd',
                'jenis_kelamin'  => 'P',
                'tempat_lahir'   => 'Tangerang',
                'tgl_lahir'      => '1977-11-16',
                'jabatan'        => 'Bendahara',
                'mata_pelajaran' => 'Konseling',
                'username'       => 'wida',
            ],
            [
                'nama_lengkap'   => 'Drs. Rusdi',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Sukabumi',
                'tgl_lahir'      => '1994-11-19',
                'jabatan'        => 'Guru/Purna bakti',
                'mata_pelajaran' => 'PKn',
                'username'       => 'rusdi',
            ],
            [
                'nama_lengkap'   => 'Nislam, S.Pd',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Bogor',
                'tgl_lahir'      => '1964-05-05',
                'jabatan'        => 'Guru',
                'mata_pelajaran' => 'TIK, Prakarya',
                'username'       => 'nislam',
            ],
            [
                'nama_lengkap'   => 'Siti Hani, S.Pd.I',
                'jenis_kelamin'  => 'P',
                'tempat_lahir'   => 'Cilacap',
                'tgl_lahir'      => '1967-04-12',
                'jabatan'        => 'Guru',
                'mata_pelajaran' => 'SBY',
                'username'       => 'siti.hani',
            ],
            [
                'nama_lengkap'   => 'Riah Hujriyanah, S.S',
                'jenis_kelamin'  => 'P',
                'tempat_lahir'   => 'Bogor',
                'tgl_lahir'      => '1970-08-22',
                'jabatan'        => 'Guru',
                'mata_pelajaran' => 'Aqidah',
                'username'       => 'riah',
            ],
            [
                'nama_lengkap'   => 'Sunadi, S.Pd.I',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Jakarta',
                'tgl_lahir'      => '1984-04-03',
                'jabatan'        => 'Guru',
                'mata_pelajaran' => 'B. Arab',
                'username'       => 'sunadi',
            ],
            [
                'nama_lengkap'   => 'Nastiti Hutami Putri, S.Pd',
                'jenis_kelamin'  => 'P',
                'tempat_lahir'   => 'Bogor',
                'tgl_lahir'      => '1981-06-09',
                'jabatan'        => 'Guru',
                'mata_pelajaran' => 'Bhs. Indonesia',
                'username'       => 'nastiti',
            ],
            [
                'nama_lengkap'   => 'Alpian Shopar',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Depok',
                'tgl_lahir'      => '1998-11-21',
                'jabatan'        => 'Guru Piket',
                'mata_pelajaran' => 'Pramuka',
                'username'       => 'alpian',
            ],
            [
                'nama_lengkap'   => 'Dodi Lesmana',
                'jenis_kelamin'  => 'L',
                'tempat_lahir'   => 'Sukabumi',
                'tgl_lahir'      => '1995-08-15',
                'jabatan'        => 'Pramuka',
                'mata_pelajaran' => 'Pramuka',
                'username'       => 'dodi',
            ],
        ];

        foreach ($dataGuru as $data) {
            Guru::create(array_merge($data, [
                'password'      => Hash::make('guru123'),
                'barcode_token' => Guru::generateBarcodeToken(),
                'agama'         => 'Islam',
                'nip'           => null,
                'email'         => null,
                'is_active'     => true,
            ]));
        }
    }
}
