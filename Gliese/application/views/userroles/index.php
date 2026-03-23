    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Configuration Starts -->
                <section id="configuration">

                    <!-- Header title -->
                    <div class="content-header row">
                        <div class="content-header-left col-md-9 col-12 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Configuración</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="#"><?php echo $selected_menu; ?></a>
                                            </li>
                                            <li class="breadcrumb-item active"><span><?php echo $selected_sub_menu; ?></span>
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Header title-->

                    <!-- Configuration Tabs -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="roles-tab" data-bs-toggle="tab" href="#roles" aria-controls="roles" role="tab" aria-selected="true">
                                                <i data-feather="users"></i> Roles
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="security-tab" data-bs-toggle="tab" href="#security" aria-controls="security" role="tab" aria-selected="false">
                                                <i data-feather="shield"></i> Seguridad
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="reports-tab" data-bs-toggle="tab" href="#reports" aria-controls="reports" role="tab" aria-selected="false">
                                                <i data-feather="file-text"></i> Reportes
                                            </a>
                                        </li>
                                    </ul>

                                    <!-- Tab panes -->
                                    <div class="tab-content pt-2">
                                        <!-- Roles Tab -->
                                        <div class="tab-pane active" id="roles" aria-labelledby="roles-tab" role="tabpanel">
                                            <div id="roles-content">
                                                <!-- Table -->
                                                <div class="row">
                                                    <div class="col-12">
                                                        <table class="table" id="datatables-user-roles">
                                                            <thead>
                                                                <tr>
                                                                    <th>Descripción</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                        </table>
                                                    </div>
                                                </div>
                                                <!-- /Table -->
                                            </div>
                                        </div>

                                        <!-- Security Tab -->
                                        <div class="tab-pane" id="security" aria-labelledby="security-tab" role="tabpanel">
                                            <div id="security-content">
                                                <div class="d-flex justify-content-center my-3">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Cargando...</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Reports Tab -->
                                        <div class="tab-pane" id="reports" aria-labelledby="reports-tab" role="tabpanel">
                                            <div id="reports-content">
                                                <div class="d-flex justify-content-center my-3">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Cargando...</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Configuration Tabs -->

                    <!-- Add Role Modal -->
                    <div class="modal fade" id="create_user_role_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
                            <div class="modal-content">
                                <div class="modal-header bg-transparent">
                                    <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body px-5 pb-5">
                                    <div class="text-center mb-4">
                                        <h1 class="role-title">Agregar nuevo rol (practicante)</h1>
                                        <p>Establecer permisos del rol</p>
                                    </div>
                                    <!-- Add role form -->
                                    <form method="POST" enctype="multipart/form-data" id="create_user_role_form" class="row" onsubmit="return false">
                                        <div class="col-12">
                                            <label class="form-label">Descripción</label>
                                            <input type="text" name="description" class="form-control" placeholder="Descripción" autofocus data-msg="" required />
                                        </div>
                                        <div class="col-12">
                                            <br>
                                            <table class="table table-flush-spacing">
                                                <tr>
                                                    <td class="fw-bolder">
                                                        <label>ACCESO ADMINISTRADOR</label>
                                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Permite un acceso completo al sistema">
                                                            <i data-feather="info"></i>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="form-check me-3 me-lg-3">
                                                            <input class="form-check-input" type="checkbox" id="create_role_select_all" />
                                                            <label class="form-check-label">Seleccionar todo</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>

                                            <div class="col-12">
                                                <h5 class="mt-1 pt-10">Permisos</h5>
                                                <br>
                                                <div id="create_role_menu">
                                                </div>
                                            </div>

                                        </div>
                                        <div class="col-12 text-center mt-2">
                                            <button id="btn_create_user_role" type="submit" class="btn btn-primary mt-2 me-1">Guardar</button>
                                            <button type="reset" class="btn btn-outline-secondary mt-2 reset" data-bs-dismiss="modal" aria-label="Close">
                                                <span>Cancelar</span>
                                            </button>
                                        </div>
                                    </form>
                                    <!--/ Add role form -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ Add Role Modal -->

                    <!-- Update Role Modal -->
                    <div class="modal fade" id="update_user_role_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-transparent">
                                    <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body px-sm-5 pb-5">
                                    <div class="text-center mb-2">
                                        <h1 class="mb-1">Actualizar rol</h1>
                                        <p>Establecer permisos de rol</p>
                                    </div>

                                    <!-- Update role form -->
                                    <form method="POST" enctype="multipart/form-data" id="update_user_role_form" class="row" onsubmit="return false">
                                        <div class="col-12">
                                            <label class="form-label">Descripción</label>
                                            <input type="text" name="description" class="form-control" autofocus data-msg="" required />
                                        </div>
                                        <div class="col-12">
                                            <br>
                                            <table class="table table-flush-spacing">
                                                <tr>
                                                    <td class="fw-bolder">
                                                        <label>ACCESO ADMINISTRADOR</label>
                                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Permite un acceso completo al sistema">
                                                            <i data-feather="info"></i>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="form-check me-3 me-lg-3">
                                                            <input class="form-check-input" type="checkbox" id="update_role_select_all" />
                                                            <label class="form-check-label">Seleccionar todo</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>

                                            <div class="col-12">
                                                <h5 class="mt-1 pt-10">Permisos</h5>
                                                <br>
                                                <div id="update_role_menu">
                                                </div>
                                            </div>

                                        </div>
                                        <input type="hidden" name="id_user_role">
                                        <div class="col-12 text-center mt-2">
                                            <button id="btn_update_user_role" type="submit" class="btn btn-primary mt-2 me-1">Guardar</button>
                                            <button type="reset" class="btn btn-outline-secondary mt-2 reset" data-bs-dismiss="modal" aria-label="Close">
                                                <span>Cancelar</span>
                                            </button>
                                        </div>
                                    </form>
                                    <!--/ Update role form -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ Update Role Modal -->

                </section>
                <!-- Configuration ends -->

            </div>
        </div>
    </div>
    <!-- END: Content-->
