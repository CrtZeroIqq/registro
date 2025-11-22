# 🚀 Instalación Rápida - Sistema de Lanyards

Sistema completo para generar PDF con QR codes para lanyards del evento.

---

## 📦 Contenido de esta Carpeta

```
sistema_lanyards/
├── instalar.php              ← Script de instalación automática
├── generar_pdf_qr.php        ← Script generador de PDF
├── generar_lanyards.html     ← Interfaz web
├── README_LANYARDS.md        ← Documentación completa
├── INSTALACION.md            ← Este archivo
└── phpqrcode/                ← Librería para generar QR codes (incluida)
```

---

## ⚡ Instalación en 3 Pasos

### Paso 1: Subir al Servidor

Sube toda la carpeta `sistema_lanyards/` a tu servidor en la ubicación deseada.

**Ejemplo:**
```
/home/user/registro/sistema_lanyards/
```

O dentro de tu dominio web:
```
/public_html/sistema_lanyards/
```

### Paso 2: Ejecutar Instalador

Accede a la URL del instalador desde tu navegador:

```
https://www.bioceanicocentral.cl/registro/sistema_lanyards/instalar.php
```

O según tu configuración:
```
https://tu-dominio.com/sistema_lanyards/instalar.php
```

El instalador automáticamente:
- ✅ Verifica la versión de PHP
- ✅ Verifica extensiones necesarias
- ✅ Verifica que phpqrcode esté incluido
- ✅ Descarga e instala TCPDF
- ✅ Verifica permisos de carpetas
- ✅ Verifica configuración de BD

### Paso 3: Configurar Base de Datos

Edita el archivo `generar_pdf_qr.php` (líneas 11-14) con tus credenciales:

```php
$servername = "localhost";      // Tu servidor MySQL
$username   = "tu_usuario";     // Tu usuario de BD
$password   = "tu_contraseña";  // Tu contraseña
$database   = "registro_evento"; // Nombre de tu base de datos
```

---

## ✅ Listo para Usar

Una vez completados los 3 pasos, accede a:

```
https://www.bioceanicocentral.cl/registro/sistema_lanyards/generar_lanyards.html
```

Haz click en "Generar PDF con QR Codes" y descarga el archivo.

---

## 🔒 Seguridad Post-Instalación

**IMPORTANTE:** Después de instalar, elimina el archivo instalador:

```bash
rm instalar.php
```

O por FTP/Panel de control, elimina:
```
sistema_lanyards/instalar.php
```

---

## 📋 Requisitos del Servidor

### Mínimos:
- PHP 7.0 o superior
- MySQL/MariaDB
- Extensiones PHP: mysqli, gd, mbstring, zlib
- 50 MB de espacio en disco (para TCPDF)
- Permisos de escritura en carpeta

### Recomendados:
- PHP 7.4 o superior
- 100 MB de espacio en disco
- 128 MB de memoria PHP

---

## 🐛 Solución de Problemas

### Error: "No se pudo descargar TCPDF"

**Solución por SSH:**
```bash
cd /ruta/a/sistema_lanyards
wget https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.6.2.tar.gz
tar -xzf 6.6.2.tar.gz
mv TCPDF-6.6.2 tcpdf
rm 6.6.2.tar.gz
```

**O descarga manual:**
1. Descarga: https://github.com/tecnickcom/TCPDF/archive/refs/tags/6.6.2.tar.gz
2. Extrae y renombra la carpeta a `tcpdf`
3. Sube la carpeta `tcpdf/` dentro de `sistema_lanyards/`

### Error: "Error de conexión a la base de datos"

**Solución:**
1. Verifica que MySQL esté activo
2. Confirma las credenciales en `generar_pdf_qr.php`
3. Verifica que la base de datos `registro_evento` exista
4. Verifica que la tabla `registros` exista

### Error: "No hay registros en la base de datos"

**Solución:**
Verifica que haya datos en la tabla:
```sql
SELECT COUNT(*) FROM registros;
```

### Error: "No se pueden crear archivos PDF"

**Solución:**
Verifica permisos de escritura:
```bash
chmod 755 /ruta/a/sistema_lanyards
```

---

## 📞 Soporte

Para más información, consulta:
- **Documentación completa:** `README_LANYARDS.md`
- **Configuración de ejemplo:** `config_ejemplo.php`

---

## 🎯 Siguiente Paso

Después de la instalación exitosa:
```
👉 https://tu-dominio.com/sistema_lanyards/generar_lanyards.html
```

¡Genera tu PDF y comienza a preparar los lanyards para el evento!

---

**Desarrollado para:** Nodo Bioceánico Central 2025
**Evento:** 26, 27 y 28 de Noviembre, 2025
**Versión:** 1.0
