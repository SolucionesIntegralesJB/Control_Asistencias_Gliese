function loadBillingData(billing_id) {
  // Obtener datos de la URL
  const urlParams = new URLSearchParams(window.location.search);
  const fields = {
    client: "input[name='client']",
    document: "input[name='document_number_client']",
    series: "input[name='series']",
    correlative: "input[name='correlative']",
    currency: "input[name='currency']",
    date: "input[name='due_date']",
    leyend: "input[name='leyend']",
    article: "td[name='article']",
    serie: "td[name='serie']",
    tax_affectation: "td[name='tax_affectation']",
    quantity: "td[name='quantity']",
    unit_value: "td[name='unit_value']",
    discount: "td[name='discount']",
    tax_amount: "td[name='tax_amount']",
    sale_price: "td[name='sale_price']",
    total_sale_taxed: "td[name='total_sale_taxed']",
    amount: "td[name='amount']",
  };

  // Verifica si los campos principales existen
  const required = [
    "client",
    "document",
    "series",
    "correlative",
    "currency",
    "date",
  ];
  const hasAllRequired = required.every((key) => urlParams.get(key));

  if (hasAllRequired) {
    Object.entries(fields).forEach(([key, selector]) => {
      const value = urlParams.get(key) || "-";
      if (selector.startsWith("input")) {
        $(selector).val(value);
      } else {
        $(selector).text(value);
      }
      // Mostrar tax_amount también en igv
      if (key === "tax_amount") {
        $("td[name='igv']").text(value);
      }
      if (key === "amount") {
        $("td[name='total_amount']").text(value);
      }
    });
  }
  // Si falta algún dato, haz AJAX
  else {
    $.ajax({
      url: BASE_URL + "Creditnote/get_billingpersale_by_id",
      type: "GET",
      data: { id: billing_id },
      dataType: "json",
      success: function (response) {
        if (response.status === "OK") {
          const data = response.data;
          $("input[name='client']").val(data.clients || "-");
          $("input[name='document_number_client']").val(
            data.document_number_client || "-"
          );
          $("input[name='series']").val(data.series || "-");
          $("input[name='correlative']").val(data.correlative || "-");
          $("input[name='currency']").val(data.currency || "-");
          $("input[name='due_date']").val(data.due_date || "-");
          $("input[name='leyend']").val(data.leyend || "-");
          $("td[name='article']").text(data.article || "-");
          $("td[name='serie']").text(data.serie || "-");
          $("td[name='tax_affectation']").text(data.tax_affectation || "-");
          $("td[name='quantity']").text(data.quantity || "-");
          $("td[name='unit_value']").text(data.unit_value || "-");
          $("td[name='discount']").text(data.discount || "-");
          $("td[name='tax_amount']").text(data.tax_amount || "-");
          $("td[name='price']").text(data.price || "-");
          $("td[name='total']").text(data.total || "-");
        }
      },
      error: function (xhr, status, error) {
        console.error("Error al cargar datos:", error);
      },
    });
  }
}

function getStatusBadge(status, response = "") {
  const statusMap = {
    1: {
      class: "warning",
      text: "Pendiente",
      tooltip: "Registrado en el sistema",
    },
    2: { class: "success", text: "Aceptado", tooltip: response },
    3: { class: "danger", text: "Rechazado", tooltip: response },
    4: { class: "warning", text: "Observado", tooltip: response },
  };

  const statusInfo = statusMap[status] || {
    class: "secondary",
    text: "Desconocido",
  };

  return `
    <div class="d-inline-flex align-items-center">
      <span class="badge rounded-pill badge-light-${statusInfo.class}">${
    statusInfo.text
  }</span>
      ${
        statusInfo.tooltip
          ? `<i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${statusInfo.tooltip}"></i>`
          : ""
      }
    </div>
  `;
}

$(document).ready(function () {
  // Obtener ID de la URL
  const urlParams = new URLSearchParams(window.location.search);
  const billing_id = urlParams.get("billing_id");

  if (billing_id) {
    loadBillingData(billing_id);
    loadCreditNoteType(); // Cargar tipo de nota de crédito
  } else {
    console.error("No se proporcionó billing_id");
    // Puedes redirigir o mostrar un mensaje
  }
});

// Mostrar el tipo de nota de crédito en texto
function mostrarTipoNotaCredito() {
  const urlParams = new URLSearchParams(window.location.search);
  const tipo = urlParams.get("id_creditnote_type");
  const tipos = {
    1: "Anulación de la operación",
    2: "Anulación por error en el RUC",
    3: "Devolución total",
    4: "Devolución parcial",
  };
  $("#credit_note_type").text(tipos[tipo] || "No seleccionado");
}
mostrarTipoNotaCredito();

// Guardar datos al presionar el botón
$("#btn_guardar").on("click", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const id = urlParams.get("billing_id");
  const id_creditnote_type = urlParams.get("id_creditnote_type");
  const leyend = $("input[name='leyend']").val();

  if (!id_creditnote_type || !leyend || !id) {
    Swal.fire({
      icon: "warning",
      title: "Completa todos los campos requeridos.",
    });
    return;
  }
  console.log({ id, id_creditnote_type, leyend });
  $.ajax({
    url: BASE_URL + "Creditnote/save_billingpersale_update",
    type: "POST",
    contentType: "application/json",
    data: JSON.stringify({
      id: id,
      id_creditnote_type: id_creditnote_type,
      leyend: leyend,
    }),
    success: function (response) {
      if (response.status === "OK") {
        Swal.fire({
          icon: "success",
          title: "Guardado correctamente",
          showConfirmButton: false,
          timer: 1500,
        }).then(() => {
          window.location.href = `${BASE_URL}Creditnote`;
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al guardar",
          text: response.msg || "Ocurrió un error.",
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: "error",
        title: "Error de conexión",
      });
    },
  });
});
