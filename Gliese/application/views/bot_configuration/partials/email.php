<!-- Acordeón Principal: Configuración de Email (SMTP) -->
<div class="accordion accordion-margin" id="accordionEmailConfig">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingEmailConfig">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmailConfig" aria-expanded="true">
                <i data-feather="mail" class="me-2"></i> Configuración de Email (SMTP)
            </button>
        </h2>
        <div id="collapseEmailConfig" class="accordion-collapse collapse show" aria-labelledby="headingEmailConfig">
            <div class="accordion-body">
                <p class="text-muted mb-4">Configure el servidor SMTP, credenciales, plantillas y dominios permitidos.</p>

                <form id="form-email-config">

                    <!-- Sección: Credenciales de Email -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i data-feather="key" class="me-2"></i>Credenciales de Email</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="email_user">Usuario de Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email_user" name="email_user" required>
                                <small class="text-muted">Dirección de correo electrónico para envío de emails</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="email_password">Contraseña de Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="email_password" name="email_password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                        <i data-feather="eye" id="eye-icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">App Password de Gmail (16 caracteres sin espacios)</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Sección: Servidor SMTP -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i data-feather="server" class="me-2"></i>Servidor SMTP</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="smtp_server">Servidor SMTP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="smtp_server" name="smtp_server" required>
                                <small class="text-muted">Dirección del servidor SMTP</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="smtp_port">Puerto SMTP <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" min="1" max="65535" required>
                                <small class="text-muted">Puerto del servidor SMTP (587 para TLS, 465 para SSL)</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Sección: Información del Remitente -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i data-feather="user" class="me-2"></i>Información del Remitente</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="sender_name">Nombre del Remitente <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="sender_name" name="sender_name" required>
                                <small class="text-muted">Nombre que aparecerá como remitente (el email será el mismo de Usuario Email)</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="google_sheets_polling_interval_ms">Intervalo de Sincronización con Google Sheets (ms) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="google_sheets_polling_interval_ms" name="google_sheets_polling_interval_ms" placeholder="10800000" required>
                                <small class="text-muted">Intervalo de sincronización con Google Sheets en milisegundos (Por defecto: 10800000ms = 3 horas)</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Sección: Google Service Account -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i data-feather="cloud" class="me-2"></i>Google Service Account</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="google_service_account_email">Email de Service Account <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="google_service_account_email" name="google_service_account_email" required>
                                <small class="text-muted">Email de la cuenta de servicio de Google</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="google_private_key">Clave Privada <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="google_private_key" name="google_private_key" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-google-key">
                                        <i data-feather="eye" id="eye-icon-google"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Clave privada de Google Service Account</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Sección: Opciones de Envío -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i data-feather="settings" class="me-2"></i>Opciones de Envío</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="auto_email_sending">Envío Automático de Emails <span class="text-danger">*</span></label>
                                <select class="form-select" id="auto_email_sending" name="auto_email_sending" required>
                                    <option value="false">Desactivado</option>
                                    <option value="true">Activado</option>
                                </select>
                                <small class="text-muted">Activar para enviar emails automáticamente en eventos del sistema</small>
                            </div>
                        </div>
                    </div>

                    <!-- Botón Guardar -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save" class="me-1"></i> Guardar Configuración
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<!-- Acordeón: Plantillas de Email -->
<div class="accordion accordion-margin mt-3" id="accordionEmailTemplates">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingEmailTemplates">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmailTemplates" aria-expanded="false">
                <i data-feather="file-text" class="me-2"></i> Plantillas de Email
            </button>
        </h2>
        <div id="collapseEmailTemplates" class="accordion-collapse collapse" aria-labelledby="headingEmailTemplates">
            <div class="accordion-body">
                <p class="text-muted mb-3">Gestione las plantillas de correo electrónico para diferentes eventos del sistema.</p>

                <div class="table-responsive">
                    <table class="table table-hover" id="table-email-templates">
                        <thead>
                            <tr>
                                <th width="20%">Tipo</th>
                                <th width="30%">Asunto</th>
                                <th width="35%">Variables Disponibles</th>
                                <th width="10%">Estado</th>
                                <th width="5%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-templates">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acordeón: Dominios de Email Permitidos -->
<div class="accordion accordion-margin mt-3" id="accordionEmailDomains">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingEmailDomains">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEmailDomains" aria-expanded="false">
                <i data-feather="shield" class="me-2"></i> Dominios de Email Permitidos
            </button>
        </h2>
        <div id="collapseEmailDomains" class="accordion-collapse collapse" aria-labelledby="headingEmailDomains">
            <div class="accordion-body">
                <p class="text-muted mb-3">Gestione los dominios de correo electrónico permitidos para el registro.</p>

                <button type="button" class="btn btn-sm btn-success mb-3" id="btn-add-domain">
                    <i data-feather="plus"></i> Agregar Dominio
                </button>

                <div class="table-responsive">
                    <table class="table table-hover" id="table-email-domains">
                        <thead>
                            <tr>
                                <th width="25%">Dominio</th>
                                <th width="30%">Descripción</th>
                                <th width="15%">Permitido</th>
                                <th width="15%">Corporativo</th>
                                <th width="10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-domains">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Template -->
<div class="modal fade" id="modal-edit-template" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Plantilla de Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-template">
                    <input type="hidden" id="template_id" name="template_id">

                    <div class="mb-3">
                        <label class="form-label" for="template_type">Tipo</label>
                        <input type="text" class="form-control" id="template_type" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="template_subject">Asunto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="template_subject" name="template_subject" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="template_content">Contenido <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="template_content" name="template_content" rows="10" required></textarea>
                        <small class="text-muted">Use las variables entre llaves {variable} para insertar datos dinámicos.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="template_active">Estado <span class="text-danger">*</span></label>
                        <select class="form-select" id="template_active" name="template_active" required>
                            <option value="0">Inactiva</option>
                            <option value="1">Activa</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-template">
                    <i data-feather="save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar/Editar Dominio -->
<div class="modal fade" id="modal-edit-domain" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-domain-title">Agregar Dominio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-domain">
                    <input type="hidden" id="domain_id" name="domain_id">

                    <div class="mb-3">
                        <label class="form-label" for="domain_name">Dominio <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="domain_name" name="domain_name" placeholder="gmail.com" required>
                        <small class="text-muted">Solo el dominio, sin @ (ejemplo: gmail.com)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="domain_description">Descripción</label>
                        <input type="text" class="form-control" id="domain_description" name="domain_description" placeholder="Gmail">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="domain_is_allowed">¿Dominio Permitido? <span class="text-danger">*</span></label>
                        <select class="form-select" id="domain_is_allowed" name="domain_is_allowed" required>
                            <option value="0">No</option>
                            <option value="1" selected>Sí</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="domain_is_corporate">¿Dominio Corporativo? <span class="text-danger">*</span></label>
                        <select class="form-select" id="domain_is_corporate" name="domain_is_corporate" required>
                            <option value="0" selected>No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-domain">
                    <i data-feather="save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
