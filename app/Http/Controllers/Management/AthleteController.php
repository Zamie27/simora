<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\AthleteProfile;
use App\Models\Category;
use App\Models\Role;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AthleteController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository
    ) {}

    /**
     * Display athlete details and documents.
     */
    public function show(Request $request, User $athlete)
    {
        // Ensure user is an athlete
        if ($athlete->role->name !== 'Atlet') {
            abort(404);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $athlete->load(['coach', 'athleteProfile', 'category', 'physicalMetrics' => function ($query) {
            $query->orderBy('recorded_at', 'desc');
        }]);

        $coachRole = Role::where('name', 'Pelatih')->first();

        $logs = $this->logRepository->getForAthlete($athlete->id, $startDate, $endDate);
        $statistics = $this->logRepository->getStatistics($athlete->id, $startDate, $endDate);
        $performanceTrend = $this->logRepository->getPerformanceTrend($athlete->id, $startDate, $endDate);

        return Inertia::render('management/AthleteDetail', [
            'athlete' => $athlete,
            'coaches' => User::where('role_id', $coachRole?->id)->get(),
            'trainingLogs' => $logs,
            'statistics' => $statistics,
            'performanceTrend' => $performanceTrend,
            'categories' => Category::orderBy('name')->get(),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Upload license and update UCI ID.
     */
    public function uploadLicense(Request $request, User $athlete)
    {
        $request->validate([
            'uci_id' => 'required|string|max:50',
            'license_valid_until' => 'required|date',
            'license_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $profile = $athlete->athleteProfile ?? new AthleteProfile(['user_id' => $athlete->id]);

        $profile->uci_id = $request->uci_id;
        $profile->license_valid_until = $request->license_valid_until;

        if ($request->hasFile('license_file')) {
            // Delete old license file if exists
            if ($profile->license_path) {
                Storage::disk('local')->delete($profile->license_path);
            }

            $extension = $request->file('license_file')->getClientOriginalExtension();
            // Name file as UCI_ID.extension
            $filename = 'UCI_'.$request->uci_id.'_'.time().'.'.$extension;

            // Store new file securely
            $path = $request->file('license_file')->storeAs('private_documents/'.$athlete->id, $filename, 'local');
            $profile->license_path = $path;
        }

        $profile->save();

        return back()->with('success', 'Lisensi dan UCI ID berhasil diperbarui.');
    }
}
