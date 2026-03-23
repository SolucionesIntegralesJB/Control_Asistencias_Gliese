// -- Functions
// --
function destroy_datatable() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#pending-table')) {
        try {
            $('#pending-table').DataTable().clear().destroy();
        } catch (e) {
            try {
                $('#pending-table').dataTable().fnDestroy();
            } catch (e) {
                /* ignore */
            }
        }
    }
}

// --
let isFirstPendingLoad = true;

function refresh_datatable() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#pending-table')) {
        $('#pending-table').DataTable().ajax.reload(null, false);
    }
}
// --
function load_datatable() {
    destroy_datatable();

    let dataTable = $('#pending-table').DataTable({
        ajax: {
            url: BASE_URL + 'S_Pending/get_pending',
            data: function (d) {
                d.status = $('#filter-status').val() || 'pendiente';
            },
            cache: false,
            dataSrc: function (json) {
                console.log('Ajax get_pending response:', json);

                if (!json) return [];

                // Mostrar el aviso solo una vez si no hay solicitudes
                if (json.status === 'ERROR' && json.msg) {
                    if (isFirstPendingLoad) {
                        functions.toast_message(json.type, json.msg, json.status);
                    }
                    isFirstPendingLoad = false;
                } else if (json.status === 'OK') {
                    isFirstPendingLoad = false;
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
                    return (row.first_names || '') + ' ' + (row.last_names || '');
                },
            },
            { data: 'document_number', defaultContent: '-' },
            { data: 'email', defaultContent: '-' },
            { data: 'phone', defaultContent: '-' },
            { data: 'institution', defaultContent: '-' },
            { data: 'specialty', defaultContent: '-' },
            { data: 'created_at', defaultContent: '-' },
            {
                data: null,
                defaultContent: '',
                class: 'center text-nowrap',
                orderable: false,
                width: '150px',
                render: function (data, type, row, meta) {
                    return (
                        '<div class="d-inline-flex gap-1">' +
                        '<button class="btn btn-sm btn-info btn-round btn-icon btn_view_detail" data-id="' +
                        row.id +
                        '" title="Ver detalle">' +
                        feather.icons['eye'].toSvg({ class: 'font-small-4' }) +
                        '</button>' +
                        '<button class="btn btn-sm btn-success btn-round btn-icon btn_quick_accept" data-id="' +
                        row.id +
                        '" title="Aceptar">' +
                        feather.icons['check'].toSvg({ class: 'font-small-4' }) +
                        '</button>' +
                        '<button class="btn btn-sm btn-danger btn-round btn-icon btn_quick_reject" data-id="' +
                        row.id +
                        '" title="Rechazar">' +
                        feather.icons['x'].toSvg({ class: 'font-small-4' }) +
                        '</button>' +
                        '</div>'
                    );
                },
            },
        ],
        dom: '<"d-flex justify-content-between align-items-center mx-0 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>t<"d-flex justify-content-between mx-0 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        buttons: [],
        language: {
            url: BASE_URL + 'public/assets/json/languaje-es.json',
        },
        order: [[6, 'desc']],
    });

    dataTable.on('xhr', function () {
        console.log('DataTable loaded rows:', dataTable.rows().count());
    });
}

// --
function view_student_detail(id) {
    $.ajax({
        url: BASE_URL + 'S_Pending/get_student_detail',
        type: 'GET',
        dataType: 'json',
        data: { id: id },
        cache: false,
        beforeSend: function () {
            // Clear all fields
            $('#detail-id').val('');
            $('#detail-full-names').text('');
            $('#detail-document-type').text('');
            $('#detail-document-number').text('');
            $('#detail-birth-date').text('');
            $('#detail-phone').text('');
            $('#detail-email').text('');
            $('#detail-address').text('');
            $('#detail-institution').text('');
            $('#detail-location').text('');
            $('#detail-specialty').text('');
            $('#detail-cycle').text('');
            $('#detail-institutional-email').text('');
            $('#detail-modality').text('');
            $('#detail-interest-area').text('');
            $('#detail-origin-phone').text('');
            $('#remarks').val('');
            $('#remarks-group').hide();
        },
        success: function (data) {
            if (data.status === 'OK') {
                const s = data.data;

                $('#detail-id').val(s.id);
                $('#detail-full-names').text((s.first_names || '') + ' ' + (s.last_names || ''));
                $('#detail-document-type').text(s.document_type_desc || '-');
                $('#detail-document-number').text(s.document_number || '-');
                $('#detail-birth-date').text(s.birth_date_formatted || '-');
                $('#detail-phone').text(s.phone || '-');
                $('#detail-email').text(s.email || '-');
                $('#detail-address').text(s.address || '-');
                $('#detail-institution').text(s.institution || '-');
                $('#detail-location').text(s.location || '-');
                $('#detail-specialty').text(s.specialty || '-');
                $('#detail-cycle').text(s.cycle || '-');
                $('#detail-institutional-email').text(s.institutional_email || '-');
                $('#detail-modality').text(s.modality || '-');
                $('#detail-interest-area').text(s.interest_area || '-');
                $('#detail-origin-phone').text(s.origin_phone || '-');

                $('#modal-student-detail').modal('show');
            } else {
                functions.toast_message(data.type, data.msg, data.status);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al cargar el detalle del practicante', 'ERROR');
        },
    });
}

// --
function aceptar_solicitud(id) {
    Swal.fire({
        title: '¿Aceptar solicitud?',
        text: "El estado cambiará a 'Aceptado'",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aceptar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-secondary',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'S_Pending/aceptar_solicitud',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({ id_student: id, template_used: 'aceptado' }),
                success: function (data) {
                    if (data.status === 'OK') {
                        functions.toast_message('success', 'Correo enviado correctamente.', 'OK');
                        $('#modal-student-detail').modal('hide');
                        refresh_datatable();
                    } else {
                        functions.toast_message(data.type, data.msg, data.status);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    functions.toast_message('error', 'Error al aceptar la solicitud', 'ERROR');
                },
            });
        }
    });
}

// --
function rechazar_solicitud(id, remarks) {
    if (!remarks || remarks.trim() === '') {
        functions.toast_message('warning', 'Debe ingresar el motivo del rechazo', 'WARNING');
        return;
    }

    Swal.fire({
        title: '¿Rechazar solicitud?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, rechazar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'S_Pending/rechazar_solicitud',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    id_student: id,
                    remarks: remarks,
                    template_used: 'rechazado',
                }),
                success: function (data) {
                    if (data.status === 'OK') {
                        functions.toast_message('success', 'Correo enviado correctamente.', 'OK');
                        $('#modal-student-detail').modal('hide');
                        refresh_datatable();
                    } else {
                        functions.toast_message(data.type, data.msg, data.status);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    functions.toast_message('error', 'Error al rechazar la solicitud', 'ERROR');
                },
            });
        }
    });
}
// --
function load_templates() {
    $.ajax({
        url: BASE_URL + 'S_Pending/get_templates',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function (data) {
            if (data.status === 'OK') {
                const templates = data.data;

                // Cargar plantilla de aceptación
                if (templates.aceptado) {
                    $('#asunto-aceptacion').val(templates.aceptado.subject || '');
                    $('#contenido-aceptacion').val(templates.aceptado.content || '');
                }

                // Cargar plantilla de rechazo
                if (templates.rechazado) {
                    $('#asunto-rechazo').val(templates.rechazado.subject || '');
                    $('#contenido-rechazo').val(templates.rechazado.content || '');
                }
            } else {
                functions.toast_message(
                    data.type || 'error',
                    data.msg || 'Error al cargar plantillas',
                    data.status || 'ERROR',
                );
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', error);
            functions.toast_message('error', 'Error al cargar las plantillas', 'ERROR');
        },
    });
}

// --
function save_template(type) {
    let subject, content;

    if (type === 'aceptado') {
        subject = $('#asunto-aceptacion').val();
        content = $('#contenido-aceptacion').val();
    } else if (type === 'rechazado') {
        subject = $('#asunto-rechazo').val();
        content = $('#contenido-rechazo').val();
    }

    if (!subject || subject.trim() === '') {
        functions.toast_message('warning', 'El asunto es requerido', 'WARNING');
        return;
    }

    if (!content || content.trim() === '') {
        functions.toast_message('warning', 'El contenido es requerido', 'WARNING');
        return;
    }

    $.ajax({
        url: BASE_URL + 'S_Pending/update_templates',
        type: 'POST',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({
            type: type,
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
    // Load initial data
    load_datatable();

    // Filter by status change event
    $('#filter-status').on('change', function () {
        isFirstPendingLoad = true;
        refresh_datatable();
    });

    // View detail button click
    $(document).on('click', '.btn_view_detail', function () {
        const id = $(this).data('id');
        view_student_detail(id);
    });

    // Quick accept button
    $(document).on('click', '.btn_quick_accept', function () {
        const id = $(this).data('id');
        aceptar_solicitud(id);
    });

    // Quick reject button
    $(document).on('click', '.btn_quick_reject', function () {
        const id = $(this).data('id');
        view_student_detail(id);
        setTimeout(function () {
            $('#remarks-group').show();
            $('#remarks').focus();
        }, 500);
    });

    // Modal aceptar button
    $(document).on('click', '#btn-aceptar', function () {
        const id = $('#detail-id').val();
        aceptar_solicitud(id);
    });

    // Modal rechazar button
    $(document).on('click', '#btn-rechazar', function () {
        $('#remarks-group').show();
        $('#remarks').focus();
    });

    // Execute reject when observaciones filled
    $(document).on('click', '#btn-rechazar', function () {
        const id = $('#detail-id').val();
        const remarks = $('#remarks').val();
        if (!remarks || remarks.trim() === '') {
            functions.toast_message('warning', 'Debe ingresar el motivo del rechazo', 'WARNING');
            $('#remarks-group').show();
            $('#remarks').focus();
            return;
        }
        rechazar_solicitud(id, remarks);
    });

    // Auto-refresh DataTable every 10 seconds
    setInterval(function () {
        refresh_datatable();
    }, 10000);

    // Configurar plantillas button click
    $(document).on('click', '#btn-config-templates', function () {
        load_templates();
        $('#modal-templates').modal('show');
    });

    // Save aceptacion template button
    $(document).on('click', '#btn-save-aceptacion', function () {
        save_template('aceptado');
    });

    // Save rechazo template button
    $(document).on('click', '#btn-save-rechazo', function () {
        save_template('rechazado');
    });
});
