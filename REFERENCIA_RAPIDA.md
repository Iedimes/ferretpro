# FerrePro - Referencia Rápida

## Acceso Rápido a Funciones

### Menú Principal

```
┌─ Dashboard        → Panel de control principal
├─ Punto de Venta   → Sistema de ventas rápido (POS)
├─ Ventas           → Histórico de todas las ventas
├─ Productos        → Gestión de inventario
├─ Clientes         → Base de datos de clientes
├─ Proveedores      → Administrar proveedores
├─ Cuentas por Cobrar → Pagos pendientes
├─ Categorías       → Organizar productos
├─ Reportes         → Análisis y estadísticas
├─ Configuración    → Datos de empresa y parámetros
└─ Salir            → Cerrar sesión
```

---

## Atajos por Tarea

### Vender un Producto
1. Punto de Venta
2. Selecciona cliente
3. Agrega productos
4. Completa venta

### Agregar Nuevo Producto
1. Productos
2. Nuevo Producto
3. Llena formulario
4. Guardar

### Revisar Deudas
1. Cuentas por Cobrar
2. Ve la lista de clientes que deben
3. Haz clic en "Registrar Pago" para cobrar

### Generar Reporte
1. Reportes
2. Selecciona tipo de reporte
3. Elige fechas
4. Haz clic en Generar

### Actualizar Configuración
1. Configuración
2. Modifica datos
3. Haz clic en "Guardar Cambios"

---

## Tamaños de Ventana Recomendados

- **Mínimo:** 1024x768 píxeles
- **Óptimo:** 1366x768 píxeles
- **Completo:** 1920x1080 píxeles

---

## Navegadores Compatibles

- ✓ Google Chrome 80+
- ✓ Mozilla Firefox 75+
- ✓ Microsoft Edge 80+
- ✓ Safari 12+

---

## Funciones por Módulo

### Dashboard
| Función | Descripción |
|---------|------------|
| Ver Ventas de Hoy | Total de ventas del día |
| Productos Bajo Stock | Qué necesita reabastecimiento |
| Clientes con Deuda | Quién debe |
| Ventas del Mes | Total mensual |

### Productos
| Función | Descripción |
|---------|------------|
| Nuevo Producto | Agregar producto |
| Editar | Modificar datos |
| Eliminar | Marcar inactivo |
| Buscar | Filtrar por nombre/código |

### Clientes
| Función | Descripción |
|---------|------------|
| Nuevo Cliente | Crear cliente |
| Editar | Cambiar datos |
| Eliminar | Desactivar cliente |
| Ver Historial | (Próximo) Ver compras |

### Punto de Venta
| Función | Descripción |
|---------|------------|
| Seleccionar Cliente | A quién vender |
| Seleccionar Productos | Qué vender |
| Ajustar Cantidades | Cuántos |
| Procesar Pago | Guardar venta |

### Reportes
| Función | Descripción |
|---------|------------|
| Ventas | Cantidad y monto |
| Productos | Más vendidos |
| Stock Bajo | Qué reponer |

---

## Búsqueda Rápida

### En Productos
- Escribe el nombre: "Martillo"
- Escribe el código: "HERR-001"
- Se filtra automáticamente

### En Clientes
- Escribe el nombre del cliente
- Se filtra mientras escribes

---

## Datos Guardados Automáticamente

✓ Ventas - Se guardan al completar
✓ Productos - Se guardan al crear/editar
✓ Clientes - Se guardan al crear/editar
✓ Pagos - Se guardan al registrar
✓ Configuración - Se guarda al hacer clic en Guardar

---

## Lo Que NO Se Pierde

- Todas tus ventas quedan guardadas
- Historial de productos
- Base de datos de clientes
- Configuración de empresa
- Movimientos de stock

---

## Validaciones Importantes

### Al Crear Producto
- ✓ Nombre es obligatorio (*)
- ✓ Código debe ser único
- ✓ Precios deben ser números

### Al Crear Cliente
- ✓ Nombre es obligatorio (*)
- ✓ Documento debe ser único
- ✓ Límite de crédito debe ser número

### Al Hacer Venta
- ✓ Debe haber al menos 1 producto
- ✓ Cantidad debe ser mayor a 0
- ✓ Debe haber stock disponible

---

## Números de Ayuda Rápida

| Pregunta | Respuesta |
|----------|-----------|
| ¿URL? | http://localhost/ferre/ferrepro/public/ |
| ¿Usuario? | admin@ferrepro.com |
| ¿Contraseña? | admin123 |
| ¿BD ubicación? | C:\wamp64\www\ferre\ferrepro\storage\ferrepro.db |
| ¿Productos incluidos? | 15 |
| ¿Clientes incluidos? | 4 |
| ¿Categorías? | 8 |
| ¿Stock mínimo alerta? | 5 unidades |
| ¿IVA default? | 21% |

---

## Resetear la Base de Datos

Si algo sale mal y quieres empezar de cero:

1. Cierra el navegador con FerrePro
2. Elimina: `C:\wamp64\www\ferre\ferrepro\storage\ferrepro.db`
3. Abre: `http://localhost/ferre/ferrepro/public/`
4. La BD se recreará automáticamente

---

## Copias de Seguridad

### Cómo Hacer Backup

1. Ve a: `C:\wamp64\www\ferre\ferrepro\storage\`
2. Copia el archivo: `ferrepro.db`
3. Pégalo en un lugar seguro (Dropbox, Google Drive, USB)
4. **Haz esto semanalmente**

### Cómo Restaurar

1. Si pierdes datos, copia el backup
2. Pégalo en: `C:\wamp64\www\ferre\ferrepro\storage\`
3. Confirma sobrescribir
4. Abre FerrePro en el navegador
5. ¡Tus datos están restaurados!

---

## Palabras Clave

| Término | Significa |
|---------|-----------|
| **Dashboard** | Panel de control |
| **POS** | Punto de Venta |
| **Stock** | Inventario disponible |
| **SKU** | Código de producto |
| **IVA** | Impuesto sobre ventas |
| **Crédito** | Vender sin cobrar ahora |
| **Deuda** | Lo que debe el cliente |

---

## Checklist Diario

- [ ] Revisa el Dashboard cada mañana
- [ ] Registra todas las ventas en POS
- [ ] Verifica productos bajo stock
- [ ] Registra pagos de clientes
- [ ] Cierra sesión al terminar

## Checklist Semanal

- [ ] Genera reporte de ventas
- [ ] Revisa cuentas por cobrar
- [ ] Haz backup de la BD
- [ ] Verifica productos obsoletos
- [ ] Actualiza precios si cambió proveedor

---

**Última actualización:** Abril 2026
**Versión:** 1.0.0
