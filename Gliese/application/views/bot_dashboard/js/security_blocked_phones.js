// ===========================================================================
// BOT DASHBOARD - SECURITY BLOCKED PHONES
// ===========================================================================

let currentBlockedPage = 1;
const blockedPerPage = 20;
let phoneToUnblock = null;
let blockTypeToUnblock = null;

function init_security_blocked_phones() {
  console.log("[Blocked Phones] Initializing...");

  // Load data
  loadBlockedPhonesStats();
  loadBlockedPhonesTable(1);

  // Event listeners - filters
  $("#filter-blocked-status, #filter-blocked-type").on("change", function () {
    loadBlockedPhonesTable(1);
  });
}

// ==================================================
// STATISTICS
// ==================================================

function loadBlockedPhonesStats() {
  $.ajax({
    url: BASE_URL + "Bot_Dashboard/get_blocked_phones_stats",
    method: "GET",
    dataType: "json",
    success: function (data) {
      if (data.status === "OK") {
        $("#stat-blocked-total").text(data.result.total || 0);
        $("#stat-blocked-active").text(data.result.active || 0);
        $("#stat-blocked-inactive").text(data.result.inactive || 0);
        $("#stat-blocked-permanent").text(data.result.permanent || 0);
      }
    },
    error: function () {
      console.error("[Blocked Phones] Error loading stats");
    },
  });
}

// ==================================================
// TABLE
// ==================================================

function loadBlockedPhonesTable(page) {
  if (page === undefined) page = 1;
  currentBlockedPage = page;
  const offset = (page - 1) * blockedPerPage;

  const status = $("#filter-blocked-status").val();
  const block_type = $("#filter-blocked-type").val();

  const data = {
    limit: blockedPerPage,
    offset: offset,
  };

  if (status) data.status = status;
  if (block_type) data.block_type = block_type;

  $.ajax({
    url: BASE_URL + "Bot_Dashboard/get_blocked_phones",
    method: "POST",
    data: data,
    dataType: "json",
    success: function (response) {
      console.log("[Blocked Phones] Response:", response);

      if (response.status === "OK") {
        renderBlockedPhones(response.result);
        updateBlockedPagination(response.total);
      } else {
        toastr.error(response.msg || "Error al cargar teléfonos", "Error");
      }
    },
    error: function (xhr, status, error) {
      console.error("[Blocked Phones] Error loading table:", error);
      toastr.error("Error al cargar teléfonos bloqueados", "Error");
    },
  });
}

// --
function renderBlockedPhones(phones) {
  const tbody = $("#tbody-blocked-phones");
  tbody.empty();

  if (phones.length === 0) {
    tbody.append(
      '<tr><td colspan="6" class="text-center">No se encontraron registros</td></tr>'
    );
    return;
  }

  phones.forEach(function (phone) {
    const isActive = phone.is_active == 1;
    const isPermanent = phone.permanent == 1;

    // Status badge
    let statusBadge;
    if (isPermanent) {
      statusBadge =
        '<span class="badge bg-danger">Permanente</span><br><small class="text-muted">N/A</small>';
    } else if (isActive) {
      statusBadge =
        '<span class="badge bg-light-danger">Bloqueado</span><br><small class="text-muted">' +
        (phone.expires_at || "") +
        "</small>";
    } else {
      statusBadge = '<span class="badge bg-light-success">Desbloqueado</span>';
    }

    // Type badge
    const typeBadge = getBlockTypeBadge(phone.block_type);

    // Action buttons
    const unlockButton = isActive
      ? `<button class="btn btn-sm btn-relief-warning" onclick="showUnblockPhoneModal('${phone.phone}', '${phone.block_type}')" title="Desbloquear">
                   <i data-feather="unlock"></i>
               </button> `
      : "";

    const detailButton = `<button class="btn btn-sm btn-relief-info" onclick='showPhoneDetail(${JSON.stringify(phone).replace(/'/g, "&#39;")})' title="Ver detalles">
                   <i data-feather="eye"></i>
               </button>`;

    const row = `
            <tr>
                <td><strong>${phone.phone || "-"}</strong></td>
                <td><small>${phone.reason || "N/A"}</small></td>
                <td>${typeBadge}</td>
                <td>
                    <span class="badge bg-light-danger">${phone.block_count || 0}</span> bloqueos<br>
                    <span class="badge bg-light-success">${phone.unblock_attempts || 0}</span> desbloqueos
                </td>
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
function getBlockTypeBadge(block_type) {
  const badges = {
    message: '<span class="badge bg-light-primary">Message</span>',
    verification: '<span class="badge bg-light-info">Verification</span>',
  };
  return (
    badges[block_type] ||
    '<span class="badge bg-light-secondary">' + (block_type || "N/A") + "</span>"
  );
}

// --
function updateBlockedPagination(total) {
  const totalPages = Math.ceil(total / blockedPerPage);
  const showing = Math.min(currentBlockedPage * blockedPerPage, total);

  $("#blocked-showing").text(showing);
  $("#blocked-total").text(total);

  const pagination = $("#blocked-pagination");
  pagination.empty();

  if (totalPages <= 1) return;

  // Previous button
  pagination.append(`
        <li class="page-item ${currentBlockedPage === 1 ? "disabled" : ""}">
            <a class="page-link" href="#" onclick="loadBlockedPhonesTable(${
              currentBlockedPage - 1
            }); return false;">Anterior</a>
        </li>
    `);

  // Page numbers (show max 5 pages)
  let startPage = Math.max(1, currentBlockedPage - 2);
  let endPage = Math.min(totalPages, startPage + 4);

  for (let i = startPage; i <= endPage; i++) {
    pagination.append(`
            <li class="page-item ${i === currentBlockedPage ? "active" : ""}">
                <a class="page-link" href="#" onclick="loadBlockedPhonesTable(${i}); return false;">${i}</a>
            </li>
        `);
  }

  // Next button
  pagination.append(`
        <li class="page-item ${
          currentBlockedPage === totalPages ? "disabled" : ""
        }">
            <a class="page-link" href="#" onclick="loadBlockedPhonesTable(${
              currentBlockedPage + 1
            }); return false;">Siguiente</a>
        </li>
    `);
}

// ==================================================
// MODALS & ACTIONS
// ==================================================

function showUnblockPhoneModal(phone, blockType) {
  phoneToUnblock = phone;
  blockTypeToUnblock = blockType;
  $("#unblock-phone-number").text(phone);

  $("#btn-confirm-unblock-phone")
    .off("click")
    .on("click", function () {
      unblockPhone();
    });

  $("#unblock-phone-modal").modal("show");
}

function unblockPhone() {
  if (!phoneToUnblock) return;

  $("#unblock-phone-modal").modal("hide");

  $.ajax({
    url: BASE_URL + "Bot_Dashboard/unblock_phone",
    method: "POST",
    data: {
      phone: phoneToUnblock,
      block_type: blockTypeToUnblock,
    },
    dataType: "json",
    success: function (data) {
      if (data.status === "OK") {
        toastr.success(data.msg, "Éxito");
        loadBlockedPhonesTable(currentBlockedPage);
        loadBlockedPhonesStats();
      } else {
        toastr.error(data.msg || "Error al desbloquear", "Error");
      }
    },
    error: function () {
      toastr.error("Error al desbloquear teléfono", "Error");
    },
  });
}

// --
function showPhoneDetail(phone) {
  const isPermanent = phone.permanent == 1;
  const isActive = phone.is_active == 1;
  const autoBlocked = phone.auto_blocked == 1;

  const statusBadge = isPermanent
    ? '<span class="badge bg-danger">Permanente</span>'
    : isActive
    ? '<span class="badge bg-light-danger">Bloqueado</span>'
    : '<span class="badge bg-light-success">Desbloqueado</span>';

  const content = `
    <div class="row">
      <div class="col-md-6 mb-1">
        <strong>Teléfono:</strong><br>${phone.phone || "-"}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Tipo de Bloqueo:</strong><br>${getBlockTypeBadge(phone.block_type)}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Motivo:</strong><br>${phone.reason || "N/A"}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Bloqueado por:</strong><br>${phone.blocked_by || "N/A"}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Fecha de Bloqueo:</strong><br>${phone.blocked_at || "N/A"}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Expira:</strong><br>${isPermanent ? "Nunca" : phone.expires_at || "N/A"}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Estado:</strong><br>${statusBadge}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Auto-bloqueado:</strong><br>${autoBlocked ? "Sí" : "No"}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Bloqueos:</strong><br><span class="badge bg-light-danger">${phone.block_count || 0}</span>
      </div>
      <div class="col-md-6 mb-1">
        <strong>Desbloqueos:</strong><br><span class="badge bg-light-success">${phone.unblock_attempts || 0}</span>
      </div>
      <div class="col-md-6 mb-1">
        <strong>Intentos Fallidos:</strong><br>${phone.failed_attempts || 0}
      </div>
      <div class="col-md-6 mb-1">
        <strong>Códigos Enviados:</strong><br>${phone.codes_sent || 0}
      </div>
    </div>
  `;

  $("#modal-phone-detail .modal-body").html(content);
  $("#modal-phone-detail").modal("show");
}
