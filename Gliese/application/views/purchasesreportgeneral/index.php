<!-- BEGIN: Content-->
<div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Campus Starts -->
                <section id="purchasesreportgeneral">

                    <!-- Header title -->
                    <div class="content-header row">
                        <div class="content-header-left col-md-9 col-12 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Lista de <?php echo strtolower($selected_sub_menu); ?></h2>
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
                    <!-- /Header table-->
                    <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="create_purchases_reportgeneral_form" class="row" onsubmit="return false">
                            <!-- First Row -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Fecha Inicio (*)</label>
                                    <input name="fecha_inicio" type="date" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Fecha Fin (*)</label>
                                    <input name="fecha_fin" type="date" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <div>
                                        <label class="form-label">Articulo(*)</label>
                                        <input name="articulo" type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div>
                                        <label class="form-label">Serie(*)</label>
                                        <input name="serie" type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <!-- Second Row -->
                            <!-- Table -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <table class="table" id="datatable-purchases_reportgeneral">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th>Usuario</th>
                                                    <th>Sucursal</th>
                                                    <th>Nº Comprobante</th>
                                                    <th>Artículo</th>
                                                    <th>Cantidad</th> 
                                                    <th>Total venta</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- /Table -->
                    </section>
                <!-- Permissions ends -->
            </div>
        </div>
    </div>
    <!-- END: Content-->