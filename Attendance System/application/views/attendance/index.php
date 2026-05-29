<div class="attendance-container">
    <div class="attendance-header">
        <div class="header-left">
            <h1>Control de Asistencias</h1>
        </div>
        <div class="header-right">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($employee_name); ?></span>
                <span class="user-email"><?php echo htmlspecialchars($employee_email); ?></span>
            </div>
            <div class="header-actions">
                <a href="<?php echo BASE_URL; ?>Dashboard" class="btn-back-dashboard" title="Ir al Dashboard">
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo BASE_URL; ?>Login/logout" class="btn-logout" title="Cerrar Sesión">
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </div>
    </div>

    <div class="attendance-info">
        <div class="info-item">
            <label>Fecha:</label>
            <span id="current-date"></span>
        </div>
        <div class="info-item">
            <label>Hora:</label>
            <span id="current-time"></span>
        </div>
    </div>

    <div class="attendance-controls">
        <div class="control-group">
            <label for="job-role-select">Rol de Trabajo:</label>
            <select id="job-role-select" class="form-select">
                <option value="">Seleccione un rol...</option>
            </select>
        </div>

        <div class="control-group">
            <label for="campus-select">Campus:</label>
            <select id="campus-select" class="form-select">
                <option value="">Seleccione un campus...</option>
            </select>
        </div>

        <div class="control-group">
            <label for="work-description">Trabajo a realizar <span class="required">*</span>:</label>
            <textarea id="work-description" class="form-textarea" rows="3" placeholder="Describa brevemente el trabajo que realizará durante el turno (ej: instalación de cámaras, soporte técnico, supervisión de almacén)"></textarea>
            <small class="form-hint">Mínimo 5 caracteres, máximo 500 caracteres</small>
        </div>
    </div>

    <!-- Sección de Contadores -->
    <div class="counters-section">
        <!-- Contador Principal del Turno -->
        <div id="main-counter-card" class="main-counter-card" style="display: none;">
            <div class="counter-time" id="shift-time">00:00:00</div>
            <div class="counter-title">TIEMPO TRABAJADO</div>
        </div>
        
        <!-- Contador de Break -->
        <div id="break-counter-card" class="break-counter-card" style="display: none;">
            <div class="counter-time" id="break-time">00:00:00</div>
            <div class="counter-title">☕ EN BREAK</div>
        </div>
        
        <!-- Horas Extra -->
        <div id="overtime-counter-card" class="overtime-counter-card" style="display: none;">
            <div class="counter-time" id="overtime-time">00:00:00</div>
            <div class="counter-title">⚡ HORAS EXTRA</div>
        </div>
    </div>

    <div class="attendance-status">
        <div class="status-card" id="status-card">
            <h3>Estado del Turno</h3>
            <div id="shift-status" class="status-message">Cargando...</div>
            
            <div id="shift-details" class="shift-details" style="display: none;">
                <p><strong>Inicio:</strong> <span id="shift-start"></span></p>
                <p><strong>Break:</strong> <span id="break-info"></span></p>
                <p><strong>Horas trabajadas:</strong> <span id="worked-hours"></span></p>
            </div>
        </div>
    </div>

    <div class="attendance-actions">
        <button id="btn-start-shift" class="btn btn-primary btn-large" disabled>
            <span class="btn-icon">▶</span>
            Iniciar Turno
        </button>
        <button id="btn-start-break" class="btn btn-warning btn-large" disabled>
            <span class="btn-icon">☕</span>
            Iniciar Break
        </button>
        <button id="btn-end-break" class="btn btn-info btn-large" disabled>
            <span class="btn-icon">▶</span>
            Finalizar Break
        </button>
        <button id="btn-end-shift" class="btn btn-success btn-large" disabled>
            <span class="btn-icon">■</span>
            Finalizar Turno
        </button>
    </div>

    <div id="action-message" class="action-message" style="display: none;"></div>
</div>

<script>
// Reloj en tiempo real
function updateClock() {
    const now = new Date();
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
    
    document.getElementById('current-date').textContent = now.toLocaleDateString('es-ES', dateOptions);
    document.getElementById('current-time').textContent = now.toLocaleTimeString('es-ES', timeOptions);
}

setInterval(updateClock, 1000);
updateClock();

// Cargar roles de trabajo
async function loadJobRoles() {
    try {
        const response = await fetch('<?php echo BASE_URL; ?>Main/get_role');
        const data = await response.json();
        
        if (data.status === 'OK') {
            const select = document.getElementById('job-role-select');
            select.innerHTML = '<option value="">Seleccione un rol...</option>';
            
            data.data.forEach(role => {
                const option = document.createElement('option');
                option.value = role.id;
                option.textContent = role.job_role;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading job roles:', error);
    }
}

// Cargar campus
async function loadCampus() {
    try {
        const response = await fetch('<?php echo BASE_URL; ?>Main/get_campus');
        const data = await response.json();
        
        if (data.status === 'OK') {
            const select = document.getElementById('campus-select');
            select.innerHTML = '<option value="">Seleccione un campus...</option>';
            
            data.data.forEach(campus => {
                const option = document.createElement('option');
                option.value = campus.id;
                option.textContent = campus.description;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading campus:', error);
    }
}

// Cargar estado actual del turno
async function loadCurrentShift() {
    console.log('DEBUG: loadCurrentShift iniciado');
    try {
        const response = await fetch('<?php echo BASE_URL; ?>Attendance/get_current_shift');
        const data = await response.json();
        console.log('DEBUG: loadCurrentShift data =', data);
        
        if (data.status === 'OK' && data.shift) {
            console.log('DEBUG: Turno activo encontrado, llamando updateShiftUI');
            updateShiftUI(data.shift, data.records);
        } else {
            console.log('DEBUG: No hay turno activo, llamando resetShiftUI');
            resetShiftUI();
        }
    } catch (error) {
        console.error('DEBUG: Error en loadCurrentShift:', error);
    }
}

// Actualizar UI según estado del turno
function updateShiftUI(shift, records) {
    console.log('DEBUG: updateShiftUI llamado, shift.status =', shift.status);
    
    const statusDiv = document.getElementById('shift-status');
    const detailsDiv = document.getElementById('shift-details');
    const statusCard = document.getElementById('status-card');
    const btnStartShift = document.getElementById('btn-start-shift');
    const btnStartBreak = document.getElementById('btn-start-break');
    const btnEndBreak = document.getElementById('btn-end-break');
    const btnEndShift = document.getElementById('btn-end-shift');
    
    // Validar existencia de elementos críticos
    if (!statusDiv || !detailsDiv || !statusCard || !btnStartShift || !btnStartBreak || !btnEndBreak || !btnEndShift) {
        console.error('DEBUG: Elementos críticos no encontrados en updateShiftUI');
        return;
    }
    
    console.log('DEBUG: Estado actual botón Iniciar Turno (antes) =', btnStartShift.disabled);
    
    // Limpiar clases de estado
    statusCard.classList.remove('status-normal', 'status-break', 'status-overtime', 'status-completed');
    
    // Resetear botones
    btnStartShift.disabled = true;
    btnStartBreak.disabled = true;
    btnEndBreak.disabled = true;
    btnEndShift.disabled = true;
    
    console.log('DEBUG: Botones reseteados a disabled = true');
    
    // Detener todos los contadores primero
    stopAllCounters();
    
    detailsDiv.style.display = 'block';
    
    const shiftStartElement = document.getElementById('shift-start');
    if (shiftStartElement) {
        shiftStartElement.textContent = shift.actual_start || 'No iniciado';
    }
    
    // Verificar break
    const breakStartRecord = records.find(r => r.record_type === 'break_start');
    const breakEndRecord = records.find(r => r.record_type === 'break_end');
    
    const breakInfoElement = document.getElementById('break-info');
    if (breakStartRecord && !breakEndRecord) {
        if (breakInfoElement) {
            breakInfoElement.textContent = 'En break desde ' + breakStartRecord.record_time;
        }
        btnEndBreak.disabled = false;
        startBreakCounter(breakStartRecord.record_time);
        statusCard.classList.add('status-break');
    } else if (breakStartRecord && breakEndRecord) {
        if (breakInfoElement) {
            breakInfoElement.textContent = 'Break: ' + shift.break_duration + ' minutos';
        }
    } else {
        if (breakInfoElement) {
            breakInfoElement.textContent = 'Sin break';
        }
    }
    
    // Horas trabajadas
    const workedHoursElement = document.getElementById('worked-hours');
    if (shift.total_worked_minutes > 0) {
        const hours = Math.floor(shift.total_worked_minutes / 60);
        const minutes = shift.total_worked_minutes % 60;
        if (workedHoursElement) {
            workedHoursElement.textContent = hours + 'h ' + minutes + 'm';
        }
    } else {
        if (workedHoursElement) {
            workedHoursElement.textContent = 'Calculando...';
        }
    }
    
    // Actualizar según estado
    switch (shift.status) {
        case 'pending':
            console.log('DEBUG: Estado = pending, habilitando botón Iniciar Turno');
            statusDiv.textContent = 'Pendiente de iniciar';
            statusDiv.className = 'status-message status-pending';
            btnStartShift.disabled = false;
            break;
        case 'in_progress':
            console.log('DEBUG: Estado = in_progress, manteniendo botón Iniciar Turno deshabilitado');
            console.log('DEBUG: shift.actual_start =', shift.actual_start);
            statusDiv.textContent = 'Turno en progreso';
            statusDiv.className = 'status-message status-in-progress';
            statusCard.classList.add('status-normal');
            
            if (shift.actual_start) {
                console.log('DEBUG: Llamando a startShiftCounter con shift.actual_start =', shift.actual_start);
                startShiftCounter(shift.actual_start);
            } else {
                console.error('DEBUG: shift.actual_start es null/undefined');
            }
            
            btnStartBreak.disabled = breakStartRecord && !breakEndRecord;
            btnEndShift.disabled = false;
            break;
        case 'completed':
            console.log('DEBUG: Estado = completed, manteniendo botón Iniciar Turno deshabilitado');
            statusDiv.textContent = 'Turno completado';
            statusDiv.className = 'status-message status-completed';
            statusCard.classList.add('status-completed');
            if (workedHoursElement) {
                workedHoursElement.textContent = shift.regular_hours + 'h regulares + ' + shift.overtime_hours + 'h extra';
            }
            break;
        default:
            console.log('DEBUG: Estado desconocido =', shift.status);
            statusDiv.textContent = shift.status;
            statusDiv.className = 'status-message';
    }
    
    console.log('DEBUG: Estado final botón Iniciar Turno (después) =', btnStartShift.disabled);
}

// Resetear UI a estado inicial
function resetShiftUI() {
    console.log('DEBUG: resetShiftUI llamado, habilitando botón Iniciar Turno');
    
    const shiftStatusElement = document.getElementById('shift-status');
    if (shiftStatusElement) {
        shiftStatusElement.textContent = 'Sin turno activo';
        shiftStatusElement.className = 'status-message status-none';
    }
    
    const shiftDetailsElement = document.getElementById('shift-details');
    if (shiftDetailsElement) {
        shiftDetailsElement.style.display = 'none';
    }
    
    const statusCard = document.getElementById('status-card');
    if (statusCard) {
        statusCard.classList.remove('status-normal', 'status-break', 'status-overtime', 'status-completed');
    }
    
    stopAllCounters();
    
    const btnStartShift = document.getElementById('btn-start-shift');
    const btnStartBreak = document.getElementById('btn-start-break');
    const btnEndBreak = document.getElementById('btn-end-break');
    const btnEndShift = document.getElementById('btn-end-shift');
    
    if (btnStartShift) btnStartShift.disabled = false;
    if (btnStartBreak) btnStartBreak.disabled = true;
    if (btnEndBreak) btnEndBreak.disabled = true;
    if (btnEndShift) btnEndShift.disabled = true;
    
    console.log('DEBUG: Botón Iniciar Turno habilitado en resetShiftUI');
}

// Mostrar mensaje de acción
function showActionMessage(message, type) {
    const messageDiv = document.getElementById('action-message');
    messageDiv.textContent = message;
    messageDiv.className = 'action-message action-' + type;
    messageDiv.style.display = 'block';
    
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 3000);
}

// Event listeners para botones
document.getElementById('btn-start-shift').addEventListener('click', async function() {
    const jobRoleId = document.getElementById('job-role-select').value;
    const campusId = document.getElementById('campus-select').value;
    const workDescription = document.getElementById('work-description').value;

    if (!jobRoleId || !campusId) {
        showActionMessage('Seleccione rol y campus', 'error');
        return;
    }

    if (!workDescription || workDescription.trim().length < 5) {
        showActionMessage('La descripción del trabajo debe tener al menos 5 caracteres', 'error');
        return;
    }

    if (workDescription.trim().length > 500) {
        showActionMessage('La descripción del trabajo no puede exceder 500 caracteres', 'error');
        return;
    }

    this.disabled = true;
    const originalText = this.textContent;
    this.textContent = 'Procesando...';

    try {
        const formData = new FormData();
        formData.append('job_role_id', jobRoleId);
        formData.append('campus_id', campusId);
        formData.append('work_description', workDescription.trim());

        const response = await fetch('<?php echo BASE_URL; ?>Attendance/start_shift', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (data.status === 'OK') {
            showActionMessage(data.msg, 'success');
            await loadCurrentShift();
            // Limpiar campo de descripción después de iniciar turno
            document.getElementById('work-description').value = '';
        } else {
            showActionMessage(data.msg, 'error');
            this.disabled = false;
            this.textContent = originalText;
        }
    } catch (error) {
        console.error('Error al iniciar turno:', error);
        showActionMessage('Error al procesar: ' + error.message, 'error');
        this.disabled = false;
        this.textContent = originalText;
    }
});

document.getElementById('btn-start-break').addEventListener('click', async function() {
    this.disabled = true;
    this.textContent = 'Procesando...';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>Attendance/start_break', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.status === 'OK') {
            showActionMessage(data.msg, 'success');
            loadCurrentShift();
        } else {
            showActionMessage(data.msg, 'error');
            this.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        showActionMessage('Error al procesar', 'error');
        this.disabled = false;
    }
    
    this.textContent = '☕ Iniciar Break';
});

document.getElementById('btn-end-break').addEventListener('click', async function() {
    this.disabled = true;
    this.textContent = 'Procesando...';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>Attendance/end_break', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.status === 'OK') {
            showActionMessage(data.msg + ' (' + data.break_duration + ' min)', 'success');
            loadCurrentShift();
        } else {
            showActionMessage(data.msg, 'error');
            this.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        showActionMessage('Error al procesar', 'error');
        this.disabled = false;
    }
    
    this.textContent = '▶ Finalizar Break';
});

document.getElementById('btn-end-shift').addEventListener('click', async function() {
    if (!confirm('¿Está seguro de finalizar el turno?')) {
        return;
    }
    
    this.disabled = true;
    this.textContent = 'Procesando...';
    
    try {
        const response = await fetch('<?php echo BASE_URL; ?>Attendance/end_shift', {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.status === 'OK') {
            showActionMessage(data.msg + ' - ' + data.regular_hours + 'h regulares, ' + data.overtime_hours + 'h extra', 'success');
            loadCurrentShift();
        } else {
            showActionMessage(data.msg, 'error');
            this.disabled = false;
        }
    } catch (error) {
        console.error('Error:', error);
        showActionMessage('Error al procesar', 'error');
        this.disabled = false;
    }
    
    this.textContent = '■ Finalizar Turno';
});

// Variables globales para contadores
let shiftStartTime = null;
let breakStartTime = null;
let shiftInterval = null;
let breakInterval = null;

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    console.log('DEBUG: DOMContentLoaded, iniciando carga inicial');
    
    // Estado inicial: botón habilitado
    document.getElementById('shift-status').textContent = 'Cargando...';
    
    loadJobRoles();
    loadCampus();
    loadCurrentShift();
    
    // Actualizar estado cada 30 segundos
    setInterval(loadCurrentShift, 30000);
});

// Función para formatear tiempo HH:MM:SS
function formatTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
}

// Función para parsear fecha de PHP/MySQL a JavaScript Date
function parseDateTime(dateTimeString) {
    console.log('DEBUG parseDateTime: dateTimeString =', dateTimeString);
    
    if (!dateTimeString) {
        console.error('DEBUG parseDateTime: dateTimeString es null/undefined');
        return null;
    }
    
    // Formato esperado: YYYY-MM-DD HH:MM:SS
    const parts = dateTimeString.split(' ');
    console.log('DEBUG parseDateTime: parts =', parts);
    
    if (parts.length !== 2) {
        console.error('DEBUG parseDateTime: formato incorrecto, se esperan 2 partes');
        return null;
    }
    
    const dateParts = parts[0].split('-');
    const timeParts = parts[1].split(':');
    console.log('DEBUG parseDateTime: dateParts =', dateParts, 'timeParts =', timeParts);
    
    if (dateParts.length !== 3 || timeParts.length !== 3) {
        console.error('DEBUG parseDateTime: formato incorrecto en fecha o hora');
        return null;
    }
    
    // JavaScript Date usa meses 0-11, así que restamos 1 al mes
    const parsedDate = new Date(
        parseInt(dateParts[0]),      // año
        parseInt(dateParts[1]) - 1,   // mes (0-11)
        parseInt(dateParts[2]),      // día
        parseInt(timeParts[0]),      // hora
        parseInt(timeParts[1]),      // minuto
        parseInt(timeParts[2])       // segundo
    );
    
    console.log('DEBUG parseDateTime: parsedDate =', parsedDate, 'isValid =', !isNaN(parsedDate.getTime()));
    
    return parsedDate;
}

// Actualizar contador del turno
function updateShiftCounter() {
    console.log('DEBUG updateShiftCounter: shiftStartTime =', shiftStartTime);
    
    if (!shiftStartTime || shiftStartTime === 'Invalid Date') {
        console.error('DEBUG updateShiftCounter: shiftStartTime inválido');
        return;
    }
    
    const now = new Date();
    console.log('DEBUG updateShiftCounter: now =', now);
    console.log('DEBUG updateShiftCounter: now.getTime() =', now.getTime());
    console.log('DEBUG updateShiftCounter: shiftStartTime.getTime() =', shiftStartTime.getTime());
    
    const elapsedMs = now - shiftStartTime;
    console.log('DEBUG updateShiftCounter: elapsedMs (milisegundos) =', elapsedMs);
    
    const elapsed = Math.floor(elapsedMs / 1000);
    console.log('DEBUG updateShiftCounter: elapsed (segundos) =', elapsed);
    
    if (isNaN(elapsed) || elapsed < 0) {
        console.error('DEBUG updateShiftCounter: elapsed inválido =', elapsed);
        return;
    }
    
    const shiftTimeElement = document.getElementById('shift-time');
    console.log('DEBUG updateShiftCounter: shiftTimeElement =', shiftTimeElement);
    
    if (shiftTimeElement) {
        const formattedTime = formatTime(elapsed);
        console.log('DEBUG updateShiftCounter: formattedTime =', formattedTime);
        shiftTimeElement.textContent = formattedTime;
    } else {
        console.error('DEBUG updateShiftCounter: shift-time element no encontrado');
    }
    
    // Verificar horas extra (> 8 horas = 28800 segundos)
    if (elapsed > 28800) {
        console.log('DEBUG updateShiftCounter: Horas extra detectadas');
        const overtimeSeconds = elapsed - 28800;
        const overtimeTimeElement = document.getElementById('overtime-time');
        if (overtimeTimeElement) {
            overtimeTimeElement.textContent = formatTime(overtimeSeconds);
        }
        
        const overtimeCounterCard = document.getElementById('overtime-counter-card');
        if (overtimeCounterCard) {
            overtimeCounterCard.style.display = 'block';
        }
        
        const overtimeDisplay = document.getElementById('overtime-display');
        if (overtimeDisplay) {
            overtimeDisplay.style.display = 'none';
        }
        
        // Cambiar estado visual a azul
        const mainCounterCard = document.getElementById('main-counter-card');
        if (mainCounterCard) {
            mainCounterCard.classList.add('status-overtime');
        }
        
        const statusCard = document.getElementById('status-card');
        if (statusCard) {
            statusCard.classList.add('status-overtime');
        }
    }
}

// Actualizar contador de break
function updateBreakCounter() {
    console.log('DEBUG updateBreakCounter: breakStartTime =', breakStartTime);
    
    if (!breakStartTime || breakStartTime === 'Invalid Date') {
        console.error('DEBUG updateBreakCounter: breakStartTime inválido');
        return;
    }
    
    const now = new Date();
    console.log('DEBUG updateBreakCounter: now =', now);
    console.log('DEBUG updateBreakCounter: now.getTime() =', now.getTime());
    console.log('DEBUG updateBreakCounter: breakStartTime.getTime() =', breakStartTime.getTime());
    
    const elapsedMs = now - breakStartTime;
    console.log('DEBUG updateBreakCounter: elapsedMs (milisegundos) =', elapsedMs);
    
    const elapsed = Math.floor(elapsedMs / 1000);
    console.log('DEBUG updateBreakCounter: elapsed (segundos) =', elapsed);
    
    if (isNaN(elapsed) || elapsed < 0) {
        console.error('DEBUG updateBreakCounter: elapsed inválido =', elapsed);
        return;
    }
    
    const breakTimeElement = document.getElementById('break-time');
    if (breakTimeElement) {
        const formattedTime = formatTime(elapsed);
        console.log('DEBUG updateBreakCounter: formattedTime =', formattedTime);
        breakTimeElement.textContent = formattedTime;
    } else {
        console.error('DEBUG updateBreakCounter: break-time element no encontrado');
    }
}

// Iniciar contador del turno
function startShiftCounter(startTime) {
    console.log('DEBUG startShiftCounter: startTime recibido =', startTime);
    
    shiftStartTime = parseDateTime(startTime);
    
    if (!shiftStartTime || shiftStartTime === 'Invalid Date') {
        console.error('DEBUG startShiftCounter: Fecha inválida después de parseDateTime:', startTime);
        return;
    }
    
    console.log('DEBUG startShiftCounter: shiftStartTime parseado =', shiftStartTime);
    
    const mainCounterCard = document.getElementById('main-counter-card');
    console.log('DEBUG startShiftCounter: mainCounterCard =', mainCounterCard);
    
    if (mainCounterCard) {
        mainCounterCard.style.display = 'block';
        console.log('DEBUG startShiftCounter: main-counter-card mostrado');
    } else {
        console.error('DEBUG startShiftCounter: main-counter-card no encontrado');
    }
    
    const shiftCounter = document.getElementById('shift-counter');
    if (shiftCounter) {
        shiftCounter.style.display = 'none';
    }
    
    if (shiftInterval) {
        console.log('DEBUG startShiftCounter: Limpiando intervalo anterior');
        clearInterval(shiftInterval);
    }
    
    console.log('DEBUG startShiftCounter: Iniciando nuevo intervalo');
    shiftInterval = setInterval(updateShiftCounter, 1000);
    console.log('DEBUG startShiftCounter: Intervalo iniciado, ID =', shiftInterval);
    
    // Actualizar inmediatamente
    updateShiftCounter();
}

// Iniciar contador de break
function startBreakCounter(startTime) {
    console.log('DEBUG startBreakCounter: startTime recibido =', startTime);
    console.log('DEBUG startBreakCounter: tipo de startTime =', typeof startTime);
    
    breakStartTime = parseDateTime(startTime);
    
    if (!breakStartTime || breakStartTime === 'Invalid Date') {
        console.error('DEBUG startBreakCounter: Fecha de break inválida:', startTime);
        return;
    }
    
    console.log('DEBUG startBreakCounter: breakStartTime parseado =', breakStartTime);
    console.log('DEBUG startBreakCounter: breakStartTime.getTime() =', breakStartTime.getTime());
    
    const breakCounterCard = document.getElementById('break-counter-card');
    if (breakCounterCard) {
        breakCounterCard.style.display = 'block';
        console.log('DEBUG startBreakCounter: break-counter-card mostrado');
    } else {
        console.error('DEBUG startBreakCounter: break-counter-card no encontrado');
    }
    
    const breakCounter = document.getElementById('break-counter');
    if (breakCounter) {
        breakCounter.style.display = 'none';
    }
    
    // Cambiar color del contador principal
    const mainCounterCard = document.getElementById('main-counter-card');
    if (mainCounterCard) {
        mainCounterCard.classList.add('status-break');
    }
    
    if (typeof breakInterval !== 'undefined' && breakInterval !== null) {
        console.log('DEBUG startBreakCounter: Limpiando intervalo anterior');
        clearInterval(breakInterval);
    }
    
    console.log('DEBUG startBreakCounter: Iniciando nuevo intervalo');
    breakInterval = setInterval(updateBreakCounter, 1000);
    console.log('DEBUG startBreakCounter: Intervalo iniciado, ID =', breakInterval);
    
    // Actualizar inmediatamente
    updateBreakCounter();
}

// Detener contador de break
function stopBreakCounter() {
    if (typeof breakInterval !== 'undefined' && breakInterval !== null) {
        clearInterval(breakInterval);
        breakInterval = null;
    }
    breakStartTime = null;
    
    const breakCounterCard = document.getElementById('break-counter-card');
    if (breakCounterCard) {
        breakCounterCard.style.display = 'none';
    }
    
    const breakCounter = document.getElementById('break-counter');
    if (breakCounter) {
        breakCounter.style.display = 'none';
    }
    
    // Restaurar color del contador principal
    const mainCounterCard = document.getElementById('main-counter-card');
    if (mainCounterCard) {
        mainCounterCard.classList.remove('status-break');
    }
}

// Detener todos los contadores
function stopAllCounters() {
    if (typeof shiftInterval !== 'undefined' && shiftInterval !== null) {
        clearInterval(shiftInterval);
        shiftInterval = null;
    }
    if (typeof breakInterval !== 'undefined' && breakInterval !== null) {
        clearInterval(breakInterval);
        breakInterval = null;
    }
    shiftStartTime = null;
    breakStartTime = null;
    
    // Ocultar contadores nuevos con validación de existencia
    const mainCounterCard = document.getElementById('main-counter-card');
    if (mainCounterCard) mainCounterCard.style.display = 'none';
    
    const shiftCounter = document.getElementById('shift-counter');
    if (shiftCounter) shiftCounter.style.display = 'none';
    
    const breakCounterCard = document.getElementById('break-counter-card');
    if (breakCounterCard) breakCounterCard.style.display = 'none';
    
    const breakCounter = document.getElementById('break-counter');
    if (breakCounter) breakCounter.style.display = 'none';
    
    const overtimeCounterCard = document.getElementById('overtime-counter-card');
    if (overtimeCounterCard) overtimeCounterCard.style.display = 'none';
    
    const overtimeDisplay = document.getElementById('overtime-display');
    if (overtimeDisplay) overtimeDisplay.style.display = 'none';
    
    // Limpiar clases de estado
    if (mainCounterCard) {
        mainCounterCard.classList.remove('status-break', 'status-overtime');
    }
}
</script>
