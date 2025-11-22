# 📄 Generador de Lanyards con QR Codes

Sistema para generar PDF con recortes de 6×6 cm que incluyen QR Code, nombre y país de cada participante registrado.

---

## 📋 Descripción

Este sistema genera automáticamente un PDF listo para imprimir con recortes perfectos de **6×6 cm** que se pueden pegar en lanyards (porta credenciales) del evento.

### Contenido de cada recorte:
- ✅ **Código QR** - Para escaneo rápido en el evento
- ✅ **Nombre completo** - Nombre y apellido del participante
- ✅ **País** - País de origen

---

## 🎯 Archivos del Sistema

### 1. **generar_pdf_qr.php**
Script principal que genera el PDF.

**Características:**
- Conecta a la base de datos `registro_evento`
- Obtiene todos los registros ordenados alfabéticamente
- Genera QR codes si no existen
- Crea PDF con recortes de 6×6 cm
- Descarga automáticamente el archivo

### 2. **generar_lanyards.html**
Interfaz web para generar el PDF fácilmente.

**Características:**
- Diseño visual atractivo
- Vista previa de distribución
- Especificaciones del documento
- Instrucciones de uso
- Botón para generar PDF

### 3. **tcpdf/**
Librería PHP para generación de PDFs.

**Versión:** 6.6.2
**Licencia:** LGPL v3
**Sitio web:** https://tcpdf.org/

---

## 🚀 Cómo Usar

### Opción 1: Interfaz Web (Recomendado)

```
https://www.bioceanicocentral.cl/registro/generar_lanyards.html
```

1. Abre la URL en tu navegador
2. Revisa las especificaciones del documento
3. Click en "📄 Generar PDF con QR Codes"
4. El PDF se descargará automáticamente

### Opción 2: Script Directo

```
https://www.bioceanicocentral.cl/registro/generar_pdf_qr.php
```

Ejecuta directamente el script y descarga el PDF.

---

## 📏 Especificaciones Técnicas

### Tamaño de Hoja
- **Formato:** A4 (210 × 297 mm)
- **Orientación:** Vertical (Portrait)

### Recortes
- **Tamaño:** 6×6 cm (60×60 mm)
- **Distribución:** 3 columnas × 4 filas
- **Total por página:** 12 recortes
- **Márgenes:** 15 mm en todos los lados

### Contenido de cada recorte
- **QR Code:** 32×32 mm (centrado)
- **Nombre:** Fuente Helvetica Bold, 10pt
- **País:** Fuente Helvetica Regular, 8pt
- **Borde:** Línea punteada gris (2,2) para facilitar el corte

---

## 🖨️ Guía de Impresión

### Antes de Imprimir

1. **Verifica el PDF:**
   - Abre el PDF generado
   - Revisa que todos los QR se vean correctamente
   - Confirma que nombres y países estén completos

2. **Configuración de Impresora:**
   - **Tamaño de papel:** A4
   - **Escala:** 100% (tamaño real - SIN ajustar a la página)
   - **Orientación:** Vertical
   - **Color:** Blanco y negro (suficiente para QR)
   - **Calidad:** Alta (600 DPI o superior)

3. **Tipo de Papel:**
   - **Recomendado:** Papel couché o cartulina de 200-250g
   - **Mínimo:** Papel bond de 120g
   - **No usar:** Papel común de 75g (muy delgado)

### Durante la Impresión

1. Imprime una página de prueba primero
2. Verifica que las dimensiones sean exactas (6×6 cm)
3. Si los recortes no son exactos, ajusta la escala en la impresora
4. Imprime el resto del documento

---

## ✂️ Guía de Corte

### Herramientas Necesarias

**Opción A - Corte Manual:**
- Tijeras afiladas o cutter
- Regla metálica
- Base de corte (cutting mat)

**Opción B - Corte Profesional:**
- Guillotina de papel
- Cortadora de tarjetas

### Proceso de Corte

1. **Verifica las guías:**
   - Las líneas punteadas marcan dónde cortar
   - Cada recorte debe ser exactamente 6×6 cm

2. **Corta por filas:**
   - Primero corta horizontalmente (4 tiras)
   - Luego corta verticalmente cada tira (3 recortes por tira)

3. **Verifica el tamaño:**
   - Usa una regla para confirmar 6×6 cm
   - Los recortes deben entrar perfectamente en el lanyard

---

## 📦 Ejemplo de Uso Completo

```bash
# 1. Abrir interfaz web
https://bioceanicocentral.cl/registro/generar_lanyards.html

# 2. Generar PDF
Click en "Generar PDF con QR Codes"
Se descarga: QR_Lanyards_2025-11-22_10-30-45.pdf

# 3. Imprimir
- Abrir PDF en Adobe Reader o similar
- Configurar escala 100%
- Imprimir en papel de 200g

# 4. Cortar
- Cortar por las líneas punteadas
- Obtener recortes de 6×6 cm

# 5. Pegar en lanyards
- Adherir cada recorte al lanyard correspondiente
```

---

## 🔍 Detalles del PDF Generado

### Nombre del Archivo
```
QR_Lanyards_YYYY-MM-DD_HH-MM-SS.pdf
```
**Ejemplo:** `QR_Lanyards_2025-11-22_10-30-45.pdf`

### Estructura del Documento

**Página 1:**
```
┌──────┬──────┬──────┐
│  1   │  2   │  3   │  ← Fila 1 (3 recortes)
├──────┼──────┼──────┤
│  4   │  5   │  6   │  ← Fila 2 (3 recortes)
├──────┼──────┼──────┤
│  7   │  8   │  9   │  ← Fila 3 (3 recortes)
├──────┼──────┼──────┤
│ 10   │ 11   │ 12   │  ← Fila 4 (3 recortes)
└──────┴──────┴──────┘
```

**Páginas siguientes:** Misma distribución hasta completar todos los registros.

---

## 📊 Estimación de Páginas

| Registros | Páginas | Tiempo de Impresión* |
|-----------|---------|---------------------|
| 12        | 1       | ~1 minuto           |
| 50        | 5       | ~5 minutos          |
| 100       | 9       | ~9 minutos          |
| 200       | 17      | ~17 minutos         |
| 500       | 42      | ~42 minutos         |

*Estimación con impresora láser a 1 página/minuto

---

## 🐛 Solución de Problemas

### Problema: "Error de conexión a la base de datos"

**Solución:**
1. Verifica que MySQL esté activo
2. Confirma las credenciales en `generar_pdf_qr.php`
3. Verifica que exista la base de datos `registro_evento`

### Problema: "No hay registros en la base de datos"

**Solución:**
1. Verifica que haya registros en la tabla `registros`
2. Ejecuta: `SELECT COUNT(*) FROM registros;`

### Problema: "QR Code no aparece en el PDF"

**Solución:**
1. Verifica que exista la carpeta `/qrcodes/`
2. Verifica permisos de escritura: `chmod 755 qrcodes/`
3. El script genera QR automáticamente si no existen

### Problema: "Los recortes no son exactamente 6×6 cm al imprimir"

**Solución:**
1. Configura la impresora en **escala 100%** (tamaño real)
2. NO uses "Ajustar a la página"
3. Verifica en propiedades de impresión: "Tamaño real" o "Sin escalado"

### Problema: "El PDF pesa mucho"

**Solución:**
- Es normal con muchos registros (cada QR es una imagen)
- **Estimación:** ~50-100 KB por recorte
- 500 registros = ~25-50 MB
- Si es muy grande, considera dividir en varios PDFs

---

## 🔧 Personalización

### Cambiar Tamaño de Recorte

Edita `generar_pdf_qr.php` línea 52-53:

```php
$recorte_ancho = 60;  // 6 cm en mm (cambiar aquí)
$recorte_alto = 60;   // 6 cm en mm (cambiar aquí)
```

### Cambiar Distribución en Página

Edita `generar_pdf_qr.php` línea 54-55:

```php
$columnas = 3;  // Número de columnas (cambiar aquí)
$filas = 4;     // Número de filas (cambiar aquí)
```

### Cambiar Fuente o Tamaño de Texto

Edita `generar_pdf_qr.php` líneas 110-119:

```php
// Nombre
$pdf->SetFont('helvetica', 'B', 10);  // Cambiar tamaño aquí

// País
$pdf->SetFont('helvetica', '', 8);    // Cambiar tamaño aquí
```

### Agregar Logo

1. Guarda tu logo como `logo_evento_small.png` en `/home/user/registro/`
2. El script lo detectará automáticamente
3. Tamaño recomendado: 100×100 px

---

## 📞 Soporte

Si tienes problemas con la generación de PDFs, contacta al administrador del sistema.

**Archivos del sistema:**
- Script principal: `generar_pdf_qr.php`
- Interfaz web: `generar_lanyards.html`
- Librería PDF: `tcpdf/`
- Documentación: `README_LANYARDS.md`

---

## 📄 Licencia

Este sistema utiliza TCPDF (LGPL v3) para la generación de PDFs.

**Desarrollado para:** Nodo Bioceánico Central 2025
**Evento:** 26, 27 y 28 de Noviembre, 2025
**Ubicación:** Arica, Chile

---

**Última actualización:** 2025-11-22
**Versión:** 1.0
