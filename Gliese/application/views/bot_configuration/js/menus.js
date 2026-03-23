// Global variables to store data for filtering
let allMenuOptions = [];
let allMenuOptionsN3 = [];
let allMenuActions = [];
let allSystemStates = [];

// Current filter values
let currentMenuOptionFilter = '';
let currentMenuOptionN3Filter = '';
let currentMenuActionFilter = '';
let currentSystemStateFilter = '';

// --
function init_menus() {
    console.log('[Menus] Initializing...');
    load_menus();
    load_menu_options();
    load_menu_options_n3();
    load_menu_actions();
    load_system_states();

    // Menu edit button handler
    $(document).on('click', '.btn-edit-menu', function() {
        const menuId = $(this).data('id');
        edit_menu(menuId);
    });

    // Menu option edit button handler
    $(document).on('click', '.btn-edit-menu-option', function() {
        const optionId = $(this).data('id');
        edit_menu_option(optionId);
    });

    // Menu action edit button handler
    $(document).on('click', '.btn-edit-menu-action', function() {
        const actionId = $(this).data('id');
        edit_menu_action(actionId);
    });

    // Menu delete button handler
    $(document).on('click', '.btn-delete-menu', function() {
        const menuId = $(this).data('id');
        delete_menu(menuId);
    });

    // Menu option delete button handler
    $(document).on('click', '.btn-delete-menu-option', function() {
        const optionId = $(this).data('id');
        delete_menu_option(optionId);
    });

    // N3 option edit button handler
    $(document).on('click', '.btn-edit-option-n3', function() {
        const optionId = $(this).data('id');
        edit_option_n3(optionId);
    });

    // N3 option delete button handler
    $(document).on('click', '.btn-delete-option-n3', function() {
        const optionId = $(this).data('id');
        delete_option_n3(optionId);
    });

    // Menu action delete button handler
    $(document).on('click', '.btn-delete-menu-action', function() {
        const actionId = $(this).data('id');
        delete_menu_action(actionId);
    });

    // State edit button handler
    $(document).on('click', '.btn-edit-state', function() {
        const stateId = $(this).data('id');
        edit_system_state(stateId);
    });

    // State delete button handler
    $(document).on('click', '.btn-delete-state', function() {
        const stateId = $(this).data('id');
        delete_system_state(stateId);
    });

    // Add button handlers
    $('#btn-add-menu').on('click', function() {
        add_menu();
    });

    $('#btn-add-option').on('click', function() {
        add_menu_option();
    });

    $('#btn-add-option-n3').on('click', function() {
        add_option_n3();
    });

    $('#btn-add-action').on('click', function() {
        add_menu_action();
    });

    $('#btn-add-state').on('click', function() {
        add_system_state();
    });

    // Save button handlers (direct button click, not form submit)
    $('#btn-save-menu').on('click', function() {
        save_menu();
    });

    $('#btn-save-option').on('click', function() {
        save_menu_option();
    });

    $('#btn-save-option-n3').on('click', function() {
        save_option_n3();
    });

    $('#btn-save-action').on('click', function() {
        save_menu_action();
    });

    $('#btn-save-state').on('click', function() {
        save_system_state();
    });

    // Form submit handlers (for Enter key)
    $('#form-edit-menu').on('submit', function(e) {
        e.preventDefault();
        save_menu();
    });

    $('#form-edit-menu-option').on('submit', function(e) {
        e.preventDefault();
        save_menu_option();
    });

    $('#form-edit-option-n3').on('submit', function(e) {
        e.preventDefault();
        save_option_n3();
    });

    $('#form-edit-menu-action').on('submit', function(e) {
        e.preventDefault();
        save_menu_action();
    });

    $('#form-edit-state').on('submit', function(e) {
        e.preventDefault();
        save_system_state();
    });

    // Filter menu options handler
    $('#filter-menu-options').on('change', function() {
        filter_menu_options($(this).val());
    });

    // Filter menu actions handler
    $('#filter-menu-actions').on('change', function() {
        filter_menu_actions($(this).val());
    });

    // Filter system states handler
    $('#filter-system-states').on('change', function() {
        filter_system_states($(this).val());
    });

    // Filter N3 options handler
    $('#filter-submenu-n3').on('change', function() {
        filter_menu_options_n3($(this).val());
    });

    // Auto-fill State Key when creating new menu
    $('#menu_key').on('input', function() {
        // Solo auto-rellenar si es un menú nuevo (no tiene ID)
        if (!$('#menu_id').val()) {
            const keyValue = $(this).val().trim();
            $('#menu_state_key').empty().append('<option value="' + keyValue + '">' + keyValue + '</option>').val(keyValue);
        }
    });

    // Auto-fill State Key when creating new option
    $('#option_key').on('input', function() {
        // Solo auto-rellenar si es una opción nueva (no tiene ID)
        if (!$('#option_id').val()) {
            const keyValue = $(this).val().trim();
            $('#option_state_key').empty().append('<option value="' + keyValue + '">' + keyValue + '</option>').val(keyValue);
        }
    });

    // Auto-fill State Key when creating new option N3
    $('#option_n3_key').on('input', function() {
        // Solo auto-rellenar si es una opción nueva (no tiene ID)
        if (!$('#option_n3_id').val()) {
            const keyValue = $(this).val().trim();
            $('#option_n3_state_key').empty().append('<option value="' + keyValue + '">' + keyValue + '</option>').val(keyValue);
        }
    });

    // Update order when menu is selected (both creating and editing)
    $(document).on('change', '#option_menu_id', function() {
        const menuId = $(this).val();

        // Actualizar orden cuando se selecciona un menú (crear o editar)
        if (menuId) {
            $.ajax({
                url: BASE_URL + 'Bot_Configuration/get_next_order_for_menu/' + menuId,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'OK' && response.data && response.data.next_order) {
                        $('#option_order').val(response.data.next_order);
                    }
                },
                error: function() {
                    console.error('[MenuOptions] Error al obtener siguiente orden');
                }
            });
        }
    });

    // Update order when parent option N3 is selected (both creating and editing)
    $(document).on('change', '#option_n3_parent_id', function() {
        const parentOptionId = $(this).val();

        // Actualizar orden cuando se selecciona un sub menú padre (crear o editar)
        if (parentOptionId) {
            $.ajax({
                url: BASE_URL + 'Bot_Configuration/get_next_order_for_parent_option/' + parentOptionId,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'OK' && response.data && response.data.next_order) {
                        $('#option_n3_order').val(response.data.next_order);
                    }
                },
                error: function() {
                    console.error('[OptionsN3] Error al obtener siguiente orden');
                }
            });
        }
    });
}

// ==================== MENUS ====================

// --
function load_menus() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menus',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const tbody = $('#tbody-menus');
            tbody.empty();

            if (response.status === 'OK' && response.data && response.data.length > 0) {
                // Sort by order ascending
                response.data.sort(function(a, b) {
                    const orderA = parseInt(a.order) || 0;
                    const orderB = parseInt(b.order) || 0;
                    return orderA - orderB;
                });

                response.data.forEach(function(menu) {
                    const statusBadge = menu.is_active == 1 ?
                        '<span class="badge bg-success">Activo</span>' :
                        '<span class="badge bg-secondary">Inactivo</span>';

                    const registrationBadge = menu.allows_registration == 1 ?
                        '<span class="badge bg-info">Sí</span>' :
                        '<span class="badge bg-light text-dark">No</span>';

                    const actionDisplay = menu.action_name ?
                        '<span class="badge bg-primary">' + menu.action_name + '</span><br><small class="text-muted">' + menu.action_key + '</small>' :
                        '<span class="text-muted">-</span>';

                    // Show state_key below label if exists
                    const labelWithState = menu.state_key
                        ? '<strong>' + menu.label + '</strong><br><small class="text-muted">Estado: <code>' + menu.state_key + '</code></small>'
                        : '<strong>' + menu.label + '</strong><br><small class="text-muted text-secondary">Sin estado</small>';

                    const menuColor = menu.color || '#3498db';

                    const row = '<tr style="border-left: 4px solid ' + menuColor + ';">' +
                        '<td>' + menu.order + '</td>' +
                        '<td>' + labelWithState + '</td>' +
                        '<td>' + actionDisplay + '</td>' +
                        '<td>' + registrationBadge + '</td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td>' +
                        '<button type="button" class="btn btn-sm btn-primary btn-edit-menu me-1" data-id="' + menu.id + '">' +
                        '<i data-feather="edit-2"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-danger btn-delete-menu" data-id="' + menu.id + '">' +
                        '<i data-feather="trash-2"></i></button>' +
                        '</td>' +
                        '</tr>';
                    tbody.append(row);
                });
                feather.replace();
            } else {
                tbody.html('<tr><td colspan="6" class="text-center">No hay menús disponibles</td></tr>');
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar menús', 'ERROR');
            console.error('[Menus] Load error:', error);
        }
    });
}

// --
function load_menu_action_dropdown() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_actions?category=menu_principal',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const select = $('#menu_action_id');
            select.find('option:not(:first)').remove();

            if (response.status === 'OK' && response.data) {
                response.data.forEach(function(action) {
                    select.append('<option value="' + action.key + '">' + action.name + ' (' + action.key + ')</option>');
                });
            }
        }
    });
}

// --
function load_state_key_dropdown() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_system_states',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const select = $('#menu_state_key');
            select.find('option:not(:first)').remove();

            if (response.status === 'OK' && response.data) {
                // Filtrar solo estados de tipo menu, sub_menu_n2 y sub_menu_n3
                const menuStates = response.data.filter(function(state) {
                    return state.type === 'menu' || state.type === 'sub_menu_n2' || state.type === 'sub_menu_n3';
                });

                menuStates.forEach(function(state) {
                    select.append('<option value="' + state.key + '">' + state.name + ' (' + state.key + ')</option>');
                });
            }
        }
    });
}

// --
function add_menu() {
    // Load actions dropdown (NO cargar states, se auto-rellena)
    load_menu_action_dropdown();

    // Get next order number
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menus',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            let nextOrder = 0;

            if (response.status === 'OK' && response.data && response.data.length > 0) {
                // Find the maximum order value
                const maxOrder = Math.max(...response.data.map(menu => parseInt(menu.order) || 0));
                nextOrder = maxOrder + 1;
            }

            // Clear form
            $('#menu_id').val('');
            $('#menu_key').val('').prop('readonly', false);
            $('#menu_state_key').val('').prop('disabled', true); // Deshabilitar
            $('#menu_state_name').val('');
            $('#menu_label').val('');
            $('#menu_description').val('');
            $('#menu_icon').val('');
            $('#menu_color').val('#87dde3');
            $('#menu_order').val(nextOrder);
            $('#menu_allows_registration').val('0');
            $('#menu_action_id').val('');
            $('#menu_is_active').val('1');

            // Mostrar campo de nombre del estado
            $('#state_name_col').show();

            // Change modal title
            $('#modal-menu-title').text('Nuevo Menú');

            const modal = new bootstrap.Modal(document.getElementById('modal-edit-menu'));
            modal.show();
        },
        error: function(_xhr, _status, error) {
            // If error, still open modal with default order 0
            $('#menu_id').val('');
            $('#menu_key').val('').prop('readonly', false);
            $('#menu_state_key').val('').prop('disabled', true);
            $('#menu_state_name').val('');
            $('#menu_label').val('');
            $('#menu_description').val('');
            $('#menu_icon').val('');
            $('#menu_color').val('#87dde3');
            $('#menu_order').val('0');
            $('#menu_allows_registration').val('0');
            $('#menu_action_id').val('');
            $('#menu_is_active').val('1');

            // Mostrar campo de nombre del estado
            $('#state_name_col').show();

            $('#modal-menu-title').text('Nuevo Menú');

            const modal = new bootstrap.Modal(document.getElementById('modal-edit-menu'));
            modal.show();
        }
    });
}

// --
function edit_menu(menuId) {
    // Load dropdowns first
    load_menu_action_dropdown();
    load_state_key_dropdown();

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu/' + menuId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const menu = response.data;
                $('#menu_id').val(menu.id);
                $('#menu_key').val(menu.key).prop('readonly', true);
                $('#menu_state_key').prop('disabled', false); // Habilitar para editar
                $('#menu_label').val(menu.label);
                $('#menu_description').val(menu.description);
                $('#menu_icon').val(menu.icon);
                $('#menu_color').val(menu.color);
                $('#menu_order').val(menu.order);
                $('#menu_allows_registration').val(menu.allows_registration == 1 ? '1' : '0');

                // Ocultar campo nombre del estado (solo al crear)
                $('#state_name_col').hide();
                $('#menu_state_name').val('');

                // Set action_key and state_key after dropdowns are loaded (use setTimeout to ensure dropdowns are populated)
                setTimeout(function() {
                    $('#menu_action_id').val(menu.action_key || '');
                    $('#menu_state_key').val(menu.state_key || '');
                }, 100);

                $('#menu_is_active').val(menu.is_active);

                // Change modal title
                $('#modal-menu-title').text('Editar Menú');

                const modal = new bootstrap.Modal(document.getElementById('modal-edit-menu'));
                modal.show();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar menú', 'ERROR');
            console.error('[Menus] Edit error:', error);
        }
    });
}

// --
function save_menu() {
    // Validate required fields
    const key = $('#menu_key').val().trim();
    const label = $('#menu_label').val().trim();
    const order = $('#menu_order').val();
    const menuId = $('#menu_id').val();

    if (!key) {
        functions.toast_message('warning', 'El campo Key es obligatorio', 'ADVERTENCIA');
        $('#menu_key').focus();
        return;
    }

    if (!label) {
        functions.toast_message('warning', 'El campo Etiqueta es obligatorio', 'ADVERTENCIA');
        $('#menu_label').focus();
        return;
    }

    if (!order && order !== '0') {
        functions.toast_message('warning', 'El campo Orden es obligatorio', 'ADVERTENCIA');
        $('#menu_order').focus();
        return;
    }

    // Obtener valores de estado con protección contra null
    const stateKeyValue = $('#menu_state_key').val();
    const stateNameValue = $('#menu_state_name').val();

    const formData = {
        menu_id: menuId,
        key: key,
        state_key: stateKeyValue ? stateKeyValue.trim() : null,
        state_name: stateNameValue ? stateNameValue.trim() : null,
        label: label,
        description: ($('#menu_description').val() || '').trim(),
        icon: ($('#menu_icon').val() || '').trim(),
        color: ($('#menu_color').val() || '').trim(),
        order: order,
        allows_registration: $('#menu_allows_registration').val(),
        action_key: $('#menu_action_id').val() || '',
        is_active: $('#menu_is_active').val()
    };

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/save_menu',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-menu')).hide();
                load_menus();
                // Reload menu options dropdown
                load_menu_options();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al guardar menú', 'ERROR');
            console.error('[Menus] Save error:', error);
        }
    });
}

// --
function delete_menu(menuId) {
    // First check if this menu has options
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/check_menu_options/' + menuId,
        method: 'GET',
        dataType: 'json',
        success: function(checkResponse) {
            if (checkResponse.status === 'OK' && checkResponse.has_options) {
                // Has options - show warning with count
                show_delete_menu_with_options_dialog(menuId, checkResponse.options_count);
            } else {
                // No options - show simple confirmation
                show_simple_delete_menu_dialog(menuId);
            }
        },
        error: function() {
            // On error, show simple confirmation as fallback
            show_simple_delete_menu_dialog(menuId);
        }
    });
}

// Helper function: Simple delete menu confirmation (no options)
function show_simple_delete_menu_dialog(menuId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer. El menú será eliminado permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            perform_delete_menu(menuId);
        }
    });
}

// Helper function: Delete menu confirmation with options
function show_delete_menu_with_options_dialog(menuId, optionsCount) {
    Swal.fire({
        title: '⚠️ Este menú tiene ' + optionsCount + ' opción(es)',
        html: '¿Qué deseas hacer?<br><br>' +
              '<strong>Solo este menú:</strong> Las ' + optionsCount + ' opción(es) quedarán sin menú<br>' +
              '<strong>Eliminar todo:</strong> Se eliminarán ' + optionsCount + ' opción(es)',
        icon: 'warning',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        denyButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '🗑️💥 Eliminar todo',
        denyButtonText: '🗑️ Solo este menú',
        cancelButtonText: '❌ Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Delete menu with options (CASCADE)
            perform_delete_menu(menuId, true);
        } else if (result.isDenied) {
            // Delete only menu (orphan options)
            perform_delete_menu(menuId, false);
        }
    });
}

// Helper function: Perform the actual menu delete
function perform_delete_menu(menuId, deleteOptions) {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/delete_menu/' + menuId,
        method: 'POST',
        data: { delete_options: deleteOptions },
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                load_menus();
                load_menu_options();
            }
        },
        error: function(xhr, status, error) {
            console.error('[Menus] Delete error:', {
                status: status,
                error: error,
                responseText: xhr.responseText
            });
            functions.toast_message('error', 'Error al eliminar menú: ' + error, 'ERROR');
        }
    });
}

// ==================== MENU OPTIONS ====================

// --
function load_menu_options() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_options',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data && response.data.length > 0) {
                // Filtrar solo las opciones de nivel 2
                allMenuOptions = response.data.filter(function(option) {
                    return option.level == 2;
                });

                // Sort by menu_id, then by order
                allMenuOptions.sort(function(a, b) {
                    if (a.menu_id !== b.menu_id) {
                        return a.menu_id - b.menu_id;
                    }
                    const orderA = parseInt(a.order) || 0;
                    const orderB = parseInt(b.order) || 0;
                    return orderA - orderB;
                });

                // Populate filter dropdown with unique menus
                populate_menu_filter();

                // Reapply current filter or display all options
                if (currentMenuOptionFilter) {
                    $('#filter-menu-options').val(currentMenuOptionFilter);
                    filter_menu_options(currentMenuOptionFilter);
                } else {
                    display_menu_options(allMenuOptions);
                }
            } else {
                allMenuOptions = [];
                const tbody = $('#tbody-options');
                tbody.html('<tr><td colspan="6" class="text-center">No hay opciones de menú disponibles</td></tr>');
            }

            // Update menu dropdown in option modal
            load_menu_dropdown();
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar opciones de menú', 'ERROR');
            console.error('[MenuOptions] Load error:', error);
        }
    });
}

// --
function populate_menu_filter() {
    const filterSelect = $('#filter-menu-options');
    filterSelect.find('option:not(:first)').remove();

    // Get unique menus from options
    const uniqueMenus = {};
    allMenuOptions.forEach(function(option) {
        if (option.menu_id && option.menu_label) {
            uniqueMenus[option.menu_id] = option.menu_label;
        }
    });

    // Add menu options to filter
    Object.keys(uniqueMenus).sort(function(a, b) {
        return uniqueMenus[a].localeCompare(uniqueMenus[b]);
    }).forEach(function(menuId) {
        filterSelect.append('<option value="' + menuId + '">' + uniqueMenus[menuId] + '</option>');
    });

    // Add "Sin menú" option if there are orphaned options
    const hasOrphans = allMenuOptions.some(function(option) {
        return !option.menu_id || option.menu_id === null;
    });
    if (hasOrphans) {
        filterSelect.append('<option value="null">Sin menú</option>');
    }
}

// --
function display_menu_options(options) {
    const tbody = $('#tbody-options');
    tbody.empty();

    if (options && options.length > 0) {
        options.forEach(function(option) {
            const statusBadge = option.is_active == 1 ?
                '<span class="badge bg-success">Activo</span>' :
                '<span class="badge bg-secondary">Inactivo</span>';

            const actionDisplay = option.action_name ?
                '<span class="badge bg-primary">' + option.action_name + '</span><br><small class="text-muted">' + option.action_key + '</small>' :
                '<span class="text-muted">-</span>';

            // Show state_key below label if exists
            const labelWithState = option.state_key
                ? '<strong>' + option.label + '</strong><br><small class="text-muted">Estado: <code>' + option.state_key + '</code></small>'
                : '<strong>' + option.label + '</strong><br><small class="text-muted text-secondary">Sin estado</small>';

            const menuColor = option.menu_color || '#3498db';

            const row = '<tr style="border-left: 4px solid ' + menuColor + ';">' +
                '<td>' + (option.menu_label || '-') + '</td>' +
                '<td>' + labelWithState + '</td>' +
                '<td>' + option.order + '</td>' +
                '<td>' + actionDisplay + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' +
                '<button type="button" class="btn btn-sm btn-primary btn-edit-menu-option me-1" data-id="' + option.id + '">' +
                '<i data-feather="edit-2"></i></button>' +
                '<button type="button" class="btn btn-sm btn-danger btn-delete-menu-option" data-id="' + option.id + '">' +
                '<i data-feather="trash-2"></i></button>' +
                '</td>' +
                '</tr>';
            tbody.append(row);
        });
        feather.replace();
    } else {
        tbody.html('<tr><td colspan="6" class="text-center">No hay opciones para el filtro seleccionado</td></tr>');
    }
}

// --
function filter_menu_options(menuId) {
    // Save current filter value
    currentMenuOptionFilter = menuId;

    if (!menuId || menuId === '') {
        // Show all options
        display_menu_options(allMenuOptions);
    } else if (menuId === 'null') {
        // Show orphaned options (without menu)
        const orphans = allMenuOptions.filter(function(option) {
            return !option.menu_id || option.menu_id === null;
        });
        display_menu_options(orphans);
    } else {
        // Filter by selected menu
        const filtered = allMenuOptions.filter(function(option) {
            return option.menu_id == menuId;
        });
        display_menu_options(filtered);
    }
}

// --
function load_menu_dropdown() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menus',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const select = $('#option_menu_id');
            select.find('option:not(:first)').remove();

            if (response.status === 'OK' && response.data) {
                response.data.forEach(function(menu) {
                    select.append('<option value="' + menu.id + '">' + menu.label + '</option>');
                });
            }
        }
    });
}

// --
function load_action_dropdown() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_actions?category=menu_opcion',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const select = $('#option_action_id');
            select.find('option:not(:first)').remove();

            if (response.status === 'OK' && response.data) {
                response.data.forEach(function(action) {
                    select.append('<option value="' + action.key + '">' + action.name + ' (' + action.key + ')</option>');
                });
            }
        }
    });
}

// --
function load_option_state_dropdown() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_system_states',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const select = $('#option_state_key');
            select.find('option:not(:first)').remove();

            if (response.status === 'OK' && response.data) {
                // Filtrar solo estados de tipo 'sub_menu_n2'
                response.data.forEach(function(state) {
                    if (state.type === 'sub_menu_n2') {
                        select.append('<option value="' + state.key + '">' + state.name + ' (' + state.key + ')</option>');
                    }
                });
            }
        }
    });
}

// --
function add_menu_option() {
    // Load dropdowns first (NO cargar states, se auto-rellena)
    load_menu_dropdown();
    load_action_dropdown();

    // Clear form
    $('#option_id').val('');
    $('#option_menu_id').val('');
    $('#option_key').val('');
    $('#option_key').prop('readonly', false); // Desbloquear key al crear
    $('#option_state_key').val('').prop('disabled', true); // Deshabilitar
    $('#option_state_name').val('');
    $('#option_text').val('');
    $('#option_description').val('');
    $('#option_icon').val('');
    $('#option_order').val('1'); // Inicialmente 1
    $('#option_level').val('2'); // Nivel 2 por defecto
    $('#option_action_id').val('');
    $('#option_is_active').val('1');

    // Mostrar campo de nombre del estado
    $('#option_state_name_col').show();

    // Change modal title
    $('#modal-option-title').text('Nueva Opción del Sub Menú');

    const modal = new bootstrap.Modal(document.getElementById('modal-edit-option'));
    modal.show();
}

// --
function edit_menu_option(optionId) {
    // Load dropdowns first
    load_menu_dropdown();
    load_action_dropdown();
    load_option_state_dropdown();

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_option/' + optionId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const option = response.data;
                $('#option_id').val(option.id);
                $('#option_parent_id').val(option.parent_option_id || '');
                $('#option_key').val(option.key);
                $('#option_key').prop('readonly', true); // Bloquear key al editar
                $('#option_state_key').prop('disabled', false); // Habilitar para editar
                $('#option_text').val(option.label);
                $('#option_description').val(option.description || '');
                $('#option_icon').val(option.icon || '');
                $('#option_order').val(option.order);
                $('#option_level').val(option.level || 1);
                $('#option_path').val(option.path || '');
                $('#option_is_active').val(option.is_active);

                // Ocultar campo nombre del estado (solo al crear)
                $('#option_state_name_col').hide();

                // Set dropdown values after they are loaded (use setTimeout to ensure dropdowns are populated)
                setTimeout(function() {
                    $('#option_menu_id').val(option.menu_id);
                    $('#option_action_id').val(option.action_key || '');
                    $('#option_state_key').val(option.state_key || '');
                }, 150);

                // Change modal title
                $('#modal-option-title').text('Editar Opción del Sub Menú');

                const modal = new bootstrap.Modal(document.getElementById('modal-edit-option'));
                modal.show();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar opción de menú', 'ERROR');
            console.error('[MenuOptions] Edit error:', error);
        }
    });
}

// --
function save_menu_option() {
    // Validate required fields
    const menuId = $('#option_menu_id').val();
    const optionKey = $('#option_key').val().trim();
    const optionText = $('#option_text').val().trim();
    const order = $('#option_order').val();
    const optionId = $('#option_id').val();

    if (!menuId) {
        functions.toast_message('warning', 'Debe seleccionar un Menú', 'ADVERTENCIA');
        $('#option_menu_id').focus();
        return;
    }

    if (!optionKey) {
        functions.toast_message('warning', 'El campo Key es obligatorio', 'ADVERTENCIA');
        $('#option_key').focus();
        return;
    }

    if (!optionText) {
        functions.toast_message('warning', 'El campo Etiqueta es obligatorio', 'ADVERTENCIA');
        $('#option_text').focus();
        return;
    }

    // Si es una opción nueva, validar nombre del estado
    if (!optionId) {
        const stateName = $('#option_state_name').val().trim();
        if (!stateName) {
            functions.toast_message('warning', 'El campo Nombre del Estado es obligatorio', 'ADVERTENCIA');
            $('#option_state_name').focus();
            return;
        }
    }

    if (!order) {
        functions.toast_message('warning', 'El campo Orden es obligatorio', 'ADVERTENCIA');
        $('#option_order').focus();
        return;
    }

    const formData = {
        option_id: optionId,
        menu_id: menuId,
        parent_option_id: $('#option_parent_id').val() || null,
        option_key: optionKey,
        state_key: $('#option_state_key').val() || null,
        state_name: $('#option_state_name').val().trim(), // Nuevo campo
        label: optionText,
        description: $('#option_description').val().trim(),
        icon: $('#option_icon').val().trim(),
        color: '#2ecc71',
        option_order: order,
        level: $('#option_level').val() || 1,
        path: $('#option_path').val() || null,
        action_key: $('#option_action_id').val() || null,
        is_active: $('#option_is_active').val()
    };

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/save_menu_option',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-option')).hide();
                load_menu_options();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al guardar opción de menú', 'ERROR');
            console.error('[MenuOptions] Save error:', error);
        }
    });
}

// --
function delete_menu_option(optionId) {
    // First check if this option has children
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/check_option_children/' + optionId,
        method: 'GET',
        dataType: 'json',
        success: function(checkResponse) {
            if (checkResponse.status === 'OK' && checkResponse.has_children) {
                // Has children - show special dialog with 3 options
                show_delete_with_children_dialog(optionId, checkResponse.children_count);
            } else {
                // No children - show simple confirmation
                show_simple_delete_option_dialog(optionId);
            }
        },
        error: function() {
            // On error, show simple confirmation as fallback
            show_simple_delete_option_dialog(optionId);
        }
    });
}

// Helper function: Simple delete confirmation (no children)
function show_simple_delete_option_dialog(optionId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer. La opción será eliminada permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            perform_delete_menu_option(optionId, true);
        }
    });
}

// Helper function: Delete confirmation with children
function show_delete_with_children_dialog(optionId, childrenCount) {
    Swal.fire({
        title: '⚠️ Esta opción tiene ' + childrenCount + ' sub-opción(es)',
        html: '¿Qué deseas hacer?<br><br>' +
              '<strong>Solo esta opción:</strong> Las sub-opciones quedarán sin padre<br>' +
              '<strong>Eliminar todo:</strong> Se eliminarán ' + childrenCount + ' sub-opción(es)',
        icon: 'warning',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        denyButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '🗑️💥 Eliminar todo',
        denyButtonText: '🗑️ Solo esta opción',
        cancelButtonText: '❌ Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Delete with children (CASCADE)
            perform_delete_menu_option(optionId, true);
        } else if (result.isDenied) {
            // Delete only this option (orphan children)
            perform_delete_menu_option(optionId, false);
        }
    });
}

// Helper function: Perform the actual delete
function perform_delete_menu_option(optionId, deleteChildren) {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/delete_menu_option/' + optionId,
        method: 'POST',
        data: { delete_children: deleteChildren },
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                load_menu_options();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al eliminar opción', 'ERROR');
            console.error('[MenuOptions] Delete error:', error);
        }
    });
}

// ==================== MENU ACTIONS ====================

// --
function load_menu_actions() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_actions',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data && response.data.length > 0) {
                // Store all actions globally for filtering
                allMenuActions = response.data;

                // Ordenar por categoría: menu_principal -> menu_opcion -> ambos
                const categoryOrder = { 'menu_principal': 1, 'menu_opcion': 2, 'ambos': 3 };
                allMenuActions.sort(function(a, b) {
                    return (categoryOrder[a.category] || 99) - (categoryOrder[b.category] || 99);
                });

                // Reapply current filter or display all actions
                if (currentMenuActionFilter) {
                    $('#filter-menu-actions').val(currentMenuActionFilter);
                    filter_menu_actions(currentMenuActionFilter);
                } else {
                    display_menu_actions(allMenuActions);
                }
            } else {
                allMenuActions = [];
                const tbody = $('#tbody-actions');
                tbody.html('<tr><td colspan="5" class="text-center">No hay acciones disponibles</td></tr>');
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar acciones', 'ERROR');
            console.error('[MenuActions] Load error:', error);
        }
    });
}

// --
function display_menu_actions(actions) {
    const tbody = $('#tbody-actions');
    tbody.empty();

    if (actions && actions.length > 0) {
        actions.forEach(function(action) {
            const statusBadge = action.is_active == 1 ?
                '<span class="badge bg-success">Activo</span>' :
                '<span class="badge bg-secondary">Inactivo</span>';

            // Badge para categoría
            let categoryBadge = '';
            if (action.category === 'menu_principal') {
                categoryBadge = '<span class="badge bg-primary">Menú Principal</span>';
            } else if (action.category === 'menu_opcion') {
                categoryBadge = '<span class="badge bg-warning">Sub Menú</span>';
            } else if (action.category === 'ambos') {
                categoryBadge = '<span class="badge bg-info">Ambos</span>';
            }

            const row = '<tr>' +
                '<td><strong>' + action.name + '</strong></td>' +
                '<td>' + categoryBadge + '</td>' +
                '<td><span class="badge bg-secondary">' + action.handler_type + '</span></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' +
                '<button type="button" class="btn btn-sm btn-primary btn-edit-menu-action me-1" data-id="' + action.id + '">' +
                '<i data-feather="edit-2"></i></button>' +
                '<button type="button" class="btn btn-sm btn-danger btn-delete-menu-action" data-id="' + action.id + '">' +
                '<i data-feather="trash-2"></i></button>' +
                '</td>' +
                '</tr>';
            tbody.append(row);
        });
        feather.replace();
    } else {
        tbody.html('<tr><td colspan="5" class="text-center">No hay acciones para el filtro seleccionado</td></tr>');
    }
}

// --
function filter_menu_actions(category) {
    // Save current filter value
    currentMenuActionFilter = category;

    if (!category || category === '') {
        // Show all actions
        display_menu_actions(allMenuActions);
    } else {
        // Filter by selected category
        const filtered = allMenuActions.filter(function(action) {
            return action.category === category;
        });
        display_menu_actions(filtered);
    }
}

// --
function add_menu_action() {
    // Clear form
    $('#action_id').val('');
    $('#action_key').val('');
    $('#action_title').val('');
    $('#action_description').val('');
    $('#action_type').val('service_method');
    $('#action_category').val('menu_opcion');
    $('#action_configuration').val('');
    $('#action_requires_auth').val('0');
    $('#action_is_active').val('1');

    // Change modal title
    $('#modal-action-title').text('Nueva Acción');

    const modal = new bootstrap.Modal(document.getElementById('modal-edit-action'));
    modal.show();
}

// --
function edit_menu_action(actionId) {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_action/' + actionId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const action = response.data;
                $('#action_id').val(action.id);
                $('#action_key').val(action.key);
                $('#action_title').val(action.name);
                $('#action_description').val(action.description);
                $('#action_type').val(action.handler_type);
                $('#action_category').val(action.category);

                // Format JSON configuration
                let configValue = '';
                if (action.handler_config) {
                    if (typeof action.handler_config === 'string') {
                        // If it's already a string, try to parse and re-format it
                        try {
                            const parsed = JSON.parse(action.handler_config);
                            configValue = JSON.stringify(parsed, null, 2);
                        } catch (e) {
                            configValue = action.handler_config;
                        }
                    } else {
                        // If it's an object, stringify it with formatting
                        configValue = JSON.stringify(action.handler_config, null, 2);
                    }
                }
                $('#action_configuration').val(configValue);

                $('#action_requires_auth').val(action.requires_auth == 1 ? '1' : '0');
                $('#action_is_active').val(action.is_active);

                // Change modal title
                $('#modal-action-title').text('Editar Acción');

                const modal = new bootstrap.Modal(document.getElementById('modal-edit-action'));
                modal.show();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar acción', 'ERROR');
            console.error('[MenuActions] Edit error:', error);
        }
    });
}

// --
function save_menu_action() {
    // Validate required fields
    const actionKey = $('#action_key').val().trim();
    const title = $('#action_title').val().trim();
    const actionType = $('#action_type').val().trim();

    if (!actionKey) {
        functions.toast_message('warning', 'El campo Key es obligatorio', 'ADVERTENCIA');
        $('#action_key').focus();
        return;
    }

    if (!title) {
        functions.toast_message('warning', 'El campo Título es obligatorio', 'ADVERTENCIA');
        $('#action_title').focus();
        return;
    }

    if (!actionType) {
        functions.toast_message('warning', 'El campo Tipo es obligatorio', 'ADVERTENCIA');
        $('#action_type').focus();
        return;
    }

    const formData = {
        action_id: $('#action_id').val(),
        action_key: actionKey,
        title: title,
        description: $('#action_description').val().trim(),
        action_type: actionType,
        category: $('#action_category').val(),
        configuration: $('#action_configuration').val().trim(),
        requires_auth: $('#action_requires_auth').val(),
        is_active: $('#action_is_active').val()
    };

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/save_menu_action',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-action')).hide();
                load_menu_actions();
                // Reload action dropdowns in menu and option forms
                load_menu_action_dropdown();
                load_action_dropdown();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al guardar acción', 'ERROR');
            console.error('[MenuActions] Save error:', error);
        }
    });
}

// --
function delete_menu_action(actionId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer. La acción será eliminada permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'Bot_Configuration/delete_menu_action/' + actionId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    functions.toast_message(response.type, response.msg, response.status);
                    if (response.status === 'OK') {
                        load_menu_actions();
                        load_action_dropdown();
                    }
                },
                error: function(_xhr, _status, error) {
                    functions.toast_message('error', 'Error al eliminar acción', 'ERROR');
                    console.error('[MenuActions] Delete error:', error);
                }
            });
        }
    });
}

// ==================== SYSTEM STATES ====================

// --
function load_system_states() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_all_system_states',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data && response.data.length > 0) {
                // Store all states globally for filtering
                allSystemStates = response.data;

                // Sort by type: menu -> form -> registration -> conversation -> system
                const typeOrder = { 'menu': 1, 'form': 2, 'registration': 3, 'conversation': 4, 'system': 5 };
                allSystemStates.sort(function(a, b) {
                    return (typeOrder[a.type] || 99) - (typeOrder[b.type] || 99);
                });

                // Reapply current filter or display all states
                if (currentSystemStateFilter) {
                    $('#filter-system-states').val(currentSystemStateFilter);
                    filter_system_states(currentSystemStateFilter);
                } else {
                    display_system_states(allSystemStates);
                }
            } else {
                allSystemStates = [];
                const tbody = $('#tbody-states');
                tbody.html('<tr><td colspan="4" class="text-center">No hay estados disponibles</td></tr>');
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar estados', 'ERROR');
            console.error('[States] Load error:', error);
        }
    });
}

// --
function display_system_states(states) {
    const tbody = $('#tbody-states');
    tbody.empty();

    if (states && states.length > 0) {
        states.forEach(function(state) {
            const statusBadge = (state.is_active == 1 || state.is_active === '1' || state.is_active === true) ?
                '<span class="badge bg-success">Activo</span>' :
                '<span class="badge bg-secondary">Inactivo</span>';

            // Badge for type with different colors
            let typeBadge = '';
            if (state.type === 'menu') {
                typeBadge = '<span class="badge bg-primary">Menu</span>';
            } else if (state.type === 'sub_menu_n2') {
                typeBadge = '<span class="badge bg-primary" style="opacity: 0.8;">Sub menu N2</span>';
            } else if (state.type === 'sub_menu_n3') {
                typeBadge = '<span class="badge bg-primary" style="opacity: 0.6;">Sub menu N3</span>';
            } else if (state.type === 'form') {
                typeBadge = '<span class="badge bg-info">Form</span>';
            } else if (state.type === 'registration') {
                typeBadge = '<span class="badge bg-warning">Registration</span>';
            } else if (state.type === 'conversation') {
                typeBadge = '<span class="badge bg-success">Conversation</span>';
            } else if (state.type === 'system') {
                typeBadge = '<span class="badge bg-secondary">System</span>';
            } else {
                typeBadge = '<span class="badge bg-dark">' + state.type + '</span>';
            }

            // Show key
            const keyWithTimeout = '<code>' + state.key + '</code>';

            // Prepare description for tooltip
            const description = state.description || 'Sin descripción';
            const tooltipAttr = 'data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="' + description + '"';

            const row = '<tr>' +
                '<td><strong class="state-name-tooltip" style="cursor: help;" ' + tooltipAttr + '>' + state.name + '</strong><br><small class="text-muted">' + keyWithTimeout + '</small></td>' +
                '<td>' + typeBadge + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' +
                '<button type="button" class="btn btn-sm btn-primary btn-edit-state me-1" data-id="' + state.id + '">' +
                '<i data-feather="edit-2"></i></button>' +
                '<button type="button" class="btn btn-sm btn-danger btn-delete-state" data-id="' + state.id + '">' +
                '<i data-feather="trash-2"></i></button>' +
                '</td>' +
                '</tr>';
            tbody.append(row);
        });

        // Initialize Bootstrap tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        feather.replace();
    } else {
        tbody.html('<tr><td colspan="4" class="text-center">No hay estados para el filtro seleccionado</td></tr>');
    }
}

// --
function filter_system_states(type) {
    // Save current filter value
    currentSystemStateFilter = type;

    if (!type || type === '') {
        // Show all states
        display_system_states(allSystemStates);
    } else {
        // Filter by selected type
        const filtered = allSystemStates.filter(function(state) {
            return state.type === type;
        });
        display_system_states(filtered);
    }
}

// --
function add_system_state() {
    // Clear form
    $('#state_id').val('');
    $('#state_key').val('');
    $('#state_name').val('');
    $('#state_description').val('');
    $('#state_type').val('');
    $('#state_is_active').val('1');

    // Enable key field for new states
    $('#state_key').prop('readonly', false);

    // Change modal title
    $('#modal-state-title').text('Nuevo Estado');

    const modal = new bootstrap.Modal(document.getElementById('modal-edit-state'));
    modal.show();
}

// --
function edit_system_state(stateId) {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_system_state/' + stateId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const state = response.data;
                $('#state_id').val(state.id);
                $('#state_key').val(state.key);
                $('#state_name').val(state.name);
                $('#state_description').val(state.description || '');
                $('#state_type').val(state.type);
                $('#state_is_active').val(state.is_active);

                // Disable key field when editing existing state to avoid errors
                $('#state_key').prop('readonly', true);

                // Change modal title
                $('#modal-state-title').text('Editar Estado');

                const modal = new bootstrap.Modal(document.getElementById('modal-edit-state'));
                modal.show();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar estado', 'ERROR');
            console.error('[States] Edit error:', error);
        }
    });
}

// --
function save_system_state() {
    // Validate required fields
    const key = $('#state_key').val().trim();
    const name = $('#state_name').val().trim();
    const type = $('#state_type').val();

    if (!key) {
        functions.toast_message('warning', 'El campo Key es obligatorio', 'ADVERTENCIA');
        $('#state_key').focus();
        return;
    }

    if (!name) {
        functions.toast_message('warning', 'El campo Nombre es obligatorio', 'ADVERTENCIA');
        $('#state_name').focus();
        return;
    }

    if (!type) {
        functions.toast_message('warning', 'Debe seleccionar un Tipo', 'ADVERTENCIA');
        $('#state_type').focus();
        return;
    }

    const formData = {
        state_id: $('#state_id').val(),
        key: key,
        name: name,
        description: $('#state_description').val().trim(),
        type: type,
        is_active: $('#state_is_active').val()
    };

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/save_system_state',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-state')).hide();
                load_system_states();
                // Reload state dropdown in menu and option forms
                load_state_key_dropdown();
                load_option_state_dropdown();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al guardar estado', 'ERROR');
            console.error('[States] Save error:', error);
        }
    });
}

// --
function delete_system_state(stateId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer. El estado será eliminado permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'Bot_Configuration/delete_system_state/' + stateId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    functions.toast_message(response.type, response.msg, response.status);
                    if (response.status === 'OK') {
                        load_system_states();
                        load_state_key_dropdown();
                    }
                },
                error: function(_xhr, _status, error) {
                    functions.toast_message('error', 'Error al eliminar estado', 'ERROR');
                    console.error('[States] Delete error:', error);
                }
            });
        }
    });
}


// ==================== SUB MENUS N3 ====================

// ==================== SUB MENUS N3 ====================

function load_menu_options_n3() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_options_n3',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data && response.data.length > 0) {
                // Store all N3 options globally for filtering
                allMenuOptionsN3 = response.data;

                // Sort by parent_option_id, then by order
                allMenuOptionsN3.sort(function(a, b) {
                    if (a.parent_option_id !== b.parent_option_id) {
                        return (a.parent_option_id || 0) - (b.parent_option_id || 0);
                    }
                    const orderA = parseInt(a.order) || 0;
                    const orderB = parseInt(b.order) || 0;
                    return orderA - orderB;
                });

                // Populate filter dropdown with N2 options
                populate_n3_filter();

                // Reapply current filter or display all N3 options
                if (currentMenuOptionN3Filter) {
                    $('#filter-submenu-n3').val(currentMenuOptionN3Filter);
                    filter_menu_options_n3(currentMenuOptionN3Filter);
                } else {
                    display_menu_options_n3(allMenuOptionsN3);
                }
            } else {
                allMenuOptionsN3 = [];
                const tbody = $('#tbody-submenu-n3');
                tbody.html('<tr><td colspan="6" class="text-center">No hay opciones N3 disponibles</td></tr>');
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar opciones N3', 'ERROR');
            console.error('[OptionsN3] Load error:', error);
        }
    });
}

// --
function populate_n3_filter() {
    const filterSelect = $('#filter-submenu-n3');
    filterSelect.find('option:not(:first)').remove();

    // Get unique N2 parents from N3 options
    const uniqueParents = {};
    allMenuOptionsN3.forEach(function(option) {
        if (option.parent_option_id && option.parent_label) {
            uniqueParents[option.parent_option_id] = option.parent_label;
        }
    });

    // Add N2 parent options to filter
    Object.keys(uniqueParents).sort(function(a, b) {
        return uniqueParents[a].localeCompare(uniqueParents[b]);
    }).forEach(function(parentId) {
        filterSelect.append('<option value="' + parentId + '">' + uniqueParents[parentId] + '</option>');
    });

    // Add "Sin padre" option if there are orphaned N3 options
    const hasOrphans = allMenuOptionsN3.some(function(option) {
        return !option.parent_option_id || option.parent_option_id === null;
    });
    if (hasOrphans) {
        filterSelect.append('<option value="null">Sin padre</option>');
    }
}

// --
function display_menu_options_n3(options) {
    const tbody = $('#tbody-submenu-n3');
    tbody.empty();

    if (options && options.length > 0) {
        options.forEach(function(option) {
            const statusBadge = option.is_active == 1 ?
                '<span class="badge bg-success">Activo</span>' :
                '<span class="badge bg-secondary">Inactivo</span>';

            const actionBadge = option.action_name ?
                '<span class="badge bg-info">' + option.action_name + '</span><br><small class="text-muted">' + option.action_key_value + '</small>' :
                '<span class="badge bg-secondary">Sin acción</span>';

            const menuColor = option.menu_color || '#3498db';

            const row = '<tr style="border-left: 4px solid ' + menuColor + ';">' +
                '<td><strong>' + (option.parent_label || 'Sin padre') + '</strong></td>' +
                '<td>' + option.label + '<br><small class="text-muted">Estado: <span class="badge bg-light text-dark">' + option.key + '</span></small></td>' +
                '<td>' + option.order + '</td>' +
                '<td>' + actionBadge + '</td>' +
                '<td>' + statusBadge + '</td>' +
                '<td>' +
                '<button type="button" class="btn btn-sm btn-primary btn-edit-option-n3 me-1" data-id="' + option.id + '">' +
                '<i data-feather="edit-2"></i></button>' +
                '<button type="button" class="btn btn-sm btn-danger btn-delete-option-n3" data-id="' + option.id + '">' +
                '<i data-feather="trash-2"></i></button>' +
                '</td>' +
                '</tr>';
            tbody.append(row);
        });
        feather.replace();
    } else {
        tbody.html('<tr><td colspan="6" class="text-center">No hay opciones para el filtro seleccionado</td></tr>');
    }
}

// --
function filter_menu_options_n3(parentId) {
    // Save current filter value
    currentMenuOptionN3Filter = parentId;

    if (!parentId || parentId === '') {
        // Show all N3 options
        display_menu_options_n3(allMenuOptionsN3);
    } else if (parentId === 'null') {
        // Show orphaned N3 options (without parent)
        const orphans = allMenuOptionsN3.filter(function(option) {
            return !option.parent_option_id || option.parent_option_id === null;
        });
        display_menu_options_n3(orphans);
    } else {
        // Filter by selected N2 parent
        const filtered = allMenuOptionsN3.filter(function(option) {
            return option.parent_option_id == parentId;
        });
        display_menu_options_n3(filtered);
    }
}

function load_n2_dropdown() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_options',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const select = $('#option_n3_parent_id');
            select.find('option:not(:first)').remove();

            if (response.status === 'OK' && response.data) {
                const n2Options = response.data.filter(opt => opt.level == 2);
                n2Options.forEach(function(option) {
                    select.append('<option value="' + option.id + '">' + option.label + ' (' + option.key + ')</option>');
                });
            }
        }
    });
}

function load_n3_action_dropdown() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_actions?category=menu_opcion',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const select = $('#option_n3_action_id');
            select.find('option:not(:first)').remove();

            if (response.status === 'OK' && response.data) {
                response.data.forEach(function(action) {
                    select.append('<option value="' + action.key + '">' + action.name + ' (' + action.key + ')</option>');
                });
            }
        }
    });
}

function load_n3_state_dropdown() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_system_states',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const select = $('#option_n3_state_key');
            select.find('option:not(:first)').remove();

            if (response.status === 'OK' && response.data) {
                // Filtrar solo estados de tipo 'sub_menu_n3'
                response.data.forEach(function(state) {
                    if (state.type === 'sub_menu_n3') {
                        select.append('<option value="' + state.key + '">' + state.name + ' (' + state.key + ')</option>');
                    }
                });
            }
        }
    });
}

function add_option_n3() {
    load_n2_dropdown();
    load_n3_action_dropdown();

    $('#option_n3_id').val('');
    $('#option_n3_parent_id').val('');
    $('#option_n3_menu_id').val('');
    $('#option_n3_key').val('');
    $('#option_n3_key').prop('readonly', false);
    $('#option_n3_state_key').val('').prop('disabled', true);
    $('#option_n3_state_name').val('');
    $('#option_n3_text').val('');
    $('#option_n3_description').val('');
    $('#option_n3_icon').val('');
    $('#option_n3_order').val('1');
    $('#option_n3_action_id').val('');
    $('#option_n3_is_active').val('1');
    $('#option_n3_level').val('3');
    $('#option_n3_path').val('');

    $('#option_n3_state_name_col').show();
    $('#modal-option-n3-title').text('Nuevo Sub Menú N3');

    const modal = new bootstrap.Modal(document.getElementById('modal-edit-option-n3'));
    modal.show();
}

function edit_option_n3(optionId) {
    load_n2_dropdown();
    load_n3_action_dropdown();
    load_n3_state_dropdown();

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_option/' + optionId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const option = response.data;
                $('#option_n3_id').val(option.id);
                $('#option_n3_parent_id').val(option.parent_option_id || '');
                $('#option_n3_menu_id').val(option.menu_id);
                $('#option_n3_key').val(option.key);
                $('#option_n3_key').prop('readonly', true);
                $('#option_n3_state_key').prop('disabled', false);
                $('#option_n3_text').val(option.label);
                $('#option_n3_description').val(option.description || '');
                $('#option_n3_icon').val(option.icon || '');
                $('#option_n3_order').val(option.order);
                $('#option_n3_level').val(option.level || 3);
                $('#option_n3_path').val(option.path || '');
                $('#option_n3_is_active').val(option.is_active);

                $('#option_n3_state_name_col').hide();

                setTimeout(function() {
                    $('#option_n3_parent_id').val(option.parent_option_id || '');
                    $('#option_n3_action_id').val(option.action_key || '');
                    $('#option_n3_state_key').val(option.state_key || '');
                }, 150);

                $('#modal-option-n3-title').text('Editar Sub Menú N3');

                const modal = new bootstrap.Modal(document.getElementById('modal-edit-option-n3'));
                modal.show();
            }
        },
        error: function(_xhr, _status, error) {
            functions.toast_message('error', 'Error al cargar opción N3', 'ERROR');
            console.error('[OptionsN3] Edit error:', error);
        }
    });
}

function save_option_n3() {
    const parentId = $('#option_n3_parent_id').val();
    const optionKey = $('#option_n3_key').val().trim();
    const optionText = $('#option_n3_text').val().trim();
    const order = $('#option_n3_order').val();
    const optionId = $('#option_n3_id').val();

    if (!parentId) {
        functions.toast_message('warning', 'Debe seleccionar un sub menú padre', 'ADVERTENCIA');
        return;
    }

    if (!optionKey || !optionText) {
        functions.toast_message('warning', 'Complete los campos obligatorios', 'ADVERTENCIA');
        return;
    }

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_menu_option/' + parentId,
        method: 'GET',
        dataType: 'json',
        success: function(parentResponse) {
            if (parentResponse.status === 'OK' && parentResponse.data) {
                const parentOption = parentResponse.data;

                const formData = {
                    option_id: optionId || null,
                    menu_id: parentOption.menu_id,
                    parent_option_id: parentId,
                    option_key: optionKey,
                    state_key: $('#option_n3_state_key').val() || null,
                    state_name: $('#option_n3_state_name').val() || null,
                    label: optionText,
                    description: $('#option_n3_description').val() || '',
                    icon: $('#option_n3_icon').val() || '',
                    color: '#2ecc71',
                    option_order: order || 1,
                    level: 3,
                    path: $('#option_n3_path').val() || null,
                    action_key: $('#option_n3_action_id').val() || null,
                    is_active: $('#option_n3_is_active').val() || 1
                };

                $.ajax({
                    url: BASE_URL + 'Bot_Configuration/save_menu_option',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'OK') {
                            functions.toast_message('success', response.msg, 'ÉXITO');
                            $('#modal-edit-option-n3').modal('hide');
                            load_menu_options_n3();
                            load_n2_dropdown();
                        } else {
                            functions.toast_message('error', response.msg, 'ERROR');
                        }
                    },
                    error: function(_xhr, _status, error) {
                        functions.toast_message('error', 'Error al guardar opción N3', 'ERROR');
                        console.error('[OptionsN3] Save error:', error);
                    }
                });
            }
        },
        error: function() {
            functions.toast_message('error', 'Error al obtener información del padre', 'ERROR');
        }
    });
}

function delete_option_n3(optionId) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción eliminará la opción N3 y su estado asociado. No se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'Bot_Configuration/delete_menu_option/' + optionId,
                method: 'POST',
                data: { delete_children: true },
                dataType: 'json',
                success: function(response) {
                    functions.toast_message(response.type, response.msg, response.status);
                    if (response.status === 'OK') {
                        load_menu_options_n3();
                        load_system_states(); // Recargar estados para reflejar la eliminación
                    }
                },
                error: function(_xhr, _status, error) {
                    functions.toast_message('error', 'Error al eliminar opción N3', 'ERROR');
                    console.error('[OptionsN3] Delete error:', error);
                }
            });
        }
    });
}
