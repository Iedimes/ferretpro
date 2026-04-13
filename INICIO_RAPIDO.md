# FerrePro - Inicio Rápido en 3 Pasos

## Paso 1: Inicia WAMP

Asegúrate de que WAMP esté ejecutándose:
- Busca WAMP en tu computadora
- Haz clic en el icono en la bandeja del sistema (esquina inferior derecha)
- Verifica que los círculos están en VERDE (MySQL y Apache activos)

## Paso 2: Abre tu Navegador

En tu navegador web favorito (Chrome, Firefox, Edge), ve a:

```
http://localhost/ferre/ferrepro/public/
```

## Paso 3: Inicia Sesión

Usa estas credenciales:

| Campo | Valor |
|-------|-------|
| Email | admin@ferrepro.com |
| Contraseña | admin123 |

¡Listo! Estás dentro de FerrePro.

---

## Primeros Pasos Recomendados

### 1. Revisa el Dashboard
Haz clic en "Dashboard" - aquí ves todo lo que está pasando

### 2. Configura tu Empresa
Ve a "Configuración" y completa:
- Nombre de tu ferretería
- Tu teléfono y email
- Número de CUIT
- Porcentaje de IVA

### 3. Explora los Productos
Ve a "Productos" para ver los 15 productos de ejemplo

### 4. Prueba una Venta
Ve a "Punto de Venta" (POS) para hacer una venta de prueba:
- Selecciona productos
- Completa la venta
- ¡Verás cómo se actualiza el Dashboard!

### 5. Revisa Reportes
Ve a "Reportes" para ver tus ventas y análisis

---

## Atajos Útiles

| Acción | Cómo Hacerlo |
|--------|------------|
| Ir a Dashboard | Haz clic en "FerrePro" en la esquina superior |
| Cerrar sesión | Haz clic en "Salir" en el menú lateral |
| Buscar producto | Escribe en el campo de búsqueda en Productos |
| Agregar cliente | Haz clic en "Nuevo Cliente" en la página Clientes |
| Hacer venta | Ve a "Punto de Venta" y selecciona productos |

---

## ¿Necesitas Ayuda?

Lee los documentos incluidos:

1. **README.md** - Información general del sistema
2. **MANUAL_DE_USO.md** - Manual completo paso a paso
3. **SPEC.md** - Especificación técnica del proyecto

---

## Credenciales de Prueba

Estos son usuarios que ya existen:

### Administrador
- **Email:** admin@ferrepro.com
- **Contraseña:** admin123
- **Rol:** Administrador

### Clientes de Ejemplo
- Cliente Mostrador (para ventas al contado)
- Juan Gómez
- María López
- Ferretería El Tornillo

### Productos de Ejemplo (15)
Verás herramientas, pinturas, tornillos, eléctrico, fontanería y más.

---

## Datos Importantes

### Base de Datos
- **Ubicación:** `C:\wamp64\www\ferre\ferrepro\storage\ferrepro.db`
- **Tipo:** SQLite 3
- **Tamaño:** Automático (crece con tus datos)

### Respaldo de Datos
- Copia el archivo `ferrepro.db` a una carpeta segura regularmente
- Esto preservará todas tus ventas, clientes y productos

### Eliminar y Reiniciar
Si algo sale mal y quieres empezar de cero:
1. Elimina: `C:\wamp64\www\ferre\ferrepro\storage\ferrepro.db`
2. Abre `http://localhost/ferre/ferrepro/public/` en el navegador
3. ¡La BD se recreará automáticamente con datos de prueba!

---

## Cambios Frecuentes que Harás

### Agregar un Producto
Productos → Nuevo Producto → Completa datos → Guardar

### Registrar una Venta
Punto de Venta → Selecciona cliente → Agrega productos → Completa venta

### Agregar un Cliente
Clientes → Nuevo Cliente → Completa datos → Guardar

### Ver lo que se vendió
Ventas → Verás todas las ventas registradas

### Ver quién te debe
Cuentas por Cobrar → Verás todos los pagos pendientes

### Hacer respaldo
Copia `ferrepro.db` a tu Dropbox, Google Drive, o USB

---

## Problemas Comunes

### "Página no encontrada" 
- ¿Escribiste bien la URL? Debe ser: `http://localhost/ferre/ferrepro/public/`
- ¿WAMP está encendido?
- Intenta: `http://localhost/phpmyadmin` - si funciona, WAMP está OK

### "Error de autenticación"
- ¿Copiaste bien el email? (sin espacios)
- ¿La contraseña es exactamente `admin123`?
- ¿Las mayúsculas/minúsculas son correctas?

### "Base de datos error"
- Cierra todas las pestañas del navegador con FerrePro
- Abre una nueva pestaña y vuelve a acceder
- Si persiste, puede que necesites reiniciar WAMP

---

## Lo Que Puedes Hacer Ahora

✅ Agregar/editar/eliminar productos
✅ Gestionar clientes y límites de crédito
✅ Hacer ventas y registrar pagos
✅ Ver reportes de ventas
✅ Controlar inventario
✅ Administrar proveedores
✅ Ver cuentas por cobrar
✅ Configurar parámetros del sistema

---

## Lo Que Viene Pronto

🔜 Impresión de facturas
🔜 Exportación a PDF/Excel
🔜 Múltiples usuarios con permisos
🔜 App móvil
🔜 Respaldos en la nube
🔜 Integración de métodos de pago

---

## Versión Actual

**FerrePro v1.0.0** - Sistema funcionando perfectamente ✓

**Base de datos:** Iniciada con datos de prueba
**Usuarios:** Funcionando correctamente
**Vistas:** Todas las páginas renderizando contenido
**POS:** Sistema de ventas operacional

---

¡Disfruta usando FerrePro y buena suerte con tu ferretería! 🔨

**Preguntas?** Lee el manual completo en MANUAL_DE_USO.md
