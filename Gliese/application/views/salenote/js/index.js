// -- Functions

// Función para formatear montos monetarios
function formatCurrency(amount) {
    if (!amount) return 'S/ 0.00';
    return 'S/ ' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// --
function destroy_datatable() {
    $('#datatable-salenote').DataTable().destroy();
}

// --
function refresh_datatable() {
    $('#datatable-salenote').DataTable().ajax.reload();
}

// --
function load_datatable() {
    destroy_datatable();
    let dataTable = $('#datatable-salenote').DataTable({
        ajax: {
            url: BASE_URL + 'Salenote/get_salenote',
            cache: false,
            dataSrc: function(json) {
                console.log("Datos recibidos:", json.data); // Para depuración
                if (json.warning && json.warning.show) {
                    showWarningAlert(json.warning);
                }
                return json.data;
            }
        },
        columns: [
            {
                data: 'issue_date',
                width: '70px',
            },
            {
                data: 'clients',
                class: 'center',
                width: '200px',
            },
            {
                data: 'voucher_type',
                width: '50px',
            },
            {
                data: null,
                render: function(row) {
                    return row.document_number;
                },
                width: '100px',
            },
            {
                data: null,
                class: 'center',
                width: '60px',
                render: function(data, type, row) {
                    return formatCurrency(row.total_sale);
                }
            },
            {
                data: 'status',
                width: '60px',
                render: function(data, type, row, meta) {
                    if (row.status == "1") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-warning">Pendiente</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Registrado en el sistema"></i>
                                </div>`;
                    } else if (row.status == "2") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-success">Aceptado</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                                </div>`;
                    } else if (row.status == "3") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-danger">Rechazado</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                                </div>`;
                    } else if (row.status == "4") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-warning">Observado</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                                </div>`;
                    }
                }
            },
            {
                class: 'center',
                width: '100px',
                render: function(data, type, row, meta) {
                    return (
                        '<button class="btn btn-sm btn-light btn-round btn-icon btn_pdf" data-process-key="' + row.id_salenote + '_1" target="_blank">' +
                        '<img src="' + BASE_URL + 'public/app-assets/images/svg/pdf.svg" style="width: 25px; height: 25px;" alt="PDF">' +
                        '</button>' +
                        ' ' +
                        '<button class="btn btn-sm btn-light btn-round btn-icon btn_pdf" data-process-key="' + row.id_salenote + '_2" target="_blank">' +
                        '<img src="' + BASE_URL + 'public/app-assets/images/svg/receipt.svg" style="width: 25px; height: 25px;" alt="Receipt">' +
                        '</button>'
                    );
                }
            },
        ],
        order: [[0, 'desc']],
        dom: functions.head_datatable(),
        buttons: [
            {
                text: '<i class="fas fa-times"></i> Limpiar filtros',
                className: 'btn btn-outline-secondary btn-sm float-start me-2',
                action: function() {
                    clearFilters();
                }
            },
            ...functions.custom_buttons_datatable([7], '#create_salenote_modal')
        ],
        language: {
            url: BASE_URL + 'public/assets/json/languaje-es.json'
        }
    });

    dataTable.on('xhr', function() {
        var data = dataTable.ajax.json();
    });

    $('#datatable-salenote').on('draw.dt', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
}

function showWarningAlert(warning) {
    Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'Ver pendientes',
        cancelButtonText: 'Cerrar',
        timer: 10000,
        timerProgressBar: true,
        customClass: {
            confirmButton: 'btn btn-warning btn-sm',
            cancelButton: 'btn btn-outline-secondary btn-sm ms-1',
            container: 'p-20'
        },
        buttonsStyling: false
    }).fire({
        icon: 'warning',
        title: '<i class="fas fa-exclamation-triangle"></i> ¡Documentos Pendientes!',
        html: `
            <div class="text-justify" style="max-width: 300px">
                <p class="mb-2">${warning.message}</p>
                <hr class="my-2">
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Recuerde: Los comprobantes deben ser procesados dentro del plazo establecido.
                </p>
            </div>
        `
    }).then((result) => {
        if (result.isConfirmed) {
            filterPendingDocs();
        }
    });
}

function filterPendingDocs() {
    let dataTable = $('#datatable-salenote').DataTable();
    dataTable.search('').columns().search('');
    dataTable.columns(5).search('Pendiente').draw();
    functions.toast_message(
        'success',
        'Se están mostrando solo los documentos pendientes',
        'OK'
    );
}

function clearFilters() {
    let dataTable = $('#datatable-salenote').DataTable();
    dataTable.search('').columns().search('').draw();

    functions.toast_message(
        'info',
        'Filtros eliminados',
        'OK'
    );
}

$(document).on('click', '.btn_pdf', function () {
    let value = $(this).attr('data-process-key');
    let [id_salenote, tipo] = value.split('_');
    let url = BASE_URL + 'Salenote/get_salenote_Report?id_salenote=' + id_salenote + '&tipo=' + tipo;
    console.log(url);
    window.open(url, '_blank');
}); 


// -- Redirect new controller
$(document).on('click', '.create-new', function () {
    // --
    window.location.assign(BASE_URL + 'Salenote_Details');
})

// -- Validate form
$('#update_salenote_form').validate({
    // --
    submitHandler: function (form) {
        update_salenote(form);
    }
})

// --
function update_salenote(form) {
    // --
    $('#btn_update_salenote').prop('disabled', true);
    // --
    let params = new FormData(form);
    // --
    $.ajax({
        url: BASE_URL + 'Salenote/update_salenote',
        type: 'POST',
        data: params,
        dataType: 'json',
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function () {
            console.log('Cargando...');
        },
        success: function (data) {
            // --
            functions.toast_message(data.type, data.msg, data.status);
            // --
            if (data.status === 'OK') {
                // --
                $('#update_salenote_modal').modal('hide');
                form.reset();
                refresh_datatable();

            } else {
                // --
                $('#btn_update_salenote').prop('disabled', false);
            }
        }
    })
}

//--
$(document).on('click', '.btn_update', function () {
    // --
    let value = $(this).attr('data-process-key');
    // --
    let params = { 'id_salenote': value }
    // --
    $.ajax({
        url: BASE_URL + 'Salenote/get_salenote_by_id',
        type: 'GET',
        data: params,
        dataType: 'json',
        contentType: false,
        processData: true,
        cache: false,
        success: function (data) {
            // --
            if (data.status === 'OK') {
                // --
                let item = data.data
                // --
                $('#update_salenote_form :input[name=id_salenote]').val(item.id_salenote);
                $('#update_salenote_form :input[name=id_category]').val(item.id_category);
                $('#update_salenote_form :input[name=description]').val(item.description);
                $('#update_salenote_form :input[name=stock]').val(item.stock);
                $('#update_salenote_form :input[name=code]').val(item.code);
                // --
            }
        }
    })
    // --
    $('#update_salenote_modal').modal('show');
})

// --
$(document).on('click', '.btn_delete', function () {
    // --
    let value = $(this).attr('data-process-key');
    // --
    let params = { 'id_salenote': value }
    // --
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¡No podrás revertir esto!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar!',
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-outline-danger ms-1'
        },
        buttonsStyling: false,
        preConfirm: _ => {
            return $.ajax({
                url: BASE_URL + 'Salenote/delete_salenote',
                type: 'POST',
                data: params,
                dataType: 'json',
                cache: false,
                success: function (data) {
                    // --
                    functions.toast_message(data.type, data.msg, data.status);
                    // --
                    if (data.status === 'OK') {
                        // --
                        refresh_datatable();
                    }
                }
            })
        }
    }).then(result => {
        if (result.isConfirmed) {
        }
    });
})

//--
load_datatable();