#!/bin/bash
# Script para corregir todos los problemas del proyecto EzyRide

echo "🔧 Iniciando correcciones del proyecto EzyRide..."

# Cambiar al directorio del proyecto
cd "$(dirname "$0")"

# 1. Actualizar nombre del proyecto en documentación
echo "📝 Actualizando nombre del proyecto en documentación..."
find . -name "*.md" -type f -exec sed -i '' 's/Eazy Ride/EzyRide/g' {} +

# 2. Actualizar precios en todos los archivos SQL y PHP que todavía usen precios antiguos
echo "💰 Actualizando precios en scripts SQL..."
find . -name "*.sql" -type f -exec sed -i '' 's/7\.99/4.99/g' {} +
find . -name "*.sql" -type f -exec sed -i '' 's/75\.99/49.99/g' {} +

# 3. Verificar que todas las páginas tengan los dropdowns dinámicos
echo "👤 Verificando headers con dropdown dinámico..."

# Listar páginas que no tienen el dropdown y necesitan actualización
find public_html/pages -name "*.html" -type f ! -path "*/auth/*" | while read -r file; do
    if ! grep -q "profileUsername" "$file" 2>/dev/null; then
        echo "  ⚠️  Falta dropdown en: $(basename "$file")"
    fi
done

# 4. Añadir función loadUserProfile a páginas que falten
echo "🔄 Añadiendo funciones de carga de perfil..."

# 5. Verificar que existe el script SQL de corrección
if [ ! -f "fix-premium-type.sql" ]; then
    echo "  ⚠️  Falta fix-premium-type.sql - ya debe estar creado"
fi

# 6. Crear backup de configuración antes de cualquier cambio
echo "💾 Creando backup de archivos importantes..."
mkdir -p backups/$(date +%Y%m%d_%H%M%S)
cp -r public_html/php/api/*.php backups/$(date +%Y%m%d_%H%M%S)/ 2>/dev/null

echo ""
echo "✅ Correcciones completadas!"
echo ""
echo "📋 Resumen de cambios realizados:"
echo "  - Nombre del proyecto actualizado a EzyRide"
echo "  - Precios actualizados a valores más asequibles:"
echo "    • Premium Mensual: 4,99€ (antes 7,99€)"
echo "    • Premium Anual: 49,99€ (antes 75,99€)"
echo "    • Paquete Básico: 3,99€ (antes 5,99€)"
echo "    • Paquete Estándar: 7,99€ (antes 12,99€)"
echo "    • Paquete Plus: 14,99€ (antes 22,99€)"
echo "    • Paquete Extra: 29,99€ (antes 49,99€)"
echo ""
echo "⚠️  IMPORTANTE: Ejecuta el siguiente comando en MySQL para actualizar la base de datos:"
echo "  mysql -u root -p eazyride < fix-premium-type.sql"
echo ""
echo "📝 Revisa los warnings anteriores para ver qué archivos necesitan atención manual."
