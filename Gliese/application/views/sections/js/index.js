// -- Functions

// --
function destroy_datatable() {
  // --
  $("#datatable-sections").dataTable().fnDestroy();
}

// --
function refresh_datatable() {
  // --
  $("#datatable-sections").DataTable().ajax.reload();
}

// --
function load_datatable() {
  // --
  destroy_datatable();
  // --
  let dataTable = $("#datatable-sections").DataTable({
    // --
    ajax: {
      url: BASE_URL + "Sections/get_sections",
      cache: false,
    },
    columns: [
      { data: "name" },
      {
        data: "status",
        class: "center",
        render: function (data, type, row, meta) {
          if (row.status == "1") {
            return '<span class="badge rounded-pill badge-light-success" text-capitalized="">Activo</span>';
          } else {
            return '<span class="badge rounded-pill badge-light-secondary" text-capitalized="">Inactivo</span>';
          }
        },
      },
      {
        class: "center",
        render: function (data, type, row) {
          // --
          return (
            '<button class="btn btn-sm btn-info btn-round btn-icon btn_update" data-process-key="' +
            row.id_section +
            '">' +
            feather.icons["edit"].toSvg({ class: "font-small-4" }) +
            "</button>" +
            " " +
            '<button  class="btn btn-sm btn-danger btn-round btn-icon btn_delete" data-process-key="' +
            row.id_section +
            '">' +
            feather.icons["trash-2"].toSvg({ class: "font-small-4" }) +
            "</button>"
          );
        },
      },
    ],
    dom: functions.head_datatable(),
    buttons: functions.custom_buttons_datatable([2], "#create_section_modal"), // -- Number of columns
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
function create_section(form) {
  $("#create_section_modal").prop("disabled", true);

  let params = new FormData(form);

  $.ajax({
    url: BASE_URL + "Sections/create_section",
    type: "POST",
    data: params,
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    success: function (data) {
      // Mostrar un mensaje al usuario
      functions.toast_message(data.type, data.msg, data.status);

      if (data.status === "OK") {
        $("#create_section_modal").modal("hide");
        form.reset();
        refresh_datatable();
      }

      $("#create_section_modal").prop("disabled", false);
    },
  });
}

//--
function update_section(form) {
  // --
  $("#btn_update_section").prop("disabled", true);
  // --
  let params = new FormData(form);
  // --
  $.ajax({
    url: BASE_URL + "Sections/update_section",
    type: "POST",
    data: params,
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    beforeSend: function () {
      console.log("Cargando...");
    },
    success: function (data) {
      // --
      functions.toast_message(data.type, data.msg, data.status);
      // --
      if (data.status === "OK") {
        // --
        $("#update_section_modal").modal("hide");
        form.reset();
        refresh_datatable();
      } else {
        // --
        $("#btn_update_section").prop("disabled", false);
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
  let params = { id_section: value };

  // --datatable-sections
  $.ajax({
    url: BASE_URL + "Sections/get_section_by_id",
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
        $("#update_section_modal :input[name=id_section]").val(item.id_section);
        $("#update_section_modal :input[name=name]").val(item.name);
        // --update_section_modal
      }
    },
  });
  // --
  $("#update_section_modal").modal("show");
});

// --
$(document).on("click", ".btn_delete", function () {
  // --
  let value = $(this).attr("data-process-key");
  // --
  let params = { id_section: value };
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
        url: BASE_URL + "Sections/delete_section",
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

// -- Reset forms
$(document).on("click", ".reset", function () {
  // --
  $("#create_section_form").validate().resetForm();
  $("#update_section_form").validate().resetForm();
});

// -- Validate form
$("#create_section_form").validate({
  // --
  submitHandler: function (form) {
    create_section(form);
  },
});

// -- Validate form
$("#update_section_form").validate({
  // --
  submitHandler: function (form) {
    update_section(form);
  },
});

// -- Reset form on modal hidden
$(".modal").on("hidden.bs.modal", function () {
  // --
  $(this).find("form")[0].reset();
  // --
  $("#create_section_modal").prop("disabled", false);
  $("#btn_update_section").prop("disabled", false);
});

//--
load_datatable();
