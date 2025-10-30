#!/bin/bash

echo "════════════════════════════════════════════"
echo "   🚗 EazyRide - Herramienta de Administración"
echo "════════════════════════════════════════════"
echo ""

# Verificar Python
if ! command -v python3 &> /dev/null; then
    echo "❌ Python 3 no encontrado!"
    echo "   Por favor instala Python 3"
    exit 1
fi

echo "✅ Python 3 encontrado"

# Verificar dependencias
echo "Verificando dependencias..."

python3 -c "import mysql.connector" 2>/dev/null || {
    echo "�� Instalando mysql-connector-python..."
    pip3 install mysql-connector-python
}

python3 -c "import pymongo" 2>/dev/null || {
    echo "📦 Instalando pymongo..."
    pip3 install pymongo
}

python3 -c "import requests" 2>/dev/null || {
    echo "📦 Instalando requests..."
    pip3 install requests
}

python3 -c "import tkinter" 2>/dev/null || {
    echo "❌ tkinter no encontrado!"
    echo "   En Ubuntu/Debian: sudo apt-get install python3-tk"
    echo "   En macOS: Debería estar incluido con Python"
    exit 1
}

echo "✅ Todas las dependencias instaladas"
echo ""
echo "🚀 Iniciando EazyRide Tool..."
echo ""

python3 eazyridetool.py

