<?php

$basePath = 'd:/Coding/Herd/simora/resources/js';

$replacements = [
    // Route imports
    '@/routes/coach' => '@/routes/pelatih',
    '@/routes/management' => '@/routes/manajemen',
    '@/routes/athlete' => '@/routes/atlet',

    // Action imports (if any old names left)
    // Wayfinder actions are generated from class names.
    // If I renamed the class, the action path changed.

    // Old roles in logic/strings
    "'coach'" => "'pelatih'",
    "'athlete'" => "'atlet'",
    "'management'" => "'manajemen'",
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
                echo "Fixed imports in: $path\n";
            }
        }
    }
}

processDirectory($basePath, ['vue', 'ts', 'js'], $replacements);

echo "Vue imports fixed.\n";
