<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Proforma Starts -->
            <section id="salesgeneralreport">

                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Consulta de <?php echo strtolower($selected_sub_menu); ?></h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#"><?php echo $selected_menu; ?></a>
                                        </li>
                                        <li class="breadcrumb-item active"><span><?php echo $selected_sub_menu; ?></span>
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Header title-->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="create_salesgeneralreport_details_form" class="row" onsubmit="return false">
                            <!-- First Row -->
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <label class="form-label">Fecha Inicio (*)</label>
                                    <input name="fecha_inicio" type="date" class="form-control">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Fecha Fin(*)</label>
                                    <input name="fecha_fin" type="date" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Producto</label>
                                    <input name="producto" type="text" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Serie</label>
                                    <input name="series" type="text" class="form-control" placeholder="Por ejemplo #ABCD">
                                </div>
                                <div class="col-md-2">
                                <br>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_salesgeneralreport_modal">Mostrar</button>
                                </div>
                            </div>
                            
                            <!-- Table -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <table class="table" id="datatable-salesgeneralreport">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Cliente</th>
                                                    <th>Usuario</th>
                                                    <th>Comprobante</th>
                                                    <th>Producto</th>
                                                    <th>Cantidad</th>
                                                    <th>Serie</th>
                                                    <th>Precio_venta</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12" aligin="center">
                                </div>
                                    <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" aligin="center">
                                        <div style="text-align:center; font-size:large;font-weight: 500;" class="alert alert-danger">
                                            <label>Venta Total del cliente </label><br>
                                            <p>
                                                <span id="sumventa">0.00</span><span> Soles</span>
                                            </p>
                                        </div>
                                    <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12" aligin="center">
                                </div>
                            </div>
                        </form>
                    </div>
                    </div>
                </section>
                <!-- Permissions ends -->
            </div>
        </div>
    </div>
    <!-- END: Content-->