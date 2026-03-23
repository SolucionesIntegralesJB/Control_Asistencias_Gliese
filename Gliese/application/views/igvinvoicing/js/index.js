// -- Functions

function destroy_datatable() {
    $('#datatable-igvinvoicing').dataTable().fnDestroy();
}

function refresh_datatable() {
    $('#datatable-igvinvoicing').DataTable().ajax.reload();
}

function load_datatable() {
    destroy_datatable();

    let dataTable = $('#datatable-igvinvoicing').DataTable({
        ajax: {
            url: BASE_URL + 'Igvinvoicing/get_igvinvoicing',
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
                data: 'date_time',
                width: '70px',
            },
            {
                data: 'client_name',
                class: 'center',
                width: '200px',
            },
            {
                data: 'client_id',
                width: '50px',
            },
            {
                data: 'document_number',
                class: 'center',
                width: '100px',
            },
            {
                data: null,
                render: function (row) {
                    return row.series + '-' + row.correlative;
                },
                width: '100px',
            },
            {
                data: 'total_sale',
                class: 'center',
                width: '60px',
                render: function(data) {
                    return 'S/ ' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'status',
                width: '60px',
                render: function (data, type, row, meta) {
                    if (row.status == "PENDIENTE") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-warning">Pendiente</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Registrado en el sistema"></i>
                                </div>`;
                    } else if (row.status == "ACEPTADO") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-success">Aceptado</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                                </div>`;
                    } else if (row.status == "RECHAZADO") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-danger">Rechazado</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                                </div>`;
                    } else if (row.status == "OBSERVADO") {
                        return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-warning">Observado</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                                </div>`;
                    }
                }
            },
            {
                class: 'center',
                width: '170px',
                render: function (data, type, row, meta) {
                    return (
                        
                        '<button class="btn btn-sm btn-light btn-round btn-icon btn_pdf" data-process-key="' + row.invoice_id + '_1" target="_blank">' +
                        '<img src="' + BASE_URL + 'public/app-assets/images/svg/pdf.svg" style="width: 25px; height: 25px;" alt="File Text">' +
                        '</button>' +
                        ' ' +
                        '<button class="btn btn-sm btn-light btn-round btn-icon btn_pdf" data-process-key="' + row.invoice_id + '_2" target="_blank">' +
                        '<img src="' + BASE_URL + 'public/app-assets/images/svg/receipt.svg" style="width: 25px; height: 25px;" alt="File Text">' +
                        '</button>' +
                        ' ' +
                        '<button class="btn btn-sm btn-light btn-round btn-icon btn_send" data-process-key="' + row.invoice_id + '">' +
                        '<img src="' + BASE_URL + 'public/app-assets/images/svg/send.svg" style="width: 25px; height: 25px;" alt="Enviar">' +
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
                action: function () {
                    clearFilters();
                }
            },
            ...functions.custom_buttons_datatable([7], '#create_igvinvoicing_modal')
        ],
        language: {
            url: BASE_URL + 'public/assets/json/languaje-es.json'
        }
    });

    dataTable.on('xhr', function () {
        var data = dataTable.ajax.json();
    });

    $('#datatable-igvinvoicing').on('draw.dt', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
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
    let dataTable = $('#datatable-igvinvoicing').DataTable();
    dataTable.search('').columns().search('');
    dataTable.columns(5).search('1').draw(); // Filtrar por status = 1 (Pendiente)
    functions.toast_message(
        'success',
        'Se están mostrando solo los documentos pendientes',
        'OK'
    );
}

function clearFilters() {
    let dataTable = $('#datatable-igvinvoicing').DataTable();
    dataTable.search('').columns().search('').draw();

    functions.toast_message(
        'info',
        'Filtros eliminados',
        'OK'
    );
}
// -- Redirect new controller
$(document).on('click', '.create-new', function() {
    // --
    window.location.assign(BASE_URL + 'Igvinvoicing_Details'); 
})

$(document).on('click', '.btn_pdf', function () {
    let value = $(this).attr('data-process-key');
    let [invoice_id, tipo] = value.split('_');
    let url = BASE_URL + 'Igvinvoicing/get_igvinvoicing_report?id_igvinvoice=' + invoice_id + '&tipo=' + tipo;
    window.open(url, '_blank');
});

$(document).on('click', '.btn_xml', function () {
    let invoice_id = $(this).attr('data-process-key');
    let url = BASE_URL + 'Igvinvoicing/xml_igvinvoicing?id_igvinvoice=' + invoice_id;

    fetch(url)
        .then(response => {
            const contentDisposition = response.headers.get('Content-Disposition');
            let filename = 'documento.xml';
            if (contentDisposition) {
                const filenameMatch = contentDisposition.match(/filename="?(.+)"?/i);
                if (filenameMatch && filenameMatch[1]) {
                    filename = filenameMatch[1].replace(/^"|"$/g, '');
                }
            }
            return response.blob().then(blob => ({ blob, filename }));
        })
        .then(({ blob, filename }) => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => console.error('Error al descargar el archivo:', error));
});

$(document).on('click', '.btn_sunat', function () {
    let invoice_id = $(this).attr('data-process-key');
    let url = BASE_URL + 'Igvinvoicing/emit_comprobante?id_igvinvoice=' + invoice_id;
    Swal.fire({
        title: 'Procesando...',
        text: 'Por favor, espere mientras se emite el comprobante.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                let mensaje = `<strong>Estado:</strong> ${response.estado}<br>`;
                mensaje += `<strong>Descripción:</strong> ${response.descripcion}<br>`;
                if (response.observaciones && response.observaciones.length > 0) {
                    mensaje += `<strong>Observaciones:</strong><br>${response.observaciones.join('<br>')}`;
                } else {
                    mensaje += '<strong>Observaciones:</strong> No hay observaciones.';
                }
                Swal.fire({
                    title: 'Respuesta de SUNAT',
                    html: mensaje,
                    icon: response.estado === 'ACEPTADA' ? 'success' : 'warning',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        refresh_datatable();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error!!',
                    html: `<strong>Código de Error:</strong> ${response.codigo_error}<br>
                            <strong>Mensaje de Error:</strong> ${response.mensaje_error}`,
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        refresh_datatable();
                    }
                });
            }
        },
        error: function (xhr, status, error) {
            Swal.fire({
                title: 'Error',
                text: 'Error al procesar la solicitud: ' + error,
                icon: 'error',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    refresh_datatable();
                }
            });
        }
    });
});

$(document).on('click', '.btn_send', function () {
    let invoice_id = $(this).attr('data-process-key');
    $('#send_modal').data('invoice_id', invoice_id);
    $('#emailInput').val('').attr('placeholder', 'Cargando...');
    $('#whatsappInput').val('').attr('placeholder', 'Cargando...');
    $('#send_modal').modal('show');

    $.ajax({
        url: BASE_URL + 'Igvinvoicing/get_client_contact',
        type: 'GET',
        data: { id_igvinvoice: invoice_id },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'OK' && response.result) {
                if (response.result.email) {
                    $('#emailInput').val(response.result.email);
                }
                if (response.result.phone) {
                    $('#whatsappInput').val(response.result.phone);
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al obtener datos:', error);
        },
        complete: function () {
            $('#emailInput').attr('placeholder', 'Ingrese el correo electrónico');
            $('#whatsappInput').attr('placeholder', 'Ingrese el número de WhatsApp');
        }
    });
});

$('#send_modal').on('hidden.bs.modal', function () {
    if ($('#send_form').length > 0 && typeof $('#send_form')[0].reset === 'function') {
        $('#send_form')[0].reset();
    } else {
        $('#emailInput').val('');
    }
    $(this).removeData('invoice_id');
});

function send_email(method) {
    let invoice_id = $('#send_modal').data('invoice_id');
    if (method === 'email') {
        let email = $('#emailInput').val();
        if (!email) {
            Swal.fire({
                title: 'Error',
                text: 'Por favor, ingrese una dirección de correo electrónico válida.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }
        Swal.fire({
            title: 'Enviando...',
            text: 'Por favor, espere mientras se envía el correo electrónico.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        $.ajax({
            url: BASE_URL + 'Igvinvoicing/send_email',
            type: 'GET',
            data: {
                id_igvinvoice: invoice_id,
                email: email
            },
            dataType: 'json',
            success: function (response) {
                if (response.status === 'OK') {
                    Swal.fire({
                        title: 'Éxito',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });
                    $('#send_modal').modal('hide');
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: response.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function (xhr, status, error) {
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al enviar el correo electrónico: ' + error,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    }

// Consigue el número y correo
}$(document).on('click', '.btn_send', function () {
    let id_igvbilling = $(this).attr('data-process-key');
    $('#send_modal').data('id_igvbilling', id_igvbilling);
    $('#emailInput').val('').attr('placeholder', 'Cargando...');
    $('#whatsappInput').val('').attr('placeholder', 'Cargando...');
    $('#send_modal').modal('show');

    $.ajax({
        url: BASE_URL + 'Igvbilling/get_number_email',
        type: 'GET',
        data: { id_igvbilling: id_igvbilling },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'OK' && response.data) {
                if (response.data.email) {
                    $('#emailInput').val(response.data.email);
                }
                if (response.data.phone) {
                    $('#whatsappInput').val(response.data.phone);
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al obtener datos:', error);
        },
        complete: function () {
            $('#emailInput').attr('placeholder', 'Ingrese el correo electrónico');
            $('#whatsappInput').attr('placeholder', 'Ingrese el número de WhatsApp');
        }
    });
});

$('#send_modal').on('hidden.bs.modal', function () {
    if ($('#send_form').length > 0 && typeof $('#send_form')[0].reset === 'function') {
        $('#send_form')[0].reset();
    } else {
        $('#emailInput').val('');
    }
    $(this).removeData('id_igvbilling');
});


function send_whatsapp() {
    let whatsappNumber = $('#whatsappInput').val().trim();
    if (/^9\d{8}$/.test(whatsappNumber)) {
        let formattedNumber = '51' + whatsappNumber;
        let whatsappLink = `https://wa.me/${formattedNumber}`;
        window.open(whatsappLink, '_blank');
    } else {
        alert('Por favor, ingrese un número de WhatsApp válido (9 dígitos comenzando con 9)');
    }
}

// Inicializar datatable al cargar la página
load_datatable();

// Inicializar tooltips
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