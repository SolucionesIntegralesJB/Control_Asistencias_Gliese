<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row"></div>
        <div class="content-body">
            <!-- Online Orders Starts -->
            <section id="online-orders">
                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Lista de Pedidos en Línea</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#">Ventas</a></li>
                                        <li class="breadcrumb-item active">Pedidos en Línea</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Header title -->

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <table class="table" id="datatable-onlineorders">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Series</th>
                                        <th>Correlative</th>
                                        <th>Total</th>
                                        <th class="estado_pago">Estado de Pago</th>
                                        <th class="estado_entrega">Estado de Entrega</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /Table -->

                <!-- View Order Modal -->
                <div class="modal fade" id="view_order_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-transparent">
                                <h4 class="modal-title">Detalles del Pedido</h4>
                                <button type="reset" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pb-5 px-sm-5">
                                <div class="order-details-content"></div>
                                <div class="row mt-2">
                                    <div class="col-12 text-center">
                                        <button type="button" class="btn btn-primary me-1 btn-print-invoice">
                                            <i data-feather='printer'></i> Imprimir Boleta
                                        </button>
                                        <button type="button" class="btn btn-success me-1 btn-print-pdf">
                                            <i data-feather='file-text'></i> Exportar PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            Cerrar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /View Order Modal -->

                <!-- Update Order Modal -->
<div class="modal fade" id="update_order_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-transparent">
                <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-5 pb-5">
                <div class="text-center mb-2">
                    <h1 class="mb-1">Actualizar Estado de Pedido</h1>
                </div>
                <form method="POST" enctype="multipart/form-data" id="update_order_form" class="row" onsubmit="event.preventDefault(); update_online_order(this);">
                    <!-- Estado de Pago -->
                    <div class="col-12">
                        <label class="form-label">Estado de Pago</label>
                        <select name="estado_pago" class="form-select" required>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Pagado">Pagado</option>
                            <option value="Anulado">Anulado</option>
                        </select>
                    </div>
                    <!-- Estado de Entrega -->
                    <div class="col-12">
                        <label class="form-label">Estado de Entrega</label>
                        <select name="estado_entrega" class="form-select" required>
                            <option value="En Tienda">En Tienda</option>
                            <option value="Enviado">Enviado</option>
                            <option value="Entregado">Entregado</option>
                            <option value="Anulado">Anulado</option>
                        </select>
                    </div>
                    <!-- Campo oculto para identificar el pedido -->
                    <input type="hidden" name="id_pedido">
                    <!-- Botones -->
                    <div class="col-12 text-center">
                        <button id="btn_update_order" type="submit" class="btn btn-primary mt-2 me-1">Guardar</button>
                        <button type="reset" class="btn btn-outline-secondary mt-2 reset" data-bs-dismiss="modal" aria-label="Close">
                            <span>Cancelar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--/ Update Order Modal -->

            </section>
            <!-- Online Orders ends -->
        </div>
    </div>
</div>
<!-- END: Content-->
