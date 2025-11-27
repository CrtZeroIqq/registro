# Sistema de Scanner con Modo Offline

## Descripción General

El sistema de scanner ahora incluye un **modo offline robusto** que permite continuar registrando asistencias incluso cuando se pierde la conexión a internet. Los registros se almacenan localmente y se sincronizan automáticamente cuando se recupera la conexión.

## Características Implementadas

### 1. **Detección Automática de Conexión**
- Monitoreo en tiempo real del estado de la conexión
- Verificación periódica cada 10 segundos mediante ping al servidor
- Detección de eventos del navegador (online/offline)
- Indicador visual del estado de conexión

### 2. **Almacenamiento Local Persistente**
- Los scans se guardan en localStorage del navegador
- Los datos persisten incluso si se cierra el navegador
- Formato de almacenamiento:
  ```json
  {
    "codigo": "REG20251127123456",
    "dia_evento": "2025-11-27",
    "timestamp": "2025-11-27T15:30:45.123Z"
  }
  ```

### 3. **Sincronización Automática**
- Auto-sincronización cada 30 segundos cuando hay conexión
- Sincronización inmediata al recuperar la conexión
- Procesamiento batch de múltiples registros pendientes
- Retry automático con exponential backoff (2s, 4s, 8s)

### 4. **Indicadores Visuales**

#### Estado de Conexión
- **Verde (En línea)**: Conexión normal, los scans se registran inmediatamente
- **Amarillo (Modo Offline)**: Sin conexión, los scans se guardan localmente
- **Azul (Sincronizando)**: Sincronizando registros pendientes

#### Cola de Pendientes
- Aparece automáticamente cuando hay scans pendientes
- Muestra el número de registros esperando sincronización
- Botón para forzar sincronización manual

### 5. **Manejo Robusto de Errores**
- Try-catch en todas las operaciones críticas
- Timeout de 10 segundos para operaciones de red
- Fallback automático a modo offline ante errores
- Logs detallados en consola para debugging

## Archivos Modificados/Creados

### Nuevos Archivos
- **`api/sincronizar_asistencias.php`**: API para sincronización batch de múltiples registros

### Archivos Modificados
- **`scanner.php`**: Scanner principal con modo offline completo

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
```

## API de Sincronización Batch

### Endpoint
`POST api/sincronizar_asistencias.php`

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
// Intervalo de auto-sincronización (milisegundos)
const SYNC_INTERVAL = 30000; // 30 segundos

// Intervalo de verificación de conexión (milisegundos)
const CONNECTION_CHECK_INTERVAL = 10000; // 10 segundos

// Timeout para operaciones de red (milisegundos)
const NETWORK_TIMEOUT = 10000; // 10 segundos

// Máximo de reintentos para sincronización
const MAX_RETRIES = 3;
```

## Ventajas del Sistema

1. **Continuidad Operacional**: El scanner nunca deja de funcionar
2. **Sin Pérdida de Datos**: Todos los scans se guardan, online u offline
3. **Sincronización Transparente**: El usuario no necesita intervenir
4. **Feedback Visual Claro**: El usuario siempre sabe el estado del sistema
5. **Resiliencia**: Maneja caídas temporales de conexión sin problemas
6. **Escalabilidad**: Puede manejar colas grandes de pendientes

## Limitaciones Conocidas

1. **Límite de localStorage**: Navegadores típicamente limitan a 5-10MB
   - Estimado: ~50,000 registros pendientes (más que suficiente)
2. **Validación Offline Limitada**: No valida duplicados hasta sincronizar
3. **Depende del Navegador**: El usuario debe usar el mismo navegador/dispositivo

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

## Monitoreo y Debugging

### Console Logs
El sistema emite logs detallados en la consola del navegador:
```
🚀 Iniciando modo offline...
✅ Conexión restaurada
🔄 Sincronizando 5 scans...
✅ Sincronización completada: 5 exitosos, 0 errores
📴 Modo offline: guardando localmente
```

### Inspección del localStorage
Para ver los scans pendientes en Chrome DevTools:
```javascript
// Abrir Console
JSON.parse(localStorage.getItem('pending_scans'))
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
