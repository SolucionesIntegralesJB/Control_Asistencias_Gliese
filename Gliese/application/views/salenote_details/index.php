<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Campus Starts -->
            <section id="salenote_details">

                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Notas de Venta</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Header table-->

                <!-- Container for adding products -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="create_salenote_form" class="row" onsubmit="return false">
                            <!-- First Row -->

                            <!-- Primera fila: Cliente - Moneda - Fecha -->
                            <!-- Primera fila: Cliente - Moneda - Fecha (partes iguales) -->
                            <div class="row mb-3">
                                <!-- Cliente -->
                                <div class="col-md-4">
                                    <label class="form-label">Cliente(*)</label>
                                    <select name="id_clients" class="form-select select2" required>
                                        <!-- Opciones para el select -->
                                    </select>
                                </div>

                                <!-- Moneda -->
                                <div class="col-md-4">
                                    <label class="form-label">Moneda</label>
                                    <span name="coins" class="form-control"></span>
                                    <input name="id_coins" type="hidden" class="form-control" readonly>
                                </div>

                                <!-- Fecha -->
                                <div class="col-md-4">
                                    <label class="form-label">Fecha(*)</label>
                                    <input name="date_issue" type="date" class="form-control" id="date_issue">
                                </div>
                            </div>

                            <!-- Segunda fila: Nº Documento - Dirección - Tipo Comprobante - IGV -->
                            <div class="row mb-3">
                                <!-- Nº de documento -->
                                <div class="col-md-3">
                                    <label class="form-label">Nº de documento</label>
                                    <input name="document_number_cli" type="text" class="form-control" readonly>
                                </div>

                                <!-- Dirección -->
                                <div class="col-md-4">
                                    <label class="form-label">Dirección</label>
                                    <input name="address_cli" type="text" class="form-control" readonly>
                                </div>

                                <!-- Tipo de comprobante -->
                                <div class="col-md-3">
                                    <label class="form-label">Tipo Comprobante(*)</label>
                                    <select name="id_voucher_type" class="form-select select2" required>
                                        <option value="8">Nota de venta</option>
                                    </select>
                                </div>

                                <!-- IGV -->
                                <div class="col-md-2">
                                    <label class="form-label">IGV</label>
                                    <input name="igv" type="text" class="form-control" readonly>
                                </div>
                            </div>


                            <input type="hidden" name="id_user" id="id_user">
                            <input type="hidden" name="id_campus" id="id_campus">

                            <div class="row mb-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_income_product_modal">Agregar productos</button>
                                </div>
                            </div>
                            <!-- Table -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <table class="table table-sm" id="add_products">
                                            <thead>
                                                <tr>
                                                    <th>Acciones</th>
                                                    <th>Código</th>
                                                    <th>Artículo</th>
                                                    <th>Serie</th>
                                                    <th>U.Medida</th>
                                                    <th>Cantidad</th>
                                                    <th>Venta U.</th>
                                                    <th>Tributo</th>
                                                    <th>Impuestos</th>
                                                    <th>Precio Venta</th>
                                                    <th>Venta Total</th>
                                                    <th>Importe</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Product rows will be added here -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="8"></th>
                                                    <th style="text-align: right;" colspan="2">TOTAL VENTA GRAVADO &nbsp;&nbsp;S/</th>
                                                    <th style="text-align: right;">
                                                        <h4 id="totalg">0.00</h4>
                                                        <input type="hidden" name="op_taxed" id="op_taxed">
                                                    </th>
                                                    <th colspan="11"></th>
                                                </tr>
                                                <tr>
                                                    <th colspan="8"></th>
                                                    <th style="text-align: right;" colspan="2">TOTAL VENTA EXONERADO &nbsp;&nbsp;S/</th>
                                                    <th style="text-align: right;">
                                                        <h4 id="totale">0.00</h4>
                                                        <input type="hidden" name="op_exonerated" id="op_exonerated">
                                                    </th>
                                                    <th colspan="11"></th>
                                                </tr>
                                                <tr>
                                                    <th colspan="8"></th>
                                                    <th style="text-align: right;" colspan="2">TOTAL VENTA INAFECTAS &nbsp;&nbsp;S/</th>
                                                    <th style="text-align: right;">
                                                        <h4 id="totali">0.00</h4>
                                                        <input type="hidden" name="op_unaffected" id="op_unaffected">
                                                    </th>
                                                    <th colspan="11"></th>
                                                </tr>
                                                <tr>
                                                    <th colspan="8"></th>
                                                    <th style="text-align: right;" colspan="2">TOTAL VENTA GRATUITAS &nbsp;&nbsp;S/</th>
                                                    <th style="text-align: right;">
                                                        <h4 id="totalgt">0.00</h4>
                                                        <input type="hidden" name="op_free" id="op_free">
                                                    </th>
                                                    <th colspan="11"></th>
                                                </tr>
                                                <tr>
                                                    <th colspan="8"></th>
                                                    <th style="text-align: right;" colspan="2">I.G.V. &nbsp;&nbsp;&nbsp;S/</th>
                                                    <th style="text-align: right;">
                                                        <h4 id="totaligv">0.00</h4>
                                                        <input type="hidden" name="igv_total" id="igv_total">
                                                    </th>
                                                    <th colspan="11"></th>
                                                </tr>
                                                <tr>
                                                    <th colspan="8"></th>
                                                    <th style="text-align: right;" colspan="2">TOTAL IMPORTE &nbsp;&nbsp;&nbsp;S/</th>
                                                    <th style="text-align: right;">
                                                        <h4 id="totalimp">0.00</h4>
                                                        <input type="hidden" name="total_sale" id="total_sale">
                                                    </th>
                                                    <th colspan="11"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- /Table -->
                            <div class="col-12">
                                <button id="btn_create_form" type="submit" class="btn btn-primary">Guardar</button>
                                <button type="button" id="btn_cancel_form" class="btn btn-secondary">Cancelar</button>
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

                                <table class="table table-striped" id="datatables-salenote-products">
                                    <thead>
                                        <tr>
                                            <th>Acción</th>
                                            <th>Código</th>
                                            <th>Nombre</th>
                                            <th>U.Medida</th>
                                            <th>Stock</th>
                                            <th>Precio Venta</th>
                                            <th>Tipo de Tributo</th>
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

<script src="<?= BASE_URL ?>public/salenote_details/js/index.js"></script>
