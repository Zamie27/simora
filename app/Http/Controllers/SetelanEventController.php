<?php

namespace App\Http\Controllers;

use App\Models\JenisEvent;
use App\Models\PoinEvent;
use Illuminate\Http\Request;

class SetelanEventController extends Controller
{
    /**
     * Store a newly created event type.
     */
    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        JenisEvent::create([
            'coach_id' => auth()->id(),
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Jenis event berhasil ditambahkan');
    }

    /**
     * Update the specified event type.
     */
    public function updateType(Request $request, JenisEvent $type)
    {
        if ($type->coach_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $type->update($validated);

        return back()->with('success', 'Jenis event berhasil diperbarui');
    }

    /**
     * Remove the specified event type.
     */
    public function destroyType(JenisEvent $type)
    {
        if ($type->coach_id !== auth()->id()) {
            abort(403);
        }

        $type->delete();

        return back()->with('success', 'Jenis event berhasil dihapus');
    }

    /**
     * Store a newly created event point/category.
     */
    public function storePoint(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        PoinEvent::create([
            'coach_id' => auth()->id(),
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Poin kejuaraan berhasil ditambahkan');
    }

    /**
     * Update the specified event point.
     */
    public function updatePoint(Request $request, PoinEvent $point)
    {
        if ($point->coach_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $point->update($validated);

        return back()->with('success', 'Poin kejuaraan berhasil diperbarui');
    }

    /**
     * Remove the specified event point.
     */
    public function destroyPoint(PoinEvent $point)
    {
        if ($point->coach_id !== auth()->id()) {
            abort(403);
        }

        $point->delete();

        return back()->with('success', 'Poin kejuaraan berhasil dihapus');
    }
}
