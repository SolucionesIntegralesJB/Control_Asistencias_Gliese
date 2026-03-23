// Store original values for change detection
let originalEmailData = {};

// --
function init_email() {
    console.log('[Email Config] Initializing...');
    load_email_config();
    load_email_templates();
    load_email_domains();

    // Form submit handler
    $('#form-email-config').on('submit', function(e) {
        e.preventDefault();
        save_email_config();
    });

    // Toggle password visibility
    $('#toggle-password').on('click', function() {
        const passwordField = $('#email_password');
        const eyeIcon = $('#eye-icon');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            eyeIcon.attr('data-feather', 'eye-off');
        } else {
            passwordField.attr('type', 'password');
            eyeIcon.attr('data-feather', 'eye');
        }
        feather.replace();
    });

    // Toggle Google private key visibility
    $('#toggle-google-key').on('click', function() {
        const keyField = $('#google_private_key');
        const eyeIcon = $('#eye-icon-google');

        if (keyField.attr('type') === 'password') {
            keyField.attr('type', 'text');
            eyeIcon.attr('data-feather', 'eye-off');
        } else {
            keyField.attr('type', 'password');
            eyeIcon.attr('data-feather', 'eye');
        }
        feather.replace();
    });

    // Test email button
    $('#btn-test-email').on('click', function() {
        send_test_email();
    });

    // Template modal handlers
    $('#btn-save-template').on('click', function() {
        save_template();
    });

    // Domain modal handlers
    $('#btn-add-domain').on('click', function() {
        open_domain_modal();
    });

    $('#btn-save-domain').on('click', function() {
        save_domain();
    });
}

// --
function load_email_config() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_email_config',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const data = response.data;

                // Load values into fields
                $('#email_user').val(data.email_user);
                $('#email_password').val(data.email_password || '');
                $('#smtp_server').val(data.smtp_server);
                $('#smtp_port').val(data.smtp_port);
                $('#sender_name').val(data.sender_name);
                $('#google_sheets_polling_interval_ms').val(data.google_sheets_polling_interval_ms);
                $('#google_service_account_email').val(data.google_service_account_email);
                $('#google_private_key').val(data.google_private_key || '');
                $('#auto_email_sending').val(data.auto_email_sending);

                // Store original values for change detection
                originalEmailData = {
                    email_user: data.email_user || '',
                    email_password: data.email_password || '',
                    smtp_server: data.smtp_server || '',
                    smtp_port: data.smtp_port || '',
                    sender_name: data.sender_name || '',
                    google_sheets_polling_interval_ms: data.google_sheets_polling_interval_ms || '',
                    google_service_account_email: data.google_service_account_email || '',
                    google_private_key: data.google_private_key || '',
                    auto_email_sending: data.auto_email_sending || ''
                };

                feather.replace();
            } else if (response.status === 'ERROR') {
                console.log('[Email Config] No data found, showing empty form');
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al cargar configuración de email', 'ERROR');
            console.error('[Email Config] Load error:', error);
        }
    });
}

// --
function save_email_config() {
    // Get current values
    const currentData = {
        email_user: $('#email_user').val() || '',
        email_password: $('#email_password').val() || '',
        smtp_server: $('#smtp_server').val() || '',
        smtp_port: $('#smtp_port').val() || '',
        sender_name: $('#sender_name').val() || '',
        google_sheets_polling_interval_ms: $('#google_sheets_polling_interval_ms').val() || '',
        google_service_account_email: $('#google_service_account_email').val() || '',
        google_private_key: $('#google_private_key').val() || '',
        auto_email_sending: $('#auto_email_sending').val() || ''
    };

    // Build form data - only include fields that changed
    const formData = {};
    let hasChanges = false;

    // Check each non-encrypted field for changes
    if (currentData.email_user !== originalEmailData.email_user) {
        formData.email_user = currentData.email_user;
        hasChanges = true;
    }

    if (currentData.smtp_server !== originalEmailData.smtp_server) {
        formData.smtp_server = currentData.smtp_server;
        hasChanges = true;
    }

    if (currentData.smtp_port !== originalEmailData.smtp_port) {
        formData.smtp_port = currentData.smtp_port;
        hasChanges = true;
    }

    if (currentData.sender_name !== originalEmailData.sender_name) {
        formData.sender_name = currentData.sender_name;
        hasChanges = true;
    }

    if (currentData.google_sheets_polling_interval_ms !== originalEmailData.google_sheets_polling_interval_ms) {
        formData.google_sheets_polling_interval_ms = currentData.google_sheets_polling_interval_ms;
        hasChanges = true;
    }

    if (currentData.google_service_account_email !== originalEmailData.google_service_account_email) {
        formData.google_service_account_email = currentData.google_service_account_email;
        hasChanges = true;
    }

    if (currentData.auto_email_sending !== originalEmailData.auto_email_sending) {
        formData.auto_email_sending = currentData.auto_email_sending;
        hasChanges = true;
    }

    // Encrypted fields - only send if changed AND not empty
    if (currentData.email_password !== originalEmailData.email_password &&
        currentData.email_password.trim() !== '') {
        formData.email_password = currentData.email_password;
        hasChanges = true;
        console.log('[Email] Sending email_password (changed)');
    }

    if (currentData.google_private_key !== originalEmailData.google_private_key &&
        currentData.google_private_key.trim() !== '') {
        formData.google_private_key = currentData.google_private_key;
        hasChanges = true;
        console.log('[Email] Sending google_private_key (changed)');
    }

    // If no changes detected, don't submit
    if (!hasChanges) {
        console.log('[Email] No changes detected, skipping save');
        functions.toast_message('info', 'No se detectaron cambios', 'INFO');
        return;
    }

    console.log('[Email] Saving changes:', Object.keys(formData));

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/update_email_config',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                load_email_config();
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al guardar configuración', 'ERROR');
            console.error('[Email Config] Save error:', error);
        }
    });
}

// --
function send_test_email() {
    const btn = $('#btn-test-email');
    const originalText = btn.html();

    btn.prop('disabled', true);
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Enviando...');

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/send_test_email',
        method: 'POST',
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al enviar email de prueba', 'ERROR');
        },
        complete: function() {
            btn.prop('disabled', false);
            btn.html(originalText);
            feather.replace();
        }
    });
}

// ============ EMAIL TEMPLATES ============

// --
function load_email_templates() {
    console.log('[Email Templates] Loading...');

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_email_templates',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('[Email Templates] Response:', response);

            if (response.status === 'OK' && response.data) {
                render_templates_table(response.data);
            } else {
                $('#tbody-templates').html('<tr><td colspan="5" class="text-center text-muted">No hay plantillas disponibles</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('[Email Templates] Load error:', error);
            $('#tbody-templates').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar plantillas</td></tr>');
        }
    });
}

// --
function render_templates_table(templates) {
    let html = '';

    templates.forEach(function(template) {
        const variables = JSON.parse(template.available_variables || '[]');
        const variablesHtml = variables.map(v => `<code>{${v}}</code>`).join(' ');
        const statusBadge = template.active == 1
            ? '<span class="badge bg-success">Activa</span>'
            : '<span class="badge bg-secondary">Inactiva</span>';

        html += `
            <tr>
                <td><strong>${template.type}</strong></td>
                <td>${template.subject}</td>
                <td><small>${variablesHtml}</small></td>
                <td>${statusBadge}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="open_template_modal(${template.id})" title="Editar">
                        <i data-feather="edit-2"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    $('#tbody-templates').html(html);
    feather.replace();
}

// --
function open_template_modal(templateId) {
    console.log('[Email Templates] Opening modal for template:', templateId);

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_email_template/' + templateId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const template = response.data;

                $('#template_id').val(template.id);
                $('#template_type').val(template.type);
                $('#template_subject').val(template.subject);
                $('#template_content').val(template.content);
                $('#template_active').val(template.active == 1 ? '1' : '0');

                const modal = new bootstrap.Modal(document.getElementById('modal-edit-template'));
                modal.show();
            } else {
                functions.toast_message('error', 'Error al cargar plantilla', 'ERROR');
            }
        },
        error: function(xhr, status, error) {
            console.error('[Email Templates] Load error:', error);
            functions.toast_message('error', 'Error al cargar plantilla', 'ERROR');
        }
    });
}

// --
function save_template() {
    const formData = {
        id: $('#template_id').val(),
        subject: $('#template_subject').val(),
        content: $('#template_content').val(),
        active: $('#template_active').val()
    };

    console.log('[Email Templates] Saving:', formData);

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/update_email_template',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);

            if (response.status === 'OK') {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-template')).hide();
                load_email_templates();
            }
        },
        error: function(xhr, status, error) {
            console.error('[Email Templates] Save error:', error);
            functions.toast_message('error', 'Error al guardar plantilla', 'ERROR');
        }
    });
}

// ============ EMAIL DOMAINS ============

// --
function load_email_domains() {
    console.log('[Email Domains] Loading...');

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_email_domains',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('[Email Domains] Response:', response);

            if (response.status === 'OK' && response.data) {
                render_domains_table(response.data);
            } else {
                $('#tbody-domains').html('<tr><td colspan="5" class="text-center text-muted">No hay dominios configurados</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('[Email Domains] Load error:', error);
            $('#tbody-domains').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar dominios</td></tr>');
        }
    });
}

// --
function render_domains_table(domains) {
    let html = '';

    domains.forEach(function(domain) {
        const allowedBadge = domain.is_allowed == 1
            ? '<span class="badge bg-success">Sí</span>'
            : '<span class="badge bg-danger">No</span>';

        const corporateBadge = domain.is_corporate == 1
            ? '<span class="badge bg-info">Sí</span>'
            : '<span class="badge bg-secondary">No</span>';

        html += `
            <tr>
                <td><strong>${domain.domain}</strong></td>
                <td>${domain.description || '-'}</td>
                <td>${allowedBadge}</td>
                <td>${corporateBadge}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="open_domain_modal(${domain.id})" title="Editar">
                        <i data-feather="edit-2"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="delete_domain(${domain.id})" title="Eliminar">
                        <i data-feather="trash-2"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    $('#tbody-domains').html(html);
    feather.replace();
}

// --
function open_domain_modal(domainId = null) {
    console.log('[Email Domains] Opening modal for domain:', domainId);

    // Limpiar formulario
    $('#form-edit-domain')[0].reset();
    $('#domain_id').val('');
    $('#domain_is_allowed').val('1');
    $('#domain_is_corporate').val('0');

    if (domainId) {
        // Modo edición
        $('#modal-domain-title').text('Editar Dominio');

        $.ajax({
            url: BASE_URL + 'Bot_Configuration/get_email_domain/' + domainId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'OK' && response.data) {
                    const domain = response.data;

                    $('#domain_id').val(domain.id);
                    $('#domain_name').val(domain.domain);
                    $('#domain_description').val(domain.description);
                    $('#domain_is_allowed').val(domain.is_allowed == 1 ? '1' : '0');
                    $('#domain_is_corporate').val(domain.is_corporate == 1 ? '1' : '0');

                    const modal = new bootstrap.Modal(document.getElementById('modal-edit-domain'));
                    modal.show();
                } else {
                    functions.toast_message('error', 'Error al cargar dominio', 'ERROR');
                }
            },
            error: function(xhr, status, error) {
                console.error('[Email Domains] Load error:', error);
                functions.toast_message('error', 'Error al cargar dominio', 'ERROR');
            }
        });
    } else {
        // Modo creación
        $('#modal-domain-title').text('Agregar Dominio');
        const modal = new bootstrap.Modal(document.getElementById('modal-edit-domain'));
        modal.show();
    }
}

// --
function save_domain() {
    const domainId = $('#domain_id').val();
    const formData = {
        domain: $('#domain_name').val().trim().toLowerCase(),
        description: $('#domain_description').val(),
        is_allowed: $('#domain_is_allowed').val(),
        is_corporate: $('#domain_is_corporate').val()
    };

    if (domainId) {
        formData.id = domainId;
    }

    console.log('[Email Domains] Saving:', formData);

    const url = domainId
        ? BASE_URL + 'Bot_Configuration/update_email_domain'
        : BASE_URL + 'Bot_Configuration/create_email_domain';

    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);

            if (response.status === 'OK') {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-domain')).hide();
                load_email_domains();
            }
        },
        error: function(xhr, status, error) {
            console.error('[Email Domains] Save error:', error);
            functions.toast_message('error', 'Error al guardar dominio', 'ERROR');
        }
    });
}

// --
function delete_domain(domainId) {
    console.log('[Email Domains] Deleting:', domainId);

    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer. El dominio será eliminado permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'Bot_Configuration/delete_email_domain/' + domainId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    functions.toast_message(response.type, response.msg, response.status);

                    if (response.status === 'OK') {
                        load_email_domains();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('[Email Domains] Delete error:', error);
                    functions.toast_message('error', 'Error al eliminar dominio', 'ERROR');
                }
            });
        }
    });
}
