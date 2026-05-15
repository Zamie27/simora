<?php

namespace App\Http\Controllers;

use App\Models\ProfilAtlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KelolaDokumenLisensiUciController extends Controller
{
    /**
     * View the UCI license and documents page based on role.
     */
    public function tampilHalamanLisensi(Request $permintaan): Response
    {
        $pengguna = $permintaan->user();
        $role = $pengguna->role->name;

        if ($role === 'Atlet') {
            return Inertia::render('dokumen/Index', [
                'athlete' => $pengguna->load('athleteProfile'),
                'role' => $role,
            ]);
        }

        if ($role === 'Manajemen' || $role === 'Pelatih') {
            $atletKueri = User::whereRole('Atlet')->with(['athleteProfile', 'coach']);

            if ($role === 'Pelatih') {
                $atletKueri->where('coach_id', $pengguna->id);
            }

            return Inertia::render('dokumen/Index', [
                'athletes' => $atletKueri->get(),
                'role' => $role,
            ]);
        }

        abort(403);
    }

    /**
     * Upload personal documents (Atlet only).
     */
    public function simpanDokumenPribadi(Request $permintaan): RedirectResponse
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
            'birth_certificate' => 'birth_certificate_path',
            'family_card' => 'family_card_path',
            'id_card' => 'id_card_path',
        ];

        foreach ($files as $inputKey => $dbColumn) {
            if ($permintaan->hasFile($inputKey)) {
                if ($profil->$dbColumn) {
                    Storage::disk('local')->delete($profil->$dbColumn);
                }
                $jalur = $permintaan->file($inputKey)->store('private_documents/'.$pengguna->id, 'local');
                $profil->$dbColumn = $jalur;
            }
        }

        if ($permintaan->hasFile('profile_photo')) {
            $pengguna->updateProfilePhoto($permintaan->file('profile_photo'));
        }

        $profil->save();

        return back()->with('success', 'Dokumen pribadi berhasil diunggah.');
    }

    /**
     * Update UCI License data (Manajemen only).
     */
    public function perbaruiLisensiUci(Request $permintaan, User $atlet): RedirectResponse
    {
        if ($permintaan->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $permintaan->validate([
            'uci_id' => 'required|string|max:50',
            'license_valid_until' => 'required|date',
            'license_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $profil = $atlet->athleteProfile ?? new ProfilAtlet(['user_id' => $atlet->id]);

        $profil->uci_id = $permintaan->uci_id;
        $profil->license_valid_until = $permintaan->license_valid_until;

        if ($permintaan->hasFile('license_file')) {
            if ($profil->license_path) {
                Storage::disk('local')->delete($profil->license_path);
            }
            $jalur = $permintaan->file('license_file')->store('private_documents/'.$atlet->id, 'local');
            $profil->license_path = $jalur;
        }

        $profil->save();

        return back()->with('success', 'Lisensi UCI berhasil diperbarui.');
    }

    /**
     * Download all documents for an athlete as ZIP.
     */
    public function unduhSemuaDokumen(User $atlet): BinaryFileResponse
    {
        if (auth()->user()->role->name !== 'Manajemen') {
            abort(403);
        }

        $profil = $atlet->athleteProfile;

        $zip = new \ZipArchive;
        $zipFileName = str_replace(' ', '_', strtolower($atlet->name)).'_dokumen.zip';
        $tempPath = storage_path('app/'.$zipFileName);

        if ($zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $filesToZip = [];

            // Athlete Profile Documents
            if ($profil) {
                // Photo Profile from athlete profile (local disk)
                if ($profil->profile_photo_path) {
                    $filesToZip['foto_profil'] = ['disk' => 'local', 'path' => $profil->profile_photo_path];
                }

                if ($profil->birth_certificate_path) {
                    $filesToZip['akte_kelahiran'] = ['disk' => 'local', 'path' => $profil->birth_certificate_path];
                }
                if ($profil->family_card_path) {
                    $filesToZip['kartu_keluarga'] = ['disk' => 'local', 'path' => $profil->family_card_path];
                }
                if ($profil->id_card_path) {
                    $filesToZip['ktp'] = ['disk' => 'local', 'path' => $profil->id_card_path];
                }
            }

            if (empty($filesToZip)) {
                $zip->close();
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
                abort(404, 'Tidak ada dokumen untuk diunduh.');
            }

            foreach ($filesToZip as $name => $fileInfo) {
                if (Storage::disk($fileInfo['disk'])->exists($fileInfo['path'])) {
                    $extension = pathinfo($fileInfo['path'], PATHINFO_EXTENSION);
                    $zip->addFile(Storage::disk($fileInfo['disk'])->path($fileInfo['path']), $name.'.'.$extension);
                }
            }

            $zip->close();
        } else {
            abort(500, 'Gagal membuat file ZIP.');
        }

        return response()->download($tempPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
