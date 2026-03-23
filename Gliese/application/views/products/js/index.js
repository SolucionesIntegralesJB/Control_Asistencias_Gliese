// -- Functions

// --
function destroy_datatable() {
  // --
  $("#datatable-products").dataTable().fnDestroy();
}

// --
function refresh_datatable() {
  // --
  $("#datatable-products").DataTable().ajax.reload();
}

// --
function load_datatable() {
  // --
  destroy_datatable();
  // --
  let dataTable = $("#datatable-products").DataTable({
    // --
    ajax: {
      url: BASE_URL + "Products/get_products",
      cache: false,
    },
    columns: [
      { data: "code" },
      { data: "name" },
      { data: "unit" },
      { data: "price" },
      { data: "label" },
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
            row.id_product +
            '">' +
            feather.icons["edit"].toSvg({ class: "font-small-4" }) +
            "</button>" +
            " " +
            '<button  class="btn btn-sm btn-danger btn-round btn-icon btn_delete" data-process-key="' +
            row.id_product +
            '">' +
            feather.icons["trash-2"].toSvg({ class: "font-small-4" }) +
            "</button>"
          );
        },
      },
    ],
    dom: functions.head_datatable(),
    buttons: functions.custom_buttons_datatable([2], "#create_product_modal"), // -- Number of columns
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
function get_u_medida() {
  // --
  $.ajax({
    url: BASE_URL + "Products/get_u_medida",
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
        $("#create_product_form :input[name=id_u_medida]").html(html);
        $("#update_product_form :input[name=id_u_medida]").html(html);
      }
    },
  });
}

// -- Functions

// ...
function get_head_types() {
  // --
  $.ajax({
    url: BASE_URL + "Products/get_head_types",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    beforeSend: function () {
      console.log("Cargando...");
    },
    success: function (data) {
      if (data.status === "OK") {
        // --
        var html = '<option value="">Seleccionar</option>';
        // --
        let combinedOption1 = '';
        let combinedOption2 = '';
        let combinedOption1Ids = [];
        let combinedOption2Ids = [];
        data.data.forEach((element, index) => {
          if (index < 2) {
            combinedOption1 += element.name + ' | ';
            combinedOption1Ids.push(element.id);
          }
          combinedOption2 += element.name + ' | ';
          combinedOption2Ids.push(element.id);
        });
        combinedOption1 = combinedOption1.slice(0, -3);
        combinedOption2 = combinedOption2.slice(0, -3);

        html += '<option value="' + combinedOption1Ids.join(',') + '">' + combinedOption1 + '</option>';
        html += '<option value="' + combinedOption2Ids.join(',') + '">' + combinedOption2 + '</option>';

        $("#create_product_form :input[name=head_type]").html(html);
        $("#update_product_form :input[name=head_type]").html(html);
      }
    },
  });
}

// --
function get_labels() {
  // --
  $.ajax({
    url: BASE_URL + "Products/get_labels",
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
        var html = '<option value="0">Seleccionar</option>';
        // var html = '';
        // --
        data.data.forEach((element) => {
          html +=
            '<option value="' + element.id + '">' + element.name + "</option>";
        });
        // -- Set values for select
        $("#create_product_form :input[name=id_label]").html(html);
        $("#update_product_form :input[name=id_label]").html(html);
      }
    },
  });
}

// --
function create_product(form) {
  $("#btn_create_product").prop("disabled", true);
  let params = new FormData(form);
  let headTypeArray = $("#create_product_form :input[name=head_type]").val().split(',');
  params.append("head_type", JSON.stringify(headTypeArray));
  $.ajax({
    url: BASE_URL + "Products/create_product",
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
      functions.toast_message(data.type, data.msg, data.status);
      if (data.status === "OK") {
        $("#create_product_modal").modal("hide");
        form.reset();
        refresh_datatable();
      }
      $("#btn_create_product").prop("disabled", false);
    },
  });
}

//--
function update_product(form) {
  // --
  $("#btn_update_product").prop("disabled", true);
  // --
  let params = new FormData(form);
  // --
  $.ajax({
    url: BASE_URL + "Products/update_product",
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
        $("#update_product_modal").modal("hide");
        form.reset();
        refresh_datatable();
      } else {
        // --
        $("#btn_update_product").prop("disabled", false);
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
  let params = { id_product: value };

  // --
  $.ajax({
    url: BASE_URL + "Products/get_product_by_id",
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
        $("#update_product_form :input[name=id_product]").val(item.id_product);
        $("#update_product_form :input[name=name]").val(item.name);
        $("#update_product_form :input[name=code]").val(item.code);
        $("#text-description").val(item.description);
        $("#update_product_form :input[name=id_u_medida]").val(item.id_unit).trigger("change");
        $("#update_product_form :input[name=head_type]").val(item.id_headers).trigger("change");
        // --
      }
    },
  });
  // --
  $("#update_product_modal").modal("show");
});
// --
$(document).on("click", ".btn_delete", function () {
  // --
  let value = $(this).attr("data-process-key");
  // --
  let params = { id_product: value };
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
        url: BASE_URL + "Products/delete_product",
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
  $("#create_product_form").validate().resetForm();
  $("#update_product_form").validate().resetForm();
});

// -- Validate form
$("#create_product_form").validate({
  // --
  submitHandler: function (form) {
    create_product(form);
  },
});

// -- Validate form
$("#update_product_form").validate({
  // --
  submitHandler: function (form) {
    update_product(form);
  },
});

// -- Reset form on modal hidden
$(".modal").on("hidden.bs.modal", function () {
  // --
  $(this).find("form")[0].reset();
  // --
  $("#btn_create_product").prop("disabled", false);
  $("#btn_update_product").prop("disabled", false);
});

// -- Get sections
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
        $("#create_product_modal :input[name=id_section]").html(html);
        $("#update_product_modal :input[name=id_section]").html(html);
      }
    },
  });
}

// -- Get categories by section
function get_categories_by_section(id_section, id_category) {
  let params = { id_section: id_section.toString() };
  console.log(params);
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
      $("#create_product_modal :input[name=id_category]").empty();
      $("#update_product_modal :input[name=id_category]").empty();
    },
    success: function (data) {
      console.log(data);
      // --
      if (data.status === "OK") {
        var html = '<option value="">Seleccionar</option>';
        data.data.forEach((element) => {
          html +=
            '<option value="' +
            element.id_category +
            '">' +
            element.name +
            "</option>";
        });
        // -- Set values for select
        $("#create_product_modal :input[name=id_category]").html(html);
        $("#update_product_modal :input[name=id_category]").html(html);
        // -- Select values
        id_category
          ? $("#update_product_form :input[name=id_category]")
            .val(id_category)
            .trigger("change")
          : "";
      }
    },
  });
}

// -- Get subcategories by category
function get_subcategories_by_category(id_category, id_subcategory) {
  let params = { id_category: id_category.toString() };
  console.log(params);
  $.ajax({
    url: BASE_URL + "Subcategories/get_subcategories_by_category",
    type: "GET",
    data: params,
    dataType: "json",
    contentType: false,
    processData: true,
    cache: false,
    beforeSend: function () {
      console.log("Cargando...");
      $("#create_product_modal :input[name=id_subcategory]").empty();
      $("#update_product_modal :input[name=id_subcategory]").empty();
    },
    success: function (data) {
      console.log(data);
      if (data.status === "OK") {
        var html = '<option value="">Seleccionar</option>';
        data.data.forEach((element) => {
          html +=
            '<option value="' +
            element.id_subcategory +
            '">' +
            element.name +
            "</option>";
        });
        $("#create_product_modal :input[name=id_subcategory]").html(html);
        $("#update_product_modal :input[name=id_subcategory]").html(html);
        // -- Select values
        id_subcategory
          ? $("#update_product_form :input[name=id_subcategory]")
            .val(id_subcategory)
            .trigger("change")
          : "";
      }
    },
  });
}

// -- Events
$("#create_product_modal :input[name=id_section]").on(
  "change",
  function (e) {
    get_categories_by_section($(this).val());
  }
);

$("#create_product_modal :input[name=id_category]").on(
  "change",
  function (e) {
    get_subcategories_by_category($(this).val());
  }
);


$("#update_product_modal select[name=id_section]").on(
  "change",
  function (e) {
    get_categories_by_section($(this).val());
  }
);

$("#update_product_modal select[name=id_category]").on(
  "change",
  function (e) {
    get_subcategories_by_category($(this).val());
  }
);


get_sections();

get_head_types();
//--
get_u_medida();
get_labels();
//--
load_datatable();
