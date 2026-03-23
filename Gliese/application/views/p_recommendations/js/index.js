/**
 * ========================================
 * JAVASCRIPT: RECOMENDACIONES (index.js)
 * ========================================
 * 
 * DESCRIPCIÓN:
 * Este archivo gestiona toda la lógica del frontend del módulo de Recomendaciones.
 * Implementa un sistema de tarjetas (cards) en lugar de tabla para mostrar las recomendaciones.
 * 
 * FUNCIONALIDADES:
 * - Cargar y renderizar recomendaciones como tarjetas
 * - Ver detalle completo de una recomendación en modal
 * - Crear nuevas recomendaciones (solo admin)
 * - Editar recomendaciones existentes (solo admin)
 * - Eliminar recomendaciones con confirmación (solo admin)
 * - Control de visibilidad de botones según rol de usuario
 * 
 * APIS CONSUMIDAS:
 * - GET /P_Recommendations/get_recomendaciones - Listar todas
 * - GET /P_Recommendations/get_recomendacion_detail?id=X - Detalle
 * - POST /P_Recommendations/crear_recomendacion - Crear
 * - PUT /P_Recommendations/editar_recomendacion - Actualizar
 * - DELETE /P_Recommendations/eliminar_recomendacion - Eliminar
 * 
 * DEPENDENCIAS:
 * - jQuery
 * - Bootstrap 5 (modals)
 * - SweetAlert2 (confirmaciones)
 * - Feather Icons (iconos)
 * - functions.toast_message() (notificaciones)
 * 
 * AUTOR: Sistema Gliese
 * FECHA CREACIÓN: Octubre 2025
 * ========================================
 */

/**
 * FUNCIÓN: RENDER_RECOMENDACIONES_CARDS
 * 
 * DESCRIPCIÓN:
 * Renderiza las recomendaciones como tarjetas (cards) en el DOM.
 * Muestra botones de edición y eliminación solo si el usuario es administrador.
 * 
 * PARÁMETROS:
 * @param {Array} items - Array de recomendaciones a renderizar
 * @param {Boolean} isAdmin - Flag que indica si el usuario es administrador
 * 
 * ESTRUCTURA DE CADA ITEM:
 * {
 *   id: 1,
 *   titulo: "Título de la recomendación",
 *   contenido: "Descripción completa...",
 *   autor: "Admin",
 *   fecha_creacion: "31/10/2025"
 * }
 * 
 * COMPORTAMIENTO:
 * - Si items está vacío, muestra mensaje "No hay recomendaciones"
 * - Crea una tarjeta Bootstrap por cada recomendación
 * - Trunca el contenido a 200 caracteres en la vista previa
 * - Añade botones de acción según el rol del usuario:
 *   * Ver (todos los usuarios)
 *   * Editar (solo administradores)
 *   * Eliminar (solo administradores)
 * 
 * LAYOUT:
 * - Grid responsive: col-12 col-sm-6 col-lg-4
 * - Móvil: 1 columna
 * - Tablet: 2 columnas
 * - Desktop: 3 columnas
 * 
 * @return void
 */
function render_recomendaciones_cards(items, isAdmin) {
  const container = $('#recomendaciones-cards');
  container.empty();

  if (!Array.isArray(items) || items.length === 0) {
    $('#recomendaciones-empty').show();
    return;
  }

  $('#recomendaciones-empty').hide();

  items.forEach(function(row){
    // Aceptar diferentes formas de la API (fallbacks a campos en inglés)
    const titulo = row.titulo || row.title || '-';
    const contenidoRaw = row.contenido || row.description || row.descripcion || '';
    const contenidoTrunc = contenidoRaw.length > 100 ? contenidoRaw.substring(0, 100) + '...' : contenidoRaw;
    // Escapar HTML para evitar que una descripción con etiquetas rompa la tarjeta
    const contenido = $('<div/>').text(contenidoTrunc).html();

    const card = $(
      '<div class="col-12 col-sm-6 col-lg-4 col-xl-3">' +
        '<div class="recommendation-card h-100 shadow-sm" data-id="'+row.id+'" data-local="'+(row._local? '1':'0')+'" style="transition: all 0.3s ease; border-radius: 12px; border: none; overflow: hidden; background: white;">' +
          '<div class="card-header border-0 p-0" style="border-radius: 12px 12px 0 0; overflow: hidden;">' +
            '<div style="height:10px; background: linear-gradient(90deg, #7b61ff 0%, #a14cf3 50%, #4facfe 100%);"></div>' +
            '<div class="px-3 py-2">' +
              '<div class="d-flex align-items-center">' +
                '<h6 class="card-title mb-0 fw-bold text-dark" style="font-size: 0.95rem;">'+feather.icons['bookmark'].toSvg({class:'me-1', width: 16, height: 16})+ titulo+'</h6>' +
              '</div>' +
            '</div>' +
          '</div>' +
          '<div class="card-body d-flex flex-column p-3">' +
            '<p class="card-text text-muted mb-3 small" style="min-height: 60px; font-size: 0.85rem; line-height: 1.5;">'+contenido+'</p>' +
            '<div class="mt-auto">' +
              '<div class="btn-group btn-group-sm w-100" role="group">' +
                '<button class="btn btn-outline-info btn_view_recomendacion flex-fill" data-id="'+row.id+'" title="Ver Detalle" style="border-radius: 6px 0 0 6px; font-size: 0.8rem;">'+feather.icons['eye'].toSvg({width: 14, height: 14})+'</button>' +
                (isAdmin ? '<button class="btn btn-outline-warning btn_edit_recomendacion flex-fill" data-id="'+row.id+'" title="Editar" style="border-radius: 0; font-size: 0.8rem;">'+feather.icons['edit-2'].toSvg({width: 14, height: 14})+'</button>' : '') +
                (isAdmin ? '<button class="btn btn-outline-danger btn_delete_recomendacion flex-fill" data-id="'+row.id+'" title="Eliminar" style="border-radius: 0 6px 6px 0; font-size: 0.8rem;">'+feather.icons['trash-2'].toSvg({width: 14, height: 14})+'</button>' : '') +
              '</div>' +
            '</div>' +
          '</div>' +
        '</div>' +
      '</div>'
    );

    container.append(card);
  });

  // Efectos hover para las tarjetas
  $('.recommendation-card').hover(
    function() {
      $(this).css({
        'transform': 'translateY(-8px)',
        'box-shadow': '0 12px 24px rgba(0,0,0,0.15)'
      });
    },
    function() {
      $(this).css({
        'transform': 'translateY(0)',
        'box-shadow': '0 2px 8px rgba(0,0,0,0.1)'
      });
    }
  );

  // Re-inicializar feather icons
  feather.replace();
}

/**
 * FUNCIÓN: LOAD_RECOMENDACIONES_CARDS
 * 
 * DESCRIPCIÓN:
 * Carga las recomendaciones desde el servidor y determina si el usuario
 * es administrador para mostrar u ocultar controles de administración.
 * 
 * FLUJO DE EJECUCIÓN:
 * 1. Obtiene información del usuario actual (incluyendo su rol)
 * 2. Determina si es administrador verificando el rol
 * 3. Muestra/oculta el botón "Agregar Recomendación" según el rol
 * 4. Carga las recomendaciones desde la API
 * 5. Renderiza las tarjetas con los controles apropiados
 * 
 * DETECCIÓN DE ADMINISTRADOR:
 * - Verifica si el rol contiene "admin" o "administrador" (case insensitive)
 * - Si la petición de usuario falla, asume que NO es administrador
 * 
 * APIS UTILIZADAS:
 * - GET /Location/get_locations_by_user - Obtener datos del usuario
 * - GET /P_Recommendations/get_recomendaciones - Obtener recomendaciones
 * 
 * MANEJO DE ERRORES:
 * - Si falla la carga de usuario, continúa sin permisos de admin
 * - Si falla la carga de recomendaciones, muestra mensaje de error
 * - En caso de error, muestra lista vacía
 * 
 * @return void
 */
function load_recomendaciones_cards() {
  // Obtener info de usuario (headerFill ya hace una petición similar); intentamos obtener rol para controlar visibilidad de acciones
  $.ajax({
    url: BASE_URL + 'Location/get_locations_by_user',
    type: 'GET',
    dataType: 'json',
    cache: false,
    success: function(userData) {
      let isAdmin = false;
      try {
        const user = userData.data ? userData.data.user : null;
        const role = user && user.role ? String(user.role).toLowerCase() : '';
        if (role.indexOf('admin') !== -1 || role.indexOf('administrador') !== -1) isAdmin = true;
      } catch(e) { isAdmin = false; }

      // Mostrar u ocultar botón agregar según flag
      if (isAdmin) $('#btn-add-recomendacion').removeClass('d-none'); else $('#btn-add-recomendacion').addClass('d-none');

      // Cargar recomendaciones
      $.ajax({
    url: BASE_URL + 'P_Recommendations/get_recomendaciones',
        type: 'GET',
        dataType: 'json',
        cache: false,
        success: function(data) {
          if (!data) { render_recomendaciones_cards([], isAdmin); return; }
          if (data.type && data.msg && data.type !== 'success') {
            functions.toast_message(data.type, data.msg, data.status || '');
          }
          const items = Array.isArray(data.data) ? data.data : (Array.isArray(data.result) ? data.result : []);
          render_recomendaciones_cards(items, isAdmin);
          render_recomendaciones_cards(items, isAdmin);
        },
        error: function() {
          functions.toast_message('error', 'Error al cargar recomendaciones', 'ERROR');
          // No usamos almacenamiento local: mostrar lista vacía
          render_recomendaciones_cards([], isAdmin);
        }
      });
    },
    error: function() { // Si falla obtener usuario, cargar igual sin permisos de admin
      $.ajax({
    url: BASE_URL + 'P_Recommendations/get_recomendaciones',
        type: 'GET',
        dataType: 'json',
        cache: false,
  success: function(data) { const items = Array.isArray(data.data) ? data.data : (Array.isArray(data.result) ? data.result : []); render_recomendaciones_cards(items, false); },
    error: function() { functions.toast_message('error', 'Error al cargar recomendaciones', 'ERROR'); render_recomendaciones_cards([], false); }
      });
    }
  });
}

/**
 * FUNCIÓN: VIEW_RECOMENDACION
 * 
 * DESCRIPCIÓN:
 * Muestra el detalle completo de una recomendación en un modal.
 * 
 * PARÁMETROS:
 * @param {Number|String} id - ID de la recomendación a visualizar
 * 
 * FLUJO:
 * 1. Realiza petición AJAX al endpoint get_recomendacion_detail
 * 2. Si la respuesta es exitosa, llena el modal con los datos
 * 3. Muestra el modal con el detalle completo
 * 4. Si hay error, muestra mensaje toast
 * 
 * MODAL UTILIZADO:
 * - #modal-recomendacion-detail
 * - Campos: detail-id, detail-titulo, detail-contenido
 * 
 * API:
 * - GET /P_Recommendations/get_recomendacion_detail?id={id}
 * 
 * @return void
 */
function view_recomendacion(id) {
  $.ajax({
    url: BASE_URL + 'P_Recommendations/get_recomendacion_detail',
    type: 'GET',
    dataType: 'json',
    data: { id: id },
    cache: false,
    success: function(data) {
      if (data.status === 'OK') {
        const r = data.data;
        // Mostrar sólo título y contenido según requerimiento
        $('#detail-id').val(r.id);
        $('#detail-titulo').text(r.titulo || '');
        $('#detail-contenido').html(r.contenido || '');
        // Rellenar metadata y ocultar si está vacía para evitar espacios
        const $modal = $('#modal-recomendacion-detail');
        if ($('#detail-autor').length) $('#detail-autor').text(r.autor || '').toggle(Boolean(r.autor));
        if ($('#detail-fecha').length) $('#detail-fecha').text(r.fecha_creacion || '').toggle(Boolean(r.fecha_creacion));
        // Si no hay autor ni fecha, ocultar todo el bloque de metadata (evita el espacio vacío)
        if ($('#detail-metadata').length) {
          const hasMeta = Boolean((r.autor || '').toString().trim() || (r.fecha_creacion || '').toString().trim());
          $('#detail-metadata').toggle(hasMeta);
        }

        // Eliminar iconos SVG o placeholders (feather) dentro del modal —
        // en la vista detalle no deben mostrarse iconos decorativos.
        $modal.find('svg').remove();
        $modal.find('[data-feather]').remove();
        $modal.find('.detail-icons').remove();

        // Mostrar modal
        $modal.modal('show');
      } else {
        functions.toast_message(data.type || 'error', data.msg || 'Error', data.status || 'ERROR');
      }
    },
    error: function(xhr, status, error) {
      console.error(error);
      functions.toast_message('error', 'Error al cargar recomendación', 'ERROR');
    }
  });
}

/**
 * FUNCIÓN: OPEN_RECOMENDACION_FORM
 * 
 * DESCRIPCIÓN:
 * Abre el modal del formulario para crear o editar una recomendación.
 * 
 * PARÁMETROS:
 * @param {Object} data - Datos de la recomendación a editar (opcional)
 *   - Si se proporciona data, el modal se abre en modo EDICIÓN
 *   - Si data es null/undefined, el modal se abre en modo CREACIÓN
 * 
 * MODO CREACIÓN (data = null):
 * - Título del modal: "Nueva Recomendación"
 * - Todos los campos vacíos
 * - Al guardar se ejecuta POST crear_recomendacion
 * 
 * MODO EDICIÓN (data presente):
 * - Título del modal: "Editar Recomendación"
 * - Campos prellenados con data.titulo y data.contenido
 * - Campo oculto form-id contiene el ID de la recomendación
 * - Al guardar se ejecuta PUT editar_recomendacion
 * 
 * CAMPOS DEL FORMULARIO:
 * - form-id (hidden): ID de la recomendación (solo en edición)
 * - form-titulo: Título de la recomendación
 * - form-contenido: Descripción completa
 * 
 * MODAL UTILIZADO:
 * - #modal-recomendacion-form
 * 
 * @return void
 */
function open_recomendacion_form(data) {
  // limpiar
  $('#form-id').val('');
  $('#form-titulo').val('');
  $('#form-contenido').val('');

  if (data) {
    $('#form-id').val(data.id || '');
    $('#form-titulo').val(data.titulo || '');
    $('#form-contenido').val(data.contenido || '');
    $('#modalRecomendacionFormLabel').html(feather.icons['edit-3'].toSvg({class:'me-2'}) + 'Editar Recomendación');
  } else {
    $('#modalRecomendacionFormLabel').html(feather.icons['plus-circle'].toSvg({class:'me-2'}) + 'Nueva Recomendación');
  }

  // Ensure a status toggle exists in the form (create if missing)
  if ($('#form-status').length === 0) {
    const statusHtml = '<div class="mb-3 d-flex align-items-center" id="form-status-group">' +
      '<div class="form-check form-switch ms-2 me-2">' +
        '<input class="form-check-input" type="checkbox" id="form-status" />' +
        '<label class="form-check-label" for="form-status">Activo</label>' +
      '</div>' +
      '<small class="text-muted ms-2">Marcar como inactivo para ocultar la recomendación mientras se edita</small>' +
    '</div>';
    // Insert before modal footer if exists, otherwise append to body
    const $modalBody = $('#modal-recomendacion-form').find('.modal-body');
    if ($modalBody.length) {
      $modalBody.append(statusHtml);
    } else {
      $('#modal-recomendacion-form').append(statusHtml);
    }
  }

  // Set status value: default active for new, apply actual status for edit
  if (data) {
    const isActive = (typeof data.status !== 'undefined') ? (parseInt(data.status, 10) === 1) : true;
    $('#form-status').prop('checked', Boolean(isActive));
  } else {
    $('#form-status').prop('checked', true);
  }

  // Aplicar el estado inicial a los campos del formulario (si está activo, bloquear escritura)
  try { setFormFieldsReadonly($('#form-status').is(':checked')); } catch(e) { /* noop si la función aún no existe */ }

  $('#modal-recomendacion-form').modal('show');
  // Re-inicializar feather icons en el modal
  feather.replace();
}

/**
 * FUNCIÓN: setFormFieldsReadonly
 * Descripción: Cuando el toggle 'Activo' está activo (checked = true),
 * los campos de título y contenido deben quedar en solo lectura (no se puede escribir).
 * Si checked = false, los campos vuelven a ser editables.
 */
function setFormFieldsReadonly(isActive) {
  // Cuando está activo -> readonly true (no permite escribir)
  $('#form-titulo').prop('readonly', Boolean(isActive));
  $('#form-contenido').prop('readonly', Boolean(isActive));
  // Opcional: añadir clase para estilos visuales si es necesario
  if (isActive) {
    $('#form-titulo').addClass('readonly-disabled');
    $('#form-contenido').addClass('readonly-disabled');
  } else {
    $('#form-titulo').removeClass('readonly-disabled');
    $('#form-contenido').removeClass('readonly-disabled');
  }
}

/**
 * FUNCIÓN: SAVE_RECOMENDACION
 * 
 * DESCRIPCIÓN:
 * Guarda una recomendación (crear o editar) enviando los datos al servidor.
 * 
 * VALIDACIONES:
 * - Verifica que el título no esté vacío
 * - Verifica que el contenido no esté vacío
 * - Si alguna validación falla, muestra mensaje toast warning
 * 
 * LÓGICA DE OPERACIÓN:
 * - Si form-id está vacío → Crear nueva recomendación (POST)
 * - Si form-id tiene valor → Editar recomendación existente (PUT)
 * 
 * PAYLOAD JSON:
 * {
 *   "titulo": "Título ingresado",
 *   "contenido": "Contenido ingresado",
 *   "id": 5  // Solo en modo edición
 * }
 * 
 * APIS:
 * - POST /P_Recommendations/crear_recomendacion - Modo crear
 * - PUT /P_Recommendations/editar_recomendacion - Modo editar
 * 
 * RESPUESTA EXITOSA:
 * - Muestra mensaje toast de éxito
 * - Si es creación, muestra el ID insertado
 * - Cierra el modal del formulario
 * - Recarga la lista de recomendaciones
 * 
 * MANEJO DE ERRORES:
 * - Muestra mensaje de error en toast
 * - Intenta extraer mensaje del servidor si está disponible
 * 
 * @return void
 */
function save_recomendacion() {
  const id = $('#form-id').val();
  const titulo = $('#form-titulo').val();
  const contenido = $('#form-contenido').val();

  if (!titulo || titulo.trim() === '') { functions.toast_message('warning', 'El título es requerido', 'WARNING'); return; }
  if (!contenido || contenido.trim() === '') { functions.toast_message('warning', 'El contenido es requerido', 'WARNING'); return; }

  const payload = { titulo: titulo, contenido: contenido };

  let url = BASE_URL + 'P_Recommendations/crear_recomendacion';
  let method = 'POST';
  if (id && id !== '') {
    url = BASE_URL + 'P_Recommendations/editar_recomendacion';
    method = 'PUT';
    payload.id = id;
    // include status when editing (1 = active, 0 = inactive)
    const statusEl = $('#form-status');
    if (statusEl.length) payload.status = statusEl.is(':checked') ? 1 : 0;
  }

  // Si el id es local, actualizar localStorage en vez de llamar al servidor
  // No soportamos localStorage: siempre intentamos con la API

  $.ajax({
    url: url,
    type: method,
    dataType: 'json',
    contentType: 'application/json',
    data: JSON.stringify(payload),
    success: function(data) {
      functions.toast_message(data.type || 'success', data.msg || 'Operación exitosa', data.status || 'OK');
      if (data.status === 'OK') {
        // mostrar id insertado si existe
        if (data.data && data.data.id) {
          functions.toast_message('success', 'Recomendación creada (ID: ' + data.data.id + ')', 'OK');
        }
        $('#modal-recomendacion-form').modal('hide');
        load_recomendaciones_cards();
      }
    },
    error: function(xhr, status, error) {
      console.error('Save error', status, error, xhr);
      // intentar parsear; si no es JSON, guardamos localmente como fallback
      let msg = 'Error al guardar recomendación';
      try {
        const body = JSON.parse(xhr.responseText);
        if (body && body.msg) msg = body.msg;
        functions.toast_message('error', msg, 'ERROR');
        return;
      } catch (e) {
        // No guardamos en local bajo ninguna circunstancia; mostrar mensaje genérico
        functions.toast_message('error', msg, 'ERROR');
        $('#modal-recomendacion-form').modal('hide');
        return;
      }
    }
  });
}

/**
 * FUNCIÓN: DELETE_RECOMENDACION
 * 
 * DESCRIPCIÓN:
 * Elimina una recomendación después de confirmar con el usuario.
 * Utiliza SweetAlert2 para mostrar un diálogo de confirmación.
 * 
 * PARÁMETROS:
 * @param {Number} id - ID de la recomendación a eliminar
 * 
 * FLUJO:
 * 1. Muestra diálogo de confirmación con SweetAlert2
 * 2. Si el usuario confirma, envía petición DELETE al servidor
 * 3. Si el servidor responde OK, recarga la lista de recomendaciones
 * 4. Muestra mensaje toast del resultado de la operación
 * 
 * CONFIRMACIÓN SWEETALERT:
 * - Título: "¿Eliminar recomendación?"
 * - Texto: "Esta acción no se puede deshacer"
 * - Icono: warning
 * - Botones: "Sí, eliminar" (danger) y "Cancelar"
 * 
 * API:
 * - DELETE /P_Recommendations/eliminar_recomendacion
 * 
 * PAYLOAD:
 * {
 *   "id": 5
 * }
 * 
 * NOTA IMPORTANTE:
 * Esta función realiza SOFT DELETE en el servidor, no elimina
 * físicamente el registro de la base de datos.
 * 
 * @return void
 */
function delete_recomendacion(id) {
  Swal.fire({
    title: '¿Eliminar esta recomendación?',
    html: '<p class="text-muted mb-0">Esta acción no se puede deshacer. La recomendación será eliminada permanentemente.</p>',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Sí, eliminar',
    cancelButtonText: '<i class="fas fa-times me-1"></i> Cancelar',
    buttonsStyling: false,
    customClass: {
      confirmButton: 'btn btn-danger me-2 shadow',
      cancelButton: 'btn btn-secondary shadow'
    },
    reverseButtons: true
  }).then((result) => {
    if (result.isConfirmed) {
      // Llamada API para eliminar
      $.ajax({
        url: BASE_URL + 'P_Recommendations/eliminar_recomendacion',
        type: 'DELETE',
        dataType: 'json',
        contentType: 'application/json',
        data: JSON.stringify({ id: id }),
        success: function(data) {
          functions.toast_message(data.type || 'success', data.msg || 'Eliminado', data.status || 'OK');
          if (data.status === 'OK') load_recomendaciones_cards();
        },
        error: function(xhr, status, error) { console.error(error); functions.toast_message('error', 'Error al eliminar recomendación', 'ERROR'); }
      });
    }
  });
}

/**
 * ========================================
 * EVENTOS DEL DOM (Document Ready)
 * ========================================
 * 
 * Esta sección maneja todos los eventos del módulo de Recomendaciones.
 * Se ejecuta cuando el DOM está completamente cargado.
 * 
 * EVENTOS CONFIGURADOS:
 * 
 * 1. CARGA INICIAL:
 *    - load_recomendaciones_cards() - Carga las recomendaciones al cargar la página
 * 
 * 2. BOTÓN AGREGAR:
 *    - #btn-add-recomendacion.click() - Abre modal para crear nueva recomendación
 *    - Solo visible para administradores
 * 
 * 3. BOTÓN VER:
 *    - .btn_view_recomendacion.click() - Muestra detalle de la recomendación
 *    - Visible para todos los usuarios
 * 
 * 4. BOTÓN EDITAR:
 *    - .btn_edit_recomendacion.click() - Carga datos y abre formulario de edición
 *    - Solo visible para administradores
 *    - Realiza petición GET para obtener datos actuales
 * 
 * 5. BOTÓN ELIMINAR:
 *    - .btn_delete_recomendacion.click() - Muestra confirmación y elimina
 *    - Solo visible para administradores
 *    - Usa SweetAlert2 para confirmar acción
 * 
 * 6. BOTÓN GUARDAR:
 *    - #btn-save-recomendacion.click() - Guarda la recomendación (crear o editar)
 *    - Valida campos antes de enviar
 * 
 * DELEGACIÓN DE EVENTOS:
 * Se usa $(document).on() en lugar de .click() directo porque las tarjetas
 * se crean dinámicamente y los botones no ex isten en el DOM inicial.
 * 
 * ========================================
 */
$(document).ready(function() {
  load_recomendaciones_cards();
  load_recomendaciones_cards();

  // Cuando el toggle de estado cambia, aplicar bloqueo/permiso de edición en los campos
  $(document).on('change', '#form-status', function() {
    setFormFieldsReadonly($(this).is(':checked'));
  });
  $(document).on('click', '#btn-add-recomendacion', function() { open_recomendacion_form(); });

  $(document).on('click', '.btn_view_recomendacion', function() { const id = $(this).data('id'); view_recomendacion(id); });

  $(document).on('click', '.btn_edit_recomendacion', function() {
    const id = $(this).data('id');
    // Si es local, abrir el formulario con los datos desde localStorage
    if (typeof id === 'string' && id.indexOf('local_') === 0) {
      const local = get_local_recomendaciones().find(i => i.id === id);
      if (local) {
        open_recomendacion_form(local);
      } else {
        functions.toast_message('warning', 'Recomendación local no encontrada', 'WARNING');
      }
      return;
    }
    // cargar datos y abrir formulario desde servidor
    $.ajax({ url: BASE_URL + 'P_Recommendations/get_recomendacion_detail', type: 'GET', dataType: 'json', data: { id: id }, success: function(data) {
      if (data.status === 'OK') open_recomendacion_form(data.data); else functions.toast_message(data.type || 'error', data.msg || 'Error', data.status || 'ERROR');
    }, error: function() { functions.toast_message('error', 'Error al cargar datos', 'ERROR'); } });
  });

  $(document).on('click', '.btn_delete_recomendacion', function() { const id = $(this).data('id');
    Swal.fire({
      title: '¿Eliminar esta recomendación?',
      html: '<p class="text-muted mb-0">Esta acción no se puede deshacer. La recomendación será eliminada permanentemente.</p>',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="fas fa-trash-alt me-1"></i> Sí, eliminar',
      cancelButtonText: '<i class="fas fa-times me-1"></i> Cancelar',
      buttonsStyling: false,
      customClass: {
        confirmButton: 'btn btn-danger me-2 shadow',
        cancelButton: 'btn btn-secondary shadow'
      },
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
            url: BASE_URL + 'P_Recommendations/eliminar_recomendacion',
          type: 'DELETE',
          dataType: 'json',
          contentType: 'application/json',
          data: JSON.stringify({ id: id }),
          success: function(data) {
            functions.toast_message(data.type || 'success', data.msg || 'Eliminado', data.status || 'OK');
            if (data.status === 'OK') load_recomendaciones_cards();
          },
          error: function(xhr, status, error) { console.error(error); functions.toast_message('error', 'Error al eliminar recomendación', 'ERROR'); }
        });
      }
    });
  });

  $(document).on('click', '#btn-save-recomendacion', function() { save_recomendacion(); });
});
