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
// function load_datatable() {
//     destroy_datatable();
  
//     $("#datatable-income-products").DataTable({
//       ajax: {
//         url: BASE_URL + "Shopping/get_Shopping",
//         cache: false,
//         dataSrc: "data"
//       },
//       order: [[0, "asc"]], // orden por N° Pedido
//       columns: [
//         {
//           data: null,
//           title: "N° Pedido",
//           render: function (data, type, row) {
//             return row.series + "-" + row.correlative;
//           },
//           sort: function (data, type, row) {
//             // Lo que se usa para ordenar
//             return parseInt(row.correlative); // Orden numérico correcto
//         }
//         },
//         {
//           data: "issue_date",
//           title: "Fecha",
//           render: function (data) {
//             return data ? String(data).split(" ")[0] : "";
//           }
//         },
//         {
//           data: "total_amount",
//           title: "Total",
//           render: function (data) {
//             return "S/ " + (data ? parseFloat(data).toFixed(2) : "0.00");
//           }
//         },
//         {
//           data: "estado_pago",
//           title: "Estado",
//           render: function (data) {
//             if (data === "Pagado")      return `<span class="badge rounded-pill badge-light-warning">Pagado</span>`;
//             if (data === "Confirmado")  return `<span class="badge rounded-pill badge-light-success">Confirmado</span>`;
//             if (data === "Anulado")     return `<span class="badge rounded-pill badge-light-danger">Anulado</span>`;
//             return `<span class="badge rounded-pill badge-light-secondary">${data ?? ""}</span>`;
//           }
//         },
//         { data: "due_date", title: "Fecha Límite" },
//         { data: "client_name", title: "Cliente" },
//         {
//           title: "Acciones",
//           className: "text-center",
//           orderable: false,
//           render: function (data, type, row) {
//             const btnDetalles = `
//               <button class="btn btn-sm btn-primary btn_details" data-id="${row.id}" title="Ver Detalles">
//                 ${feather.icons["eye"].toSvg({ class: "font-small-2" })}
//               </button>`;
  
//             const btnConfirmar = `
//               <button class="btn btn-sm btn-success btn_confirm ${row.estado_pago === "Pagado" ? "" : "disabled"}"
//                       data-id="${row.id}" 
//                       title="Confirmar"
//                       ${row.estado_pago === "Pagado" ? "" : "disabled"}>
//                 ${feather.icons["check-circle"].toSvg({ class: "font-small-2" })}
//               </button>`;
  
//             // Anular: habilitado solo si no está Anulado
//             const btnAnular = `
//               <button class="btn btn-sm btn-danger btn-anular ${row.estado_pago !== "Anulado" ? "" : "disabled"}"
//                       data-id="${row.id}" 
//                       title="Anular"
//                       ${row.estado_pago !== "Anulado" ? "" : "disabled"}>
//                 ${feather.icons["x-circle"].toSvg({ class: "font-small-2" })}
//               </button>`;
  
//             return btnDetalles + " " + btnConfirmar + " " + btnAnular;
//           }
//         }
//       ],
//       dom: functions.head_datatable(),
//       buttons: functions.custom_buttons_datatable([6], "#create_income_products_modal"),
//       language: { url: BASE_URL + "public/assets/json/languaje-es.json" }
//     });
// }
function load_datatable() {
  destroy_datatable();

  $("#datatable-income-products").DataTable({
    ajax: {
      url: BASE_URL + "Shopping/get_Shopping",
      cache: false,
      dataSrc: "data"
    },
    order: [[0, "desc"]], // Orden inicial: N° Pedido DESC (numérico)
    columns: [
      {
        data: null,
        title: "N° Pedido",
        render: function (data, type, row) {
          // Para ordenar numéricamente usamos correlative como número
          if (type === "sort" || type === "type") {
            return parseInt(row.correlative);
          }
          // Para mostrar en la tabla
          return row.series + "-" + row.correlative;
        }
      },
      {
        data: "issue_date",
        title: "Fecha",
        render: function (data) {
          return data ? String(data).split(" ")[0] : "";
        }
      },
      {
        data: "total_amount",
        title: "Total",
        render: function (data) {
          return "S/ " + (data ? parseFloat(data).toFixed(2) : "0.00");
        }
      },
      {
        data: "estado_pago",
        title: "Estado",
        render: function (data) {
          if (data === "Pagado")      return `<span class="badge rounded-pill badge-light-warning">Pagado</span>`;
          if (data === "Confirmado")  return `<span class="badge rounded-pill badge-light-success">Confirmado</span>`;
          if (data === "Anulado")     return `<span class="badge rounded-pill badge-light-danger">Anulado</span>`;
          return `<span class="badge rounded-pill badge-light-secondary">${data ?? ""}</span>`;
        }
      },
      { data: "due_date", title: "Fecha Límite" },
      { data: "client_name", title: "Cliente" },
      {
        title: "Acciones",
        className: "text-center",
        orderable: false,
        render: function (data, type, row) {
          const btnDetalles = `
            <button class="btn btn-sm btn-primary btn_details" data-id="${row.id}" title="Ver Detalles">
              ${feather.icons["eye"].toSvg({ class: "font-small-2" })}
            </button>`;

          const btnConfirmar = `
            <button class="btn btn-sm btn-success btn_confirm ${row.estado_pago === "Pagado" ? "" : "disabled"}"
                    data-id="${row.id}" 
                    title="Confirmar"
                    ${row.estado_pago === "Pagado" ? "" : "disabled"}>
              ${feather.icons["check-circle"].toSvg({ class: "font-small-2" })}
            </button>`;

          const btnAnular = `
            <button class="btn btn-sm btn-danger btn-anular ${row.estado_pago !== "Anulado" ? "" : "disabled"}"
                    data-id="${row.id}" 
                    title="Anular"
                    ${row.estado_pago !== "Anulado" ? "" : "disabled"}>
              ${feather.icons["x-circle"].toSvg({ class: "font-small-2" })}
            </button>`;

          return btnDetalles + " " + btnConfirmar + " " + btnAnular;
        }
      }
    ],
    dom: functions.head_datatable(),
    buttons: functions.custom_buttons_datatable([6], "#create_income_products_modal"),
    language: { url: BASE_URL + "public/assets/json/languaje-es.json" }
  });
}

  // ------------------- DETALLLES -------------------

//-------------------------------------------------------------

$(document).on('click', '.btn_details', function() {
  const orderId = $(this).data('id');
  loadOrderDetails(orderId);
});
  // ------------------- DETALLES -------------------
function loadOrderDetails(orderId) {
  // Mensaje de carga
  $('#incomeProductDetails').html('<tr><td colspan="5" class="text-center">Cargando...</td></tr>');
  $('#total_order').text('S/ 0.00');

  $.ajax({
      url: BASE_URL + "Shopping/getOrderDetails",
      type: "POST",
      data: { order_id: orderId, action: 'get_order_details' },
      dataType: "json",
      success: function(res) {
          if (res.success) {
              const order = res.order; // objeto con todos los campos
              const details = res.details || [];

              // Información general
              $('#income_person').val(order.client_name ?? '');
              $('#income_user').val(order.user_name ?? '');
              $('#voucher_type').val(order.voucher_name ?? '');
              $('#proof_series').val(order.series + '-' + order.correlative);
              $('#estado_entrega').val(order.estado_entrega ?? '');
              $('#estado_pago').val(order.estado_pago ?? '');
              $('#payment_shape').val(order.payment_shape ?? '');
              $('#coin_name').val(order.coin_name ?? '');
              $('#transfer_reference').val(order.transfer_reference ?? '');
              $('#transfer_date').val(order.transfer_date ?? '');
              $('#notes').val(order.notes ?? '');

              // Transfer proof como enlace
         
              if (order.transfer_proof) {
                $('#transfer_proof').html(
                    `<a href="/carrito/${order.transfer_proof}" target="_blank">Ver comprobante</a>`
                );
            } else {
                $('#transfer_proof').html('No hay comprobante');
            }
            

              // Tabla de productos
              let rows = '';
              let total = 0;

              if (details.length) {
                  details.forEach(d => {
                      rows += `<tr>
                                  <td class="text-center">${d.product_id}</td>
                                  <td class="text-center">${d.product_name}</td>
                                  <td class="text-center">${d.quantity}</td>
                                  <td class="text-center">S/ ${parseFloat(d.item_unit_price).toFixed(2)}</td>
                                  <td class="text-center">S/ ${parseFloat(d.subtotal).toFixed(2)}</td>
                               </tr>`;
                      total += parseFloat(d.subtotal);
                  });
              } else {
                  rows = '<tr><td colspan="5" class="text-center">No hay productos</td></tr>';
              }

              $('#incomeProductDetails').html(rows);
              $('#total_order').text('S/ ' + total.toFixed(2));

              // Abrir modal
              $('#incomeProductModal').modal('show');

          } else {
              Swal.fire("Error", res.message, "error");
          }
      },
      error: function(err) {
          console.error(err);
          Swal.fire("Error", "No se pudieron cargar los detalles.", "error");
      }
  });
}



  // ------------------- CONFIRMAR -------------------
  $(document).on("click", ".btn_confirm", function () {
    if ($(this).hasClass("disabled")) return; // evita acción si está deshabilitado
  
    const id = $(this).data("id");
  
    Swal.fire({
      title: "¿Confirmar pedido?",
      text: `Vas a confirmar el pedido #${id}`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#d33",
      confirmButtonText: "Sí, confirmar"
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + "Shopping/confirmarPedido",
          type: "POST",
          data: { id },
          dataType: "json",
          success: function (response) {
            if (response.success) {
              Swal.fire("Confirmado", response.message, "success");
              refresh_datatable();
            } else {
              Swal.fire("Error", response.message || "No se pudo confirmar", "error");
            }
          },
          error: function () {
            Swal.fire("Error", "Hubo un problema con la petición AJAX.", "error");
          }
        });
      }
    });
  });
  
  // ------------------- ANULAR -------------------
  $(document).on("click", ".btn-anular", function () {
    if ($(this).hasClass("disabled")) return; // evita acción si está deshabilitado
  
    const id = $(this).data("id");
  
    Swal.fire({
      title: "¿Anular pedido?",
      text: `Vas a anular el pedido #${id}`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#dc3545",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Sí, anular"
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: BASE_URL + "Shopping/anularPedido",
          type: "POST",
          data: { id },
          dataType: "json",
          success: function (response) {
            if (response.success) {
              Swal.fire("Anulado", response.message, "success");
              refresh_datatable();
            } else {
              Swal.fire("Error", response.message || "No se pudo anular", "error");
            }
          },
          error: function () {
            Swal.fire("Error", "Hubo un problema con la petición AJAX.", "error");
          }
        });
      }
    });
  });


// function showWarningAlert(warning) {
//     Swal.mixin({
//         toast: true,
//         position: 'bottom-end',
//         showConfirmButton: true,
//         showCancelButton: true,
//         confirmButtonText: 'Ver pendientes',
//         cancelButtonText: 'Cerrar',
//         timer: 15000,
//         timerProgressBar: true,
//         customClass: {
//             confirmButton: 'btn btn-warning btn-sm',
//             cancelButton: 'btn btn-outline-secondary btn-sm ms-1',
//             container: 'p-20'
//         },
//         buttonsStyling: false
//     }).fire({
//         icon: 'warning',
//         title: '<i class="fas fa-exclamation-triangle"></i> Registros Pendientes!',
//         html: `
//             <div class="text-justify" style="max-width: 300px">
//                 <p class="mb-2">${warning.message}</p>
//                 <hr class="my-2">
//                 <p class="text-muted small mb-0">
//                     <i class="fas fa-info-circle me-1"></i>
//                     Por favor: Tenga en cuenta que los registros pendientes que no sean aceptados dentro del plazo establecido serán rechazados.
//                 </p>
//             </div>
//         `
//     }).then((result) => {
//         if (result.isConfirmed) {
//             window.location.assign(BASE_URL + 'Income_Pending');
//         }
//     });
// }
// // ------------------- CREATE -------------------
// $(document).on('click', '.create-new', function() {
//     window.location.assign(BASE_URL + 'Income_Details');
// })

// // ------------------- DETAILS -------------------
// // ------------------- DETAILS -------------------
// $("#datatable-income-products").on("click", ".btn_details", function () {
//     var id_income = $(this).data("process-key");

//     $.ajax({
//         url: BASE_URL + "Income/get_income_details",
//         type: "GET",
//         data: { id: id_income },
//         dataType: "json",
//         success: function (response) {
//             console.log(response); // Depuración: Verifica que response tiene datos

//             if (response.status === "OK") {
//                 var data = response.result || response.data; // Asegura que estamos obteniendo los datos correctos
//                 console.log(data);

//                 // Llenar los inputs con los datos recibidos
//                 $("#income_person").val(data.person_name); // Nombre del proveedor
//                 $("#income_user").val(data.user_name || "N/A"); // Nombre del usuario
//                 $("#voucher_type_id").val(data.voucher_type_description); // Tipo de comprobante
//                 $("#payment_type_id").val(data.payment_type_description); // Tipo de pago
//                 $("#proof_series").val(data.proof_series); // Serie de comprobante
//                 $("#voucher_series").val(data.voucher_series); // Serie de comprobante
//                 $("#date_issue").val(data.date_issue); // Fecha de emisión
//                 $("#date_expiration").val(data.date_expiration || "N/A"); // Fecha de expiración (si existe)
//                 $("#igv").val(data.igv); // IGV
//                 $("#number_installments").val(data.number_installments || "N/A"); // Número de cuotas
//                 $("#value_installment").val(data.value_installment || "N/A"); // Valor de la cuota
             
//                 // Aquí asignamos directamente el valor de full_purchase recibido
//                 $("#full_purchase").val("S/ " + parseFloat(data.full_purchase).toFixed(2)); // Compra total

//                 // Limpiar la tabla de detalles de productos antes de agregar nuevos datos
//                 $("#incomeProductDetails").empty();

//                 if (data.products.length > 0) {
//                     let total = 0;
                
//                     data.products.forEach(product => {
//                         console.log(product);
//                         let cantidad = parseInt(product.quantity) || 0;
//                         let precioVenta = parseFloat(product.unit_price) || 0;
//                         let subtotal = cantidad * precioVenta;
//                         total += subtotal;

//                         let row = `
//                             <tr>
//                                 <td class="text-center">${product.product_code}</td> <!-- Código Producto -->
//                                 <td class="text-center">${product.product_name}</td> <!-- Nombre Producto -->
//                                 <td class="text-center">${cantidad}</td> <!-- Cantidad -->
//                                 <td class="text-center">S/ ${precioVenta.toFixed(2)}</td> <!-- Precio Venta -->
//                                 <td class="text-center">S/ ${subtotal.toFixed(2)}</td> <!-- Subtotal -->
//                             </tr>
//                         `;
//                         $("#incomeProductDetails").append(row);
//                     });
//                 } else {
//                     $("#incomeProductDetails").append(`
//                         <tr>
//                             <td colspan="7" class="text-center text-muted">No hay productos registrados.</td>
//                         </tr>
//                     `);
//                 }

//                 // Mostrar el modal
//                 $("#incomeProductModal").modal("show");
//             } else {
//                 alert("No se encontraron detalles para este ingreso de productos.");
//             }
//         },
//         error: function (xhr, status, error) {
//             console.error("Error en la petición AJAX:", error);
//             alert("Hubo un problema al obtener los detalles. Inténtalo de nuevo.");
//         }
//     });
// });

// // ------------------- DELETE -------------------
// $("#datatable-income-products").on("click", ".btn_delete_custom", function () {
//     var id_income = $(this).data("process-key");
//     if (id_income) {
//         Swal.fire({
//             title: "¿Estás seguro de eliminar este ingreso?",
//             text: "Esta acción no se puede deshacer.",
//             icon: "error",
//             showCancelButton: true,
//             confirmButtonText: "Sí, eliminar",
//             cancelButtonText: "Cancelar",
//         }).then((result) => {
//             if (result.isConfirmed) {
//                 $.ajax({
//                     url: BASE_URL + "Income/delete_income",
//                     type: "POST",
//                     data: { id_income: id_income },  
//                     success: function (response) {
//                         if (response.status === "OK") {
//                             Swal.fire({
//                                 title: "¡Eliminado!",
//                                 text: "El registro ha sido eliminado.",
//                                 icon: "success"
//                             }).then(() => {
//                                 // Recargar el DataTable sin recargar la página
//                                 location.reload();// Solo recarga los datos del DataTable
//                             });
//                         } else {
//                             Swal.fire("Error", "Hubo un problema al eliminar.", "error");
//                         }
//                     },
//                     error: function () {
//                         Swal.fire("Error", "No se pudo completar la solicitud.", "error");
//                     },
//                 });
//             }
//         });
//     } else {
//         Swal.fire("Error", "ID no encontrado", "error");
//     }
// });

    load_datatable();