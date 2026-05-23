<div class="container">
    <div class="dashboard-header">
        <h1>Bienvenido, <?php echo htmlspecialchars($user_name); ?> <?php echo htmlspecialchars($user_last_name); ?></h1>
        <p>Sistema de Control de Asistencias</p>
        <p class="mt-20"><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
        <p><strong>Sesión iniciada:</strong> <?php echo date('d/m/Y H:i:s', $login_time); ?></p>
    </div>
    
    <div class="dashboard-content">
        <h2>Estado del Turno</h2>
        <div class="status-card">
            <div class="status-item">
                <span class="status-label">Estado Actual:</span>
                <span class="status-value <?php echo $shift_status === 'Sin turno' ? 'status-none' : ($shift_status === 'Turno activo' ? 'status-active' : ($shift_status === 'En break' ? 'status-break' : 'status-completed')); ?>">
                    <?php echo htmlspecialchars($shift_status); ?>
                </span>
            </div>
            
            <?php if ($current_shift): ?>
                <div class="status-item">
                    <span class="status-label">Hora Inicio:</span>
                    <span class="status-value"><?php echo htmlspecialchars($current_shift['actual_start']); ?></span>
                </div>
                <?php if ($current_shift['break_start']): ?>
                    <div class="status-item">
                        <span class="status-label">Inicio Break:</span>
                        <span class="status-value"><?php echo htmlspecialchars($current_shift['break_start']); ?></span>
                    </div>
                <?php endif; ?>
                <?php if ($current_shift['break_end']): ?>
                    <div class="status-item">
                        <span class="status-label">Fin Break:</span>
                        <span class="status-value"><?php echo htmlspecialchars($current_shift['break_end']); ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <h2>Estadísticas de Asistencia</h2>
        <?php if ($statistics): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Horas del Día</h3>
                    <div class="stat-value"><?php echo number_format($statistics['day']['day_hours'], 2); ?>h</div>
                    <div class="stat-label">Regulares</div>
                    <div class="stat-overtime"><?php echo number_format($statistics['day']['day_overtime'], 2); ?>h extra</div>
                    <div class="stat-break">Break: <?php echo floor($statistics['day']['day_break_minutes'] / 60); ?>h <?php echo $statistics['day']['day_break_minutes'] % 60; ?>min</div>
                </div>
                
                <div class="stat-card">
                    <h3>Horas de la Semana</h3>
                    <div class="stat-value"><?php echo number_format($statistics['week']['week_hours'], 2); ?>h</div>
                    <div class="stat-label">Regulares</div>
                    <div class="stat-overtime"><?php echo number_format($statistics['week']['week_overtime'], 2); ?>h extra</div>
                    <div class="stat-break">Break: <?php echo floor($statistics['week']['week_break_minutes'] / 60); ?>h <?php echo $statistics['week']['week_break_minutes'] % 60; ?>min</div>
                </div>
                
                <div class="stat-card">
                    <h3>Horas del Mes</h3>
                    <div class="stat-value"><?php echo number_format($statistics['month']['month_hours'], 2); ?>h</div>
                    <div class="stat-label">Regulares</div>
                    <div class="stat-overtime"><?php echo number_format($statistics['month']['month_overtime'], 2); ?>h extra</div>
                    <div class="stat-break">Break: <?php echo floor($statistics['month']['month_break_minutes'] / 60); ?>h <?php echo $statistics['month']['month_break_minutes'] % 60; ?>min</div>
                </div>
            </div>
        <?php else: ?>
            <p>No hay estadísticas disponibles.</p>
        <?php endif; ?>
        
        <div class="dashboard-actions">
            <a href="<?php echo BASE_URL; ?>Attendance" class="btn btn-primary">Ir a Marcación</a>
            <a href="<?php echo BASE_URL; ?>Login/logout" class="btn btn-danger">Cerrar Sesión</a>
        </div>
    </div>
</div>
