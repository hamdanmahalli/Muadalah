<?php

namespace App\Services;

use App\Models\Guru;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GuruKelengkapanService
{
    /**
     * Aturan validasi untuk seluruh bidang data dasar + data kelengkapan.
     */
    public function rules(): array
    {
        return [
            // Data dasar (master guru)
            'nama_guru'              => 'nullable|string|max:255',
            'nip'                    => 'nullable|string|max:50',
            'no_hp'                  => 'nullable|string|max:30',
            'gender'                 => 'nullable|string|max:20',
            'alamat'                 => 'nullable|string',
            'status'                 => 'nullable|string|max:20',
            'tempat_lahir'           => 'nullable|string|max:100',
            'tanggal_lahir'          => 'nullable|date',
            'pendidikan_terakhir'    => 'nullable|string|max:100',

            // Data kelengkapan (gabungan, termasuk jarak)
            'jarak_km'               => 'nullable|numeric|min:0|max:9999.99',
            'nik'                    => 'nullable|string|max:16',
            'nik_kk'                 => 'nullable|string|max:16',
            'agama'                  => 'nullable|string|max:50',
            'kewarganegaraan'        => 'nullable|string|max:50',
            'rt'                     => 'nullable|string|max:10',
            'rw'                     => 'nullable|string|max:10',
            'kelurahan'              => 'nullable|string|max:100',
            'kecamatan'              => 'nullable|string|max:100',
            'kabupaten_kota'         => 'nullable|string|max:100',
            'kode_pos'               => 'nullable|string|max:5',
            'nuptk'                  => 'nullable|string|max:30',
            'nrg'                    => 'nullable|string|max:30',
            'status_kepegawaian'     => 'nullable|string|max:50',
            'golongan_ruang'         => 'nullable|string|max:20',
            'program_studi'          => 'nullable|string|max:100',
            'perguruan_tinggi'       => 'nullable|string|max:150',
            'tahun_lulus'            => 'nullable|digits:4',
            'status_sertifikasi'     => 'nullable|boolean',
            'no_sertifikat_pendidik' => 'nullable|string|max:50',
            'tahun_sertifikasi'      => 'nullable|digits:4',
            'tmt_kerja'              => 'nullable|date',
            'no_sk_pengangkatan'     => 'nullable|string|max:100',
            'tgl_sk_pengangkatan'    => 'nullable|date',
            'no_sk_pembagian_tugas'  => 'nullable|string|max:100',
            'npwp'                   => 'nullable|string|max:30',
            'nama_bank'              => 'nullable|string|max:50',
            'no_rekening'            => 'nullable|string|max:30',
            'atas_nama_rekening'     => 'nullable|string|max:150',
            'bpjs_ketenagakerjaan'   => 'nullable|string|max:30',
            'bpjs_kesehatan'         => 'nullable|string|max:30',
            'status_menikah'         => 'nullable|string|max:20',
            'nama_pasangan'          => 'nullable|string|max:150',
            'jumlah_anak'            => 'nullable|integer|min:0|max:99',
        ];
    }

    /**
     * Validasi, normalisasi, lalu simpan bidang yang diizinkan ke guru.
     *
     * @param  array  $data        data mentah dari request
     * @param  array  $fields      daftar bidang yang boleh ikut disimpan
     * @param  bool   $syncJabatan ikut selaraskan relasi jabatan (menggunakan kunci jabatan_ids)
     */
    public function simpan(Guru $guru, array $data, array $fields, bool $syncJabatan = false): void
    {
        $validator = Validator::make($data, $this->rules());

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $bersih = [];

        foreach ($fields as $field) {
            if (array_key_exists($field, $validator->validated())) {
                $bersih[$field] = $validator->validated()[$field];
            }
        }

        // Normalisasi: string kosong → null (agar DB rapi)
        foreach ($bersih as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $bersih[$key] = null;
            }
        }

        if (array_key_exists('status_sertifikasi', $bersih)) {
            $bersih['status_sertifikasi'] = (bool) $bersih['status_sertifikasi'];
        }
        if (array_key_exists('kewarganegaraan', $bersih)) {
            $bersih['kewarganegaraan'] = $bersih['kewarganegaraan'] ?: 'WNI';
        }
        if (array_key_exists('jumlah_anak', $bersih)) {
            $bersih['jumlah_anak'] = $bersih['jumlah_anak'] ?? 0;
        }

        $guru->update($bersih);

        if ($syncJabatan && array_key_exists('jabatan_ids', $data)) {
            $guru->jabatans()->sync($data['jabatan_ids'] ?? []);
        }
    }
}