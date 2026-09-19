<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\GuruNotifikasiSetting;
use App\Models\HariOperasional;
use App\Models\Jabatan;
use App\Models\Kelas;
use App\Models\MasterJam;
use App\Models\Periode;
use App\Models\PlotJadwal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private const PASSWORD = 'demo1234';

    public function run(): void
    {
        if (config('app.demo') !== true) {
            return;
        }

        $this->call(PermissionSeeder::class);
        $this->call(JabatanSeeder::class);
        $this->call(PelajaranSeeder::class);

        $periode = Periode::create([
            'tahun_ajaran'    => '2026/2027',
            'semester'        => 'Ganjil',
            'is_active'       => true,
            'tanggal_mulai'   => '2026-07-13',
            'tanggal_selesai' => '2026-12-19',
        ]);

        $hariCfg = ['Senin' => 10, 'Selasa' => 10, 'Rabu' => 10, 'Kamis' => 10, 'Jumat' => 10, 'Sabtu' => 6];
        foreach ($hariCfg as $nama => $maxJam) {
            HariOperasional::updateOrCreate(['hari' => $nama], [
                'is_active' => true,
                'max_jam'   => $maxJam,
                'jam_mulai' => '07:00:00',
                'keterangan' => null,
            ]);
        }

        $jamKaku = [
            [1, '07:00:00', '07:30:00'], [2, '07:30:00', '08:00:00'],
            [3, '08:00:00', '08:30:00'], [4, '08:30:00', '09:00:00'],
            [5, '10:00:00', '10:30:00'], [6, '10:30:00', '11:00:00'],
            [7, '11:00:00', '11:30:00'], [8, '11:30:00', '12:00:00'],
            [9, '12:45:00', '13:15:00'], [10, '13:15:00', '13:45:00'],
        ];
        foreach ($jamKaku as $j) {
            MasterJam::updateOrCreate(['jam_ke' => $j[0]], ['jam_mulai' => $j[1], 'jam_selesai' => $j[2]]);
        }

        $daftarGuru = [
            ['nig' => '1001', 'nama' => 'Gus H. A. Bachtiar Yogiarto', 'jk' => 'Laki-laki', 'jab' => ['Kepala Sekolah']],
            ['nig' => '1002', 'nama' => 'Ust. Rizan',                  'jk' => 'Laki-laki', 'jab' => ['Wakil Kurikulum']],
            ['nig' => '1003', 'nama' => 'Ust. Affan Zainul M',         'jk' => 'Laki-laki', 'jab' => ['Tata Usaha (TU)']],
            ['nig' => '1004', 'nama' => 'Ust. M. Zadid Taqwa',         'jk' => 'Laki-laki', 'jab' => ['Bendahara']],
            ['nig' => '1005', 'nama' => 'Ust. Hifni Zainul M',         'jk' => 'Laki-laki', 'jab' => ['Guru']],
            ['nig' => '1006', 'nama' => 'Ust. Zuhdiyanto',             'jk' => 'Laki-laki', 'jab' => ['Guru']],
            ['nig' => '1007', 'nama' => 'Neng Fidela Devina M',        'jk' => 'Perempuan', 'jab' => ['Wali Kelas']],
            ['nig' => '1008', 'nama' => 'Neng Indah Ramadhani',        'jk' => 'Perempuan', 'jab' => ['Wali Kelas']],
            ['nig' => '1009', 'nama' => 'Neng Silfia',                 'jk' => 'Perempuan', 'jab' => ['Guru']],
            ['nig' => '1010', 'nama' => 'Ustd. Melly Y. I',            'jk' => 'Perempuan', 'jab' => ['Guru']],
        ];

        $guruByNig = [];
        foreach ($daftarGuru as $i => $g) {
            $guru = Guru::updateOrCreate(['nig' => $g['nig']], [
                'nama_guru'           => $g['nama'],
                'jenis_kelamin'       => $g['jk'],
                'gender'              => $g['jk'],
                'tempat_lahir'        => 'Bandung',
                'tanggal_lahir'       => '1990-0' . (($i % 9) + 1) . '-0' . ($i % 9 + 1),
                'no_hp'               => '0812' . str_pad((string) ($i * 111111 + 111111), 8, '0', STR_PAD_LEFT),
                'alamat'              => 'Jl. Pendidikan No. ' . ($i + 1),
                'pendidikan_terakhir' => 'S1',
                'status'              => 'Aktif',
                'jarak_km'            => 2 + $i,
                'boleh_edit_profil'   => true,
            ]);
            foreach ($g['jab'] as $namaJab) {
                $jabatan = Jabatan::where('nama_jabatan', $namaJab)->first();
                if ($jabatan && !$guru->jabatans()->where('jabatans.id', $jabatan->id)->exists()) {
                    $guru->jabatans()->attach($jabatan->id, ['is_utama' => true]);
                }
            }
            GuruNotifikasiSetting::firstOrCreate(['guru_id' => $guru->id], ['is_enabled' => true, 'mode' => 'sound']);
            $guruByNig[$g['nig']] = $guru;
        }

        $akun = [
            'admin'     => ['username' => 'demo_admin',     'name' => 'Administrator Demo',     'role' => 'Administrator'],
            'bendahara' => ['username' => 'demo_bendahara', 'name' => 'Ust. M. Zadid Taqwa',    'role' => 'Bendahara'],
            'staf'      => ['username' => 'demo_staf',      'name' => 'Ust. Affan Zainul M',    'role' => 'Staf Bendahara'],
            'guru'      => ['username' => 'demo_guru',      'name' => 'Ust. Hifni Zainul M',    'role' => 'Dewan Guru'],
            'walikelas' => ['username' => 'demo_walikelas', 'name' => 'Neng Fidela Devina M',   'role' => 'Wali Kelas'],
        ];

        $userId = [];
        foreach ($akun as $key => $a) {
            $user = User::updateOrCreate(['username' => $a['username']], [
                'name'     => $a['name'],
                'email'    => $a['username'] . '@demo.mumaris.com',
                'role'     => $a['role'],
                'status'   => 'Aktif',
                'lembaga'  => 'MUMARIS',
                'password' => Hash::make(self::PASSWORD),
            ]);
            if (!$user->hasRole($a['role'])) {
                $user->assignRole($a['role']);
            }
            $userId[$key] = $user->id;
        }

        $kelasDef = [
            ['VII.A', 'VII', '1007'], ['VII.B', 'VII', '1008'],
            ['VIII.A', 'VIII', '1007'], ['VIII.B', 'VIII', '1008'],
            ['IX.A', 'IX', '1005'], ['IX.B', 'IX', '1006'],
        ];

        $kelasRows = [];
        foreach ($kelasDef as $k) {
            $kelasRows[] = Kelas::updateOrCreate(['nama_kelas' => $k[0]], [
                'tingkat'      => $k[1],
                'wali_kelas_id' => $guruByNig[$k[2]]->id,
            ]);
        }

        $pelajaranIds = DB::table('pelajarans')->pluck('id')->take(12)->all();
        $plotBelajar = [];
        $guruListGrup = [$guruByNig['1005'], $guruByNig['1006'], $guruByNig['1007'], $guruByNig['1008'], $guruByNig['1009'], $guruByNig['1010']];
        foreach ($kelasRows as $kk => $kelas) {
            for ($p = 0; $p < 8; $p++) {
                $guru = $guruListGrup[($kk + $p) % count($guruListGrup)];
                PlotJadwal::updateOrCreate(
                    ['kelas_id' => $kelas->id, 'pelajaran_id' => $pelajaranIds[$p % count($pelajaranIds)]],
                    ['guru_id' => $guru->id, 'beban_jam' => 2 + (($kk + $p) % 3)]
                );
                $plotBelajar[$kelas->id][] = $guru->id;
            }
        }

        /* LANJUTAN_7_11 */

        $hariSeq = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $jadwalRows = [];
        foreach ($kelasRows as $kk => $kelas) {
            foreach ($hariSeq as $hd => $hari) {
                for ($jam = 1; $jam <= $hariCfg[$hari]; $jam++) {
                    $guruId = $plotBelajar[$kelas->id][($hd + $jam) % count($plotBelajar[$kelas->id])];
                    $jadwalRows[] = [
                        'periode_id'     => $periode->id,
                        'tahun_ajaran'   => $periode->tahun_ajaran,
                        'kelas_id'       => $kelas->id,
                        'hari'           => $hari,
                        'berlaku_mulai'  => $periode->tanggal_mulai,
                        'berlaku_sampai' => $periode->tanggal_selesai,
                        'jam_ke'         => $jam,
                        'pelajaran_id'   => $pelajaranIds[($kk + $hd + $jam) % count($pelajaranIds)],
                        'guru_id'        => $guruId,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }
            }
        }
        foreach (array_chunk($jadwalRows, 250) as $chunk) {
            DB::table('jadwal_harians')->insert($chunk);
        }

        $jadwalList = DB::table('jadwal_harians')->select('id', 'hari')->whereNotNull('guru_id')->get();
        $tanggalKerja = [];
        $d = \Carbon\Carbon::today();
        while (count($tanggalKerja) < 20) {
            if (in_array($d->englishDayOfWeek, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])) {
                $tanggalKerja[] = $d->copy();
            }
            $d->subDay();
        }

        $hadirRows = [];
        foreach ($tanggalKerja as $tgl) {
            $hariIni = map_hari($tgl->englishDayOfWeek);
            foreach ($jadwalList as $jd) {
                if ($jd->hari !== $hariIni) {
                    continue;
                }
                $status = 'Hadir';
                $pengganti = null;
                $ket = null;
                $rol = (int) $jd->id % 10;
                if ($rol === 4) {
                    $status = 'Izin';
                    $ket = 'Izin (sakit / keperluan keluarga)';
                    $pengganti = $guruByNig['1009']->nig;
                } elseif ($rol === 7) {
                    $status = 'Alpha';
                }
                $hadirRows[] = [
                    'periode_id'    => $periode->id,
                    'jadwal_id'     => $jd->id,
                    'tanggal'       => $tgl->format('Y-m-d'),
                    'status'        => $status,
                    'nig_pengganti' => $pengganti,
                    'keterangan'    => $ket,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }
        foreach (array_chunk($hadirRows, 400) as $chunk) {
            DB::table('kehadiran_gurus')->insert($chunk);
        }

        /* LANJUTAN_9_11 */

        $bulanHonor = (int) date('n');
        $tahunHonor = (int) date('Y');
        $honorKonfId = DB::table('honor_konfigurasis')->insertGetId([
            'periode_id'            => $periode->id,
            'bulan'                 => $bulanHonor,
            'tahun'                 => $tahunHonor,
            'tarif_jam_normal'      => 5000,
            'tarif_pengabdian'      => 4000,
            'tarif_piket'           => 4000,
            'tarif_piket_pengabdian' => 4000,
            'tarif_transport'       => 20000,
            'tarif_wali_kelas'      => 50000,
            'catatan'               => 'Data contoh mode demo.',
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);

        $jamWajib = DB::table('plot_jadwals')
            ->select('guru_id', DB::raw('SUM(beban_jam) as total'))
            ->whereNotNull('guru_id')
            ->groupBy('guru_id')
            ->pluck('total', 'guru_id')
            ->toArray();

        $strukturalMap = [
            'Kepala Sekolah' => 300000,
            'Wakil Kurikulum' => 200000,
            'Bendahara'      => 200000,
        ];

        $honorPeriodeId = DB::table('honor_periodes')->insertGetId([
            'honor_konfigurasi_id' => $honorKonfId,
            'bulan'                => $bulanHonor,
            'tahun'                => $tahunHonor,
            'status'               => 'draft',
            'created_by'           => $userId['bendahara'],
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        foreach ($guruByNig as $idx => $guru) {
            $isTetap = $idx <= '1006';
            $statusHonor = $isTetap ? 'Tetap' : 'Pengabdian';
            DB::table('honor_guru_configs')->insert([
                'honor_konfigurasi_id' => $honorKonfId,
                'guru_id'              => $guru->id,
                'status_honor'         => $statusHonor,
                'dari_luar'            => false,
                'tarif_override'       => null,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            $jabatanUtama = $guru->jabatans()->wherePivot('is_utama', true)->first();
            $namaJab = $jabatanUtama?->nama_jabatan;
            if ($namaJab && !DB::table('honor_struktural_configs')
                ->where('honor_konfigurasi_id', $honorKonfId)
                ->where('jabatan_id', $jabatanUtama->id)
                ->exists()) {
                DB::table('honor_struktural_configs')->insert([
                    'honor_konfigurasi_id' => $honorKonfId,
                    'jabatan_id'           => $jabatanUtama->id,
                    'nominal'              => $strukturalMap[$namaJab] ?? 0,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }

            $jamW = (int) ($jamWajib[$guru->id] ?? 0);
            $alpa = ($idx % 3 === 0) ? 1 : 0;
            $izin = ($idx % 4 === 0) ? 1 : 0;
            $sakit = ($idx % 5 === 0) ? 1 : 0;
            $realita = max(0, $jamW - ($alpa + $izin + $sakit));
            $tarif = $isTetap ? 5000 : 4000;
            $honorPokok = $realita * $tarif;
            $tunjanganStruktural = $strukturalMap[$namaJab ?? ''] ?? 0;
            $tunjanganWali = str_contains($namaJab ?? '', 'Wali Kelas') ? 150000 : 0;
            $total = $honorPokok + $tunjanganStruktural + $tunjanganWali;

            DB::table('honor_details')->insert([
                'honor_periode_id'      => $honorPeriodeId,
                'guru_id'               => $guru->id,
                'jam_wajib'             => $jamW,
                'alpa'                  => $alpa,
                'izin'                  => $izin,
                'sakit'                 => $sakit,
                'piket_jam'             => 0,
                'realita_jam'           => $realita,
                'persentase'            => $jamW > 0 ? round($realita / $jamW * 100, 2) : 0,
                'keterangan'            => '-',
                'honor_pokok'           => $honorPokok,
                'tunjangan_struktural'  => $tunjanganStruktural,
                'tunjangan_wali_kelas'  => $tunjanganWali,
                'transport'             => 0,
                'honor_piket'           => 0,
                'total'                 => $total,
                'is_diterima'           => false,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }

        $namaSiswa = [
            'VII.A'  => ['Ahmad Fauzan', 'Bilal Hadi', "Chairul 'Anam", 'Dimas Ramadhan', 'Faiz Hibatullah', 'Hafizh Alim', 'Ilham Maulana', 'Jafar Shodiq', 'Khoirul Umam', 'M. Rizal Bahtiar', 'Nabhan Aufa', 'Rafi Ahmad'],
            'VII.B'  => ['Sofia Azzahra', 'Nadhifah Zahra', 'Naila Putri', 'Zulfa Aini', 'Aisyah Nabila', 'Syifa Humairoh', 'Umi Kulsum', 'Roudhotul Jannah', 'Nihayatul Khoiroh', "Mar'atus Sholihah", 'Kamila Husna', 'Indana Zulfa'],
            'VIII.A' => ['Bima Pratama', 'Candra Wijaya', 'Eko Saputra', 'Ganang Aji', 'Hendra Kusuma', 'Iqbal Mauludi', 'Lukman Hakim', 'Oki Setiawan', 'Rangga Aditya', 'Samsul Arifin', 'Toni Firmansyah', 'Yoga Prasetyo'],
        ];

        $nomorSiswa = 0;
        foreach ($namaSiswa as $namaKelas => $daftar) {
            $kelas = collect($kelasRows)->first(fn($k) => $k->nama_kelas === $namaKelas);
            foreach ($daftar as $absen => $nama) {
                $nomorSiswa++;
                $siswaId = DB::table('siswas')->insertGetId([
                    'nis'          => '2026' . str_pad((string) $nomorSiswa, 3, '0', STR_PAD_LEFT),
                    'nisn'         => '0012345' . str_pad((string) $nomorSiswa, 3, '0', STR_PAD_LEFT),
                    'nama_siswa'   => $nama,
                    'jenis_kelamin' => ($namaKelas === 'VII.B' ? 'P' : 'L'),
                    'tempat_lahir' => 'Bandung',
                    'tanggal_lahir' => '2014-' . str_pad((string) (($absen % 12) + 1), 2, '0', STR_PAD_LEFT) . '-0' . (($absen % 9) + 1),
                    'alamat'       => 'Jl. Pesantren No. ' . ($absen + 1),
                    'nama_ayah'    => 'Bpk. Contoh',
                    'nama_ibu'     => 'Ibu Contoh',
                    'pekerjaan_ortu' => 'Wiraswasta',
                    'no_hp_ortu'   => '0857' . str_pad((string) ($nomorSiswa * 1371), 8, '0', STR_PAD_LEFT),
                    'tahun_masuk'  => '2026',
                    'status'       => 'Aktif',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                DB::table('angkatan_siswas')->insert([
                    'siswa_id'    => $siswaId,
                    'periode_id'  => $periode->id,
                    'kelas_id'    => $kelas->id,
                    'nomor_absen' => $absen + 1,
                    'status'      => 'Aktif',
                    'tanggal_masuk' => '2026-07-13',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }

        $jenisSpp = DB::table('jenis_tagihans')->insertGetId(['nama_tagihan' => 'SPP', 'deskripsi' => 'Iuran bulanan siswa.', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()]);
        $jenisKegiatan = DB::table('jenis_tagihans')->insertGetId(['nama_tagihan' => 'Uang Kegiatan', 'deskripsi' => 'Dana kegiatan semester.', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()]);

        $siswaIds = DB::table('siswas')->pluck('id')->values();
        foreach ($siswaIds as $i => $siswaId) {
            $mod = $i % 10;
            $status = $mod <= 5 ? 'lunas' : ($mod <= 7 ? 'parsial' : 'belum');

            $tagihanSpp = DB::table('tagihans')->insertGetId([
                'siswa_id'        => $siswaId,
                'jenis_tagihan_id' => $jenisSpp,
                'periode_id'      => $periode->id,
                'target_scope'    => 'semua_kelas',
                'keterangan'      => 'SPP (Juli 2026)',
                'nominal'         => 150000,
                'tanggal_jatuh_tempo' => '2026-07-15',
                'status'          => $status,
                'dibuat_oleh'     => $userId['admin'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            if ($status === 'lunas') {
                DB::table('pembayarans')->insert([
                    'tagihan_id'      => $tagihanSpp,
                    'nominal_dibayar' => 150000,
                    'tanggal_bayar'   => '2026-07-' . str_pad((string) (($i % 28) + 1), 2, '0', STR_PAD_LEFT),
                    'metode'          => 'Tunai',
                    'user_id'         => $userId['staf'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            } elseif ($status === 'parsial') {
                DB::table('pembayarans')->insert([
                    'tagihan_id'      => $tagihanSpp,
                    'nominal_dibayar' => 50000,
                    'tanggal_bayar'   => '2026-07-10',
                    'metode'          => 'Tunai',
                    'user_id'         => $userId['staf'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
                DB::table('pembayarans')->insert([
                    'tagihan_id'      => $tagihanSpp,
                    'nominal_dibayar' => 50000,
                    'tanggal_bayar'   => '2026-07-25',
                    'metode'          => 'Tunai',
                    'user_id'         => $userId['staf'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            DB::table('tagihans')->insert([
                'siswa_id'        => $siswaId,
                'jenis_tagihan_id' => $jenisKegiatan,
                'periode_id'      => $periode->id,
                'target_scope'    => 'semua_kelas',
                'keterangan'      => 'Uang Kegiatan Ganjil',
                'nominal'         => 50000,
                'tanggal_jatuh_tempo' => '2026-08-15',
                'status'          => $mod <= 6 ? 'lunas' : 'belum',
                'dibuat_oleh'     => $userId['admin'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        /* LANJUTAN_12_16 */

        $anggaranId = DB::table('anggaran_kebendaharaan')->insertGetId([
            'periode_id'   => $periode->id,
            'nama'         => 'RAB Semester Ganjil 2026/2027',
            'tahun_ajaran' => '2026/2027',
            'status'       => 'final',
            'created_by'   => $userId['admin'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $posData = [
            ['101', 'ATK & Alat Tulis',         12, 'bulan', 250000, 3000000],
            ['102', 'Fotokopi & Penggandaan',   12, 'bulan', 150000, 1800000],
            ['103', 'Listrik & Air',            12, 'bulan', 500000, 6000000],
            ['201', 'Pemeliharaan Gedung',       2, 'kali',  2000000, 4000000],
            ['202', 'Buku Perpustakaan',        50, 'eksemplar', 60000, 3000000],
            ['301', 'Media Pembelajaran',        6, 'paket', 750000, 4500000],
        ];

        $kelompokMap = [
            ['100', 'Pelayanan Umum', 1],
            ['200', 'Sarana & Prasarana', 2],
            ['300', 'Pembelajaran', 3],
        ];
        $kelompokIdByKode = [];
        foreach ($kelompokMap as $km) {
            $kelompokIdByKode[$km[0]] = DB::table('anggaran_kelompok')->insertGetId([
                'anggaran_id' => $anggaranId,
                'kode'        => (int) $km[0],
                'nama'        => $km[1],
                'urutan'      => $km[2],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $posIdByKode = [];
        foreach ($posData as $pd) {
            $kelompokKode = substr($pd[0], 0, 1) . '00';
            $posIdByKode[$pd[0]] = DB::table('anggaran_pos')->insertGetId([
                'anggaran_id'   => $anggaranId,
                'kelompok_id'   => $kelompokIdByKode[$kelompokKode],
                'kode'          => (int) $pd[0],
                'uraian'        => $pd[1],
                'volume'        => $pd[2],
                'satuan'        => $pd[3],
                'harga_satuan'  => $pd[4],
                'jumlah'        => $pd[5],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            list($setengahA, $setengahB) = [intdiv($pd[5], 2), $pd[5] - intdiv($pd[5], 2)];
            DB::table('anggaran_pos_bulan')->insert([
                ['anggaran_pos_id' => $posIdByKode[$pd[0]], 'bulan_fiskal' => 1, 'nominal' => $setengahA],
                ['anggaran_pos_id' => $posIdByKode[$pd[0]], 'bulan_fiskal' => 2, 'nominal' => $setengahB],
            ]);
        }
        $pos101 = $posIdByKode['101'];
        $pos102 = $posIdByKode['102'];
        $pos103 = $posIdByKode['103'];
        $pos201 = $posIdByKode['201'];
        $pos301 = $posIdByKode['301'];

        $rencanaPemasukanId = DB::table('anggaran_pemasukan')->insertGetId([
            'anggaran_id'    => $anggaranId,
            'urutan'         => 1,
            'uraian'         => 'Dana BOS Wustha',
            'volume'         => 12,
            'satuan'         => 'bulan',
            'harga_satuan'   => 1858333,
            'jumlah'         => 22300000,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $buatPencairan = function (string $kode, int $posId, string $tanggal, int $nominal, string $status, ?int $bulan, string $keperluan, ?string $tolak = null, string $jenis = 'rutin') use ($periode, $userId): int {
            $row = [
                'kode'           => $kode,
                'periode_id'     => $periode->id,
                'pos_id'         => $posId,
                'bulan_fiskal'   => $bulan,
                'jenis'          => $jenis,
                'tanggal_aju'    => $tanggal,
                'nominal'        => $nominal,
                'keperluan'      => $keperluan,
                'status'         => $status,
                'diajukan_oleh'  => $userId['bendahara'],
                'disetujui_oleh' => null,
                'disetujui_at'   => null,
                'dibayar_oleh'   => null,
                'dibayar_at'     => null,
                'tolak_alasan'   => $tolak,
                'created_at'     => now(),
                'updated_at'     => now(),
            ];
            if ($status === 'dibayar') {
                $row['dibayar_oleh'] = $userId['staf'];
                $row['dibayar_at']   = now();
            }
            return DB::table('pencairan')->insertGetId($row);
        };

        $spp1 = $buatPencairan('SPP-2026-0001', $pos101, '2026-07-10', 1500000, 'dibayar', 1, 'Pembelian ATK bulan Juli & administrasi kelas');
        $spp2 = $buatPencairan('SPP-2026-0002', $pos102, '2026-07-15', 900000, 'dibayar', 1, 'Fotokopi modul & penggandaan soal');
        $spp3 = $buatPencairan('SPP-2026-0003', $pos103, '2026-07-20', 1000000, 'ditolak', 2, 'Pembayaran tagihan listrik & air', 'Dibayar bulan depan dari pos cadangan. Tolong ajukan ulang.');

        $pos202 = $posIdByKode['202'];
        $spp4 = $buatPencairan('SPP-2026-0004', $pos202, '2026-08-03', 3000000, 'dibayar', 2, 'Modal awal pengadaan buku pegangan (toko)', null, 'modal_toko');
        $spp5 = $buatPencairan('SPP-2026-0005', $pos301, '2026-08-18', 2250000, 'diajukan', 3, 'Pengadaan media pembelajaran kelas VIII');

        $itemSpecs = [
            [$spp1, $pos101, 1, 1500000],
            [$spp2, $pos102, 1, 900000],
            [$spp3, $pos103, 2, 1000000],
            [$spp4, $pos202, 2, 3000000],
            [$spp5, $pos301, 3, 2250000],
        ];
        $pencairanItemId = [];
        foreach ($itemSpecs as $is) {
            $pencairanItemId[$is[0]] = DB::table('pencairan_item')->insertGetId([
                'pencairan_id'   => $is[0],
                'anggaran_pos_id' => $is[1],
                'bulan_fiskal'   => $is[2],
                'nominal'        => $is[3],
            ]);
        }

        DB::table('pemasukan')->insert([
            [
                'periode_id'           => $periode->id,
                'anggaran_pemasukan_id' => $rencanaPemasukanId,
                'uraian'               => 'Dana BOS Wustha',
                'tanggal'              => '2026-07-13',
                'jumlah'               => 11150000,
                'created_by'           => $userId['admin'],
                'created_at'           => now(),
                'updated_at'           => now(),
            ],
            [
                'periode_id'           => $periode->id,
                'anggaran_pemasukan_id' => $rencanaPemasukanId,
                'uraian'               => 'Dana BOS Wustha',
                'tanggal'              => '2026-08-10',
                'jumlah'               => 11150000,
                'created_by'           => $userId['admin'],
                'created_at'           => now(),
                'updated_at'           => now(),
            ],
        ]);

        $buatLpj = function (string $kode, int $posId, ?int $pencairanId, string $tanggal, int $nominal, string $status, string $uraian) use ($periode, $userId): int {
            $divalidasi = $status === 'disetujui' ? $userId['admin'] : null;
            $divalidasiAt = $status === 'disetujui' ? now() : null;
            $lpjId = DB::table('laporan_pengeluaran')->insertGetId([
                'kode'            => $kode,
                'periode_id'      => $periode->id,
                'pos_id'          => $posId,
                'pencairan_id'    => $pencairanId,
                'tanggal'         => $tanggal,
                'nominal'         => $nominal,
                'keterangan'      => $uraian,
                'status'          => $status,
                'dibuat_oleh'     => $userId['bendahara'],
                'divalidasi_oleh' => $divalidasi,
                'divalidasi_at'   => $divalidasiAt,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            DB::table('laporan_pengeluaran_item')->insert([
                'laporan_pengeluaran_id' => $lpjId,
                'anggaran_pos_id'        => $posId,
                'pencairan_item_id'      => isset($pencairanId) ? ($pencairanItemId[$pencairanId] ?? null) : null,
                'uraian'                 => $uraian,
                'nominal'                => $nominal,
            ]);
            return $lpjId;
        };

        $buatLpj('LPJ-2026-0001', $pos101, $spp1, '2026-07-28', 1500000, 'disetujui', 'Belanja ATK & alat tulis kelas (Juli)');
        $buatLpj('LPJ-2026-0002', $pos102, $spp2, '2026-07-30', 900000, 'disetujui', 'Fotokopi & penggandaan modul');
        $buatLpj('LPJ-2026-0003', $pos201, null, '2026-08-05', 2000000, 'diajukan', 'Perbaikan plafon & pengecatan ruang TU');
        $buatLpj('LPJ-2026-0004', $pos101, null, '2026-08-12', 1900000, 'disetujui', 'ATK tambahan ruang guru');
        $buatLpj('LPJ-2026-0005', $pos103, null, '2026-08-14', 1000000, 'ditolak', 'Tagihan wifi (tercatat ganda)');

        DB::table('pinjaman')->insertGetId([
            'kode'             => 'PJ-2026-0001',
            'periode_id'       => $periode->id,
            'pencairan_id'     => null,
            'peminjam_user_id' => $userId['staf'],
            'jumlah'           => 1000000,
            'tanggal'          => '2026-08-01',
            'keperluan'        => 'Dana blusukan pembelian buku penguat',
            'status'           => 'aktif',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        DB::table('pinjaman')->insertGetId([
            'kode'             => 'PJ-2026-0002',
            'periode_id'       => $periode->id,
            'pencairan_id'     => null,
            'peminjam_user_id' => $userId['walikelas'],
            'jumlah'           => 600000,
            'tanggal'          => '2026-08-20',
            'keperluan'        => 'Keperluan blusukan wali kelas VII.A',
            'status'           => 'lunas',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        /* LANJUTAN_T13 */

        $barangDef = [
            ['BRG-0001', 'Buku Tulis',            'Pcs',  3500,  5000,  'Buku tulis 58 hal'],
            ['BRG-0002', 'Bolpoin Standard',      'Pcs',  2500,  3500,  'Bolpoin biru/hitam'],
            ['BRG-0003', 'Pensil 2B',             'Pcs',  2000,  3000,  'Pensil kayu 2B'],
            ['BRG-0004', 'Tinta Spidol',          'Pcs',  12000, 15000, 'Spidol snowman besar'],
            ['BRG-0005', 'Buku Paket Fiqh Kelas VII', 'Pcs', 45000, 55000, 'Buku paket fiqh'],
            ['BRG-0006', 'Kitab Alfiyah Ibnu Malik', 'Pcs', 80000, 95000, 'Kitab pegangan santri'],
            ['BRG-0007', 'Buku Paket B. Arab IX', 'Pcs',  42000, 52000, 'Buku paket B. Arab'],
            ['BRG-0008', 'Sampul Plastik',        'Pcs',  1000,  2000,  'Sampul buku'],
        ];
        $barangId = [];
        foreach ($barangDef as $b) {
            $barangId[$b[0]] = DB::table('barang')->insertGetId([
                'kode'        => $b[0],
                'nama'        => $b[1],
                'satuan'      => $b[2],
                'harga_beli'  => $b[3],
                'harga_jual'  => $b[4],
                'is_active'   => true,
                'keterangan'  => $b[5],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        $pbId = DB::table('pembelian_barang')->insertGetId([
            'kode'         => 'PB-2026-0001',
            'pencairan_id' => $spp4,
            'tanggal'      => '2026-08-05',
            'total'        => 0,
            'keterangan'   => 'Stok awal toko buku — modal dari SPP-2026-0004',
            'created_by'   => $userId['staf'],
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        $pembelianItems = [
            ['BRG-0001', 60,  3500,  5000],
            ['BRG-0002', 50,  2500,  3500],
            ['BRG-0003', 30,  2000,  3000],
            ['BRG-0004', 10,  12000, 15000],
            ['BRG-0005', 25,  45000, 55000],
            ['BRG-0006', 10,  80000, 95000],
            ['BRG-0008', 100, 1000,  2000],
        ];
        $totalPembelian = 0;
        foreach ($pembelianItems as $pi) {
            $totalPembelian += $pi[1] * $pi[2];
            DB::table('pembelian_barang_item')->insert([
                'pembelian_barang_id' => $pbId,
                'barang_id'           => $barangId[$pi[0]],
                'qty'                 => $pi[1],
                'harga_beli'          => $pi[2],
                'harga_jual'          => $pi[3],
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
        DB::table('pembelian_barang')->where('id', $pbId)->update(['total' => $totalPembelian, 'updated_at' => now()]);

        $kelasViiA = collect($kelasRows)->first(fn($k) => $k->nama_kelas === 'VII.A');
        $kelasViiiA = collect($kelasRows)->first(fn($k) => $k->nama_kelas === 'VIII.A');
        $siswaViiA = DB::table('siswas')
            ->join('angkatan_siswas', 'angkatan_siswas.siswa_id', '=', 'siswas.id')
            ->where('angkatan_siswas.kelas_id', $kelasViiA->id)
            ->where('angkatan_siswas.periode_id', $periode->id)
            ->orderBy('angkatan_siswas.nomor_absen')
            ->pluck('siswas.id')
            ->values();

        $buatDistribusi = function (string $kode, $waliGuru, $kelas, string $tanggal, string $ket, array $items) use ($userId, $barangId): int {
            $dsbId = DB::table('distribusi_barang')->insertGetId([
                'kode'          => $kode,
                'wali_kelas_id' => $waliGuru->id,
                'kelas_id'      => $kelas->id,
                'tanggal'       => $tanggal,
                'keterangan'    => $ket,
                'created_by'    => $userId['staf'],
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            foreach ($items as $it) {
                DB::table('distribusi_barang_item')->insert([
                    'distribusi_barang_id' => $dsbId,
                    'barang_id'            => $barangId[$it[0]],
                    'qty'                  => $it[1],
                    'harga_jual'           => $it[2],
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }
            return $dsbId;
        };

        $dsb1 = $buatDistribusi('DSB-2026-0001', $guruByNig['1007'], $kelasViiA, '2026-08-10', 'Buku pegangan kelas VII.A', [
            ['BRG-0001', 12, 5000],
            ['BRG-0002', 12, 3500],
            ['BRG-0005', 12, 55000],
            ['BRG-0008', 24, 2000],
        ]);
        $buatDistribusi('DSB-2026-0002', $guruByNig['1008'], $kelasViiiA, '2026-08-12', 'Buku pegangan kelas VIII.A', [
            ['BRG-0004', 2, 15000],
            ['BRG-0008', 20, 2000],
            ['BRG-0001', 10, 5000],
        ]);

        $penjualanDef = [
            [0, 'BRG-0005', 1, 55000, 'lunas'],
            [1, 'BRG-0001', 2, 5000, 'lunas'],
            [1, 'BRG-0002', 1, 3500, 'lunas'],
            [2, 'BRG-0006', 1, 95000, 'belum'],
            [3, 'BRG-0008', 4, 2000, 'lunas'],
            [4, 'BRG-0001', 1, 5000, 'belum'],
            [5, 'BRG-0005', 1, 55000, 'lunas'],
            [5, 'BRG-0008', 2, 2000, 'lunas'],
            [6, 'BRG-0002', 2, 3500, 'belum'],
            [7, 'BRG-0006', 1, 95000, 'lunas'],
        ];
        $totalLunas = 0;
        foreach ($penjualanDef as $pdj) {
            $status = $pdj[4];
            if ($status === 'lunas') {
                $totalLunas += $pdj[2] * $pdj[3];
            }
            DB::table('penjualan_barang')->insert([
                'distribusi_barang_id' => $dsb1,
                'siswa_id'             => $siswaViiA[$pdj[0]],
                'barang_id'            => $barangId[$pdj[1]],
                'qty'                  => $pdj[2],
                'harga_jual'           => $pdj[3],
                'tanggal'              => '2026-08-1' . (($pdj[0] % 9) + 1),
                'status'               => $status,
                'dicatat_oleh'         => $userId['staf'],
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        $setoranId = DB::table('setoran_barang')->insertGetId([
            'kode'          => 'STD-2026-0001',
            'distribusi_barang_id' => $dsb1,
            'wali_kelas_id' => $guruByNig['1007']->id,
            'tanggal'       => '2026-08-15',
            'total'         => $totalLunas,
            'keterangan'    => 'Setoran penjualan buku tahap 1 (VII.A)',
            'created_by'    => $userId['staf'],
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        DB::table('pemasukan')->insert([
            'periode_id'           => $periode->id,
            'anggaran_pemasukan_id' => null,
            'setoran_barang_id'    => $setoranId,
            'uraian'               => 'Hasil Penjualan Buku (Setoran VII.A)',
            'tanggal'              => '2026-08-15',
            'jumlah'               => $totalLunas,
            'created_by'           => $userId['staf'],
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        DB::table('pengumuman')->insert([
            'judul'          => 'Sambutan Tahun Ajaran Baru 2026/2027',
            'isi'            => 'Selamat datang di aplikasi Smart TU. Data pada demo ini adalah data contoh dan akan di-reset otomatis setiap hari pukul 03.00 WIB.',
            'warna'          => 'emerald',
            'gambar'         => null,
            'aktif'          => true,
            'tanggal_mulai'  => '2026-07-13',
            'tanggal_selesai' => '2026-12-19',
            'created_by'     => $userId['admin'],
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
    }
}