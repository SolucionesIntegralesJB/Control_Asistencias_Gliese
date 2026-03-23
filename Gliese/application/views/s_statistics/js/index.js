/**
 * JavaScript para Estadísticas de Jornadas Laborales
 */

$(document).ready(function() {
    // Cargar datos al iniciar
    loadGeneralStats();
    loadActiveNow();

    // Actualizar practicantes activos cada 30 segundos
    setInterval(loadActiveNow, 30000);

    // Re-inicializar iconos de Feather
    setTimeout(() => {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }
    }, 100);
});

/**
 * Aplicar filtros al hacer clic en Buscar
 */
$(document).on('click', '#btn-aplicar-filtros', function() {
    loadGeneralStats();
    loadPracticanteStats();
});

/**
 * Cargar estadísticas generales
 */
function loadGeneralStats() {
    const fecha_inicio = $('#fecha_inicio').val();
    const fecha_fin = $('#fecha_fin').val();
    const id_grupo = $('#id_grupo').val();

    $.ajax({
        url: BASE_URL + 'S_Statistics/getGeneralStats',
        type: 'GET',
        data: {
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            id_grupo: id_grupo
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;

                $('#stat-practicantes').text(data.total_practicantes || 0);
                $('#stat-horas-normales').text(formatHours(parseFloat(data.total_horas_normales || 0)));
                $('#stat-horas-extras').text(formatHours(parseFloat(data.total_horas_extras || 0)));
                $('#stat-total-horas').text(formatHours(parseFloat(data.total_horas || 0)));
            }
        },
        error: function() {
            console.error('Error al cargar estadísticas generales');
        }
    });
}

/**
 * Cargar practicantes activos ahora
 */
function loadActiveNow() {
    $.ajax({
        url: BASE_URL + 'S_Statistics/getActiveNow',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                let html = '';

                if (data.length === 0) {
                    html = `
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i data-feather="inbox"></i>
                                No hay practicantes trabajando en este momento
                            </td>
                        </tr>
                    `;
                } else {
                    data.forEach(function(item) {
                        const tipo_badge = item.tipo_sesion === 'normal'
                            ? '<span class="badge bg-success">Normal</span>'
                            : '<span class="badge bg-warning">Horas Extras</span>';

                        const horas = Math.floor(item.minutos_trabajados / 60);
                        const minutos = item.minutos_trabajados % 60;
                        const tiempo = `${horas}h ${minutos}m`;

                        html += `
                            <tr>
                                <td><strong>${item.practicante}</strong></td>
                                <td>${item.grupo}</td>
                                <td>${tipo_badge}</td>
                                <td>${formatDateTime(item.start_time)}</td>
                                <td><span class="badge bg-primary">${tiempo}</span></td>
                                <td><small>${item.task_description || '-'}</small></td>
                            </tr>
                        `;
                    });
                }

                $('#tbody-activos').html(html);

                // Re-inicializar iconos
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }
        },
        error: function() {
            console.error('Error al cargar practicantes activos');
        }
    });
}

/**
 * Cargar estadísticas por practicante
 */
function loadPracticanteStats() {
    const fecha_inicio = $('#fecha_inicio').val();
    const fecha_fin = $('#fecha_fin').val();
    const id_grupo = $('#id_grupo').val();

    $.ajax({
        url: BASE_URL + 'S_Statistics/getPracticanteStats',
        type: 'GET',
        data: {
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin,
            id_grupo: id_grupo
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                let html = '';

                if (data.length === 0) {
                    html = `
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                No hay datos para el rango de fechas seleccionado
                            </td>
                        </tr>
                    `;
                } else {
                    data.forEach(function(item) {
                        const porcentaje = parseFloat(item.porcentaje_evidencia || 0).toFixed(0);
                        const porcentaje_class = porcentaje >= 80 ? 'bg-success' : porcentaje >= 50 ? 'bg-warning' : 'bg-danger';

                        html += `
                            <tr>
                                <td><strong>${item.practicante}</strong></td>
                                <td>${item.grupo}</td>
                                <td>${item.dias_trabajados}</td>
                                <td>${formatHours(parseFloat(item.horas_normales || 0))}</td>
                                <td>${formatHours(parseFloat(item.horas_extras || 0))}</td>
                                <td><strong>${formatHours(parseFloat(item.total_horas || 0))}</strong></td>
                                <td>
                                    <span class="badge bg-success">${item.dias_con_evidencia}</span>
                                    <span class="badge bg-danger">${item.dias_sin_evidencia}</span>
                                </td>
                                <td>
                                    <span class="badge ${porcentaje_class}">${porcentaje}%</span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info btn-ver-detalle" data-id="${item.id_user}">
                                        <i data-feather="eye" style="width: 14px; height: 14px;"></i>
                                        Ver
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                }

                $('#tbody-practicantes').html(html);

                // Re-inicializar iconos
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }
        },
        error: function() {
            console.error('Error al cargar estadísticas por practicante');
        }
    });
}

/**
 * Ver detalle de un practicante
 */
$(document).on('click', '.btn-ver-detalle', function() {
    const id_user = $(this).data('id');
    const fecha_inicio = $('#fecha_inicio').val();
    const fecha_fin = $('#fecha_fin').val();

    // Aquí puedes abrir un modal con el detalle
    Swal.fire({
        title: 'Detalle de Jornadas',
        html: '<div class="text-center"><div class="spinner-border" role="status"></div></div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });

    $.ajax({
        url: BASE_URL + 'S_Statistics/getDetailByUser',
        type: 'GET',
        data: {
            id_user: id_user,
            fecha_inicio: fecha_inicio,
            fecha_fin: fecha_fin
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<div class="text-start">';

                // Jornadas Normales
                html += '<h6 class="mb-3 text-primary"><i class="me-1">📋</i> Jornadas Normales</h6>';
                html += '<div class="table-responsive" style="max-height: 350px; overflow-y: auto;">';
                html += '<table class="table table-sm table-bordered">';
                html += '<thead class="table-light"><tr><th style="width: 100px;">Fecha</th><th style="width: 80px;">Horas</th><th>Descripción del trabajo</th><th style="width: 70px;">Evidencia</th></tr></thead>';
                html += '<tbody>';

                response.sessions.forEach(function(s) {
                    const evidencia = s.has_evidence == 1
                        ? '<span class="badge bg-success">Sí</span>'
                        : '<span class="badge bg-danger">No</span>';

                    const descripcion = s.task_description || s.description || '-';

                    html += `
                        <tr>
                            <td class="text-nowrap">${s.session_date}</td>
                            <td class="text-center"><strong>${formatHours(parseFloat(s.total_hours || 0))}</strong></td>
                            <td style="word-wrap: break-word; white-space: normal; max-width: 300px;">${descripcion}</td>
                            <td class="text-center">${evidencia}</td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';

                // Horas Extras
                if (response.overtime && response.overtime.length > 0) {
                    html += '<h6 class="mb-3 mt-4 text-warning"><i class="me-1">⚡</i> Horas Extras</h6>';
                    html += '<div class="table-responsive" style="max-height: 250px; overflow-y: auto;">';
                    html += '<table class="table table-sm table-bordered">';
                    html += '<thead class="table-light"><tr><th style="width: 100px;">Fecha</th><th style="width: 80px;">Horas</th><th>Motivo / Descripción</th></tr></thead>';
                    html += '<tbody>';

                    response.overtime.forEach(function(o) {
                        const motivo = o.reason || o.task_description || '-';

                        html += `
                            <tr>
                                <td class="text-nowrap">${o.session_date}</td>
                                <td class="text-center"><strong>${formatHours(parseFloat(o.total_hours || 0))}</strong></td>
                                <td style="word-wrap: break-word; white-space: normal; max-width: 350px;">${motivo}</td>
                            </tr>
                        `;
                    });

                    html += '</tbody></table></div>';
                }

                html += '</div>';

                Swal.fire({
                    title: 'Detalle de Jornadas',
                    html: html,
                    width: '800px',
                    confirmButtonText: 'Cerrar'
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar el detalle'
            });
        }
    });
});

/**
 * Exportar a Excel
 */
$(document).on('click', '#btn-export-excel', function() {
    const fecha_inicio = $('#fecha_inicio').val();
    const fecha_fin = $('#fecha_fin').val();
    const id_grupo = $('#id_grupo').val();

    window.open(
        BASE_URL + 'S_Statistics/exportExcel?fecha_inicio=' + fecha_inicio +
        '&fecha_fin=' + fecha_fin + '&id_grupo=' + (id_grupo || ''),
        '_blank'
    );
});

/**
 * Formatear fecha y hora
 */
function formatDateTime(datetime) {
    if (!datetime) return '-';
    const date = new Date(datetime);
    return date.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' });
}

/**
 * Formatear horas decimales a formato legible (Xh Ym)
 * Ejemplo: 1.5 → "1h 30m", 2.25 → "2h 15m"
 */
function formatHours(decimalHours) {
    const hours = Math.floor(decimalHours);
    const minutes = Math.round((decimalHours - hours) * 60);
    return `${hours}h ${minutes.toString().padStart(2, '0')}m`;
}

/**
 * Cambiar a tab de practicantes
 */
$(document).on('shown.bs.tab', '#practicantes-tab', function() {
    loadPracticanteStats();
});
