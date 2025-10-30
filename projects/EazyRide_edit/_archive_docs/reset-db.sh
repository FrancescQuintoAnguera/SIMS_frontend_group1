#!/bin/bash

# ====================================================================
# Script para resetear la base de datos y asegurar esquema actualizado
# ====================================================================
# Uso: ./reset-db.sh
# 
# Este script:
# - Detiene todos los contenedores Docker
# - Elimina volúmenes antiguos (importante para actualizar el esquema)
# - Reconstruye y reinicia todos los servicios
# - Verifica que la base de datos se creó correctamente
#
# ⚠️  IMPORTANTE: Ejecuta este script cuando:
#    - Clones el repositorio por primera vez
#    - Cambies de rama Git (especialmente si hay cambios en mariadb-init.sql)
#    - Veas errores como "Unknown column 'v.battery_level'"
# ====================================================================

set -e  # Detener si hay algún error

echo ""
echo "╔════════════════════════════════════════════════╗"
echo "║  🔄  Reiniciando Base de Datos y Contenedores  ║"
echo "╚════════════════════════════════════════════════╝"
echo ""

echo "📦 [1/5] Deteniendo contenedores y eliminando volúmenes antiguos..."
docker-compose down -v

echo ""
echo "🏗️  [2/5] Reconstruyendo imágenes Docker..."
docker-compose build

echo ""
echo "🚀 [3/5] Iniciando todos los servicios..."
docker-compose up -d

echo ""
echo "⏳ [4/5] Esperando que MariaDB se inicialice..."
echo "    (esto puede tardar 15-20 segundos)"
for i in {1..20}; do
    if docker exec VC-mariadb mariadb -u root -p'simsuser123.' -e "SELECT 1" &>/dev/null; then
        echo "    ✅ MariaDB está listo!"
        break
    fi
    echo -n "."
    sleep 1
done
echo ""

echo ""
echo "🔍 [5/5] Verificando estructura de la base de datos..."
echo ""

# Verificar que la tabla vehicles existe y tiene las columnas correctas
if docker exec VC-mariadb mariadb -u root -p'simsuser123.' simsdb -e "DESCRIBE vehicles;" 2>/dev/null | grep -q "battery_level"; then
    echo "✅ Tabla 'vehicles' verificada correctamente"
    
    # Mostrar cuántos vehículos hay
    VEHICLE_COUNT=$(docker exec VC-mariadb mariadb -u root -p'simsuser123.' simsdb -e "SELECT COUNT(*) FROM vehicles;" 2>/dev/null | tail -1)
    echo "✅ Vehículos en la base de datos: $VEHICLE_COUNT"
    
    echo ""
    echo "╔════════════════════════════════════════════════╗"
    echo "║            ✅  TODO LISTO  ✅                   ║"
    echo "╚════════════════════════════════════════════════╝"
    echo ""
    echo "🌐 Aplicación web:    http://localhost:8080"
    echo "🔧 phpMyAdmin:        http://localhost:8081"
    echo "📊 MongoDB Express:   http://localhost:8082 (si está configurado)"
    echo ""
    echo "💡 Recuerda: Necesitas registrarte de nuevo ya que la BD se ha recreado"
    echo ""
else
    echo ""
    echo "❌ ERROR: La tabla 'vehicles' no tiene la estructura correcta"
    echo "   Por favor, verifica el archivo mariadb-init.sql"
    echo ""
    exit 1
fi
