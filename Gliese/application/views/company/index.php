<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <!-- Campus Starts -->
            <section id="company">

                <!-- Header title -->
                <div class="content-header row">
                    <div class="content-header-left col-md-9 col-12 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Configuración</h2>
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
                    <div class="card-header border-bottom p-2 d-flex justify-content-end">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_config_modal">
                            Configuración de SUNAT
                        </button>&nbsp;&nbsp;&nbsp;
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_config_modal2">
                            Configuración de Sistema
                        </button>
                    </div>

                    <div class="card-body mt-3">
                        <form method="POST" enctype="multipart/form-data" id="create_company_form" onsubmit="return false">
                            <!-- Información de la empresa -->
                            <h4 class="mb-2">Información de la empresa</h4>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="razon_social">Razón Social:</label>
                                        <input type="text" class="form-control" name="razon_social" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="nombre_comercial">Nombre Comercial:</label>
                                        <input type="text" class="form-control" name="nombre_comercial" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="ruc">RUC:</label>
                                        <input type="text" class="form-control" name="ruc" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="telefono">Teléfono:</label>
                                        <input type="text" class="form-control" name="telefono" required>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="id_company">

                            <!-- Dirección principal -->
                            <h4 class="mb-2">Dirección principal</h4>
                            <div class="row mb-2">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="direccion">Dirección:</label>
                                        <input type="text" class="form-control" name="direccion" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="distrito">Distrito:</label>
                                        <input type="text" class="form-control" name="distrito" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="provincia">Provincia:</label>
                                        <input type="text" class="form-control" name="provincia" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="departamento">Departamento:</label>
                                        <input type="text" class="form-control" name="departamento" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="ubigeo">Ubigeo:</label>
                                        <input type="text" class="form-control" name="ubigeo" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="codigo_postal">Código postal:</label>
                                        <input type="text" class="form-control" name="codigo_postal" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="pais">País:</label>
                                        <input type="text" class="form-control" name="pais" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de contacto -->
                            <h4 class="mb-2">Información de contacto</h4>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="email">Correo electrónico:</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="web">Sitio web:</label>
                                        <input type="text" class="form-control" name="web" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Información adicional -->
                            <h4 class="mb-2">Información adicional</h4>
                            <div class="row mb-2">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="fecha_autorizacion">Fecha de Autorización:</label>
                                        <input type="date" class="form-control" name="fecha_autorizacion" required>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="direccion_secundaria">Dirección secundaria:</label>
                                        <input type="text" class="form-control" name="direccion_secundaria" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="publicidad">Publicidad:</label>
                                        <input type="text" class="form-control" name="publicidad" required>
                                    </div>
                                </div>
                            </div>
                            <!-- Logo del sitio -->
                            <h4 class="mb-2">Logo del sitio</h4>
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="logo_sitio">Subir logo:</label>
                                        <input type="file" class="form-control" id="logo_sitio" name="logo_sitio" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-5 d-flex justify-content-center">
                                    <div class="form-group text-center">
                                        <img id="logo_preview" name="logo_preview" src="" alt="Logo de la empresa" class="img-fluid">
                                    </div>
                                </div>
                            </div>

                            <!-- Botón de envío -->
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" id="btn_create_config">Guardar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal para cargar configuración de Sistema-->
                <div class="modal fade" id="create_config_modal" tabindex="-1" aria-labelledby="createConfigModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createConfigModalLabel">
                                    <img src="<?= BASE_URL ?>/public/app-assets/images/logo/sunat.svg" alt="Icono" style="width: 30px; height: 30px; margin-right: 10px;">
                                    Configuración de SUNAT
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="sunat_config_form">
                                    <div class="mb-2">
                                        <label for="modo_emision" class="form-label">Modo de Emisión</label>
                                        <select class="form-select" id="modo_emision" name="modo_emision" required>
                                            <option value="FE_BETA">BETA</option>
                                            <option value="FE_PRODUCCION">PRODUCCIÓN</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="id_config">
                                    <div class="mb-2">
                                        <label for="usuario_sunat" class="form-label">Usuario SUNAT</label>
                                        <input type="text" class="form-control" id="usuario_sunat" name="usuario_sunat" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="contrasena_sunat" class="form-label">Contraseña SUNAT</label>
                                        <input type="password" class="form-control" id="contrasena_sunat" name="contrasena_sunat" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="contrasena_certificado" class="form-label">Contraseña del Certificado</label>
                                        <input type="password" class="form-control" id="contrasena_certificado" name="contrasena_certificado" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="certificado" class="form-label">Certificado (.p12)</label>
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="certificado" name="certificado" accept=".p12">
                                        </div>
                                        <small class="form-text text-dark fw-bold mt-1" id="certificado_info"></small>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="button" class="btn btn-primary" id="save_config_sunat">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal para cargar configuración de Sistema-->
                <div class="modal fade" id="create_config_modal2" tabindex="-1" aria-labelledby="createConfigModalLabel2" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="createConfigModalLabel2">
                                    <img src="<?= BASE_URL ?>/public/app-assets/images/svg/config.svg" alt="Icono" style="width: 30px; height: 30px; margin-right: 10px;">
                                    Configuración de Sistema
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="system_config_form">
                                    <div class="mb-2">                                        
                                        <label for="token" class="form-label">Token Consulta RUC/DNI</label>
                                        <input type="text" class="form-control mb-2" id="token" name="token" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="host" class="form-label">Host</label>
                                        <input type="text" class="form-control" id="host" name="host" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="email" class="form-label">Usuario</label>
                                        <input type="text" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="mb-2">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                <button type="button" class="btn btn-primary" id="save_config">Guardar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Permissions ends -->
        </div>
    </div>
</div>
<!-- END: Content-->