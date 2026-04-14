<?php

namespace App\Http\Controllers\Athlete;

use App\Http\Controllers\Controller;
use App\Models\AthleteProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentController extends Controller
{
    /**
     * View the athlete documents page.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->athleteProfile;

        return Inertia::render('athlete/Documents', [
            'profile' => $profile,
        ]);
    }

    /**
     * Upload personal documents.
     */
    public function store(Request $request)
    {
        $request->validate([
            'profile_photo' => 'nullable|image|max:2048',
            'birth_certificate' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
            'family_card' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_card' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $user = $request->user();
        $profile = $user->athleteProfile ?? new AthleteProfile(['user_id' => $user->id]);

        $files = [
            'profile_photo' => 'profile_photo_path',
            'birth_certificate' => 'birth_certificate_path',
            'family_card' => 'family_card_path',
            'id_card' => 'id_card_path',
        ];

        foreach ($files as $inputKey => $dbColumn) {
            if ($request->hasFile($inputKey)) {
                // Delete old file if exists
                if ($profile->$dbColumn) {
                    Storage::disk('local')->delete($profile->$dbColumn);
                }

                // Store new file in private_documents directory
                $path = $request->file($inputKey)->store('private_documents/'.$user->id, 'local');
                $profile->$dbColumn = $path;
            }
        }

        if ($request->hasFile('profile_photo')) {
            $user->updateProfilePhoto($request->file('profile_photo'));
        }

        $profile->save();

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }
}
