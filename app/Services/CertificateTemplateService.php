<?php

namespace App\Services;

use App\Models\CertificateTemplate;
use App\Models\Participant;
use Barryvdh\DomPDF\Facade\Pdf;
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

        // Register custom fonts with DomPDF
        $this->registerFontsWithDompdf($template->text_elements ?? []);

        // Build font CSS
        $fontCss = $this->buildFontCss($template->text_elements ?? []);

        return PDF::loadView('admin.certificates.certificate-pdf', [
            'pages' => [['elements' => $resolvedElements]],
            'backgroundBase64' => $backgroundBase64,
            'orientation' => $orientation,
            'fontCss' => $fontCss,
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

        // Register custom fonts with DomPDF
        $this->registerFontsWithDompdf($template->text_elements ?? []);

        // Build font CSS
        $fontCss = $this->buildFontCss($template->text_elements ?? []);

        return PDF::loadView('admin.certificates.certificate-pdf', [
            'pages' => $pages,
            'backgroundBase64' => $backgroundBase64,
            'orientation' => $orientation,
            'fontCss' => $fontCss,
        ])->setPaper('a4', $orientation);
    }
    
    private function buildFontCss(array $elements): string
    {
        $usedFamilies = collect($elements)
            ->pluck('fontFamily')
            ->filter()
            ->unique()
            ->toArray();

        // Exclude system fonts
        $systemFonts = ['dejavu sans', 'dejavu serif', 'dejavu sans mono', 'helvetica', 'times', 'courier'];
        $usedFamilies = array_diff($usedFamilies, $systemFonts);

        if (empty($usedFamilies)) {
            return '';
        }

        $fontDir = storage_path('fonts');

        if (! is_dir($fontDir)) {
            return '';
        }

        $css = '';
        $files = scandir($fontDir);

        // Group font files by family
        $fontFiles = [];

        foreach ($files as $file) {
            if (! str_ends_with($file, '.ttf')) {
                continue;
            }

            // Skip cached font files (they have hash in name)
            if (preg_match('/_[a-f0-9]{32}\.ttf$/', $file)) {
                continue;
            }

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

            $fontFamily = ucwords(str_replace('_', ' ', implode('_', $nameParts)));

            // Only include fonts that are actually used
            if (! in_array($fontFamily, $usedFamilies)) {
                continue;
            }

            $fontPath = $fontDir . DIRECTORY_SEPARATOR . $file;

            if (! file_exists($fontPath)) {
                continue;
            }

            if (! isset($fontFiles[$fontFamily])) {
                $fontFiles[$fontFamily] = [];
            }

            $fontFiles[$fontFamily][] = [
                'path' => $fontPath,
                'weight' => $fontWeight,
                'style' => $fontStyle,
            ];
        }

        // Generate CSS for each font family
        foreach ($fontFiles as $fontFamily => $variants) {
            // Find normal variant as fallback
            $normalVariant = null;
            foreach ($variants as $variant) {
                if ($variant['weight'] === 'normal' && $variant['style'] === 'normal') {
                    $normalVariant = $variant;
                    break;
                }
            }

            // Generate @font-face for each variant
            foreach ($variants as $variant) {
                $fontData = base64_encode(file_get_contents($variant['path']));
                $fontUrl = 'data:font/truetype;charset=utf-8;base64,' . $fontData;

                $css .= "@font-face {\n";
                $css .= "    font-family: '{$fontFamily}';\n";
                $css .= "    src: url('{$fontUrl}') format('truetype');\n";
                $css .= "    font-weight: {$variant['weight']};\n";
                $css .= "    font-style: {$variant['style']};\n";
                $css .= "}\n";
            }

            // For script fonts with only normal variant, also register for bold/italic to prevent fallback
            if ($normalVariant !== null && count($variants) === 1) {
                $fontData = base64_encode(file_get_contents($normalVariant['path']));
                $fontUrl = 'data:font/truetype;charset=utf-8;base64,' . $fontData;

                // Register as bold
                $css .= "@font-face {\n";
                $css .= "    font-family: '{$fontFamily}';\n";
                $css .= "    src: url('{$fontUrl}') format('truetype');\n";
                $css .= "    font-weight: bold;\n";
                $css .= "    font-style: normal;\n";
                $css .= "}\n";
            }
        }

        return $css;
    }

    private function registerFontsWithDompdf(array $elements): void
    {
        $usedFamilies = collect($elements)
            ->pluck('fontFamily')
            ->filter()
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

        // Configure DomPDF options
        $options = new Options();
        $options->set('fontDir', $fontDir);
        $options->set('fontCache', $fontDir);

        $dompdf = new Dompdf($options);
        $fontMetrics = $dompdf->getFontMetrics();

        $files = scandir($fontDir);

        foreach ($files as $file) {
            if (! str_ends_with($file, '.ttf')) {
                continue;
            }

            // Skip cached font files (they have hash in name)
            if (preg_match('/_[a-f0-9]{32}\.ttf$/', $file)) {
                continue;
            }

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

            $fontFamily = ucwords(str_replace('_', ' ', implode('_', $nameParts)));

            // Only register fonts that are actually used
            if (! in_array($fontFamily, $usedFamilies)) {
                continue;
            }

            $fontPath = $fontDir . DIRECTORY_SEPARATOR . $file;

            if (! file_exists($fontPath)) {
                continue;
            }

            // Register font with DomPDF
            $fontMetrics->registerFont(
                ['family' => $fontFamily, 'style' => $fontStyle, 'weight' => $fontWeight],
                $fontPath
            );

            // For single-variant fonts (like script fonts), also register for bold to prevent fallback
            if ($fontWeight === 'normal' && $fontStyle === 'normal') {
                // Check if this font has only one variant
                $variantCount = 0;
                foreach ($files as $f) {
                    if (str_ends_with($f, '.ttf') && !preg_match('/_[a-f0-9]{32}\.ttf$/', $f)) {
                        $np = explode('_', str_replace('.ttf', '', $f));
                        $sp = array_pop($np);
                        $fn = ucwords(str_replace('_', ' ', implode('_', $np)));
                        if ($fn === $fontFamily) {
                            $variantCount++;
                        }
                    }
                }

                if ($variantCount === 1) {
                    $fontMetrics->registerFont(
                        ['family' => $fontFamily, 'style' => 'normal', 'weight' => 'bold'],
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
