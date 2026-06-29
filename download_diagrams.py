import os
import re
import sys
import base64
import urllib.request
import time
from urllib.error import URLError, HTTPError

def clean_filename(name):
    # Membersihkan nama file dari karakter yang tidak didukung oleh sistem operasi dan tanda kurung
    name = re.sub(r'[\\/*?:"<>|()&,.]', "", name)
    name = name.replace(" ", "_")
    name = re.sub(r'_+', '_', name)
    return name.strip('_')

def parse_and_download(md_file_path, output_dir):
    if not os.path.exists(md_file_path):
        print(f"Error: Berkas {md_file_path} tidak ditemukan.")
        return

    # Bersihkan direktori jika sudah ada agar file lama tidak bercampur
    if os.path.exists(output_dir):
        for f in os.listdir(output_dir):
            file_p = os.path.join(output_dir, f)
            if os.path.isfile(file_p):
                try:
                    os.remove(file_p)
                except Exception:
                    pass
    else:
        os.makedirs(output_dir, exist_ok=True)
    
    with open(md_file_path, "r", encoding="utf-8") as f:
        content = f.read()

    # Regex untuk mencari header UC dan blok kode Mermaid setelahnya
    pattern = re.compile(
        r'(?:^|\n)(?:##|###)\s*(UC-\d+\.\d+.*?)\n.*?```mermaid\s*\n(.*?)\n```',
        re.DOTALL
    )

    matches = pattern.findall(content)
    
    # Jika tidak ada turunan (UC-xx.y), coba cari level utama (UC-xx)
    if not matches:
        pattern = re.compile(
            r'(?:^|\n)(?:##|###)\s*(UC-\d+.*?)\n.*?```mermaid\s*\n(.*?)\n```',
            re.DOTALL
        )
        matches = pattern.findall(content)

    # Fallback untuk diagram apa pun jika bukan format UC (seperti robustness)
    if not matches:
        pattern = re.compile(
            r'(?:^|\n)(?:##|###)\s*(.*?)\n.*?```mermaid\s*\n(.*?)\n```',
            re.DOTALL
        )
        matches = pattern.findall(content)

    if not matches:
        print("Tidak ada diagram Mermaid yang ditemukan dalam berkas MD.")
        return

    print(f"Ditemukan {len(matches)} diagram. Memulai proses unduhan...")

    headers = {
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    }

    success_count = 0
    for idx, (title, code) in enumerate(matches, 1):
        # Hapus awalan UC-xx.y atau UC-xx jika ada
        clean_title = re.sub(r'^UC-\d+(\.\d+)?\s*', '', title.strip())
        clean_title = clean_filename(clean_title)
        filename = f"{idx}_{clean_title}.png"
        file_path = os.path.join(output_dir, filename)

        # Membersihkan kode Mermaid dari spasi berlebih
        code_clean = code.strip()

        # Base64 url-safe encoding
        code_bytes = code_clean.encode("utf-8")
        base64_bytes = base64.urlsafe_b64encode(code_bytes)
        base64_string = base64_bytes.decode("ascii")

        # Hapus padding '=' jika ada (opsional, tapi disarankan oleh beberapa parser)
        base64_string = base64_string.rstrip("=")

        url = f"https://mermaid.ink/img/{base64_string}"

        print(f"[{idx}/{len(matches)}] Mengunduh {filename} ({title.strip()})...")
        
        try:
            req = urllib.request.Request(url, headers=headers)
            with urllib.request.urlopen(req, timeout=15) as response:
                with open(file_path, "wb") as out_file:
                    out_file.write(response.read())
            print(f"   [OK] Berhasil disimpan di: {file_path}")
            success_count += 1
        except HTTPError as e:
            print(f"   [FAIL] Gagal mengunduh (HTTP Error {e.code}): {e.reason}")
        except URLError as e:
            print(f"   [FAIL] Gagal mengunduh (URL Error): {e.reason}")
        except Exception as e:
            print(f"   [FAIL] Gagal mengunduh (Error): {str(e)}")

        # Jeda 1 detik untuk menghindari rate limit pada server API mermaid.ink
        time.sleep(1)

    print(f"\nSelesai! Berhasil mengunduh {success_count} dari {len(matches)} diagram.")
    print(f"Semua berkas disimpan di direktori: {os.path.abspath(output_dir)}")

if __name__ == "__main__":
    # Default file targets
    default_files = ["sequence_diagrams.md", "robustness_diagrams.md"]
    output_directory = "exported_diagrams"

    target_file = None
    if len(sys.argv) > 1:
        target_file = sys.argv[1]
        if len(sys.argv) > 2:
            output_directory = sys.argv[2]
        parse_and_download(target_file, output_directory)
    else:
        # Jika tidak ada argumen, coba unduh kedua file default yang ada di direktori kerja
        for f_name in default_files:
            if os.path.exists(f_name):
                print(f"\n=== Memproses berkas: {f_name} ===")
                # Untuk robustness, simpan di subfolder tersendiri agar rapi
                subfolder = os.path.join(output_directory, f_name.split(".")[0])
                parse_and_download(f_name, subfolder)
            else:
                print(f"Berkas default {f_name} tidak ditemukan. Silakan jalankan dengan argumen: python download_diagrams.py [nama_file.md]")
