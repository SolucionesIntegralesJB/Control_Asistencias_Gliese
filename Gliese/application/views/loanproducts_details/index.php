<!-- BEGIN: Content-->
<div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Campus Starts -->
                <section id="loanproducts_detail">

                    <!-- Header title -->
                    <div class="content-header row">
                        <div class="content-header-left col-md-9 col-12 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Préstamo de Productos</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Header table-->

                    <!-- Container for adding products -->
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" id="create_income_details_form" class="row" onsubmit="return false">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <div>
                                            <label class="form-label">Sucursal</label>
                                            <select name="business_name" class="form-select select2" data-msg="">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Fecha</label>
                                        <input name="proof_date" type="date" class="form-control">
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                    <label class="form-label">Nº de documento</label>
                                    <input name="document_number_cli" type="text" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                    <label class="form-label">Dirección</label>
                                    <input name="address_cli" type="text" class="form-control">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_income_product_modal">Prestar productos</button>
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
                                                        <th>Producto</th>
                                                        <th>Serie</th>
                                                        <th>U.Medida</th>
                                                        <th>Cantidad</th>
                                                        <th>Precio venta</th>
                                                        <th>Importe</th>
                                                    </tr>
                                                </thead>
                                     
                                            <tfoot>
                                                <tr>
                                                <th colspan="4"></th>
                                                <th colspan="2">TOTAL VENTA GRAVADO</th>
                                                <th>
                                                    <h4 id="totalg">0.00</h4><input type="hidden" name="total_venta_gravado" id="total_venta_gravado">
                                                </th>
                        
                                                <h4 id="totale"></h4><input type="hidden" name="total_venta_exonerado" id="total_venta_exonerado">

                                                <h4 id="totali"></h4><input type="hidden" name="total_venta_inafectas" id="total_venta_inafectas">
                        
                                                <h4 id="totalgt"></h4><input type="hidden" name="total_venta_gratuitas" id="total_venta_gratuitas">
                            
                                                </tr>
                                                <tr>
                                                <th colspan="4"></th>
                                                <th colspan="2">TOTAL DESCUENTO</th>
                                                <th>
                                                    <h4 id="totald">0.00</h4><input type="hidden" name="total_descuentos" id="total_descuentos">
                                                </th>
                            
                                                <h4 id="totalisc"></h4><input type="hidden" name="isc" id="isc">
                                                
                                                </tr>
                                                <tr>
                                                <th style="height:2px;" colspan="4"></th>
                                                <th colspan="2">IGV</th>
                                                <th>
                                                    <h4 id="totaligv">0.00</h4><input type="hidden" name="total_igv" id="total_igv">
                                                </th>
                                                </tr>
                                                <tr>
                                                <th style="height:2px;" colspan="4"></th>
                                                <th style="height:2px;" colspan="2">TOTAL IMPORTE</th>
                                                <th style="height:2px;">
                                                    <h4 id="totalimp">0.00</h4><input type="hidden" name="total_importe" id="total_importe">
                                                </th>
                                                </tr>
                                            </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Table -->
                                
                                 <!-- Table -->
                                 <div class="row" style="display: none;">
                                    <div class="col-12">
                                        <div class="card">
                                            <table class="table" id="add_cuota">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                        <label class="form-label">N° Cuotas</label>
                                                        <input name="cuota" type="number" class="form-control" id="cuota" min="1" >
                                                        <th>
                                                            <label class="form-label">Valor de cuota</label>
                                                            <div>
                                                                <span id="valor-de-cuota">0</span>
                                                            </div>
                                                        </th>
                                                        <th>
                                                        <label class="form-label">Tipo de crédito</label>
                                                        <select name="credit_type" class="form-control" id="credit_type">
                                                                <option value="diario">Diario</option>
                                                                <option value="mensual">Mensual</option>
                                                                <option value="selectime">Seleccionar fechas</option>
                                                            </select>
                                                        </th>
                                                        <th>
                                                        <label class="form-label" id="start_date_label">Fecha de inicio</label>
                                                        <input name="proof_date" type="date" class="form-control" id="start_date">
                                                        </th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Table -->
                                <!-- Table -->
                                <div class="row" style="display: none;">
                                    <table id="tblListadoFechas" class="table table-striped table-bordered table-condensed table-hover">
                                        <thead>
                                            <tr>
                                                <th>Nro de cuota</th>
                                                <th>Valor de cuota</th>
                                                <th>Fecha de pago</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <!-- /Table -->

                                <div class="col-12">
                                    <button id="btn_update_guardar" type="submit" class="btn btn-secondary">Guardar</button>
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='Income/index.php'">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- End Container to add products -->

                    <!-- Create Income Products Modal -->
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="modal fade" id="create_income_product_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-edit-product">
                                <!-- Contenido del modal -->
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-transparent pb-3">
                                            <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body px-sm-5 pb-5">
                                            <div class="text-center mb-2">
                                                <h1 class="mb-1">Selecionar Producto</h1>
                                            </div>
                                            <table class="table table-striped" id="datatables-income-products">
                                                <thead>
                                                    <tr>
                                                        <th>Acción</th>
                                                        <th>Codigo</th>
                                                        <th>Categoría</th>
                                                        <th>Descripcion</th>
                                                        <th>Stock</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
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