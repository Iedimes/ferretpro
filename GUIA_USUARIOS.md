# Guía - Gestión de Usuarios en FerrePro

## ¿Quién Puede Gestionar Usuarios?

Solo los **Administradores** pueden crear, editar y desactivar usuarios.

## Cómo Crear un Nuevo Usuario

### Paso 1: Acceder a Usuarios
1. En el menú lateral, haz clic en **"Usuarios"**
2. Verás la lista de usuarios actuales

### Paso 2: Crear Nuevo Usuario
1. Haz clic en el botón verde **"Nuevo Usuario"**
2. Se abrirá el formulario de creación

### Paso 3: Completar Formulario

**Campos requeridos (*):**

| Campo | Descripción | Ejemplo |
|-------|------------|---------|
| **Nombre Completo** | Nombre del vendedor o empleado | Juan Pérez |
| **Email** | Correo único del usuario | juan@ferrepro.com |
| **Contraseña** | Contraseña para iniciar sesión | MiContraseña123 |
| **Rol** | Nivel de permisos | Vendedor |

### Paso 4: Seleccionar Rol

#### Vendedor
- ✓ Acceso a Punto de Venta (POS)
- ✓ Realiza ventas
- ✓ Ve sus propias ventas
- ✗ No ve reportes generales
- ✗ No puede cambiar configuración

#### Gerente
- ✓ Acceso a todos los módulos excepto Usuarios
- ✓ Puede ver reportes completos
- ✓ Puede cambiar configuración
- ✓ Acceso a POS
- ✗ No puede crear/editar usuarios

#### Administrador
- ✓ Acceso COMPLETO a todo
- ✓ Puede crear/editar/desactivar usuarios
- ✓ Control total de configuración
- ✓ Acceso a todas las funciones

### Paso 5: Guardar
1. Haz clic en **"Guardar Usuario"**
2. El usuario se crea y aparece en la lista
3. El usuario puede iniciar sesión inmediatamente

---

## Editar un Usuario

### Pasos

1. Ve a **Usuarios** en el menú
2. Haz clic en **"Editar"** junto al usuario que quieres modificar
3. Cambia los datos que necesites:
   - Nombre
   - Email
   - Rol
   - Contraseña (opcional - dejar en blanco mantiene la actual)
4. Haz clic en **"Guardar Usuario"**

---

## Desactivar/Activar un Usuario

### Para Desactivar (Temporalmente)

1. Ve a **Usuarios**
2. Busca el usuario
3. Haz clic en **"Desactivar"**
4. El usuario NO podrá iniciar sesión
5. Sus ventas anteriores quedan en el sistema

### Para Reactivar

1. Ve a **Usuarios**
2. Busca el usuario (aparecerá con estado "Inactivo")
3. Haz clic en **"Activar"**
4. El usuario puede volver a iniciar sesión

---

## Eliminar un Usuario

⚠️ **Nota:** Actualmente los usuarios se desactivan, no se eliminan.

Esto es así porque:
- Se preserva el historial de ventas
- No se pierden datos asociados
- Puedes reactivar el usuario si es necesario

---

## Credenciales de Prueba

Por defecto, el sistema incluye:

### Usuario Admin
- **Email:** admin@ferrepro.com
- **Contraseña:** admin123
- **Rol:** Administrador
- **Permisos:** Acceso completo

---

## Cambiar Contraseña

Cada usuario puede cambiar su contraseña (próximamente):

1. Haz clic en tu nombre (esquina superior derecha)
2. Selecciona **"Mi Perfil"**
3. Haz clic en **"Cambiar Contraseña"**
4. Escribe tu contraseña actual
5. Escribe la nueva contraseña dos veces
6. Haz clic en **"Actualizar"**

---

## Seguridad de Contraseñas

### Recomendaciones

✓ **Usa contraseñas fuertes:**
- Mínimo 8 caracteres
- Combina mayúsculas, minúsculas, números
- Incluye caracteres especiales (!@#$%^&*)

✓ **Cambia contraseña regularmente**
- Cada 30-90 días

✓ **No compartas contraseñas**
- Cada usuario debe tener su propia cuenta

✗ **NO uses:**
- Nombre de la empresa
- Fecha de nacimiento
- Números secuenciales (123456)
- Contraseña igual en todos lados

---

## Historial de Usuarios

Cada usuario tiene asociado:

| Dato | Se guarda? | Ejemplo |
|------|-----------|---------|
| Nombre | ✓ | Juan Pérez |
| Email | ✓ | juan@ferrepro.com |
| Rol | ✓ | Vendedor |
| Ventas realizadas | ✓ | 150 ventas |
| Fecha de creación | ✓ | 15/04/2024 |
| Contraseña anterior | ✗ | Se encripta |
| Intentos de login fallidos | (Próximo) | - |

---

## Tabla de Permisos por Rol

| Funcionalidad | Vendedor | Gerente | Admin |
|--------|----------|---------|-------|
| Dashboard | ✓ | ✓ | ✓ |
| Punto de Venta | ✓ | ✓ | ✓ |
| Ver Ventas | ✓ Propias | ✓ Todas | ✓ Todas |
| Productos | Ver | ✓ CRUD | ✓ CRUD |
| Clientes | Ver | ✓ CRUD | ✓ CRUD |
| Cuentas por Cobrar | Ver | ✓ Editar | ✓ Editar |
| Reportes | Limitados | ✓ Completos | ✓ Completos |
| Usuarios | ✗ | ✗ | ✓ CRUD |
| Configuración | ✗ | ✓ Limitada | ✓ Completa |

---

## Casos de Uso Comunes

### Caso 1: Nuevo Vendedor
1. Creas usuario con rol "Vendedor"
2. Le das email: vendedor@ferrepro.com
3. Contraseña temporal
4. El vendedor accede y vende desde POS
5. Posteriormente cambia su contraseña

### Caso 2: Promover a Gerente
1. Editas usuario de Vendedor
2. Cambias rol a "Gerente"
3. Ahora puede ver reportes y configuración
4. Mantiene acceso a POS

### Caso 3: Empleado Temporal
1. Creas usuario con rol "Vendedor"
2. Durante su trabajo, realiza ventas
3. Cuando se va, haces clic en "Desactivar"
4. Sus ventas quedan registradas
5. Puedes reactivarlo si vuelve

### Caso 4: Control de Auditoría
1. Cada venta registra quién la hizo
2. En reportes ves ventas por vendedor
3. Puedes ver quién hizo qué cambio
4. Útil para análisis de performance

---

## Preguntas Frecuentes

### P: ¿Qué pasa si olvido la contraseña de admin?
R: Por ahora debes acceder a la base de datos SQLite. Próximamente habrá recuperación de contraseña.

### P: ¿Puedo tener dos usuarios con el mismo email?
R: No, el email debe ser único para cada usuario.

### P: ¿Qué pasa cuando desactivo un usuario?
R: No puede iniciar sesión, pero su historial de ventas se mantiene.

### P: ¿Cuántos usuarios puedo crear?
R: Sin límite, aunque se recomienda tener hasta 20-30 vendedores por ferretería.

### P: ¿Se pueden ver todas las ventas de un vendedor?
R: Sí, en Reportes puedes filtrar por vendedor.

---

**Versión:** 1.0.0
**Última actualización:** Abril 2026
