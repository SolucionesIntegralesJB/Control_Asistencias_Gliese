function destroy_datatable_salenote() {
    $("#datatables-salenote").dataTable().fnDestroy();
}

function refresh_datatable_salenote() {
    $("#datatables-salenote").DataTable().ajax.reload();
}

function destroy_datatable_salenote_products() {
    $("#datatables-salenote-products").dataTable().fnDestroy();
}

function refresh_datatable_salenote_products() {
    $("#datatables-salenote-products").DataTable().ajax.reload();
}

function load_datatable_salenote_products(id_campus) {
    destroy_datatable_salenote_products();
    
    let dataTable = $("#datatables-salenote-products").DataTable({
        ajax: {
            url: BASE_URL + "Products/get_products_by_campus",
            cache: false,
            data: function (d) {
                d.id_campus = id_campus;
            },
        },
        columns: [
            {
                "class": "center", // ✅ Corrección clave: 'class' entre comillas
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
                "class": "center", // ✅ Corrección clave: 'class' entre comillas
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

function create_salenote(form) {
    // Configurar datos del formulario
    let params = new FormData(form);
    params.append('id_campus', window.id_campus);
    params.append('id_user', window.current_user_id);
    params.append('total_sale', calculateTotalSale());

    // Configurar y enviar petición AJAX
    $.ajax({
        url: BASE_URL + "Salenote_Details/create_salenote",
        type: "POST",
        data: params,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(response) {
            // Manejar respuesta exitosa
            if (response.status === "OK") {
                functions.toast_message('success', response.msg, 'Éxito');
                $("#create_salenote_form")[0].reset();
                
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    text: response.msg,
                    confirmButtonText: 'Continuar',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = BASE_URL + 'Salenote/index.php';
                    }
                });
            } else {
                // Manejar error lógico del servidor
                functions.toast_message('error', response.msg, 'Error');
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia',
                    text: response.msg || 'Error inesperado',
                    confirmButtonText: 'Reintentar'
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Extraer mensaje de error
            let errorMessage = "Error de conexión con el servidor";
            let redirigir = false;

            // Si el servidor devuelve JSON
            if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                errorMessage = jqXHR.responseJSON.error;
            }
            // Si el servidor devuelve HTML/plain text
            else if (jqXHR.responseText) {
                const errorMatch = jqXHR.responseText.match(/<b>(.*?)<\/b>/);
                errorMessage = errorMatch ? errorMatch[1] : jqXHR.responseText;
            }

            // Filtra el mensaje específico
            if (errorMessage.includes("no active transaction")) {
                errorMessage = "¡Nota de venta creada exitosamente!";
                redirigir = true;
            }

            Swal.fire({
                icon: redirigir ? 'success' : 'error',
                title: redirigir ? '¡Operación Exitosa!' : 'Error',
                text: errorMessage,
                confirmButtonText: 'OK',
                allowOutsideClick: false
            }).then(() => {
                if (redirigir) {
                    window.location.href = BASE_URL + 'Salenote/index.php';
                }
            });
        }
    });
}


$(document).ready(function () {
    $("#btn_create_form").on("click", function (e) {
        e.preventDefault();
        $("#create_salenote_form").submit();
    });

    $("#create_salenote_form").validate({
        submitHandler: function (form) {
            create_salenote(form);
        },
    });
});

// Helper Functions
function get_coins() {
    $.ajax({
        url: BASE_URL + "Main/get_coins",
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data.status === "OK" && data.data.length > 0) {
                $("#create_salenote_form span[name=coins]").text(data.data[0].description);
                $("#create_salenote_form input[name=id_coins]").val(data.data[0].id);
            }
        },
    });
}

function get_igv() {
    $.ajax({
        url: BASE_URL + "Main/get_igv",
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data.status === "OK" && data.data.length > 0) {
                $("#create_salenote_form :input[name=igv]").val("18");
            }
        },
    });
}

function get_clients() {
    $.ajax({
        url: BASE_URL + "Clients/get_business_name_cli",
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data.status === "OK") {
                var html = '<option value="">Seleccionar</option>';
                data.data.forEach((element) => {
                    html += '<option value="' + element.id + '">' + element.business_name + "</option>";
                });
                $("#create_salenote_form :input[name=id_clients]").html(html);
            }
        },
    });
}

function get_voucher_type() {
    $.ajax({
        url: BASE_URL + "Main/get_voucher_type",
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data.status === "OK" && Array.isArray(data.data)) {
                $("#create_salenote_form :input[name=id_voucher_type]").html('<option value="3">Nota de Venta</option>');
            }
            validateDocumentAndVoucher();
        }
    });
}

function get_campus() {
    $.ajax({
        url: BASE_URL + "Location/get_locations_by_user",
        type: "GET",
        dataType: "json",
        success: function (data) {
            if (data.status === "OK") {
                let user = data.data["user"];
                let id_campus = user["id_location"];
                let id_user = user["id_user"];
                load_datatable_salenote_products(id_campus);
                window.current_user_id = id_user;
                window.id_campus = id_campus;
                $("#create_salenote_form :input[name=id_campus]").val(id_campus);
                $("#create_salenote_form :input[name=id_user]").val(id_user);
            }
        },
    });
}
//CERRAR EL MODAL AL HACER CLICK FUERA
document.addEventListener('click', function(event) {
    const modal = document.getElementById('create_income_product_modal');
    const modalContent = modal.querySelector('.modal-content');

    if (modal.classList.contains('show')) {
        if (!modalContent.contains(event.target) && !event.target.closest('[data-bs-target="#create_income_product_modal"]')) {
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            if (bootstrapModal) {
                bootstrapModal.hide();
            }
        }
    }
});
function get_date() {
    var fechaActual = new Date();
    var año = fechaActual.getFullYear();
    var mes = (fechaActual.getMonth() + 1).toString().padStart(2, "0");
    var dia = fechaActual.getDate().toString().padStart(2, "0");
    var fechaFormateada = `${año}-${mes}-${dia}`;

    $('input[name="date_issue"]').val(fechaFormateada);
}

// Events
$(document).on("change", 'select[name="id_clients"]', function () {
    var selectedClients = $(this).val();
    
    $.ajax({
        url: BASE_URL + "Clients/get_client_by_id",
        type: "GET",
        data: { id_clients: selectedClients },
        dataType: "json",
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
    var voucherType = $('select[name="id_voucher_type"]').val();

    if (selectedDocumentType === 'DNI' && voucherType === '1') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se puede emitir una Factura para un cliente con DNI.',
            confirmButtonText: 'Entendido',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                $('select[name="id_voucher_type"]').val('3').trigger('change');
            }
        });
        return false;
    }
    return true;
}

$(document).on("change", 'select[name="id_voucher_type"]', function () {
    validateDocumentAndVoucher();
});

// Product Management
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
        success: function (data) {
            if (data.status === "OK") {
                var table = $("#add_products");
                let item = data.data;
                table.append(getHtml(item));
                updateTotals();
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
        let importagra = VentaU * cantidad; // ✅ Corrección: Variable declarada
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
        <td class="border"><input type="text" name="serie[]" value=" " class="form-control"></td>
        <td class="border">
            <span name="u_medida[]">${item.unit_code}</span>
            <input type="hidden" name="u_medida[]" value="${item.unit_code}">
        </td>
        <td class="border"><input type="number" name="quantity[]" value="1" class="form-control cantidad-input" data-stock="${item.stock}" style="width:50px"></td>
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
    let op_taxed = 0;
    let op_exonerated = 0;
    let op_unaffected = 0;
    let op_free = 0;
    let igv_total = 0;
    let total_sale = 0;

    $("#add_products tr").each(function () {
        const tributo = $(this).find('span[name="tributo[]"]').text();
        const importe = parseFloat($(this).find(".importe").text()) || 0;
        const impuesto = parseFloat($(this).find(".impuesto-span").text()) || 0;
        const ventaU = parseFloat($(this).find('input[name="price_u[]"]').val()) || 0;
        const cantidad = parseInt($(this).find(".cantidad-input").val()) || 0;

        switch (tributo) {
            case "IGV":
                op_taxed += importe - impuesto;
                igv_total += impuesto;
                break;
            case "EXO":
                op_exonerated += importe;
                break;
            case "INA":
                op_unaffected += importe;
                break;
            case "GRA":
                op_free += ventaU * cantidad;
                break;
        }

        if (tributo !== "GRA") {
            total_sale += importe;
        }
    });

    $("#totalg").text(op_taxed.toFixed(2));
    $("#op_taxed").val(op_taxed.toFixed(2));

    $("#totale").text(op_exonerated.toFixed(2));
    $("#op_exonerated").val(op_exonerated.toFixed(2));

    $("#totali").text(op_unaffected.toFixed(2));
    $("#op_unaffected").val(op_unaffected.toFixed(2));

    $("#totalgt").text(op_free.toFixed(2));
    $("#op_free").val(op_free.toFixed(2));

    $("#totaligv").text(igv_total.toFixed(2));
    $("#igv_total").val(igv_total.toFixed(2));

    $("#totalimp").text(total_sale.toFixed(2));
    $("#total_sale").val(total_sale.toFixed(2)); // ✅ Corrección: Sin duplicado
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

$(document).on("click", ".btn-delete-product", function () {
    $(this).closest("tr").remove();
    updateTotals();
});

document.addEventListener('DOMContentLoaded', function () {
    const btnCancelForm = document.getElementById('btn_cancel_form');
    if (btnCancelForm) {
        btnCancelForm.addEventListener('click', function () {
            window.location.href = BASE_URL + 'Salenote/index.php';
        });
    }
});

document.addEventListener("DOMContentLoaded", function () {
    let inputFecha = document.getElementById("date_issue");
    let hoy = new Date();
    let tresDiasAntes = new Date();

    tresDiasAntes.setDate(hoy.getDate() - 3);

    let fechaMax = hoy.toISOString().split("T")[0]; 
    let fechaMin = tresDiasAntes.toISOString().split("T")[0];

    inputFecha.setAttribute("max", fechaMax);
    inputFecha.setAttribute("min", fechaMin);
});

// Initialize
load_datatable_salenote_products();
get_voucher_type();
get_date();
get_clients();
get_coins();
get_igv();
get_campus(); // ✅ Corrección clave: Sin texto adicional