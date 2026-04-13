# Índice de Documentación - FerrePro

## 📚 Documentación Disponible

### Para Usuarios

#### 🚀 Empezar
1. **[INICIO_RAPIDO.md](INICIO_RAPIDO.md)** - Inicia en 3 pasos
   - Cómo abrir la app
   - Credenciales
   - Primeros pasos

2. **[README.md](README.md)** - Información general
   - Stack técnico
   - Características principales
   - Solución de problemas

#### 📖 Manuales Detallados

3. **[MANUAL_DE_USO.md](MANUAL_DE_USO.md)** - Guía completa paso a paso
   - Todos los módulos explicados
   - Cómo realizar cada tarea
   - Buenas prácticas
   - Preguntas frecuentes

4. **[GUIA_USUARIOS.md](GUIA_USUARIOS.md)** - Gestión de usuarios
   - Crear/editar usuarios
   - Roles y permisos
   - Seguridad de contraseñas
   - Casos de uso

5. **[REFERENCIA_RAPIDA.md](REFERENCIA_RAPIDA.md)** - Atajo rápido
   - Funciones por módulo
   - Atajos de teclado
   - Tabla de validaciones
   - Checklist diario/semanal

#### 🔐 Seguridad v1.2.0

6. **[CAMBIOS_v1.2.md](CAMBIOS_v1.2.md)** - Cambios técnicos de seguridad
   - Recuperación de contraseña
   - Perfil de usuario
   - Historial de login
   - Protección contra fuerza bruta
   - Timeout de sesión
   - Framework 2FA

7. **[RESUMEN_v1.2.md](RESUMEN_v1.2.md)** - Resumen en español para usuarios
   - Qué cambió en v1.2.0
   - Cómo usar las nuevas features
   - Ejemplos prácticos
   - Preguntas frecuentes

### Para Desarrolladores

#### 🛠️ Técnico

8. **[SPEC.md](SPEC.md)** - Especificación técnica
   - Requisitos del sistema
   - Arquitectura
   - Base de datos
   - Seguridad
   - API (próxima)

9. **[CAMBIOS_v1.1.md](CAMBIOS_v1.1.md)** - Cambios de versión v1.1.0
   - Qué es nuevo en v1.1.0
   - Archivos nuevos
   - Correcciones de bugs
   - Changelog técnico

8. **[ROADMAP.md](ROADMAP.md)** - Hoja de ruta
   - Próximas versiones
   - Timeline
   - Presupuesto
   - Equipo necesario

---

## 🗂️ Estructura de Carpetas

```
ferrepro/
├── 📋 Documentación
│   ├── INICIO_RAPIDO.md          ← Empieza aquí
│   ├── README.md                  ← Info general
│   ├── MANUAL_DE_USO.md           ← Guía completa
│   ├── GUIA_USUARIOS.md           ← Gestión de usuarios
│   ├── REFERENCIA_RAPIDA.md       ← Atajo rápido
│   ├── SPEC.md                    ← Técnico
│   ├── CAMBIOS_v1.1.md            ← Cambios
│   ├── ROADMAP.md                 ← Futuro
│   └── DOCUMENTACION.md           ← Este archivo
│
├── public/
│   └── index.php                  ← Enrutador principal
│
├── class/
│   ├── Database.php               ← Base de datos
│   ├── Validator.php              ← Validaciones
│   └── Flash.php                  ← Notificaciones
│
├── views/
│   ├── layouts/
│   │   └── main.php               ← Template principal
│   ├── auth/
│   │   └── login.php
│   ├── dashboard.php
│   ├── products/
│   ├── clients/
│   ├── categories/
│   ├── providers/
│   ├── sales/
│   ├── receivable/
│   ├── pos/
│   ├── reports/
│   ├── users/
│   ├── settings/
│   └── ...
│
├── storage/
│   └── ferrepro.db                ← Base de datos SQLite
│
└── actions/
    └── helpers.php                ← Funciones auxiliares
```

---

## 🎯 Guía Rápida por Rol

### Administrador
1. Lee: [INICIO_RAPIDO.md](INICIO_RAPIDO.md)
2. Lee: [MANUAL_DE_USO.md](MANUAL_DE_USO.md)
3. Lee: [GUIA_USUARIOS.md](GUIA_USUARIOS.md)
4. Para desarrollo: [SPEC.md](SPEC.md)

### Gerente
1. Lee: [INICIO_RAPIDO.md](INICIO_RAPIDO.md)
2. Lee: [MANUAL_DE_USO.md](MANUAL_DE_USO.md) - Secciones de reportes y configuración
3. Referencia rápida: [REFERENCIA_RAPIDA.md](REFERENCIA_RAPIDA.md)

### Vendedor
1. Lee: [INICIO_RAPIDO.md](INICIO_RAPIDO.md)
2. Lee sección POS: [MANUAL_DE_USO.md#punto-de-venta-pos](MANUAL_DE_USO.md)
3. Referencia rápida: [REFERENCIA_RAPIDA.md](REFERENCIA_RAPIDA.md)

### Desarrollador
1. Lee: [SPEC.md](SPEC.md)
2. Lee: [CAMBIOS_v1.1.md](CAMBIOS_v1.1.md)
3. Lee: [ROADMAP.md](ROADMAP.md)
4. Código fuente en `class/` y `views/`

---

## 🔍 Búsqueda Rápida

### Problema: No puedo iniciar sesión
→ [MANUAL_DE_USO.md#inicio-de-sesión](MANUAL_DE_USO.md)

### Problema: No veo datos de productos
→ [MANUAL_DE_USO.md#gestión-de-productos](MANUAL_DE_USO.md)

### Problema: Quiero crear un usuario
→ [GUIA_USUARIOS.md#cómo-crear-un-nuevo-usuario](GUIA_USUARIOS.md)

### Problema: Cómo hago una venta
→ [MANUAL_DE_USO.md#punto-de-venta-pos](MANUAL_DE_USO.md)

### Pregunta: ¿Cuáles son mis permisos?
→ [GUIA_USUARIOS.md#tabla-de-permisos-por-rol](GUIA_USUARIOS.md)

### Pregunta: ¿Qué viene próximo?
→ [ROADMAP.md](ROADMAP.md)

### Pregunta: ¿Cómo funciona la BD?
→ [SPEC.md#base-de-datos](SPEC.md)

---

## 📱 Dispositivos Recomendados

- **Desktop:** 1366x768 o superior
- **Tablet:** iPad Air o similar
- **Móvil:** Solo para consulta (próxima app nativa)

---

## 🔐 Credenciales de Prueba

| Campo | Valor |
|-------|-------|
| **Email** | admin@ferrepro.com |
| **Contraseña** | admin123 |
| **Rol** | Administrador |

---

## 📊 Contenido de Cada Documento

| Documento | Páginas | Temas | Para |
|-----------|---------|-------|------|
| INICIO_RAPIDO.md | 5 | Acceso, primeros pasos | Todos |
| README.md | 8 | General, problemas | Todos |
| MANUAL_DE_USO.md | 35+ | Módulos completos | Usuarios |
| GUIA_USUARIOS.md | 20 | Gestión de usuarios | Admin |
| REFERENCIA_RAPIDA.md | 15 | Atajos, checklist | Todos |
| SPEC.md | 25+ | Técnico, arquitectura | Dev |
| CAMBIOS_v1.1.md | 12 | Nuevas features | Dev |
| ROADMAP.md | 20 | Futuro del proyecto | PM/Dev |

---

## 🎓 Ruta de Aprendizaje Recomendada

### Día 1: Iniciación
1. Leer: INICIO_RAPIDO.md
2. Iniciar sesión
3. Explorar Dashboard

### Día 2: Operación Básica
1. Leer: secciones principales de MANUAL_DE_USO.md
2. Crear 3 productos de prueba
3. Realizar una venta en POS

### Día 3: Profundización
1. Leer: Secciones avanzadas de MANUAL_DE_USO.md
2. Crear clientes
3. Ver reportes

### Día 4: Administración
1. Leer: GUIA_USUARIOS.md (si eres admin)
2. Crear nuevos usuarios
3. Configurar parámetros

### Día 5: Optimización
1. Leer: REFERENCIA_RAPIDA.md
2. Usar atajos
3. Optimizar flujo de trabajo

---

## 🤝 Soporte

### Para Usuarios
- Consulta el [MANUAL_DE_USO.md](MANUAL_DE_USO.md)
- Busca en [REFERENCIA_RAPIDA.md](REFERENCIA_RAPIDA.md)
- Pregunta en el chat interno (próximo)

### Para Administradores
- Lee [GUIA_USUARIOS.md](GUIA_USUARIOS.md)
- Consulta [SPEC.md](SPEC.md) para técnico
- Revisa [CAMBIOS_v1.1.md](CAMBIOS_v1.1.md)

### Para Desarrolladores
- Estudia [SPEC.md](SPEC.md)
- Revisa [ROADMAP.md](ROADMAP.md)
- Contribuye siguiendo estándares

---

## 📝 Cómo Contribuir

Para mejorar la documentación:
1. Crea un issue
2. Sugiere cambios
3. Envía pull request

---

## 📅 Versiones Documentadas

- v1.0.0 - MVP (inicial)
- v1.1.0 - Usuarios y validaciones (actual)
- v1.2.0+ - En roadmap

---

## 📞 Información de Contacto

**Soporte:** support@ferrepro.local
**Desarrollo:** dev@ferrepro.local
**Reportar Bug:** bugs@ferrepro.local

---

## ✨ Lo Que Necesitas Saber

✅ Todos los documentos están en Markdown
✅ Puedes abrirlos con cualquier editor de texto
✅ Se actualizan cada versión
✅ Están en español
✅ Incluyen ejemplos prácticos

---

**Última actualización:** Abril 2024
**Versión documentada:** 1.1.0
**Total de documentos:** 8 archivos
**Total de páginas:** 150+

¡Bienvenido a FerrePro! 🎉
