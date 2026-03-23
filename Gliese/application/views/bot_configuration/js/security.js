// SECURITY CONFIGURATION JS

// Cargar toda la configuracion de seguridad
function loadSecurityConfig() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_security_config',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK') {
                populateSecurityForms(response.data);
                updateSecurityBadges(response.data);
            } else {
                toastr.error('Error al cargar configuracion de seguridad', 'Error');
            }
        },
        error: function() {
            toastr.error('Error de conexion al cargar seguridad', 'Error');
        }
    });
}

// Variable global para guardar datos originales
var originalSecurityData = {};

// Poblar todos los formularios con datos
function populateSecurityForms(data) {
    // Guardar datos originales para comparar cambios
    originalSecurityData = JSON.parse(JSON.stringify(data));

    // Lista de todos los switches (boolean fields)
    const switchFields = [
        'enable_rate_limiting',
        'enable_action_rate_limiting',
        'enable_phone_blocking',
        'enable_security_alerts',
        'alert_on_suspicious_activity',
        'enable_signature_validation',
        'enable_cors_protection',
        'enable_helmet_protection',
        'trust_proxy',
        'slowloris_protection_enabled'
    ];

    switchFields.forEach(function(field) {
        if (data[field] !== undefined) {
            const isChecked = data[field] === 'true' || data[field] === '1' || data[field] === true;
            $('#' + field).prop('checked', isChecked);
        }
    });

    // Lista de todos los campos numericos y de texto
    const inputFields = [
        'rate_limit_window_ms',
        'rate_limit_max_requests',
        'security_violation_block_minutes',
        'rate_limit_block_after_violations',
        'max_messages_per_minute',
        'max_verification_attempts',
        'max_ai_queries_per_hour',
        'spam_block_minutes',
        'otp_code_expiration_minutes',
        'verification_block_minutes',
        'user_block_escalation_threshold',
        'admin_email_alerts',
        'slowloris_headers_timeout',
        'slowloris_request_timeout',
        'slowloris_keepalive_timeout',
        'slowloris_max_connections_per_ip',
        'slowloris_slow_request_threshold'
    ];

    inputFields.forEach(function(field) {
        if (data[field] !== undefined) {
            $('#' + field).val(data[field]);
        }
    });

    // Refresh feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Actualizar badges de estado
function updateSecurityBadges(data) {
    const rateLimitEnabled = data.enable_rate_limiting === 'true' || data.enable_rate_limiting === '1';
    $('#badge-rate-limiting-global')
        .removeClass('bg-light-primary bg-light-success bg-light-danger')
        .addClass(rateLimitEnabled ? 'bg-light-success' : 'bg-light-danger')
        .text(rateLimitEnabled ? 'Activo' : 'Inactivo');

    const actionRateLimitEnabled = data.enable_action_rate_limiting === 'true' || data.enable_action_rate_limiting === '1';
    $('#badge-action-rate-limiting')
        .removeClass('bg-light-primary bg-light-success bg-light-danger')
        .addClass(actionRateLimitEnabled ? 'bg-light-success' : 'bg-light-danger')
        .text(actionRateLimitEnabled ? 'Activo' : 'Inactivo');

    const alertsEnabled = data.enable_security_alerts === 'true' || data.enable_security_alerts === '1';
    $('#badge-alerts')
        .removeClass('bg-light-primary bg-light-success bg-light-danger')
        .addClass(alertsEnabled ? 'bg-light-success' : 'bg-light-danger')
        .text(alertsEnabled ? 'Activo' : 'Inactivo');

    const slowlorisEnabled = data.slowloris_protection_enabled === 'true' || data.slowloris_protection_enabled === '1';
    $('#badge-slowloris')
        .removeClass('bg-light-primary bg-light-success bg-light-danger')
        .addClass(slowlorisEnabled ? 'bg-light-success' : 'bg-light-danger')
        .text(slowlorisEnabled ? 'Activo' : 'Inactivo');

    // Capa 5: General Security (activo si al menos 1 switch esta ON)
    const signatureEnabled = data.enable_signature_validation === 'true' || data.enable_signature_validation === '1';
    const corsEnabled = data.enable_cors_protection === 'true' || data.enable_cors_protection === '1';
    const helmetEnabled = data.enable_helmet_protection === 'true' || data.enable_helmet_protection === '1';
    const proxyEnabled = data.trust_proxy === 'true' || data.trust_proxy === '1';
    const generalSecurityActive = signatureEnabled || corsEnabled || helmetEnabled || proxyEnabled;
    $('#badge-general-security')
        .removeClass('bg-light-primary bg-light-success bg-light-danger')
        .addClass(generalSecurityActive ? 'bg-light-success' : 'bg-light-danger')
        .text(generalSecurityActive ? 'Activo' : 'Inactivo');
}

// SWITCHES CRITICOS (requieren contraseña para desactivar)
const criticalSwitches = {
    'enable_rate_limiting': 'Rate Limiting Global protege contra ataques de fuerza bruta. Sin esta capa, usuarios maliciosos podrían saturar el sistema con solicitudes ilimitadas.',
    'enable_action_rate_limiting': 'Action Rate Limiting previene abuso de funciones específicas. Desactivarlo permite spam de mensajes y agotamiento de recursos de IA.',
    'enable_security_alerts': 'Las alertas de seguridad notifican actividad sospechosa. Desactivarlas significa que no recibirá avisos de posibles ataques.',
    'enable_signature_validation': 'Signature Validation verifica que los webhooks provienen de Meta. Sin esta validación, cualquiera podría enviar mensajes falsos al bot.'
};

// SWITCHES NO CRITICOS (solo advertencia)
const warningSwitches = {
    'enable_cors_protection': 'CORS Protection valida el origen de solicitudes HTTP. Desactivarlo podría permitir solicitudes desde sitios web no autorizados.',
    'enable_helmet_protection': 'Helmet Protection agrega headers de seguridad HTTP estándar. Desactivarlo reduce la protección contra ataques XSS y clickjacking.',
    'trust_proxy': 'Trust Proxy permite obtener la IP real detrás de proxies. Desactivarlo podría mostrar IPs incorrectas en los logs.',
    'slowloris_protection_enabled': 'Slowloris Protection detecta ataques de conexiones lentas. Desactivarlo deja el servidor vulnerable a este tipo de ataque DoS.',
    'alert_on_suspicious_activity': 'Las alertas por actividad sospechosa notifican comportamientos anómalos. Desactivarlas reduce la visibilidad de posibles amenazas.'
};

// FUNCION PARA AUTO-GUARDAR SWITCHES
function autoSaveSwitch(switchId) {
    const isChecked = $('#' + switchId).is(':checked');
    const formData = {};
    formData[switchId] = isChecked ? 'true' : 'false';

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/update_security_config',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK') {
                toastr.success('Configuracion actualizada', 'Exito', {timeOut: 1000});
            } else {
                toastr.error(response.msg || 'Error al guardar', 'Error');
                // Revertir el switch si falla
                $('#' + switchId).prop('checked', !isChecked);
            }
        },
        error: function() {
            toastr.error('Error de conexion', 'Error');
            // Revertir el switch si falla
            $('#' + switchId).prop('checked', !isChecked);
        }
    });
}

// FUNCION PARA MANEJAR SWITCHES CON PROTECCION
function handleProtectedSwitch(switchId, updateBadgeCallback) {
    const isChecked = $('#' + switchId).is(':checked');

    // Si está ACTIVANDO, guardar directamente
    if (isChecked) {
        if (updateBadgeCallback) updateBadgeCallback();
        autoSaveSwitch(switchId);
        return;
    }

    // Si está DESACTIVANDO, verificar tipo de switch
    if (criticalSwitches[switchId]) {
        // SWITCH CRITICO: Pedir contraseña
        $('#' + switchId).prop('checked', true); // Revertir temporalmente
        $('#confirm-switch-id').val(switchId);
        $('#modal-disable-message').html(criticalSwitches[switchId]);
        $('#confirm-password').val('').removeClass('is-invalid');
        $('#modal-confirm-disable').modal('show');
        if (typeof feather !== 'undefined') feather.replace();
    } else if (warningSwitches[switchId]) {
        // SWITCH NO CRITICO: Solo advertencia
        $('#' + switchId).prop('checked', true); // Revertir temporalmente
        $('#warning-switch-id').val(switchId);
        $('#modal-warning-message').html(warningSwitches[switchId]);
        $('#modal-warning-disable').modal('show');
        if (typeof feather !== 'undefined') feather.replace();
    } else {
        // Switch sin protección
        if (updateBadgeCallback) updateBadgeCallback();
        autoSaveSwitch(switchId);
    }
}

// CONFIRMAR DESACTIVACION CON CONTRASEÑA
$('#btn-confirm-disable').on('click', function() {
    const switchId = $('#confirm-switch-id').val();
    const password = $('#confirm-password').val();

    if (!password) {
        $('#confirm-password').addClass('is-invalid');
        $('#password-error').text('Ingrese su contraseña');
        return;
    }

    const $btn = $(this);
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Verificando...');

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/verify_admin_password',
        type: 'POST',
        data: { password: password },
        dataType: 'json',
        success: function(response) {
            $btn.prop('disabled', false).html('<i data-feather="unlock"></i> Confirmar Desactivación');
            if (typeof feather !== 'undefined') feather.replace();

            if (response.status === 'OK') {
                $('#modal-confirm-disable').modal('hide');
                $('#' + switchId).prop('checked', false);

                // Actualizar badge correspondiente
                updateBadgeForSwitch(switchId, false);

                // Guardar el cambio
                autoSaveSwitch(switchId);
                toastr.warning('Capa de seguridad desactivada', 'Advertencia');
            } else {
                $('#confirm-password').addClass('is-invalid');
                $('#password-error').text(response.msg || 'Contraseña incorrecta');
            }
        },
        error: function() {
            $btn.prop('disabled', false).html('<i data-feather="unlock"></i> Confirmar Desactivación');
            if (typeof feather !== 'undefined') feather.replace();
            toastr.error('Error de conexión', 'Error');
        }
    });
});

// CONFIRMAR DESACTIVACION SIN CONTRASEÑA (solo advertencia)
$('#btn-confirm-warning').on('click', function() {
    const switchId = $('#warning-switch-id').val();
    $('#modal-warning-disable').modal('hide');
    $('#' + switchId).prop('checked', false);

    // Actualizar badge correspondiente
    updateBadgeForSwitch(switchId, false);

    // Guardar el cambio
    autoSaveSwitch(switchId);
});

// CANCELAR MODALES
$('#btn-cancel-disable, #modal-confirm-disable .btn-close').on('click', function() {
    // El switch ya está revertido, no hacer nada más
});

$('#btn-cancel-warning, #modal-warning-disable .btn-close').on('click', function() {
    // El switch ya está revertido, no hacer nada más
});

// FUNCION PARA ACTUALIZAR BADGE SEGUN SWITCH
function updateBadgeForSwitch(switchId, isChecked) {
    const badgeMap = {
        'enable_rate_limiting': 'badge-rate-limiting-global',
        'enable_action_rate_limiting': 'badge-action-rate-limiting',
        'enable_security_alerts': 'badge-alerts',
        'slowloris_protection_enabled': 'badge-slowloris'
    };

    if (badgeMap[switchId]) {
        $('#' + badgeMap[switchId])
            .removeClass('bg-light-success bg-light-danger')
            .addClass(isChecked ? 'bg-light-success' : 'bg-light-danger')
            .text(isChecked ? 'Activo' : 'Inactivo');
    }

    // Para switches de Configuración General
    if (['enable_signature_validation', 'enable_cors_protection', 'enable_helmet_protection', 'trust_proxy'].includes(switchId)) {
        updateGeneralSecurityBadge();
    }
}

// GUARDAR Rate Limiting Global
$('#form-rate-limiting-global').on('submit', function(e) {
    e.preventDefault();

    const formData = {
        rate_limit_window_ms: $('#rate_limit_window_ms').val(),
        rate_limit_max_requests: $('#rate_limit_max_requests').val(),
        security_violation_block_minutes: $('#security_violation_block_minutes').val(),
        rate_limit_block_after_violations: $('#rate_limit_block_after_violations').val()
    };

    // Filtrar campos vacios y detectar cambios
    const filteredData = {};
    for (let key in formData) {
        if (formData[key] !== '' && formData[key] !== null && formData[key] !== undefined) {
            // Comparar con valor original
            if (formData[key] != originalSecurityData[key]) {
                filteredData[key] = formData[key];
            }
        }
    }

    if (Object.keys(filteredData).length === 0) {
        toastr.info('No hay cambios para guardar', 'Info');
        return;
    }

    saveSecurityLayer(filteredData, 'btn-save-rate-limiting-global', 'Rate Limiting Global');
});

// GUARDAR Action Rate Limiting
$('#form-action-rate-limiting').on('submit', function(e) {
    e.preventDefault();

    const formData = {
        enable_action_rate_limiting: $('#enable_action_rate_limiting').is(':checked') ? 'true' : 'false',
        enable_phone_blocking: $('#enable_phone_blocking').is(':checked') ? 'true' : 'false',
        max_messages_per_minute: $('#max_messages_per_minute').val(),
        max_verification_attempts: $('#max_verification_attempts').val(),
        max_ai_queries_per_hour: $('#max_ai_queries_per_hour').val(),
        spam_block_minutes: $('#spam_block_minutes').val(),
        otp_code_expiration_minutes: $('#otp_code_expiration_minutes').val(),
        verification_block_minutes: $('#verification_block_minutes').val(),
        user_block_escalation_threshold: $('#user_block_escalation_threshold').val()
    };

    // Filtrar campos vacios y detectar cambios
    const filteredData = {};
    for (let key in formData) {
        if (formData[key] !== '' && formData[key] !== null && formData[key] !== undefined) {
            if (formData[key] != originalSecurityData[key]) {
                filteredData[key] = formData[key];
            }
        }
    }

    if (Object.keys(filteredData).length === 0) {
        toastr.info('No hay cambios para guardar', 'Info');
        return;
    }

    saveSecurityLayer(filteredData, 'btn-save-action-rate-limiting', 'Action Rate Limiting');
});

// GUARDAR Alertas de Seguridad
$('#form-alerts').on('submit', function(e) {
    e.preventDefault();

    const formData = {
        admin_email_alerts: $('#admin_email_alerts').val()
    };

    // Filtrar campos vacios y detectar cambios
    const filteredData = {};
    for (let key in formData) {
        if (formData[key] !== '' && formData[key] !== null && formData[key] !== undefined) {
            if (formData[key] != originalSecurityData[key]) {
                filteredData[key] = formData[key];
            }
        }
    }

    if (Object.keys(filteredData).length === 0) {
        toastr.info('No hay cambios para guardar', 'Info');
        return;
    }

    saveSecurityLayer(filteredData, 'btn-save-alerts', 'Alertas de Seguridad');
});

// GUARDAR Slowloris Protection
$('#form-slowloris').on('submit', function(e) {
    e.preventDefault();

    const formData = {
        slowloris_headers_timeout: $('#slowloris_headers_timeout').val(),
        slowloris_request_timeout: $('#slowloris_request_timeout').val(),
        slowloris_keepalive_timeout: $('#slowloris_keepalive_timeout').val(),
        slowloris_max_connections_per_ip: $('#slowloris_max_connections_per_ip').val(),
        slowloris_slow_request_threshold: $('#slowloris_slow_request_threshold').val()
    };

    // Filtrar campos vacios y detectar cambios
    const filteredData = {};
    for (let key in formData) {
        if (formData[key] !== '' && formData[key] !== null && formData[key] !== undefined) {
            if (formData[key] != originalSecurityData[key]) {
                filteredData[key] = formData[key];
            }
        }
    }

    if (Object.keys(filteredData).length === 0) {
        toastr.info('No hay cambios para guardar', 'Info');
        return;
    }

    saveSecurityLayer(filteredData, 'btn-save-slowloris', 'Slowloris Protection');
});

// FUNCION GENERICA PARA GUARDAR CAPAS
function saveSecurityLayer(formData, buttonId, layerName) {
    const $button = $('#' + buttonId);
    const originalHtml = $button.html();
    $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Guardando...');

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/update_security_config',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            $button.prop('disabled', false).html(originalHtml);
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            if (response.status === 'OK') {
                toastr.success(layerName + ' guardada correctamente', 'Exito');
                loadSecurityConfig();
            } else {
                toastr.error(response.msg || 'Error al guardar ' + layerName, 'Error');
            }
        },
        error: function() {
            $button.prop('disabled', false).html(originalHtml);
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
            toastr.error('Error de conexion al guardar ' + layerName, 'Error');
        }
    });
}

// AUTO-GUARDAR SWITCHES AL CAMBIAR (con protección)

// SWITCHES CRITICOS - Requieren contraseña para desactivar
$('#enable_rate_limiting').on('change', function() {
    handleProtectedSwitch('enable_rate_limiting', function() {
        const isChecked = $('#enable_rate_limiting').is(':checked');
        $('#badge-rate-limiting-global')
            .removeClass('bg-light-success bg-light-danger')
            .addClass(isChecked ? 'bg-light-success' : 'bg-light-danger')
            .text(isChecked ? 'Activo' : 'Inactivo');
    });
});

$('#enable_action_rate_limiting').on('change', function() {
    handleProtectedSwitch('enable_action_rate_limiting', function() {
        const isChecked = $('#enable_action_rate_limiting').is(':checked');
        $('#badge-action-rate-limiting')
            .removeClass('bg-light-success bg-light-danger')
            .addClass(isChecked ? 'bg-light-success' : 'bg-light-danger')
            .text(isChecked ? 'Activo' : 'Inactivo');
    });
});

$('#enable_phone_blocking').on('change', function() {
    handleProtectedSwitch('enable_phone_blocking', null);
});

$('#enable_security_alerts').on('change', function() {
    handleProtectedSwitch('enable_security_alerts', function() {
        const isChecked = $('#enable_security_alerts').is(':checked');
        $('#badge-alerts')
            .removeClass('bg-light-success bg-light-danger')
            .addClass(isChecked ? 'bg-light-success' : 'bg-light-danger')
            .text(isChecked ? 'Activo' : 'Inactivo');
    });
});

$('#enable_signature_validation').on('change', function() {
    handleProtectedSwitch('enable_signature_validation', updateGeneralSecurityBadge);
});

// SWITCHES NO CRITICOS - Solo advertencia
$('#alert_on_suspicious_activity').on('change', function() {
    handleProtectedSwitch('alert_on_suspicious_activity', null);
});

$('#enable_cors_protection').on('change', function() {
    handleProtectedSwitch('enable_cors_protection', updateGeneralSecurityBadge);
});

$('#enable_helmet_protection').on('change', function() {
    handleProtectedSwitch('enable_helmet_protection', updateGeneralSecurityBadge);
});

$('#trust_proxy').on('change', function() {
    handleProtectedSwitch('trust_proxy', updateGeneralSecurityBadge);
});

$('#slowloris_protection_enabled').on('change', function() {
    handleProtectedSwitch('slowloris_protection_enabled', function() {
        const isChecked = $('#slowloris_protection_enabled').is(':checked');
        $('#badge-slowloris')
            .removeClass('bg-light-success bg-light-danger')
            .addClass(isChecked ? 'bg-light-success' : 'bg-light-danger')
            .text(isChecked ? 'Activo' : 'Inactivo');
    });
});

// Funcion para actualizar badge de Configuración General en tiempo real
function updateGeneralSecurityBadge() {
    const signatureEnabled = $('#enable_signature_validation').is(':checked');
    const corsEnabled = $('#enable_cors_protection').is(':checked');
    const helmetEnabled = $('#enable_helmet_protection').is(':checked');
    const proxyEnabled = $('#trust_proxy').is(':checked');
    const generalSecurityActive = signatureEnabled || corsEnabled || helmetEnabled || proxyEnabled;
    $('#badge-general-security')
        .removeClass('bg-light-success bg-light-danger')
        .addClass(generalSecurityActive ? 'bg-light-success' : 'bg-light-danger')
        .text(generalSecurityActive ? 'Activo' : 'Inactivo');
}

// FUNCION DE INICIALIZACION
function init_security() {
    console.log('[Security] Inicializando modulo de seguridad...');
    loadSecurityConfig();
}

// Exponer funcion globalmente para index.js
window.init_security = init_security;
