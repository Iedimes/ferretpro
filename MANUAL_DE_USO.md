# Manual de Uso - FerrePro

Sistema de Gestión para Ferretería - Guía Completa de Uso

## Índice

1. [Inicio de Sesión](#inicio-de-sesión)
2. [Dashboard / Panel Principal](#dashboard--panel-principal)
3. [Gestión de Productos](#gestión-de-productos)
4. [Gestión de Clientes](#gestión-de-clientes)
5. [Gestión de Categorías](#gestión-de-categorías)
6. [Gestión de Proveedores](#gestión-de-proveedores)
7. [Punto de Venta (POS)](#punto-de-venta-pos)
8. [Registro de Ventas](#registro-de-ventas)
9. [Cuentas por Cobrar](#cuentas-por-cobrar)
10. [Reportes](#reportes)
11. [Configuración del Sistema](#configuración-del-sistema)

---

## Inicio de Sesión

### Acceder a la Aplicación

1. Abre tu navegador web (Chrome, Firefox, Edge, etc.)
2. Escribe en la barra de direcciones: `http://localhost/ferre/ferrepro/public/`
3. Verás la pantalla de inicio de sesión

### Credenciales de Prueba

- **Email:** `admin@ferrepro.com`
- **Contraseña:** `admin123`

### Pasos para Ingresar

1. En el campo "Email", escribe: `admin@ferrepro.com`
2. En el campo "Contraseña", escribe: `admin123`
3. Haz clic en el botón "Iniciar Sesión"
4. ¡Listo! Serás redirigido al Dashboard principal

### Cerrar Sesión

- Haz clic en "Salir" en el menú lateral izquierdo
- Serás redirigido a la pantalla de login

---

## Dashboard / Panel Principal

El Dashboard es tu centro de control donde puedes ver un resumen de toda la operación.

### Información que Muestra

**Parte Superior (Indicadores):**
- **Ventas de Hoy:** Número de ventas registradas y monto total
- **Productos Bajo Stock:** Cantidad de productos que están por debajo del stock mínimo
- **Clientes con Deuda:** Número de clientes que tienen saldos pendientes
- **Ventas del Mes:** Resumen del mes actual

**Parte Inferior (Tabla de Ventas Recientes):**
- Últimas 10 ventas realizadas
- Datos: Cliente, Monto, Usuario que realizó la venta, Fecha

### Cómo Usar el Dashboard

1. El Dashboard se carga automáticamente al iniciar sesión
2. También puedes hacer clic en "Dashboard" en el menú lateral en cualquier momento
3. Los datos se actualizan automáticamente cada vez que accedes

---

## Gestión de Productos

Aquí administras tu inventario de productos.

### Acceder a Productos

1. En el menú lateral izquierdo, haz clic en **"Productos"**
2. Verás una tabla con todos tus productos

### Información de Cada Producto

| Columna | Descripción |
|---------|------------|
| **Código** | Código único del producto (ej: HERR-001) |
| **Nombre** | Nombre del producto (ej: Martillo) |
| **Categoría** | Categoría a la que pertenece |
| **Precio Venta** | Precio de venta al público |
| **Stock** | Cantidad disponible en inventario |
| **Acciones** | Botones para Editar o Eliminar |

### Agregar un Nuevo Producto

1. Haz clic en el botón verde **"Nuevo Producto"**
2. Completa el formulario:

   **Datos Básicos:**
   - **Código:** Código único (ej: HERR-005)
   - **Código de Barras:** Si tienes un código de barras
   - **Nombre:** Nombre del producto *

   **Clasificación:**
   - **Categoría:** Selecciona una categoría existente
   - **Proveedor:** Selecciona el proveedor
   - **Unidad:** Unidad de medida (Unidad, Kilo, Metro, Litro)

   **Precios:**
   - **Precio Costo:** Costo de compra
   - **Precio Venta:** Precio de venta al público
   - **Precio Mayorista:** Precio especial para mayoristas (opcional)

   **Stock:**
   - **Stock:** Cantidad actual en inventario
   - **Stock Mínimo:** Cantidad mínima antes de alerta (ej: 5)

   **Ubicación:**
   - **Ubicación:** Dónde se guarda en la ferretería (ej: Pasillo A, Estante 3)

3. Haz clic en **"Guardar"**

### Editar un Producto

1. En la tabla de productos, haz clic en el botón **"Editar"** del producto
2. Modifica los datos que necesites
3. Haz clic en **"Guardar"**

### Eliminar un Producto

1. En la tabla de productos, haz clic en el botón **"Eliminar"**
2. Confirma la eliminación
3. El producto se marca como inactivo (no se ve en ventas futuras)

### Buscar Productos

1. En la parte superior derecha, hay un campo **"Buscar productos..."**
2. Escribe el nombre o código del producto que buscas
3. La tabla se filtra automáticamente mientras escribes

---

## Gestión de Clientes

Administra tu base de datos de clientes.

### Acceder a Clientes

1. En el menú lateral, haz clic en **"Clientes"**
2. Verás una tabla con todos tus clientes

### Información de Cada Cliente

| Campo | Descripción |
|-------|------------|
| **Nombre** | Nombre del cliente |
| **Documento** | DNI, CUIT, etc. |
| **Teléfono** | Número de contacto |
| **Límite de Crédito** | Cuánto puede comprar al crédito |
| **Categoría** | Minorista, Mayorista, Revendedor |

### Agregar un Nuevo Cliente

1. Haz clic en **"Nuevo Cliente"**
2. Completa el formulario:

   **Datos Personales:**
   - **Nombre:** Nombre completo del cliente *
   - **Tipo de Documento:** DNI, CUIT, etc.
   - **Número de Documento:** Número del documento
   - **Teléfono:** Número de contacto

   **Datos de Contacto:**
   - **Email:** Correo electrónico (opcional)
   - **Dirección:** Domicilio del cliente

   **Información Comercial:**
   - **Límite de Crédito:** Monto máximo de crédito
   - **Categoría:** Minorista / Mayorista / Revendedor

3. Haz clic en **"Guardar"**

### Editar un Cliente

1. Haz clic en **"Editar"** junto al cliente
2. Modifica los datos necesarios
3. Haz clic en **"Guardar"**

### Eliminar un Cliente

1. Haz clic en **"Eliminar"**
2. Confirma la eliminación
3. El cliente se marca como inactivo

---

## Gestión de Categorías

Las categorías sirven para organizar los productos.

### Acceder a Categorías

1. En el menú lateral, haz clic en **"Categorías"**
2. Verás todas las categorías disponibles con cantidad de productos

### Agregar una Nueva Categoría

1. Haz clic en **"Nueva Categoría"**
2. Completa:

   - **Nombre:** Nombre de la categoría *
   - **Descripción:** Descripción opcional

3. Haz clic en **"Guardar"**

### Editar una Categoría

1. Haz clic en **"Editar"**
2. Modifica el nombre o descripción
3. Haz clic en **"Guardar"**

---

## Gestión de Proveedores

Administra tus proveedores de productos.

### Acceder a Proveedores

1. En el menú lateral, haz clic en **"Proveedores"**
2. Verás lista de proveedores

### Agregar un Proveedor

1. Haz clic en **"Nuevo Proveedor"**
2. Completa:

   **Información General:**
   - **Nombre:** Nombre del proveedor *
   - **Persona de Contacto:** Nombre de contacto
   - **Teléfono:** Número de contacto

   **Información de Contacto:**
   - **Email:** Correo del proveedor
   - **Dirección:** Domicilio del proveedor

   **Notas:**
   - **Notas:** Observaciones sobre el proveedor

3. Haz clic en **"Guardar"**

---

## Punto de Venta (POS)

Sistema de venta rápida para vendedores en mostrador.

### Acceder a POS

1. En el menú lateral, haz clic en **"Punto de Venta"**
2. Verás una interfaz con:
   - Selector de categorías (izquierda)
   - Productos disponibles (centro)
   - Carrito de compra (derecha)

### Realizar una Venta

1. **Selecciona el Cliente (opcional):**
   - Desplegable: "Cliente Mostrador" si es venta al contado
   - O selecciona un cliente específico para crédito

2. **Selecciona Productos:**
   - Filtra por categoría si quieres (lado izquierdo)
   - Busca el producto en el listado
   - Haz clic en el producto para agregarlo al carrito

3. **Ajusta Cantidades:**
   - En el carrito, ajusta la cantidad si es necesario

4. **Completa la Venta:**
   - Verifica el total
   - Selecciona método de pago (Efectivo, Tarjeta, etc.)
   - Haz clic en **"Completar Venta"**

5. **Confirma:**
   - Se genera un comprobante
   - La venta se registra automáticamente
   - El stock se actualiza

---

## Registro de Ventas

Aquí puedes consultar todas las ventas realizadas.

### Acceder a Ventas

1. En el menú lateral, haz clic en **"Ventas"**
2. Verás tabla con todas las ventas

### Información de Cada Venta

| Campo | Descripción |
|-------|------------|
| **ID** | Número de venta |
| **Cliente** | Nombre del cliente |
| **Monto** | Total de la venta |
| **Vendedor** | Usuario que realizó la venta |
| **Fecha** | Cuándo se realizó |

### Filtrar o Buscar Ventas

- Usa los filtros disponibles para buscar por rango de fechas
- Búsqueda por cliente
- Búsqueda por vendedor

---

## Cuentas por Cobrar

Administra los pagos pendientes de tus clientes.

### Acceder a Cuentas por Cobrar

1. En el menú lateral, haz clic en **"Cuentas por Cobrar"**
2. Verás lista de deudas pendientes

### Información de Cada Cuenta

| Campo | Descripción |
|-------|------------|
| **Cliente** | Nombre del cliente deudor |
| **Monto** | Cantidad adeudada |
| **Pagado** | Cuánto ya pagó |
| **Fecha de Vencimiento** | Cuándo debe pagar |
| **Estado** | Pendiente, Parcial, Cancelada |

### Registrar un Pago

1. Haz clic en **"Registrar Pago"** en la cuenta que deseas
2. Ingresa:
   - **Monto a Pagar:** Cuánto paga
   - **Método de Pago:** Efectivo, Transferencia, etc.
   - **Referencia:** Número de comprobante (opcional)
   - **Notas:** Observaciones

3. Haz clic en **"Registrar Pago"**

### Marcar como Pagada

1. Si la deuda está completamente pagada
2. Haz clic en **"Marcar como Pagada"**
3. El estado cambia a "Cancelada"

---

## Reportes

Genera reportes de tu negocio.

### Acceder a Reportes

1. En el menú lateral, haz clic en **"Reportes"**
2. Verás opciones de reportes

### Tipos de Reportes Disponibles

1. **Reporte de Ventas:**
   - Ventas por período
   - Monto total
   - Cantidad de transacciones

2. **Reporte de Productos:**
   - Productos más vendidos
   - Cantidad vendida por producto
   - Ingresos por producto

3. **Productos con Stock Bajo:**
   - Listado de productos por debajo del stock mínimo
   - Qué necesita ser reabastecido

### Filtrar Reportes

1. Selecciona el tipo de reporte
2. Especifica:
   - **Fecha Desde:** Fecha inicial (ej: 01/01/2024)
   - **Fecha Hasta:** Fecha final (ej: 31/01/2024)
3. Haz clic en **"Generar Reporte"**
4. Se mostrará la información solicitada

### Exportar Reportes (Futuro)

- Próximamente: Exportación a Excel, PDF, etc.

---

## Configuración del Sistema

Personaliza el sistema con los datos de tu ferretería.

### Acceder a Configuración

1. En el menú lateral, haz clic en **"Configuración"**
2. Verás dos secciones principales

### Sección: Datos de la Empresa

Aquí completarás la información de tu ferretería:

1. **Nombre de la Empresa:**
   - Por defecto: "Ferretería FerrePro"
   - Cámbialo por el nombre real de tu negocio

2. **Teléfono:**
   - Tu número de teléfono de contacto

3. **Email:**
   - Tu correo electrónico comercial

4. **CUIT / Documento:**
   - Tu número de CUIT o documento fiscal

### Sección: Parámetros

Configura variables del sistema:

1. **Prefijo de Factura:**
   - Por defecto: "A"
   - Usado para numeración de facturas (A-001, A-002, etc.)

2. **% IVA:**
   - Por defecto: "21"
   - Porcentaje de IVA aplicable a ventas
   - Cámbialo según tu jurisdicción

3. **Alerta de Stock Bajo:**
   - Por defecto: "5"
   - Cantidad mínima de stock antes de mostrar alerta
   - Si un producto tiene menos de este número, aparecerá en rojo

### Guardar Cambios

1. Completa los datos que necesites cambiar
2. Haz clic en el botón azul **"Guardar Cambios"** al final
3. Se mostrará un mensaje de confirmación

---

## Consejos de Uso

### Buenas Prácticas

1. **Actualiza Stock Regularmente:**
   - Mantén actualizado el inventario
   - Realiza conteos periódicos
   - Ajusta cantidades si es necesario

2. **Mantén Datos de Clientes Limpios:**
   - Verifica que los datos de contacto sean correctos
   - Actualiza límites de crédito regularmente

3. **Revisa Reportes Semanalmente:**
   - Consulta productos más vendidos
   - Identifica tendencias de ventas
   - Detecta productos sin movimiento

4. **Revisa Cuentas por Cobrar:**
   - Verifica pagos pendientes
   - Haz seguimiento a deudores
   - Registra pagos puntualmente

5. **Configura Correctamente:**
   - Asegúrate de tener correctamente configurados:
     - Datos de la empresa
     - Porcentaje de IVA
     - Stock mínimo de alerta

### Atajos de Teclado

- **Búsqueda rápida:** En la página de Productos, usa Ctrl+F
- **Ir al Dashboard:** Haz clic en el logo "FerrePro" en la esquina superior

---

## Preguntas Frecuentes

### P: ¿Cómo cambio mi contraseña?
R: Próximamente habrá una sección de perfil de usuario donde podrás cambiar tu contraseña.

### P: ¿Puedo tener múltiples usuarios?
R: Sí, el sistema soporta múltiples usuarios. Actualmente el usuario de prueba es admin@ferrepro.com.

### P: ¿Cómo hago copia de seguridad?
R: Los datos se guardan en una base de datos SQLite en: `C:\wamp64\www\ferre\ferrepro\storage\ferrepro.db`

### P: ¿Se puede imprimir un comprobante de venta?
R: Próximamente se agregará funcionalidad de impresión y generación de PDF.

### P: ¿Hay límite de productos o clientes?
R: No, puedes agregar ilimitados productos y clientes. El sistema escala automáticamente.

---

## Contacto y Soporte

Si tienes problemas o sugerencias:

1. Verifica que WAMP esté corriendo
2. Intenta acceder nuevamente a `http://localhost/ferre/ferrepro/public/`
3. Revisa la consola del navegador (F12) para mensajes de error

---

**Última actualización:** 2024
**Versión:** 1.0.0
