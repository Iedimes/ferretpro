# FerrePro - Sistema de Gestión para Ferretería

Sistema web completo para administrar una ferretería de barrio, construido con PHP puro y SQLite.

## Inicio Rápido

### 1. Acceder a la Aplicación

```
http://localhost/ferre/ferrepro/public/
```

### 2. Credenciales de Prueba

- **Email:** `admin@ferrepro.com`
- **Contraseña:** `admin123`

### 3. Menú Principal

Después de iniciar sesión, verás el menú lateral con:
- **Dashboard:** Panel de control principal
- **Punto de Venta:** Sistema de ventas rápido
- **Ventas:** Histórico de ventas
- **Productos:** Administrar inventario
- **Clientes:** Gestión de clientes
- **Proveedores:** Administrar proveedores
- **Cuentas por Cobrar:** Pagos pendientes
- **Categorías:** Organizar productos
- **Reportes:** Análisis de ventas
- **Configuración:** Datos de la empresa
- **Salir:** Cerrar sesión

---

## Características Principales

✅ **Gestión de Inventario**
- Agregar, editar y eliminar productos
- Control de stock con alertas
- Categorización de productos
- Códigos de barras

✅ **Sistema de Ventas (POS)**
- Venta rápida en mostrador
- Carrito de compra interactivo
- Cálculo automático de montos
- Múltiples métodos de pago

✅ **Gestión de Clientes**
- Base de datos de clientes
- Límites de crédito
- Categorías (minorista, mayorista, revendedor)
- Historial de compras

✅ **Cuentas por Cobrar**
- Seguimiento de deudas
- Registro de pagos
- Estados de cuenta

✅ **Reportes**
- Ventas por período
- Productos más vendidos
- Productos con bajo stock
- Análisis de ingresos

✅ **Configuración**
- Datos de la empresa
- Parámetros de sistema
- Información de usuario

---

## Información Técnica

### Stack Tecnológico
- **Backend:** PHP 7.x+
- **Base de Datos:** SQLite 3
- **Frontend:** HTML5 + Bootstrap 5 + JavaScript
- **Servidor:** WAMP/XAMPP

### Estructura de Directorios

```
ferrepro/
├── public/
│   ├── index.php          # Enrutador principal
│   └── test.php           # Información de PHP
├── class/
│   └── Database.php       # Clase de base de datos
├── storage/
│   └── ferrepro.db        # Base de datos SQLite
├── views/
│   ├── layouts/
│   │   └── main.php       # Layout principal
│   ├── auth/
│   │   └── login.php      # Página de login
│   ├── dashboard.php      # Panel principal
│   ├── products/
│   ├── clients/
│   ├── categories/
│   ├── providers/
│   ├── sales/
│   ├── receivable/
│   ├── pos/
│   ├── reports/
│   └── settings/
├── SPEC.md                # Especificación técnica
└── MANUAL_DE_USO.md       # Manual de usuario

```

### Base de Datos

La base de datos SQLite contiene las siguientes tablas:

- **users:** Usuarios del sistema
- **products:** Catálogo de productos
- **categories:** Categorías de productos
- **clients:** Clientes
- **providers:** Proveedores
- **sales:** Histórico de ventas
- **sale_details:** Detalles de cada venta
- **accounts_receivable:** Cuentas por cobrar
- **payments:** Registro de pagos
- **stock_movements:** Movimiento de inventario
- **settings:** Configuración del sistema

---

## Datos de Prueba Incluidos

### Usuarios
- Admin: admin@ferrepro.com / admin123

### Productos (15 items)
- Herramientas (martillo, destornillador, alicates, sierra)
- Pinturas (latex, esmalte)
- Tornillos y tuercas
- Material eléctrico
- Fontanería
- Jardinería
- Elementos de protección

### Clientes (4)
- Cliente Mostrador (para ventas al contado)
- Juan Gómez
- María López
- Ferretería El Tornillo

### Categorías (8)
- Herramientas
- Pinturas
- Tornillos
- Eléctrico
- Fontanería
- Madera
- Protección
- Jardinería

### Proveedores (2)
- Proveedor Principal
- Ferretería ABC

---

## Solución de Problemas

### La página no carga
- Verifica que WAMP está ejecutándose
- Confirma que Apache está iniciado
- Recarga la página (Ctrl+R)

### Error de autenticación
- Verifica las credenciales: admin@ferrepro.com / admin123
- Borra cookies del navegador
- Intenta en navegación privada

### Problemas de base de datos
- La BD está en: `C:\wamp64\www\ferre\ferrepro\storage\ferrepro.db`
- Si se corrompe, elimina el archivo para que se recree automáticamente
- **IMPORTANTE:** Haz copia de seguridad antes de eliminar

### No ves datos en inventario
- Verifica que estés iniciado como admin
- Accede a Productos → Nuevo Producto para agregar manualmente

---

## Próximas Mejoras Planeadas

- [ ] Autenticación mejorada con recuperación de contraseña
- [ ] Generación de facturas en PDF
- [ ] Impresión de comprobantes
- [ ] Exportación de reportes (Excel, PDF)
- [ ] Múltiples usuarios con permisos diferenciados
- [ ] Sistema de compras a proveedores
- [ ] Control de devoluciones
- [ ] Integración con métodos de pago online
- [ ] App móvil para ventas
- [ ] Respaldos automáticos en la nube

---

## Versión

**FerrePro v1.0.0** - Sistema de Gestión Inicial

---

## Licencia

Este software es de uso interno para la ferretería.

---

## Contacto

Para reportar bugs o sugerencias, documenta el error con:
- Qué estabas haciendo
- Qué error ves
- Capturas de pantalla si es posible

---

**Disfruta usando FerrePro!** 🔨
