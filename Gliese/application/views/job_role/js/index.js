// -- Functions

// --
function destroy_datatable() {
  // --
  $("#datatable-job_role").dataTable().fnDestroy();
}

// --
function refresh_datatable() {
  // --
  $("#datatable-job_role").DataTable().ajax.reload();
}

// --
function load_datatable() {
  // --
  destroy_datatable();
  // --
  let dataTable = $("#datatable-job_role").DataTable({
    // --
    ajax: {
      url: BASE_URL + "Job_Role/get_job_roles",
      cache: false,
    },
    columns: [
      { data: "id_job_role" },
      { data: "job_role" },
      {
        class: "text-center",
        render: function (data, type, row, meta) {
          // --
          return (
            '<button class="btn btn-sm btn-info btn-round btn-icon btn_update" data-process-key="' +
            row.id_job_role +
            '">' +
            feather.icons["edit"].toSvg({ class: "font-small-4" }) +
            "</button>" +
            " " +
            '<button  class="btn btn-sm btn-danger btn-round btn-icon btn_delete" data-process-key="' +
            row.id_job_role +
            '">' +
            feather.icons["trash-2"].toSvg({ class: "font-small-4" }) +
            "</button>"
          );
        },
      },
    ],
    order: [[0, "asc"]],
    dom: functions.head_datatable(),
    buttons: functions.custom_buttons_datatable([0, 1], "#create_job_role_modal"), // -- Columnas para exportar PDF (acciones excluida)
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

//--
function create_job_role(form) {
  // --
  $("#btn_create_job_role").prop("disabled", true);
  // --
  let params = new FormData(form);
  // --
  $.ajax({
    url: BASE_URL + "Job_Role/create_job_role",
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
        $("#create_job_role_modal").modal("hide");
        form.reset();
        refresh_datatable();
      } else {
        // --
        $("#btn_create_job_role").prop("disabled", false);
      }
    },
  });
}

//--
function update_job_role(form) {
  // --
  $("#btn_update_job_role").prop("disabled", true);
  // --
  let params = new FormData(form);
  // --
  $.ajax({
    url: BASE_URL + "Job_Role/update_job_role",
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
        $("#update_job_role_modal").modal("hide");
        form.reset();
        refresh_datatable();
      } else {
        // --
        $("#btn_update_job_role").prop("disabled", false);
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
  let params = { id_job_role: value };
  // --
  $.ajax({
    url: BASE_URL + "Job_Role/get_job_role_by_id",
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
        $("#update_job_role_form :input[name=id_job_role]").val(item.id_job_role);
        $("#update_job_role_form :input[name=job_role]").val(item.job_role);
      }
    },
  });
  // --
  $("#update_job_role_modal").modal("show");
});

// --
$(document).on("click", ".btn_delete", function () {
  // --
  let value = $(this).attr("data-process-key");
  // --
  let params = { id_job_role: value };
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
        url: BASE_URL + "Job_Role/delete_job_role",
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
  $("#create_job_role_form").validate().resetForm();
  $("#update_job_role_form").validate().resetForm();
});

// -- Validate form
$("#create_job_role_form").validate({
  // --
  submitHandler: function (form) {
    create_job_role(form);
  },
});

// -- Validate form
$("#update_job_role_form").validate({
  // --
  submitHandler: function (form) {
    update_job_role(form);
  },
});

// -- Reset form on modal hidden
$(".modal").on("hidden.bs.modal", function () {
  // --
  $(this).find("form")[0].reset();
  // -- Enable buttons
  $("#btn_create_job_role").prop("disabled", false);
  $("#btn_update_job_role").prop("disabled", false);
});

//--
load_datatable();
