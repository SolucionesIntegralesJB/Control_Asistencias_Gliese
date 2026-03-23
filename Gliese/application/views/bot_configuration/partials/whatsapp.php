<div class="row">
    <div class="col-12">
        <h4 class="mb-3">Configuración de WhatsApp</h4>
        <p class="text-muted">Configure las credenciales de la API de WhatsApp Business de Meta.</p>
    </div>
</div>

<form id="form-whatsapp-config">

    <!-- Acordeón 1: Credenciales de WhatsApp API -->
    <div class="accordion accordion-margin" id="accordionWhatsAppAPI">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingWhatsAppAPI">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWhatsAppAPI" aria-expanded="true" aria-controls="collapseWhatsAppAPI">
                    <i data-feather="message-circle" class="me-2"></i> Credenciales de WhatsApp API
                </button>
            </h2>
            <div id="collapseWhatsAppAPI" class="accordion-collapse collapse show" aria-labelledby="headingWhatsAppAPI">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="enable_auto_download_media" name="enable_auto_download_media">
                                <label class="form-check-label fw-bold" for="enable_auto_download_media">Habilitar Descarga Automática de Multimedia</label>
                            </div>
                            <small class="text-muted">Descarga automática de archivos multimedia recibidos</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="whatsapp_api_url">URL de la API <span class="text-danger">*</span></label>
                            <input type="url" class="form-control" id="whatsapp_api_url" name="whatsapp_api_url" required>
                            <small class="text-muted">URL base de WhatsApp Business API</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="whatsapp_access_token">Access Token <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="whatsapp_access_token" name="whatsapp_access_token" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('whatsapp_access_token', 'eye-icon-token')">
                                    <i data-feather="eye-off" id="eye-icon-token"></i>
                                </button>
                            </div>
                            <small class="text-muted">Token permanente generado en Meta Developer Console</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="whatsapp_phone_number_id">Phone Number ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="whatsapp_phone_number_id" name="whatsapp_phone_number_id" required>
                            <small class="text-muted">ID del número de WhatsApp Business</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="meta_app_secret">Meta App Secret <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="meta_app_secret" name="meta_app_secret" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('meta_app_secret', 'eye-icon-secret')">
                                    <i data-feather="eye-off" id="eye-icon-secret"></i>
                                </button>
                            </div>
                            <small class="text-muted">App Secret para validación de webhooks</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="whatsapp_verify_token">Verify Token <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="whatsapp_verify_token" name="whatsapp_verify_token" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('whatsapp_verify_token', 'eye-icon-verify')">
                                    <i data-feather="eye-off" id="eye-icon-verify"></i>
                                </button>
                            </div>
                            <small class="text-muted">Token personalizado para verificar webhooks</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="message_retention_days">Retención de Mensajes Procesados (días) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="message_retention_days" name="message_retention_days" placeholder="30" required>
                            <small class="text-muted">Días que se mantienen registros de mensajes procesados</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="message_storage_mode">Modo de Almacenamiento <span class="text-danger">*</span></label>
                            <select class="form-select" id="message_storage_mode" name="message_storage_mode" required>
                                <option value="compact">Compacto</option>
                                <option value="full">Completo</option>
                            </select>
                            <small class="text-muted">Modo de almacenamiento de mensajes registrados</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="max_history_messages">Máx. Mensajes en el Chat <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_history_messages" name="max_history_messages" placeholder="1" required>
                            <small class="text-muted">Número máximo de mensajes en historial (solo modo compacto)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="outgoing_polling_interval_ms">Intervalo de Polling (ms) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="outgoing_polling_interval_ms" name="outgoing_polling_interval_ms" placeholder="20000" required>
                            <small class="text-muted">Intervalo de polling para mensajes salientes (ms)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="outgoing_max_retries">Máx. Reintentos <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="outgoing_max_retries" name="outgoing_max_retries" placeholder="5" required>
                            <small class="text-muted">Número máximo de reintentos para mensajes salientes</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="max_image_size_bytes">Tamaño Máx. de Imágenes (bytes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_image_size_bytes" name="max_image_size_bytes" placeholder="15728640" required>
                            <small class="text-muted">Tamaño máximo de imágenes permitido en bytes (15 MB)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="max_document_size_bytes">Tamaño Máx. de Documentos (bytes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_document_size_bytes" name="max_document_size_bytes" placeholder="15728640" required>
                            <small class="text-muted">Tamaño máximo de documentos permitido en bytes (15 MB)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="max_video_size_bytes">Tamaño Máx. de Videos (bytes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_video_size_bytes" name="max_video_size_bytes" placeholder="15728640" required>
                            <small class="text-muted">Tamaño máximo de videos permitido en bytes (15 MB)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="max_audio_size_bytes">Tamaño Máx. de Audios (bytes) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_audio_size_bytes" name="max_audio_size_bytes" placeholder="15728640" required>
                            <small class="text-muted">Tamaño máximo de audios permitido en bytes (15 MB)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="row mt-4">
        <div class="col-12">
            <button type="submit" class="btn btn-primary" id="btn-save-whatsapp">
                <i data-feather="save"></i> Guardar Cambios
            </button>
        </div>
    </div>
</form>

<script>
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === 'password') {
        input.type = 'text';
        icon.setAttribute('data-feather', 'eye');
    } else {
        input.type = 'password';
        icon.setAttribute('data-feather', 'eye-off');
    }

    // Re-render feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Toggle max_history_messages based on message_storage_mode
function toggleMaxHistoryMessages() {
    const storageMode = $('#message_storage_mode').val();
    const maxHistoryField = $('#max_history_messages');

    if (storageMode === 'compact') {
        maxHistoryField.prop('disabled', false);
    } else {
        maxHistoryField.prop('disabled', true);
    }
}

// Initialize on page load
$(document).ready(function() {
    toggleMaxHistoryMessages();

    // Add change event listener
    $('#message_storage_mode').on('change', toggleMaxHistoryMessages);
});
</script>
