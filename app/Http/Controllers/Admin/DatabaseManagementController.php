<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\DistanceCategory;
use App\Models\Contact;
use App\Models\BibSetting;
use App\Models\CertificateTemplate;
use App\Models\Gallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\Artisan;

class DatabaseManagementController extends Controller
{
    private const ALLOWED_TABLES = [
        'participants',
        'events',
        'distance_categories',
        'contacts',
        'galleries',
        'certificate_templates',
    ];

    private const BACKUP_FILENAME_PATTERN = '/^backup_[a-z]+_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/';

    public function index(): View
    {
        $tables = [
            'events' => [
                'name' => 'Events',
                'count' => Event::count(),
                'description' => 'Data event lari'
            ],
            'participants' => [
                'name' => 'Participants',
                'count' => Participant::count(),
                'description' => 'Data peserta'
            ],
            'distance_categories' => [
                'name' => 'Distance Categories',
                'count' => DistanceCategory::count(),
                'description' => 'Kategori jarak'
            ],
            'contacts' => [
                'name' => 'Contacts',
                'count' => Contact::count(),
                'description' => 'Data kontak'
            ],
            'galleries' => [
                'name' => 'Galleries',
                'count' => Gallery::count(),
                'description' => 'Data galeri foto'
            ],
            'certificate_templates' => [
                'name' => 'Certificate Templates',
                'count' => CertificateTemplate::count(),
                'description' => 'Template sertifikat'
            ],
        ];

        $backups = $this->getBackups();

        return view('admin.database.index', compact('tables', 'backups'));
    }

    public function backup(Request $request): RedirectResponse
    {
        try {
            $type = $request->input('type', 'full');
            $filename = 'backup_' . $type . '_' . date('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/backups/' . $filename);

            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            if (config('database.default') === 'sqlite') {
                $source = config('database.connections.sqlite.database');
                $lock = fopen($source, 'r');
                if ($lock) {
                    flock($lock, LOCK_SH);
                    copy($source, $path);
                    flock($lock, LOCK_UN);
                    fclose($lock);
                } else {
                    throw new \Exception('Tidak bisa mengakses database');
                }
            } else {
                $dbName = config('database.connections.mysql.database');

                $sql = "-- Backup: {$filename}\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
                $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

                $tables = DB::select('SHOW TABLES');
                $tableKey = "Tables_in_{$dbName}";

                foreach ($tables as $tableObj) {
                    $table = $tableObj->$tableKey;

                    $createResult = DB::select("SHOW CREATE TABLE `{$table}`");
                    $createStmt = $createResult[0]->{'Create Table'};
                    $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    $sql .= "{$createStmt};\n\n";

                    $rows = DB::select("SELECT * FROM `{$table}`");
                    if (empty($rows)) {
                        continue;
                    }

                    $columns = array_keys(get_object_vars($rows[0]));
                    $columnList = '`' . implode('`, `', $columns) . '`';

                    $chunks = array_chunk($rows, 100);
                    foreach ($chunks as $chunk) {
                        $values = [];
                        foreach ($chunk as $row) {
                            $rowValues = [];
                            foreach ($columns as $col) {
                                $val = $row->$col;
                                if ($val === null) {
                                    $rowValues[] = 'NULL';
                                } else {
                                    $rowValues[] = DB::connection()->getPdo()->quote($val);
                                }
                            }
                            $values[] = '(' . implode(', ', $rowValues) . ')';
                        }
                        $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n" . implode(",\n", $values) . ";\n";
                    }
                    $sql .= "\n";
                }

                $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

                file_put_contents($path, $sql);
            }

            return redirect()
                ->route('admin.database.index')
                ->with('success', 'Backup database berhasil dibuat: ' . $filename);
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.database.index')
                ->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    public function restore(Request $request): RedirectResponse
    {
        try {
            $uploadedFile = $request->file('sql_file');

            if ($uploadedFile) {
                if ($uploadedFile->getClientOriginalExtension() !== 'sql') {
                    throw new \Exception('File harus berekstensi .sql');
                }

                if (! $uploadedFile->isValid()) {
                    throw new \Exception('File upload tidak valid');
                }

                $path = $uploadedFile->getRealPath();
                $filename = $uploadedFile->getClientOriginalName();

                if (filesize($path) === 0) {
                    throw new \Exception('File SQL kosong');
                }
            } else {
                $filename = $request->input('backup_file');

                if (empty($filename)) {
                    return redirect()
                        ->route('admin.database.index')
                        ->with('error', 'Pilih file backup atau upload file SQL.');
                }

                $path = $this->validateBackupPath($filename);

                if ($path === null) {
                    return redirect()
                        ->route('admin.database.index')
                        ->with('error', 'File backup tidak valid.');
                }
            }

            if (config('database.default') === 'sqlite') {
                $target = config('database.connections.sqlite.database');

                $currentBackup = $target . '.backup_' . date('Y-m-d_H-i-s');
                copy($target, $currentBackup);

                $lock = fopen($target, 'w');
                if ($lock) {
                    flock($lock, LOCK_EX);
                    copy($path, $target);
                    flock($lock, LOCK_UN);
                    fclose($lock);
                } else {
                    throw new \Exception('Tidak bisa mengakses database');
                }
            } else {
                $sqlContent = file_get_contents($path);

                if ($sqlContent === false || $sqlContent === '') {
                    throw new \Exception('File SQL kosong atau tidak bisa dibaca');
                }

                DB::statement('SET FOREIGN_KEY_CHECKS = 0');

                $statements = explode(";\n", $sqlContent);
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if ($statement === '' || str_starts_with($statement, '--')) {
                        continue;
                    }

                    try {
                        DB::statement($statement);
                    } catch (\Exception $e) {
                        if (! str_contains($e->getMessage(), 'Unknown table')) {
                            throw $e;
                        }
                    }
                }

                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }

            return redirect()
                ->route('admin.database.index')
                ->with('success', 'Database berhasil direstore dari: ' . $filename);
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.database.index')
                ->with('error', 'Gagal restore database: ' . $e->getMessage());
        }
    }

    public function delete(Request $request): RedirectResponse
    {
        try {
            $tables = $request->input('tables', []);

            if (empty($tables)) {
                return redirect()
                    ->route('admin.database.index')
                    ->with('error', 'Pilih minimal satu tabel untuk dihapus');
            }

            $invalid = array_diff($tables, self::ALLOWED_TABLES);
            if (! empty($invalid)) {
                return redirect()
                    ->route('admin.database.index')
                    ->with('error', 'Tabel yang dipilih tidak valid.');
            }

            DB::transaction(function () use ($tables): void {
                foreach ($tables as $table) {
                    match ($table) {
                        'participants' => Participant::query()->delete(),
                        'events' => Event::query()->delete(),
                        'distance_categories' => DistanceCategory::query()->delete(),
                        'contacts' => Contact::query()->delete(),
                        'galleries' => (function () {
                            Gallery::query()->delete();
                            Storage::disk('public')->deleteDirectory('galleries');
                        })(),
                        'certificate_templates' => CertificateTemplate::query()->delete(),
                    };
                }
            });

            return redirect()
                ->route('admin.database.index')
                ->with('success', 'Data berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.database.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $path = $this->validateBackupPath($filename);

        if ($path === null) {
            return redirect()
                ->route('admin.database.index')
                ->with('error', 'File backup tidak valid.');
        }

        return response()->download($path);
    }

    public function destroyBackup(string $filename): RedirectResponse
    {
        try {
            $path = $this->validateBackupPath($filename);

            if ($path === null) {
                return redirect()
                    ->route('admin.database.index')
                    ->with('error', 'File backup tidak valid.');
            }

            unlink($path);

            return redirect()
                ->route('admin.database.index')
                ->with('success', 'Backup berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.database.index')
                ->with('error', 'Gagal menghapus backup: ' . $e->getMessage());
        }
    }

    private function validateBackupPath(string $filename): ?string
    {
        if (! preg_match(self::BACKUP_FILENAME_PATTERN, $filename)) {
            return null;
        }

        $path = storage_path('app/backups/' . basename($filename));

        if (! file_exists($path) || ! str_starts_with(realpath($path), realpath(storage_path('app/backups')))) {
            return null;
        }

        return $path;
    }

    private function getBackups(): array
    {
        $backupPath = storage_path('app/backups');
        $backups = [];

        if (is_dir($backupPath)) {
            $files = glob($backupPath . '/*.sql');

            foreach ($files as $file) {
                $filename = basename($file);
                if (! preg_match(self::BACKUP_FILENAME_PATTERN, $filename)) {
                    continue;
                }

                $backups[] = [
                    'filename' => $filename,
                    'size' => $this->formatBytes(filesize($file)),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }

            usort($backups, function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
        }

        return $backups;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
