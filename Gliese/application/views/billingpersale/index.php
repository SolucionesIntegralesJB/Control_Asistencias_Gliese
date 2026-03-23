<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Proforma Starts -->
            <section id="billingpersale">

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
                <style>
                    .estado-tooltip-custom {
                        text-align: left !important;
                        max-width: 800px !important;
                        white-space: nowrap !important;
                    }
                </style>


                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">                            
                            <table class="table" id="datatable-billingpersale">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Usuario</th>
                                        <th>Documento</th>
                                        <th>Número</th>
                                        <th>Total Venta</th>
                                        <th> Estado </th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /Table -->

                <!-- Modal -->
                <div class="modal fade" id="send_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-lg" style="display: flex; align-items: center; min-height: 100vh;">
                        <div class="modal-content">
                            <div class="modal-header bg-transparent">
                                <button type="reset" class="btn-close reset" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pb-5 px-sm-5 pt-50">
                                <div class="text-center mb-4">
                                    <h1 class="mb-1">Envío de documento</h1>
                                </div>
                                <div class="mb-4">
                                    <label for="emailInput" class="form-label">Correo electrónico</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text" id="email-icon"></span>
                                        <input type="email" class="form-control" id="emailInput" placeholder="Ingrese el correo electrónico" required style="min-width: 300px;">
                                        <button class="btn btn-outline-primary" type="button" onclick="send_email('email')">
                                            <span> Enviar</span>
                                        </button>

                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="whatsappInput" class="form-label">WhatsApp</label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text" id="whatsapp-icon"></span>
                                        <input type="tel" class="form-control" id="whatsappInput" placeholder="Ingrese el número de WhatsApp" pattern="[9]\d{8}" maxlength="9" required>
                                        <button class="btn btn-outline-primary" type="button" onclick="send_whatsapp()" style="width: 85px;">
                                            <span>Abrir</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Modal -->
            </section>
        </div>
    </div>
</div>
<!-- END: Content-->