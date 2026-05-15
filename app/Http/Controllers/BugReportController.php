<?php

namespace App\Http\Controllers;

use App\Models\LaporanBug;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BugReportController extends Controller
{
    /**
     * Display the report dashboard with all bug reports.
     */
    public function index()
    {
        $daftarLaporanBug = LaporanBug::latest('created_at')->get();

        return Inertia::render('report/Dashboard', [
            'bugReports' => $daftarLaporanBug,
            'stats' => [
                'total' => $daftarLaporanBug->count(),
                'pending' => $daftarLaporanBug->where('status', 'pending')->count(),
                'in_progress' => $daftarLaporanBug->where('status', 'sedang dikerjakan')->count(),
                'resolved' => $daftarLaporanBug->where('status', 'tuntas diperbaiki')->count(),
            ],
        ]);
    }

    /**
     * Store a new bug report (Public/Global).
     */
    public function store(Request $permintaan)
    {
        $dataTervalidasi = $permintaan->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reporter_name' => 'required|string|max:255',
            'reporter_contact' => 'required|string|max:255',
            'url' => 'nullable|string|max:2048',
            'images.*' => 'nullable|image|max:5120', // Max 5MB per image
            'images' => 'nullable|array|max:5', // Max 5 images
        ]);

        $imagePaths = [];
        if ($permintaan->hasFile('images')) {
            foreach ($permintaan->file('images') as $image) {
                $imagePaths[] = $image->store('bug-reports', 'public');
            }
        }

        LaporanBug::create([
            'title' => $dataTervalidasi['title'],
            'description' => $dataTervalidasi['description'],
            'image_path' => $imagePaths,
            'reporter_name' => $dataTervalidasi['reporter_name'],
            'reporter_contact' => $dataTervalidasi['reporter_contact'],
            'url' => $dataTervalidasi['url'] ?? null,
            'user_id' => $permintaan->user()?->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Bug report submitted successfully! If it is fatal, we will contact you via '.$dataTervalidasi['reporter_contact']);
    }

    /**
     * Update the status of a specific bug report (Admin/Report Role).
     */
    public function updateStatus(Request $permintaan, LaporanBug $laporanBug)
    {
        $dataTervalidasi = $permintaan->validate([
            'status' => 'required|string|in:pending,sedang dikerjakan,tuntas diperbaiki',
        ]);

        $updateData = ['status' => $dataTervalidasi['status']];

        if ($dataTervalidasi['status'] === 'sedang dikerjakan' && ! $laporanBug->in_progress_at) {
            $updateData['in_progress_at'] = now();
        }

        if ($dataTervalidasi['status'] === 'tuntas diperbaiki' && ! $laporanBug->resolved_at) {
            $updateData['resolved_at'] = now();
        }

        $laporanBug->update($updateData);

        return back()->with('success', 'Status laporan bug berhasil diperbarui.');
    }
}
