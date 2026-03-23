// -- Functions

// --
function destroy_datatable() {
  // --
  $("#datatable-label").dataTable().fnDestroy();
}

// --
function refresh_datatable() {
  // --
  $("#datatable-label").DataTable().ajax.reload();
}

// --
function load_datatable() {
  // --
  destroy_datatable();
  // --
  let dataTable = $("#datatable-label").DataTable({
    // --
    ajax: {
      url: BASE_URL + "Labels/get_labels",
      cache: false,
    },
    columns: [
      { data: "name" },
      {
        data: "color",
        render: function (data, type, row, meta) {
          const colorDiv = `<div class="rounded" style="background-color: ${row.color}; width: 30px; height: 30px;"></div>`;

          return colorDiv;
        },
      },
      {
        class: "center",
        render: function (data, type, row, meta) {
          // --
          return (
            '<button class="btn btn-sm btn-info btn-round btn-icon btn_update" data-process-key="' +
            row.id_label +
            '">' +
            feather.icons["edit"].toSvg({ class: "font-small-4" }) +
            "</button>" +
            " " +
            '<button  class="btn btn-sm btn-danger btn-round btn-icon btn_delete" data-process-key="' +
            row.id_label +
            '">' +
            feather.icons["trash-2"].toSvg({ class: "font-small-4" }) +
            "</button>"
          );
        },
      },
    ],
    dom: functions.head_datatable(),
    buttons: functions.custom_buttons_datatable([0], "#create_label_modal"), // -- Number of columns
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
function create_label(form) {
  // --
  $("#btn_create_label").prop("disabled", true);
  // --
  let params = new FormData(form);
  // --
  $.ajax({
    url: BASE_URL + "Labels/create_label",
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
        $("#create_label_modal").modal("hide");
        form.reset();
        refresh_datatable();
      } else {
        // --
        $("#btn_create_label").prop("disabled", false);
      }
    },
  });
}

// --
function update_label(form) {
  // --
  $("#btn_update_label").prop("disabled", true);
  // --
  let params = new FormData(form);
  // --
  $.ajax({
    url: BASE_URL + "Labels/update_label",
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
        $("#update_label_modal").modal("hide");
        form.reset();
        refresh_datatable();
      } else {
        // --
        $("#btn_update_label").prop("disabled", false);
      }
    },
  });
}

// -- Events

// --
$(document).on("click", ".btn_update", function () {
  // --
  let value = $(this).attr("data-process-key");
  // --
  let params = { id_label: value };
  // --
  $.ajax({
    url: BASE_URL + "Labels/get_label_by_id",
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
        $("#update_label_form :input[name=id_label]").val(item.id_label);
        $("#update_label_form :input[name=name]").val(item.name);
        $("#update_label_form :input[name=color]").val(item.color);
      }
    },
  });
  // --
  $("#update_label_modal").modal("show");
});

// --
$(document).on("click", ".btn_delete", function () {
  // --
  let value = $(this).attr("data-process-key");
  // --
  let params = { id_label: value };
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
        url: BASE_URL + "Labels/delete_label",
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
  $("#create_label_form").validate().resetForm();
  $("#update_label_form").validate().resetForm();
});

// -- Validate form
$("#create_label_form").validate({
  // --
  submitHandler: function (form) {
    create_label(form);
  },
});

// -- Validate form
$("#update_label_form").validate({
  // --
  submitHandler: function (form) {
    update_label(form);
  },
});

// -- Reset form on modal hidden
$(".modal").on("hidden.bs.modal", function () {
  // --
  $(this).find("form")[0].reset();
  // -- Enable buttons
  $("#btn_create_label").prop("disabled", false);
  $("#btn_update_label").prop("disabled", false);
});

// --
load_datatable();
