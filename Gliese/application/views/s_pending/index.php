    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Students Pendientes Starts -->
                <section id="students-pending">  

                    <!-- Header title -->
                    <div class="content-header row">
                        <div class="content-header-left col-md-9 col-12 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Solicitudes Pendientes</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="#"><?php echo $selected_menu; ?></a></li>
                                            <li class="breadcrumb-item active"><span><?php echo $selected_sub_menu; ?></span></li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Header title-->

                    <!-- DataTable -->
                    <div class="card">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Practicantes Pendientes de Aprobación</h4>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label mb-0 fw-bold text-nowrap">Estado:</label>
                                    <select class="form-select form-select-sm" id="filter-status" style="width: 150px;">
                                        <option value="pendiente" selected>Pendiente</option>
                                        <option value="rechazado">Rechazado</option>
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary" id="btn-config-templates">
                                    <i data-feather="settings"></i> Configurar Plantillas
                                </button>
                            </div>
                        </div>
                        <div class="card-datatable">
                            <table class="dt-responsive table" id="pending-table">
                                <thead>
                                    <tr>
                                        <th>Nombres y Apellidos</th>
                                        <th>Documento</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Institución</th>
                                        <th>Especialidad</th>
                                        <th>Fecha Registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /DataTable -->

                </section>
                <!-- Practicantes Pendientes Ends -->
            </div>
        </div>
    </div>
    <!-- END: Content-->

    <!-- Modal Detalle Practicante -->
    <div class="modal fade" id="modal-student-detail" tabindex="-1" aria-labelledby="modalStudentDetailLabel">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStudentDetailLabel">Detalle de Solicitud</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="detail-id">

                    <!-- Datos Personales -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="fw-bold text-primary">Datos Personales</h6>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" width="200">Nombres y Apellidos:</td>
                                        <td id="detail-full-names"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Tipo Documento:</td>
                                        <td id="detail-document-type"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Número Documento:</td>
                                        <td id="detail-document-number"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Fecha Nacimiento:</td>
                                        <td id="detail-birth-date"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Teléfono:</td>
                                        <td id="detail-phone"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td id="detail-email"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Dirección:</td>
                                        <td id="detail-address"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Datos Académicos -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="fw-bold text-primary">Datos Académicos</h6>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" width="200">Institución:</td>
                                        <td id="detail-institution"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Sede:</td>
                                        <td id="detail-location"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Especialidad:</td>
                                        <td id="detail-specialty"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Ciclo:</td>
                                        <td id="detail-cycle"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email Institucional:</td>
                                        <td id="detail-institutional-email"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Datos de Prácticas -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="fw-bold text-primary">Datos de Prácticas</h6>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" width="200">Modalidad:</td>
                                        <td id="detail-modality"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Área de Interés:</td>
                                        <td id="detail-interest-area"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Teléfono Origen:</td>
                                        <td id="detail-origin-phone"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Campo Observaciones (para rechazo) -->
                    <div class="row mb-3" id="remarks-group" style="display: none;">
                        <div class="col-12">
                            <label for="remarks" class="form-label fw-bold">Motivo del Rechazo:</label>
                            <textarea class="form-control" id="remarks" rows="3" placeholder="Ingrese el motivo del rechazo..."></textarea>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-danger" id="btn-rechazar">Rechazar</button>
                    <button type="button" class="btn btn-success" id="btn-aceptar">Aceptar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Modal Detalle Practicante -->

    <!-- Modal Configurar Plantillas -->
    <div class="modal fade" id="modal-templates" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Configurar Plantillas de Correo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-aceptacion" data-bs-toggle="tab" href="#template-aceptacion" role="tab">
                                <i data-feather="check-circle"></i> Aceptación
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-rechazo" data-bs-toggle="tab" href="#template-rechazo" role="tab">
                                <i data-feather="x-circle"></i> Rechazo
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content mt-3">
                        <!-- Plantilla Aceptación -->
                        <div class="tab-pane fade show active" id="template-aceptacion" role="tabpanel">
                            <input type="hidden" id="template-aceptacion-id">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Asunto:</label>
                                <input type="text" class="form-control" id="asunto-aceptacion" placeholder="Asunto del correo">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Contenido:</label>
                                <textarea class="form-control" id="contenido-aceptacion" rows="10" placeholder="Contenido del correo"></textarea>
                            </div>
                            <div class="alert alert-info">
                                <strong>Variables disponibles:</strong> {first_names}, {lastnames}, {last_names}, {specialty}, {interest_area}, {email}
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-primary" id="btn-save-aceptacion">
                                    <i data-feather="save"></i> Guardar Plantilla de Aceptación
                                </button>
                            </div>
                        </div>

                        <!-- Plantilla Rechazo -->
                        <div class="tab-pane fade" id="template-rechazo" role="tabpanel">
                            <input type="hidden" id="template-rechazo-id">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Asunto:</label>
                                <input type="text" class="form-control" id="asunto-rechazo" placeholder="Asunto del correo">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Contenido:</label>
                                <textarea class="form-control" id="contenido-rechazo" rows="10" placeholder="Contenido del correo"></textarea>
                            </div>
                            <div class="alert alert-info">
                                <strong>Variables disponibles:</strong> {first_names}, {lastnames}, {last_names}, {specialty}, {interest_area}, {email}
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-primary" id="btn-save-rechazo">
                                    <i data-feather="save"></i> Guardar Plantilla de Rechazo
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /Modal Configurar Plantillas -->
