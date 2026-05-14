<?php

$routeFile = 'd:/Coding/Herd/simora/routes/web.php';
$content = file_get_contents($routeFile);

$replacements = [
    // URLs
    "'athletes'" => "'atlet'",
    "'athletes/" => "'atlet/",
    "'categories'" => "'kategori'",
    "'categories/" => "'kategori/",
    "'exercise-types'" => "'jenis-latihan'",
    "'exercise-types/" => "'jenis-latihan/",
    "'reports'" => "'laporan'",
    "'reports/" => "'laporan/",
    "'event-settings'" => "'pengaturan-acara'",
    "'event-types'" => "'tipe-acara'",
    "'event-types/" => "'tipe-acara/",
    "'event-points'" => "'poin-acara'",
    "'event-points/" => "'poin-acara/",
    "'training-sessions'" => "'sesi-latihan'",
    "'training-sessions/" => "'sesi-latihan/",
    "'training-logs/" => "'riwayat-latihan/",
    "'performance-comparison'" => "'komparasi-performa'",
    "'performance-comparison/data'" => "'komparasi-performa/data'",
    "'events'" => "'acara'",
    "'events/" => "'acara/",
    "'training-plans'" => "'jadwal-latihan'",
    "'messages'" => "'pesan'",
    "'messages/" => "'pesan/",
    "'physical'" => "'fisik'",
    "'training'" => "'latihan'",
    "'training/log'" => "'latihan/riwayat'",
    "'training/log/" => "'latihan/riwayat/",
    "'documents'" => "'dokumen'",
    "'waiting-verification'" => "'menunggu-verifikasi'",

    // Parameters
    '{user}' => '{pengguna}',
    '{athlete}' => '{atlet}',
    '{category}' => '{kategori}',
    '{exerciseType}' => '{jenisLatihan}',
    '{type}' => '{tipe}',
    '{point}' => '{poin}',
    '{session}' => '{sesi}',
    '{log}' => '{catatan}',
    '{event}' => '{acara}',
    '{message}' => '{pesan}',
    '{bugReport}' => '{laporanBug}',

    // Route Names (already handled coach/athlete/management but need to handle suffixes)
    "name('users.index')" => "name('pengguna.index')",
    "name('users.store')" => "name('pengguna.store')",
    "name('users.update')" => "name('pengguna.update')",
    "name('users.destroy')" => "name('pengguna.destroy')",
    "name('users.pending')" => "name('pengguna.tertunda')",
    "name('users.verify')" => "name('pengguna.verifikasi')",

    "name('athletes." => "name('atlet.",
    "name('categories." => "name('kategori.",
    "name('exercise-types." => "name('jenis-latihan.",
    "name('reports." => "name('laporan.",
    "name('event-settings." => "name('pengaturan-acara.",
    "name('event-types." => "name('tipe-acara.",
    "name('event-points." => "name('poin-acara.",
    "name('training-sessions." => "name('sesi-latihan.",
    "name('training-logs." => "name('riwayat-latihan.",
    "name('performance." => "name('komparasi.",
    "name('events." => "name('acara.",
    "name('training-plans." => "name('jadwal-latihan.",
    "name('messages." => "name('pesan.",
    "name('physical." => "name('fisik.",
    "name('training." => "name('latihan.",
    "name('documents." => "name('dokumen.",
    "name('verification." => "name('verifikasi.",
];

$content = str_replace(array_keys($replacements), array_values($replacements), $content);
file_put_contents($routeFile, $content);
echo "Routes fully translated to Indonesian.\n";
