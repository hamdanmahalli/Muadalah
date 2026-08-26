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
