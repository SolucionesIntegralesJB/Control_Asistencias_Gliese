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
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <table class="table" id="datatable-income-products">
                            <thead>
                                    <tr>     
                                        <th>N° Pedido</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Fecha Límite</th>
                                        <th>Cliente</th> 
                                        <th>Acciones</th>
                                    </tr>    
                                </thead>
                                <tfoot>
                                    <tr>     
                                        <th>N° Pedido</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Fecha Límite</th>
                                        <th>Cliente</th>
                                        <th>Acciones</th>
                                    </tr>   
                                </tfoot>

                            </table>
                        </div>
                    </div>
                </div>      <!-- Data Table -->
          

                <!-- /Data Table -->
            </section>
            <!-- /Income Products Section -->
            
            <!-- Notification Toast -->
            <div id="toastContainer" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>
            


            <!-- Details Modal -->
                <!-- Details Modal -->
<div class="modal fade" id="incomeProductModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-edit-user">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <h5 class="modal-title">Detalle del Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body pb-5 px-sm-5 pt-50">
        <form id="details_form" class="row" onsubmit="return false">
          <!-- Información general -->
<div class="mb-1 col-md-4">
  <label class="form-label">Cliente</label>
  <input type="text" id="income_person" class="form-control bg-light fw-bold" disabled />
</div>
<div class="mb-1 col-md-4">
  <label class="form-label">Usuario</label>
  <input type="text" id="income_user" class="form-control bg-light fw-bold" disabled />
</div>
<div class="mb-1 col-md-4">
  <label class="form-label">Tipo Comprobante</label>
  <input type="text" id="voucher_type" class="form-control bg-light fw-bold" disabled />
</div>

<div class="mb-1 col-md-4">
  <label class="form-label">Serie - Comprobante</label>
  <input type="text" id="proof_series" class="form-control bg-light fw-bold" disabled />
</div>
<div class="mb-1 col-md-4">
  <label class="form-label">Estado de Entrega</label>
  <input type="text" id="estado_entrega" class="form-control bg-light fw-bold" disabled />
</div>
<div class="mb-1 col-md-4">
  <label class="form-label">Estado de Pago</label>
  <input type="text" id="estado_pago" class="form-control bg-light fw-bold" disabled />
</div>

<div class="mb-1 col-md-4">
  <label class="form-label">Forma de Pago</label>
  <input type="text" id="payment_shape" class="form-control bg-light fw-bold" disabled />
</div>
<div class="mb-1 col-md-4">
  <label class="form-label">Moneda</label>
  <input type="text" id="coin_name" class="form-control bg-light fw-bold" disabled />
</div>
<div class="mb-1 col-md-4">
  <label class="form-label">Transfer Reference</label>
  <input type="text" id="transfer_reference" class="form-control bg-light fw-bold" disabled />
</div>

<div class="mb-1 col-md-4">
  <label class="form-label">Transfer Date</label>
  <input type="text" id="transfer_date" class="form-control bg-light fw-bold" disabled />
</div>

<!-- Para Notas, si quieres que ocupe toda la fila -->
<div class="mb-1 col-12">
  <label class="form-label">Notas</label>
  <textarea id="notes" class="form-control bg-light fw-bold" rows="4" disabled></textarea>
</div>
<div class="mb-1 col-md-4">
  <label class="form-label">Comprobante</label>
  <div id="transfer_proof" class="fw-bold"></div>
</div>
          <!-- Tabla de productos -->
          <div class="mb-1 col-md-12">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th class="bg-primary text-white text-center">Código</th>
                  <th class="bg-primary text-white text-center">Producto</th>
                  <th class="bg-primary text-white text-center">Cantidad</th>
                  <th class="bg-primary text-white text-center">Precio Unitario</th>
                  <th class="bg-primary text-white text-center">Subtotal</th>
                </tr>
              </thead>
              <tbody id="incomeProductDetails">
                <tr><td colspan="5" class="text-center">Seleccione un pedido...</td></tr>
              </tbody>
              <tfoot>
                <tr>
                  <th colspan="4" class="text-end">Total:</th>
                  <th class="text-center" id="total_order">S/ 0.00</th>
                </tr>
              </tfoot>
            </table>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

            <!-- Details Modal -->

        </div>
    </div>
</div>