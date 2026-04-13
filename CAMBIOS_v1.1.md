# FerrePro v1.1.0 - Cambios y Mejoras

**Fecha de Actualización:** Abril 2026

## ¿Qué es Nuevo?

### 1. ✅ Sistema de Gestión de Usuarios
- **Nuevo módulo:** Gestión de usuarios del sistema
- **Roles disponibles:** Vendedor, Gerente, Administrador
- **Control de permisos:** Cada rol tiene acceso diferente
- **Activación/Desactivación:** Puedes desactivar usuarios sin perder datos

**Acceso:** Menú lateral → Usuarios (solo para Administradores)

### 2. ✅ Validaciones Mejoradas
- Nueva clase `Validator.php` para validar datos
- Validaciones en:
  - Emails (formato correcto)
  - Teléfonos (10+ dígitos)
  - Documentos (números)
  - Precios (positivos)
  - Cantidades (mayores a 0)

### 3. ✅ Sistema de Notificaciones Flash
- Nueva clase `Flash.php` para mensajes
- Notificaciones de éxito, error, advertencia
- Se muestran una sola vez

### 4. ✅ Correcciones de Bugs
- Arreglado error en POS (strings multilinea en JavaScript)
- Mejorado helpers.php con validación de BASE_URL
- Corregido error de Settings con FETCH_KEY_PAIR

### 5. ✅ Documentación Expandida
- `GUIA_USUARIOS.md` - Cómo gestionar usuarios
- Actualizado `MANUAL_DE_USO.md` con nueva sección de usuarios
- Mejor organización de documentos

---

## Módulos por Rol

### Vendedor
```
✓ Dashboard (limitado)
✓ Punto de Venta (POS)
✓ Ver sus propias ventas
✗ Otros módulos
```

### Gerente
```
✓ Dashboard (completo)
✓ Punto de Venta (POS)
✓ Productos (solo ver)
✓ Clientes (ver)
✓ Reportes (completos)
✓ Configuración (limitada)
✗ Gestión de Usuarios
```

### Administrador
```
✓ ACCESO COMPLETO
✓ Gestión de Usuarios
✓ Configuración completa
✓ Todos los reportes
✓ Todos los módulos
```

---

## Nuevos Archivos

```
class/
├── Validator.php          # Validaciones de formularios
└── Flash.php              # Sistema de notificaciones

views/users/
├── index.php              # Lista de usuarios
└── edit.php               # Crear/editar usuario

Documentación:
├── GUIA_USUARIOS.md       # Guía de usuarios
└── CAMBIOS_v1.1.md        # Este archivo
```

---

## Cambios en index.php

Se agregó nuevo caso `users` en el switch:
```php
case 'users':
    // Gestión de usuarios (solo admin)
```

## Cambios en main.php

Se agregó "Usuarios" al menú lateral (solo visible para admin)

---

## Cómo Actualizar Desde v1.0.0

### Opción 1: Automático
1. El sistema detecta automáticamente los nuevos archivos
2. Las vistas de usuarios se cargan cuando accedes
3. Los usuarios existentes siguen funcionando

### Opción 2: Manual
1. Copia los nuevos archivos
2. No requiere cambios en la BD existente
3. Tu base de datos de v1.0.0 funciona perfectamente

---

## Testing

### ✓ Verificado
- Login con usuario admin
- Acceso a módulos según rol
- Creación de nuevos usuarios
- Edición de usuarios
- Desactivación/Activación de usuarios
- Búsqueda de usuarios
- Todas las vistas previas siguen funcionando

### ✓ Seguridad
- Contraseñas hasheadas con PASSWORD_DEFAULT
- Validación de roles en cada operación
- Solo admin puede crear/editar usuarios

---

## Próximas Mejoras (v1.2.0)

- [ ] Recuperación de contraseña por email
- [ ] Perfil de usuario con cambio de contraseña
- [ ] Historial de login (auditoría)
- [ ] Exportación de reportes por usuario
- [ ] Gráficos de vendedor
- [ ] Comisiones por vendedor

---

## Performance

- Sin cambios significativos
- Las queries de usuarios están optimizadas
- Índices en emails y ids

---

## Compatibilidad

✓ Compatible con PHP 7.4+
✓ Compatible con SQLite 3
✓ Compatible con todos los navegadores

---

## Changelog Técnico

### class/Validator.php
- Nueva clase con 10+ métodos de validación
- Validación de email, teléfono, documento, precio
- Sistema de errores acumulable

### class/Flash.php  
- Nueva clase para notificaciones flash
- Soporta 4 tipos: success, error, warning, info
- Auto-limpieza después de mostrar

### views/users/
- Nueva carpeta con vistas de usuarios
- Interfaz similar a otros módulos
- Búsqueda integrada

### actions/helpers.php
- Agregado check de BASE_URL

### views/pos/index.php
- Arreglado error de strings multilinea
- Mejor rendimiento en JavaScript

---

## Notas para Desarrolladores

### Usar Validator
```php
require_once 'class/Validator.php';

Validator::required('name', 'Nombre');
Validator::email('email');
Validator::numeric('price');

if (Validator::hasErrors()) {
    $errors = Validator::getErrors();
}
```

### Usar Flash
```php
require_once 'class/Flash.php';

Flash::set('success', 'Usuario creado exitosamente');
Flash::set('error', 'El email ya existe');

echo Flash::renderAll(); // En vista
```

---

## Soporte

Si encuentras problemas:
1. Verifica que WAMP esté corriendo
2. Revisa la consola del navegador (F12)
3. Intenta con navegación privada
4. Elimina cookies de FerrePro

---

**Versión:** 1.1.0
**Lanzamiento:** Abril 2026
**Estado:** Estable
