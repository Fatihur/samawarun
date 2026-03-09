# Installation

Get started by installing and configuring the Laravel WordTemplate package in your project.

## Requirements

Ensure your environment meets these criteria:

- PHP: 8.1+
- Laravel: 10.x+
- Composer: v2
- PHP Extensions: `ext-zip`, `ext-xml`, and `ext-gd` (optional for images).

To verify the extensions, run:

```bash
php -m | grep -E "zip|xml|gd"
```

## Install the Package

Run this Composer command in your project's root directory:

```bash
composer require novay/laravel-word-template
```

## Configuration & Samples

Publish the configuration file to customize the package and access sample templates.

```bash
php artisan vendor:publish --provider="Novay\Word\Providers\WordServiceProvider"
```

This command creates `config/word.php` and publishes several sample `.docx` files to `storage/app/templates` that you can use as a reference.

## Verify Your Installation

Confirm the package is working by creating a test document with Laravel Tinker.

Start Tinker:

```bash
php artisan tinker
```

Run the command to create a file:

```php
Word::builder()->addText('Hello World')->save(storage_path('app/test.docx'));
```

If successful, you will find `test.docx` in your `storage/app` folder. 🎉

---

# Configuration

The configuration file for Laravel WordTemplate is located at:

```bash
config/word.php
```

You can publish this file to your project using:

```bash
php artisan vendor:publish --provider="Novay\Word\Providers\WordServiceProvider"
```

---

# Quick Start

This page provides the fastest way to get started with Laravel WordTemplate. Follow the steps below to create your first Word document.

## Template Mode

If you want to use a pre-existing template, it's simple. Let's say you have an `invoice.docx` file with placeholders like this:

```text
Name: ${name}
Date: ${date}
```

You can use the following code in your controller to fill it with data:

```php
use Novay\Word\Facades\Word;

class InvoiceController extends Controller
{
    public function generate()
    {
        return Word::template(storage_path('templates/invoice.docx'))
            ->setValues([
                'name' => 'Novianto Rahmadi',
                'date' => now()->format('d M Y'),
            ])
            ->download('invoice.docx');
    }
}
```

**The Result:** Your user will immediately download the `invoice.docx` file with all the data filled in automatically.

## Builder Mode

If you need to create a new document from scratch, you can use Builder Mode.

```php
use Novay\Word\Facades\Word;

class ReportController extends Controller
{
    public function build()
    {
        return Word::builder()
            ->addTitle('Monthly Report', 1)
            ->addText('This is a monthly report generated automatically.')
            ->addTable([
                ['No', 'Name', 'Total'],
                [1, 'Product A', 120],
                [2, 'Product B', 80],
            ])
            ->download('report.docx');
    }
}
```

---

# Template Mode: Getting Started

The Template Mode feature lets you use your existing Word (`.docx`) files as a base for generating new documents. This is the fastest way to create professional files by replacing text, images, and data loops directly from your Laravel application.

## Loading a Template

To load a template, use the `template($file)` function. This function accepts various file paths, including from your application's `public` or `storage` directories, or even a remote URL.

Here's a simple example:

```php
use Novay\Word\Facades\Word;

Word::template('template.docx');
```

You can specify the location using standard Laravel helpers:

- **Public directory:** `$filePath = public_path('template.docx');`
- **Storage directory:** `$filePath = storage_path('app/templates/template.docx');`
- **URL:** `$urlDocument = 'https://example.com/templates/document.docx';`

> **Note:** A common practice is to store templates in the `storage/app/templates/` folder.

---

# Replacing Values

The Replace Values feature lets you fill Word documents with dynamic data from your Laravel application. This works by replacing placeholders, typically formatted like `${variable}`, with the values you provide.

## One by One

To replace a few placeholders individually, use the `setValue()` method. For example, if your document has text like this:

```text
Hello ${name},
Welcome to ${app}!
```

You can replace them with this:

```php
use Novay\Word\Facades\Word;

// ...
return Word::template($filePath)
    ->setValue('name', 'Novay')
    ->setValue('app', 'Automatic Documents')
    ->download('result.docx');
```

Your document will be instantly populated with the data you provided:

```text
Hello Novay,
Welcome to Automatic Documents!
```

## Batch Replacing Values

For documents with many placeholders, it's more efficient to use the `setValues()` method with an associative array.

For example, your contract document might contain many details like this:

```text
Work contract for ${client} related to the ${project} project.
Project deadline: ${deadline}.
```

Instead of calling `setValue()` multiple times, you can do it in a single call with `setValues()`:

```php
use Novay\Word\Facades\Word;

// ...
return Word::template('contract.docx')
    ->setValues([
        'client' => 'PT Jaya Abadi',
        'project' => 'Website Redesign',
        'deadline' => 'September 30, 2025',
    ])
    ->download('final-contract.docx');
```

This way, all the data from your array will be inserted directly into the document. Pretty handy, right?

---

# Looping Data

The Looping Data feature is for repeating a block of content in your Word template. With this, you can generate dynamic tables and lists from a single template, saving you a lot of time.

## Basic Looping

Currently, looping only works if the placeholders are placed inside a table. Please refer to the sample file `storage/app/templates/basic-loop.docx` which is included when you publish the config.

For example, your template's table might contain a single row with the following text:

```text
List of Employees:
${i}. ${name} (${email})
```

Now, in your controller, you can loop through an array of data and populate the document.

```php
$users = [
    ['i' => 1, 'name' => 'Novianto Rahmadi', 'email' => 'novay@btekno.id'],
    ['i' => 2, 'name' => 'Melani Malik', 'email' => 'melan@btekno.id'],
];

return Word::template(storage_path('app/templates/basic-loop.docx'))
    ->setLoop('i', $users)
    ->download('basic-loop-final.docx');
```

Then, you'll see this within your document:

```text
List of Employees:
1. Novianto Rahmadi (novay@btekno.id)
2. Melani Malik (melan@btekno.id)
```

## Table Looping

You can also create dynamic tables. First, set up your template with a table logic.
Now, prepare the necessary data. Make sure the placeholder for each column is included.

```php
$experience = [
    [
        'no' => 1,
        'client' => 'Diskominfo Samarinda',
        'job' => 'Samarinda AI',
        'position' => 'Data Analyst'
    ],
    [
        'no' => 2,
        'client' => 'Disdik Berau',
        'job' => 'Education Data Unification System',
        'position' => 'Programmer'
    ],
    [
        'no' => 3,
        'client' => 'BPKAD Kutai Kartanegara',
        'job' => 'Amanda',
        'position' => 'Programmer'
    ],
];

return Word::template(storage_path('app/templates/template.docx'))
    ->setLoop('no', $experience)
    ->download('output.docx');
```

---

# Inserting Images

Beyond just text, you can also replace placeholders in your document with images. This is perfect for things like company logos, signatures, or product photos.

## 1. Replacing an Image

To replace a simple image placeholder, you'll use the `setImage()` method.

**Example: template-image.docx**
This template should contain a placeholder like `${logo}` where you want your image to appear.

```php
use Novay\Word\Facades\Word;

Route::get('/template-image', function ()
{
    $template = storage_path('app/templates/template-image.docx');
    $ttd = storage_path('app/templates/signature.png');

    return Word::template($template)
        ->setImage('ttd', $ttd)
        ->download('output.docx');
});
```

This code will replace the `${signature}` placeholder with the `signature.png` image.

## 2. Replacing Multiple Images

You have a couple of ways to replace more than one image at a time.

**Option A: Chain `setImage()`**
You can chain the `setImage()` method for each image you want to replace. This is a good choice for a small number of images.

```php
Word::template($template)
    ->setImage('signature', $signature, ['width' => 120])
    ->setImage('stamp', $stamp, ['width' => 200])
    ->download('output.docx');
```

**Option B: Use `setImages()`**
For a cleaner and more organized approach, especially with multiple images and options, use the `setImages()` method with an associative array.

```php
Word::template($template)
    ->setImages([
        'signature' => [
            'path'  => $signature,
            'width' => 120,
            'height'=> 120,
            'ratio' => true // default: true
        ],
        'stamp' => [
            'path'  => $stamp,
            'width' => 200,
            'height'=> 100,
            'ratio' => true // default: true
        ]
    ])
    ->download('output.docx');
```

## 3. Images Within Loops

_@TODO pending library updates._

## 4. Image Sizing Options

When inserting an image, you can control its dimensions using the width, height, and ratio options.

| Option   | Type      | Description                                                                              |
| -------- | --------- | ---------------------------------------------------------------------------------------- |
| `width`  | `int`     | Sets the image width in pixels.                                                          |
| `height` | `int`     | Sets the image height in pixels.                                                         |
| `ratio`  | `boolean` | Determines if the image's original aspect ratio is maintained (`true`) or not (`false`). |

**How the Ratio Option Works**

| Ratio            | Description                                                                                                                                                                                          |
| ---------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `true` (default) | You only need to specify a width or a height. The library will automatically calculate the other dimension to prevent the image from being distorted. This is the recommended option for most cases. |
| `false`          | You must specify both a width and a height. The image will be forced to fit these exact dimensions, which can stretch or squash it.                                                                  |

> **📌 Quick Tips**
> Use high-resolution images to ensure they look sharp, especially when printed.

---

# Building Documents from Scratch

Beyond using templates, Laravel WordTemplate also supports creating Word documents from scratch using Builder Mode.

Builder Mode is perfect for:

- Generating completely dynamic reports or official documents.
- Creating documents directly from a controller.
- Building layouts with text, tables, images, and custom styling.

## 1. Creating a Blank Document

You can start with an empty document and add content using a fluent, chainable syntax.

```php
use Novay\Word\Facades\Word;

Route::get('/builder', function () {
    return Word::builder()
        ->addTitle('Sales Report', 1) // Heading 1
        ->addText('This report was automatically created using Laravel WordTemplate.')
        ->download('report.docx');
});
```

## 2. Adding Paragraphs and Styling

The `addText()` method lets you add paragraphs, and you can pass an array of options to style them.

```php
Word::builder()
    ->addText('Hello, this is the first paragraph.')
    ->addText('This is the second paragraph with custom styling.', [
        'bold' => true,
        'italic' => true,
        'size' => 14,
        'color' => 'FF0000',
    ])
    ->download('styled_paragraph.docx');
```

Available style options:

- `bold` → bold text
- `italic` → italic text
- `underline` → underlined text
- `size` → font size
- `color` → hexadecimal color (e.g., FF0000 for red)

## 3. Adding Lists

You can create bulleted or numbered lists with `addList()`. The optional second argument controls the nesting level.

```php
Word::builder()
    ->addTitle('To-do List', 2)
    ->addList('Buy office supplies')
    ->addList('Team meeting', 0) // level 0 (top-level)
    ->addList('Prepare the report', 1) // level 1 (nested)
    ->download('list.docx');
```

## 4. Creating Tables

The `addTable()` method accepts a two-dimensional array to generate a table.

```php
Word::builder()
    ->addTitle('Sales Report', 2)
    ->addTable([
        ['Product', 'Quantity', 'Price'],
        ['Laptop', '5', '$50,000,000'],
        ['Printer', '2', '$6,000,000'],
    ])
    ->download('table.docx');
```

## 5. Adding Images

You can easily insert images from a local path and control their size and alignment.

```php
Word::builder()
    ->addTitle('Image Example', 2)
    ->addImage(public_path('logo.png'), [
        'width' => 120,
        'height' => 120,
        'alignment' => 'center',
    ])
    ->download('image.docx');
```

## 6. A Full Example

You can chain multiple builder methods to create a complete, complex document with a single fluid call.

```php
Word::builder()
    ->addTitle('Monthly Report', 1)
    ->addText('This document was created using Builder Mode.', ['italic' => true])
    ->addList('Sales Summary')
    ->addTable([
        ['Product', 'Quantity', 'Price'],
        ['Laptop', '5', '$50,000,000'],
        ['Printer', '2', '$6,000,000'],
    ])
    ->addImage(public_path('signature.png'), [
        'width' => 100,
        'alignment' => 'right',
    ])
    ->save(storage_path('app/monthly-report.docx'));
```

> **📌 Tips**
>
> - Use Builder Mode when your document's structure is fully dynamic and changes with the data.
> - Use Template Mode when you have a pre-designed document with a static layout.
> - You can even combine them! Load a template, then use builder methods to add extra content.

---

# Saving & Exporting

The `save()` method lets you store a generated document directly on your server, which is useful for archiving or background tasks.

## Saving to a Specific Location

To save a document, just pass the full file path to the `save()` method. The `storage_path()` helper is recommended for saving files that shouldn't be publicly accessible.

Example:

```php
use Novay\Word\Facades\Word;

Word::builder()
    ->addTitle('Report', 1)
    ->addText('This document is saved to storage.')
    ->save(storage_path('app/public/report.docx'));
```

The file `report.docx` will be saved to `storage/app/public/`.

## Saving to the Public Folder

You can also save files to the public folder, making them directly accessible via a URL.

```php
Word::builder()
    ->addTitle('Invoice', 1)
    ->addText('The latest invoice document.')
    ->save(public_path('invoice.docx'));
```

## Downloading Files

The `download()` method makes it simple to send your generated documents directly to the user's browser.

**Default Filename**
When you call `download()` without a filename, the package automatically generates a unique name that includes a timestamp.

```php
use Novay\Word\Facades\Word;

return Word::builder()
    ->addTitle('Financial Report', 1)
    ->addText('This file will be downloaded directly.')
    ->download(); // Result: document_20250914_022830.docx
```

**Custom Filename**
To use a specific filename, pass it as the first argument.

```php
return Word::builder()
    ->addTitle('Invoice #2025', 1)
    ->addText('The document is using custom filename.')
    ->download('invoice.docx');
```

---

# Merge Documents (BETA)

Laravel WordTemplate mendukung penggabungan beberapa file Word (DOCX) menjadi satu dokumen. Fitur ini berguna untuk laporan gabungan, append dokumen, atau arsip kontrak.

## Merge Beberapa File DOCX

```php
use Novay\Word\Facades\Word;

Word::merge([
    storage_path('app/reports/january.docx'),
    storage_path('app/reports/february.docx'),
    storage_path('app/reports/march.docx'),
])->save(storage_path('app/reports/q1.docx'));
```

👉 File `january.docx`, `february.docx`, dan `march.docx` akan digabung jadi `q1.docx`.

## Merge dengan Download Langsung

```php
return Word::merge([
    public_path('docs/intro.docx'),
    public_path('docs/chapter1.docx'),
    public_path('docs/chapter2.docx'),
])->download('book.docx');
```

## Merge dari Builder + Template

Anda juga bisa menggabungkan hasil dari builder dengan template yang sudah ada.

```php
$tempDoc = Word::builder()
    ->addTitle('Appendix', 1)
    ->addText('Ini adalah lampiran tambahan.')
    ->toTempFile();

Word::merge([
    'templates/main.docx',
    $tempDoc,
])->download('final.docx');
```

👉 `main.docx` akan digabung dengan hasil builder di `final.docx`.

## Merge dengan Pemisah Halaman

Secara default, setiap dokumen akan digabung langsung. Jika ingin memaksa page break antar dokumen:

```php
Word::merge([
    'templates/cover.docx',
    'templates/content.docx',
], ['pageBreak' => true])
->download('merged.docx');
```

👉 Cover akan diikuti oleh konten pada halaman baru.

## Merge + Multi-format Export

```php
Word::merge([
    'templates/part1.docx',
    'templates/part2.docx',
])
->export(storage_path('app/final/combined'), ['docx', 'pdf']);
```

👉 Hasil: `combined.docx` & `combined.pdf`

> **📌 Tips**
>
> - Gunakan `merge([...])` untuk menggabungkan banyak file.
> - Bisa gabung template, builder result, atau campuran keduanya.
> - Opsi `pageBreak => true` menambahkan halaman baru antar file.
> - Hasil merge bisa di-save, download, atau export ke multi-format.

---

# Watermark

Laravel WordTemplate mendukung watermark berupa teks maupun gambar. Fitur ini cocok untuk dokumen draft, confidential, atau official copy.

## 1️⃣ Watermark Teks Sederhana

```php
use Novay\Word\Facades\Word;

Word::builder()
    ->addWatermarkText('CONFIDENTIAL')
    ->addTitle('Laporan Keuangan', 1)
    ->addText('Isi laporan dengan watermark teks.')
    ->download('watermark-text.docx');
```

👉 Watermark teks akan muncul miring (diagonal) pada semua halaman.

## 2️⃣ Watermark Teks dengan Opsi

```php
Word::builder()
    ->addWatermarkText('DRAFT ONLY', [
        'font' => 'Arial',
        'size' => 50,
        'color' => 'FF0000', // merah
        'rotation' => -30,
    ])
    ->addTitle('Dokumen Draft', 1)
    ->download('watermark-text-options.docx');
```

👉 Hasil watermark lebih besar, warna merah, miring ke kiri.

## 3️⃣ Watermark Gambar

```php
Word::builder()
    ->addWatermarkImage(public_path('logo.png'), [
        'width' => 200,
        'height' => 200,
        'marginTop' => 150,
        'marginLeft' => 150,
    ])
    ->addTitle('Dokumen dengan Watermark Gambar', 1)
    ->download('watermark-image.docx');
```

👉 Gambar akan ditempatkan di background halaman.

## 4️⃣ Kombinasi Watermark Teks + Gambar

```php
Word::builder()
    ->addWatermarkText('INTERNAL USE ONLY', [
        'size' => 40,
        'color' => '888888',
    ])
    ->addWatermarkImage(public_path('logo.png'), [
        'width' => 120,
        'marginTop' => 100,
        'marginLeft' => 100,
    ])
    ->addTitle('Dokumen Rahasia', 1)
    ->download('watermark-mixed.docx');
```

## 5️⃣ Watermark pada Template

Anda juga bisa menambahkan watermark meskipun dokumen berasal dari template.

```php
Word::template('templates/invoice.docx')
    ->replace([
        'customer' => 'Budi',
        'amount' => 'Rp 5.000.000',
    ])
    ->addWatermarkText('PAID')
    ->download('invoice-watermarked.docx');
```

> **📌 Tips**
>
> - `addWatermarkText()` untuk teks watermark.
> - `addWatermarkImage()` untuk gambar watermark.
> - Watermark otomatis muncul di semua halaman.
> - Gunakan opsi `rotation`, `size`, `color` untuk teks.
> - Gunakan `marginTop` & `marginLeft` untuk mengatur posisi gambar.

---

# Digital Signature

Laravel WordTemplate mendukung penambahan tanda tangan berupa gambar maupun digital signature. Fitur ini cocok untuk dokumen resmi seperti kontrak, invoice, atau sertifikat.

## 1️⃣ Signature dengan Gambar

```php
use Novay\Word\Facades\Word;

Word::builder()
    ->addTitle('Surat Perjanjian', 1)
    ->addText('Isi perjanjian antara pihak A dan pihak B...')
    ->addTextBreak(2)
    ->addText('Hormat kami,')
    ->addImage(public_path('signatures/director.png'), [
        'width' => 120,
        'alignment' => 'left',
    ])
    ->addText('Direktur Utama', ['bold' => true])
    ->download('signature-image.docx');
```

👉 Tanda tangan berupa gambar (`director.png`) akan muncul di bawah teks.

## 2️⃣ Multiple Signatures

```php
Word::builder()
    ->addTitle('Kontrak Kerjasama', 1)
    ->addText('Isi kontrak...')
    ->addTextBreak(2)
    ->addTable([
        [
            ['text' => 'Pihak Pertama', 'alignment' => 'center'],
            ['text' => 'Pihak Kedua', 'alignment' => 'center'],
        ],
        [
            ['image' => public_path('signatures/person1.png'), 'width' => 100, 'alignment' => 'center'],
            ['image' => public_path('signatures/person2.png'), 'width' => 100, 'alignment' => 'center'],
        ],
        [
            ['text' => 'Andi Wijaya', 'alignment' => 'center'],
            ['text' => 'Budi Santoso', 'alignment' => 'center'],
        ],
    ])
    ->download('signature-multiple.docx');
```

👉 Dua tanda tangan ditampilkan berdampingan dalam tabel.

## 3️⃣ Digital Signature (Simple)

Untuk digital signature, Anda bisa menyematkan informasi tanda tangan (nama, jabatan, waktu). Catatan: ini bukan tanda tangan X.509, tetapi metadata sederhana dalam dokumen.

```php
Word::builder()
    ->addTitle('Surat Keputusan', 1)
    ->addText('Isi surat...')
    ->addDigitalSignature([
        'name' => 'Siti Aminah',
        'role' => 'Kepala Departemen',
        'signed_at' => now()->toDateTimeString(),
    ])
    ->download('signature-digital.docx');
```

👉 Metadata tanda tangan akan ditambahkan ke dalam dokumen.

## 4️⃣ Digital Signature dengan X.509 (Advanced)

Jika Anda memiliki sertifikat digital (misalnya `.pfx` atau `.pem`), Anda bisa menggunakannya untuk menandatangani file DOCX/PDF agar lebih resmi.

```php
Word::builder()
    ->addTitle('Kontrak Resmi', 1)
    ->addText('Isi kontrak dengan tanda tangan digital...')
    ->signWithCertificate(storage_path('certs/mycert.pfx'), 'password-ku')
    ->save(storage_path('app/contracts/official.docx'));
```

👉 Dokumen akan diproteksi dengan tanda tangan digital X.509.

## 5️⃣ Signature di Template

```php
Word::template('templates/contract.docx')
    ->replace([
        'party_a' => 'Andi Wijaya',
        'party_b' => 'Budi Santoso',
    ])
    ->addImage(public_path('signatures/person1.png'), ['width' => 120])
    ->addImage(public_path('signatures/person2.png'), ['width' => 120])
    ->download('contract-signed.docx');
```

> **📌 Tips**
>
> - `addImage()` → untuk tanda tangan manual berupa gambar.
> - `addDigitalSignature()` → metadata tanda tangan sederhana.
> - `signWithCertificate()` → untuk tanda tangan digital resmi berbasis X.509.
> - Gunakan tabel untuk membuat layout tanda tangan lebih rapi.

---

# Cheat Sheet

👉 Use this page for a quick check of all features without having to read the detailed documentation.

### Template Mode

| Method                                  | Description                                |
| --------------------------------------- | ------------------------------------------ |
| `Word::template($path)`                 | Loads a .docx template                     |
| `Word::setValue($key, $value)`          | Replaces a single placeholder with a value |
| `Word::setValues($array)`               | Replaces multiple placeholders at once     |
| `Word::setImage($key, $path, $options)` | Inserts an image into a placeholder        |
| `Word::setLoop($block, $data)`          | Looping data di dalam template             |

### Builder Mode

| Method                            | Description                |
| --------------------------------- | -------------------------- |
| `Word::builder()`                 | Buat dokumen baru dari nol |
| `Word::addTitle($text, $level)`   | Tambah heading             |
| `Word::addText($text, $style)`    | Tambah teks paragraf       |
| `Word::addTable($rows, $style)`   | Buat tabel sederhana       |
| `Word::addImage($path, $options)` | Tambah gambar ke dokumen   |
| `Word::setHeader($content)`       | Tambah header              |
| `Word::setFooter($content)`       | Tambah footer              |

### Export & Output

| Method                      | Description                   |
| --------------------------- | ----------------------------- |
| `Word::saveAs($path)`       | Simpan dokumen ke file        |
| `Word::download($filename)` | Unduh langsung ke browser     |
| `Word::export($format)`     | Ekspor dokumen ke format lain |

### Advanced Features

| Method                                | Description                 |
| ------------------------------------- | --------------------------- |
| `Word::merge($files)`                 | Gabungkan beberapa .docx    |
| `Word::setWatermark($text, $options)` | Tambah watermark            |
| `Word::signDocument($cert, $key)`     | Tambah tanda tangan digital |
