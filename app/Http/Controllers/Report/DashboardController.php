<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\BugReport;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the report dashboard with all bug reports.
     */
    public function index()
    {
        $bugReports = BugReport::latest('created_at')->get();

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
     * Update the status of a specific bug report.
     */
    public function updateStatus(Request $request, BugReport $bugReport)
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
