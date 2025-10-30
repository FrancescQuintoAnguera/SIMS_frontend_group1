#!/bin/bash

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║         🧪 TEST RÁPIDO - EAZYRIDE                           ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

echo "📋 Verificando archivos modificados..."
echo ""

files=(
    "public_html/pages/vehicle/purchase-time.html"
    "public_html/pages/profile/premium.html"
    "public_html/pages/vehicle/localitzar-vehicle.html"
    "public_html/php/api/subscribe-premium.php"
    "public_html/js/toast.js"
)

all_ok=true

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "  ✅ $file"
    else
        echo "  ❌ $file - NO ENCONTRADO"
        all_ok=false
    fi
done

echo ""
echo "📊 Verificando precios en archivos..."
echo ""

# Verificar precios en purchase-time.html
if grep -q "5,99€" public_html/pages/vehicle/purchase-time.html && \
   grep -q "12,99€" public_html/pages/vehicle/purchase-time.html && \
   grep -q "22,99€" public_html/pages/vehicle/purchase-time.html && \
   grep -q "49,99€" public_html/pages/vehicle/purchase-time.html; then
    echo "  ✅ Precios de puntos actualizados en purchase-time.html"
else
    echo "  ❌ Precios de puntos NO actualizados en purchase-time.html"
    all_ok=false
fi

# Verificar precios en premium.html
if grep -q "7,99€" public_html/pages/profile/premium.html && \
   grep -q "75,99€" public_html/pages/profile/premium.html; then
    echo "  ✅ Precios premium actualizados en premium.html"
else
    echo "  ❌ Precios premium NO actualizados en premium.html"
    all_ok=false
fi

# Verificar precios en PHP
if grep -q "7.99" public_html/php/api/subscribe-premium.php && \
   grep -q "75.99" public_html/php/api/subscribe-premium.php; then
    echo "  ✅ Precios premium actualizados en subscribe-premium.php"
else
    echo "  ❌ Precios premium NO actualizados en subscribe-premium.php"
    all_ok=false
fi

echo ""
echo "🔍 Verificando funcionalidades..."
echo ""

# Verificar dropdowns en purchase-time.html
if grep -q "profileDropdown" public_html/pages/vehicle/purchase-time.html && \
   grep -q "profileUsername" public_html/pages/vehicle/purchase-time.html; then
    echo "  ✅ Dropdown de usuario en purchase-time.html"
else
    echo "  ❌ Dropdown de usuario NO encontrado en purchase-time.html"
    all_ok=false
fi

# Verificar dropdowns en premium.html
if grep -q "profileDropdown" public_html/pages/profile/premium.html && \
   grep -q "profileUsername" public_html/pages/profile/premium.html; then
    echo "  ✅ Dropdown de usuario en premium.html"
else
    echo "  ❌ Dropdown de usuario NO encontrado en premium.html"
    all_ok=false
fi

# Verificar dropdowns en localitzar-vehicle.html
if grep -q "profileDropdown" public_html/pages/vehicle/localitzar-vehicle.html && \
   grep -q "profileUsername" public_html/pages/vehicle/localitzar-vehicle.html; then
    echo "  ✅ Dropdown de usuario en localitzar-vehicle.html"
else
    echo "  ❌ Dropdown de usuario NO encontrado en localitzar-vehicle.html"
    all_ok=false
fi

# Verificar toast.js
if grep -q "window.toast" public_html/js/toast.js; then
    echo "  ✅ Toast.js corregido"
else
    echo "  ❌ Toast.js NO corregido"
    all_ok=false
fi

echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
if [ "$all_ok" = true ]; then
    echo "║            ✅ TODAS LAS VERIFICACIONES PASADAS              ║"
    echo "║                                                              ║"
    echo "║  🚀 El proyecto está listo para usar!                       ║"
    echo "║                                                              ║"
    echo "║  Abre en tu navegador:                                       ║"
    echo "║  → http://localhost:8080/pages/vehicle/purchase-time.html   ║"
    echo "║  → http://localhost:8080/pages/profile/premium.html         ║"
    echo "║  → http://localhost:8080/pages/vehicle/localitzar-vehicle.html ║"
else
    echo "║            ⚠️  ALGUNAS VERIFICACIONES FALLARON              ║"
    echo "║                                                              ║"
    echo "║  Revisa los archivos marcados con ❌                        ║"
fi
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""
