#!/usr/bin/env bash
# Generate API documentation (HTML) with phpDocumentor. Mirrors phpdoc.xml (app + routes, default template).
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [[ ! -f tools/phpdocumentor/vendor/bin/phpdoc ]]; then
    composer install --working-dir=tools/phpdocumentor --no-interaction
fi

exec "$ROOT/tools/phpdocumentor/vendor/bin/phpdoc" run -c none \
    -d app -d routes \
    -t "file://${ROOT}/docs/phpdoc" \
    --template=default \
    --cache-folder="${ROOT}/.phpdoc/cache" \
    --defaultpackagename=App \
    --title="Laravel E-commerce — Application code" \
    --force
