// --
function init_company_info() {
    console.log('[Company Info] Initializing...');
    load_company_info();

    // Form submit handler
    $('#form-company-info').on('submit', function(e) {
        e.preventDefault();
        save_company_info();
    });
}

// --
function load_company_info() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_company_info',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const data = response.data;

                // Sección 1: Contacto
                $('#company_name').val(data.company_name || '');
                $('#company_phone').val(data.company_phone || '');
                $('#company_email').val(data.company_email || '');
                $('#company_website').val(data.company_website || '');

                // Sección 2: Ubicación
                $('#company_address').val(data.company_address || '');
                $('#company_city').val(data.company_city || '');
                $('#company_region').val(data.company_region || '');
                $('#company_country').val(data.company_country || '');

                // Sección 3: Redes Sociales
                $('#social_facebook').val(data.social_facebook || '');
                $('#social_instagram').val(data.social_instagram || '');

                // Sección 4: Horarios
                $('#schedule_weekdays').val(data.schedule_weekdays || '');
                $('#schedule_saturday').val(data.schedule_saturday || '');
                $('#schedule_sunday').val(data.schedule_sunday || '');
                $('#schedule_emergency').val(data.schedule_emergency || '');

                // Sección 5: Bot y Enlaces
                $('#assistant_name').val(data.assistant_name || '');
                $('#google_sheet_id').val(data.google_sheet_id || '');
                $('#google_form_url').val(data.google_form_url || '');
                $('#whatsapp_group_link').val(data.whatsapp_group_link || '');
                $('#media_storage_path').val(data.media_storage_path || '');

                feather.replace();
            } else if (response.status === 'ERROR') {
                // No data yet, show empty form
                console.log('[Company Info] No data found, showing empty form');
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al cargar información de empresa', 'ERROR');
            console.error('[Company Info] Load error:', error);
        }
    });
}

// --
function save_company_info() {
    const formData = $('#form-company-info').serialize();

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/update_company_info',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                load_company_info();
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al guardar información', 'ERROR');
            console.error('[Company Info] Save error:', error);
        }
    });
}
