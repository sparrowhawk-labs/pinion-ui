#!/usr/bin/env bash
# compare-page/build.sh — spec JSON → <out-dir>/index.html + page.css
#
#   scripts/compare-page/build.sh <spec.json> <out-dir>
#
# 1. gen.php renders index.html from the spec (real classes from src/Compose
#    and the Blade variant maps of THIS checkout).
# 2. Tailwind CLI compiles page.css from the preset + that index.html, so the
#    page shows the themes exactly as this checkout ships them.
#
# Requirements: php, and node_modules in packages/pinion-ui-css (`npm install`
# there). In a worktree without node_modules, point PINION_NODE_MODULES at the
# main checkout's packages/pinion-ui-css/node_modules.
#
# Tailwind resolves `@plugin "daisyui"` against the entry file's location, so
# the entry is written at the repo root and a root-level node_modules symlink
# is created for the build and removed afterwards (only if this script made it).
set -euo pipefail

if [ $# -lt 2 ]; then
  echo "usage: $0 <spec.json> <out-dir>" >&2
  exit 1
fi

SPEC=$(cd "$(dirname "$1")" && pwd)/$(basename "$1")
mkdir -p "$2"
OUT=$(cd "$2" && pwd)
HERE=$(cd "$(dirname "$0")" && pwd)
ROOT=$(cd "$HERE/../.." && pwd)
NM=${PINION_NODE_MODULES:-$ROOT/packages/pinion-ui-css/node_modules}

if [ ! -x "$NM/.bin/tailwindcss" ]; then
  echo "tailwindcss not found under $NM — run 'npm install' in packages/pinion-ui-css or set PINION_NODE_MODULES" >&2
  exit 1
fi

php "$HERE/gen.php" "$SPEC" "$OUT"

ENTRY="$ROOT/.compare-page.entry.css"
MADE_LINK=0
cleanup() {
  rm -f "$ENTRY"
  [ "$MADE_LINK" = 1 ] && rm -f "$ROOT/node_modules"
  return 0
}
trap cleanup EXIT

if [ ! -e "$ROOT/node_modules" ]; then
  ln -s "$NM" "$ROOT/node_modules"
  MADE_LINK=1
fi

cat > "$ENTRY" <<EOF
@import "tailwindcss";
@import "./src/resources/css/pinion-ui.css";
@source "$OUT/index.html";
EOF

"$NM/.bin/tailwindcss" -i "$ENTRY" -o "$OUT/page.css" 2>&1 | grep -v '^$' || true
echo "wrote $OUT/page.css ($(wc -c < "$OUT/page.css" | tr -d ' ') bytes)"
