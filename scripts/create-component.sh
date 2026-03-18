#!/bin/bash

# Script to create PHP component functions for WordPress theme
# Uso interactivo: ./scripts/create-component.sh
# Ejemplo con parámetros: ./scripts/create-component.sh -b front-page -c blog-card-modal -d "Tarjeta modal del blog en home"
# Parámetros:
#   -b | --block       => Nombre del bloque/página al que pertenece el componente (por ejemplo: front-page, archive-product).
#                         Debe coincidir con uno de los grupos detectados en la carpeta templates o con "core".
#   -c | --component   => Nombre interno del componente (slug). Se usa para el nombre del archivo PHP y la clase CSS.
#                         Solo minúsculas/números/guiones (ejemplo: blog-card-modal).
#   -d | --description => Descripción breve del componente. Se usa en el comentario PHP y como título por defecto en el HTML inicial.
#   -h | --help        => Muestra este mensaje de ayuda y termina la ejecución.

# Get the script directory and change to the theme directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$(dirname "$SCRIPT_DIR")"
THEME_NAME="$(basename "$THEME_DIR")"
SRC_DIR="$THEME_DIR/src"
TEMPLATES_DIR="$THEME_DIR/templates"

# Parámetros CLI
CLI_BLOCK=""
CLI_COMPONENT_NAME=""
CLI_COMPONENT_DESCRIPTION=""

while [[ $# -gt 0 ]]; do
    case "$1" in
        -b|--block)
            CLI_BLOCK="$2"
            shift 2
            ;;
        -c|--component)
            CLI_COMPONENT_NAME="$2"
            shift 2
            ;;
        -d|--description)
            CLI_COMPONENT_DESCRIPTION="$2"
            shift 2
            ;;
        -h|--help)
            echo "Uso: ./scripts/create-component.sh [-b bloque] [-c componente] [-d descripcion]"
            echo "  -b, --block       Nombre del bloque/página (por ejemplo: front-page)"
            echo "  -c, --component   Nombre del componente (por ejemplo: blog-card-modal)"
            echo "  -d, --description Descripción breve del componente"
            exit 0
            ;;
        *)
            echo "✗ Opción desconocida: $1"
            echo "Prueba con --help para ver las opciones disponibles."
            exit 1
            ;;
    esac
done

# Function to get available groups/pages
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

# Function to validate the component name
validate_component_name() {
    local name="$1"
    local components_dir="$2"
    
    # Check that it's not empty
    if [ -z "$name" ]; then
        echo "✗ Error: El nombre del componente no puede estar vacío"
        return 1
    fi
    
    # Check that it only contains lowercase letters, numbers, and hyphens
    if [[ ! "$name" =~ ^[a-z0-9-]+$ ]]; then
        echo "✗ Error: El nombre del componente solo puede contener letras minúsculas, números y guiones"
        return 1
    fi
    
    # Check that it doesn't start or end with a hyphen
    if [[ "$name" =~ ^- ]] || [[ "$name" =~ -$ ]]; then
        echo "✗ Error: El nombre del componente no puede empezar ni terminar con guión"
        return 1
    fi
    
    # Check that it doesn't have consecutive hyphens
    if [[ "$name" =~ -- ]]; then
        echo "✗ Error: El nombre del componente no puede tener guiones consecutivos"
        return 1
    fi
    
    # Check that it has at least one character
    if [ ${#name} -lt 1 ]; then
        echo "✗ Error: El nombre del componente debe tener al menos un carácter"
        return 1
    fi
    
    # Check that the file doesn't already exist
    if [ -f "$components_dir/${name}.php" ]; then
        echo "✗ Error: Ya existe un componente con el nombre '${name}.php'"
        return 1
    fi
    
    return 0
}

# Change to the theme directory
cd "$THEME_DIR" || {
    echo "✗ Error: No se pudo acceder al directorio del tema: $THEME_DIR"
    exit 1
}

# Get available groups
mapfile -t AVAILABLE_GROUPS < <(get_available_groups)

if [ ${#AVAILABLE_GROUPS[@]} -eq 0 ]; then
    echo "✗ Error: No se encontraron grupos disponibles"
    exit 1
fi

# Selección de grupo/bloque (CLI o interactivo)
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  Crear nuevo componente PHP"
echo "═══════════════════════════════════════════════════════════════"
echo ""

if [ -n "$CLI_BLOCK" ]; then
    SELECTED_GROUP=""
    for g in "${AVAILABLE_GROUPS[@]}"; do
        if [ "$g" == "$CLI_BLOCK" ]; then
            SELECTED_GROUP="$g"
            break
        fi
    done

    if [ -z "$SELECTED_GROUP" ]; then
        echo "✗ Error: El bloque/página '$CLI_BLOCK' no existe entre los disponibles."
        echo "  Bloques/páginas disponibles: ${AVAILABLE_GROUPS[*]}"
        exit 1
    fi
else
    echo "Selecciona una página/grupo:"
    for i in "${!AVAILABLE_GROUPS[@]}"; do
        echo "  $((i+1)). ${AVAILABLE_GROUPS[$i]}"
    done
    echo ""

    # Ask for group selection
    while true; do
        read -p "Página/Grupo (número): " GROUP_SELECTION
        if [[ "$GROUP_SELECTION" =~ ^[0-9]+$ ]] && [ "$GROUP_SELECTION" -ge 1 ] && [ "$GROUP_SELECTION" -le ${#AVAILABLE_GROUPS[@]} ]; then
            SELECTED_GROUP="${AVAILABLE_GROUPS[$((GROUP_SELECTION-1))]}"
            break
        else
            echo "✗ Error: Por favor selecciona un número válido (1-${#AVAILABLE_GROUPS[@]})"
        fi
    done
fi

# Determine components directory based on group
COMPONENTS_DIR="$SRC_DIR/$SELECTED_GROUP/index/components"

# Create components directory if it doesn't exist
if [ ! -d "$COMPONENTS_DIR" ]; then
    mkdir -p "$COMPONENTS_DIR"
    echo "✓ Directorio de componentes creado: $COMPONENTS_DIR"
fi

echo ""
echo "Página/Grupo seleccionado: $SELECTED_GROUP"
echo "Ubicación: $COMPONENTS_DIR"
echo ""

# Ask for the component name (CLI o interactivo)
if [ -n "$CLI_COMPONENT_NAME" ]; then
    COMPONENT_NAME="$CLI_COMPONENT_NAME"
else
    echo "Condiciones para el nombre del componente:"
    echo "  • Solo letras minúsculas (a-z)"
    echo "  • Puede contener números (0-9)"
    echo "  • Puede contener guiones (-)"
    echo "  • No puede empezar ni terminar con guión"
    echo "  • No puede tener guiones consecutivos"
    echo "  • No puede tener espacios ni caracteres especiales"
    echo "  • Ejemplo válido: searchbar, product-card, filter-menu"
    echo ""
    read -p "Nombre del componente: " COMPONENT_NAME
fi

# Validate the component name
while ! validate_component_name "$COMPONENT_NAME" "$COMPONENTS_DIR"; do
    echo ""
    read -p "Nombre del componente: " COMPONENT_NAME
done

# Ask for the component description (CLI o interactivo)
echo ""
if [ -n "$CLI_COMPONENT_DESCRIPTION" ]; then
    COMPONENT_DESCRIPTION="$CLI_COMPONENT_DESCRIPTION"
else
    read -p "Descripción breve del componente: " COMPONENT_DESCRIPTION
fi

# Validate that the description is not empty
while [ -z "$COMPONENT_DESCRIPTION" ]; do
    echo "✗ Error: La descripción no puede estar vacía"
    read -p "Descripción breve del componente: " COMPONENT_DESCRIPTION
done

# Generate function name (replace hyphens with underscores)
FUNCTION_NAME="etheme_render_$(echo "$COMPONENT_NAME" | tr '-' '_')"
COMPONENT_FILE="${COMPONENT_NAME}.php"
COMPONENT_PATH="$COMPONENTS_DIR/$COMPONENT_FILE"

# Create the component file
echo ""
echo "Creando componente $COMPONENT_FILE..."

cat > "$COMPONENT_PATH" << EOF
<?php
/**
 * $COMPONENT_DESCRIPTION
 * 
 * @param array \$args Optional arguments for the component.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function $FUNCTION_NAME( \$args = array() ) {
	// Set default arguments
	\$defaults = array(
		// Add your default arguments here
	);
	
	\$args = wp_parse_args( \$args, \$defaults );
	
	// Extract arguments for easier access
	extract( \$args );
	?>
	
	<div class="component-$COMPONENT_NAME">
		<h2><?php echo esc_html( '$COMPONENT_DESCRIPTION' ); ?></h2>
		<!-- Add your component HTML here -->
	</div>
	<?php
}

EOF

if [ $? -eq 0 ]; then
    echo "✓ Componente creado exitosamente"
else
    echo "✗ Error: No se pudo crear el componente"
    exit 1
fi

# Check if there's a render.php file to update
RENDER_FILE="$SRC_DIR/$SELECTED_GROUP/index/render.php"

if [ -f "$RENDER_FILE" ]; then
    echo ""
    read -p "¿Deseas agregar el require_once al render.php? (s/N): " ADD_REQUIRE
    
    if [[ "$ADD_REQUIRE" =~ ^[sS]$ ]]; then
        # Check if the require_once already exists
        if grep -q "require_once.*$COMPONENT_FILE" "$RENDER_FILE"; then
            echo "⚠ El componente ya está incluido en render.php"
        else
            # Find the line with the last require_once for components
            LAST_REQUIRE_LINE=$(grep -n "require_once.*components.*\.php" "$RENDER_FILE" | tail -1 | cut -d: -f1)
            
            if [ -n "$LAST_REQUIRE_LINE" ]; then
                # Add after the last component require
                sed -i "${LAST_REQUIRE_LINE}a\\require_once get_template_directory() . '/src/$SELECTED_GROUP/index/components/$COMPONENT_FILE';" "$RENDER_FILE"
                echo "✓ Componente agregado a render.php"
            else
                # No components found, try to add after filter-helpers
                HELPER_LINE=$(grep -n "require_once.*filter-helpers.php" "$RENDER_FILE" | head -1 | cut -d: -f1)
                
                if [ -n "$HELPER_LINE" ]; then
                    sed -i "${HELPER_LINE}a\\\n// Include component functions\\nrequire_once get_template_directory() . '/src/$SELECTED_GROUP/index/components/$COMPONENT_FILE';" "$RENDER_FILE"
                    echo "✓ Componente agregado a render.php"
                else
                    echo "⚠ No se pudo encontrar dónde agregar el require_once automáticamente"
                    echo "  Agrega manualmente esta línea a render.php:"
                    echo "  require_once get_template_directory() . '/src/$SELECTED_GROUP/index/components/$COMPONENT_FILE';"
                fi
            fi
        fi
    fi
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  ✓ Componente '$COMPONENT_NAME' creado exitosamente!"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "Ubicación: $COMPONENT_PATH"
echo "Función: $FUNCTION_NAME()"
echo ""
echo "Próximos pasos:"
echo "  1. Edita el archivo $COMPONENT_FILE para implementar tu componente"
echo "  2. Usa la función $FUNCTION_NAME() en tu render.php"
echo "  3. Ejemplo de uso:"
echo "     <?php $FUNCTION_NAME( array( 'key' => 'value' ) ); ?>"
echo ""

