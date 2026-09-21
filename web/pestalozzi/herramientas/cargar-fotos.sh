#!/bin/bash
# Sube en bloque una carpeta de fotos a la galería.
#
#   ./cargar-fotos.sh ~/Desktop/fotos-actividades
#
# El nombre de la carpeta pasa a ser el nombre de la categoría en la
# galería. Se puede correr las veces que haga falta, con carpetas
# distintas: nunca pisa lo ya cargado, solo agrega.

set -euo pipefail
DIR_HERRAMIENTAS="$(cd "$(dirname "$0")" && pwd)"
cd "$DIR_HERRAMIENTAS"

if [ $# -eq 0 ]; then
  echo "Uso: ./cargar-fotos.sh <carpeta-de-fotos>"
  echo "Ejemplo: ./cargar-fotos.sh ~/Desktop/fotos-actividades"
  exit 1
fi

CARPETA="$1"

if [ ! -d "$CARPETA" ]; then
  echo "ERROR: no existe la carpeta \"$CARPETA\"."
  exit 1
fi

echo "==> Buscando la credencial de Sanity..."
TOKEN=$(cd "$DIR_HERRAMIENTAS/.." && npx --yes sanity@latest debug --secrets 2>/dev/null \
        | grep "Auth token:" | awk '{print $3}')

if [ -z "$TOKEN" ]; then
  echo "ERROR: no se encontró la credencial."
  echo "       Corré primero:  npx sanity login   (desde la carpeta del proyecto)"
  exit 1
fi

echo "==> Subiendo fotos de \"$CARPETA\":"
echo ""
SANITY_TOKEN="$TOKEN" node cargar-fotos.js "$CARPETA"
