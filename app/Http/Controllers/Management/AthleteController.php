<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Models\AthleteProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AthleteController extends Controller
{
    /**
     * Display athlete details and documents.
     */
    public function show(User $athlete)
    {
        // Ensure user is an athlete
        if ($athlete->role->name !== 'Atlet') {
            abort(404);
        }

        $athlete->load('coach', 'athleteProfile');

        return Inertia::render('management/AthleteDetail', [
            'athlete' => $athlete,
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
