<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DocumentAccessController extends Controller
{
    /**
     * View an athlete's private document.
     * Types: profile_photo, birth_certificate, family_card, id_card, license
     */
    public function show(Request $permintaan, User $atlet, string $tipe)
    {
        $viewer = $permintaan->user();

        // Security Check: Who can view this document?
        // 1. Management
        // 2. The athlete themselves
        // 3. The athlete's coach
        $canView = $viewer->role->name === 'Manajemen' ||
                   $viewer->id === $atlet->id ||
                   ($viewer->role->name === 'Pelatih' && $atlet->coach_id === $viewer->id);

        if (! $canView) {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized to view this document.');
        }

        $profil = $atlet->athleteProfile;

        if (! $profil) {
            abort(Response::HTTP_NOT_FOUND, 'Athlete profile not found.');
        }

        $jalur = null;
        switch ($tipe) {
            case 'profile_photo':
                $jalur = $profil->profile_photo_path;
                break;
            case 'birth_certificate':
                $jalur = $profil->birth_certificate_path;
                break;
            case 'family_card':
                $jalur = $profil->family_card_path;
                break;
            case 'id_card':
                $jalur = $profil->id_card_path;
                break;
            case 'license':
                $jalur = $profil->license_path;
                break;
            default:
                abort(Response::HTTP_BAD_REQUEST, 'Invalid document type.');
        }

        if (! $jalur || ! Storage::disk('local')->exists($jalur)) {
            abort(Response::HTTP_NOT_FOUND, 'Document not found.');
        }

        return response()->file(Storage::disk('local')->path($jalur));
    }
}
