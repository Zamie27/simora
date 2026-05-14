<?php

$basePath = 'd:/Coding/Herd/simora/app/Http/Controllers';

$variableTranslations = [
    // Entities
    '$athlete' => '$atlet',
    '$athletes' => '$daftarAtlet',
    '$coach' => '$pelatih',
    '$coaches' => '$daftarPelatih',
    '$manager' => '$manajer',
    '$managers' => '$daftarManajer',
    '$user' => '$pengguna',
    '$users' => '$daftarPengguna',

    // Core Domain
    '$category' => '$kategori',
    '$categories' => '$daftarKategori',
    '$exerciseType' => '$jenisLatihan',
    '$exerciseTypes' => '$daftarJenisLatihan',
    '$session' => '$sesi',
    '$sessions' => '$daftarSesi',
    '$log' => '$catatan',
    '$logs' => '$daftarCatatan',
    '$trainingLogs' => '$riwayatLatihan',
    '$event' => '$acara',
    '$events' => '$daftarAcara',
    '$point' => '$poin',
    '$points' => '$daftarPoin',
    '$type' => '$tipe',
    '$message' => '$pesan',
    '$messages' => '$daftarPesan',
    '$profile' => '$profil',
    '$bugReport' => '$laporanBug',
    '$bugReports' => '$daftarLaporanBug',

    // Properties/Context
    '$statistics' => '$statistik',
    '$performanceTrend' => '$trenPerforma',
    '$startDate' => '$tanggalMulai',
    '$endDate' => '$tanggalSelesai',
    '$metrics' => '$metrik',
    '$recordedAt' => '$waktuPencatatan',
    '$dob' => '$tanggalLahir',
    '$age' => '$usia',
    '$validated' => '$dataTervalidasi',
    '$request' => '$permintaan',
    '$missing' => '$dataHilang',
    '$filename' => '$namaFile',
    '$extension' => '$ekstensi',
    '$path' => '$jalur',
    '$cachedOtp' => '$otpTersimpan',
    '$isVerified' => '$telahDiverifikasi',
    '$query' => '$kueri',

    // Specific contexts
    '->coach_id' => '->coach_id', // database column, DO NOT TRANSLATE
    '->coach' => '->coach', // relation name, keep as is for now unless we rename relations
];

// We only want to replace variables exactly (with word boundaries or $ prefix)
// For simplicity, we just use string replace since variables start with $ and are usually distinct
// but we need to be careful with substrings like $athlete matching $athletes
// So we sort by length descending
uksort($variableTranslations, function ($a, $b) {
    return strlen($b) - strlen($a);
});

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($basePath));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $newContent = str_replace(array_keys($variableTranslations), array_values($variableTranslations), $content);
        if ($content !== $newContent) {
            file_put_contents($file->getPathname(), $newContent);
            echo 'Translated variables in: '.$file->getFilename()."\n";
        }
    }
}
echo "Variable translation completed.\n";
