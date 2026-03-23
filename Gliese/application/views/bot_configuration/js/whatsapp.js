// Store original values for change detection
let originalWhatsAppData = {};

// --
function init_whatsapp() {
    console.log('[WhatsApp Config] Initializing...');
    load_whatsapp_config();

    // Form submit handler
    $('#form-whatsapp-config').on('submit', function(e) {
        e.preventDefault();
        save_whatsapp_config();
    });

    // Auto-save switch on change
    $('#enable_auto_download_media').on('change', function() {
        const isChecked = $(this).is(':checked');
        const value = isChecked ? 'true' : 'false';

        // Only save if value actually changed from original
        if (originalWhatsAppData.enable_auto_download_media !== undefined &&
            value === originalWhatsAppData.enable_auto_download_media) {
            console.log('[WhatsApp] Auto-download media value unchanged, skipping save');
            return;
        }

        $.ajax({
            url: BASE_URL + 'Bot_Configuration/update_whatsapp_config',
            method: 'POST',
            data: { enable_auto_download_media: value },
            dataType: 'json',
            success: function(response) {
                functions.toast_message(response.type, response.msg, response.status);
                if (response.status === 'OK') {
                    // Update original value after successful save
                    originalWhatsAppData.enable_auto_download_media = value;
                }
            },
            error: function(xhr, status, error) {
                functions.toast_message('error', 'Error al guardar configuración', 'ERROR');
                console.error('[WhatsApp] Auto-save error:', error);
            }
        });
    });
}

// --
function load_whatsapp_config() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_whatsapp_config',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const data = response.data;

                // Switch
                const autoDownload = data.enable_auto_download_media === 'true' || data.enable_auto_download_media === '1' || data.enable_auto_download_media === true;
                $('#enable_auto_download_media').prop('checked', autoDownload);

                // Credenciales de WhatsApp API
                $('#whatsapp_api_url').val(data.whatsapp_api_url);
                $('#whatsapp_access_token').val(data.whatsapp_access_token);
                $('#meta_app_secret').val(data.meta_app_secret);
                $('#whatsapp_verify_token').val(data.whatsapp_verify_token);
                $('#whatsapp_phone_number_id').val(data.whatsapp_phone_number_id);
                $('#message_retention_days').val(data.message_retention_days);
                $('#message_storage_mode').val(data.message_storage_mode);
                $('#max_history_messages').val(data.max_history_messages);
                $('#outgoing_polling_interval_ms').val(data.outgoing_polling_interval_ms);
                $('#outgoing_max_retries').val(data.outgoing_max_retries);
                $('#max_image_size_bytes').val(data.max_image_size_bytes);
                $('#max_document_size_bytes').val(data.max_document_size_bytes);
                $('#max_video_size_bytes').val(data.max_video_size_bytes);
                $('#max_audio_size_bytes').val(data.max_audio_size_bytes);

                // Store original values for change detection
                originalWhatsAppData = {
                    enable_auto_download_media: autoDownload ? 'true' : 'false',
                    whatsapp_api_url: data.whatsapp_api_url || '',
                    whatsapp_access_token: data.whatsapp_access_token || '',
                    meta_app_secret: data.meta_app_secret || '',
                    whatsapp_verify_token: data.whatsapp_verify_token || '',
                    whatsapp_phone_number_id: data.whatsapp_phone_number_id || '',
                    message_retention_days: data.message_retention_days || '',
                    message_storage_mode: data.message_storage_mode || '',
                    max_history_messages: data.max_history_messages || '',
                    outgoing_polling_interval_ms: data.outgoing_polling_interval_ms || '',
                    outgoing_max_retries: data.outgoing_max_retries || '',
                    max_image_size_bytes: data.max_image_size_bytes || '',
                    max_document_size_bytes: data.max_document_size_bytes || '',
                    max_video_size_bytes: data.max_video_size_bytes || '',
                    max_audio_size_bytes: data.max_audio_size_bytes || ''
                };

                // Toggle max_history_messages field based on storage mode
                if (typeof toggleMaxHistoryMessages === 'function') {
                    toggleMaxHistoryMessages();
                }

                feather.replace();
            } else if (response.status === 'ERROR') {
                console.log('[WhatsApp Config] No data found, showing default values');
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al cargar configuración de WhatsApp', 'ERROR');
            console.error('[WhatsApp Config] Load error:', error);
        }
    });
}

// --
function save_whatsapp_config() {
    // Get current values
    const currentData = {
        enable_auto_download_media: $('#enable_auto_download_media').is(':checked') ? 'true' : 'false',
        whatsapp_api_url: $('#whatsapp_api_url').val() || '',
        whatsapp_access_token: $('#whatsapp_access_token').val() || '',
        meta_app_secret: $('#meta_app_secret').val() || '',
        whatsapp_verify_token: $('#whatsapp_verify_token').val() || '',
        whatsapp_phone_number_id: $('#whatsapp_phone_number_id').val() || '',
        message_retention_days: $('#message_retention_days').val() || '',
        message_storage_mode: $('#message_storage_mode').val() || '',
        max_history_messages: $('#max_history_messages').val() || '',
        outgoing_polling_interval_ms: $('#outgoing_polling_interval_ms').val() || '',
        outgoing_max_retries: $('#outgoing_max_retries').val() || '',
        max_image_size_bytes: $('#max_image_size_bytes').val() || '',
        max_document_size_bytes: $('#max_document_size_bytes').val() || '',
        max_video_size_bytes: $('#max_video_size_bytes').val() || '',
        max_audio_size_bytes: $('#max_audio_size_bytes').val() || ''
    };

    // Build form data - only include fields that changed
    const formData = {};
    let hasChanges = false;

    // Check each field for changes
    if (currentData.enable_auto_download_media !== originalWhatsAppData.enable_auto_download_media) {
        formData.enable_auto_download_media = currentData.enable_auto_download_media;
        hasChanges = true;
    }

    if (currentData.whatsapp_api_url !== originalWhatsAppData.whatsapp_api_url) {
        formData.whatsapp_api_url = currentData.whatsapp_api_url;
        hasChanges = true;
    }

    if (currentData.whatsapp_phone_number_id !== originalWhatsAppData.whatsapp_phone_number_id) {
        formData.whatsapp_phone_number_id = currentData.whatsapp_phone_number_id;
        hasChanges = true;
    }

    if (currentData.message_retention_days !== originalWhatsAppData.message_retention_days) {
        formData.message_retention_days = currentData.message_retention_days;
        hasChanges = true;
    }

    if (currentData.message_storage_mode !== originalWhatsAppData.message_storage_mode) {
        formData.message_storage_mode = currentData.message_storage_mode;
        hasChanges = true;
    }

    if (currentData.max_history_messages !== originalWhatsAppData.max_history_messages) {
        formData.max_history_messages = currentData.max_history_messages;
        hasChanges = true;
    }

    if (currentData.outgoing_polling_interval_ms !== originalWhatsAppData.outgoing_polling_interval_ms) {
        formData.outgoing_polling_interval_ms = currentData.outgoing_polling_interval_ms;
        hasChanges = true;
    }

    if (currentData.outgoing_max_retries !== originalWhatsAppData.outgoing_max_retries) {
        formData.outgoing_max_retries = currentData.outgoing_max_retries;
        hasChanges = true;
    }

    if (currentData.max_image_size_bytes !== originalWhatsAppData.max_image_size_bytes) {
        formData.max_image_size_bytes = currentData.max_image_size_bytes;
        hasChanges = true;
    }

    if (currentData.max_document_size_bytes !== originalWhatsAppData.max_document_size_bytes) {
        formData.max_document_size_bytes = currentData.max_document_size_bytes;
        hasChanges = true;
    }

    if (currentData.max_video_size_bytes !== originalWhatsAppData.max_video_size_bytes) {
        formData.max_video_size_bytes = currentData.max_video_size_bytes;
        hasChanges = true;
    }

    if (currentData.max_audio_size_bytes !== originalWhatsAppData.max_audio_size_bytes) {
        formData.max_audio_size_bytes = currentData.max_audio_size_bytes;
        hasChanges = true;
    }

    // Encrypted fields - only send if changed AND not empty
    if (currentData.whatsapp_access_token !== originalWhatsAppData.whatsapp_access_token &&
        currentData.whatsapp_access_token.trim() !== '') {
        formData.whatsapp_access_token = currentData.whatsapp_access_token;
        hasChanges = true;
        console.log('[WhatsApp] Sending access_token (changed)');
    }

    if (currentData.meta_app_secret !== originalWhatsAppData.meta_app_secret &&
        currentData.meta_app_secret.trim() !== '') {
        formData.meta_app_secret = currentData.meta_app_secret;
        hasChanges = true;
        console.log('[WhatsApp] Sending meta_app_secret (changed)');
    }

    if (currentData.whatsapp_verify_token !== originalWhatsAppData.whatsapp_verify_token &&
        currentData.whatsapp_verify_token.trim() !== '') {
        formData.whatsapp_verify_token = currentData.whatsapp_verify_token;
        hasChanges = true;
        console.log('[WhatsApp] Sending verify_token (changed)');
    }

    // If no changes detected, don't submit
    if (!hasChanges) {
        console.log('[WhatsApp] No changes detected, skipping save');
        functions.toast_message('info', 'No se detectaron cambios', 'INFO');
        return;
    }

    console.log('[WhatsApp] Saving changes:', Object.keys(formData));

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/update_whatsapp_config',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                load_whatsapp_config();
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al guardar configuración', 'ERROR');
            console.error('[WhatsApp Config] Save error:', error);
        }
    });
}
