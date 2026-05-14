<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class KelolaDokumenLisensiUciController extends Controller
{
    /**
     * View the athlete documents page.
     */
    public function tampilData(Request $permintaan)
    {
        $pengguna = $permintaan->user();
        $profil = $pengguna->athleteProfile;

        return Inertia::render('atlet/Documents', [
            'profile' => $profil,
        ]);
    }

    /**
     * Upload personal documents.
     */
    public function simpanData(Request $permintaan)
    {
        $permintaan->validate([
            'profile_photo' => 'nullable|image|max:2048',
            'birth_certificate' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
            'family_card' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
            'id_card' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $pengguna = $permintaan->user();
        $profil = $pengguna->athleteProfile ?? new ProfilAtlet(['user_id' => $pengguna->id]);

        $files = [
            'profile_photo' => 'profile_photo_path',
            'birth_certificate' => 'birth_certificate_path',
            'family_card' => 'family_card_path',
            'id_card' => 'id_card_path',
        ];

        foreach ($files as $inputKey => $dbColumn) {
            if ($permintaan->hasFile($inputKey)) {
                // Delete old file if exists
                if ($profil->$dbColumn) {
                    Storage::disk('local')->delete($profil->$dbColumn);
                }

                // Store new file in private_documents directory
                $jalur = $permintaan->file($inputKey)->store('private_documents/'.$pengguna->id, 'local');
                $profil->$dbColumn = $jalur;
            }
        }

        if ($permintaan->hasFile('profile_photo')) {
            $pengguna->updateProfilePhoto($permintaan->file('profile_photo'));
        }

        $profil->save();

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }
}
