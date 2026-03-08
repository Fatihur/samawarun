<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BibSetting;
use App\Models\DistanceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BibSettingController extends Controller
{
    public function index(): View
    {
        $activeTab = request()->string('tab')->value() === 'template' ? 'template' : 'format';

        return view('admin.bib-settings.index', [
            'setting' => BibSetting::current(),
            'distanceCategories' => DistanceCategory::where('is_active', true)->orderBy('name')->get(),
            'activeTab' => $activeTab,
        ]);
    }

    public function layoutGuide(): Response
    {
        $setting = BibSetting::current();

        $pdf = app('dompdf.wrapper');

        return $pdf->loadView('admin.bib-settings.layout-guide', [
            'setting' => $setting,
        ])
            ->setPaper('a5', 'landscape')
            ->download('panduan-tata-letak-nomor-dada.pdf');
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = BibSetting::current();
        $section = $request->string('section')->value();

        if ($section === 'template') {
            $validated = $request->validate([
                'template_title' => ['required', 'string', 'max:60'],
                'footer_text' => ['required', 'string', 'max:255'],
                'primary_color' => ['required', 'in:#0f172a,#1d4ed8,#166534,#7c2d12,#6d28d9'],
                'accent_color' => ['required', 'in:#cbd5e1,#bfdbfe,#bbf7d0,#fed7aa,#ddd6fe'],
                'text_color' => ['required', 'in:#0f172a,#1e3a8a,#14532d,#7c2d12,#4c1d95'],
                'meta_text_color' => ['required', 'in:#334155,#1e40af,#166534,#9a3412,#6d28d9'],
                'bib_font_size' => ['required', 'integer', 'in:84,96,108,120,132,144'],
                'name_font_size' => ['required', 'integer', 'in:18,22,26,30,34'],
                'show_event_date' => ['nullable', 'boolean'],
                'show_event_location' => ['nullable', 'boolean'],
                'background_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],
                'remove_background_image' => ['nullable', 'boolean'],
            ]);

            $validated['show_event_date'] = $request->boolean('show_event_date');
            $validated['show_event_location'] = $request->boolean('show_event_location');

            unset($validated['background_image'], $validated['remove_background_image']);

            if ($request->boolean('remove_background_image') && $setting->background_image_path) {
                Storage::disk('public')->delete($setting->background_image_path);
                $validated['background_image_path'] = null;
            }

            if ($request->hasFile('background_image')) {
                if ($setting->background_image_path) {
                    Storage::disk('public')->delete($setting->background_image_path);
                }

                $validated['background_image_path'] = $request->file('background_image')->store('bib/backgrounds', 'public');
            }

            $setting->update($validated);

            return redirect()
                ->route('admin.bib-settings.index', ['tab' => 'template'])
                ->with('success', 'Pengaturan desain template berhasil diperbarui.');
        }

        $validated = $request->validate([
            'number_padding' => ['required', 'integer', 'min:1', 'max:6'],
            'category_prefixes' => ['nullable', 'array'],
            'category_prefixes.*' => ['nullable', 'string', 'max:10'],
            'category_start_numbers' => ['nullable', 'array'],
            'category_start_numbers.*' => ['nullable', 'integer', 'min:1', 'max:999999'],
        ]);

        $setting->update($validated);

        return redirect()
            ->route('admin.bib-settings.index', ['tab' => 'format'])
            ->with('success', 'Pengaturan format nomor dada berhasil diperbarui.');
    }
}
