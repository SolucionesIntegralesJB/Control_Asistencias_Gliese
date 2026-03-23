<!-- BEGIN: Content-->

<div class="app-content content">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <div class="content-overlay"></div>
  <div class="header-navbar-shadow"></div>
  <div class="content-wrapper container-xxl p-0">
    <div class="content-header row">
    </div>
    <div class="content-body">
      <!-- Campus Starts -->
      <section id="igvinvoicing_details">

        <!-- Header title -->
        <div class="content-header row">
          <div class="content-header-left col-md-9 col-12 mb-2">
            <div class="row breadcrumbs-top">
              <div class="col-12">
                <h2 class="content-header-title float-start mb-0">Ventas por Facturacion con IGV </h2>
              </div>
            </div>
          </div>
        </div>
        <!-- /Header table-->
        <!-- Container for adding products -->
        <div class="card">
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data" id="create_income_details_form" class="row"
              onsubmit="return false">
              <!-- First Row -->
              <div class="row mb-3">
                <div class="col-md-5">
                  <div>
                    <label class="form-label">Cliente(*)</label>
                    <select name="business_name_cli" class="form-select select2" required>
                      <!-- Opciones para el select -->
                    </select>
                  </div>
                </div>
                <div class="col-md-2 col-lg-auto col-xl-1.5">
                  <label class="form-label">Fecha Emisión (*)</label>
                  <input name="fecha_emision" type="date" class="form-control">
                </div>
                <div class="col-md-2 col-lg-auto col-xl-1.5">
                  <label class="form-label">Fecha Vencimiento (*)</label>
                  <input name="fecha_vencimiento" type="date" class="form-control">
                </div>
                <div class="col-md-2 col-lg-1.5" style="width: 12.5%;">
                  <label class="form-label">Moneda</label>
                  <span name="coins" class="form-control"></span>
                  <input name="coins" type="hidden" class="form-control" readonly>
                </div>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 col-xl-1">
                  <label class="form-label">Impuesto</label>
                  <input type="hidden" name="igv_asig" id="igv_asig">
                  <input name="igv" type="text" class="form-control" readonly>
                  <input type="hidden" name="igv" id="impuesto" value="1">
                </div>
              </div>

              <!-- Second Row -->
              <div class="row mb-2">
                <div class="col-md-2">
                  <label class="form-label">Nº de documento</label>
                  <input name="document_number_cli" type="text" class="form-control" readonly>
                </div>
                <div class="col-md-4">
                  <label class="form-label">Dirección</label>
                  <input name="address_cli" type="text" class="form-control" class="form-control" readonly>
                </div>
                <div class="col-md-2">
                  <div>
                    <label class="form-label">Forma Pago(*)</label>
                    <select name="fp_description" class="form-select select2" data-msg="" required>
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div>
                    <label class="form-label">Tipo Comprobante(*)</label>
                    <select name="vt_description" class="form-select select2" data-msg="" required>
                    </select>
                  </div>
                </div>
                <div class="col-md-2">
                  <div>
                    <label class="form-label">Medio Pago(*)</label>
                    <select name="pt_description" class="form-select select2" data-msg="" required>
                    </select>
                  </div>
                </div>
              </div>





              <!-- Table -->
              <div class="form-group col-lg-7 col-md-7 col-sm-7 col-xs-12 ">
                <a data-toggle="modal" href="#myModal">
                  <button id="btnAgregarArt" type="button" class="btn btn-primary" onclick="agregarDetalle()"> <span
                      class="fa fa-plus"></span>&nbsp;&nbsp;Agregar Servicio</button>
                </a>
              </div>
              <!-- Table -->
              <div class="row table-responsive mt-2">
                <div class="col-12">
                  <div class="card">
                    <table id="detalles" class=" table">
                      <thead>
                        <tr>
                          <th>Opciones</th>
                          <th>Codigo</th>
                          <th>Servicio</th>
                          <th>U. Medida</th>
                          <th>Precio</th>
                          <th>Cantidad</th>
                          <th>Sub total</th>
                          <th>IGV</th>
                          <th>Importe</th>
                        </tr>
                      </thead>
                      <tfoot>
                        <tr>
                          <th colspan="6"></th>
                          <th colspan="2">SUBTOTAL</th>
                          <th>
                            <h4 id="totalg">0.00</h4><input type="hidden" name="op_gravadas" id="op_gravadas">
                          </th>
                        </tr>

                        <tr>
                          <th style="height:2px;" colspan="6"></th>
                          <th colspan="2">IGV</th>
                          <th>
                            <h4 id="totaligv">0.00</h4><input type="hidden" name="igv_total" id="igv_total">
                          </th>
                        </tr>
                        <tr>
                          <th style="height:2px;" colspan="6"></th>
                          <th style="height:2px;" colspan="2">TOTAL</th>
                          <th style="height:2px;">
                            <h4 id="totalventa">0.00</h4><input type="hidden" name="total_venta" id="total_venta">
                          </th>
                        </tr>
                      </tfoot>
                      <tbody>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <button class="btn btn-primary" type="submit" id="btnGuardar"><i class="fa fa-save"></i>&nbsp;&nbsp;
                  Guardar</button>

                <button id="btnCancelar" class="btn btn-danger" onclick="window.location.href='Igvinvoicing/index.php'"
                  type="button"><i class="fa fa-arrow-circle-left"></i> &nbsp;&nbsp;Cancelar</button>

              </div>
            </form>
          </div>
          <!--Fin centro -->
        </div><!-- /.box -->
    </div><!-- /.col -->
  </div><!-- /.row -->
  </section><!-- /.content -->

</div><!-- /.content-wrapper -->
<!--Fin-Contenido -->