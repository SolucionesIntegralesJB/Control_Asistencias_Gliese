// Variables globales
let cotizeTable;

$(document).ready(function () {
  initCotizeTable();
});

function initCotizeTable() {
  try {
    if ($.fn.DataTable.isDataTable("#datatable-cotize")) {
      $("#datatable-cotize").DataTable().destroy();
    }

    cotizeTable = $("#datatable-cotize").DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: BASE_URL + "Cotize/get_cotize",
        type: "GET",
        dataSrc: function (json) {
          // Verificar si la respuesta es válida
          if (!json || typeof json !== "object") {
            console.error("Respuesta inválida:", json);
            toastr.error("Error: Respuesta del servidor inválida");
            return [];
          }

          // Verificar si la respuesta tiene la estructura esperada
          if (json.status === "OK" && Array.isArray(json.result)) {
            return json.result;
          } else if (Array.isArray(json)) {
            return json;
          } else {
            toastr.error(json.message || "Error al cargar los datos");
            return [];
          }
        },
        error: function (xhr, error, thrown) {
          console.error("Error en la solicitud AJAX:", error);
          toastr.error("Error al conectar con el servidor");
        },
      },
      columns: [
        {
          data: "date_issue",
          render: function (data) {
            if (!data) return "";
            const fecha = new Date(data);
            return fecha.toLocaleDateString("es-ES", {
              day: "2-digit",
              month: "2-digit",
              year: "numeric",
            });
          },
        },
        { data: "cotize_type" },
        { data: "client_name" },
        { data: "document_number" },
        { data: "number" },
        { data: "id" },
        {
          data: "total",
          render: function (data) {
            return "S/. " + parseFloat(data || 0).toFixed(2);
          },
        },
        {
          data: "status",
          render: function (data) {
            let badge =
              parseInt(data) === 1 ? "badge bg-success" : "badge bg-danger";
            let text = parseInt(data) === 1 ? "Activo" : "Anulado";
            return '<span class="' + badge + '">' + text + "</span>";
          },
        },
        {
          data: null,
          render: function (data) {
            const cotizeId = data.id || "";

            return `
                            <div class="btn-group">
                                <button type="button" class="btn btn-info btn-sm me-1" onclick="viewCotizacion('${cotizeId}')" title="Ver cotización">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm me-1" onclick="editCotizacion('${cotizeId}')" title="Editar cotización">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteCotizacion('${cotizeId}')" title="Anular cotización">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>`;
          },
          orderable: false,
        },
      ],
      order: [[0, "desc"]],
      language: {
        url: BASE_URL + "public/app-assets/js/scripts/tables/spanish.json",
      },
      responsive: true,
    });
  } catch (error) {
    console.error("Error al inicializar datatable:", error);
    toastr.error("Error al cargar la tabla de cotizaciones");
  }
}

function deleteCotizacion(cotizeId) {
  // Mostrar confirmación antes de anular
  Swal.fire({
    title: "¿Está seguro?",
    text: "¿Desea anular esta cotización? Esta acción no se puede revertir.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Sí, anular",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      // Realizar la solicitud AJAX para anular la cotización
      $.ajax({
        url: BASE_URL + "Cotize/cancel_cotize",
        type: "POST",
        data: {
          cotize_id: cotizeId,
        },
        dataType: "json",
        success: function (response) {
          if (response.status === "OK") {
            Swal.fire(
              "¡Anulada!",
              "La cotización ha sido anulada correctamente.",
              "success"
            );
            // Recargar la tabla para mostrar los cambios
            cotizeTable.ajax.reload();
          } else {
            Swal.fire(
              "Error",
              response.message || "No se pudo anular la cotización.",
              "error"
            );
          }
        },
        error: function () {
          Swal.fire(
            "Error",
            "Ocurrió un error al comunicarse con el servidor.",
            "error"
          );
        },
      });
    }
  });
}

// Función para ver detalles de la cotización
function viewCotizacion(id) {
  if (!id) {
    toastr.error("ID de cotización no válido");
    return;
  }

  // Mostrar indicador de carga
  Swal.fire({
    title: "Cargando cotización",
    text: "Por favor espere...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  // Cargar datos de la cotización mediante AJAX
  $.ajax({
    url: BASE_URL + "Cotize/get_quotation_details",
    type: "GET",
    data: { id: id },
    dataType: "json",
    success: function (response) {
      Swal.close();

      if (response.status === "OK" && response.result) {
        // Mostrar modal con los datos
        $("#cotize-view-modal .modal-title").text("Cotización #" + id);

        // Cargar datos en el modal
        loadCotizacionDataToViewModal(response.result);

        // Mostrar el modal
        $("#cotize-view-modal").modal("show");
      } else {
        toastr.error(
          response.message || "Error al cargar los datos de la cotización"
        );
      }
    },
    error: function (xhr, error, thrown) {
      Swal.close();
      console.error("Error en la solicitud AJAX:", error);
      toastr.error("Error al conectar con el servidor");
    },
  });
}

function editCotizacion(id) {
  if (!id) {
    toastr.error("ID de cotización no válido");
    return;
  }

  // Mostrar indicador de carga
  Swal.fire({
    title: "Cargando cotización",
    text: "Por favor espere...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  // Cargar datos de la cotización mediante AJAX
  $.ajax({
    url: BASE_URL + "Cotize/get_quotation_details",
    type: "GET",
    data: { id: id },
    dataType: "json",
    success: function (response) {
      Swal.close();

      if (response.status === "OK" && response.result) {
        // Mostrar modal con los datos
        $("#cotize-edit-modal .modal-title").text("Editar Cotización #" + id);

        // Cargar datos en el modal
        loadCotizacionDataToEditModal(response.result);

        // Mostrar el modal
        $("#cotize-edit-modal").modal("show");
      } else {
        toastr.error(
          response.message || "Error al cargar los datos de la cotización"
        );
      }
    },
    error: function (xhr, error, thrown) {
      Swal.close();
      console.error("Error en la solicitud AJAX:", error);
      toastr.error("Error al conectar con el servidor");
    },
  });
}

// Función para cargar datos en el modal de visualización
function loadCotizacionDataToViewModal(cotizeData) {
  if (!cotizeData) {
    console.error("Datos de cotización no válidos");
    return;
  }

  // Datos del cliente
  $("#view-client-name").text(cotizeData.client_name || "");
  $("#view-client-document").text(cotizeData.client_document_number || "");
  $("#view-client-address").text(cotizeData.client_address || "");

  // Datos generales de la cotización
  $("#view-reference").text(cotizeData.reference || "");
  $("#view-cotize-type").text(cotizeData.cotize_type || "");
  $("#view-offer-validity").text(cotizeData.offer_validity || "");
  $("#view-emission-date").text(formatDate(cotizeData.date_issue) || "");

  // Limpiar tabla de productos
  $("#view-products-table tbody").empty();

  // Agregar productos a la tabla
  if (cotizeData.details && Array.isArray(cotizeData.details)) {
    cotizeData.details.forEach(function (detail, index) {
      const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${detail.description || ""}</td>
                    <td>${parseFloat(detail.quantity || 0).toFixed(2)}</td>
                    <td>S/. ${parseFloat(detail.unit_price || 0).toFixed(
                      2
                    )}</td>
                    <td>S/. ${parseFloat(detail.subtotal || 0).toFixed(2)}</td>
                </tr>
            `;
      $("#view-products-table tbody").append(row);
    });
  }

  // Mostrar totales
  $("#view-subtotal").text(
    "S/. " + parseFloat(cotizeData.subtotal || 0).toFixed(2)
  );
  $("#view-igv").text("S/. " + parseFloat(cotizeData.igv || 0).toFixed(2));
  $("#view-total").text("S/. " + parseFloat(cotizeData.total || 0).toFixed(2));
}

// Función para cargar datos en el modal de edición
function loadCotizacionDataToEditModal(cotizeData) {
  // Establecer ID de cotización
  $("#edit-cotize-id").val(cotizeData.id || "");

  // Datos del cliente
  $("#edit-client").val(cotizeData.person_id).trigger("change");
  $("#edit-client-document").val(cotizeData.client_document_number || "");
  $("#edit-client-address").val(cotizeData.client_address || "");

  // Datos generales de la cotización
  $("#edit-reference").val(cotizeData.reference || "");
  $("#edit-cotize-type").val(cotizeData.cotize_type || "Regular");
  $("#edit-offer-validity").val(cotizeData.offer_validity || "15");

  // Limpiar tabla de productos
  $("#edit-products-table tbody").empty();

  // Agregar productos a la tabla
  if (cotizeData.details && Array.isArray(cotizeData.details)) {
    cotizeData.details.forEach(function (detail) {
      addProductToEditTable({
        id: detail.product_id || "",
        description: detail.description || "",
        quantity: detail.quantity || "1.00",
        unit_price: detail.unit_price || "0.00",
        subtotal: detail.subtotal || "0.00",
      });
    });
  }

  // Actualizar totales
  updateEditTotals();
}

// Función para formatear fecha
function formatDate(dateString) {
  if (!dateString) return "";
  const fecha = new Date(dateString);
  return fecha.toLocaleDateString("es-ES", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
}

// Función para agregar producto a la tabla de edición
function addProductToEditTable(product) {
  if (!product) {
    console.error("Datos de producto no válidos");
    return;
  }

  const row = `
        <tr>
            <td>
                <button type="button" class="btn btn-danger btn-sm" onclick="removeEditProduct(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
            <td>
                <input type="text" class="form-control" name="edit_product_description[]" value="${
                  product.description || ""
                }" required>
            </td>
            <td>
                <input type="number" class="form-control edit-product-quantity" name="edit_product_quantity[]" value="${
                  product.quantity || "1.00"
                }" min="0.01" step="0.01" required onchange="calculateEditSubtotal(this)" onkeyup="calculateEditSubtotal(this)">
            </td>
            <td>
                <input type="number" class="form-control edit-product-price" name="edit_product_price[]" value="${
                  product.unit_price || "0.00"
                }" min="0.01" step="0.01" required onchange="calculateEditSubtotal(this)" onkeyup="calculateEditSubtotal(this)">
            </td>
            <td>
                <input type="text" class="form-control edit-product-subtotal" name="edit_product_subtotal[]" value="${
                  product.subtotal || "0.00"
                }" readonly>
            </td>
        </tr>
    `;
  $("#edit-products-table tbody").append(row);
}

// Función para calcular subtotal en la tabla de edición
function calculateEditSubtotal(input) {
  const row = $(input).closest("tr");
  const quantity = parseFloat(row.find(".edit-product-quantity").val()) || 0;
  const price = parseFloat(row.find(".edit-product-price").val()) || 0;
  const subtotal = quantity * price;

  row.find(".edit-product-subtotal").val(subtotal.toFixed(2));

  // Actualizar totales
  updateEditTotals();
}

// Función para eliminar producto de la tabla de edición
function removeEditProduct(button) {
  $(button).closest("tr").remove();
  updateEditTotals();
}

// Función para actualizar totales en el modal de edición
function updateEditTotals() {
  let subtotal = 0;

  // Sumar subtotales de cada producto
  $("#edit-products-table tbody tr").each(function () {
    subtotal += parseFloat($(this).find(".edit-product-subtotal").val()) || 0;
  });

  // Calcular IGV (18%)
  const igv = subtotal * 0.18;
  const total = subtotal + igv;

  // Mostrar totales
  $("#edit-subtotal").text("S/. " + subtotal.toFixed(2));
  $("#edit-subtotal-input").val(subtotal.toFixed(2));

  $("#edit-igv").text("S/. " + igv.toFixed(2));
  $("#edit-igv-input").val(igv.toFixed(2));

  $("#edit-total").text("S/. " + total.toFixed(2));
  $("#edit-total-input").val(total.toFixed(2));
}

// Función para abrir modal de agregar producto
function openEditProductModal() {
  // Limpiar campos del modal
  $("#new-product-description").val("");
  $("#new-product-quantity").val("1.00");
  $("#new-product-price").val("0.00");

  // Mostrar modal
  $("#edit-product-modal").modal("show");
}

// Función para guardar cambios de la cotización
function updateCotizacion() {
  // Validar que haya al menos un producto
  if ($("#edit-products-table tbody tr").length === 0) {
    toastr.error("Debe agregar al menos un producto a la cotización");
    return;
  }

  // Recopilar datos de productos
  const products = [];
  $("#edit-products-table tbody tr").each(function () {
    products.push({
      description: $(this)
        .find('input[name="edit_product_description[]"]')
        .val(),
      quantity: $(this).find('input[name="edit_product_quantity[]"]').val(),
      unit_price: $(this).find('input[name="edit_product_price[]"]').val(),
      subtotal: $(this).find('input[name="edit_product_subtotal[]"]').val(),
    });
  });

  // Preparar datos para enviar
  const formData = {
    cotize_id: $("#edit-cotize-id").val(),
    business_name_cli: $("#edit-client").val(),
    referencia: $("#edit-reference").val(),
    pt_description: $("#edit-cotize-type").val(),
    validez_oferta: $("#edit-offer-validity").val(),
    subtotal: $("#edit-subtotal-input").val(),
    igv: $("#edit-igv-input").val(),
    total: $("#edit-total-input").val(),
    details: products,
  };

  // Mostrar indicador de carga
  Swal.fire({
    title: "Actualizando cotización",
    text: "Por favor espere...",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  // Enviar solicitud AJAX
  $.ajax({
    url: BASE_URL + "Cotize/update_quotation",
    type: "POST",
    data: {
      cotize_id: formData.cotize_id,
      business_name_cli: formData.business_name_cli,
      referencia: formData.referencia,
      pt_description: formData.pt_description,
      validez_oferta: formData.validez_oferta,
      subtotal: formData.subtotal,
      igv: formData.igv,
      total: formData.total,
      details: JSON.stringify(formData.details),
    },
    dataType: "json",
    success: function (response) {
      Swal.close();

      if (response.status === "OK") {
        // Cerrar modal
        $("#cotize-edit-modal").modal("hide");

        // Mostrar mensaje de éxito
        Swal.fire({
          icon: "success",
          title: "¡Éxito!",
          text: response.message || "Cotización actualizada correctamente",
        });

        // Recargar tabla de cotizaciones
        refresh_datatable();
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: response.message || "Error al actualizar la cotización",
        });
      }
    },
    error: function (xhr, error, thrown) {
      Swal.close();
      console.error("Error en la solicitud AJAX:", error);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Error al conectar con el servidor",
      });
    },
  });
}

// Función para recargar la tabla
function refresh_datatable() {
  if (cotizeTable) {
    cotizeTable.ajax.reload();
  }
}

// Función para cargar datos del cliente en el modal de edición
function cargarDatosClienteEdit(clientId) {
  if (!clientId) {
    $("#edit-client-document").val("");
    $("#edit-client-address").val("");
    return;
  }

  $.ajax({
    url: BASE_URL + "Clients/get_client_by_id",
    type: "GET",
    data: { id: clientId },
    dataType: "json",
    success: function (response) {
      if (response.status === "OK" && response.result) {
        $("#edit-client-document").val(response.result.document_number || "");
        $("#edit-client-address").val(response.result.address || "");
      } else {
        $("#edit-client-document").val("");
        $("#edit-client-address").val("");
        toastr.error("Error al cargar datos del cliente");
      }
    },
    error: function (xhr, error, thrown) {
      $("#edit-client-document").val("");
      $("#edit-client-address").val("");
      console.error("Error en la solicitud AJAX:", error);
      toastr.error("Error al conectar con el servidor");
    },
  });
}

// Función para agregar nuevo producto a la tabla de edición
function addNewProductToEdit() {
  const description = $("#new-product-description").val();
  const quantity = parseFloat($("#new-product-quantity").val()) || 0;
  const price = parseFloat($("#new-product-price").val()) || 0;

  if (!description) {
    toastr.error("Por favor ingrese una descripción para el producto");
    return;
  }

  if (quantity <= 0) {
    toastr.error("La cantidad debe ser mayor a cero");
    return;
  }

  if (price <= 0) {
    toastr.error("El precio debe ser mayor a cero");
    return;
  }

  const subtotal = quantity * price;

  // Agregar producto a la tabla
  addProductToEditTable({
    description: description,
    quantity: quantity.toFixed(2),
    unit_price: price.toFixed(2),
    subtotal: subtotal.toFixed(2),
  });

  // Actualizar totales
  updateEditTotals();

  // Cerrar modal
  $("#edit-product-modal").modal("hide");

  // Limpiar campos
  $("#new-product-description").val("");
  $("#new-product-quantity").val("1.00");
  $("#new-product-price").val("0.00");
}
