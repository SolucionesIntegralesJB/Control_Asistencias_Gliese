<!-- BEGIN: Content -->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row"></div>
        <div class="content-body">
            <!-- Income Products Section -->
            <section id="income">
                <!-- Header Title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">
                                    Lista de <?php echo strtolower($selected_sub_menu); ?>
                                </h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                       
                                        <li class="breadcrumb-item active">
                                            <span><?php echo $selected_sub_menu; ?></span>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Header Title -->
                
                <!-- Data Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <table class="table" id="datatable-income-products">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Numero de Serie</th>
                                        <th>Comprobante</th>
                                        <th>Método Pago</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Comprobante</th>
                                        <th>Número de Serie</th>
                                        <th>Método Pago</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- /Data Table -->
            </section>
            <!-- /Income Products Section -->
            
            <!-- Notification Toast -->
            <div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>
            


            <!-- Details Modal -->
            <div class="modal fade" id="incomeProductModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-edit-user">
        <div class="modal-content">
            <div class="modal-header bg-transparent">
                <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-5 px-sm-5 pt-50">
                <div class="text-center mb-2">
                    <h1 class="mb-1">Detalle del Ingreso</h1>
                </div>

                <form method="GET" enctype="multipart/form-data" id="details_form" class="row" onsubmit="return false">
                    <!-- Información General del Ingreso -->
              
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Proveedor (ID Persona)</label>
                        <input type="text" id="income_person" class="form-control bg-light fw-bold" disabled />
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Usuario (ID Usuario)</label>
                        <input type="text" id="income_user" class="form-control bg-light fw-bold" disabled />
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Tipo de Comprobante (ID Tipo Comprobante)</label>
                        <input type="text" id="voucher_type_id" class="form-control bg-light fw-bold" disabled />
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Tipo de Pago (ID Tipo de Pago)</label>
                        <input type="text" id="payment_type_id" class="form-control bg-light fw-bold" disabled />
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Serie de Comprobante</label>
                        <input type="text" id="proof_series" class="form-control bg-light fw-bold" disabled />
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Serie de Comprobante</label>
                        <input type="text" id="voucher_series" class="form-control bg-light fw-bold" disabled />
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Fecha de Emisión</label>
                        <input type="text" id="date_issue" class="form-control bg-light fw-bold" disabled />
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Fecha de Expiración</label>
                        <input type="text" id="date_expiration" class="form-control bg-light fw-bold" disabled />
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">IGV</label>
                        <input type="text" id="igv" class="form-control bg-light fw-bold" disabled />
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Número de Cuotas</label>
                        <input type="text" id="number_installments" class="form-control bg-light fw-bold" disabled />
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Valor de la Cuota</label>
                        <input type="text" id="value_installment" class="form-control bg-light fw-bold" disabled />
                    </div>
                    
                    <!-- Tabla de Productos -->
                    <div class="mb-1 col-md-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>

                                    <th class="bg-primary text-white fw-bold text-center">Código</th>
                                    <th class="bg-primary text-white fw-bold text-center">Producto</th>
                                    <th class="bg-primary text-white fw-bold text-center">Cantidad</th>

                                    <th class="bg-primary text-white fw-bold text-center">Precio de Venta</th>
                                    <th class="bg-primary text-white fw-bold text-center">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="incomeProductDetails">
                                <!-- Aquí se agregarán las filas dinámicamente con los productos del ingreso -->
                            </tbody>
                        </table>
                    </div>
                    <div class="mb-1 col-md-6">
                        <label class="form-label">Compra Total</label>
                        <input type="text" id="full_purchase" class="form-control bg-light fw-bold" disabled />
                    </div>
                    
                    <!-- /Tabla de Productos -->

                </form>
            </div>
        </div>
    </div>
</div>

            <!-- Details Modal -->

        </div>
    </div>
</div>