import os
import re

tests_dir = r"d:\Coding\simora\tests"
assertion_pattern = re.compile(r"\bassert[A-Za-z0-9_]+")

total_assertions = 0
assertion_counts = {}

for root, dirs, files in os.walk(tests_dir):
    for file in files:
        if file.endswith(".php"):
            path = os.path.join(root, file)
            with open(path, "r", encoding="utf-8", errors="ignore") as f:
                content = f.read()
                # Find matches
                matches = assertion_pattern.findall(content)
                # Filter out import statements like `use Inertia\Testing\AssertableInertia as Assert;`
                # and things like Assert::class etc.
                filtered_matches = []
                for m in matches:
                    if m.lower() == "assert":
                        continue
                    filtered_matches.append(m)
                
                if filtered_matches:
                    count = len(filtered_matches)
                    total_assertions += count
                    assertion_counts[path] = count

print(f"Total assertions found: {total_assertions}")
for path, count in sorted(assertion_counts.items()):
    print(f"- {os.path.basename(path)}: {count}")

