<?php

$basePath = 'd:/Coding/Herd/simora/resources/js';

$replacements = [
    "@/actions/AthleteController" => "@/actions/LihatRingkasanDaftarAtletController",
    "@/actions/TrainingSessionController" => "@/actions/KelolaEvaluasiDanUmpanBalikLatihanController",
    "@/actions/TrainingPlanController" => "@/actions/KelolaJadwalSesiLatihanController",
    "@/actions/TrainingLogController" => "@/actions/AnalisaGrafikDanStatistikLatihanController",
    "@/actions/PerformanceComparisonController" => "@/actions/BandingkanPerformaDanMemfilterRiwayatController",
    "@/actions/EventController" => "@/actions/KelolaEventDanPartisipasiController",
    "@/actions/DocumentController" => "@/actions/KelolaDokumenLisensiUciController",
    "@/actions/MessageController" => "@/actions/KelolaPesanController",
    "@/actions/PhysicalMetricController" => "@/actions/KelolaDataMetrikFisikController",
    "@/actions/UserController" => "@/actions/MemverifikasiPendaftaranDanMenetapkanPelatihController",
    
    // Merged
    "@/actions/KategoriController" => "@/actions/LihatRingkasanDaftarAtletController",
    "@/actions/JenisLatihanController" => "@/actions/KelolaJadwalSesiLatihanController",
    "@/actions/SetelanKelolaEventDanPartisipasiController" => "@/actions/KelolaEventDanPartisipasiController",
];

function processDirectory($dir, $extensions, $replacements) {
    if (!is_dir($dir)) return;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), $extensions)) {
            $path = $file->getPathname();
            $content = file_get_contents($path);
            $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
            if ($content !== $newContent) {
                file_put_contents($path, $newContent);
                echo "Fixed action imports in: $path\n";
            }
        }
    }
}

processDirectory($basePath, ['vue', 'ts', 'js'], $replacements);

echo "Action imports fixed.\n";
