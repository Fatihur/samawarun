<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistanceCategory;
use Illuminate\Http\Request;

class DistanceCategoryController extends Controller
{
    public function index()
    {
        $categories = DistanceCategory::orderBy('name')->get();
        return view('admin.distance-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:distance_categories,name',
        ]);

        DistanceCategory::create([
            'name' => $request->name,
            'is_active' => true,
        ]);

        return back()->with('success', 'Kategori jarak berhasil ditambahkan.');
    }

    public function update(Request $request, DistanceCategory $distanceCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:distance_categories,name,' . $distanceCategory->id,
        ]);

        $distanceCategory->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Kategori jarak berhasil diperbarui.');
    }

    public function destroy(DistanceCategory $distanceCategory)
    {
        if ($distanceCategory->events()->exists()) {
            return back()->with(
                'error',
                'Kategori jarak tidak bisa dihapus karena masih terhubung dengan event. Hapus atau ubah event terlebih dahulu.'
            );
        }

        $distanceCategory->delete();
        return back()->with('success', 'Kategori jarak berhasil dihapus.');
    }

    public function toggleActive(DistanceCategory $distanceCategory)
    {
        $distanceCategory->update(['is_active' => !$distanceCategory->is_active]);
        return back()->with('success', 'Status kategori jarak berhasil diubah.');
    }
}
