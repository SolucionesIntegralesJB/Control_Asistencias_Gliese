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
                    <h2 class="content-header-title float-left mb-0">Asignación de Practicantes</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>Practicantes">Practicantes</a></li>
                            <li class="breadcrumb-item active">Asignación</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Breadcrumbs -->

    <!-- Content -->
    <div class="content-body">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Practicantes Aceptados sin Credenciales</h4>
                <button type="button" class="btn btn-primary" id="btn-config-template">
                    <i data-feather="settings"></i> Configurar Plantilla
                </button>
            </div>
            <div class="card-body">
                <table id="assign-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>NOMBRES Y APELLIDOS</th>
                            <th>DOCUMENTO</th>
                            <th>EMAIL</th>
                            <th>INSTITUCIÓN</th>
                            <th>ESPECIALIDAD</th>
                            <th>ÁREA DE INTERÉS</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- /Content -->

    <!-- Modal Asignar Practicante -->
    <div class="modal fade" id="modal-assign" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Practicante a Grupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="assign-id-student">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Practicante:</label>
                        <p id="assign-name-student" class="mb-0"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="assign-group">Grupo: <span class="text-danger">*</span></label>
                        <select class="form-select" id="assign-group" required>
                            <option value="">Seleccione un grupo</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" for="assign-role">Rol: <span class="text-danger">*</span></label>
                        <select class="form-select" id="assign-role" required>
                            <option value="">Seleccione un rol</option>
                        </select>
                    </div>

                    <div class="alert alert-info mb-0">
                        <strong>Nota:</strong> Se generarán credenciales automáticamente:<br>
                        • <strong>Usuario:</strong> Número de documento (DNI)<br>
                        • <strong>Contraseña:</strong> Generada aleatoriamente (8 caracteres)
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-confirm-assign">Asignar y Generar Credenciales</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Modal Asignar Practicante -->

    <!-- Modal Credenciales Generadas -->
    <div class="modal fade" id="modal-credentials" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i data-feather="check-circle"></i> Credenciales Generadas</h5>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>⚠️ IMPORTANTE:</strong> Guarde estas credenciales. No se mostrarán nuevamente.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Usuario:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cred-username" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copiar('cred-username')">
                                <i data-feather="copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Contraseña:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cred-password" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="copiar('cred-password')">
                                <i data-feather="copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Grupo Asignado:</label>
                        <p id="cred-grupo" class="mb-0"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="btn-close-credentials">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Modal Credenciales Generadas -->

    <!-- Modal Configurar Plantilla -->
    <div class="modal fade" id="modal-template" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar Plantilla de Credenciales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Asunto:</label>
                        <input type="text" class="form-control" id="template-subject" placeholder="Asunto del correo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contenido:</label>
                        <textarea class="form-control" id="template-content" rows="12" placeholder="Contenido del correo"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <strong>Variables disponibles:</strong> {first_names}, {last_names}, {username}, {password}, {group}, {start_date}, {min_required_hours}, {system_url}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btn-save-template">
                        <i data-feather="save"></i> Guardar Plantilla
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Modal Configurar Plantilla -->
        </div>
    </div>
    <!-- END: Content-->

<script>
function copiar(id) {
    const elemento = document.getElementById(id);
    elemento.select();
    elemento.setSelectionRange(0, 99999);
    document.execCommand('copy');
    functions.toast_message('success', 'Copiado al portapapeles', 'OK');
}
</script>
