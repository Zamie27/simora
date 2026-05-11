<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
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
        $bugReports = LaporanBug::latest('created_at')->get();

        return Inertia::render('report/Dashboard', [
            'bugReports' => $bugReports,
            'stats' => [
                'total' => $bugReports->count(),
                'pending' => $bugReports->where('status', 'pending')->count(),
                'in_progress' => $bugReports->where('status', 'sedang dikerjakan')->count(),
                'resolved' => $bugReports->where('status', 'tuntas diperbaiki')->count(),
            ],
        ]);
    }

    /**
     * Store a new bug report (Public/Global).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reporter_name' => 'required|string|max:255',
            'reporter_contact' => 'required|string|max:255',
            'url' => 'nullable|string|max:2048',
            'images.*' => 'nullable|image|max:5120', // Max 5MB per image
            'images' => 'nullable|array|max:5', // Max 5 images
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('bug-reports', 'public');
            }
        }

        LaporanBug::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image_path' => $imagePaths,
            'reporter_name' => $validated['reporter_name'],
            'reporter_contact' => $validated['reporter_contact'],
            'url' => $validated['url'] ?? null,
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Bug report submitted successfully! If it is fatal, we will contact you via '.$validated['reporter_contact']);
    }

    /**
     * Update the status of a specific bug report (Admin/Report Role).
     */
    public function updateStatus(Request $request, LaporanBug $bugReport)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,sedang dikerjakan,tuntas diperbaiki',
        ]);

        $bugReport->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Status laporan bug berhasil diperbarui.');
    }
}
