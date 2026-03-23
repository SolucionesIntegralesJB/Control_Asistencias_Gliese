<!-- Estilos personalizados para dropdown de asistencia -->
<style>
.dropdown-menu .dropdown-item {
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
}

.dropdown-menu .dropdown-item:hover {
    background-color: var(--bs-gray-100);
}

.dropdown-menu .dropdown-item i {
    margin-right: 0.5rem;
}

.btn-group .btn-sm {
    --bs-btn-padding-y: 0.25rem;
    --bs-btn-padding-x: 0.5rem;
}

/* Selector de color circular */
.color-picker-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: 3px solid #ddd;
    cursor: pointer;
    padding: 0;
    transition: all 0.3s ease;
}

.color-picker-circle:hover {
    border-color: #999;
    transform: scale(1.1);
}

.color-picker-circle::-webkit-color-swatch-wrapper {
    padding: 0;
}

.color-picker-circle::-webkit-color-swatch {
    border: none;
    border-radius: 50%;
}

.color-picker-circle::-moz-color-swatch {
    border: none;
    border-radius: 50%;
}

/* Hacer tabla más compacta */
#members-table {
    font-size: 0.8rem;
}

#members-table thead th {
    padding: 0.4rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

#members-table tbody td {
    padding: 0.4rem 0.5rem;
    vertical-align: middle;
}

#members-table .badge {
    font-size: 0.7rem;
    padding: 0.25em 0.5em;
}

#members-table .btn-sm {
    padding: 0.2rem 0.4rem;
    font-size: 0.75rem;
}

#members-table .btn-sm i {
    width: 12px !important;
    height: 12px !important;
}

/* Dropdown que se abre hacia arriba para evitar scroll */
.dropdown-menu-up {
    bottom: 100% !important;
    top: auto !important;
    margin-bottom: 0.125rem;
}

/* Asegurar que el dropdown no se corte en el offcanvas */
.offcanvas-body {
    overflow-y: auto;
    overflow-x: visible;
}

.table-responsive {
    overflow: visible !important;
}
</style>

<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <!-- Breadcrumbs -->
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0">Gestión de Grupos</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>Students">Practicantes</a></li>
                                <li class="breadcrumb-item active">Grupos</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Breadcrumbs -->

        <!-- Content -->
        <div class="content-body">
            <!-- Header con filtros y búsqueda -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <div class="input-group">
                                <span class="input-group-text"><i data-feather="search"></i></span>
                                <input type="text" class="form-control" id="search-groups" placeholder="Buscar grupos...">
                            </div>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <select class="form-select" id="filter-status">
                                <option value="">Todos los estados</option>
                                <option value="1">Activos</option>
                                <option value="0">Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <select class="form-select" id="filter-availability">
                                <option value="">Disponibilidad</option>
                                <option value="available">Con espacios</option>
                                <option value="full">Llenos</option>
                            </select>
                        </div>
                        <div class="col-md-2 text-end">
                            <button type="button" class="btn btn-primary w-100" id="btn-new-group">
                                <i data-feather="plus"></i> Nuevo
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vista de tarjetas -->
            <div id="groups-cards-container" class="row"></div>

            <!-- Paginación -->
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span id="groups-info">Mostrando 0 grupos</span>
                        </div>
                        <nav>
                            <ul class="pagination mb-0" id="groups-pagination"></ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Content -->

        <!-- Modal Crear/Editar Grupo -->
        <div class="modal fade" id="modal-group" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-group-title">Nuevo Grupo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="form-group">
                            <input type="hidden" id="group-id">

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-1" for="group-code">Código del Grupo: <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" id="group-code" required placeholder="Ej: GRP-001">
                                    <small class="form-text text-muted">Debe ser único</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold mb-1" for="group-capacity">Capacidad Máxima: <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control form-control-sm" id="group-capacity" required min="1" max="50" value="<?php echo isset(
                                        $default_capacity,
                                    )
                                        ? $default_capacity
                                        : 15; ?>">
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="form-label fw-bold mb-1" for="group-name">Nombre del Grupo: <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="group-name" required placeholder="Nombre descriptivo del grupo">
                            </div>

                            <div class="row g-2 mt-1">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold mb-1" for="group-type">Tipo de Grupo: <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm" id="group-type" required>
                                        <option value="PRACTICANTE">Practicantes</option>
                                        <option value="SUPERVISOR">Supervisores</option>
                                    </select>
                                    <small class="form-text text-muted">Define si es un grupo de practicantes o supervisores</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold mb-1 d-block">Color:</label>
                                    <div class="d-flex justify-content-center">
                                        <input type="color" class="color-picker-circle" id="group-color" value="#28a745" title="Seleccionar color">
                                    </div>
                                    <small class="form-text text-muted text-center d-block">Verde: Practicantes<br>Azul: Supervisores</small>
                                </div>
                            </div>

                            <!-- NOTA: La asignación de supervisores ahora se gestiona desde "Mi Grupo" > "Asignación de Supervisores" -->

                            <div class="mt-2">
                                <label class="form-label fw-bold mb-1" for="group-description">Descripción:</label>
                                <textarea class="form-control form-control-sm" id="group-description" rows="2" placeholder="Descripción opcional del grupo y sus objetivos"></textarea>
                            </div>

                            <div class="mt-2" id="status-container" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <label class="form-label fw-bold mb-0 me-3">Estado del Grupo:</label>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="group-status" checked style="cursor: pointer;">
                                        <label class="form-check-label" for="group-status" style="cursor: pointer;">
                                            <small id="group-status-label">Activo</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btn-save-group">
                            <i data-feather="save"></i> Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modal Crear/Editar Grupo -->

        <!-- Offcanvas Ver Miembros -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas-members" style="width: 80%;">
            <div class="offcanvas-header border-bottom">
                <div>
                    <h5 class="offcanvas-title mb-1">
                        <i data-feather="users" style="width: 20px; height: 20px;"></i>
                        <span id="offcanvas-group-name">Grupo</span>
                    </h5>
                    <div class="d-flex gap-3 mt-2">
                        <small class="text-muted">
                            <i data-feather="hash" style="width: 14px; height: 14px;"></i>
                            <span id="offcanvas-group-code">-</span>
                        </small>
                        <small class="text-muted">
                            <i data-feather="users" style="width: 14px; height: 14px;"></i>
                            <span id="offcanvas-group-members">0/0</span> miembros
                        </small>
                        <small>
                            <span id="offcanvas-group-badge"></span>
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body">
                <!-- Botón Agregar Miembro -->
                <div class="mb-3">
                    <button class="btn btn-success btn-sm" id="btn-add-member" onclick="open_add_user_modal()">
                        <i data-feather="user-plus"></i> Agregar Miembro
                    </button>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="members-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-members" data-bs-toggle="tab" data-bs-target="#tab-members-content" type="button">
                            <i data-feather="users" style="width: 16px; height: 16px;"></i>
                            Miembros
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-attendances" data-bs-toggle="tab" data-bs-target="#tab-attendances-content" type="button">
                            <i data-feather="calendar" style="width: 16px; height: 16px;"></i>
                            Asistencias
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="members-tabs-content">
                    <!-- Tab Miembros -->
                    <div class="tab-pane fade show active" id="tab-members-content">
                        <div class="table-responsive">
                            <table id="members-table" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>PRACTICANTE</th>
                                        <th>CARRERA</th>
                                        <th class="text-center">CUENTA</th>
                                        <th class="text-center">CONEXIÓN</th>
                                        <th class="text-center">HORAS</th>
                                        <th class="text-center">SUPERVISIONES</th>
                                        <th class="text-center">ASISTENCIA</th>
                                        <th class="text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Asistencias -->
                    <div class="tab-pane fade" id="tab-attendances-content">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-3">Registro de Asistencias por Practicante</h6>
                                <div class="table-responsive">
                                    <table id="attendances-table" class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>PRACTICANTE</th>
                                                <th>ÚLTIMA FECHA</th>
                                                <th class="text-center">PRESENTES</th>
                                                <th class="text-center">AUSENTES</th>
                                                <th class="text-center">TARDANZAS</th>
                                                <th class="text-center">JUSTIFICADOS</th>
                                                <th class="text-center">% ASISTENCIA</th>
                                                <th class="text-center">ACCIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Offcanvas Ver Miembros -->

        <!-- Modal Historial de Asistencia Individual -->
        <div class="modal fade" id="modal-attendance-history" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i data-feather="clock"></i> Historial de Asistencia - <span id="history-student-name"></span>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>FECHA</th>
                                        <th>ESTADO</th>
                                        <th>HORA REGISTRO</th>
                                        <th>REGISTRADO POR</th>
                                        <th>OBSERVACIONES</th>
                                    </tr>
                                </thead>
                                <tbody id="attendance-history-tbody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modal Historial de Asistencia Individual -->

        <!-- Modal Marcar Asistencia Individual -->
        <div class="modal fade" id="modal-mark-attendance" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i data-feather="check-circle"></i> Marcar Asistencia
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="attendance-student-id">
                        <input type="hidden" id="attendance-group-id">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Practicante:</label>
                            <p class="mb-0" id="attendance-student-name"></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="attendance-date">Fecha: <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="attendance-date" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Estado de Asistencia: <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attendance-status" id="attendance-present" value="1" checked>
                                <label class="form-check-label" for="attendance-present">
                                    <i data-feather="check-circle" style="width: 16px; height: 16px;" class="text-success"></i>
                                    Presente
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attendance-status" id="attendance-absent" value="0">
                                <label class="form-check-label" for="attendance-absent">
                                    <i data-feather="x-circle" style="width: 16px; height: 16px;" class="text-danger"></i>
                                    Ausente
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attendance-status" id="attendance-late" value="2">
                                <label class="form-check-label" for="attendance-late">
                                    <i data-feather="clock" style="width: 16px; height: 16px;" class="text-warning"></i>
                                    Tardanza
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="attendance-status" id="attendance-permission" value="3">
                                <label class="form-check-label" for="attendance-permission">
                                    <i data-feather="alert-circle" style="width: 16px; height: 16px;" class="text-info"></i>
                                    Permiso
                                </label>
                            </div>
                        </div>

                        <div class="row mb-3" id="schedule-container">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="attendance-entry-time">Hora Entrada:</label>
                                <input type="time" class="form-control" id="attendance-entry-time">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" for="attendance-exit-time">Hora Salida:</label>
                                <input type="time" class="form-control" id="attendance-exit-time">
                            </div>
                        </div>

                        <div class="mb-3" id="hours-container">
                            <label class="form-label fw-bold" for="attendance-hours">Horas Trabajadas:</label>
                            <input type="number" class="form-control" id="attendance-hours" min="0" max="24" step="0.5" value="8">
                            <small class="form-text text-muted">Se calcula automáticamente si ingresa hora entrada/salida</small>
                        </div>

                        <div class="mb-3" id="activity-container">
                            <label class="form-label fw-bold" for="attendance-activity">Actividad Realizada:</label>
                            <textarea class="form-control" id="attendance-activity" rows="2" placeholder="Descripción de las actividades del día..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="attendance-remarks">Observaciones:</label>
                            <textarea class="form-control" id="attendance-remarks" rows="2" placeholder="Notas adicionales (opcional)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btn-confirm-attendance">
                            <i data-feather="save"></i> Guardar Asistencia
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modal Marcar Asistencia Individual -->

        <!-- Modal Confirmar Eliminación -->
        <div class="modal fade" id="modal-delete" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i data-feather="trash-2"></i> Confirmar Eliminación
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>⚠️ ATENCIÓN:</strong> Esta acción no se puede deshacer.
                        </div>
                        <p>¿Está seguro de que desea eliminar el grupo <strong id="name-group-delete"></strong>?</p>
                        <input type="hidden" id="id-group-delete">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="btn-confirm-delete">
                            <i data-feather="trash-2"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modal Confirmar Eliminación -->

        <!-- Modal Eliminar Miembro del Grupo -->
        <div class="modal fade" id="modal-delete-member" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i data-feather="user-x"></i> Eliminar Miembro del Grupo
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>⚠️ ATENCIÓN:</strong> El practicante será removido del grupo pero mantendrá sus horas registradas.
                        </div>
                        <p>¿Está seguro de que desea eliminar a <strong id="name-member-delete"></strong> del grupo?</p>
                        <input type="hidden" id="id-member-delete">
                        <input type="hidden" id="current-group-delete">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-danger" id="btn-confirm-delete-member">
                            <i data-feather="user-x"></i> Eliminar del Grupo
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modal Eliminar Miembro del Grupo -->

        <!-- Modal Reasignar Miembro a Otro Grupo -->
        <div class="modal fade" id="modal-reassign-member" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i data-feather="users"></i> Reasignar a Otro Grupo
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>ℹ️ INFORMACIÓN:</strong> El practicante será trasladado al grupo seleccionado manteniendo sus horas registradas.
                        </div>
                        <p>Reasignar a <strong id="name-member-reassign"></strong> al siguiente grupo:</p>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="new-group-select">Seleccionar Nuevo Grupo:</label>
                            <select class="form-select" id="new-group-select" required>
                                <option value="">-- Seleccione un grupo --</option>
                            </select>
                            <small class="form-text text-muted">Solo se muestran grupos con espacios disponibles</small>
                        </div>

                        <input type="hidden" id="id-member-reassign">
                        <input type="hidden" id="current-group-reassign">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btn-confirm-reassign-member">
                            <i data-feather="users"></i> Reasignar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modal Reasignar Miembro a Otro Grupo -->

        <!-- Modal Agregar Usuario al Grupo -->
        <div class="modal fade" id="modal-add-user" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i data-feather="user-plus"></i> Agregar Miembro al Grupo
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>ℹ️ INFORMACIÓN:</strong> Seleccione un usuario con rol PRACTICANTE para agregarlo a <strong id="target-group-name"></strong>.
                        </div>

                        <!-- Campo de búsqueda -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i data-feather="search"></i></span>
                                <input type="text" class="form-control" id="search-users" placeholder="Buscar por nombre o email...">
                            </div>
                        </div>

                        <!-- Lista de usuarios -->
                        <div id="users-list" style="max-height: 400px; overflow-y: auto;">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted">Cargando usuarios...</p>
                            </div>
                        </div>

                        <input type="hidden" id="target-group-id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modal Agregar Usuario al Grupo -->

        <!-- Modal Detalles del Practicante -->
        <div class="modal fade" id="modal-student-details" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i data-feather="user"></i> Detalles del Practicante
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Campos ocultos para almacenar información del grupo actual -->
                        <input type="hidden" id="current-group-id" value="">
                        <input type="hidden" id="current-group-name" value="">
                        
                        <!-- Información del practicante -->
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Información Personal</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Nombre:</strong></td>
                                        <td id="detail-name">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Documento:</strong></td>
                                        <td id="detail-document">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td id="detail-email">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Teléfono:</strong></td>
                                        <td id="detail-phone">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Información Académica</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Carrera:</strong></td>
                                        <td id="detail-specialty">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Área Interés:</strong></td>
                                        <td id="detail-interest-area">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Asignado:</strong></td>
                                        <td id="detail-assignment-date">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Estado del Sistema</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Estado Cuenta:</strong></td>
                                        <td id="detail-account-status">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Conexión:</strong></td>
                                        <td id="detail-connection-status">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Grupos Supervisa:</strong></td>
                                        <td id="detail-supervised-groups">-</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Progreso</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td><strong>Horas:</strong></td>
                                        <td id="detail-hours">-</td>
                                    </tr>
                                    <tr>
                                        <td><strong>% Asistencia:</strong></td>
                                        <td id="detail-attendance-percentage">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modal Detalles del Practicante -->

        <!-- Modal Configurar Reuniones (Meet + Horarios) -->
        <div class="modal fade" id="modal-meeting-config" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i data-feather="video"></i> Configurar Reuniones del Grupo
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="meeting-group-id">

                        <div class="alert alert-info">
                            <strong><i data-feather="info"></i> Información:</strong> Configure el enlace de Google Meet y los horarios de reunión para <strong id="meeting-group-name"></strong>.
                        </div>

                        <!-- Link de Meet -->
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="meeting-link">
                                <i data-feather="link"></i> Enlace de Google Meet:
                            </label>
                            <input type="url" class="form-control" id="meeting-link"
                                   placeholder="https://meet.google.com/xxx-xxxx-xxx">
                            <small class="form-text text-muted">Ingrese el enlace completo de Google Meet para el grupo</small>
                        </div>

                        <!-- Horarios de Reunión -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i data-feather="calendar"></i> Horarios de Reunión:
                            </label>
                            <div id="schedules-container">
                                <!-- Schedules will be added here -->
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn-add-schedule">
                                <i data-feather="plus"></i> Agregar Horario
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btn-save-meeting-config">
                            <i data-feather="save"></i> Guardar Configuración
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modal Configurar Reuniones -->
    </div>
</div>
<!-- END: Content-->