<!-- Security Configuration Partial -->
<div class="row">
    <div class="col-12">
        <h4 class="mb-2"><i data-feather="shield"></i> Autenticación de Doble Factor (2FA)</h4>
        <p class="text-muted">Protege tu cuenta agregando una capa adicional de seguridad.</p>
    </div>
</div>

<hr class="my-2">

<!-- 2FA Status Card -->
<div class="row">
    <div class="col-md-6 col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i data-feather="lock"></i> Estado de 2FA</h5>
                <div id="2fa-status-container">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <span class="text-muted">Cargando estado...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-12">
        <div class="card bg-light-info">
            <div class="card-body">
                <h5 class="card-title text-info"><i data-feather="info"></i> ¿Qué es 2FA?</h5>
                <p class="card-text small mb-0">
                    La autenticación de doble factor agrega una capa extra de seguridad. Los practicantes recibirán un código de 6 dígitos por email cada vez que inicien sesión.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Configuración de 2FA por Email -->
<hr class="my-2">
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i data-feather="mail"></i> Autenticación por Email</h5>
                <div class="alert alert-info">
                    <i data-feather="info"></i>
                    <strong>Modo de Seguridad Activo:</strong> Cada practicante recibirá un código de 6 dígitos por email en cada inicio de sesión.
                    <ul class="mb-0 mt-2">
                        <li>Los códigos expiran en <span id="expiration-time-display">5</span> minutos</li>
                        <li>Máxima seguridad: No hay códigos reutilizables</li>
                        <li>Se envían directamente al email del usuario</li>
                    </ul>
                </div>

                <!-- Control de tiempo de expiración -->
                <div class="mt-3">
                    <label for="code-expiration-time" class="form-label fw-bold">
                        <i data-feather="clock"></i> Tiempo de expiración del código
                    </label>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <select class="form-select" id="code-expiration-time">
                                <option value="3">3 minutos</option>
                                <option value="5" selected>5 minutos (Recomendado)</option>
                                <option value="10">10 minutos</option>
                                <option value="15">15 minutos</option>
                            </select>
                        </div>
                        <div class="col-md-6 mt-2 mt-md-0">
                            <button type="button" class="btn btn-primary" id="btn-update-expiration">
                                <i data-feather="save"></i> Guardar cambio
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i data-feather="info"></i> Cambiar el tiempo afectará solo a los nuevos códigos generados.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Modals -->
<div class="modal fade" id="enable-2fa-modal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i data-feather="shield"></i> Activar 2FA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Al activar 2FA, los practicantes recibirán un código de 6 dígitos por email cada vez que inicien sesión.</p>
                <p class="text-info"><i data-feather="info"></i> Asegúrate de que los practicantes tengan acceso a sus correos electrónicos.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-enable-2fa">Activar 2FA</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="disable-2fa-modal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white"><i data-feather="alert-triangle"></i> Desactivar 2FA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas desactivar la autenticación de doble factor?</p>
                <p class="text-warning"><i data-feather="alert-circle"></i> Tu cuenta será menos segura sin 2FA.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-disable-2fa">Desactivar 2FA</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="show-qr-modal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white"><i data-feather="check-circle"></i> 2FA Activado</h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i data-feather="check-circle"></i> <strong>2FA activado correctamente para TODO el sistema</strong>
                </div>
                <p class="text-muted">Los practicantes ahora recibirán un código de 6 dígitos por email cada vez que inicien sesión.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Entendido</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- RATE LIMITING - Protección contra ataques de fuerza bruta -->
<!-- ========================================================= -->
<hr class="my-4">

<div class="row">
    <div class="col-12">
        <h4 class="mb-2"><i data-feather="alert-octagon"></i> Rate Limiting - Protección contra ataques</h4>
        <p class="text-muted">Bloquea automáticamente intentos de login masivos (ataques Hydra, fuerza bruta, etc.)</p>
    </div>
</div>

<hr class="my-2">

<!-- Rate Limiting Status Card -->
<div class="row">
    <div class="col-md-6 col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i data-feather="shield-off"></i> Estado del Rate Limiting</h5>
                <div id="rate-limit-status-container">
                    <div class="d-flex align-items-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <span class="text-muted">Cargando estado...</span>
                    </div>
                </div>
                <div id="rate-limit-actions" class="mt-3" style="display: none;">
                    <!-- Botones aparecerán aquí dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-12">
        <div class="card bg-light-warning">
            <div class="card-body">
                <h5 class="card-title text-warning"><i data-feather="alert-triangle"></i> ¿Qué es Rate Limiting?</h5>
                <p class="card-text small mb-0">
                    Protección automática que bloquea cuentas e IPs después de múltiples intentos fallidos de login. Detiene ataques de fuerza bruta como Hydra.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Configuración de Rate Limiting -->
<hr class="my-2">
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i data-feather="settings"></i> Configuración de Límites</h5>

                <div class="alert alert-warning">
                    <i data-feather="info"></i>
                    <strong>Protección activa:</strong> El sistema bloqueará automáticamente intentos sospechosos de login.
                </div>

                <!-- Configuración de límites por usuario -->
                <div class="row mt-3">
                    <div class="col-md-6 col-12">
                        <div class="border rounded p-3">
                            <h6 class="fw-bold"><i data-feather="user"></i> Límites por Cuenta</h6>
                            <hr>

                            <div class="mb-3">
                                <label for="max-attempts-per-user" class="form-label">
                                    Intentos fallidos antes de bloquear cuenta
                                </label>
                                <input type="number" class="form-control" id="max-attempts-per-user"
                                       min="1" value="3">
                                <small class="text-muted">💡 Recomendado: 3 intentos. Puedes usar cualquier valor que desees.</small>
                            </div>

                            <div class="mb-3">
                                <label for="user-lockout-duration" class="form-label">
                                    Duración del bloqueo (minutos)
                                </label>
                                <select class="form-select" id="user-lockout-duration">
                                    <option value="300">5 minutos</option>
                                    <option value="600">10 minutos</option>
                                    <option value="900" selected>15 minutos</option>
                                    <option value="1800">30 minutos</option>
                                    <option value="3600">60 minutos</option>
                                </select>
                                <small class="text-muted">Tiempo que la cuenta permanecerá bloqueada</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-12 mt-3 mt-md-0">
                        <div class="border rounded p-3">
                            <h6 class="fw-bold"><i data-feather="monitor"></i> Límites por IP</h6>
                            <hr>

                            <div class="mb-3">
                                <label for="max-attempts-per-ip" class="form-label">
                                    Intentos fallidos antes de bloquear IP
                                </label>
                                <input type="number" class="form-control" id="max-attempts-per-ip"
                                       min="1" value="3">
                                <small class="text-muted">💡 Recomendado: 3 intentos para bloqueo agresivo contra Hydra. Configura a tu gusto.</small>
                            </div>

                            <div class="mb-3">
                                <label for="ip-lockout-duration" class="form-label">
                                    Duración del bloqueo (minutos)
                                </label>
                                <select class="form-select" id="ip-lockout-duration">
                                    <option value="900">15 minutos</option>
                                    <option value="1800" selected>30 minutos</option>
                                    <option value="3600">60 minutos</option>
                                    <option value="7200">120 minutos</option>
                                </select>
                                <small class="text-muted">Tiempo que la IP permanecerá bloqueada</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Defensa Anti-Brute Force (Sleep Delay) -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="border rounded p-3" style="border-color: #7367f0 !important;">
                            <h6 class="fw-bold"><i data-feather="zap"></i> Defensa Anti-Brute Force (Sleep Delay)</h6>
                            <hr>
                            <p class="text-muted small mb-3">
                                <i data-feather="info" style="width: 14px; height: 14px;"></i>
                                Agrega un retraso intencional en cada intento fallido de login. Esto hace que herramientas como <strong>Hydra</strong> no puedan enviar muchas solicitudes rápidamente, causando que se desconecten antes de completar el ataque.
                            </p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sleep-delay" class="form-label">
                                            Retraso por intento fallido (segundos)
                                        </label>
                                        <select class="form-select" id="sleep-delay">
                                            <option value="0">0 segundos (Sin retraso - Solo para testing)</option>
                                            <option value="1">1 segundo</option>
                                            <option value="2" selected>2 segundos (Recomendado)</option>
                                            <option value="3">3 segundos</option>
                                            <option value="5">5 segundos (Muy agresivo)</option>
                                        </select>
                                        <small class="text-muted">💡 2 segundos es suficiente para bloquear Hydra. Usa 0 solo para pruebas de penetración.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info mb-0 h-100 d-flex align-items-center">
                                        <div>
                                            <strong><i data-feather="shield" style="width: 14px; height: 14px;"></i> ¿Cómo funciona?</strong><br>
                                            <small>Cada login fallido espera X segundos antes de responder. Un usuario normal no nota el retraso, pero herramientas automatizadas como Hydra se desconectan por timeout.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón para guardar configuración -->
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-primary" id="btn-save-rate-limit-config">
                            <i data-feather="save"></i> Guardar Configuración
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales para Rate Limiting -->
<div class="modal fade" id="enable-rate-limit-modal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i data-feather="shield"></i> Activar Rate Limiting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas activar el Rate Limiting?</p>
                <div class="alert alert-info">
                    <i data-feather="info"></i>
                    <strong>Protección automática:</strong> El sistema bloqueará IPs y cuentas después de múltiples intentos fallidos de login.
                    <ul class="mt-2 mb-0">
                        <li>Bloquea cuentas después de 5 intentos fallidos</li>
                        <li>Bloquea IPs después de 10 intentos fallidos</li>
                        <li>Protege contra ataques Hydra y fuerza bruta</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-enable-rate-limit">Activar Rate Limiting</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="disable-rate-limit-modal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white"><i data-feather="alert-triangle"></i> Desactivar Rate Limiting</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas desactivar la protección Rate Limiting?</p>
                <div class="alert alert-danger">
                    <i data-feather="alert-circle"></i>
                    <strong>Advertencia:</strong> Tu sistema quedará vulnerable a ataques de fuerza bruta.
                    <ul class="mt-2 mb-0">
                        <li>No se bloquearán IPs sospechosas</li>
                        <li>No se bloquearán cuentas por intentos fallidos</li>
                        <li>Atacantes podrán hacer intentos ilimitados</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-disable-rate-limit">Desactivar Rate Limiting</button>
            </div>
        </div>
    </div>
</div>
