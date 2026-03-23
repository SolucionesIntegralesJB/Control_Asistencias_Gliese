function get_company() {
    $.ajax({
        url: BASE_URL + "Company/get_company",
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
            let item = data.data;
            $("#create_company_form :input[name=id_company]").val(item.id);
            $("#create_company_form :input[name=razon_social]").val(item.business_name);
            $("#create_company_form :input[name=nombre_comercial]").val(item.company_name);
            $("#create_company_form :input[name=ruc]").val(item.ruc);
            $("#create_company_form :input[name=telefono]").val(item.phone);
            $("#create_company_form :input[name=email]").val(item.email);
            $("#create_company_form :input[name=direccion]").val(item.address);
            $("#create_company_form :input[name=distrito]").val(item.district);
            $("#create_company_form :input[name=provincia]").val(item.province);
            $("#create_company_form :input[name=departamento]").val(item.department);
            $("#create_company_form :input[name=codigo_postal]").val(item.postal_code);
            $("#create_company_form :input[name=ubigeo]").val(item.ubigeo);
            $("#create_company_form :input[name=pais]").val(item.country);
            $("#create_company_form :input[name=web]").val(item.web);
            $("#create_company_form :input[name=fecha_autorizacion]").val(item.start_date);
            $("#create_company_form :input[name=direccion_secundaria]").val(item.address2);
            $("#create_company_form :input[name=publicidad]").val(item.industry);
            $("#logo_preview").attr("src", item.logo).show();
        }
    });
}

function get_token() {
    $.ajax({
        url: BASE_URL + "Company/get_config",
        type: "GET",
        dataType: "json",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function () {
            console.log("Cargando...");
        },
        success: function (data) {
            let item = data.data;  
            $("#system_config_form :input[name=token]").val(item.token);
            $("#system_config_form :input[name=host]").val(item.host);
            $("#system_config_form :input[name=email]").val(item.email);
            $("#system_config_form :input[name=password]").val(item.password);
        }
    });
}

function get_sunat() {
    $.ajax({
        url: BASE_URL + "Company/get_sunat",
        type: "GET",
        dataType: "json",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function () {
            console.log("Cargando...");
        },
        success: function (data) {
            let item = data.data;
            $("#create_config_modal :input[name=id_config]").val(item.id);
            $("#create_config_modal :input[name=usuario_sunat]").val(item.user);
            $("#create_config_modal :input[name=contrasena_sunat]").val(item.password);
            $("#create_config_modal :input[name=contrasena_certificado]").val(item.cert_password);
            $("#create_config_modal :input[name=modo_emision]").val(item.sunat_endpoint);

            if (item.certificate) {
                let fileName = item.certificate.split('/').pop();
                $("#certificado_actual").text(fileName);
                $("#certificado_info").text("Certificado actual: " + fileName);
                $("#certificado").removeAttr("required");
            } else {
                $("#certificado_actual").text("No hay certificado");
                $("#certificado_info").text("Por favor, seleccione un certificado.");
                $("#certificado").attr("required", "required");
            }
        }
    });
}

$("#certificado").on("change", function () {
    if (this.files && this.files[0]) {
        $("#certificado_actual").text(this.files[0].name);
        $("#certificado_info").text("Nuevo certificado seleccionado: " + this.files[0].name);
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const passwordFields = document.querySelectorAll('input[type="password"]');
    
    passwordFields.forEach(field => {
        const toggleButton = document.createElement('button');
        toggleButton.className = 'btn btn-outline-secondary toggle-password';
        toggleButton.type = 'button';
        toggleButton.innerHTML = '<i class="far fa-eye"></i>';
        
        const inputGroup = document.createElement('div');
        inputGroup.className = 'input-group';
        
        field.parentNode.insertBefore(inputGroup, field);
        inputGroup.appendChild(field);
        inputGroup.appendChild(toggleButton);
        
        toggleButton.addEventListener('click', function() {
            const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
            field.setAttribute('type', type);
            
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
});

function create_config(form) {
    // --
    $("#btn_create_config").prop("disabled", true);
    // --
    let params = new FormData(form);
    // --
    $.ajax({
        url: BASE_URL + "Company/update_company",
        type: "POST",
        data: params,
        dataType: "json",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: function () {
            console.log("Enviando solicitud...");
        },
        success: function (data) {
            functions.toast_message(data.type, data.msg, data.status);
            if (data.status === 'OK' && data.data && data.data.logo_url) {
                if (data.data.logo_url.trim() !== "") {
                    if (data.data.logo_url !== "No se actualizó el logo") {
                        $("#logo_preview").attr("src", data.data.logo_url);
                    } else {
                        // $("#logo_preview").attr("src", data.data.logo_url); 
                    }
                }
            }
        },
    });
}

$("#create_company_form").validate({
    // --
    submitHandler: function (form) {
        create_config(form);
    },
});

function sunat_config(form) {
    const $saveButton = $("#save_config_sunat");
    $saveButton.prop("disabled", true);
    const params = new FormData(form);
    $.ajax({
        url: BASE_URL + "Company/create_config",
        type: "POST",
        data: params,
        dataType: "json",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: () => console.log("Enviando solicitud..."),
        success: function (data) {
            functions.toast_message(data.type, data.msg, data.status);
        },
        complete: () => $saveButton.prop("disabled", false)
    });
}

function system_config(form) {
    const $saveButton = $("#save_config");
    $saveButton.prop("disabled", true);
    const params = new FormData(form);
    $.ajax({
        url: BASE_URL + "Company/update_token",
        type: "POST",
        data: params,
        dataType: "json",
        contentType: false,
        processData: false,
        cache: false,
        beforeSend: () => console.log("Enviando solicitud..."),
        success: function (data) {
            functions.toast_message(data.type, data.msg, data.status);
        },
        complete: () => $saveButton.prop("disabled", false)
    });
}

$("#system_config_form").on("submit", function (e) {
    e.preventDefault();
    system_config(this);
});

$("#save_config").on("click", function () {
    $("#system_config_form").submit();
});

// Evento submit para el formulario
$("#sunat_config_form").on("submit", function (e) {
    e.preventDefault();
    sunat_config(this);
});

// Evento click para el botón de guardar (por si acaso)
$("#save_config_sunat").on("click", function () {
    $("#sunat_config_form").submit();
});

get_company();
get_sunat();
get_token();