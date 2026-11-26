#!/usr/bin/env python3
"""Empaqueta los plugins en dist/ listos para instalar desde
Administracion del sitio > Extensiones > Instalar extensiones.

Genera dist/local_reglasdeuda_<release>.zip -> local/reglasdeuda

Moodle exige que la carpeta raiz del ZIP sea el nombre del plugin sin el tipo
("reglasdeuda"), por eso se renombra al comprimir.

Uso:
    python build.py                     # ZIP con la version actual
    python build.py --bump              # incrementa $plugin->version (YYYYMMDDXX) en ambos
    python build.py --release 0.2.0     # cambia $plugin->release en ambos
"""
import argparse
import hashlib
import json
import datetime
import re
import sys
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parent
PLUGINS = ["local_reglasdeuda"]
DIST = ROOT / "dist"
ENCODING = "utf-8"
MANIFIESTO = "archivos.json"

EXCLUDE_NAMES = {".git", ".gitignore", ".DS_Store", "Thumbs.db", "__pycache__", "node_modules", ".idea"}
EXCLUDE_SUFFIXES = {".pyc", ".zip"}

RE_COMPONENT = re.compile(r"\$plugin->component\s*=\s*'([^']+)'")
RE_VERSION = re.compile(r"(\$plugin->version\s*=\s*)(\d{10})")
RE_RELEASE = re.compile(r"(\$plugin->release\s*=\s*')([^']*)(')")
RE_DEPENDENCIA = re.compile(r"('local_reglasdeuda'\s*=>\s*)(\d{10})")


def version_file(plugin):
    return ROOT / plugin / "version.php"


def read_meta(text):
    comp = RE_COMPONENT.search(text)
    ver = RE_VERSION.search(text)
    rel = RE_RELEASE.search(text)
    if not (comp and ver and rel):
        sys.exit("ERROR: version.php debe definir component, version y release")
    return comp.group(1), ver.group(2), rel.group(2)


def next_version(current):
    today = datetime.date.today().strftime("%Y%m%d")
    if current[:8] >= today:
        return str(int(current) + 1)  # Mismo dia (o fecha futura): incrementa XX.
    return today + "00"


def write(path, text):
    with open(path, "w", encoding=ENCODING, newline="") as f:
        f.write(text)


def update_versions(bump, release):
    local_text = version_file("local_reglasdeuda").read_text(encoding=ENCODING)
    _, local_version, _ = read_meta(local_text)
    new_local = next_version(local_version) if bump else local_version

    for plugin in PLUGINS:
        path = version_file(plugin)
        text = path.read_text(encoding=ENCODING)
        _, version, _ = read_meta(text)
        if bump:
            new = new_local if plugin == "local_reglasdeuda" else next_version(version)
            text = RE_VERSION.sub(lambda m: m.group(1) + new, text, count=1)
            print(f"{plugin}: version {version} -> {new}")
        if release:
            text = RE_RELEASE.sub(lambda m: m.group(1) + release + m.group(3), text, count=1)
            print(f"{plugin}: release -> {release}")
        # La condicion siempre exige la version actual del plugin local.
        text = RE_DEPENDENCIA.sub(lambda m: m.group(1) + new_local, text)
        write(path, text)


def build(plugin):
    src = ROOT / plugin
    component, version, release = read_meta(version_file(plugin).read_text(encoding=ENCODING))
    if component != plugin:
        sys.exit(f"ERROR: {plugin}/version.php declara {component}")
    plugintype, name = component.split("_", 1)
    lang = src / "lang" / "en" / f"{component}.php"
    if not lang.is_file():
        sys.exit(f"ERROR: falta {lang.relative_to(ROOT)}")

    DIST.mkdir(exist_ok=True)
    zip_path = DIST / f"{component}_{release}.zip"
    if zip_path.exists():
        print(f"Aviso: ya existia {zip_path.name}, se reemplaza (usa --release para crear uno nuevo).")
        zip_path.unlink()

    def excluded(path):
        rel = path.relative_to(src)
        return any(part in EXCLUDE_NAMES for part in rel.parts) or path.suffix in EXCLUDE_SUFFIXES

    # Manifiesto: sha1 de cada archivo, para que el plugin detecte lo que el antivirus del hosting borre.
    manifiesto = {}
    for path in sorted(src.rglob("*")):
        if path.is_file() and not excluded(path) and path.name != MANIFIESTO:
            manifiesto[path.relative_to(src).as_posix()] = hashlib.sha1(path.read_bytes()).hexdigest()
    (src / MANIFIESTO).write_text(json.dumps(manifiesto, indent=1, sort_keys=True) + "\n", encoding="utf-8")

    with zipfile.ZipFile(zip_path, "w", zipfile.ZIP_DEFLATED) as zf:
        for path in sorted(src.rglob("*")):
            if excluded(path):
                continue
            arcname = f"{name}/{path.relative_to(src).as_posix()}"
            if path.is_dir():
                zf.writestr(arcname + "/", "")
            else:
                zf.write(path, arcname)

    print(f"{component}: version {version}, release {release} -> {zip_path.relative_to(ROOT)}")


def main():
    ap = argparse.ArgumentParser(description="Empaqueta los plugins de Moodle en dist/")
    ap.add_argument("--bump", action="store_true", help="incrementa $plugin->version (YYYYMMDDXX)")
    ap.add_argument("--release", help="establece $plugin->release, p.ej. 0.2.0")
    args = ap.parse_args()
    if args.bump or args.release:
        update_versions(args.bump, args.release)
    for plugin in PLUGINS:
        build(plugin)


if __name__ == "__main__":
    main()
