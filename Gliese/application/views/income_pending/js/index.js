// ------------------- DESTROY DATATABLE -------------------
function destroy_datatable() {
    // --
    $("#datatable-income-products-pending").dataTable().fnDestroy();
    }
    
    // ------------------- REFRESH DATATABLE -------------------
function refresh_datatable() {
// --
$("#datatable-income-products-pending").DataTable().ajax.reload();
}

let cambiosPendientes = {};


// --- status: 'Active'---

function load_datatable() {
    destroy_datatable();

    console.log("Inicializando DataTable...");
    
    let dataTable = $("#datatable-income-products").DataTable({
        ajax: {
            url: BASE_URL + "Income_Pending/get_income_products_pending",
            cache: false,
            dataSrc: "data",
            error: function (xhr, error, thrown) {
                console.error("Error en la petición:", xhr.responseText);
                alert("Error al cargar datos. Ver consola para detalles.");
            }
        },
    
        columns: [
            { 
                data: "date_issue",
                render: function (data, type, row) {
                    return type === "display" ? data.split(" ")[0] : data;
                }
            },
            { data: "person_name" },
            { data: "voucher_type_description" },
            { data: "voucher_series" },
            { data: "payment_type_description" },
            { 
                data: "full_purchase",
                render: function (data) {
                    return "S/ " + parseFloat(data).toFixed(2);
                }
            },
            { 
                data: "status",
                render: function (data, type, row) {
                    let estado = cambiosPendientes[row.id] ?? data;
                    let badgeClass = estado == "1" ? "badge-light-warning" : "badge-light-success";
                    let badgeText = estado == "1" ? "Pendiente" : "Activo";
                    return `<span id="estado-${row.id}" class="badge rounded-pill ${badgeClass}">${badgeText}</span>`;
                }
            },
            {
                class: "center",
                render: function (data, type, row) {
                    let estado = cambiosPendientes[row.id] ?? row.status;
                    let checked = estado == "2" ? "checked" : "";  
                    let switchColor = estado == "2" ? "bg-success" : "bg-warning";
                    
                    return `
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="form-check form-switch">
                                <input class="form-check-input switch-status ${switchColor}" 
                                        type="checkbox" ${checked} 
                                        data-id="${row.id}"
                                        ${row.status == "2" ? "disabled" : ""}>
                            </div>
                            <button class="btn btn-sm btn-primary btn-details ms-2" 
                                    data-id="${row.id}">
                                ${feather.icons["eye"].toSvg({ class: "font-small-2" })}
                            </button>
                        </div>`;
                }
            }
        ],
        dom: functions.head_datatable(),
        buttons: [],
        language: { url: BASE_URL + "public/assets/json/languaje-es.json" },
    });

    dataTable.on("xhr", function () {
        let data = dataTable.ajax.json();
        console.log("Datos recibidos:", data);
    });


    // Evento para actualizar el estado cuando se cambia el switch
    $(document).on("change", ".switch-status", function() {
        const $switch = $(this);
        const id = $switch.data("id");
        const isChecked = $switch.prop("checked");
        
        $switch.prop("disabled", true);
        
        // Actualizar cambiosPendientes
        cambiosPendientes[id] = isChecked ? "2" : "1"; 

        // Actualizar visualmente
        const estadoBadge = $(`#estado-${id}`);
        if (isChecked) {
            estadoBadge.removeClass("badge-light-warning")
                        .addClass("badge-light-success")
                        .text("Activo");
            $switch.removeClass("bg-warning")
                    .addClass("bg-success");
        } else {
            estadoBadge.removeClass("badge-light-success")
                        .addClass("badge-light-warning")
                        .text("Pendiente");
            $switch.removeClass("bg-success")
                    .addClass("bg-warning");
        }
        
        $switch.prop("disabled", false);
    });

    $(document).on("click", "#register_active", function () {
        if (Object.keys(cambiosPendientes).length === 0) {
            alert("No hay cambios pendientes.");
            return;
        }
    });  
}

$(document).on("click", "#register_active", function () {
    const $button = $(this);

    if (!cambiosPendientes || Object.keys(cambiosPendientes).length === 0) {
        Swal.fire({
            icon: "warning",
            title: "Sin cambios",
            text: "No hay cambios pendientes para guardar."
        });
        return;
    }

    const idsParaActualizar = Object.entries(cambiosPendientes)
        .filter(([id, estado]) => estado === "2")
        .map(([id]) => parseInt(id));

    if (idsParaActualizar.length === 0) {
        Swal.fire({
            icon: "info",
            title: "Nada que activar",
            text: "No hay registros marcados como 'Activo' para guardar."
        });
        return;
    }

    Swal.fire({
        title: "¿Confirmar cambios?",
        text: `Se actualizarán ${idsParaActualizar.length} registros a estado "Activo"`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, guardar",
        cancelButtonText: "Cancelar",
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return $.ajax({
                url: BASE_URL + "Income_Pending/update_income_products_status",
                type: "POST",
                data: JSON.stringify({ registros: idsParaActualizar }),
                contentType: "application/json",
                dataType: "json",
                beforeSend: function () {
                    $button.prop("disabled", true).html(`
                        <span class="spinner-border spinner-border-sm" role="status"></span> Procesando...
                    `);
                }
            }).catch((xhr) => {
                console.error("Error AJAX:", {
                    status: xhr.status,
                    response: xhr.responseText,
                    statusText: xhr.statusText
                });

                Swal.showValidationMessage(
                    xhr.responseJSON?.msg || "Error en el servidor. Código: " + xhr.status
                );
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        $button.prop("disabled", false).text("Registrar Cambios");

        if (result.isConfirmed && result.value) {
            const response = result.value;

            if (response.status === "OK") {
                Swal.fire({
                    icon: "success",
                    title: "Éxito",
                    text: response.msg || "Estados actualizados correctamente."
                }).then(() => {
                    cambiosPendientes = {};
                    refresh_datatable();
                });
                
            } else if (response.status === "EXCEPTION") {
                Swal.fire({
                    icon: "error",
                    title: "Error en BD",
                    text: response.msg
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: response.msg || "Error al actualizar estados."
                });
            }
        }
    });
});




$("#datatable-income-products").on("click", ".btn-details", function () {

    var id_income = $(this).data("id");

    $.ajax({
        url: BASE_URL + "Income/get_income_details",
        type: "GET",
        data: { id: id_income },
        dataType: "json",
        success: function (response) {
            console.log(response); // Depuración: Verifica que response tiene datos

            if (response.status === "OK") {
                var data = response.result || response.data; // Asegura que estamos obteniendo los datos correctos
                console.log(data);

                // Llenar los inputs con los datos recibidos
                $("#income_person").val(data.person_name); // Nombre del proveedor
                $("#income_user").val(data.user_name || "N/A"); // Nombre del usuario
                $("#voucher_type_id").val(data.voucher_type_description); // Tipo de comprobante
                $("#payment_type_id").val(data.payment_type_description); // Tipo de pago
                $("#proof_series").val(data.proof_series); // Serie de comprobante
                $("#voucher_series").val(data.voucher_series); // Serie de comprobante
                $("#date_issue").val(data.date_issue); // Fecha de emisión
                $("#date_expiration").val(data.date_expiration || "N/A"); // Fecha de expiración (si existe)
                $("#igv").val(data.igv); // IGV
                $("#number_installments").val(data.number_installments || "N/A"); // Número de cuotas
                $("#value_installment").val(data.value_installment || "N/A"); // Valor de la cuota
            
                // Aquí asignamos directamente el valor de full_purchase recibido
                $("#full_purchase").val("S/ " + parseFloat(data.full_purchase).toFixed(2)); // Compra total

                // Limpiar la tabla de detalles de productos antes de agregar nuevos datos
                $("#incomeProductDetails").empty();

                if (data.products.length > 0) {
                    let total = 0;
                
                    data.products.forEach(product => {
                        console.log(product);
                        let cantidad = parseInt(product.quantity) || 0;
                        let precioVenta = parseFloat(product.unit_price) || 0;
                        let subtotal = cantidad * precioVenta;
                        total += subtotal;

                        let row = `
                            <tr>
                                <td class="text-center">${product.product_code}</td> <!-- Código Producto -->
                                <td class="text-center">${product.product_name}</td> <!-- Nombre Producto -->
                                <td class="text-center">${cantidad}</td> <!-- Cantidad -->
                                <td class="text-center">S/ ${precioVenta.toFixed(2)}</td> <!-- Precio Venta -->
                                <td class="text-center">S/ ${subtotal.toFixed(2)}</td> <!-- Subtotal -->
                            </tr>
                        `;
                        $("#incomeProductDetails").append(row);
                    });
                } else {
                    $("#incomeProductDetails").append(`
                        <tr>
                            <td colspan="7" class="text-center text-muted">No hay productos registrados.</td>
                        </tr>
                    `);
                }

                // Mostrar el modal
                $("#incomeProductModal").modal("show");
            } else {
                alert("No se encontraron detalles para este ingreso de productos.");
            }
        },
        error: function (xhr, status, error) {
            console.error("Error en la petición AJAX:", error);
            alert("Hubo un problema al obtener los detalles. Inténtalo de nuevo.");
        }
    });
});
load_datatable();