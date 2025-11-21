<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia - QR Scanner</title>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            color: #667eea;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 0.9rem;
        }

        .scanner-container {
            background: white;
            padding: 20px;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        #reader {
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            display: none;
        }

        .btn-scan {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn-scan:hover {
            transform: translateY(-2px);
        }

        .btn-scan:active {
            transform: translateY(0);
        }

        .btn-scan:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .result-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .result-card.success {
            background: #d4edda;
            border: 2px solid #28a745;
        }

        .result-card.error {
            background: #f8d7da;
            border: 2px solid #dc3545;
        }

        .result-card h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .result-card .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .result-card .info-row:last-child {
            border-bottom: none;
        }

        .result-card .label {
            font-weight: bold;
            color: #555;
        }

        .result-card .value {
            color: #333;
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: bold;
        }

        .badge.general {
            background: #007bff;
            color: white;
        }

        .badge.inacap {
            background: #E30613;
            color: white;
        }

        .status-indicator {
            text-align: center;
            padding: 10px;
            margin-top: 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .status-indicator.scanning {
            background: #fff3cd;
            color: #856404;
        }

        .status-indicator.success {
            background: #d4edda;
            color: #155724;
        }

        .status-indicator.error {
            background: #f8d7da;
            color: #721c24;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Estilos para selector de día */
        .day-selector {
            padding: 20px;
        }

        .day-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .btn-day {
            background: white;
            border: 3px solid #667eea;
            border-radius: 15px;
            padding: 20px 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .btn-day:hover {
            background: #667eea;
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-day .day-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .btn-day:hover .day-number {
            color: white;
        }

        .btn-day .day-name {
            font-size: 1rem;
            margin-top: 5px;
            font-weight: 600;
        }

        .btn-day .day-month {
            font-size: 0.85rem;
            color: #999;
            margin-top: 3px;
        }

        .btn-day:hover .day-month {
            color: #e0e0e0;
        }

        @media (max-width: 500px) {
            .day-buttons {
                grid-template-columns: 1fr;
            }
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
                <h3 style="text-align: center; color: #333; margin-bottom: 15px;">
                    📅 Selecciona el día del evento
                </h3>
                <div class="day-buttons">
                    <button class="btn-day" data-day="2025-11-26">
                        <div class="day-number">26</div>
                        <div class="day-name">Miércoles</div>
                        <div class="day-month">Nov 2025</div>
                    </button>
                    <button class="btn-day" data-day="2025-11-27">
                        <div class="day-number">27</div>
                        <div class="day-name">Jueves</div>
                        <div class="day-month">Nov 2025</div>
                    </button>
                    <button class="btn-day" data-day="2025-11-28">
                        <div class="day-number">28</div>
                        <div class="day-name">Viernes</div>
                        <div class="day-month">Nov 2025</div>
                    </button>
                </div>
            </div>

            <!-- Información del día seleccionado -->
            <div id="selectedDayInfo" style="display: none; text-align: center; padding: 15px; background: #e7f3ff; border-radius: 10px; margin-bottom: 15px;">
                <strong>Día seleccionado:</strong> <span id="selectedDayText"></span>
                <button id="btnChangeDay" style="margin-left: 10px; padding: 5px 10px; border: none; background: #667eea; color: white; border-radius: 5px; cursor: pointer;">Cambiar día</button>
            </div>

            <button id="btnStartScan" class="btn-scan" style="display: none;">
                📷 Iniciar Escaneo QR
            </button>

            <div id="reader"></div>

            <div id="status" class="status-indicator" style="display: none;"></div>

            <div id="loading" class="loading">
                <div class="spinner"></div>
                <p>Procesando...</p>
            </div>

            <div id="result" class="result-card"></div>
        </div>
    </div>

    <script>
        let html5QrCode;
        let isScanning = false;
        let selectedDay = null;
        
        const btnStartScan = document.getElementById('btnStartScan');
        const readerDiv = document.getElementById('reader');
        const statusDiv = document.getElementById('status');
        const loadingDiv = document.getElementById('loading');
        const resultDiv = document.getElementById('result');
        const daySelector = document.getElementById('daySelector');
        const selectedDayInfo = document.getElementById('selectedDayInfo');
        const selectedDayText = document.getElementById('selectedDayText');
        const btnChangeDay = document.getElementById('btnChangeDay');

        // Manejar selección de día
        document.querySelectorAll('.btn-day').forEach(btn => {
            btn.addEventListener('click', () => {
                selectedDay = btn.dataset.day;
                const dayNumber = btn.querySelector('.day-number').textContent;
                const dayName = btn.querySelector('.day-name').textContent;
                
                // Ocultar selector y mostrar info del día
                daySelector.style.display = 'none';
                selectedDayInfo.style.display = 'block';
                selectedDayText.textContent = `${dayName} ${dayNumber} de Noviembre 2025`;
                btnStartScan.style.display = 'block';
            });
        });

        // Cambiar día seleccionado
        btnChangeDay.addEventListener('click', () => {
            if (isScanning) {
                stopScanning();
            }
            selectedDay = null;
            daySelector.style.display = 'block';
            selectedDayInfo.style.display = 'none';
            btnStartScan.style.display = 'none';
            resultDiv.style.display = 'none';
        });

        // Iniciar escaneo
        btnStartScan.addEventListener('click', async () => {
            if (!selectedDay) {
                alert('Por favor selecciona un día primero');
                return;
            }
            
            if (!isScanning) {
                await startScanning();
            } else {
                await stopScanning();
            }
        });

        async function startScanning() {
            try {
                readerDiv.style.display = 'block';
                resultDiv.style.display = 'none';
                statusDiv.style.display = 'block';
                statusDiv.className = 'status-indicator scanning';
                statusDiv.textContent = '📷 Escaneando... Enfoca el QR';

                html5QrCode = new Html5Qrcode("reader");
                
                await html5QrCode.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    onScanSuccess,
                    onScanFailure
                );

                isScanning = true;
                btnStartScan.textContent = '⏹️ Detener Escaneo';
                btnStartScan.style.background = '#dc3545';
            } catch (err) {
                console.error('Error al iniciar cámara:', err);
                alert('Error al acceder a la cámara. Verifica los permisos.');
                readerDiv.style.display = 'none';
                statusDiv.style.display = 'none';
            }
        }

        async function stopScanning() {
            if (html5QrCode) {
                await html5QrCode.stop();
                html5QrCode.clear();
                isScanning = false;
                readerDiv.style.display = 'none';
                statusDiv.style.display = 'none';
                btnStartScan.textContent = '📷 Iniciar Escaneo QR';
                btnStartScan.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            }
        }

        async function onScanSuccess(decodedText, decodedResult) {
            console.log('QR detectado:', decodedText);
            
            // Detener escaneo temporalmente
            await stopScanning();
            
            // Mostrar loading
            loadingDiv.classList.add('active');
            resultDiv.style.display = 'none';

            // Enviar código al servidor con el día seleccionado
            try {
                const response = await fetch('api/registrar_asistencia.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        codigo: decodedText,
                        dia_evento: selectedDay
                    })
                });

                const data = await response.json();
                loadingDiv.classList.remove('active');

                if (data.status === 'ok') {
                    mostrarResultado(data, 'success');
                } else {
                    mostrarResultado(data, 'error');
                }

                // Reiniciar escaneo después de 3 segundos
                setTimeout(() => {
                    startScanning();
                }, 3000);

            } catch (error) {
                console.error('Error:', error);
                loadingDiv.classList.remove('active');
                mostrarResultado({
                    message: 'Error de conexión con el servidor'
                }, 'error');
                
                setTimeout(() => {
                    startScanning();
                }, 3000);
            }
        }

        function onScanFailure(error) {
            // No hacer nada, es normal que falle mientras busca el QR
        }

        function mostrarResultado(data, tipo) {
            resultDiv.style.display = 'block';
            resultDiv.className = `result-card ${tipo}`;

            if (tipo === 'success') {
                const tipoAsistente = data.tipo_asistente === 'inacap' ? 'INACAP' : 'General';
                const badgeClass = data.tipo_asistente === 'inacap' ? 'inacap' : 'general';
                
                let institucionHTML = '';
                if (data.tipo_asistente === 'general') {
                    institucionHTML = `
                        <div class="info-row">
                            <span class="label">Empresa:</span>
                            <span class="value">${data.institucion || 'N/A'}</span>
                        </div>
                    `;
                } else {
                    institucionHTML = `
                        <div class="info-row">
                            <span class="label">Tipo:</span>
                            <span class="value">${data.tipo_participante || 'N/A'}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Carrera:</span>
                            <span class="value">${data.carrera || 'N/A'}</span>
                        </div>
                    `;
                }

                resultDiv.innerHTML = `
                    <h3>✅ Asistencia Registrada</h3>
                    <div class="info-row">
                        <span class="label">Código:</span>
                        <span class="value">${data.codigo}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Nombre:</span>
                        <span class="value">${data.nombre}</span>
                    </div>
                    ${institucionHTML}
                    <div class="info-row">
                        <span class="label">Categoría:</span>
                        <span class="value"><span class="badge ${badgeClass}">${tipoAsistente}</span></span>
                    </div>
                    <div class="info-row">
                        <span class="label">Día:</span>
                        <span class="value">${data.dia_evento_formatted}</span>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <h3>❌ Error</h3>
                    <p style="color: #721c24; margin-top: 10px;">${data.message}</p>
                `;
            }
        }
    </script>
</body>
</html>