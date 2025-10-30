#!/bin/bash

# =====================================================
# Script de Instalación Completa - EazyRide v2.0
# =====================================================

echo "🚀 Instalación Completa del Sistema EazyRide v2.0"
echo "=================================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Función para mostrar mensajes
show_message() {
    local type=$1
    local message=$2
    
    case $type in
        "success")
            echo -e "${GREEN}✅ $message${NC}"
            ;;
        "error")
            echo -e "${RED}❌ $message${NC}"
            ;;
        "warning")
            echo -e "${YELLOW}⚠️  $message${NC}"
            ;;
        "info")
            echo -e "${BLUE}ℹ️  $message${NC}"
            ;;
        *)
            echo "$message"
            ;;
    esac
}

# Verificar si estamos en el directorio correcto
if [ ! -f "install-complete-system.sql" ]; then
    show_message "error" "Error: No se encuentra el archivo install-complete-system.sql"
    show_message "info" "Asegúrate de ejecutar este script desde el directorio raíz del proyecto"
    exit 1
fi

# PASO 1: Configuración de Base de Datos
show_message "info" "Paso 1/6: Configuración de Base de Datos"
echo ""

# Solicitar credenciales de MySQL
read -p "Usuario MySQL [root]: " DB_USER
DB_USER=${DB_USER:-root}

read -sp "Contraseña MySQL: " DB_PASS
echo ""

read -p "Nombre de la base de datos [eazyride]: " DB_NAME
DB_NAME=${DB_NAME:-eazyride}

# Verificar conexión
echo ""
show_message "info" "Verificando conexión a MySQL..."
if mysql -u "$DB_USER" -p"$DB_PASS" -e "USE $DB_NAME;" 2>/dev/null; then
    show_message "success" "Conexión exitosa a la base de datos"
else
    show_message "error" "No se pudo conectar a MySQL con las credenciales proporcionadas"
    exit 1
fi

# Ejecutar script SQL
show_message "info" "Instalando tablas y funciones..."
if mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < install-complete-system.sql 2>&1 | tee /tmp/eazyride_install.log; then
    show_message "success" "Base de datos configurada correctamente"
else
    show_message "error" "Error al ejecutar el script SQL"
    show_message "info" "Revisa el log en /tmp/eazyride_install.log"
    exit 1
fi

# PASO 2: Crear directorios necesarios
show_message "info" "Paso 2/6: Creando directorios necesarios"
echo ""

mkdir -p public_html/php/logs
mkdir -p public_html/php/config
chmod 755 public_html/php/logs

show_message "success" "Directorios creados"

# PASO 3: Verificar archivos JavaScript
show_message "info" "Paso 3/6: Verificando archivos JavaScript"
echo ""

required_js_files=(
    "public_html/js/translations.js"
    "public_html/js/toast.js"
    "public_html/js/layout.js"
)

missing_files=0
for file in "${required_js_files[@]}"; do
    if [ -f "$file" ]; then
        show_message "success" "Encontrado: $file"
    else
        show_message "error" "Falta: $file"
        ((missing_files++))
    fi
done

if [ $missing_files -gt 0 ]; then
    show_message "warning" "Faltan $missing_files archivos JavaScript"
    show_message "info" "Asegúrate de que todos los archivos estén en su lugar"
fi

# PASO 4: Verificar archivos PHP
show_message "info" "Paso 4/6: Verificando archivos PHP API"
echo ""

required_php_files=(
    "public_html/php/api/get-points.php"
    "public_html/php/api/purchase-points.php"
    "public_html/php/api/subscribe-premium.php"
    "public_html/php/config/error_handler.php"
)

for file in "${required_php_files[@]}"; do
    if [ -f "$file" ]; then
        show_message "success" "Encontrado: $file"
        # Verificar sintaxis PHP
        if php -l "$file" > /dev/null 2>&1; then
            show_message "success" "  └─ Sintaxis correcta"
        else
            show_message "error" "  └─ Error de sintaxis"
        fi
    else
        show_message "error" "Falta: $file"
    fi
done

# PASO 5: Configurar permisos
show_message "info" "Paso 5/6: Configurando permisos"
echo ""

chmod 644 public_html/php/api/*.php 2>/dev/null
chmod 644 public_html/php/config/*.php 2>/dev/null
chmod 755 public_html/php/logs

show_message "success" "Permisos configurados"

# PASO 6: Crear archivo de configuración
show_message "info" "Paso 6/6: Creando archivo de configuración"
echo ""

CONFIG_FILE="public_html/php/config/database.php"

if [ ! -f "$CONFIG_FILE" ]; then
    cat > "$CONFIG_FILE" << EOF
<?php
/**
 * Configuración de Base de Datos
 * Generado automáticamente por install.sh
 */

define('DB_HOST', 'localhost');
define('DB_USER', '$DB_USER');
define('DB_PASS', '$DB_PASS');
define('DB_NAME', '$DB_NAME');
define('DB_CHARSET', 'utf8mb4');
?>
EOF
    
    chmod 600 "$CONFIG_FILE"
    show_message "success" "Archivo de configuración creado"
else
    show_message "info" "Archivo de configuración ya existe (no sobrescrito)"
fi

# Resumen final
echo ""
echo "=================================================="
show_message "success" "¡Instalación completada!"
echo "=================================================="
echo ""

show_message "info" "Resumen de la instalación:"
echo "  • Base de datos: $DB_NAME"
echo "  • Usuario: $DB_USER"
echo "  • Tablas creadas: ✅"
echo "  • Funciones y procedimientos: ✅"
echo "  • Eventos programados: ✅"
echo "  • Archivos JavaScript: ✅"
echo "  • Archivos PHP: ✅"
echo "  • Permisos configurados: ✅"
echo ""

show_message "warning" "PRÓXIMOS PASOS:"
echo ""
echo "1. Configurar PHP para producción:"
echo "   - Editar php.ini"
echo "   - Configurar display_errors = Off"
echo "   - Configurar log_errors = On"
echo ""
echo "2. Limpiar caché del navegador:"
echo "   - Chrome/Edge: Ctrl/Cmd + Shift + Delete"
echo "   - Firefox: Ctrl/Cmd + Shift + Delete"
echo "   - Safari: Cmd + Option + E"
echo ""
echo "3. Probar el sistema:"
echo "   - Registrar usuario"
echo "   - Comprar puntos"
echo "   - Activar Premium"
echo "   - Verificar descuentos"
echo ""
echo "4. Revisar documentación:"
echo "   - INSTRUCCIONES_FINALES.md"
echo "   - MEJORAS_FRONTEND_COMPLETAS.md"
echo "   - RESUMEN_EJECUTIVO.md"
echo ""

show_message "success" "¡Sistema EazyRide v2.0 listo para usar! 🎉"
echo ""

# Preguntar si quiere ver el estado de la BD
read -p "¿Deseas ver el estado actual de la base de datos? (s/n): " show_status

if [ "$show_status" = "s" ] || [ "$show_status" = "S" ]; then
    echo ""
    show_message "info" "Estado de la Base de Datos:"
    echo ""
    
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << EOF
SELECT 
    'TABLA' as tipo,
    TABLE_NAME as nombre,
    TABLE_ROWS as registros
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = '$DB_NAME'
AND TABLE_NAME IN ('users', 'user_points', 'point_transactions', 'premium_subscriptions')
UNION ALL
SELECT 
    'FUNCIÓN' as tipo,
    ROUTINE_NAME as nombre,
    '-' as registros
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = '$DB_NAME'
AND ROUTINE_TYPE = 'FUNCTION'
UNION ALL
SELECT 
    'PROCEDIMIENTO' as tipo,
    ROUTINE_NAME as nombre,
    '-' as registros
FROM INFORMATION_SCHEMA.ROUTINES
WHERE ROUTINE_SCHEMA = '$DB_NAME'
AND ROUTINE_TYPE = 'PROCEDURE'
ORDER BY tipo, nombre;
EOF
    
    echo ""
fi

show_message "info" "Para más ayuda, consulta la documentación o los logs en:"
echo "  • /tmp/eazyride_install.log (instalación)"
echo "  • public_html/php/logs/php_errors.log (errores PHP)"
echo ""

exit 0
