#!/bin/bash

echo "🚀 Instalando Sistema Premium y EazyPoints..."
echo ""

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar que el contenedor de MariaDB está corriendo
if ! docker ps | grep -q "maria"; then
    echo -e "${RED}❌ Error: El contenedor de MariaDB no está corriendo${NC}"
    echo "Ejecuta: docker-compose up -d"
    exit 1
fi

# Obtener nombre del contenedor de MariaDB
CONTAINER_NAME=$(docker ps --filter "ancestor=mariadb:latest" --format "{{.Names}}")

if [ -z "$CONTAINER_NAME" ]; then
    echo -e "${RED}❌ Error: No se encontró el contenedor de MariaDB${NC}"
    exit 1
fi

echo -e "${YELLOW}📊 Actualizando base de datos...${NC}"

# Ejecutar el script SQL (usando variables de .env)
docker exec -i $CONTAINER_NAME mariadb -u root -prootpass123 simsdb < update-premium-system.sql

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Base de datos actualizada correctamente${NC}"
    echo ""
    
    # Verificar las tablas
    echo -e "${YELLOW}🔍 Verificando tablas creadas...${NC}"
    docker exec -i $CONTAINER_NAME mariadb -u root -prootpass123 simsdb -e "
        SELECT 
            TABLE_NAME, 
            TABLE_ROWS 
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = 'simsdb' 
        AND TABLE_NAME IN ('user_points', 'point_transactions', 'premium_subscriptions')
        ORDER BY TABLE_NAME;
    "
    
    echo ""
    echo -e "${YELLOW}🔍 Verificando columnas de premium en users...${NC}"
    docker exec -i $CONTAINER_NAME mariadb -u root -prootpass123 simsdb -e "
        SHOW COLUMNS FROM users WHERE Field IN ('is_premium', 'premium_expires_at');
    "
    
    echo ""
    echo -e "${GREEN}✅ ¡Sistema instalado correctamente!${NC}"
    echo ""
    echo -e "${YELLOW}📝 Próximos pasos:${NC}"
    echo "1. Accede a http://localhost:8080/pages/profile/perfil.html"
    echo "2. Verifica tu saldo de puntos"
    echo "3. Prueba comprar puntos en http://localhost:8080/pages/vehicle/purchase-time.html"
    echo "4. Activa premium en http://localhost:8080/pages/profile/premium.html"
    echo ""
    echo "📖 Lee GUIA_IMPLEMENTACION_PREMIUM.md para más información"
    
else
    echo -e "${RED}❌ Error al actualizar la base de datos${NC}"
    echo "Revisa el archivo update-premium-system.sql"
    exit 1
fi
