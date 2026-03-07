#!/bin/bash

# Script para iniciar desarrollo con webpack y tailwind
# Asegura que tailwindwatch se inicie después de que webpack termine de limpiar

echo "🔨 Generando CSS inicial de Tailwind..."
tailwindcss -i ./src/index.css -o ./build/index.css --minify

if [ $? -ne 0 ]; then
    echo "❌ Error al generar CSS inicial"
    exit 1
fi

echo "✅ CSS inicial generado"
echo "🚀 Iniciando webpack y tailwind en modo watch..."

# Iniciar wpstart en background
npm run wpstart &
WPSTART_PID=$!

# Esperar a que webpack termine su primera compilación
# Verificamos que el directorio build existe y tiene archivos
echo "⏳ Esperando a que webpack termine la primera compilación..."
sleep 3

# Verificar que build/index.css existe (puede haber sido borrado por webpack)
if [ ! -f "./build/index.css" ]; then
    echo "⚠️  build/index.css fue borrado, regenerando..."
    tailwindcss -i ./src/index.css -o ./build/index.css --minify
fi

# Iniciar tailwindwatch
echo "🎨 Iniciando Tailwind en modo watch..."
tailwindcss -i ./src/index.css -o ./build/index.css --watch --minify &
TAILWIND_PID=$!

# Función para limpiar procesos al salir
cleanup() {
    echo ""
    echo "🛑 Deteniendo procesos..."
    kill $WPSTART_PID 2>/dev/null
    kill $TAILWIND_PID 2>/dev/null
    exit 0
}

# Capturar Ctrl+C
trap cleanup SIGINT SIGTERM

# Esperar a que ambos procesos terminen
wait $WPSTART_PID $TAILWIND_PID

