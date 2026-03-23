// -- Functions
// --
function destroy_datatable_proforma() {
    $("#datatables-proforma").dataTable().fnDestroy();
}

function refresh_datatable_proforma() {
    $("#datatables-proforma").DataTable().ajax.reload();
}

function destroy_datatable_proforma_products() {
    $("#datatables-proforma-products").dataTable().fnDestroy();
}

function refresh_datatable_proforma_products() {
    $("#datatables-proforma-products").DataTable().ajax.reload();
}

function load_datatable_proforma_products(id_campus) {
    destroy_datatable_proforma_products();
    let dataTable = $("#datatables-proforma-products").DataTable({
        ajax: {
            url: BASE_URL + "Products/get_products_by_campus",
            cache: false,
            data: function (d) {
                d.id_campus = id_campus;
            },
        },
        columns: [
            {
                class: "center",
                render: function (data, type, row) {
                    return (
                        '<button class="btn btn-sm btn-info btn-round btn-icon btn_add" data-process-key="' +
                        row.id_product +
                        '">' +
                        feather.icons["plus"].toSvg({ class: "font-small-4" }) +
                        "</button>"
                    );
                },
            },
            { data: "code" },
            { data: "name" },
            { data: "unit" },
            { data: "stock" },
            { data: "price" },
            {
                class: "center",
                render: function (data, type, row) {
                    return `
                        <select class="form-select tributo-select" data-product-id="${row.id_product}" data-tributo style="width: 120px;">
                            <option value="IGV">Gravado</option>
                            <option value="EXO">Exonerado</option>
                            <option value="INA">Inafectas</option>
                            <option value="GRA">Gratuitas</option>
                        </select>
                    `;
                },
            },
        ],
        language: {
            url: BASE_URL + "public/assets/json/languaje-es.json",
        },
    });
    dataTable.on("xhr", function () {
        var data = dataTable.ajax.json();
        if (data && data.status === "OK") {
            functions.toast_message(data.type, data.msg, data.status);
        }
    });
}

function calculateTotalSale() {
    let total = 0;
    $("#add_products tr").each(function () {
        const importe = parseFloat($(this).find(".importe").text()) || 0;
        total += importe;
    });
    return total.toFixed(2);
}

// Función modificada para referencia editable
function updateReference() {
    let references = [];
    $("#add_products tr").each(function () {
        const productName = $(this).find('span[name="name[]"]').text().trim();
        if (productName) {
            references.push(productName);
        }
    });
    
    const refInput = $('input[name="reference"]');
    if (!refInput.data('manual')) {
        refInput.val(references.join(", "));
    }
}

// Evento para marcar edición manual en referencia
$(document).ready(function() {
    $('input[name="reference"]').on('input', function() {
        $(this).data('manual', true);
    });
});

function create_proforma(form) {
    let params = new FormData(form);
    params.append('id_campus', window.id_campus);
    params.append('id_user', window.current_user_id);
    params.append('total_sale', calculateTotalSale());

    $.ajax({
        url: BASE_URL + "proforma_Details/create_proforma",
        type: "POST",
        data: params,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (response) {
            if (response.status === "OK") {
                functions.toast_message(response.type, response.msg, response.status);
                $("#create_proforma_details_form")[0].reset();
                // Resetear marca de edición manual
                $('input[name="reference"]').data('manual', false);
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.msg,
                    confirmButtonText: 'OK',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.href = BASE_URL + 'Proforma/index.php';
                });
            } else {
                functions.toast_message(response.type, response.msg, response.status);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            let errorMessage = "Error en la conexión con el servidor";
            if (jqXHR.responseText) {
                let match = jqXHR.responseText.match(/<b>(.*?)<\/b>/);
                if (match) {
                    errorMessage = match[1];
                }
            }
            functions.toast_message("error", errorMessage, "ERROR");
        },
    });
}

$(document).ready(function () {
    $("#btn_create_form").on("click", function (e) {
        e.preventDefault();
        $("#create_proforma_details_form").submit();
    });
});

$(document).ready(function () {
    $("#create_proforma_details_form").validate({
        submitHandler: function (form) {
            create_proforma(form);
        },
    });
});

function get_coins() {
    $.ajax({
        url: BASE_URL + "Main/get_coins",
        type: "GET",
        dataType: "json",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function () {
            console.log("Cargando...");
        },
        success: function (data) {
            if (data.status === "OK" && data.data.length > 0) {
                $("#create_proforma_details_form span[name=coins]").text(
                    data.data[0].description
                );
                $("#create_proforma_details_form input[name=coins]").val(
                    data.data[0].code
                );
            }
        },
    });
}

function get_igv() {
    $.ajax({
        url: BASE_URL + "Main/get_igv",
        type: "GET",
        dataType: "json",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function () {
            console.log("Cargando...");
        },
        success: function (data) {
            if (data.status === "OK" && data.data.length > 0) {
                $("#create_proforma_details_form :input[name=igv]").val("18");
            }
        },
    });
}

function get_business_name_cli() {
    $.ajax({
        url: BASE_URL + "Clients/get_business_name_cli",
        type: "GET",
        dataType: "json",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function () {
            console.log("Cargando...");
        },
        success: function (data) {
            if (data.status === "OK") {
                var html = '<option value="">Seleccionar</option>';
                data.data.forEach((element) => {
                    html +=
                        '<option value="' +
                        element.id +
                        '">' +
                        element.business_name +
                        "</option>";
                });
                $("#create_proforma_details_form :input[name=business_name_cli]").html(
                    html
                );
            }
        },
    });
}

function get_voucher_type() {
    return 10;
}

function get_campus() {
    $.ajax({
        url: BASE_URL + "Location/get_locations_by_user",
        type: "GET",
        dataType: "json",
        cache: false,
        success: function (data) {
            if (data.status === "OK") {
                let user = data.data["user"];
                let id_campus = user["id_location"];
                let id_user = user["id_user"];
                load_datatable_proforma_products(id_campus);
                window.current_user_id = id_user;
                window.id_campus = id_campus;
                $("#create_proforma_details_form :input[name=id_campus]").val(id_campus);
                $("#create_proforma_details_form :input[name=id_user]").val(id_user);
            }
        },
    });
}

function get_date() {
    var fechaActual = new Date();
    var año = fechaActual.getFullYear();
    var mes = (fechaActual.getMonth()+1).toString().padStart(2, "0");
    var dia = (fechaActual.getDate()).toString().padStart(2, "0");
    var fechaFormateada = `${año}-${mes}-${dia}`;

    $('input[name="date_issue"]').val(fechaFormateada);
}

// -- Events
$(document).on("change", 'select[name="business_name_cli"]', function () {
    var selectedClients = $(this).val();
    $.ajax({
        url: BASE_URL + "Clients/get_client_by_id",
        type: "GET",
        data: { id_clients: selectedClients },
        dataType: "json",
        contentType: false,
        processData: true,
        cache: false,
        success: function (data) {
            if (data.status === "OK") {
                var clientsData = data.data;
                $('input[name="document_number_cli"]').val(clientsData.document_number);
                $('input[name="address_cli"]').val(clientsData.address);

                selectedDocumentType = clientsData.document_description;
                validateDocumentAndVoucher();
            }
        },
    });
});

let selectedDocumentType = '';

function validateDocumentAndVoucher() {
    var voucherType = $('select[name="vt_description"]').val();

    if (selectedDocumentType === 'DNI' && voucherType === '1') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se puede emitir una Proforma para un cliente con DNI.',
            confirmButtonText: 'Entendido',
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                $('select[name="vt_description"]').val('2').trigger('change');
            }
        });
        return false;
    }
    return true;
}

$(document).on("change", 'select[name="vt_description"]', function () {
    validateDocumentAndVoucher();
});

// Evento modificado para actualización controlada de referencia
$(document).on("click", ".btn_add", function () {
    let value = $(this).attr("data-process-key");
    let tributo = $('select.tributo-select[data-product-id="' + value + '"]').val();
    let params = {
        id_product: value,
        id_campus: window.id_campus,
    };

    let existingRow = $('#add_products tr[data-product-id="' + value + '"][data-tributo="' + tributo + '"]');
    if (existingRow.length > 0) {
        functions.toast_message("info", "Ya existe el producto.", "INFORMACIÓN");
        return;
    }

    $.ajax({
        url: BASE_URL + "Products/get_product_by_campus_and_id",
        type: "GET",
        data: params,
        dataType: "json",
        contentType: false,
        processData: true,
        cache: false,
        beforeSend: function () {
            console.log("Cargando...");
        },
        success: function (data) {
            if (data.status === "OK") {
                var table = $("#add_products");
                let item = data.data;
                table.append(getHtml(item));
                updateTotals();
                updateReference(); // Actualización controlada
            }
        },
    });
});

function calcularValores(price, cantidad, tributo) {
    let VentaU, Newprice, Impuesto, venta_total, importe;

    if (tributo === "IGV") {
        Newprice = price;
        VentaU = price / 1.18;
        importe = Newprice * cantidad;
        venta_total = VentaU * cantidad;
        Impuesto = importe - venta_total;
    } else if (tributo === "EXO" || tributo === "INA") {
        VentaU = price / 1.18;
        Newprice = price;
        venta_total = importe = VentaU * cantidad;
        Impuesto = 0;
    } else if (tributo === "GRA") {
        VentaU = price / 1.18;
        Newprice = price;
        Impuesto = venta_total = importe = 0;
        importagra = VentaU * cantidad;
    }

    return {
        VentaU: VentaU.toFixed(5),
        Newprice: Newprice.toFixed(2),
        Impuesto: Impuesto.toFixed(2),
        venta_total: venta_total.toFixed(2),
        importe: importe.toFixed(2),
    };
}

function getHtml(item) {
    const tributo = $('select.tributo-select[data-product-id="' + item.id_product + '"]').val();
    const valores = calcularValores(parseFloat(item.price), 1, tributo);

    return `
    <tr class="table table-lg" data-product-id="${item.id_product}" data-tributo="${tributo}">
        <td class="border text-center"><button class="btn btn-danger btn-sm btn-delete-product">X</button></td>
        <td class="border">
            <span name="code[]">${item.code}</span>
            <input type="hidden" name="id_product[]" value="${item.id_product}">
            <input type="hidden" name="code[]" value="${item.code}">
        </td>
        <td class="border">
            <span name="name[]">${item.name}</span>
            <input type="hidden" name="name[]" value="${item.name}">
        </td>
        <td class="border"><input type="text" name="serie[]" value=" "class="form-control"></td>
        <td class="border">
            <span name="u_medida[]">${item.unit_code}</span>
            <input type="hidden" name="u_medida[]" value="${item.unit_code}">
        </td>
        <td class="border"><input type="number" name="cantidad[]" value="1" class="form-control cantidad-input" data-stock="${item.stock}" style="width:50px"></td>
        <td class="border">
            <span name="price_u[]">${parseFloat(valores.VentaU).toFixed(2)}</span>
            <input type="hidden" name="price_u[]" value="${valores.VentaU}">
        </td>
        <td class="border">
            <span name="tributo[]">${tributo}</span>
            <input type="hidden" name="tributo[]" value="${tributo}">
        </td>
        <td class="border">
            <span name="impuesto[]" class="impuesto-span">${valores.Impuesto}</span>
            <input type="hidden" name="impuesto[]" value="${valores.Impuesto}">
        </td>
        <td class="border"><input type="number" class="form-control price-input" name="price[]" value="${valores.Newprice}" step="0.01"></td>
        <td class="border">
            <span name="venta_t[]" class="venta_total">${valores.venta_total}</span>
            <input type="hidden" name="venta_t[]" value="${valores.venta_total}">
        </td>
        <td class="border">
            <span name="importe[]" class="importe">${valores.importe}</span>
            <input type="hidden" name="importe[]" value="${valores.importe}">
        </td>
    </tr>`;
}

function updateTotals() {
    let tv_gravado = 0;
    let tv_exonerado = 0;
    let tv_inafectas = 0;
    let tv_gratuitas = 0;
    let total_igv = 0;
    let total_importe = 0;

    $("#add_products tr").each(function () {
        const tributo = $(this).find('span[name="tributo[]"]').text();
        const importe = parseFloat($(this).find(".importe").text()) || 0;
        const impuesto = parseFloat($(this).find(".impuesto-span").text()) || 0;
        const ventaU = parseFloat($(this).find('input[name="price_u[]"]').val()) || 0;
        const cantidad = parseInt($(this).find(".cantidad-input").val()) || 0;

        switch (tributo) {
            case "IGV":
                tv_gravado += importe - impuesto;
                total_igv += impuesto;
                break;
            case "EXO":
                tv_exonerado += importe;
                break;
            case "INA":
                tv_inafectas += importe;
                break;
            case "GRA":
                tv_gratuitas += ventaU * cantidad;
                break;
        }

        if (tributo !== "GRA") {
            total_importe += importe;
        }
    });

    $("#totalg").text(tv_gravado.toFixed(2));
    $("#tv_gravado").val(tv_gravado.toFixed(2));

    $("#totale").text(tv_exonerado.toFixed(2));
    $("#tv_exonerado").val(tv_exonerado.toFixed(2));

    $("#totali").text(tv_inafectas.toFixed(2));
    $("#tv_inafectas").val(tv_inafectas.toFixed(2));

    $("#totalgt").text(tv_gratuitas.toFixed(2));
    $("#tv_gratuitas").val(tv_gratuitas.toFixed(2));

    $("#totaligv").text(total_igv.toFixed(2));
    $("#total_igv").val(total_igv.toFixed(2));

    $("#totalimp").text(total_importe.toFixed(2));
    $("#total_importe").val(total_importe.toFixed(2));
}

function updateValues($row) {
    const cantidad = parseInt($row.find(".cantidad-input").val()) || 0;
    const Newprice = parseFloat($row.find('input[name="price[]"]').val());
    const tributo = $row.find('span[name="tributo[]"]').text();

    const valores = calcularValores(Newprice, cantidad, tributo);

    $row.find('span[name="price_u[]"]').text(parseFloat(valores.VentaU).toFixed(2));
    $row.find('input[name="price_u[]"]').val(valores.VentaU);

    $row.find('span[name="venta_t[]"]').text(valores.venta_total);
    $row.find('input[name="venta_t[]"]').val(valores.venta_total);

    $row.find('span[name="importe[]"]').text(valores.importe);
    $row.find('input[name="importe[]"]').val(valores.importe);

    $row.find('span[name="impuesto[]"]').text(valores.Impuesto);
    $row.find('input[name="impuesto[]"]').val(valores.Impuesto);
    updateTotals();
}

$(document).on("input", ".cantidad-input", function () {
    const $input = $(this);
    const $row = $input.closest("tr");
    const stock = parseInt($input.data("stock"));
    let cantidad = parseInt($input.val()) || 0;

    if (cantidad > stock) {
        functions.toast_message(
            "error",
            "La cantidad ingresada excede el stock disponible.",
            "ERROR"
        );
        cantidad = stock;
        $input.val(stock);
    }

    updateValues($row);
});

$(document).on("input", ".cantidad-input, .price-input", function () {
    updateValues($(this).closest("tr"));
});

// Evento modificado para actualizar referencia al eliminar
$(document).on("click", ".btn-delete-product", function () {
    $(this).closest("tr").remove();
    updateTotals();
    updateReference();
});

document.addEventListener('DOMContentLoaded', function () {
    const btnCancelForm = document.getElementById('btn_cancel_form');
    if (btnCancelForm) {
        btnCancelForm.addEventListener('click', function () {
            window.location.href = 'Proforma/index.php';
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    let inputFecha = document.querySelector('input[name="date_issue"]');
    if (inputFecha) {
        let hoy = new Date();
        let fechaActual = hoy.toISOString().split("T")[0];
        inputFecha.value = fechaActual;
    } else {
        console.error("El elemento con nombre 'date_issue' no se encontró en el DOM.");
    }
});

// Inicialización final
get_coins();
get_igv();
get_business_name_cli();
get_voucher_type();
get_campus();
get_date();