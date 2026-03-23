<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row"></div>
        <div class="content-body">
            <!-- Cotizaciones Starts -->
            <section id="cotize">
                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Lista de <?php echo strtolower($selected_sub_menu); ?></h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#"><?php echo $selected_menu; ?></a></li>
                                        <li class="breadcrumb-item active"><?php echo $selected_sub_menu; ?></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-md-end col-md-3 col-12 d-md-block d-none">
                        <div class="mb-1 breadcrumb-right">
                            <a href="<?php echo BASE_URL; ?>Cotize_Details" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Nueva Cotización
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /Header title-->

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <table class="table" id="datatable-cotize">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo de Cotización</th>
                                            <th>Cliente</th>
                                            <th>Documento</th>
                                            <th>Referencia</th>
                                            <th>Número</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Table -->
            </section>
            <!-- Cotizaciones Ends -->
        </div>
    </div>
</div>
<!-- END: Content-->

<!-- Modal para Ver Cotización -->
<div class="modal fade" id="cotize-view-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ver Cotización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Información del Cliente</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Cliente:</th>
                                <td id="view-client-name"></td>
                            </tr>
                            <tr>
                                <th>Documento:</th>
                                <td id="view-client-document"></td>
                            </tr>
                            <tr>
                                <th>Dirección:</th>
                                <td id="view-client-address"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Información de la Cotización</h6>
                        <table class="table table-bordered">
                            <tr>
                                <th>Fecha de Emisión:</th>
                                <td id="view-emission-date"></td>
                            </tr>
                            <tr>
                                <th>Referencia:</th>
                                <td id="view-reference"></td>
                            </tr>
                            <tr>
                                <th>Tipo de Cotización:</th>
                                <td id="view-cotize-type"></td>
                            </tr>
                            <tr>
                                <th>Validez de Oferta:</th>
                                <td id="view-offer-validity"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <h6>Productos</h6>
                <div class="table-responsive">
                    <table class="table table-bordered" id="view-products-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los productos se cargarán dinámicamente -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3"></th>
                                <th>Subtotal:</th>
                                <th id="view-subtotal">S/. 0.00</th>
                            </tr>
                            <tr>
                                <th colspan="3"></th>
                                <th>IGV (18%):</th>
                                <th id="view-igv">S/. 0.00</th>
                            </tr>
                            <tr>
                                <th colspan="3"></th>
                                <th>Total:</th>
                                <th id="view-total">S/. 0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Cotización -->
<div class="modal fade" id="cotize-edit-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Cotización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-cotize-form">
                    <input type="hidden" id="edit-cotize-id" name="cotize_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Cliente(*)</label>
                            <select id="edit-client" name="business_name_cli" class="form-select select2" required onchange="cargarDatosClienteEdit(this.value)">
                                <option value="">Seleccione un cliente</option>
                                <?php if (isset($clients) && !empty($clients)): ?>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?php echo htmlspecialchars($client['id']); ?>">
                                            <?php echo htmlspecialchars($client['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nº de documento</label>
                            <input id="edit-client-document" type="text" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Dirección</label>
                            <input id="edit-client-address" type="text" class="form-control" readonly>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Referencia</label>
                            <input id="edit-reference" name="referencia" type="text" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo Cotización(*)</label>
                            <input id="edit-cotize-type" name="pt_description" type="text" class="form-control" value="Regular">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Validez de la Oferta(*)</label>
                            <input id="edit-offer-validity" name="validez_oferta" type="text" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary" onclick="openEditProductModal()">
                                <i class="fas fa-plus me-1"></i> Agregar Producto
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered" id="edit-products-table">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los productos se cargarán dinámicamente -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3"></th>
                                    <th>Subtotal:</th>
                                    <th id="edit-subtotal">S/. 0.00</th>
                                    <input type="hidden" id="edit-subtotal-input" name="subtotal">
                                </tr>
                                <tr>
                                    <th colspan="3"></th>
                                    <th>IGV (18%):</th>
                                    <th id="edit-igv">S/. 0.00</th>
                                    <input type="hidden" id="edit-igv-input" name="igv_total">
                                </tr>
                                <tr>
                                    <th colspan="3"></th>
                                    <th>Total:</th>
                                    <th id="edit-total">S/. 0.00</th>
                                    <input type="hidden" id="edit-total-input" name="total_venta">
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="updateCotizacion()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Producto en Edición -->
<div class="modal fade" id="edit-product-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="form-label">Descripción(*)</label>
                        <input type="text" id="new-product-description" class="form-control" required>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Cantidad(*)</label>
                        <input type="number" id="new-product-quantity" class="form-control" min="0.01" step="0.01" value="1.00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Precio Unitario(*)</label>
                        <input type="number" id="new-product-price" class="form-control" min="0.01" step="0.01" value="0.00" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="addNewProductToEdit()">Agregar</button>
            </div>
        </div>
    </div>
</div>

<!-- END: Content-->