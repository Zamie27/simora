<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AthleteController extends Controller
{
    public function index(Request $request): Response
    {
        $coach = $request->user();

        $athletes = User::whereRole('Atlet')
            ->where('coach_id', $coach->id)
            ->with(['latestPhysicalMetric'])
            ->get();

        return Inertia::render('coach/Athletes', [
            'athletes' => $athletes,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(User $athlete): Response
    {
        $this->authorizeAccess($athlete);

        $athlete->load(['physicalMetrics' => function ($query) {
            $query->orderBy('recorded_at', 'desc');
        }]);

        return Inertia::render('coach/AthleteDetail', [
            'athlete' => $athlete,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function storeMetric(Request $request, User $athlete)
    {
        $this->authorizeAccess($athlete);

        if (! $athlete->date_of_birth || ! $athlete->gender) {
            $missing = [];
            if (! $athlete->date_of_birth) {
                $missing[] = 'tanggal lahir';
            }
            if (! $athlete->gender) {
                $missing[] = 'jenis kelamin';
            }

            $message = 'Atlet belum mengisi '.implode(' dan ', $missing).' di profil mereka. Harap beritahu atlet untuk melengkapi profil.';

            return back()->withErrors(['profile_incomplete' => $message]);
        }

        $validated = $request->validate([
            'height' => 'required|numeric|min:50|max:250',
            'weight' => 'required|numeric|min:20|max:200',
            'category' => 'required|string|max:255',
            'recorded_at' => 'required|date',
        ]);

        // Calculate age at the time of recording using explicit year/month/day comparison
        // to avoid Carbon timezone off-by-one errors with diffInYears
        $recordedAt = Carbon::parse($validated['recorded_at'])->startOfDay();
        $dob = $athlete->date_of_birth;

        $age = $recordedAt->year - $dob->year;
        if ($recordedAt->month < $dob->month || ($recordedAt->month === $dob->month && $recordedAt->day < $dob->day)) {
            $age--;
        }
        $validated['age'] = max(0, $age);

        $athlete->physicalMetrics()->create($validated);

        return back()->with('success', 'Data fisik atlet berhasil diperbarui.');
    }

    private function authorizeAccess(User $athlete)
    {
        if ($athlete->coach_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki akses ke data atlet ini.');
        }
    }
}
