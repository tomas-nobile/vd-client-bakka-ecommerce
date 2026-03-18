#!/bin/bash

# Script to create WordPress blocks by cloning the base block 0_block
# Uso interactivo: ./scripts/create-block.sh
# Ejemplo con parámetros: ./scripts/create-block.sh -g front-page -b index -t "Home.Blog"
# Parámetros:
#   -g | --group   => Nombre del grupo/página donde se va a crear el bloque (por ejemplo: front-page, archive-product).
#                     Debe coincidir con uno de los grupos detectados en la carpeta templates o con "core".
#   -b | --block   => Nombre interno del bloque (slug). Se usa para la carpeta y el nombre técnico.
#                     Solo minúsculas/números/guiones. Si no se indica en modo interactivo, el valor por defecto es "index".
#   -t | --title   => Título legible del bloque que se mostrará en el editor de WordPress (por ejemplo: Home.Blog).
#                     El script lo combina con el grupo para generar el título final.
#   -h | --help    => Muestra este mensaje de ayuda y termina la ejecución.

# Get the script directory and change to the theme directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$(dirname "$SCRIPT_DIR")"
THEME_NAME="$(basename "$THEME_DIR")"
NAMESPACE=$(echo "$THEME_NAME" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9-]/-/g' | sed 's/--*/-/g' | sed 's/^-\|-$//g')
SRC_DIR="$THEME_DIR/src"
TEMPLATES_DIR="$THEME_DIR/templates"
BASE_BLOCK_DIR="$SRC_DIR/0_block"

# Parámetros por defecto / CLI
CLI_GROUP=""
CLI_BLOCK_NAME=""
CLI_BLOCK_TITLE=""

while [[ $# -gt 0 ]]; do
    case "$1" in
        -g|--group)
            CLI_GROUP="$2"
            shift 2
            ;;
        -b|--block)
            CLI_BLOCK_NAME="$2"
            shift 2
            ;;
        -t|--title)
            CLI_BLOCK_TITLE="$2"
            shift 2
            ;;
        -h|--help)
            echo "Uso: ./scripts/create-block.sh [-g grupo] [-b bloque] [-t titulo]"
            echo "  -g, --group   Nombre del grupo/página (por ejemplo: front-page)"
            echo "  -b, --block   Nombre del bloque (por defecto: index)"
            echo "  -t, --title   Título del bloque (por ejemplo: Home.Blog)"
            exit 0
            ;;
        *)
            echo "✗ Opción desconocida: $1"
            echo "Prueba con --help para ver las opciones disponibles."
            exit 1
            ;;
    esac
done

# Function to get available groups
get_available_groups() {
    local groups=("core")
    
    # Get .html file names in templates (without extension)
    if [ -d "$TEMPLATES_DIR" ]; then
        while IFS= read -r file; do
            if [ -f "$file" ]; then
                local basename=$(basename "$file" .html)
                groups+=("$basename")
            fi
        done < <(find "$TEMPLATES_DIR" -maxdepth 1 -name "*.html" -type f 2>/dev/null)
    fi
    
    printf '%s\n' "${groups[@]}"
}

# Function to validate the block name
validate_block_name() {
    local name="$1"
    local group_dir="$2"
    
    # Check that it's not empty
    if [ -z "$name" ]; then
        echo "✗ Error: El nombre del bloque no puede estar vacío"
        return 1
    fi
    
    # Check that it only contains lowercase letters, numbers, and hyphens
    if [[ ! "$name" =~ ^[a-z0-9-]+$ ]]; then
        echo "✗ Error: El nombre del bloque solo puede contener letras minúsculas, números y guiones"
        return 1
    fi
    
    # Check that it doesn't start or end with a hyphen
    if [[ "$name" =~ ^- ]] || [[ "$name" =~ -$ ]]; then
        echo "✗ Error: El nombre del bloque no puede empezar ni terminar con guión"
        return 1
    fi
    
    # Check that it doesn't have consecutive hyphens
    if [[ "$name" =~ -- ]]; then
        echo "✗ Error: El nombre del bloque no puede tener guiones consecutivos"
        return 1
    fi
    
    # Check that it has at least one character
    if [ ${#name} -lt 1 ]; then
        echo "✗ Error: El nombre del bloque debe tener al menos un carácter"
        return 1
    fi
    
    # Check that the directory doesn't already exist in the group
    if [ -d "$group_dir/$name" ]; then
        echo "✗ Error: Ya existe un bloque con el nombre '$name' en este grupo"
        return 1
    fi
    
    return 0
}

# Change to the theme directory
cd "$THEME_DIR" || {
    echo "✗ Error: No se pudo acceder al directorio del tema: $THEME_DIR"
    exit 1
}

# Verify that the base block exists
if [ ! -d "$BASE_BLOCK_DIR" ]; then
    echo "✗ Error: No se encontró el bloque base en $BASE_BLOCK_DIR"
    exit 1
fi

# Get available groups
mapfile -t AVAILABLE_GROUPS < <(get_available_groups)

if [ ${#AVAILABLE_GROUPS[@]} -eq 0 ]; then
    echo "✗ Error: No se encontraron grupos disponibles"
    exit 1
fi

# Selección de grupo (CLI o interactivo)
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  Crear nuevo bloque de WordPress"
echo "═══════════════════════════════════════════════════════════════"
echo ""

if [ -n "$CLI_GROUP" ]; then
    # Validar que el grupo exista entre los disponibles
    SELECTED_GROUP=""
    for g in "${AVAILABLE_GROUPS[@]}"; do
        if [ "$g" == "$CLI_GROUP" ]; then
            SELECTED_GROUP="$g"
            break
        fi
    done

    if [ -z "$SELECTED_GROUP" ]; then
        echo "✗ Error: El grupo '$CLI_GROUP' no existe entre los disponibles."
        echo "  Grupos disponibles: ${AVAILABLE_GROUPS[*]}"
        exit 1
    fi
else
    echo "Selecciona un grupo:"
    for i in "${!AVAILABLE_GROUPS[@]}"; do
        echo "  $((i+1)). ${AVAILABLE_GROUPS[$i]}"
    done
    echo ""

    # Ask for group selection
    while true; do
        read -p "Grupo (número): " GROUP_SELECTION
        if [[ "$GROUP_SELECTION" =~ ^[0-9]+$ ]] && [ "$GROUP_SELECTION" -ge 1 ] && [ "$GROUP_SELECTION" -le ${#AVAILABLE_GROUPS[@]} ]; then
            SELECTED_GROUP="${AVAILABLE_GROUPS[$((GROUP_SELECTION-1))]}"
            break
        else
            echo "✗ Error: Por favor selecciona un número válido (1-${#AVAILABLE_GROUPS[@]})"
        fi
    done
fi

# Create group directory if it doesn't exist
GROUP_DIR="$SRC_DIR/$SELECTED_GROUP"
mkdir -p "$GROUP_DIR"

echo ""
echo "Grupo seleccionado: $SELECTED_GROUP"
echo ""

# Ask for the block name (por CLI o interactivo, por defecto: index)
if [ -n "$CLI_BLOCK_NAME" ]; then
    BLOCK_NAME="$CLI_BLOCK_NAME"
else
    echo "Condiciones para el nombre del bloque:"
    echo "  • Solo letras minúsculas (a-z)"
    echo "  • Puede contener números (0-9)"
    echo "  • Puede contener guiones (-)"
    echo "  • No puede empezar ni terminar con guión"
    echo "  • No puede tener guiones consecutivos"
    echo "  • No puede tener espacios ni caracteres especiales"
    echo "  • Ejemplo válido: mi-bloque, bloque-123, test-block"
    echo ""
    read -p "Nombre del bloque (por defecto: index): " BLOCK_NAME

    if [ -z "$BLOCK_NAME" ]; then
        BLOCK_NAME="index"
    fi
fi

# Validate the block name
while ! validate_block_name "$BLOCK_NAME" "$GROUP_DIR"; do
    echo ""
    read -p "Nombre del bloque: " BLOCK_NAME
done

# Ask for the block title (CLI o interactivo)
echo ""
if [ -n "$CLI_BLOCK_TITLE" ]; then
    BLOCK_TITLE="$CLI_BLOCK_TITLE"
else
    read -p "Título del bloque: " BLOCK_TITLE
fi

# Validate that the title is not empty
while [ -z "$BLOCK_TITLE" ]; do
    echo "✗ Error: El título del bloque no puede estar vacío"
    read -p "Título del bloque: " BLOCK_TITLE
done

# Add the group name to the title and block name
FULL_BLOCK_NAME="$SELECTED_GROUP/$BLOCK_NAME"
# Use group-name format with hyphens for block.json name
BLOCK_JSON_NAME="$SELECTED_GROUP-$BLOCK_NAME"
FULL_BLOCK_TITLE="$SELECTED_GROUP.$BLOCK_TITLE"

# Create the new block directory
NEW_BLOCK_DIR="$GROUP_DIR/$BLOCK_NAME"
echo ""
echo "Copiando bloque base a $NEW_BLOCK_DIR..."
cp -r "$BASE_BLOCK_DIR" "$NEW_BLOCK_DIR"

if [ $? -ne 0 ]; then
    echo "✗ Error: No se pudo copiar el bloque base"
    exit 1
fi

echo "✓ Bloque copiado exitosamente"

# Modify block.json
echo "Modificando block.json..."
sed -i "s|\"name\": \"[^\"]*\/0_block\"|\"name\": \"$NAMESPACE/$BLOCK_JSON_NAME\"|g" "$NEW_BLOCK_DIR/block.json"
sed -i "s|\"textdomain\": \"0_block\"|\"textdomain\": \"$BLOCK_NAME\"|g" "$NEW_BLOCK_DIR/block.json"
sed -i "s|\"title\": \"0 Block\"|\"title\": \"$FULL_BLOCK_TITLE\"|g" "$NEW_BLOCK_DIR/block.json"
echo "✓ block.json modificado"

# Modify edit.js
echo "Modificando edit.js..."
sed -i "s|<p {|<div {|g" "$NEW_BLOCK_DIR/edit.js"
sed -i "s|</p>|</div>|g" "$NEW_BLOCK_DIR/edit.js"
# Use the correct em dash
sed -i "s|{ __( '0 Block – hello from the editor!', '0_block' ) }|{ __( '$FULL_BLOCK_TITLE – hello from the editor!', '$BLOCK_NAME' ) }|g" "$NEW_BLOCK_DIR/edit.js"
echo "✓ edit.js modificado"

# Modify render.php
echo "Modificando render.php..."
cat > "$NEW_BLOCK_DIR/render.php" << EOF
<?php
/**
 * PHP file to use when rendering the block type on the server to show on the front end.
 *
 * The following variables are exposed to the file:
 *     \$attributes (array): The block attributes.
 *     \$content (string): The block default content.
 *     \$block (WP_Block): The block instance.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<h1><?php echo esc_html( '$FULL_BLOCK_TITLE' ); ?></h1>
</div>
EOF
echo "✓ render.php modificado"

# Modify view.js
echo "Modificando view.js..."
VIEW_LOG_NAME="$SELECTED_GROUP-$BLOCK_NAME"
sed -i "s|console.log( 'Hello World! (from myblocks-0_block block)' );|console.log( 'Hello World! (from $NAMESPACE-$VIEW_LOG_NAME block)' );|g" "$NEW_BLOCK_DIR/view.js"
echo "✓ view.js modificado"

# Modify style.scss - use the full block name with hyphens for the CSS class
echo "Modificando style.scss..."
CSS_CLASS_NAME="$SELECTED_GROUP-$BLOCK_NAME"
sed -i "s|\.wp-block-myblocks-0_block|\.wp-block-$NAMESPACE-$CSS_CLASS_NAME|g" "$NEW_BLOCK_DIR/style.scss"
# Add styling preference comment if not already present
if ! grep -q "Prefer using Tailwind CSS" "$NEW_BLOCK_DIR/style.scss"; then
	# Insert comment after the closing */ of the header comment
	sed -i '/\*\//a\
 *\
 * Prefer using Tailwind CSS for styling in render.php.\
 * For complex designs that cannot be achieved with Tailwind CSS, use style.scss' "$NEW_BLOCK_DIR/style.scss"
fi
echo "✓ style.scss modificado"

# Modify editor.scss
echo "Modificando editor.scss..."
sed -i "s|\.wp-block-myblocks-0_block|\.wp-block-$NAMESPACE-$CSS_CLASS_NAME|g" "$NEW_BLOCK_DIR/editor.scss"
echo "✓ editor.scss modificado"

# Update functions.php to register the new block
echo ""
echo "Actualizando functions.php..."
BUILD_PATH="$SELECTED_GROUP/$BLOCK_NAME"
if ! grep -q "register_block_type( __DIR__ . '/build/$BUILD_PATH' )" "$THEME_DIR/functions.php"; then
    # Add the registration line for the new block inside the myblocksinit function
    sed -i "s|register_block_type( __DIR__ . '/build/0_block' );|register_block_type( __DIR__ . '/build/0_block' );\n    register_block_type( __DIR__ . '/build/$BUILD_PATH' );|g" "$THEME_DIR/functions.php"
fi
echo "✓ functions.php actualizado"

# Update webpack.config.js: add block entries only (do not touch output)
echo "Actualizando webpack.config.js (solo entry)..."
WEBPACK_CONFIG="$THEME_DIR/webpack.config.js"
if [ -f "$WEBPACK_CONFIG" ]; then
    # Check if entries already exist, similar to functions.php logic
    if ! grep -q "'$BUILD_PATH/index'" "$WEBPACK_CONFIG"; then
        # Add block entries only: match the }, that closes entry (the one before "output:")
        sed -i "/^[[:space:]]*},[[:space:]]*$/{ N; s|^[[:space:]]*},[[:space:]]*\n[[:space:]]*output:.*|\t\t'$BUILD_PATH/index': path.resolve(process.cwd(), 'src/$BUILD_PATH', 'index.js'),\n\t\t'$BUILD_PATH/view': path.resolve(process.cwd(), 'src/$BUILD_PATH', 'view.js'),\n\t},\n\toutput: {|; }" "$WEBPACK_CONFIG"
        echo "✓ Entradas de bloque agregadas a webpack.config.js (solo entry)"
    else
        echo "⚠ Las entradas ya existen en webpack.config.js"
    fi
else
    echo "⚠ Advertencia: No se encontró el archivo webpack.config.js"
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  ✓ Bloque '$FULL_BLOCK_NAME' creado exitosamente!"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "Grupo: $SELECTED_GROUP"
echo "Ubicación: $NEW_BLOCK_DIR"
echo ""
echo "Próximos pasos:"
echo "  1. Ejecuta 'npm run build' para compilar el bloque"
echo "  2. El bloque estará disponible en el editor de WordPress"
echo ""
