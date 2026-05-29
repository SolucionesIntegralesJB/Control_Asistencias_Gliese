// -- Functions

// -- Format date to DD/MM/YYYY using native JavaScript
function formatDate(dateString) {
  if (!dateString) return '-';
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

// -- Format datetime to DD/MM/YYYY HH:mm:ss using native JavaScript
function formatDateTime(dateTimeString) {
  if (!dateTimeString) return '-';
  const date = new Date(dateTimeString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');
  const seconds = String(date.getSeconds()).padStart(2, '0');
  return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
}

// --
function destroy_datatable() {
  // --
  $("#datatable-attendance").dataTable().fnDestroy();
}

// --
function refresh_datatable() {
  // --
  $("#datatable-attendance").DataTable().ajax.reload();
}

// --
function load_datatable() {
  // --
  destroy_datatable();
  // --
  let dataTable = $("#datatable-attendance").DataTable({
    // --
    ajax: {
      url: BASE_URL + "Attendance/get_attendance_list",
      data: function (d) {
        // -- Add filters
        d.employee_id = $("#filter_employee").val();
        d.start_date = $("#filter_start_date").val();
        d.end_date = $("#filter_end_date").val();
        d.status = $("#filter_status").val();
        d.campus_id = $("#filter_campus").val();
      },
      cache: false,
      dataSrc: function (json) {
        // -- Handle custom response structure
        console.log("DEBUG: DataTables received JSON:", json);
        if (json.status === "OK") {
          return json.data;
        } else {
          console.error("DEBUG: Error in response:", json);
          return [];
        }
      },
    },
    columns: [
      { data: "employee_name" },
      {
        data: "shift_date",
        render: function(data) {
          return formatDate(data);
        }
      },
      {
        data: "job_role_name",
        render: function(data) {
          return data || '-';
        }
      },
      {
        data: "campus_name",
        render: function(data) {
          return data || '-';
        }
      },
      {
        data: "work_description",
        render: function(data) {
          if (!data) return '-';
          // Truncate if longer than 50 characters
          return data.length > 50 ? data.substring(0, 50) + '...' : data;
        }
      },
      {
        data: "actual_start",
        render: function(data) {
          return data ? data.substring(0, 5) : '-';
        }
      },
      {
        data: "actual_end",
        render: function(data) {
          return data ? data.substring(0, 5) : '-';
        }
      },
      {
        data: "regular_hours",
        render: function(data) {
          return data ? parseFloat(data).toFixed(2) + 'h' : '-';
        }
      },
      {
        data: "overtime_hours",
        render: function(data) {
          return data ? parseFloat(data).toFixed(2) + 'h' : '-';
        }
      },
      {
        data: "status",
        render: function(data) {
          let badgeClass = '';
          let statusText = '';
          switch(data) {
            case 'pending':
              badgeClass = 'badge-light-warning';
              statusText = 'Pendiente';
              break;
            case 'in_progress':
              badgeClass = 'badge-light-info';
              statusText = 'En Progreso';
              break;
            case 'completed':
              badgeClass = 'badge-light-success';
              statusText = 'Completado';
              break;
            case 'cancelled':
              badgeClass = 'badge-light-danger';
              statusText = 'Cancelado';
              break;
            default:
              badgeClass = 'badge-light-secondary';
              statusText = data;
          }
          return '<span class="badge ' + badgeClass + '">' + statusText + '</span>';
        }
      },
      {
        data: "total_payment",
        render: function(data) {
          return data ? 'S/ ' + parseFloat(data).toFixed(2) : '-';
        }
      },
      {
        class: "text-center",
        render: function (data, type, row, meta) {
          // --
          return (
            '<button class="btn btn-sm btn-info btn-round btn-icon btn_detail" data-process-key="' +
            row.id +
            '">' +
            feather.icons["eye"].toSvg({ class: "font-small-4" }) +
            "</button>"
          );
        },
      },
    ],
    order: [[1, "desc"]],
    dom: functions.head_datatable(),
    buttons: functions.custom_buttons_datatable([0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10], null), // -- Columnas para exportar (acciones excluida)
    language: {
      url: BASE_URL + "public/assets/json/languaje-es.json",
    },
  });

  // --
  dataTable.on("xhr", function () {
    // --
    var json = dataTable.ajax.json();
    console.log("DEBUG: DataTables xhr event:", json);
    if (json) {
      functions.toast_message(json.type, json.msg, json.status);
    }
  });
}

// --
function load_employees() {
  // --
  $.ajax({
    url: BASE_URL + "Attendance/get_employees_list",
    type: "GET",
    dataType: "json",
    cache: false,
    success: function (data) {
      // --
      if (data.status === "OK") {
        // --
        var html = '<option value="">Todos</option>';
        // --
        data.data.forEach((element) => {
          html +=
            '<option value="' +
            element.id +
            '">' +
            element.name +
            (element.position ? ' - ' + element.position : '') +
            "</option>";
        });
        // -- Set values for select
        $("#filter_employee").html(html);
      }
    },
  });
}

// --
function load_campus() {
  // --
  $.ajax({
    url: BASE_URL + "Attendance/get_campus_list",
    type: "GET",
    dataType: "json",
    cache: false,
    success: function (data) {
      // --
      if (data.status === "OK") {
        // --
        var html = '<option value="">Todas</option>';
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
        $("#filter_campus").html(html);
      }
    },
  });
}

// --
function load_shift_detail(shiftId) {
  // --
  $.ajax({
    url: BASE_URL + "Attendance/get_attendance_detail",
    type: "GET",
    data: { id: shiftId },
    dataType: "json",
    cache: false,
    success: function (data) {
      // --
      if (data.status === "OK") {
        // -- Populate general info
        $("#detail_employee_name").text(data.data.employee_name || '-');
        $("#detail_shift_date").text(formatDate(data.data.shift_date));
        
        // -- Status badge
        let statusBadge = '';
        switch(data.data.status) {
          case 'pending':
            statusBadge = '<span class="badge bg-light-warning text-warning">Pendiente</span>';
            break;
          case 'in_progress':
            statusBadge = '<span class="badge bg-light-info text-info">En Progreso</span>';
            break;
          case 'completed':
            statusBadge = '<span class="badge bg-light-success text-success">Completado</span>';
            break;
          case 'cancelled':
            statusBadge = '<span class="badge bg-light-danger text-danger">Cancelado</span>';
            break;
          default:
            statusBadge = '<span class="badge bg-light-secondary text-secondary">' + data.data.status + '</span>';
        }
        $("#detail_status").html(statusBadge);

        $("#detail_job_role").text(data.data.job_role || '-');
        $("#detail_campus").text(data.data.campus_name || '-');
        $("#detail_actual_start").text(data.data.actual_start ? data.data.actual_start.substring(0, 5) : '-');
        $("#detail_actual_end").text(data.data.actual_end ? data.data.actual_end.substring(0, 5) : '-');
        $("#detail_work_description").text(data.data.work_description || '-');
        
        // -- Populate hours summary
        $("#detail_regular_hours").text(parseFloat(data.data.regular_hours || 0).toFixed(2) + 'h');
        $("#detail_overtime_hours").text(parseFloat(data.data.overtime_hours || 0).toFixed(2) + 'h');
        $("#detail_break_duration").text((data.data.break_duration || 0) + ' min');
        
        // -- Populate financial summary
        $("#detail_hourly_rate").text('S/ ' + parseFloat(data.data.hourly_rate || 0).toFixed(2));
        $("#detail_overtime_rate").text('S/ ' + parseFloat(data.data.overtime_rate || 0).toFixed(2));
        $("#detail_regular_payment").text('S/ ' + parseFloat(data.data.regular_payment || 0).toFixed(2));
        $("#detail_overtime_payment").text('S/ ' + parseFloat(data.data.overtime_payment || 0).toFixed(2));
        $("#detail_total_payment").text('S/ ' + parseFloat(data.data.total_payment || 0).toFixed(2));
        
        // -- Populate edit form
        $("#edit_shift_id").val(shiftId);
        $("#edit_actual_start").val(data.data.actual_start ? data.data.actual_start.substring(0, 5) : '');
        $("#edit_actual_end").val(data.data.actual_end ? data.data.actual_end.substring(0, 5) : '');
        $("#edit_break_start").val(data.data.break_start ? data.data.break_start.substring(0, 5) : '');
        $("#edit_break_end").val(data.data.break_end ? data.data.break_end.substring(0, 5) : '');
        $("#edit_hourly_rate").val(data.data.hourly_rate || '');
        $("#edit_overtime_rate").val(data.data.overtime_rate || '');
        
        // -- Load timeline events
        load_shift_events(shiftId);
        
        // -- Show modal
        $("#detail_modal").modal("show");
      } else {
        functions.toast_message(data.type, data.msg, data.status);
      }
    },
  });
}

// --
function load_shift_events(shiftId) {
  // --
  $.ajax({
    url: BASE_URL + "Attendance/get_shift_events",
    type: "GET",
    data: { shift_id: shiftId },
    dataType: "json",
    cache: false,
    success: function (data) {
      // --
      let html = '';

      if (data.status === "OK" && data.data.length > 0) {
        // -- Generate timeline items
        data.data.forEach((event) => {
          let eventTitle = '';
          let eventDescription = '';
          let itemClass = '';

          switch(event.event_type) {
            case 'shift_start':
              eventTitle = 'Inicio de Turno';
              eventDescription = 'El empleado inició su turno';
              itemClass = 'shift_start';
              break;
            case 'break_start':
              eventTitle = 'Inicio de Break';
              eventDescription = 'El empleado inició su descanso';
              itemClass = 'break_start';
              break;
            case 'break_end':
              eventTitle = 'Fin de Break';
              eventDescription = 'El empleado terminó su descanso';
              itemClass = 'break_end';
              break;
            case 'shift_end':
              eventTitle = 'Fin de Turno';
              eventDescription = 'El empleado terminó su turno';
              itemClass = 'shift_end';
              break;
            case 'manual_edit':
              eventTitle = 'Edición Manual';
              eventDescription = 'Se realizó una edición manual del turno';
              itemClass = 'manual_edit';
              break;
            case 'rate_change':
              eventTitle = 'Cálculo de Pagos';
              eventDescription = 'Se calcularon los pagos del turno';
              itemClass = 'rate_change';
              break;
            default:
              eventTitle = event.event_type;
              eventDescription = 'Evento registrado';
              itemClass = '';
          }

          let eventTime = formatDateTime(event.event_time);

          html += `
            <div class="timeline-item ${itemClass}">
              <div class="timeline-content">
                <div class="timeline-time">${eventTime}</div>
                <div class="timeline-title">${eventTitle}</div>
                <div class="timeline-description">${eventDescription}</div>
              </div>
            </div>
          `;
        });
      } else {
        html = '<div class="text-center text-muted py-4">No hay eventos registrados</div>';
      }

      $("#timeline_container").html(html);
    },
  });
}

// --
function update_shift() {
  // --
  let formData = $("#edit_shift_form").serialize();
  
  $.ajax({
    url: BASE_URL + "Attendance/update_shift",
    type: "POST",
    data: formData,
    dataType: "json",
    cache: false,
    success: function (data) {
      // --
      functions.toast_message(data.type, data.msg, data.status);
      // --
      if (data.status === "OK") {
        // -- Refresh detail
        let shiftId = $("#edit_shift_id").val();
        load_shift_detail(shiftId);
        // -- Refresh datatable
        refresh_datatable();
      }
    },
  });
}

// -- Document Ready
$(document).ready(function () {
  // --
  load_datatable();
  load_employees();
  load_campus();
  
  // -- Initialize select2
  $(".select2").select2({
    dropdownParent: $("#attendance"),
    width: "100%",
  });
  
  // -- Filter button click
  $("#btn_filter").on("click", function () {
    refresh_datatable();
  });
  
  // -- Detail button click
  $(document).on("click", ".btn_detail", function () {
    // --
    let shiftId = $(this).data("process-key");
    load_shift_detail(shiftId);
  });
  
  // -- Edit shift form submit
  $("#edit_shift_form").on("submit", function (e) {
    e.preventDefault();
    update_shift();
  });
});
