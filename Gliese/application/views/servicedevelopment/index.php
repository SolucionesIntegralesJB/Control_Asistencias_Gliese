<!-- BEGIN: Content-->
<div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Proforma Starts -->
                <section id="servicedevelopment"> 
           
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
                    <!-- /Header title-->

                    <!-- Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <table class="table" id="datatable-servicedevelopment">
                                    <thead>
                                        <tr>
                                            <th>Opciones</th>
                                            <th>Fecha Ingreso</th>
                                            <th>Fecha Termino</th>
                                            <th>Nombre Cliente</th>            
                                            <th>Nombre Proyecto</th>                                            
                                            <th>Estado Servicio</th>
                                            <th>Estado Pagado</th>
                                            <th>Saldo a Pagar</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /Table -->
                    
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->