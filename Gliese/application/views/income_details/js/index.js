
// -- Functions
function destroy_datatable_income() {
  $("#datatables-income").dataTable().fnDestroy();
}

function refresh_datatable_income() {
  $("#datatables-income").DataTable().ajax.reload();
}

function load_datatable_income() {
  destroy_datatable_income();
  let dataTable = $("#datatables-income").DataTable({
    ajax: {
      url: BASE_URL + "",
      cache: false,
    },
    columns: [{ data: "first_name" }],
    language: {
      url: BASE_URL + "public/assets/json/languaje-es.json",
    },
  });

  dataTable.on("xhr", function () {
    var data = dataTable.ajax.json();
    functions.toast_message(data.type, data.msg, data.status);
  });
}

function destroy_datatable_income_products() {
  $("#datatables-income-products").dataTable().fnDestroy();
}

function refresh_datatable_income_products() {
  $("#datatables-income-products").DataTable().ajax.reload();
}

function load_datatable_income_products() {
  destroy_datatable_income_products();
  let dataTable = $("#datatables-income-products").DataTable({
    ajax: {
      url: BASE_URL + "Products/get_products",
      cache: false,
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
      { data: "price" },
      { data: "id_unit" },
      { data: "label" },
    ],
    language: {
      url: BASE_URL + "public/assets/json/languaje-es.json",
    },
  });

  dataTable.on("xhr", function () {
    var data = dataTable.ajax.json();
    functions.toast_message(data.type, data.msg, data.status);
  });
}

function get_person_name() {
  $.ajax({
    url: BASE_URL + "Main/get_person_name",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    success: function (data) {
      if (data.status === "OK") {
        var html = '<option value="">Seleccionar</option>';
        data.data.forEach((element) => {
          html += `<option value="${element.id}">${element.name}</option>`;
        });
        $("#create_income_form :input[name=p_name]").html(html);
      }
    },
  });
}

function get_payment_type() {
  $.ajax({
    url: BASE_URL + "Main/get_payment_type",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    success: function (data) {
      if (data.status === "OK") {
        var html = '<option value="">Seleccionar</option>';
        data.data.forEach((element) => {
          html += `<option value="${element.id}">${element.description}</option>`;
        });
        $("#create_income_form :input[name=pt_description]").html(html);
      }
    },
  });
}

function get_voucher_type() {
  $.ajax({
    url: BASE_URL + "Main/get_voucher_type",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    success: function (data) {
      if (data.status === "OK") {
        var html = '<option value="">Seleccionar</option>';
        data.data.forEach((element) => {
          html += `<option value="${element.id}">${element.description}</option>`;
        });
        $("#create_income_form :input[name=t_description]").html(html);
      }
    },
  });
}

function get_user_name() {
  $.ajax({
    url: BASE_URL + "Main/get_user_name",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    success: function (data) {
      if (data.status === "OK") {
        var html = '<option value="">Seleccionar</option>';
        data.data.forEach((element) => {
          html += `<option value="${element.id}">${element.first_name}</option>`;
        });
        $("#create_income_form :input[name=u_name]").html(html);
      }
    },
  });
}

// -- Events
$(document).on("click", ".btn_add", function () {
  let value = $(this).attr("data-process-key");
  let params = { id_product: value };

  $.ajax({
    url: BASE_URL + "Products/get_product_by_id",
    type: "GET",
    data: params,
    dataType: "json",
    contentType: false,
    processData: true,
    cache: false,
    success: function (data) {
      if (data.status === "OK") {
        let item = data.data;
        let productRow = $(`tr[data-product-id="${item.id_product}"]`);
        if (productRow.length > 0) {
          let quantityInput = productRow.find(".product-quantity");
          quantityInput.val(parseInt(quantityInput.val()) + 1);
          calcularSubtotal(quantityInput);
        } else {
          $("#add_products").append(getHtml(item));
          calcularSubtotal();
        }
      }
    },
  });
});

function getHtml(item) {
  var stock = parseInt(item.stock) || 1;
  var purchasePrice = parseFloat(item.price) || 0;
  var subtotal = stock * purchasePrice;

  return `<tr class="text-center" data-product-id="${item.id_product}">
    <td><button class="btn btn-danger btn-delete-product"><i class="fa fa-trash"></i></button></td>
   <td><span class="product-code">${item.code || ''}</span></td>
<td><span class="product-name">${item.name || ''}</span></td>
<td>
  <div class="input-group" style="width: 180px;">
    <span class="input-group-text">S/.</span>
    <input type="number" name="price[]" value="${purchasePrice.toFixed(2)}" class="form-control product-price text-center" step="0.01" min="0" placeholder="Precio">
  </div>
</td>
    <td><input type="number" name="stock[]" value="${stock}" class="form-control product-quantity text-center" style="width: 140px;" oninput="calcularSubtotal(this)" min="1"></td>
    
    <td><span class="subtotal product-subtotal" data-value="${subtotal}">S/. ${subtotal.toFixed(2)}</span></td>
  </tr>`;
}

function calcularSubtotal(element) {
  var row = $(element).closest("tr");
  var stock = parseInt(row.find('input[name="stock[]"]').val()) || 0;
  var price = parseFloat(row.find('input[name="price[]"]').val()) || 0;
  var subtotal = stock * price;
  row.find("span.subtotal").text("S/" + subtotal.toFixed(2));
  row.find("span.subtotal").attr("data-value", subtotal.toFixed(2));
  calcularTotalGeneral();
}

function calcularTotalGeneral() {
  var total = 0;
  $(".product-subtotal").each(function () {
    total += parseFloat($(this).attr("data-value")) || 0;
  });
  $("#total-general").text("S/" + total.toFixed(2));
}

$(document).ready(function () {
  $("#add_products").append(`
    <tfoot>
      <tr>
        <td colspan="7" class="text-end"><strong>Costo Invertido:</strong></td>
        <td><span id="total-general"> S/ 0.00</span></td>
      </tr>
    </tfoot>
  `);
});

$("#cuota").on("input", calculateQuotaValue);

$(document).on("click", ".btn-delete-product", function () {
  $(this).closest("tr").remove();
});

$(document).ready(function () {
  $('select[name="pt_description"]').change(function () {
    var selectedOption = $(this).val();
    if (selectedOption === "2") {
      $("#add_cuota").closest(".row").show();
    } else {
      $("#add_cuota").closest(".row").hide();
    }
  });

  var initialOption = $('select[name="pt_description"]').val();
  if (initialOption === "1") {
    $("#add_cuota").closest(".row").show();
  } else {
    $("#add_cuota").closest(".row").hide();
  }
});

function calculateQuotaValue() {
  var total = parseFloat($("#total-subtotal").text()) || 0;
  var cuotas = parseInt($("#cuota").val()) || 1;
  if (cuotas > 0) {
    var valorDeCuota = total / cuotas;
    $("#valor-de-cuota").text(valorDeCuota.toFixed(2));
  }
}

$(document).ready(function () {
  $("#btn_guardar_product").click(function () {
    let productos = [];

    $("#add_products tr").each(function () {
      let productId = $(this).attr("data-product-id");
      let price = parseFloat($(this).find('input[name="price[]"]').val()) || 0;
      let quantity = parseInt($(this).find('input[name="stock[]"]').val()) || 1;

      if (productId) {
        productos.push({
          id_product: productId,
          quantity: quantity,
          unit_price: price,
          subtotal: price * quantity,
        });
      }
    });

    let formData = {
      id_person: $("select[name='p_name']").val(),
      id_user: $("select[name='u_name']").val(),
      id_voucher_type: $("select[name='t_description']").val(),
      id_payment_type: $("select[name='pt_description']").val(),
      proof_series: $("input[name='proof_series']").val(),
      voucher_series: $("input[name='voucher_series']").val(),
      date_expiration: $("input[name='date_expiration']").val(),
      number_installments: $("input[name='number_installments']").val() || 1,
      value_installment: $("input[name='value_installment']").val() || 0,
      productos: productos,
    };
console.log(formData);
    if (!formData.proof_series || !formData.voucher_series ) {
      Swal.fire({
        icon: "warning",
        title: "Campos Obligatorios",
        text: "Debe completar todos los campos antes de guardar.",
      });
      return;
    }
    

    $.ajax({
      url: BASE_URL + "Income_Details/create_income_products",
      type: "POST",
      data: JSON.stringify(formData),
      contentType: "application/json",
      dataType: "json",
      success: function (response) {
        if (response.status === "OK") {
          Swal.fire({
            icon: "success",
            title: "Compra registrada",
            text: "Los productos han sido guardados exitosamente.",
          }).then(() => {
            window.location.href = BASE_URL + "Income/index.php";
          });
        } else {
          Swal.fire({
            icon: "warning",
            title: "Error al guardar",
            text: response.msg,
          });
        }
      },
      error: function (xhr, status, error) {
        Swal.fire({
          icon: "error",
          title: "Error en la petición",
          text: "Hubo un problema al procesar la solicitud.",
        });
      },
    });
  });
});


function enviarProductos(idIngreso) {
  let productos = [];

  $("#add_products tr").each(function () {
    let productId = $(this).attr("data-product-id");
    let price = parseFloat($(this).find('input[name="price[]"]').val()) || 0;
    let quantity = parseInt($(this).find('input[name="stock[]"]').val()) || 1;

    if (productId) {
      productos.push({
        id_income: idIngreso,
        id_product: productId,
        quantity: quantity,
        unit_price: price,
        subtotal: price * quantity,
      });
    }
  });
  console.log(productos);
  if (productos.length > 0) {
    $.ajax({
      url: BASE_URL + "Income_Details/create_income_products_details",
      type: "POST",
      data: JSON.stringify({ productos }),
      contentType: "application/json",
      dataType: "json",
      success: function (response) {
        if (response.status === "OK") {
          Swal.fire({
            icon: "success",
            title: "Compra registrada",
            text: "Los productos han sido guardados exitosamente.",
          }).then(() => {
            window.location.href = BASE_URL + "Income/index.php";
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: response.msg || "No se pudo guardar los productos.",
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Error en la solicitud",
          text: "Error al enviar los productos al servidor.",
        });
      },
    });
  } else {
    Swal.fire({
      icon: "error",
      title: "Campos Obligatorios",
      text: "Debe agregar al menos un producto.",
    });
  }
}

//View: income_details - index.js
//EXCEL PRODUCTS

$(document).ready(function () {
  $("#btn-import-excel").click(function () {
    const file = $("#excel-file")[0].files[0];
    if (!file) {
      console.error("No se seleccionó ningún archivo.");
      return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
      try {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: "array" });
        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        const jsonData = XLSX.utils.sheet_to_json(sheet, { defval: "" });

        console.log("Excel cargado:", jsonData);

        let totalRequests = jsonData.length;
        let completedRequests = 0;

        jsonData.forEach(item => {
          const productId = parseInt(item.id);
          const stock = parseInt(item.stock) || 1;

          if (isNaN(productId)) {
            console.warn("ID no válido en el Excel.");
            completedRequests++;
            checkAllCompleted();
            return;
          }

          $.ajax({
            url: BASE_URL + "Income_Details/getById",
            method: "GET",
            data: { id: productId },
            dataType: "json",
            success: function (response) {
              if (response.status === "OK") {
                const producto = response.data;
                const subtotal = producto.price * stock;

                const row = `
                  <tr data-product-id="${producto.id}">
                    <td><button class="btn btn-danger btn-sm btn-delete-product"><i class="fa fa-trash"></i></button></td>
                    <td>${producto.code}</td>
                    <td>${producto.name}</td>
                    <td><input type="number" name="price[]" class="form-control" value="${producto.price}" readonly /></td>
                    <td><input type="number" name="stock[]" class="form-control" value="${stock}" /></td>
                    <td class="subtotal">${subtotal.toFixed(2)}</td>
                  </tr>
                `;

                $("#add_products tbody").append(row);
              } else {
                console.warn(`Producto con ID ${productId} no encontrado en base de datos.`);
              }
              completedRequests++;
              checkAllCompleted();
            },
            error: function (err) {
              console.error("Error en la solicitud AJAX:", err);
              completedRequests++;
              checkAllCompleted();
            }
          });
        });

        function checkAllCompleted() {
          if (completedRequests === totalRequests) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('create_income_excel'));
            if (modal) modal.hide();
          }
        }

      } catch (err) {
        console.error("Error al procesar el archivo Excel:", err);
      }
    };

    reader.onerror = function (err) {
      console.error("Error al leer el archivo:", err);
    };

    reader.readAsArrayBuffer(file);
  });


  $(document).on("click", ".btn-delete-product", function () {
    $(this).closest("tr").remove();
  });


  $(document).on("input", 'input[name="stock[]"]', function () {
    const row = $(this).closest("tr");
    const price = parseFloat(row.find('input[name="price[]"]').val()) || 0;
    const stock = parseInt($(this).val()) || 0;
    const subtotal = price * stock;

    row.find(".subtotal").text(subtotal.toFixed(2));
  });
});


//EXCEL FULL
$('#btn_upload_income').on('click', function () {
  const file = $('#excel-file-full')[0].files[0];

  if (!file) {
    alert('Selecciona un archivo Excel.');
    return;
  }

  const reader = new FileReader();

  reader.onload = function (e) {
    const data = new Uint8Array(e.target.result);
    const workbook = XLSX.read(data, { type: 'array' });

    const sheet = workbook.Sheets[workbook.SheetNames[0]];
    const rows = XLSX.utils.sheet_to_json(sheet, { defval: '' });

    console.log("Contenido del Excel:", rows);

    const productos = [];

    // Desde la fila 1 en adelante son productos
    for (let i = 0; i < rows.length; i++) {
      const fila = rows[i];

      const id_product = parseInt(fila.id || fila.id_product);
      const quantity = parseInt(fila.stock || fila.quantity || 1);

      if (!isNaN(id_product) && !isNaN(quantity)) {
        productos.push({
          id_product,
          quantity
        });
      }
    }


    // Fila 0 = datos del ingreso
    const fila0 = rows[0];
    const ingreso = {
      id_person: parseInt(fila0.id_person),
      id_user: parseInt(fila0.id_user),
      id_voucher_type: parseInt(fila0.id_voucher_type),
      id_payment_type: parseInt(fila0.id_payment_type),
      proof_series: fila0.proof_series,
      voucher_series: fila0.voucher_series,
      date_expiration: fila0.date_expiration,
      number_installments: parseInt(fila0.number_installments),
      value_installment: parseFloat(fila0.value_installment),
      status: 1,
      productos: productos
    };

    console.log("✅ Ingreso armado para enviar:", ingreso);

    $.ajax({
      url: BASE_URL + "Income_Details/create_income_full",
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(ingreso),
      success: function (res) {
        if (res.status === 'OK') {
          Swal.fire({
            icon: "success",
            title: "Ingreso registrado",
            text: "Los productos han sido guardados exitosamente.",
          }).then(() => {
            window.location.href = BASE_URL + "Income/index.php";
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: res.message || "No se pudo registrar el ingreso.",
          });
        }
      },
      error: function (xhr, status, error) {
        console.error(xhr.responseText);
        Swal.fire({
          icon: "error",
          title: "Error en la solicitud",
          text: "Hubo un problema con el servidor.",
        });
      }
    });
  };

  reader.readAsArrayBuffer(file);
});


load_datatable_income();
load_datatable_income_products();
get_payment_type();
get_person_name();
get_voucher_type();
get_user_name();
