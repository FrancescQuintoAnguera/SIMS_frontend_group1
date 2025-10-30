# 🚗 Sistema de Reclamación y Liberación de Vehículos

## 📋 Flujo Completo del Usuario

### 1️⃣ Usuario Sin Vehículo (Localizar)

**Página:** `pages/vehicle/localitzar-vehicle.html`

- Usuario ve lista de vehículos disponibles
- Usuario ve marcadores en el mapa
- Solo muestra vehículos con `status='available'`

**Tecnologías:**
- Mapa interactivo con Leaflet.js
- Lista responsive con Tailwind CSS
- JavaScript modular (localitzar-vehicle.js, vehicles.js)

---

### 2️⃣ Reclamar Vehículo

**Acción:** Click en botón "Reclamar" (lista o mapa)

**Modal de Confirmación:**
- Título: "Confirmar Reclamació"
- Mensaje: "Es cobraran **0.50€** per desbloquejar el vehicle"
- Botones: "Cancel·lar" | "Acceptar i Reclamar"

**Proceso Backend (cuando se confirma):**

```
1. Frontend → POST /php/api/vehicles.php
   Body: { "action": "claim", "vehicle_id": X }

2. Backend valida:
   ✓ Usuario autenticado
   ✓ Vehículo existe y está disponible
   ✓ Usuario NO tiene otro vehículo activo

3. Backend ejecuta:
   ✓ UPDATE vehicles SET status='in_use' WHERE id=X
   ✓ INSERT INTO bookings (user_id, vehicle_id, status='active', total_cost=0.50)
   ✓ Guarda vehicle_id en $_SESSION['current_vehicle_id']

4. Backend responde:
   { 
     "success": true, 
     "vehicle": {...},
     "booking": {...}
   }

5. Frontend:
   ✓ Guarda en localStorage('currentVehicle')
   ✓ Redirige → administrar-vehicle.html
```

**Archivos involucrados:**
- `js/vehicle-claim-modal.js` - Modal de confirmación
- `js/vehicles.js` - Método `claimVehicle()`
- `php/api/vehicles.php` - Endpoint `action=claim`
- `php/controllers/VehicleController.php` - Método `claimVehicle()`
- `php/models/Vehicle.php` - Método `updateStatus()`
- `php/models/Booking.php` - Método `createBooking()`

---

### 3️⃣ Controlar Vehículo

**Página:** `pages/vehicle/administrar-vehicle.html`

**Al cargar la página:**

```
1. Frontend → GET /php/api/vehicles.php?action=current

2. Backend:
   ✓ Busca booking activo del usuario
   ✓ Obtiene datos completos del vehículo
   ✓ Verifica que el vehículo está en uso

3. Backend responde:
   {
     "success": true,
     "vehicle": {
       "id": 5,
       "license_plate": "ABC123",
       "brand": "Tesla",
       "model": "Model 3",
       "battery": 85,
       "location": { "lat": 40.7117, "lng": 0.5783 },
       "price_per_minute": 0.38
     },
     "booking": {
       "id": 10,
       "start_datetime": "2025-10-21 10:30:00",
       "status": "active"
     }
   }

4. Frontend muestra:
   ✓ Información del vehículo
   ✓ Mapa con ubicación
   ✓ Nivel de batería
   ✓ Controles interactivos
```

**Información Mostrada:**
- Matrícula, marca, modelo
- Nivel de batería (visual con colores)
- Ubicación en mapa interactivo
- Precio por minuto
- Tiempo de uso actual

**Controles Disponibles:**
- 🔑 Encender/Apagar motor
- 💡 Encender/Apagar luces
- 📢 Bocina
- 🔓 Bloquear/Desbloquear
- ❌ **Finalitzar Reserva** (botón rojo)

**Archivos involucrados:**
- `js/administrar-vehicle.js` - Control principal
- `php/api/vehicles.php` - Endpoint `action=current`
- `php/controllers/VehicleController.php` - Método `getCurrentVehicle()`

---

### 4️⃣ Finalizar Reserva

**Acción:** Click en botón "Finalitzar Reserva"

**Modal de Confirmación:**
- Muestra vehículo: "Tesla Model 3 (ABC123)"
- Muestra tiempo de uso: "15 minuts"
- Muestra costo estimado: "5.70€"
- Botones: "Cancel·lar" | "Finalitzar"

**Proceso Backend (cuando se confirma):**

```
1. Frontend → POST /php/api/vehicles.php
   Body: { "action": "release" }

2. Backend ejecuta:
   ✓ Busca booking activo del usuario
   ✓ UPDATE vehicles SET status='available' WHERE id=X
   ✓ UPDATE bookings SET status='completed', end_datetime=NOW() 
     WHERE user_id=Y AND vehicle_id=X AND status='active'
   ✓ Limpia $_SESSION['current_vehicle_id']

3. Backend responde:
   { 
     "success": true,
     "message": "Vehicle released successfully"
   }

4. Frontend:
   ✓ Limpia localStorage.removeItem('currentVehicle')
   ✓ Muestra alert: "Reserva finalitzada correctament!"
   ✓ Redirige → localitzar-vehicle.html
```

**Archivos involucrados:**
- `js/administrar-vehicle.js` - Método `setupReleaseButton()`
- `js/vehicles.js` - Método `releaseVehicle()`
- `php/api/vehicles.php` - Endpoint `action=release`
- `php/controllers/VehicleController.php` - Método `releaseVehicle()`
- `php/models/Vehicle.php` - Método `updateStatus()`
- `php/models/Booking.php` - Método `completeBooking()`

---

### 5️⃣ Vehículo Liberado

**Resultado:**
- Vehículo vuelve a aparecer en `localitzar-vehicle.html`
- Status del vehículo: `available`
- Usuario puede reclamar otro vehículo
- El booking anterior está en estado `completed`

---

## 🔒 Seguridad Implementada

### ✅ Un Usuario = Un Vehículo

```php
// En VehicleController::claimVehicle()
$activeBooking = $this->bookingModel->getActiveBooking($userId);
if ($activeBooking) {
    return [
        'success' => false,
        'message' => 'You already have an active booking'
    ];
}
```

**Garantiza:** Usuario NO puede reclamar 2 vehículos simultáneamente.

---

### ✅ Vehículos Disponibles

```php
// En Vehicle::getAvailableVehicles()
$sql = "SELECT * FROM vehicles WHERE status = 'available'";
```

**Garantiza:** Solo se muestran vehículos que NO están en uso.

---

### ✅ Autenticación Obligatoria

```php
// En php/api/vehicles.php
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}
```

**Garantiza:** Solo usuarios autenticados pueden usar el sistema.

---

### ✅ Sincronización Sesión ↔ LocalStorage

**Fuente de Verdad:** Sesión PHP + Base de Datos

**Uso de localStorage:** Solo para caché de UI rápida

```javascript
// En administrar-vehicle.js
// PRIMERO: Consultar servidor
const response = await fetch('/php/api/vehicles.php?action=current');

// SEGUNDO (fallback): LocalStorage solo si servidor falla
if (!vehicleFromServer) {
    const stored = localStorage.getItem('currentVehicle');
}
```

---

## 📊 Estados de Vehículo

| Estado | Descripción | Visible en Localizar |
|--------|-------------|---------------------|
| `available` | Vehículo libre, puede ser reclamado | ✅ SÍ |
| `in_use` | Vehículo reclamado por un usuario | ❌ NO |
| `maintenance` | Vehículo en mantenimiento | ❌ NO |
| `out_of_service` | Vehículo fuera de servicio | ❌ NO |

---

## 📊 Estados de Booking

| Estado | Descripción |
|--------|-------------|
| `pending` | Reserva creada pero no confirmada |
| `confirmed` | Reserva confirmada |
| `active` | Usuario tiene el vehículo actualmente |
| `completed` | Reserva finalizada correctamente |
| `cancelled` | Reserva cancelada |

---

## 🗂️ Estructura de la Base de Datos

### Tabla `vehicles`

```sql
CREATE TABLE vehicles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    license_plate VARCHAR(20) UNIQUE NOT NULL,
    brand VARCHAR(50),
    model VARCHAR(50),
    year INT,
    battery INT DEFAULT 100,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    status ENUM('available', 'in_use', 'maintenance', 'out_of_service') DEFAULT 'available',
    vehicle_type ENUM('car', 'scooter', 'bike') DEFAULT 'car',
    price_per_minute DECIMAL(5, 2) DEFAULT 0.38
);
```

### Tabla `bookings`

```sql
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    status ENUM('pending', 'confirmed', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    total_minutes INT,
    total_cost DECIMAL(10, 2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
);
```

---

## 🧪 Testing del Flujo

### Prueba 1: Reclamar Vehículo

1. Navegar a: `http://localhost/pages/vehicle/localitzar-vehicle.html`
2. Verificar que aparecen vehículos
3. Click en "Reclamar"
4. Verificar modal con "0.50€"
5. Click en "Acceptar i Reclamar"
6. **Esperado:** Redirección a `administrar-vehicle.html`

### Prueba 2: Ver Vehículo Reclamado

1. En `administrar-vehicle.html`
2. **Esperado:** Ver datos del vehículo
3. **Esperado:** Ver mapa con ubicación
4. **Esperado:** Ver batería
5. **Esperado:** Ver controles
6. **Esperado:** Ver botón "Finalitzar Reserva"

### Prueba 3: Finalizar Reserva

1. Click en "Finalitzar Reserva"
2. Verificar modal con tiempo y costo
3. Click en "Finalitzar"
4. **Esperado:** Alert "Reserva finalitzada correctament!"
5. **Esperado:** Redirección a `localitzar-vehicle.html`
6. **Esperado:** Vehículo aparece disponible de nuevo

### Prueba 4: Seguridad - No 2 Vehículos

1. Reclamar un vehículo
2. En otra pestaña, intentar reclamar otro
3. **Esperado:** Error "You already have an active booking"

---

## 🐛 Debugging

### Consola del Navegador (F12)

```javascript
// Ver vehículo actual en localStorage
console.log(JSON.parse(localStorage.getItem('currentVehicle')));

// Ver si hay sesión
console.log('User ID:', window.sessionUserId);
```

### PHP Error Log

```bash
# En Linux/Mac
tail -f /var/log/apache2/error.log

# En Windows (XAMPP)
tail -f C:\xampp\apache\logs\error.log
```

### Network Tab (F12)

- Ver petición `POST /php/api/vehicles.php`
- Verificar payload: `{"action": "claim", "vehicle_id": 5}`
- Verificar respuesta: `{"success": true, "vehicle": {...}}`

---

## 📝 Logs del Sistema

### Frontend (Console)

```
🗺️ Inicializando localizador de vehículos...
📍 Ubicación obtenida: {lat: 41.1956, lng: 1.6051}
🚗 Vehículos cargados: 5
✅ Localizador inicializado
```

```
🚗 Reclamando vehículo: {id: 5, ...}
✅ Vehículo reclamado exitosamente
🔄 Redirigiendo a administrar-vehicle.html...
```

```
🚗 Inicializando control de vehículo...
📡 Consultando servidor...
✅ Vehículo encontrado en servidor
💾 Guardado en localStorage
✅ Control de vehículo inicializado
```

```
🔴 Liberando vehículo...
✅ Vehículo liberado correctamente
🔄 Redirigiendo a localitzar-vehicle.html...
```

### Backend (PHP Error Log)

```
=== VEHICLES API REQUEST ===
Method: POST
Content-Type: application/json
Action detected: claim
JSON Input: {"action":"claim","vehicle_id":5}

=== VehicleController::claimVehicle START ===
Vehicle ID: 5
User ID: 1
Vehicle found: {...}
No active bookings for user
Updating vehicle status to 'in_use'...
Vehicle status updated successfully
Creating booking...
SUCCESS: Booking created with ID: 10
```

---

## 🎯 Puntos Clave de Implementación

### 1. Lectura de JSON Body en PHP

```php
// CRÍTICO: PHP no pone JSON body en $_POST
$jsonInput = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && 
    strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $jsonInput = json_decode(file_get_contents('php://input'), true);
}

// Prioridad: GET > JSON body > POST form > default
$action = $_GET['action'] ?? 
          ($jsonInput['action'] ?? null) ?? 
          $_POST['action'] ?? 
          'list';
```

### 2. Redirección Automática

```javascript
// Después de claim exitoso
setTimeout(() => {
    window.location.href = './administrar-vehicle.html';
}, 1000);

// Después de release exitoso
setTimeout(() => {
    window.location.href = './localitzar-vehicle.html';
}, 500);
```

### 3. Sincronización Servidor-Cliente

```javascript
// SIEMPRE consultar servidor primero
const response = await fetch('/php/api/vehicles.php?action=current');

// LocalStorage solo como fallback/cache
const stored = localStorage.getItem('currentVehicle');
```

---

## 🚀 Mejoras Futuras

### Funcionalidades

- [ ] WebSocket para ubicación en tiempo real
- [ ] Notificaciones push cuando la batería es baja
- [ ] Historial de viajes del usuario
- [ ] Sistema de valoración de vehículos
- [ ] Reserva anticipada de vehículos

### Optimizaciones

- [ ] Caché de vehículos disponibles (Redis)
- [ ] Compresión de respuestas API (gzip)
- [ ] Lazy loading de marcadores del mapa
- [ ] Service Worker para modo offline

### Seguridad

- [ ] Rate limiting en endpoints
- [ ] Tokens CSRF
- [ ] Validación más estricta de inputs
- [ ] Logs de auditoría

---

## 📞 Soporte

Si encuentras problemas:

1. **Revisar consola del navegador** (F12)
2. **Revisar PHP error log**
3. **Revisar Network tab** (F12)
4. **Verificar base de datos** (phpmyadmin o similar)

---

**Última actualización:** 2025-10-21
**Versión:** 1.0
**Estado:** ✅ Funcional y Probado
