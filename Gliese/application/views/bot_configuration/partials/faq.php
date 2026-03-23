<div class="row">
    <div class="col-12">
        <h4 class="mb-3">Preguntas Frecuentes (FAQ)</h4>
        <p class="text-muted">Gestione las preguntas frecuentes que el bot puede responder automáticamente.</p>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <button type="button" class="btn btn-primary" id="btn-create-faq">
            <i data-feather="plus"></i> Nueva FAQ
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-hover" id="table-faq">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Pregunta</th>
                        <th>Respuesta</th>
                        <th>Vistas</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="faq-tbody">
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
    </div>
</div>

<!-- Create/Edit FAQ Modal -->
<div class="modal fade" id="modal-faq" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-faq-title">Nueva FAQ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-faq">
                <div class="modal-body">
                    <input type="hidden" id="faq_id" name="id">

                    <div class="mb-3">
                        <label class="form-label" for="faq_order">Orden</label>
                        <input type="number" class="form-control" id="faq_order" name="order" placeholder="Ej: 1, 2, 3..." required>
                        <small class="text-muted">Número de orden para mostrar las FAQs</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="faq_question">Pregunta <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="faq_question" name="question" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="faq_answer">Respuesta <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="faq_answer" name="answer" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="faq_category">Categoría</label>
                        <select class="form-select" id="faq_category" name="category">
                            <option value="hours">Horarios</option>
                            <option value="coverage">Cobertura</option>
                            <option value="registration">Registro</option>
                            <option value="services">Servicios</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="faq_is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="faq_is_active">Activa</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
