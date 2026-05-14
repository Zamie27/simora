<?php

$basePath = 'd:/Coding/Herd/simora';

// 1. Rename directories in Vue pages
$dirs = [
    'coach' => 'pelatih',
    'athlete' => 'atlet',
    'management' => 'manajemen',
];

foreach ($dirs as $old => $new) {
    $oldPath = "$basePath/resources/js/pages/$old";
    $newPath = "$basePath/resources/js/pages/$new";
    if (is_dir($oldPath)) {
        rename($oldPath, $newPath);
        echo "Renamed folder $old to $new\n";
    }
}

// 2. Search and replace strings in all Vue and Route files
$replacements = [
    // Web Routes URL / Prefix
    "prefix('management')" => "prefix('manajemen')",
    "name('management.')" => "name('manajemen.')",
    "prefix('coach')" => "prefix('pelatih')",
    "name('coach.')" => "name('pelatih.')",
    "prefix('athlete')" => "prefix('atlet')",
    "name('athlete.')" => "name('atlet.')",

    // Web routes paths (just in case they don't use prefix directly)
    'management/' => 'manajemen/',
    'coach/' => 'pelatih/',
    'athlete/' => 'atlet/',

    // Vue Inertia Component Paths
    'management/Athletes' => 'manajemen/Athletes',
    'management/AthleteDetail' => 'manajemen/AthleteDetail',
    'management/Categories' => 'manajemen/Categories',
    'management/Dashboard' => 'manajemen/Dashboard',
    'management/EventSettings' => 'manajemen/EventSettings',
    'management/ExerciseTypes' => 'manajemen/ExerciseTypes',
    'management/PendingUsers' => 'manajemen/PendingUsers',
    'management/Reports' => 'manajemen/Reports',
    'management/Users' => 'manajemen/Users',

    'coach/AthleteDetail' => 'pelatih/AthleteDetail',
    'coach/Athletes' => 'pelatih/Athletes',
    'coach/Dashboard' => 'pelatih/Dashboard',
    'coach/EventDetail' => 'pelatih/EventDetail',
    'coach/Events' => 'pelatih/Events',
    'coach/Messages' => 'pelatih/Messages',
    'coach/PerformanceComparison' => 'pelatih/PerformanceComparison',
    'coach/Reports' => 'pelatih/Reports',
    'coach/SessionDetail' => 'pelatih/SessionDetail',
    'coach/TrainingPlans' => 'pelatih/TrainingPlans',
    'coach/TrainingSessions' => 'pelatih/TrainingSessions',

    'athlete/Dashboard' => 'atlet/Dashboard',
    'athlete/Events' => 'atlet/Events',
    'athlete/Messages' => 'atlet/Messages',
    'athlete/PhysicalMetrics' => 'atlet/PhysicalMetrics',
    'athlete/TrainingLogs' => 'atlet/TrainingLogs',
    'athlete/UciLicense' => 'atlet/UciLicense',

    // Vue route() helpers
    "route('management." => "route('manajemen.",
    "route('coach." => "route('pelatih.",
    "route('athlete." => "route('atlet.",

    // Variable translations inside Vue (basic references to objects/routes)
    "user.role?.name === 'Coach'" => "user.role?.name === 'Pelatih'",
    "user.role?.name === 'Athlete'" => "user.role?.name === 'Atlet'",
    "user.role?.name === 'Management'" => "user.role?.name === 'Manajemen'",
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
                echo "Updated: $path\n";
            }
        }
    }
}

// Process Vue and routes
processDirectory("$basePath/resources/js", ['vue', 'ts', 'js'], $replacements);
processDirectory("$basePath/routes", ['php'], $replacements);
processDirectory("$basePath/app/Http/Controllers", ['php'], $replacements);

// 3. Migrate Controller Logic
$targetAtletController = "$basePath/app/Http/Controllers/LihatRingkasanDaftarAtletController.php";
$kategoriController = "$basePath/app/Http/Controllers/KategoriController.php";

if (file_exists($kategoriController) && file_exists($targetAtletController)) {
    $targetContent = file_get_contents($targetAtletController);

    $kategoriMethods = <<<'EOD'
    /**
     * UC-05: Lihat Ringkasan Daftar Atlet
     * Turunan: Menampilkan daftar kategori
     */
    public function tampilDaftarKategori(): Response
    {
        return Inertia::render('manajemen/Categories', [
            'categories' => \App\Models\Kategori::orderBy('name')->get(),
        ]);
    }

    /**
     * UC-05: Lihat Ringkasan Daftar Atlet
     * Turunan: Menyimpan kategori baru
     */
    public function simpanKategori(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string|max:1000',
        ]);

        \App\Models\Kategori::create($validated);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * UC-05: Lihat Ringkasan Daftar Atlet
     * Turunan: Memperbarui kategori
     */
    public function perbaruiKategoriData(Request $request, \App\Models\Kategori $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$category->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $category->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * UC-05: Lihat Ringkasan Daftar Atlet
     * Turunan: Menghapus kategori
     */
    public function hapusKategori(\App\Models\Kategori $category)
    {
        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
EOD;

    // Remove closing brace and append methods
    $targetContent = preg_replace('/}\s*$/', "\n$kategoriMethods\n}\n", $targetContent);
    file_put_contents($targetAtletController, $targetContent);
    unlink($kategoriController);
    echo "Merged KategoriController into LihatRingkasanDaftarAtletController\n";
}

$targetJadwalController = "$basePath/app/Http/Controllers/KelolaJadwalSesiLatihanController.php";
$jenisLatihanController = "$basePath/app/Http/Controllers/JenisLatihanController.php";

if (file_exists($jenisLatihanController) && file_exists($targetJadwalController)) {
    $targetContent = file_get_contents($targetJadwalController);

    $jenisMethods = <<<'EOD'
    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Menampilkan daftar jenis latihan
     */
    public function tampilDaftarJenisLatihan(): Response
    {
        return Inertia::render('manajemen/ExerciseTypes', [
            'exerciseTypes' => \App\Models\JenisLatihan::orderBy('name')->get(),
        ]);
    }

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Menyimpan jenis latihan baru
     */
    public function simpanJenisLatihan(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:exercise_types,name',
            'description' => 'nullable|string|max:1000',
        ]);

        \App\Models\JenisLatihan::create($validated);

        return back()->with('success', 'Jenis Latihan berhasil ditambahkan.');
    }

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Memperbarui jenis latihan
     */
    public function perbaruiJenisLatihan(Request $request, \App\Models\JenisLatihan $exerciseType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:exercise_types,name,'.$exerciseType->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $exerciseType->update($validated);

        return back()->with('success', 'Jenis Latihan berhasil diperbarui.');
    }

    /**
     * UC-07: Kelola Jadwal Sesi Latihan
     * Turunan: Menghapus jenis latihan
     */
    public function hapusJenisLatihan(\App\Models\JenisLatihan $exerciseType)
    {
        $exerciseType->delete();

        return back()->with('success', 'Jenis Latihan berhasil dihapus.');
    }
EOD;

    $targetContent = preg_replace('/}\s*$/', "\n$jenisMethods\n}\n", $targetContent);
    file_put_contents($targetJadwalController, $targetContent);
    unlink($jenisLatihanController);
    echo "Merged JenisLatihanController into KelolaJadwalSesiLatihanController\n";
}

$targetEventController = "$basePath/app/Http/Controllers/KelolaEventDanPartisipasiController.php";
$setelanEventController = "$basePath/app/Http/Controllers/SetelanKelolaEventDanPartisipasiController.php";

if (file_exists($setelanEventController) && file_exists($targetEventController)) {
    $targetContent = file_get_contents($targetEventController);

    $setelanMethods = <<<'EOD'
    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Menyimpan setelan tipe event baru
     */
    public function simpanTipeEvent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:event_types,name',
            'description' => 'nullable|string',
        ]);

        \App\Models\EventType::create($validated);
        return back()->with('success', 'Tipe Event berhasil ditambahkan.');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Memperbarui setelan tipe event
     */
    public function perbaruiTipeEvent(Request $request, \App\Models\EventType $type)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:event_types,name,'.$type->id,
            'description' => 'nullable|string',
        ]);

        $type->update($validated);
        return back()->with('success', 'Tipe Event berhasil diperbarui.');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Menghapus setelan tipe event
     */
    public function hapusTipeEvent(\App\Models\EventType $type)
    {
        $type->delete();
        return back()->with('success', 'Tipe Event berhasil dihapus.');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Menyimpan setelan poin event baru
     */
    public function simpanPoinEvent(Request $request)
    {
        $validated = $request->validate([
            'rank' => 'required|integer|min:1',
            'points' => 'required|integer|min:0',
        ]);

        \App\Models\EventPoint::updateOrCreate(
            ['rank' => $validated['rank']],
            ['points' => $validated['points']]
        );

        return back()->with('success', 'Poin Event berhasil disimpan.');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Memperbarui setelan poin event
     */
    public function perbaruiPoinEvent(Request $request, \App\Models\EventPoint $point)
    {
        $validated = $request->validate([
            'rank' => 'required|integer|min:1',
            'points' => 'required|integer|min:0',
        ]);

        $point->update($validated);
        return back()->with('success', 'Poin Event berhasil diperbarui.');
    }

    /**
     * UC-13: Kelola Event & Partisipasi
     * Turunan: Menghapus setelan poin event
     */
    public function hapusPoinEvent(\App\Models\EventPoint $point)
    {
        $point->delete();
        return back()->with('success', 'Poin Event berhasil dihapus.');
    }
EOD;

    $targetContent = preg_replace('/}\s*$/', "\n$setelanMethods\n}\n", $targetContent);
    file_put_contents($targetEventController, $targetContent);
    unlink($setelanController ?? $setelanEventController);
    echo "Merged SetelanKelolaEventDanPartisipasiController into KelolaEventDanPartisipasiController\n";
}

echo "Refactoring process completed.\n";
