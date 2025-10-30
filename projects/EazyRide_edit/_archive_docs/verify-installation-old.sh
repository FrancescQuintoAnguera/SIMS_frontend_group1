#!/bin/bash

# Script de Verificación Post-Instalación - EazyRide
# Verifica que todas las reparaciones se aplicaron correctamente

echo "=========================================="
echo "  Verificación de Reparaciones EazyRide"
echo "=========================================="
echo ""

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

PASSED=0
FAILED=0

check_pass() {
    echo -e "${GREEN}✓${NC} $1"
    PASSED=$((PASSED + 1))
}

check_fail() {
    echo -e "${RED}✗${NC} $1"
    FAILED=$((FAILED + 1))
}

check_warn() {
    echo -e "${YELLOW}⚠${NC} $1"
}

echo -e "${BLUE}[1/5] Verificando archivos PHP...${NC}"
echo ""

# Verificar archivos PHP nuevos
if [ -f "public_html/php/api/claim-daily-bonus.php" ]; then
    check_pass "claim-daily-bonus.php existe"
else
    check_fail "claim-daily-bonus.php NO existe"
fi

if [ -f "public_html/php/api/check-daily-bonus.php" ]; then
    check_pass "check-daily-bonus.php existe"
else
    check_fail "check-daily-bonus.php NO existe"
fi

# Verificar archivos PHP actualizados
for file in "header.php" "get-points.php" "purchase-points.php" "subscribe-premium.php"; do
    path="public_html/php"
    if [[ "$file" == "header.php" ]]; then
        path="public_html/php/components"
    else
        path="public_html/php/api"
    fi
    
    if [ -f "$path/$file" ]; then
        # Verificar que tiene las mejoras
        if [[ "$file" == "get-points.php" ]]; then
            if grep -q "premium_valid" "$path/$file"; then
                check_pass "$file actualizado con cálculo Premium"
            else
                check_warn "$file existe pero podría no tener las actualizaciones"
            fi
        elif [[ "$file" == "subscribe-premium.php" ]]; then
            if grep -q "bonus_points = 200" "$path/$file"; then
                check_pass "$file actualizado con bonus de activación"
            else
                check_warn "$file existe pero podría no tener las actualizaciones"
            fi
        elif [[ "$file" == "header.php" ]]; then
            if grep -q "profileDropdown" "$path/$file"; then
                check_pass "$file actualizado con menú dropdown"
            else
                check_warn "$file existe pero podría no tener las actualizaciones"
            fi
        else
            check_pass "$file existe"
        fi
    else
        check_fail "$file NO existe"
    fi
done

echo ""
echo -e "${BLUE}[2/5] Verificando archivos HTML...${NC}"
echo ""

# Verificar HTML
if [ -f "public_html/pages/profile/perfil.html" ]; then
    if grep -q "dailyBonusSection" "public_html/pages/profile/perfil.html"; then
        check_pass "perfil.html actualizado con sección de bonus diario"
    else
        check_warn "perfil.html existe pero podría no tener las actualizaciones"
    fi
else
    check_fail "perfil.html NO existe"
fi

if [ -f "public_html/pages/vehicle/purchase-time.html" ]; then
    if grep -q "applyPremiumDiscounts" "public_html/pages/vehicle/purchase-time.html"; then
        check_pass "purchase-time.html actualizado con descuentos Premium"
    else
        check_warn "purchase-time.html existe pero podría no tener las actualizaciones"
    fi
else
    check_fail "purchase-time.html NO existe"
fi

echo ""
echo -e "${BLUE}[3/5] Verificando scripts SQL...${NC}"
echo ""

if [ -f "update-premium-system.sql" ]; then
    if grep -q "last_daily_bonus" "update-premium-system.sql"; then
        check_pass "update-premium-system.sql actualizado con columna bonus diario"
    else
        check_warn "update-premium-system.sql existe pero falta columna last_daily_bonus"
    fi
else
    check_fail "update-premium-system.sql NO existe"
fi

if [ -f "update-daily-bonus-column.sql" ]; then
    check_pass "update-daily-bonus-column.sql existe"
else
    check_fail "update-daily-bonus-column.sql NO existe"
fi

echo ""
echo -e "${BLUE}[4/5] Verificando base de datos...${NC}"
echo ""

# Intentar conectar a BD
DB_CHECK=0

if command -v docker &> /dev/null; then
    if docker ps | grep -q mariadb; then
        echo "Usando Docker MariaDB..."
        
        # Verificar columnas
        if docker exec mariadb mysql -uroot -e "USE voltiacar; SHOW COLUMNS FROM users LIKE 'is_premium';" 2>/dev/null | grep -q "is_premium"; then
            check_pass "Columna users.is_premium existe"
        else
            check_fail "Columna users.is_premium NO existe"
        fi
        
        if docker exec mariadb mysql -uroot -e "USE voltiacar; SHOW COLUMNS FROM users LIKE 'last_daily_bonus';" 2>/dev/null | grep -q "last_daily_bonus"; then
            check_pass "Columna users.last_daily_bonus existe"
        else
            check_fail "Columna users.last_daily_bonus NO existe (ejecuta update-daily-bonus-column.sql)"
        fi
        
        # Verificar tablas
        if docker exec mariadb mysql -uroot -e "USE voltiacar; SHOW TABLES LIKE 'user_points';" 2>/dev/null | grep -q "user_points"; then
            check_pass "Tabla user_points existe"
        else
            check_fail "Tabla user_points NO existe (ejecuta update-premium-system.sql)"
        fi
        
        if docker exec mariadb mysql -uroot -e "USE voltiacar; SHOW TABLES LIKE 'point_transactions';" 2>/dev/null | grep -q "point_transactions"; then
            check_pass "Tabla point_transactions existe"
        else
            check_fail "Tabla point_transactions NO existe (ejecuta update-premium-system.sql)"
        fi
        
        if docker exec mariadb mysql -uroot -e "USE voltiacar; SHOW TABLES LIKE 'premium_subscriptions';" 2>/dev/null | grep -q "premium_subscriptions"; then
            check_pass "Tabla premium_subscriptions existe"
        else
            check_fail "Tabla premium_subscriptions NO existe (ejecuta update-premium-system.sql)"
        fi
        
        DB_CHECK=1
    fi
fi

if [ $DB_CHECK -eq 0 ]; then
    check_warn "No se pudo verificar la base de datos automáticamente"
    echo "         Verifica manualmente con: mysql -uroot -p voltiacar"
fi

echo ""
echo -e "${BLUE}[5/5] Verificando documentación...${NC}"
echo ""

if [ -f "REPARACIONES_COMPLETAS.md" ]; then
    check_pass "Documentación técnica completa"
else
    check_fail "REPARACIONES_COMPLETAS.md NO existe"
fi

if [ -f "README_REPARACIONES.md" ]; then
    check_pass "Guía de usuario"
else
    check_fail "README_REPARACIONES.md NO existe"
fi

if [ -f "RESUMEN_REPARACIONES.md" ]; then
    check_pass "Resumen ejecutivo"
else
    check_fail "RESUMEN_REPARACIONES.md NO existe"
fi

echo ""
echo "=========================================="
echo "  Resumen de Verificación"
echo "=========================================="
echo ""
echo -e "${GREEN}Pasados:${NC} $PASSED"
echo -e "${RED}Fallados:${NC} $FAILED"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ ¡Todas las verificaciones pasaron!${NC}"
    echo ""
    echo "El sistema está correctamente instalado y listo para usar."
    echo ""
    echo "Próximos pasos:"
    echo "1. Reinicia los servicios (docker-compose restart)"
    echo "2. Limpia el caché del navegador"
    echo "3. Prueba las funcionalidades:"
    echo "   - Accede al perfil de usuario"
    echo "   - Compra puntos"
    echo "   - Activa Premium"
    echo "   - Reclama el bonus diario"
    echo ""
    exit 0
else
    echo -e "${RED}✗ Hay $FAILED verificaciones fallidas${NC}"
    echo ""
    echo "Acciones recomendadas:"
    
    if [ $FAILED -gt 5 ]; then
        echo "1. Ejecuta: ./install-reparaciones.sh"
        echo "2. Selecciona 'Instalación completa'"
    else
        echo "1. Revisa los errores arriba"
        echo "2. Ejecuta los scripts SQL faltantes"
        echo "3. Verifica que todos los archivos están presentes"
    fi
    
    echo ""
    echo "Para más ayuda, consulta README_REPARACIONES.md"
    echo ""
    exit 1
fi
