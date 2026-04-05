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

            // Ensure backup directory exists
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            if (config('database.default') === 'sqlite') {
                // Backup SQLite database
                $source = config('database.connections.sqlite.database');
                copy($source, $path);
            } else {
                // Backup MySQL database using mysqldump
                $dbName = config('database.connections.mysql.database');
                $dbUser = config('database.connections.mysql.username');
                $dbPass = config('database.connections.mysql.password');
                $dbHost = config('database.connections.mysql.host');
                
                $command = sprintf(
                    'mysqldump -h %s -u %s -p%s %s > %s',
                    escapeshellarg($dbHost),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbName),
                    escapeshellarg($path)
                );
                
                exec($command, $output, $returnCode);
                
                if ($returnCode !== 0) {
                    throw new \Exception('Gagal membuat backup database');
                }
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
            $filename = $request->input('backup_file');
            $path = storage_path('app/backups/' . $filename);

            if (!file_exists($path)) {
                return redirect()
                    ->route('admin.database.index')
                    ->with('error', 'File backup tidak ditemukan');
            }

            if (config('database.default') === 'sqlite') {
                // Restore SQLite database
                $target = config('database.connections.sqlite.database');
                
                // Backup current database first
                $currentBackup = $target . '.backup_' . date('Y-m-d_H-i-s');
                copy($target, $currentBackup);
                
                // Restore
                copy($path, $target);
            } else {
                // Restore MySQL database
                $dbName = config('database.connections.mysql.database');
                $dbUser = config('database.connections.mysql.username');
                $dbPass = config('database.connections.mysql.password');
                $dbHost = config('database.connections.mysql.host');
                
                $command = sprintf(
                    'mysql -h %s -u %s -p%s %s < %s',
                    escapeshellarg($dbHost),
                    escapeshellarg($dbUser),
                    escapeshellarg($dbPass),
                    escapeshellarg($dbName),
                    escapeshellarg($path)
                );
                
                exec($command, $output, $returnCode);
                
                if ($returnCode !== 0) {
                    throw new \Exception('Gagal merestore database');
                }
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

            $deletedTables = [];

            foreach ($tables as $table) {
                switch ($table) {
                    case 'participants':
                        Participant::query()->delete();
                        $deletedTables[] = 'Participants';
                        break;
                    case 'events':
                        Event::query()->delete();
                        $deletedTables[] = 'Events';
                        break;
                    case 'distance_categories':
                        DistanceCategory::query()->delete();
                        $deletedTables[] = 'Distance Categories';
                        break;
                    case 'contacts':
                        Contact::query()->delete();
                        $deletedTables[] = 'Contacts';
                        break;
                    case 'galleries':
                        Gallery::query()->delete();
                        // Also delete gallery files
                        Storage::disk('public')->deleteDirectory('galleries');
                        $deletedTables[] = 'Galleries';
                        break;
                    case 'certificate_templates':
                        CertificateTemplate::query()->delete();
                        $deletedTables[] = 'Certificate Templates';
                        break;
                }
            }

            return redirect()
                ->route('admin.database.index')
                ->with('success', 'Data berhasil dihapus dari: ' . implode(', ', $deletedTables));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.database.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function download(string $filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (!file_exists($path)) {
            return redirect()
                ->route('admin.database.index')
                ->with('error', 'File backup tidak ditemukan');
        }

        return response()->download($path);
    }

    public function destroyBackup(string $filename): RedirectResponse
    {
        try {
            $path = storage_path('app/backups/' . $filename);
            
            if (file_exists($path)) {
                unlink($path);
            }

            return redirect()
                ->route('admin.database.index')
                ->with('success', 'Backup berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.database.index')
                ->with('error', 'Gagal menghapus backup: ' . $e->getMessage());
        }
    }

    private function getBackups(): array
    {
        $backupPath = storage_path('app/backups');
        $backups = [];

        if (is_dir($backupPath)) {
            $files = glob($backupPath . '/*.sql');
            
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => $this->formatBytes(filesize($file)),
                    'created_at' => date('Y-m-d H:i:s', filemtime($file))
                ];
            }

            // Sort by creation date (newest first)
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
