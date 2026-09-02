#!/bin/bash
# Publica en el sitio lo que esté escrito en contenido.txt.
#
#   ./cargar.sh
#
# Se puede correr las veces que haga falta: lo que dejes vacío en el
# archivo no se toca, así que no pisa lo que ya esté cargado.

set -euo pipefail
cd "$(dirname "$0")"

if [ ! -f contenido.txt ]; then
  echo "ERROR: falta contenido.txt en esta carpeta."
  exit 1
fi

echo "==> Buscando la credencial de Sanity..."
TOKEN=$(cd "$(dirname "$0")/.." && npx --yes sanity@latest debug --secrets 2>/dev/null \
        | grep "Auth token:" | awk '{print $3}')

if [ -z "$TOKEN" ]; then
  echo "ERROR: no se encontró la credencial."
  echo "       Corré primero:  npx sanity login   (desde la carpeta del proyecto)"
  exit 1
fi

echo "==> Leyendo contenido.txt:"
echo ""
SANITY_TOKEN="$TOKEN" node cargar.js contenido.txt
