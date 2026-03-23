<!-- Acordeón: Mensajes del Sistema -->
<style>
.cursor-pointer {
    cursor: pointer;
}
</style>
<div class="accordion accordion-margin mt-3" id="accordionMessages">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingMessages">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMessages" aria-expanded="true">
                <i data-feather="message-circle" class="me-2"></i> Mensajes del Sistema
            </button>
        </h2>
        <div id="collapseMessages" class="accordion-collapse collapse show" aria-labelledby="headingMessages">
            <div class="accordion-body">
                <p class="text-muted mb-4">Configure los mensajes predefinidos que el bot enviará en diferentes situaciones.</p>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm" id="btn-add-message">
                            <i data-feather="plus" class="me-1"></i> Nuevo Mensaje
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <select class="form-select form-select-sm" id="filter-messages" style="max-width: 300px;">
                                <option value="">Todas las categorías</option>
                                <option value="contact">Contact</option>
                                <option value="error">Error</option>
                                <option value="faq">FAQ</option>
                                <option value="menu">Menu</option>
                                <option value="registration">Registration</option>
                                <option value="security">Security</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Usos</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-messages">
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

<!-- Modal: Editar Mensaje -->
<div class="modal fade" id="modal-edit-message" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-message-title">Editar Mensaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-message">
                    <input type="hidden" id="message_id" name="message_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="message_key">Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="message_key" name="message_key" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="message_label">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="message_label" name="message_label" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="message_description">Descripción</label>
                            <textarea class="form-control" id="message_description" name="message_description" rows="2" placeholder="Breve descripción del propósito de este mensaje"></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="message_category">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select" id="message_category" name="message_category" required>
                                <option value="">Seleccione una categoría</option>
                                <option value="contact">Contact</option>
                                <option value="error">Error</option>
                                <option value="faq">FAQ</option>
                                <option value="menu">Menu</option>
                                <option value="registration">Registration</option>
                                <option value="security">Security</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="message_text">Mensaje que se envía al usuario <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message_text" name="message_text" rows="5" required></textarea>
                            <small class="text-muted">Variables disponibles: {nombre}, {empresa}, {hora}, {fecha}</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="message_variables">Variables (JSON)</label>
                            <textarea class="form-control" id="message_variables" name="message_variables" rows="3" placeholder='{"variable1": "valor1", "variable2": "valor2"}'></textarea>
                            <small class="text-muted">Formato JSON opcional para variables personalizadas</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="message_is_active">Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="message_is_active" name="message_is_active">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-message">
                    <i data-feather="save" class="me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
