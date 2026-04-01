<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BugReportController extends Controller
{
    /**
     * Store a new bug report.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'reporter_name' => 'required|string|max:255',
            'reporter_contact' => 'required|string|max:255',
            'image' => 'nullable|image|max:5120', // Max 5MB
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('bug-reports', 'public');
        }

        BugReport::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image_path' => $imagePath,
            'reporter_name' => $validated['reporter_name'],
            'reporter_contact' => $validated['reporter_contact'],
            'user_id' => $request->user()?->id,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Bug report submitted successfully! If it is fatal, we will contact you via ' . $validated['reporter_contact']);
    }
}
