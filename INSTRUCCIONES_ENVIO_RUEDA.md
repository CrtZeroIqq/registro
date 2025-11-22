# 📧 Envío Masivo - Rueda de Negocios B2B

## 📋 Descripción

Sistema de envío masivo de correos electrónicos para recordar a las empresas que manifestaron interés en participar en la **Rueda de Negocios B2B** del Nodo Bioceánico 2025.

---

## 🎯 Archivos Creados

### 1. **envio_rueda_negocios.php**
Script principal que realiza el envío masivo de correos.

**Características:**
- ✅ Envía solo a empresas que dijeron "Sí" a la rueda
- ✅ Correo breve y directo (no extenso)
- ✅ Explica cómo funciona la rueda de negocios
- ✅ Invita a registrarse en el sistema
- ✅ Indica que NO es spam (se envía por interés manifestado)
- ✅ Delays de seguridad (3s entre correos, 45s entre lotes)
- ✅ Control de estado en tiempo real
- ✅ Logs detallados

### 2. **monitor_envio_rueda.html**
Interfaz web para monitorear el envío en tiempo real.

**Características:**
- 📊 Estadísticas en tiempo real
- 📈 Barra de progreso visual
- 📋 Últimos 10 envíos
- ⚠️ Detección y reporte de errores
- 🔄 Actualización automática cada 3 segundos

### 3. **verificar_empresas_rueda.php**
Script auxiliar para verificar cuántas empresas están interesadas.

**Retorna:**
- Total de empresas interesadas en la rueda
- Listado completo con datos
- Porcentaje sobre el total de registros

---

## 🚀 Cómo Usar

### Paso 1: Verificar Empresas Interesadas

Primero, verifica cuántas empresas dijeron "Sí" a la rueda:

```bash
# Opción 1: En el navegador
https://www.bioceanicocentral.cl/registro/verificar_empresas_rueda.php

# Opción 2: Por terminal
curl https://www.bioceanicocentral.cl/registro/verificar_empresas_rueda.php
```

**Respuesta esperada:**
```json
{
  "status": "ok",
  "total_interesados": 45,
  "total_registros": 150,
  "porcentaje": 30,
  "empresas": [
    {
      "id": 1,
      "nombre": "Juan Pérez",
      "email": "juan@empresa.cl",
      "empresa": "Empresa ABC",
      "cargo": "Gerente",
      "rueda": "Si"
    },
    ...
  ]
}
```

### Paso 2: Abrir el Monitor

Abre en tu navegador:

```
https://www.bioceanicocentral.cl/registro/monitor_envio_rueda.html
```

### Paso 3: Iniciar el Envío

1. Haz clic en el botón **"▶️ Iniciar Envío Masivo"**
2. Confirma la acción en el diálogo
3. El proceso iniciará automáticamente

**⏰ Tiempo estimado:**
- ~3 segundos por correo
- Pausas de 45 segundos cada 15 correos
- **Ejemplo:** 60 empresas = ~6-8 minutos

### Paso 4: Monitorear el Progreso

El monitor mostrará en tiempo real:
- ✅ Total enviados
- ❌ Errores detectados
- 📊 Porcentaje de progreso
- 📋 Últimos envíos realizados
- ⚠️ Detalles de errores (si hay)

---

## 📝 Contenido del Correo

El correo enviado incluye:

### Asunto:
```
Rueda de Negocios B2B - Nodo Bioceánico 2025
```

### Contenido (Resumen):

1. **Saludo personalizado** con nombre de la persona
2. **Recordatorio de interés manifestado**
   - "Te contactamos porque manifestaste interés..."
3. **Nota importante**: NO es spam
4. **Explicación breve de cómo funciona:**
   - 15 mesas físicas
   - 4 bloques de 15 minutos
   - Empresas demandantes vs oferentes
   - Reuniones confirmadas cara a cara
5. **Modalidades de participación:**
   - 🏢 Busco Servicios (Demandante)
   - 🤝 Ofrezco Servicios (Oferente)
6. **Botón CTA:** "Registrarme Ahora"
   - Link: `https://www.bioceanicocentral.cl/registro/rueda/views/registro.php`
7. **Detalles del evento:**
   - Fecha: 28 de Noviembre, 2025
   - Horario: 11:00 - 12:15 hrs
   - Lugar: Arica, Chile

---

## 🔍 Archivos de Estado y Logs

### estado_envio_rueda.json
Estado en tiempo real del envío:

```json
{
  "iniciado": "2025-11-22 10:30:00",
  "estado": "enviando",
  "total": 45,
  "enviados": 23,
  "errores": 2,
  "progreso_porcentaje": 55.6,
  "ultimo_envio": "juan@empresa.cl",
  "logs": [...],
  "errores_detalle": [...]
}
```

### envio_rueda_detallado.log
Log completo de todos los envíos:

```
[2025-11-22 10:30:15] === INICIO DEL PROCESO - RUEDA DE NEGOCIOS ===
[2025-11-22 10:30:15] Total de correos a enviar: 45
[2025-11-22 10:30:15] --- Procesando registro ID: 12 ---
[2025-11-22 10:30:15] Preparando envío a: juan@empresa.cl
[2025-11-22 10:30:18] ✓ ENVIADO exitosamente a: juan@empresa.cl
...
```

---

## ⚙️ Configuración Técnica

### Delays de Seguridad:
```php
$DELAY_ENTRE_CORREOS = 3;  // 3 segundos entre cada correo
$LOTE_SIZE = 15;           // 15 correos por lote
$DELAY_ENTRE_LOTES = 45;   // 45 segundos entre lotes
```

### Base de Datos:
```php
$servername = "localhost";
$username   = "seidev";
$password   = 'Tz9!qE7#Xr2@Lm5$Vp8^';
$database   = "registro_evento";
```

### Consulta SQL:
```sql
SELECT * FROM registros
WHERE (LOWER(rueda) = 'si' OR LOWER(rueda) = 'sí')
ORDER BY id
```

### Servidor SMTP:
```php
Host: 10.24.254.104
Port: 587
User: contacto@bioceanicocentral.cl
Pass: @@Bio2025
```

---

## ⚠️ Notas Importantes

### 1. **No Duplicados**
El script NO tiene filtro de emails ya procesados. Si necesitas evitar duplicados, agrega los emails al array `$emails_ya_procesados` dentro del script.

### 2. **Verificar Antes de Enviar**
**SIEMPRE** ejecuta `verificar_empresas_rueda.php` antes de iniciar el envío para confirmar la cantidad de correos a enviar.

### 3. **No Cerrar el Navegador**
Mantén la ventana del monitor abierta durante todo el proceso. Si cierras el navegador, el script seguirá ejecutándose, pero no podrás monitorear el progreso.

### 4. **Revisar Logs**
Después del envío, revisa:
- `estado_envio_rueda.json` - Estado final
- `envio_rueda_detallado.log` - Log completo

### 5. **Errores Comunes**
Si hay errores, generalmente son por:
- Email inválido en la base de datos
- Problemas de conectividad SMTP
- Timeout del servidor

---

## 🔧 Solución de Problemas

### Problema: "No hay empresas interesadas en Rueda de Negocios"

**Solución:**
1. Verifica que haya registros con `rueda = "Si"` en la BD
2. Ejecuta esta query directamente:
```sql
SELECT COUNT(*) FROM registros WHERE LOWER(rueda) = 'si' OR LOWER(rueda) = 'sí';
```

### Problema: "Error al conectar con la base de datos"

**Solución:**
1. Verifica credenciales de BD en el script
2. Confirma que MySQL esté activo
3. Verifica permisos del usuario

### Problema: "Error al enviar correo"

**Solución:**
1. Verifica configuración SMTP
2. Revisa que el servidor de correo esté disponible
3. Confirma credenciales SMTP

---

## 📊 Estadísticas y Reportes

### Después del Envío

1. **Total Enviados:** Verifica en el monitor
2. **Errores:** Revisa la sección de errores
3. **Tasa de Éxito:** Calcula `(enviados / total) * 100`

### Exportar Logs

Para exportar los logs a CSV:
```bash
# Convertir JSON a CSV (puedes usar jq o un script personalizado)
cat estado_envio_rueda.json | jq -r '.logs[] | [.timestamp, .email, .nombre, .status] | @csv' > envio_rueda.csv
```

---

## ✅ Checklist Pre-Envío

Antes de iniciar el envío masivo, verifica:

- [ ] Ejecutaste `verificar_empresas_rueda.php`
- [ ] Confirmaste la cantidad de correos a enviar
- [ ] Revisaste el template del correo
- [ ] Verificaste la URL de registro en el correo
- [ ] Abriste el monitor en el navegador
- [ ] Confirmaste que el servidor SMTP está disponible
- [ ] Tienes tiempo suficiente (no lo hagas con prisa)

---

## 📞 Soporte

Si tienes problemas con el envío, contacta al administrador del sistema.

**Archivos importantes:**
- Script principal: `envio_rueda_negocios.php`
- Monitor: `monitor_envio_rueda.html`
- Verificador: `verificar_empresas_rueda.php`
- Estado: `estado_envio_rueda.json`
- Logs: `envio_rueda_detallado.log`

---

## 📄 Ejemplo de Uso Completo

```bash
# 1. Verificar empresas
curl https://bioceanicocentral.cl/registro/verificar_empresas_rueda.php

# 2. Abrir monitor
# En navegador: https://bioceanicocentral.cl/registro/monitor_envio_rueda.html

# 3. Iniciar envío (desde el monitor)
# Click en "Iniciar Envío Masivo"

# 4. Esperar a que termine

# 5. Revisar logs
tail -f envio_rueda_detallado.log

# 6. Verificar estado final
cat estado_envio_rueda.json
```

---

**Desarrollado para:** Nodo Bioceánico Central 2025
**Evento:** Rueda de Negocios B2B
**Fecha:** 28 de Noviembre, 2025
**Versión:** 1.0
