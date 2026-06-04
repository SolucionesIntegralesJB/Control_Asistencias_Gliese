    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Users Starts -->
                <section id="employees">
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

                    <!-- Filters -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-1">
                                            <label class="form-label">Estado</label>
                                            <select id="filter_status" class="form-select">
                                                <option value="">Todos</option>
                                                <option value="1">Activos</option>
                                                <option value="0">Inactivos</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1 mb-1 d-flex align-items-end">
                                            <button id="btn_filter" class="btn btn-primary w-100">
                                                <i class="bi bi-filter"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Filters -->

                    <!-- Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <table class="table" id="datatable-employees">
                                    <thead>
                                        <tr>
                                            <th style="width: 200px;">Nombre</th>
                                            <th>Nº de documento</th>
                                            <th>Direccion</th>
                                            <th>Email</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /Table -->

                    <!-- Create Supplier Modal -->
                    <div class="modal fade" id="create_employees_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"> <!--  aria-hidden="true" -->
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-transparent">
                                    <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body px-sm-5 pb-5">
                                    <div class="text-center mb-2">
                                        <h1 class="mb-1">Agregar nuevo empleado</h1>
                                        <!-- <p data-i18n="Add new campus description">Permissions you may use and assign to your users.</p> -->
                                    </div>
                                    <form method="POST" enctype="multipart/form-data" id="create_employees_form" class="row" onsubmit="return false">
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Tipo de documento</label>
                                                <select id="document_type" name="document_type" class="form-select select2" data-msg="" required>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Nº De Documento</label>
                                            <div class="input-group">
                                                <input type="number" id="document_number" name="document_number" class="form-control" placeholder="Nº De Documento" autofocus data-msg="" required />
                                                <button type="button" class="btn btn-primary btn_get_company_data">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Nombre</label>
                                            <input type="text" id="name" name="name" class="form-control" placeholder="Nombre" autofocus data-msg="" required />
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" placeholder="user@example.com" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Teléfono</label>
                                                <input type="phone" name="phone" class="form-control" placeholder="Teléfono" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Dirección</label>
                                                <input type="address" id="address" name="address" class="form-control" placeholder="Dirección" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Referencia</label>
                                                <!-- <input type="text" name="reference" class="form-control" placeholder="user@example.com" data-msg=""/> -->
                                                <textarea name="reference" class="form-control" id="" cols="2" rows="2" style="max-height: 68px;" placeholder="Referencia"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Área de trabajo</label>
                                                <input type="text" name="work_area" class="form-control" placeholder="Área de trabajo" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Cargo</label>
                                                <input type="text" name="position" class="form-control" placeholder="Cargo" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Sueldo</label>
                                                <input type="number" step="0.01" min="0" name="salary" class="form-control" placeholder="0.00" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Contraseña (Attendance System)</label>
                                                <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Confirmar Contraseña</label>
                                                <input type="password" name="password_confirm" class="form-control" placeholder="Repita la contraseña" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12 text-center">
                                            <button id="btn_create_employees" type="submit" class="btn btn-primary mt-2 me-1">Guardar</button>
                                            <button type="reset" class="btn btn-outline-secondary mt-2 reset" data-bs-dismiss="modal" aria-label="Close">
                                                <span>Cancelar</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ Create Supplier Modal -->


                    <!-- Update User Modal -->
                    <div class="modal fade" id="update_employees_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-edit-employees">
                            <div class="modal-content">
                                <div class="modal-header bg-transparent">
                                    <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pb-5 px-sm-5 pt-50">
                                    <div class="text-center mb-2">
                                        <h1 class="mb-1">Actualizar empleado</h1>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data" id="update_employees_form" class="row" onsubmit="return false">
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Tipo de documento</label>
                                                <select name="document_type" class="form-select select2" data-msg="" required>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Nº De Documento</label>
                                            <input type="number" name="document_number" class="form-control" placeholder="Nº De Documento" autofocus data-msg="" required />
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Nombre</label>
                                            <input type="text" name="name" class="form-control" placeholder="Nombre" autofocus data-msg="" required />
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" placeholder="user@example.com" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Teléfono</label>
                                                <input type="phone" name="phone" class="form-control" placeholder="Teléfono" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Dirección</label>
                                                <input type="address" name="address" class="form-control" placeholder="Dirección" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Referencia</label>
                                                <textarea name="reference" class="form-control" id="" cols="2" rows="2" style="max-height: 68px;" placeholder="Referencia"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Área de trabajo</label>
                                                <input type="text" name="work_area" class="form-control" placeholder="Área de trabajo" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Cargo</label>
                                                <input type="text" name="position" class="form-control" placeholder="Cargo" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Sueldo</label>
                                                <input type="number" step="0.01" min="0" name="salary" class="form-control" placeholder="0.00" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Contraseña (Attendance System) <small class="text-muted">- Dejar vacío para mantener actual</small></label>
                                                <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" data-msg="" />
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div>
                                                <label class="form-label">Confirmar Contraseña</label>
                                                <input type="password" name="password_confirm" class="form-control" placeholder="Repita la contraseña" data-msg="" />
                                            </div>
                                        </div>
                                        <input type="hidden" name="id_employees">
                                        <div class="col-12 text-center mt-2 pt-50">
                                            <button id="btn_update_employees" type="submit" class="btn btn-primary me-1">Guardar</button>
                                            <button type="reset" class="btn btn-outline-secondary reset" data-bs-dismiss="modal">Cancelar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ Update User Modal -->

                </section>
                <!-- Users ends -->

            </div>
        </div>
    </div>
    <!-- END: Content-->