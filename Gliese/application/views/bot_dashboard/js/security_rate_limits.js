// ==================================================
// GLOBAL VARIABLES
// ==================================================

let currentRateLimitPage = 1;
const recordsPerPage = 20;

// ==================================================
// INITIALIZATION
// ==================================================

function init_security_rate_limits() {
  console.log("[Rate Limits] Initializing...");

  // Load data
  load_rate_limit_stats();
  load_rate_limits(1);

  // Event listeners - filters
  $("#filter-rate-status, #filter-rate-severity").on("change", function () {
    load_rate_limits(1);
  });
}

// ==================================================
// RATE LIMITS FUNCTIONS
// ==================================================

// --
function load_rate_limit_stats() {
  $.ajax({
    url: BASE_URL + "Bot_Dashboard/get_rate_limit_stats",
    method: "GET",
    dataType: "json",
    success: function (response) {
      console.log("[Dashboard] Rate limit stats response:", response);

      if (response.status === "OK") {
        const data = response.data;
        console.log("[Dashboard] Rate limit stats data:", data);

        // Update statistics
        $("#stat-rate-total").text(data.total || 0);
        $("#stat-rate-blocked").text(data.currently_blocked || 0);
        $("#stat-rate-unblocked").text(data.unblocked || 0);
        $("#stat-rate-permanent").text(data.permanent || 0);
      }
    },
    error: function (xhr, status, error) {
      console.error("[Dashboard] Error loading rate limit stats:", error);
    },
  });
}

// --
function load_rate_limits(page = 1) {
  currentRateLimitPage = page;
  const offset = (page - 1) * recordsPerPage;

  const status = $("#filter-rate-status").val();
  const severity = $("#filter-rate-severity").val();

  const data = {
    limit: recordsPerPage,
    offset: offset,
  };

  if (status) data.status = status;
  if (severity) data.severity = severity;

  $.ajax({
    url: BASE_URL + "Bot_Dashboard/get_rate_limits",
    method: "POST",
    data: data,
    dataType: "json",
    success: function (response) {
      console.log("[Dashboard] Rate limits response:", response);

      if (response.status === "OK") {
        render_rate_limits(response.data, response.max_blocks || 5);
        update_rate_pagination(response.total);
      } else {
        toastr.error(response.msg || "Error al cargar rate limits", "Error");
      }
    },
    error: function (xhr, status, error) {
      console.error("[Dashboard] Error loading rate limits:", error);
      toastr.error("Error al cargar rate limits", "Error");
    },
  });
}

// --
function render_rate_limits(limits, maxBlocks) {
  const tbody = $("#tbody-rate-limits");
  tbody.empty();

  if (limits.length === 0) {
    tbody.append(
      '<tr><td colspan="6" class="text-center">No se encontraron registros</td></tr>'
    );
    return;
  }

  limits.forEach(function (limit) {
    const isPermanent = limit.is_permanent == 1;
    const isBlocked = isPermanent ||
      (limit.blocked_until && new Date(limit.blocked_until) > new Date());

    // Status badge
    let statusBadge;
    if (isPermanent) {
      statusBadge = '<span class="badge bg-danger">Permanente</span><br><small class="text-muted">N/A</small>';
    } else if (isBlocked) {
      statusBadge = '<span class="badge bg-light-danger">Bloqueado</span><br><small class="text-muted">' + limit.blocked_until + '</small>';
    } else {
      statusBadge = '<span class="badge bg-light-success">Desbloqueado</span>';
    }

    // Severity badge
    const severityBadge = get_severity_badge(limit.severity);

    // Action badges (multiple types)
    const actionTypes = (limit.action_types || '').split(',');
    const actionBadges = actionTypes.map(function(type) {
      return get_action_type_badge(type.trim());
    }).join(' ');

    // Action buttons
    const unlockButton = isBlocked
      ? `<button class="btn btn-sm btn-relief-warning" onclick="clear_rate_limit(${limit.id})" title="Desbloquear">
                   <i data-feather="unlock"></i>
               </button> `
      : '';

    const detailButton = `<button class="btn btn-sm btn-relief-info" onclick='show_rate_limit_detail(${JSON.stringify(limit).replace(/'/g, "&#39;")})' title="Ver detalles">
                   <i data-feather="eye"></i>
               </button>`;

    const row = `
            <tr>
                <td><strong>${limit.identifier_value || '-'}</strong></td>
                <td>${actionBadges}</td>
                <td>
                    <span class="badge bg-light-warning">${limit.alerts_sent_count || 0}</span> enviadas<br>
                    <span class="badge bg-light-danger">${limit.block_count || 0}/${maxBlocks}</span> bloqueos
                </td>
                <td>${severityBadge}</td>
                <td>${statusBadge}</td>
                <td>${unlockButton}${detailButton}</td>
            </tr>
        `;
    tbody.append(row);
  });

  // Reinitialize feather icons
  if (typeof feather !== "undefined") {
    feather.replace();
  }
}

// --
function get_action_type_badge(action_type) {
  const badges = {
    message: '<span class="badge bg-light-primary">Message</span>',
    verification: '<span class="badge bg-light-info">Verification</span>',
    registration: '<span class="badge bg-light-success">Registration</span>',
    ai_query: '<span class="badge bg-light-warning">AI Query</span>',
    http_rate_limit: '<span class="badge bg-light-danger">HTTP Rate Limit</span>',
    rate_limit_exceeded: '<span class="badge bg-light-danger">Rate Limit Exceeded</span>',
  };
  return (
    badges[action_type] ||
    '<span class="badge bg-light-secondary">' + action_type + "</span>"
  );
}

// --
function get_severity_badge(severity) {
  const badges = {
    low: '<span class="badge bg-light-success">Baja</span>',
    medium: '<span class="badge bg-light-warning">Media</span>',
    high: '<span class="badge bg-light-danger">Alta</span>',
    critical: '<span class="badge bg-danger">Crítica</span>',
  };
  return (
    badges[severity] ||
    '<span class="badge bg-light-secondary">' + (severity || 'N/A') + "</span>"
  );
}

// --
function update_rate_pagination(total) {
  const totalPages = Math.ceil(total / recordsPerPage);
  const showing = Math.min(currentRateLimitPage * recordsPerPage, total);

  $("#rate-showing").text(showing);
  $("#rate-total").text(total);

  const pagination = $("#rate-pagination");
  pagination.empty();

  if (totalPages <= 1) return;

  // Previous button
  pagination.append(`
        <li class="page-item ${currentRateLimitPage === 1 ? "disabled" : ""}">
            <a class="page-link" href="#" onclick="load_rate_limits(${
              currentRateLimitPage - 1
            }); return false;">Anterior</a>
        </li>
    `);

  // Page numbers (show max 5 pages)
  let startPage = Math.max(1, currentRateLimitPage - 2);
  let endPage = Math.min(totalPages, startPage + 4);

  for (let i = startPage; i <= endPage; i++) {
    pagination.append(`
            <li class="page-item ${i === currentRateLimitPage ? "active" : ""}">
                <a class="page-link" href="#" onclick="load_rate_limits(${i}); return false;">${i}</a>
            </li>
        `);
  }

  // Next button
  pagination.append(`
        <li class="page-item ${
          currentRateLimitPage === totalPages ? "disabled" : ""
        }">
            <a class="page-link" href="#" onclick="load_rate_limits(${
              currentRateLimitPage + 1
            }); return false;">Siguiente</a>
        </li>
    `);
}

// --
function clear_rate_limit(id) {
  $('#btn-confirm-unlock').off('click').on('click', function () {
    $('#modal-confirm-unlock').modal('hide');

    $.ajax({
      url: BASE_URL + "Bot_Dashboard/clear_rate_limit",
      method: "POST",
      data: { id: id },
      dataType: "json",
      success: function (response) {
        if (response.status === "OK") {
          toastr.success(response.msg, "Exito");
          load_rate_limits(currentRateLimitPage);
          load_rate_limit_stats();
        } else {
          toastr.error(response.msg || "Error al desbloquear", "Error");
        }
      },
      error: function (xhr, status, error) {
        console.error("[Dashboard] Error clearing rate limit:", error);
        toastr.error("Error al desbloquear rate limit", "Error");
      },
    });
  });

  $('#modal-confirm-unlock').modal('show');
}

// --
function show_rate_limit_detail(limit) {
  const patternBadges = {
    brute_force: '<span class="badge bg-danger">Fuerza Bruta</span>',
    ddos: '<span class="badge bg-danger">DDoS</span>',
    spam: '<span class="badge bg-warning">Spam</span>',
    scraping: '<span class="badge bg-info">Scraping</span>',
    slowloris_attack: '<span class="badge bg-danger">Slowloris</span>',
  };
  const patternDisplay = patternBadges[limit.pattern_detected] ||
    (limit.pattern_detected ? '<span class="badge bg-secondary">' + limit.pattern_detected + '</span>' : '<span class="text-muted">N/A</span>');

  let metadataHtml = '<span class="text-muted">N/A</span>';
  if (limit.metadata) {
    try {
      const meta = typeof limit.metadata === 'string' ? JSON.parse(limit.metadata) : limit.metadata;
      metadataHtml = '<pre class="mb-0" style="max-height:200px;overflow:auto;font-size:12px;">' + JSON.stringify(meta, null, 2) + '</pre>';
    } catch (e) {
      metadataHtml = '<span class="text-muted">' + limit.metadata + '</span>';
    }
  }

  const content = `
    <div class="row">
      <div class="col-md-6 mb-1">
        <strong>Identificador:</strong><br>${limit.identifier_value || '-'}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Tipo de Acción:</strong><br>${limit.action_types || '-'}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Primer Intento:</strong><br>${limit.first_attempt || '-'}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Último Intento:</strong><br>${limit.last_attempt || '-'}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Bloqueado Hasta:</strong><br>${limit.blocked_until || 'N/A'}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Permanente:</strong><br>${limit.is_permanent == 1 ? 'Sí' : 'No'}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Patrón Detectado:</strong><br>${patternDisplay}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Severidad:</strong><br>${get_severity_badge(limit.severity)}
      </div>
      <div class="col-12 mb-1">
        <strong>Metadata:</strong><br>${metadataHtml}
      </div>
    </div>
  `;

  $('#modal-rate-limit-detail .modal-body').html(content);
  $('#modal-rate-limit-detail').modal('show');
}

