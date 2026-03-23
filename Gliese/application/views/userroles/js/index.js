// ===========================================================================
// USER ROLES - CONFIGURATION MODULE (TABS: Roles + Security)
// ===========================================================================

// ============================================
// TAB MANAGEMENT
// ============================================

function loadPartial(partial) {
    const targetId = `${partial}-content`;

    $.ajax({
        url: BASE_URL + 'UserRoles/load_partial',
        type: 'POST',
        data: { partial: partial },
        success: function (html) {
            $(`#${targetId}`).html(html);
            feather.replace();

            // Initialize tab-specific functions
            if (partial === 'security') {
                initSecurityTab();
            } else if (partial === 'reports') {
                initReportsTab();
            }
        },
        error: function () {
            $(`#${targetId}`).html('<div class="alert alert-danger">Error al cargar el contenido.</div>');
        }
    });
}

// ============================================
// ROLES TAB (Original functionality)
// ============================================

function destroy_datatable_user_roles() {
    if ($.fn.DataTable.isDataTable('#datatables-user-roles')) {
        $('#datatables-user-roles').DataTable().destroy();
    }
}

function refresh_datatable_user_roles() {
    $('#datatables-user-roles').DataTable().ajax.reload(null, false);
}

function load_datatable_user_roles() {
    destroy_datatable_user_roles();
    let dataTable = $('#datatables-user-roles').DataTable({
        ajax: {
            url: BASE_URL + 'UserRoles/get_user_roles',
            cache: false,
        },
        columns: [
            { data: 'description' },
            {
                class: 'center',
                render: function (data, type, row, meta) {
                    return (
                        '<button class="btn btn-sm btn-info btn-round btn-icon btn_update_user_role" data-process-key="'+ row.id +'">' +
                        feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
                        '</button>'
                        + ' ' +
                        '<button  class="btn btn-sm btn-danger btn-round btn-icon btn_delete_user_role" data-process-key="'+ row.id +'">' +
                        feather.icons['trash-2'].toSvg({ class: 'font-small-4' }) +
                        '</button>'
                    );
                }
            },
        ],
        dom: functions.head_datatable(),
        buttons: functions.custom_buttons_datatable([0,1], '#create_user_role_modal'),
        language: { url: BASE_URL + 'public/assets/json/languaje-es.json' }
    })

    dataTable.on('xhr', function() {
        var data = dataTable.ajax.json();
        functions.toast_message(data.type, data.msg, data.status)
    });
}

function create_user_role(form) {
    $('#btn_create_user_role').prop('disabled', true);
    let params = new FormData(form);
    params.append('permission', JSON.stringify(get_values_for_checkbox('create_user_role_modal', 'menu')));

    $.ajax({
        url: BASE_URL + 'UserRoles/create_user_role',
        type: 'POST',
        data: params,
        dataType: 'json',
        contentType: false,
        processData: false,
        cache: false,
        success: function(data) {
            functions.toast_message(data.type, data.msg, data.status)
            if (data.status === 'OK') {
                $('#create_user_role_modal').modal('hide');
                form.reset();
                setTimeout(function() {
                    refresh_datatable_user_roles();
                }, 300);
                $('#btn_create_user_role').prop('disabled', false);
            } else {
                $('#btn_create_user_role').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al crear el rol: ' + error, 'ERROR');
            $('#btn_create_user_role').prop('disabled', false);
        }
    })
}

function update_user_role(form) {
    $('#btn_update_user_role').prop('disabled', true);
    let params = new FormData(form);
    params.append('permission', JSON.stringify(get_values_for_checkbox('update_user_role_modal', 'menu')));

    $.ajax({
        url: BASE_URL + 'UserRoles/update_user_role',
        type: 'POST',
        data: params,
        dataType: 'json',
        contentType: false,
        processData: false,
        cache: false,
        success: function(data) {
            functions.toast_message(data.type, data.msg, data.status);
            if (data.status === 'OK') {
                $('#update_user_role_modal').modal('hide');
                form.reset();
                setTimeout(function() {
                    refresh_datatable_user_roles();
                }, 300);
                $('#btn_update_user_role').prop('disabled', false);
            } else {
                $('#btn_update_user_role').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al actualizar el rol: ' + error, 'ERROR');
            $('#btn_update_user_role').prop('disabled', false);
        }
    })
}

function delete_user_role(id_user_role) {
    $.ajax({
        url: BASE_URL + 'UserRoles/delete_user_role',
        type: 'POST',
        data: JSON.stringify({ id_user_role: id_user_role }),
        dataType: 'json',
        contentType: 'application/json',
        cache: false,
        success: function(data) {
            functions.toast_message(data.type, data.msg, data.status);
            if (data.status === 'OK') {
                setTimeout(function() {
                    refresh_datatable_user_roles();
                }, 300);
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al eliminar el rol: ' + error, 'ERROR');
        }
    })
}

function get_values_for_checkbox(modal, permission_name) {
    let checkboxes = $(`#${modal} input[name*="${permission_name}"]:checked`);
    let values = [];

    checkboxes.each(function() {
        values.push({
            id_sub_menu: $(this).val(),
            status: 1
        });
    });

    return values;
}

function get_and_show_update_user_role(id_user_role) {
    $.ajax({
        url: BASE_URL + 'UserRoles/get_menu',
        type: 'GET',
        success: function(menuData) {
            if (menuData.status === 'OK') {
                renderMenuPermissions('update', menuData.data, id_user_role);
            }
        }
    });
}

function renderMenuPermissions(mode, menuData, id_user_role) {
    const modalId = mode === 'create' ? 'create_role_menu' : 'update_role_menu';
    const containerId = `#${modalId}`;

    // Group menu items
    const groupedMenu = {};
    menuData.forEach(item => {
        if (!groupedMenu[item.description_menu]) {
            groupedMenu[item.description_menu] = {
                id: item.id_menu,
                submenus: []
            };
        }
        groupedMenu[item.description_menu].submenus.push(item);
    });

    let html = '';
    Object.keys(groupedMenu).forEach(menuName => {
        html += `<div class="col-12 mb-2">
            <h6 class="fw-bold">${menuName}</h6>
            <div class="row ms-2">`;

        groupedMenu[menuName].submenus.forEach(submenu => {
            const checkboxId = `${mode}_menu_${submenu.id_sub_menu}`;
            html += `<div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="menu_${submenu.id_sub_menu}"
                           value="${submenu.id_sub_menu}" id="${checkboxId}">
                    <label class="form-check-label" for="${checkboxId}">
                        ${submenu.description_sub_menu}
                    </label>
                </div>
            </div>`;
        });

        html += `</div></div>`;
    });

    $(containerId).html(html);

    // If update mode, load existing permissions
    if (mode === 'update' && id_user_role) {
        $.ajax({
            url: BASE_URL + 'UserRoles/get_permissions_by_user_role?id_user_role=' + id_user_role,
            type: 'GET',
            success: function(permData) {
                if (permData.status === 'OK') {
                    permData.data.forEach(perm => {
                        $(`#update_menu_${perm.id_sub_menu}`).prop('checked', true);
                    });
                }
            }
        });

        // Load role description
        $.ajax({
            url: BASE_URL + 'UserRoles/get_user_roles',
            type: 'GET',
            success: function(rolesData) {
                if (rolesData.status === 'OK') {
                    const role = rolesData.data.find(r => r.id == id_user_role);
                    if (role) {
                        $('#update_user_role_form input[name="description"]').val(role.description);
                        $('#update_user_role_form input[name="id_user_role"]').val(role.id);
                    }
                }
            }
        });
    }
}

// ============================================
// SECURITY TAB (2FA)
// ============================================

function initSecurityTab() {
    load2FAStatus();
    loadTokensConfig();
    loadCodeExpirationTime(); // Cargar tiempo de expiración actual

    // Rate Limiting
    loadRateLimitStatus();
    loadRateLimitConfig();

    // Event listeners
    $(document).off('click', '#btn-enable-2fa').on('click', '#btn-enable-2fa', function() {
        showEnable2FAModal();
    });

    $(document).off('click', '#btn-disable-2fa').on('click', '#btn-disable-2fa', function() {
        showDisable2FAModal();
    });

    $(document).off('click', '#btn-confirm-enable-2fa').on('click', '#btn-confirm-enable-2fa', function() {
        enable2FA();
    });

    $(document).off('click', '#btn-confirm-disable-2fa').on('click', '#btn-confirm-disable-2fa', function() {
        disable2FA();
    });

    $(document).off('click', '#btn-save-tokens-config').on('click', '#btn-save-tokens-config', function() {
        saveTokensConfig();
    });

    // NUEVO: Guardar tiempo de expiración de códigos 2FA
    $(document).off('click', '#btn-update-expiration').on('click', '#btn-update-expiration', function() {
        saveCodeExpirationTime();
    });

    // Rate Limiting: Guardar configuración
    $(document).off('click', '#btn-save-rate-limit-config').on('click', '#btn-save-rate-limit-config', function() {
        saveRateLimitConfig();
    });

    // Rate Limiting: Activar/Desactivar
    $(document).off('click', '#btn-confirm-enable-rate-limit').on('click', '#btn-confirm-enable-rate-limit', function() {
        enableRateLimit();
    });

    $(document).off('click', '#btn-confirm-disable-rate-limit').on('click', '#btn-confirm-disable-rate-limit', function() {
        disableRateLimit();
    });

    $(document).off('submit', '#change-password-form').on('submit', '#change-password-form', function(e) {
        e.preventDefault();
        changePassword(this);
    });
}

function load2FAStatus() {
    $.ajax({
        url: BASE_URL + 'UserRoles/get_2fa_status',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.status === 'OK') {
                render2FAStatus(data.result);
            } else {
                $('#2fa-status-container').html('<div class="alert alert-danger">Error al cargar estado: ' + (data.msg || 'Error desconocido') + '</div>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error cargando estado 2FA:', xhr.responseText);
            $('#2fa-status-container').html('<div class="alert alert-danger">Error de conexión al cargar estado 2FA. Revisa la consola para más detalles.</div>');
        }
    });
}

function render2FAStatus(status) {
    const isEnabled = status.is_enabled;
    let html = '';

    if (isEnabled) {
        html = `
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="badge bg-success">
                        <i data-feather="check-circle"></i> Activado
                    </span>
                    <p class="small text-muted mb-0 mt-1">Tu cuenta está protegida con 2FA</p>
                </div>
                <button type="button" class="btn btn-danger btn-sm" id="btn-disable-2fa">
                    Desactivar 2FA
                </button>
            </div>
        `;
        $('#backup-codes-section').show();
    } else {
        html = `
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="badge bg-warning">
                        <i data-feather="alert-circle"></i> Desactivado
                    </span>
                    <p class="small text-muted mb-0 mt-1">Tu cuenta no está protegida con 2FA</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" id="btn-enable-2fa">
                    Activar 2FA
                </button>
            </div>
        `;
        $('#backup-codes-section').hide();
    }

    $('#2fa-status-container').html(html);
    feather.replace();
}

function loadTokensConfig() {
    $.ajax({
        url: BASE_URL + 'UserRoles/get_tokens_config',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.status === 'OK' && data.result) {
                $('#tokens-quantity').val(data.result.backup_codes_quantity || 10);
            }
        },
        error: function() {
            console.error('Error cargando configuración de tokens');
        }
    });
}

function saveTokensConfig() {
    const quantity = parseInt($('#tokens-quantity').val());

    if (quantity < 2 || quantity > 20) {
        functions.toast_message('warning', 'La cantidad debe estar entre 2 y 20', 'ERROR');
        return;
    }

    $.ajax({
        url: BASE_URL + 'UserRoles/save_tokens_config',
        type: 'POST',
        data: { backup_codes_quantity: quantity },
        dataType: 'json',
        success: function(data) {
            functions.toast_message(data.type, data.msg, data.status);
        },
        error: function() {
            functions.toast_message('error', 'Error al guardar configuración', 'ERROR');
        }
    });
}

function showEnable2FAModal() {
    $('#enable-2fa-modal').modal('show');
}

function showDisable2FAModal() {
    $('#disable-2fa-modal').modal('show');
}

function enable2FA() {
    $('#btn-confirm-enable-2fa').prop('disabled', true);

    $.ajax({
        url: BASE_URL + 'UserRoles/enable_2fa',
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            $('#btn-confirm-enable-2fa').prop('disabled', false);

            console.log('enable_2fa response:', data);

            functions.toast_message(data.type, data.msg, data.status);

            if (data.status === 'OK') {
                $('#enable-2fa-modal').modal('hide');
                $('#show-qr-modal').modal('show');

                // Refresh status
                load2FAStatus();
            }
        },
        error: function(xhr, status, error) {
            $('#btn-confirm-enable-2fa').prop('disabled', false);
            console.error('Error activando 2FA:', xhr.responseText, status, error);
            functions.toast_message('error', 'Error al activar 2FA: ' + error, 'ERROR');
        }
    });
}

function disable2FA() {
    $('#btn-confirm-disable-2fa').prop('disabled', true);

    $.ajax({
        url: BASE_URL + 'UserRoles/disable_2fa',
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            $('#btn-confirm-disable-2fa').prop('disabled', false);
            functions.toast_message(data.type, data.msg, data.status);

            if (data.status === 'OK') {
                $('#disable-2fa-modal').modal('hide');
                load2FAStatus();
            }
        },
        error: function() {
            $('#btn-confirm-disable-2fa').prop('disabled', false);
            functions.toast_message('error', 'Error al desactivar 2FA', 'ERROR');
        }
    });
}

function changePassword(form) {
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);

    if (data.new_password !== data.confirm_password) {
        functions.toast_message('warning', 'Las contraseñas no coinciden', 'ERROR');
        return;
    }

    if (data.new_password.length < 6) {
        functions.toast_message('warning', 'La contraseña debe tener al menos 6 caracteres', 'ERROR');
        return;
    }

    $('#btn-change-password').prop('disabled', true);

    $.ajax({
        url: BASE_URL + 'UserRoles/change_password',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            $('#btn-change-password').prop('disabled', false);
            functions.toast_message(response.type, response.msg, response.status);

            if (response.status === 'OK') {
                form.reset();
            }
        },
        error: function() {
            $('#btn-change-password').prop('disabled', false);
            functions.toast_message('error', 'Error al cambiar la contraseña', 'ERROR');
        }
    });
}

// ============================================
// NUEVO: Gestión de tiempo de expiración 2FA
// ============================================

function loadCodeExpirationTime() {
    $.ajax({
        url: BASE_URL + 'UserRoles/get_code_expiration_time',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.status === 'OK' && data.result) {
                const minutes = data.result.expiration_minutes || 5;
                $('#code-expiration-time').val(minutes);
                updateExpirationDisplay(minutes);
            }
        },
        error: function() {
            console.error('Error cargando tiempo de expiración');
        }
    });
}

function saveCodeExpirationTime() {
    const minutes = parseInt($('#code-expiration-time').val());

    if (!minutes || minutes < 3 || minutes > 15) {
        functions.toast_message('warning', 'El tiempo debe estar entre 3 y 15 minutos', 'ERROR');
        return;
    }

    $('#btn-update-expiration').prop('disabled', true);

    $.ajax({
        url: BASE_URL + 'UserRoles/save_code_expiration_time',
        type: 'POST',
        data: { expiration_minutes: minutes },
        dataType: 'json',
        success: function(data) {
            $('#btn-update-expiration').prop('disabled', false);
            functions.toast_message(data.type, data.msg, data.status);

            if (data.status === 'OK') {
                updateExpirationDisplay(minutes);
            }
        },
        error: function() {
            $('#btn-update-expiration').prop('disabled', false);
            functions.toast_message('error', 'Error al guardar configuración', 'ERROR');
        }
    });
}

function updateExpirationDisplay(minutes) {
    // Actualizar el texto que muestra el tiempo actual de expiración
    $('#expiration-time-display').text(minutes);
}

// ============================================
// INITIALIZATION
// ============================================

$(document).ready(function() {
    // Restaurar la pestaña activa desde localStorage
    const activeTab = localStorage.getItem('activeUserRolesTab') || 'roles';

    if (activeTab === 'security') {
        $('a[href="#security"]').tab('show');
        loadPartial('security');
    } else if (activeTab === 'reports') {
        $('a[href="#reports"]').tab('show');
        loadPartial('reports');
    } else {
        // Initialize roles tab (por defecto)
        load_datatable_user_roles();
    }

    // Tab click handler - Guardar pestaña activa en localStorage
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).attr('href').substring(1); // Remove #

        // Guardar la pestaña activa
        localStorage.setItem('activeUserRolesTab', target);

        if (target === 'security') {
            loadPartial('security');
        } else if (target === 'reports') {
            loadPartial('reports');
        } else if (target === 'roles') {
            load_datatable_user_roles();
        }
    });

    // Create role modal
    $('#create_user_role_modal').on('show.bs.modal', function() {
        $.ajax({
            url: BASE_URL + 'UserRoles/get_menu',
            type: 'GET',
            success: function(data) {
                if (data.status === 'OK') {
                    renderMenuPermissions('create', data.data, null);
                }
            }
        });
    });

    // Create role form submit
    $(document).on('submit', '#create_user_role_form', function(e) {
        e.preventDefault();
        create_user_role(this);
    });

    // Update role button
    $(document).on('click', '.btn_update_user_role', function() {
        const id = $(this).data('process-key');
        get_and_show_update_user_role(id);
        $('#update_user_role_modal').modal('show');
    });

    // Update role form submit
    $(document).on('submit', '#update_user_role_form', function(e) {
        e.preventDefault();
        update_user_role(this);
    });

    // Delete role button
    $(document).on('click', '.btn_delete_user_role', function() {
        const id = $(this).data('process-key');
        if (confirm('¿Estás seguro de eliminar este rol?')) {
            delete_user_role(id);
        }
    });

    // Select all checkboxes
    $(document).on('change', '#create_role_select_all', function() {
        $('#create_role_menu input[type="checkbox"]').prop('checked', this.checked);
    });

    $(document).on('change', '#update_role_select_all', function() {
        $('#update_role_menu input[type="checkbox"]').prop('checked', this.checked);
    });
});

// ============================================
// RATE LIMITING FUNCTIONS
// ============================================

function loadRateLimitStatus() {
    $.ajax({
        url: BASE_URL + 'UserRoles/get_rate_limit_config',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.status === 'OK' && data.result) {
                const enabled = data.result.rate_limit_enabled == '1';

                const statusHtml = enabled ?
                    '<span class="badge bg-success"><i data-feather="check-circle"></i> Activado</span>' :
                    '<span class="badge bg-danger"><i data-feather="x-circle"></i> Desactivado</span>';

                $('#rate-limit-status-container').html(statusHtml);

                // Mostrar botón apropiado
                const buttonHtml = enabled ?
                    '<button type="button" class="btn btn-danger btn-sm" id="btn-disable-rate-limit"><i data-feather="shield-off"></i> Desactivar Rate Limiting</button>' :
                    '<button type="button" class="btn btn-success btn-sm" id="btn-enable-rate-limit"><i data-feather="shield"></i> Activar Rate Limiting</button>';

                $('#rate-limit-actions').html(buttonHtml).show();
                feather.replace();

                // Event listeners para botones
                $('#btn-enable-rate-limit').on('click', function() {
                    $('#enable-rate-limit-modal').modal('show');
                });

                $('#btn-disable-rate-limit').on('click', function() {
                    $('#disable-rate-limit-modal').modal('show');
                });
            } else {
                $('#rate-limit-status-container').html('<span class="text-danger">Error: No se pudo leer la configuración</span>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar Rate Limiting status:', xhr.responseText);
            $('#rate-limit-status-container').html('<span class="text-danger">Error al cargar estado (ver consola)</span>');
        }
    });
}

function loadRateLimitConfig() {
    $.ajax({
        url: BASE_URL + 'UserRoles/get_rate_limit_config',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.status === 'OK' && data.result) {
                // Cargar valores desde la BD
                $('#max-attempts-per-user').val(data.result.max_attempts_per_user || 5);
                $('#user-lockout-duration').val(data.result.user_lockout_duration || 900);
                $('#max-attempts-per-ip').val(data.result.max_attempts_per_ip || 10);
                $('#ip-lockout-duration').val(data.result.ip_lockout_duration || 1800);
                $('#sleep-delay').val(data.result.sleep_delay || 2);
            }
        },
        error: function() {
            // Valores por defecto si falla
            $('#max-attempts-per-user').val(5);
            $('#user-lockout-duration').val(900);
            $('#max-attempts-per-ip').val(10);
            $('#ip-lockout-duration').val(1800);
            $('#sleep-delay').val(2);
        }
    });
}

function saveRateLimitConfig() {
    const maxAttemptsUser = parseInt($('#max-attempts-per-user').val());
    const userLockoutDuration = parseInt($('#user-lockout-duration').val());
    const maxAttemptsIP = parseInt($('#max-attempts-per-ip').val());
    const ipLockoutDuration = parseInt($('#ip-lockout-duration').val());
    const sleepDelay = parseInt($('#sleep-delay').val());

    // Validación básica (solo valores vacíos o negativos)
    if (!maxAttemptsUser || maxAttemptsUser < 1) {
        functions.toast_message('warning', 'Los intentos por cuenta deben ser al menos 1', 'ERROR');
        return;
    }

    if (!maxAttemptsIP || maxAttemptsIP < 1) {
        functions.toast_message('warning', 'Los intentos por IP deben ser al menos 1', 'ERROR');
        return;
    }

    // Advertencias informativas (NO bloquean el guardado)
    if (maxAttemptsUser < 2) {
        functions.toast_message('info', '⚠️ Advertencia: Menos de 2 intentos puede bloquear usuarios legítimos por error de tipeo', 'WARNING');
    }

    if (maxAttemptsIP < 2) {
        functions.toast_message('info', '⚠️ Advertencia: Menos de 2 intentos puede ser muy restrictivo', 'WARNING');
    }

    if (maxAttemptsUser > 10) {
        functions.toast_message('info', '⚠️ Advertencia: Más de 10 intentos puede dar tiempo a ataques de fuerza bruta', 'WARNING');
    }

    if (maxAttemptsIP > 20) {
        functions.toast_message('info', '⚠️ Advertencia: Más de 20 intentos reduce la protección contra Hydra', 'WARNING');
    }

    $('#btn-save-rate-limit-config').prop('disabled', true).html('<i data-feather="loader"></i> Guardando...');
    feather.replace();

    $.ajax({
        url: BASE_URL + 'UserRoles/save_rate_limit_config',
        type: 'POST',
        data: {
            max_attempts_per_user: maxAttemptsUser,
            user_lockout_duration: userLockoutDuration,
            max_attempts_per_ip: maxAttemptsIP,
            ip_lockout_duration: ipLockoutDuration,
            sleep_delay: sleepDelay
        },
        dataType: 'json',
        success: function(data) {
            $('#btn-save-rate-limit-config').prop('disabled', false).html('<i data-feather="save"></i> Guardar Configuración');
            feather.replace();

            functions.toast_message(data.type, data.msg, data.status);

            if (data.status === 'OK') {
                loadRateLimitConfig();
            }
        },
        error: function() {
            $('#btn-save-rate-limit-config').prop('disabled', false).html('<i data-feather="save"></i> Guardar Configuración');
            feather.replace();
            functions.toast_message('error', 'Error al guardar la configuración', 'ERROR');
        }
    });
}

function enableRateLimit() {
    $('#btn-confirm-enable-rate-limit').prop('disabled', true).html('<i data-feather="loader"></i> Activando...');
    feather.replace();

    $.ajax({
        url: BASE_URL + 'UserRoles/enable_rate_limit',
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            $('#btn-confirm-enable-rate-limit').prop('disabled', false).html('Activar Rate Limiting');
            $('#enable-rate-limit-modal').modal('hide');

            functions.toast_message(data.type, data.msg, data.status);

            if (data.status === 'OK') {
                loadRateLimitStatus();
            }
        },
        error: function() {
            $('#btn-confirm-enable-rate-limit').prop('disabled', false).html('Activar Rate Limiting');
            functions.toast_message('error', 'Error al activar Rate Limiting', 'ERROR');
        }
    });
}

function disableRateLimit() {
    $('#btn-confirm-disable-rate-limit').prop('disabled', true).html('<i data-feather="loader"></i> Desactivando...');
    feather.replace();

    $.ajax({
        url: BASE_URL + 'UserRoles/disable_rate_limit',
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            $('#btn-confirm-disable-rate-limit').prop('disabled', false).html('Desactivar Rate Limiting');
            $('#disable-rate-limit-modal').modal('hide');

            functions.toast_message(data.type, data.msg, data.status);

            if (data.status === 'OK') {
                loadRateLimitStatus();
            }
        },
        error: function() {
            $('#btn-confirm-disable-rate-limit').prop('disabled', false).html('Desactivar Rate Limiting');
            functions.toast_message('error', 'Error al desactivar Rate Limiting', 'ERROR');
        }
    });
}

// ============================================
// REPORTS TAB (Rate Limiting Monitoring)
// ============================================

function initReportsTab() {
    loadReportsStats();
    loadBlockedIPs();
    loadAttackedAccounts();
    loadLoginAttempts();

    // Event listeners
    $('#btn-refresh-blocked-ips').on('click', function() {
        loadBlockedIPs();
    });

    $('#btn-refresh-attacked-accounts').on('click', function() {
        loadAttackedAccounts();
    });

    $('#btn-refresh-login-attempts').on('click', function() {
        loadLoginAttempts();
    });

    $('#filter-login-attempts').on('change', function() {
        loadLoginAttempts();
    });

    $('#btn-confirm-unblock-ip').on('click', function() {
        unblockIP();
    });
}

function loadReportsStats() {
    $.ajax({
        url: BASE_URL + 'UserRoles/get_rate_limit_stats',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.status === 'OK') {
                $('#report-stat-blocked-ips').text(data.result.blocked_ips || 0);
                $('#report-stat-attacked-accounts').text(data.result.attacked_accounts || 0);
                $('#report-stat-failed-attempts').text(data.result.failed_attempts || 0);
                $('#report-stat-success-logins').text(data.result.success_logins || 0);
            }
        },
        error: function() {
            console.error('Error al cargar estadísticas');
        }
    });
}

function loadBlockedIPs() {
    const tbody = $('#table-blocked-ips tbody');
    tbody.html('<tr><td colspan="8" class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando...</td></tr>');

    $.ajax({
        url: BASE_URL + 'UserRoles/get_blocked_ips',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            tbody.empty();

            if (data.status === 'OK' && data.result.length > 0) {
                data.result.forEach(function(ip) {
                    const remainingTime = formatTimeRemaining(ip.remaining_seconds);
                    const isPermanent = ip.is_permanent == 1;
                    const typeLabel = isPermanent ?
                        '<span class="badge bg-danger">Permanente</span>' :
                        '<span class="badge bg-warning">Temporal</span>';

                    const row = `
                        <tr>
                            <td><code>${ip.ip_address}</code></td>
                            <td>${formatDateTime(ip.blocked_at)}</td>
                            <td>${isPermanent ? 'Permanente' : formatDateTime(ip.blocked_until)}</td>
                            <td>${ip.attempt_count}</td>
                            <td><small>${ip.reason}</small></td>
                            <td>${isPermanent ? 'Permanente' : remainingTime}</td>
                            <td>${typeLabel}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-success" onclick="showUnblockIPModal('${ip.ip_address}')">
                                    <i data-feather="unlock"></i> Desbloquear
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
                feather.replace();
            } else {
                tbody.html('<tr><td colspan="8" class="text-center text-muted">No hay IPs bloqueadas</td></tr>');
            }
        },
        error: function() {
            tbody.html('<tr><td colspan="8" class="text-center text-danger">Error al cargar IPs bloqueadas</td></tr>');
        }
    });
}

function loadAttackedAccounts() {
    const tbody = $('#table-attacked-accounts tbody');
    tbody.html('<tr><td colspan="6" class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando...</td></tr>');

    $.ajax({
        url: BASE_URL + 'UserRoles/get_attacked_accounts',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            tbody.empty();

            if (data.status === 'OK' && data.result.length > 0) {
                data.result.forEach(function(account) {
                    // Formatear IPs del atacante con badges
                    const ips = account.attacker_ips.split(', ');
                    const blockedIps = account.blocked_ips || [];
                    let ipsHtml = ips.map(function(ip) {
                        const isBlocked = blockedIps.indexOf(ip.trim()) !== -1;
                        if (isBlocked) {
                            return '<code class="text-danger" title="IP Bloqueada">' + ip.trim() + '</code> <i data-feather="shield" style="width:12px;height:12px;color:#dc3545;" title="Bloqueada"></i>';
                        } else {
                            return '<code>' + ip.trim() + '</code>';
                        }
                    }).join('<br>');

                    // Estado de protección
                    let protectionBadge;
                    if (account.all_ips_blocked) {
                        protectionBadge = '<span class="badge bg-success"><i data-feather="shield" style="width:12px;height:12px;"></i> Todas bloqueadas</span>';
                    } else if (blockedIps.length > 0) {
                        protectionBadge = '<span class="badge bg-warning"><i data-feather="alert-triangle" style="width:12px;height:12px;"></i> Parcial</span>';
                    } else {
                        protectionBadge = '<span class="badge bg-secondary">Sin bloqueo</span>';
                    }

                    const row = `
                        <tr>
                            <td><strong>${account.username}</strong></td>
                            <td>${ipsHtml}</td>
                            <td><span class="badge bg-danger">${account.total_attempts}</span></td>
                            <td>${formatDateTime(account.first_attempt)}</td>
                            <td>${formatDateTime(account.last_attempt)}</td>
                            <td>${protectionBadge}</td>
                        </tr>
                    `;
                    tbody.append(row);
                });
                feather.replace();
            } else {
                tbody.html('<tr><td colspan="6" class="text-center text-muted">No hay cuentas atacadas en las últimas 24 horas</td></tr>');
            }
        },
        error: function() {
            tbody.html('<tr><td colspan="6" class="text-center text-danger">Error al cargar cuentas atacadas</td></tr>');
        }
    });
}

function loadLoginAttempts() {
    const tbody = $('#table-login-attempts tbody');
    const filter = $('#filter-login-attempts').val();

    tbody.html('<tr><td colspan="5" class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Cargando...</td></tr>');

    $.ajax({
        url: BASE_URL + 'UserRoles/get_login_attempts',
        type: 'GET',
        data: { filter: filter },
        dataType: 'json',
        success: function(data) {
            tbody.empty();

            if (data.status === 'OK' && data.result.length > 0) {
                data.result.forEach(function(attempt) {
                    const resultBadge = attempt.success == 1 ?
                        '<span class="badge bg-success">Exitoso</span>' :
                        '<span class="badge bg-danger">Fallido</span>';

                    const row = `
                        <tr>
                            <td>${formatDateTime(attempt.attempt_time)}</td>
                            <td><strong>${attempt.username}</strong></td>
                            <td><code>${attempt.ip_address}</code></td>
                            <td>${resultBadge}</td>
                            <td><small>${attempt.user_agent || 'N/A'}</small></td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            } else {
                tbody.html('<tr><td colspan="5" class="text-center text-muted">No hay intentos de login registrados</td></tr>');
            }
        },
        error: function() {
            tbody.html('<tr><td colspan="5" class="text-center text-danger">Error al cargar intentos de login</td></tr>');
        }
    });
}

// Modals
let ipToUnblock = null;

function showUnblockIPModal(ip) {
    ipToUnblock = ip;
    $('#unblock-ip-address').text(ip);
    $('#unblock-ip-modal').modal('show');
}

function unblockIP() {
    if (!ipToUnblock) return;

    $('#btn-confirm-unblock-ip').prop('disabled', true).html('<i data-feather="loader"></i> Desbloqueando...');
    feather.replace();

    $.ajax({
        url: BASE_URL + 'UserRoles/unblock_ip',
        type: 'POST',
        data: { ip_address: ipToUnblock },
        dataType: 'json',
        success: function(data) {
            $('#btn-confirm-unblock-ip').prop('disabled', false).html('Desbloquear IP');
            $('#unblock-ip-modal').modal('hide');

            functions.toast_message(data.type, data.msg, data.status);

            if (data.status === 'OK') {
                loadBlockedIPs();
                loadAttackedAccounts();
                loadReportsStats();
            }
        },
        error: function() {
            $('#btn-confirm-unblock-ip').prop('disabled', false).html('Desbloquear IP');
            functions.toast_message('error', 'Error al desbloquear IP', 'ERROR');
        }
    });
}

// Helper functions
function formatDateTime(datetime) {
    if (!datetime) return 'N/A';
    const date = new Date(datetime);
    return date.toLocaleString('es-PE', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function formatTimeRemaining(seconds) {
    if (!seconds || seconds <= 0) return 'Expirado';

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    } else if (minutes > 0) {
        return `${minutes}m ${secs}s`;
    } else {
        return `${secs}s`;
    }
}
