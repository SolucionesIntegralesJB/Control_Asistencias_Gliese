<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Bot Configuration Starts -->
            <section id="bot-configuration">

                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Configuracion del Bot</h2>
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

                <!-- Configuration Tabs -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Nav tabs -->
                                <ul class="nav nav-tabs" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="company-tab" data-bs-toggle="tab" href="#company-info" aria-controls="company-info" role="tab" aria-selected="true">
                                            <i data-feather="briefcase"></i> Empresa
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="general-tab" data-bs-toggle="tab" href="#general-config" aria-controls="general-config" role="tab" aria-selected="false">
                                            <i data-feather="settings"></i> General
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="security-tab" data-bs-toggle="tab" href="#security" aria-controls="security" role="tab" aria-selected="false">
                                            <i data-feather="shield"></i> Seguridad
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="email-tab" data-bs-toggle="tab" href="#email" aria-controls="email" role="tab" aria-selected="false">
                                            <i data-feather="mail"></i> Email
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="whatsapp-tab" data-bs-toggle="tab" href="#whatsapp" aria-controls="whatsapp" role="tab" aria-selected="false">
                                            <i data-feather="message-circle"></i> WhatsApp
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="ai-tab" data-bs-toggle="tab" href="#ai-config" aria-controls="ai-config" role="tab" aria-selected="false">
                                            <i data-feather="cpu"></i> IA
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="menus-tab" data-bs-toggle="tab" href="#menus" aria-controls="menus" role="tab" aria-selected="false">
                                            <i data-feather="menu"></i> Menús
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="messages-tab" data-bs-toggle="tab" href="#messages" aria-controls="messages" role="tab" aria-selected="false">
                                            <i data-feather="message-square"></i> Mensajes
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="faq-tab" data-bs-toggle="tab" href="#faq" aria-controls="faq" role="tab" aria-selected="false">
                                            <i data-feather="help-circle"></i> FAQ
                                        </a>
                                    </li>
                                </ul>

                                <!-- Tab panes -->
                                <div class="tab-content pt-2">
                                    <!-- Company Info Tab -->
                                    <div class="tab-pane active" id="company-info" aria-labelledby="company-tab" role="tabpanel">
                                        <div id="company-info-content">
                                            <div class="d-flex justify-content-center my-3">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Cargando...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- General Config Tab -->
                                    <div class="tab-pane" id="general-config" aria-labelledby="general-tab" role="tabpanel">
                                        <div id="general-config-content">
                                            <p class="text-muted">Selecciona esta pestana para cargar la configuracion general.</p>
                                        </div>
                                    </div>

                                    <!-- Security Tab -->
                                    <div class="tab-pane" id="security" aria-labelledby="security-tab" role="tabpanel">
                                        <div id="security-content">
                                            <p class="text-muted">Selecciona esta pestana para cargar la configuracion de seguridad.</p>
                                        </div>
                                    </div>

                                    <!-- Email Tab -->
                                    <div class="tab-pane" id="email" aria-labelledby="email-tab" role="tabpanel">
                                        <div id="email-content">
                                            <p class="text-muted">Selecciona esta pestana para cargar la configuracion de email.</p>
                                        </div>
                                    </div>

                                    <!-- WhatsApp Tab -->
                                    <div class="tab-pane" id="whatsapp" aria-labelledby="whatsapp-tab" role="tabpanel">
                                        <div id="whatsapp-content">
                                            <p class="text-muted">Selecciona esta pestana para cargar la configuracion de WhatsApp.</p>
                                        </div>
                                    </div>

                                    <!-- AI Config Tab -->
                                    <div class="tab-pane" id="ai-config" aria-labelledby="ai-tab" role="tabpanel">
                                        <div id="ai-config-content">
                                            <p class="text-muted">Selecciona esta pestana para cargar la configuracion de IA.</p>
                                        </div>
                                    </div>

                                    <!-- Menus Tab -->
                                    <div class="tab-pane" id="menus" aria-labelledby="menus-tab" role="tabpanel">
                                        <div id="menus-content">
                                            <p class="text-muted">Selecciona esta pestana para cargar los menus del bot.</p>
                                        </div>
                                    </div>

                                    <!-- Messages Tab -->
                                    <div class="tab-pane" id="messages" aria-labelledby="messages-tab" role="tabpanel">
                                        <div id="messages-content">
                                            <p class="text-muted">Selecciona esta pestana para cargar los mensajes del sistema.</p>
                                        </div>
                                    </div>

                                    <!-- FAQ Tab -->
                                    <div class="tab-pane" id="faq" aria-labelledby="faq-tab" role="tabpanel">
                                        <div id="faq-content">
                                            <p class="text-muted">Selecciona esta pestana para cargar las FAQs.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Configuration Tabs -->

            </section>
        </div>
    </div>
</div>
<!-- END: Content-->
