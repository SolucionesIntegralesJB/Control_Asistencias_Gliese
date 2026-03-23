<div class="row">
    <div class="col-12">
        <h4 class="mb-3">Configuración General</h4>
        <p class="text-muted">Configure los parámetros generales del sistema de prácticas preprofesionales.</p>
    </div>
</div>

<form id="form-general-config">

    <!-- Acordeón 1: Configuración General -->
    <div class="accordion accordion-margin" id="accordionGeneral">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingGeneral">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGeneral" aria-expanded="true" aria-controls="collapseGeneral">
                    <i data-feather="settings" class="me-2"></i> Configuración General
                </button>
            </h2>
            <div id="collapseGeneral" class="accordion-collapse collapse show" aria-labelledby="headingGeneral">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="form-label" for="min_required_hours">Horas Mínimas Requeridas <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="min_required_hours" name="min_required_hours" placeholder="475" required>
                            <small class="text-muted">Número mínimo de horas para completar prácticas</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="max_supervisors_per_group">Máximo Supervisores por Grupo <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_supervisors_per_group" name="max_supervisors_per_group" placeholder="3" required>
                            <small class="text-muted">Máximo de supervisores permitidos por grupo</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="system_url">URL del Sistema <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="system_url" name="system_url" placeholder="http://localhost/gliese" required>
                            <small class="text-muted">URL del sistema para correos y enlaces</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acordeón 2: Caché y Logs -->
    <div class="accordion accordion-margin" id="accordionCacheLogs">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingCacheLogs">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCacheLogs" aria-expanded="false" aria-controls="collapseCacheLogs">
                    <i data-feather="database" class="me-2"></i> Caché y Logs
                </button>
            </h2>
            <div id="collapseCacheLogs" class="accordion-collapse collapse" aria-labelledby="headingCacheLogs">
                <div class="accordion-body">
                    <!-- Sección: Configuración de Redis -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i data-feather="server" class="me-2"></i>Configuración de Redis</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch form-switch-lg">
                                    <input class="form-check-input" type="checkbox" id="redis_enabled" name="redis_enabled">
                                    <label class="form-check-label fw-bold" for="redis_enabled">Habilitar Redis</label>
                                </div>
                                <small class="text-muted">Habilitar/deshabilitar Redis para caché</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="redis_host">Host de Redis <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="redis_host" name="redis_host" placeholder="127.0.0.1" required>
                                <small class="text-muted">Host del servidor Redis para caché</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="redis_port">Puerto de Redis <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="redis_port" name="redis_port" placeholder="6379" required>
                                <small class="text-muted">Puerto del servidor Redis (por defecto: 6379)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="redis_db">Base de Datos Redis <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="redis_db" name="redis_db" placeholder="0" required>
                                <small class="text-muted">Número de base de datos Redis a usar (0-15)</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Sección: TTL de Caché -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i data-feather="clock" class="me-2"></i>TTL de Caché</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="cache_ttl_general_ms">TTL Caché General (ms) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="cache_ttl_general_ms" name="cache_ttl_general_ms" placeholder="3000" required>
                                <small class="text-muted">TTL para caché de servicios generales (FAQ, mensajes, company_info, menú) en milisegundos</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="cache_ttl_config_ms">TTL Caché de Configuraciones (ms) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="cache_ttl_config_ms" name="cache_ttl_config_ms" placeholder="3000" required>
                                <small class="text-muted">TTL para caché de configuraciones críticas (BackoffService, MediaService) en milisegundos</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Sección: Logs -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i data-feather="file-text" class="me-2"></i>Logs</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="log_cleanup_schedule">Hora de Limpieza de Logs <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="log_cleanup_schedule" name="log_cleanup_schedule" placeholder="2" required>
                                <small class="text-muted">Hora diaria para ejecutar limpieza de logs (formato HH:mm)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="audit_log_retention_days">Retención de Logs de Auditoría (días) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="audit_log_retention_days" name="audit_log_retention_days" placeholder="0.00139" step="0.00001" required>
                                <small class="text-muted">Días de retención de logs de auditoría</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="row mt-4">
        <div class="col-12">
            <button type="submit" class="btn btn-primary" id="btn-save-general">
                <i data-feather="save"></i> Guardar Cambios
            </button>
        </div>
    </div>
</form>
