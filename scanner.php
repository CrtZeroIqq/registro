<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro de Asistencia - QR Scanner (Modo Offline)</title>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 20px;
    }

    .container { max-width: 500px; margin: 0 auto; }

    .header {
        background: #fff;
        padding: 20px;
        border-radius: 15px 15px 0 0;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .header h1 { color: #667eea; margin-bottom: 5px; }
    .header p { color: #666; }

    /* INDICADOR DE ESTADO DE CONEXIÓN */
    .connection-status {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        margin-top: 10px;
        border-radius: 8px;
        font-weight: bold;
        font-size: 0.9rem;
        transition: all 0.3s;
    }

    .connection-status.online {
        background: #d4edda;
        color: #155724;
    }

    .connection-status.offline {
        background: #fff3cd;
        color: #856404;
    }

    .connection-status.syncing {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        margin-right: 8px;
        animation: pulse 2s infinite;
    }

    .status-indicator.online { background: #28a745; }
    .status-indicator.offline { background: #ffc107; }
    .status-indicator.syncing { background: #17a2b8; }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* COLA DE PENDIENTES */
    .pending-queue {
        background: #fff3cd;
        padding: 12px;
        margin-top: 10px;
        border-radius: 8px;
        border-left: 4px solid #ffc107;
        display: none;
    }

    .pending-queue.show {
        display: block;
    }

    .pending-queue h4 {
        margin: 0 0 8px 0;
        color: #856404;
        font-size: 0.9rem;
    }

    .pending-queue p {
        margin: 0;
        color: #856404;
        font-size: 0.85rem;
    }

    .pending-queue button {
        margin-top: 8px;
        padding: 6px 12px;
        background: #ffc107;
        border: none;
        border-radius: 5px;
        color: #000;
        font-weight: bold;
        cursor: pointer;
        font-size: 0.85rem;
    }

    .pending-queue button:hover {
        background: #e0a800;
    }

    .scanner-container {
        background: #fff;
        padding: 20px;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    #reader { display: none; margin-bottom: 20px; border-radius: 10px; overflow: hidden; }

    .btn-scan, .btn-search {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg,#667eea,#764ba2);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1.1rem;
        font-weight: bold;
        cursor: pointer;
        margin-top: 10px;
    }
    .btn-search { background: #0056b3; }

    .result-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
        display: none;
    }

    /* MODAL */
    .modal {
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.6);
        display: none;
        justify-content: center;
        align-items: center;
        padding: 20px;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        width: 100%;
        height: 90%;
        border-radius: 10px;
        padding: 20px;
        overflow-y: auto;
        position: relative;
    }

    .close-modal {
        position: absolute;
        right: 15px;
        top: 15px;
        background: #dc3545;
        padding: 10px 15px;
        color: white;
        border: none;
        border-radius: 5px;
        font-weight: bold;
        cursor: pointer;
    }

    .search-input {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: 2px solid #ccc;
        margin-bottom: 10px;
        font-size: 1rem;
    }

    .result-item {
        padding: 15px;
        border-bottom: 1px solid #ddd;
        cursor: pointer;
    }

    .result-item:hover {
        background: #f1f1f1;
    }

</style>
</head>

<body>
<div class="container">

    <div class="header">
        <h1>📋 Registro de Asistencia</h1>
        <p>Nodo Bioceánico 2025</p>

        <!-- INDICADOR DE ESTADO DE CONEXIÓN -->
        <div id="connectionStatus" class="connection-status online">
            <span class="status-indicator online"></span>
            <span id="connectionText">En línea</span>
        </div>

        <!-- COLA DE PENDIENTES -->
        <div id="pendingQueue" class="pending-queue">
            <h4>⚠️ Scans pendientes de sincronizar</h4>
            <p><span id="pendingCount">0</span> registros esperando conexión</p>
            <button id="btnSyncNow">Sincronizar ahora</button>
        </div>
    </div>

    <div class="scanner-container">

        <!-- Selector de día -->
        <div id="daySelector" class="day-selector">
            <h3 style="text-align:center;">📅 Selecciona el día</h3>
            <button class="btn-scan" data-day="2025-11-26">Miércoles 26</button>
            <button class="btn-scan" data-day="2025-11-27">Jueves 27</button>
            <button class="btn-scan" data-day="2025-11-28">Viernes 28</button>
        </div>

        <div id="selectedDayInfo" style="display:none; margin-bottom:15px; padding:10px; background:#e7f3ff; border-radius:10px;">
            <strong>Día seleccionado:</strong> <span id="selectedDayText"></span>
            <button id="btnChangeDay" style="margin-left:10px; padding:5px 10px; background:#667eea; color:#fff; border:none; border-radius:5px;">Cambiar</button>
        </div>

        <button id="btnStartScan" class="btn-scan" style="display:none;">📷 Iniciar Escaneo</button>
        <button id="btnOpenSearch" class="btn-search" style="display:none;">🔍 Buscar sin QR</button>

        <div id="reader"></div>

        <div id="result" class="result-card"></div>

    </div>
</div>

<!-- MODAL -->
<div id="modalSearch" class="modal">
    <div class="modal-content">
        <button class="close-modal" id="closeModal">Cerrar</button>

        <h2>🔍 Buscar Persona</h2>
        <p>Usa uno o más filtros</p>

        <div id="dbStatusModal" style="padding: 8px; margin-bottom: 10px; background: #e7f3ff; border-radius: 5px; font-size: 0.85rem; display: none;">
            <span id="dbStatusText"></span>
        </div>

        <input id="searchNombre" class="search-input" placeholder="Nombre o Apellido">
        <input id="searchEmpresa" class="search-input" placeholder="Empresa">
        <input id="searchPais" class="search-input" placeholder="País">

        <button id="btnDoSearch" class="btn-scan">Buscar</button>

        <div id="searchResults"></div>
    </div>
</div>

<script>
let selectedDay = null;
let html5QrCode;
let isScanning = false;

/* ========================
   MODO OFFLINE - VARIABLES
======================== */
let isOnline = navigator.onLine;
let isSyncing = false;
let connectionCheckInterval;
let autoSyncInterval;
let dbSyncInterval;

const STORAGE_KEY = 'pending_scans';
const DB_CACHE_KEY = 'cached_registros';
const DB_TIMESTAMP_KEY = 'db_last_update';
const SYNC_INTERVAL = 30000; // 30 segundos
const CONNECTION_CHECK_INTERVAL = 10000; // 10 segundos
const DB_SYNC_INTERVAL = 300000; // 5 minutos

/* ✓ SONIDOS */
const successSound = new Audio("success.mp3");
const errorSound = new Audio("error.mp3");

/* ========================
   MODO OFFLINE - FUNCIONES
======================== */

// Obtener scans pendientes del localStorage
function getPendingScans() {
    try {
        const data = localStorage.getItem(STORAGE_KEY);
        return data ? JSON.parse(data) : [];
    } catch (e) {
        console.error('Error al leer localStorage:', e);
        return [];
    }
}

// Guardar scans pendientes
function savePendingScans(scans) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(scans));
        updatePendingQueueUI();
    } catch (e) {
        console.error('Error al guardar en localStorage:', e);
    }
}

// Agregar scan pendiente
function addPendingScan(codigo, dia_evento) {
    const pendingScans = getPendingScans();
    pendingScans.push({
        codigo,
        dia_evento,
        timestamp: new Date().toISOString()
    });
    savePendingScans(pendingScans);
}

// Actualizar UI de cola de pendientes
function updatePendingQueueUI() {
    const pendingScans = getPendingScans();
    const count = pendingScans.length;

    document.getElementById('pendingCount').textContent = count;

    if (count > 0) {
        document.getElementById('pendingQueue').classList.add('show');
    } else {
        document.getElementById('pendingQueue').classList.remove('show');
    }
}

/* ========================
   BASE DE DATOS LOCAL
======================== */

// Obtener registros cacheados
function getCachedRegistros() {
    try {
        const data = localStorage.getItem(DB_CACHE_KEY);
        return data ? JSON.parse(data) : [];
    } catch (e) {
        console.error('Error al leer cache de BD:', e);
        return [];
    }
}

// Guardar registros en cache
function saveCachedRegistros(registros) {
    try {
        localStorage.setItem(DB_CACHE_KEY, JSON.stringify(registros));
        localStorage.setItem(DB_TIMESTAMP_KEY, new Date().toISOString());
        console.log(`✅ BD cacheada: ${registros.length} registros`);
        updateDBStatusUI();
    } catch (e) {
        console.error('Error al cachear BD:', e);
    }
}

// Obtener timestamp de última actualización
function getDBLastUpdate() {
    return localStorage.getItem(DB_TIMESTAMP_KEY);
}

// Descargar y cachear base de datos
async function downloadAndCacheDB() {
    if (!isOnline) {
        console.log('📴 Offline: no se puede descargar BD');
        return false;
    }

    try {
        console.log('🔄 Descargando base de datos...');

        const response = await fetch('api/obtener_registros.php', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();

        if (result.status === 'ok') {
            saveCachedRegistros(result.registros);
            console.log(`✅ BD descargada: ${result.total} registros`);
            return true;
        } else {
            throw new Error(result.message || 'Error al descargar BD');
        }

    } catch (error) {
        console.error('❌ Error al descargar BD:', error);
        return false;
    }
}

// Búsqueda local en cache
function searchLocalDB(nombre, empresa, pais) {
    const registros = getCachedRegistros();

    if (registros.length === 0) {
        return [];
    }

    const nombreLower = nombre.toLowerCase().trim();
    const empresaLower = empresa.toLowerCase().trim();
    const paisLower = pais.toLowerCase().trim();

    return registros.filter(r => {
        const matchNombre = !nombre || r.nombre.toLowerCase().includes(nombreLower);
        const matchEmpresa = !empresa || r.empresa.toLowerCase().includes(empresaLower);
        const matchPais = !pais || r.pais.toLowerCase().includes(paisLower);

        return matchNombre && matchEmpresa && matchPais;
    }).slice(0, 50); // Limitar a 50 resultados
}

// Actualizar UI de estado de BD
function updateDBStatusUI() {
    const lastUpdate = getDBLastUpdate();
    const cachedCount = getCachedRegistros().length;

    if (lastUpdate && cachedCount > 0) {
        const date = new Date(lastUpdate);
        const timeAgo = getTimeAgo(date);
        console.log(`📊 BD Local: ${cachedCount} registros (actualizado ${timeAgo})`);
    }
}

// Calcular tiempo transcurrido
function getTimeAgo(date) {
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffDays > 0) return `hace ${diffDays} día${diffDays > 1 ? 's' : ''}`;
    if (diffHours > 0) return `hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
    if (diffMins > 0) return `hace ${diffMins} minuto${diffMins > 1 ? 's' : ''}`;
    return 'recién';
}

// Actualizar indicador de conexión
function updateConnectionStatus(online, syncing = false) {
    const statusDiv = document.getElementById('connectionStatus');
    const statusIndicator = statusDiv.querySelector('.status-indicator');
    const statusText = document.getElementById('connectionText');

    statusDiv.className = 'connection-status';
    statusIndicator.className = 'status-indicator';

    if (syncing) {
        statusDiv.classList.add('syncing');
        statusIndicator.classList.add('syncing');
        statusText.textContent = 'Sincronizando...';
    } else if (online) {
        statusDiv.classList.add('online');
        statusIndicator.classList.add('online');
        statusText.textContent = 'En línea';
    } else {
        statusDiv.classList.add('offline');
        statusIndicator.classList.add('offline');
        statusText.textContent = 'Modo Offline';
    }
}

// Verificar conexión con ping al servidor
async function checkConnection() {
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);

        const response = await fetch('api/registrar_asistencia.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ codigo: 'PING_TEST', dia_evento: '2000-01-01' }),
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        // Cualquier respuesta (incluso error) significa que hay conexión
        if (!isOnline) {
            console.log('✅ Conexión restaurada - iniciando sincronización');
            isOnline = true;
            updateConnectionStatus(true);

            // Sincronizar inmediatamente al recuperar conexión
            await handleConnectionRestored();
        }
        return true;
    } catch (error) {
        if (isOnline) {
            isOnline = false;
            updateConnectionStatus(false);
            console.log('❌ Conexión perdida');
        }
        return false;
    }
}

// Manejar recuperación de conexión
async function handleConnectionRestored() {
    console.log('🔄 Manejando recuperación de conexión...');

    // Resetear flag de sincronización por si quedó en true
    if (isSyncing) {
        console.log('⚠️ Reseteando flag de sincronización');
        isSyncing = false;
    }

    const pendingScans = getPendingScans();
    const cachedCount = getCachedRegistros().length;

    console.log(`📊 Estado: ${pendingScans.length} scans pendientes, ${cachedCount} registros en BD local`);

    // 1. Sincronizar scans pendientes
    if (pendingScans.length > 0) {
        console.log(`🔄 Iniciando sincronización de ${pendingScans.length} scans pendientes...`);
        try {
            await syncPendingScans();
        } catch (error) {
            console.error('❌ Error en sincronización de scans:', error);
        }
    } else {
        console.log('✅ No hay scans pendientes para sincronizar');
    }

    // 2. Actualizar BD si es necesario
    const lastUpdate = getDBLastUpdate();
    const shouldUpdateDB = !lastUpdate ||
        (new Date() - new Date(lastUpdate)) > 3600000; // 1 hora

    if (shouldUpdateDB || cachedCount === 0) {
        console.log('🔄 Descargando actualización de BD...');
        try {
            await downloadAndCacheDB();
        } catch (error) {
            console.error('❌ Error al actualizar BD:', error);
        }
    } else {
        console.log(`✅ BD local actualizada (${cachedCount} registros)`);
    }

    console.log('✅ Sincronización post-reconexión completada');
}

// Sincronizar scans pendientes con retry
async function syncPendingScans(retryCount = 0) {
    const MAX_RETRIES = 3;
    const pendingScans = getPendingScans();

    if (pendingScans.length === 0) {
        console.log('✅ No hay scans pendientes');
        return { success: true, synced: 0 };
    }

    if (isSyncing) {
        console.log('⏳ Sincronización ya en curso');
        return { success: false, message: 'Sincronización en curso' };
    }

    isSyncing = true;
    updateConnectionStatus(isOnline, true);

    try {
        console.log(`🔄 Sincronizando ${pendingScans.length} scans... (intento ${retryCount + 1}/${MAX_RETRIES + 1})`);

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000);

        const response = await fetch('api/sincronizar_asistencias.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ asistencias: pendingScans }),
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();
        console.log('📥 Respuesta de sincronización:', result);

        if (result.status === 'ok') {
            // Eliminar los scans sincronizados exitosamente
            const failedScans = [];
            result.resultados.forEach((res, index) => {
                if (res.status === 'error') {
                    failedScans.push(pendingScans[index]);
                    console.log(`❌ Scan fallido: ${res.codigo} - ${res.message}`);
                } else {
                    console.log(`✅ Scan sincronizado: ${res.codigo}`);
                }
            });

            savePendingScans(failedScans);

            console.log(`✅ Sincronización completada: ${result.exitosos} exitosos, ${result.errores} errores`);

            if (result.exitosos > 0) {
                try {
                    successSound.play();
                } catch (e) {
                    console.log('No se pudo reproducir sonido');
                }

                mostrarResultado({
                    status: 'ok',
                    message: `✅ ${result.exitosos} registro(s) sincronizado(s)`
                });
            }

            return { success: true, synced: result.exitosos, errors: result.errores };
        } else {
            throw new Error(result.message || 'Error desconocido');
        }

    } catch (error) {
        console.error('❌ Error en sincronización:', error);

        // Retry con exponential backoff
        if (retryCount < MAX_RETRIES) {
            const delay = Math.pow(2, retryCount) * 2000; // 2s, 4s, 8s
            console.log(`🔄 Reintentando en ${delay/1000}s... (intento ${retryCount + 1}/${MAX_RETRIES})`);

            await new Promise(resolve => setTimeout(resolve, delay));

            isSyncing = false;
            return syncPendingScans(retryCount + 1);
        }

        console.error(`❌ Sincronización falló después de ${MAX_RETRIES} intentos`);
        return { success: false, message: error.message };

    } finally {
        // SIEMPRE resetear flags, incluso si hay error
        isSyncing = false;
        updateConnectionStatus(isOnline, false);
        console.log('🔄 Flags de sincronización reseteados');
    }
}

// Inicializar sistema offline
function initOfflineMode() {
    // Actualizar UI inicial
    updatePendingQueueUI();
    updateConnectionStatus(isOnline);
    updateDBStatusUI();

    // Event listeners del navegador
    window.addEventListener('online', () => {
        console.log('🌐 Evento online detectado');
        checkConnection();
    });

    window.addEventListener('offline', () => {
        console.log('📴 Evento offline detectado');
        isOnline = false;
        updateConnectionStatus(false);
    });

    // Verificación periódica de conexión
    connectionCheckInterval = setInterval(checkConnection, CONNECTION_CHECK_INTERVAL);

    // Auto-sincronización periódica de scans
    autoSyncInterval = setInterval(async () => {
        if (isOnline && !isSyncing && getPendingScans().length > 0) {
            console.log('🔄 Auto-sincronización periódica de scans...');
            await syncPendingScans();
        }
    }, SYNC_INTERVAL);

    // Auto-sincronización periódica de BD
    dbSyncInterval = setInterval(async () => {
        if (isOnline) {
            console.log('🔄 Auto-sincronización periódica de BD...');
            await downloadAndCacheDB();
        }
    }, DB_SYNC_INTERVAL);

    // Botón de sincronización manual
    document.getElementById('btnSyncNow').addEventListener('click', async () => {
        if (!isOnline) {
            alert('⚠️ No hay conexión a internet. Los datos se sincronizarán automáticamente cuando se restaure la conexión.');
            return;
        }
        await syncPendingScans();
    });

    // Verificación inicial y descarga de BD
    checkConnection().then(() => {
        if (isOnline) {
            // Descargar BD si no existe o está desactualizada (más de 1 hora)
            const lastUpdate = getDBLastUpdate();
            const shouldUpdate = !lastUpdate ||
                (new Date() - new Date(lastUpdate)) > 3600000; // 1 hora

            if (shouldUpdate) {
                console.log('📥 Descargando BD inicial...');
                downloadAndCacheDB();
            } else {
                console.log('✅ Usando BD cacheada');
                updateDBStatusUI();
            }
        } else {
            const cachedCount = getCachedRegistros().length;
            if (cachedCount === 0) {
                console.log('⚠️ No hay BD cacheada y estás offline. La búsqueda no funcionará.');
            } else {
                console.log(`✅ Usando BD cacheada offline: ${cachedCount} registros`);
            }
        }
    });
}

/* ✓ Selección de día */
document.querySelectorAll('[data-day]').forEach(btn => {
    btn.addEventListener('click', () => {
        selectedDay = btn.dataset.day;
        document.getElementById("selectedDayText").innerText = selectedDay;
        document.getElementById("daySelector").style.display = "none";
        document.getElementById("selectedDayInfo").style.display = "block";
        document.getElementById("btnStartScan").style.display = "block";
        document.getElementById("btnOpenSearch").style.display = "block";
    });
});

/* Cambiar día */
document.getElementById("btnChangeDay").onclick = () => {
    selectedDay = null;
    if (isScanning) stopScanning();
    document.getElementById("daySelector").style.display = "block";
    document.getElementById("selectedDayInfo").style.display = "none";
    document.getElementById("btnStartScan").style.display = "none";
    document.getElementById("btnOpenSearch").style.display = "none";
};

/* Escanear */
async function startScanning() {
    try {
        html5QrCode = new Html5Qrcode("reader");
        document.getElementById("reader").style.display = "block";
        document.getElementById("result").style.display = "none";

        await html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (qr) => onScanSuccess(qr)
        );

        isScanning = true;
        document.getElementById("btnStartScan").innerText = "⏹ Detener Escaneo";
    } catch (err) {
        console.error('Error iniciando cámara:', err);
        alert("Error iniciando cámara: " + err.message);
    }
}

async function stopScanning() {
    if (html5QrCode) {
        try {
            await html5QrCode.stop();
            html5QrCode.clear();
        } catch (err) {
            console.error('Error deteniendo scanner:', err);
        }
    }
    isScanning = false;
    document.getElementById("reader").style.display = "none";
    document.getElementById("btnStartScan").innerText = "📷 Iniciar Escaneo";
}

document.getElementById("btnStartScan").onclick = () => {
    if (!selectedDay) return alert("Selecciona un día");
    isScanning ? stopScanning() : startScanning();
};

async function onScanSuccess(qrCode) {
    stopScanning();
    enviarAsistencia(qrCode, true);
}

/* =====================
     BUSCADOR MANUAL
======================*/

document.getElementById("btnOpenSearch").onclick = () => {
    document.getElementById("modalSearch").style.display = "flex";

    // Actualizar estado de BD en modal
    const cachedCount = getCachedRegistros().length;
    const lastUpdate = getDBLastUpdate();
    const dbStatusModal = document.getElementById("dbStatusModal");
    const dbStatusText = document.getElementById("dbStatusText");

    if (cachedCount > 0 && lastUpdate) {
        const timeAgo = getTimeAgo(new Date(lastUpdate));
        const icon = isOnline ? '🌐' : '💾';
        dbStatusModal.style.display = "block";
        dbStatusText.innerHTML = `${icon} BD Local: <strong>${cachedCount}</strong> registros (actualizado ${timeAgo})`;
    } else if (!isOnline) {
        dbStatusModal.style.display = "block";
        dbStatusModal.style.background = "#fff3cd";
        dbStatusText.innerHTML = "⚠️ Sin BD local. Conecta a internet para descargar.";
    } else {
        dbStatusModal.style.display = "none";
    }
};

document.getElementById("closeModal").onclick = () =>
    document.getElementById("modalSearch").style.display = "none";

document.getElementById("btnDoSearch").onclick = async () => {
    const nombre = document.getElementById("searchNombre").value;
    const empresa = document.getElementById("searchEmpresa").value;
    const pais = document.getElementById("searchPais").value;

    let data = [];

    // Si estamos offline, usar búsqueda local
    if (!isOnline) {
        console.log('📴 Búsqueda offline en cache local');
        data = searchLocalDB(nombre, empresa, pais);

        if (data.length === 0) {
            const cachedCount = getCachedRegistros().length;
            if (cachedCount === 0) {
                document.getElementById("searchResults").innerHTML =
                    "<p>⚠️ No hay base de datos local. Conecta a internet para descargar los registros.</p>";
            } else {
                document.getElementById("searchResults").innerHTML =
                    "<p>No se encontraron resultados en la base de datos local.</p>";
            }
            return;
        }
    } else {
        // Si estamos online, intentar búsqueda en servidor
        try {
            console.log('🌐 Búsqueda online en servidor');
            const res = await fetch(`api/buscar_personas.php?nombre=${nombre}&empresa=${empresa}&pais=${pais}`);

            if (!res.ok) {
                throw new Error('Error en la búsqueda');
            }

            data = await res.json();
        } catch (error) {
            console.error('Error en búsqueda online:', error);

            // Fallback a búsqueda local
            console.log('⚠️ Error en búsqueda online, usando cache local');
            data = searchLocalDB(nombre, empresa, pais);

            if (data.length === 0) {
                document.getElementById("searchResults").innerHTML =
                    "<p>❌ Error al buscar en el servidor y no hay resultados en cache local.</p>";
                return;
            }
        }
    }

    // Mostrar resultados
    let html = "";
    const source = isOnline ? '🌐' : '💾';

    data.forEach(p => {
        html += `
            <div class="result-item" onclick="registrarManual('${p.codigo}')">
                <strong>${p.nombre}</strong> ${source}<br>
                Empresa: ${p.empresa}<br>
                País: ${p.pais}<br>
                <small>Código: ${p.codigo}</small>
            </div>`;
    });

    document.getElementById("searchResults").innerHTML = html || "<p>No se encontraron resultados.</p>";
};

window.registrarManual = function(codigo) {
    document.getElementById("modalSearch").style.display = "none";
    enviarAsistencia(codigo, false);
};

/* ==========================
      ENVÍO AL SERVIDOR
      CON MODO OFFLINE
==========================*/

async function enviarAsistencia(codigo, desdeQR) {
    try {
        // Si estamos offline, guardar directamente
        if (!isOnline) {
            console.log('📴 Modo offline: guardando localmente');
            addPendingScan(codigo, selectedDay);

            mostrarResultado({
                status: 'ok',
                message: '💾 Guardado offline (se sincronizará automáticamente)',
                nombre: codigo,
                dia_evento_formatted: selectedDay,
                tipo_asistente: 'pendiente'
            });

            successSound.play();

            if (desdeQR) setTimeout(() => startScanning(), 2500);
            return;
        }

        // Intentar enviar online
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000);

        const res = await fetch("api/registrar_asistencia.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ codigo, dia_evento: selectedDay }),
            signal: controller.signal
        });

        clearTimeout(timeoutId);

        if (!res.ok) {
            throw new Error(`HTTP ${res.status}`);
        }

        const data = await res.json();
        mostrarResultado(data);

        if (data.status === "ok") {
            successSound.play();
        } else {
            errorSound.play();
        }

        if (desdeQR) setTimeout(() => startScanning(), 2500);

    } catch (error) {
        console.error('❌ Error al enviar asistencia:', error);

        // Si hay error de red, guardar offline
        if (error.name === 'AbortError' || error.message.includes('fetch')) {
            isOnline = false;
            updateConnectionStatus(false);

            addPendingScan(codigo, selectedDay);

            mostrarResultado({
                status: 'ok',
                message: '💾 Sin conexión - Guardado offline',
                nombre: codigo,
                dia_evento_formatted: selectedDay,
                tipo_asistente: 'pendiente'
            });

            successSound.play();
        } else {
            mostrarResultado({
                status: 'error',
                message: '❌ Error: ' + error.message
            });

            errorSound.play();
        }

        if (desdeQR) setTimeout(() => startScanning(), 2500);
    }
}

function mostrarResultado(data) {
    const card = document.getElementById("result");
    card.style.display = "block";

    if (data.status === "ok" || data.status === "duplicado") {
        card.style.background = '#d4edda';
        card.style.borderLeft = '4px solid #28a745';
        card.innerHTML = `
            <h3>✅ Asistencia Registrada</h3>
            <p><strong>${data.nombre || 'Código: ' + data.codigo}</strong></p>
            <p>${data.message || ''}</p>
            ${data.dia_evento_formatted ? `<p>Día: ${data.dia_evento_formatted}</p>` : ''}
            ${data.tipo_asistente ? `<p>Tipo: ${data.tipo_asistente.toUpperCase()}</p>` : ''}
        `;
    } else {
        card.style.background = '#f8d7da';
        card.style.borderLeft = '4px solid #dc3545';
        card.innerHTML = `
            <h3>❌ Error</h3>
            <p>${data.message}</p>
        `;
    }
}

/* ========================
   INICIALIZAR AL CARGAR
======================== */
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Iniciando modo offline...');
    initOfflineMode();
});
</script>
</body>
</html>
