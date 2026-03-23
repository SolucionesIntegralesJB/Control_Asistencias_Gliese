<!-- Statistics -->
<div class="row mb-2">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-light-primary p-75"><i data-feather="activity" class="font-small-3 me-25"></i> Total: <strong id="stat-rate-total">0</strong></span>
            <span class="badge bg-light-danger p-75"><i data-feather="slash" class="font-small-3 me-25"></i> Bloqueados: <strong id="stat-rate-blocked">0</strong></span>
            <span class="badge bg-light-success p-75"><i data-feather="unlock" class="font-small-3 me-25"></i> Desbloqueados: <strong id="stat-rate-unblocked">0</strong></span>
            <span class="badge bg-danger p-75"><i data-feather="lock" class="font-small-3 me-25"></i> Permanentes: <strong id="stat-rate-permanent">0</strong></span>
        </div>
    </div>
</div>

<!-- Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i data-feather="list"></i> Registros de Rate Limits</h4>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" id="filter-rate-status">
                            <option value="">Todos los estados</option>
                            <option value="blocked">Bloqueados</option>
                            <option value="unblocked">Desbloqueados</option>
                            <option value="permanent">Permanentes</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" id="filter-rate-severity">
                            <option value="">Todas las severidades</option>
                            <option value="low">Baja</option>
                            <option value="medium">Media</option>
                            <option value="high">Alta</option>
                            <option value="critical">Crítica</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="table-rate-limits">
                        <thead>
                            <tr>
                                <th>Identificador</th>
                                <th>Acción</th>
                                <th>Alertas</th>
                                <th>Severidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-rate-limits">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <div>
                        <span class="text-muted">Mostrando <span id="rate-showing">0</span> de <span id="rate-total">0</span> registros</span>
                    </div>
                    <nav>
                        <ul class="pagination" id="rate-pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirm Unlock -->
<div class="modal fade" id="modal-confirm-unlock" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i data-feather="unlock"></i> Desbloquear</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>¿Está seguro de desbloquear este rate limit?</p>
                <small class="text-muted">Se restablecerán los contadores de bloqueo.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btn-confirm-unlock">Desbloquear</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Rate Limit Detail -->
<div class="modal fade" id="modal-rate-limit-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i data-feather="eye"></i> Detalle de Rate Limit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
