import os
import re
from pathlib import Path

def extract_methods(file_path):
    methods = []
    with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
        content = f.read()
        # Simple regex to find public functions
        matches = re.finditer(r'public\s+function\s+([a-zA-Z0-9_]+)\s*\(', content)
        for match in matches:
            methods.append(match.group(1))
    return methods

def scan_dir(base_dir):
    controllers = {}
    for root, dirs, files in os.walk(base_dir):
        for file in files:
            if file.endswith('.php'):
                path = os.path.join(root, file)
                name = file
                if name not in controllers:
                    controllers[name] = set()
                controllers[name].update(extract_methods(path))
    return controllers

legacy_dir = r"d:\IHUB BETS V2.1.0-SISTEMA ESPORTIVO\legacy-source\sistema\app\Http\Controllers"
v2_dir = r"d:\IHUB BETS V2.1.0-SISTEMA ESPORTIVO\IHUB BETS-V2.1.0\app\Http\Controllers"

legacy_ctrls = scan_dir(legacy_dir)
v2_ctrls = scan_dir(v2_dir)

missing_methods = {}
missing_controllers = []

for ctrl, methods in legacy_ctrls.items():
    if ctrl not in v2_ctrls:
        # Check if renamed, for example Controller -> something else. We'll just list it.
        missing_controllers.append(ctrl)
    else:
        v2_methods = v2_ctrls[ctrl]
        miss = methods - v2_methods
        if miss:
            missing_methods[ctrl] = miss

print("=== CONTROLLERS COMPLETELY MISSING IN V2 ===")
for c in missing_controllers:
    print(c)

print("\n=== METHODS MISSING IN V2 ===")
for c, miss in missing_methods.items():
    print(f"{c}: {', '.join(miss)}")

