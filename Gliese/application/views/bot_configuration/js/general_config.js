// Store original values for change detection
let originalGeneralData = {};

// --
function init_general_config() {
    console.log('[General Config] Initializing...');
    load_general_config();

    // Form submit handler
    $('#form-general-config').on('submit', function(e) {
        e.preventDefault();
        save_general_config();
    });

    // Auto-save switch on change
    $('#redis_enabled').on('change', function() {
        const isChecked = $(this).is(':checked');
        const value = isChecked ? 'true' : 'false';

        // Only save if value actually changed from original
        if (originalGeneralData.redis_enabled !== undefined &&
            value === originalGeneralData.redis_enabled) {
            console.log('[General] Redis enabled value unchanged, skipping save');
            return;
        }

        $.ajax({
            url: BASE_URL + 'Bot_Configuration/update_general_config',
            method: 'POST',
            data: { redis_enabled: value },
            dataType: 'json',
            success: function(response) {
                functions.toast_message(response.type, response.msg, response.status);
                if (response.status === 'OK') {
                    // Update original value after successful save
                    originalGeneralData.redis_enabled = value;
                }
            },
            error: function(xhr, status, error) {
                functions.toast_message('error', 'Error al guardar configuración', 'ERROR');
                console.error('[General] Auto-save error:', error);
            }
        });
    });
}

// --
function load_general_config() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_general_config',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('[General Config] Response:', response);
            if (response.status === 'OK' && response.data) {
                const data = response.data;
                console.log('[General Config] Data received:', data);

                // Sección 1: Configuraciones de Estudiantes
                $('#min_required_hours').val(data.min_required_hours || '475');

                // Sección 2: Configuraciones de Grupos
                $('#max_supervisors_per_group').val(data.max_supervisors_per_group || '3');

                // Sección 3: Sistema
                $('#system_url').val(data.system_url || 'http://localhost/gliese');

                // Sección 4: Caché y Logs - Redis
                const redisEnabled = data.redis_enabled === 'true' || data.redis_enabled === '1' || data.redis_enabled === true;
                $('#redis_enabled').prop('checked', redisEnabled);
                $('#redis_host').val(data.redis_host || '127.0.0.1');
                $('#redis_port').val(data.redis_port || '6379');
                $('#redis_db').val(data.redis_db || '0');
                // Sección 4: Caché y Logs - TTL
                $('#cache_ttl_general_ms').val(data.cache_ttl_general_ms || '3000');
                $('#cache_ttl_config_ms').val(data.cache_ttl_config_ms || '3000');

                // Sección 4: Caché y Logs - Logs
                $('#log_cleanup_schedule').val(data.log_cleanup_schedule || '2');
                $('#audit_log_retention_days').val(data.audit_log_retention_days || '0.00139');

                // Store original values for change detection
                originalGeneralData = {
                    min_required_hours: data.min_required_hours || '475',
                    max_supervisors_per_group: data.max_supervisors_per_group || '3',
                    system_url: data.system_url || 'http://localhost/gliese',
                    redis_enabled: redisEnabled ? 'true' : 'false',
                    redis_host: data.redis_host || '127.0.0.1',
                    redis_port: data.redis_port || '6379',
                    redis_db: data.redis_db || '0',
                    cache_ttl_general_ms: data.cache_ttl_general_ms || '3000',
                    cache_ttl_config_ms: data.cache_ttl_config_ms || '3000',
                    log_cleanup_schedule: data.log_cleanup_schedule || '2',
                    audit_log_retention_days: data.audit_log_retention_days || '0.00139'
                };

                feather.replace();
            } else if (response.status === 'ERROR') {
                console.log('[General Config] No data found, showing default values');
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al cargar configuración general', 'ERROR');
            console.error('[General Config] Load error:', error);
        }
    });
}

// --
function save_general_config() {
    // Get current values
    const currentData = {
        min_required_hours: $('#min_required_hours').val() || '',
        max_supervisors_per_group: $('#max_supervisors_per_group').val() || '',
        system_url: $('#system_url').val() || '',
        redis_enabled: $('#redis_enabled').is(':checked') ? 'true' : 'false',
        redis_host: $('#redis_host').val() || '',
        redis_port: $('#redis_port').val() || '',
        redis_db: $('#redis_db').val() || '',
        cache_ttl_general_ms: $('#cache_ttl_general_ms').val() || '',
        cache_ttl_config_ms: $('#cache_ttl_config_ms').val() || '',
        log_cleanup_schedule: $('#log_cleanup_schedule').val() || '',
        audit_log_retention_days: $('#audit_log_retention_days').val() || ''
    };

    // Build form data - only include fields that changed
    const formData = {};
    let hasChanges = false;

    // Check each field for changes
    Object.keys(currentData).forEach(function(key) {
        if (currentData[key] !== originalGeneralData[key]) {
            formData[key] = currentData[key];
            hasChanges = true;
        }
    });

    // If no changes detected, don't submit
    if (!hasChanges) {
        console.log('[General] No changes detected, skipping save');
        functions.toast_message('info', 'No se detectaron cambios', 'INFO');
        return;
    }

    console.log('[General] Saving changes:', Object.keys(formData));

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/update_general_config',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                load_general_config();
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al guardar configuración', 'ERROR');
            console.error('[General Config] Save error:', error);
        }
    });
}