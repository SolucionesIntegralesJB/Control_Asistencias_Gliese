<div class="card">
    <div class="card-header">
        <h4 class="card-title">Configuración de Inteligencia Artificial</h4>
        <p class="text-muted">Configure la API de OpenAI y los prompts del sistema de IA.</p>
    </div>
    <div class="card-body">
        <!-- API Key Section -->
        <form id="form-ai-api-key" class="mb-4">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label" for="openai_api_key">OpenAI API Key</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="openai_api_key" name="openai_api_key">
                        <button class="btn btn-outline-secondary" type="button" id="toggle-openai-key">
                            <i data-feather="eye" id="eye-icon-openai"></i>
                        </button>
                    </div>
                    <small class="text-muted">API Key de OpenAI para el modelo GPT. <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener">Obtener API Key</a></small>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i data-feather="save" class="me-1"></i> Guardar API Key
                    </button>
                </div>
            </div>
        </form>

        <hr>

        <!-- Prompts Section -->
        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Prompts del Sistema</h5>
                <button class="btn btn-success btn-sm" id="btn-add-prompt">
                    <i data-feather="plus" class="me-1"></i> Nuevo Prompt
                </button>
            </div>

            <!-- Prompts Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Contexto</th>
                            <th>Descripción</th>
                            <th>Uso</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-prompts">
                        <tr>
                            <td colspan="6" class="text-center">
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

<!-- Modal: Edit Prompt -->
<div class="modal fade" id="modal-edit-prompt" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-prompt-title">Editar Prompt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-prompt">
                    <input type="hidden" id="prompt_id" name="prompt_id">

                    <div class="mb-3">
                        <label class="form-label" for="prompt_key">Key <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="prompt_key" name="prompt_key" required>
                        <small class="text-muted">Identificador único del prompt (sin espacios, usar guiones bajos)</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="prompt_context">Contexto</label>
                            <select class="form-select" id="prompt_context_select" name="prompt_context_select">
                                <!-- Los contextos se cargarán dinámicamente -->
                                <option value="">Sin contexto</option>
                                <option value="__new__">+ Crear nuevo contexto...</option>
                            </select>
                            <input type="text" class="form-control mt-2" id="prompt_context_input" name="prompt_context" placeholder="Escriba el nombre del nuevo contexto" style="display: none;">
                            <small class="text-muted">Seleccione un contexto o cree uno nuevo</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="prompt_display_order">Orden</label>
                            <input type="number" class="form-control" id="prompt_display_order" name="prompt_display_order" value="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="prompt_description">Descripción</label>
                        <textarea class="form-control" id="prompt_description" name="prompt_description" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="prompt_text">Texto del Prompt <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="prompt_text" name="prompt_text" rows="10" required></textarea>
                        <small class="text-muted">Variables disponibles: {company_name}, {assistant_name}, {company_address}, {company_phone}, {company_email}, {company_website}, {services}, {practices}</small>
                    </div>

                    <div class="row" id="ai-config-fields">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="prompt_model">Modelo IA</label>
                            <select class="form-select" id="prompt_model" name="prompt_model">
                                <option value="">Sin modelo</option>
                                <option value="gpt-4o-mini">gpt-4o-mini</option>
                                <option value="gpt-4o">gpt-4o</option>
                                <option value="gpt-4">gpt-4</option>
                                <option value="gpt-3.5-turbo">gpt-3.5-turbo</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="prompt_max_tokens">Max Tokens</label>
                            <input type="number" class="form-control" id="prompt_max_tokens" name="prompt_max_tokens" min="1" max="4096" placeholder="300">
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="prompt_temperature">Temperatura</label>
                            <input type="number" class="form-control" id="prompt_temperature" name="prompt_temperature" min="0" max="2" step="0.01" placeholder="0.70">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="prompt_is_active">Estado</label>
                        <select class="form-select" id="prompt_is_active" name="prompt_is_active">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-prompt">
                    <i data-feather="save" class="me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
