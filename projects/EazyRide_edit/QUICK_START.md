# 🚀 Quick Start - EazyRide Tool

## Problema: No se conectan las bases de datos

### ✅ SOLUCIÓN:

El archivo `.env` está en el directorio raíz pero la herramienta lo buscaba en `python_gui/`.

**Ya está arreglado** - La herramienta ahora busca en múltiples ubicaciones.

---

## 📋 Verificar Configuración

### 1. Verificar que Docker esté corriendo

```bash
docker ps

# Deberías ver estos contenedores:
# - VC-mariadb
# - VC-mongodb
# - VC-web
```

### 2. Verificar archivo .env

```bash
cat .env

# Debe contener:
# DB_HOST=mariadb
# DB_USER=simsuser
# DB_PASS=simspass123
# DB_NAME=simsdb
# MONGO_INITDB_ROOT_USERNAME=simsadmin
# MONGO_INITDB_ROOT_PASSWORD=mongopass123
```

### 3. Ejecutar la herramienta

```bash
cd python_gui
python3 ezyridetool.py
```

---

## 🔍 Mensajes que Verás

Al iniciar, la herramienta mostrará:

```
✅ Cargando .env desde: /Users/ganso/Desktop/EazyRide_edit/.env

============================================================
🔧 CONFIGURACIÓN CARGADA:
============================================================
MariaDB:
  Host: localhost
  User: simsuser
  Pass: ************
  Database: simsdb
  Config válido: ✅

MongoDB:
  Host: localhost:27017
  User: simsadmin
  Pass: **************
  Database: simsdb
  URI válido: ✅

Web URL: http://localhost:8080
============================================================
```

---

## ❓ Problemas Comunes

### "MariaDB: Error ❌"

**Causa:** Docker no está corriendo o puerto 3306 ocupado

**Solución:**
```bash
# Iniciar Docker
docker-compose up -d

# Verificar que está corriendo
docker ps | grep mariadb

# Ver logs si hay error
docker logs VC-mariadb
```

### "MongoDB: Error ❌"

**Causa:** Docker no está corriendo o puerto 27017 ocupado

**Solución:**
```bash
# Verificar que está corriendo
docker ps | grep mongodb

# Ver logs si hay error
docker logs VC-mongodb

# Verificar puerto
lsof -i :27017
```

### "No configurado ⚠️"

**Causa:** Archivo .env no encontrado o credenciales vacías

**Solución:**
```bash
# Verificar que existe
ls -la .env

# Verificar contenido
cat .env | grep DB_USER
cat .env | grep MONGO_USER

# Si no existe, copiar desde ejemplo
cp .env.example .env
```

---

## 🔧 Comandos Útiles

### Reiniciar servicios Docker

```bash
docker-compose restart
```

### Ver logs de bases de datos

```bash
# MariaDB
docker logs -f VC-mariadb

# MongoDB
docker logs -f VC-mongodb
```

### Probar conexión manual

```bash
# MariaDB
mysql -h localhost -u simsuser -psimspass123 simsdb

# MongoDB
mongosh mongodb://simsadmin:mongopass123@localhost:27017/admin
```

### Limpiar y reiniciar desde cero

```bash
# Detener todo
docker-compose down

# Eliminar volúmenes (¡CUIDADO! Borra datos)
docker-compose down -v

# Iniciar todo de nuevo
docker-compose up -d
```

---

## 📞 Ayuda

Si los problemas persisten:

1. Verifica que Docker Desktop esté corriendo
2. Comprueba que los puertos 3306 y 27017 estén libres
3. Revisa los logs de Docker
4. Verifica que el archivo .env tenga las credenciales correctas

