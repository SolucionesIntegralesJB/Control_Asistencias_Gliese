// -- Variables globales
let members_table;
let supervisors_cache = [];
let groups_data = [];
let filtered_groups = [];
let current_page = 1;
let items_per_page = 12;
// Lista temporal de supervisores para el modal (cada item: {id, type, name})
let group_supervisors_list = [];
// IDs originales de supervisores cuando abrimos el modal en modo edición
let original_group_supervisors_ids = [];
// Flag para saber si el offcanvas actual corresponde a un grupo de tipo SUPERVISOR
// NOTE: We no longer use a global flag. We store `isSupervisor` on the offcanvas element
// via jQuery data: $('#offcanvas-members').data('isSupervisor', true/false)

// -- Funciones
// --
function destroy_datatable(tableId) {
    // Destruye una tabla DataTable existente si está inicializada.
    // Intenta limpiar y destruir la tabla, ignorando errores si ocurren.
    if ($.fn.DataTable && $.fn.DataTable.isDataTable(tableId)) {
        try {
            $(tableId).DataTable().clear().destroy();
        } catch (e) {
            try {
                $(tableId).dataTable().fnDestroy();
            } catch (e) {
                /* ignora */
            }
        }
    }
}

// --
function load_groups() {
    // Carga los grupos de practicantes activos desde el servidor.
    // Procesa la respuesta y aplica filtros para renderizar los grupos.
    $.ajax({
        url: BASE_URL + 'S_Groups/get_groups',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function (json) {
            console.log('Ajax get_groups response:', json);

            if (!json) {
                groups_data = [];
                render_groups();
                return;
            }

            if (json.type && json.msg) {
                if (json.type !== 'success') {
                    functions.toast_message(json.type, json.msg, json.status || '');
                }
            }

            if (Array.isArray(json.data)) {
                groups_data = json.data;
            } else if (Array.isArray(json.result)) {
                groups_data = json.result;
            } else if (Array.isArray(json)) {
                groups_data = json;
            } else {
                groups_data = [];
            }

            apply_filters();
        },
        error: function (xhr, status, error) {
            console.error('Error cargando grupos:', error);
            functions.toast_message('error', 'Error al cargar grupos', 'ERROR');
            groups_data = [];
            render_groups();
        },
    });
}

// --
function apply_filters() {
    // Aplica filtros a los grupos según búsqueda, estado y disponibilidad.
    // Filtra los datos de grupos_data y actualiza la variable filtered_groups para su renderización.
    const search = $('#search-groups').val().toLowerCase();
    const status = $('#filter-status').val();
    const availability = $('#filter-availability').val();

    filtered_groups = groups_data.filter(function (group) {
        // Filtro de búsqueda
        if (search) {
            // Construir texto seguro de supervisores (puede venir como array desde el servidor)
            let supervisorsText = '';
            try {
                if (Array.isArray(group.supervisors)) {
                    supervisorsText = group.supervisors
                        .map(function (s) {
                            return (
                                s.full_name ||
                                s.name ||
                                s.first_name ||
                                s.last_name ||
                                s.supervisor_id ||
                                ''
                            );
                        })
                        .filter(Boolean)
                        .join(', ');
                } else {
                    supervisorsText = group.supervisors || '';
                }
            } catch (e) {
                supervisorsText = '';
            }

            if (
                !String(group.group_code || '')
                    .toLowerCase()
                    .includes(search) &&
                !String(group.group_name || '')
                    .toLowerCase()
                    .includes(search) &&
                !String(supervisorsText || '')
                    .toLowerCase()
                    .includes(search)
            ) {
                return false;
            }
        }

        // Filtro de estado
        if (status !== '' && group.status != status) {
            return false;
        }

        // Filtro de disponibilidad
        if (availability === 'available' && group.available_spaces <= 0) {
            return false;
        }
        if (availability === 'full' && group.available_spaces > 0) {
            return false;
        }

        return true;
    });

    current_page = 1;
    render_groups();
}

function render_groups() {
    // Renderiza las tarjetas de los grupos filtrados en el contenedor groups-cards-container.
    // Muestra un mensaje si no hay grupos, actualiza la información de paginación y renderiza las tarjetas.
    const container = $('#groups-cards-container');
    container.empty();

    if (filtered_groups.length === 0) {
        container.html(
            '<div class="col-12">' +
                '<div class="card">' +
                '<div class="card-body text-center py-5">' +
                '<i data-feather="inbox" style="width: 48px; height: 48px;" class="text-muted"></i>' +
                '<p class="mt-3 mb-0 text-muted">No se encontraron grupos</p>' +
                '</div></div></div>',
        );
        $('#groups-info').text('Mostrando 0 grupos');
        $('#groups-pagination').empty();
        feather.replace();
        return;
    }

    // Paginación
    const start = (current_page - 1) * items_per_page;
    const end = start + items_per_page;
    const page_groups = filtered_groups.slice(start, end);

    // Renderizar cards
    page_groups.forEach(function (group) {
        const card = create_group_card(group);
        container.append(card);
    });

    // Actualizar información
    const total = filtered_groups.length;
    const showing_start = start + 1;
    const showing_end = Math.min(end, total);
    $('#groups-info').text(
        'Mostrando ' + showing_start + '-' + showing_end + ' de ' + total + ' grupos',
    );

    // Renderizar paginación
    render_pagination();

    // Re-inicializar iconos Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}
// --
function create_group_card(group) {
    // Crea una tarjeta HTML para un grupo de practicantes.
    // Genera el contenido de la tarjeta con información del grupo, incluyendo disponibilidad, estado,
    // supervisores, miembros y botones de acción (ver miembros, editar, eliminar).
    let availability_badge = '';
    if (group.available_spaces <= 0) {
        availability_badge = '<span class="badge bg-danger">Lleno</span>';
    } else if (group.available_spaces <= 3) {
        availability_badge =
            '<span class="badge bg-warning">' + group.available_spaces + ' disponibles</span>';
    } else {
        availability_badge =
            '<span class="badge bg-success">' + group.available_spaces + ' disponibles</span>';
    }

    // Badge de estado
    const status_badge =
        group.status == 1
            ? '<span class="badge bg-success">Activo</span>'
            : '<span class="badge bg-secondary">Inactivo</span>';

    // Badge de tipo de grupo con color personalizado
    const groupTypeText = group.group_type === 'SUPERVISOR' ? 'Supervisores' : 'Practicantes';
    const groupColor = group.color || (group.group_type === 'SUPERVISOR' ? '#007bff' : '#28a745');
    const group_type_badge = `<span class="badge" style="background-color: ${groupColor}">${groupTypeText}</span>`;

    // Aplicar color a toda la tarjeta: generar un fondo claro a partir del color del grupo
    function lightenHex(hex, percent) {
        // hex puede venir con o sin '#'
        hex = hex.replace('#', '');
        if (hex.length === 3) {
            hex = hex
                .split('')
                .map(function (c) {
                    return c + c;
                })
                .join('');
        }
        const num = parseInt(hex, 16);
        const r = (num >> 16) & 0xff;
        const g = (num >> 8) & 0xff;
        const b = num & 0xff;
        const newR = Math.min(255, Math.round(r + (255 - r) * (percent / 100)));
        const newG = Math.min(255, Math.round(g + (255 - g) * (percent / 100)));
        const newB = Math.min(255, Math.round(b + (255 - b) * (percent / 100)));
        const toHex = (n) => n.toString(16).padStart(2, '0');
        return `#${toHex(newR)}${toHex(newG)}${toHex(newB)}`;
    }

    const cardBg = lightenHex(groupColor, 88); // muy claro

    // Supervisores o creador
    let supervisors_text = '';
    let creator_name =
        group.created_by_name || group.created_by || group.creator || 'No especificado';
    if (group.group_type === 'PRACTICANTE') {
        // Mostrar supervisores en formato de lista alineada
        let principal = '';
        let tecnico = '';
        let asistentes = [];
        if (Array.isArray(group.supervisors)) {
            group.supervisors.forEach(function (sup) {
                if (sup.supervisor_type && sup.full_name) {
                    // Mapear los valores de la base de datos (en inglés) a los textos en español
                    const type = sup.supervisor_type.toLowerCase();
                    if (type === 'principal') {
                        principal = sup.full_name;
                    } else if (type === 'technical') {
                        tecnico = sup.full_name;
                    } else if (type === 'assistant') {
                        asistentes.push(sup.full_name);
                    }
                }
            });
        }
        supervisors_text =
            '<div class="mb-2">' +
            '<div class="d-flex align-items-center">' +
            '<i data-feather="user-check" class="me-2" style="width: 16px; height: 16px;"></i>' +
            '<span style="font-weight:500;">Supervisores:</span>' +
            '</div>' +
            '<div style="margin-left: 0px;">' +
            '<span style="display:block; margin-bottom:6px;">Principal:' +
            (principal ? ' ' + principal : '') +
            '</span>' +
            '<span style="display:block; margin-bottom:6px;">Técnico:' +
            (tecnico ? ' ' + tecnico : '') +
            '</span>';
        if (asistentes.length > 0) {
            asistentes.forEach(function (asist) {
                supervisors_text +=
                    '<span style="display:block; margin-bottom:6px;">Asistente:' +
                    (asist ? ' ' + asist : '') +
                    '</span>';
            });
        } else {
            supervisors_text += '<span style="display:block; margin-bottom:6px;">Asistente:</span>';
        }
        supervisors_text += '</div></div>';
    } else {
        // SUPERVISOR: solo ícono y nombre en una línea, como antes
        supervisors_text =
            '<div class="d-flex align-items-center mb-2">' +
            '<i data-feather="user-check" class="me-2" style="width: 16px; height: 16px;"></i>' +
            '<small class="flex-grow-1 text-muted">' +
            creator_name +
            '</small>' +
            '</div>';
    }

    // Botón de miembros - siempre habilitado para grupos de supervisores
    let members_button = '';
    if (group.current_members > 0) {
        members_button =
            '<button class="btn btn-sm btn-outline-primary btn-view-members w-100 mb-2" data-id="' +
            group.id +
            '" data-name="' +
            (group.name || group.group_name) +
            '">' +
            '<i data-feather="users"></i> Ver ' +
            group.current_members +
            ' miembro' +
            (group.current_members != 1 ? 's' : '') +
            '</button>';
    } else {
        // SIEMPRE habilitado para cualquier tipo de grupo
        members_button =
            '<button class="btn btn-sm btn-outline-primary btn-view-members w-100 mb-2" data-id="' +
            group.id +
            '" data-name="' +
            (group.name || group.group_name) +
            '">' +
            '<i data-feather="users"></i> Ver miembros</button>';
    }

    // Botones de acción
    let delete_button = '';
    if (group.current_members == 0) {
        delete_button =
            '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="' +
            group.id +
            '" data-name="' +
            (group.name || group.group_name) +
            '" title="Eliminar">' +
            '<i data-feather="trash-2"></i></button>';
    } else {
        delete_button =
            '<button class="btn btn-sm btn-outline-secondary" disabled title="No se puede eliminar (tiene miembros)">' +
            '<i data-feather="lock"></i></button>';
    }

    const card_html =
        '<div class="col-12 col-sm-6 col-lg-4 col-xl-3 mb-3">' +
        '<div class="card h-100" style="background-color: ' +
        cardBg +
        '; border: 1px solid ' +
        groupColor +
        ';">' +
        '<div class="card-body">' +
        '<div class="d-flex justify-content-between align-items-start mb-3">' +
        '<div class="flex-grow-1">' +
        '<h5 class="mb-0">' +
        (group.code || group.group_code) +
        '</h5>' +
        '<small class="text-muted">' +
        (group.name || group.group_name) +
        '</small>' +
        '<div class="mt-1">' +
        group_type_badge +
        '</div>' +
        '</div>' +
        '<div class="text-end">' +
        status_badge +
        '</div>' +
        '</div>' +
        '<div class="mb-3">' +
        supervisors_text +
        '<div class="d-flex align-items-center mb-2">' +
        '<i data-feather="users" class="me-2" style="width: 16px; height: 16px;"></i>' +
        '<small class="flex-grow-1">' +
        group.current_members +
        '/' +
        group.max_capacity +
        ' miembros</small>' +
        '</div>' +
        '<div class="d-flex align-items-center mb-2">' +
        '<i data-feather="calendar" class="me-2" style="width: 16px; height: 16px;"></i>' +
        '<small class="flex-grow-1">' +
        group.created_date +
        '</small>' +
        '</div>' +
        (group.meet_link ?
            '<div class="d-flex align-items-center mb-2">' +
            '<i data-feather="video" class="me-2 text-success" style="width: 16px; height: 16px;"></i>' +
            '<small class="flex-grow-1 text-success fw-bold">Enlace configurado</small>' +
            '</div>' : '') +
        '<div class="text-center mt-2">' +
        availability_badge +
        '</div>' +
        '</div>' +
        members_button +
        '<button class="btn btn-sm ' + (group.meet_link ? 'btn-success' : 'btn-outline-info') + ' btn-config-meeting w-100 mb-2" data-id="' +
        group.id +
        '" data-name="' +
        (group.name || group.group_name) +
        '" title="' + (group.meet_link ? 'Ver/Editar Reuniones' : 'Configurar Reuniones') + '">' +
        '<i data-feather="video"></i> ' + (group.meet_link ? 'Ver Reuniones' : 'Configurar Reuniones') + '</button>' +
        '<div class="d-flex gap-2">' +
        '<button class="btn btn-sm btn-outline-primary btn-edit flex-grow-1" data-id="' +
        group.id +
        '" title="Editar">' +
        '<i data-feather="edit-2"></i> Editar</button>' +
        delete_button +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';

    return card_html;
}
// --
function render_pagination() {
    // Renderiza la paginación para los grupos filtrados.
    // Genera los enlaces de paginación con botones de anterior, siguiente y números de página,
    // mostrando un máximo de 5 páginas alrededor de la página actual.
    const pagination = $('#groups-pagination');
    pagination.empty();

    const total_pages = Math.ceil(filtered_groups.length / items_per_page);

    if (total_pages <= 1) return;

    // Botón anterior
    const disabled_prev = current_page === 1 ? 'disabled' : '';
    pagination.append(
        '<li class="page-item ' +
            disabled_prev +
            '">' +
            '<a class="page-link" href="#" data-page="' +
            (current_page - 1) +
            '">Anterior</a>' +
            '</li>',
    );

    // Números de página
    let start_page = Math.max(1, current_page - 2);
    let end_page = Math.min(total_pages, current_page + 2);

    if (start_page > 1) {
        pagination.append(
            '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>',
        );
        if (start_page > 2) {
            pagination.append(
                '<li class="page-item disabled"><span class="page-link">...</span></li>',
            );
        }
    }

    for (let i = start_page; i <= end_page; i++) {
        const active = i === current_page ? 'active' : '';
        pagination.append(
            '<li class="page-item ' +
                active +
                '">' +
                '<a class="page-link" href="#" data-page="' +
                i +
                '">' +
                i +
                '</a>' +
                '</li>',
        );
    }

    if (end_page < total_pages) {
        if (end_page < total_pages - 1) {
            pagination.append(
                '<li class="page-item disabled"><span class="page-link">...</span></li>',
            );
        }
        pagination.append(
            '<li class="page-item"><a class="page-link" href="#" data-page="' +
                total_pages +
                '">' +
                total_pages +
                '</a></li>',
        );
    }

    // Botón siguiente
    const disabled_next = current_page === total_pages ? 'disabled' : '';
    pagination.append(
        '<li class="page-item ' +
            disabled_next +
            '">' +
            '<a class="page-link" href="#" data-page="' +
            (current_page + 1) +
            '">Siguiente</a>' +
            '</li>',
    );
}

// --
function refresh_groups_table() {
    // Refresca la tabla de grupos cargando los datos nuevamente.
    load_groups();
}

function load_supervisors() {
    // Carga los supervisores disponibles desde el servidor o usa el caché si ya están cargados.
    // Pobla el selector de supervisores con los datos obtenidos.
    if (supervisors_cache.length > 0) {
        populate_supervisors_select();
        return;
    }

    // Determinar si pedimos supervisores basados en `user_roles` (solo para practicantes)
    let supervisorsUrl = BASE_URL + 'S_Groups/get_supervisors';
    try {
        const groupTypeElem = $('#group-type');
        const groupType = groupTypeElem.length ? groupTypeElem.val() : null;
        if (!groupType || groupType === 'PRACTICANTE') {
            supervisorsUrl += '?for=practicantes';
        }
    } catch (e) {
        supervisorsUrl += '?for=practicantes';
    }

    $.ajax({
        url: supervisorsUrl,
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function (data) {
            if (data.status === 'OK') {
                supervisors_cache = data.data || [];
                populate_supervisors_select();
            } else {
                // Mostrar como información en lugar de error: a veces no hay supervisores y eso no es un error crítico
                console.info(
                    'Aviso carga supervisores:',
                    data.msg || 'No hay supervisores disponibles.',
                );
            }
        },
        error: function (xhr, status, error) {
            // Aquí sí es un error en la petición (network/500). Mantener como error.
            console.error('Error cargando supervisores:', error);
        },
    });
}

function populate_supervisors_select() {
    // Llena el selector de supervisores con los datos almacenados en el caché.
    const select = $('#group-supervisor');
    select.empty();
    select.append('<option value="">Sin supervisor asignado</option>');

    supervisors_cache.forEach(function (supervisor) {
        select.append(
            '<option value="' +
                supervisor.id +
                '">' +
                supervisor.full_name +
                ' (' +
                supervisor.role +
                ')</option>',
        );
    });
}

// --
function open_group_modal(id = null) {
    // Abre el modal para crear o editar un grupo de practicantes.
    // Carga los datos del grupo en modo edición o inicializa el formulario en modo creación.
    load_supervisors();

    if (id) {
        // Modo edición - buscar el grupo en groups_data
        $('#modal-group-title').text('Editar Grupo');
        $('#group-id').val(id);

        // Buscar el grupo en los datos cargados
        const group = groups_data.find(function (g) {
            return g.id == id;
        });

        if (group) {
            $('#group-code').val(group.code || group.group_code);
            $('#group-name').val(group.name || group.group_name);
            $('#group-type').val(group.group_type || 'PRACTICANTE');
            // Bloquear la edición del tipo de grupo cuando se abre el modal en modo edición
            $('#group-type').prop('disabled', true);
            $('#group-color').val(group.color || '#28a745');
            $('#group-description').val(group.description || '');
            $('#group-capacity').val(group.max_capacity);

            // Mostrar u ocultar supervisor según el tipo de grupo
            if (group.group_type === 'SUPERVISOR') {
                $('#supervisor-container').hide();
                $('#group-supervisor').val('');
            } else {
                $('#supervisor-container').show();
                // Buscar el supervisor principal en el array group.supervisors
                let principalId = '';
                if (Array.isArray(group.supervisors)) {
                    const principal = group.supervisors.find(function (sup) {
                        return (
                            sup.supervisor_type && sup.supervisor_type.toLowerCase() === 'principal'
                        );
                    });
                    if (principal && principal.supervisor_id) {
                        principalId = principal.supervisor_id;
                    } else if (principal && principal.id) {
                        principalId = principal.id;
                    }
                }
                $('#group-supervisor').val(principalId);
            }

            // Inicializar lista de supervisores desde los datos del grupo
            group_supervisors_list = [];
            if (Array.isArray(group.supervisors) && group.supervisors.length > 0) {
                group.supervisors.forEach(function (s) {
                    const sid =
                        s.supervisor_id || s.id || s.user_id || s.supervisorId || s.supervisorId;
                    const stype = (s.supervisor_type || s.type || 'principal').toLowerCase();
                    const sname =
                        s.full_name ||
                        s.name ||
                        s.fullname ||
                        s.first_name ||
                        s.first_name + ' ' + s.last_name ||
                        '';
                    if (sid) {
                        group_supervisors_list.push({ id: sid, type: stype, name: sname });
                    }
                });
            }

            // Guardar IDs originales para detectar eliminación total
            original_group_supervisors_ids = group_supervisors_list.map(function (x) {
                return String(x.id);
            });

            // Renderizar lista en el modal
            render_group_supervisors_list();

            // Mostrar control de estado solo en edición
            $('#status-container').show();
            $('#group-status').prop('checked', group.status == 1);
            update_status_label();

            // Seleccionar supervisor después de cargar las opciones
            setTimeout(function () {
                let principalId = '';
                if (Array.isArray(group.supervisors)) {
                    const principal = group.supervisors.find(function (sup) {
                        return (
                            sup.supervisor_type && sup.supervisor_type.toLowerCase() === 'principal'
                        );
                    });
                    if (principal && principal.supervisor_id) {
                        principalId = principal.supervisor_id;
                    } else if (principal && principal.id) {
                        principalId = principal.id;
                    }
                }
                $('#group-supervisor').val(principalId);
            }, 100);
        }
    } else {
    // Modo creación
        $('#modal-group-title').text('Nuevo Grupo');
        $('#form-group')[0].reset();
        $('#group-id').val('');
    $('#group-type').val('PRACTICANTE'); // Valor por defecto
    // Permitir seleccionar el tipo al crear
    $('#group-type').prop('disabled', false);
        $('#group-color').val('#28a745'); // Verde por defecto
        $('#group-capacity').val(15); // Valor por defecto
        $('#supervisor-container').show(); // Mostrar por defecto
        $('#status-container').hide(); // Ocultar estado en creación
        // Reset lista de supervisores
        group_supervisors_list = [];
        render_group_supervisors_list();
        original_group_supervisors_ids = [];
    }

    $('#modal-group').modal('show');
}

// Renderiza la lista de supervisores añadidos en el modal
function render_group_supervisors_list() {
    const container = $('#group-supervisors-list');
    if (!container.length) return;
    container.empty();

    group_supervisors_list.forEach(function (s) {
        const displayName = s.name && s.name !== '' ? s.name : 'ID ' + s.id;
        const badge = $(
            '<span class="badge bg-primary me-1 mb-1" data-id="' +
                s.id +
                '" style="font-size:0.85rem; padding:0.45rem 0.6rem; display:inline-flex; align-items:center;">' +
                $('<div>').text(displayName).html() +
                ' <small class="text-light ms-1">(' +
                s.type +
                ')</small> <a href="#" class="text-light ms-2 remove-supervisor" style="text-decoration:none;">&times;</a></span>',
        );

        container.append(badge);
    });
}

// NOTA: La gestión de supervisores ahora se hace desde "Mi Grupo" > "Asignación de Supervisores"
// Estos event listeners se mantienen comentados para evitar errores, pero ya no se usan

/*
// Añadir supervisor desde selects
$(document).on('click', '#btn-add-supervisor', function (e) {
    e.preventDefault();

    const supId = $('#group-supervisor').val();
    const supName = $('#group-supervisor option:selected').text();
    const supType = $('#group-supervisor-type').val();

    if (!supId) return;

    // evitar duplicados
    if (group_supervisors_list.find((x) => String(x.id) === String(supId))) {
        return;
    }

    const maxAllowed = parseInt($('#max-supervisors-label').text()) || 3;
    if (group_supervisors_list.length >= maxAllowed) {
        functions.toast_message(
            'warning',
            'Se ha alcanzado el número máximo de supervisores',
            'WARN',
        );
        return;
    }

    group_supervisors_list.push({ id: supId, type: supType, name: supName });
    render_group_supervisors_list();
});

// Remover supervisor
$(document).on('click', '.remove-supervisor', function (e) {
    e.preventDefault();
    const parent = $(this).closest('.badge');
    const id = parent.data('id');
    group_supervisors_list = group_supervisors_list.filter((x) => String(x.id) !== String(id));
    render_group_supervisors_list();
});
*/

// --
function update_status_label() {
    // Actualiza la etiqueta del estado del grupo en el modal.
    // Cambia el texto de la etiqueta según si el grupo está activo o inactivo.
    const isActive = $('#group-status').is(':checked');
    $('#group-status-label').text(isActive ? 'Activo' : 'Inactivo');
}

function save_group() {
    // Guarda o actualiza un grupo de practicantes.
    // Valida el formulario, recopila los datos y realiza una solicitud AJAX para crear o editar el grupo.
    const form = $('#form-group')[0];

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const data = {
        group_code: $('#group-code').val().trim(),
        group_name: $('#group-name').val().trim(),
        group_type: $('#group-type').val(),
        color: $('#group-color').val(),
        description: $('#group-description').val().trim(),
        max_capacity: parseInt($('#group-capacity').val()),
    };

    const id = $('#group-id').val();
    const isEdit = id !== '';

    if (isEdit) {
        data.id = id;
        // Solo incluir status si está en modo edición
        data.status = $('#group-status').is(':checked') ? 1 : 0;
    }

    // NOTA: La asignación de supervisores ya no se envía aquí
    // Ahora se gestiona desde "Mi Grupo" > "Asignación de Supervisores"
    // El código antiguo se mantiene comentado:
    /*
    // Enviar la lista de supervisores construida en el modal cuando exista
    if (Array.isArray(group_supervisors_list) && group_supervisors_list.length > 0) {
        // Normalizar la estructura a { id: <>, type: <> }
        data.supervisors = group_supervisors_list.map(function (s) {
            return { id: s.id, type: s.type };
        });
    } else {
        // Fallback: si hay un supervisor seleccionado en el select legacy, enviarlo como principal
        const supervisorId = $('#group-supervisor').val();
        if (supervisorId) {
            data.supervisors = [
                {
                    id: supervisorId,
                    type: 'principal',
                },
            ];
        }
    }

    // Si estamos en edición y el grupo originalmente tenía supervisores pero ahora la lista quedó vacía,
    // enviar explícitamente supervisors: [] para indicar que queremos eliminar todas las asignaciones.
    if (
        isEdit &&
        Array.isArray(original_group_supervisors_ids) &&
        original_group_supervisors_ids.length > 0 &&
        (!Array.isArray(group_supervisors_list) || group_supervisors_list.length === 0)
    ) {
        data.supervisors = [];
    }
    */

    const url = BASE_URL + 'S_Groups/' + (isEdit ? 'edit_group' : 'create_group');
    const method = isEdit ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        type: method,
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function (response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                $('#modal-group').modal('hide');
                refresh_groups_table();
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al guardar el grupo', 'ERROR');
        },
    });
}

// --
function confirm_delete_group(id, name) {
    // Abre el modal de confirmación para eliminar un grupo.
    // Establece el ID y el nombre del grupo en el modal.
    $('#id-group-delete').val(id);
    $('#name-group-delete').text(name);
    $('#modal-delete').modal('show');
}

function delete_group() {
    // Elimina un grupo mediante una solicitud AJAX DELETE.
    // Procesa la respuesta y actualiza la tabla de grupos si la eliminación es exitosa.
    const id = $('#id-group-delete').val();

    $.ajax({
        url: BASE_URL + 'S_Groups/delete_group',
        type: 'DELETE',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({ id: id }),
        success: function (response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                $('#modal-delete').modal('hide');
                refresh_groups_table();
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al eliminar el grupo', 'ERROR');
        },
    });
}

let current_group_id = null;

// --
function view_group_members(id, name) {
    // Muestra los miembros de un grupo en el offcanvas.
    // Actualiza el encabezado del offcanvas con la información del grupo seleccionado y carga la tabla de miembros.
    current_group_id = id;

    // Buscar datos del grupo
    const group = groups_data.find(function (g) {
        return g.id == id;
    });

    // Variable para saber si es grupo de supervisores
    let is_supervisor_group = false;

    if (group) {
        // Actualizar header del offcanvas
        $('#offcanvas-group-name').text(group.name || group.group_name);
        $('#offcanvas-group-code').text(group.code || group.group_code);
        $('#offcanvas-group-members').text(group.current_members + '/' + group.max_capacity);

        // Verificar si es grupo de supervisores
        is_supervisor_group = group.group_type === 'SUPERVISOR';
        // Guardar el contexto en el offcanvas para que otros modales (detalle) puedan leerlo
        $('#offcanvas-members').data('isSupervisor', is_supervisor_group);

        // Badge de estado
        const status_badge =
            group.status == 1
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-secondary">Inactivo</span>';
        $('#offcanvas-group-badge').html(status_badge);
    }

    // Destruir tabla anterior
    destroy_datatable('#members-table');

    // Crear nueva tabla de miembros
    members_table = $('#members-table').DataTable({
        ajax: {
            url: BASE_URL + 'S_Groups/get_group_members?group_id=' + id,
            cache: false,
            dataSrc: function (json) {
                console.log('Ajax get_group_members response:', json);

                if (!json || !json.data) {
                    return [];
                }

                return Array.isArray(json.data) ? json.data : [];
            },
        },
        columns: [
            {
                data: null,
                render: function (data, type, row) {
                    return (row.names || '') + ' ' + (row.surnames || '');
                },
            },
            { data: 'specialty', defaultContent: '-' },
            {
                data: 'account_enabled',
                class: 'text-center',
                render: function (data, type, row) {
                    if (data == 1) {
                        return '<span class="badge bg-success"><i data-feather="check-circle" style="width: 12px; height: 12px;"></i> Habilitada</span>';
                    } else {
                        return '<span class="badge bg-danger"><i data-feather="x-circle" style="width: 12px; height: 12px;"></i> Deshabilitada</span>';
                    }
                },
            },
            {
                data: 'user_active',
                class: 'text-center',
                render: function (data, type, row) {
                    // Solo mostrar conexión si la cuenta está habilitada
                    if (row.account_enabled != 1) {
                        return '<span class="badge bg-secondary">-</span>';
                    }

                    if (data == 1) {
                        return '<span class="badge bg-success"><i data-feather="wifi" style="width: 12px; height: 12px;"></i> En línea</span>';
                    } else {
                        return '<span class="badge bg-warning"><i data-feather="wifi-off" style="width: 12px; height: 12px;"></i> Desconectado</span>';
                    }
                },
            },
            {
                data: null,
                class: 'text-center',
                render: function (data, type, row) {
                    const completed = parseFloat(row.completed_hours || 0);
                    const required = parseFloat(row.required_hours || 0);
                    const percentage = required > 0 ? Math.round((completed / required) * 100) : 0;

                    let colorClass = 'text-danger';
                    if (percentage >= 100) colorClass = 'text-success';
                    else if (percentage >= 75) colorClass = 'text-warning';
                    else if (percentage >= 50) colorClass = 'text-info';

                    return (
                        '<span class="' +
                        colorClass +
                        ' fw-bold">' +
                        completed +
                        '/' +
                        required +
                        '</span><br>' +
                        '<small class="text-muted">(' +
                        percentage +
                        '%)</small>'
                    );
                },
            },
            {
                data: 'groups_supervised',
                class: 'text-center',
                render: function (data, type, row) {
                    const count = parseInt(data) || 0;
                    if (count === 0) {
                        return '<span class="badge bg-light text-dark">0</span>';
                    } else if (count === 1) {
                        return '<span class="badge bg-info">' + count + '</span>';
                    } else {
                        return '<span class="badge bg-primary">' + count + '</span>';
                    }
                },
            },
            {
                data: null,
                class: 'text-center',
                orderable: false,
                render: function (data, type, row) {
                    // Obtener fecha actual
                    const today = new Date().toISOString().split('T')[0];
                    const dateKey = `attendance_${row.id}_${today}`;

                    // Verificar si hay asistencia marcada hoy en localStorage
                    let initialStatus = '-';
                    let badgeClass = '';
                    const saved = localStorage.getItem(dateKey);

                    if (saved) {
                        const data = JSON.parse(saved);
                        const status = data.status;
                        if (status === 1) {
                            badgeClass = 'bg-success';
                            initialStatus = 'P';
                        } else if (status === 0) {
                            badgeClass = 'bg-danger';
                            initialStatus = 'A';
                        } else if (status === 2) {
                            badgeClass = 'bg-warning text-dark';
                            initialStatus = 'T';
                        } else if (status === 3) {
                            badgeClass = 'bg-info';
                            initialStatus = 'J';
                        }
                    }

                    const statusHTML = badgeClass
                        ? `<span class="badge ${badgeClass}" style="min-width:22px;">${initialStatus}</span>`
                        : initialStatus;

                    return `
                    <div class="btn-group dropdown attendance-dropdown" id="attendance-wrapper-${row.id}" style="min-width:70px;">
                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle d-flex align-items-center gap-1 px-2"
                                data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true"
                                data-id="${row.id}" data-name="${row.names} ${row.surnames}" title="Marcar asistencia">
                            <span class="attendance-status" data-id="${row.id}">${statusHTML}</span>
                            <span class="small fw-light">Marcar</span>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item btn-attendance-present" href="#"
                                   data-id="${row.id}" data-status="1" data-name="${row.names} ${row.surnames}">
                                    <i data-feather="check-circle" class="text-success me-1" style="width: 14px; height: 14px;"></i>
                                    Presente
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item btn-attendance-absent" href="#"
                                   data-id="${row.id}" data-status="0" data-name="${row.names} ${row.surnames}">
                                    <i data-feather="x-circle" class="text-danger me-1" style="width: 14px; height: 14px;"></i>
                                    Ausente
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item btn-attendance-late" href="#"
                                   data-id="${row.id}" data-status="2" data-name="${row.names} ${row.surnames}">
                                    <i data-feather="clock" class="text-warning me-1" style="width: 14px; height: 14px;"></i>
                                    Tardanza
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item btn-attendance-justified" href="#"
                                   data-id="${row.id}" data-status="3" data-name="${row.names} ${row.surnames}">
                                    <i data-feather="file-text" class="text-info me-1" style="width: 14px; height: 14px;"></i>
                                    Justificado
                                </a>
                            </li>
                        </ul>
                    </div>
                    `;
                },
            },
            {
                data: null,
                defaultContent: '',
                class: 'text-center text-nowrap',
                orderable: false,
                width: '140px',
                render: function (data, type, row, meta) {
                    let buttons = '';

                    // Botón ver detalles (ojo)
                    // Obtener contexto actual del offcanvas (fallback a false)
                    const offcanvasIsSupervisor =
                        $('#offcanvas-members').data('isSupervisor') === true ? '1' : '0';
                    buttons +=
                        '<button class="btn btn-sm btn-outline-info btn-view-details me-1" ' +
                        'data-id="' +
                        row.id +
                        '" data-name="' +
                        (row.names + ' ' + row.surnames) +
                        '" data-is-supervisor="' +
                        offcanvasIsSupervisor +
                        '" ' +
                        'title="Ver detalles">' +
                        '<i data-feather="eye" style="width: 14px; height: 14px;"></i></button>';

                    // Botón reasignar
                    buttons +=
                        '<button class="btn btn-sm btn-outline-primary btn-reassign-member me-1" ' +
                        'data-id="' +
                        row.id +
                        '" data-name="' +
                        (row.names + ' ' + row.surnames) +
                        '" ' +
                        'title="Reasignar a otro grupo">' +
                        '<i data-feather="users" style="width: 14px; height: 14px;"></i></button>';

                    // Botón eliminar del grupo
                    buttons +=
                        '<button class="btn btn-sm btn-outline-danger btn-delete-member" ' +
                        'data-id="' +
                        row.id +
                        '" data-name="' +
                        (row.names + ' ' + row.surnames) +
                        '" ' +
                        'title="Eliminar del grupo">' +
                        '<i data-feather="user-x" style="width: 14px; height: 14px;"></i></button>';

                    return buttons;
                },
            },
        ],
        dom: 't<"d-flex justify-content-between"ip>',
        language: {
            url: BASE_URL + 'public/assets/json/languaje-es.json',
        },
        order: [[0, 'asc']], // Ordenar por nombre del practicante
        pageLength: 10,
        columnDefs: [
            {
                // Ocultar columna "GRUPOS" (índice 5) si NO es grupo de supervisores
                targets: 5,
                visible: is_supervisor_group,
            },
        ],
        drawCallback: function () {
            // Calcular estadísticas
            calculate_group_statistics();

            // Re-inicializar iconos Feather después de dibujar la tabla
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        },
    });

    // Mostrar offcanvas
    const offcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvas-members'));
    offcanvas.show();
}

// --
function calculate_group_statistics() {
    // Calcula estadísticas del grupo actual basadas en los datos de la tabla de miembros.
    // Actualiza las estadísticas de activos, inactivos, horas promedio y asistencia promedio en la interfaz.
    const table = $('#members-table').DataTable();
    const data = table.rows().data();

    let active = 0;
    let inactive = 0;
    let total_hours = 0;
    let total_attendance = 0;
    let count = data.length;

    for (let i = 0; i < count; i++) {
        const row = data[i];

        // Contar activos/inactivos
        if (row.status == 1) {
            active++;
        } else {
            inactive++;
        }

        // Sumar horas
        const hours = parseFloat(row.completed_hours || 0);
        total_hours += hours;

        // Calcular asistencia
        const attendance = parseFloat(row.attendance_percentage || 0);
        total_attendance += attendance;
    }

    // Calcular promedios
    const average_hours = count > 0 ? Math.round(total_hours / count) : 0;
    const average_attendance = count > 0 ? Math.round(total_attendance / count) : 0;

    // Actualizar estadísticas en el UI
    $('#stat-active').text(active);
    $('#stat-inactive').text(inactive);
    $('#stat-average-hours').text(average_hours);
    $('#stat-average-attendance').text(average_attendance + '%');
}

function open_mark_attendance_modal(id, name) {
    // Abre el modal para marcar la asistencia de un practicante.
    // Inicializa el formulario con la fecha actual, el ID del practicante y el grupo, y restablece los campos.
    $('#attendance-student-id').val(id);
    $('#attendance-group-id').val(current_group_id);
    $('#attendance-student-name').text(name);

    // Establecer fecha actual (local, no UTC)
    const today = new Date().toLocaleDateString('en-CA'); // Formato YYYY-MM-DD
    $('#attendance-date').val(today);

    // Resetear formulario
    $('#attendance-present').prop('checked', true);
    $('#attendance-hours').val(8);
    $('#attendance-entry-time').val('');
    $('#attendance-exit-time').val('');
    $('#attendance-activity').val('');
    $('#attendance-remarks').val('');
    $('#hours-container').show();
    $('#schedule-container').show();
    $('#activity-container').show();

    $('#modal-mark-attendance').modal('show');

    // Re-inicializar iconos
    setTimeout(function () {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 100);
}

// --
function save_attendance() {
    // Guarda la asistencia de un practicante.
    // Recopila los datos del formulario, valida la fecha y realiza una solicitud AJAX para guardar la asistencia.
    const student_id = $('#attendance-student-id').val();
    const group_id = $('#attendance-group-id').val();
    const date = $('#attendance-date').val();
    const attendance_status = parseInt($('input[name="attendance-status"]:checked').val());
    const entry_time = $('#attendance-entry-time').val();
    const exit_time = $('#attendance-exit-time').val();
    const activity_performed = $('#attendance-activity').val();
    const remarks = $('#attendance-remarks').val();

    let hours = parseFloat($('#attendance-hours').val()) || 0;

    // Si está ausente (0) o con permiso (3), horas = 0
    if (attendance_status == 0 || attendance_status == 3) {
        hours = 0;
    }

    if (!date) {
        functions.toast_message('error', 'Debe seleccionar una fecha', 'ERROR');
        return;
    }

    const data = {
        student_id: student_id,
        group_id: group_id,
        date: date,
        attendance_status: attendance_status,
        worked_hours: hours,
        entry_time: entry_time || null,
        exit_time: exit_time || null,
        activity_performed: activity_performed || null,
        remarks: remarks || null,
    };

    $.ajax({
        url: BASE_URL + 'S_Groups/save_attendance',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function (response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                $('#modal-mark-attendance').modal('hide');

                // Recargar tabla de miembros
                if (members_table) {
                    $('#members-table').DataTable().ajax.reload();
                }

                // Recargar tabla principal de grupos
                refresh_groups_table();
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al guardar asistencia', 'ERROR');
        },
    });
}

function confirm_delete_member(id, name) {
    // Abre el modal de confirmación para eliminar un miembro del grupo.
    // Establece el ID y el nombre del miembro en el modal.
    $('#id-member-delete').val(id);
    $('#name-member-delete').text(name);
    $('#modal-delete-member').modal('show');
}

function delete_group_member() {
    // Elimina un miembro de un grupo mediante una solicitud AJAX POST.
    // Procesa la respuesta y recarga las tablas si la eliminación es exitosa.
    const id = $('#id-member-delete').val();

    if (!id) {
        functions.toast_message('error', 'Error en los datos del miembro', 'ERROR');
        return;
    }

    $.ajax({
        url: BASE_URL + 'S_Groups/remove_group_member',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({ student_id: id }),
        success: function (response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                $('#modal-delete-member').modal('hide');

                // Recargar tabla de miembros si está abierta
                if (members_table) {
                    $('#members-table').DataTable().ajax.reload();
                }

                // Recargar tabla principal de grupos
                refresh_groups_table();
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al eliminar miembro del grupo', 'ERROR');
        },
    });
}

// --
function open_reassign_member_modal(id, name) {
    // Abre el modal para reasignar un miembro a otro grupo.
    // Establece el ID y el nombre del miembro en el modal y carga los grupos disponibles.
    $('#id-member-reassign').val(id);
    $('#name-member-reassign').text(name);

    // Cargar grupos disponibles
    load_available_groups();

    $('#modal-reassign-member').modal('show');
}

function load_available_groups() {
    // Carga los grupos disponibles para reasignar un miembro.
    // Excluye el grupo actual si se proporciona y llena el selector con los grupos disponibles.
    const select = $('#new-group-select');

    // Obtener el grupo actual si se puede
    const current_group = $('#current-group-reassign').val();
    let url = BASE_URL + 'S_Groups/get_available_groups';

    if (current_group) {
        url += '?exclude_group_id=' + current_group;
    }

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function (response) {
            select.empty();
            select.append('<option value="">-- Seleccione un grupo --</option>');

            if (response.status === 'OK' && response.data && response.data.length > 0) {
                response.data.forEach(function (group) {
                    const option =
                        '<option value="' +
                        group.id +
                        '">' +
                        group.group_code +
                        ' - ' +
                        group.group_name +
                        ' (' +
                        group.available_spaces +
                        ' espacios)</option>';
                    select.append(option);
                });
            } else {
                select.append('<option value="" disabled>No hay grupos disponibles</option>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error cargando grupos:', error);
            select.empty();
            select.append('<option value="" disabled>Error al cargar grupos</option>');
        },
    });
}

// --
function reassign_group_member() {
    // Reasigna un miembro a un nuevo grupo.
    // Valida los datos y realiza una solicitud AJAX para reasignar el miembro, recargando las tablas si es exitoso.
    const id = $('#id-member-reassign').val();
    const new_group_id = $('#new-group-select').val();

    if (!id || !new_group_id) {
        functions.toast_message('error', 'Debe seleccionar un grupo de destino', 'ERROR');
        return;
    }

    $.ajax({
        url: BASE_URL + 'S_Groups/reassign_group_member',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({
            student_id: id,
            new_group_id: new_group_id,
        }),
        success: function (response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                $('#modal-reassign-member').modal('hide');

                // Recargar tabla de miembros si está abierta
                if (members_table) {
                    $('#members-table').DataTable().ajax.reload();
                }

                // Recargar tabla principal de grupos
                refresh_groups_table();
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al reasignar miembro', 'ERROR');
        },
    });
}

function view_student_details(id, name, isSupervisor) {
    // Muestra los detalles de un practicante en un modal.
    // Obtiene los datos del practicante desde la tabla actual y llena el modal con la información.
    const table = $('#members-table').DataTable();
    const data = table.rows().data();

    let student = null;
    for (let i = 0; i < data.length; i++) {
        if (data[i].id == id) {
            student = data[i];
            break;
        }
    }

    if (!student) {
        toastr.error('No se encontraron los datos del practicante');
        return;
    }

    // Llenar el modal con los detalles
    $('#detail-name').text(student.names + ' ' + student.surnames);
    $('#detail-document').text(student.document_number || '-');
    $('#detail-email').text(student.email || '-');
    $('#detail-phone').text(student.phone || '-');
    $('#detail-specialty').text(student.specialty || '-');
    $('#detail-interest-area').text(student.interest_area || '-');
    $('#detail-assignment-date').text(student.assignment_date || '-');
    $('#detail-hours').text((student.completed_hours || 0) + '/' + (student.required_hours || 0));
    $('#detail-attendance-percentage').text((student.attendance_percentage || 0) + '%');

    // Estados con badges
    const accountStatus =
        student.account_enabled == 1
            ? '<span class="badge bg-success">Habilitada</span>'
            : '<span class="badge bg-danger">Deshabilitada</span>';
    $('#detail-account-status').html(accountStatus);

    const connectionStatus =
        student.account_enabled != 1
            ? '<span class="badge bg-secondary">-</span>'
            : student.user_active == 1
              ? '<span class="badge bg-success">En línea</span>'
              : '<span class="badge bg-warning">Desconectado</span>';
    $('#detail-connection-status').html(connectionStatus);

    const supervisedGroups = student.groups_supervised || 0;
    // Determinar si debemos mostrar el campo: prioridad al parámetro pasado, si no hay parámetro leer el offcanvas
    const isSupervisorOffcanvas =
        typeof isSupervisor !== 'undefined'
            ? isSupervisor
            : $('#offcanvas-members').data('isSupervisor') === true;
    if (isSupervisorOffcanvas) {
        $('#detail-supervised-groups').text(supervisedGroups);
        // mostrar la fila de la tabla que contiene el td
        const showRow = $('#detail-supervised-groups').closest('tr');
        if (showRow && showRow.length) showRow.show();
    } else {
        // Ocultar el campo y su label para grupos que no son de supervisores
        $('#detail-supervised-groups').text('-');
        // Intentar ocultar el contenedor si existe
        const row = $('#detail-supervised-groups').closest('tr');
        if (row && row.length) row.hide();
    }

    // Mostrar el modal
    $('#modal-student-details').modal('show');
}
// ============================================
// FUNCIONES PARA CONFIGURAR REUNIONES (MEET + HORARIOS)
// ============================================

let schedule_counter = 0;

// --
function open_meeting_config_modal(group_id, group_name) {
    // Abre el modal para configurar el enlace de Meet y horarios del grupo
    $('#meeting-group-id').val(group_id);
    $('#meeting-group-name').text(group_name);

    // Buscar datos del grupo
    const group = groups_data.find(g => g.id == group_id);

    // Limpiar formulario
    $('#meeting-link').val('');
    $('#schedules-container').empty();
    schedule_counter = 0;

    if (group) {
        // Cargar meet_link si existe
        if (group.meet_link) {
            $('#meeting-link').val(group.meet_link);
        }

        // Cargar horarios si existen
        if (group.schedule) {
            try {
                const schedules = typeof group.schedule === 'string' ? JSON.parse(group.schedule) : group.schedule;
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

    // Si no hay horarios, agregar uno vacío por defecto
    if (schedule_counter === 0) {
        add_schedule_row();
    }

    $('#modal-meeting-config').modal('show');

    // Re-inicializar iconos
    setTimeout(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 100);
}

// --
function add_schedule_row(day = 'monday', start_time = '09:00', end_time = '11:00') {
    // Agrega una fila de horario al formulario
    schedule_counter++;

    const row = $(`
        <div class="card mb-2 schedule-row" data-schedule-id="${schedule_counter}">
            <div class="card-body p-2">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label mb-1 small">Día:</label>
                        <select class="form-select form-select-sm schedule-day" required>
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
                        <label class="form-label mb-1 small">Hora Inicio:</label>
                        <input type="time" class="form-control form-control-sm schedule-start" value="${start_time}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1 small">Hora Fin:</label>
                        <input type="time" class="form-control form-control-sm schedule-end" value="${end_time}" required>
                    </div>
                    <div class="col-md-2 text-center">
                        <label class="form-label mb-1 small">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-remove-schedule" data-schedule-id="${schedule_counter}">
                            <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `);

    $('#schedules-container').append(row);

    // Re-inicializar iconos
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// --
function save_meeting_config() {
    // Guarda la configuración de reuniones (Meet link y horarios)
    const group_id = $('#meeting-group-id').val();
    const meet_link = $('#meeting-link').val().trim();

    // Recopilar horarios
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

    // Validar que al menos haya un dato para guardar
    if (!meet_link && schedules.length === 0) {
        functions.toast_message('warning', 'Debe ingresar al menos el enlace de Meet o un horario', 'WARN');
        return;
    }

    const data = {
        id: group_id
    };

    if (meet_link) {
        data.meet_link = meet_link;
    }

    if (schedules.length > 0) {
        data.schedule = schedules;
    }

    // Enviar al servidor
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
                refresh_groups_table();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al guardar la configuración', 'ERROR');
        }
    });
}

// -- Document Ready
$(document).ready(function () {
    // Inicializa la carga de grupos y configura los eventos de la interfaz.
    // Maneja clics en botones, cambios en filtros, y validaciones en tiempo real.
    load_groups();

    // Botón nuevo grupo
    $(document).on('click', '#btn-new-group', function () {
        open_group_modal();
    });

    // Nota: no necesitamos un handler para restaurar la fila de "Grupos Supervisa".
    // La visibilidad se calcula cada vez que se abre el modal leyendo el dato del offcanvas.

    // Botón guardar grupo
    $(document).on('click', '#btn-save-group', function () {
        save_group();
    });

    // Botón editar grupo
    $(document).on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        open_group_modal(id);
    });

    // Botón eliminar grupo
    $(document).on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        confirm_delete_group(id, name);
    });

    // Confirmar eliminación
    $(document).on('click', '#btn-confirm-delete', function () {
        delete_group();
    });

    // Ver miembros del grupo
    $(document).on('click', '.btn-view-members', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        view_group_members(id, name);
    });

    // Búsqueda y filtros
    $(document).on('input', '#search-groups', function () {
        apply_filters();
    });

    $(document).on('change', '#filter-status', function () {
        apply_filters();
    });

    $(document).on('change', '#filter-availability', function () {
        apply_filters();
    });

    // Paginación
    $(document).on('click', '#groups-pagination .page-link', function (e) {
        e.preventDefault();
        const page = parseInt($(this).data('page'));
        if (!isNaN(page) && page > 0) {
            current_page = page;
            render_groups();
            // Scroll to top
            $('html, body').animate({ scrollTop: 0 }, 300);
        }
    });

    // Validación en tiempo real del código de grupo
    $(document).on('input', '#group-code', function () {
        let value = $(this)
            .val()
            .toUpperCase()
            .replace(/[^A-Z0-9-]/g, '');
        $(this).val(value);
    });

    // Validación de capacidad
    $(document).on('input', '#group-capacity', function () {
        const value = parseInt($(this).val());
        if (value < 1) $(this).val(1);
        if (value > 50) $(this).val(50);
    });

    // Cambio de estado del grupo
    $(document).on('change', '#group-status', function () {
        update_status_label();
    });

    // Cambio de tipo de grupo
    $(document).on('change', '#group-type', function () {
        const groupType = $(this).val();
        if (groupType === 'SUPERVISOR') {
            // Oculta y limpia el selector de supervisor
            $('#supervisor-container').hide();
            $('#group-supervisor').val('');
            $('#group-color').val('#007bff'); // Azul para supervisores
        } else {
            $('#supervisor-container').show();
            $('#group-color').val('#28a745'); // Verde para practicantes
        }
    });

    // Botón eliminar miembro del grupo
    $(document).on('click', '.btn-delete-member', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        confirm_delete_member(id, name);
    });

    // Botón reasignar miembro a otro grupo
    $(document).on('click', '.btn-reassign-member', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        open_reassign_member_modal(id, name);
    });

    // Confirmar eliminación de miembro
    $(document).on('click', '#btn-confirm-delete-member', function () {
        delete_group_member();
    });

    // Confirmar reasignación de miembro
    $(document).on('click', '#btn-confirm-reassign-member', function () {
        reassign_group_member();
    });

    // Botón ver detalles del practicante
    $(document).on('click', '.btn-view-details', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        // Leer atributo data-is-supervisor (1|0) y convertir a booleano
        const isSupervisorAttr = $(this).attr('data-is-supervisor');
        const isSupervisor =
            typeof isSupervisorAttr !== 'undefined' && String(isSupervisorAttr) === '1';
        view_student_details(id, name, isSupervisor);
    });

    // Eventos para dropdown de asistencia
    $(document).on('click', '.btn-attendance-present', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');
        mark_quick_attendance(id, name, 1, 'Presente');
    });

    $(document).on('click', '.btn-attendance-absent', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');
        mark_quick_attendance(id, name, 0, 'Ausente');
    });

    $(document).on('click', '.btn-attendance-late', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');
        mark_quick_attendance(id, name, 2, 'Tardanza');
    });

    $(document).on('click', '.btn-attendance-justified', function (e) {
        e.preventDefault();
        const id = $(this).data('id');
        const name = $(this).data('name');
        mark_quick_attendance(id, name, 3, 'Justificado');
    });

    // Confirmar asistencia (para modal detallado)
    $(document).on('click', '#btn-confirm-attendance', function () {
        save_attendance();
    });

    // Evento para cambiar a la pestaña de asistencias
    $(document).on('click', '#tab-attendances', function () {
        load_group_attendance_history();
    });

    // Botón marcar asistencia grupal
    $(document).on('click', '#btn-mark-group-attendance', function () {
        open_mark_group_attendance_modal();
    });

    // Controlar visibilidad de campos según estado de asistencia
    $(document).on('change', 'input[name="attendance-status"]', function () {
        const status = $(this).val();

        if (status == 1 || status == 2) {
            // Presente o Tardanza
            $('#hours-container').show();
            $('#schedule-container').show();
            $('#activity-container').show();
            if ($('#attendance-hours').val() == 0) {
                $('#attendance-hours').val(8);
            }
        } else if (status == 0) {
            // Ausente
            $('#hours-container').hide();
            $('#schedule-container').hide();
            $('#activity-container').hide();
            $('#attendance-hours').val(0);
            $('#attendance-entry-time').val('');
            $('#attendance-exit-time').val('');
            $('#attendance-activity').val('');
        } else if (status == 3) {
            // Permiso
            $('#hours-container').hide();
            $('#schedule-container').hide();
            $('#activity-container').show();
            $('#attendance-hours').val(0);
            $('#attendance-entry-time').val('');
            $('#attendance-exit-time').val('');
        }
    });

    // Calcular horas automáticamente al cambiar hora de entrada o salida
    $(document).on('change', '#attendance-entry-time, #attendance-exit-time', function () {
        const entry = $('#attendance-entry-time').val();
        const exit = $('#attendance-exit-time').val();

        if (entry && exit) {
            const [h1, m1] = entry.split(':').map(Number);
            const [h2, m2] = exit.split(':').map(Number);

            const entry_minutes = h1 * 60 + m1;
            const exit_minutes = h2 * 60 + m2;

            let difference = exit_minutes - entry_minutes;

            // Si la salida es menor que la entrada, asumimos que cruzó medianoche
            if (difference < 0) {
                difference += 24 * 60;
            }

            const hours = (difference / 60).toFixed(2);
            $('#attendance-hours').val(hours);
        }
    });

    // Reinicializar iconos cuando se muestran tabs
    $(document).on('shown.bs.tab', 'button[data-bs-toggle="tab"]', function (e) {
        setTimeout(function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        }, 50);
    });

    // Limpiar formulario al cerrar modal
    $('#modal-group').on('hidden.bs.modal', function () {
        $('#form-group')[0].reset();
        $('#group-id').val('');
    });

    // Limpiar modales de miembros al cerrar
    $('#modal-delete-member').on('hidden.bs.modal', function () {
        $('#id-member-delete').val('');
        $('#current-group-delete').val('');
    });

    $('#modal-reassign-member').on('hidden.bs.modal', function () {
        $('#id-member-reassign').val('');
        $('#current-group-reassign').val('');
        $('#new-group-select')
            .empty()
            .append('<option value="">-- Seleccione un grupo --</option>');
    });

    // Botón configurar reuniones
    $(document).on('click', '.btn-config-meeting', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        open_meeting_config_modal(id, name);
    });

    // Botón agregar horario
    $(document).on('click', '#btn-add-schedule', function () {
        add_schedule_row();
    });

    // Botón eliminar horario
    $(document).on('click', '.btn-remove-schedule', function () {
        const schedule_id = $(this).data('schedule-id');
        $(`.schedule-row[data-schedule-id="${schedule_id}"]`).remove();

        // Si no quedan horarios, agregar uno por defecto
        if ($('.schedule-row').length === 0) {
            add_schedule_row();
        }
    });

    // Botón guardar configuración de reuniones
    $(document).on('click', '#btn-save-meeting-config', function () {
        save_meeting_config();
    });

    // Limpiar modal de configuración al cerrar
    $('#modal-meeting-config').on('hidden.bs.modal', function () {
        $('#meeting-group-id').val('');
        $('#meeting-link').val('');
        $('#schedules-container').empty();
        schedule_counter = 0;
    });
});

// --
function load_group_attendance_history() {
    // Carga el historial de asistencias de un grupo específico.
    // Realiza una solicitud AJAX para obtener los datos y actualiza la tabla de asistencias.
    if (!current_group_id) {
        toastr.error('No hay grupo seleccionado');
        return;
    }

    $.ajax({
        url: BASE_URL + 'S_Groups/get_attendance_history',
        method: 'GET',
        data: { group_id: current_group_id },
        dataType: 'json',
        beforeSend: function () {
            const tbody = $('#attendances-table tbody');
            tbody.html(`
                <tr>
                    <td colspan="6" class="text-center">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </td>
                </tr>
            `);
        },
        success: function (response) {
            if (response.status === 'OK') {
                display_group_attendance_history(response.data);
            } else {
                toastr.error(response.msg || 'Error al cargar historial de asistencias');
                const tbody = $('#attendances-table tbody');
                tbody.html(
                    '<tr><td colspan="6" class="text-center text-muted">Error al cargar datos</td></tr>',
                );
            }
        },
        error: function (xhr, status, error) {
            console.error(
                'Error al cargar historial:',
                error,
                'Respuesta cruda:',
                xhr.responseText,
            );
            let msg = 'Error de conexión al cargar historial';
            if (xhr.responseText && xhr.responseText.trim().startsWith('<')) {
                msg = 'El servidor devolvió HTML (posible error de PHP o sesión expirada)';
            }
            toastr.error(msg);
            const tbody = $('#attendances-table tbody');
            tbody.html('<tr><td colspan="7" class="text-center text-danger">' + msg + '</td></tr>');
        },
    });
}

// --
function display_group_attendance_history(history) {
    // Muestra el historial de asistencias de un grupo en la tabla de asistencias.
    // Genera filas con información de cada practicante, incluyendo estadísticas y un botón para ver el historial detallado.
    const tbody = $('#attendances-table tbody');
    tbody.empty();

    if (!history || history.length === 0) {
        tbody.html(
            '<tr><td colspan="7" class="text-center text-muted">No hay registros de asistencia</td></tr>',
        );
        return;
    }

    history.forEach(function (record) {
        const percentage = parseFloat(record.attendance_percentage) || 0;
        const percentageBadge =
            percentage >= 80 ? 'bg-success' : percentage >= 60 ? 'bg-warning' : 'bg-danger';

        const row = `
            <tr>
                <td>${record.student_name}</td>
                <td>${record.formatted_date}</td>
                <td class="text-center">
                    <span class="badge bg-success">${record.present || 0}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-danger">${record.absent || 0}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-warning">${record.late || 0}</span>
                </td>
                <td class="text-center">
                    <span class="badge bg-info">${record.permissions || 0}</span>
                </td>
                <td class="text-center">
                    <span class="badge ${percentageBadge}">${percentage.toFixed(1)}%</span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info btn-view-attendance-history"
                            data-id="${record.student_id}"
                            data-name="${record.student_name}">
                        <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });

    // Re-inicializar iconos Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// --
function mark_quick_attendance(student_id, name, status, status_text) {
    // Marca la asistencia rápida de un practicante.
    // Muestra una confirmación, envía los datos al servidor y actualiza la interfaz sin recargar toda la tabla.
    if (!current_group_id) {
        toastr.error('No hay grupo seleccionado');
        return;
    }

    // Confirmar acción
    const today_formatted = new Date().toLocaleDateString('es-PE');
    const message = `¿Marcar a <strong>${name}</strong> como <strong>${status_text}</strong> para hoy ${today_formatted}?`;

    Swal.fire({
        title: 'Confirmar Asistencia',
        html: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, marcar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            // Preparar datos - usar fecha local (no UTC)
            const current_date = new Date().toLocaleDateString('en-CA'); // Formato YYYY-MM-DD

            const attendances = [
                {
                    student_id: student_id,
                    status: status,
                    remarks: status === 3 ? 'Justificado' : status === 2 ? 'Tardanza' : '',
                },
            ];

            // Enviar datos
            $.ajax({
                url: BASE_URL + 'S_Groups/mark_attendance',
                method: 'POST',
                data: JSON.stringify({
                    group_id: current_group_id,
                    date: current_date,
                    attendances: attendances,
                }),
                contentType: 'application/json',
                dataType: 'json',
                beforeSend: function () {
                    Swal.fire({
                        title: 'Guardando...',
                        html: 'Registrando asistencia',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });
                },
                success: function (response) {
                    if (response.status === 'OK') {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: `Asistencia de ${name} marcada como ${status_text}`,
                            icon: 'success',
                            timer: 1600,
                            showConfirmButton: false,
                        });

                        // Guardar en localStorage con la fecha actual
                        const dateKey = `attendance_${student_id}_${current_date}`;
                        localStorage.setItem(
                            dateKey,
                            JSON.stringify({
                                status: status,
                                text: status_text,
                                date: current_date,
                            }),
                        );

                        // Actualizar visual del botón sin recargar toda la tabla
                        const wrapper = document.querySelector(
                            `#attendance-wrapper-${student_id} .attendance-status`,
                        );
                        if (wrapper) {
                            let badgeClass = 'bg-secondary';
                            let text = '-';
                            if (status === 1) {
                                badgeClass = 'bg-success';
                                text = 'P';
                            } else if (status === 0) {
                                badgeClass = 'bg-danger';
                                text = 'A';
                            } else if (status === 2) {
                                badgeClass = 'bg-warning text-dark';
                                text = 'T';
                            } else if (status === 3) {
                                badgeClass = 'bg-info';
                                text = 'J';
                            }
                            wrapper.innerHTML = `<span class="badge ${badgeClass}" style="min-width:22px;">${text}</span>`;
                        }

                        // Actualizar estadísticas (sin redibujar toda la tabla si no es necesario)
                        calculate_group_statistics();

                        // Si está en la pestaña de asistencias, recargar historial para reflejar nuevo conteo
                        if (
                            document.getElementById('tab-attendances').classList.contains('active')
                        ) {
                            load_group_attendance_history();
                        }
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.msg || 'Error al guardar asistencia',
                            icon: 'error',
                        });
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor',
                        icon: 'error',
                    });
                },
            });
        }
    });
}
// --
$(document).on('click', '.btn-view-attendance-history', function (e) {
    // Carga y muestra el historial de asistencia de un practicante en un modal.
    // Realiza una solicitud AJAX para obtener los datos y llena la tabla con el historial.
    e.preventDefault();
    const student_id = $(this).data('id');
    const student_name = $(this).data('name');

    $('#history-student-name').text(student_name);

    $.ajax({
        url: BASE_URL + 'S_Groups/get_student_attendance_history',
        type: 'POST',
        data: { student_id: student_id },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'OK') {
                const tbody = $('#attendance-history-tbody');
                tbody.empty();

                if (!response.data || response.data.length === 0) {
                    tbody.html(
                        '<tr><td colspan="5" class="text-center text-muted">No hay historial de asistencia</td></tr>',
                    );
                } else {
                    response.data.forEach(function (item) {
                        let statusBadge = '';
                        if (item.attendance_status === 1) {
                            statusBadge = '<span class="badge bg-success">Presente</span>';
                        } else if (item.attendance_status === 0) {
                            statusBadge = '<span class="badge bg-danger">Ausente</span>';
                        } else if (item.attendance_status === 2) {
                            statusBadge = '<span class="badge bg-warning">Tardanza</span>';
                        } else if (item.attendance_status === 3) {
                            statusBadge = '<span class="badge bg-info">Justificado</span>';
                        }

                        const row = `
                            <tr>
                                <td>${item.formatted_date}</td>
                                <td>${statusBadge}</td>
                                <td>${item.formatted_time}</td>
                                <td>${item.recorded_by_name || 'Sistema'}</td>
                                <td>${item.remarks || '-'}</td>
                            </tr>
                        `;
                        tbody.append(row);
                    });
                }

                $('#modal-attendance-history').modal('show');

                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.msg || 'Error al cargar historial',
                    icon: 'error',
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error de conexión',
                text: 'No se pudo cargar el historial',
                icon: 'error',
            });
        },
    });
});

// ============================================
// FUNCIONES PARA AGREGAR USUARIOS AL GRUPO
// ============================================

let available_users = [];

// --
function open_add_user_modal() {
    // Abre el modal para agregar un usuario a un grupo.
    // Valida el grupo actual, verifica la disponibilidad de espacios y carga los usuarios disponibles.
    if (!current_group_id) {
        Swal.fire({
            title: 'Error',
            text: 'No se pudo identificar el grupo actual',
            icon: 'error',
        });
        return;
    }

    // Obtener información del grupo actual
    const group = groups_data.find((g) => g.id == current_group_id);
    if (!group) {
        Swal.fire({
            title: 'Error',
            text: 'No se encontró información del grupo',
            icon: 'error',
        });
        return;
    }

    // Verificar si el grupo tiene espacio
    if (group.available_spaces <= 0) {
        Swal.fire({
            title: 'Grupo Completo',
            text: 'El grupo ha alcanzado su capacidad máxima. No se pueden agregar más miembros.',
            icon: 'warning',
        });
        return;
    }

    $('#target-group-id').val(current_group_id);
    $('#target-group-name').text(group.group_name);

    // Mostrar modal
    $('#modal-add-user').modal('show');

    // Cargar usuarios disponibles
    load_available_users();
}

// --
function load_available_users() {
    // Carga los usuarios disponibles para agregar a un grupo.
    // Realiza una solicitud AJAX y renderiza la lista de usuarios disponibles.
    const group_id = $('#target-group-id').val();

    $('#users-list').html(`
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando usuarios...</p>
        </div>
    `);

    $.ajax({
        url: BASE_URL + 'S_Groups/get_available_student_users?group_id=' + group_id,
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function (response) {
            if (response.status === 'OK' && response.data) {
                available_users = response.data;
                render_available_users(available_users);
            } else {
                $('#users-list').html(`
                    <div class="alert alert-warning text-center">
                        <i data-feather="alert-circle"></i>
                        <p class="mb-0 mt-2">No hay usuarios disponibles para agregar</p>
                    </div>
                `);
            }
        },
        error: function (xhr, status, error) {
            $('#users-list').html(`
                <div class="alert alert-danger text-center">
                    <i data-feather="alert-triangle"></i>
                    <p class="mb-0 mt-2">Error al cargar usuarios: ${error}</p>
                </div>
            `);
        },
    });
}
// --
function render_available_users(users) {
    const container = $('#users-list');
    container.empty();

    if (users.length === 0) {
        container.html(`
            <div class="alert alert-info text-center">
                <i data-feather="info"></i>
                <p class="mb-0 mt-2">No hay usuarios disponibles para agregar a este grupo</p>
            </div>
        `);
        return;
    }

    users.forEach(function (user) {
        // Cambia aquí: Si group_display no es "Sin grupo", el usuario ya tiene grupo
        const has_group = user.group_display && user.group_display !== 'Sin grupo';

        const group_badge = has_group
            ? `<span class="badge bg-warning text-dark">${user.group_display}</span>`
            : `<span class="badge bg-success">Disponible</span>`;

        const action_text = has_group ? 'Reasignar' : 'Agregar';
        const btn_class = has_group ? 'btn-warning' : 'btn-success';

        // Badge de rol
        const role_badge =
            user.role === 'SUPERVISOR'
                ? `<span class="badge bg-primary ms-1">Supervisor</span>`
                : `<span class="badge bg-success ms-1">Practicante</span>`;

        // contenedor donde insertaremos badges de supervisiones (vacío inicialmente)
        const supervisionsContainerId = 'supervisions-' + user.id;

        const card = `
            <div class="card mb-2 user-card" data-user-id="${user.id}" data-name="${user.full_name.toLowerCase()}" data-email="${user.email.toLowerCase()}">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">${user.full_name} ${role_badge}</h6>
                            <small class="text-muted">
                                <i data-feather="mail" style="width: 12px; height: 12px;"></i> ${user.email}
                                ${user.phone ? `<i data-feather="phone" class="ms-2" style="width: 12px; height: 12px;"></i> ${user.phone}` : ''}
                            </small>
                            <div class="mt-1">${group_badge}</div>
                            <div id="${supervisionsContainerId}" class="mt-1"></div>
                        </div>
                        <div>
                            <button class="btn ${btn_class} btn-sm" onclick="confirm_add_user(${user.id}, '${user.full_name}', '${user.group_display}', ${has_group}, ${user.role ? user.role.toUpperCase() === 'SUPERVISOR' : false})">
                                <i data-feather="user-plus"></i> ${action_text}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.append(card);

        // Si el usuario es SUPERVISOR, pedir sus grupos supervisados y renderizar badges
        if (user.role && user.role.toUpperCase().indexOf('SUPERVISOR') !== -1) {
            (function (uid, containerId) {
                $.ajax({
                    url: BASE_URL + 'S_Groups/get_supervisions_by_user',
                    type: 'GET',
                    dataType: 'json',
                    data: { user_id: uid },
                    cache: false,
                    success: function (resp) {
                        if (resp.status === 'OK' && resp.data && resp.data.length) {
                            let html = '';
                            resp.data.forEach(function (g) {
                                html += `<span class="badge bg-info text-white me-1">${g.group_name}</span>`;
                            });
                            $('#' + containerId).html(html);
                        } else {
                            // opcional: mostrar nada o texto sutil
                            // $('#' + containerId).html('<small class="text-muted">Sin supervisiones</small>');
                        }
                        if (typeof feather !== 'undefined') feather.replace();
                    },
                    error: function () {
                        // ignorar errores de este request para no romper el modal
                    },
                });
            })(user.id, supervisionsContainerId);
        }
    });

    // Re-inicializar iconos Feather
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// --
function confirm_add_user(user_id, name, current_group, has_group, isSupervisor) {
    // Muestra una confirmación para agregar o reasignar un usuario a un grupo.
    // Prepara el mensaje según si el usuario ya pertenece a otro grupo y envía la solicitud al confirmar.
    const group_id = $('#target-group-id').val();
    // Buscar el grupo destino en groups_data
    const group = groups_data.find((g) => g.id == group_id);
    const group_name = group ? group.group_name || group.name || '' : '';

    let message = '';
    if (has_group) {
        message = `¿Está seguro de reasignar a <strong>${name}</strong> desde <strong>${current_group}</strong> a <strong>${group_name}</strong>?`;

        // Si el usuario es supervisor y el grupo destino es PRACTICANTE, advertir que se eliminarán sus asignaciones
        if (
            isSupervisor &&
            group &&
            group.group_type &&
            group.group_type.toUpperCase() === 'PRACTICANTE'
        ) {
            message += `<br><br><span class="text-danger"><strong>Al reasignar a ${name} se eliminará como supervisor de todos los grupos que supervisa.</strong></span>`;
        }
    } else {
        message = `¿Está seguro de agregar a <strong>${name}</strong> al grupo <strong>${group_name}</strong>?`;
    }

    Swal.fire({
        title: has_group ? 'Reasignar Usuario' : 'Agregar Usuario',
        html: message,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: has_group ? 'Sí, reasignar' : 'Sí, agregar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
    }).then((result) => {
        if (result.isConfirmed) {
            add_user_to_group(user_id, group_id);
        }
    });
}

function add_user_to_group(user_id, group_id) {
    // Agrega o reasigna un usuario a un grupo mediante una solicitud AJAX.
    // Actualiza la tabla de miembros y los grupos si la operación es exitosa.
    $.ajax({
        url: BASE_URL + 'S_Groups/add_user_to_group',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({
            user_id: user_id,
            group_id: group_id,
        }),
        success: function (response) {
            if (response.status === 'OK') {
                Swal.fire({
                    title: '¡Éxito!',
                    text: response.msg,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                });

                // Cerrar modal
                $('#modal-add-user').modal('hide');

                // Recargar tabla de miembros
                if (members_table) {
                    members_table.ajax.reload(null, false);
                }

                // Recargar grupos para actualizar contadores
                load_groups();
            } else {
                Swal.fire({
                    title: 'Error',
                    text: response.msg || 'Error al agregar el usuario',
                    icon: 'error',
                });
            }
        },
        error: function (xhr, status, error) {
            Swal.fire({
                title: 'Error de conexión',
                text: 'No se pudo agregar el usuario: ' + error,
                icon: 'error',
            });
        },
    });
}
// --
$(document).on('keyup', '#search-users', function () {
    // Filtra las tarjetas de usuarios en el modal de agregar usuario según el texto de búsqueda.
    // Muestra u oculta las tarjetas basándose en coincidencias con el nombre o email.
    const search = $(this).val().toLowerCase();

    if (search === '') {
        $('.user-card').show();
        return;
    }

    $('.user-card').each(function () {
        const name = $(this).data('name') || '';
        const email = $(this).data('email') || '';

        if (name.includes(search) || email.includes(search)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});
