<div class="attendance-container">
    <div class="attendance-header">
        <div class="header-left">
            <h1>Control de Asistencias</h1>
        </div>
        <div class="header-right">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($user_name); ?> <?php echo htmlspecialchars($user_last_name); ?></span>
                <span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
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
    </div>

    <div class="attendance-status">
        <div class="status-card">
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
    try {
        const response = await fetch('<?php echo BASE_URL; ?>Attendance/get_current_shift');
        const data = await response.json();
        
        if (data.status === 'OK' && data.shift) {
            updateShiftUI(data.shift, data.records);
        } else {
            resetShiftUI();
        }
    } catch (error) {
        console.error('Error loading current shift:', error);
    }
}

// Actualizar UI según estado del turno
function updateShiftUI(shift, records) {
    const statusDiv = document.getElementById('shift-status');
    const detailsDiv = document.getElementById('shift-details');
    const btnStartShift = document.getElementById('btn-start-shift');
    const btnStartBreak = document.getElementById('btn-start-break');
    const btnEndBreak = document.getElementById('btn-end-break');
    const btnEndShift = document.getElementById('btn-end-shift');
    
    // Resetear botones
    btnStartShift.disabled = true;
    btnStartBreak.disabled = true;
    btnEndBreak.disabled = true;
    btnEndShift.disabled = true;
    
    detailsDiv.style.display = 'block';
    document.getElementById('shift-start').textContent = shift.actual_start || 'No iniciado';
    
    // Verificar break
    const breakStartRecord = records.find(r => r.record_type === 'break_start');
    const breakEndRecord = records.find(r => r.record_type === 'break_end');
    
    if (breakStartRecord && !breakEndRecord) {
        document.getElementById('break-info').textContent = 'En break desde ' + breakStartRecord.record_time;
        btnEndBreak.disabled = false;
    } else if (breakStartRecord && breakEndRecord) {
        document.getElementById('break-info').textContent = 'Break: ' + shift.break_duration + ' minutos';
    } else {
        document.getElementById('break-info').textContent = 'Sin break';
    }
    
    // Horas trabajadas
    if (shift.total_worked_minutes > 0) {
        const hours = Math.floor(shift.total_worked_minutes / 60);
        const minutes = shift.total_worked_minutes % 60;
        document.getElementById('worked-hours').textContent = hours + 'h ' + minutes + 'm';
    } else {
        document.getElementById('worked-hours').textContent = 'Calculando...';
    }
    
    // Actualizar según estado
    switch (shift.status) {
        case 'pending':
            statusDiv.textContent = 'Pendiente de iniciar';
            statusDiv.className = 'status-message status-pending';
            btnStartShift.disabled = false;
            break;
        case 'in_progress':
            statusDiv.textContent = 'Turno en progreso';
            statusDiv.className = 'status-message status-in-progress';
            btnStartBreak.disabled = breakStartRecord && !breakEndRecord;
            btnEndShift.disabled = false;
            break;
        case 'completed':
            statusDiv.textContent = 'Turno completado';
            statusDiv.className = 'status-message status-completed';
            document.getElementById('worked-hours').textContent = shift.regular_hours + 'h regulares + ' + shift.overtime_hours + 'h extra';
            break;
        default:
            statusDiv.textContent = shift.status;
            statusDiv.className = 'status-message';
    }
}

// Resetear UI a estado inicial
function resetShiftUI() {
    document.getElementById('shift-status').textContent = 'Sin turno activo';
    document.getElementById('shift-status').className = 'status-message status-none';
    document.getElementById('shift-details').style.display = 'none';
    
    const btnStartShift = document.getElementById('btn-start-shift');
    const btnStartBreak = document.getElementById('btn-start-break');
    const btnEndBreak = document.getElementById('btn-end-break');
    const btnEndShift = document.getElementById('btn-end-shift');
    
    btnStartShift.disabled = false;
    btnStartBreak.disabled = true;
    btnEndBreak.disabled = true;
    btnEndShift.disabled = true;
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
    
    if (!jobRoleId || !campusId) {
        showActionMessage('Seleccione rol y campus', 'error');
        return;
    }
    
    this.disabled = true;
    this.textContent = 'Procesando...';
    
    try {
        const formData = new FormData();
        formData.append('job_role_id', jobRoleId);
        formData.append('campus_id', campusId);
        
        const response = await fetch('<?php echo BASE_URL; ?>Attendance/start_shift', {
            method: 'POST',
            body: formData
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
        console.error('DEBUG: Error en fetch:', error);
        console.error('DEBUG: Error message:', error.message);
        console.error('DEBUG: Error stack:', error.stack);
        showActionMessage('Error al procesar: ' + error.message, 'error');
        this.disabled = false;
    }
    
    this.textContent = 'Iniciar Turno';
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

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    loadJobRoles();
    loadCampus();
    loadCurrentShift();
    
    // Actualizar estado cada 30 segundos
    setInterval(loadCurrentShift, 30000);
});
</script>
