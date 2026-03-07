#!/bin/bash

# Script to delete WordPress blocks
# Usage: ./delete-block.sh

# Get the script directory and change to the theme directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$(dirname "$SCRIPT_DIR")"
SRC_DIR="$THEME_DIR/src"
BUILD_DIR="$THEME_DIR/build"
TEMPLATES_DIR="$THEME_DIR/templates"

# Function to get available groups
get_available_groups() {
    local groups=()
    
    # Get directories in src/ that contain blocks
    if [ -d "$SRC_DIR" ]; then
        while IFS= read -r dir; do
            if [ -d "$dir" ] && [ "$(basename "$dir")" != "0_block" ]; then
                local basename=$(basename "$dir")
                # Check that the directory contains at least one block (has block.json in subdirectories)
                if find "$dir" -mindepth 2 -maxdepth 2 -name "block.json" -type f 2>/dev/null | grep -q .; then
                    groups+=("$basename")
                fi
            fi
        done < <(find "$SRC_DIR" -maxdepth 1 -type d 2>/dev/null | sort)
    fi
    
    printf '%s\n' "${groups[@]}"
}

# Function to get blocks in a group
get_blocks_in_group() {
    local group="$1"
    local group_dir="$SRC_DIR/$group"
    local blocks=()
    
    if [ -d "$group_dir" ]; then
        while IFS= read -r dir; do
            if [ -d "$dir" ] && [ -f "$dir/block.json" ]; then
                local basename=$(basename "$dir")
                blocks+=("$basename")
            fi
        done < <(find "$group_dir" -maxdepth 1 -type d 2>/dev/null | sort)
    fi
    
    printf '%s\n' "${blocks[@]}"
}

# Change to the theme directory
cd "$THEME_DIR" || {
    echo "✗ Error: No se pudo acceder al directorio del tema: $THEME_DIR"
    exit 1
}

# Get available groups
mapfile -t AVAILABLE_GROUPS < <(get_available_groups)

if [ ${#AVAILABLE_GROUPS[@]} -eq 0 ]; then
    echo "✗ Error: No se encontraron grupos con blockes"
    exit 1
fi

# Show group selection menu
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  Eliminar bloque de WordPress"
echo "═══════════════════════════════════════════════════════════════"
echo ""
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

# Get blocks from the selected group
mapfile -t GROUP_BLOCKS < <(get_blocks_in_group "$SELECTED_GROUP")

if [ ${#GROUP_BLOCKS[@]} -eq 0 ]; then
    echo ""
    echo "✗ Error: No se encontraron blockes en el grupo '$SELECTED_GROUP'"
    exit 1
fi

# Show block selection menu
echo ""
echo "Grupo seleccionado: $SELECTED_GROUP"
echo ""
echo "Selecciona un blocke:"
for i in "${!GROUP_BLOCKS[@]}"; do
    echo "  $((i+1)). ${GROUP_BLOCKS[$i]}"
done
echo ""

# Ask for block selection
while true; do
    read -p "Componente (número): " BLOCK_SELECTION
    if [[ "$BLOCK_SELECTION" =~ ^[0-9]+$ ]] && [ "$BLOCK_SELECTION" -ge 1 ] && [ "$BLOCK_SELECTION" -le ${#GROUP_BLOCKS[@]} ]; then
        SELECTED_BLOCK="${GROUP_BLOCKS[$((BLOCK_SELECTION-1))]}"
        break
    else
        echo "✗ Error: Por favor selecciona un número válido (1-${#GROUP_BLOCKS[@]})"
    fi
done

# Confirm deletion
echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  Confirmar eliminación"
echo "═══════════════════════════════════════════════════════════════"
echo ""
echo "Grupo: $SELECTED_GROUP"
echo "Componente: $SELECTED_BLOCK"
echo ""
read -p "¿Estás seguro de que deseas eliminar este blocke? (s/N): " CONFIRM

if [[ ! "$CONFIRM" =~ ^[sS]$ ]]; then
    echo ""
    echo "✗ Operación cancelada"
    exit 0
fi

# Component paths
BLOCK_SRC_DIR="$SRC_DIR/$SELECTED_GROUP/$SELECTED_BLOCK"
BLOCK_BUILD_DIR="$BUILD_DIR/$SELECTED_GROUP/$SELECTED_BLOCK"
BUILD_PATH="$SELECTED_GROUP/$SELECTED_BLOCK"

# Delete directory in src/
if [ -d "$BLOCK_SRC_DIR" ]; then
    echo ""
    echo "Eliminando blocke de src/..."
    rm -rf "$BLOCK_SRC_DIR"
    if [ $? -eq 0 ]; then
        echo "✓ Componente eliminado de src/"
    else
        echo "✗ Error: No se pudo eliminar el blocke de src/"
        exit 1
    fi
else
    echo "⚠ Advertencia: El directorio $BLOCK_SRC_DIR no existe"
fi

# Delete directory in build/
if [ -d "$BLOCK_BUILD_DIR" ]; then
    echo "Eliminando blocke de build/..."
    rm -rf "$BLOCK_BUILD_DIR"
    if [ $? -eq 0 ]; then
        echo "✓ Componente eliminado de build/"
    else
        echo "⚠ Advertencia: No se pudo eliminar el blocke de build/"
    fi
fi

# Remove registration in functions.php
echo "Actualizando functions.php..."
FUNCTIONS_FILE="$THEME_DIR/functions.php"
if [ -f "$FUNCTIONS_FILE" ]; then
    # Search and remove the block registration line
    if grep -q "register_block_type( __DIR__ . '/build/$BUILD_PATH' )" "$FUNCTIONS_FILE"; then
        # Create a temporary file without the line to delete
        grep -v "register_block_type( __DIR__ . '/build/$BUILD_PATH' )" "$FUNCTIONS_FILE" > "$FUNCTIONS_FILE.tmp"
        mv "$FUNCTIONS_FILE.tmp" "$FUNCTIONS_FILE"
        echo "✓ Registro eliminado de functions.php"
    else
        echo "⚠ Advertencia: No se encontró el registro en functions.php"
    fi
else
    echo "⚠ Advertencia: No se encontró el archivo functions.php"
fi

# Remove entries from webpack.config.js
echo "Actualizando webpack.config.js..."
WEBPACK_CONFIG="$THEME_DIR/webpack.config.js"
if [ -f "$WEBPACK_CONFIG" ]; then
    # Search and remove block entries, similar to functions.php logic
    if grep -q "'$BUILD_PATH/index'" "$WEBPACK_CONFIG"; then
        # Remove both index and view entries in one pass
        grep -v -E "('$BUILD_PATH/(index|view)')" "$WEBPACK_CONFIG" > "$WEBPACK_CONFIG.tmp"
        mv "$WEBPACK_CONFIG.tmp" "$WEBPACK_CONFIG"
        echo "✓ Entradas de bloque eliminadas de webpack.config.js"
    else
        echo "⚠ Advertencia: No se encontraron las entradas en webpack.config.js"
    fi
else
    echo "⚠ Advertencia: No se encontró el archivo webpack.config.js"
fi

# Check if the group is empty and delete it if necessary
GROUP_DIR="$SRC_DIR/$SELECTED_GROUP"
if [ -d "$GROUP_DIR" ]; then
    # Count how many directories remain (excluding . and ..)
    REMAINING=$(find "$GROUP_DIR" -mindepth 1 -maxdepth 1 -type d 2>/dev/null | wc -l)
    if [ "$REMAINING" -eq 0 ]; then
        echo ""
        read -p "El grupo '$SELECTED_GROUP' está vacío. ¿Deseas eliminarlo? (s/N): " DELETE_GROUP
        if [[ "$DELETE_GROUP" =~ ^[sS]$ ]]; then
            rm -rf "$GROUP_DIR"
            echo "✓ Grupo '$SELECTED_GROUP' eliminado"
        fi
    fi
fi

echo ""
echo "═══════════════════════════════════════════════════════════════"
echo "  ✓ Componente '$SELECTED_GROUP/$SELECTED_BLOCK' eliminado"
echo "═══════════════════════════════════════════════════════════════"
echo ""

