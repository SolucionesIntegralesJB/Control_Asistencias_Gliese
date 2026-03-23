    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Campus Starts -->
                <section id="purchasesreport">

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

                    

                    <!-- /Table -->
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" id="create_income_details_form" class="row" onsubmit="return false">
                            <!-- First Row -->
                            <div class="row mb-3">

                                <div class="col-md-6">
                                    <label class="form-label">Fecha Emisión (*)</label>
                                    <input name="fecha_emision" type="date" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fecha Vencimiento (*)</label>
                                    <input name="fecha_vencimiento" type="date" class="form-control">
                                </div>
                            </div>
                            <!-- Second Row -->



                            <!-- Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <table class="table" id="datatable-purchasesreport">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Proveedor</th>
                                            <th>Comprobante</th>
                                            <th>Numero</th>
                                            <th>Total Compra</th> 
                                            <th>Impuesto</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /Table -->

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12" aligin="center">
                            </div>
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" aligin="center">
                                <div style="text-align:center; font-size:large;font-weight: 500;" class="alert alert-danger">
                                    <label>Compra Total </label><br>
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
    <!-- END: Content-->