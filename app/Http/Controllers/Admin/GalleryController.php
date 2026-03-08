<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        $galleries = Gallery::orderBy('sort_order')->orderByDesc('created_at')->get();

        return view('admin.gallery.index', compact('galleries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $path = $request->file('image')->store('gallery', 'public');

        $maxOrder = Gallery::max('sort_order') ?? 0;

        Gallery::create([
            'title' => $request->title,
            'image_path' => $path,
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Foto berhasil ditambahkan ke galeri.');
    }

    public function update(Request $request, Gallery $gallery)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
        ]);

        $gallery->update([
            'title' => $request->title,
        ]);

        return back()->with('success', 'Keterangan foto berhasil diperbarui.');
    }

    public function destroy(Gallery $gallery)
    {
        if ($gallery->image_path) {
            Storage::disk('public')->delete($gallery->image_path);
        }

        $gallery->delete();

        return back()->with('success', 'Foto berhasil dihapus dari galeri.');
    }

    public function toggleActive(Gallery $gallery)
    {
        $gallery->update(['is_active' => !$gallery->is_active]);

        return back()->with('success', 'Status foto berhasil diubah.');
    }
}
