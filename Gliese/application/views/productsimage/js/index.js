// -- Functions

// --
function destroy_datatable() {
    // --
    $('#datatable-productsimage').dataTable().fnDestroy();
}

// --
function refresh_datatable() {
    // --
    $('#datatable-productsimage').DataTable().ajax.reload();
}

// --
function load_datatable() {
    // --
    destroy_datatable();
    // --
    let dataTable = $('#datatable-productsimage').DataTable({
        // --
        ajax: {
            url: BASE_URL + 'ProductsImage/get_products',
            cache: false,
        },
        columns: [
            { data: 'code' },
            { data: 'name' },
            { data: 'image' },
            {
                class: 'center',
                render: function (data, type, row) {
                    // --
                    return (
                        '<button class="btn btn-sm btn-primary btn-round btn-icon btn_update" data-process-key="'+ row.id_product +'">' +
                        feather.icons['image'].toSvg({ class: 'font-small-4' }) +
                        '</button>'
                    );
                }
            },
        ],
        dom: functions.head_datatable(),
        buttons: functions.custom_buttons_datatable2([0]),
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

//--
function update_product(form) {
    // --
    $('#btn_update_product').prop('disabled', true);
    // --
    let params = new FormData(form);
    // --
    $.ajax({
        url: BASE_URL + 'ProductsImage/update_product',
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
                $('#update_product_modal').modal('hide');
                form.reset();
                refresh_datatable();

            } else {
                // --
                $('#btn_update_product').prop('disabled', false);
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
    let params = {'id_product': value}
    // --
    $.ajax({
        url: BASE_URL + 'ProductsImage/get_product_by_id',
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
                $('#update_product_form :input[name=id_product]').val(item.id_product);
                $('#update_product_form :input[name=code]').val(item.code);
                $('#update_product_form :input[name=name]').val(item.name);
                $('#update_product_form :input[name=image]').val(item.image);
                // --
            }
        }
    })
    // --
    $('#update_product_modal').modal('show');
})

// --
$(document).on('click', '.btn_update', function() {
    // --
    let value = $(this).attr('data-process-key');
    // --
    let params = {'id_product': value}
    // --
    $.ajax({
        url: BASE_URL + 'ProductsImage/get_product_by_id',
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
                $('#update_product_form :input[name=id_product]').val(item.id);
            }
        }
    })
    // --
    $('#update_product_modal').modal('show');
})


// --
$(document).on('click', '.btn_delete', function() {
    // --
    let value = $(this).attr('data-process-key');
    // --
    let params = {'id_product': value}
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
                url: BASE_URL + 'ProductsImage/delete_product',
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
    $('#create_productimage_form').validate().resetForm();
    $('#update_product_form').validate().resetForm();
})

// -- Validate form
$('#create_productimage_form').validate({
    // --
    submitHandler: function(form) {
        create_product(form);
    }
})

// -- Validate form
$('#update_product_form').validate({
    // --
    submitHandler: function(form) {
        update_product(form);
    }
})

// -- Reset form on modal hidden
$('.modal').on('hidden.bs.modal', function () {
    // --
    $(this).find('form')[0].reset();
    // -- Enable buttons
    $('#btn_create_productimage').prop('disabled', false);
    $('#btn_update_product').prop('disabled', false);
});

// --
document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.getElementById("imageInput");

    imageInput.addEventListener("change", function () {
        // Accede a la lista de archivos seleccionados
        const files = this.files;

        // Puedes realizar acciones adicionales con los archivos, como enviarlos al servidor, etc.
        // Por ejemplo:
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            console.log("Nombre del archivo:", file.name);
            console.log("Tipo de archivo:", file.type);
            console.log("Tamaño del archivo:", file.size, "bytes");
            // Puedes enviar el archivo al servidor aquí
        }
    });
});


// --
load_datatable()