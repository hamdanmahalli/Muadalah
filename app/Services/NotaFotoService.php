<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Kompresi foto nota (buku kas) menjadi JPEG di bawah 1 MB via GD.
 * Hasil disimpan di disk public_uploads (public/uploads) sehingga langsung
 * bisa diakses publik tanpa symlink.
 */
class NotaFotoService
{
    private int $maxBytes = 1_000_000;

    public function simpan(UploadedFile $file, string $periodeTahun = 'x'): string
    {
        $src = $this->bukaGambar($file);
        if (!$src) {
            throw new \InvalidArgumentException('File harus berupa gambar JPG, PNG, atau WebP.');
        }

        $w = imagesx($src);
        $h = imagesy($src);
        $maxWidth = 1600;

        if ($w > $maxWidth) {
            $nh = (int) round($h * $maxWidth / $w);
            $dst = imagecreatetruecolor($maxWidth, $nh);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $maxWidth, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }

        $bytes = null;
        for ($q = 88; $q >= 40; $q -= 8) {
            ob_start();
            imagejpeg($src, null, $q);
            $buf = ob_get_clean();
            if (strlen((string) $buf) <= $this->maxBytes) {
                $bytes = $buf;
                break;
            }
        }

        if ($bytes === null) {
            ob_start();
            imagejpeg($src, null, 55);
            $bytes = ob_get_clean();
        }
        imagedestroy($src);

        $rel = 'nota/' . $periodeTahun . '/' . now()->format('Y-m-d') . '/'
            . 'kas-' . now()->format('Ymd-His') . '-' . Str::lower(Str::random(6)) . '.jpg';

        Storage::disk('public_uploads')->put($rel, $bytes);

        return $rel;
    }

    public function hapus(?string $rel): void
    {
        if (!$rel) {
            return;
        }
        Storage::disk('public_uploads')->delete($rel);
    }

    private function bukaGambar(UploadedFile $file): mixed
    {
        $mime = $file->getMimeType();

        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png'               => @imagecreatefrompng($file->getRealPath()),
            'image/webp'              => @imagecreatefromwebp($file->getRealPath()),
            default                   => null,
        };
    }
}