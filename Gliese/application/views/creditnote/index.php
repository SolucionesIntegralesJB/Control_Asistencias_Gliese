<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Creditnote Starts -->
            <section id="creditnote">

                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Lista de <?php echo strtolower($selected_sub_menu); ?></h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#">Notas</a>
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

                <!-- Botones de acción -->
                <div class="box-header with-border">
                    <h1 class="box-title">
                        <button class="btn btn-primary" id="btnescoger" data-bs-toggle="modal" data-bs-target="#eligeModal">
                            <i class="fa fa-plus-circle"></i> Escoger tipo de nota de credito
                        </button>
                        <button class="btn btn-primary" id="btnagregar" data-bs-toggle="modal" data-bs-target="#tblBillingpersaleModal">
                            <i class="fa fa-plus-circle"></i> Agregar nueva nota de credito
                        </button>
                    </h1>
                </div>
                <!-- /Botones de acción -->

                <!-- Tabla principal -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <table class="table" id="datatable-creditnote">
                                <thead>
                                    <th>Opciones</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Usuario</th>
                                    <th>Documento</th>
                                    <th>Número</th>
                                    <th>Motivo</th>
                                    <th>ID. Doc. Relacionado</th>
                                    <th>Respuesta SUNAT</th>
                                    <th>Estado</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /Tabla principal -->

                <!-- Modal Tipo de Nota -->
                <div class="modal fade" id="eligeModal" tabindex="-1" aria-labelledby="eligeModalLabel">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <h5 class="modal-title text-white">Escoger tipo de Nota de Crédito</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Seleccione el motivo:</label>
                                    <select id="tipoNotaC" class="form-select" required>
                                        <option value="" disabled selected>-- Seleccionar --</option>
                                        <option value="1">Anulación de la operación</option>
                                        <option value="2">Anulación por error en el RUC</option>
                                        <option value="3">Devolución total</option>
                                        <option value="4">Devolución parcial</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fa fa-times me-1"></i> Cancelar
                                </button>
                                <button type="button" class="btn btn-primary" id="save_creditnote_type" data-bs-dismiss="modal">
                                    <i class="fa fa-check me-1"></i> Aceptar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Modal Tipo de Nota -->

                <!-- Modal: Tabla de Billingpersale -->
                <div class="modal fade" id="tblBillingpersaleModal" tabindex="-1" aria-labelledby="tblBillingpersaleModalLabel">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-primary">
                                <h5 class="modal-title text-white">Agregar nueva nota de credito</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                                <!-- Tabla -->
                                <div class="table-responsive">
                                    <table class="table table-striped" id="datatable-billingpersale">
                                        <thead>
                                            <tr>
                                                <th>Opciones</th>
                                                <th>Fecha</th>
                                                <th>Cliente</th>
                                                <th>Usuario</th>
                                                <th>Documento</th>
                                                <th>Número</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fa fa-times me-1"></i> Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Modal: Tabla de Billingpersale -->

                <div class="modal fade" id="documentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="documentDetailsModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="documentDetailsModalLabel">Detalles del Documento</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="documentDetailsModalBody">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fa fa-times me-1"></i> Cerrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </div>
    </div>
</div>
<!-- END: Content-->