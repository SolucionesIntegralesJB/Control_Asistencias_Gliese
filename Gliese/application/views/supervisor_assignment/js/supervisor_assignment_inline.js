/**
 * ============================================================================
 * ASIGNACIÓN DE SUPERVISORES - INLINE VERSION
 * ============================================================================
 * Versión simplificada para cargar dentro del tab de P_Group
 */

// Evitar múltiples bindings si el script se carga más de una vez
$(document).off('click', '.btn-new-assignment');
$(document).off('click', '#btn-save-supervisor-assignment');
$(document).off('click', '.btn-unassign');
// removed obsolete handlers for legacy select-group / toggle-members

/**
 * Carga todas las asignaciones activas
 */
function load_assignments() {
    $.ajax({
        url: BASE_URL + 'Supervisor_Assignment/get_assignments',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function(response) {
            if (response.status === 'OK' && response.data && response.data.length > 0) {
                render_assignments(response.data);
            } else {
                $('#assignments-tbody').html('<tr><td colspan="7" class="text-center text-muted py-4">No hay asignaciones registradas</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar asignaciones:', error);
            $('#assignments-tbody').html('<tr><td colspan="7" class="text-center text-danger py-4">Error al cargar asignaciones</td></tr>');
        }
    });
}

/**
 * Renderiza la tabla de asignaciones
 */
function render_assignments(data) {
    const tbody = $('#assignments-tbody');
    tbody.empty();

    data.forEach(function(assignment) {
        const row = `
            <tr>
                <td>${assignment.supervisor_nombre || '-'}</td>
                <td><small>${assignment.supervisor_email || '-'}</small></td>
                <td>
                    <span class="badge bg-light-info">${assignment.group_name || '-'}</span>
                    <br><small class="text-muted">Código: ${assignment.group_code || '-'}</small>
                </td>
                <td class="text-center"><span class="badge bg-light-secondary">${assignment.total_practicantes || 0}</span></td>
                <td><small>${assignment.assigned_date || '-'}</small></td>
                <td><span class="badge ${assignment.status == 1 ? 'bg-light-success' : 'bg-light-secondary'}">${assignment.status == 1 ? 'Activo' : 'Inactivo'}</span></td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-unassign"
                            data-supervisor-id="${assignment.supervisor_id}"
                            data-group-id="${assignment.group_id}"
                            title="Desasignar">
                        <i data-feather="user-x" style="width: 14px; height: 14px;"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

/**
 * Carga los supervisores disponibles
 */
function load_supervisors_for_assignment() {
    // Mantener como fallback: carga todos los supervisores (si hace falta)
    $.ajax({
        url: BASE_URL + 'Supervisor_Assignment/get_supervisors',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function(response) {
            const select = $('#select-supervisor');
            select.empty().append('<option value="">Seleccione un supervisor...</option>');

            if (response.status === 'OK' && response.data && response.data.length > 0) {
                response.data.forEach(function(supervisor) {
                    select.append(`<option value="${supervisor.id}">${supervisor.nombre_completo || supervisor.username}</option>`);
                });
            }
        }
    });
}

/**
 * Carga los grupos disponibles
 */
function load_groups_for_assignment() {
    $.ajax({
        url: BASE_URL + 'Supervisor_Assignment/get_groups',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function(response) {
            const supGroupSelect = $('#select-supervisor-group');
            const practContainer = $('#practicant-groups-container');

            supGroupSelect.empty().append('<option value="">Seleccione un grupo de supervisores...</option>');
            practContainer.empty();

            if (response.status === 'OK' && response.data && response.data.length > 0) {
                response.data.forEach(function(group) {
                    const label = `${group.group_name} (${group.group_code})`;
                    if (group.group_type && group.group_type.toUpperCase() === 'SUPERVISOR') {
                        supGroupSelect.append(`<option value="${group.id}" data-code="${group.group_code}">${label}</option>`);
                    }
                    if (group.group_type && group.group_type.toUpperCase() === 'PRACTICANTE') {
                        const currentMembers = group.current_members || 0;
                        const maxCapacity = group.max_capacity || 0;
                        const capacityText = maxCapacity > 0 ? `${currentMembers}/${maxCapacity}` : '';
                        const itemHtml = `
                            <div class="group-card p-2 mb-2 border rounded" style="cursor: pointer; transition: all 0.2s; background: #f8f9fa;">
                                <div class="form-check d-flex align-items-start">
                                    <input class="form-check-input pract-group-checkbox mt-1" type="checkbox" value="${group.id}" id="practicant-group-${group.id}" style="width: 18px; height: 18px;">
                                    <label class="form-check-label flex-grow-1 ms-2" for="practicant-group-${group.id}" style="cursor: pointer;">
                                        <div class="fw-bold text-dark">${group.group_name}</div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <small class="text-muted">${group.group_code || ''}</small>
                                            ${capacityText ? `<span class="badge bg-light-primary" style="font-size: 10px;">${capacityText} practicantes</span>` : ''}
                                        </div>
                                    </label>
                                </div>
                            </div>
                        `;
                        practContainer.append(itemHtml);
                    }
                });

                // Agregar estilos hover para las tarjetas
                practContainer.find('.group-card').hover(
                    function() { $(this).css('background', '#e9ecef'); },
                    function() { $(this).css('background', '#f8f9fa'); }
                );

                // Hacer clic en la tarjeta toggle el checkbox
                practContainer.find('.group-card').on('click', function(e) {
                    if (!$(e.target).is('input')) {
                        const checkbox = $(this).find('.pract-group-checkbox');
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    }
                });

            } else {
                practContainer.html('<p class="text-muted small mb-0">No hay grupos de practicantes disponibles.</p>');
            }

            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }
    });
}

/**
 * Carga los miembros de un grupo de supervisores y los pone en el select de supervisores
 */
function load_supervisor_group_members(group_id) {
    const select = $('#select-supervisor');
    select.empty().append('<option value="">Seleccione un supervisor...</option>');
    if (!group_id) return;

    // Obtener el código del grupo (group_code) desde la opción seleccionada
    const selectedOption = $('#select-supervisor-group option[value="' + group_id + '"]');
    const groupCode = selectedOption.data('code');

    if (!groupCode) {
        // Si por alguna razón no tenemos el código, usar todos los supervisores disponibles
        load_supervisors_for_assignment();
        return;
    }

    // Traer todos los supervisores y filtrar por el código de grupo
    $.ajax({
        url: BASE_URL + 'S_Groups/get_supervisors?for=practicantes',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function(response) {
            if (response && response.data && response.data.length > 0) {
                const filtered = response.data.filter(function(sup) {
                    return (
                        sup.group_code &&
                        sup.group_code === groupCode &&
                        sup.group_type &&
                        sup.group_type.toUpperCase() === 'SUPERVISOR'
                    );
                });

                const list = filtered.length > 0 ? filtered : response.data;

                list.forEach(function(sup) {
                    const name = sup.full_name || '';
                    select.append(`<option value="${sup.id}">${name.trim()}</option>`);
                });
            } else {
                load_supervisors_for_assignment();
            }
        },
        error: function() {
            load_supervisors_for_assignment();
        }
    });
}

/**
 * Carga y muestra los miembros del grupo dentro del modal de asignación
 */
// legacy group members viewer removed (no longer used)

/**
 * Asigna un supervisor a un grupo
 */
function assign_supervisor() {
    const supervisor_id = $('#select-supervisor').val();
    const supervisor_group_id = $('#select-supervisor-group').val();
    const practGroups = [];
    $('.pract-group-checkbox:checked').each(function() {
        practGroups.push($(this).val());
    });
    const notes = $('#assignment-notes').val();

    if (!Array.isArray(practGroups) || practGroups.length === 0) {
        if (typeof functions !== 'undefined') functions.toast_message('warning', 'Seleccione al menos un grupo de practicantes', 'WARNING');
        return;
    }
    // Determinar lista de supervisores a usar
    let supervisors = [];

    if (supervisor_id && supervisor_id !== '') {
        supervisors = [supervisor_id];
    } else if (supervisor_group_id && supervisor_group_id !== '') {
        // Tomar todos los supervisores cargados en el select (opciones diferentes de vacío)
        $('#select-supervisor option').each(function() {
            const val = $(this).val();
            if (val && val !== '') {
                supervisors.push(val);
            }
        });

        if (supervisors.length === 0) {
            if (typeof functions !== 'undefined') functions.toast_message('warning', 'El grupo seleccionado no tiene supervisores disponibles', 'WARNING');
            return;
        }
    } else {
        if (typeof functions !== 'undefined') functions.toast_message('warning', 'Seleccione un supervisor o un grupo de supervisores', 'WARNING');
        return;
    }

    // Enviar una petición por cada combinación supervisor / grupo seleccionado
    let totalRequests = supervisors.length * practGroups.length;
    let remaining = totalRequests;
    let errors = 0;
    let successCount = 0;
    let duplicateCount = 0;

    supervisors.forEach(function(supId) {
        practGroups.forEach(function(group_id) {
            $.ajax({
                url: BASE_URL + 'Supervisor_Assignment/assign',
                type: 'POST',
                dataType: 'json',
                data: { supervisor_id: supId, group_id: group_id, notes: notes },
                success: function(response) {
                    if (response && response.status === 'OK') {
                        successCount++;
                    } else if (response && response.status === 'ERROR' &&
                               response.msg &&
                               response.msg.indexOf('ya está asignado') !== -1) {
                        duplicateCount++;
                    } else {
                        errors++;
                    }
                },
                error: function() {
                    errors++;
                },
                complete: function() {
                    remaining--;
                    if (remaining === 0) {
                        if (typeof console !== 'undefined') {
                            console.log('Resultado asignación supervisores', {
                                totalRequests: totalRequests,
                                successCount: successCount,
                                duplicateCount: duplicateCount,
                                errors: errors
                            });
                        }

                        if (successCount > 0 && errors === 0) {
                            if (typeof functions !== 'undefined') functions.toast_message('success', 'Asignaciones realizadas correctamente', 'SUCCESS');
                        } else if (successCount > 0 && errors > 0) {
                            if (typeof functions !== 'undefined') functions.toast_message('warning', 'Algunas asignaciones se realizaron, pero otras fallaron', 'WARNING');
                        } else if (successCount === 0 && duplicateCount > 0 && errors === 0) {
                            if (typeof functions !== 'undefined') functions.toast_message('info', 'No se crearon nuevas asignaciones: todos los supervisores ya estaban asignados a esos grupos', 'INFO');
                        } else {
                            if (typeof functions !== 'undefined') functions.toast_message('error', 'No se pudieron realizar las asignaciones', 'ERROR');
                        }
                        $('#modal-assign-supervisor').modal('hide');
                        if ($('#form-assign')[0]) {
                            $('#form-assign')[0].reset();
                        }
                        load_assignments();
                    }
                }
            });
        });
    });
}

/**
 * Desasigna un supervisor de un grupo
 */
function unassign_supervisor(supervisor_id, group_id) {
    $.ajax({
        url: BASE_URL + 'Supervisor_Assignment/unassign',
        type: 'POST',
        dataType: 'json',
        data: { supervisor_id: supervisor_id, group_id: group_id },
        success: function(response) {
            if (response.status === 'OK') {
                if (typeof functions !== 'undefined') {
                    functions.toast_message('success', response.msg, 'SUCCESS');
                }
                load_assignments();
            }
        }
    });
}

// EVENTOS
$(document).on('click', '.btn-new-assignment', function(e) {
    e.preventDefault();

    if ($('#form-assign')[0]) {
        $('#form-assign')[0].reset();
    }

    // Cargar listados: grupos (supervisores + practicantes) y supervisores (fallback)
    load_groups_for_assignment();
    load_supervisors_for_assignment();

    // Mostrar modal global definido en la vista P_Group
    $('#modal-assign-supervisor').modal('show');
});

$(document).on('click', '#btn-save-supervisor-assignment', function(e) {
    e.preventDefault();
    assign_supervisor();
});

$(document).on('click', '.btn-unassign', function(e) {
    e.preventDefault();
    const supervisorId = $(this).data('supervisor-id');
    const groupId = $(this).data('group-id');

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Desasignar supervisor?',
            text: 'Esta acción desasignará al supervisor de este grupo.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, desasignar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                unassign_supervisor(supervisorId, groupId);
            }
        });
    } else {
        if (confirm('¿Está seguro que desea desasignar al supervisor de este grupo?')) {
            unassign_supervisor(supervisorId, groupId);
        }
    }
});

// Cuando cambie el grupo de supervisores, cargar los integrantes para elegir supervisor
$(document).on('change', '#select-supervisor-group', function(e) {
    const groupId = $(this).val();
    if (groupId) {
        load_supervisor_group_members(groupId);
    } else {
        // limpiar select de supervisores
        $('#select-supervisor').empty().append('<option value="">Seleccione un supervisor...</option>');
    }
});

// Resetear formulario al cerrar el modal (solo una vez)
$('#modal-assign-supervisor').off('hidden.bs.modal.supervisorAssign');
$('#modal-assign-supervisor').on('hidden.bs.modal.supervisorAssign', function () {
    if ($('#form-assign')[0]) {
        $('#form-assign')[0].reset();
    }
    $('#select-supervisor-group').val('');
    $('#select-supervisor').empty().append('<option value="">Seleccione un supervisor...</option>');
    $('#practicant-groups-container').empty().html('<p class="text-muted small mb-0">Cargando grupos de practicantes...</p>');
});
