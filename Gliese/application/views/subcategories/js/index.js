// -- Functions

// --
function destroy_datatable() {
  // --
  $("#datatable-subcategory").dataTable().fnDestroy();
}

// --
function refresh_datatable() {
  // --
  $("#datatable-subcategory").DataTable().ajax.reload();
}

// --
function load_datatable() {
  // --
  destroy_datatable();
  // --
  let dataTable = $("#datatable-subcategory").DataTable({
    // --
    ajax: {
      url: BASE_URL + "Subcategories/get_subcategories",
      cache: false,
    },
    columns: [
      { data: "name_section" },
      { data: "name_category" },
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
        render: function (data, type, row, meta) {
          // --
          return (
            '<button class="btn btn-sm btn-info btn-round btn-icon btn_update" data-process-key="' +
            row.id_subcategory +
            '">' +
            feather.icons["edit"].toSvg({ class: "font-small-4" }) +
            "</button>" +
            " " +
            '<button  class="btn btn-sm btn-danger btn-round btn-icon btn_delete" data-process-key="' +
            row.id_subcategory +
            '">' +
            feather.icons["trash-2"].toSvg({ class: "font-small-4" }) +
            "</button>"
          );
        },
      },
    ],
    dom: functions.head_datatable(),
    buttons: functions.custom_buttons_datatable(
      [0],
      "#create_subcategory_modal"
    ), // -- Number of columns
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
function create_subcategory(form) {
  // --
  $("#btn_create_category").prop("disabled", true);
  // --
  let params = new FormData(form);
  // --
  $.ajax({
    url: BASE_URL + "Subcategories/create_subcategory",
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
        $("#create_subcategory_modal").modal("hide");
        form.reset();
        refresh_datatable();
      } else {
        // --
        $("#btn_create_category").prop("disabled", false);
      }
    },
  });
}

// --
function update_subcategory(form) {
  // --
  $("#btn_update_category").prop("disabled", true);
  // --
  let params = new FormData(form);
  // --
  $.ajax({
    url: BASE_URL + "Subcategories/update_subcategory",
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
        $("#update_subcategory_modal").modal("hide");
        form.reset();
        refresh_datatable();
      } else {
        // --
        $("#btn_update_subcategory").prop("disabled", false);
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
  let params = { id_subcategory: value };
  // --
  $.ajax({
    url: BASE_URL + "Subcategories/get_subcategory_by_id",
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
        $("#update_subcategory_form :input[name=id_subcategory]").val(
          item.id_subcategory
        );
        $("#update_subcategory_form :input[name=name]").val(item.name);
        $("#update_subcategory_form :input[name=id_section]")
          .val(item.id_section)
          .trigger("change");
        get_categories_by_section(item.id_section, item.id_category);
      }
    },
  });
  // --
  $("#update_subcategory_modal").modal("show");
});

// --
$(document).on("click", ".btn_delete", function () {
  // --
  let value = $(this).attr("data-process-key");
  // --
  let params = { id_subcategory: value };
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
        url: BASE_URL + "Subcategories/delete_subcategory",
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
  $("#create_subcategory_form").validate().resetForm();
  $("#update_subcategory_form").validate().resetForm();
});

// -- Validate form
$("#create_subcategory_form").validate({
  // --
  submitHandler: function (form) {
    create_subcategory(form);
  },
});

// -- Validate form
$("#update_subcategory_form").validate({
  // --
  submitHandler: function (form) {
    update_subcategory(form);
  },
});

// -- Reset form on modal hidden
$(".modal").on("hidden.bs.modal", function () {
  // --
  $(this).find("form")[0].reset();
  // -- Enable buttons
  $("#btn_create_category").prop("disabled", false);
  $("#btn_update_category").prop("disabled", false);
});

// --
load_datatable();

// --
function get_sections() {
  // --
  $.ajax({
    url: BASE_URL + "Sections/get_sections",
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
        // var html = '';
        // --
        data.data.forEach((element) => {
          html +=
            '<option value="' +
            element.id_section +
            '">' +
            element.name +
            "</option>";
        });
        // -- Set values for select
        $("#create_subcategory_modal :input[name=id_section]").html(html);
        $("#update_subcategory_modal :input[name=id_section]").html(html);
      }
    },
  });
}

// --
function get_categories_by_section(id_section, id_category) {
  // let value = $("#update_subcategory_form :input[name=id_section]").val();
  let params = { id_section: id_section.toString() };
  // --
  $.ajax({
    url: BASE_URL + "Subcategories/get_categories_by_section",
    type: "GET",
    data: params,
    dataType: "json",
    contentType: false,
    processData: true,
    cache: false,
    beforeSend: function () {
      console.log("Cargando...");
      $("#create_subcategory_modal :input[name=id_category]").empty();
      $("#update_subcategory_modal :input[name=id_category]").empty();
    },
    success: function (data) {
      // --;
      if (data.status === "OK") {
        // --
        var html = '<option value="">Seleccionar</option>';
        // var html = '';
        // --
        data.data.forEach((element) => {
          html +=
            '<option value="' +
            element.id_category +
            '">' +
            element.name +
            "</option>";
        });
        // -- Set values for select
        $("#create_subcategory_modal :input[name=id_category]").html(html);
        $("#update_subcategory_modal :input[name=id_category]").html(html);
        // -- Select values
        id_category
          ? $("#update_subcategory_form :input[name=id_category]")
              .val(id_category)
              .trigger("change")
          : "";
      }
    },
  });
}

$("#create_subcategory_modal :input[name=id_section]").on(
  "change",
  function (e) {
    get_categories_by_section($(this).val());
  }
);

$("#update_subcategory_modal select[name=id_section]").on(
  "change",
  function (e) {
    get_categories_by_section($(this).val());
  }
);

get_sections();
