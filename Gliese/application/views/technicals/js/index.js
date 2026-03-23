// -- Functions

// --
function destroy_datatable() {
    // --
    $('#datatable-technicals').dataTable().fnDestroy();
}

// --
function refresh_datatable() {
    // --
    $('#datatable-technicals').DataTable().ajax.reload();
}

// --
function load_datatable() {
    // --
    destroy_datatable();
    // --
    let dataTable = $('#datatable-technicals').DataTable({
        // --
        ajax: {
            url: BASE_URL + 'Technicals/get_technicals',
            cache: false,
        },
        columns: [
            { data: 'name' },
            { data: 'document_type' },
            { data: 'document_number' },
            { data: 'phone' },
            { data: 'area' },
            { data: 'cargo' },
            { data: 'technical_type' },    
            {
                class: 'center',
                render: function (data, type, row, meta) {
                    // --
                    return (
                        '<button class="btn btn-sm btn-info btn-round btn-icon btn_update" data-process-key="'+ row.id_technicals +'">' +
                        feather.icons['edit'].toSvg({ class: 'font-small-4' }) +
                        '</button>'
                        + ' ' + 
                        '<button  class="btn btn-sm btn-danger btn-round btn-icon btn_delete" data-process-key="'+ row.id_technicals +'">' +
                        feather.icons['trash-2'].toSvg({ class: 'font-small-4' }) +
                        '</button>'
                    );
                }
            },    
        ],
        dom: functions.head_datatable(),
        buttons: functions.custom_buttons_datatable([7], '#create_technicals_modal'), // -- Number of columns
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
function get_document_types() {
    // --
    $.ajax({
        url: BASE_URL + 'Main/get_document_types',
        type: 'GET',
        dataType: 'json',
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function() {
            console.log('Cargando...');
        },
        success: function(data) {
            // --
            if (data.status === 'OK') {
                // -- Find the first item with id equal to 1
                const firstDocumentType = data.data.find(item => item.id === 1);

                if (firstDocumentType) {
                    // -- Set values for select with only the first item
                    var html = '<option value="' + firstDocumentType.id + '">' + firstDocumentType.description + '</option>';
                    $('#create_technicals_form :input[name=document_type]').html(html);
                    $('#update_technicals_form :input[name=document_type]').html(html);
                }
            }
        }
    });
}


//--
function create_technicals(form) {
    // --
    $('#btn_create_technicals').prop('disabled', true);
    // --
    let params = new FormData(form);
    let documentType = $('#create_technicals_form :input[name=document_type]').find('option:selected').text();
    // --
    params.append('description_document_type', documentType);
    // --
    $.ajax({
        url: BASE_URL + 'Technicals/create_technicals',
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
                $('#create_technicals_modal').modal('hide');
                form.reset();
                refresh_datatable();

            } else {
                // --
                $('#btn_create_technicals').prop('disabled', false);
            }
        }
    })
}

//--
function update_technicals(form) {
    // --
    $('#btn_update_technicals').prop('disabled', true);
    // --
    let params = new FormData(form);
    let documentType = $('#update_technicals_form :input[name=document_type]').find('option:selected').text();
    // --
    params.append('description_document_type', documentType);
    // -- 
    $.ajax({
        url: BASE_URL + 'Technicals/update_technicals',
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
                $('#update_technicals_modal').modal('hide');
                form.reset();
                refresh_datatable();

            } else {
                // --
                $('#btn_update_technicals').prop('disabled', false);
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
    let params = {'id_technicals': value}
    // --
    $.ajax({
        url: BASE_URL + 'Technicals/get_technicals_by_id',
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
                $('#update_technicals_form :input[name=id_technicals]').val(item.id_technicals);
                $('#update_technicals_form :input[name=name]').val(item.name);
                $('#update_technicals_form :input[name=document_number]').val(item.document_number);
                $('#update_technicals_form :input[name=phone]').val(item.phone);
                $('#update_technicals_form :input[name=area]').val(item.area);
                $('#update_technicals_form :input[name=cargo]').val(item.cargo);
                $('#update_technicals_form :input[name=technical_type]').val(item.technical_type);
                // --
                $('#update_technicals_form :input[name=document_type]').val(item.id_document_type).trigger('change');
            }
        }
    })
    // --
    $('#update_technicals_modal').modal('show');
})

// --
$(document).on('click', '.btn_delete', function() {
    // --
    let value = $(this).attr('data-process-key');
    // --
    let params = {'id_technicals': value}
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
                url: BASE_URL + 'Technicals/delete_technicals',
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

// -- Reset forms
$(document).on('click', '.reset', function() {
    // --
    $('#create_technicals_form').validate().resetForm();
    $('#update_technicals_form').validate().resetForm();
})

// -- Validate form
$('#create_technicals_form').validate({
    // --
    submitHandler: function(form) {
        create_technicals(form);
    }
})

// -- Validate form
$('#update_technicals_form').validate({
    // --
    submitHandler: function(form) {
        update_technicals(form);
    }
})

// -- Reset form on modal hidden
$('.modal').on('hidden.bs.modal', function () {
    // --
    $(this).find('form')[0].reset();
    // -- Enable buttons
    $('#btn_create_technicals').prop('disabled', false);
    $('#btn_update_technicals').prop('disabled', false);
});



get_document_types();
//--
load_datatable();
