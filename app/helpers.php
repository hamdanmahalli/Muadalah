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

if (!function_exists('js_q')) {
    /**
     * Escape nilai untuk disisipkan aman ke dalam string JavaScript ber-quote-tunggal
     * di dalam atribut HTML ber-quote-ganda, mis. onclick="fn('{{ js_q($nama) }}')".
     * Mencegah injeksi skrip via tanda kutip / tag.
     */
    function js_q($value): string
    {
        $s = (string)$value;
        // 1) Sembunyikan backslash & kutip tunggal agar tetap aman pada level JS
        $s = str_replace(['\\', "'"], ['\\\\', "\\'"], $s);
        // 2) Escape HTML supaya aman pada level atribut (kutip ganda, tag, &, dll.)
        return e($s);
    }
}
