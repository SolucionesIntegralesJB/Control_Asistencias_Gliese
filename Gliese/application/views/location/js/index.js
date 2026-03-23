// -- Events

// --
$("#login-with-location").click(function () {
  login();
});

// --
$("#login-with-location").keypress(function (e) {
  // --
  let code = e.keyCode ? e.keyCode : e.which;
  if (code === 13) {
    login();
  }
});

// -- Functions
// --
function login() {
  // --
  let location = $("#select-location").val();
  // --
  if (location === 0) {
    // -- https://pixinvent.com/demo/vuexy-html-bootstrap-admin-template/documentation/documentation-extensions.html#toastr
    // --
    toastr["warning"]("👋 Es necesario seleccionar una sucursal.", "Ups!", {
      closeButton: true,
      tapToDismiss: false,
    });
  } else {
    // --
    let params = {
      id_location: location,
    };
    // --
    $.ajax({
      url: BASE_URL + "Login/get_active",
      type: "POST",
      data: params,
      dataType: "json",
      cache: false,
      success: function (data) {
        // --
        if (data.status === "OK") {
          // -- Redirect
          window.location.replace(BASE_URL + "Dashboards");
        } else {
          // --
          toastr["error"](data.msg, "Ups!", {
            closeButton: true,
            tapToDismiss: false,
          });
        }
      },
    });
  }
}

// LOCATIONS BY USER
function locations() {
  console.log("Cargando...");
  // --
  $.ajax({
    url: BASE_URL + "Location/get_locations_by_user",
    type: "GET",
    dataType: "json",
    cache: false,
    success: function (data) {
      // --
      if (data.status === "OK") {
        //
        campus = data.data["campus"];
        console.log(campus);
        var html = '<option value="">Seleccionar</option>';
        // var html = '';
        // --
        campus.forEach((element) => {
          html +=
            '<option value="' +
            element.id +
            '">' +
            element.description +
            "</option>";
        });
        // -- Set values for select
        $("#select-location").html(html);
      } else {
        // --
        toastr["error"](data.msg, "Ups!", {
          closeButton: true,
          tapToDismiss: false,
        });
      }
    },
  });
}

locations();
