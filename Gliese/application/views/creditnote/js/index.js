// -- Functions

// --
function destroy_datatable() {
  // --
  $("#datatable-creditnote").dataTable().fnDestroy();
}

// --
function refresh_datatable() {
  // --
  $("#datatable-creditnote").DataTable().ajax.reload();
}

// --
function load_datatable() {
  destroy_datatable();
  let dataTable = $("#datatable-creditnote").DataTable({
    ajax: {
      url: BASE_URL + "Creditnote/get_billingpersale",
      cache: false,
    },
    columns: [
      {
        class: "center",
        render: function (data, type, row, meta) {
          return (
            '<button class="btn btn-sm btn-primary btn-round btn-icon btn-invoice" data-process-key="' +
            row.id_creditnote +
            '">' +
            feather.icons["file-text"].toSvg({ class: "font-small-4" }) +
            " </button>"
          );
        },
      },

      { data: "due_date" },
      { data: "clients" },
      { data: "user_name" },
      { data: "document_number_client" },

      {
        data: null,
        render: function (row) {
          return `${row.series} - ${row.correlative}`;
        },
      },

      { data: "leyend" },

      {
        data: "voucher_type",
        class: "center",
        render: function (data, type, row, meta) {
          // Color según el valor numérico del voucher_type
          const colorClass = Number.isInteger(Number(row.voucher_type))
            ? "btn-success"
            : "btn-secondary";

          return `
            <button class="btn btn-sm ${colorClass} btn-round btn-voucher_type" 
                data-process-key="${row.id}"
                data-voucher_type="${row.voucher_type}"
                data-row-data='${JSON.stringify(row)}'>
              ${row.voucher_type}
            </button>`;
        },

        createdCell: function (td, cellData, rowData, row, col) {
          $(td)
            .find(".btn-voucher_type")
            .on("click", function () {
              const rowData = $(this).data("row-data");

              // Taba única sin condicionales
              const safe = (val) =>
                val === null || val === undefined || val === "" ? "-" : val;
              const tableContent = `
                <table class="table table-bordered table-sm">
                <thead>
                <tr>
                  <th style="background-color:rgb(102, 16, 242); color:rgb(255,255,255);">FECHA</th>
                  <th style="background-color:rgb(102, 16, 242); color:rgb(255,255,255);">CLIENTE</th>
                  <th style="background-color:rgb(102, 16, 242); color:rgb(255,255,255);">USUARIO</th>
                  <th style="background-color:rgb(102, 16, 242); color:rgb(255,255,255);">DOCUMENTO</th>
                  <th style="background-color:rgb(102, 16, 242); color:rgb(255,255,255);">NÚMERO</th>
                  <th style="background-color:rgb(102, 16, 242); color:rgb(255,255,255);">MOTIVO</th>
                  <th style="background-color:rgb(102, 16, 242); color:rgb(255,255,255);">TIPO DE NOTA DE CRÉDITO</th>
                  <th style="background-color:rgb(102, 16, 242); color:rgb(255,255,255);">ID. DOC. RELACIONADO</th>
                  <th style="background-color:rgb(102, 16, 242); color:rgb(255,255,255);">RESPUESTA SUNAT</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td>${safe(rowData.due_date)}</td>
                  <td>${safe(rowData.clients)}</td>
                  <td>${safe(rowData.user_name)}</td>
                  <td>${safe(rowData.document_number_client)}</td>
                  <td>${safe(rowData.series)} - ${safe(
                rowData.correlative
              )}</td>
                  <td>${safe(rowData.leyend)}</td>
                  <td>${safe(rowData.creditnote_type_description)}</td>
                  <td>${safe(rowData.voucher_type)}</td>
                  <td>${safe(rowData.sunat_response)}</td>
                </tr>
                </tbody>
                </table>`;

              $("#documentDetailsModalBody").html(tableContent);
              $("#documentDetailsModal").modal("show");
            });
        },
      },

      { data: "sunat_response" },

      {
        data: "status",
        class: "center",
        render: function (data, type, row, meta) {
          if (row.status == "1") {
            return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-warning">Pendiente</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Registrado en el sistema"></i>
                                </div>`;
          } else if (row.status == "2") {
            return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-success">Aceptado</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                                </div>`;
          } else if (row.status == "3") {
            return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-danger">Rechazado</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                                </div>`;
          } else if (row.status == "4") {
            return `<div class="d-inline-flex align-items-center">
                                    <span class="badge rounded-pill badge-light-warning">Observado</span>
                                    <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                                </div>`;
          }
        },
      },
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

// --
function load_billingpersale_datatable() {
  // Destruir cualquier instancia previa de DataTable
  $("#datatable-billingpersale").dataTable().fnDestroy();

  // Inicializar DataTable
  let billingpersale_datatable = $("#datatable-billingpersale").DataTable({
    ajax: {
      url: BASE_URL + "Creditnote/get_billingpersale",
      cache: false,
    },
    columns: [
      {
        class: "text-center",
        render: function (data, type, row) {
          return `
      <button 
        class="btn btn-sm btn-info btn-round btn-icon btn_add" 
        data-process-key="${row.id}"
        data-client="${row.clients}"
        data-document="${row.document_number_client}"
        data-series="${row.series}"
        data-correlative="${row.correlative}"
        data-currency="${row.currency}"
        data-date="${row.due_date}"
        data-leyend="${row.leyend}"
        data-article="${row.article}"
        data-serie="${row.serie}"
        data-tax_affectation="${row.tax_affectation}"
        data-quantity="${row.quantity}"
        data-unit_value="${row.unit_value}"
        data-discount="${row.discount}"
        data-tax_amount="${row.tax_amount}"
        data-sale_price="${row.sale_price}"
        data-total_sale_taxed="${row.total_sale_taxed}"
        data-amount="${row.amount}"

      >
        ${feather.icons["plus"].toSvg({ class: "font-small-4" })}
      </button>
    `;
        },
      },

      { data: "due_date" },
      { data: "clients" },
      { data: "user_name" },
      { data: "document_number_client" },
      { data: "series" },

      {
        data: "status",
        width: "60px",
        render: function (data, type, row, meta) {
          if (row.status == "1") {
            return `<div class="d-inline-flex align-items-center">
                            <span class="badge rounded-pill badge-light-warning">Pendiente</span>
                            <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Registrado en el sistema"></i>
                        </div>`;
          } else if (row.status == "2") {
            return `<div class="d-inline-flex align-items-center">
                            <span class="badge rounded-pill badge-light-success">Aceptado</span>
                            <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                        </div>`;
          } else if (row.status == "3") {
            return `<div class="d-inline-flex align-items-center">
                            <span class="badge rounded-pill badge-light-danger">Rechazado</span>
                            <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                        </div>`;
          } else if (row.status == "4") {
            return `<div class="d-inline-flex align-items-center">
                            <span class="badge rounded-pill badge-light-warning">Observado</span>
                            <i class="fa fa-info-circle ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="${row.response}"></i>
                        </div>`;
          }
        },
      },
    ],

    buttons: functions.custom_buttons_datatable(
      [10],
      "#create_billingpersale_modal"
    ), // -- Número de columnas
    language: {
      url: BASE_URL + "public/assets/json/languaje-es.json",
    },
  });

  // Manejar eventos de DataTable
  billingpersale_datatable.on("xhr", function () {
    var data = billingpersale_datatable.ajax.json();
    functions.toast_message(data.type, data.msg, data.status);
  });
}

// -- Events

//--

$(document).on("click", ".btn_add", function () {
  const billingId = $(this).data("process-key") || "-";
  const client = $(this).data("client") || "-";
  const document = $(this).data("document") || "-";
  const series = $(this).data("series") || "-";
  const correlative = $(this).data("correlative") || "-";
  const currency = $(this).data("currency") || "-";
  const date = $(this).data("date") || "-";
  const leyend = $(this).data("leyend") || "-";
  const article = $(this).data("article") || "-";
  const serie = $(this).data("serie") || "-";
  const tax_affectation = $(this).data("tax_affectation") || "-";
  const quantity = $(this).data("quantity") || "-";
  const unit_value = $(this).data("unit_value") || "-";
  const discount = $(this).data("discount") || "-";
  const tax_amount = $(this).data("tax_amount") || "-";
  const sale_price = $(this).data("sale_price") || "-";
  const total_sale_taxed = $(this).data("total_sale_taxed") || "-";
  const amount = $(this).data("amount") || "-";

  // Redirigir con todos los datos en la URL
  window.location.href = `${BASE_URL}Creditnote_Details?billing_id=${billingId}&id_creditnote_type=${
    window.selectedCreditNoteType
  }&client=${encodeURIComponent(
    client
  )}&document=${document}&series=${series}&correlative=${correlative}&currency=${currency}&date=${date}&leyend=${leyend}
  &article=${article}&serie=${serie}&tax_affectation=${tax_affectation}&quantity=${quantity}&unit_value=${unit_value}&discount=${discount}&tax_amount=${tax_amount}&sale_price=${sale_price}&total_sale_taxed=${total_sale_taxed}&amount=${amount}`;
});

// --
$(document).on("click", ".btn_delete", function () {
  // --
  let value = $(this).attr("data-process-key");
  // --
  let params = { id_creditnote: value };
  // --
  Swal.fire({
    title: "¿Estás seguro?",
    text: "¡No podrás revertir esto!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Si, eliminar!",
    customClass: {
      confirmButton: "btn btn-primary",
      cancelButton: "btn btn-outline-danger ms-1",
    },
    buttonsStyling: false,
    preConfirm: (_) => {
      return $.ajax({
        url: BASE_URL + "Creditnote/delete_creditnote",
        type: "POST",
        data: params,
        dataType: "json",
        cache: false,
        success: function (data) {
          // --
          functions.toast_message(data.type, data.msg, data.status);
          // --
          if (data.status === "OK") {
            // --
            refresh_datatable();
          }
        },
      });
    },
  }).then((result) => {
    if (result.isConfirmed) {
    }
  });
});

// Ajuste automático del ancho solo para el modal de detalles
$(document).ready(function () {
  if (!document.getElementById("custom-modal-css")) {
    const style = document.createElement("style");
    style.id = "custom-modal-css";
    style.innerHTML = `
      #documentDetailsModal .modal-dialog {
        max-width: 98vw !important;
        width: auto !important;
        min-width: fit-content !important;
      }
      #documentDetailsModal .modal-content {
        width: auto !important;
        min-width: fit-content !important;
      }
      #documentDetailsModal .modal-body {
        overflow-x: auto !important;
      }
    `;
    document.head.appendChild(style);
  }
});

// Ajuste automático del ancho solo para el DataTable principal
$(document).ready(function () {
  if (!document.getElementById("custom-datatable-css")) {
    const style = document.createElement("style");
    style.id = "custom-datatable-css";
    style.innerHTML = `
      #datatable-creditnote_wrapper {
        overflow-x: auto !important;
      }
      #datatable-creditnote {
        min-width: fit-content !important;
        width: auto !important;
        max-width: 98vw !important;
      }
    `;
    document.head.appendChild(style);
  }
});

// -- Reset form on modal hidden
$(".modal").on("hidden.bs.modal", function () {
  // --
  $(this).find("form")[0].reset();
  // --
  $("#btn_update_creditnote").prop("disabled", false);
  $("#btn_update_billingpersale").prop("disabled", false);
});

// --

window.selectedCreditNoteType = "";

$("#eligeModal").on("click", "#save_creditnote_type", function () {
  const selectedType = $("#tipoNotaC").val();
  if (!selectedType) {
    Swal.fire({
      icon: "warning",
      title: "Por favor, selecciona un tipo de nota de crédito.",
    });
    return;
  }
  window.selectedCreditNoteType = selectedType;
  $("#eligeModal").modal("hide");

  // Mensaje de éxito
  Swal.fire({
    icon: "success",
    title: "Tipo de nota de crédito seleccionado correctamente.",
  });
});

$(document).on("click", ".btn-invoice", function () {
  const id = $(this).data("process-key");

  // Opción 1: Abrir en nueva pestaña (si Factura.php genera HTML)
  window.open("../../../Reporte/Factura.php.php?billingId=" + billingId, "_blank");

  // Opción 2: Descargar PDF (necesitarías modificar Factura.php)
  // window.location.href = 'generar_factura.php?action=pdf&id=' + id;
});

//--
load_datatable();
load_billingpersale_datatable();
