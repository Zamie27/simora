<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\EventPoint;
use App\Models\EventType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EventSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('management/EventSettings/Index', [
            'eventTypes' => EventType::with('coach')->latest()->get(),
            'eventPoints' => EventPoint::with('coach')->latest()->get(),
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

        EventType::create([
            'coach_id' => null, // Global type
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Jenis event global berhasil ditambahkan');
    }

    /**
     * Update the specified event type.
     */
    public function updateType(Request $request, EventType $type)
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
    public function destroyType(EventType $type)
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

        EventPoint::create([
            'coach_id' => null, // Global point
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Poin kejuaraan global berhasil ditambahkan');
    }

    /**
     * Update the specified event point.
     */
    public function updatePoint(Request $request, EventPoint $point)
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
    public function destroyPoint(EventPoint $point)
    {
        $point->delete();

        return back()->with('success', 'Poin kejuaraan berhasil dihapus');
    }
}
