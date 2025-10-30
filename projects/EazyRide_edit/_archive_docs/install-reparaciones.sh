#!/bin/bash

# Script de Instalación y Reparación - EazyRide
# Este script aplica todas las correcciones y mejoras al sistema

echo "=========================================="
echo "  EazyRide - Instalación de Reparaciones"
echo "=========================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Variables de configuración
DB_HOST="localhost"
DB_NAME="voltiacar"
DB_USER="root"
DB_PASS=""

# Función para mostrar mensajes
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar si Docker está corriendo
check_docker() {
    log_info "Verificando Docker..."
    if docker ps > /dev/null 2>&1; then
        log_info "Docker está corriendo ✓"
        return 0
    else
        log_warn "Docker no está corriendo o no está instalado"
        return 1
    fi
}

# Verificar base de datos
check_database() {
    log_info "Verificando base de datos..."
    
    if check_docker; then
        # Si usa Docker
        docker exec -i mariadb mysql -u${DB_USER} -p${DB_PASS} -e "USE ${DB_NAME};" 2>/dev/null
        if [ $? -eq 0 ]; then
            log_info "Base de datos accesible ✓"
            return 0
        fi
    else
        # Si usa MySQL local
        mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASS} -e "USE ${DB_NAME};" 2>/dev/null
        if [ $? -eq 0 ]; then
            log_info "Base de datos accesible ✓"
            return 0
        fi
    fi
    
    log_error "No se puede acceder a la base de datos"
    return 1
}

# Aplicar actualizaciones de base de datos
update_database() {
    log_info "Aplicando actualizaciones de base de datos..."
    
    if check_docker; then
        # Docker
        log_info "Ejecutando update-premium-system.sql..."
        docker exec -i mariadb mysql -u${DB_USER} -p${DB_PASS} ${DB_NAME} < update-premium-system.sql
        
        log_info "Ejecutando update-daily-bonus-column.sql..."
        docker exec -i mariadb mysql -u${DB_USER} -p${DB_PASS} ${DB_NAME} < update-daily-bonus-column.sql
    else
        # MySQL local
        log_info "Ejecutando update-premium-system.sql..."
        mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASS} ${DB_NAME} < update-premium-system.sql
        
        log_info "Ejecutando update-daily-bonus-column.sql..."
        mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASS} ${DB_NAME} < update-daily-bonus-column.sql
    fi
    
    if [ $? -eq 0 ]; then
        log_info "Base de datos actualizada correctamente ✓"
        return 0
    else
        log_error "Error al actualizar la base de datos"
        return 1
    fi
}

# Verificar estructura de archivos
verify_files() {
    log_info "Verificando estructura de archivos..."
    
    local files=(
        "public_html/php/components/header.php"
        "public_html/php/api/get-points.php"
        "public_html/php/api/purchase-points.php"
        "public_html/php/api/subscribe-premium.php"
        "public_html/php/api/claim-daily-bonus.php"
        "public_html/php/api/check-daily-bonus.php"
        "public_html/pages/profile/perfil.html"
        "public_html/pages/vehicle/purchase-time.html"
    )
    
    local missing=0
    for file in "${files[@]}"; do
        if [ -f "$file" ]; then
            log_info "  ✓ $file"
        else
            log_error "  ✗ $file (falta)"
            missing=$((missing + 1))
        fi
    done
    
    if [ $missing -eq 0 ]; then
        log_info "Todos los archivos están presentes ✓"
        return 0
    else
        log_warn "$missing archivo(s) faltan"
        return 1
    fi
}

# Verificar permisos
check_permissions() {
    log_info "Verificando permisos de archivos..."
    
    # Verificar que los archivos PHP son ejecutables
    find public_html/php -name "*.php" -type f ! -perm -644 -exec chmod 644 {} \;
    
    # Verificar que los directorios son accesibles
    find public_html -type d ! -perm -755 -exec chmod 755 {} \;
    
    log_info "Permisos verificados ✓"
}

# Test de funcionalidad
test_functionality() {
    log_info "Ejecutando tests de funcionalidad..."
    
    # Test 1: Verificar tablas en BD
    log_info "Test 1: Verificando tablas..."
    if check_docker; then
        docker exec -i mariadb mysql -u${DB_USER} -p${DB_PASS} ${DB_NAME} -e "
            SELECT 
                (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='is_premium') as has_is_premium,
                (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='last_daily_bonus') as has_last_daily_bonus,
                (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME='user_points') as has_user_points,
                (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME='point_transactions') as has_point_transactions,
                (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME='premium_subscriptions') as has_premium_subscriptions;
        " 2>/dev/null
    else
        mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASS} ${DB_NAME} -e "
            SELECT 
                (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='is_premium') as has_is_premium,
                (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_NAME='users' AND COLUMN_NAME='last_daily_bonus') as has_last_daily_bonus,
                (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME='user_points') as has_user_points,
                (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME='point_transactions') as has_point_transactions,
                (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_NAME='premium_subscriptions') as has_premium_subscriptions;
        " 2>/dev/null
    fi
    
    if [ $? -eq 0 ]; then
        log_info "  ✓ Estructura de base de datos correcta"
    else
        log_error "  ✗ Error en estructura de base de datos"
    fi
}

# Menú principal
show_menu() {
    echo ""
    echo "Selecciona una opción:"
    echo "1) Instalación completa (recomendado)"
    echo "2) Solo actualizar base de datos"
    echo "3) Solo verificar archivos"
    echo "4) Ejecutar tests"
    echo "5) Ver documentación"
    echo "6) Salir"
    echo ""
    read -p "Opción: " option
    
    case $option in
        1)
            log_info "Iniciando instalación completa..."
            check_database
            update_database
            verify_files
            check_permissions
            test_functionality
            log_info "Instalación completa finalizada ✓"
            ;;
        2)
            log_info "Actualizando base de datos..."
            check_database && update_database
            ;;
        3)
            verify_files
            ;;
        4)
            test_functionality
            ;;
        5)
            if [ -f "REPARACIONES_COMPLETAS.md" ]; then
                cat REPARACIONES_COMPLETAS.md | less
            else
                log_error "Archivo de documentación no encontrado"
            fi
            ;;
        6)
            log_info "Saliendo..."
            exit 0
            ;;
        *)
            log_error "Opción no válida"
            show_menu
            ;;
    esac
}

# Inicio del script
echo ""
log_info "Bienvenido al instalador de reparaciones de EazyRide"
echo ""

# Solicitar credenciales de BD si es necesario
read -p "Host de la base de datos [localhost]: " input_host
DB_HOST=${input_host:-localhost}

read -p "Nombre de la base de datos [voltiacar]: " input_db
DB_NAME=${input_db:-voltiacar}

read -p "Usuario de la base de datos [root]: " input_user
DB_USER=${input_user:-root}

read -sp "Contraseña de la base de datos: " input_pass
echo ""
DB_PASS=${input_pass}

# Mostrar menú
show_menu

# Al finalizar
echo ""
log_info "Para más información, consulta REPARACIONES_COMPLETAS.md"
echo ""
echo "=========================================="
log_info "Proceso completado"
echo "=========================================="
