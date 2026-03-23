// --
// SELECT PRODUCT
// --

function get_products() {
  // --
  $.ajax({
    url: BASE_URL + "Products/get_products",
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
            element.id_product +
            '">' +
            element.name +
            "</option>";
        });
        $("#select-product").html(html);
      }
    },
  });
}

$(document).on("click", "#btn_select_product", function () {
  var id_product = $('#select-product').val();
  let params = { id_product: id_product };

  $.ajax({
    url: BASE_URL + "Productdetails/get_product_by_id",
    type: "GET",
    data: params,
    dataType: "json",
    contentType: false,
    processData: true,
    cache: false,
    success: function (data) {
      if (data.status === "OK") {
        let item = data.data;
        function setValueOrEmpty(selector, value) {
          $(selector).val(value || '');
        }
        setValueOrEmpty("#create_product_form :input[name=id_product]", item.id_product);
        setValueOrEmpty("#create_product_form :input[name=name]", item.name);
        setValueOrEmpty("#create_product_form :input[name=code]", item.code);
        setValueOrEmpty("#text-description", item.description);
        setValueOrEmpty("#create_product_form :input[name=id_u_medida]", item.id_unit);
        setValueOrEmpty("#create_product_form :input[name=id_label]", item.id_label);
        setValueOrEmpty("#create_product_form :input[name=price]", item.price);
        $("#create_product_form :input[name=id_u_medida]").trigger('change');
        $("#create_product_form :input[name=id_label]").trigger('change');
        $("#create_product_form :input[name=id_category]").trigger('change');
        $("#create_product_form :input[name=id_subcategory]").trigger('change');
        tableProductDetail(item.content_headers)
        showExistingImages(item.images);
        setValueOrEmpty("#create_product_form :input[name=id_section]", item.id_section);
        $("#create_product_form :input[name=id_section]").trigger('change');
        if (item.id_section) {
          get_categories_by_section(item.id_section, function () {
            setValueOrEmpty("#create_product_form :input[name=id_category]", item.id_category);
            if (item.id_category) {
              get_subcategories_by_categories(item.id_category, function () {
                setValueOrEmpty("#create_product_form :input[name=id_subcategory]", item.id_subcategory);
              });
            }
          });
        }
        $('input[data-campus-id]').val('');
        if (item.stock_by_campus && Array.isArray(item.stock_by_campus)) {
          item.stock_by_campus.forEach((campusStock) => {
            $(`input[data-campus-id="${campusStock.id_campus}"]`).val(campusStock.stock);
          });
        }
      }
    }
  });
});

function create_product(form) {
  let params = new FormData(form);
  $.ajax({
    url: BASE_URL + "Productdetails/create_product_stock_file",
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
        let item = data.data;
        create_inventory(item["id"]);
      }
    }
  })
}

// -- Validate form
$("#create_product_form").validate({
  // --
  submitHandler: function (form) {
    create_product(form);
  },
});

// -- SELECTS --
function get_u_medida() {
  // --
  $.ajax({
    url: BASE_URL + "Products/get_u_medida",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
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
            element.id +
            '">' +
            element.description +
            "</option>";
        });
        // -- Set values for select
        $("#create_product_form :input[name=id_u_medida]").html(html);
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
    success: function (data) {
      // --
      if (data.status === "OK") {
        // --
        var html = '<option value="">Seleccionar</option>';
        // var html = '';
        // --
        data.data.forEach((element) => {
          html +=
            '<option value="' + element.id + '">' + element.name + "</option>";
        });
        // -- Set values for select
        $("#create_product_form :input[name=id_label]").html(html);
      }
    },
  });
}

get_u_medida();
get_labels();
get_products();

// ---------------------------
// CREATE INVENTORY - STOCK
// ---------------------------

function create_inventory(id) {
  const idProduct = id;
  const idSection = $("#select-section-create").val();
  const idCategory = $("#select-category-create").val();
  const idSubcategory = $("#select-subcategory-create").val();
  let params = {
    id_product: idProduct,
    id_section: idSection,
    id_category: idCategory,
    id_subcategory: idSubcategory
  };

  $.ajax({
    url: BASE_URL + "Productdetails/create_inventory",
    type: "POST",
    data: JSON.stringify(params),
    dataType: "json",
    contentType: "application/json",
    processData: false,
    cache: false,
    beforeSend: function () {
      console.log("Cargando...");
    },
    success: function (data) {
      if (data.status === "OK") {
        functions.toast_message(data.type, data.msg, data.status);
      } else {
        console.error("Error en la respuesta del servidor:", data);
      }
    },
    error: function (xhr, status, error) {
      console.error("Error en la solicitud AJAX:", error);
      console.error("Estado:", status);
      console.error("Respuesta del servidor:", xhr.responseText);
    }
  });
}
// --


// --
function get_campus() {
  $.ajax({
    url: BASE_URL + "Campus/get_campus",
    type: "GET",
    dataType: "json",
    contentType: false,
    processData: false,
    cache: false,
    success: function (data) {
      if (data.status === "OK") {
        data.data.forEach((element) => {
          $("#datatable-stock tbody").append(`
            <tr>
              <td>${element.description}</td>
              <td><input type="number" name="stock[]" class="form-control input_stock_row" placeholder="Stock" autofocus data-campus-id="${element.id_campus}" readonly /></td>
            </tr>
          `);
        });
      }
    },
  });
}
get_campus();

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
        $("#create_product_form :input[name=id_section]").html(html);
      }
    },
  });
}

// --
function get_categories_by_section(id_section, callback) {
  let params = { id_section: id_section };
  $.ajax({
    url: BASE_URL + "Subcategories/get_categories_by_section",
    type: "GET",
    data: params,
    dataType: "json",
    contentType: false,
    processData: true,
    cache: false,
    beforeSend: function () {
      $("#create_product_form :input[name=id_category]").empty();
    },
    success: function (data) {
      if (data.status === "OK") {
        var html = '<option value="">Seleccionar</option>';
        data.data.forEach((element) => {
          html += `<option value="${element.id_category}">${element.name}</option>`;
        });
        $("#create_product_form :input[name=id_category]").html(html);
        if (typeof callback === 'function') {
          callback();
        }
      }
    },
  });
}

function get_subcategories_by_categories(id_category, callback) {
  let params = { id_category: id_category };
  $.ajax({
    url: BASE_URL + "Productdetails/get_subcategories_by_categories",
    type: "GET",
    data: params,
    dataType: "json",
    contentType: false,
    processData: true,
    cache: false,
    beforeSend: function () {
      $("#create_product_form :input[name=id_subcategory]").empty();
    },
    success: function (data) {
      if (data.status === "OK") {
        var html = '<option value="">Seleccionar</option>';
        data.data.forEach((element) => {
          html += `<option value="${element.id_subcategory}">${element.name}</option>`;
        });
        $("#create_product_form :input[name=id_subcategory]").html(html);
        if (typeof callback === 'function') {
          callback();
        }
      }
    },
  });
}

$("#create_product_form :input[name=id_section]").on("change", function (e) {
  get_categories_by_section($(this).val());
});

$("#create_product_form :input[name=id_category]").on("change", function (e) {
  get_subcategories_by_categories($(this).val());
});

get_sections();

function tableProductDetail(content_headers) {
  const table_product_detail = document.getElementById('table_product_detail');
  table_product_detail.innerHTML = '';
  positionCounter = 0;
  const headers = content_headers.reduce((acc, header) => {
    if (!acc.includes(header.id_header)) {
      acc.push(header.id_header);
    }
    return acc;
  }, []).sort((a, b) => a - b);

  const tableHTML = `
    <table class="table table-responsive" id="add_products">
      <thead>
        <tr>
          <th>Acciones</th>
          ${headers.map(id => `<th>${getHeaderName(id)}</th>`).join('')}
        </tr>
      </thead>
      <tbody class="product_detail_body">
      </tbody>    
    </table>`;

  table_product_detail.innerHTML = tableHTML;
  const tbody = document.querySelector('.product_detail_body');
  const positions = [...new Set(content_headers.map(item => item.position).filter(position => position !== null))].sort((a, b) => a - b);

  if (positions.length > 0) {
    positions.forEach(position => {
      const row = document.createElement('tr');

      const actionCell = document.createElement('td');
      actionCell.innerHTML = `
        <button class="btn btn-danger" onclick="eliminarFila(this)">X</button>
        <input type="hidden" name="position[]" value="${position}">
      `;
      row.appendChild(actionCell);

      headers.forEach(headerId => {
        const cell = document.createElement('td');
        const item = content_headers.find(h => h.position == position && h.id_header == headerId);

        if (item) {
          cell.innerHTML = `
            <input type="text" class="form-control" placeholder="${getHeaderName(headerId)}" name="content[${position}][]" value="${item.content || ''}">
            <input type="hidden" name="id_header[]" value="${headerId}">
          `;
        } else {
          cell.innerHTML = `
            <input type="text" class="form-control" placeholder="${getHeaderName(headerId)}" name="content[${position}][]">
            <input type="hidden" name="id_header[]" value="${headerId}">
          `;
        }

        row.appendChild(cell);
      });

      tbody.appendChild(row);
      positionCounter = Math.max(positionCounter, position);
    });
  }
}

function getHeaderName(id) {
  switch (parseInt(id)) {
    case 1: return 'Descripción';
    case 2: return 'Especificación';
    case 3: return 'Características';
    default: return `Columna ${id}`;
  }
}

function agregarFila() {
  const table = document.getElementById('add_products').getElementsByTagName('tbody')[0];
  const headers = Array.from(table.parentElement.querySelectorAll('thead th')).slice(1);
  const newRow = table.insertRow(table.rows.length);
  positionCounter++;
  const cell1 = newRow.insertCell(0);
  cell1.innerHTML = `
    <button class="btn btn-danger" onclick="eliminarFila(this)">X</button>
    <input type="hidden" name="position[]" value="${positionCounter}">
  `;
  headers.forEach((header, index) => {
    const cell = newRow.insertCell(index + 1);
    const headerId = index + 1;
    cell.innerHTML = `
      <input type="text" class="form-control" placeholder="${header.textContent}" name="content[${positionCounter}][]">
      <input type="hidden" name="id_header[]" value="${headerId}">
    `;
  });
}

function eliminarFila(btn) {
  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);
}

function guardarDetallesProducto() {
  const id_product = $("#create_product_form :input[name=id_product]").val();
  const table = document.getElementById('add_products');
  const rows = table.getElementsByTagName('tbody')[0].rows;

  let id_headers = [];
  let positions = [];
  let contents = [];

  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    const position = row.querySelector('input[name="position[]"]').value;
    positions.push(position);

    const contentInputs = row.querySelectorAll('input[name^="content["]');
    const rowContents = [];

    contentInputs.forEach((input, index) => {
      rowContents.push(input.value);
      if (i === 0) {
        id_headers.push(input.nextElementSibling.value);
      }
    });
    contents.push(rowContents);
  }

  const data = {
    id_product: id_product,
    id_header: id_headers,
    position: positions,
    content: contents
  };

  $.ajax({
    url: BASE_URL + "Productdetails/create_detail_head",
    type: "POST",
    data: JSON.stringify(data),
    dataType: "json",
    contentType: "application/json",
    processData: false,
    cache: false,
    beforeSend: function () {
      console.log("Cargando...");
    },
    success: function (response) {
      if (response.status === "OK") {
        functions.toast_message(response.type, response.msg, response.status);
      }
    },
  });
}

// ------------------
// FILES IMG
// ------------------
// --
$("#select_image_product").click(function (e) {
  $("#file_images_product").click();
});

$("#dropzone").click(function (e) {
  if (e.target === this) {
    $("#file_images_product").click();
  }
});

$("#file_images_product").on("change", function (e) {
  files = this.files;
  showFiles(files);
});

$("#dropzone").on("dragover", (e) => {
  e.preventDefault();
  $("#dropzone").css("backgroundColor", "rgb(115 103 240 / 7%)");
  $("#dropzone h4").eq(1).html("Suelte aquí sus imágenes para cargarlas.");
});

$("#dropzone").on("dragleave", (e) => {
  e.preventDefault();
  $("#dropzone").css("backgroundColor", "transparent");
  $("#dropzone h4").eq(1).html("Arrastra y suelta  las imágenes aquí.");
});

$("#dropzone").on("drop", (e) => {
  e.preventDefault();
  if (e.originalEvent.dataTransfer) {
    const files = e.originalEvent.dataTransfer.files;
    showFiles(files);
  } else {
    console.log("No data transferred during drop event");
  }
  $("#dropzone").css("backgroundColor", "transparent");
  $("#dropzone h4").eq(1).html("Arrastra y suelta  las imágenes aquí.");
  $("#dropzone_guide").prop("hidden", true);
});

function showFiles(files) {
  if (files.length === undefined) {
    processFile(files);
  } else {
    for (const file of files) {
      processFile(file);
    }
  }
}

function showExistingImages(images) {
  $("#dropzone_img").empty();
  if (images && images.length > 0) {
    images.forEach((image, index) => {
      const id = `Prd${image.id_product}-file${image.id}`;
      const imageHtml = `
        <div id="${id}" class="rounded text-center d-flex flex-column justify-content-center align-items-center gap-1 p-2" style="height: 310px; width: 230px; background-color: #f0f0f0">
          <img src="${image.image_url}" alt="Imagen ${index + 1}" class="rounded" style="height: 180px; aspect-ratio: 1 / 1">
          <div>
            <p class="my-0">Imagen ${index + 1}</p>
          </div>
          <button class="btn btn-sm btn-danger btn-round btn-icon btn_remove_image_product" data-idProduct="${id}" data-imageId="${image.id}">
            Eliminar
          </button>
        </div>
      `;
      $("#dropzone_img").append(imageHtml);
    });
  } 
}

function processFile(file) {
  const idProduct = $("#create_product_form :input[name=id_product]").val();
  const docType = file.type;
  const validExtensions = ["image/jpeg", "image/jpg", "image/png"];
  if (validExtensions.includes(docType)) {
    const fileReader = new FileReader();
    const id = `Prd${idProduct}-file${Math.random().toString(32).substring(7)}`;
    fileReader.onload = (e) => {
      const fileUrl = e.target.result;
      const image = `
        <div id="${id}" class="rounded text-center d-flex flex-column justify-content-center align-items-center gap-1 p-2" style="height: 310px; width: 230px; background-color: #f0f0f0">
          <img src="${fileUrl}" alt="${file.name}" class="rounded" style="height: 180px; aspect-ratio: 1 / 1">
          <div>
            <p class="my-0">${file.name}</p>
            <span><strong>${(file.size / 1024).toFixed(1)}</strong> KB</span>
          </div>
          <button class="btn btn-sm btn-danger btn-round btn-icon btn_remove_image_product" data-idProduct="${id}">
            Eliminar
          </button>
        </div>
      `;
      $("#dropzone_img").append(image);
    
    };
    fileReader.readAsDataURL(file);
  } else {
    functions.toast_message('warning', 'El tipo de archivo no está permitido.', 'WARNING');
  }
}

function uploadFile(file, id, idProduct) {
  const formData = new FormData();
  formData.append('images[]', file);
  formData.append('id_product', idProduct);
  formData.append('image_urls[]', id);

  $.ajax({
    url: BASE_URL + "Productdetails/img_product",
    type: "POST",
    data: formData,
    processData: false,
    contentType: false,
    success: function (response) {
      if (response.status === 'OK') {
        functions.toast_message(response.type, response.msg, response.status);
        if (response.data && response.data.image_url) {
          $(`#${id} img`).attr('src', response.data.image_url);
          if (response.data.id) {
            $(`#${id} .btn_remove_image_product`).attr('data-imageId', response.data.id);
          }
        }
      } else {
        functions.toast_message(response.type, response.msg, response.status);
      }
    },
    error: function () {
      functions.toast_message('error', 'Error en la conexión. Por favor, intente de nuevo.', 'ERROR');
    }
  });
}

$(document).on("click", ".btn_remove_image_product", function (e) {
  const imageProductId = $(e.target).attr("data-idproduct");
  const idProduct = $("#create_product_form :input[name=id_product]").val();
  const imageUrl = $(`#${imageProductId} img`).attr('src');
  const imageId = $(e.target).attr("data-imageId");

  if (imageUrl.startsWith('data:')) {
    $(`#${imageProductId}`).remove();
    if ($("#dropzone_img").find("div").length === 0) {
      $("#dropzone_guide").prop("hidden", false);
    }
  } else {
    $.ajax({
      url: BASE_URL + "Productdetails/delete_img_product",
      type: "POST",
      data: JSON.stringify({
        id_product: idProduct,
        image_url: imageUrl,
        id: imageId
      }),
      contentType: "application/json",
      dataType: "json",
      success: function (response) {
        if (response.status === 'OK') {
          $(`#${imageProductId}`).remove();
          if ($("#dropzone_img").find("div").length === 0) {
            $("#dropzone_guide").prop("hidden", false);
          }
          functions.toast_message(response.type, response.msg, response.status);
        } else {
          functions.toast_message(response.type, response.msg, response.status);
        }
      },
      error: function () {
        functions.toast_message('error', 'Error en la conexión. Por favor, intente de nuevo.', 'ERROR');
      }
    });
  }
});

function saveAllImages() {
  const idProduct = $("#create_product_form :input[name=id_product]").val();
  const images = $("#dropzone_img").find("img");
  if (images.length === 0) {
    functions.toast_message('warning', 'No hay imágenes para guardar.', 'WARNING');
    return;
  }
  
  images.each(function (index) {
    const src = $(this).attr('src');
    const id = $(this).closest('div').attr('id');
    if (src.startsWith('data:')) {
      const file = dataURLtoFile(src, `image_${index}.jpg`);
      uploadFile(file, id, idProduct);
    }
  });
}

function dataURLtoFile(dataurl, filename) {
  var arr = dataurl.split(','),
    mime = arr[0].match(/:(.*?);/)[1],
    bstr = atob(arr[1]),
    n = bstr.length,
    u8arr = new Uint8Array(n);
  while (n--) {
    u8arr[n] = bstr.charCodeAt(n);
  }
  return new File([u8arr], filename, { type: mime });
}


$("#dropzone").append('<button id="save_all_images" class="btn btn-primary mt-3">Guardar todas las imágenes</button>');

$("#save_all_images").on("click", saveAllImages);

