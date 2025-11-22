# 🧪 Carpeta de Pruebas - Rueda de Negocios

## 📁 Archivos en esta carpeta

### 1. **envio_rueda_PRUEBA.php**
Script de prueba que envía **UN SOLO correo** con datos de ejemplo.

**Características:**
- ✅ NO consulta la base de datos
- ✅ Envía solo a 1 email configurado
- ✅ Usa datos de prueba (nombre, empresa, cargo)
- ✅ Banner visible de "CORREO DE PRUEBA"
- ✅ Mismo diseño que el script principal

### 2. **monitor_prueba.html**
Interfaz web para ejecutar las pruebas fácilmente.

**Funciones:**
- Botón para enviar correo de prueba
- Muestra resultado en tiempo real
- Instrucciones de uso
- Link para volver al monitor principal

---

## 🚀 Cómo Usar las Pruebas

### Paso 1: Configurar Email de Prueba

Abre el archivo `envio_rueda_PRUEBA.php` y edita la línea 21:

```php
$EMAIL_PRUEBA = 'tu-email@ejemplo.com';  // 👈 CAMBIA ESTE EMAIL
```

También puedes cambiar los datos de ejemplo (líneas 22-25):

```php
$NOMBRE_PRUEBA = 'Juan';
$APELLIDO_PRUEBA = 'Pérez';
$EMPRESA_PRUEBA = 'Mi Empresa S.A.';
$CARGO_PRUEBA = 'Gerente General';
```

### Paso 2: Ejecutar la Prueba

**Opción A - Usando el Monitor:**
```
https://www.bioceanicocentral.cl/registro/pruebas_rueda/monitor_prueba.html
```

1. Abre el monitor en tu navegador
2. Haz click en "🚀 Enviar Correo de Prueba"
3. Espera el resultado

**Opción B - Directo desde el navegador:**
```
https://www.bioceanicocentral.cl/registro/pruebas_rueda/envio_rueda_PRUEBA.php
```

**Opción C - Por terminal:**
```bash
php /home/user/registro/pruebas_rueda/envio_rueda_PRUEBA.php
```

### Paso 3: Verificar el Correo

1. Revisa la bandeja de entrada del email configurado
2. **Importante:** También revisa la carpeta de SPAM
3. Verifica que el correo se vea correctamente:
   - ✓ Banner "CORREO DE PRUEBA" visible arriba y abajo
   - ✓ Logo del Nodo Bioceánico cargado
   - ✓ Colores y diseño correcto
   - ✓ Botón "Registrarme Ahora" funcional
   - ✓ Botón "Ya no me interesa" funcional
   - ✓ Todos los textos con acentos correctos

---

## 📧 Contenido del Correo de Prueba

El correo enviado incluye:

### 1. Banner de Advertencia (Superior e Inferior)
```
⚠️ CORREO DE PRUEBA - NO ES ENVÍO REAL
```

### 2. Asunto
```
[PRUEBA] Rueda de Negocios B2B - Nodo Bioceánico 2025
```

### 3. Contenido Completo
- Header con logo y título
- Saludo personalizado
- Explicación de la rueda de negocios
- Nota "NO es spam"
- Modalidades de participación
- Botón CTA "Registrarme Ahora"
- Detalles del evento
- **Botón "Ya no me interesa"** (NUEVO)
- Footer

---

## ✅ Checklist de Verificación

Después de enviar el correo de prueba, verifica:

- [ ] El correo llegó a la bandeja (o spam)
- [ ] El asunto dice "[PRUEBA]"
- [ ] Banner de prueba visible arriba y abajo
- [ ] Logo del Nodo Bioceánico cargado
- [ ] Nombre y apellido correctos
- [ ] Empresa y cargo correctos
- [ ] Colores azul (#004aad) y verde (#2ecc71) correctos
- [ ] Botón "Registrarme Ahora" es azul y grande
- [ ] Link del botón apunta a la página de registro
- [ ] Botón "Ya no me interesa" es gris
- [ ] Link "Ya no me interesa" abre el cliente de correo
- [ ] Todos los acentos se ven bien (ú, á, é, í, ó)
- [ ] No hay caracteres raros (�)
- [ ] Footer con copyright visible

---

## 🔧 Diferencias con el Script Principal

| Característica | Script de Prueba | Script Principal |
|---|---|---|
| **Consulta BD** | ❌ No | ✅ Sí |
| **Cantidad de correos** | 1 solo | Todos los interesados |
| **Datos** | Hardcodeados | Desde BD |
| **Banner de prueba** | ✅ Sí | ❌ No |
| **Asunto** | `[PRUEBA]` incluido | Sin `[PRUEBA]` |
| **Delays** | ❌ No | ✅ Sí (3s y 45s) |
| **Logs** | ❌ No | ✅ Sí |
| **Estado** | ❌ No | ✅ Sí |

---

## 🐛 Solución de Problemas

### Problema: "Error al enviar correo de prueba"

**Posibles causas:**
1. Servidor SMTP no disponible
2. Credenciales SMTP incorrectas
3. Puerto bloqueado (587)
4. Email de destino inválido

**Solución:**
1. Verifica que el servidor `10.24.254.104` esté disponible
2. Confirma credenciales en el script (líneas 67-69)
3. Intenta con otro email de destino
4. Revisa logs del servidor de correo

### Problema: "El correo no llega"

**Solución:**
1. Revisa carpeta SPAM
2. Verifica que el email de destino sea válido
3. Espera 1-2 minutos (puede haber delay)
4. Intenta con otro proveedor de email (Gmail, Outlook, etc.)

### Problema: "El correo se ve mal (caracteres raros)"

**Solución:**
1. Esto NO debería pasar porque usamos `UTF-8` y `htmlentities()`
2. Si pasa, verifica la configuración del cliente de correo
3. Abre el correo en otro cliente (web, móvil, desktop)

### Problema: "El logo no se ve"

**Solución:**
1. Verifica que la URL del logo sea correcta:
   ```
   https://www.bioceanicocentral.cl/wp-content/uploads/2025/11/logonuevo.png
   ```
2. Algunos clientes de correo bloquean imágenes por defecto
3. Pide al destinatario que "permita imágenes"

---

## 📊 Interpretando los Resultados

### ✅ Resultado Exitoso

Si ves esto en el navegador:

```
✅ Correo de Prueba Enviado Exitosamente
Destinatario: tu-email@ejemplo.com
Nombre: Juan Pérez
Empresa: Mi Empresa S.A.
```

**Significa:**
- El correo fue enviado correctamente
- Revisa la bandeja de entrada del email
- Si no aparece en 2 minutos, revisa SPAM

### ❌ Resultado con Error

Si ves esto:

```
❌ Error al Enviar Correo de Prueba
Error: SMTP connect() failed
```

**Significa:**
- No se pudo conectar al servidor SMTP
- Verifica configuración del servidor
- Contacta al administrador del sistema

---

## 🎯 Siguientes Pasos

Después de verificar que la prueba funciona:

### 1. Volver al Monitor Principal
```
https://www.bioceanicocentral.cl/registro/monitor_envio_rueda.html
```

### 2. Ejecutar Envío Real

**Antes de enviar:**
- [ ] Verificaste la cantidad de empresas interesadas
- [ ] Confirmaste que el correo de prueba se ve bien
- [ ] Tienes tiempo suficiente (no lo hagas con prisa)
- [ ] El servidor SMTP está disponible

**Para enviar:**
1. Abre el monitor principal
2. Click en "▶️ Iniciar Envío Masivo"
3. Confirma la acción
4. Monitorea el progreso

---

## 📝 Notas Adicionales

### Cambiar el Template del Correo

Si necesitas modificar el diseño del correo:

1. Edita el archivo `envio_rueda_PRUEBA.php`
2. Busca la sección `$mail->Body = "..."`
3. Modifica el HTML
4. Guarda y prueba de nuevo
5. **Una vez confirmado**, replica los cambios en `envio_rueda_negocios.php`

### Múltiples Pruebas

Puedes enviar el correo de prueba **cuantas veces quieras**.

- No hay límite de envíos
- No afecta la base de datos
- Solo envía a 1 email

### Probar con Diferentes Emails

Para probar cómo se ve en diferentes clientes:

1. Gmail: Cambia `$EMAIL_PRUEBA` a tu Gmail
2. Outlook: Cambia a tu Outlook
3. Corporativo: Cambia a tu email corporativo

Así verificas compatibilidad con todos los clientes.

---

## 🔒 Seguridad

### ⚠️ Importante

**NO subas este archivo a producción sin protección.**

El script contiene credenciales SMTP en texto plano:
- Host: `10.24.254.104`
- User: `contacto@bioceanicocentral.cl`
- Pass: `@@Bio2025`

**Recomendaciones:**
1. Esta carpeta debe estar protegida por `.htaccess`
2. O mejor aún, fuera del `DocumentRoot`
3. Considera usar variables de entorno para credenciales

---

## 📞 Soporte

Si tienes problemas con las pruebas, contacta al administrador del sistema.

**Archivos de esta carpeta:**
- `envio_rueda_PRUEBA.php` - Script de prueba
- `monitor_prueba.html` - Monitor de pruebas
- `README_PRUEBAS.md` - Este archivo

**Archivos principales (fuera de esta carpeta):**
- `../envio_rueda_negocios.php` - Script de producción
- `../monitor_envio_rueda.html` - Monitor de producción
- `../INSTRUCCIONES_ENVIO_RUEDA.md` - Documentación completa

---

**Última actualización:** 2025-11-22
**Versión:** 1.0
