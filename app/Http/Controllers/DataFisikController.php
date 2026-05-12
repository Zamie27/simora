<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\User;
use App\Repositories\TrainingLogRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DataFisikController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository
    ) {}

    /**
     * Display the index view based on role.
     */
    public function index(Request $request)
    {
        $role = $request->user()->role->name ?? '';

        if ($role === 'Atlet') {
            return $this->athleteIndex($request);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Store data based on role.
     */
    public function store(Request $request)
    {
        $role = $request->user()->role->name ?? '';

        if ($role === 'Atlet') {
            return $this->athleteStore($request);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Show detailed physical metrics.
     */
    public function show(Request $request, User $athlete)
    {
        $role = $request->user()->role->name ?? '';

        if ($role === 'Manajemen') {
            return $this->managementShow($request, $athlete);
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * -----------------------------------------------------------------
     * ATLET METHODS
     * -----------------------------------------------------------------
     */
    private function athleteIndex(Request $request): Response
    {
        $user = $request->user();

        $metrics = $user->physicalMetrics()
            ->orderBy('recorded_at', 'desc')
            ->get();

        $user->load('category');

        return Inertia::render('athlete/PhysicalProfile', [
            'metrics' => $metrics,
            'categories' => Kategori::orderBy('name')->get(),
        ]);
    }

    private function athleteStore(Request $request)
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

    /**
     * -----------------------------------------------------------------
     * MANAJEMEN METHODS
     * -----------------------------------------------------------------
     */
    private function managementShow(Request $request, User $athlete)
    {
        $athlete->load(['physicalMetrics' => function ($query) {
            $query->orderBy('recorded_at', 'desc');
        }]);

        return Inertia::render('management/AthletePhysicalReview', [
            'athlete' => $athlete,
        ]);
    }
}
