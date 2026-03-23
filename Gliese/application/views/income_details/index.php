<!-- View: income_details - index.php -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">



<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row"></div>
        <div class="content-body">
            <!-- Income Section -->
            <section id="income_details">
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <h2 class="content-header-title float-start mb-0">Lista de ingresos</h2>
                    </div>
                </div>

                <!-- Income Form -->
                <div class="card">
                    <div class="card-body">
                     
                        <form method="POST" enctype="multipart/form-data" id="create_income_form" class="row" onsubmit="return false">

                        <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Persona</label>
                                    <select name="p_name" class="form-select select2" required></select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Usuario</label>
                                    <select name="u_name" class="form-select select2" required></select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fecha de Expiración</label>
                                    <input name="date_expiration" type="datetime-local" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Serie</label>
                                    <input name="proof_series" type="text" class="form-control" placeholder="Ingrese la Serie" oninput="this.value = this.value.toUpperCase()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Serie del Voucher</label>
                                    <input name="voucher_series" type="text" class="form-control" placeholder="Ingrese la serie del voucher" oninput="this.value = this.value.toUpperCase()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Valor de la Cuota</label>
                                    <input name="value_installment" type="text" class="form-control" placeholder="Ingrese el valor de la cuota" oninput="this.value = this.value.toUpperCase()">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Pago</label>
                                    <select name="pt_description" class="form-select select2" required></select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tipo de Comprobante</label>
                                    <select name="t_description" class="form-select select2" required></select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Número de Cuotas</label>
                                    <input name="number_installments" type="text" class="form-control" placeholder="Ingrese el número de cuotas" oninput="this.value = this.value.toUpperCase()">
                                </div>
                            </div>

                            <div class="col-12 text-end">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_income_product_modal">
                                    Agregar productos
                                </button>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#create_income_excel">  <i class="fa fa-file-excel-o"></i>
                                          Importar Prodcutos 
                                </button>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#create_full_excel">
  <i class="fa fa-file-excel-o"></i> Importar Datos Completos
</button>


                            </div>

                            <!-- Products Table -->
                            <div class="col-12">
                                <div class="card mt-4">
                                    <div class="table-responsive">
                                        <table class="table text-center" id="add_products">
                                            <thead>
                                                <tr>
                                                    <th>Acción</th>
                                                    <th>Code</th>
                                                    <th>Name</th>
                                                    <th>Price</th>
                                                    <th>Stock</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones finales -->
                            <div class="col-12 text-end mt-3">
                                <button id="btn_guardar_product" type="submit" class="btn btn-primary">Guardar</button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='Income/index.php'">Cancelar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- End Income Form -->

                <!-- Create Income Products Modal -->
                <div class="modal fade" id="create_income_product_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-transparent pb-3">
                                <button type="reset" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-sm-5 pb-5">
                                <div class="text-center mb-2">
                                    <h1 class="mb-1">Seleccionar Producto</h1>
                                </div>
                                <table class="table table-striped text-center" id="datatables-income-products">
                                    <thead>
                                        <tr>
                                            <th>Acción</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Descripción</th>
                                            <th>Price</th>
                                            <th>Unidad</th>
                                            <th>Etiqueta</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Create Income Products Modal -->













<!-- Modal para importar Excel -->
<div class="modal fade" id="create_income_excel" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <h5 class="modal-title">Importar productos desde Excel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="file" id="excel-file" class="form-control" accept=".xlsx, .xls">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btn-import-excel">
          Cargar productos
        </button>
      </div>
    </div>
  </div>
</div>


<!-- Modal para importar ingreso completo DE UN EXCEL -->
<div class="modal fade" id="create_full_excel" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <h5 class="modal-title">Importar productos desde Excel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="file" id="excel-file-full" class="form-control" accept=".xlsx, .xls">
      </div>
      <div class="modal-footer">
        <button type="button" id="btn_upload_income" class="btn btn-success mt-3">
          Guardar ingreso
        </button>
      </div>
    </div>
  </div>
</div>









        </div>
    </div>
</div>
<!-- END: Content -->
