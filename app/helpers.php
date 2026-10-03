<?php

if (!function_exists('map_hari')) {
    function map_hari(?string $englishDay): ?string
    {
        $map = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Ahad',
        ];

        return $map[$englishDay] ?? $englishDay;
    }
}

if (!function_exists('get_periode_aktif')) {
    function get_periode_aktif(): ?\App\Models\Periode
    {
        return \App\Models\Periode::where('is_active', true)->first();
    }
}

if (!function_exists('rupiah_to_int')) {
    /**
     * Ubah input rupiah "1.500.000" / "1 500 000" / "1500000" menjadi integer.
     * Bila desimal ada (menggunakan koma), bagian desimal dibuang.
     */
    function rupiah_to_int($value): int
    {
        $s = (string) $value;
        if (str_contains($s, ',')) {
            $s = substr($s, 0, strpos($s, ','));
        }
        $s = str_replace(['.', ' ', 'Rp', 'rp'], '', $s);
        return (int) $s;
    }
}

if (!function_exists('js_q')) {
    /**
     * Escape nilai untuk disisipkan aman ke dalam string JavaScript ber-quote-tunggal
     * di dalam atribut HTML ber-quote-ganda, mis. onclick="fn('{{ js_q($nama) }}')".
     * Mencegah injeksi skrip via tanda kutip / tag.
     */
    function js_q($value): string
    {
        $s = (string)$value;
        // 1) Sembunyikan backslash & kutip tunggal agar aman pada level JS
        $s = str_replace(['\\', "'"], ['\\\\', "\\'"], $s);
        // 2) Escape HTML supaya aman pada level atribut (kutip ganda, tag, &, dll.)
        return e($s);
    }
}

if (!function_exists('bulan_fiskal_list')) {
    /**
     * Bulan anggaran (fiskal) aplikasi, dimulai Juli: 1=Juli ... 12=Juni.
     * Dipakai pada pencairan per bulan & alokasi per pos.
     */
    function bulan_fiskal_list(): array
    {
        return [
            1  => 'JULI',
            2  => 'AGUSTUS',
            3  => 'SEPTEMBER',
            4  => 'OKTOBER',
            5  => 'NOVEMBER',
            6  => 'DESEMBER',
            7  => 'JANUARI',
            8  => 'FEBRUARI',
            9  => 'MARET',
            10 => 'APRIL',
            11 => 'MEI',
            12 => 'JUNI',
        ];
    }
}

if (!function_exists('bulan_fiskal_label')) {
    function bulan_fiskal_label(int $bulan): string
    {
        return bulan_fiskal_list()[$bulan] ?? 'Bulan ' . $bulan;
    }
}

if (!function_exists('fiskal_bulan')) {
    /**
     * Bulan fiskal (1=Juli ... 12=Juni) dari sebuah tanggal kalender.
     */
    function fiskal_bulan($tanggal): int
    {
        $cal = (int) \Illuminate\Support\Carbon::parse($tanggal)->format('n');

        return $cal >= 7 ? $cal - 6 : $cal + 6;
    }
}

if (!function_exists('fiskal_tahun')) {
    /**
     * Tahun kalender awal tahun ajaran fiskal (mis. Juli 2026 -> 2026, Feb 2027 -> 2026).
     */
    function fiskal_tahun($tanggal): int
    {
        $c = \Illuminate\Support\Carbon::parse($tanggal);

        return (int) $c->format('n') >= 7 ? (int) $c->format('Y') : (int) $c->format('Y') - 1;
    }
}

if (!function_exists('fiskal_range')) {
    /**
     * Rentang tanggal kalender [mulai, selesai] untuk bulan fiskal + tahun fiskal.
     *
     * @return array{0: \Illuminate\Support\Carbon, 1: \Illuminate\Support\Carbon}
     */
    function fiskal_range(int $bulan, int $tahun): array
    {
        $bulan = max(1, min(12, $bulan));
        $mulai = \Illuminate\Support\Carbon::create($tahun, 7, 1)->addMonths($bulan - 1)->startOfMonth();

        return [$mulai->copy()->startOfDay(), $mulai->copy()->endOfMonth()];
    }
}
