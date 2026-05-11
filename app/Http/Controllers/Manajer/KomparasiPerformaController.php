<?php

namespace App\Http\Controllers\Manajer;

use App\Http\Controllers\Controller;
use App\Repositories\TrainingLogRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KomparasiPerformaController extends Controller
{
    public function __construct(
        private TrainingLogRepository $logRepository
    ) {}

    /**
     * Display the performance comparison page for management.
     */
    public function index(Request $request)
    {
        return Inertia::render('management/PerformanceComparison');
    }
}
