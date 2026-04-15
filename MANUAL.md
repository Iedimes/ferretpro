# MANUAL DE USUARIO - Ferretería Pro

## ÍNDICE
1. [Inicio](#inicio)
2. [Módulos](#módulos)
3. [Caja](#caja)
4. [Test Data](#test-data)
5. [Resolución](#resolución)

---

## 1. INICIO

### Acceder
```
URL: http://localhost/ferretpro/public
```

### Credenciales
- **Usuario:** admin@ferrepro.com
- **Contraseña:** admin123

---

## 2. MÓDULOS

### Dashboard
- Ventas Hoy / Mes
- Stock Bajo
- CxC Total y Próximos 7 días
- CxP Próximos 7 días
- Caja / Backup

### Punto de Venta (POS)
- Carrito de compras
- Cliente (opcional)
- Tipo: Contado / Crédito
- Método: Efectivo / Transferencia / QR / Tarjeta

### Ventas
- Lista con filtro de fecha
- Imprimir comprobante

### Cotizaciones
- Crear, editar, convertir a venta, imprimir

### Compras
- Contado / Crédito
- Crédito → Genera CxP automáticamente
- Recibir → Actualiza stock

### Productos / Clientes / Proveedores / Categorías
- CRUD estándar

### Cuentas por Cobrar
- Cobrar ventas a crédito

### Cuentas por Pagar
- Pagar con método, cuenta, referencia

### Gastos
- Útiles, Servicios, Mantenimiento, Transporte
- Efectivo / Transferencia
- Caja Física / Banco

### Caja
- Apertura
- Movimientos automáticos
- Movimientos manuales
- Cierre

---

## 3. CAJA

### Flujo Automático
| Operación | Efectivo | Transferencia/QR |
|-----------|----------|------------------|
| Venta | → Caja | → Banco |
| Gasto | ← Caja | ← Banco |
| Pago CxP | ← Caja | ← Banco |

---

## 4. TEST DATA

Para datos de prueba:
1. Ir a `?page=test_data`
2. Click en "Generar datos"

---

## 5. RESOLUCIÓN

### Error de login
- Verificar credenciales
- Limpiar cookies

### Sin datos
- Ejecutar Test Data

---

**Versión:** 1.2
**Actualizado:** Abril 2026