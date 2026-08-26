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
        $selectedTables = $request->input('tables', []);
        
        if(empty($selectedTables)) {
            return back()->with('error', 'Gagal! Anda harus memilih minimal satu tabel untuk di-backup.');
        }

        $dbName = config('database.connections.' . config('database.default') . '.database');
        $dbUser = config('database.connections.' . config('database.default') . '.username');
        $dbHost = config('database.connections.' . config('database.default') . '.host', '127.0.0.1');
        $dbPort = config('database.connections.' . config('database.default') . '.port', '5432');
        $dbPass = config('database.connections.' . config('database.default') . '.password');

        $fileName = 'Backup_SmartPesantren_' . date('Y-m-d_H-i-s') . '.backup';
        $filePath = storage_path('app/public/' . $fileName);

        $tableArgs = '';
        foreach ($selectedTables as $table) {
            $tableArgs .= " -t public." . escapeshellarg($table);
        }

        // Menggunakan -Fc (Custom Format) alih-alih plain text
        $pgDump = $this->getPgPath('pg_dump');
        $command = "\"{$pgDump}\" -h {$dbHost} -p {$dbPort} -U {$dbUser} {$tableArgs} -Fc {$dbName} -f \"{$filePath}\"";

        $process = Process::fromShellCommandline($command);
        $process->setEnv(['PGPASSWORD' => $dbPass]);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            return back()->with('error', 'Terjadi kesalahan sistem saat membuat backup: ' . $process->getErrorOutput());
        }

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    // 3. PROSES IMPOR (RESTORE) - Menggunakan pg_restore
    public function importSql(Request $request)
    {
        $request->validate([
            'file_sql' => 'required|file'
        ]);

        $file = $request->file('file_sql');
        $filePath = $file->getRealPath();

        $dbName = config('database.connections.' . config('database.default') . '.database');
        $dbUser = config('database.connections.' . config('database.default') . '.username');
        $dbHost = config('database.connections.' . config('database.default') . '.host', '127.0.0.1');
        $dbPort = config('database.connections.' . config('database.default') . '.port', '5432');
        $dbPass = config('database.connections.' . config('database.default') . '.password');

        // Menggunakan pg_restore untuk format -Fc
        $pgRestore = $this->getPgPath('pg_restore');
        $command = "\"{$pgRestore}\" -h {$dbHost} -p {$dbPort} -U {$dbUser} -d {$dbName} --clean --if-exists \"{$filePath}\"";

        $process = Process::fromShellCommandline($command);
        $process->setEnv(['PGPASSWORD' => $dbPass]);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            return back()->with('error', 'Gagal melakukan Restore Database: ' . $process->getErrorOutput());
        }

        return back()->with('sukses', 'Alhamdulillah! Database berhasil dipulihkan.');
    }
}