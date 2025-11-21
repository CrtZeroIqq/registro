# CLAUDE.md - AI Assistant Guide for Event Registration System

## Project Overview

**Project Name**: Nodo Bioceánico Central 2025 - Event Registration & Attendance System
**Event Date**: November 26-28, 2025
**Location**: Arica, Chile
**Purpose**: Conference registration, QR code generation, email campaigns, and attendance tracking

**Technology Stack**:
- **Backend**: PHP 7.x+ (vanilla, no framework)
- **Database**: MySQL (database: `registro_evento`)
- **Frontend**: HTML5 + Vanilla JavaScript
- **Email**: PHPMailer (SMTP)
- **QR Codes**: phpqrcode library
- **Scanner**: html5-qrcode (v2.3.8)

---

## Directory Structure

```
/home/user/registro/
├── api/                        # REST API endpoints
│   ├── buscar_personas.php     # Search API for attendee lookup
│   └── registrar_asistencia.php # Attendance registration API
├── PHPMailer/                  # Email library (v6.x, LGPL-2.1)
│   ├── src/                    # Core PHPMailer classes
│   └── language/               # Multi-language support
├── phpqrcode/                  # QR code generation library
│   ├── qrlib.php               # Main QR generator
│   └── cache/                  # QR code cache
├── qrcodes/                    # Generated QR code storage (PNG files)
├── sounds/                     # Audio feedback
│   └── success.mp3             # Success sound for scanner
├── *.php                       # Backend scripts
├── *.html                      # Frontend interfaces
├── *.log                       # Process and tracking logs
├── *.ics                       # Calendar event files
└── .htaccess                   # Apache configuration
```

---

## Key Files and Their Purposes

### Registration System
| File | Purpose | Key Features |
|------|---------|--------------|
| `registro.html` | Main registration form (966 lines) | Comprehensive form for general public with personal/business data |
| `registro_inacap.html` | INACAP institution form | Simplified registration for institutional members |
| `guardar_registro.php` | Process general registrations | Generates QR codes, sends emails, creates calendar invites |
| `guardar_registro_inacap.php` | Process INACAP registrations | Similar to above, separate code prefix (INACAP...) |

### Attendance Management
| File | Purpose | Key Features |
|------|---------|--------------|
| `scanner.php` | QR scanner interface (318 lines) | Multi-day attendance tracking, manual search fallback |
| `asistencia.php` | Dual-purpose scanner/API | Standalone UI + API endpoint for attendance |
| `api/registrar_asistencia.php` | Attendance API backend | Validates codes, prevents duplicates, records timestamp |
| `api/buscar_personas.php` | Search API | Manual lookup by name, email, or code |

### Email Campaign System
| File | Purpose | Key Features |
|------|---------|--------------|
| `envio_masivo.php` | Mass email sender (474 lines) | Throttled sending (15/batch), progress tracking, logging |
| `envio_rueda_negocios.php` | Rueda de negocios campaign | Filtered by rueda='Si', special invitation template |
| `enviar_qr_masivo.php` | Alternative mass sender | QR distribution variant |
| `dashboard_envios.html` | Campaign dashboard | Real-time progress monitoring |
| `dashboard_rueda.html` | Rueda de negocios dashboard | Monitor business networking campaign |
| `track.php` | Email open tracking | 1x1 pixel tracker with IP/user-agent logging |
| `obtener_estadisticas.php` | Statistics API | Email campaign metrics |

### Utilities
| File | Purpose |
|------|---------|
| `index.html` | Access restriction page |
| `test_mail.php` | Email testing script |
| `test_qr.php` | QR generation testing |
| `test_envio_rueda.php` | Test script for rueda de negocios email template |
| `.htaccess` | Disables directory listing, restricts PHP access |

### Data Files
| File | Size | Purpose |
|------|------|---------|
| `email_tracking.log` | 93KB | JSON log of email opens |
| `envio_detallado.log` | 83KB | Detailed mass email process log |
| `estado_envio.json` | 3.9KB | Real-time email campaign state |
| `envio_rueda_detallado.log` | New | Detailed log for rueda de negocios campaign |
| `estado_envio_rueda.json` | New | Real-time state for rueda de negocios campaign |

---

## Database Schema

**Database**: `registro_evento`
**Connection Info**: See Configuration section below

### Tables

**`registros`** - General event registrations
- Primary key: `id` (auto-increment)
- Unique fields: `codigo` (e.g., REG20251119123456789), `email`
- Key fields: nombre, apellido, email, telefono, empresa, cargo, region, ciudad
- Business networking: rueda (values: 'Si', 'No', or NULL - indicates interest in business roundtable)
- Additional: fecha_registro, qr_code_path, email_enviado, email_abierto

**`registros_inacap`** - INACAP institution registrations
- Similar structure to `registros`
- Unique code prefix: INACAP20251119123456789
- Institution-specific fields

**`asistencias`** - Attendance records
- Fields: codigo, fecha_asistencia (YYYY-MM-DD), timestamp
- Prevents duplicate check-ins per day
- Supports both REG and INACAP codes

**`blocked_attempts`** - Blacklist tracking
- Logs blocked registration attempts with IP, timestamp

---

## Configuration

### Database Credentials
**⚠️ WARNING**: Credentials are currently hardcoded in PHP files. Consider using environment variables.

```php
$servername = "localhost";
$username = "seidev";
$password = "Tz9!qE7#Xr2@Lm5$Vp8^";
$database = "registro_evento";
```

**Files containing DB credentials**:
- guardar_registro.php
- guardar_registro_inacap.php
- scanner.php
- asistencia.php
- envio_masivo.php
- api/registrar_asistencia.php
- api/buscar_personas.php
- obtener_estadisticas.php
- track.php

### Email SMTP Configuration
```php
$mail->Host = '10.24.254.104';  // Internal SMTP server
$mail->Port = 587;               // STARTTLS
$mail->Username = 'contacto@bioceanicocentral.cl';
$mail->Password = '@@Bio2025';   // ⚠️ Hardcoded
$mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo Bioceánico Central');
```

### Apache Configuration (.htaccess)
```apache
Options -Indexes                    # Disables directory browsing
<FilesMatch "\.(php|inc)$">
    Require all denied              # ⚠️ Blocks ALL PHP access (too restrictive!)
</FilesMatch>
```

---

## Development Workflows

### 1. Registration Flow
```
User fills form → JavaScript validates → POST to PHP backend →
Validate data → Check blacklist → Generate unique code →
Create QR PNG → Generate ICS file → Send email → Return JSON response
```

**Code Generation Pattern**:
- General: `REG` + `YmdHis` + `rand(100,999)` → `REG20251119123456789`
- INACAP: `INACAP` + `YmdHis` + `rand(100,999)` → `INACAP20251119123456789`

**QR Code Storage**: `/qrcodes/{codigo}.png`
**ICS Files**: `evento_bioceanic_{codigo}.ics`

### 2. Attendance Tracking Flow
```
Scanner opens → Select event day → Scan QR or search manually →
Validate code → Check duplicates → Record attendance →
Play sound feedback → Update UI
```

**Duplicate Prevention**: One check-in per code per day (based on `fecha_asistencia`)

### 3. Mass Email Campaign Workflow
```
Admin starts envio_masivo.php → Fetch pending recipients →
Exclude processed emails → Send in batches (15 emails/batch) →
Wait 3 seconds between emails → Wait 45 seconds between batches →
Update estado_envio.json → Log to envio_detallado.log → Complete
```

**Throttling Strategy**:
- 15 emails per batch
- 3-second delay between individual emails
- 45-second pause between batches
- Real-time progress updates to JSON file

### 4. Email Tracking
```
Email sent with tracking pixel → Recipient opens email →
Browser loads track.php?code=XXX → Log timestamp/IP/user-agent →
Update email_abierto flag in database
```

---

## Coding Conventions

### File Naming
- PHP files: `snake_case` (e.g., `guardar_registro.php`)
- HTML files: `snake_case` (e.g., `registro_inacap.html`)
- Generated files: `{prefix}_{identifier}.{ext}`

### Database Access Pattern
```php
// Always use prepared statements
$conn = new mysqli($servername, $username, $password, $database);
$stmt = $conn->prepare("SELECT * FROM registros WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
```

**✅ Good**: All database queries use prepared statements (prevents SQL injection)

### API Response Format
```php
// Success
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'message' => 'Operación exitosa']);

// Error
http_response_code(400); // Use appropriate HTTP codes
echo json_encode(['status' => 'error', 'message' => 'Descripción del error']);
```

### Error Handling
```php
try {
    // Critical operations
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error message']);
}
```

### Email HTML Pattern
- Inline CSS (for email client compatibility)
- Responsive design with `max-width` containers
- HTML entity encoding for Spanish characters (`&aacute;`, `&iacute;`, etc.)
- Embedded images via Content-ID (CID)
- Tracking pixel at end: `<img src="track.php?code={codigo}" width="1" height="1">`

### Frontend JavaScript
- Vanilla JavaScript (no frameworks)
- `async/await` for API calls
- `fetch()` API with JSON
- `FormData` + `Object.fromEntries()` for form processing
- Console logging for debugging

---

## Security Considerations

### ✅ Good Security Practices
1. **Prepared Statements**: All SQL queries use parameterized statements
2. **Email Validation**: Uses `filter_var($email, FILTER_VALIDATE_EMAIL)`
3. **Blacklist System**: Blocks known malicious emails/domains
4. **Input Sanitization**: `htmlentities()` used for display
5. **Attempt Logging**: Tracks blocked registration attempts with IP addresses

### ⚠️ Security Concerns
1. **Hardcoded Credentials**: Database and email passwords in source code
2. **No Environment Variables**: Configuration not externalized
3. **No Authentication**: Admin functions (scanner, mass email) have no login
4. **No CSRF Protection**: Forms lack anti-CSRF tokens
5. **Overly Restrictive .htaccess**: Blocks all PHP files (breaks functionality)
6. **No Rate Limiting**: Registration endpoints could be abused
7. **Exposed Logs**: `.log` and `.json` files potentially accessible

### Recommended Improvements
```php
// TODO: Use environment variables
$db_password = getenv('DB_PASSWORD');
$smtp_password = getenv('SMTP_PASSWORD');

// TODO: Add session-based authentication for admin pages
session_start();
if (!isset($_SESSION['admin_authenticated'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// TODO: Add CSRF tokens to forms
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
```

---

## Common Tasks for AI Assistants

### Task 1: Add a New Registration Field

1. **Update HTML form** (`registro.html` or `registro_inacap.html`)
   ```html
   <input type="text" name="nuevo_campo" id="nuevo_campo" required>
   ```

2. **Update JavaScript validation** (if needed)
   ```javascript
   // Add validation logic in form submit handler
   ```

3. **Update PHP backend** (`guardar_registro.php`)
   ```php
   $nuevo_campo = $data['nuevo_campo'] ?? '';
   // Add to INSERT query
   ```

4. **Update database schema**
   ```sql
   ALTER TABLE registros ADD COLUMN nuevo_campo VARCHAR(255);
   ```

### Task 2: Modify Email Template

1. **Locate email HTML** in `guardar_registro.php` or `envio_masivo.php`
2. **Find the heredoc block** (usually `<<<HTML ... HTML`)
3. **Modify inline CSS** and content
4. **Test with** `test_mail.php`
5. **Remember**: Use HTML entities for Spanish characters

### Task 3: Add New Scanner Event Day

1. **Edit** `scanner.php` around line 100-150
2. **Add option** to date selector:
   ```html
   <option value="2025-11-29">29 de noviembre 2025</option>
   ```
3. **No backend changes needed** (dates are dynamic)

### Task 4: Generate Reports

**Query Examples**:
```sql
-- Total registrations
SELECT COUNT(*) FROM registros;

-- Attendance by day
SELECT fecha_asistencia, COUNT(*)
FROM asistencias
GROUP BY fecha_asistencia;

-- Email open rate
SELECT
    COUNT(*) as total_enviados,
    SUM(email_abierto) as total_abiertos,
    (SUM(email_abierto) / COUNT(*)) * 100 as tasa_apertura
FROM registros
WHERE email_enviado = 1;

-- Registrations by region
SELECT region, COUNT(*) as total
FROM registros
GROUP BY region
ORDER BY total DESC;
```

### Task 5: Debug Email Issues

1. **Check SMTP connection**: Run `test_mail.php`
2. **Review logs**: Check `envio_detallado.log`
3. **Verify recipient**: Check if email is in hardcoded exclusion list (envio_masivo.php line ~50)
4. **Test PHPMailer**: Enable debug mode
   ```php
   $mail->SMTPDebug = 2; // Verbose debug output
   ```

### Task 6: Modify QR Code Appearance

1. **Edit** `guardar_registro.php` or `test_qr.php`
2. **Locate** `QRcode::png()` call:
   ```php
   QRcode::png($codigo, $qrCodePath, QR_ECLEVEL_L, 10, 2);
   //                                 ^^^^^^^^^^^  ^^  ^
   //                                 Error level  Size Margin
   ```
3. **Adjust parameters**:
   - Error correction: `QR_ECLEVEL_L/M/Q/H` (L=7%, H=30% recovery)
   - Size: 1-10 (pixel multiplier)
   - Margin: 0-10 (quiet zone size)

---

## Important Notes for AI Assistants

### When Making Changes

1. **Always read files first** before editing
2. **Use Edit tool** for existing files (never Write unless creating new files)
3. **Test database queries** before modifying production code
4. **Preserve Spanish text** and HTML entities (`&aacute;`, `&ntilde;`, etc.)
5. **Maintain consistent indentation** (mixture of tabs/spaces in codebase)
6. **Check for hardcoded exclusion lists** in mass email scripts

### Before Committing

1. **Verify no sensitive data** is being committed
2. **Check that .htaccess** doesn't break new PHP files
3. **Test email sending** if modifying email code
4. **Validate QR code generation** if changing registration flow
5. **Test both registration forms** (general and INACAP)

### File Dependencies

**PHPMailer Dependencies**:
```php
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
```

**QR Code Dependency**:
```php
require_once 'phpqrcode/qrlib.php';
```

**Common Pattern**:
```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
```

### Known Limitations

1. **No package manager**: Dependencies are manually included
2. **No version control** for third-party libraries
3. **Mixed HTML/PHP**: Some files serve dual purposes (API + UI)
4. **No testing framework**: Manual testing required
5. **No build process**: Files are production-ready as written
6. **No localization system**: Spanish text is hardcoded

### Database Connection Pattern

**Every PHP file that uses the database follows this pattern**:
```php
$servername = "localhost";
$username = "seidev";
$password = "Tz9!qE7#Xr2@Lm5$Vp8^";
$database = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Error de conexión']));
}

$conn->set_charset("utf8mb4"); // Support Spanish characters
```

### Email Sending Pattern

**Standard PHPMailer setup used across all email-sending files**:
```php
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = '10.24.254.104';
$mail->SMTPAuth = true;
$mail->Username = 'contacto@bioceanicocentral.cl';
$mail->Password = '@@Bio2025';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->CharSet = 'UTF-8';
$mail->setFrom('contacto@bioceanicocentral.cl', 'Nodo Bioceánico Central');
```

---

## Testing Utilities

### Test Email Functionality
```bash
# Access test_mail.php via browser or CLI
php test_mail.php
```

### Test QR Code Generation
```bash
php test_qr.php
```

### Test Database Connection
```php
<?php
$conn = new mysqli("localhost", "seidev", "Tz9!qE7#Xr2@Lm5$Vp8^", "registro_evento");
echo $conn->connect_error ? "Failed" : "Connected";
?>
```

### Monitor Email Campaign Progress
1. Start mass email: `php envio_masivo.php` (or access via browser)
2. Monitor dashboard: Open `dashboard_envios.html`
3. Check logs: `tail -f envio_detallado.log`
4. Review state: `cat estado_envio.json`

---

## Git Workflow

### Current Branch
- **Development Branch**: `claude/claude-md-mi8dd4kes1c27bge-01XkPU6QYfvoA3Tg9hCkxVC6`
- **All changes** should be developed and pushed to this branch

### Commit Guidelines
1. **Clear messages**: Describe what changed and why
2. **Logical commits**: Group related changes
3. **Test before committing**: Verify functionality
4. **Push when complete**: Use `git push -u origin <branch-name>`

### Files to Ignore
- `*.log` files (already in .gitignore if exists)
- `qrcodes/*.png` (generated content)
- `phpqrcode/cache/*` (cache files)
- `estado_envio.json` (runtime state)

---

## Quick Reference

### File Locations for Common Changes

| What to Change | File to Edit | Line Range (Approx) |
|----------------|--------------|---------------------|
| Registration form fields | `registro.html` | 200-800 |
| Email template | `guardar_registro.php` | 100-300 |
| Scanner UI | `scanner.php` | 50-250 |
| Email throttling | `envio_masivo.php` | 100-200 |
| Attendance validation | `api/registrar_asistencia.php` | 30-80 |
| Search functionality | `api/buscar_personas.php` | 20-60 |
| Email tracking | `track.php` | Full file (~80 lines) |

### Database Tables Quick Access
```sql
-- View all registrations
SELECT * FROM registros ORDER BY fecha_registro DESC LIMIT 100;

-- View INACAP registrations
SELECT * FROM registros_inacap ORDER BY fecha_registro DESC;

-- View attendance records
SELECT * FROM asistencias ORDER BY timestamp DESC LIMIT 100;

-- Check blacklist
SELECT * FROM blocked_attempts ORDER BY timestamp DESC;
```

### Common Regex Patterns in Code
- **Email validation**: `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/`
- **Code pattern**: `/^(REG|INACAP)\d{17}$/`
- **Phone numbers**: Often validated in JavaScript before submission

---

## Environment Setup

### Requirements
- PHP 7.0+ (with MySQLi extension)
- MySQL 5.7+
- Apache 2.4+ (with mod_rewrite)
- GD Library (for QR code generation)

### PHP Extensions Needed
```ini
extension=mysqli
extension=gd
extension=mbstring
extension=openssl  # For SMTP TLS
```

### File Permissions
```bash
# QR code directory must be writable
chmod 755 qrcodes/
chmod 644 qrcodes/*.png

# Cache directory for phpqrcode
chmod 755 phpqrcode/cache/
```

---

## Troubleshooting Guide

### Issue: Emails not sending
1. Check SMTP credentials in PHP files
2. Verify SMTP server is reachable: `telnet 10.24.254.104 587`
3. Enable PHPMailer debug: `$mail->SMTPDebug = 2;`
4. Check recipient is not in exclusion list

### Issue: QR codes not generating
1. Verify GD library: `php -m | grep gd`
2. Check `qrcodes/` directory permissions
3. Test with `test_qr.php`
4. Check disk space: `df -h`

### Issue: Scanner not working
1. Verify HTTPS (camera requires secure context)
2. Check browser permissions for camera access
3. Test with different browsers (Chrome recommended)
4. Verify html5-qrcode CDN is accessible

### Issue: Database connection failed
1. Verify MySQL is running: `systemctl status mysql`
2. Test credentials: `mysql -u seidev -p registro_evento`
3. Check `bind-address` in MySQL config (should allow localhost)
4. Review error logs: `/var/log/mysql/error.log`

### Issue: .htaccess blocking PHP files
**Current .htaccess blocks ALL PHP files** - this may need adjustment:
```apache
# More selective approach (recommended):
<FilesMatch "^(test_|envio_masivo|scanner)\.php$">
    Require all denied
</FilesMatch>
```

---

## Performance Considerations

### Mass Email Optimization
- Current: 15 emails/batch, 3s delay, 45s between batches
- Theoretical max: ~180 emails/hour
- For large campaigns (>1000), consider:
  - Background queue system
  - Dedicated email service (SendGrid, AWS SES)
  - Async processing

### Database Optimization
```sql
-- Add indexes for common queries
CREATE INDEX idx_email ON registros(email);
CREATE INDEX idx_codigo ON registros(codigo);
CREATE INDEX idx_fecha_asistencia ON asistencias(fecha_asistencia);
CREATE INDEX idx_email_enviado ON registros(email_enviado, email_abierto);
```

### QR Code Caching
- Generated QR codes are stored in `/qrcodes/` (one-time generation)
- PHPMailer embeds QR codes in emails (no external image hosting)
- Consider: Periodic cleanup of old QR codes if disk space is limited

---

## Business Networking System (Rueda de Negocios)

### Overview
The system includes a specialized email campaign for the "Rueda de Negocios" (Business Roundtable) - a networking event within the main conference where participants can have 1-on-1 meetings with business leaders and decision-makers.

### How It Works

**Registration Phase**:
- During registration, attendees can express interest in the business roundtable via the `rueda` field
- Database stores `rueda = 'Si'` for interested participants

**Email Campaign**:
- **Script**: `envio_rueda_negocios.php`
- **Dashboard**: `dashboard_rueda.html`
- **SQL Filter**: `WHERE rueda = 'Si'`
- **Email Template**: Participation instructions with:
  - Event date and time confirmation (Nov 28, 2025, 11:00-12:30)
  - Two participation modalities explained (A: Receive Requests, B: Request Meetings)
  - Time blocks table (4 blocks of 15 minutes each)
  - Capacity information (15 tables, 60 max meetings)
  - System access instructions with portal URL and credentials
  - Step-by-step instructions for each modality

**Campaign Features**:
- Same throttling as main campaign (15 emails/batch, 3s delay, 45s between batches)
- Separate state tracking (`estado_envio_rueda.json`)
- Separate detailed logs (`envio_rueda_detallado.log`)
- Real-time progress monitoring via dedicated dashboard
- Email open tracking integrated

### How to Run the Campaign

1. **Test the Email First** (Recommended):
   ```bash
   # Edit test_envio_rueda.php and change the email address
   # OR access via browser with email parameter:
   # test_envio_rueda.php?email=tu@email.com
   ```
   - The script will use real data from a registration with `rueda='Si'`
   - Email subject includes `[PRUEBA]` prefix to distinguish from real campaign
   - Verify design, content, QR code, and overall appearance
   - Check email tracking pixel works

2. **Verify Database**:
   ```sql
   SELECT COUNT(*) FROM registros WHERE rueda = 'Si';
   ```

3. **Open Dashboard**:
   - Navigate to `dashboard_rueda.html` in browser
   - Dashboard shows campaign info and real-time metrics

4. **Start Campaign**:
   - Click "Iniciar Envío a Rueda de Negocios" button
   - Script runs in background (`envio_rueda_negocios.php`)
   - Monitor progress in real-time

5. **Monitor Results**:
   - Check logs: `tail -f envio_rueda_detallado.log`
   - Check state: `cat estado_envio_rueda.json`
   - View dashboard for visual progress

### Email Template Content

The participation instructions email includes:

**Header**:
- Event title and date: November 28, 2025, 11:00-12:30 hrs
- Confirmation of participation

**System Explanation**:
- Two modalities clearly explained with visual boxes
- **Empresas que Buscan Servicios/Proveedores** (Companies seeking services): Fixed table, receive and approve/reject meeting requests
- **Empresas que Ofrecen Servicios/Productos** (Companies offering services): Explore companies and request meetings

**Time Blocks**:
- Table with 4 time blocks (15 minutes each)
- Block 1: 11:00 - 11:15
- Block 2: 11:20 - 11:35
- Block 3: 11:40 - 11:55
- Block 4: 12:00 - 12:15

**Capacity Information**:
- 15 physical tables
- Maximum 60 meetings

**Step-by-Step Instructions**:
- Separate instructions for each modality
- Clear numbered steps from login to event day

**System Access**:
- Simplified CTA button linking directly to the system portal
- Portal URL: https://www.bioceanicocentral.cl/rueda-negocios-arica/views/registro.php
- Login credentials table removed for simplicity

**Tracking**:
- Email open tracking pixel included

### File Locations

| Component | File Path |
|-----------|-----------|
| Test Script | `/home/user/registro/test_envio_rueda.php` |
| Campaign Script | `/home/user/registro/envio_rueda_negocios.php` |
| Dashboard | `/home/user/registro/dashboard_rueda.html` |
| State File | `/home/user/registro/estado_envio_rueda.json` |
| Detailed Log | `/home/user/registro/envio_rueda_detallado.log` |

### Customization

To modify the email template:
1. Edit `envio_rueda_negocios.php`
2. Find the heredoc block starting with `$mail->Body = "`
3. Modify HTML content (remember to use HTML entities for Spanish characters)
4. Test before running campaign

---

## Contact Information for Context

**Event Details**:
- **Name**: Nodo Bioceánico Central 2025
- **Organizer**: contacto@bioceanicocentral.cl
- **Dates**: November 26-28, 2025
- **Location**: Arica, Chile

**Technical Context**:
- **Type**: Conference/business networking event
- **Expected Attendees**: Mix of general public + INACAP institution members
- **Key Features**: Registration, QR check-in, email campaigns, attendance tracking

---

## Changelog

### 2025-11-21 - Simplified Rueda de Negocios Email Template (Final Version)
- Replaced generic "Modalidad A/B" terminology with descriptive business-focused names:
  - "Empresas que Buscan Servicios/Proveedores" (Companies seeking services/suppliers)
  - "Empresas que Ofrecen Servicios/Productos" (Companies offering services/products)
- Removed detailed access credentials table, kept only prominent CTA button
- Removed "Sistema de reserva por orden de llegada" from capacity section
- Removed modality verification from "Importante" section
- Updated AltBody plain text versions to match HTML changes
- Applied changes to both production and test scripts
- Updated CLAUDE.md documentation to reflect final template

### 2025-11-21 - Updated Rueda de Negocios Email Template
- Updated email template with complete participation instructions
- New subject: "🤝 Rueda de Negocios Arica - 28 de Noviembre | Instrucciones de Participación"
- Added detailed explanation of two participation modalities (A and B)
- Included time blocks table (4 blocks of 15 minutes)
- Added system access information with portal URL
- Included step-by-step instructions for each modality
- Updated both `envio_rueda_negocios.php` and `test_envio_rueda.php`
- Updated CLAUDE.md documentation with new email content

### 2025-11-21 - Business Networking Campaign System Added
- Created `envio_rueda_negocios.php` - mass email campaign for business roundtable
- Created `dashboard_rueda.html` - real-time monitoring dashboard
- Added specialized email template with networking invitation
- Implemented SQL filtering by `rueda = 'Si'` field
- Separate state tracking and logging for campaign isolation
- Updated CLAUDE.md with comprehensive documentation

### 2025-11-21 - Initial CLAUDE.md Creation
- Comprehensive codebase analysis completed
- Documentation created for AI assistant guidance
- Security concerns documented
- Development workflows mapped
- Common tasks and troubleshooting guide added

---

## Additional Resources

- **PHPMailer Documentation**: https://github.com/PHPMailer/PHPMailer
- **phpqrcode Documentation**: http://phpqrcode.sourceforge.net/
- **html5-qrcode GitHub**: https://github.com/mebjas/html5-qrcode
- **MySQL Documentation**: https://dev.mysql.com/doc/

---

*This document was generated by AI analysis of the codebase and should be updated as the project evolves.*
