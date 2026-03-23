<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Campus Starts -->
            <section id="referralguide_details">

                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-8 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Guía de Remisión</h2>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right col-md-4 text-end">
                        <button id="destinatarioButton" class="btn btn-primary create-new">Destinatario</button>
                    </div>
                </div>
                <!-- /Header table-->

                <!-- Container for adding products -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="create_income_details_form" class="row" onsubmit="return false">
                            <!-- Datos de Traslado -->
                            <div class="row mb-3">
                                <h2 class="col-12">Datos de Inicio de Traslado</h2>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo Comprobante (*)</label>
                                    <select name="vt_description" class="form-select select2" data-msg="" required>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Fecha de Emisión (*)</label>
                                    <input name="date_issue" type="text" class="form-control" id="date_issue" required readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Fecha de Traslado (*)</label>
                                    <input name="date_transfer" type="date" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Motivo de Traslado (*)</label>
                                    <select name="mt_description" class="form-select select2" data-msg="" required> 
                                    </select>
                                </div>
                            </div>

                            <!-- Modalidad de Transporte -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Modalidad de Transporte (*)</label>
                                    <input name="modality_transport" type="text" class="form-control" placeholder="Ingrese Modalidad de Transporte" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Traslado (*)</label>
                                    <input name="transfer_type" type="text" class="form-control" placeholder="Ingrese Tipo de Traslado" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Peso bruto de la Guía (*)</label>
                                    <input name="gross_weight" type="text" class="form-control" placeholder="Ingrese Peso Bruto" required>
                                </div>
                            </div>

                            <!-- Datos del Destinatario -->
                            <div class="row mb-3">
                            <h2 class="col-12">Datos del Destinatario</h2>
                                <div class="col-md-6">
                                    <label class="form-label">Apellidos y Nombres, Denominación o Razón (*)</label>
                                    <select name="business_name_cli" class="form-select select2" required>
                                        <!-- Opciones para el select -->
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nº de Documento o RUC (*)</label>
                                    <input name="document_number_cli" type="text" class="form-control" placeholder="Ingrese Nº de Documento o RUC" required>
                                </div>
                            </div>

                            <!-- Datos del Transportista -->
                            <div class="row mb-3">
                                <h2 class="col-12">Datos del Transportista</h2>
                                <div class="col-md-4">
                                    <label class="form-label">Razón Social (*)</label>
                                    <select name="business_name_carrier" class="form-select select2" required>
                                        <!-- Opciones para el select -->
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Nº de Documento o RUC (*)</label>
                                    <input name="document_number_trans" type="text" class="form-control" placeholder="Nº de Documento o RUC" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Marca (*)</label>
                                    <input name="brand" type="text" class="form-control" placeholder="Marca" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Placa (*)</label>
                                    <input name="plate" type="text" class="form-control" placeholder="Placa" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Licencia de Conducir (*)</label>
                                    <input name="drivers_license" type="text" class="form-control" placeholder="Licencia de Conducir" required>
                                </div>
                            </div>

                            <!-- Dirección de Punto de Partida y Llegada -->
                            <div class="row mb-3">
                                <h2 class="col-12">Datos de Destino y Origen</h2>
                                <div class="col-md-6">
                                    <label class="form-label">Dirección de Punto de Partida</label>
                                    <select name="description" class="form-select select2" required>
                                        <!-- Opciones para el select -->
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Dirección de Punto de Llegada</label>
                                    <input name="punto_llegada" type="text" class="form-control" placeholder="Ingrese la Dirección de Punto de Llegada" required>
                                </div>
                            </div>

                            <!-- Botón para Agregar Productos -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_income_product_modal">Seleccionar Comprobante</button>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <table class="table" id="add_products">
                                            <thead>
                                                <tr>
                                                    <th>Acciones</th>
                                                    <th>Artículo</th>
                                                    <th>Unidad Medida</th>
                                                    <th>Cantidad</th>
                                                </tr>
                                            </thead>
                                            <!-- Aquí puedes agregar filas para los productos en la tabla -->
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- /Table -->

                            <div class="col-12">
                                <button id="btn_update_guardar" type="submit" class="btn btn-secondary">Guardar</button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='Referralguide/index.php'">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- End Container to add products -->

                <!-- Create Income Products Modal -->
                <div class="modal fade" id="create_income_product_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <!-- Contenido del modal -->
                        <div class="modal-content">
                            <div class="modal-header bg-transparent pb-3">
                                <button type="button" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-sm-5 pb-5">
                                <div class="text-center mb-2">
                                    <h1 class="mb-1">Seleccionar Producto</h1>
                                </div>
                                <table class="table table-striped" id="datatables-income-products">
                                    <thead>
                                        <tr>
                                            <th>Acción</th>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th>Usuario</th>
                                            <th>Documento</th>
                                            <th>Número</th>
                                            <th>Total Venta</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <!-- Aquí puedes agregar productos desde el modal -->
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Create Income Products Modal -->

            </section>
            <!-- Permissions ends -->
        </div>
    </div>
</div>
<!-- END: Content-->

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=AIzaSyCCovVks8Nwf62J2M369dhsqltG_e3YezU"></script>
