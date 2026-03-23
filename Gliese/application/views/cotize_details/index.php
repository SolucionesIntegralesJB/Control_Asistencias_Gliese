<?php
// Obtener datos de clientes
$clients = $this->get_data('clients');
$hasClients = !empty($clients) && is_array($clients);
?>

<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row"></div>
        <div class="content-body">
            <section id="cotize-details">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="create_income_details_form" onsubmit="return false">
                            <!-- Campos ocultos para el modo y el ID -->
                            <input type="hidden" id="view_mode" value="<?php echo isset($view_mode) ? $view_mode : 'create'; ?>">
                            <input type="hidden" id="cotize_id" name="cotize_id" value="<?php echo isset($cotize['id']) ? $cotize['id'] : ''; ?>">
                            
                            <!-- First Row -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div>
                                        <label class="form-label">Cliente(*)</label>
                                        <select name="business_name_cli" class="form-select select2" required onchange="cargarDatosCliente(this.value)">
                                            <option value="">Seleccione un cliente</option>
                                            <?php if ($hasClients): ?>
                                                <?php foreach ($clients as $client): ?>
                                                    <option value="<?php echo htmlspecialchars($client['id']); ?>">
                                                        <?php echo htmlspecialchars($client['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Fecha Emisión (*)</label>
                                    <input name="fecha_emision" type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Impuesto</label>
                                    <input name="igv" type="text" class="form-control" value="18%" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Referencia</label>
                                    <input name="referencia" type="text" placeholder="Agregue una referencia" class="form-control">
                                </div>
                            </div>
                            
                            <!-- Second Row -->
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label class="form-label">Nº de documento</label>
                                    <input name="document_number_cli" type="text" class="form-control" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Dirección</label>
                                    <input name="address_cli" type="text" class="form-control" readonly>
                                </div>
                                <div class="col-md-3">
                                    <div>
                                        <label class="form-label">Tipo Cotización(*)</label>
                                        <input name="pt_description" type="text" class="form-control" value="Cotización Regular" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div>
                                        <label class="form-label">Validez de la Oferta(*)</label>
                                        <input name="validez_oferta" type="text" placeholder="Validez de la Oferta" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón Agregar Producto -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" onclick="openProductModal()">
                                        <i class="fas fa-plus me-1"></i> Agregar Producto
                                    </button>
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
                                                    <th>Descripción</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio Unitario</th>
                                                    <th>Precio Parcial</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Las filas se agregarán aquí dinámicamente -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3"></th>
                                                    <th>SUBTOTAL</th>
                                                    <th>
                                                        <h4 id="subtotal">0.00</h4>
                                                        <input type="hidden" name="ssubtotal" id="ssubtotal">
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="3"></th>
                                                    <th>IGV</th>
                                                    <th>
                                                        <h4 id="totaligv">0.00</h4>
                                                        <input type="hidden" name="igv_total" id="igv_total">
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th colspan="3"></th>
                                                    <th>TOTAL IMPORTE</th>
                                                    <th>
                                                        <h4 id="totalimp">0.00</h4>
                                                        <input type="hidden" name="total_venta" id="total_venta">
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones de acción -->
                            <div class="row mt-3">
                                <div class="col-12 text-end">
                                    <a href="<?php echo BASE_URL; ?>Cotize" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Volver
                                    </a>
                                    
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Guardar Cotización
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal de Selección de Productos -->
                <div class="modal fade" id="create_income_product_modal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Seleccionar Producto</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <table class="table" id="products-table">
                                    <thead>
                                        <tr>
                                            <th>Acción</th>
                                            <th>Nombre</th>
                                            <th>Código</th>
                                            <th>Unidad</th>
                                            <th>Precio</th>
                                            <th>Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Modal de Selección de Productos -->
                 
            </section>
        </div>
    </div>
</div>

<style>
    #toast-container.toast-bottom-right {
        bottom: 20px !important;
        right: 20px !important;
        top: auto !important;
    }
</style>
<!-- END: Content-->

<!-- Al final del archivo, antes de cerrar el body -->
<?php if (isset($cotize) && !empty($cotize) && (isset($view_mode) && ($view_mode === 'edit' || $view_mode === 'view'))): ?>
<script>
    // Pasar datos de la cotización al JavaScript
    window.cotizeData = <?php echo json_encode($cotize); ?>;
</script>
<?php endif; ?>