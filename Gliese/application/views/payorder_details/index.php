<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Campus Starts -->
            <section id="payorder_details">

                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Orden de pagos</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Header table-->

                <!-- Container for adding products -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="create_income_details_form" class="row" onsubmit="return false">
                            <!-- First Row -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div>
                                        <label class="form-label">Cliente(*)</label>
                                        <select name="business_name_cli" class="form-select select2" required>
                                        <!-- Opciones para el select -->
                                    </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <label class="form-label">Fecha (*)</label>
                                    <input name="fecha_emision" type="date" class="form-control">
                                </div>

                                <!-- <div class="col-md-2">
                                    <label class="form-label">Fecha Vencimiento (*)</label>
                                    <input name="fecha_vencimiento" type="date" class="form-control">
                                </div> -->

                                <div class="col-md-2">
                                    <label class="form-label">Moneda</label>
                                    <input name="coins" type="text" class="form-control" disabled>
                                </div>

                                

                            </div>
                            <!-- Second Row -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Nº de documento</label>
                                    <input name="document_number_cli" type="text" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Dirección</label>
                                    <input name="address_cli" type="text" class="form-control">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Impuesto</label>
                                    <input name="igv" type="text" class="form-control" disabled>
                                </div>
                               
                            </div>

                            <div class="row mb-3">
                                <div class="col-12">
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_income_product_modal">Agregar productos</button>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <table class="table" id="add_products">
                                            <thead>
                                                <tr>
                                                    <th>Artículo</th>
                                                    <th>Serie</th>
                                                    <th>U.Medida</th>
                                                    <th>Cantidad</th>
                                                    <th>Descuento</th>
                                                    <th>Precio Venta</th>
                                                    <th>Importe</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- /Table -->

                            <div class="col-12">
                                <button id="btn_update_guardar" type="submit" class="btn btn-secondary">Guardar</button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='Payorder/index.php'">Cancelar</button>
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
                                <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-sm-5 pb-5">
                                <div class="text-center mb-2">
                                    <h1 class="mb-1">Seleccionar Producto</h1>
                                </div>
                                <table class="table table-striped" id="datatables-income-products">
                                    <thead>
                                        <tr>
                                            <th>Acción</th>
                                            <th>Nombre</th>
                                            <th>U.Medida</th>
                                            <th>Categoría</th>
                                            <th>Código</th>
                                            <th>Stock</th>
                                            <th>Precio Venta</th>
                                            <th>Imagen</th>
                                            <th>Afectación</th>
                                        </tr>
                                    </thead>
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
