import os
import glob

# Map of English to Indonesian URL segments
replacements = {
    '/athletes': '/atlet',
    '/categories': '/kategori',
    '/exercise-types': '/jenis-latihan',
    '/reports': '/laporan',
    '/event-settings': '/pengaturan-acara',
    '/training-sessions': '/sesi-latihan',
    '/performance-comparison': '/komparasi-performa',
    '/events': '/acara',
    '/physical': '/fisik',
    '/training': '/latihan',
    '/documents': '/dokumen',
    '/pending': '/pending' # already pending in web.php, but let's leave it
}

# Add exact matches for variables that might be wrong, but we already fixed the main ones
# We'll focus strictly on URL strings like '/manajemen/athletes'
prefixes = ['/manajemen', '/pelatih', '/atlet', '/report']

files = glob.glob('resources/js/pages/**/*.vue', recursive=True)

modified_files = 0
for file in files:
    with open(file, 'r') as f:
        content = f.read()
    
    new_content = content
    
    # We want to replace paths like '/manajemen/athletes' -> '/manajemen/atlet'
    # and `/manajemen/athletes/${id}` -> `/manajemen/atlet/${id}`
    for prefix in prefixes:
        for eng, ind in replacements.items():
            target = prefix + eng
            replacement = prefix + ind
            new_content = new_content.replace(target, replacement)
            
            # also handle string interpolations
            # e.g. `${prefix}${eng}` if any... we just replace the exact substrings
            
    if new_content != content:
        with open(file, 'w') as f:
            f.write(new_content)
        modified_files += 1
        print(f"Modified {file}")

print(f"Total files modified: {modified_files}")
