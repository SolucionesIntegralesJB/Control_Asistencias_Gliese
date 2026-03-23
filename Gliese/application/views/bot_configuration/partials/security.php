<div class="row">
    <div class="col-12">
        <h4 class="mb-1">Configuración de Seguridad</h4>
        <p class="text-muted">Configure las capas de protección del Bot de WhatsApp.</p>
    </div>
</div>

<!-- CAPA 1: Rate Limiting Global -->
<div class="accordion accordion-margin mb-2" id="accordionRateLimitingGlobal">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingRateLimitingGlobal">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseRateLimitingGlobal" aria-expanded="true" aria-controls="collapseRateLimitingGlobal">
                <i data-feather="zap" class="me-2"></i> Rate Limiting Global
                <span class="badge bg-light-primary ms-2" id="badge-rate-limiting-global">Cargando...</span>
            </button>
        </h2>
        <div id="collapseRateLimitingGlobal" class="accordion-collapse collapse show" aria-labelledby="headingRateLimitingGlobal">
            <div class="accordion-body">
                <form id="form-rate-limiting-global">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="enable_rate_limiting" name="enable_rate_limiting">
                                <label class="form-check-label fw-bold" for="enable_rate_limiting">Habilitar Rate Limiting Global</label>
                            </div>
                            <small class="text-muted">Limita la cantidad de solicitudes globales por usuario</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="rate_limit_window_ms">Ventana de Tiempo (ms)</label>
                            <input type="number" class="form-control" id="rate_limit_window_ms" name="rate_limit_window_ms" placeholder="60000">
                            <small class="text-muted">Periodo en milisegundos para contar solicitudes</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="rate_limit_max_requests">Máximo de Solicitudes</label>
                            <input type="number" class="form-control" id="rate_limit_max_requests" name="rate_limit_max_requests" placeholder="100">
                            <small class="text-muted">Máximo de solicitudes por ventana de tiempo</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="security_violation_block_minutes">Bloqueo por Violación (min)</label>
                            <input type="number" class="form-control" id="security_violation_block_minutes" name="security_violation_block_minutes" placeholder="1">
                            <small class="text-muted">Minutos de bloqueo por violación de seguridad</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="rate_limit_block_after_violations">Escalamiento a Bloqueo Permanente</label>
                            <input type="number" class="form-control" id="rate_limit_block_after_violations" name="rate_limit_block_after_violations" placeholder="5">
                            <small class="text-muted">Bloqueos antes de escalar a bloqueo permanente</small>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="btn-save-rate-limiting-global">
                                <i data-feather="save"></i> Guardar Capa 1
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CAPA 2: Action Rate Limiting -->
<div class="accordion accordion-margin mb-2" id="accordionActionRateLimiting">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingActionRateLimiting">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseActionRateLimiting" aria-expanded="false" aria-controls="collapseActionRateLimiting">
                <i data-feather="activity" class="me-2"></i> Action Rate Limiting
                <span class="badge bg-light-primary ms-2" id="badge-action-rate-limiting">Cargando...</span>
            </button>
        </h2>
        <div id="collapseActionRateLimiting" class="accordion-collapse collapse" aria-labelledby="headingActionRateLimiting">
            <div class="accordion-body">
                <form id="form-action-rate-limiting">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="enable_action_rate_limiting" name="enable_action_rate_limiting">
                                <label class="form-check-label fw-bold" for="enable_action_rate_limiting">Habilitar Rate Limiting por Acción</label>
                            </div>
                            <small class="text-muted">Limita acciones específicas del bot por usuario</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="enable_phone_blocking" name="enable_phone_blocking">
                                <label class="form-check-label fw-bold" for="enable_phone_blocking">Habilitar Bloqueo de Teléfonos</label>
                            </div>
                            <small class="text-muted">Permite bloquear números de teléfono específicos</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="max_messages_per_minute">Máx. Mensajes/Minuto</label>
                            <input type="number" class="form-control" id="max_messages_per_minute" name="max_messages_per_minute" placeholder="20">
                            <small class="text-muted">Límite de mensajes por usuario por minuto</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="max_verification_attempts">Máx. Intentos Verificación</label>
                            <input type="number" class="form-control" id="max_verification_attempts" name="max_verification_attempts" placeholder="5">
                            <small class="text-muted">Intentos antes de bloquear verificación</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="max_ai_queries_per_hour">Máx. Consultas IA/Hora</label>
                            <input type="number" class="form-control" id="max_ai_queries_per_hour" name="max_ai_queries_per_hour" placeholder="10">
                            <small class="text-muted">Límite de consultas a la IA por hora</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="spam_block_minutes">Bloqueo por Spam (min)</label>
                            <input type="number" class="form-control" id="spam_block_minutes" name="spam_block_minutes" placeholder="30">
                            <small class="text-muted">Minutos de bloqueo al detectar spam</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="otp_code_expiration_minutes">Expiración Código OTP (min)</label>
                            <input type="number" class="form-control" id="otp_code_expiration_minutes" name="otp_code_expiration_minutes" placeholder="5">
                            <small class="text-muted">Minutos de validez del código OTP</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="verification_block_minutes">Bloqueo Verificación (min)</label>
                            <input type="number" class="form-control" id="verification_block_minutes" name="verification_block_minutes" placeholder="1">
                            <small class="text-muted">Minutos de bloqueo por verificaciones fallidas</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="user_block_escalation_threshold">Umbral de Escalamiento</label>
                            <input type="number" class="form-control" id="user_block_escalation_threshold" name="user_block_escalation_threshold" placeholder="3">
                            <small class="text-muted">Bloqueos antes de escalar duración</small>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="btn-save-action-rate-limiting">
                                <i data-feather="save"></i> Guardar Capa 2
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CAPA 3: Alertas de Seguridad -->
<div class="accordion accordion-margin mb-2" id="accordionAlerts">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingAlerts">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAlerts" aria-expanded="false" aria-controls="collapseAlerts">
                <i data-feather="bell" class="me-2"></i> Alertas de Seguridad
                <span class="badge bg-light-primary ms-2" id="badge-alerts">Cargando...</span>
            </button>
        </h2>
        <div id="collapseAlerts" class="accordion-collapse collapse" aria-labelledby="headingAlerts">
            <div class="accordion-body">
                <form id="form-alerts">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="enable_security_alerts" name="enable_security_alerts">
                                <label class="form-check-label fw-bold" for="enable_security_alerts">Habilitar Alertas de Seguridad</label>
                            </div>
                            <small class="text-muted">Envía notificaciones de eventos de seguridad</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="alert_on_suspicious_activity" name="alert_on_suspicious_activity">
                                <label class="form-check-label" for="alert_on_suspicious_activity">Alertar por Actividad Sospechosa</label>
                            </div>
                            <small class="text-muted">Notifica comportamientos anómalos</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="admin_email_alerts">Email Administrador</label>
                            <input type="email" class="form-control" id="admin_email_alerts" name="admin_email_alerts" placeholder="admin@ejemplo.com">
                            <small class="text-muted">Email donde se envían las alertas</small>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="btn-save-alerts">
                                <i data-feather="save"></i> Guardar Capa 4
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CAPA 5: Configuración General -->
<div class="accordion accordion-margin mb-2" id="accordionGeneralSecurity">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingGeneralSecurity">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneralSecurity" aria-expanded="false" aria-controls="collapseGeneralSecurity">
                <i data-feather="settings" class="me-2"></i> Configuración General
                <span class="badge bg-light-primary ms-2" id="badge-general-security">Cargando...</span>
            </button>
        </h2>
        <div id="collapseGeneralSecurity" class="accordion-collapse collapse" aria-labelledby="headingGeneralSecurity">
            <div class="accordion-body">
                <div class="alert alert-info">
                    <i data-feather="info" class="me-1"></i>
                    Los switches se guardan automáticamente al cambiar.
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_signature_validation" name="enable_signature_validation">
                            <label class="form-check-label fw-bold" for="enable_signature_validation">Signature Validation</label>
                        </div>
                        <small class="text-muted">Validar firmas de webhooks de Meta</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_cors_protection" name="enable_cors_protection">
                            <label class="form-check-label fw-bold" for="enable_cors_protection">CORS Protection</label>
                        </div>
                        <small class="text-muted">Valida origen de solicitudes HTTP</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enable_helmet_protection" name="enable_helmet_protection">
                            <label class="form-check-label fw-bold" for="enable_helmet_protection">Helmet Protection</label>
                        </div>
                        <small class="text-muted">Headers de seguridad HTTP estándar</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="trust_proxy" name="trust_proxy">
                            <label class="form-check-label fw-bold" for="trust_proxy">Trust Proxy</label>
                        </div>
                        <small class="text-muted">Obtener IP real detrás de proxy/load balancer</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CAPA 6: Slowloris Protection -->
<div class="accordion accordion-margin mb-2" id="accordionSlowloris">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingSlowloris">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSlowloris" aria-expanded="false" aria-controls="collapseSlowloris">
                <i data-feather="shield-off" class="me-2"></i> Slowloris Protection
                <span class="badge bg-light-primary ms-2" id="badge-slowloris">Cargando...</span>
            </button>
        </h2>
        <div id="collapseSlowloris" class="accordion-collapse collapse" aria-labelledby="headingSlowloris">
            <div class="accordion-body">
                <div class="alert alert-info">
                    <i data-feather="info" class="me-1"></i>
                    <strong>Slowloris</strong> es un ataque que mantiene conexiones abiertas enviando datos muy lentamente.
                </div>
                <form id="form-slowloris">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="slowloris_protection_enabled" name="slowloris_protection_enabled">
                                <label class="form-check-label fw-bold" for="slowloris_protection_enabled">Habilitar Protección Slowloris</label>
                            </div>
                            <small class="text-muted">Detecta y bloquea ataques Slowloris</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="slowloris_headers_timeout">Timeout Headers (ms)</label>
                            <input type="number" class="form-control" id="slowloris_headers_timeout" name="slowloris_headers_timeout" placeholder="20000">
                            <small class="text-muted">Tiempo máximo para recibir headers</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="slowloris_request_timeout">Timeout Request (ms)</label>
                            <input type="number" class="form-control" id="slowloris_request_timeout" name="slowloris_request_timeout" placeholder="60000">
                            <small class="text-muted">Tiempo máximo para completar request</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="slowloris_keepalive_timeout">Keepalive Timeout (ms)</label>
                            <input type="number" class="form-control" id="slowloris_keepalive_timeout" name="slowloris_keepalive_timeout" placeholder="5000">
                            <small class="text-muted">Tiempo de conexión keep-alive</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="slowloris_max_connections_per_ip">Máx. Conexiones/IP</label>
                            <input type="number" class="form-control" id="slowloris_max_connections_per_ip" name="slowloris_max_connections_per_ip" placeholder="10">
                            <small class="text-muted">Conexiones simultáneas permitidas por IP</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="slowloris_slow_request_threshold">Umbral Request Lento (ms)</label>
                            <input type="number" class="form-control" id="slowloris_slow_request_threshold" name="slowloris_slow_request_threshold" placeholder="5000">
                            <small class="text-muted">Velocidad mínima de transferencia</small>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" id="btn-save-slowloris">
                                <i data-feather="save"></i> Guardar Capa 6
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Confirmación de contraseña para desactivar switches críticos -->
<div class="modal fade" id="modal-confirm-disable" tabindex="-1" aria-labelledby="modalConfirmDisableLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-transparent">
                <h5 class="modal-title" id="modalConfirmDisableLabel">
                    <i data-feather="shield-off" class="me-1 text-danger"></i> Confirmar Desactivación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-2">
                    <div class="alert-body">
                        <i data-feather="alert-triangle" class="me-1"></i>
                        <strong>Advertencia:</strong> Está a punto de desactivar una capa de seguridad crítica.
                    </div>
                </div>
                <p id="modal-disable-message" class="mb-2 text-muted">
                    <!-- Mensaje dinámico según el switch -->
                </p>
                <div class="mb-1">
                    <label class="form-label" for="confirm-password">Ingrese su contraseña para confirmar:</label>
                    <input type="password" class="form-control" id="confirm-password" placeholder="Contraseña">
                    <div class="invalid-feedback" id="password-error">Contraseña incorrecta</div>
                </div>
                <input type="hidden" id="confirm-switch-id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="btn-cancel-disable">
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btn-confirm-disable">
                    <i data-feather="unlock" class="me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Advertencia para switches no críticos -->
<div class="modal fade" id="modal-warning-disable" tabindex="-1" aria-labelledby="modalWarningDisableLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-transparent">
                <h5 class="modal-title" id="modalWarningDisableLabel">
                    <i data-feather="alert-circle" class="me-1 text-warning"></i> Advertencia
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modal-warning-message" class="mb-0 text-muted">
                    <!-- Mensaje dinámico según el switch -->
                </p>
                <input type="hidden" id="warning-switch-id" value="">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="btn-cancel-warning">
                    Cancelar
                </button>
                <button type="button" class="btn btn-warning" id="btn-confirm-warning">
                    <i data-feather="check" class="me-1"></i> Desactivar
                </button>
            </div>
        </div>
    </div>
</div>
