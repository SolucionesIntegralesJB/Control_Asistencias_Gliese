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
function get_business_name() {
    // --
    $.ajax({
        url: BASE_URL + 'Suppliers/get_business_name',
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
                $('#create_income_details_form :input[name=business_name]').html(html);
            }
        }
    })
}

// --
function get_voucher_type() {
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

//--
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
    <td><input type="number"  name="stock[]" value="${stock}" class="form-control" oninput="calcularSubtotal(this)"></td>
    <td><input type="number"  name="sale_price[]" value="${sale_price}" min=0.1 class="form-control" ></td>
    <td><input type="text" name="text[]" class="form-control"></td>
    <td><input type="number"  name="sale_price[]" value="${sale_price}" min=0.1 class="form-control" ></td>
    <td><span class="subtotal" data-value="${subtotal}">${subtotal.toFixed(2)}</span></td>
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

    calculatesubtotal();
}

function calculatesubtotal() {
    var total = 0.0;
    $('.subtotal').each(function () {
        total += parseFloat($(this).attr('data-value'));
    });

    // Update the item that shows the total subtotals
    $('#total-subtotal').text(total.toFixed(2));
}
$('#cuota').on('input', calculateQuotaValue);

$(document).on('click', '.btn-delete-product', function() {
    // Delete the row corresponding to the button "x" clicked
    $(this).closest('tr').remove();
});


$(document).ready(function () {
    $('select[name="pt_description"]').change(function () {
        var selectedOption = $(this).val();

        if (selectedOption === '2') {
            $('#add_cuota').closest('.row').show();
        } else {
            $('#add_cuota').closest('.row').hide();
        }
    });
    
    var initialOption = $('select[name="pt_description"]').val();
    if (initialOption === '1') {
        $('#add_cuota').closest('.row').show();
    } else {
        $('#add_cuota').closest('.row').hide();
    }
});

function calculateQuotaValue() {
    var total = parseFloat($('#total-subtotal').text()); 
    var cuotas = parseInt($('#cuota').val()); 

    if (!isNaN(total) && !isNaN(cuotas) && cuotas > 0) {
        var valorDeCuota = total / cuotas;
        $('#valor-de-cuota').text(valorDeCuota.toFixed(2)); 
    }
}

$(document).ready(function () {
    function setDefaultDate() {
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var dd = String(today.getDate()).padStart(2, '0');
        var formattedDate = yyyy + '-' + mm + '-' + dd;
        $("#start_date").val(formattedDate);
    }

    setDefaultDate();
    $(document).on("click", "#start_date", function () {
        generarCuotas();
    });

    $("#credit_type").change(function () {
        if ($(this).val() === "mensual") {
            $("#start_date_label").text("Día de pago");
            $("#start_date").remove();
            var select = $("<select name='proof_date' class='form-control' id='start_date'></select>");
            for (var i = 1; i <= 31; i++) {
                select.append("<option value='" + i + "'>" + i + "</option>");
            }
            $("#start_date_label").append(select);
        } else if ($(this).val() === "selectime") {
            $("#start_date").remove();
            var generarButton = $("<input type='button' name='proof_date' value='Generar' id='start_date' onclick='generarCuotas()' class='form-control'>");
            $("#start_date_label").append(generarButton);
            inputDate.change(function() {
                generarCuotas();
            });
        } else {
            $("#start_date_label").text("Fecha de inicio");
            $("#start_date").remove();
            var inputDate = $("<input type='date' name='proof_date' class='form-control' id='start_date'>");
            $("#start_date_label").append(inputDate);
            
            setDefaultDate();
            $("#tblListadoFechas").parent().hide();
        }
    });
    function generarCuotas() {
        // Verificar que el tipo de crédito sea "Seleccionar fechas" antes de mostrar la tabla
        if ($("#credit_type").val() === "selectime") {
            // Aquí debes agregar la lógica para mostrar la tabla que proporcionaste
            $("#tblListadoFechas").parent().show(); // Muestra la tabla
        } else {
            // Si no es "Seleccionar fechas," ocultar la tabla
            $("#tblListadoFechas").parent().hide();
        }
    }
});



load_datatable_income();
load_datatable_income_products();
get_payment_type();
get_business_name();
get_voucher_type();
get_series();