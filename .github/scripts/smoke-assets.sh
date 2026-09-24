#!/usr/bin/env bash
#
# Boot the workbench, render Adminer's login page, and require that every asset
# it links to is actually served by this package's routes.
#
# The Pest suite asserts that the asset routes serve the files the pinned
# Adminer release ships. It cannot assert the other half - that Adminer links to
# those routes - because Adminer calls exit() when it renders the login form, so
# the page cannot be captured in-process through Testbench. That half is exactly
# where upstream drift shows up: Adminer 6.0.1 moved every asset link from
# "../adminer/static/..." to "./static/..." and relocated jush, and 6.1.0
# renamed logo.png to logo.svg. This script closes that gap over real HTTP.
#
# Exits non-zero on the first asset that does not serve, so it can gate a
# release.

set -uo pipefail

PORT="${SMOKE_PORT:-8765}"
BASE="http://127.0.0.1:${PORT}"
LOG="$(mktemp)"
FAILURES=0

cleanup() {
    pkill -f "testbench serve --port=${PORT}" 2>/dev/null
}
trap cleanup EXIT

echo "==> Building workbench"
composer build --quiet || { echo "FAIL: workbench build failed"; exit 1; }

echo "==> Starting server on port ${PORT}"
php vendor/bin/testbench serve --port="${PORT}" > "${LOG}" 2>&1 &

for _ in $(seq 1 30); do
    [ "$(curl -s -o /dev/null -w '%{http_code}' "${BASE}/adminer/")" = "200" ] && break
    sleep 1
done

status="$(curl -s -o /dev/null -w '%{http_code}' "${BASE}/adminer/")"
if [ "${status}" != "200" ]; then
    echo "FAIL: /adminer/ returned ${status}, expected 200"
    echo "--- server log ---"; tail -30 "${LOG}"
    exit 1
fi

page="$(curl -s "${BASE}/adminer/")"

# Guard against passing vacuously on an error page that happens to link nothing.
if ! grep -q "id='h1'" <<<"${page}"; then
    echo "FAIL: /adminer/ did not render an Adminer page (no #h1 element)"
    echo "--- first 40 lines ---"; head -40 <<<"${page}"
    exit 1
fi

echo "==> Checking every asset the page links to"

# Adminer emits relative links ("./static/x.css", "static/editing.js"), which
# resolve against /adminer/. Absolute URLs and data: URIs belong to somebody
# else and are left alone.
# mapfile is bash 4+; this stays portable to the bash 3.2 shipped on macOS.
assets="$(
    grep -oE "(href|src)=['\"][^'\"]+['\"]" <<<"${page}" \
        | sed -E "s/^(href|src)=['\"]//; s/['\"]$//" \
        | grep -vE '^(https?:|data:|#|/)' \
        | sed -E 's#^\./##' \
        | sort -u
)"

if [ -z "${assets}" ]; then
    echo "FAIL: page linked no relative assets at all - the extraction or the page is wrong"
    exit 1
fi

while IFS= read -r asset; do
    [ -z "${asset}" ] && continue
    # Strip a query string; Adminer appends ?version= to some links.
    path="${asset%%\?*}"
    [ -z "${path}" ] && continue

    result="$(curl -s -o /dev/null -w '%{http_code} %{content_type}' "${BASE}/adminer/${path}")"
    code="${result%% *}"
    type="${result#* }"

    if [ "${code}" != "200" ]; then
        echo "  FAIL ${asset} -> ${code}"
        FAILURES=$((FAILURES + 1))
        continue
    fi

    # A stylesheet served as text/html means the request fell through to the
    # catch-all Adminer route instead of the asset route - which is how the
    # pre-6.0.1 asset anchoring failed, and it is invisible to a status check.
    case "${path}" in
        *.css) expected="text/css" ;;
        *.js)  expected="text/javascript" ;;
        *.svg) expected="image/svg" ;;
        *.png) expected="image/png" ;;
        *)     expected="" ;;
    esac

    if [ -n "${expected}" ] && [[ "${type}" != *"${expected}"* ]]; then
        echo "  FAIL ${asset} -> 200 but Content-Type '${type}', expected '${expected}'"
        FAILURES=$((FAILURES + 1))
        continue
    fi

    echo "  ok   ${asset} (${type})"
done <<< "${assets}"

# jush is reached through the static route but served from a different package,
# and its modules are loaded lazily rather than linked in <head> - so the page
# scan above never sees them. Check the entry points explicitly.
echo "==> Checking jush modules"
for f in jush/jush.css jush/jush-dark.css jush/modules/jush.js jush/modules/jush-textarea.js; do
    code="$(curl -s -o /dev/null -w '%{http_code}' "${BASE}/adminer/static/${f}")"
    if [ "${code}" != "200" ]; then
        echo "  FAIL static/${f} -> ${code}"
        FAILURES=$((FAILURES + 1))
    else
        echo "  ok   static/${f}"
    fi
done

if [ "${FAILURES}" -gt 0 ]; then
    echo
    echo "FAIL: ${FAILURES} asset(s) did not serve correctly"
    exit 1
fi

echo
echo "PASS: every linked asset serves"
