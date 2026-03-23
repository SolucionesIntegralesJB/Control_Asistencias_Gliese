// -- Functions

// --
function destroy_datatable() {
    // --
    $('#datatable-proforma').dataTable().fnDestroy();
}

// --
function refresh_datatable() {
    // --
    $('#datatable-proforma').DataTable().ajax.reload();
}

// --
function load_datatable() {
    // --
    destroy_datatable();
    // --
    let dataTable = $('#datatable-proforma').DataTable({
        // --
        ajax: {
            url: BASE_URL + 'Ticket/get_proforma',
            cache: false,
        },
        columns: [
            { data: 'date' },
            { data: 'clients' },
            { data: 'user' },
            { data: 'voucher_type' },
            { data: 'correlative' },
            { data: 'total_sale' },
            { data: 'status',
                class: 'center',
                render: function (data, type, row, meta) {
                    if (row.status == "1") {
                        return ('<span class="badge rounded-pill badge-light-success" text-capitalized="">Activo</span>');
                    } else {
                        return ('<span class="badge rounded-pill badge-light-secondary" text-capitalized="">Inactivo</span>');
                    }
                }
            },    
            {
                class: 'center',
                render: function (data, type, row, meta) {
                    return (
                        '<button class="btn btn-sm btn-info btn-round btn-icon btn_update" data-process-key="'+ row.id_ticket +'">' +
                        feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
                        '</button>'
                        + ' ' + 
                        '<button  class="btn btn-sm btn-danger btn-round btn-icon btn_delete" data-process-key="'+ row.id_ticket +'">' +
                        feather.icons['trash-2'].toSvg({ class: 'font-small-4' }) +
                        '</button>'
                    );
                }
            },    
        ],
        dom: functions.head_datatable(),
        buttons: functions.custom_buttons_datatable([7], '#create_ticket_modal'), // -- Number of columns
        language: {
            url: BASE_URL + 'public/assets/json/languaje-es.json'
        }
    })

    // --
    dataTable.on('xhr', function() {
        // --
        var data = dataTable.ajax.json();
        // --
        functions.toast_message(data.type, data.msg, data.status);
    });
}

// --
function update_ticket(form) {
    // --
    $('#btn_update_ticket').prop('disabled', true);
    // --
    let params = new FormData(form);
    // --
    $.ajax({
        url: BASE_URL + 'Ticket/update_ticket',
        type: 'POST',
        data: params,
        dataType: 'json',
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function() {
            console.log('Cargando...');
        },
        success: function(data) {
            // --
            functions.toast_message(data.type, data.msg, data.status);
            // --
            if (data.status === 'OK') {
                // --
                $('#update_ticket_modal').modal('hide');
                form.reset();
                refresh_datatable();

            } else {
                // --
                $('#btn_update_ticket').prop('disabled', false);
            }
        }
    })
}

// -- Events

//--
$(document).on('click', '.btn_update', function() {
    // --
    let value = $(this).attr('data-process-key');
    // --
    let params = {'id_ticket': value}
    // --
    $.ajax({
        url: BASE_URL + 'Ticket/get_ticket_by_id',
        type: 'GET',
        data: params,
        dataType: 'json',
        contentType: false,
        processData: true,
        cache: false,
        success: function(data) {
            // --
            if (data.status === 'OK') {
                // --
                let item = data.data
                // --
                $('#update_ticket_form :input[name=id_ticket]').val(item.id_ticket);
                $('#update_ticket_form :input[name=id_category]').val(item.id_category);
                $('#update_ticket_form :input[name=description]').val(item.description);
                $('#update_ticket_form :input[name=stock]').val(item.stock);
                $('#update_ticket_form :input[name=code]').val(item.code);
                // --
            }
        }
    })
    // --
    $('#update_ticket_modal').modal('show');
})

// --
$(document).on('click', '.btn_delete', function() {
    // --
    let value = $(this).attr('data-process-key');
    // --
    let params = {'id_ticket': value}
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
                url: BASE_URL + 'Ticket/delete_ticket',
                type: 'POST',
                data: params,
                dataType: 'json',
                cache: false,
                success: function(data) {
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

// -- Redirect new controller
$(document).on('click', '.create-new', function() {
    // --
    window.location.assign(BASE_URL + 'Ticket_Details');
})

// -- Reset forms
$(document).on('click', '.reset', function() {
    // --
    $('#update_ticket_form').validate().resetForm();
})



// -- Validate form
$('#update_ticket_form').validate({
    // --
    submitHandler: function(form) {
        update_ticket(form);
    }
})

// -- Reset form on modal hidden
$('.modal').on('hidden.bs.modal', function () {
    // --
    $(this).find('form')[0].reset();
    // --
    $('#btn_update_ticket').prop('disabled', false);
});




//--
load_datatable();