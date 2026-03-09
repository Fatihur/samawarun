<?php

namespace App\Console\Commands;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class InstallCertificateFonts extends Command
{
    protected $signature = 'certificates:install-fonts';

    protected $description = 'Download and install Google Fonts for certificate PDF generation';

    private array $fonts = [
        'Inter' => [
            'variants' => ['regular', 'italic', '700', '700italic'],
            'id' => 'inter',
        ],
        'Roboto' => [
            'variants' => ['regular', 'italic', '700', '700italic'],
            'id' => 'roboto',
        ],
        'Poppins' => [
            'variants' => ['regular', 'italic', '700', '700italic'],
            'id' => 'poppins',
        ],
        'Montserrat' => [
            'variants' => ['regular', 'italic', '700', '700italic'],
            'id' => 'montserrat',
        ],
        'Open Sans' => [
            'variants' => ['regular', 'italic', '700', '700italic'],
            'id' => 'open-sans',
        ],
        'Lato' => [
            'variants' => ['regular', 'italic', '700', '700italic'],
            'id' => 'lato',
        ],
        'Playfair Display' => [
            'variants' => ['regular', 'italic', '700', '700italic'],
            'id' => 'playfair-display',
        ],
        'Merriweather' => [
            'variants' => ['regular', 'italic', '700', '700italic'],
            'id' => 'merriweather',
        ],
        'Dancing Script' => [
            'variants' => ['regular', '700'],
            'id' => 'dancing-script',
        ],
        'Great Vibes' => [
            'variants' => ['regular'],
            'id' => 'great-vibes',
        ],
        'Pacifico' => [
            'variants' => ['regular'],
            'id' => 'pacifico',
        ],
        'Caveat' => [
            'variants' => ['regular', '700'],
            'id' => 'caveat',
        ],
        'Satisfy' => [
            'variants' => ['regular'],
            'id' => 'satisfy',
        ],
        'Cookie' => [
            'variants' => ['regular'],
            'id' => 'cookie',
        ],
        'Kaushan Script' => [
            'variants' => ['regular'],
            'id' => 'kaushan-script',
        ],
        'Tangerine' => [
            'variants' => ['regular', '700'],
            'id' => 'tangerine',
        ],
        'Allura' => [
            'variants' => ['regular'],
            'id' => 'allura',
        ],
        'Alex Brush' => [
            'variants' => ['regular'],
            'id' => 'alex-brush',
        ],
        'Pinyon Script' => [
            'variants' => ['regular'],
            'id' => 'pinyon-script',
        ],
        'Parisienne' => [
            'variants' => ['regular'],
            'id' => 'parisienne',
        ],
    ];

    public function handle(): int
    {
        $fontDir = storage_path('fonts');

        if (! is_dir($fontDir)) {
            mkdir($fontDir, 0777, true);
        }

        $this->info('Installing Google Fonts for certificate PDF...');
        $this->newLine();

        $bar = $this->output->createProgressBar(count($this->fonts));
        $bar->start();

        $installedFonts = [];

        foreach ($this->fonts as $fontName => $config) {
            $result = $this->installFont($fontName, $config, $fontDir);

            if ($result) {
                $installedFonts[] = $fontName;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (count($installedFonts) > 0) {
            $this->info('Successfully installed '.count($installedFonts).' fonts:');

            foreach ($installedFonts as $font) {
                $this->line('  ✓ '.$font);
            }
        }

        $this->newLine();
        $this->info('Fonts installed to: '.$fontDir);

        return self::SUCCESS;
    }

    private function installFont(string $fontName, array $config, string $fontDir): bool
    {
        try {
            $response = Http::timeout(30)->get(
                'https://gwfh.mranftl.com/api/fonts/'.$config['id'],
                ['subsets' => 'latin']
            );

            if (! $response->ok()) {
                $this->warn("  ✗ Failed to fetch {$fontName} metadata");

                return false;
            }

            $data = $response->json();
            $variants = $data['variants'] ?? [];

            $fontStyles = [];

            foreach ($variants as $variant) {
                $variantId = $variant['id'] ?? '';

                if (! in_array($variantId, $config['variants'], true)) {
                    continue;
                }

                $ttfUrl = $variant['ttf'] ?? null;

                if (! $ttfUrl) {
                    continue;
                }

                $isItalic = str_contains($variantId, 'italic');
                $isBold = str_contains($variantId, '700');

                if ($isBold && $isItalic) {
                    $style = 'bold_italic';
                } elseif ($isBold) {
                    $style = 'bold';
                } elseif ($isItalic) {
                    $style = 'italic';
                } else {
                    $style = 'normal';
                }

                $safeName = strtolower(str_replace(' ', '_', $fontName));
                $fileName = $safeName.'_'.$style.'.ttf';
                $filePath = $fontDir.DIRECTORY_SEPARATOR.$fileName;

                if (! file_exists($filePath)) {
                    $fontData = Http::timeout(60)->get($ttfUrl)->body();
                    file_put_contents($filePath, $fontData);
                }

                $fontStyles[$style] = $filePath;
            }

            if (empty($fontStyles)) {
                return false;
            }

            // Register with DomPDF
            $this->registerWithDompdf($fontName, $fontStyles, $fontDir);

            return true;
        } catch (\Throwable $e) {
            $this->warn("  ✗ Error installing {$fontName}: ".$e->getMessage());

            return false;
        }
    }

    private function registerWithDompdf(string $fontName, array $fontStyles, string $fontDir): void
    {
        $options = new Options();
        $options->set('fontDir', $fontDir);
        $options->set('fontCache', $fontDir);

        $dompdf = new Dompdf($options);
        $fontMetrics = $dompdf->getFontMetrics();

        $normal = $fontStyles['normal'] ?? null;
        $bold = $fontStyles['bold'] ?? $normal;
        $italic = $fontStyles['italic'] ?? $normal;
        $boldItalic = $fontStyles['bold_italic'] ?? $bold;

        if ($normal) {
            $fontMetrics->registerFont(
                ['family' => $fontName, 'style' => 'normal', 'weight' => 'normal'],
                $normal
            );
        }

        if ($bold) {
            $fontMetrics->registerFont(
                ['family' => $fontName, 'style' => 'normal', 'weight' => 'bold'],
                $bold
            );
        }

        if ($italic) {
            $fontMetrics->registerFont(
                ['family' => $fontName, 'style' => 'italic', 'weight' => 'normal'],
                $italic
            );
        }

        if ($boldItalic) {
            $fontMetrics->registerFont(
                ['family' => $fontName, 'style' => 'italic', 'weight' => 'bold'],
                $boldItalic
            );
        }

        $fontMetrics->saveFontFamilies();
    }
}
