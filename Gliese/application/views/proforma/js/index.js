// -- Functions

function destroy_datatable() { 
    $('#datatable-proforma').dataTable().fnDestroy(); 
}

function refresh_datatable() { 
    $('#datatable-proforma').DataTable().ajax.reload(); 
}

function load_datatable() { 
    destroy_datatable();

    let dataTable = $('#datatable-proforma').DataTable({
        ajax: {
            url: BASE_URL + 'Proforma/get_proforma',
            cache: false,
            dataSrc: function (json) {
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
                render: function (row) {
                    return row.series_proforma + '-' + row.correlative;
                },
                width: '100px',
            },
            {
                data: 'total_sale',
                class: 'center',
                width: '60px',
            },
            {
                data: 'status',
                width: '60px',
                render: function (data, type, row, meta) {
                    if (row.status == "1") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-warning">Pendiente</span>
                                </div>`;
                    } else if (row.status == "2") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-success">Aceptado</span>
                                </div>`;
                    } else if (row.status == "3") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-danger">Rechazado</span>
                                </div>`;
                    } else if (row.status == "4") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-warning">Observado</span>
                                </div>`;
                    }
                }
            },
            {
                class: 'center',
                width: '170px',
                render: function (data, type, row, meta) {
                    return (
                        '<button class="btn btn-sm btn-light btn-round btn-icon btn_pdf" data-process-key="' + row.id_proforma + '_1" target="_blank">' +
                        '<img src="' + BASE_URL + 'public/app-assets/images/svg/pdf.svg" style="width: 25px; height: 25px;" alt="File Text">' +
                        '</button>' +
                        ' ' +
                        '<button class="btn btn-sm btn-danger btn-round btn-icon btn_delete" data-process-key="' + row.id_proforma + '">' +
                        feather.icons["trash-2"].toSvg({ class: "font-small-4" }) +
                        "</button>"
                    
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
                action: function () {
                    clearFilters();
                }
            },
            ...functions.custom_buttons_datatable([7], '#create_proforma_modal')
        ],
        language: {
            url: BASE_URL + 'public/assets/json/languaje-es.json'
        }
    });

    dataTable.on('xhr', function () {
        var data = dataTable.ajax.json();
    });

    $('#datatable-proforma').on('draw.dt', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
    
}
function showWarningAlert(warning) { 
    Swal.mixin({ toast: true, position: 'bottom-end', 
        showConfirmButton: true, 
        showCancelButton: true, 
        confirmButtonText: 'Ver pendientes', 
        cancelButtonText: 'Cerrar', timer: 10000, 
        timerProgressBar: true, customClass: 
        { confirmButton: 'btn btn-warning btn-sm',
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
                    Recuerde: Los comprobantes deben ser enviados a SUNAT dentro del plazo establecido.
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
    let dataTable = $('#datatable-proforma').DataTable();
    dataTable.search('').columns().search('');
    dataTable.columns(5).search('Pendiente').draw();
    functions.toast_message(
        'success',
        'Se están mostrando solo los documentos pendientes',
        'OK'
    );
}

function clearFilters() {
    let dataTable = $('#datatable-proforma').DataTable();
    dataTable.search('').columns().search('').draw();

    functions.toast_message(
        'info',
        'Filtros eliminados',
        'OK'
    );
}

$(document).on('click', '.btn_pdf', function() {
    let value = $(this).attr('data-process-key');
    let [id_proforma, tipo] = value.split('_');
    
    // Abrir en nueva pestaña
    let url = BASE_URL + 'Proforma/get_proforma_Report?id_proforma=' + id_proforma;
    window.open(url, '_blank');
});

// --
function update_proforma(form) {
    $('#btn_update_proforma').prop('disabled', true);
    let params = new FormData(form);
    $.ajax({
        url: BASE_URL + 'Proforma/update_proforma',
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
            functions.toast_message(data.type, data.msg, data.status);
            if (data.status === 'OK') {
                $('#update_proforma_modal').modal('hide');
                form.reset();
                refresh_datatable();
            } else {
                $('#btn_update_proforma').prop('disabled', false);
            }
        }
    });
}

//--
$(document).on('click', '.btn_update', function () {
    let value = $(this).attr('data-process-key');
    let params = { 'id_proforma': value }
    $.ajax({
        url: BASE_URL + 'Proforma/get_proforma_by_id',
        type: 'GET',
        data: params,
        dataType: 'json',
        contentType: false,
        processData: true,
        cache: false,
        success: function (data) {
            if (data.status === 'OK') {
                let item = data.data;
                $('#update_proforma_form :input[name=id_proforma]').val(item.id_proforma);
                $('#update_proforma_form :input[name=id_category]').val(item.id_category);
                $('#update_proforma_form :input[name=description]').val(item.description);
                $('#update_proforma_form :input[name=stock]').val(item.stock);
                $('#update_proforma_form :input[name=code]').val(item.code);
            }
        }
    });
    $('#update_proforma_modal').modal('show');
});

// --
$(document).on('click', '.btn_delete', function () {
    let value = $(this).attr('data-process-key');
    let params = { 'id_proforma': value }
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
                url: BASE_URL + 'Proforma/delete_proforma',
                type: 'POST',
                data: params,
                dataType: 'json',
                cache: false,
                success: function (data) {
                    functions.toast_message(data.type, data.msg, data.status);
                    if (data.status === 'OK') {
                        refresh_datatable();
                    }
                }
            });
        }
    }).then(result => {
        if (result.isConfirmed) {
        }
    });
});

// -- Redirect new controller
$(document).on('click', '.create-new', function () {
    window.location.assign(BASE_URL + 'proforma_Details');
});

// -- Validate form
$('#update_proforma_form').validate({
    submitHandler: function (form) {
        update_proforma(form);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const iconos = [
        { id: 'email-icon', ruta: 'gmail.svg' },
        { id: 'whatsapp-icon', ruta: 'whatsapp.svg' },
    ];

    iconos.forEach(icono => {
        const elemento = document.getElementById(icono.id);
        if (elemento) {
            cargarIconoSvg(elemento, BASE_URL + 'public/app-assets/images/svg/' + icono.ruta);
        }
    });
});

function cargarIconoSvg(elemento, rutaIcono) {
    fetch(rutaIcono)
        .then(respuesta => respuesta.text())
        .then(contenidoSvg => {
            const parser = new DOMParser();
            const docSvg = parser.parseFromString(contenidoSvg, 'image/svg+xml');
            const elementoSvg = docSvg.documentElement;

            elementoSvg.style.width = '30px';
            elementoSvg.style.height = '30px';
            elementoSvg.style.marginRight = '5px';

            elemento.appendChild(elementoSvg);
        })
        .catch(error => {
            console.error('Error al cargar el icono SVG:', error);
        });
}

load_datatable();