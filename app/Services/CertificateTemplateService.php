<?php

namespace App\Services;

use App\Models\CertificateTemplate;
use App\Models\Participant;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateTemplateService
{
    public function storeBackgroundImage(CertificateTemplate $template, UploadedFile $file): string
    {
        if ($template->background_image_path) {
            Storage::disk('public')->delete($template->background_image_path);
        }

        $filename = now()->format('YmdHis').'-'.Str::random(10).'.jpg';
        $directory = 'certificates/backgrounds';
        $storagePath = Storage::disk('public')->path($directory);

        if (! is_dir($storagePath)) {
            mkdir($storagePath, 0777, true);
        }

        $absolutePath = $storagePath.DIRECTORY_SEPARATOR.$filename;

        $this->compressImage($file->getRealPath(), $absolutePath, 1600, 70);

        return $directory.'/'.$filename;
    }

    private function compressImage(string $sourcePath, string $destPath, int $maxWidth, int $quality): void
    {
        $info = getimagesize($sourcePath);

        if ($info === false) {
            copy($sourcePath, $destPath);

            return;
        }

        $mime = $info['mime'];
        $srcWidth = $info[0];
        $srcHeight = $info[1];

        $source = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            default => null,
        };

        if (! $source) {
            copy($sourcePath, $destPath);

            return;
        }

        if ($srcWidth > $maxWidth) {
            $ratio = $maxWidth / $srcWidth;
            $newWidth = $maxWidth;
            $newHeight = (int) ($srcHeight * $ratio);
        } else {
            $newWidth = $srcWidth;
            $newHeight = $srcHeight;
        }

        $dest = imagecreatetruecolor($newWidth, $newHeight);

        if ($mime === 'image/png') {
            $white = imagecolorallocate($dest, 255, 255, 255);
            imagefill($dest, 0, 0, $white);
        }

        imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
        imagejpeg($dest, $destPath, $quality);

        imagedestroy($source);
        imagedestroy($dest);
    }

    public function supportedPlaceholders(): array
    {
        return [
            'participant_name' => 'Nama lengkap peserta',
            'bib_number' => 'Nomor BIB peserta',
            'participant_email' => 'Email peserta',
            'participant_phone' => 'Nomor telepon peserta',
            'distance_category' => 'Kategori jarak peserta',
            'event_name' => 'Nama event',
            'event_date' => 'Tanggal event, format d M Y',
            'event_location' => 'Lokasi event',
            'finish_time' => 'Waktu finish, format d M Y H:i:s',
            'finish_date' => 'Tanggal finish, format d M Y',
            'race_duration' => 'Durasi race, format HH:MM:SS',
            'generated_at' => 'Waktu file sertifikat dibuat, format d M Y H:i',
        ];
    }

    public function buildValues(Participant $participant): array
    {
        $participant->loadMissing('event');

        return [
            'participant_name' => (string) $participant->name,
            'bib_number' => (string) ($participant->bib_number ?? '-'),
            'participant_email' => (string) $participant->email,
            'participant_phone' => (string) $participant->phone,
            'distance_category' => (string) $participant->distance_category,
            'event_name' => (string) ($participant->event?->name ?? '-'),
            'event_date' => $participant->event?->date?->translatedFormat('d M Y') ?? '-',
            'event_location' => (string) ($participant->event?->location ?? '-'),
            'finish_time' => $participant->race_finished_at?->format('d M Y H:i:s') ?? '-',
            'finish_date' => $participant->race_finished_at?->translatedFormat('d M Y') ?? '-',
            'race_duration' => (string) ($participant->formatted_race_duration ?? '-'),
            'generated_at' => Carbon::now()->translatedFormat('d M Y H:i'),
        ];
    }

    public function generatePdf(CertificateTemplate $template, Participant $participant): \Barryvdh\DomPDF\PDF
    {
        $orientation = $template->orientation ?? 'landscape';
        $backgroundBase64 = $this->getBackgroundBase64($template);
        $resolvedElements = $this->resolveElements($template, $participant);

        // Register custom fonts with DomPDF before generating
        $this->registerFontsWithDompdf($template->text_elements ?? []);

        $pdf = app('dompdf.wrapper');

        return $pdf->loadView('admin.certificates.certificate-pdf', [
            'pages' => [['elements' => $resolvedElements]],
            'backgroundBase64' => $backgroundBase64,
            'orientation' => $orientation,
            'fontFamilies' => $this->getUsedFontFamilies($template->text_elements ?? []),
        ])->setPaper('a4', $orientation);
    }

    public function generateBulkPdf(CertificateTemplate $template, Collection $participants): \Barryvdh\DomPDF\PDF
    {
        $orientation = $template->orientation ?? 'landscape';
        $backgroundBase64 = $this->getBackgroundBase64($template);

        $pages = [];
        foreach ($participants as $participant) {
            $pages[] = ['elements' => $this->resolveElements($template, $participant)];
        }

        // Register custom fonts with DomPDF before generating
        $this->registerFontsWithDompdf($template->text_elements ?? []);

        $pdf = app('dompdf.wrapper');

        return $pdf->loadView('admin.certificates.certificate-pdf', [
            'pages' => $pages,
            'backgroundBase64' => $backgroundBase64,
            'orientation' => $orientation,
            'fontFamilies' => $this->getUsedFontFamilies($template->text_elements ?? []),
        ])->setPaper('a4', $orientation);
    }
    
    private function getUsedFontFamilies(array $elements): array
    {
        $usedFamilies = collect($elements)
            ->pluck('fontFamily')
            ->filter()
            ->unique()
            ->toArray();

        // Return array with original case as keys and lowercase as values for lookup
        $result = [];
        foreach ($usedFamilies as $family) {
            $result[$family] = strtolower($family);
        }

        return $result;
    }

    private function registerFontsWithDompdf(array $elements): void
    {
        $usedFamilies = collect($elements)
            ->pluck('fontFamily')
            ->filter()
            ->map(fn ($f) => strtolower($f))
            ->unique()
            ->toArray();

        // Exclude system fonts
        $systemFonts = ['dejavu sans', 'dejavu serif', 'dejavu sans mono', 'helvetica', 'times', 'courier'];
        $usedFamilies = array_diff($usedFamilies, $systemFonts);

        if (empty($usedFamilies)) {
            return;
        }

        $fontDir = storage_path('fonts');

        if (! is_dir($fontDir)) {
            return;
        }

        $options = new Options();
        $options->set('fontDir', $fontDir);
        $options->set('fontCache', $fontDir);

        $dompdf = new Dompdf($options);
        $fontMetrics = $dompdf->getFontMetrics();

        $files = scandir($fontDir);

        foreach ($files as $file) {
            if (str_ends_with($file, '.ttf') && !str_contains($file, '_')) {
                // Only process original font files (not cached ones with hashes)
                $nameParts = explode('_', str_replace('.ttf', '', $file));
                $stylePart = array_pop($nameParts);

                $fontWeight = 'normal';
                $fontStyle = 'normal';

                if ($stylePart === 'italic') {
                    if (count($nameParts) > 0 && end($nameParts) === 'bold') {
                        array_pop($nameParts);
                        $fontWeight = 'bold';
                        $fontStyle = 'italic';
                    } else {
                        $fontStyle = 'italic';
                    }
                } elseif ($stylePart === 'bold') {
                    $fontWeight = 'bold';
                } elseif ($stylePart !== 'normal') {
                    $nameParts[] = $stylePart;
                }

                $fontFamily = ucwords(implode(' ', $nameParts));

                // Only register fonts that are actually used
                if (! in_array(strtolower($fontFamily), $usedFamilies)) {
                    continue;
                }

                $fontPath = $fontDir . DIRECTORY_SEPARATOR . $file;

                if (file_exists($fontPath)) {
                    $fontMetrics->registerFont(
                        ['family' => $fontFamily, 'style' => $fontStyle, 'weight' => $fontWeight],
                        $fontPath
                    );
                }
            }
        }

        $fontMetrics->saveFontFamilies();
    }

    private function getBackgroundBase64(CertificateTemplate $template): ?string
    {
        if (! $template->background_image_path) {
            return null;
        }

        $absolutePath = public_path('storage/'.$template->background_image_path);

        if (! file_exists($absolutePath)) {
            return null;
        }

        $data = file_get_contents($absolutePath);
        $mime = mime_content_type($absolutePath) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($data);
    }

    private function resolveElements(CertificateTemplate $template, Participant $participant): array
    {
        $values = $this->buildValues($participant);
        $resolved = [];

        foreach ($template->text_elements ?? [] as $element) {
            $el = $element;
            $el['value'] = $values[$element['placeholder']] ?? $element['placeholder'];
            $resolved[] = $el;
        }

        return $resolved;
    }
}
