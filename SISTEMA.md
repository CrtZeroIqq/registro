# Documentación Integral del Sistema Nodo/Cumbre Bioceánico 2025

Este documento describe en profundidad cómo funciona la plataforma de registro, correo y control de asistencia. Resume la arquitectura de archivos PHP, la interacción de los formularios públicos, las APIs de asistencia, la generación de QR/ICS y los aspectos operativos necesarios para desplegar y mantener el servicio.

## Vista general de arquitectura
- **Cliente web estático**: formularios HTML (`registro.html`, `registro_inacap.html`) y pantallas operativas (`scanner.php`, `dashboard_envios.html`, `estadisticas_apertura.html`) servidos por PHP/Apache o `php -S`. No hay framework JS; se apoya en `Html5Qrcode` para escaneo desde cámara.
- **Capa PHP**:
  - Endpoints de registro (`guardar_registro.php`, `guardar_registro_inacap.php`).
  - APIs JSON nuevas para asistencia (`api/registrar_asistencia.php`, `api/buscar_personas.php`) y endpoint heredado monolítico (`asistencia.php`).
  - Scripts batch de correo (`enviar_qr_masivo.php`) y pruebas (`test_mail.php`).
- **Dependencias locales**: `PHPMailer` para SMTP autenticado y `phpqrcode` para generar PNG; no se usan composer ni autoloaders.
- **Persistencia**: MySQL `registro_evento` accesible vía `mysqli` con credenciales embebidas. Las tablas principales son `registros`, `registros_inacap`, `asistencias`, `asistencia` (legado) y `blocked_attempts` (creada al vuelo).
- **Archivos generados**: PNG en `qrcodes/` y archivos .ics por registro. Los eventos ICS también se guardan en ese directorio para adjuntarlos al correo.

## Mapa de rutas y responsabilidades
- `registro.html` → envía datos a `guardar_registro.php` (POST/JSON).
- `registro_inacap.html` → envía JSON a `guardar_registro_inacap.php`.
- `scanner.php` → UI operativa: selecciona `dia_evento`, lee QR con cámara o busca manualmente → invoca `api/registrar_asistencia.php` y `api/buscar_personas.php`.
- `asistencia.php` → endpoint legado con modos `?action=buscar` y `?action=asistencia`, además de una interfaz embebida para escaneo/búsqueda.
- `enviar_qr_masivo.php` → recorre registros y reenvía QR y ICS; usa la URL pública del PNG (`/registro/qrcodes/<codigo>.png`).
- `track.php`, `estadisticas_apertura.html`, `dashboard_envios.html` → archivos para seguimiento/apertura de correos (consultar antes de usarlos porque no hay backend centralizado documentado).

## Flujo de registro general
1. El front (`registro.html`) envía datos personales y de empresa. El backend acepta tanto `application/json` como `x-www-form-urlencoded` y normaliza los arrays (por ejemplo, `intereses`).【F:guardar_registro.php†L24-L42】
2. Valida campos obligatorios (`nombre`, `apellido`, `email`, `pais`). Si el email coincide con la lista negra, persiste el intento en `blocked_attempts` con IP y user agent y responde `400` genérico para que el frontend lo considere fallo.【F:guardar_registro.php†L44-L77】
3. Genera un código `REGyyyymmddHHMMSSnnn` y lo inserta en `registros` con los metadatos de empresa, tamaño, sector, website, intereses y fuente.【F:guardar_registro.php†L80-L101】
4. Produce el PNG en `qrcodes/<codigo>.png` (crea el directorio si falta) y el ICS `evento_<codigo>.ics` con horario 26–28 nov 2025 en Arica.【F:guardar_registro.php†L103-L132】
5. Envía correo SMTP StartTLS al relay privado (`10.24.254.104`, usuario `contacto@bioceanicocentral.cl`) con el QR embebido (CID) y el ICS adjunto. El cuerpo incluye resumen de contacto y sector del asistente.【F:guardar_registro.php†L134-L191】

## Flujo de registro INACAP
1. `registro_inacap.html` envía un JSON estricto; el endpoint rechaza JSON inválido o campos vacíos y valida formato de email antes de continuar.【F:guardar_registro_inacap.php†L22-L52】
2. Genera códigos prefijados `INACAPyyyymmddHHMMSSnnn` y guarda en `registros_inacap` los campos `nombre`, `rut`, `carrera`, `email`, `tipo_participante`. Maneja errores SQL con excepciones claras para los operadores.【F:guardar_registro_inacap.php†L54-L75】
3. Crea QR y un ICS propio (mismo evento, distinto UID) y adjunta ambos al correo de confirmación. El cuerpo remarca el tipo de participante y agrega aviso para presentar el QR en acceso.【F:guardar_registro_inacap.php†L77-L125】
4. Retorna JSON con `status=ok` y el código emitido; en fallas responde 400 y registra en `error_log` del servidor.【F:guardar_registro_inacap.php†L127-L158】

## Control de asistencia en terreno
- **API nueva (`api/registrar_asistencia.php`)**
  1. Recibe JSON con `codigo` y `dia_evento` (YYYY-MM-DD). Valida ambos campos y formato de fecha.【F:api/registrar_asistencia.php†L15-L36】
  2. Detecta el tipo por prefijo (`REG` → tabla `registros`, `INACAP` → `registros_inacap`) y arma nombre/empresa o carrera según corresponda. Responde error explícito si no se encuentra el código o el prefijo es desconocido.【F:api/registrar_asistencia.php†L38-L78】
  3. Verifica duplicidad por combinación `codigo` + `dia_evento` en `asistencias`; si existe, devuelve un mensaje de advertencia sin insertar.【F:api/registrar_asistencia.php†L80-L94】
  4. Inserta en `asistencias` con `tipo_asistente`, `nombre_completo`, `institucion` y devuelve un payload enriquecido listo para UI (fecha formateada, tipo participante/carrera cuando aplica).【F:api/registrar_asistencia.php†L96-L134】

- **Búsqueda manual (`api/buscar_personas.php`)**: Permite autocompletar por nombre, empresa o país; construye dinámicamente la consulta parametrizada y limita a 50 filas ordenadas por nombre para evitar respuestas largas.【F:api/buscar_personas.php†L15-L51】

- **Endpoint legado (`asistencia.php`)**: expone `?action=buscar` (30 resultados) y `?action=asistencia` (inserta en `asistencia` sin prevención de duplicados). Incluye una interfaz HTML con escáner usando `Html5Qrcode` para compatibilidad retro, pero carece de las validaciones modernas de prefijo y deduplicación.【F:asistencia.php†L1-L74】【F:asistencia.php†L76-L120】

## Modelo de datos
- **registros**: almacena registros generales con identificación de empresa y preferencias (`tamano`, `sector`, `intereses`, `rueda`, `fuente`). Clave primaria `id`; `codigo_registro` se usa como clave de negocio en QR y asistencias.【F:guardar_registro.php†L80-L101】
- **registros_inacap**: contiene participantes afiliados a INACAP (`rut`, `carrera`, `tipo_participante`) y su `codigo` QR propio.【F:guardar_registro_inacap.php†L54-L75】
- **asistencias**: tabla moderna para control diario (`codigo`, `dia_evento`, `tipo_asistente`, `nombre_completo`, `institucion`) con verificación de unicidad por código+fecha en la lógica de aplicación.【F:api/registrar_asistencia.php†L80-L134】
- **asistencia** (legado): usada solo por `asistencia.php`; guarda `codigo_registro`, `dia_evento`, `fecha_hora` sin reglas de duplicidad.【F:asistencia.php†L34-L59】
- **blocked_attempts**: creada on-demand para emails en lista negra; registra `email`, `ip`, `user_agent`, `reason`, `created_at` para auditoría básica.【F:guardar_registro.php†L48-L77】

## Correo, QR y calendarios
- **SMTP**: todos los envíos usan el relay interno `10.24.254.104` (587/STARTTLS) con usuario `contacto@bioceanicocentral.cl`; se deshabilita validación de certificado (`verify_peer=false`).【F:guardar_registro.php†L134-L162】【F:guardar_registro_inacap.php†L95-L121】
- **Contenido**: correos HTML en UTF-8 con QR embebido como CID, resumen de datos y fechas del evento; adjuntan el ICS para agendar en Gmail/Outlook.【F:guardar_registro.php†L164-L191】【F:guardar_registro_inacap.php†L103-L125】
- **QR**: se genera con `phpqrcode` en nivel de corrección L y tamaño 5; el contenido es solo el código (no URL), lo que obliga a que el sistema conozca el prefijo para enrutarlo.【F:guardar_registro.php†L103-L119】【F:guardar_registro_inacap.php†L77-L88】
- **ICS**: cada registro crea un archivo independiente en `qrcodes/` para ser adjuntado; las fechas están fijadas y deberían actualizarse si cambia la agenda oficial.【F:guardar_registro.php†L120-L132】【F:guardar_registro_inacap.php†L89-L101】

## Operación y despliegue
- **Requisitos**: PHP con `mysqli`, `openssl`, permisos de escritura en `qrcodes/`, acceso a MySQL `registro_evento`, salida SMTP hacia `10.24.254.104`.
- **Configuración**: credenciales de BD y SMTP están incrustadas en múltiples archivos; mover a variables de entorno o `.env.php` reduciría exposición. Asegurar `utf8mb4` en la conexión (ya se establece en las APIs nuevas).【F:api/registrar_asistencia.php†L11-L22】【F:api/buscar_personas.php†L7-L16】
- **Logs y seguimiento**: errores de PHPMailer se devuelven en la respuesta JSON; `guardar_registro_inacap.php` registra excepciones en el `error_log`. Archivos `email_tracking.log`, `envio_detallado.log` y `estado_envio.json` se usan para monitoreo manual de campañas masivas (revisar antes de limpiar). 
- **Seguridad**: la lista negra corta registros maliciosos y guarda evidencia; no hay autenticación en las APIs de asistencia, por lo que se recomienda restringir por IP o requerir token si se expone fuera de la red interna. Los formularios aceptan JSON y POST sin CSRF, apropiado solo para canales públicos controlados.
- **Mantenimiento**: mantener `PHPMailer`/`phpqrcode` actualizados, revisar permisos de `qrcodes/`, y depurar duplicados en `asistencia` si se migra definitivamente a `asistencias`. Consolidar la lógica en `api/registrar_asistencia.php` y retirar `asistencia.php` cuando ya no sea necesario.
