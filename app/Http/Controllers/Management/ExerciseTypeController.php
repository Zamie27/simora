<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\ExerciseType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExerciseTypeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('management/ExerciseTypes', [
            'exerciseTypes' => ExerciseType::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        ExerciseType::create($validated);

        return back()->with('success', 'Jenis latihan berhasil ditambahkan');
    }

    public function update(Request $request, ExerciseType $exerciseType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $exerciseType->update($validated);

        return back()->with('success', 'Jenis latihan berhasil diperbarui');
    }

    public function destroy(ExerciseType $exerciseType): RedirectResponse
    {
        // Check if there are sessions using this type
        if ($exerciseType->sessions()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus jenis latihan yang sudah memiliki sesi latihan.');
        }

        $exerciseType->delete();

        return back()->with('success', 'Jenis latihan berhasil dihapus');
    }
}
