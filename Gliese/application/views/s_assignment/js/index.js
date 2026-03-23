// -- Variables globales
let isFirstAssignLoad = true;

// -- Functions
// --
function destroy_datatable() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#assign-table')) {
        try {
            $('#assign-table').DataTable().clear().destroy();
        } catch (e) {
            try {
                $('#assign-table').dataTable().fnDestroy();
            } catch (e) {
                /* ignore */
            }
        }
    }
}

// --
function refresh_datatable() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#assign-table')) {
        $('#assign-table').DataTable().ajax.reload(null, false);
    }
}

// -- Carga la tabla de estudiantes aceptados sin credenciales.
function load_datatable() {
    destroy_datatable();

    let dataTable = $('#assign-table').DataTable({
        // Carga la tabla de estudiantes aceptados sin credenciales.
        // Utiliza DataTables para mostrar los datos y permite asignar estudiantes a grupos.
        ajax: {
            url: BASE_URL + 'S_Assignment/get_students_accepted',
            cache: false,
            dataSrc: function (json) {
                // Procesa la respuesta del servidor y muestra mensajes en español si es necesario.
                console.log('Ajax get_students_accepted response:', json);

                if (!json) return [];

                // Mostrar el aviso solo una vez si no hay estudiantes para asignar
                if (json.status === 'ERROR' && json.msg === 'No hay estudiantes para asignar.') {
                    if (isFirstAssignLoad) {
                        functions.toast_message(json.type, json.msg, json.status);
                    }
                    isFirstAssignLoad = false;
                } else if (json.status === 'OK') {
                    isFirstAssignLoad = false;
                }

                if (Array.isArray(json.data)) return json.data;
                if (Array.isArray(json.result)) return json.result;
                if (Array.isArray(json)) return json;

                return [];
            },
        },
        columns: [
            {
                data: null,
                defaultContent: '-',
                render: function (data, type, row) {
                    // Muestra nombres y apellidos del estudiante.
                    return (row.first_names || '') + ' ' + (row.last_names || '');
                },
            },
            { data: 'document_number', defaultContent: '-' },
            { data: 'email', defaultContent: '-' },
            { data: 'institution', defaultContent: '-' },
            { data: 'specialty', defaultContent: '-' },
            { data: 'interest_area', defaultContent: '-' },
            {
                data: null,
                defaultContent: '',
                class: 'center text-nowrap',
                orderable: false,
                width: '100px',
                render: function (data, type, row, meta) {
                    // Botón para asignar estudiante.
                    return (
                        '<button class="btn btn-sm btn-primary btn-round btn-icon btn_assign" data-id="' +
                        row.id +
                        '" data-name="' +
                        (row.first_names || '') +
                        ' ' +
                        (row.last_names || '') +
                        '" title="Asignar">' +
                        feather.icons['user-check'].toSvg({ class: 'font-small-4' }) +
                        '</button>'
                    );
                },
            },
        ],
        // Usamos un DOM personalizado sin la sección de botones (B) porque en
        // esta vista de 'Asignación' no queremos mostrar Exportar / Agregar.
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        // No mostrar botones en esta tabla (específico para la zona Asignación)
        buttons: [],
        language: {
            url: BASE_URL + 'public/assets/json/languaje-es.json',
        },
        order: [[0, 'asc']],
    });

    dataTable.on('xhr', function () {
        // Muestra en consola la cantidad de filas cargadas.
        console.log('DataTable loaded rows:', dataTable.rows().count());
    });
}

// -- Carga los grupos activos y los muestra en el selector correspondiente.
function load_groups() {
    // Carga los grupos activos y los muestra en el selector correspondiente.
    // Utiliza los datos del backend y muestra los grupos con su disponibilidad.
    $.ajax({
        url: BASE_URL + 'S_Assignment/get_groups',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function (data) {
            if (data.status === 'OK') {
                const select = $('#assign-group');
                select.empty();
                select.append('<option value="">Seleccione un grupo</option>');

                data.data.forEach(function (group) {
                    const available = group.available_spaces;
                    const text =
                        group.group_name +
                        ' (' +
                        group.group_code +
                        ') - Disponibles: ' +
                        available +
                        '/' +
                        group.max_capacity;
                    const disabled = available <= 0 ? ' disabled' : '';
                    // Guardamos el código y tipo del grupo como data-attributes
                    select.append(
                        '<option value="' +
                            group.id +
                            '" data-code="' +
                            group.group_code +
                            '" data-type="' +
                            group.group_type +
                            '"' +
                            disabled +
                            '>' +
                            text +
                            '</option>',
                    );
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        },
    });
}

// -- Carga los roles activos.
function load_roles(group_type) {
    // Carga los roles activos y los muestra en el selector correspondiente.
    // Si el grupo es de tipo SUPERVISOR, solo se muestra el rol SUPERVISOR.
    // Para grupos de tipo PRACTICANTE, solo se muestra el rol PRACTICANTE.
    $.ajax({
        url: BASE_URL + 'S_Assignment/get_roles',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function (data) {
            if (data.status === 'OK') {
                const select = $('#assign-role');
                select.empty();

                const roles = Array.isArray(data.data) ? data.data : [];

                // Buscar roles específicos
                const practitioner = roles.find(
                    (r) => (r.description || '').toLowerCase() === 'practicante',
                );
                const supervisor = roles.find(
                    (r) => (r.description || '').toLowerCase() === 'supervisor',
                );

                // Si el grupo es de tipo SUPERVISOR, solo mostrar rol SUPERVISOR
                if (group_type && group_type === 'SUPERVISOR') {
                    if (supervisor) {
                        select.append(
                            '<option value="' + supervisor.id + '" selected>SUPERVISOR</option>',
                        );
                        select.val(supervisor.id).trigger('change');
                    } else {
                        select.append('<option value="">Rol SUPERVISOR no encontrado</option>');
                    }
                    return;
                }

                // Para grupos de tipo PRACTICANTE, mostrar solo PRACTICANTE
                if (practitioner) {
                    select.append(
                        '<option value="' + practitioner.id + '" selected>PRACTICANTE</option>',
                    );
                    select.val(practitioner.id).trigger('change');
                } else {
                    select.append('<option value="">Rol PRACTICANTE no encontrado</option>');
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        },
    });
}

// -- Cargar grupos al modal de asignación
function assign_student(id, name) {
    // Abre el modal para asignar estudiante y carga los selects correspondientes.
    // Limpia los campos y carga los grupos y roles activos.
    //
    $('#assign-id-student').val(id);
    $('#assign-name-student').text(name);

    // Limpiar selects
    $('#assign-group').html('<option value="">Seleccione un grupo</option>');
    $('#assign-role').html('<option value="">Seleccione un grupo primero</option>');

    load_groups();

    $('#modal-assign').modal('show');
}

// -- Confirmar asignación
function confirm_assign() {
    // Confirma la asignación del estudiante y envía los datos al backend.
    // Valida los campos requeridos y muestra mensajes en español.
    const id_student = $('#assign-id-student').val();
    const id_group = $('#assign-group').val();
    const id_role = $('#assign-role').val();

    if (!id_group) {
        functions.toast_message('warning', 'Debe seleccionar un grupo', 'WARNING');
        return;
    }

    if (!id_role) {
        functions.toast_message('warning', 'Debe seleccionar un rol', 'WARNING');
        return;
    }

    $.ajax({
        url: BASE_URL + 'S_Assignment/assign_student',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({
            id_student: id_student,
            id_group: id_group,
            id_role: id_role,
        }),
        success: function (data) {
            functions.toast_message(data.type, data.msg, data.status);
            if (data.status === 'OK') {
                $('#modal-assign').modal('hide');

                // Mostrar credenciales generadas
                $('#cred-username').val(data.data.username);
                $('#cred-password').val(data.data.password);
                $('#cred-grupo').text(data.data.group);
                $('#modal-credentials').modal('show');

                refresh_datatable();
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al asignar el estudiante', 'ERROR');
        },
    });
}

// -- Carga la plantilla de correo para credenciales.
function load_credentials_template() {
    // Carga la plantilla de correo para credenciales desde el backend.
    // Muestra los valores en los campos de asunto y contenido del modal.
    $.ajax({
        url: BASE_URL + 'S_Assignment/get_credentials_template',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function (data) {
            if (data.status === 'OK') {
                $('#template-subject').val(data.data.subject || '');
                $('#template-content').val(data.data.content || '');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
        },
    });
}

// -- Guarda la plantilla de correo para credenciales.
function save_credentials_template() {
    // Guarda la plantilla de correo para credenciales en el backend.
    // Valida los campos requeridos y muestra mensajes en español.
    // Envía la información usando nombres de variables en inglés según las reglas.
    const subject = $('#template-subject').val();
    const content = $('#template-content').val();

    if (!subject || subject.trim() === '') {
        functions.toast_message('warning', 'El asunto es requerido', 'WARNING');
        return;
    }

    if (!content || content.trim() === '') {
        functions.toast_message('warning', 'El contenido es requerido', 'WARNING');
        return;
    }

    $.ajax({
        url: BASE_URL + 'S_Assignment/update_credentials_template',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({
            subject: subject,
            content: content,
        }),
        success: function (data) {
            functions.toast_message(data.type, data.msg, data.status);
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al guardar la plantilla', 'ERROR');
        },
    });
}
// -- Document Ready
$(document).ready(function () {
    // Carga inicial de la tabla
    load_datatable();

    // Botón asignar
    $(document).on('click', '.btn_assign', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        assign_student(id, name);
    });

    // Cambio de grupo - recargar roles según el tipo de grupo seleccionado
    $(document).on('change', '#assign-group', function () {
        const selectedOption = $(this).find('option:selected');
        const groupType = selectedOption.data('type');
        load_roles(groupType);
    });

    // Botón confirmar asignación
    $(document).on('click', '#btn-confirm-assign', function () {
        confirm_assign();
    });

    // Botón cerrar modal credenciales
    $(document).on('click', '#btn-close-credentials', function () {
        $('#modal-credentials').modal('hide');
        refresh_datatable();
    });

    // Botón configurar plantilla
    $(document).on('click', '#btn-config-template', function () {
        load_credentials_template();
        $('#modal-template').modal('show');
    });

    // Botón guardar plantilla
    $(document).on('click', '#btn-save-template', function () {
        save_credentials_template();
    });

    // Auto-refresh DataTable cada 10 segundos
    setInterval(function () {
        refresh_datatable();
    }, 10000);
});
