// -- Functions
// --
function init() {
  mostrarform(false);
  listar();

  // $('#impuesto').prop('disabled',true);

  $("#formulario").on("submit", function (e) {
    guardaryeditar(e);
  });
}

function destroy_datatable_income() {
  // --
  $("#datatables-income").dataTable().fnDestroy();
}

// --
function refresh_datatable_income() {
  // --
  $("#datatables-income").DataTable().ajax.reload();
}

// --
function load_datatable_income() {
  // --
  destroy_datatable_income();
  // --
  let dataTable = $("#datatables-income").DataTable({
    // --
    ajax: {
      url: BASE_URL + "",
      cache: false,
    },
    columns: [{ data: "first_name" }],
    // dom: functions.head_datatable(),
    // buttons: functions.custom_buttons_datatable([0,1], '#create_user_modal'), // -- Number of columns
    language: {
      url: BASE_URL + "public/assets/json/languaje-es.json",
    },
  });

  // --
  dataTable.on("xhr", function () {
    // --
    var data = dataTable.ajax.json();
    // --
    functions.toast_message(data.type, data.msg, data.status);
  });
}

function get_coins() {
  console.log("Ejecutando get_coins()..."); // Verifica si la función se ejecuta

  $.ajax({
    url: "main/get_coins", // Ajusta la ruta si es necesario
    method: "GET",
    dataType: "json",
    success: function (response) {
      console.log("Respuesta AJAX:", response); // Muestra la respuesta en la consola

      if (response.status === "OK" && response.data.length > 0) {
        let moneda = response.data[0].description;
        $('span[name="coins"]').text(moneda);
        $('input[name="coins"]').val(moneda);
        console.log("Campo 'coins' actualizado con:", moneda);
      } else {
        console.error("No se encontraron datos de moneda.");
      }
    },
    error: function (xhr, status, error) {
      console.error("Error en AJAX:", xhr.responseText);
    },
  });
}

// Ejecutar la función cuando se cargue la página
$(document).ready(function () {
  get_coins();
});

// Modifica la función get_igv para actualizar un campo de entrada de texto
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
        // Actualiza el valor del campo de entrada de texto
        $("#create_income_details_form :input[name=igv]").val(
          data.data[0].value
        );
      }
    },
  });
}

// --
function get_business_name_cli() {
  // --
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
      // --
      if (data.status === "OK") {
        // --
        var html = '<option value="">Seleccionar</option>';
        // --
        data.data.forEach((element) => {
          html +=
            '<option value="' +
            element.id +
            '">' +
            element.business_name +
            "</option>";
        });
        // -- Set values for select
        $("#create_income_details_form :input[name=business_name_cli]").html(
          html
        );
      }
    },
  });
}

// --
function get_business_name() {
  // --
  $.ajax({
    url: BASE_URL + "Suppliers/get_business_name",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    beforeSend: function () {
      console.log("Cargando...");
    },
    success: function (data) {
      // --
      if (data.status === "OK") {
        // --
        var html = '<option value="">Seleccionar</option>';
        // --
        data.data.forEach((element) => {
          html +=
            '<option value="' +
            element.id +
            '">' +
            element.business_name +
            "</option>";
        });
        // -- Set values for select
        $("#create_income_details_form :input[name=business_name]").html(html);
      }
    },
  });
}

// --
function get_voucher_type() {
  $.ajax({
    url: BASE_URL + "Main/get_voucher_type",
    type: "GET",
    dataType: "json",
    cache: false,
    beforeSend: function () {
      console.log("Cargando tipos de comprobante...");
    },
    success: function (data) {
      console.log("Datos recibidos:", data); // Verifica qué datos llegan

      if (data.status === "OK") {
        var html = '<option value="">Seleccionar</option>';
        var documentNumber = $('input[name="document_number_cli"]').val();
        var disableFactura = documentNumber.length === 8; // Si tiene 8 dígitos, se deshabilita Factura

        data.data.forEach((element) => {
          console.log("ID:", element.id, "Descripción:", element.description);

          if (parseInt(element.id) === 1 || parseInt(element.id) === 2) {
            if (parseInt(element.id) === 1 && disableFactura) {
              // Si es Factura y el documento tiene 8 dígitos, la deshabilitamos
              html += `<option value="${element.id}" disabled>${element.description}</option>`;
            } else {
              html += `<option value="${element.id}">${element.description}</option>`;
            }
          }
        });

        console.log("HTML generado:", html); // Verifica qué opciones se generan
        $("#create_income_details_form select[name=vt_description]").html(html);
      } else {
        console.error("Respuesta sin estado OK:", data);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error en la petición AJAX:", status, error);
    },
  });
}
// hola como estas bien y tu bien genshin
$(document).ready(function () {
  $('#create_income_details_form select[name="vt_description"]').on(
    "change",
    function () {
      var selectedValue = $(this).val(); // ID del tipo de comprobante seleccionado
      var documentNumber = $('input[name="document_number_cli"]').val(); // Número de documento

      // Si se selecciona Factura (ID = 1) y el documento tiene 8 dígitos, mostrar error y regresar a Boleta

      if (selectedValue === "1" && documentNumber.length === 8) {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "No se puede emitir una Factura para un cliente con DNI!",
        });

        // Volver automáticamente a Boleta de Venta (ID = 2)
        $(this).val("2").trigger("change");
      }
    }
  );
});

// --
function get_payment_type() {
  // --
  $.ajax({
    url: BASE_URL + "Main/get_payment_type",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    beforeSend: function () {
      console.log("Cargando...");
    },
    success: function (data) {
      // --
      if (data.status === "OK") {
        // --
        var html = '<option value="">Seleccionar</option>';
        // --
        data.data.forEach((element) => {
          html +=
            '<option value="' +
            element.id +
            '">' +
            element.description +
            "</option>";
        });
        // -- Set values for select
        $("#create_income_details_form :input[name=pt_description]").html(html);
      }
    },
  });
}

$(document).on("change", 'select[name="fp_description"]', function () {
  var selectedPaymentMethod = $(this).val();
  var fechaVencimiento = $('input[name="fecha_vencimiento"]');
  var fechaActual = new Date().toISOString().split("T")[0]; // Formato YYYY-MM-DD

  if (selectedPaymentMethod === "1") {
    // Suponiendo que "1" es Contado
    // Bloquear y establecer fecha actual
    fechaVencimiento.val(fechaActual);
    fechaVencimiento.prop("readonly", true);
    fechaVencimiento.css("background-color", "#e9ecef");
  } else if (selectedPaymentMethod === "2") {
    // Suponiendo que "2" es Crédito
    // Habilitar y permitir selección
    fechaVencimiento.prop("readonly", false);
    fechaVencimiento.css("background-color", "");
    // Establecer mínimo como fecha actual
    fechaVencimiento.attr("min", fechaActual);
  }
});

// Modificamos la función get_payment_method para asegurarnos de que los valores sean correctos
function get_payment_method() {
  $.ajax({
    url: BASE_URL + "Main/get_payment_method",
    type: "GET",
    dataType: "json",
    success: function (data) {
      if (data.status === "OK") {
        var html = '<option value="">Seleccionar</option>';
        data.data.forEach((element) => {
          html += `<option value="${element.id}">${element.description}</option>`;
        });
        $('select[name="fp_description"]').html(html);

        // Establecer Contado como valor predeterminado y disparar el evento change
        $('select[name="fp_description"]').val("1").trigger("change");
      }
    },
  });
}
function getFechaActual() {
  var fechaActual = new Date().toISOString().split("T")[0];
  var inputFechaEmision = $('input[name="fecha_emision"]');

  // Solo establecer fecha emisión si no está ya establecida
  if (!inputFechaEmision.val()) {
    inputFechaEmision.val(fechaActual);
  }

  // Establecer mínimo para fecha emisión (2 días antes como máximo)
  var fechaMinEmision = new Date();
  fechaMinEmision.setDate(fechaMinEmision.getDate() - 2);
  inputFechaEmision.attr("min", fechaMinEmision.toISOString().split("T")[0]);

  // No tocamos fecha vencimiento aquí, lo maneja el evento de forma de pago
}
// --
function get_series() {
  // --
  $.ajax({
    url: BASE_URL + "Income/get_series",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    beforeSend: function () {
      console.log("Cargando...");
    },
    success: function (data) {
      // --
      if (data.status === "OK") {
        // --
        let series = data.data[0];
        // -- Set values for select
        $("#create_income_details_form :input[name=proof_series]").val(
          series.proof_series
        );
        $("#create_income_details_form :input[name=voucher_series]").val(
          series.voucher_series
        );
      }
    },
  });
}

// Función para establecer la fecha actual en "fecha_emision"
// y restringir la fecha mínima en "fecha_emision"
function getFechaActual() {
  var fechaActual = new Date();
  var año = fechaActual.getFullYear();
  var mes = String(fechaActual.getMonth() + 1).padStart(2, "0");
  var dia = String(fechaActual.getDate()).padStart(2, "0");
  var fechaFormateada = `${año}-${mes}-${dia}`;

  var fechaMinEmision = new Date();
  fechaMinEmision.setDate(fechaMinEmision.getDate() - 2);
  var añoMin = fechaMinEmision.getFullYear();
  var mesMin = String(fechaMinEmision.getMonth() + 1).padStart(2, "0");
  var diaMin = String(fechaMinEmision.getDate()).padStart(2, "0");
  var fechaMinimaEmision = `${añoMin}-${mesMin}-${diaMin}`;

  var inputFechaEmision = document.querySelector("input[name='fecha_emision']");
  var inputFechaVencimiento = document.querySelector(
    "input[name='fecha_vencimiento']"
  );
  var selectVoucherType = document.querySelector(
    "select[name='vt_description']"
  );

  if (inputFechaEmision) {
    inputFechaEmision.value = fechaFormateada;
    inputFechaEmision.min = fechaMinimaEmision;
  }

  if (inputFechaVencimiento) {
    inputFechaVencimiento.value =
      inputFechaVencimiento.value || fechaFormateada; // Mantiene la fecha si ya estaba puesta, si no, usa la actual
    inputFechaVencimiento.min = fechaFormateada;
  }

  // Evento para bloquear la fecha de vencimiento en "Factura" o "Boleta de Venta"
  if (selectVoucherType) {
    $(selectVoucherType).on("change.select2", function () {
      console.log("ID seleccionado:", selectVoucherType.value);

      if (selectVoucherType.value === "1" || selectVoucherType.value === "2") {
        let fechaExistente = inputFechaVencimiento.value || fechaFormateada; // Usa la fecha existente o la actual
        inputFechaVencimiento.value = fechaExistente;
        inputFechaVencimiento.setAttribute("readonly", true);
        inputFechaVencimiento.style.backgroundColor = "#e9ecef";
      } else {
        inputFechaVencimiento.removeAttribute("readonly");
        inputFechaVencimiento.style.backgroundColor = "";
      }
    });

    // Disparar el evento de cambio al cargar la página
    $(selectVoucherType).trigger("change.select2");
  }
}

// Ejecutar después de cargar los tipos de comprobante
$(document).ready(function () {
  get_voucher_type();
  setTimeout(getFechaActual, 500); // Asegurar que el select ya está cargado antes de asignar eventos
});

// -- Events
// --

// -- Variables globales (sin cambios)
var cont = 0;
var detalles = 0;
$("#btnGuardar").hide();
var codigo_prod = 0;

// -- Función para enviar el formulario (modificada)
$("#create_income_details_form").on("submit", function (e) {
  e.preventDefault();

  // Verificar si tenemos el ID del cliente (modificado)
  var clientId = $("select[name='business_name_cli']").val();
  if (!clientId) {
    alert("Por favor seleccione un cliente");
    return;
  }
  idUser = clientId; // Usamos directamente el valor del select como ID

  // Calcular los totales (sin cambios)
  calcularTotales();

  // Recopila los datos del formulario (sin cambios)
  var formData = new FormData($("#create_income_details_form")[0]);

  // Agrega manualmente el id_user al FormData (sin cambios)
  formData.append("id_user", idUser);
});

// -- Evento change para business_name_cli (modificado)
$(document).on("change", 'select[name="business_name_cli"]', function () {
  var selectedClients = $(this).val();
  idUser = selectedClients; // Asignamos directamente el valor seleccionado

  // El resto del código se mantiene igual
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
        console.log("id asignado:", idUser);
      }
    },
  });
});
// -- Inicialización (sin cambios)
$(document).ready(function () {
  get_coins();
  get_igv();
  get_business_name_cli();
  get_payment_method();
  get_voucher_type();
  get_series();
  getFechaActual();
  modificarSubtotales();
});

// Función para limpiar el formulario
function limpiar() {
  $("select[name='business_name_cli']").val("").trigger("change");
  $("input[name='fecha_emision']").val("");
  $("input[name='fecha_vencimiento']").val("");
  $("span[name='coins']").text("");
  $("input[name='coins']").val("");
  $("input[name='igv']").val("");
  $("input[name='document_number_cli']").val("");
  $("input[name='address_cli']").val("");
  $("select[name='fp_description']").val("").trigger("change");
  $("select[name='vt_description']").val("").trigger("change");
  $("select[name='pt_description']").val("").trigger("change");

  $("input[name='op_gravadas']").val("");
  $("#totalg").html("0.00");
  $("input[name='igv_total']").val("");
  $("#totaligv").html("0.00");

  $("input[name='total_venta']").val("");
  $(".filas").remove();
  $("#totalventa").html("0.00");

  // Obtenemos la fecha actual
  var now = new Date();
  var day = ("0" + now.getDate()).slice(-2);
  var month = ("0" + (now.getMonth() + 1)).slice(-2);
  var today = now.getFullYear() + "-" + month + "-" + day;
  $("input[name='fecha_vencimiento']").val(today);
  $("input[name='fecha_emision']").val(today);
}

// Función para mostrar/ocultar el formulario
function mostrarform(flag) {
  limpiar();
  if (flag) {
    $("#listadoregistros").hide();
    $("#formularioregistros").show();
    $("#btnagregar").hide();
    $("#btnGuardar").hide();
    $("#btnCancelar").show();
    $("#btnAgregarArt").show();
    detalles = 0;
  } else {
    $("#listadoregistros").show();
    $("#formularioregistros").hide();
    $("#btnagregar").show();
  }
}

// Función para cancelar el formulario
function cancelarform() {
  limpiar();
  window.location.href = "http://localhost/test_gliese/Igvinvoicing";
  mostrarform(false);
  // location.reload();
}

// Función para agregar un detalle a la venta
function agregarDetalle() {
  codigo_prod++;
  var newCodigoProd = "S" + String(codigo_prod).padStart(4, "0");

  // Obtenemos el IGV del campo igv_asig
  var igv_asig = document.getElementById("igv_asig");
  var impuesto = parseFloat(igv_asig.value) || 18; // Usa 18% como valor por defecto si no hay valor

  var cantidad = 1;
  var precio_venta = 1;
  igv_asig.value = impuesto; // Aseguramos que el campo tenga valor

  var igv_factor = 1 + impuesto / 100;
  var subtotal = (cantidad * precio_venta) / igv_factor;
  var igvv = cantidad * precio_venta - subtotal;
  subtotal = subtotal.toFixed(2);
  igvv = igvv.toFixed(2);
  var importe = cantidad * precio_venta;

  var fila =
    '<tr style="height:10px; padding:0;" id="fila' +
    cont +
    '">' +
    '<td><button type="button" class="btn btn-danger" onclick="eliminarDetalle(' +
    cont +
    ')">X</button></td>' +
    '<td><input type="text" name="product_code[]" class="form-control"></td>' +
    '<td style="width:30%;"><input type="text" class="form-control" name="product_description[]"  required></td>' +
    '<td style="width:10%" ><p style="margin-top:10px">NIU</p></td>' +
    cont +
    '"  style="width:120px;" required readonly></td>' +
    '<td style="width:7%"><input class="form-control" type="number" min="0" step="0.01" name="sale_price[]" id="sale_price' +
    cont +
    '" value="' +
    precio_venta +
    '" style="width:60px;"></td>' +
    '<td style="width:7%"><input class="form-control" type="number" min="0" name="quantity[]" id="quantity' +
    cont +
    '" value="' +
    cantidad +
    '" style="width:60px;"></td>' +
    '<td style="width:12%"><span name="subtotal" id="subtotal' +
    cont +
    '">' +
    subtotal +
    "</span></td>" +
    '<td style="width:12%"><span name="tax" id="tax' +
    cont +
    '">' +
    igvv +
    "</span></td>" +
    '<td style="width:12%"><span name="amount" id="amount' +
    cont +
    '">' +
    importe +
    "</span></td>" +
    "</tr>";
  detalles = detalles + 1;
  $("#detalles").append(fila);
  $("#sale_price" + cont).keyup(modificarSubtotales);
  $("#quantity" + cont).keyup(modificarSubtotales);
  $("#quantity" + cont).change(modificarSubtotales);
  $("#sale_price" + cont).change(modificarSubtotales);
  cont++;

  modificarSubtotales();
}

// Función para modificar los subtotales
function modificarSubtotales() {
  var cantidad = document.getElementsByName("quantity[]");
  var precio_venta = document.getElementsByName("sale_price[]");
  var importe = document.getElementsByName("amount");
  var subt = 0.0;
  var newvparcial = 0;
  var total_tax = 0;

  // Obtenemos el porcentaje de IGV del campo igv_asig
  var igv_percentage =
    parseFloat(document.getElementById("igv_asig").value) || 18; // Valor por defecto 18% si no hay valor
  var igv_factor = 1 + igv_percentage / 100;

  for (var i = 0; i < cantidad.length; i++) {
    var inpC = cantidad[i];
    var inpP = precio_venta[i];
    var inpS = importe[i];

    // Calculamos el importe total (precio * cantidad)
    inpS.value = inpP.value * inpC.value;

    // Calculamos el subtotal (precio sin IGV)
    var st = inpS.value / igv_factor;
    // Calculamos el IGV (precio total - subtotal)
    var ig = inpS.value - st;

    // Acumulamos los totales
    newvparcial += parseFloat(inpS.value);
    subt += st;
    total_tax += ig;

    // Mostramos los valores en las celdas correspondientes
    document.getElementsByName("subtotal")[i].innerHTML = addCommas(
      st.toFixed(2)
    );
    document.getElementsByName("tax")[i].innerHTML = addCommas(ig.toFixed(2));
    document.getElementsByName("amount")[i].innerHTML = addCommas(
      inpS.value.toFixed(2)
    );
  }

  // Actualizamos los totales generales
  $("#totalg").html("S/. " + addCommas(subt.toFixed(2)));
  $("input[name='taxable_operations']").val(subt.toFixed(2));

  $("#totaligv").html("S/. " + addCommas(total_tax.toFixed(2)));
  $("input[name='total_tax']").val(total_tax.toFixed(2));

  $("#totalventa").html("S/. " + addCommas(newvparcial.toFixed(2)));
  $("input[name='total_sale']").val(newvparcial.toFixed(2));

  evaluar();
}

// Función para evaluar si se deben mostrar u ocultar botones
function evaluar() {
  if (detalles > 0) {
    $("#btnGuardar").show();
  } else {
    $("#btnGuardar").hide();
    cont = 0;
  }
}

// Función para eliminar un detalle
function eliminarDetalle(index) {
  $("#fila" + index).remove();
  detalles--;
  modificarSubtotales();
  evaluar();
}

function calcularTotales() {
  let subtotal = 0;
  let igv = 0;
  let total = 0;
  let igv_percentage = parseFloat($("input[name='igv_asig']").val()) || 18;

  // Recorre los productos para calcular el subtotal
  $(".filas").each(function () {
    let precio =
      parseFloat($(this).find("input[name='sale_price[]']").val()) || 0;
    let cantidad =
      parseFloat($(this).find("input[name='quantity[]']").val()) || 0;
    subtotal += (precio * cantidad) / (1 + igv_percentage / 100);
  });

  // Calcula el IGV y el total
  igv = subtotal * (igv_percentage / 100);
  total = subtotal + igv;

  // Asigna los valores a los campos
  $("input[name='op_gravadas']").val(subtotal.toFixed(2));
  $("input[name='igv_total']").val(igv.toFixed(2));
  $("input[name='total_venta']").val(total.toFixed(2));

  return {
    subtotal: subtotal.toFixed(2),
    igv: igv.toFixed(2),
    total: total.toFixed(2),
  };
}
$("#create_income_details_form").on("submit", function (e) {
  e.preventDefault();

  // Validar cliente
  var clientId = $("select[name='business_name_cli']").val();
  if (!clientId) {
    alert("Por favor seleccione un cliente");
    return false;
  }

  // Calcular totales y obtener los valores
  var totales = calcularTotales();

  // Crear objeto con los datos
  var data = {
    business_name_cli: clientId,
    fecha_emision: $("input[name='fecha_emision']").val(),
    fecha_vencimiento: $("input[name='fecha_vencimiento']").val(),
    coins: $("input[name='coins']").val(),
    igv: $("input[name='igv']").val(),
    igv_asig: $("input[name='igv_asig']").val(),
    document_number_cli: $("input[name='document_number_cli']").val(),
    address_cli: $("input[name='address_cli']").val(),
    fp_description: $("select[name='fp_description']").val(),
    vt_description: $("select[name='vt_description']").val(),
    pt_description: $("select[name='pt_description']").val(),
    id_user: clientId,
    op_gravadas: totales.subtotal, // Usar los valores calculados
    igv_total: totales.igv, // Usar los valores calculados
    total_venta: totales.total, // Usar los valores calculados
    product_code: $("input[name='product_code[]']")
      .map(function () {
        return $(this).val();
      })
      .get(),
    product_description: $("input[name='product_description[]']")
      .map(function () {
        return $(this).val();
      })
      .get(),
    unit_of_measure: $("input[name='unit_of_measure[]']")
      .map(function () {
        return $(this).val();
      })
      .get(),
    sale_price: $("input[name='sale_price[]']")
      .map(function () {
        return $(this).val();
      })
      .get(),
    quantity: $("input[name='quantity[]']")
      .map(function () {
        return $(this).val();
      })
      .get(),
  };
  console.log("IGV enviado:", $("input[name='igv_asig']").val());
  console.log("Datos enviados:", data);

  // Enviar como JSON
  $.ajax({
    url: BASE_URL + "Igvinvoicing_Details/save_invoice",
    type: "POST",
    data: JSON.stringify(data),
    contentType: "application/json",
    dataType: "json",
    beforeSend: function () {
      $("#btnGuardar").prop("disabled", true);
    },
    success: function (response) {
      if (response && response.status === "OK") {
        // Mostrar mensaje de éxito y esperar confirmación
        Swal.fire({
          title:
            "Éxito: " + (response.message || "Factura creada correctamente"),
          icon: "success",
          confirmButtonText: "OK",
          draggable: true,
        }).then((result) => {
          if (result.isConfirmed) {
            // Redirigir después de presionar "OK"
            window.location.href = BASE_URL + "Igvinvoicing";
          }
        });

        // Opción 1: Redirigir a URL específica (RECOMENDADO)
      } else {
        alert("Error: " + (response?.message || "Respuesta inválida"));
      }
    },
    error: function (xhr) {
      let errorMsg = "Error: ";
      try {
        const jsonResponse = JSON.parse(xhr.responseText);
        errorMsg += jsonResponse.message || xhr.statusText;
      } catch (e) {
        errorMsg += "Servidor devolvió: " + xhr.responseText.substring(0, 100);
      }
      console.error("Detalles:", {
        status: xhr.status,
        response: xhr.responseText,
      });
      alert(errorMsg);
    },
    complete: function () {
      $("#btnGuardar").prop("disabled", false);
    },
  });
});

// Función para formatear números con comas
function addCommas(nStr) {
  nStr += "";
  x = nStr.split(".");
  x1 = x[0];
  x2 = x.length > 1 ? "." + x[1] : "";
  var rgx = /(\d+)(\d{3})/;
  while (rgx.test(x1)) {
    x1 = x1.replace(rgx, "$1" + "," + "$2");
  }
  return x1 + x2;
}

get_payment_type();
disableData();
get_business_name();
get_voucher_type();
get_series();
getFechaActual();
get_business_name_cli();
get_coins();
get_igv();
get_payment_method();
agregarDetalle();
modificarSubtotales();
