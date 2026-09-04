<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class DatabaseManagerController extends Controller
{
    // Fungsi pembantu untuk mendeteksi path binari PostgreSQL di Windows
    private function getPgPath($tool)
    {
        // Sesuaikan versi '16' dengan versi PostgreSQL yang terinstal di komputer Anda
        $commonPaths = [
            "C:\\Program Files\\PostgreSQL\\18\\bin\\{$tool}.exe",
            "C:\\Program Files\\PostgreSQL\\15\\bin\\{$tool}.exe",
            "C:\\Program Files\\PostgreSQL\\14\\bin\\{$tool}.exe",
            "C:\\xampp\\postgresql\\bin\\{$tool}.exe",
            $tool // Fallback ke global path jika sudah didaftarkan di environment
        ];

        foreach ($commonPaths as $path) {
            if (file_exists($path) || $path === $tool) {
                return $path;
            }
        }

        return $tool;
    }

    // 1. TAMPILAN HALAMAN BACKUP & RESTORE
    public function index()
    {
        $tables = DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
        return view('admin.backup-restore', compact('tables'));
    }

    // 2. PROSES EKSPOR (BACKUP) - Custom Format (.backup / .sql)
    public function exportSql(Request $request)
    {
        $request->validate([
            'tables'   => 'required|array|min:1',
            'tables.*' => 'string|max:100',
        ]);

        $selectedTables = $request->input('tables', []);
        
        if (empty($selectedTables)) {
            return back()->with('error', 'Gagal! Anda harus memilih minimal satu tabel untuk di-backup.');
        }

        $dbName = config('database.connections.' . config('database.default') . '.database');
        $dbUser = config('database.connections.' . config('database.default') . '.username');
        $dbHost = config('database.connections.' . config('database.default') . '.host', '127.0.0.1');
        $dbPort = config('database.connections.' . config('database.default') . '.port', '5432');
        $dbPass = config('database.connections.' . config('database.default') . '.password');

        // Simpan backup di storage privat (BUKAN public) agar tidak terekspos via symlink
        $fileName = 'Backup_SmartPesantren_' . date('Y-m-d_H-i-s') . '.backup';
        $privatePath = storage_path('app/private');
        if (!is_dir($privatePath)) {
            mkdir($privatePath, 0755, true);
        }
        $filePath = $privatePath . '/' . $fileName;

        // Gunakan bentuk array argumen (bukan string shell) untuk mencegah injection
        $command = array_merge(
            [$this->getPgPath('pg_dump'), '-h', $dbHost, '-p', $dbPort, '-U', $dbUser],
            ['-Fc'],
            collect($selectedTables)->flatMap(fn($t) => ['-t', 'public.' . $t])->all(),
            [$dbName, '-f', $filePath]
        );

        // Sanitasi setiap nilai yang disisipkan ke argumen (pertahanan berlapis)
        foreach ($command as $k => $v) {
            $command[$k] = escapeshellarg($v === $dbHost ? $dbHost : $v);
        }

        $process = new Process($command);
        $process->setEnv(['PGPASSWORD' => $dbPass]);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            return back()->with('error', 'Terjadi kesalahan sistem saat membuat backup.');
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    // 3. PROSES IMPOR (RESTORE) - Menggunakan pg_restore
    public function importSql(Request $request)
    {
        $request->validate([
            'file_sql' => 'required|file|mimes:sql,plain,txt,backup|max:204800',
        ]);

        $file = $request->file('file_sql');
        $filePath = $file->getRealPath();

        $dbName = config('database.connections.' . config('database.default') . '.database');
        $dbUser = config('database.connections.' . config('database.default') . '.username');
        $dbHost = config('database.connections.' . config('database.default') . '.host', '127.0.0.1');
        $dbPort = config('database.connections.' . config('database.default') . '.port', '5432');
        $dbPass = config('database.connections.' . config('database.default') . '.password');

        // Menggunakan pg_restore untuk format -Fc (bentuk array argumen, tanpa string shell)
        $command = [
            $this->getPgPath('pg_restore'),
            '-h', $dbHost,
            '-p', $dbPort,
            '-U', $dbUser,
            '-d', $dbName,
            '--clean',
            '--if-exists',
            $filePath,
        ];

        $process = new Process($command);
        $process->setEnv(['PGPASSWORD' => $dbPass]);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            return back()->with('error', 'Gagal melakukan Restore Database.');
        }

        return back()->with('sukses', 'Alhamdulillah! Database berhasil dipulihkan.');
    }
}