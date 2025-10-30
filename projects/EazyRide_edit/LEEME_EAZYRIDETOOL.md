# 🚗 EazyRide - Herramienta de Administración v2.0

Herramienta GUI completa en español para gestionar la aplicación EazyRide.

## 🚀 Inicio Rápido

```bash
./run_tool.sh
```

O manualmente:
```bash
python3 eazyridetool.py
```

## ✨ Características Principales

### 📊 Gestión de Base de Datos
- **MariaDB**: Crear/Eliminar BD, tablas, estadísticas
- **MongoDB**: Crear/Eliminar BD, colecciones, estadísticas
- **Backup/Restore**: Respaldo completo de bases de datos
- **Acciones Rápidas**: Inicializar o resetear todo

### 🧪 Pruebas del Servidor
- Pruebas de API (autenticación, perfil, vehículos)
- Verificación del servidor web
- Pruebas de conexión a BD
- Suite completa de pruebas

### 👥 Gestión de Usuarios
- Crear usuarios administradores
- Listar, buscar y eliminar usuarios
- Resetear contraseñas
- Estadísticas de usuarios

### 🚗 Gestión de Vehículos
- Listar, añadir y eliminar vehículos
- Ver estado (disponibles, en uso)
- Monitoreo de batería
- Generar vehículos de prueba

### 🛠️ Herramientas del Sistema
- Limpiar caché y archivos temporales
- Verificación de integridad
- Verificación de seguridad
- Verificación de salud del sistema

### 📝 Logs y Monitoreo
- Visor de logs (PHP, Apache, MySQL)
- Monitoreo de recursos
- Sesiones activas

## 📦 Instalación

### Requisitos
```bash
pip install mysql-connector-python pymongo requests
```

### Configuración Docker

La herramienta está configurada para conectarse a los contenedores Docker:

```yaml
# docker-compose.yml
services:
  mariadb:
    ports:
      - "3306:3306"
  
  mongodb:
    ports:
      - "27017:27017"
  
  web:
    ports:
      - "8080:80"
```

### Archivo .env

```env
# MariaDB
DB_HOST=localhost
DB_USER=root
DB_PASS=tu_contraseña
DB_NAME=eazyride

# MongoDB
MONGO_INITDB_ROOT_USERNAME=admin
MONGO_INITDB_ROOT_PASSWORD=tu_contraseña
MONGO_INITDB_DATABASE=eazyride
MONGO_HOST=localhost
MONGO_PORT=27017

# Servidor Web
WEB_URL=http://localhost:8080
```

## 🎯 Uso

### Primera Vez

1. **Verificar Estado**
   - Clic en "🔄 Actualizar Estado"
   - Verificar conexiones (MariaDB, MongoDB, Servidor Web)

2. **Inicializar Sistema**
   - Pestaña "📊 Base de Datos"
   - Clic en "🚀 Inicializar Todo"

3. **Crear Administrador**
   - Pestaña "👥 Usuarios"
   - Completar formulario
   - Clic en "✅ Crear Administrador"

4. **Ejecutar Pruebas**
   - Pestaña "🧪 Pruebas Servidor"
   - Clic en "🎯 Ejecutar Pruebas Completas"

## 📊 Estructura de Pestañas

### 📊 Base de Datos
**MariaDB:**
- ✅ Crear Base de Datos
- 🏗️ Crear Tablas
- 🗑️ Eliminar Base de Datos
- 📊 Mostrar Tablas
- 📈 Estadísticas

**MongoDB:**
- ✅ Crear Base de Datos
- 🏗️ Crear Colecciones
- 🗑️ Eliminar Base de Datos
- 📊 Mostrar Colecciones
- 📈 Estadísticas

**Acciones Rápidas:**
- 🚀 Inicializar Todo
- 🔄 Resetear Todo
- 💾 Hacer Backup
- 📥 Restaurar Backup

### 🧪 Pruebas Servidor
- 🧪 Probar Autenticación
- 🧪 Probar API Perfil
- 🧪 Probar API Vehículos
- 🎯 Ejecutar Todas las Pruebas
- 🌐 Verificar Servidor Web
- 📡 Verificar Conexión BD

### 👥 Usuarios
**Crear Administrador:**
- Formulario completo
- Validación de datos
- Hash de contraseña

**Gestión:**
- Listar todos
- Buscar usuario
- Eliminar usuario
- Resetear contraseña

**Estadísticas:**
- Total de usuarios
- Usuarios admin
- Nuevos usuarios

### 🚗 Vehículos
**Gestión:**
- Listar todos
- Añadir vehículo
- Eliminar vehículo
- Buscar vehículo

**Estado:**
- Vehículos disponibles
- Vehículos en uso
- Estado de batería

**Datos de Prueba:**
- Generar vehículos
- Limpiar datos

### 🛠️ Herramientas
- Limpiar caché
- Limpiar temporales
- Limpiar logs
- Verificar integridad
- Verificación de seguridad
- Verificación de salud
- Actualizar sistema

### 📝 Logs
- Visor de logs
- Errores PHP
- Logs Apache
- Logs MySQL
- Monitoreo de recursos
- Sesiones activas

## 🎨 Interfaz

```
┌─────────────────────────────────────────────────────────────┐
│  🚗 EazyRide Admin Tool                                     │
│  Suite Completa de Administración y Pruebas                │
├─────────────────────────────────────────────────────────────┤
│  Estado del Sistema:                                        │
│  ✅ MariaDB: Conectado  ✅ MongoDB: Conectado  ✅ Web     │
├─────────────────────────────────────────────────────────────┤
│  [📊 Base de Datos] [🧪 Pruebas] [👥 Usuarios]            │
│  [🚗 Vehículos] [🛠️ Herramientas] [📝 Logs]               │
├─────────────────────────────────────────────────────────────┤
│  Consola de Salida:                                         │
│  [23:45:12] ✅ Conexión exitosa a MariaDB                  │
│  [23:45:13] ✅ Conexión exitosa a MongoDB                  │
│  [23:45:14] 🚀 Sistema listo                               │
└─────────────────────────────────────────────────────────────┘
```

## 🔄 Funciones Implementadas

### ✅ Completamente Funcionales
- Conexión a MariaDB y MongoDB (Docker)
- Creación y eliminación de bases de datos
- Creación de tablas y colecciones
- Estadísticas en tiempo real
- Gestión completa de usuarios
- Gestión de vehículos
- Backup y restore completo
- Generación de datos de prueba
- Verificación de sistemas
- Exportar reportes HTML

### 🔜 Próximamente
- Monitoreo en tiempo real
- Gráficos de estadísticas
- Logs visuales completos
- Optimización de imágenes
- Sistema de notificaciones

## 💾 Backup y Restore

### Hacer Backup
1. Clic en "💾 Hacer Backup"
2. Seleccionar carpeta destino
3. Esperar completación
4. Se crea carpeta con timestamp

### Restaurar Backup
1. Clic en "📥 Restaurar Backup"
2. Seleccionar carpeta de backup
3. Confirmar restauración
4. Esperar completación

## 🔐 Seguridad

- Contraseñas hasheadas con SHA-256
- Credenciales en archivo .env
- Nunca exponer credenciales
- Backup seguro de datos

## 🐛 Solución de Problemas

### Error: No se puede conectar a MariaDB
```bash
# Verificar Docker
docker ps | grep mariadb

# Verificar puerto
lsof -i :3306

# Verificar credenciales
cat .env | grep DB_
```

### Error: No se puede conectar a MongoDB
```bash
# Verificar Docker
docker ps | grep mongodb

# Verificar puerto
lsof -i :27017

# Verificar credenciales
cat .env | grep MONGO_
```

### Error: Servidor web no responde
```bash
# Verificar Docker
docker ps | grep web

# Verificar puerto
curl http://localhost:8080
```

## 📝 Logs

La consola muestra:
- **✅** Operaciones exitosas (verde)
- **❌** Errores (rojo)
- **⚠️** Advertencias (naranja)
- **ℹ️** Información (azul)

Todas las operaciones incluyen timestamp automático.

## 🎯 Características Destacadas

### Backup Completo
- Backup de MariaDB con mysqldump
- Backup de MongoDB con mongodump
- Organizado por timestamp
- Fácil restauración

### Generación de Datos de Prueba
- Vehículos aleatorios
- Ubicaciones GPS reales (Barcelona)
- Marcas y modelos reales
- Niveles de batería realistas

### Sistema de Logs
- Timestamps automáticos
- Color coding
- Exportar a archivo
- Exportar reporte HTML

## 🔄 Actualización desde v1.0

### Mejoras:
- ✅ Todo en español
- ✅ Configuración Docker correcta
- ✅ Backup/Restore funcional
- ✅ Generación de datos de prueba
- ✅ Búsqueda de usuarios
- ✅ Búsqueda de vehículos
- ✅ Estadísticas de batería
- ✅ Verificaciones de sistema
- ✅ Interfaz mejorada
- ✅ Mejor manejo de errores

## 📖 Documentación

- `LEEME_EAZYRIDETOOL.md` - Este archivo
- `INICIO_RAPIDO.md` - Guía rápida
- `run_tool.sh` - Script de inicio

## 🤝 Soporte

Para problemas o sugerencias:
- Revisar la consola de salida
- Verificar archivo .env
- Comprobar conexiones Docker
- Exportar reporte para análisis

## 📜 Licencia

Parte del proyecto EazyRide. Todos los derechos reservados.

---

🚗 **EazyRide Admin Tool v2.0** - ¡Gestión completa en español!
