#!/bin/bash
# Script de verificación final para EzyRide

echo "🔍 Verificando instalación de EzyRide..."
echo ""

cd "$(dirname "$0")"

# Contador de verificaciones
PASSED=0
FAILED=0
WARNINGS=0

echo "📁 Verificando estructura de archivos..."
echo ""

# Verificar archivos principales
[ -f "public_html/pages/vehicle/purchase-time.html" ] && echo "✓ purchase-time.html existe" && ((PASSED++)) || echo "✗ purchase-time.html NO existe" && ((FAILED++))
[ -f "public_html/pages/profile/premium.html" ] && echo "✓ premium.html existe" && ((PASSED++)) || echo "✗ premium.html NO existe" && ((FAILED++))
[ -f "public_html/pages/vehicle/localitzar-vehicle.html" ] && echo "✓ localitzar-vehicle.html existe" && ((PASSED++)) || echo "✗ localitzar-vehicle.html NO existe" && ((FAILED++))
[ -f "public_html/pages/profile/perfil.html" ] && echo "✓ perfil.html existe" && ((PASSED++)) || echo "✗ perfil.html NO existe" && ((FAILED++))
[ -f "public_html/pages/dashboard/gestio.html" ] && echo "✓ gestio.html existe" && ((PASSED++)) || echo "✗ gestio.html NO existe" && ((FAILED++))

echo ""
echo "🔌 Verificando archivos JavaScript..."
echo ""

[ -f "public_html/js/toast.js" ] && echo "✓ toast.js existe" && ((PASSED++)) || echo "✗ toast.js NO existe" && ((FAILED++))
[ -f "public_html/js/translations.js" ] && echo "✓ translations.js existe" && ((PASSED++)) || echo "✗ translations.js NO existe" && ((FAILED++))
[ -f "public_html/js/header-profile.js" ] && echo "✓ header-profile.js existe" && ((PASSED++)) || echo "✗ header-profile.js NO existe" && ((FAILED++))

echo ""
echo "🐘 Verificando archivos PHP..."
echo ""

[ -f "public_html/php/api/purchase-points.php" ] && echo "✓ purchase-points.php existe" && ((PASSED++)) || echo "✗ purchase-points.php NO existe" && ((FAILED++))
[ -f "public_html/php/api/subscribe-premium.php" ] && echo "✓ subscribe-premium.php existe" && ((PASSED++)) || echo "✗ subscribe-premium.php NO existe" && ((FAILED++))
[ -f "public_html/php/api/get-points.php" ] && echo "✓ get-points.php existe" && ((PASSED++)) || echo "✗ get-points.php NO existe" && ((FAILED++))

echo ""
echo "💰 Verificando precios actualizados..."
echo ""

grep -q "4.99" public_html/php/api/subscribe-premium.php 2>/dev/null && echo "✓ Precio premium mensual actualizado (4.99€)" && ((PASSED++)) || echo "✗ Precio premium mensual NO actualizado" && ((FAILED++))
grep -q "49.99" public_html/php/api/subscribe-premium.php 2>/dev/null && echo "✓ Precio premium anual actualizado (49.99€)" && ((PASSED++)) || echo "✗ Precio premium anual NO actualizado" && ((FAILED++))
grep -q "3.99" public_html/pages/vehicle/purchase-time.html 2>/dev/null && echo "✓ Precio paquete básico actualizado (3.99€)" && ((PASSED++)) || echo "✗ Precio paquete básico NO actualizado" && ((FAILED++))

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📊 RESUMEN"
echo "✓ Pasadas:    $PASSED"
echo "✗ Fallidas:   $FAILED"
echo ""

if [ $FAILED -eq 0 ]; then
    echo "🎉 ¡Todas las verificaciones pasaron!"
    echo ""
    echo "📝 Próximos pasos:"
    echo "  1. Ejecutar: mysql -u root -p eazyride < fix-premium-type.sql"
    echo "  2. Iniciar el servidor: docker-compose up -d"
    echo "  3. Abrir: http://localhost:8080"
    echo ""
else
    echo "⚠️  Hay problemas que necesitan atención"
    echo ""
fi
