    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Bot Historial Starts -->
                <section id="bot-historial">

                    <!-- Header title -->
                    <div class="content-header row">
                        <div class="content-header-left col-md-9 col-12 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Historial del Bot WhatsApp</h2>
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

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bolder mb-75" id="total_usuarios">0</h3>
                                        <span>Total Usuarios</span>
                                    </div>
                                    <div class="avatar bg-light-primary p-50">
                                        <span class="avatar-content">
                                            <i data-feather="users" class="font-medium-4"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bolder mb-75" id="email_verificados">0</h3>
                                        <span>Email Verificado</span>
                                    </div>
                                    <div class="avatar bg-light-success p-50">
                                        <span class="avatar-content">
                                            <i data-feather="check-circle" class="font-medium-4"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bolder mb-75" id="formularios_completados">0</h3>
                                        <span>Formularios Completos</span>
                                    </div>
                                    <div class="avatar bg-light-info p-50">
                                        <span class="avatar-content">
                                            <i data-feather="file-text" class="font-medium-4"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bolder mb-75" id="grupos_enviados">0</h3>
                                        <span>Grupos Enviados</span>
                                    </div>
                                    <div class="avatar bg-light-warning p-50">
                                        <span class="avatar-content">
                                            <i data-feather="send" class="font-medium-4"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bolder mb-75" id="registros_completos">0</h3>
                                        <span>Completos</span>
                                    </div>
                                    <div class="avatar bg-light-success p-50">
                                        <span class="avatar-content">
                                            <i data-feather="check-square" class="font-medium-4"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-6">
                            <div class="card">
                                <div class="card-body d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="fw-bolder mb-75" id="registros_en_progreso">0</h3>
                                        <span>En Progreso</span>
                                    </div>
                                    <div class="avatar bg-light-primary p-50">
                                        <span class="avatar-content">
                                            <i data-feather="clock" class="font-medium-4"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Statistics Cards -->

                    <!-- DataTable -->
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h4 class="card-title">Historial de Estados de Usuario</h4>
                        </div>
                        <div class="card-datatable">
                            <table class="dt-responsive table" id="historial-table">
                                <thead>
                                    <tr>
                                        <th>Teléfono</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Estado</th>
                                        <th>Estado Formulario</th>
                                        <th>Email Verificado</th>
                                        <th>Grupo Enviado</th>
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
                <!-- Bot Historial Ends -->
            </div>
        </div>
    </div>
    <!-- END: Content-->

    <!-- Modal Detalle Usuario -->
    <div class="modal fade" id="modal-user-detail" tabindex="-1" aria-labelledby="modalUserDetailLabel">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUserDetailLabel">Detalle de Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- User Info -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="fw-bold">Información General</h6>
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold" width="200">Teléfono:</td>
                                        <td id="detail-phone"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Nombre:</td>
                                        <td id="detail-name"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td id="detail-email"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Estado Actual:</td>
                                        <td id="detail-state"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Estado Anterior:</td>
                                        <td id="detail-previous_state"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Clave Estado Actual:</td>
                                        <td id="detail-state_option_key"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Clave Estado Anterior:</td>
                                        <td id="detail-previous_state_option_key"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Estado Formulario:</td>
                                        <td id="detail-form_status"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email Verificado:</td>
                                        <td id="detail-email_verified"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Grupo Enviado:</td>
                                        <td id="detail-group_assigned"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Código Verificación:</td>
                                        <td id="detail-codigo"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Código Expiración:</td>
                                        <td id="detail-code_expiration"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Formulario Enviado:</td>
                                        <td id="detail-form_submitted"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Formulario Completado:</td>
                                        <td id="detail-form_completed"></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Último Mensaje:</td>
                                        <td id="last_message"></td>
                                    </tr>
                                </tbody>
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
    <!-- /Modal Detalle Usuario -->
