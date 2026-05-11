<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatistikLatihanController extends Controller
{
    /**
     * Display global training statistics for management.
     */
    public function index(Request $request)
    {
        // Global stats are usually handled by the main Dashboard or Reports
        // This controller can handle specific global statistical deep-dives
        return Inertia::render('management/GlobalStatistics');
    }
}
