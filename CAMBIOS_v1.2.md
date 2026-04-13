# Changelog - FerrePro v1.2.0
## Autenticación y Seguridad

**Fecha de Lanzamiento:** 2024
**Versión Anterior:** v1.1.0

---

## Resumen de Cambios

FerrePro v1.2.0 enfoca en **seguridad de autenticación y protección de datos**. Se implementan múltiples características para mejorar la experiencia del usuario y prevenir accesos no autorizados.

### Estadísticas
- **Nuevas Clases:** 2 (Auth.php, TOTP.php)
- **Nuevas Tablas BD:** 3 (password_resets, login_history, session_recovery)
- **Nuevas Vistas:** 3 (forgot_password.php, reset_password.php, profile.php)
- **Cambios en Rutas:** 5 rutas nuevas
- **Cambios Totales:** 40+ cambios en código

---

## Características Principales

### 1. Recuperación de Contraseña (Password Reset) ✅
**Descripción:** Los usuarios pueden recuperar su contraseña si la olvidan.

**Cómo Funciona:**
1. Usuario accede a "¿Olvidó su contraseña?" en la página de login
2. Ingresa su email registrado
3. Se genera un token único con expiración de 1 hora
4. Usuario accede a link de reinicio y define nueva contraseña
5. Token se marca como usado (no puede reutilizarse)

**Seguridad:**
- Tokens únicos de 64 caracteres (SHA256)
- Expiración automática después de 1 hora
- Solo se puede usar una vez
- La contraseña se guarda con hash bcrypt

**Archivos Modificados:**
- `public/index.php` - Rutas: forgot_password, reset_password, reset_password_post
- `class/Auth.php` - Métodos: generatePasswordResetToken(), verifyPasswordResetToken(), resetPasswordWithToken()
- `views/auth/login.php` - Link "¿Olvidó su contraseña?"
- `views/auth/forgot_password.php` - NUEVO
- `views/auth/reset_password.php` - NUEVO

**Uso:**
```
1. Acceder a ?page=forgot_password
2. Ingresar email
3. Recibir link (o copiar del navegador para testing)
4. Acceder a link con token
5. Ingresar nueva contraseña
```

---

### 2. Perfil de Usuario y Cambio de Contraseña ✅
**Descripción:** Cada usuario puede acceder a su perfil, actualizar información y cambiar contraseña.

**Características:**
- Ver y editar nombre y email
- Cambiar contraseña (requiere contraseña actual)
- Ver rol de usuario
- Ver fecha de registro
- Historial de últimos 10 logins

**Seguridad:**
- Requiere verificación de contraseña actual
- Validación de email único en el sistema
- Contraseña mínima de 8 caracteres

**Archivos Modificados:**
- `public/index.php` - Ruta: profile
- `class/Auth.php` - Método: changePassword()
- `class/Flash.php` - Agregados métodos convenience (success, error, warning, info)
- `views/profile.php` - NUEVO
- `views/layouts/main.php` - Link a "Mi Perfil" en navbar

**Uso:**
```
1. Acceder a ?page=profile
2. Actualizar nombre/email o cambiar contraseña
3. Confirmar con contraseña actual
4. Ver historial de logins
```

---

### 3. Historial de Login (Auditoría) ✅
**Descripción:** Sistema de logging de todos los intentos de login (exitosos y fallidos).

**Registra:**
- ID de usuario
- Dirección IP
- User Agent (navegador/cliente)
- Estado (success/failed)
- Razón de fallo (si aplica)
- Timestamp exacto

**Funciones:**
- Visualización en perfil de usuario (últimos 10)
- Identificación de accesos sospechosos
- Auditoría de seguridad
- Base para análisis de intentos de ataque

**Archivos Modificados:**
- `class/Database.php` - Nueva tabla: login_history
- `class/Auth.php` - Métodos: logLoginAttempt(), getLoginHistory()

**Base de Datos:**
```sql
CREATE TABLE login_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    status TEXT DEFAULT 'success',
    failed_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

### 4. Protección Contra Ataques de Fuerza Bruta ✅
**Descripción:** Bloqueo automático de cuenta después de múltiples intentos fallidos.

**Mecanismo:**
- Máximo 5 intentos fallidos de login
- Después del 5to intento: cuenta bloqueada por 15 minutos
- Se resetea después del lockout period
- Se resetea en login exitoso

**Seguridad:**
- Previene ataques de diccionario
- Bloqueo temporal (no permanente)
- Reset automático

**Archivos Modificados:**
- `class/Auth.php` - Métodos:
  - recordFailedAttempt()
  - isUserLockedOut()
  - resetFailedAttempts()

**Configuración:**
```php
private static $max_failed_attempts = 5;
private static $lockout_duration = 900; // 15 minutos
```

---

### 5. Timeout de Sesión (Auto-logout) ✅
**Descripción:** Las sesiones expiran automáticamente después de 15 minutos de inactividad.

**Funcionalidad:**
- Tiempo de inactividad: 15 minutos (configurable)
- Al expirar: usuario es deslogueado automáticamente
- Mensaje informativo al logout por timeout
- Se restablece con cada acción del usuario

**Seguridad:**
- Protege contra acceso no autorizado si se deja sesión abierta
- Especialmente importante en dispositivos compartidos

**Archivos Modificados:**
- `public/index.php` - Verificación de session timeout

**Configuración:**
```php
$session_timeout = 15 * 60; // 15 minutos en segundos
```

---

### 6. Framework de 2FA (TOTP) ✅
**Descripción:** Base implementada para autenticación de dos factores usando TOTP.

**Características Implementadas:**
- Generación de secretos aleatorios (base32)
- Generación de URLs QR para apps autenticadores
- Verificación de códigos TOTP (RFC 6238)
- Soporte para discrepancias de tiempo
- Métodos en Auth class para enable/disable 2FA
- Métodos para verificar códigos 2FA

**Funcionalidad:**
- Compatible con Google Authenticator, Microsoft Authenticator, etc.
- Códigos de 6 dígitos
- Ventana de tiempo de 30 segundos
- Tolerancia de ±1 ventana de tiempo

**Archivos Nuevos:**
- `class/TOTP.php` - Implementación TOTP completa

**Métodos Disponibles:**
```php
TOTP::generateSecret()           // Generar secreto
TOTP::getQRCodeURL()             // URL para código QR
TOTP::verify()                   // Verificar código
TOTP::getCurrentCode()           // Código actual (testing)

Auth::enable2FA()                // Activar 2FA para usuario
Auth::disable2FA()               // Desactivar 2FA
Auth::verify2FACode()            // Verificar código 2FA
Auth::is2FAEnabled()             // Chequear si está activado
```

**Nota:** La integración completa en el flujo de login está planeada para v1.3.0

---

### 7. Recuperación de Sesión ✅
**Descripción:** Framework para recuperar sesiones en caso de logout accidental.

**Funcionalidad:**
- Generar tokens de recuperación (64 caracteres)
- Recuperar sesión con token válido
- Validación de IP (seguridad básica)
- Tokens con expiración de 1 hora

**Métodos en Auth:**
```php
Auth::generateSessionRecoveryToken($userId)  // Generar token
Auth::recoverSession($token)                 // Recuperar sesión
```

**Nota:** La interfaz para recuperación de sesión está planeada para v1.3.0

---

## Cambios en Base de Datos

### Nuevas Tablas

#### 1. password_resets
```sql
CREATE TABLE password_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token TEXT UNIQUE NOT NULL,
    email TEXT NOT NULL,
    used INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 2. login_history
```sql
CREATE TABLE login_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    ip_address TEXT,
    user_agent TEXT,
    status TEXT DEFAULT 'success',
    failed_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 3. session_recovery
```sql
CREATE TABLE session_recovery (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    recovery_token TEXT UNIQUE NOT NULL,
    session_token TEXT,
    ip_address TEXT,
    used INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Nuevas Columnas en users
- `last_login DATETIME` - Último login exitoso
- `last_password_change DATETIME` - Último cambio de contraseña
- `failed_login_attempts INTEGER DEFAULT 0` - Contador de intentos fallidos
- `locked_until DATETIME` - Fecha/hora de desbloqueo (si está bloqueado)
- `two_fa_enabled INTEGER DEFAULT 0` - Estado de 2FA
- `two_fa_secret TEXT` - Secreto TOTP (encriptado en producción)

---

## Cambios en Clases PHP

### Nueva Clase: Auth.php
Centraliza toda la lógica de autenticación y seguridad.

**Métodos Principales:**
- `logLoginAttempt($userId, $status, $reason)` - Registrar intento
- `recordFailedAttempt($email)` - Registrar fallo
- `isUserLockedOut($user)` - Chequear bloqueo
- `resetFailedAttempts($userId)` - Resetear contador
- `generatePasswordResetToken($email)` - Generar token reset
- `verifyPasswordResetToken($token)` - Verificar token
- `resetPasswordWithToken($token, $password)` - Cambiar contraseña con token
- `changePassword($userId, $currentPass, $newPass)` - Cambiar contraseña actual
- `getLoginHistory($userId, $limit)` - Obtener historial
- `getRecentFailedAttempts($email, $minutes)` - Contar fallos recientes
- `enable2FA($userId)` - Activar 2FA
- `disable2FA($userId)` - Desactivar 2FA
- `verify2FACode($userId, $code)` - Verificar código 2FA
- `is2FAEnabled($userId)` - Chequear si está activado
- `generateSessionRecoveryToken($userId)` - Generar token recuperación
- `recoverSession($token)` - Recuperar sesión

### Nueva Clase: TOTP.php
Implementación completa de TOTP (RFC 6238) para 2FA.

**Métodos Principales:**
- `generateSecret($length)` - Generar secreto base32
- `getQRCodeURL($secret, $email, $issuer)` - URL QR para apps
- `verify($secret, $code, $discrepancy)` - Verificar código
- `getCurrentCode($secret)` - Código actual (testing)

### Modificada Clase: Flash.php
Agregados métodos convenience para notificaciones.

**Nuevos Métodos:**
- `success($message)` - Notificación de éxito
- `error($message)` - Notificación de error
- `warning($message)` - Notificación de advertencia
- `info($message)` - Notificación informativa

---

## Cambios en Vistas

### Modificada: auth/login.php
- Agregado link "¿Olvidó su contraseña?"
- Actualizado version a v1.2.0
- Mejorado estilos

### Nueva: auth/forgot_password.php
- Formulario para recuperación de contraseña
- Validación de email
- Mensajes de éxito/error

### Nueva: auth/reset_password.php
- Formulario para ingresar nueva contraseña
- Validación de contraseña (mínimo 8 caracteres)
- Confirmación de contraseña

### Nueva: profile.php
- Sección: Mi Perfil (editar nombre/email)
- Sección: Cambiar Contraseña
- Sección: Historial de Login (últimos 10)
- Estilos responsive con Bootstrap

### Modificada: layouts/main.php
- Agregado link "Mi Perfil" en navbar
- Agregada visualización de Flash messages
- Mejorado diseño de navbar

---

## Cambios en Enrutamiento

### Nuevas Rutas

```
?page=forgot_password              GET  - Mostrar formulario recuperación
?page=forgot_password_post         POST - Procesar solicitud de recuperación
?page=reset_password&token=XXX     GET  - Mostrar formulario reset
?page=reset_password_post          POST - Procesar cambio de contraseña
?page=profile                      GET  - Mostrar perfil de usuario
```

### Rutas Modificadas
```
?page=login_post                   POST - Mejorado con auditoría y bloqueos
?page=login                        GET  - Agregado link de recuperación
```

---

## Mejoras de Seguridad

| Aspecto | Mejora | Tipo |
|--------|--------|------|
| **Contraseñas** | Recovery tokens con expiración | Prevención |
| **Ataques Fuerza Bruta** | Bloqueo de 15 min después de 5 intentos | Prevención |
| **Sesiones** | Auto-logout después de 15 min inactividad | Prevención |
| **Auditoría** | Registro completo de intentos de login | Detección |
| **Contraseña Actual** | Requerida para cambios | Prevención |
| **2FA Framework** | Base para futuro 2FA | Expansible |

---

## Instrucciones de Actualización

### Para Usuarios Existentes
1. Descargar v1.2.0
2. Reemplazar archivos
3. BD se actualiza automáticamente (nuevas columnas/tablas)
4. Continuar usando como de costumbre

### Características Nuevas
- Ir a "¿Olvidó su contraseña?" para recuperar contraseña
- Acceder a "Mi Perfil" para cambiar contraseña
- Ver historial de login en perfil

---

## Próximas Características (v1.3.0)

- [ ] Integración completa de 2FA en login
- [ ] Interfaz de recuperación de sesión
- [ ] Exportación a PDF/Excel de auditoría
- [ ] Configuración granular de timeouts
- [ ] Email para password reset (SMTP)
- [ ] Backup automático de claves TOTP
- [ ] Análisis de intentos de ataque en reportes

---

## Testing Realizado

✅ Recuperación de contraseña (generar token, verificar, resetear)
✅ Perfil de usuario (editar info, cambiar contraseña)
✅ Historial de login (registro y visualización)
✅ Bloqueo de cuenta (5 intentos fallidos)
✅ Timeout de sesión (15 min inactividad)
✅ Validaciones de email y contraseña
✅ Sintaxis PHP (todos los archivos)
✅ Compatibilidad con WAMP/XAMPP

---

## Notas Técnicas

### Configuración de Constantes
```php
// Auth.php
private static $max_failed_attempts = 5;
private static $lockout_duration = 900;      // 15 minutos
private static $reset_token_expiry = 3600;   // 1 hora

// index.php
$session_timeout = 15 * 60;  // 15 minutos
```

### Dependencias
- PHP 7.4+ (hash_hmac, random_bytes)
- SQLite 3
- No requiere librerías externas

### Performance
- Queries indexadas automáticamente por SQLite
- Cleanup de tokens expirados bajo demanda
- Historial limitado a últimos 20 registros por vista

---

## Contribuidores
- OpenCode Agent
- FerrePro Development Team

---

## Soporte
Para reportar bugs o solicitar características: https://github.com/anomalyco/opencode
