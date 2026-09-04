<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\JadwalHarian;
use App\Models\User;
use App\Services\JadwalMatrixService;
use Illuminate\Support\Facades\Hash;

/**
 * Logika autentikasi & layanan terkait identitas pengguna.
 *
 * SRP: Satu tanggung jawab — proses login, "intip jadwal" (tanpa login),
 * dan perubahan password. Controller hanya meneruskan request & respons.
 */
class AuthService
{
    public function __construct(
        protected JadwalMatrixService $matrix,
        protected AuthenticatedGuruService $guruService
    ) {}

    /**
     * Cari user berdasarkan email atau username.
     */
    public function temukanUser(string $loginId): ?User
    {
        $loginType = filter_var($loginId, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        return User::where($loginType, $loginId)->first();
    }

    /**
     * Siapkan data "intip jadwal" hari ini untuk sebuah identitas (tanpa login).
     *
     * @return array{ok: bool, pesan?: string, ...}
     */
    public function intipJadwalData(string $loginId): array
    {
        $loginId = trim($loginId);
        if ($loginId === '') {
            return ['ok' => false, 'pesan' => 'Identitas tidak ditemukan.'];
        }

        $user = $this->temukanUser($loginId);
        if (!$user) {
            return ['ok' => false, 'pesan' => 'Identitas tidak ditemukan.'];
        }

        // Cari data guru dari akun tersebut
        $guru = Guru::where('nama_guru', $user->name)
                    ->orWhere('nig', $user->username)
                    ->first();

        // Bukan akun guru (Admin/TU) -> tanpa jadwal mengajar
        if (!$guru) {
            return ['ok' => false, 'pesan' => 'Akun tanpa jadwal mengajar.'];
        }

        $hariIni = map_hari(\Carbon\Carbon::now()->format('l'));
        $periodeAktif = get_periode_aktif();
        $tahunAjaran = $periodeAktif ? $periodeAktif->tahun_ajaran : null;

        $jadwalMentah = JadwalHarian::with(['kelas', 'pelajaran'])
                            ->where('guru_id', $guru->id)
                            ->where('hari', 'ilike', $hariIni)
                            ->where('tahun_ajaran', $tahunAjaran)
                            ->orderBy('jam_ke')
                            ->get();

        $jadwal = $this->matrix->blokJadwalIntip($jadwalMentah);

        return [
            'ok'        => true,
            'nama_guru' => $guru->nama_guru,
            'hari'      => $hariIni,
            'tanggal'   => \Carbon\Carbon::now()->format('d/m/Y'),
            'jadwal'    => $jadwal,
        ];
    }

    /**
     * Ubah password pengguna dengan urutan validasi lama/baru/konfirmasi/minimal.
     *
     * @return array{pesan: string, status?: int}
     */
    public function gantiPassword(User $user, ?string $passwordLama, ?string $passwordBaru, ?string $konfirmasi): ?array
    {
        if (!Hash::check($passwordLama, $user->password)) {
            return ['pesan' => 'Gagal! Password lama Anda salah.', 'status' => 400];
        }

        if ($passwordBaru !== $konfirmasi) {
            return ['pesan' => 'Gagal! Konfirmasi password baru tidak cocok.', 'status' => 400];
        }

        if (strlen($passwordBaru) < 6) {
            return ['pesan' => 'Gagal! Password baru minimal 6 karakter.', 'status' => 400];
        }

        $user->update(['password' => Hash::make($passwordBaru)]);

        return ['pesan' => 'Alhamdulillah, Password berhasil diperbarui!'];
    }
}
