<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Creditnote Starts -->
            <section id="creditnote_details">

                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Agregar nueva Nota de Crédito</h2>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Header table-->

                <!-- Container for adding products -->
                <div class="cards">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="add_billingpersale_form" class="row" onsubmit="return false">

                            <!-- Credit Note Type -->
                            <div class="row mb-3 justify-content-center">
                                <div class="col-md-6 text-center">
                                    <div class="card" style="background-color:rgb(115, 103, 240);">
                                        <div class="card-body">
                                            <p name="credit_note_type" id="credit_note_type" class="card-text text-white"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /Credit Note Type -->

                            <!-- First Row -->
                            <div class="row mb-3">
                                <div class="col-md-8"> <!-- Amplía el ancho de la columna del Cliente -->
                                    <label class="form-label"><strong>Cliente(*):</strong></label>
                                    <input name="client" type="text" class="form-control" disabled>

                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label"><strong>N° de documento:</strong></label>
                                            <input name="document_number_client" type="text" class="form-control" disabled>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label"><strong>Serie:</strong></label>
                                            <input name="series" type="text" class="form-control" disabled>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label"><strong>Correlativo:</strong></label>
                                            <input name="correlative" type="text" class="form-control" disabled>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label"><strong>Sustento:</strong></label>
                                        <input name="leyend" type="text" class="form-control" placeholder="Motivo o sustento" required>
                                    </div>

                                </div>

                                <div class="col-md-4">
                                    <label class="form-label"><strong>Fecha (*):</strong></label>
                                    <input name="due_date" type="text" class="form-control" id="fecha_actual" disabled>

                                    <div class="col-md-12"> <!-- Iguala el ancho de la columna de Moneda -->
                                        <label class="form-label"><strong>Moneda:</strong></label>
                                        <input name="currency" type="text" class="form-control" disabled>
                                    </div>

                                </div>
                            </div>
                            <!-- /First Row -->

                            <!-- Second Row -->
                            <div class="row mb-3">
                                <!-- Table -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <table class="table" id="add_products">
                                                <thead>
                                                    <tr>
                                                        <th>Artículo</th>
                                                        <th>Serie</th>
                                                        <th>Afectación</th>
                                                        <th>Cantidad</th>
                                                        <th>Val. Vta. U.</th>
                                                        <th>Descuento</th>
                                                        <th>Impuestos</th>
                                                        <th>Precio Venta</th>
                                                        <th>Importe</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Fila única (solo lectura) -->
                                                    <tr>
                                                        <td name="article">-</td>
                                                        <td name="serie">-</td>
                                                        <td name="tax_affectation">-</td>
                                                        <td name="quantity">-</td>
                                                        <td name="unit_value">-</td>
                                                        <td name="discount">-</td>
                                                        <td name="tax_amount">-</td>
                                                        <td name="sale_price">-</td>
                                                        <td name="amount">-</td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="7" class="text-end"><strong>TOTAL VENTA GRAVADO</strong></td>
                                                        <td name="total_sale_taxed">0.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="7" class="text-end"><strong>IGV</strong></td>
                                                        <td name="igv">0.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="7" class="text-end"><strong>TOTAL IMPORTE</strong></td>
                                                        <td name="total_amount">0.00</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- /Table -->
                                <div class="col-12">
                                    <button id="btn_guardar" type="submit" class="btn btn-primary">Guardar</button>
                                    <button type="button" class="btn btn-secondary" onclick="window.location.href='Creditnote/index.php'">Cancelar</button>
                                </div>
                            </div>
                            <!-- /Second Row -->
                        </form>
                    </div>
                </div>
                <!-- /Container for adding products -->
            </section>
            <!-- Permissions ends -->
        </div>
    </div>
</div>
<!-- END: Content-->