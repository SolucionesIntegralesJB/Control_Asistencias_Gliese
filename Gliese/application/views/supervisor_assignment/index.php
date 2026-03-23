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
                        <h2 class="content-header-title float-left mb-0">
                            <i data-feather="users" class="me-50"></i>
                            Asignación de Supervisores
                        </h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Inicio</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>S_Groups">Grupos</a></li>
                                <li class="breadcrumb-item active">Asignación de Supervisores</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Breadcrumbs -->

        <!-- Content -->
        <div class="content-body">
            <!-- Botón para nueva asignación -->
            <div class="row mb-3">
                <div class="col-12">
                    <button type="button" class="btn btn-primary btn-new-assignment">
                        <i data-feather="user-plus" class="me-50"></i>
                        Asignar Supervisor a Grupo
                    </button>
                </div>
            </div>

            <!-- Tarjeta de asignaciones -->
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i data-feather="list" class="me-50"></i>
                        Asignaciones Activas
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Barra de búsqueda -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i data-feather="search"></i></span>
                                <input type="text" class="form-control" id="search-assignments" placeholder="Buscar por supervisor o grupo...">
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de asignaciones -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="assignments-table">
                            <thead>
                                <tr>
                                    <th>Supervisor</th>
                                    <th>Email</th>
                                    <th>Grupo</th>
                                    <th>Practicantes</th>
                                    <th>Fecha Asignación</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="assignments-tbody">
                                <!-- Se carga dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Mensaje cuando no hay datos -->
                    <div id="no-assignments" class="text-center py-5" style="display: none;">
                        <i data-feather="inbox" style="width: 48px; height: 48px;" class="text-muted mb-2"></i>
                        <p class="text-muted">No hay asignaciones registradas</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Content -->
    </div>
</div>
<!-- END: Content-->

<!-- Modal para Nueva Asignación -->
<div class="modal fade" id="modal-assign-supervisor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">
                    <i data-feather="user-plus" class="me-50"></i>
                    Asignar Supervisor a Grupo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-assign">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="select-supervisor-group" class="form-label">Grupo de Supervisores</label>
                                <select class="form-select" id="select-supervisor-group">
                                    <option value="">Seleccione un grupo de supervisores...</option>
                                </select>
                                <div class="form-text">Al elegir un grupo, se listarán los supervisores pertenecientes.</div>
                            </div>

                            <div class="mb-3">
                                <label for="select-supervisor" class="form-label">Supervisor (opcional)</label>
                                <select class="form-select" id="select-supervisor">
                                    <option value="">Seleccione un supervisor...</option>
                                </select>
                                <div class="form-text">Si no selecciona un supervisor, se usarán todos los del grupo elegido.</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Grupos de Practicantes a asignar <span class="text-danger">*</span></label>
                                <div id="practicant-groups-container" class="border rounded p-1" style="max-height: 260px; overflow-y: auto;">
                                    <p class="text-muted small mb-0">Cargando grupos de practicantes...</p>
                                </div>
                                <div class="form-text">Marque uno o más grupos para asignar al supervisor.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="assignment-notes" class="form-label">Notas (Opcional)</label>
                        <textarea class="form-control" id="assignment-notes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i data-feather="x" class="me-50"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btn-save-supervisor-assignment">
                    <i data-feather="check" class="me-50"></i>
                    Asignar
                </button>
            </div>
        </div>
    </div>
</div>
<!-- /Modal -->

<!-- Modal de Confirmación para Desasignar -->
<div class="modal fade" id="modal-unassign-confirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">
                    <i data-feather="alert-triangle" class="me-50"></i>
                    Confirmar Desasignación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea desasignar al supervisor de este grupo?</p>
                <div class="alert alert-warning mb-0">
                    <div class="alert-body">
                        <i data-feather="info" class="me-50"></i>
                        Esta acción marcará la asignación como inactiva.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i data-feather="x" class="me-50"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btn-confirm-unassign">
                    <i data-feather="user-x" class="me-50"></i>
                    Desasignar
                </button>
            </div>
        </div>
    </div>
</div>
<!-- /Modal Confirmación -->
