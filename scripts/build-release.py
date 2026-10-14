#!/usr/bin/env python3
"""Build the installable plugin, excluding development files."""
import pathlib, re, sys, zipfile
root = pathlib.Path(__file__).resolve().parents[1]
version = re.search(r'^Version:\s*(\S+)', (root / 'arkivdatabasen.php').read_text(), re.M).group(1)
if len(sys.argv) > 1 and sys.argv[1] != 'v' + version:
    raise SystemExit('Release tag must match the plugin Version header: v' + version)
output = root / 'dist' / 'arkivdatabasen.zip'
output.parent.mkdir(exist_ok=True)
paths = [root / name for name in ['arkivdatabasen.php', 'README.md', 'CHANGELOG.md', 'LICENSE']]
for folder in ['includes', 'images', 'vendor']:
    paths.extend(p for p in (root / folder).rglob('*') if p.is_file())
with zipfile.ZipFile(output, 'w', zipfile.ZIP_DEFLATED) as archive:
    for path in sorted(paths):
        item = zipfile.ZipInfo('arkivdatabasen/' + str(path.relative_to(root)), (2026, 1, 1, 0, 0, 0))
        item.external_attr = 0o100644 << 16
        item.compress_type = zipfile.ZIP_DEFLATED
        archive.writestr(item, path.read_bytes())
print(output)
