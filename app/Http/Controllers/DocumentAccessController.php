<?php

namespace App\Http\Controllers;

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
    public function show(Request $request, User $athlete, string $type)
    {
        $viewer = $request->user();

        // Security Check: Who can view this document?
        // 1. Management
        // 2. The athlete themselves
        // 3. The athlete's coach
        $canView = $viewer->role->name === 'Manajemen' ||
                   $viewer->id === $athlete->id ||
                   ($viewer->role->name === 'Pelatih' && $athlete->coach_id === $viewer->id);

        if (! $canView) {
            abort(Response::HTTP_FORBIDDEN, 'Unauthorized to view this document.');
        }

        $profile = $athlete->athleteProfile;

        if (! $profile) {
            abort(Response::HTTP_NOT_FOUND, 'Athlete profile not found.');
        }

        $path = null;
        switch ($type) {
            case 'profile_photo':
                $path = $profile->profile_photo_path;
                break;
            case 'birth_certificate':
                $path = $profile->birth_certificate_path;
                break;
            case 'family_card':
                $path = $profile->family_card_path;
                break;
            case 'id_card':
                $path = $profile->id_card_path;
                break;
            case 'license':
                $path = $profile->license_path;
                break;
            default:
                abort(Response::HTTP_BAD_REQUEST, 'Invalid document type.');
        }

        if (! $path || ! Storage::disk('local')->exists($path)) {
            abort(Response::HTTP_NOT_FOUND, 'Document not found.');
        }

        return response()->file(Storage::disk('local')->path($path));
    }
}
