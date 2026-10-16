#!/usr/bin/env bash
# compare-page/shots.sh — ×2 screenshots of chosen theme sections
#
#   scripts/compare-page/shots.sh <out-dir> <theme> [theme ...] [--tune <name>]
#
# Serves <out-dir> over a local http server (headless Chromium does not load
# file:// pages here), opens it with playwright-cli, switches on the ×2 zoom
# (and the tune, if given), scrolls each theme section into view and saves
# <out-dir>/shots/YYYYMMDD-HHmmss_<theme>[_<tune>]_x2.png. The window is wide
# enough for the full 8-color table at ×2, so one shot per theme.
set -euo pipefail

if [ $# -lt 2 ]; then
  echo "usage: $0 <out-dir> <theme> [theme ...] [--tune <name>]" >&2
  exit 1
fi

OUT=$(cd "$1" && pwd); shift
TUNE=""
THEMES=()
while [ $# -gt 0 ]; do
  case "$1" in
    --tune) TUNE="$2"; shift 2 ;;
    *) THEMES+=("$1"); shift ;;
  esac
done

PW=${PLAYWRIGHT_CLI:-playwright-cli}
SESSION=compare-page
STAMP=$(date +%Y%m%d-%H%M%S)
mkdir -p "$OUT/shots"

PORT=${PORT:-$(python3 -c 'import socket; s=socket.socket(); s.bind(("127.0.0.1",0)); print(s.getsockname()[1])')}
python3 -m http.server "$PORT" --bind 127.0.0.1 --directory "$OUT" >/dev/null 2>&1 &
SERVER=$!
trap '"$PW" -s=$SESSION close-all >/dev/null 2>&1 || true; kill $SERVER >/dev/null 2>&1 || true' EXIT
sleep 1

"$PW" -s=$SESSION open "http://127.0.0.1:$PORT/index.html" >/dev/null
if ! "$PW" -s=$SESSION eval "() => document.querySelector('main') !== null" | grep -q true; then
  echo "page did not load from http://127.0.0.1:$PORT/index.html" >&2
  exit 1
fi
"$PW" -s=$SESSION resize 3000 1000 >/dev/null
"$PW" -s=$SESSION eval "() => { document.querySelector('main').classList.add('zoom'); document.getElementById('zoom').setAttribute('aria-pressed', 'true'); }" >/dev/null
if [ -n "$TUNE" ]; then
  "$PW" -s=$SESSION eval "() => { document.documentElement.setAttribute('data-tune', '$TUNE'); }" >/dev/null
fi

for theme in "${THEMES[@]}"; do
  "$PW" -s=$SESSION eval "() => { document.getElementById('$theme').scrollIntoView(); }" >/dev/null
  name="${STAMP}_${theme}${TUNE:+_$TUNE}_x2.png"
  "$PW" -s=$SESSION screenshot --filename="$OUT/shots/$name" >/dev/null
  echo "shots/$name"
done
