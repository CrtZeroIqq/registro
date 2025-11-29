# Sistema de Registro - Cumbre Nodo Bioceánico 2025

## 📋 Descripción General

Sistema completo de gestión de registros y asistencia para el evento **"Cumbre Nodo Bioceánico 2025"**, realizado en Arica, Chile del 26 al 28 de noviembre de 2025. El evento se enfoca en la integración macrozonal andina y el desarrollo en la región bioceánica.

### Objetivo del Sistema
- Gestionar registros de participantes (público general e institucional INACAP)
- Generar y enviar códigos QR únicos por email
- Controlar asistencia mediante escaneo de códigos QR
- Rastrear apertura de emails
- Generar estadísticas y reportes del evento

---

## 🏗️ Arquitectura del Sistema

### Tecnologías Principales

**Backend:**
- **PHP 7.4+** - Lenguaje principal del servidor
- **MySQL/MariaDB** - Base de datos relacional
- **Apache** - Servidor web con soporte .htaccess

**Frontend:**
- **HTML5** - Estructura de páginas
- **CSS3** - Diseño responsive (Flexbox/Grid)
- **JavaScript (Vanilla)** - Interactividad sin frameworks
- **Chart.js 4.4.0** - Visualización de gráficos y estadísticas

**Librerías PHP:**
- **PHPMailer** - Envío de emails vía SMTP
- **phpqrcode** - Generación de códigos QR en formato PNG

**Librerías JavaScript:**
- **html5-qrcode 2.3.8** - Escaneo de códigos QR con cámara

---

## 📁 Estructura del Proyecto

```
/home/user/registro/
│
├── 📄 Formularios de Registro
│   ├── registro.html (36KB)          # Formulario principal de registro
│   └── registro_inacap.html (5.4KB)  # Formulario institucional INACAP
│
├── 🔧 Backend - Procesamiento
│   ├── guardar_registro.php (260 líneas)        # Procesa registro general
│   ├── guardar_registro_inacap.php (172 líneas) # Procesa registro INACAP
│   ├── asistencia.php (338 líneas)              # Interface de control de asistencia
│   ├── scanner.php (318 líneas)                 # Escáner QR con cámara
│   ├── track.php (52 líneas)                    # Pixel de rastreo de emails
│   └── test_mail.php                            # Pruebas de envío de email
│
├── 📧 Sistema de Envío Masivo
│   ├── envio_masivo.php (473 líneas)            # Envío masivo de emails con QR
│   ├── enviar_qr_masivo.php (200 líneas)        # Reenvío masivo de QR
│   ├── dashboard_envios.html (17KB)             # Monitor de progreso de envíos
│   └── estadisticas_apertura.html (7.5KB)       # Estadísticas de apertura
│
├── 📊 Dashboard de Estadísticas (NUEVO)
│   ├── dashboard_informe.html                   # Dashboard principal de estadísticas
│   └── dashboard_informe_demo.html              # Versión demo con datos de ejemplo
│
├── 🔌 API REST
│   ├── api/registrar_asistencia.php (139 líneas)      # Registra asistencia
│   ├── api/buscar_personas.php (69 líneas)            # Búsqueda de registros
│   ├── api/estadisticas_dashboard.php                 # Estadísticas completas
│   └── api/estadisticas_dashboard_demo.php            # Estadísticas demo
│
├── 📦 Dependencias
│   ├── PHPMailer/                    # Librería de envío de emails
│   │   ├── src/PHPMailer.php
│   │   ├── src/SMTP.php
│   │   └── src/Exception.php
│   └── phpqrcode/                    # Librería de generación QR
│       └── qrlib.php
│
├── 🖼️ Recursos
│   ├── qrcodes/                      # Códigos QR generados (322KB)
│   ├── logo1.png                     # Logo del evento
│   ├── logos2.png                    # Logo alternativo
│   └── sounds/                       # Sonidos de feedback
│       ├── success.mp3
│       └── error.mp3
│
├── 📅 Archivos de Calendario
│   ├── Nodo_Bioceanico_2025.ics     # Evento para calendarios
│   └── Cumbre_Bioceanico_2025.ics   # Formato alternativo
│
├── 📝 Logs y Estado
│   ├── email_tracking.log (93KB)     # Registro de aperturas de email
│   ├── envio_detallado.log (83KB)   # Log detallado de envíos
│   └── estado_envio.json             # Estado en tiempo real de envíos
│
└── 🔒 Configuración
    ├── index.html                    # Página de acceso restringido
    └── .htaccess                     # Configuración Apache

Total: ~2,814 líneas de código PHP/JS/HTML
```

---

## 🗄️ Base de Datos

### Configuración de Conexión

```php
$servername = "localhost";
$username   = "seidev";
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = "registro_evento";
```

### Esquema de Tablas

#### 1️⃣ Tabla: `registros`
Almacena registros del público general.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT(11) | PK, AUTO_INCREMENT |
| `nombre` | VARCHAR(100) | Nombre del participante |
| `apellido` | VARCHAR(100) | Apellido del participante |
| `email` | VARCHAR(150) | Email (obligatorio) |
| `telefono` | VARCHAR(50) | Teléfono de contacto |
| `pais` | VARCHAR(80) | País de origen |
| `documento` | VARCHAR(80) | Número de documento/RUT |
| `empresa` | VARCHAR(150) | Nombre de la empresa |
| `cargo` | VARCHAR(100) | Cargo en la empresa |
| `tamano` | VARCHAR(100) | Tamaño de empresa (1-10, 11-50, etc.) |
| `sector` | VARCHAR(100) | Sector económico |
| `website` | VARCHAR(150) | Sitio web de la empresa |
| `intereses` | TEXT | Temas de interés (separados por comas) |
| `objetivos` | TEXT | Objetivos de participación |
| `rueda` | VARCHAR(100) | Participación en rueda de negocios |
| `fuente` | VARCHAR(100) | Cómo se enteró del evento |
| `codigo_registro` | VARCHAR(30) | Código QR único (formato: REG + timestamp + random) |
| `fecha` | TIMESTAMP | Fecha de registro (DEFAULT CURRENT_TIMESTAMP) |
| `email_abierto` | TINYINT(1) | Si abrió el email (0/1) |
| `fecha_apertura` | DATETIME | Fecha/hora de apertura del email |

**Índices:**
- PRIMARY KEY: `id`
- UNIQUE: `codigo_registro`

#### 2️⃣ Tabla: `registros_inacap`
Registros institucionales de INACAP.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT(11) | PK, AUTO_INCREMENT |
| `nombre` | VARCHAR(255) | Nombre completo |
| `rut` | VARCHAR(20) | RUT del participante |
| `carrera` | VARCHAR(255) | Carrera o programa |
| `email` | VARCHAR(255) | Email institucional |
| `tipo_participante` | ENUM('alumno','docente','administrativo') | Tipo |
| `codigo` | VARCHAR(50) | Código QR único |
| `fecha_registro` | TIMESTAMP | Fecha de registro |

#### 3️⃣ Tabla: `asistencias`
Control de asistencia por día del evento.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT(11) | PK, AUTO_INCREMENT |
| `codigo` | VARCHAR(50) | Código del participante |
| `dia_evento` | DATE | Fecha del día del evento (2025-11-26/27/28) |
| `evento` | VARCHAR(100) | Nombre del evento (default: "Nodo Bioceánico 2025") |
| `tipo_asistente` | ENUM('general','inacap') | Tipo de participante |
| `nombre_completo` | VARCHAR(255) | Nombre del asistente |
| `institucion` | VARCHAR(255) | Empresa o institución |
| `email` | VARCHAR(255) | Email del asistente |
| `fecha_hora` | TIMESTAMP | Momento exacto del registro |

**Índices:**
- PRIMARY KEY: `id`
- UNIQUE: `unique_codigo_dia` (codigo + dia_evento) - Previene registros duplicados por día
- INDEX: `idx_fecha` (fecha_hora)
- INDEX: `idx_evento` (evento)

#### 4️⃣ Tabla: `blocked_attempts`
Log de intentos de registro bloqueados (blacklist).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id` | INT(11) | PK, AUTO_INCREMENT |
| `email` | VARCHAR(255) | Email bloqueado |
| `ip` | VARCHAR(45) | Dirección IP |
| `user_agent` | TEXT | Navegador/dispositivo |
| `reason` | VARCHAR(255) | Razón del bloqueo |
| `created_at` | TIMESTAMP | Fecha del intento |

---

## 🔄 Flujos Principales del Sistema

### 1. Flujo de Registro

```
┌─────────────────┐
│ Usuario accede  │
│ registro.html   │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│ Completa formulario     │
│ (nombre, email, país...) │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────────────┐
│ guardar_registro.php            │
│ 1. Valida datos obligatorios    │
│ 2. Verifica blacklist           │
│ 3. Genera código único REG...   │
│ 4. Inserta en BD                │
│ 5. Genera QR code (PNG)         │
│ 6. Envía email con QR y .ics    │
└────────┬────────────────────────┘
         │
         ▼
┌──────────────────────┐
│ Email enviado        │
│ - HTML con diseño    │
│ - QR adjunto         │
│ - Calendario .ics    │
│ - Pixel de tracking  │
└──────────────────────┘
```

**Formato de Código QR:** `REG[YYYYMMDDHHmmss][XXX]`
- Ejemplo: `REG20251120143022847`
- YYYYMMDDHHmmss: Timestamp del registro
- XXX: Número aleatorio 100-999

### 2. Flujo de Envío Masivo

```
┌──────────────────────┐
│ Admin accede         │
│ envio_masivo.php     │
└──────────┬───────────┘
           │
           ▼
┌────────────────────────────┐
│ Consulta registros sin QR  │
│ o para reenvío             │
└──────────┬─────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Procesa en lotes             │
│ (control de rate limiting)   │
│                              │
│ Por cada registro:           │
│ 1. Genera/recupera QR        │
│ 2. Prepara email HTML        │
│ 3. Adjunta QR y calendario   │
│ 4. Envía vía SMTP            │
│ 5. Log detallado             │
│ 6. Actualiza estado (JSON)   │
└──────────┬───────────────────┘
           │
           ▼
┌────────────────────────────┐
│ Dashboard monitorea        │
│ progreso en tiempo real    │
│ (dashboard_envios.html)    │
└────────────────────────────┘
```

**Configuración SMTP:**
- Servidor: `10.24.254.104`
- Puerto: `587` (STARTTLS)
- Usuario: `contacto@bioceanicocentral.cl`

### 3. Flujo de Tracking de Emails

```
┌──────────────────────┐
│ Usuario recibe email │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────────────┐
│ Cliente email carga          │
│ pixel invisible:             │
│ <img src="track.php?c=...">  │
└──────────┬───────────────────┘
           │
           ▼
┌────────────────────────────────┐
│ track.php                      │
│ 1. Recibe código de registro   │
│ 2. Valida en BD                │
│ 3. UPDATE email_abierto = 1    │
│ 4. Guarda fecha_apertura       │
│ 5. Log en email_tracking.log   │
│ 6. Retorna GIF 1x1 transparente│
└────────────────────────────────┘
```

**Formato de Log:**
```json
{
  "codigo": "REG20251120143022847",
  "nombre": "Juan Pérez",
  "email": "juan@example.com",
  "timestamp": "2025-11-21 09:30:15",
  "ip": "192.168.1.100",
  "user_agent": "Mozilla/5.0..."
}
```

### 4. Flujo de Control de Asistencia

#### Opción A: Escaneo QR Automático

```
┌───────────────────────┐
│ Participante llega    │
│ con QR impreso/móvil  │
└──────────┬────────────┘
           │
           ▼
┌───────────────────────────────┐
│ Staff abre scanner.php        │
│ (activa cámara con            │
│  html5-qrcode library)        │
└──────────┬────────────────────┘
           │
           ▼
┌──────────────────────────────┐
│ Escanea código QR            │
│ REG20251120143022847         │
└──────────┬───────────────────┘
           │
           ▼
┌───────────────────────────────────┐
│ api/registrar_asistencia.php      │
│ 1. Valida código en registros     │
│ 2. Verifica no duplicado del día  │
│ 3. INSERT en asistencias          │
│ 4. Reproduce success.mp3          │
│ 5. Muestra confirmación visual    │
└───────────────────────────────────┘
```

#### Opción B: Búsqueda Manual

```
┌──────────────────────┐
│ Staff abre modal de  │
│ búsqueda en scanner  │
└──────────┬───────────┘
           │
           ▼
┌────────────────────────────┐
│ Escribe nombre/empresa     │
│ api/buscar_personas.php    │
└──────────┬─────────────────┘
           │
           ▼
┌────────────────────────────┐
│ Selecciona participante    │
│ del listado (máx 30)       │
└──────────┬─────────────────┘
           │
           ▼
┌────────────────────────────┐
│ Registra asistencia        │
│ (mismo proceso que QR)     │
└────────────────────────────┘
```

### 5. Flujo de Dashboard de Estadísticas

```
┌─────────────────────────┐
│ Admin accede            │
│ dashboard_informe.html  │
└──────────┬──────────────┘
           │
           ▼
┌──────────────────────────────────┐
│ Carga datos via AJAX             │
│ api/estadisticas_dashboard.php   │
└──────────┬───────────────────────┘
           │
           ▼
┌────────────────────────────────────┐
│ API ejecuta 12 consultas SQL:     │
│ 1. Stats generales                │
│ 2. Stats asistencia               │
│ 3. Distribución por países        │
│ 4. Distribución por sectores      │
│ 5. Tamaños de empresa             │
│ 6. Top cargos                     │
│ 7. Análisis de intereses          │
│ 8. Fuentes de registro            │
│ 9. Evolución temporal             │
│ 10. Top empresas                  │
│ 11. Rueda de negocios             │
│ 12. Registros INACAP              │
└──────────┬─────────────────────────┘
           │
           ▼
┌────────────────────────────────┐
│ Dashboard renderiza:           │
│ - 8 tarjetas de métricas       │
│ - 6 gráficos Chart.js          │
│ - 3 tablas de datos            │
│ - Funciones exportar/imprimir  │
└────────────────────────────────┘
```

---

## 🌐 APIs y Endpoints

### 1. `api/registrar_asistencia.php`

**Método:** POST
**Descripción:** Registra la asistencia de un participante a un día específico del evento.

**Request Body (JSON):**
```json
{
  "codigo": "REG20251120143022847",
  "dia_evento": "2025-11-26",
  "tipo_asistente": "general"
}
```

**Response (Éxito):**
```json
{
  "status": "ok",
  "message": "Asistencia registrada exitosamente",
  "data": {
    "nombre": "Juan Pérez González",
    "empresa": "Empresa XYZ",
    "dia": "26/11/2025"
  }
}
```

**Response (Error - Ya registrado):**
```json
{
  "status": "error",
  "message": "Ya existe un registro de asistencia para este participante en esta fecha"
}
```

**Response (Error - No encontrado):**
```json
{
  "status": "error",
  "message": "No se encontró ningún registro con ese código"
}
```

### 2. `api/buscar_personas.php`

**Método:** GET
**Parámetros:** `q` (query string)
**Descripción:** Busca participantes por nombre o apellido.

**Ejemplo:**
```
GET /api/buscar_personas.php?q=juan
```

**Response:**
```json
[
  {
    "nombre": "Juan",
    "apellido": "Pérez",
    "empresa": "Empresa ABC",
    "codigo_registro": "REG20251120143022847"
  },
  {
    "nombre": "Juana",
    "apellido": "González",
    "empresa": "Empresa XYZ",
    "codigo_registro": "REG20251120150033124"
  }
]
```

**Límite:** Máximo 30 resultados

### 3. `api/estadisticas_dashboard.php`

**Método:** GET
**Descripción:** Retorna estadísticas completas del evento.

**Response Structure:**
```json
{
  "general": {
    "total_registros": 162,
    "emails_unicos": 160,
    "paises_total": 8,
    "emails_abiertos": 145,
    "emails_sin_abrir": 17,
    "tasa_apertura": 89.5
  },
  "asistencia": {
    "total_asistentes": 128,
    "dia_26": 98,
    "dia_27": 112,
    "dia_28": 87,
    "inacap": 45,
    "general": 83,
    "tasa_asistencia": 79.0
  },
  "paises": [
    {"pais": "Chile", "cantidad": 98, "porcentaje": 60.5},
    {"pais": "Argentina", "cantidad": 28, "porcentaje": 17.3}
  ],
  "sectores": [...],
  "tamanos": [...],
  "cargos": [...],
  "intereses": [...],
  "fuentes": [...],
  "fechas_registro": [...],
  "empresas": [...],
  "rueda": [...],
  "inacap": {"total_inacap": 45}
}
```

### 4. `obtener_estadisticas.php`

**Método:** GET
**Descripción:** Retorna estadísticas básicas de apertura de emails.

**Response:**
```json
{
  "total": 162,
  "abiertos": 145,
  "sin_abrir": 17,
  "tasa_apertura": 89.5,
  "registros": [
    {
      "codigo": "REG20251120143022847",
      "nombre": "Juan",
      "apellido": "Pérez",
      "email": "juan@example.com",
      "abierto": 1,
      "fecha_apertura": "21/11/2025 09:30"
    }
  ]
}
```

---

## 🎨 Características Principales

### 1. Sistema de Códigos QR

**Generación:**
- Librería: `phpqrcode`
- Formato: PNG
- Nivel de corrección: `QR_ECLEVEL_L`
- Almacenamiento: `/qrcodes/`
- Nombre de archivo: `{codigo}.png`

**Estructura del Código:**
```
REG + [timestamp 14 dígitos] + [random 3 dígitos]
REG 20251120143022 847
    └──────┬──────┘ └┬┘
       Fecha/hora   Random
```

### 2. Envío de Emails con Diseño HTML

**Características:**
- Templates HTML responsive
- Imágenes embebidas (logo)
- QR code adjunto
- Archivo .ics para calendario
- Pixel de tracking invisible
- Diseño compatible con clientes de email

**Estructura del Email:**
```html
<!DOCTYPE html>
<html>
<body style="background: linear-gradient(...)">
  <div style="max-width: 600px; margin: 0 auto;">
    <img src="cid:logo" />
    <h1>¡Bienvenido a la Cumbre!</h1>
    <p>Tu código de registro: <strong>{CODIGO}</strong></p>
    <img src="cid:qrcode" />
    <p>Evento: 26-28 Noviembre 2025, Arica</p>
  </div>
  <!-- Pixel de tracking -->
  <img src="https://bioceanicocentral.cl/registro/track.php?c={CODIGO}"
       width="1" height="1" style="display:none" />
</body>
</html>
```

### 3. Tracking de Apertura de Emails

**Método:** Pixel tracking (imagen 1x1)

**Funcionamiento:**
1. Email incluye imagen invisible: `track.php?c={codigo}`
2. Cuando el usuario abre el email, el cliente carga la imagen
3. `track.php` recibe la petición
4. Actualiza `email_abierto = 1` en la BD
5. Guarda timestamp en `fecha_apertura`
6. Registra en log: IP, User-Agent, timestamp
7. Retorna GIF transparente 1x1

**Limitaciones:**
- Solo funciona si el cliente permite imágenes
- Algunos clientes bloquean tracking pixels
- No detecta lecturas sin imágenes

### 4. Control de Asistencia Multicanal

**Canal 1: Escaneo QR con Cámara**
- Librería: `html5-qrcode 2.3.8`
- Soporte: Cámaras frontales y traseras
- Feedback: Visual + sonoro (success.mp3/error.mp3)
- Validación en tiempo real

**Canal 2: Búsqueda Manual**
- Modal con búsqueda en tiempo real
- Autocomplete por nombre/apellido/empresa
- Límite 30 resultados
- Selección con un clic

**Validaciones:**
- Código válido en BD
- No duplicado en el mismo día (constraint UNIQUE)
- Tipo de participante correcto (general/inacap)

### 5. Dashboard de Estadísticas Avanzado

**8 Métricas Principales:**
1. Total de registros
2. Tasa de apertura de emails
3. Tasa de asistencia
4. Países representados
5. Asistencia día 26
6. Asistencia día 27
7. Asistencia día 28
8. Registros INACAP

**6 Gráficos Interactivos:**
1. **Registros por país** - Barras horizontales
2. **Distribución por sector** - Dona (doughnut)
3. **Tamaño de empresas** - Circular (pie)
4. **Asistencia por día** - Barras verticales
5. **Principales intereses** - Barras horizontales
6. **Evolución temporal** - Línea con área

**3 Tablas de Datos:**
1. Top 15 empresas participantes
2. Top 10 cargos más frecuentes
3. Fuentes de registro con barras de progreso

**Funciones:**
- 🖨️ Imprimir informe (window.print)
- 📥 Exportar a CSV con todos los datos
- 🔄 Actualización automática desde API
- 📱 Diseño responsive

### 6. Sistema de Blacklist

**Configuración:**
```php
$blacklist = [
    'email@bloqueado.com',
    '@dominio-bloqueado.com',  // Bloquea todo el dominio
];
```

**Funcionamiento:**
- Verifica email contra lista negra
- Soporta dominios completos (@example.com)
- Soporta emails individuales
- Registra intentos en `blocked_attempts`
- Respuesta genérica (no revela el bloqueo)
- HTTP 400 Bad Request

### 7. Calendario (.ics) Automático

**Formato:** iCalendar (RFC 5545)

**Contenido:**
```
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Nodo Bioceánico//ES
BEGIN:VEVENT
UID:nodo-bioceanico-2025@bioceanicocentral.cl
DTSTAMP:20251120T143000Z
DTSTART:20251126T090000
DTEND:20251128T180000
SUMMARY:Cumbre Nodo Bioceánico 2025
LOCATION:Arica, Chile
DESCRIPTION:Evento de integración regional...
END:VEVENT
END:VCALENDAR
```

**Compatibilidad:**
- Google Calendar
- Outlook
- Apple Calendar
- Thunderbird

---

## 🔐 Seguridad

### Medidas Implementadas

1. **SQL Injection Prevention**
   - Uso de prepared statements en todas las consultas
   - Binding de parámetros con `bind_param()`

2. **Validación de Datos**
   - Campos obligatorios verificados
   - Sanitización de inputs
   - Validación de formato de email

3. **Blacklist de Emails**
   - Control de usuarios no deseados
   - Log de intentos bloqueados
   - Respuestas genéricas (no revelan bloqueo)

4. **Protección SMTP**
   - Conexión STARTTLS (puerto 587)
   - Credenciales encriptadas en tránsito
   - Manejo de certificados auto-firmados

5. **Control de Acceso**
   - Página de acceso restringido (index.html)
   - Sin autenticación implementada (⚠️ PENDIENTE)

### ⚠️ Consideraciones de Seguridad

**CRÍTICO - Pendiente de Implementar:**
- ✗ No hay sistema de autenticación para admin
- ✗ Credenciales de BD hardcodeadas en archivos PHP
- ✗ No hay protección CSRF en formularios
- ✗ Falta rate limiting en endpoints API
- ✗ Sin validación de tipos MIME en archivos

**Recomendaciones:**
1. Implementar sistema de login para panel admin
2. Mover credenciales a archivo .env
3. Agregar tokens CSRF en formularios
4. Implementar rate limiting (ej: 100 req/min)
5. Validar archivos subidos si se implementa upload

---

## 📊 Métricas del Sistema

### Datos de Producción (Última Actualización)

- **Total de registros:** ~162
- **Países representados:** 8
- **Tasa de apertura emails:** ~89.5%
- **Códigos QR generados:** ~162
- **Emails enviados exitosamente:** 100%
- **Tamaño de logs:** ~176KB

### Rendimiento

**Tiempos estimados:**
- Registro individual: < 2 segundos
- Generación QR: < 0.5 segundos
- Envío email: ~1-2 segundos
- Escaneo QR: < 1 segundo
- Carga dashboard: < 3 segundos

**Concurrencia:**
- Envíos masivos: Procesamiento secuencial (sin límite de rate)
- API asistencia: Soporta múltiples requests simultáneos
- Dashboard: Optimizado para consultas concurrentes

---

## 🚀 Deployment y Configuración

### Requisitos del Servidor

**Software:**
- PHP >= 7.4
- MySQL/MariaDB >= 5.7
- Apache con mod_rewrite
- Extensiones PHP: mysqli, gd, mbstring

**Configuración PHP Recomendada:**
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
display_errors = Off (en producción)
error_reporting = E_ALL
```

### Instalación

1. **Clonar repositorio:**
```bash
git clone https://github.com/CrtZeroIqq/registro.git
cd registro
```

2. **Configurar base de datos:**
```sql
CREATE DATABASE registro_evento CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. **Importar esquema:**
Las tablas se crean automáticamente en el primer uso (ver código en guardar_registro.php)

4. **Configurar permisos:**
```bash
chmod 775 qrcodes/
chown www-data:www-data qrcodes/
chmod 644 *.log
```

5. **Configurar SMTP:**
Editar credenciales en `envio_masivo.php`:
```php
$mail->Host       = '10.24.254.104';
$mail->SMTPAuth   = true;
$mail->Username   = 'contacto@bioceanicocentral.cl';
$mail->Password   = '[PASSWORD]';
$mail->Port       = 587;
```

6. **Actualizar servidor web:**
```bash
# Desde repositorio a producción
cp -r /home/user/registro/* /var/www/html/registro/
# O hacer pull directamente en producción:
cd /var/www/html/registro
git pull origin main
```

### Variables de Entorno (Recomendado)

Crear archivo `.env`:
```env
DB_HOST=localhost
DB_USER=seidev
DB_PASS=Tz9!qE7#Xr2@Lm5$Vp8^
DB_NAME=registro_evento

SMTP_HOST=10.24.254.104
SMTP_USER=contacto@bioceanicocentral.cl
SMTP_PASS=[PASSWORD]
SMTP_PORT=587

BASE_URL=https://www.bioceanicocentral.cl/registro
```

---

## 🛠️ Guía de Desarrollo

### Estructura de un Archivo PHP Típico

```php
<?php
// 1. Configuración inicial
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// 2. Conexión a BD
$servername = "localhost";
$username   = "seidev";
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = "registro_evento";
$conn = new mysqli($servername, $username, $password, $database);

// 3. Captura de datos
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

// 4. Validaciones
if (empty($data['campo_obligatorio'])) {
    echo json_encode(['status' => 'error', 'message' => 'Falta campo']);
    exit;
}

// 5. Preparar statement
$stmt = $conn->prepare("SELECT * FROM tabla WHERE campo = ?");
$stmt->bind_param('s', $data['campo']);
$stmt->execute();

// 6. Procesar resultados
$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

// 7. Respuesta
echo json_encode(['status' => 'ok', 'data' => $rows]);
$conn->close();
?>
```

### Agregar una Nueva Estadística

1. **Editar API:** `api/estadisticas_dashboard.php`
```php
// Agregar consulta SQL
$sql_nueva = "SELECT campo, COUNT(*) as total
              FROM tabla
              GROUP BY campo";
$result = $conn->query($sql_nueva);
$stats_nueva = $result->fetch_assoc();

// Agregar a respuesta JSON
echo json_encode([
    // ... otras stats
    'nueva_stat' => $stats_nueva
]);
```

2. **Editar Dashboard:** `dashboard_informe.html`
```javascript
// En función renderizarMetricas() o crear nueva
const nuevaMetrica = `
    <div class="metric-card primary">
        <div class="icon">📈</div>
        <div class="label">Nueva Métrica</div>
        <div class="value">${statsData.nueva_stat.total}</div>
    </div>
`;

// Agregar al DOM
document.getElementById('metricsGrid').innerHTML += nuevaMetrica;
```

### Agregar Nuevo Gráfico

```javascript
// Crear canvas en HTML
<canvas id="nuevoGrafico"></canvas>

// En JavaScript
const ctx = document.getElementById('nuevoGrafico').getContext('2d');
new Chart(ctx, {
    type: 'bar',  // bar, line, pie, doughnut, radar
    data: {
        labels: statsData.nueva_stat.map(item => item.label),
        datasets: [{
            label: 'Mi Dataset',
            data: statsData.nueva_stat.map(item => item.value),
            backgroundColor: colors.multi
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
```

### Crear Nuevo Endpoint API

1. **Crear archivo:** `api/mi_nuevo_endpoint.php`
```php
<?php
header('Content-Type: application/json');
// Conexión BD...

$stmt = $conn->prepare("SELECT * FROM tabla WHERE condicion = ?");
$stmt->bind_param('s', $_GET['parametro']);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
?>
```

2. **Consumir desde frontend:**
```javascript
fetch('api/mi_nuevo_endpoint.php?parametro=valor')
    .then(response => response.json())
    .then(data => {
        console.log(data);
        // Procesar datos...
    })
    .catch(error => console.error('Error:', error));
```

---

## 📝 Logs y Debugging

### Archivos de Log

1. **`email_tracking.log`** - Aperturas de emails
```json
{"codigo":"REG...","nombre":"Juan","email":"...","timestamp":"...","ip":"..."}
```

2. **`envio_detallado.log`** - Envíos de emails
```
[2025-11-20 14:30:22] ✅ Email enviado a: juan@example.com (REG...)
[2025-11-20 14:30:25] ❌ Error al enviar a: error@example.com - SMTP timeout
```

3. **`estado_envio.json`** - Estado en tiempo real de envío masivo
```json
{
  "total": 162,
  "enviados": 145,
  "errores": 2,
  "porcentaje": 89.5,
  "ultimo_enviado": "juan@example.com",
  "logs": ["Log 1", "Log 2", ...]
}
```

### Debugging

**Habilitar errores PHP (solo desarrollo):**
```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

**Ver logs de Apache:**
```bash
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log
```

**Debug de consultas MySQL:**
```php
if (!$stmt->execute()) {
    error_log("SQL Error: " . $stmt->error);
    die(json_encode(['error' => $stmt->error]));
}
```

**Debug de emails:**
```php
$mail->SMTPDebug = 2;  // 0=off, 1=client, 2=client+server
$mail->Debugoutput = 'html';
```

---

## 🐛 Problemas Comunes y Soluciones

### 1. Error: "Unknown column 'X' in 'SELECT'"

**Causa:** Nombre de columna incorrecto en SQL.

**Solución:**
```bash
# Ver estructura de tabla en phpMyAdmin o:
mysql -u seidev -p registro_evento
DESCRIBE nombre_tabla;
```

### 2. Emails no se envían

**Diagnóstico:**
```php
// En envio_masivo.php, habilitar debug:
$mail->SMTPDebug = 2;
```

**Causas comunes:**
- Puerto SMTP bloqueado (verificar firewall)
- Credenciales incorrectas
- Límite de tasa del servidor SMTP
- Timeout de conexión

**Solución:**
```bash
# Test de conectividad SMTP
telnet 10.24.254.104 587
```

### 3. QR no se genera

**Verificar:**
```bash
# Permisos del directorio
ls -la qrcodes/
chmod 775 qrcodes/
chown www-data:www-data qrcodes/

# Extensión GD de PHP instalada
php -m | grep gd
```

### 4. Dashboard no carga datos

**Pasos:**
1. Abrir consola del navegador (F12)
2. Ver errores de red
3. Verificar respuesta de API:
```bash
curl https://bioceanicocentral.cl/registro/api/estadisticas_dashboard.php
```
4. Ver logs de Apache para errores PHP

### 5. Tracking de emails no funciona

**Verificar:**
- Cliente de email permite imágenes
- URL del pixel es accesible:
```bash
curl https://bioceanicocentral.cl/registro/track.php?c=REG20251120143022847
```
- Logs de Apache muestran peticiones GET a track.php

---

## 📚 Recursos Adicionales

### Documentación de Librerías

- **PHPMailer:** https://github.com/PHPMailer/PHPMailer
- **phpqrcode:** http://phpqrcode.sourceforge.net/
- **Chart.js:** https://www.chartjs.org/docs/latest/
- **html5-qrcode:** https://github.com/mebjas/html5-qrcode

### Referencias RFC

- **iCalendar (RFC 5545):** https://tools.ietf.org/html/rfc5545
- **SMTP (RFC 5321):** https://tools.ietf.org/html/rfc5321

### Testing

**Validador de HTML:**
https://validator.w3.org/

**Test de emails:**
https://www.mail-tester.com/

---

## 👥 Contacto y Soporte

**Proyecto:** Sistema de Registro Nodo Bioceánico 2025
**Repositorio:** https://github.com/CrtZeroIqq/registro
**Rama de desarrollo:** `claude/registration-stats-dashboard-01JWFab3hKnjaZjLh9jK2FPR`

**Email del evento:** contacto@bioceanicocentral.cl
**Sitio web:** https://www.bioceanicocentral.cl

---

## 📄 Licencia

Desarrollado por Patricio Hernández Pavez, para Seid Global Consulting
Licenciado por Seid Global Consulting y Seid Develpment, para uso exclusivo en Registro de Asistentes al Proyecto "Nodo Bioceánico Central", Ejecutado por INACAP.
Noviembre 2025.

---

## ✅ Checklist de Implementación

### Funcionalidades Core
- [x] Formulario de registro público
- [x] Formulario de registro INACAP
- [x] Generación de códigos QR únicos
- [x] Envío de emails con QR
- [x] Tracking de apertura de emails
- [x] Control de asistencia con QR
- [x] Control de asistencia manual
- [x] Dashboard de envíos
- [x] Dashboard de estadísticas
- [x] Exportación de datos a CSV
- [x] Sistema de blacklist
- [x] Calendario .ics automático

### Pendientes / Mejoras Futuras
- [ ] Sistema de autenticación admin
- [ ] Panel de administración completo
- [ ] Exportación a PDF de estadísticas
- [ ] Notificaciones en tiempo real
- [ ] API REST completa con documentación OpenAPI
- [ ] Tests automatizados
- [ ] Migración de credenciales a .env
- [ ] Implementación de CSRF tokens
- [ ] Rate limiting en APIs
- [ ] Backup automático de BD
- [ ] Logs centralizados
- [ ] Monitoreo de uptime

---

**Última actualización:** 28 de Noviembre de 2025
**Versión del dashboard:** 1.0
**Estado:** ✅ Producción

---

*Este documento fue generado para facilitar el mantenimiento y desarrollo futuro del sistema. Para contribuir o reportar issues, usa el repositorio de GitHub.*
