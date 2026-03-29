<?php

namespace App\Http\Controllers\Athlete;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PhysicalController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $metrics = $user->physicalMetrics()
            ->orderBy('recorded_at', 'desc')
            ->get();

        $user->load('category');

        return Inertia::render('athlete/PhysicalProfile', [
            'metrics' => $metrics,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (! $user->date_of_birth || ! $user->gender) {
            $missing = [];
            if (! $user->date_of_birth) {
                $missing[] = 'tanggal lahir';
            }
            if (! $user->gender) {
                $missing[] = 'jenis kelamin';
            }

            $message = 'Harap lengkapi '.implode(' dan ', $missing).' di halaman Profil terlebih dahulu sebelum menginput data fisik.';

            return back()->withErrors(['profile_incomplete' => $message]);
        }

        if (! $user->category_id) {
            return back()->withErrors(['category' => 'Harap beritahu pelatih untuk menentukan kategori Anda terlebih dahulu.']);
        }

        $validated = $request->validate([
            'height' => 'required|numeric|min:50|max:250',
            'weight' => 'required|numeric|min:20|max:200',
            'recorded_at' => 'required|date',
        ]);

        // Calculate age at the time of recording using explicit year/month/day comparison
        // to avoid Carbon timezone off-by-one errors with diffInYears
        $recordedAt = Carbon::parse($validated['recorded_at'])->startOfDay();
        $dob = $user->date_of_birth;

        $age = $recordedAt->year - $dob->year;
        if ($recordedAt->month < $dob->month || ($recordedAt->month === $dob->month && $recordedAt->day < $dob->day)) {
            $age--;
        }
        $validated['age'] = max(0, $age);
        $validated['category'] = $user->category->name;

        $user->physicalMetrics()->create($validated);

        return back()->with('success', 'Data fisik berhasil disimpan.');
    }
}
