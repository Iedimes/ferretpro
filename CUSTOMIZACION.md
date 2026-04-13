# Guía - Customización de FerrePro

## Cambios Comunes Que Querrás Hacer

### 1. Cambiar el Nombre de la Empresa

#### En la Configuración (interfaz)
1. Accede a **Configuración** en el menú
2. En "Datos de la Empresa", cambia "Nombre de la Empresa"
3. Haz clic en "Guardar Cambios"

#### En el Código
Edita el archivo: `views/layouts/main.php`

Busca:
```html
<h5 class="text-white mb-0">FerrePro</h5>
```

Cambia a:
```html
<h5 class="text-white mb-0">Tu Ferretería</h5>
```

### 2. Cambiar el Porcentaje de IVA

#### En la Configuración
1. Ve a **Configuración**
2. En "Parámetros", cambia "% IVA" (default: 21)
3. Haz clic en "Guardar Cambios"

**Nota:** Se aplicará en futuras ventas, las anteriores no se modifican

### 3. Agregar Nuevas Categorías

1. Ve a **Categorías** en el menú
2. Haz clic en **"Nueva Categoría"**
3. Ingresa nombre y descripción
4. Haz clic en "Guardar"

Ejemplo:
- Tuercas y pernos
- Cadenas y cables
- Herramientas eléctricas
- etc.

### 4. Personalizar Colores

#### Color Primario (Azul)
Edita: `views/layouts/main.php`

Busca:
```css
:root { --primary: #2563eb; --dark: #1e293b; }
```

Cambia `#2563eb` a tu color favorito:
```css
:root { --primary: #ff5722; --dark: #1e293b; } /* Naranja */
```

### 5. Cambiar Logo (próximo)

Actualmente no hay logo, pero en v1.2.0 se agregará.

Para ahora, puedes cambiar el texto "FerrePro" por un nombre personalizado.

### 6. Agregar Nuevos Campos a Productos

**Precaución:** Requiere editar la BD

1. Accede a `storage/ferrepro.db` con SQLite
2. Usa `ALTER TABLE products ADD COLUMN nuevo_campo TEXT`
3. Edita `views/products/index.php` para mostrar el nuevo campo

**Mejor alternativa:** Espera a v1.2.0 con UI para esto

### 7. Cambiar el Logo en la Pestaña del Navegador

Edita: `views/layouts/main.php`

En el `<head>`, agrega:
```html
<link rel="icon" type="image/png" href="/favicon.png">
```

Guarda tu imagen como `favicon.png` en la carpeta `public/`

### 8. Cambiar Textos del Sistema

Busca en los archivos `.php` de `views/`

Ejemplo, en `dashboard.php`:
```php
<h6 class="text-muted">Ventas Hoy</h6>
```

Cambia a:
```php
<h6 class="text-muted">Ventas de Hoy</h6>
```

### 9. Agregar Nuevos Usuarios Fácilmente

1. Ve a **Usuarios** (solo si eres admin)
2. Haz clic en **"Nuevo Usuario"**
3. Completa los datos
4. Selecciona el rol
5. Haz clic en "Guardar Usuario"

### 10. Cambiar la Sesión Timeout

Edita: `public/index.php`

Agrega (después de `session_start()`):
```php
ini_set('session.gc_maxlifetime', 1800); // 30 minutos
session_set_cookie_params(1800);
```

### 11. Cambiar Stock Mínimo de Alerta

En **Configuración**:
- "Alerta de Stock Bajo" (default: 5)

Cambias este valor, y productos con stock menor mostrarán alerta.

### 12. Personalizar Dashboard

El dashboard muestra:
- Ventas de hoy
- Ventas del mes
- Stock bajo
- Cuentas por cobrar

Para cambiar qué muestra, edita: `views/dashboard.php`

### 13. Agregar un Nuevo Módulo

#### Paso 1: Crear la vista
Crea: `views/mimodulo/index.php`

```php
<?php
$title = 'Mi Módulo';
$pageTitle = 'Gestión de Mi Módulo';

$content = '<div class="card">
    <div class="card-body">
        <p>Contenido de mi módulo</p>
    </div>
</div>';
?>
```

#### Paso 2: Agregar a menú
Edita: `views/layouts/main.php`

Agrega:
```html
<a class="nav-link" href="?page=mimodulo">Mi Módulo</a>
```

#### Paso 3: Agregar ruta en index.php
Edita: `public/index.php`

Antes del `default`:
```php
case 'mimodulo':
    view('mimodulo/index');
    break;
```

### 14. Cambiar el Idioma (español está por defecto)

Actualmente solo está disponible en español.

Para agregar otro idioma (v1.3.0):
- Crear carpeta `lang/en/`
- Crear archivos con traducciones
- Cargar según preferencia de usuario

### 15. Agregar un Campo de Búsqueda Avanzada

La búsqueda actual es simple (por nombre).

Para agregar filtros, en cualquier vista:
```html
<input type="text" placeholder="Buscar...">
<select>
    <option>Categoría</option>
</select>
<button>Filtrar</button>
```

---

## Cambios Avanzados

### Cambiar Base de Datos a MySQL

1. Instala MySQL
2. Crea BD: `CREATE DATABASE ferrepro;`
3. Edita `class/Database.php`:

```php
$dbHost = 'localhost';
$dbName = 'ferrepro';
$dbUser = 'root';
$dbPass = 'tu_contraseña';

parent::__construct("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
```

4. Ejecuta script de migración

### Agregar Compresión de Imágenes

Cuando subes fotos (futuro), puedes agregar:

```php
function compressImage($source, $destination, $quality = 80) {
    // Comprime imagen
}
```

### Implementar Caché

Para reportes pesados:

```php
class Cache {
    public static function get($key) { }
    public static function set($key, $value) { }
}
```

### Agregar Logs de Auditoría

```php
// En cada acción importante:
log_action('create_product', $product_id, user()['id']);
```

---

## Cambios de Apariencia

### Cambiar Fuente
Edita: `views/layouts/main.php` en `<style>`

```css
body { font-family: 'Segoe UI', sans-serif; }
```

Cambia a:
```css
body { font-family: 'Arial', sans-serif; }
```

### Cambiar Espaciado
Bootstrap usa clases como `mb-3` (margin-bottom).

Para aumentar, agrega clases:
```html
<div class="mb-5"> <!-- Más espacio -->
```

### Cambiar Bordes
Agrega clases de Bootstrap:
```html
<div class="card border-danger"> <!-- Borde rojo -->
```

### Cambiar Sombras
```html
<div class="card shadow-lg"> <!-- Sombra grande -->
```

---

## Problemas Comunes de Personalización

### "No ves mis cambios"
- Limpia cache del navegador (Ctrl+Shift+Delete)
- Recarga la página (Ctrl+F5)
- Prueba en navegación privada

### "Rompí algo"
- Reversa los cambios
- Restaura desde copia de seguridad de código
- Consulta el git history

### "Necesito cambios más complejos"
- Consulta la documentación técnica (SPEC.md)
- Revisa ejemplos de código en `views/`
- Usa developer tools (F12)

---

## Recursos

- Bootstrap 5 Docs: https://getbootstrap.com/docs/5.0/
- PHP Manual: https://www.php.net/manual/
- SQLite Docs: https://www.sqlite.org/docs.html
- JavaScript: https://developer.mozilla.org/en-US/docs/Web/JavaScript/

---

## Consejos

✓ Siempre haz backup antes de cambios
✓ Prueba en navegación privada primero
✓ Lee el código existente para entender patrones
✓ Mantén la estructura de carpetas
✓ Documenta tus cambios
✓ Prueba en múltiples navegadores

---

**Versión:** 1.1.0
**Para:** Desarrolladores y customización

¡Siéntete libre de personalizar FerrePro según necesites!
