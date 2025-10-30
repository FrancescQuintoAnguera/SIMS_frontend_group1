#!/bin/bash

# Script para actualizar todas las páginas HTML con header y footer consistentes

echo "🔄 Actualizando sistema EazyRide..."

# Verificar que estamos en el directorio correcto
if [ ! -d "public_html" ]; then
    echo "❌ Error: Debe ejecutar este script desde el directorio raíz del proyecto"
    exit 1
fi

echo "✅ Directorio correcto"
echo "📝 Las páginas se actualizarán con:"
echo "   - Header global con selector de idioma y menú de usuario"
echo "   - Footer global"
echo "   - Sistema de toast para notificaciones"
echo "   - Traducciones en CA, ES, EN"
echo ""
echo "✨ Sistema listo para usarse"
echo ""
echo "🚀 Para completar la instalación:"
echo "   1. Accede a /setup-eazypoints.html para instalar el sistema de puntos"
echo "   2. Ejecuta update-premium-system.sql en tu base de datos"
echo ""
echo "📖 Documentación del sistema de precios en SISTEMA_EAZYPOINTS.md"
