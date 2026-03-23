/**
 * Script para gestionar la configuración de horarios
 * Cargado en P_Group
 */

// Cargar configuraciones al iniciar
$(document).ready(function() {
    load_schedule_configs();
});

/**
 * Carga las configuraciones desde la base de datos
 */
function load_schedule_configs() {
    $.ajax({
        url: BASE_URL + 'Schedule_Config/get_configs',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                // Poblar los campos con los valores
                response.data.forEach(function(config) {
                    const input = $('[data-key="' + config.config_key + '"]');

                    if (input.hasClass('config-switch')) {
                        // Es un checkbox switch
                        input.prop('checked', config.config_value == '1');
                    } else if (input.hasClass('config-input')) {
                        // Es un input numérico
                        input.val(config.config_value);
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar configuraciones:', error);
        }
    });
}

/**
 * Guarda todas las configuraciones
 */
$(document).on('click', '#btn-save-configs', function(e) {
    e.preventDefault();

    console.log('🔵 Botón clickeado - Iniciando guardado...');

    const btn = $(this);
    const originalHtml = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Guardando...');

    const configs_to_save = [];

    // Recopilar todos los valores
    console.log('🔍 Buscando elementos .config-input y .config-switch...');
    const elements = $('.config-input, .config-switch');
    console.log('📊 Elementos encontrados: ' + elements.length);

    elements.each(function() {
        const key = $(this).data('key');
        let value;

        if ($(this).hasClass('config-switch')) {
            value = $(this).is(':checked') ? '1' : '0';
        } else {
            value = $(this).val();
        }

        if (key) {
            configs_to_save.push({ key: key, value: value });
            console.log('✅ Agregado: ' + key + ' = ' + value);
        }
    });

    console.log('📦 Total configuraciones a guardar: ' + configs_to_save.length);

    if (configs_to_save.length === 0) {
        console.error('❌ No se encontraron configuraciones para guardar');

        // Mostrar información de debug
        let debugInfo = 'DEBUG INFO:\n';
        debugInfo += 'Elementos con .config-input: ' + $('.config-input').length + '\n';
        debugInfo += 'Elementos con .config-switch: ' + $('.config-switch').length + '\n';
        debugInfo += 'Elementos con [data-key]: ' + $('[data-key]').length + '\n';
        debugInfo += 'Botón existe: ' + $('#btn-save-configs').length + '\n';

        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            html: 'No hay configuraciones para guardar<br><br><small>' + debugInfo.replace(/\n/g, '<br>') + '</small>'
        });
        btn.prop('disabled', false).html(originalHtml);
        return;
    }

    // Guardar cada configuración
    let saved = 0;
    let errors = 0;
    const total = configs_to_save.length;

    configs_to_save.forEach(function(config) {
        $.ajax({
            url: BASE_URL + 'Schedule_Config/update_config',
            type: 'POST',
            data: {
                config_key: config.key,
                config_value: config.value
            },
            dataType: 'json',
            success: function(response) {
                saved++;
                if (saved + errors === total) {
                    finishSaving(btn, originalHtml, errors);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al guardar ' + config.key + ':', error);
                errors++;
                if (saved + errors === total) {
                    finishSaving(btn, originalHtml, errors);
                }
            }
        });
    });
});

/**
 * Finaliza el proceso de guardado
 */
function finishSaving(btn, originalHtml, errors) {
    btn.prop('disabled', false).html(originalHtml);

    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    if (errors === 0) {
        Swal.fire({
            icon: 'success',
            title: 'Guardado',
            text: 'Configuraciones guardadas correctamente',
            timer: 2000,
            showConfirmButton: false
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo errores al guardar algunas configuraciones'
        });
    }
}
