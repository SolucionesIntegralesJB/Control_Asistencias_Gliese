/**
 * P_Group Module - JavaScript (Gestión de Grupos con DataTable)
 * Muestra todos los grupos en formato tabla con opciones de configuración
 */

let groups_table;
let groups_data = [];
let schedule_counter = 0;
let meet_links_data = [];
let schedules_table;

const DAY_NAMES_ES = {
    monday: 'Lunes',
    tuesday: 'Martes',
    wednesday: 'Miércoles',
    thursday: 'Jueves',
    friday: 'Viernes',
    saturday: 'Sábado',
    sunday: 'Domingo'
};

$(document).ready(function () {
    feather.replace();
    init_groups_table();

    // Evento: Agregar horario
    $(document).on('click', '#btn-add-schedule', function () {
        add_schedule_row();
        feather.replace();
    });

    // Evento: Eliminar horario
    $(document).on('click', '.btn-remove-schedule', function () {
        $(this).closest('.schedule-row').remove();
        feather.replace();
    });

    // Evento: Guardar configuración de reuniones
    $(document).on('click', '#btn-save-meeting-config', function () {
        save_meeting_config();
    });

    // Evento: Configurar reuniones (delegado)
    $(document).on('click', '.btn-config-meeting', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        open_meeting_config_modal(id, name);
    });

    // Evento: Ver miembros (delegado)
    $(document).on('click', '.btn-view-members', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        view_group_members(id, name);
    });
});

/**
 * Inicializa la tabla de grupos con DataTable
 */
function init_groups_table() {
    // Si la tabla no existe en la vista (la reemplazamos por el panel),
    // no inicializamos DataTable. En su lugar, cargamos los grupos
    // vía AJAX para que estén disponibles en los modales (asignar enlaces).
    if ($('#pg-groups-table').length === 0) {
        $.ajax({
            url: BASE_URL + 'P_Group/get_group',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'OK') {
                    groups_data = response.data;
                    $('#total-groups').text(response.data.length);
                    // Mostrar información de reuniones si hay un grupo con enlace configurado
                    display_group_meeting_info(response.data);
                } else {
                    $('#total-groups').text('0');
                }
            }
        });
        return;
    }

    groups_table = $('#pg-groups-table').DataTable({
        ajax: {
            url: BASE_URL + 'P_Group/get_group',
            method: 'GET',
            dataSrc: function (json) {
                if (json.status === 'OK') {
                    groups_data = json.data;
                    $('#total-groups').text(json.data.length);
                    return json.data;
                } else {
                    $('#total-groups').text('0');
                    return [];
                }
            },
            error: function () {
                Swal.fire({
                    title: 'Error',
                    text: 'Error al cargar los grupos',
                    icon: 'error',
                    confirmButtonColor: '#667eea'
                });
            }
        },
        columns: [
            {
                data: 'id',
                width: '80px',
                render: function (data) {
                    return `<span class="badge bg-primary">#${data}</span>`;
                }
            },
            {
                data: 'nombre',
                render: function (data) {
                    return `<strong style="color: #667eea;">${data}</strong>`;
                }
            },
            {
                data: 'descripcion',
                render: function (data) {
                    if (!data || data.trim() === '') {
                        return '<span class="text-muted fst-italic">Sin descripción</span>';
                    }
                    return data.length > 50 ? data.substring(0, 50) + '...' : data;
                }
            },
            {
                data: 'responsable',
                render: function (data) {
                    if (!data || data.trim() === '') {
                        return '<span class="text-muted">No asignado</span>';
                    }
                    return data;
                }
            },
            {
                data: null,
                width: '100px',
                className: 'text-center',
                render: function (row) {
                    const current = row.current_members || 0;
                    const capacity = row.capacity || 0;
                    const percentage = capacity > 0 ? (current / capacity) * 100 : 0;
                    let colorClass = 'success';
                    if (percentage > 80) colorClass = 'danger';
                    else if (percentage > 60) colorClass = 'warning';

                    return `<span class="badge bg-${colorClass}">${current}/${capacity}</span>`;
                }
            },
            {
                data: 'fecha_creacion',
                width: '120px',
                className: 'text-center',
                render: function (data) {
                    return `<small><i data-feather="calendar" style="width: 12px; height: 12px;"></i> ${data}</small>`;
                }
            },
            {
                data: null,
                width: '220px',
                className: 'text-center',
                orderable: false,
                render: function (row) {
                    return `
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-info btn-view-members"
                                data-id="${row.id}" data-name="${row.nombre}" title="Ver Miembros">
                                <i data-feather="users" style="width: 14px; height: 14px;"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-config-meeting"
                                data-id="${row.id}" data-name="${row.nombre}" title="Configurar Reuniones">
                                <i data-feather="video" style="width: 14px; height: 14px;"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 10,
        responsive: true,
        order: [[0, 'desc']],
        drawCallback: function () {
            feather.replace();
        }
    });
}

/**
 * Abre modal para configurar link de Meet y horarios
 */
function open_meeting_config_modal(group_id, group_name) {
    $('#meeting-group-id').val(group_id);
    $('#meeting-group-name').text(group_name);

    const group = groups_data.find(g => g.id == group_id);

    // Limpiar campos
    $('#meeting-link').val('');
    $('#schedules-container').empty();
    schedule_counter = 0;

    if (group) {
        // Cargar link de Meet si existe
        if (group.meet_link) {
            $('#meeting-link').val(group.meet_link);
        }

        // Cargar horarios si existen
        if (group.schedule) {
            try {
                const schedules = typeof group.schedule === 'string'
                    ? JSON.parse(group.schedule)
                    : group.schedule;

                if (Array.isArray(schedules) && schedules.length > 0) {
                    schedules.forEach(sch => {
                        add_schedule_row(sch.day, sch.start_time, sch.end_time);
                    });
                }
            } catch (e) {
                console.error('Error parsing schedule:', e);
            }
        }
    }

    // Si no hay horarios, agregar uno vacío
    if (schedule_counter === 0) {
        add_schedule_row();
    }

    $('#modal-meeting-config').modal('show');
    feather.replace();
}

/**
 * Agrega una fila de horario al modal
 */
function add_schedule_row(day = '', start_time = '', end_time = '') {
    schedule_counter++;
    const row_id = 'schedule-' + schedule_counter;

    const html = `
    <div class="schedule-row mb-2" id="${row_id}">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <select class="form-select form-select-sm schedule-day">
                    <option value="">Seleccionar día</option>
                    <option value="monday" ${day === 'monday' ? 'selected' : ''}>Lunes</option>
                    <option value="tuesday" ${day === 'tuesday' ? 'selected' : ''}>Martes</option>
                    <option value="wednesday" ${day === 'wednesday' ? 'selected' : ''}>Miércoles</option>
                    <option value="thursday" ${day === 'thursday' ? 'selected' : ''}>Jueves</option>
                    <option value="friday" ${day === 'friday' ? 'selected' : ''}>Viernes</option>
                    <option value="saturday" ${day === 'saturday' ? 'selected' : ''}>Sábado</option>
                    <option value="sunday" ${day === 'sunday' ? 'selected' : ''}>Domingo</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="time" class="form-control form-control-sm schedule-start"
                       value="${start_time}" placeholder="Hora inicio">
            </div>
            <div class="col-md-3">
                <input type="time" class="form-control form-control-sm schedule-end"
                       value="${end_time}" placeholder="Hora fin">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-schedule w-100">
                    <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                </button>
            </div>
        </div>
    </div>`;

    $('#schedules-container').append(html);
    feather.replace();
}

/**
 * Guarda la configuración de reuniones (Meet + Horarios)
 */
function save_meeting_config() {
    const group_id = $('#meeting-group-id').val();
    const meet_link = $('#meeting-link').val().trim();

    // Recolectar horarios
    const schedules = [];
    $('.schedule-row').each(function() {
        const day = $(this).find('.schedule-day').val();
        const start_time = $(this).find('.schedule-start').val();
        const end_time = $(this).find('.schedule-end').val();

        if (day && start_time && end_time) {
            schedules.push({
                day: day,
                start_time: start_time,
                end_time: end_time
            });
        }
    });

    // Validar que al menos haya un dato
    if (!meet_link && schedules.length === 0) {
        Swal.fire({
            title: 'Advertencia',
            text: 'Debes ingresar al menos el enlace de Meet o un horario',
            icon: 'warning',
            confirmButtonColor: '#667eea'
        });
        return;
    }

    // Preparar datos
    const data = {
        id: group_id
    };

    if (meet_link) {
        data.meet_link = meet_link;
    }

    if (schedules.length > 0) {
        data.schedule = schedules;
    }

    // Enviar petición
    $.ajax({
        url: BASE_URL + 'S_Groups/update_group_meeting_config',
        type: 'PUT',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                $('#modal-meeting-config').modal('hide');
                // Solo recargar si la tabla existe
                if (groups_table) {
                    groups_table.ajax.reload(null, false);
                }
            }
        },
        error: function() {
            Swal.fire({
                title: 'Error',
                text: 'Error al guardar la configuración',
                icon: 'error',
                confirmButtonColor: '#667eea'
            });
        }
    });
}

/**
 * Muestra los miembros del grupo
 */
function view_group_members(group_id, group_name) {
    Swal.fire({
        title: 'Miembros de ' + group_name,
        text: 'La función de gestión de miembros estará disponible pronto',
        icon: 'info',
        confirmButtonColor: '#667eea'
    });
}

// ==================== GESTIÓN DE ENLACES MEET ====================

/**
 * Abre modal para gestionar enlaces Meet
 */
$(document).on('click', '#btn-manage-configs', function() {
    open_manage_links_modal();
});

function open_manage_links_modal() {
    load_meet_links();
    $('#modal-manage-links').modal('show');
}

/**
 * Carga todos los enlaces Meet
 */
function load_meet_links() {
    $.ajax({
        url: BASE_URL + 'P_Group/get_meet_links',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK') {
                meet_links_data = response.data;
                render_meet_links(response.data);
            } else {
                $('#links-list').html('<p class="text-muted text-center">No hay enlaces registrados</p>');
            }
            feather.replace();
        },
        error: function() {
            Swal.fire({
                title: 'Error',
                text: 'Error al cargar los enlaces',
                icon: 'error',
                confirmButtonColor: '#667eea'
            });
        }
    });
}

/**
 * Renderiza la lista de enlaces Meet
 */
function render_meet_links(links) {
    let html = '';

    if (links.length === 0) {
        html = '<p class="text-muted text-center">No hay enlaces registrados</p>';
    } else {
        links.forEach(function(link) {
            html += `
            <div class="card mb-2" style="border-left: 4px solid #667eea;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold">${link.name}</h6>
                            <p class="mb-1 small text-muted">${link.meet_url}</p>
                            ${link.description ? `<p class="mb-0 small">${link.description}</p>` : ''}
                            <span class="badge bg-info mt-1">${link.groups_count} grupo(s) asignado(s)</span>
                        </div>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-primary btn-assign-link" data-id="${link.id}" data-name="${link.name}">
                                <i data-feather="users" style="width: 14px; height: 14px;"></i> Asignar
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete-link ms-2" data-id="${link.id}" title="Eliminar enlace">
                                <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        });
    }

    $('#links-list').html(html);
    feather.replace();
}

/**
 * Crear nuevo enlace Meet
 */
$(document).on('click', '#btn-create-link', function() {
    const name = $('#new-link-name').val().trim();
    const url = $('#new-link-url').val().trim();
    const description = $('#new-link-desc').val().trim();

    if (!name || !url) {
        Swal.fire({
            title: 'Advertencia',
            text: 'El nombre y la URL son obligatorios',
            icon: 'warning',
            confirmButtonColor: '#667eea'
        });
        return;
    }

    const data = { name, meet_url: url, description };

    $.ajax({
        url: BASE_URL + 'P_Group/create_meet_link',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                $('#new-link-name').val('');
                $('#new-link-url').val('');
                $('#new-link-desc').val('');
                load_meet_links();
            }
        },
        error: function() {
            Swal.fire({
                title: 'Error',
                text: 'Error al crear el enlace',
                icon: 'error',
                confirmButtonColor: '#667eea'
            });
        }
    });
});

/**
 * Abrir modal para asignar enlace a grupos
 */
$(document).on('click', '.btn-assign-link', function() {
    const link_id = $(this).data('id');
    const link_name = $(this).data('name');
    open_assign_modal(link_id, link_name);
});

// Eliminar enlace Meet
$(document).on('click', '.btn-delete-link', function() {
    const id = $(this).data('id');
    Swal.fire({
        title: 'Confirmar eliminación',
        text: '¿Estás seguro de eliminar este enlace? Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'P_Group/delete_meet_link',
                type: 'DELETE',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({ id: id }),
                success: function(response) {
                    functions.toast_message(response.type, response.msg, response.status);
                    if (response.status === 'OK') {
                        load_meet_links();
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al eliminar el enlace',
                        icon: 'error',
                        confirmButtonColor: '#667eea'
                    });
                }
            });
        }
    });
});

function open_assign_modal(link_id, link_name) {
    $('#assign-link-id').val(link_id);
    $('#assign-link-name').text(link_name);
    // Determinar grupos ya asignados a este enlace (si la info llegó desde load_meet_links)
    let assignedIds = [];
    if (typeof meet_links_data !== 'undefined' && Array.isArray(meet_links_data)) {
        const linkObj = meet_links_data.find(l => String(l.id) === String(link_id));
        if (linkObj) {
            const raw = linkObj.assigned_group_ids || linkObj.assigned_groups || linkObj.group_ids || '';
            if (raw && typeof raw === 'string') {
                assignedIds = raw.split(',').map(x => x.trim()).filter(x => x !== '');
            } else if (Array.isArray(raw)) {
                assignedIds = raw.map(x => String(x));
            }
        }
    }

    // Verificar si groups_data está vacío y cargar si es necesario
    if (groups_data.length === 0) {
        // Cargar grupos de la tabla P_Group
        $.ajax({
            url: BASE_URL + 'P_Group/get_group',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'OK' && Array.isArray(response.data) && response.data.length > 0) {
                    groups_data = response.data;
                    render_groups_checkboxes(assignedIds);
                } else {
                    // Intentar cargar desde S_Groups (módulo de 'Grupos' principal)
                    $.ajax({
                        url: BASE_URL + 'S_Groups/get_groups',
                        method: 'GET',
                        dataType: 'json',
                        success: function(res2) {
                            if (res2.status === 'OK' && Array.isArray(res2.data)) {
                                groups_data = res2.data;
                            } else {
                                groups_data = [];
                            }
                            render_groups_checkboxes(assignedIds);
                        },
                        error: function() {
                            groups_data = [];
                            render_groups_checkboxes(assignedIds);
                        }
                    });
                }
            }
        });
    } else {
        render_groups_checkboxes(assignedIds);
    }

    function render_groups_checkboxes(assignedIds = []) {
        let html = '';
        if (groups_data.length === 0) {
            html = '<p class="text-muted">No hay grupos disponibles</p>';
        } else {
                groups_data.forEach(function(group) {
                // Compatibilidad con estructuras de P_Group y S_Groups
                const gid = group.id || group.ID || group.group_id || '';
                const gname = group.nombre || group.name || group.group_name || group.code || 'Grupo';
                const gdesc = group.descripcion || group.description || group.group_description || '';

                const isChecked = assignedIds.map(String).includes(String(gid));
                html += `
                <div class="form-check mb-2">
                    <input class="form-check-input group-checkbox" type="checkbox" value="${gid}" id="group-${gid}" ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label" for="group-${gid}">
                        <strong>${gname}</strong> <small class="text-muted">(${gdesc || 'Sin descripción'})</small>
                    </label>
                </div>`;
            });
        }

        $('#groups-checkboxes').html(html);
    }

    $('#modal-assign-link').modal('show');
    feather.replace();
}

/**
 * Guardar asignación de enlace a grupos
 */
$(document).on('click', '#btn-save-assignment', function() {
    const link_id = $('#assign-link-id').val();
    const selected_groups = [];

    $('.group-checkbox:checked').each(function() {
        selected_groups.push(parseInt($(this).val()));
    });

    if (selected_groups.length === 0) {
        Swal.fire({
            title: 'Advertencia',
            text: 'Selecciona al menos un grupo',
            icon: 'warning',
            confirmButtonColor: '#667eea'
        });
        return;
    }

    const data = {
        meet_link_id: parseInt(link_id),
        group_ids: selected_groups
    };

    $.ajax({
        url: BASE_URL + 'P_Group/assign_meet_link',
        type: 'PUT',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                $('#modal-assign-link').modal('hide');
                load_meet_links();
                // Solo recargar si la tabla existe
                if (groups_table) {
                    groups_table.ajax.reload(null, false);
                }
            }
        },
        error: function() {
            Swal.fire({
                title: 'Error',
                text: 'Error al asignar el enlace',
                icon: 'error',
                confirmButtonColor: '#667eea'
            });
        }
    });
});

/**
 * Buscador de enlaces Meet
 */
$(document).on('input', '#links-search', function() {
    const query = $(this).val().trim().toLowerCase();

    if (!query) {
        render_meet_links(meet_links_data);
        return;
    }

    const filtered = meet_links_data.filter(function(link) {
        const name = (link.name || '').toLowerCase();
        const desc = (link.description || '').toLowerCase();
        const url = (link.meet_url || '').toLowerCase();
        return name.includes(query) || desc.includes(query) || url.includes(query);
    });

    render_meet_links(filtered);
});

/**
 * Ver horarios de practicantes
 */
$(document).on('click', '#btn-view-schedules', function() {
    open_schedules_modal();
});

function open_schedules_modal() {
    if (schedules_table) {
        schedules_table.destroy();
    }

    $('#modal-student-schedules').modal('show');

    schedules_table = $('#schedules-table').DataTable({
        ajax: {
            url: BASE_URL + 'P_Group/get_student_schedules',
            method: 'GET',
            dataSrc: function(json) {
                if (json.status === 'OK') {
                    return json.data;
                } else {
                    return [];
                }
            }
        },
        columns: [
            {
                data: null,
                render: function(row) {
                    return `${row.student_name} ${row.student_lastname}`;
                }
            },
            {
                data: 'group_name',
                render: function(data) {
                    return data || '<span class="text-muted">Sin grupo</span>';
                }
            },
            {
                data: 'schedule_file',
                render: function(data, type, row) {
                    const fileName = data.split('/').pop();
                    const extension = fileName.split('.').pop().toLowerCase();
                    let icon = 'file';
                    if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
                        icon = 'image';
                    } else if (extension === 'pdf') {
                        icon = 'file-text';
                    }
                    return `<i data-feather="${icon}"></i> ${fileName}`;
                }
            },
            {
                data: 'uploaded_at',
                render: function(data) {
                    return new Date(data).toLocaleString('es-PE');
                }
            },
            {
                data: 'status',
                render: function(data) {
                    const badges = {
                        'pending': '<span class="badge bg-warning">Pendiente</span>',
                        'approved': '<span class="badge bg-success">Aprobado</span>',
                        'rejected': '<span class="badge bg-danger">Rechazado</span>'
                    };
                    return badges[data] || data;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(row) {
                    return `
                        <button class="btn btn-sm btn-info btn-view-schedule" data-file="${row.schedule_file}" title="Ver archivo">
                            <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                        </button>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 10,
        order: [[3, 'desc']],
        drawCallback: function() {
            feather.replace();
        }
    });
}

/**
 * Ver archivo de horario
 */
$(document).on('click', '.btn-view-schedule', function() {
    const file = $(this).data('file');
    window.open(BASE_URL + file, '_blank');
});

/**
 * Mostrar información de reuniones del grupo del practicante
 */
function display_group_meeting_info(groups) {
    // Si el usuario es practicante y tiene un grupo asignado
    if (!groups || groups.length === 0) {
        return;
    }

    // Tomar el primer grupo (el del practicante)
    const group = groups[0];

    // Agregar botón para subir horario
    const uploadButtonHtml = `
        <div class="mb-3">
            <button type="button" class="btn btn-upload-schedule btn-primary w-100"
                    data-group-id="${group.id}"
                    data-group-name="${group.nombre || 'Mi Grupo'}">
                <i data-feather="upload"></i> Subir/Actualizar Horario del Grupo
            </button>
        </div>
    `;

    $('#meeting-info-content').prepend(uploadButtonHtml);

    // Si el grupo tiene enlace de Meet configurado
    if (group.meet_link) {
        const meetingHtml = `
            <div class="alert alert-success mt-3">
                <div class="d-flex align-items-start">
                    <i data-feather="video" class="me-3" style="width: 24px; height: 24px;"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-2 fw-bold">${group.meet_link_name || 'Enlace de Reunión'}</h6>
                        <div class="mb-2">
                            <strong>Enlace:</strong>
                            <a href="${group.meet_link}" target="_blank" class="text-primary">
                                ${group.meet_link}
                            </a>
                        </div>
                        ${group.schedule ? `<div class="mb-0"><strong>Descripción:</strong> ${group.schedule}</div>` : ''}
                    </div>
                </div>
            </div>
        `;

        $('#meeting-info-content').append(meetingHtml);
    }

    $('#group-meeting-info').show();
    feather.replace();
}

// ============================================================================
// ASIGNACIÓN DE SUPERVISORES - TAB MANAGEMENT
// ============================================================================

/**
 * Carga el contenido del tab de asignación de supervisores
 */
function load_supervisor_assignment_tab() {
    const content = $('#supervisor-assignment-content');

    // Mostrar loading
    content.html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Cargando asignación de supervisores...</p>
        </div>
    `);

    // Cargar contenido desde el servidor
    $.ajax({
        url: BASE_URL + 'Supervisor_Assignment',
        type: 'GET',
        success: function(html) {
            content.html(html);

            // Cargar explícitamente el script inline de asignación de supervisores,
            // ya que los <script> del HTML parcial no se ejecutan con .html().
            $.getScript(
                BASE_URL + 'application/views/supervisor_assignment/js/supervisor_assignment_inline.js',
            )
                .done(function () {
                    if (typeof load_assignments === 'function') {
                        load_assignments();
                    }

                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                })
                .fail(function (jqxhr, settings, exception) {
                    console.error('No se pudo cargar supervisor_assignment_inline.js:', exception);
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                });
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar asignación de supervisores:', error);
            content.html(`
                <div class="alert alert-danger">
                    <i data-feather="alert-circle"></i>
                    <strong>Error:</strong> No se pudo cargar el contenido de asignación de supervisores.
                    <br><small>Verifica que el módulo esté correctamente configurado.</small>
                </div>
            `);
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }
    });
}

// ============================================================================
// CONFIGURACIÓN DE HORARIOS - TAB MANAGEMENT
// ============================================================================

/**
 * Carga el contenido del tab de configuración de horarios
 */
function load_schedule_config_tab() {
    const content = $('#schedule-config-content');

    // Mostrar loading
    content.html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-3 text-muted">Cargando configuración de horarios...</p>
        </div>
    `);

    // Cargar la vista parcial desde el controlador
    $.ajax({
        url: BASE_URL + 'P_Group/load_schedule_config_partial',
        type: 'GET',
        dataType: 'html',
        success: function(html) {
            content.html(html);

            // Re-inicializar iconos de Feather
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar configuración de horarios:', error);
            content.html(`
                <div class="alert alert-danger">
                    <i data-feather="alert-circle"></i>
                    <strong>Error:</strong> No se pudo cargar la configuración de horarios.
                    <br><small>Detalle: ${error}</small>
                </div>
            `);
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }
    });
}

// Evento: Cambio de tab
$(document).on('shown.bs.tab', 'a[data-bs-toggle="tab"]', function (e) {
    const target = $(e.target).attr('href');

    if (target === '#supervisors-section') {
        // Solo cargar si no se ha cargado antes
        const content = $('#supervisor-assignment-content').html().trim();
        if (content.includes('Cargando asignación de supervisores')) {
            load_supervisor_assignment_tab();
        }
    } else if (target === '#schedules-section') {
        // Cargar tab de configuración de horarios
        const content = $('#schedule-config-content').html().trim();
        if (content.includes('Cargando configuración de horarios')) {
            load_schedule_config_tab();
        }
    }

    // Re-renderizar iconos
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});

// ============================================================================
// GESTIÓN DE HORARIOS CON PLANTILLA CSV
// ============================================================================

/**
 * Descargar plantilla CSV de horarios
 */
$(document).on('click', '#btn-download-schedule-template', function() {
    window.location.href = BASE_URL + 'P_Group/download_schedule_template';
});

/**
 * Subir archivo CSV de horarios
 */
$(document).on('click', '#btn-upload-schedule-csv', function() {
    const fileInput = document.getElementById('schedule_file_input');
    const groupId = $('#upload-schedule-group-id').val();

    if (!fileInput.files || fileInput.files.length === 0) {
        Swal.fire({
            title: 'Advertencia',
            text: 'Por favor selecciona un archivo CSV',
            icon: 'warning',
            confirmButtonColor: '#667eea'
        });
        return;
    }

    if (!groupId) {
        Swal.fire({
            title: 'Error',
            text: 'No se pudo identificar el grupo',
            icon: 'error',
            confirmButtonColor: '#667eea'
        });
        return;
    }

    const formData = new FormData();
    formData.append('schedule_file', fileInput.files[0]);
    formData.append('group_id', groupId);

    // Mostrar loading
    Swal.fire({
        title: 'Procesando...',
        text: 'Subiendo y procesando el archivo',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: BASE_URL + 'P_Group/upload_schedule_csv',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            Swal.close();
            functions.toast_message(response.type, response.msg, response.status);

            if (response.status === 'OK') {
                $('#modal-upload-schedule').modal('hide');
                // Recargar la página para ver los cambios
                setTimeout(function() {
                    location.reload();
                }, 1500);
            }
        },
        error: function() {
            Swal.close();
            Swal.fire({
                title: 'Error',
                text: 'Error al procesar el archivo',
                icon: 'error',
                confirmButtonColor: '#667eea'
            });
        }
    });
});

// Agregar botón en la vista del grupo (si existe)
$(document).on('click', '.btn-upload-schedule', function() {
    const groupId = $(this).data('group-id');
    const groupName = $(this).data('group-name');
    open_upload_schedule_modal(groupId, groupName);
});

/**
 * Abre el modal con lógica mejorada: muestra horario guardado o permite subir nuevo
 */
function open_upload_schedule_modal(group_id, group_name) {
    $('#upload-schedule-group-id').val(group_id);
    $('#upload-schedule-group-name').text(group_name);
    $('#schedule_file_input').val(''); // Limpiar input

    // Mostrar modal con loading
    $('#modal-upload-schedule').modal('show');

    // Mostrar loading mientras se carga
    $('#saved-schedule-container').hide();
    $('#upload-schedule-container').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-3">Cargando horario...</p></div>');
    $('#btn-delete-schedule').hide();
    $('#btn-upload-schedule-csv').hide();

    // Cargar horario guardado del grupo
    $.ajax({
        url: BASE_URL + 'P_Group/get_group_schedule',
        type: 'GET',
        data: { group_id: group_id },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK') {
                const schedules = response.data || [];
                const modificationCount = response.modification_count || 0;
                const maxModifications = response.max_modifications || 3;
                const canDelete = response.can_delete || false;

                // Mostrar contador de modificaciones
                $('#modification-counter').html(`
                    <span class="badge ${modificationCount >= maxModifications ? 'bg-danger' : 'bg-info'}">
                        Modificaciones: ${modificationCount}/${maxModifications}
                    </span>
                `);

                if (schedules.length > 0) {
                    // Ya existe un horario guardado - mostrar vista de calendario
                    render_schedule_calendar(schedules);
                    $('#saved-schedule-container').show();
                    $('#upload-schedule-container').hide();

                    // Mostrar botón de eliminar (habilitado o deshabilitado según límite)
                    if (canDelete) {
                        $('#btn-delete-schedule').show().prop('disabled', false);
                    } else {
                        $('#btn-delete-schedule').show().prop('disabled', true).attr('title', 'Has alcanzado el límite de modificaciones');
                    }
                } else {
                    // No existe horario - mostrar formulario de subida
                    render_upload_form();
                    $('#saved-schedule-container').hide();
                    $('#upload-schedule-container').show();
                    $('#btn-upload-schedule-csv').show();
                }
            } else {
                // Error al obtener - mostrar formulario de subida
                render_upload_form();
                $('#saved-schedule-container').hide();
                $('#upload-schedule-container').show();
                $('#btn-upload-schedule-csv').show();
            }

            feather.replace();
        },
        error: function() {
            // Error en la petición - mostrar formulario de subida
            render_upload_form();
            $('#saved-schedule-container').hide();
            $('#upload-schedule-container').show();
            $('#btn-upload-schedule-csv').show();
            feather.replace();
        }
    });
}

/**
 * Renderiza el formulario de subida de horario
 */
function render_upload_form() {
    $('#upload-schedule-container').html(`
        <!-- Paso 1: Descargar plantilla -->
        <div class="card mb-3 border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i data-feather="download"></i> Paso 1: Descargar Plantilla
                </h6>
            </div>
            <div class="card-body">
                <p>Descarga la plantilla Excel y ábrela para rellenar tu horario.</p>
                <button type="button" class="btn btn-success" id="btn-download-schedule-template">
                    <i data-feather="download"></i> Descargar Plantilla Excel
                </button>
            </div>
        </div>

        <!-- Paso 2: Rellenar plantilla -->
        <div class="card mb-3 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i data-feather="edit"></i> Paso 2: Rellenar la Plantilla
                </h6>
            </div>
            <div class="card-body">
                <p><strong>Instrucciones:</strong></p>
                <ul class="small">
                    <li><strong>Tipos de actividad:</strong> Trabajo, Estudio, Reunión o Descanso</li>
                    <li><strong>Las celdas se colorearán automáticamente</strong> según el tipo de actividad</li>
                    <li><strong>Puedes dejar muchas celdas vacías</strong> si no tienes actividades programadas</li>
                </ul>
                <div class="alert alert-warning small">
                    <strong>⚠️ Importante:</strong> Las reuniones deben durar exactamente 1 hora y no pueden repetirse con otro grupo.
                </div>
            </div>
        </div>

        <!-- Paso 3: Subir archivo -->
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0">
                    <i data-feather="upload"></i> Paso 3: Subir Archivo
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Selecciona el archivo Excel rellenado:</label>
                    <input type="file" class="form-control" id="schedule_file_input" accept=".csv,.xls,.xlsx">
                    <small class="text-muted">Formatos aceptados: CSV, XLS, XLSX (máximo 5MB)</small>
                </div>
            </div>
        </div>
    `);
}

/**
 * Renderiza el horario guardado en formato de calendario semanal
 */
function render_schedule_calendar(schedules) {
    const daysOrder = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    const daysLabels = {
        'lunes': 'Lunes',
        'martes': 'Martes',
        'miercoles': 'Miércoles',
        'jueves': 'Jueves',
        'viernes': 'Viernes',
        'sabado': 'Sábado',
        'domingo': 'Domingo'
    };

    const actividadColors = {
        'estudio': { border: '#3b82f6', bg: '#eff6ff', icon: 'book-open', label: 'Estudio' },
        'trabajo': { border: '#f59e0b', bg: '#fef3c7', icon: 'briefcase', label: 'Trabajo' },
        'reunion': { border: '#10b981', bg: '#d1fae5', icon: 'users', label: 'Reunión' },
        'descanso': { border: '#6b7280', bg: '#f3f4f6', icon: 'coffee', label: 'Descanso' }
    };

    // Agrupar horarios por día
    const schedulesByDay = {};
    daysOrder.forEach(day => {
        schedulesByDay[day] = schedules.filter(s => s.day_of_week === day);
    });

    let html = '<div class="table-responsive"><table class="table table-bordered">';
    html += '<thead><tr><th width="120">Día</th><th>Horarios</th></tr></thead><tbody>';

    daysOrder.forEach(day => {
        const daySchedules = schedulesByDay[day];
        html += `<tr><td class="fw-bold">${daysLabels[day]}</td><td>`;

        if (daySchedules.length > 0) {
            daySchedules.forEach(schedule => {
                const config = actividadColors[schedule.activity_type] || actividadColors['trabajo'];
                html += `
                    <div class="d-inline-block me-2 mb-2 p-2 rounded" style="border-left: 4px solid ${config.border}; background: ${config.bg};">
                        <i data-feather="${config.icon}" style="width: 14px; height: 14px;"></i>
                        <strong>${schedule.start_time} - ${schedule.end_time}</strong>
                        <span class="badge" style="background: ${config.border}; color: white;">${config.label}</span>
                        ${schedule.description ? `<br><small class="text-muted">${schedule.description}</small>` : ''}
                    </div>
                `;
            });
        } else {
            html += '<span class="text-muted">Sin actividades</span>';
        }

        html += '</td></tr>';
    });

    html += '</tbody></table></div>';

    // Agregar leyenda
    html += '<div class="mt-3"><strong>Leyenda:</strong><div class="d-flex flex-wrap gap-3 mt-2">';
    Object.entries(actividadColors).forEach(([key, config]) => {
        html += `
            <div class="d-flex align-items-center">
                <div style="width: 20px; height: 20px; background: ${config.bg}; border-left: 4px solid ${config.border}; border-radius: 4px;" class="me-2"></div>
                <span>${config.label}</span>
            </div>
        `;
    });
    html += '</div></div>';

    $('#schedule-calendar-view').html(html);
}

/**
 * Evento: Eliminar horario
 */
$(document).on('click', '#btn-delete-schedule', function() {
    const groupId = $('#upload-schedule-group-id').val();
    const groupName = $('#upload-schedule-group-name').text();

    Swal.fire({
        title: '¿Eliminar horario?',
        text: `Se eliminará el horario del grupo "${groupName}". Podrás subir uno nuevo después.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Eliminando...',
                text: 'Eliminando el horario del grupo',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: BASE_URL + 'P_Group/delete_group_schedule',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({ group_id: parseInt(groupId) }),
                success: function(response) {
                    Swal.close();
                    functions.toast_message(response.type, response.msg, response.status);

                    if (response.status === 'OK') {
                        // Recargar el modal para mostrar el formulario de subida
                        open_upload_schedule_modal(groupId, groupName);
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al eliminar el horario',
                        icon: 'error',
                        confirmButtonColor: '#667eea'
                    });
                }
            });
        }
    });
});
