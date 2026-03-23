// -- Functions
// --
function destroy_datatable_income() {
    // --
    $('#datatables-income').dataTable().fnDestroy();
}

// --
function refresh_datatable_income() {
    // --
    $('#datatables-income').DataTable().ajax.reload();
}

// --
function load_datatable_income() {
    // --
    destroy_datatable_income();
    // --
    let dataTable = $('#datatables-income').DataTable({
        // --
        ajax: {
            url: BASE_URL + '',
            cache: false,
        },
        columns: [
            { data: 'first_name' },
        ],
        // dom: functions.head_datatable(),
        // buttons: functions.custom_buttons_datatable([0,1], '#create_user_modal'), // -- Number of columns
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
function destroy_datatable_income_products() {
    // --
    $('#datatables-income-products').dataTable().fnDestroy();
}

// --
function refresh_datatable_income_products() {
    // --
    $('#datatables-income-products').DataTable().ajax.reload();
}

// --
function load_datatable_income_products() {
    // --
    destroy_datatable_income_products();
    // --
    let dataTable = $('#datatables-income-products').DataTable({
        // --
        ajax: {
            url: BASE_URL + 'Products/get_products',
            cache: false,
        },
        columns: [
            {
                class: 'center',
                render: function (data, type, row) {
                    // --
                    return (
                        '<button class="btn btn-sm btn-info btn-round btn-icon btn_add" data-process-key="'+ row.id_product +'">' +
                        feather.icons['plus'].toSvg({ class: 'font-small-4' }) +
                        '</button>'
                    );
                }
            },
            { data: 'code' },
            { data: 'category' },
            { data: 'description' },     
            { data: 'description' },     
            { data: 'description' },     
            { data: 'description' },      
            { data: 'stock' },
            
        ],
        // dom: functions.head_datatable(),
        // buttons: functions.custom_buttons_datatable([2], '#create_product_modal'), // -- Number of columns
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
function get_business_name_cli() { /**CORREGIR */
    // --
    $.ajax({
        url: BASE_URL + 'Clients/get_business_name_cli',
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
                // --
                data.data.forEach(element => {
                    html += '<option value="' + element.id + '">'+ element.business_name +'</option>';
                });
                // -- Set values for select
                $('#create_income_details_form :input[name=business_name_cli]').html(html);
            }
        }
    })
}

// --
function get_business_name_carrier() { /**CORREGIR */
    // --
    $.ajax({
        url: BASE_URL + 'Carrier/get_business_name_carrier',
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
                // --
                data.data.forEach(element => {
                    html += '<option value="' + element.id + '">'+ element.business_name +'</option>';
                });
                // -- Set values for select
                $('#create_income_details_form :input[name=business_name_carrier]').html(html);
            }
        }
    })
}

// --
function get_campus() {
    // --
    $.ajax({
        url: BASE_URL + 'Campus/get_campus',
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
                // --
                data.data.forEach(element => {
                    // Incluye todos los elementos sin filtrar por id
                    html += '<option value="' + element.id + '">'+ element.description +'</option>';
                });
                // -- Establece los valores para el select
                $('#create_income_details_form :input[name=description]').html(html);
            }
        }
    });
}

// --
/**function get_voucher_type() {
    // --
    $.ajax({
        url: BASE_URL + 'Main/get_voucher_type',
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
                // --
                data.data.forEach(element => {
                    html += '<option value="' + element.id + '">'+ element.description +'</option>';
                });
                // -- Set values for select
                $('#create_income_details_form :input[name=vt_description]').html(html);
            }
        }
    })
}*/
function get_voucher_type() { /** Nuevo cambio*/
    // --
    $.ajax({
        url: BASE_URL + 'Main/get_voucher_type',
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
                // --
                data.data.forEach(element => {
                    // Only include the element with id 4
                    if (element.id === 4) {
                        html += '<option value="' + element.id + '">'+ element.description +'</option>';
                    }
                });
                // -- Set values for select
                $('#create_income_details_form :input[name=vt_description]').html(html);
            }
        }
    });
}

// --
function get_transfer_type() { /** Función Modificada */
    $.ajax({
        url: BASE_URL + 'Main/get_transfer_type', // Actualiza la URL de la solicitud
        type: 'GET',
        dataType: 'json',
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function() {
            console.log('Cargando...');
        },
        success: function(data) {
            if (data.status === 'OK') {
                var html = '<option value="">Seleccionar</option>';
                data.data.forEach(element => {
                    html += '<option value="' + element.id + '">'+ element.description +'</option>';
                });
                $('#create_income_details_form :input[name=tt_description]').html(html);
            }
        }
    });
}

// --
function get_motive_transfer() { /** Función Modificada */
    $.ajax({
        url: BASE_URL + 'Referralguide/get_motive_transfer', // Actualiza la URL de la solicitud
        type: 'GET',
        dataType: 'json',
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function() {
            console.log('Cargando...');
        },
        success: function(data) {
            if (data.status === 'OK') {
                var html = '<option value="">Seleccionar</option>';
                data.data.forEach(element => {
                    html += '<option value="' + element.id + '">'+ element.description +'</option>';
                });
                $('#create_income_details_form :input[name=mt_description]').html(html);
            }
        }
    });
}

// --
function get_payment_type() {
    // --
    $.ajax({
        url: BASE_URL + 'Main/get_payment_type',
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
                // --
                data.data.forEach(element => {
                    html += '<option value="' + element.id + '">'+ element.description +'</option>';
                });
                // -- Set values for select
                $('#create_income_details_form :input[name=pt_description]').html(html);
            }
        }
    })
}

// --
function get_series() {
    // --
    $.ajax({
        url: BASE_URL + 'Income/get_series',
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
                let series = data.data[0]
                // -- Set values for select
                $('#create_income_details_form :input[name=proof_series]').val(series.proof_series);
                $('#create_income_details_form :input[name=voucher_series]').val(series.voucher_series);
            }
        }
    })
}

// -- Events
$(document).on('change', 'select[name="business_name_carrier"]', function() { /* New Event */
    // --
    var selectedCarrier = $(this).val();

    // --
    $.ajax({
        url: BASE_URL + 'Carrier/get_carrier_by_id', // Cambia la URL según tu endpoint
        type: 'GET',
        data: { 'id_carrier': selectedCarrier },
        dataType: 'json',
        contentType: false,
        processData: true,
        cache: false,
        success: function(data) {
            if (data.status === 'OK') {
                var carrierData = data.data;

                // Completa los campos con la información del transportista
                $('input[name="document_number_trans"]').val(carrierData.document_number);
                $('input[name="brand"]').val(carrierData.brand);
                $('input[name="plate"]').val(carrierData.plate);
                $('input[name="drivers_license"]').val(carrierData.drivers_license);
            }
        }
    });
});


// --
$(document).on('change', 'select[name="business_name_cli"]', function() { /* New Event */
    // --
    var selectedClients = $(this).val();

    // --
    $.ajax({
        url: BASE_URL + 'Clients/get_client_by_id', // Cambia la URL según tu endpoint
        type: 'GET',
        data: { 'id_clients': selectedClients },
        dataType: 'json',
        contentType: false,
        processData: true,
        cache: false,
        success: function(data) {
            if (data.status === 'OK') {
                var clientsData = data.data;

                // Completa los campos con la información del destinatario
                $('input[name="document_number_cli"]').val(clientsData.document_number);
                // Otras asignaciones de datos aquí...
            }
        }
    });
});

//--
$(document).on('click', '.btn_add', function() {
    // --
    let value = $(this).attr('data-process-key');
    // --
    let params = {'id_product': value}
    // --
    $.ajax({
        url: BASE_URL + 'Products/get_product_by_id',
        type: 'GET',
        data: params,
        dataType: 'json',
        contentType: false,
        processData: true,
        cache: false,
        beforeSend: function() {
            console.log('Cargando...');
        },
        success: function(data) {
            // --
            if (data.status === 'OK') {
                // Get reference to the products table in the main window
                var table = $('#add_products');
                
                let item = data.data
                // Add a new row to the table with the data of the selected product
                table.append(getHtml(item));
                calcularSubtotal();
            }
        }
    })
});

function getHtml(item) {
    var stock = 1;
    var purchase_price = 1;
    var sale_price = 1;
    var subtotal = stock * purchase_price;
    return ` <tr>
    <td><button class="btn btn-danger btn-delete-product">X</button></td>
    <td>${item.description}</td>
    <td>${item.u_extent}</td>
    <td><input type="number" name="amount" value="1" min="1" class="form-control" oninput="validateAmount(this)"></td>
    </tr>`
}

function getPercentage() {
    return `
    <td>
        <select name="price_percentage[]" id="price_percentage'+cont+'" class="form-control" onchange="calcularSubtotal(this)">
            '<option value="10">10%</option>'+
            '<option value="15">15%</option>'+
            '<option value="20">20%</option>'+
            '<option value="25">25%</option>'+
            '<option value="30">30%</option>'+
           
            '<option value="40">40%</option>'+
           
            '<option value="50">50%</option>'+
        </select>
    </td>`
}

function calcularSubtotal(element) {
    var row = $(element).closest('tr');
    var stock = parseInt(row.find('input[name="stock[]"]').val());
    var purchasePrice = parseFloat(row.find('input[name="purchase_price[]"]').val());
    var percentage = parseInt(row.find('select[name="price_percentage[]"]').val());
    var salePrice = parseFloat(row.find('input[name="sale_price[]"]').val());
    var subtotal = stock * purchasePrice;
    var newSalePrice = (subtotal + (subtotal * (percentage / 100))) / stock;
    
    row.find('span.subtotal').text(subtotal.toFixed(2));
    row.find('input[name="sale_price[]"]').val(newSalePrice.toFixed(2));
    row.find('span.subtotal').attr('data-value', subtotal.toFixed(2));
}

// Función para establecer la fecha actual en el campo de entrada "fecha_emision"
function get_date_issue() {
    var dateIssue = new Date();

    var options = {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    };

    var dateIssueElement = document.getElementById("date_issue");
    if (dateIssueElement) {
        dateIssueElement.value = dateIssue.toLocaleDateString("es-ES", options);
    }
}

// Función para configurar el campo de entrada de fecha de traslado
function get_date_transfer() {
    const transferDateField = document.querySelector('input[name="date_transfer"]');
    
    if (transferDateField) {
        // Obtén la fecha actual
        const currentDate = new Date();
        
        // Convierte la fecha actual a una cadena con el formato YYYY-MM-DD
        const currentDateStr = currentDate.toISOString().split('T')[0];
        
        // Configura el atributo "min" del campo de entrada de fecha
        transferDateField.min = currentDateStr;
    }
}

/*function calcularTotales()
{
	var sub=document.getElementsByName("subtotal");
	var total=0.0;
	for (var i = 0; i < sub.length; i++) {
		total+= document.getElementsByName("subtotal")[i].value;
	}

	$("#total").html("S/. "+total.toFixed(2));
	$("#total_compra").val(total.toFixed(2));
	evaluar();


} */

$(document).on('click', '.btn-delete-product', function() {
    // Delete the row corresponding to the button "x" clicked
    $(this).closest('tr').remove();
});

// -- Redirect Clients
$(document).ready(function() {
    $(document).on('click', '#destinatarioButton', function() {
        window.open(BASE_URL + 'Clients', '_blank');
    });
});


load_datatable_income();
load_datatable_income_products();
get_payment_type();
get_business_name_cli();
get_business_name_carrier();
get_campus();
get_voucher_type();
get_series();
get_transfer_type() /** Nuevo Cambio */
get_motive_transfer();
get_date_transfer();
get_date_issue(); /*llamada a la función*/

