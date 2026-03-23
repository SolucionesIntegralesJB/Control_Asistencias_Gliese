function destroy_datatable() {
    if ($("#datatable-onlineorders").length && $.fn.DataTable.isDataTable("#datatable-onlineorders")) {
        $("#datatable-onlineorders").DataTable().destroy();
    }
}

function refresh_datatable() {
    if ($.fn.DataTable.isDataTable("#datatable-onlineorders")) {
        $("#datatable-onlineorders").DataTable().ajax.reload();
    }
}

function load_datatable() {
    destroy_datatable();

    let dataTable = $("#datatable-onlineorders").DataTable({
        ajax: {
            url: BASE_URL + "Online_Order/get_online_orders",
            cache: false,
            dataSrc: function(json) {
                let pagosPendientes = json.data.some(order => order.Estado_pago === "Pendiente");

                if (pagosPendientes) {
                    showWarningAlert({
                        message: "Tiene pedidos con pagos pendientes. Revise su historial para más detalles."
                    });
                }

                return json.data; 
            }
        },
        columns: [
            { data: "Fecha" },
            { data: "Cliente" },
            { data: "Series" },
            { data: "Correlative" },
            { data: "Total_venta" },
            {
                data: "Estado_pago",
                render: function (data) {
                    let badgeClass = {
                        "Pendiente": "badge-light-warning",
                        "Pagado": "badge-light-success",
                        "Anulado": "badge-light-danger"
                    }[data] || "badge-light-secondary";
                    return `<span class="badge rounded-pill ${badgeClass}">${data}</span>`;
                }
            },
            {
                data: "Estado_entrega",
                render: function (data) {
                    let badgeClass = {
                        "Enviado": "badge-light-primary",
                        "Entregado": "badge-light-success",
                        "Anulado": "badge-light-danger"
                    }[data] || "badge-light-secondary";
                    return `<span class="badge rounded-pill ${badgeClass}">${data}</span>`;
                }
            },
            {
                className: "text-center",
                render: function (data, type, row) {
                    let editIcon = feather.icons['edit'] ? feather.icons['edit'].toSvg({ class: 'font-small-4' }) : '';
                    return `
                        <button class="btn btn-sm btn-info btn-round btn-icon btn_update" data-id="${row.id_pedido}">
                            ${editIcon}
                        </button>
                        <button class="btn btn-sm btn-light btn-round btn-icon btn_pdf" data-id="${row.id_pedido}_1">
                        <img src="${BASE_URL}public/app-assets/images/svg/pdf.svg" style="width: 25px; height: 25px;" alt="PDF">
                        </button>
                        <button class="btn btn-sm btn-light btn-round btn-icon btn_xml" data-id="${row.id_pedido}_2" target="_blank"> 
                        <img src="${BASE_URL}public/app-assets/images/svg/receipt.svg" style="width: 25px; height: 25px;" alt="File Text"> 
                        </button> 
                        `
                        ;
                }
            }
        ],
        order: [[1, 'asc']],
        language: {
            url: BASE_URL + "public/assets/json/languaje-es.json"
        }
    });
}

function update_online_order(form) {
    $('#btn_update_order').prop('disabled', true);
    
    let params = new FormData(form);
    let documentType = $('#update_order_form :input[name=document_type]').find('option:selected').text();
    params.append('document_type', documentType);

    $.ajax({
        url: BASE_URL + 'Online_Order/update_online_order',
        type: 'POST',
        data: params,
        dataType: 'json',
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function() {
        },
        success: function(data) {
            
            functions.toast_message(data.type, data.msg, data.status);
            if (data.status === 'OK') {
                $('#update_order_modal').modal('hide');
                form.reset();
                refresh_datatable();
            } 
                $('#btn_update_order').prop('disabled', false);
            
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#btn_update_order').prop('disabled', false); 
        }
    });
}


$(document).on('click', '.btn_update', function(){
    let value = $(this).attr('data-id');
    let params = {'id_pedido': value};

    $.ajax({
        url: BASE_URL + 'Online_Order/get_order_by_id',
        type: 'GET',
        data: params,
        dataType: 'json',
        cache: false,
        success: function(data){
            console.log(data);
            if(data.status === 'OK') {
                let item = data.data

                $('#update_order_form :input[name=id_pedido]').val(item.id_pedido);
                $('#update_order_form :input[name=estado_pago]').val(item.estado_pago);
                $('#update_order_form :input[name=estado_entrega]').val(item.estado_entrega);
            }
        }
    })
    $('#update_order_modal').modal('show');
});
$(document).on('click', '.btn_pdf', function () {
    let value = $(this).attr('data-id');
    let [id_pedido, tipo] = value.split('_');

    console.log("ID del pedido:", id_pedido);
    console.log("Tipo:", tipo);

    if (!id_pedido || !tipo) {
        console.error("Error: id_pedido o tipo están vacíos.");
        return; // No continuar si hay error
    }

    let url = BASE_URL + 'Online_Order/get_onlineOrder_Report?id_pedido=' + id_pedido + '&tipo=' + tipo;
    console.log("URL generada:", url);

    window.open(url, '_blank');
});



$(document).on('click', '.btn_xml', function () {
    let value = $(this).attr('data-id');
    let [id_pedido ,tipo]= value.split('_');
    console.log("Valor de data-id:", value);
    let url = BASE_URL + 'Online_Order/get_onlineOrder_Report?id_pedido=' + id_pedido + '&tipo=' + tipo;
    console.log("URL generada:", url);
    window.open(url, '_blank');
});

function showWarningAlert(warning) {
    Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'Ver pagos pendientes',
        cancelButtonText: 'Cerrar',
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            confirmButton: 'btn btn-warning btn-sm',
            cancelButton: 'btn btn-outline-secondary btn-sm ms-1',
            container: 'p-20'
        },
        buttonsStyling: false
    }).fire({
        icon: 'warning',
        title: '<i class="fas fa-exclamation-triangle"></i> ¡Pagos Pendientes!',
        html: `
            <div class="text-justify" style="max-width: 300px">
                <p class="mb-2">${warning.message}</p>
                <hr class="my-2">
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Recuerde: Recuerde hacer los pagos pendientes antes que su recibo venza.
                </p>
            </div>
        `
    })
}
$(document).ready(function() {
    load_datatable();
});














// ------------------------------------------------------


// --
function get_document_types() {
    // --
    $.ajax({
        url: BASE_URL + "Main/get_document_types",
        type: "GET", 
        dataType: "json",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function() {
            console.log("Cargando tipos de documento...");
        },
        success: function(data) {
            // --
            if (data.status === "OK") {
                // --
                var html = '<option value="">Seleccionar</option>';
                // --
                data.data.forEach(element => {
                    html += '<option value="' + element.id + '">' + 
                           element.description + '</option>';
                });
                // -- Set values for select
                $("#create_online_order_form :input[name=document_type]").html(html);
                $("#update_online_order_form :input[name=document_type]").html(html);
            } 
        },
    });
}
function get_payment_status() {
    $.ajax({
        url: BASE_URL + "Online_Order/get_payment_status",
        type: "GET",
        dataType: "json",
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            if (data.status === "OK") {
                var html = '<div class="btn-group btn-group-sm" role="group">';
                data.data.forEach((element, index) => {
                    html += `
                        <input type="radio" class="btn-check" 
                               id="payment_status_${element.id}" 
                               name="payment_status" 
                               value="${element.id}" 
                               autocomplete="off"
                               ${element.id === 'PENDING' ? 'checked' : ''}>
                        <label class="btn ${element.id === 'PAID' ? 'btn-outline-success' : 'btn-outline-warning'}" 
                               for="payment_status_${element.id}">
                            ${element.name}
                        </label>
                    `;
                });
                html += '</div>';

                $("#create_order_form .payment-status-options").html(html);
                $("#update_order_form .payment-status-options").html(html);
            }
        }
    });
}

function get_delivery_status() {
    $.ajax({
        url: BASE_URL + "Online_Order/get_delivery_status",
        type: "GET",
        dataType: "json",
        cache: false,
        contentType: false,
        processData: false,
        success: function(data) {
            if (data.status === "OK") {
                var html = '<div class="btn-group btn-group-sm" role="group">';
                data.data.forEach((element, index) => {
                    html += `
                        <input type="radio" class="btn-check" 
                               id="delivery_status_${element.id}" 
                               name="delivery_status" 
                               value="${element.id}" 
                               autocomplete="off"
                               ${element.id === 'PENDING' ? 'checked' : ''}>
                        <label class="btn ${getDeliveryStatusClass(element.id)}" 
                               for="delivery_status_${element.id}">
                            ${element.name}
                        </label>
                    `;
                });
                html += '</div>';

                $("#create_order_form .delivery-status-options").html(html);
                $("#update_order_form .delivery-status-options").html(html);
            }
        }
    });
}

function getDeliveryStatusClass(status) {
    const classes = {
        'PENDING': 'btn-outline-warning',
        'PROCESSING': 'btn-outline-info',
        'DELIVERED': 'btn-outline-success'
    };
    return classes[status] || 'btn-outline-secondary';
}

// Inicializar los estados cuando se carga la página
$(document).ready(function() {
    try {
        load_datatable();
    } catch (e) {
        console.log('Error al inicializar datatable:', e);
    }
    get_payment_status();
    get_delivery_status();
});


// Eventos del formulario
$(document).ready(function() {
    $("#edit_order_form").on("submit", function(e) {
        e.preventDefault();
        update_order(this);
    });
});