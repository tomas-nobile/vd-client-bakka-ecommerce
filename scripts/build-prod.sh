#!/bin/bash
#
# Build de producción limpio para deploy (Hostinger u otro hosting).
# - Borra build/ previa
# - Ejecuta webpack + tailwind en modo producción (sin source maps)
# - Elimina cualquier .map residual como defensa extra
# - Arma un .zip listo para subir al hosting

set -e

THEME_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$THEME_DIR"

THEME_SLUG="bakka"
DEPLOY_DIR="deploy"
TIMESTAMP="$(date +%Y%m%d-%H%M%S)"

# Carpetas/archivos que van al paquete de deploy.
# Ajustar si se agregan/quitan carpetas al tema.
INCLUDE_PATHS=(
    "build"
    "src"
    "assets"
    "includes"
    "languages"
    "parts"
    "templates"
    "woocommerce"
    "functions.php"
    "style.css"
    "theme.json"
    ".htaccess"
)

echo "🔢 Bumpeando versión patch del tema..."

# Leer versión actual desde style.css
CURRENT_THEME_VER=$(grep -oP 'Version:\s*\K[\d.]+' style.css | head -1)
if [ -z "$CURRENT_THEME_VER" ]; then
    echo "   ⚠️  No se encontró Version en style.css — saltando bump del tema."
else
    THEME_MAJOR=$(echo "$CURRENT_THEME_VER" | cut -d. -f1)
    THEME_MINOR=$(echo "$CURRENT_THEME_VER" | cut -d. -f2)
    THEME_PATCH=$(echo "$CURRENT_THEME_VER" | cut -d. -f3)
    NEW_THEME_VER="$THEME_MAJOR.$THEME_MINOR.$((THEME_PATCH + 1))"
    sed -i "s/Version: $CURRENT_THEME_VER/Version: $NEW_THEME_VER/" style.css
    sed -i "s/\"version\": \"$CURRENT_THEME_VER\"/\"version\": \"$NEW_THEME_VER\"/" package.json
    echo "   ✓ style.css + package.json  $CURRENT_THEME_VER → $NEW_THEME_VER"
fi

echo ""
echo "🔢 Bumpeando versión patch en todos los block.json..."
while IFS= read -r -d '' file; do
    current_ver=$(grep -oP '"version":\s*"\K[^"]+' "$file" | head -1)
    if [ -z "$current_ver" ]; then continue; fi
    major=$(echo "$current_ver" | cut -d. -f1)
    minor=$(echo "$current_ver" | cut -d. -f2)
    patch=$(echo "$current_ver" | cut -d. -f3)
    new_ver="$major.$minor.$((patch + 1))"
    sed -i "s/\"version\": \"$current_ver\"/\"version\": \"$new_ver\"/" "$file"
    echo "   ✓ $file  $current_ver → $new_ver"
done < <(find src -name "block.json" -print0)

echo ""
echo "🧹 Limpiando build/ previa..."
rm -rf build/

echo ""
echo "📦 Ejecutando build de producción (NODE_ENV=production)..."
NODE_ENV=production npm run build

echo ""
echo "🔍 Verificando que no queden source maps en build/..."
MAP_COUNT=$(find build -name "*.map" 2>/dev/null | wc -l)

if [ "$MAP_COUNT" -gt 0 ]; then
    echo "⚠️  Se encontraron $MAP_COUNT archivo(s) .map — eliminándolos..."
    find build -name "*.map" -delete
fi

REMAINING=$(find build -name "*.map" 2>/dev/null | wc -l)
if [ "$REMAINING" -ne 0 ]; then
    echo "❌ Aún quedan source maps después de limpiar. Abortando."
    exit 1
fi

echo "✅ Sin source maps en build/"

# -----------------------------------------------------------------
# Armado del paquete (zip o tar.gz)
# -----------------------------------------------------------------

echo ""
echo "📦 Armando paquete de deploy..."

mkdir -p "$DEPLOY_DIR"

# Staging: copiamos solo lo que va al zip, bajo una carpeta con el
# nombre del tema, así al descomprimir en wp-content/themes/ queda
# directamente themes/bakka/
STAGING="$DEPLOY_DIR/_staging-$TIMESTAMP"
rm -rf "$STAGING"
mkdir -p "$STAGING/$THEME_SLUG"

for ITEM in "${INCLUDE_PATHS[@]}"; do
    if [ -e "$ITEM" ]; then
        cp -R "$ITEM" "$STAGING/$THEME_SLUG/"
        echo "   ✓ $ITEM"
    else
        echo "   ⊘ $ITEM (no existe, omitido)"
    fi
done

# Elegimos formato: zip si está, si no tar.gz
if command -v zip >/dev/null 2>&1; then
    ARCHIVE="$DEPLOY_DIR/${THEME_SLUG}-${TIMESTAMP}.zip"
    ( cd "$STAGING" && zip -rq "../../${ARCHIVE}" "$THEME_SLUG" )
    FORMAT="zip"
else
    ARCHIVE="$DEPLOY_DIR/${THEME_SLUG}-${TIMESTAMP}.tar.gz"
    tar -czf "$ARCHIVE" -C "$STAGING" "$THEME_SLUG"
    FORMAT="tar.gz"
    echo ""
    echo "ℹ️  'zip' no está instalado — se generó un .tar.gz."
    echo "   Hostinger File Manager descomprime .tar.gz, pero si preferís .zip:"
    echo "   sudo apt install zip"
fi

rm -rf "$STAGING"

SIZE=$(du -h "$ARCHIVE" | cut -f1)

echo ""
echo "============================================================"
echo "✅ Paquete listo para deploy"
echo "============================================================"
echo ""
echo "   📁 $ARCHIVE ($SIZE, formato $FORMAT)"
echo ""
echo "📤 Cómo subirlo a Hostinger:"
echo "   1. hPanel → File Manager → /public_html/wp-content/themes/"
echo "   2. Upload → elegí el archivo de arriba"
echo "   3. Click derecho sobre el archivo → Extract"
echo "   4. Borrá el archivo comprimido del servidor"
echo ""
echo "🔐 Después del deploy, probá en el navegador que estas URLs"
echo "   devuelvan 403 o 404 (NO contenido):"
echo "   • https://tusitio.com/wp-content/themes/bakka/build/core/navbar/view.js.map"
echo "   • https://tusitio.com/wp-content/themes/bakka/src/"
echo "   • https://tusitio.com/wp-content/themes/bakka/webpack.config.js"
echo ""
