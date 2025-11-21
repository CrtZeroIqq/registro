<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro de Asistencia - QR Scanner</title>
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

/* ✓ SONIDOS */
const successSound = new Audio("success.mp3");
const errorSound = new Audio("error.mp3");

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
        alert("Error iniciando cámara");
    }
}

async function stopScanning() {
    if (html5QrCode) {
        await html5QrCode.stop();
        html5QrCode.clear();
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
    enviarAsistencia(qrCode, true);  // TRUE = viene de QR
}

/* =====================
     BUSCADOR MANUAL
======================*/

document.getElementById("btnOpenSearch").onclick = () =>
    document.getElementById("modalSearch").style.display = "flex";

document.getElementById("closeModal").onclick = () =>
    document.getElementById("modalSearch").style.display = "none";

document.getElementById("btnDoSearch").onclick = async () => {
    const nombre = document.getElementById("searchNombre").value;
    const empresa = document.getElementById("searchEmpresa").value;
    const pais = document.getElementById("searchPais").value;

    const res = await fetch(`api/buscar_personas.php?nombre=${nombre}&empresa=${empresa}&pais=${pais}`);
    const data = await res.json();

    let html = "";
    data.forEach(p => {
        html += `
            <div class="result-item" onclick="registrarManual('${p.codigo}')">
                <strong>${p.nombre}</strong><br>
                Empresa: ${p.empresa}<br>
                País: ${p.pais}<br>
                <small>Código: ${p.codigo}</small>
            </div>`;
    });

    document.getElementById("searchResults").innerHTML = html || "<p>No se encontraron resultados.</p>";
};

window.registrarManual = function(codigo) {
    document.getElementById("modalSearch").style.display = "none";
    enviarAsistencia(codigo, false); // FALSE = NO reinicia QR
};

/* ==========================
      ENVÍO AL SERVIDOR
==========================*/

async function enviarAsistencia(codigo, desdeQR) {
    const res = await fetch("api/registrar_asistencia.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ codigo, dia_evento: selectedDay })
    });

    const data = await res.json();
    mostrarResultado(data);

    if (data.status === "ok") successSound.play();
    else errorSound.play();

    if (desdeQR) setTimeout(() => startScanning(), 2500);
}

function mostrarResultado(data) {
    const card = document.getElementById("result");
    card.style.display = "block";

    if (data.status === "ok") {
        card.innerHTML = `
            <h3>✅ Asistencia Registrada</h3>
            <p><strong>${data.nombre}</strong></p>
            <p>Día: ${data.dia_evento_formatted}</p>
            <p>Tipo: ${data.tipo_asistente.toUpperCase()}</p>
        `;
    } else {
        card.innerHTML = `
            <h3>❌ Error</h3>
            <p>${data.message}</p>
        `;
    }
}
</script>
</body>
</html>
