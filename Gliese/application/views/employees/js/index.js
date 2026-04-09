// -- Functions

// --
function destroy_datatable() {
  // --
  $("#datatable-employees").dataTable().fnDestroy();
}

// --
function refresh_datatable() {
  // --
  $("#datatable-employees").DataTable().ajax.reload();
}

// --
function load_datatable() {
  // --
  destroy_datatable();
  // --
  let dataTable = $("#datatable-employees").DataTable({
    // --
    ajax: {
      url: BASE_URL + "Employees/get_employees",
      cache: false,
    },
    columns: [
      { data: "name" },
      { data: "document_description" },
      { data: "document_number" },
      { data: "phone" },
      { data: "address" },
      { data: "email" },
      { data: "work_area" },
      { data: "position" },
      { data: "salary" },
      {
        class: "text-center",
        render: function (data, type, row, meta) {
          // --
          return (
            '<button class="btn btn-sm btn-info btn-round btn-icon btn_update" data-process-key="' +
            row.id_employees +
            '">' +
            feather.icons["edit"].toSvg({ class: "font-small-4" }) +
            "</button>" +
            " " +
            '<button  class="btn btn-sm btn-danger btn-round btn-icon btn_delete" data-process-key="' +
            row.id_employees +
            '">' +
            feather.icons["trash-2"].toSvg({ class: "font-small-4" }) +
            "</button>"
          );
        },
      },
    ],
    order: [[0, "asc"]],
    dom: functions.head_datatable(),
    buttons: functions.custom_buttons_datatable([9], "#create_employees_modal"), // -- Columnas para exportar PDF (acciones excluida)
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

// --
function get_document_types() {
  // --
  $.ajax({
    url: BASE_URL + "Main/get_document_types",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    beforeSend: function () {

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
        $("#create_employees_form :input[name=document_type]").html(html);
        $("#update_employees_form :input[name=document_type]").html(html);
      }
    },
  });
}

function get_role() {
  $.ajax({
    url: BASE_URL + "Main/get_role",
    type: "GET",
    dataType: "json",
    cache: false,
    contentType: false,
    processData: false,
    success: function (data) {
      if (data.status === "OK") {
        var html = '<div class="btn-group btn-group-sm" role="group">';
        data.data.forEach((element, index) => {
          html += `
            <input type="checkbox" class="btn-check" id="role_${element.id}" name="role" value="${element.id}" autocomplete="off">
            <label class="btn btn-outline-primary" for="role_${element.id}">
              ${element.description}
            </label>
          `;
        });
        html += '</div>';

        $("#create_employees_form .role-options").html(html);
        $("#update_employees_form .role-options").html(html);

        // Inicializar los checkboxes estilizados
        initializeStyledCheckboxes();
      }
    },
  });
}

function initializeStyledCheckboxes() {
  $('.btn-check').on('change', function () {
    updateCheckboxStyle($(this));
  });

  // Inicializar estilos
  $('.btn-check').each(function () {
    updateCheckboxStyle($(this));
  });
}

function updateCheckboxStyle($checkbox) {
  var $label = $('label[for="' + $checkbox.attr('id') + '"]');
  if ($checkbox.is(':checked')) {
    $label.removeClass('btn-outline-primary').addClass('btn-primary');
  } else {
    $label.removeClass('btn-primary').addClass('btn-outline-primary');
  }
}

//--
function create_employees(form) {
  // --
  $("#btn_create_employees").prop("disabled", true);
  // --
  let params = new FormData(form);
  // --
  $.ajax({
    url: BASE_URL + "Employees/create_employees",
    type: "POST",
    data: params,
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    beforeSend: function () {

    },
    success: function (data) {
      // --
      functions.toast_message(data.type, data.msg, data.status);
      // --
      if (data.status === "OK") {
        // --
        $("#create_employees_modal").modal("hide");
        form.reset();
        refresh_datatable();
      } else {
        // --
        $("#btn_create_employees").prop("disabled", false);
      }
    },
  });
}

//--
function update_clients(form) {
  // --
  $("#btn_update_employees").prop("disabled", true);
  // --
  let params = new FormData(form);
  let documentType = $("#update_employees_form :input[name=document_type]")
    .find("option:selected")
    .text();
  // --
  params.append("description_document_type", documentType);
  // --
  $.ajax({
    url: BASE_URL + "Employees/update_employees",
    type: "POST",
    data: params,
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    beforeSend: function () {

    },
    success: function (data) {
      // --

      functions.toast_message(data.type, data.msg, data.status);
      // --
      if (data.status === "OK") {
        // --
        $("#update_employees_modal").modal("hide");
        form.reset();
        refresh_datatable();
      } else {
        // --
        $("#btn_update_employees").prop("disabled", false);
      }
    },
  });
}

// -- Events

//--
$(document).on("click", ".btn_update", function () {
  // --
  let value = $(this).attr("data-process-key");
  // --
  let params = { id_employees: value };
  // --
  $.ajax({
    url: BASE_URL + "Employees/get_employee_by_id",
    type: "GET",
    data: params,
    dataType: "json",
    contentType: false,
    processData: true,
    cache: false,
    success: function (data) {
      // --
      if (data.status === "OK") {
        // --
        let item = data.data;
        // --
        $("#update_employees_form :input[name=id_employees]").val(item.id_employees);
        $("#update_employees_form :input[name=document_type]").val(item.document_type_id).trigger("change");
        $("#update_employees_form :input[name=document_number]").val(item.document_number);
        $("#update_employees_form :input[name=name]").val(item.name);
        $("#update_employees_form :input[name=address]").val(item.address);
        $("#update_employees_form :input[name=phone]").val(item.phone);
        $("#update_employees_form :input[name=email]").val(item.email);
        $("#update_employees_form :input[name=reference]").val(item.reference);
        $("#update_employees_form :input[name=work_area]").val(item.work_area);
        $("#update_employees_form :input[name=position]").val(item.position);
        $("#update_employees_form :input[name=salary]").val(item.salary);
      }
    },
  });
  // --
  $("#update_employees_modal").modal("show");
});

// --
$(document).on("click", ".btn_delete", function () {
  // --
  let value = $(this).attr("data-process-key");
  // --
  let params = { id_employees: value };
  // --
  Swal.fire({
    title: "¿Estás seguro?",
    text: "¡No podrás revertir esto!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Si, desactivar!",
    customClass: {
      confirmButton: "btn btn-primary",
      cancelButton: "btn btn-outline-danger ms-1",
    },
    buttonsStyling: false,
    preConfirm: (_) => {
      return $.ajax({
        url: BASE_URL + "Employees/delete_employees",
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

$(document).ready(function () {
  $("#document_number").on("input", function () {
    let documentType = $("#document_type").val();
    let maxLength = documentType == 1 ? 8 : documentType == 2 ? 11 : null;
    if (maxLength) {
      let value = $(this).val();
      if (value.length > maxLength) {
        $(this).val(value.slice(0, maxLength));
        functions.toast_message("warning", `Máximo ${maxLength} caracteres permitidos`, "WARNING");
      }
    }
  });

  $("#document_type").on("change", function () {
    let documentType = $(this).val();
    let maxLength = documentType == 1 ? 8 : documentType == 2 ? 11 : null;
    if (maxLength) {
      let documentNumber = $("#document_number").val();
      if (documentNumber.length > maxLength) {
        $("#document_number").val(documentNumber.slice(0, maxLength));
        functions.toast_message("warning", `Máximo ${maxLength} caracteres permitidos`, "WARNING");
      }
    }
  });
});

$(document).on("click", ".btn_get_company_data", function () {
  let documentType = $("#document_type").val();
  let nroDoc = $("#document_number").val();

  if (documentType == 2 && nroDoc.length !== 11) {
    functions.toast_message("error", "Ingrese un RUC válido", "ERROR");
    return;
  }

  $.ajax({
    url: BASE_URL + "Employees/get_company_data",
    type: "GET",
    data: { nroDoc: nroDoc },
    dataType: "json",
    cache: false,
    success: function (response) {
      functions.toast_message(response.type, response.msg, response.status);
      if (response.status === 'OK') {
        let name = response.data.razonSocial ? response.data.razonSocial : response.data.nombres + " " + response.data.apellidoPaterno + " " + response.data.apellidoMaterno;
        $("#create_employees_modal :input[name=name]").val(name);
        $("#create_employees_modal :input[name=address]").val(response.data.direccion);
      }
    },
  });
});


// -- Reset forms
$(document).on("click", ".reset", function () {
  // --
  $("#create_employees_form").validate().resetForm();
  $("#update_employees_form").validate().resetForm();
});

// -- Validate form
$("#create_employees_form").validate({
  // --
  submitHandler: function (form) {
    create_employees(form);
  },
});

// -- Validate form
$("#update_employees_form").validate({
  // --
  submitHandler: function (form) {
    update_clients(form);
  },
});

// -- Reset form on modal hidden
$(".modal").on("hidden.bs.modal", function () {
  // --
  $(this).find("form")[0].reset();
  // -- Enable buttons
  $("#btn_create_employees").prop("disabled", false);
  $("#btn_update_employees").prop("disabled", false);
});
get_role();
get_document_types();
//--
load_datatable();
