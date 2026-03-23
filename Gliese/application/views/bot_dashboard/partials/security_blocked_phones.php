<!-- Statistics -->
<div class="row mb-2">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-light-primary p-75"><i data-feather="phone" class="font-small-3 me-25"></i> Total: <strong id="stat-blocked-total">0</strong></span>
            <span class="badge bg-light-danger p-75"><i data-feather="lock" class="font-small-3 me-25"></i> Bloqueados: <strong id="stat-blocked-active">0</strong></span>
            <span class="badge bg-light-success p-75"><i data-feather="unlock" class="font-small-3 me-25"></i> Desbloqueados: <strong id="stat-blocked-inactive">0</strong></span>
            <span class="badge bg-danger p-75"><i data-feather="shield-off" class="font-small-3 me-25"></i> Permanentes: <strong id="stat-blocked-permanent">0</strong></span>
        </div>
    </div>
</div>

<!-- Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title"><i data-feather="list"></i> Teléfonos Bloqueados</h4>
            </div>
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" id="filter-blocked-status">
                            <option value="">Todos los estados</option>
                            <option value="blocked">Bloqueados</option>
                            <option value="unblocked">Desbloqueados</option>
                            <option value="permanent">Permanentes</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select form-select-sm" id="filter-blocked-type">
                            <option value="">Todos los tipos</option>
                            <option value="message">Message</option>
                            <option value="verification">Verification</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="table-blocked-phones">
                        <thead>
                            <tr>
                                <th>Teléfono</th>
                                <th>Motivo</th>
                                <th>Tipo</th>
                                <th>Intentos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-blocked-phones">
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
                        <span class="text-muted">Mostrando <span id="blocked-showing">0</span> de <span id="blocked-total">0</span> registros</span>
                    </div>
                    <nav>
                        <ul class="pagination" id="blocked-pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirm Unblock -->
<div class="modal fade" id="unblock-phone-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i data-feather="unlock"></i> Desbloquear</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>¿Desbloquear el teléfono <strong id="unblock-phone-number"></strong>?</p>
                <small class="text-muted">Se restablecerán los contadores de bloqueo.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btn-confirm-unblock-phone">Desbloquear</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Phone Detail -->
<div class="modal fade" id="modal-phone-detail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i data-feather="eye"></i> Detalle de Teléfono</h5>
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
