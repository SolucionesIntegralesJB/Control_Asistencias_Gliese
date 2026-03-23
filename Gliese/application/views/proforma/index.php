<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-start mb-0">Lista de Proformas</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#"><?php echo $selected_menu; ?></a></li>
                                <li class="breadcrumb-item active"><span>Proformas</span></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <!-- Proforma Starts -->
            <section id="proforma">
                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Proformas</h4>
                            </div>
                            <div class="card-body">
                                <table class="table" id="datatable-proforma">
                                    <thead>
                                        <tr>
                                        <th>Fecha de Emisión</th>
                                        <th>Cliente</th>
                                        <th>Tipo de Documento</th>
                                        <th>Número de Correlativo</th>
                                        <th>Total de Venta</th>
                                        <th>Estado</th>
                                        <th>Opciones</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Table -->
            </section>
        </div>
    </div>
</div>
<!-- END: Content-->

<!-- Include your JavaScript file -->
<script src="<?php echo BASE_URL; ?>public/app-assets/js/proforma.js"></script>