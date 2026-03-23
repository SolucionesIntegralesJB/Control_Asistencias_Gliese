// ------------------- DESTROY DATATABLE -------------------
function destroy_datatable() {
    // --
    $("#datatable-income-products").dataTable().fnDestroy();
  }
  
  // ------------------- REFRESH DATATABLE -------------------
  function refresh_datatable() {
    // --
    $("#datatable-income-products").DataTable().ajax.reload();
  }
  
// ------------------- LISTADO DATATABLE -------------------
function load_datatable() {
    destroy_datatable();

    console.log("Inicializando DataTable...");
    
    let dataTable = $("#datatable-income-products").DataTable({
        ajax: {
            url: BASE_URL + "Income/get_income",
            cache: false,
            dataSrc: function (json) {
                // Mostrar notificación si hay pendientes
                if (json.warning && json.warning.show) {
                    showWarningAlert(json.warning);
                }
                return json.data;
            },
            error: function (xhr, error, thrown) {
                console.error("Error en la petición AJAX:", xhr.responseText);
            }
        },
        order: [[0, "desc"]], // Ordenar por fecha y hora (columna oculta)
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
            { data: "full_purchase" },
            { data: "status" ,
            
                title: "Estado",
                class: "center",
                render: function (data, type, row) {
                    if (row.status == "1") {
                    return `<div class="d-inline-flex align-items-center">
                                <span class="badge rounded-pill badge-light-warning">Pendiente</span>
                                <span 
                                style="display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border: 1px solid #fff; border-radius: 4px; cursor: pointer; background-color:"#fff"; margin-left: 5px;"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="Registrado en el sistema"
                                onclick="window.location.assign(BASE_URL + 'Income_Pending')">
                                ${feather.icons["edit"].toSvg({ width: 14, height: 14, class: "text-warning" })}
                                </span>
                            </div>`;
                    } else if (row.status == "2") {
                    return `<div class="d-inline-flex align-items-center">
                                <span class="badge rounded-pill badge-light-success">Aceptado</span>
                            </div>`;
                    } else if (row.status == "3") {
                    return `<div class="d-inline-flex align-items-center">
                                <span class="badge rounded-pill badge-light-danger">Rechazado</span>
                            </div>`;
                    }
                }
            },
            {
                class: "center",
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary btn_details" data-process-key="${row.id}">
                            ${feather.icons["eye"].toSvg({ class: "font-small-2" })}
                        </button>
                        <button class="btn btn-sm btn-danger btn_delete_custom" data-process-key="${row.id}">
                            ${feather.icons["trash-2"].toSvg({ class: "font-small-2" })}
                        </button>`;
                }
            }
        ],
        dom: functions.head_datatable(),
        buttons: functions.custom_buttons_datatable([7], "#create_income_products_modal"),
        language: { url: BASE_URL + "public/assets/json/languaje-es.json" },
    });

    dataTable.on("xhr", function () {
        let data = dataTable.ajax.json();
        console.log("Datos recibidos:", data);
    });
}
function showWarningAlert(warning) {
    Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'Ver pendientes',
        cancelButtonText: 'Cerrar',
        timer: 15000,
        timerProgressBar: true,
        customClass: {
            confirmButton: 'btn btn-warning btn-sm',
            cancelButton: 'btn btn-outline-secondary btn-sm ms-1',
            container: 'p-20'
        },
        buttonsStyling: false
    }).fire({
        icon: 'warning',
        title: '<i class="fas fa-exclamation-triangle"></i> Registros Pendientes!',
        html: `
            <div class="text-justify" style="max-width: 300px">
                <p class="mb-2">${warning.message}</p>
                <hr class="my-2">
                <p class="text-muted small mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Por favor: Tenga en cuenta que los registros pendientes que no sean aceptados dentro del plazo establecido serán rechazados.
                </p>
            </div>
        `
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.assign(BASE_URL + 'Income_Pending');
        }
    });
}
// ------------------- CREATE -------------------
$(document).on('click', '.create-new', function() {
    window.location.assign(BASE_URL + 'Income_Details');
})

// ------------------- DETAILS -------------------
// ------------------- DETAILS -------------------
$("#datatable-income-products").on("click", ".btn_details", function () {
    var id_income = $(this).data("process-key");

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

// ------------------- DELETE -------------------
$("#datatable-income-products").on("click", ".btn_delete_custom", function () {
    var id_income = $(this).data("process-key");
    if (id_income) {
        Swal.fire({
            title: "¿Estás seguro de eliminar este ingreso?",
            text: "Esta acción no se puede deshacer.",
            icon: "error",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: BASE_URL + "Income/delete_income",
                    type: "POST",
                    data: { id_income: id_income },  
                    success: function (response) {
                        if (response.status === "OK") {
                            Swal.fire({
                                title: "¡Eliminado!",
                                text: "El registro ha sido eliminado.",
                                icon: "success"
                            }).then(() => {
                                // Recargar el DataTable sin recargar la página
                                location.reload();// Solo recarga los datos del DataTable
                            });
                        } else {
                            Swal.fire("Error", "Hubo un problema al eliminar.", "error");
                        }
                    },
                    error: function () {
                        Swal.fire("Error", "No se pudo completar la solicitud.", "error");
                    },
                });
            }
        });
    } else {
        Swal.fire("Error", "ID no encontrado", "error");
    }
});

    load_datatable();