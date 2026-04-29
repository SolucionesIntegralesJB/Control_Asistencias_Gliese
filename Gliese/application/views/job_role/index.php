<!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Job Roles Starts -->
                <section id="job_role">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
                    <!-- Header title -->
                    <div class="content-header row">
                        <div class="content-header-left col-md-9 col-12 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Lista de <?php echo strtolower($selected_sub_menu); ?></h2>
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

                    <!-- Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <table class="table" id="datatable-job_role">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Rol de Trabajo</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /Table -->

                    <!-- Create Job Role Modal -->
                    <div class="modal fade" id="create_job_role_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-transparent">
                                    <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body px-sm-5 pb-5">
                                    <div class="text-center mb-2">
                                        <h1 class="mb-1">Agregar nuevo rol de trabajo</h1>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data" id="create_job_role_form" class="row" onsubmit="return false">
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Rol de Trabajo</label>
                                                <input type="text" id="job_role" name="job_role" class="form-control" placeholder="Ingrese el rol de trabajo" autofocus data-msg="" required />
                                            </div>
                                        </div>
                                        <div class="col-12 text-center">
                                            <button id="btn_create_job_role" type="submit" class="btn btn-primary mt-2 me-1">Guardar</button>
                                            <button type="reset" class="btn btn-outline-secondary mt-2 reset" data-bs-dismiss="modal" aria-label="Close">
                                                <span>Cancelar</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ Create Job Role Modal -->

                    <!-- Update Job Role Modal -->
                    <div class="modal fade" id="update_job_role_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-edit-job_role">
                            <div class="modal-content">
                                <div class="modal-header bg-transparent">
                                    <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pb-5 px-sm-5 pt-50">
                                    <div class="text-center mb-2">
                                        <h1 class="mb-1">Actualizar rol de trabajo</h1>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data" id="update_job_role_form" class="row" onsubmit="return false">
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Rol de Trabajo</label>
                                                <input type="text" name="job_role" class="form-control" placeholder="Ingrese el rol de trabajo" autofocus data-msg="" required />
                                            </div>
                                        </div>
                                        <input type="hidden" name="id_job_role">
                                        <div class="col-12 text-center mt-2 pt-50">
                                            <button id="btn_update_job_role" type="submit" class="btn btn-primary me-1">Guardar</button>
                                            <button type="reset" class="btn btn-outline-secondary reset" data-bs-dismiss="modal">Cancelar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ Update Job Role Modal -->

                </section>
                <!-- Job Roles ends -->

            </div>
        </div>
    </div>
    <!-- END: Content-->
