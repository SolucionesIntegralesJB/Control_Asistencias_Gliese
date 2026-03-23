// -- Functions

// --
function destroy_datatable() {
    // --
    $('#datatable-usersale').dataTable().fnDestroy();
}

// --
function refresh_datatable() {
    // --
    $('#datatable-usersale').DataTable().ajax.reload();
}

// --
function load_datatable() {
    // --
    destroy_datatable();
    // --
    let dataTable = $('#datatable-usersale').DataTable({
        // --
        ajax: {
            url: BASE_URL + 'Loaninquiry/get_loaninquiry',
            cache: false,
        },
        columns: [
            { data: 'code' },
            { data: 'category' },
            { data: 'description' },
            { data: 'stock' },
            { data: 'purchase_price'},
            { data: 'sale_price'},
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

// --
function get_categories() {
    // --
    $.ajax({
        url: BASE_URL + 'Categories/get_categories',
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
                // --
                var html = '<option value="">Seleccionar</option>';
                // var html = '';
                // --
                data.data.forEach(element => {
                    html += '<option value="' + element.id + '">'+ element.description +'</option>';
                });
                // -- Set values for select
                $('#create_product_form :input[name=id_category]').html(html);
                $('#update_product_form :input[name=id_category]').html(html);
            }
        }
    })
}

  





//--
get_categories();
//--
load_datatable();