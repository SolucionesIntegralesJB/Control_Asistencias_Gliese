// -- Functions
// --
function destroy_datatable() {
    // Safely destroy datatable only if it exists
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#historial-table')) {
        try {
            $('#historial-table').DataTable().clear().destroy();
        } catch (e) {
            // fallback to old API if needed
            try {
                $('#historial-table').dataTable().fnDestroy();
            } catch (e) {
                /* ignore */
            }
        }
    }
}

// --
function refresh_datatable() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#historial-table')) {
        $('#historial-table').DataTable().ajax.reload(null, false);
    }
}

// --
function load_statistics() {
    $.ajax({
        url: BASE_URL + 'Bot_History/get_historial_stats',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function (data) {
            if (data.status === 'OK') {
                $('#total_usuarios').text(data.data.total_usuarios || 0);
                $('#email_verificados').text(data.data.email_verificados || 0);
                $('#formularios_completados').text(data.data.formularios_completados || 0);
                $('#grupos_enviados').text(data.data.grupos_enviados || 0);
                $('#registros_completos').text(data.data.registros_completos || 0);
                $('#registros_en_progreso').text(data.data.registros_en_progreso || 0);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error cargando estadísticas:', error);
        },
    });
}

// --
function load_datatable() {
    destroy_datatable();

    let dataTable = $('#historial-table').DataTable({
        ajax: {
            url: BASE_URL + 'Bot_History/get_history',
            cache: false,
            // Accept different server response shapes: { data: [...] }, { result: [...] } or raw array
            dataSrc: function (json) {
                console.log('Ajax get_history response:', json);

                if (!json) return [];

                // If the server returned an informational message, show it (but avoid duplicate toasts)
                if (json.type && json.msg) {
                    // show only warnings/errors here; success toasts can be noisy on each reload
                    if (json.type !== 'success') {
                        functions.toast_message(json.type, json.msg, json.status || '');
                    }
                }

                if (Array.isArray(json.data)) return json.data;
                if (Array.isArray(json.result)) return json.result;
                if (Array.isArray(json)) return json;

                return [];
            },
        },
        columns: [
            { data: 'phone', defaultContent: '-' },
            { data: 'name', defaultContent: '-' },
            { data: 'email', defaultContent: '-' },
            { data: 'state', defaultContent: '-' },
            {
                data: 'form_status',
                defaultContent: '',
                class: 'center',
                render: function (data, type, row, meta) {
                    if (data === 'completado') {
                        return '<span class="badge rounded-pill badge-light-success">Completado</span>';
                    } else if (data === 'en_progreso') {
                        return '<span class="badge rounded-pill badge-light-warning">En Progreso</span>';
                    } else {
                        return '<span class="badge rounded-pill badge-light-secondary">No Iniciado</span>';
                    }
                },
            },
            {
                data: 'email_verified',
                defaultContent: '0',
                class: 'center',
                render: function (data, type, row, meta) {
                    if (data == '1') {
                        return (
                            '<span class="badge rounded-pill badge-light-success">' +
                            feather.icons['check-circle'].toSvg({ class: 'font-small-4' }) +
                            '</span>'
                        );
                    } else {
                        return (
                            '<span class="badge rounded-pill badge-light-danger">' +
                            feather.icons['x-circle'].toSvg({ class: 'font-small-4' }) +
                            '</span>'
                        );
                    }
                },
            },
            {
                data: 'group_assigned',
                defaultContent: '0',
                class: 'center',
                render: function (data, type, row, meta) {
                    if (data == '1') {
                        return (
                            '<span class="badge rounded-pill badge-light-success">' +
                            feather.icons['check-circle'].toSvg({ class: 'font-small-4' }) +
                            '</span>'
                        );
                    } else {
                        return (
                            '<span class="badge rounded-pill badge-light-danger">' +
                            feather.icons['x-circle'].toSvg({ class: 'font-small-4' }) +
                            '</span>'
                        );
                    }
                },
            },
            {
                data: null,
                defaultContent: '',
                class: 'center',
                orderable: false,
                render: function (data, type, row, meta) {
                    return (
                        '<button class="btn btn-sm btn-info btn-round btn-icon btn_view_detail" data-phone="' +
                        row.phone +
                        '">' +
                        feather.icons['eye'].toSvg({ class: 'font-small-4' }) +
                        '</button>'
                    );
                },
            },
        ],
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: {
            url: BASE_URL + 'public/assets/json/languaje-es.json',
        },
        order: [[0, 'desc']],
    });

    dataTable.on('xhr', function () {
        // Log rows loaded for debugging
        console.log('DataTable loaded rows:', dataTable.rows().count());
    });
}

// --
function view_user_detail(phone) {
    $.ajax({
        url: BASE_URL + 'Bot_History/get_user_detail',
        type: 'GET',
        dataType: 'json',
        data: { phone: phone },
        cache: false,
        beforeSend: function () {
            $('#detail-phone').text('');
            $('#detail-name').text('');
            $('#detail-email').text('');
            $('#detail-state').text('');
            $('#detail-previous_state').text('');
            $('#detail-state_option_key').text('');
            $('#detail-previous_state_option_key').text('');
            $('#detail-form_status').text('');
            $('#detail-email_verified').text('');
            $('#detail-group_assigned').text('');
            $('#detail-codigo').text('');
            $('#detail-code_expiration').text('');
            $('#detail-form_submitted').text('');
            $('#detail-form_completed').text('');
            $('#last_message').text('');
        },
        success: function (data) {
            if (data.status === 'OK') {
                const user = data.data;

                $('#detail-phone').text(user.phone || '-');
                $('#detail-name').text(user.name || '-');
                $('#detail-email').text(user.email || '-');
                $('#detail-state').text(user.state || '-');
                $('#detail-previous_state').text(user.previous_state || '-');
                $('#detail-state_option_key').text(user.state_option_key || '-');
                $('#detail-previous_state_option_key').text(user.previous_state_option_key || '-');
                $('#detail-form_status').html(getform_statusBadge(user.form_status));
                $('#detail-email_verified').html(
                    user.email_verified == '1'
                        ? '<span class="badge bg-success">Sí</span>'
                        : '<span class="badge bg-danger">No</span>',
                );
                $('#detail-group_assigned').html(
                    user.group_assigned == '1'
                        ? '<span class="badge bg-success">Sí</span>'
                        : '<span class="badge bg-danger">No</span>',
                );
                $('#detail-codigo').text(user.verification_code || '-');
                $('#detail-code_expiration').text(user.code_expiration_formatted || '-');
                $('#detail-form_submitted').text(user.form_submitted_formatted || '-');
                $('#detail-form_completed').text(user.form_completed_formatted || '-');
                $('#last_message').text(user.last_message || '-');

                $('#modal-user-detail').modal('show');
            } else {
                functions.toast_message(data.type, data.msg, data.status);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al cargar el detalle del usuario', 'ERROR');
        },
    });
}

// --
function getform_statusBadge(estado) {
    if (estado === 'completado') {
        return '<span class="badge bg-success">Completado</span>';
    } else if (estado === 'en_progreso') {
        return '<span class="badge bg-warning">En Progreso</span>';
    } else {
        return '<span class="badge bg-secondary">No Iniciado</span>';
    }
}

// -- Document Ready
$(document).ready(function () {
    // Load initial data
    load_statistics();
    load_datatable();

    // View detail button click
    $(document).on('click', '.btn_view_detail', function () {
        const phone = $(this).data('phone');
        view_user_detail(phone);
    });

    // Auto-refresh statistics every 3 seconds
    setInterval(function () {
        load_statistics();
    }, 3000);

    // Auto-refresh DataTable every 3 seconds
    setInterval(function () {
        refresh_datatable();
    }, 3000);
});
