// Variables globales
let igvPorcentaje = 0.18; // 18% por defecto

// Inicialización cuando el documento está listo
$(document).ready(function () {
  // Establecer fecha actual en el campo de fecha
  let today = new Date().toISOString().split("T")[0];
  $('input[name="fecha_emision"]').val(today);

  // Inicializar Select2 para mejorar los selectores
  if ($.fn.select2) {
    $(".select2").select2({
      placeholder: "Seleccione una opción",
      allowClear: true,
    });
  }

  // Configurar validación del formulario
  $("#create_income_details_form").on("submit", function (e) {
    e.preventDefault();

    const submitButton = $(this).find('button[type="submit"]');
    if (submitButton.prop("disabled")) {
      return false;
    }
    submitButton.prop("disabled", true);

    // Validar que haya al menos un producto
    if ($("#add_products tbody tr").length === 0) {
      toastr.error("Debe agregar al menos un producto a la cotización");
      submitButton.prop("disabled", false);
      return false;
    }

    // Proceder con el guardado
    saveCotize(this)
      .then(() => {
        submitButton.prop("disabled", false);
      })
      .catch(() => {
        submitButton.prop("disabled", false);
      });
  });

  // Inicializar objeto functions si no existe
  if (typeof functions === "undefined") {
    window.functions = {};
  }

  // Implementar la función toast_message correctamente
  functions.toast_message = function (type, message, title) {
    const validTypes = ["success", "info", "warning", "error"];

    // Validar el tipo de mensaje
    if (validTypes.includes(type)) {
      toastr[type](message, title || ""); // Usar título si está definido
    } else {
      console.error(`Tipo de mensaje no válido para toastr: ${type}`);
      toastr.info(message, title || "");
    }
  };

  // Agregar función warning faltante
  functions.warning = function (message, title) {
    functions.toast_message("warning", message, title || "Advertencia");
  };

  // Configurar toastr (notificaciones)
  toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-bottom-right",
    timeOut: 5000,
    newestOnTop: true,
    preventDuplicates: true,
    showDuration: 300,
    hideDuration: 1000,
    extendedTimeOut: 1000,
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
  };

  // Verificar si hay datos de cotización para cargar (modo edición/vista)
  if (typeof window.cotizeData !== "undefined") {
    const viewMode = $("#view_mode").val();
    if (viewMode === "edit" || viewMode === "view") {
      // Deshabilitar campos en modo vista
      if (viewMode === "view") {
        disableFormFields();
      }

      // Cargar datos de la cotización
      loadCotizacionData(window.cotizeData);
    }
  }
});

// Función para abrir el modal de productos
function openProductModal() {
  // Inicializar la tabla si aún no lo está
  if (!$.fn.DataTable.isDataTable("#products-table")) {
    initProductsTable();
  } else {
    // Recargar datos si la tabla ya existe
    $("#products-table").DataTable().ajax.reload();
  }
  $("#create_income_product_modal").modal("show");
}

// Inicializar tabla de productos
function initProductsTable() {
  try {
    if ($.fn.DataTable.isDataTable("#products-table")) {
      $("#products-table").DataTable().destroy();
    }

    const table = $("#products-table").DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: BASE_URL + "Products/get_active_products",
        type: "GET",
        dataSrc: function (json) {
          let dataToReturn = [];
          if (json && json.status === "OK" && Array.isArray(json.data)) {
            dataToReturn = json.data;
          } else if (Array.isArray(json)) {
            dataToReturn = json;
          } else {
            console.error("Estructura de datos inválida:", json);
            functions.toast_message(
              "error",
              "Error en el formato de datos de productos"
            );
          }
          return dataToReturn;
        },
        error: function (xhr, status, error) {
          console.error("Error AJAX al cargar productos:", {
            xhr,
            status,
            error,
          });
          functions.toast_message(
            "error",
            "Error al cargar la lista de productos."
          );
        },
      },
      columns: [
        {
          data: null,
          render: function (data, type, row) {
            return `<button type="button" class="btn btn-sm btn-primary select-product" data-id="${
              row.id || row.id_product
            }">
                            <i class="fas fa-check"></i> Seleccionar
                        </button>`;
          },
          orderable: false,
          searchable: false,
        },
        { data: "name", defaultContent: "N/A" },
        { data: "code", defaultContent: "N/A" },
        { data: "unit", defaultContent: "UND" },
        {
          data: "price",
          render: function (data) {
            return "S/. " + parseFloat(data || 0).toFixed(2);
          },
          defaultContent: "S/. 0.00",
        },
        {
          data: "stock",
          defaultContent: "0",
        },
      ],
      language: {
        url: BASE_URL + "public/app-assets/js/scripts/tables/spanish.json",
      },
      responsive: true,
      drawCallback: function () {
        $("#products-table .select-product")
          .off("click")
          .on("click", function () {
            var tr = $(this).closest("tr");
            var data = table.row(tr).data();
            if (data) {
              seleccionarProducto(data);
            } else {
              functions.toast_message(
                "error",
                "Error al obtener datos del producto."
              );
            }
          });
      },
    });

    return table;
  } catch (error) {
    console.error("Error al inicializar DataTable de productos:", error);
    functions.toast_message(
      "error",
      "Error al inicializar la tabla de productos"
    );
  }
}

// Función para seleccionar y agregar un producto a la tabla
function seleccionarProducto(producto) {
  try {
    // Validación básica
    if (!producto || typeof producto !== "object") {
      throw new Error("Datos del producto inválidos");
    }

    // Usar id_product o id según esté disponible
    const productId = producto.id_product || producto.id;
    const productName = producto.name || "Producto sin nombre";
    const productDescription = producto.description || productName;
    const productPrice = parseFloat(producto.price || 0).toFixed(2);

    if (!productId) throw new Error("ID del producto no encontrado");

    var tbody = $("#add_products tbody");

    // Verificar si el producto ya está en la tabla
    let existe = false;
    tbody.find('input[name="product_id[]"]').each(function () {
      if ($(this).val() == productId) {
        existe = true;
        // Incrementar cantidad si ya existe
        let cantidadInput = $(this).closest("tr").find(".cantidad");
        cantidadInput.val(parseInt(cantidadInput.val()) + 1);
        calcularPrecioParcial(cantidadInput);
        functions.toast_message(
          "info",
          "Cantidad actualizada para producto existente."
        );
        return false;
      }
    });

    if (existe) {
      $("#create_income_product_modal").modal("hide");
      return;
    }

    // Si no existe, agregar nueva fila
    var newRowHtml = `
            <tr>
                <td>
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
                <td>
                    <input type="hidden" name="product_id[]" value="${productId}">
                    <input type="text" class="form-control descripcion" value="${productDescription}" readonly>
                </td>
                <td>
                    <input type="number" class="form-control cantidad" value="1" min="1" onchange="calcularPrecioParcial(this)">
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control precio-unitario" value="${productPrice}" readonly>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control precio-parcial" value="${productPrice}" readonly>
                </td>
            </tr>
        `;

    tbody.append(newRowHtml);

    // Calcular totales después de agregar
    calcularTotales();

    // Cerrar el modal
    $("#create_income_product_modal").modal("hide");

    functions.toast_message("success", "Producto agregado correctamente", "OK");
  } catch (error) {
    console.error("Error al procesar producto:", error);
    functions.toast_message(
      "error",
      `Error al agregar producto: ${error.message}`,
      "ERROR"
    );
  }
}

// Función para eliminar una fila de producto
function eliminarFila(button) {
  $(button).closest("tr").remove();
  calcularTotales();
  functions.toast_message(
    "warning",
    "Producto eliminado de la cotización",
    "OK"
  );
}

// Función para calcular el precio parcial de una línea
function calcularPrecioParcial(input) {
  var row = $(input).closest("tr");
  var cantidad = parseFloat(row.find(".cantidad").val()) || 0;
  var precioUnitario = parseFloat(row.find(".precio-unitario").val()) || 0;
  var precioParcial = cantidad * precioUnitario;

  row.find(".precio-parcial").val(precioParcial.toFixed(2));

  calcularTotales();
}

// Función para calcular los totales de la cotización
function calcularTotales() {
  var subtotal = 0;

  // Sumar todos los precios parciales
  $("#add_products tbody tr").each(function () {
    subtotal += parseFloat($(this).find(".precio-parcial").val()) || 0;
  });

  // Calcular IGV y total
  var igv = subtotal * igvPorcentaje;
  var total = subtotal + igv;

  // Actualizar los campos
  $("#subtotal").text(subtotal.toFixed(2));
  $("#ssubtotal").val(subtotal.toFixed(2));

  $("#totaligv").text(igv.toFixed(2));
  $("#igv_total").val(igv.toFixed(2));

  $("#totalimp").text(total.toFixed(2));
  $("#total_venta").val(total.toFixed(2));
}

// Función para cargar datos del cliente seleccionado
function cargarDatosCliente(clienteId) {
  if (!clienteId) {
    functions.warning("No se proporcionó ID de cliente");
    return;
  }

  functions.toast_message("info", "Cargando datos del cliente...", "OK");

  $.ajax({
    url: BASE_URL + "Cotize_Details/get_client_data",
    type: "GET",
    data: { id_client: clienteId },
    dataType: "json",
    success: function (response) {
      if (response.status === "OK" && response.data) {
        try {
          $('input[name="document_number_cli"]').val(
            response.data.document_number || ""
          );
          $('input[name="address_cli"]').val(response.data.address || "");
          $('input[name="pt_description"]').val("Cotización Regular");

          functions.toast_message(
            "success",
            "Datos cargados correctamente",
            "Éxito"
          );
        } catch (error) {
          console.error("Error al establecer valores:", error);
          functions.toast_message(
            "error",
            "Error al procesar los datos: " + error.message
          );
        }
      } else {
        functions.toast_message(
          "error",
          response.message || "Error al cargar datos del cliente"
        );
      }
    },
    error: function (xhr, status, error) {
      console.error("Error en la petición:", {
        status: status,
        error: error,
        response: xhr.responseText,
      });
      functions.toast_message("error", "Error al conectar con el servidor");
    },
  });
}

// Función para guardar la cotización
function saveCotize(form) {
  return new Promise((resolve, reject) => {
    // Validar que haya al menos un producto
    if ($("#add_products tbody tr").length === 0) {
      functions.toast_message(
        "error",
        "Debe agregar al menos un producto a la cotización"
      );
      reject(new Error("No hay productos en la cotización"));
      return;
    }

    // Recopilar datos del formulario
    const formData = new FormData(form);

    // Recopilar detalles de productos
    const detalles = [];
    $("#add_products tbody tr").each(function () {
      const fila = $(this);
      const detalle = {
        product_id: fila.find('input[name="product_id[]"]').val(),
        description: fila.find(".descripcion").val(),
        quantity: parseFloat(fila.find(".cantidad").val()),
        unit_price: parseFloat(fila.find(".precio-unitario").val()),
        subtotal: parseFloat(fila.find(".precio-parcial").val()),
      };
      detalles.push(detalle);
    });

    // Agregar detalles al formulario
    formData.append("details", JSON.stringify(detalles));

    // Agregar subtotal, IGV y total
    formData.append("subtotal", $("#ssubtotal").val());
    formData.append("igv", $("#igv_total").val());
    formData.append("total", $("#total_venta").val());

    // Mostrar indicador de carga
    Swal.fire({
      title: "Guardando...",
      text: "Por favor espere mientras se guarda la cotización",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    // Enviar datos al servidor
    $.ajax({
      url: BASE_URL + "Cotize_Details/save_cotize_details",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      timeout: 90000, // 90 segundos de timeout
      success: function (response) {
        Swal.close();

        if (response && response.status === "OK") {
          Swal.fire({
            icon: "success",
            title: "¡Éxito!",
            text: "Cotización guardada correctamente",
            showConfirmButton: true,
          }).then(() => {
            // Redirigir a la lista de cotizaciones
            window.location.href = BASE_URL + "Cotize";
            resolve(response);
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text:
              response && response.message
                ? response.message
                : "Ocurrió un error al guardar la cotización",
          });
          reject(
            new Error(
              response && response.message
                ? response.message
                : "Error al guardar la cotización"
            )
          );
        }
      },
      error: function (xhr, status, error) {
        Swal.close();

        let errorMsg = "Ocurrió un error al conectar con el servidor";

        // Intentar extraer mensaje de error más específico
        try {
          if (xhr.responseText) {
            const contentType = xhr.getResponseHeader("Content-Type");
            if (contentType && contentType.includes("application/json")) {
              const responseJson = JSON.parse(xhr.responseText);
              if (responseJson && responseJson.message) {
                errorMsg = responseJson.message;
              }
            } else if (xhr.responseText.includes("<")) {
              const match = xhr.responseText.match(/<b>(.*?)<\/b>/);
              if (match && match[1]) {
                errorMsg = match[1];
              }
            } else {
              errorMsg = xhr.responseText.substring(0, 100);
            }
          }
        } catch (e) {
          console.error("Error al procesar respuesta:", e);
        }

        Swal.fire({
          icon: "error",
          title: "Error",
          text: errorMsg,
        });
        reject(new Error(errorMsg));
      },
    });
  });
}

// Función para cargar datos de cotización existente
function loadCotizacionData(cotizeData) {
  try {
    // Cargar datos del cliente
    $('select[name="business_name_cli"]')
      .val(cotizeData.person_id)
      .trigger("change");
    $('input[name="document_number_cli"]').val(
      cotizeData.client_document_number || ""
    );
    $('input[name="address_cli"]').val(cotizeData.client_address || "");

    // Cargar datos generales de la cotización
    $('input[name="referencia"]').val(cotizeData.reference || "");
    $('input[name="pt_description"]').val(
      cotizeData.cotize_type || "Cotización Regular"
    );
    $('input[name="validez_oferta"]').val(cotizeData.offer_validity || "15");

    // Cargar detalles de productos
    if (cotizeData.details && Array.isArray(cotizeData.details)) {
      // Limpiar tabla de productos
      $("#add_products tbody").empty();

      // Agregar cada producto a la tabla
      cotizeData.details.forEach(function (detail) {
        const newRow = `
                    <tr>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarFila(this)">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                        <td>
                            <input type="hidden" name="product_id[]" value="${
                              detail.product_id || ""
                            }">
                            <input type="text" class="form-control descripcion" value="${
                              detail.description || ""
                            }" readonly>
                        </td>
                        <td>
                            <input type="number" class="form-control cantidad" value="${
                              detail.quantity || 1
                            }" min="1" onchange="calcularPrecioParcial(this)">
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control precio-unitario" value="${
                              detail.unit_price || 0
                            }" readonly>
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control precio-parcial" value="${
                              detail.subtotal || 0
                            }" readonly>
                        </td>
                    </tr>
                `;
        $("#add_products tbody").append(newRow);
      });

      // Actualizar totales
      calcularTotales();
    }
  } catch (error) {
    console.error("Error al cargar datos de la cotización:", error);
    functions.toast_message(
      "error",
      "Error al cargar los datos de la cotización"
    );
  }
}

// Deshabilitar campos del formulario para modo visualización
function disableFormFields() {
  $("form input, form select, form textarea").prop("disabled", true);
  $('button[type="button"]').not(".btn-close").hide();
  $('button[type="submit"]').hide();
}
