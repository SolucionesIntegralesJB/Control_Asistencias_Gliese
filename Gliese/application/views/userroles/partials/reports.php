<!-- Reports Partial - Rate Limiting Monitoring and Management -->
<div class="row">
    <div class="col-12">
        <h4 class="mb-2"><i data-feather="bar-chart-2"></i> Reportes de Seguridad - Rate Limiting</h4>
        <p class="text-muted">Monitoreo y gestión de bloqueos por intentos fallidos de login</p>
    </div>
</div>

<hr class="my-2">

<!-- Estadísticas Resumidas -->
<div class="row">
    <div class="col-md-3 col-sm-6 col-12">
        <div class="card text-center">
            <div class="card-body">
                <div class="avatar bg-light-danger p-50 mb-1">
                    <div class="avatar-content">
                        <i data-feather="alert-octagon" class="font-medium-5"></i>
                    </div>
                </div>
                <h2 class="fw-bolder" id="report-stat-blocked-ips">0</h2>
                <p class="card-text">IPs Bloqueadas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="card text-center">
            <div class="card-body">
                <div class="avatar bg-light-warning p-50 mb-1">
                    <div class="avatar-content">
                        <i data-feather="target" class="font-medium-5"></i>
                    </div>
                </div>
                <h2 class="fw-bolder" id="report-stat-attacked-accounts">0</h2>
                <p class="card-text">Cuentas Atacadas (24h)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="card text-center">
            <div class="card-body">
                <div class="avatar bg-light-primary p-50 mb-1">
                    <div class="avatar-content">
                        <i data-feather="activity" class="font-medium-5"></i>
                    </div>
                </div>
                <h2 class="fw-bolder" id="report-stat-failed-attempts">0</h2>
                <p class="card-text">Intentos Fallidos (24h)</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="card text-center">
            <div class="card-body">
                <div class="avatar bg-light-success p-50 mb-1">
                    <div class="avatar-content">
                        <i data-feather="check-circle" class="font-medium-5"></i>
                    </div>
                </div>
                <h2 class="fw-bolder" id="report-stat-success-logins">0</h2>
                <p class="card-text">Logins Exitosos (24h)</p>
            </div>
        </div>
    </div>
</div>

<hr class="my-2">

<!-- IPs Bloqueadas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i data-feather="alert-triangle"></i> IPs Bloqueadas</h4>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-refresh-blocked-ips">
                    <i data-feather="refresh-cw"></i> Actualizar
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="table-blocked-ips">
                        <thead>
                            <tr>
                                <th>IP</th>
                                <th>Bloqueado Desde</th>
                                <th>Bloqueado Hasta</th>
                                <th>Intentos</th>
                                <th>Razón</th>
                                <th>Tiempo Restante</th>
                                <th>Tipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <span class="ms-2">Cargando IPs bloqueadas...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cuentas Atacadas -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i data-feather="target"></i> Cuentas Atacadas (Últimas 24h)</h4>
                <button type="button" class="btn btn-sm btn-outline-primary" id="btn-refresh-attacked-accounts">
                    <i data-feather="refresh-cw"></i> Actualizar
                </button>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <i data-feather="info" style="width: 14px; height: 14px;"></i>
                    Muestra las cuentas que recibieron intentos de login fallidos. Solo se bloquea la IP del atacante, no la cuenta del practicante.
                </p>
                <div class="table-responsive">
                    <table class="table table-hover" id="table-attacked-accounts">
                        <thead>
                            <tr>
                                <th>Cuenta Atacada</th>
                                <th>IPs del Atacante</th>
                                <th>Total Intentos</th>
                                <th>Primer Intento</th>
                                <th>Último Intento</th>
                                <th>IP Bloqueada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <span class="ms-2">Cargando cuentas atacadas...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Historial de Intentos de Login -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i data-feather="clock"></i> Historial de Logins Exitosos (Últimas 24 horas)</h4>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="filter-login-attempts" style="width: auto;">
                        <option value="success" selected>Solo Exitosos</option>
                        <option value="failed">Solo Fallidos</option>
                        <option value="all">Todos</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-refresh-login-attempts">
                        <i data-feather="refresh-cw"></i> Actualizar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover" id="table-login-attempts">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Usuario</th>
                                <th>IP</th>
                                <th>Resultado</th>
                                <th>User Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <span class="ms-2">Cargando historial...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Desbloqueo de IP -->
<div class="modal fade" id="unblock-ip-modal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i data-feather="unlock"></i> Desbloquear IP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas desbloquear la IP <strong id="unblock-ip-address"></strong>?</p>
                <div class="alert alert-warning">
                    <i data-feather="alert-triangle"></i> Esta acción permitirá que esta IP intente acceder nuevamente al sistema.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-confirm-unblock-ip">Desbloquear IP</button>
            </div>
        </div>
    </div>
</div>

