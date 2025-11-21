<?php
/* ============================================================
   MODO API (BUSCAR / REGISTRAR ASISTENCIA)
   ============================================================ */
$servername = "localhost";
$username   = "seidev";
$password   = "Tz9!qE7#Xr2@Lm5$Vp8^";
$database   = "registro_evento";

$conn = new mysqli($servername, $username, $password, $database);

if (isset($_GET['action'])) {

    header("Content-Type: application/json");

    /* --- BÚSQUEDA DE PERSONAS --- */
    if ($_GET['action'] === "buscar") {
        $q = $_GET['q'] ?? "";
        $like = "%$q%";

        $stmt = $conn->prepare("
            SELECT nombre, apellido, empresa, codigo_registro
            FROM registros
            WHERE nombre LIKE ? OR apellido LIKE ?
            ORDER BY nombre ASC
            LIMIT 30
        ");
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();

        $data = [];
        while ($row = $res->fetch_assoc()) { $data[] = $row; }

        echo json_encode($data);
        exit;
    }

    /* --- REGISTRAR ASISTENCIA --- */
    if ($_GET['action'] === "asistencia") {
        $input = json_decode(file_get_contents("php://input"), true);

        $codigo = $input['codigo'] ?? "";
        $dia    = $input['dia_evento'] ?? "";

        if (!$codigo || !$dia) {
            echo json_encode(["status"=>"error","message"=>"Datos incompletos"]);
            exit;
        }

        // BUSCA REGISTRO
        $stmt = $conn->prepare("SELECT * FROM registros WHERE codigo_registro = ?");
        $stmt->bind_param("s", $codigo);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 0) {
            echo json_encode(["status"=>"error","message"=>"QR no válido"]);
            exit;
        }

        $row = $res->fetch_assoc();

        // GUARDAR ASISTENCIA
        $stmt = $conn->prepare("
            INSERT INTO asistencia (codigo_registro, dia_evento, fecha_hora)
            VALUES (?, ?, NOW())
        ");
        $stmt->bind_param("ss", $codigo, $dia);
        $stmt->execute();

        echo json_encode([
            "status"=>"ok",
            "codigo"=>$codigo,
            "nombre"=>$row['nombre']." ".$row['apellido'],
            "institucion"=>$row['empresa'],
            "dia"=>$dia
        ]);
        exit;
    }
}

/* ============================================================
   SI NO ES UNA LLAMADA API → MOSTRAR LA INTERFAZ COMPLETA
   ============================================================ */
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" 
      content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no">
<title>Registro de Asistencia - Nodo Bioceánico</title>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
    * { margin:0; padding:0; box-sizing:border-box; }

    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        background: linear-gradient(135deg,#667eea,#764ba2);
        min-height:100vh; padding:15px; color:white;
    }

    .container { max-width:480px; margin:auto; }

    .header {
        background:white; color:#333; padding:20px;
        border-radius:12px 12px 0 0; text-align:center;
    }

    .header h1 { font-size:1.3rem; color:#667eea; margin-bottom:6px; }
    .header p { font-size:0.9rem; }

    .box {
        background:white; border-radius:0 0 12px 12px;
        padding:20px; color:#333;
    }

    .btn {
        width:100%; padding:15px; border-radius:12px;
        border:none; font-size:1.1rem; font-weight:bold;
        margin-bottom:10px; color:white; cursor:pointer;
    }

    #btnScan { background:#667eea; }
    #btnManual { background:#28a745; }

    #reader {
        width:100%; display:none; margin-top:10px;
        border-radius:12px; overflow:hidden;
    }

    #result {
        margin-top:15px; display:none; padding:15px;
        border-radius:12px;
    }

    .ok    { background:#d4edda; border-left:5px solid #28a745; }
    .error { background:#f8d7da; border-left:5px solid #dc3545; }

    /* MODAL FULLSCREEN */
    #modal {
        position:fixed; top:0; left:0; width:100%; height:100%;
        background:white; z-index:99999; padding:20px;
        display:none; overflow-y:auto;
    }

    #searchInput {
        width:100%; padding:15px; font-size:1.2rem;
        border:2px solid #667eea; border-radius:12px;
        margin-bottom:15px;
    }

    .result-item {
        padding:18px; border-bottom:1px solid #eee;
        font-size:1.2rem;
    }
</style>
</head>
<body>

<div class="container">

    <div class="header">
        <h1>Registro de Asistencia</h1>
        <p>Nodo Bioceánico 2025</p>
    </div>

    <div class="box">

        <label style="font-weight:bold;">Día del evento:</label>
        <select id="dia" 
                style="width:100%;padding:14px;border-radius:12px;border:2px solid #667eea;font-size:1.1rem;margin-bottom:15px;">
            <option value="">Seleccione...</option>
            <option value="2025-11-26">Miércoles 26</option>
            <option value="2025-11-27">Jueves 27</option>
            <option value="2025-11-28">Viernes 28</option>
        </select>

        <button id="btnScan"   class="btn">📷 Iniciar escaneo</button>
        <button id="btnManual" class="btn">🔍 Buscar manual</button>

        <div id="reader"></div>
        <div id="result"></div>
    </div>
</div>

<!-- MODAL DE BÚSQUEDA -->
<div id="modal">
    <h2 style="text-align:center;color:#333;margin-bottom:10px;">Buscar invitado</h2>
    <input id="searchInput" type="text" placeholder="Escribe nombre o apellido...">

    <div id="searchList"></div>

    <button onclick="closeModal()" 
            style="width:100%;padding:15px;margin-top:15px;border:none;border-radius:12px;background:#dc3545;color:white;font-size:1.1rem;">
        Cerrar
    </button>
</div>

<script>
let qrScanner=null;
let scanning=false;

const btnScan   = document.getElementById("btnScan");
const btnManual = document.getElementById("btnManual");
const reader    = document.getElementById("reader");
const resultBox = document.getElementById("result");
const dia       = document.getElementById("dia");

/* ---------------------- */
/* SCAN QR                */
/* ---------------------- */
btnScan.onclick = async () => {
    if (!dia.value) return alert("Seleccione un día");

    if (!scanning) startScan();
    else stopScan();
};

async function startScan() {
    qrScanner = new Html5Qrcode("reader");

    reader.style.display = "block";

    await qrScanner.start(
        { facingMode:"environment" },
        { fps:10, qrbox:250 },
        onScanOK
    );

    scanning=true;
    btnScan.textContent="⛔ Detener escaneo";
    btnScan.style.background="#dc3545";
}

async function stopScan() {
    if (qrScanner) {
        await qrScanner.stop();
        qrScanner.clear();
    }
    scanning=false;
    reader.style.display="none";
    btnScan.textContent="📷 Iniciar escaneo";
    btnScan.style.background="#667eea";
}

async function onScanOK(code) {
    stopScan();
    registrar(code);
}

/* ---------------------- */
/* REGISTRAR ASISTENCIA   */
/* ---------------------- */
async function registrar(code) {
    resultBox.style.display="none";

    const r = await fetch("asistencia.php?action=asistencia", {
        method:"POST",
        headers:{ "Content-Type":"application/json" },
        body:JSON.stringify({codigo:code, dia_evento:dia.value})
    });

    const data = await r.json();

    if (data.status==="ok") {
        resultBox.className="ok";
        resultBox.innerHTML = `
            <strong>✔ Asistencia registrada</strong><br>
            ${data.nombre}<br>
            ${data.institucion ?? ""}
        `;
    } else {
        resultBox.className="error";
        resultBox.innerHTML = `<strong>❌ Error:</strong> ${data.message}`;
    }

    resultBox.style.display="block";

    setTimeout(()=>startScan(), 2000);
}

/* ---------------------- */
/* BÚSQUEDA MANUAL        */
/* ---------------------- */
btnManual.onclick = () => {
    if (!dia.value) return alert("Seleccione un día primero");

    openModal();
    searchInput.focus();
};

function openModal() {
    document.getElementById("modal").style.display="block";
}
function closeModal() {
    document.getElementById("modal").style.display="none";
    searchInput.value="";
    searchList.innerHTML="";
}

const searchInput = document.getElementById("searchInput");
const searchList  = document.getElementById("searchList");

searchInput.onkeyup = async () => {
    const q = searchInput.value.trim();
    if (q.length<2) { searchList.innerHTML=""; return; }

    const res = await fetch("asistencia.php?action=buscar&q="+encodeURIComponent(q));
    const data = await res.json();

    if (data.length===0) {
        searchList.innerHTML="<p style='padding:20px;text-align:center;color:#555;'>Sin resultados</p>";
        return;
    }

    let html="";
    data.forEach(p=>{
        html += `
            <div class="result-item" onclick="selectUser('${p.codigo_registro}')">
                <strong>${p.nombre} ${p.apellido}</strong><br>
                <span style="font-size:0.9rem;color:#666;">${p.empresa ?? ""}</span>
            </div>`;
    });

    searchList.innerHTML=html;
};

async function selectUser(code) {
    closeModal();
    registrar(code);
}
</script>

</body>
</html>
