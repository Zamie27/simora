<?php

$basePath = 'd:/Coding/Herd/simora/resources/js';

$replacements = [
    // Route Prefixes
    'coach.' => 'pelatih.',
    'management.' => 'manajemen.',
    'athlete.' => 'atlet.',

    // Sub-route Names (translated or renamed)
    '.trainingSessions' => '.sesiLatihan',
    '.trainingPlans' => '.jadwalLatihan',
    '.trainingLogs' => '.riwayatLatihan',
    '.performanceComparison' => '.komparasi',
    '.performanceReports' => '.laporan',
    '.events' => '.acara',
    '.eventTypes' => '.tipeAcara',
    '.eventPoints' => '.poinAcara',
    '.physicalMetrics' => '.fisik',
    '.messages' => '.pesan',
    '.documents' => '.dokumen',
    '.license-documents' => '.dokumenLisensi',
    '.users' => '.pengguna',
    '.profile' => '.profil',
    '.registration-verification' => '.verifikasiPendaftaran',
    '.athletes' => '.atlet',
    '.categories' => '.kategori',
    '.exerciseTypes' => '.jenisLatihan',
    '.event-settings' => '.pengaturan-acara',

    // Specific ones found in web.php
    '.pending' => '.tertunda',
    '.verify' => '.verifikasi',
    '.comparison' => '.komparasi',
    '.quickUpdate' => '.quickUpdate',

    // Hyphenated to camelCase (Wayfinder standard)
    'sesi-latihan' => 'sesiLatihan',
    'riwayat-latihan' => 'riwayatLatihan',
    'jadwal-latihan' => 'jadwalLatihan',
    'tipe-acara' => 'tipeAcara',
    'poin-acara' => 'poinAcara',
    'jenis-latihan' => 'jenisLatihan',
    'verifikasi-pendaftaran' => 'verifikasiPendaftaran',
    'pengaturan-acara' => 'pengaturanAcara',
];

function processDirectory($dir, $extensions, $replacements)
{
    if (! is_dir($dir)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array($file->getExtension(), $extensions)) {
            $path = $file->getPathname();
            $content = file_get_contents($path);
            $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
            if ($content !== $newContent) {
                file_put_contents($path, $newContent);
                echo "Fixed usages in: $path\n";
            }
        }
    }
}

processDirectory($basePath, ['vue', 'ts', 'js'], $replacements);

echo "Vue usages fixed.\n";
