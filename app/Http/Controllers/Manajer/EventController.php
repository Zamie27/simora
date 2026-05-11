<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Models\JenisEvent;
use App\Models\PoinEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('management/EventSettings/Index', [
            'eventTypes' => JenisEvent::with('coach')->latest()->get(),
            'eventPoints' => PoinEvent::with('coach')->latest()->get(),
        ]);
    }

    /**
     * Store a newly created event type.
     */
    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        JenisEvent::create([
            'coach_id' => null, // Global type
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Jenis event global berhasil ditambahkan');
    }

    /**
     * Update the specified event type.
     */
    public function updateType(Request $request, JenisEvent $type)
    {
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
            'coach_id' => null, // Global point
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Poin kejuaraan global berhasil ditambahkan');
    }

    /**
     * Update the specified event point.
     */
    public function updatePoint(Request $request, PoinEvent $point)
    {
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
        $point->delete();

        return back()->with('success', 'Poin kejuaraan berhasil dihapus');
    }
}
