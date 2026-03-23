// Global variables to store data for filtering
let allSystemMessages = [];

// Current filter value
let currentMessageFilter = '';

// --
function init_messages() {
    console.log('[Messages] Initializing...');
    load_system_messages();

    // Edit button handler
    $(document).on('click', '.btn-edit-message', function() {
        const messageId = $(this).data('id');
        edit_system_message(messageId);
    });

    // Delete button handler
    $(document).on('click', '.btn-delete-message', function() {
        const messageId = $(this).data('id');
        delete_system_message(messageId);
    });

    // Add button handler
    $('#btn-add-message').on('click', function() {
        add_system_message();
    });

    // Save button handler
    $('#btn-save-message').on('click', function() {
        save_system_message();
    });

    // Form submit handler
    $('#form-edit-message').on('submit', function(e) {
        e.preventDefault();
        save_system_message();
    });

    // Filter handler
    $('#filter-messages').on('change', function() {
        filter_system_messages($(this).val());
    });
}

// ==================== SYSTEM MESSAGES ====================

// --
function load_system_messages() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_system_messages',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data && response.data.length > 0) {
                // Store all messages globally for filtering
                allSystemMessages = response.data;

                // Sort by category
                const categoryOrder = {
                    'menu': 1, 'registration': 2, 'contact': 3,
                    'faq': 4, 'error': 5, 'security': 6, 'system': 7
                };
                allSystemMessages.sort(function(a, b) {
                    return (categoryOrder[a.category] || 99) - (categoryOrder[b.category] || 99);
                });

                // Reapply current filter or display all messages
                if (currentMessageFilter) {
                    $('#filter-messages').val(currentMessageFilter);
                    filter_system_messages(currentMessageFilter);
                } else {
                    display_system_messages(allSystemMessages);
                }
            } else {
                allSystemMessages = [];
                const tbody = $('#tbody-messages');
                tbody.html('<tr><td colspan="5" class="text-center">No hay mensajes disponibles</td></tr>');
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar mensajes', 'ERROR');
            console.error('[Messages] Load error:', error);
        }
    });
}

// --
function display_system_messages(messages) {
    const tbody = $('#tbody-messages');
    tbody.empty();

    if (messages && messages.length > 0) {
        messages.forEach(function(message) {
            const statusBadge = message.is_active == 1 ?
                '<span class="badge bg-success">Activo</span>' :
                '<span class="badge bg-secondary">Inactivo</span>';

            // Badge para categoría
            let categoryBadge = '';
            if (message.category === 'menu') {
                categoryBadge = '<span class="badge bg-primary">Menu</span>';
            } else if (message.category === 'registration') {
                categoryBadge = '<span class="badge bg-warning">Registration</span>';
            } else if (message.category === 'contact') {
                categoryBadge = '<span class="badge bg-info">Contact</span>';
            } else if (message.category === 'faq') {
                categoryBadge = '<span class="badge bg-success">FAQ</span>';
            } else if (message.category === 'error') {
                categoryBadge = '<span class="badge bg-danger">Error</span>';
            } else if (message.category === 'security') {
                categoryBadge = '<span class="badge bg-dark">Security</span>';
            } else if (message.category === 'system') {
                categoryBadge = '<span class="badge bg-secondary">System</span>';
            } else {
                categoryBadge = '<span class="badge bg-light text-dark">' + message.category + '</span>';
            }

            // Show label with key below and tooltip if description exists
            let nameWithKey = '';
            if (message.description && message.description.trim() !== '') {
                nameWithKey = '<span class="cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top" title="' + message.description + '"><strong>' + (message.label || message.key) + '</strong></span><br><small class="text-muted"><code>' + message.key + '</code></small>';
            } else {
                nameWithKey = '<strong>' + (message.label || message.key) + '</strong><br><small class="text-muted"><code>' + message.key + '</code></small>';
            }

            const row = '<tr>' +
                '<td>' + nameWithKey + '</td>' +
                '<td>' + categoryBadge + '</td>' +
                '<td><span class="badge bg-light text-dark">' + (message.usage_count || 0) + '</span></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' +
                '<button type="button" class="btn btn-sm btn-primary btn-edit-message me-1" data-id="' + message.id + '">' +
                '<i data-feather="edit-2"></i></button>' +
                '<button type="button" class="btn btn-sm btn-danger btn-delete-message" data-id="' + message.id + '">' +
                '<i data-feather="trash-2"></i></button>' +
                '</td>' +
                '</tr>';
            tbody.append(row);
        });
        feather.replace();

        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    } else {
        tbody.html('<tr><td colspan="5" class="text-center">No hay mensajes para el filtro seleccionado</td></tr>');
    }
}

// --
function filter_system_messages(category) {
    // Save current filter value
    currentMessageFilter = category;

    if (!category || category === '') {
        // Show all messages
        display_system_messages(allSystemMessages);
    } else {
        // Filter by selected category
        const filtered = allSystemMessages.filter(function(message) {
            return message.category === category;
        });
        display_system_messages(filtered);
    }
}

// --
function add_system_message() {
    // Clear form
    $('#message_id').val('');
    $('#message_key').val('').prop('readonly', false);
    $('#message_label').val('');
    $('#message_description').val('');
    $('#message_category').val('');
    $('#message_text').val('');
    $('#message_variables').val('');
    $('#message_is_active').val('1');

    // Change modal title
    $('#modal-message-title').text('Nuevo Mensaje');

    const modal = new bootstrap.Modal(document.getElementById('modal-edit-message'));
    modal.show();
}

// --
function edit_system_message(messageId) {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_system_message/' + messageId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const message = response.data;
                $('#message_id').val(message.id);
                $('#message_key').val(message.key).prop('readonly', true);
                $('#message_label').val(message.label || '');
                $('#message_description').val(message.description || '');
                $('#message_category').val(message.category);
                $('#message_text').val(message.message);

                // Format JSON variables
                let variablesValue = '';
                if (message.variables) {
                    if (typeof message.variables === 'string') {
                        try {
                            const parsed = JSON.parse(message.variables);
                            variablesValue = JSON.stringify(parsed, null, 2);
                        } catch (e) {
                            variablesValue = message.variables;
                        }
                    } else {
                        variablesValue = JSON.stringify(message.variables, null, 2);
                    }
                }
                $('#message_variables').val(variablesValue);

                $('#message_is_active').val(message.is_active);

                // Change modal title
                $('#modal-message-title').text('Editar Mensaje');

                const modal = new bootstrap.Modal(document.getElementById('modal-edit-message'));
                modal.show();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar mensaje', 'ERROR');
            console.error('[Messages] Edit error:', error);
        }
    });
}

// --
function save_system_message() {
    // Validate required fields
    const key = $('#message_key').val().trim();
    const label = $('#message_label').val().trim();
    const category = $('#message_category').val();
    const messageText = $('#message_text').val().trim();

    if (!key) {
        functions.toast_message('warning', 'El campo Key es obligatorio', 'ADVERTENCIA');
        $('#message_key').focus();
        return;
    }

    if (!label) {
        functions.toast_message('warning', 'El campo Nombre es obligatorio', 'ADVERTENCIA');
        $('#message_label').focus();
        return;
    }

    if (!category) {
        functions.toast_message('warning', 'Debe seleccionar una Categoría', 'ADVERTENCIA');
        $('#message_category').focus();
        return;
    }

    if (!messageText) {
        functions.toast_message('warning', 'El campo Mensaje es obligatorio', 'ADVERTENCIA');
        $('#message_text').focus();
        return;
    }

    const formData = {
        message_id: $('#message_id').val(),
        key: key,
        label: label,
        description: $('#message_description').val().trim(),
        category: category,
        message: messageText,
        variables: $('#message_variables').val().trim(),
        is_active: $('#message_is_active').val()
    };

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/save_system_message',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-message')).hide();
                load_system_messages();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al guardar mensaje', 'ERROR');
            console.error('[Messages] Save error:', error);
        }
    });
}

// --
function delete_system_message(messageId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer. El mensaje será eliminado permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'Bot_Configuration/delete_system_message/' + messageId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    functions.toast_message(response.type, response.msg, response.status);
                    if (response.status === 'OK') {
                        load_system_messages();
                    }
                },
                error: function(_xhr, _status, error) {
                    functions.toast_message('error', 'Error al eliminar mensaje', 'ERROR');
                    console.error('[Messages] Delete error:', error);
                }
            });
        }
    });
}
