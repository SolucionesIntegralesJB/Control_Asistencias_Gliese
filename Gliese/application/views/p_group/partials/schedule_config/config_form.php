<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i data-feather="settings" style="width: 32px; height: 32px;"></i>
                Configuración de Horarios
            </h2>
            <p class="text-muted mb-0 mt-1">Gestiona las reglas y límites para la modificación de horarios de los practicantes</p>
        </div>
    </div>

    <!-- Tarjetas de Configuración -->
    <div class="row g-3 mb-4">
        <!-- Fila 1: Límite de Modificaciones -->
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-primary bg-opacity-10 text-primary rounded-circle p-3 me-3">
                                    <i data-feather="edit-3" style="width: 24px; height: 24px;"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Límite de Modificaciones</h5>
                                    <small class="text-muted">Número máximo de veces que un practicante puede modificar su horario</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="number" class="form-control config-input"
                                       data-key="max_schedule_modifications"
                                       id="max_schedule_modifications"
                                       min="1" max="10" value="3">
                                <span class="input-group-text">veces</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fila 2: Días de Trabajo por Semana -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3">
                            <i data-feather="calendar" style="width: 24px; height: 24px;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Días de Trabajo por Semana</h5>
                            <small class="text-muted">Rango permitido</small>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Mínimo</label>
                            <div class="input-group">
                                <input type="number" class="form-control config-input"
                                       data-key="min_work_days_per_week"
                                       id="min_work_days_per_week"
                                       min="1" max="7" value="5">
                                <span class="input-group-text">días</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Máximo</label>
                            <div class="input-group">
                                <input type="number" class="form-control config-input"
                                       data-key="max_work_days_per_week"
                                       id="max_work_days_per_week"
                                       min="1" max="7" value="5">
                                <span class="input-group-text">días</span>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Los practicantes deben trabajar entre el mínimo y máximo de días especificado</small>
                </div>
            </div>
        </div>

        <!-- Fila 2: Horas de Trabajo por Día -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-box bg-info bg-opacity-10 text-info rounded-circle p-3 me-3">
                            <i data-feather="clock" style="width: 24px; height: 24px;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Horas de Trabajo por Día</h5>
                            <small class="text-muted">Rango permitido</small>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Mínimo</label>
                            <div class="input-group">
                                <input type="number" class="form-control config-input"
                                       data-key="min_work_hours_per_day"
                                       id="min_work_hours_per_day"
                                       min="1" max="12" value="4">
                                <span class="input-group-text">hrs</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Máximo</label>
                            <div class="input-group">
                                <input type="number" class="form-control config-input"
                                       data-key="max_work_hours_per_day"
                                       id="max_work_hours_per_day"
                                       min="1" max="12" value="6">
                                <span class="input-group-text">hrs</span>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Cada día de trabajo debe tener entre el mínimo y máximo de horas especificado</small>
                </div>
            </div>
        </div>

        <!-- Fila 3: Reuniones Semanales -->
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-success bg-opacity-10 text-success rounded-circle p-3 me-3">
                                    <i data-feather="users" style="width: 24px; height: 24px;"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Reuniones Semanales</h5>
                                    <small class="text-muted">Rango permitido</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">Mínimo</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control config-input"
                                               data-key="required_meetings_per_week"
                                               id="required_meetings_per_week"
                                               min="0" max="7" value="1">
                                        <span class="input-group-text">reunión(es)</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold">Máximo</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control config-input"
                                               data-key="max_meetings_per_week"
                                               id="max_meetings_per_week"
                                               min="0" max="7" value="2">
                                        <span class="input-group-text">reunión(es)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">Número de reuniones que debe tener cada grupo por semana</small>
                </div>
            </div>
        </div>

        <!-- Fila 4: Duración de Reuniones -->
        <div class="col-12">
            <div class="card shadow-sm h-100 border-warning">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="icon-box bg-warning bg-opacity-10 text-warning rounded-circle p-3 me-3">
                                    <i data-feather="video" style="width: 24px; height: 24px;"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0">Duración de Cada Reunión</h5>
                                    <small class="text-muted">Rango permitido en horas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">Mínimo</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control config-input"
                                               data-key="min_meeting_duration_hours"
                                               id="min_meeting_duration_hours"
                                               min="0.5" max="8" step="0.5" value="1">
                                        <span class="input-group-text">hrs</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold">Máximo</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control config-input"
                                               data-key="max_meeting_duration_hours"
                                               id="max_meeting_duration_hours"
                                               min="0.5" max="8" step="0.5" value="3">
                                        <span class="input-group-text">hrs</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2">⚠️ Cada reunión debe durar entre el mínimo y máximo de horas especificado</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón de Guardar -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <button type="button" class="btn btn-primary btn-lg px-5" id="btn-save-configs">
                <i data-feather="save" style="width: 18px; height: 18px;"></i>
                Guardar Configuración
            </button>
        </div>
    </div>
</div>

<style>
.icon-box {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.form-check-input {
    width: 3rem;
    height: 1.5rem;
    cursor: pointer;
}

.config-input:focus {
    border-color: #7367f0;
    box-shadow: 0 0 0 0.2rem rgba(115, 103, 240, 0.25);
}
</style>
