<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Bot Dashboard Starts -->
            <section id="bot-dashboard">

                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Dashboard de Seguridad</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#"><?php echo $selected_menu; ?></a></li>
                                        <li class="breadcrumb-item active"><span><?php echo $selected_sub_menu; ?></span></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Header title-->

                <!-- Dashboard Tabs -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="rate-limits-tab" data-bs-toggle="tab" href="#rate-limits" aria-controls="rate-limits" role="tab" aria-selected="true">
                                            <i data-feather="activity"></i> Rate Limits
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="blocked-phones-tab" data-bs-toggle="tab" href="#blocked-phones" aria-controls="blocked-phones" role="tab" aria-selected="false">
                                            <i data-feather="phone-off"></i> Teléfonos Bloqueados
                                        </a>
                                    </li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content pt-2">
                                    <!-- Rate Limits Tab -->
                                    <div class="tab-pane active" id="rate-limits" aria-labelledby="rate-limits-tab" role="tabpanel">
                                        <div id="rate-limits-content">
                                            <div class="d-flex justify-content-center my-3">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Blocked Phones Tab -->
                                    <div class="tab-pane" id="blocked-phones" aria-labelledby="blocked-phones-tab" role="tabpanel">
                                        <div id="blocked-phones-content">
                                            <p class="text-muted">Selecciona esta pestaña para cargar los teléfonos bloqueados.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Dashboard Tabs -->

            </section>
        </div>
    </div>
</div>
<!-- END: Content-->