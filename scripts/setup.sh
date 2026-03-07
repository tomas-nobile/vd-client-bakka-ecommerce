#!/bin/bash

# Script to create WordPress blocks
# Usage: ./create-component.sh block-name

# Get the script directory and change to the theme directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
THEME_DIR="$(dirname "$SCRIPT_DIR")"
THEME_NAME="$(basename "$THEME_DIR")"
NAMESPACE=$(echo "$THEME_NAME" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9-]/-/g' | sed 's/--*/-/g' | sed 's/^-\|-$//g')

# Change to the theme directory
cd "$THEME_DIR" || {
    echo "✗ Error: No se pudo acceder al directorio del tema: $THEME_DIR"
    exit 1
}

# Create style.css, theme.json and templates/index.html
cat > style.css << EOF
/*
Theme Name: $THEME_NAME
Description: null
Version: 1.0.0
Author: tomas-nobile
*/
EOF
cat > theme.json << EOF
{
    "name": "$THEME_NAME",
    "version": "1.0.0",
    "description": "null",
    "author": "tomas-nobile"
}
EOF
mkdir -p templates
touch templates/index.html

# Create the block directly in the theme directory (using . for the current directory)
npx @wordpress/create-block@latest "0_block" --variant dynamic --target-dir . --namespace "$NAMESPACE"

# Verify if the creation was successful
if [ $? -eq 0 ]; then
    echo "✓ Bloque '0_block' creado exitosamente en $THEME_DIR"
else
    echo "✗ Error al crear el bloque '0_block'"
    exit 1
fi

# Delete 0_block.php file 
rm 0_block.php

# Update functions.php with block registration and asset loading
cat > functions.php << 'EOF'
<?php
function myblocksinit() {
    register_block_type( __DIR__ . '/build/0_block' );
}
add_action( 'init', 'myblocksinit' );

function test_theme_load_assets() {
    wp_enqueue_script('test-theme-main-js', get_theme_file_uri('/build/index.js'), array('wp-element'), '1.0', true);
    wp_enqueue_style('test-theme-main-css', get_theme_file_uri('/build/index.css'));
}
add_action('wp_enqueue_scripts', 'test_theme_load_assets');

function test_theme_add_support() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'test_theme_add_support');
?>
EOF

echo "✓ functions.php actualizado con enqueue de assets"

# Install Tailwind CSS dependencies
echo "📦 Instalando dependencias de Tailwind CSS..."
npm install --save-dev tailwindcss@^4.0.6 @tailwindcss/cli@^4.0.6 @tailwindcss/typography@^0.5.16 npm-run-all@^4.1.5

if [ $? -eq 0 ]; then
    echo "✓ Dependencias de Tailwind CSS instaladas exitosamente"
else
    echo "✗ Error al instalar dependencias de Tailwind CSS"
    exit 1
fi

# Update package.json scripts to include Tailwind
echo "📝 Actualizando scripts en package.json..."
node -e "
const fs = require('fs');
const pkg = JSON.parse(fs.readFileSync('package.json', 'utf8'));
pkg.scripts = {
    ...pkg.scripts,
    'build': 'npm-run-all --sequential buildwp tailwindbuild',
    'buildwp': pkg.scripts.build,
    'start': 'npm-run-all --parallel wpstart tailwindwatch',
    'wpstart': pkg.scripts.start,
    'tailwindbuild': 'tailwindcss -i ./src/index.css -o ./build/index.css --minify',
    'tailwindwatch': 'tailwindcss -i ./src/index.css -o ./build/index.css --watch --minify'
};
fs.writeFileSync('package.json', JSON.stringify(pkg, null, '\t'));
"

echo "✓ Scripts de Tailwind añadidos a package.json"

# Create src/index.css with Tailwind imports
cat > src/index.css << 'EOF'
@import "tailwindcss";
@plugin "@tailwindcss/typography";
EOF

echo "✓ Archivo src/index.css creado con imports de Tailwind"

# Create src/index.js main theme entry point
cat > src/index.js << 'EOF'
// Main theme JavaScript entry point
// Add your custom JavaScript here
EOF

echo "✓ Archivo src/index.js creado"

# Create webpack.config.js
cat > webpack.config.js << 'EOF'
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,
	entry: {
		// Main theme entry point
		index: path.resolve(process.cwd(), 'src', 'index.js'),
		// Block entries
		'0_block/index': path.resolve(process.cwd(), 'src/0_block', 'index.js'),
		'0_block/view': path.resolve(process.cwd(), 'src/0_block', 'view.js'),
		'core/navbar/index': path.resolve(process.cwd(), 'src/core/navbar', 'index.js'),
		'core/navbar/view': path.resolve(process.cwd(), 'src/core/navbar', 'view.js'),
		'core/header/index': path.resolve(process.cwd(), 'src/core/header', 'index.js'),
		'core/header/view': path.resolve(process.cwd(), 'src/core/header', 'view.js'),
		'index/tomas/index': path.resolve(process.cwd(), 'src/index/tomas', 'index.js'),
		'index/tomas/view': path.resolve(process.cwd(), 'src/index/tomas', 'view.js'),
	},
};
EOF

echo "✓ webpack.config.js creado"

# Build the theme with Tailwind
echo "🔨 Compilando tema con Tailwind CSS..."
npm run build

if [ $? -eq 0 ]; then
    echo "✓ Tema compilado exitosamente"
    echo ""
    echo "🎉 ¡Tema WordPress con Tailwind CSS configurado correctamente!"
    echo ""
    echo "📝 Comandos disponibles:"
    echo "  npm run build  - Compilación para producción"
    echo "  npm run start  - Modo desarrollo con watch"
    echo ""
else
    echo "✗ Error al compilar el tema"
    exit 1
fi

