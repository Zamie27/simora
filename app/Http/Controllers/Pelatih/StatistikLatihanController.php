<?php

namespace App\Http\Controllers\Pelatih;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StatistikLatihanController extends Controller
{
    /**
     * Analyze performance statistics for specific athletes.
     */
    public function index(Request $request)
    {
        return Inertia::render('coach/StatisticsAnalysis');
    }
}
