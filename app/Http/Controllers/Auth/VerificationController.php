<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $permintaan): RedirectResponse
    {
        if ($permintaan->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $permintaan->user()->sendManualEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
