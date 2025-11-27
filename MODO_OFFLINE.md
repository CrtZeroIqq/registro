# Sistema de Scanner con Modo Offline

## Descripción General

El sistema de scanner ahora incluye un **modo offline robusto** que permite continuar registrando asistencias incluso cuando se pierde la conexión a internet. Los registros se almacenan localmente y se sincronizan automáticamente cuando se recupera la conexión. Además, incluye **cache local de la base de datos** para permitir búsquedas por nombre sin conexión.

## Características Implementadas

### 1. **Detección Automática de Conexión**
- Monitoreo en tiempo real del estado de la conexión
- Verificación periódica cada 10 segundos mediante ping al servidor
- Detección de eventos del navegador (online/offline)
- Indicador visual del estado de conexión

### 2. **Almacenamiento Local Persistente**
- Los scans se guardan en localStorage del navegador
- Los datos persisten incluso si se cierra el navegador
- Formato de almacenamiento de scans pendientes:
  ```json
  {
    "codigo": "REG20251127123456",
    "dia_evento": "2025-11-27",
    "timestamp": "2025-11-27T15:30:45.123Z"
  }
  ```

### 3. **Cache Local de Base de Datos (NUEVO)**
- Descarga completa de la base de datos de registros al iniciar
- Actualización automática cada 5 minutos cuando hay conexión
- Búsqueda local funcional incluso sin conexión
- Indicador de última actualización de BD
- Fallback automático a cache si falla la búsqueda online
- Almacena código, nombre, empresa y país de todos los registros
- Formato de cache:
  ```json
  {
    "codigo": "REG20251127123456",
    "nombre": "Juan Pérez",
    "empresa": "Empresa ABC",
    "pais": "Chile",
    "tipo": "general"
  }
  ```

### 4. **Sincronización Automática**
- Auto-sincronización de scans cada 30 segundos cuando hay conexión
- Auto-sincronización de BD cada 5 minutos cuando hay conexión
- Sincronización inmediata al recuperar la conexión
- Procesamiento batch de múltiples registros pendientes
- Retry automático con exponential backoff (2s, 4s, 8s)

### 5. **Indicadores Visuales**

#### Estado de Conexión
- **Verde (En línea)**: Conexión normal, los scans se registran inmediatamente
- **Amarillo (Modo Offline)**: Sin conexión, los scans se guardan localmente
- **Azul (Sincronizando)**: Sincronizando registros pendientes

#### Cola de Pendientes
- Aparece automáticamente cuando hay scans pendientes
- Muestra el número de registros esperando sincronización
- Botón para forzar sincronización manual

#### Estado de BD Local (NUEVO)
- Muestra en el modal de búsqueda el estado de la BD local
- Indica número de registros cacheados
- Muestra tiempo transcurrido desde última actualización
- Icono visual: 🌐 (online) o 💾 (offline)

### 6. **Manejo Robusto de Errores**
- Try-catch en todas las operaciones críticas
- Timeout de 10 segundos para operaciones de red
- Fallback automático a modo offline ante errores
- Logs detallados en consola para debugging

## Archivos Modificados/Creados

### Nuevos Archivos
- **`api/sincronizar_asistencias.php`**: API para sincronización batch de múltiples registros
- **`api/obtener_registros.php`**: API para descargar la base de datos completa (NUEVO)

### Archivos Modificados
- **`scanner.php`**: Scanner principal con modo offline completo y cache de BD
- **`MODO_OFFLINE.md`**: Documentación actualizada

## Flujo de Funcionamiento

### Escenario 1: Conexión Normal
```
1. Usuario escanea QR
2. Sistema detecta conexión disponible
3. Envía inmediatamente a la API
4. Muestra resultado (éxito/error)
5. Continúa escaneando
```

### Escenario 2: Sin Conexión
```
1. Usuario escanea QR
2. Sistema detecta que no hay conexión
3. Guarda en localStorage
4. Muestra mensaje "💾 Guardado offline"
5. Incrementa contador de pendientes
6. Continúa escaneando
```

### Escenario 3: Recuperación de Conexión
```
1. Sistema detecta conexión restaurada
2. Cambia indicador a "Sincronizando..."
3. Envía todos los registros pendientes a api/sincronizar_asistencias.php
4. Procesa respuestas individuales
5. Elimina los registros exitosos del localStorage
6. Mantiene los fallidos para retry
7. Muestra notificación de sincronización
8. Cambia indicador a "En línea"
9. Descarga actualización de BD si es necesario
```

### Escenario 4: Búsqueda Manual Offline (NUEVO)
```
1. Usuario abre modal de búsqueda
2. Sistema muestra estado de BD local (registros cacheados, última actualización)
3. Usuario ingresa criterios de búsqueda
4. Sistema detecta que está offline
5. Realiza búsqueda en cache local (localStorage)
6. Muestra resultados con icono 💾 indicando fuente local
7. Usuario selecciona persona
8. Registro se guarda en cola de pendientes
9. Se sincroniza cuando se recupera conexión
```

### Escenario 5: Búsqueda Manual Online con Fallback
```
1. Usuario busca persona estando online
2. Sistema intenta búsqueda en servidor (🌐)
3. Si falla la conexión durante la búsqueda
4. Sistema hace fallback automático a cache local (💾)
5. Muestra resultados disponibles en cache
6. Usuario puede continuar trabajando sin interrupción
```

## APIs Implementadas

### API de Obtener Registros (NUEVO)

**Endpoint:** `GET api/obtener_registros.php`

**Descripción:** Descarga la base de datos completa de registros para cache local.

**Response:**
```json
{
  "status": "ok",
  "timestamp": "2025-11-27 15:30:45",
  "total": 250,
  "registros": [
    {
      "codigo": "REG20251127123456",
      "nombre": "Juan Pérez",
      "empresa": "Empresa ABC",
      "pais": "Chile",
      "tipo": "general"
    },
    {
      "codigo": "INACAP20251127789012",
      "nombre": "María González",
      "empresa": "Ingeniería Civil",
      "pais": "Estudiante",
      "tipo": "inacap"
    }
  ]
}
```

**Uso:**
- Se llama automáticamente al cargar el scanner
- Se actualiza cada 5 minutos en background
- Se actualiza al recuperar conexión perdida
- Los datos se almacenan en localStorage

### API de Sincronización Batch

**Endpoint:** `POST api/sincronizar_asistencias.php`

### Request
```json
{
  "asistencias": [
    {
      "codigo": "REG20251127123456",
      "dia_evento": "2025-11-27",
      "timestamp": "2025-11-27T15:30:45.123Z"
    },
    {
      "codigo": "INACAP20251127789012",
      "dia_evento": "2025-11-27",
      "timestamp": "2025-11-27T15:31:20.456Z"
    }
  ]
}
```

### Response
```json
{
  "status": "ok",
  "message": "Sincronización completada: 2 exitosos, 0 errores",
  "total": 2,
  "exitosos": 2,
  "errores": 0,
  "resultados": [
    {
      "codigo": "REG20251127123456",
      "dia_evento": "2025-11-27",
      "timestamp": "2025-11-27T15:30:45.123Z",
      "status": "ok",
      "message": "Sincronizado exitosamente",
      "nombre": "Juan Pérez",
      "tipo_asistente": "general",
      "institucion": "Empresa ABC"
    },
    {
      "codigo": "INACAP20251127789012",
      "dia_evento": "2025-11-27",
      "timestamp": "2025-11-27T15:31:20.456Z",
      "status": "duplicado",
      "message": "Ya registrado previamente",
      "nombre": "María González"
    }
  ]
}
```

## Configuración

### Parámetros Ajustables (en scanner.php)

```javascript
// Intervalo de auto-sincronización de scans (milisegundos)
const SYNC_INTERVAL = 30000; // 30 segundos

// Intervalo de auto-sincronización de BD (milisegundos)
const DB_SYNC_INTERVAL = 300000; // 5 minutos

// Intervalo de verificación de conexión (milisegundos)
const CONNECTION_CHECK_INTERVAL = 10000; // 10 segundos

// Timeout para operaciones de red (milisegundos)
const NETWORK_TIMEOUT = 10000; // 10 segundos

// Máximo de reintentos para sincronización
const MAX_RETRIES = 3;

// Keys de localStorage
const STORAGE_KEY = 'pending_scans';
const DB_CACHE_KEY = 'cached_registros';
const DB_TIMESTAMP_KEY = 'db_last_update';
```

## Ventajas del Sistema

1. **Continuidad Operacional**: El scanner nunca deja de funcionar
2. **Sin Pérdida de Datos**: Todos los scans se guardan, online u offline
3. **Búsqueda Offline Completa**: Búsqueda por nombre funciona sin conexión (NUEVO)
4. **Sincronización Transparente**: El usuario no necesita intervenir
5. **Feedback Visual Claro**: El usuario siempre sabe el estado del sistema
6. **Resiliencia**: Maneja caídas temporales de conexión sin problemas
7. **Escalabilidad**: Puede manejar colas grandes de pendientes
8. **Actualización Automática de BD**: La base de datos local se mantiene actualizada (NUEVO)
9. **Fallback Inteligente**: Si falla la búsqueda online, usa cache automáticamente (NUEVO)

## Limitaciones Conocidas

1. **Límite de localStorage**: Navegadores típicamente limitan a 5-10MB
   - Estimado scans pendientes: ~50,000 registros
   - Estimado cache BD: ~5,000-10,000 personas registradas
   - Si se excede el límite, el navegador puede limpiar datos antiguos
2. **Validación Offline Limitada**: No valida duplicados hasta sincronizar
3. **Depende del Navegador**: El usuario debe usar el mismo navegador/dispositivo
4. **Cache de BD desactualizado**: Si no hay conexión por mucho tiempo (>1 hora), la BD local puede estar desactualizada
5. **Sin validación en tiempo real offline**: Los códigos QR no se validan contra la BD hasta que se sincroniza

## Casos de Uso

### Evento en Zona con Conexión Inestable
- El scanner cambiará entre online/offline según disponibilidad
- Todos los registros se guardarán y sincronizarán eventualmente
- El operador puede seguir trabajando sin interrupciones

### Pérdida Total de Internet
- Todos los scans se acumulan en localStorage
- Cuando se restaure internet (minutos, horas o días después)
- La sincronización ocurrirá automáticamente

### Sincronización Manual
- Si se necesita forzar una sincronización, hacer click en "Sincronizar ahora"
- Útil antes de cerrar el navegador o cambiar de dispositivo

### Búsqueda de Personas Sin Conexión (NUEVO)
- El operador puede buscar personas por nombre incluso sin internet
- La búsqueda usa el cache local descargado previamente
- Ideal para eventos donde el operador no tiene los QR codes impresos
- Los registros se guardan en cola y se sincronizan automáticamente

## Monitoreo y Debugging

### Console Logs
El sistema emite logs detallados en la consola del navegador:
```
🚀 Iniciando modo offline...
📥 Descargando BD inicial...
✅ BD descargada: 250 registros
✅ Conexión restaurada
🔄 Sincronizando 5 scans...
✅ Sincronización completada: 5 exitosos, 0 errores
📴 Modo offline: guardando localmente
💾 Búsqueda offline en cache local
🔄 Auto-sincronización periódica de BD...
📊 BD Local: 250 registros (actualizado hace 5 minutos)
```

### Inspección del localStorage
Para ver los datos cacheados en Chrome DevTools (F12 → Console):
```javascript
// Ver scans pendientes
JSON.parse(localStorage.getItem('pending_scans'))

// Ver base de datos cacheada
JSON.parse(localStorage.getItem('cached_registros'))

// Ver timestamp de última actualización de BD
localStorage.getItem('db_last_update')

// Ver tamaño total usado en localStorage (aproximado)
Object.keys(localStorage).reduce((total, key) =>
  total + localStorage[key].length, 0
) / 1024 + ' KB'
```

## Recomendaciones de Uso

1. **Mantener el navegador abierto** durante eventos largos
2. **Verificar el contador de pendientes** periódicamente
3. **Forzar sincronización manual** antes de cerrar el navegador
4. **Monitorear la consola** en caso de problemas
5. **No limpiar datos del navegador** hasta confirmar sincronización

## Soporte

Si experimentas problemas:
1. Verifica el estado de conexión en el indicador
2. Revisa la consola del navegador (F12) para mensajes de error
3. Intenta sincronización manual con el botón
4. Verifica que la API esté funcionando correctamente
5. Contacta al administrador del sistema

---

**Desarrollado para**: Nodo Bioceánico 2025
**Última actualización**: 2025-11-27
