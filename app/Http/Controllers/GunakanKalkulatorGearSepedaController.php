<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GunakanKalkulatorGearSepedaController extends Controller
{
    /**
     * UC-15: Gunakan Kalkulator Gear Sepeda
     * Turunan: Menampilkan halaman kalkulator gear sepeda mandiri.
     */
    public function tampilHalamanKalkulator(Request $permintaan): Response
    {
        return Inertia::render('kalkulator-gear/Index');
    }
}
