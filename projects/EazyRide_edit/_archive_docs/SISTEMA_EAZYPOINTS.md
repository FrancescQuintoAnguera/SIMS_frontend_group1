# 💰 Sistema EazyPoints - Implementación Completa

## 📊 Resumen del Sistema

El nuevo sistema de **EazyPoints** reemplaza el sistema de compra de tiempo directo por una moneda virtual que psicológicamente reduce la percepción del gasto real.

---

## 🎯 Estructura de Precios

### 📦 Paquetes de EazyPoints

| Paquet | Punts | Preu | Descompte | Equivalència |
|--------|-------|------|-----------|--------------|
| **Bàsic** | 400 | 7,50€ | 20% | ~30 min lloguer |
| **Mig** | 1.000 | 18,00€ | 23% | ~1,25h lloguer |
| **Gran** | 2.000 | 34,00€ | 30% | ~2,5h lloguer |
| **Extra** | 5.000 | 80,00€ | 35% + bonus | ~6h lloguer |

### 🚗 Cost del Lloguer

| Temps | Punts | Equivalent en € |
|-------|-------|-----------------|
| 30 minuts | 400 pts | ~7,5€ |
| 1 hora | 800 pts | ~15€ |
| 2 hores | 1.600 pts | ~30€ |
| Hora addicional | +1.000 pts | +~18,75€ |

### ✨ Usuarios Premium

#### Planes:
- **Mensual**: 9,99€/mes
- **Anual**: 95€/año (ahorro de ~25€)

#### Beneficios:
1. **15 minutos gratuitos diarios** (equivalente a ~200 pts/día)
2. **15% descuento** en todos los paquets de punts
3. **Reducción de coste** en horas adicionales: 850-900 pts/h (vs 1.000 pts)
4. **Acceso prioritario** a vehículos
5. **Vehículos exclusivos** con mejor autonomía
6. **Tiempo extendido** sin coste extra
7. **Atención prioritaria** al cliente
8. **Estadísticas avanzadas** (km recorridos, ahorro CO₂)
9. **Gamificación**: insignias y rankings

---

## 🗄️ Estructura de Base de Datos

### Nuevas Tablas Necesarias

#### 1. Tabla `user_points`
```sql
CREATE TABLE user_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    points INT DEFAULT 0,
    total_purchased INT DEFAULT 0,
    total_spent INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 2. Tabla `point_transactions`
```sql
CREATE TABLE point_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('purchase', 'spend', 'bonus', 'refund', 'premium_daily') NOT NULL,
    points INT NOT NULL,
    price DECIMAL(10,2) DEFAULT NULL,
    package_name VARCHAR(50) DEFAULT NULL,
    discount INT DEFAULT 0,
    description TEXT,
    booking_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_user_type (user_id, type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 3. Tabla `premium_subscriptions`
```sql
CREATE TABLE premium_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('monthly', 'annual') NOT NULL,
    status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    last_daily_bonus DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_end_date (end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 4. Actualizar tabla `users`
```sql
ALTER TABLE users ADD COLUMN is_premium BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN premium_expires_at DATE DEFAULT NULL;
```

---

## 🔧 APIs PHP Necesarias

### 1. **get-points.php** - Obtener saldo de puntos
```php
<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autoritzat']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Obtener o crear registro de puntos
    $stmt = $pdo->prepare("
        SELECT points, total_purchased, total_spent 
        FROM user_points 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        // Crear registro si no existe
        $stmt = $pdo->prepare("
            INSERT INTO user_points (user_id, points) VALUES (?, 0)
        ");
        $stmt->execute([$user_id]);
        $result = ['points' => 0, 'total_purchased' => 0, 'total_spent' => 0];
    }
    
    echo json_encode([
        'success' => true,
        'points' => (int)$result['points'],
        'total_purchased' => (int)$result['total_purchased'],
        'total_spent' => (int)$result['total_spent']
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>
```

### 2. **purchase-points.php** - Comprar paquete de puntos
```php
<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autoritzat']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$points = (int)$data['points'];
$price = (float)$data['price'];
$package = $data['package'];
$discount = (int)$data['discount'];

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Verificar si es usuario premium para descuento adicional
    $stmt = $pdo->prepare("
        SELECT is_premium, premium_expires_at 
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $final_price = $price;
    $final_discount = $discount;
    
    // Descuento adicional del 15% para premium
    if ($user['is_premium'] && 
        $user['premium_expires_at'] && 
        strtotime($user['premium_expires_at']) > time()) {
        $final_price = $price * 0.85;  // 15% adicional
        $final_discount += 15;
    }
    
    // TODO: Aquí iría la integración con pasarela de pago
    // Por ahora, simplemente agregamos los puntos
    
    // Actualizar puntos del usuario
    $stmt = $pdo->prepare("
        INSERT INTO user_points (user_id, points, total_purchased) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            points = points + VALUES(points),
            total_purchased = total_purchased + VALUES(points)
    ");
    $stmt->execute([$user_id, $points, $points]);
    
    // Registrar transacción
    $stmt = $pdo->prepare("
        INSERT INTO point_transactions 
        (user_id, type, points, price, package_name, discount, description) 
        VALUES (?, 'purchase', ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id, 
        $points, 
        $final_price, 
        $package, 
        $final_discount,
        "Compra paquet $package"
    ]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Compra realitzada correctament',
        'points_added' => $points,
        'price_paid' => $final_price
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>
```

### 3. **spend-points.php** - Gastar puntos en reserva
```php
<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autoritzat']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$points = (int)$data['points'];
$booking_id = (int)$data['booking_id'];
$description = $data['description'] ?? 'Lloguer de vehicle';

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    
    // Verificar saldo
    $stmt = $pdo->prepare("SELECT points FROM user_points WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $current_points = $stmt->fetchColumn();
    
    if ($current_points < $points) {
        throw new Exception('Punts insuficients');
    }
    
    // Descontar puntos
    $stmt = $pdo->prepare("
        UPDATE user_points 
        SET points = points - ?, 
            total_spent = total_spent + ?
        WHERE user_id = ?
    ");
    $stmt->execute([$points, $points, $user_id]);
    
    // Registrar transacción
    $stmt = $pdo->prepare("
        INSERT INTO point_transactions 
        (user_id, type, points, booking_id, description) 
        VALUES (?, 'spend', ?, ?, ?)
    ");
    $stmt->execute([$user_id, -$points, $booking_id, $description]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'points_remaining' => $current_points - $points
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
```

### 4. **premium-daily-bonus.php** - Bonus diario premium
```php
<?php
require_once '../config/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autoritzat']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $pdo = getDBConnection();
    
    // Verificar suscripción premium activa
    $stmt = $pdo->prepare("
        SELECT id, last_daily_bonus 
        FROM premium_subscriptions 
        WHERE user_id = ? 
          AND status = 'active' 
          AND end_date >= CURDATE()
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$subscription) {
        echo json_encode(['success' => false, 'message' => 'No tens subscripció premium activa']);
        exit;
    }
    
    // Verificar si ya reclamó hoy
    $today = date('Y-m-d');
    if ($subscription['last_daily_bonus'] === $today) {
        echo json_encode(['success' => false, 'message' => 'Ja has rebut el bonus d\'avui']);
        exit;
    }
    
    $pdo->beginTransaction();
    
    // 15 minutos gratis = ~200 puntos
    $bonus_points = 200;
    
    // Agregar puntos
    $stmt = $pdo->prepare("
        INSERT INTO user_points (user_id, points) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE points = points + VALUES(points)
    ");
    $stmt->execute([$user_id, $bonus_points]);
    
    // Registrar transacción
    $stmt = $pdo->prepare("
        INSERT INTO point_transactions 
        (user_id, type, points, description) 
        VALUES (?, 'premium_daily', ?, '15 minuts gratuïts diaris Premium')
    ");
    $stmt->execute([$user_id, $bonus_points]);
    
    // Actualizar fecha del último bonus
    $stmt = $pdo->prepare("
        UPDATE premium_subscriptions 
        SET last_daily_bonus = ? 
        WHERE id = ?
    ");
    $stmt->execute([$today, $subscription['id']]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'bonus_points' => $bonus_points,
        'message' => 'Bonus diari rebut correctament!'
    ]);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
}
?>
```

---

## 📱 Cambios en el Frontend

### Archivos Actualizados:

✅ **purchase-time.html** → Ahora muestra paquetes de EazyPoints
- Saldo actual de puntos
- Banner premium
- 4 paquetes con descuentos
- Modal de confirmación mejorado
- Integración con APIs

### Archivos que Necesitan Actualización:

1. **gestio.html** - Mostrar saldo de puntos en dashboard
2. **administrar-vehicle.html** - Descontar puntos al usar vehículo
3. **premium.html** - Página de suscripción premium
4. **perfil.html** - Mostrar estadísticas de puntos

---

## 🎮 Lógica del Sistema de Reservas

### Cálculo de Puntos por Tiempo:

```javascript
function calculatePointsForTime(minutes) {
    if (minutes <= 30) return 400;
    if (minutes <= 60) return 800;
    if (minutes <= 120) return 1600;
    
    // Más de 2 horas
    const hours_extra = Math.ceil((minutes - 120) / 60);
    const is_premium = checkUserPremium(); // función a implementar
    const points_per_hour = is_premium ? 900 : 1000;
    
    return 1600 + (hours_extra * points_per_hour);
}
```

### Descuento Premium en Paquetes:

```javascript
function applyPremiumDiscount(price) {
    const is_premium = checkUserPremium();
    if (is_premium) {
        return price * 0.85; // 15% descuento
    }
    return price;
}
```

---

## 💡 Ventajas del Sistema EazyPoints

### Psicológicas:
1. **Desconexión del valor real**: Los usuarios no ven directamente el gasto en euros
2. **Gamificación**: Acumular puntos se siente como un juego
3. **Incentivo a comprar más**: Mejores descuentos en paquetes grandes
4. **Fidelización**: Sistema premium con beneficios continuos

### Económicas:
1. **Prepago**: Los usuarios pagan por adelantado
2. **Margen mejorado**: Puntos no usados = beneficio
3. **Previsión de ingresos**: Suscripciones mensuales/anuales
4. **Upselling**: Incentivar paso a premium

### Operativas:
1. **Control de uso**: Los puntos limitan el uso no controlado
2. **Flexibilidad**: Fácil ajustar precios en puntos
3. **Analytics**: Mejor seguimiento de patrones de uso
4. **Reducción de fraude**: Sistema de créditos prepagados

---

## 📊 Métricas a Seguir

### KPIs Principales:
- **Tasa de conversión** a premium
- **Valor promedio** de compra de puntos
- **Puntos no utilizados** (dead points)
- **Frecuencia** de uso por usuario
- **LTV** (Lifetime Value) por usuario
- **Churn rate** de premium

### Reportes Recomendados:
1. Balance de puntos por usuario
2. Historial de transacciones
3. Análisis de paquetes más vendidos
4. Uso de beneficios premium
5. Proyección de renovaciones

---

## 🚀 Plan de Implementación

### Fase 1: Base de Datos (1-2 días)
- [ ] Crear tablas nuevas
- [ ] Migrar usuarios existentes
- [ ] Scripts de inicialización

### Fase 2: APIs Backend (2-3 días)
- [ ] get-points.php
- [ ] purchase-points.php
- [ ] spend-points.php
- [ ] premium-daily-bonus.php
- [ ] Integration con pasarela de pago

### Fase 3: Frontend (2-3 días)
- [ ] Actualizar purchase-time.html ✅
- [ ] Crear premium.html
- [ ] Actualizar gestio.html
- [ ] Actualizar administrar-vehicle.html

### Fase 4: Testing (2 días)
- [ ] Test de compra de puntos
- [ ] Test de gasto de puntos
- [ ] Test de suscripción premium
- [ ] Test de bonus diario

### Fase 5: Deploy (1 día)
- [ ] Backup de BD
- [ ] Migración a producción
- [ ] Monitoreo inicial

**Total estimado: 8-11 días**

---

## ⚠️ Consideraciones Importantes

1. **Integración de pago**: Stripe, PayPal, Redsys, etc.
2. **Regulación legal**: Consultar legislación sobre monedas virtuales
3. **Política de reembolso**: Definir claramente
4. **Expiración de puntos**: ¿Los puntos caducan?
5. **Transferencia entre usuarios**: ¿Permitir o no?
6. **Conversión a dinero**: ¿Permitir cash-out?

---

**¡Sistema EazyPoints listo para implementar!** 🎉
