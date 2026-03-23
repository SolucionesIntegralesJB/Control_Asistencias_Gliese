/**
 * ============================================================================
 * MI INFORMACIÓN - JavaScript (index.js)
 * ============================================================================
 * 
 * PROPÓSITO:
 * Gestiona la interfaz y la carga dinámica de información personal del
 * usuario logueado (practicante o supervisor) mediante AJAX.
 * 
 * FUNCIONES PRINCIPALES:
 * 1. load_my_info() - Carga los datos del usuario desde el backend
 * 2. render_practicante_profile(data) - Renderiza la vista de practicante
 * 3. render_supervisor_profile(data) - Renderiza la vista de supervisor
 * 
 * FLUJO DE EJECUCIÓN:
 * 1. $(document).ready() ejecuta load_my_info()
 * 2. load_my_info() hace petición AJAX a /P_Info/get_my_info
 * 3. Backend detecta tipo de usuario y retorna datos
 * 4. JavaScript detecta tipo por campos (tarea_asignada = practicante)
 * 5. Llama a render_practicante_profile() o render_supervisor_profile()
 * 6. Se renderizan las tarjetas con iconos y animaciones
 * 
 * DEPENDENCIAS:
 * - jQuery 3.x
 * - Feather Icons (para iconos modernos)
 * - BASE_URL (variable global con la URL base del sistema)
 * - functions.toast_message() (para mostrar notificaciones)
 * 
 * CARACTERÍSTICAS UI:
 * - Tarjetas con iconos Feather
 * - Animación de entrada escalonada (delay por tarjeta)
 * - Efectos hover en las tarjetas
 * - Badge de tarea asignada para practicantes
 * - Avatar con badge de estado online
 * - Diseño responsive (col-12 col-md-6 col-lg-4)
 * 
 * AUTOR: Sistema Gliese
 * FECHA: 2025
 * ============================================================================
 */

/**
 * ============================================================================
 * FUNCIÓN: render_practicante_profile(data)
 * ============================================================================
 * 
 * PROPÓSITO:
 * Renderiza dinámicamente la interfaz de información del practicante
 * con todos sus datos personales, académicos y tarea asignada.
 * 
 * PARÁMETROS:
 * @param {Object} data - Objeto con los datos del practicante
 * 
 * DATOS ESPERADOS EN 'data':
 * - nombre_completo: Nombre completo del practicante
 * - documento: Número de documento
 * - tipo_documento: Tipo de documento (DNI, CE, etc.)
 * - email: Correo electrónico
 * - telefono: Número de teléfono
 * - institucion: Institución educativa
 * - especialidad: Carrera o especialidad
 * - area_interes: Área de interés
 * - fecha_inicio_formatted: Fecha de inicio (DD/MM/YYYY)
 * - fecha_termino_formatted: Fecha de término (DD/MM/YYYY)
 * - fecha_nacimiento_formatted: Fecha de nacimiento (DD/MM/YYYY)
 * - ciclo: Ciclo académico
 * - modalidad: Modalidad de práctica
 * - tarea_asignada: Tarea o proyecto asignado
 * - avatar_url: (Opcional) URL de la foto del practicante
 * 
 * PROCESO:
 * 1. Actualiza el nombre en el header (#pf-nombre)
 * 2. Actualiza el texto de rol (#pf-role-text)
 * 3. Si existe avatar_url, actualiza la imagen
 * 4. Muestra la tarjeta de tarea asignada (#pf-tarea)
 * 5. Crea 12 tarjetas de información con iconos
 * 6. Cada tarjeta tiene animación de entrada escalonada
 * 7. Re-renderiza los iconos de Feather
 * 
 * ICONOS UTILIZADOS (Feather Icons):
 * - credit-card: Documento
 * - file-text: Tipo de documento
 * - mail: Email
 * - phone: Teléfono
 * - home: Institución
 * - book-open: Especialidad
 * - target: Área de interés
 * - calendar: Fechas (inicio, término, nacimiento)
 * - gift: Fecha de nacimiento
 * - trending-up: Ciclo
 * - monitor: Modalidad
 * 
 * ESTRUCTURA HTML GENERADA:
 * <div class="col-12 col-md-6 col-lg-4">
 *   <div class="card pf-field-card" style="animation-delay: Xs;">
 *     <div class="card-body">
 *       <div class="pf-field-title">
 *         <span class="icon-wrapper">
 *           <i data-feather="icon-name"></i>
 *         </span>
 *         Título del campo
 *       </div>
 *       <span class="pf-field-value">Valor del campo</span>
 *     </div>
 *   </div>
 * </div>
 */
// Renderizar perfil de practicante
function render_practicante_profile(data) {
  // Si no hay datos, usar objeto vacío por defecto
  if (!data) data = {};
  
  // === PASO 1: Actualizar información del header ===
  const nombre = data.nombre_completo || '-';
  $('#pf-nombre').text(nombre); // Actualizar nombre en el header
  $('#pf-role-text').text('Practicante'); // Indicar que es practicante

  // === PASO 2: Actualizar avatar si existe ===
  if (data.avatar_url) {
    $('#pf-avatar').attr('src', data.avatar_url);
  }

  // === PASO 3: Mostrar tarea asignada ===
  const tareaText = data.tarea_asignada || 'Sin tarea asignada';
  $('#pf-tarea-text').text(tareaText);
  $('#pf-tarea').show(); // Mostrar sección de tarea (solo para practicantes)

  // === PASO 3.5: Cargar tareas del grupo ===
  load_group_tasks();

  // === PASO 4: Definir los campos a mostrar con sus iconos ===
  const container = $('#practicante-cards');
  container.empty(); // Limpiar contenedor antes de renderizar

  // Array de campos con sus configuraciones
  const fields = [
    { key: 'documento', label: 'Documento', icon: 'credit-card' },
    { key: 'email', label: 'Email', icon: 'mail' },
    { key: 'telefono', label: 'Teléfono', icon: 'phone' },
    { key: 'institucion', label: 'Institución', icon: 'home' },
    { key: 'especialidad', label: 'Especialidad', icon: 'book-open' },
    { key: 'area_interes', label: 'Área de interés', icon: 'target' },
    { key: 'fecha_inicio_formatted', label: 'Fecha inicio', icon: 'calendar' },
    { key: 'fecha_termino_formatted', label: 'Fecha término', icon: 'calendar' },
    { key: 'fecha_nacimiento_formatted', label: 'Fecha nacimiento', icon: 'gift' },
    { key: 'ciclo', label: 'Ciclo', icon: 'trending-up' },
    { key: 'modalidad', label: 'Modalidad', icon: 'monitor' }
  ];

  // === PASO 5: Crear tarjetas dinámicamente ===
  fields.forEach(function(f, index){
    // Obtener valor del campo o mostrar '-' si no existe
    const val = data[f.key] || '-';
    
    // Crear tarjeta con template literal
    // El delay de animación aumenta 0.05s por cada tarjeta (efecto cascada)
    const card = $(`
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card pf-field-card" style="animation-delay: ${index * 0.05}s;">
          <div class="card-body">
            <div class="pf-field-title">
              <span class="icon-wrapper">
                <i data-feather="${f.icon}" style="width: 18px; height: 18px;"></i>
              </span>
              ${f.label}
            </div>
            <span class="pf-field-value">${val}</span>
          </div>
        </div>
      </div>
    `);
    
    // Agregar tarjeta al contenedor
    container.append(card);
  });

  // === PASO 6: Re-renderizar iconos de Feather ===
  // Esto es necesario porque los iconos se agregan dinámicamente
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
}

/**
 * ============================================================================
 * FUNCIÓN: render_supervisor_profile(data)
 * ============================================================================
 * 
 * PROPÓSITO:
 * Renderiza dinámicamente la interfaz de información del supervisor
 * con sus datos personales y laborales.
 * 
 * PARÁMETROS:
 * @param {Object} data - Objeto con los datos del supervisor
 * 
 * DATOS ESPERADOS EN 'data':
 * - nombre_completo: Nombre completo del supervisor
 * - documento: Número de documento
 * - tipo_documento: Tipo de documento (DNI, CE, etc.)
 * - email: Correo electrónico
 * - telefono: Número de teléfono
 * - cargo: Cargo o puesto del supervisor
 * - area_supervision: Área que supervisa
 * - fecha_ingreso_formatted: Fecha de ingreso (DD/MM/YYYY)
 * - avatar_url: (Opcional) URL de la foto del supervisor
 * 
 * PROCESO:
 * 1. Actualiza el nombre en el header (#pf-nombre)
 * 2. Actualiza el texto de rol (#pf-role-text)
 * 3. Si existe avatar_url, actualiza la imagen
 * 4. OCULTA la tarjeta de tarea asignada (solo es para practicantes)
 * 5. Crea 7 tarjetas de información con iconos
 * 6. Cada tarjeta tiene animación de entrada escalonada
 * 7. Re-renderiza los iconos de Feather
 * 
 * ICONOS UTILIZADOS (Feather Icons):
 * - credit-card: Documento
 * - file-text: Tipo de documento
 * - mail: Email
 * - phone: Teléfono
 * - briefcase: Cargo
 * - eye: Área de supervisión
 * - calendar: Fecha de ingreso
 * 
 * DIFERENCIAS CON PRACTICANTE:
 * - No muestra tarea asignada (#pf-tarea se oculta)
 * - Menos campos (7 vs 12)
 * - Campos enfocados en información laboral
 */
// Renderizar perfil de supervisor
function render_supervisor_profile(data) {
  // Si no hay datos, usar objeto vacío por defecto
  if (!data) data = {};
  
  // === PASO 1: Actualizar información del header ===
  const nombre = data.nombre_completo || '-';
  $('#pf-nombre').text(nombre); // Actualizar nombre en el header
  $('#pf-role-text').text('Supervisor'); // Indicar que es supervisor

  // === PASO 2: Actualizar avatar si existe ===
  if (data.avatar_url) {
    $('#pf-avatar').attr('src', data.avatar_url);
  }

  // === PASO 3: Ocultar sección de tarea ===
  // Los supervisores NO tienen tarea asignada
  $('#pf-tarea').hide();

  // === PASO 4: Definir los campos a mostrar con sus iconos ===
  const container = $('#practicante-cards');
  container.empty(); // Limpiar contenedor antes de renderizar

  // Array de campos con sus configuraciones
  const fields = [
    { key: 'documento', label: 'Documento', icon: 'credit-card' },
    { key: 'email', label: 'Email', icon: 'mail' },
    { key: 'telefono', label: 'Teléfono', icon: 'phone' },
    { key: 'cargo', label: 'Cargo', icon: 'briefcase' },
    { key: 'area_supervision', label: 'Área de supervisión', icon: 'eye' },
    { key: 'fecha_ingreso_formatted', label: 'Fecha ingreso', icon: 'calendar' }
  ];

  // === PASO 5: Crear tarjetas dinámicamente ===
  fields.forEach(function(f, index){
    // Obtener valor del campo o mostrar '-' si no existe
    const val = data[f.key] || '-';
    
    // Crear tarjeta con template literal
    // El delay de animación aumenta 0.05s por cada tarjeta (efecto cascada)
    const card = $(`
      <div class="col-12 col-md-6 col-lg-4">
        <div class="card pf-field-card" style="animation-delay: ${index * 0.05}s;">
          <div class="card-body">
            <div class="pf-field-title">
              <span class="icon-wrapper">
                <i data-feather="${f.icon}" style="width: 18px; height: 18px;"></i>
              </span>
              ${f.label}
            </div>
            <span class="pf-field-value">${val}</span>
          </div>
        </div>
      </div>
    `);
    
    // Agregar tarjeta al contenedor
    container.append(card);
  });

  // === PASO 6: Re-renderizar iconos de Feather ===
  // Esto es necesario porque los iconos se agregan dinámicamente
  if (typeof feather !== 'undefined') {
    feather.replace();
  }
}

/**
 * ============================================================================
 * FUNCIÓN: load_my_info()
 * ============================================================================
 * 
 * PROPÓSITO:
 * Realiza una petición AJAX al backend para obtener la información
 * del usuario logueado y renderiza la interfaz correspondiente.
 * 
 * ENDPOINT LLAMADO:
 * GET /P_Info/get_my_info
 * 
 * PROCESO:
 * 1. Hace petición GET al controlador C_P_Info::get_my_info()
 * 2. El backend detecta el tipo de usuario (practicante/supervisor)
 * 3. Retorna los datos en formato JSON
 * 4. JavaScript detecta el tipo por los campos del objeto
 * 5. Llama a la función de renderizado correspondiente
 * 
 * DETECCIÓN DE TIPO DE USUARIO:
 * - Si response.data tiene 'tarea_asignada' → Practicante
 * - Si response.data tiene 'institucion' → Practicante
 * - Si no → Supervisor
 * 
 * RESPUESTA ESPERADA:
 * {
 *   "status": "OK",
 *   "type": "success",
 *   "msg": "Información obtenida correctamente.",
 *   "data": {objeto con los datos}
 * }
 * 
 * MANEJO DE ERRORES:
 * - Si status != OK: Muestra mensaje con toast_message()
 * - Si hay error AJAX: Muestra mensaje de error en consola
 * - En ambos casos renderiza vista vacía
 * 
 * DEPENDENCIAS:
 * - BASE_URL: Variable global con la URL base del sistema
 * - functions.toast_message(): Para mostrar notificaciones
 * - render_practicante_profile(): Para renderizar vista de practicante
 * - render_supervisor_profile(): Para renderizar vista de supervisor
 */
// Cargar información del usuario actual
function load_my_info() {
  // === PETICIÓN AJAX AL BACKEND ===
  $.ajax({
    url: BASE_URL + 'P_Info/get_my_info', // Endpoint del controlador
    type: 'GET', // Método HTTP
    dataType: 'json', // Esperamos respuesta JSON
    cache: false, // No cachear la petición
    
    // === ÉXITO: Respuesta recibida correctamente ===
    success: function(response) {
      // Verificar que la respuesta tenga status OK y contenga datos
      if (response.status === 'OK' && response.data) {

        // === DETECCIÓN AUTOMÁTICA DEL TIPO DE USUARIO ===
        // Primero verificar si es administrador (rol 1)
        if (response.data.hasOwnProperty('id_role') && response.data.id_role == 1) {
          // Es administrador - mostrar solo gestión de tareas
          render_admin_tasks_view();
        }
        // Si el objeto tiene 'tarea_asignada' o 'institucion', es practicante
        else if (response.data.hasOwnProperty('tarea_asignada') || response.data.hasOwnProperty('institucion')) {
          render_practicante_profile(response.data);
        } else {
          // Si no, es supervisor
          render_supervisor_profile(response.data);
        }

      } else {
        // El backend retornó un error (usuario no encontrado, sin permisos, etc.)
        functions.toast_message(
          response.type || 'warning',
          response.msg || 'No se pudo cargar la información',
          'WARNING'
        );

        // Mostrar campos vacíos en lugar de pantalla en blanco
        render_practicante_profile({});
      }
    },
    
    // === ERROR: Falló la petición AJAX ===
    error: function(xhr, status, error) {
      // Registrar error en consola para debugging
      console.error('Error al cargar información:', error);
      
      // Mostrar mensaje de error al usuario
      functions.toast_message('error', 'Error al cargar información personal', 'ERROR');
      
      // Mostrar campos vacíos en lugar de pantalla en blanco
      render_practicante_profile({});
    }
  });
}

/**
 * ============================================================================
 * FUNCIÓN: load_group_tasks()
 * ============================================================================
 *
 * PROPÓSITO:
 * Carga las tareas asignadas al grupo del practicante mediante AJAX
 * y las muestra en tarjetas con estilos modernos y mejorados.
 */
function load_group_tasks() {
  $.ajax({
    url: BASE_URL + 'Group_Tasks/get_my_tasks',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK' && response.data && response.data.length > 0) {
        // Mostrar sección de tareas
        $('#pf-group-tasks').show();

        // Renderizar tareas
        const container = $('#group-tasks-container');
        container.empty();

        // Contar tareas por estado
        let pendientes = 0, enProgreso = 0, completadas = 0;
        response.data.forEach(function(t) {
          if (t.status === 'pendiente') pendientes++;
          else if (t.status === 'en_progreso') enProgreso++;
          else if (t.status === 'completada') completadas++;
        });

        // Crear resumen de tareas
        const summaryHtml = `
          <div class="row mb-4">
            <div class="col-4">
              <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%); border: 1px solid #ffc107;">
                <h3 class="mb-0 fw-bold text-warning">${pendientes}</h3>
                <small class="text-muted">Pendientes</small>
              </div>
            </div>
            <div class="col-4">
              <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, #cce5ff 0%, #b8daff 100%); border: 1px solid #17a2b8;">
                <h3 class="mb-0 fw-bold text-info">${enProgreso}</h3>
                <small class="text-muted">En Progreso</small>
              </div>
            </div>
            <div class="col-4">
              <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border: 1px solid #28a745;">
                <h3 class="mb-0 fw-bold text-success">${completadas}</h3>
                <small class="text-muted">Completadas</small>
              </div>
            </div>
          </div>
        `;
        container.append(summaryHtml);

        response.data.forEach(function(task, index) {
          // Determinar estilos según estado
          let statusConfig = {
            pendiente: {
              badge: '<span class="badge px-3 py-2" style="background: linear-gradient(135deg, #f6c343 0%, #f5a623 100%); color: #fff;">Pendiente</span>',
              borderColor: '#f6c343',
              bgGradient: 'linear-gradient(135deg, #fffdf5 0%, #fff9e6 100%)',
              icon: 'clock',
              iconColor: '#f5a623'
            },
            en_progreso: {
              badge: '<span class="badge px-3 py-2" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: #fff;">En Progreso</span>',
              borderColor: '#4facfe',
              bgGradient: 'linear-gradient(135deg, #f0f9ff 0%, #e0f4ff 100%)',
              icon: 'activity',
              iconColor: '#4facfe'
            },
            completada: {
              badge: '<span class="badge px-3 py-2" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: #fff;">Completada</span>',
              borderColor: '#28a745',
              bgGradient: 'linear-gradient(135deg, #f0fff4 0%, #e6ffed 100%)',
              icon: 'check-circle',
              iconColor: '#28a745'
            }
          };

          let config = statusConfig[task.status] || statusConfig.pendiente;

          // Determinar badge de prioridad con mejor diseño
          let priorityConfig = {
            alta: '<span class="badge ms-2" style="background: #dc3545; font-size: 10px;">URGENTE</span>',
            media: '<span class="badge ms-2" style="background: #6c757d; font-size: 10px;">Media</span>',
            baja: '<span class="badge ms-2" style="background: #adb5bd; font-size: 10px;">Baja</span>'
          };
          let priorityBadge = priorityConfig[task.priority] || '';

          // Formatear fecha límite
          let dueDate = 'Sin fecha límite';
          let dueDateClass = 'text-muted';
          if (task.due_date) {
            const dateObj = new Date(task.due_date);
            const today = new Date();
            const diffDays = Math.ceil((dateObj - today) / (1000 * 60 * 60 * 24));

            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const year = dateObj.getFullYear();
            dueDate = `${day}/${month}/${year}`;

            if (diffDays < 0 && task.status !== 'completada') {
              dueDateClass = 'text-danger fw-bold';
              dueDate += ' (Vencida)';
            } else if (diffDays <= 3 && task.status !== 'completada') {
              dueDateClass = 'text-warning fw-bold';
              dueDate += ` (${diffDays} días)`;
            }
          }

          // Crear tarjeta de tarea con diseño mejorado
          const taskCard = $(`
            <div class="task-item mb-3" style="animation: fadeInUp 0.5s ease-out ${index * 0.1}s both;">
              <div class="card border-0 shadow-sm" style="border-radius: 16px; overflow: hidden; background: ${config.bgGradient}; border-left: 5px solid ${config.borderColor} !important;">
                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center">
                      <div class="me-3" style="width: 48px; height: 48px; border-radius: 12px; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                        <i data-feather="${config.icon}" style="width: 24px; height: 24px; color: ${config.iconColor};"></i>
                      </div>
                      <div>
                        <h5 class="mb-1 fw-bold" style="color: #1e293b;">
                          ${task.task_title}
                          ${priorityBadge}
                        </h5>
                        <small class="${dueDateClass}">
                          <i data-feather="calendar" style="width: 12px; height: 12px;"></i>
                          ${dueDate}
                        </small>
                      </div>
                    </div>
                    <div>
                      ${config.badge}
                    </div>
                  </div>
                  ${task.task_description ? `
                    <p class="mb-3" style="color: #64748b; line-height: 1.6; font-size: 0.95rem;">
                      ${task.task_description}
                    </p>
                  ` : ''}
                  <div class="d-flex justify-content-between align-items-center pt-2" style="border-top: 1px solid rgba(0,0,0,0.05);">
                    <small class="text-muted">
                      <i data-feather="user" style="width: 12px; height: 12px;"></i>
                      Asignada el ${task.created_at ? formatDate(task.created_at) : '-'}
                    </small>
                    ${task.status === 'pendiente' ? `
                      <button class="btn btn-sm btn-start-task" data-id="${task.id}" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; padding: 6px 16px; font-size: 12px; font-weight: 600;">
                        <i data-feather="play" style="width: 12px; height: 12px;"></i>
                        Iniciar Tarea
                      </button>
                    ` : ''}
                    ${task.status === 'en_progreso' ? `
                      <button class="btn btn-sm btn-complete-task" data-id="${task.id}" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 8px; padding: 6px 16px; font-size: 12px; font-weight: 600;">
                        <i data-feather="check" style="width: 12px; height: 12px;"></i>
                        Completar
                      </button>
                    ` : ''}
                  </div>
                </div>
              </div>
            </div>
          `);

          container.append(taskCard);
        });

        // Re-renderizar iconos de Feather
        if (typeof feather !== 'undefined') {
          feather.replace();
        }

        // Vincular eventos de botones
        bind_task_buttons();

      } else {
        // No hay tareas o error
        $('#pf-group-tasks').show();
        $('#group-tasks-container').html(`
          <div class="text-center py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 16px;">
            <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
              <i data-feather="inbox" style="width: 40px; height: 40px; color: #6c757d;"></i>
            </div>
            <h5 class="text-muted mb-2">No hay tareas asignadas</h5>
            <p class="text-muted small">Tu grupo no tiene tareas pendientes en este momento</p>
          </div>
        `);

        if (typeof feather !== 'undefined') {
          feather.replace();
        }
      }
    },
    error: function() {
      console.error('Error al cargar tareas del grupo');
      $('#pf-group-tasks').hide();
    }
  });
}

/**
 * Formatear fecha a formato DD/MM/YYYY
 */
function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

/**
 * Vincular eventos de botones de tareas
 */
function bind_task_buttons() {
  // Botón iniciar tarea
  $(document).off('click', '.btn-start-task').on('click', '.btn-start-task', function() {
    const taskId = $(this).data('id');
    update_task_status(taskId, 'en_progreso');
  });

  // Botón completar tarea
  $(document).off('click', '.btn-complete-task').on('click', '.btn-complete-task', function() {
    const taskId = $(this).data('id');
    Swal.fire({
      title: '¿Completar tarea?',
      text: 'Esta acción marcará la tarea como completada',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, completar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        update_task_status(taskId, 'completada');
      }
    });
  });
}

/**
 * Actualizar estado de una tarea
 */
function update_task_status(taskId, newStatus) {
  $.ajax({
    url: BASE_URL + 'Group_Tasks/update_status',
    type: 'PUT',
    data: JSON.stringify({ id: taskId, status: newStatus }),
    contentType: 'application/json',
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        toastr.success(response.msg || 'Estado actualizado');
        load_group_tasks(); // Recargar tareas
      } else {
        toastr.error(response.msg || 'Error al actualizar');
      }
    },
    error: function() {
      toastr.error('Error de comunicación con el servidor');
    }
  });
}

/**
 * ============================================================================
 * GESTIÓN DE TAREAS PARA ADMINISTRADOR
 * ============================================================================
 */

let adminTaskTable;
let isEditingAdminTask = false;

/**
 * Renderizar vista de administrador (solo tareas)
 */
function render_admin_tasks_view() {
  console.log('✓ Ejecutando render_admin_tasks_view()');

  // Ocultar header de perfil (avatar, nombre, rol)
  $('.profile-header').hide();

  // Ocultar tarjetas de información personal
  $('#practicante-cards').hide();

  // Ocultar sección de tarea asignada
  $('#pf-tarea').hide();

  // Ocultar sección de tareas de grupo
  $('#pf-group-tasks').hide();

  // Mostrar sección de administración de tareas
  $('#admin-tasks-section').show();
  console.log('✓ Sección de administración mostrada');

  // Inicializar componentes de administración
  load_admin_stats();
  load_admin_groups();

  // Usar setTimeout para asegurar que el DOM esté completamente renderizado
  setTimeout(function() {
    console.log('✓ Inicializando tabla de tareas...');
    init_admin_task_table();
  }, 100);

  bind_admin_task_events();
}

/**
 * Cargar estadísticas de tareas
 */
function load_admin_stats() {
  $.ajax({
    url: BASE_URL + 'Group_Tasks/get_stats',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        $('#admin-stat-total').text(response.data.total_tasks || 0);
        $('#admin-stat-pending').text(response.data.pending_tasks || 0);
        $('#admin-stat-in-progress').text(response.data.in_progress_tasks || 0);
        $('#admin-stat-completed').text(response.data.completed_tasks || 0);
      }
    },
    error: function() {
      console.error('Error al cargar estadísticas');
    }
  });
}

/**
 * Cargar grupos para el selector
 */
function load_admin_groups() {
  $.ajax({
    url: BASE_URL + 'Group_Tasks/get_groups',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK' && response.data) {
        let options = '<option value="">Seleccione un grupo...</option>';
        response.data.forEach(function(group) {
          options += `<option value="${group.id}">${group.group_name} (${group.group_code})</option>`;
        });
        $('#admin-task-group').html(options);
      }
    },
    error: function() {
      console.error('Error al cargar grupos');
    }
  });
}

/**
 * Inicializar DataTable de tareas
 */
function init_admin_task_table() {
  // Destruir tabla existente si ya fue inicializada
  if ($.fn.DataTable.isDataTable('#admin-table-tasks')) {
    $('#admin-table-tasks').DataTable().destroy();
  }

  console.log('Inicializando DataTable de tareas...');
  console.log('BASE_URL:', BASE_URL);
  console.log('URL completa:', BASE_URL + 'Group_Tasks/get_all_tasks');

  adminTaskTable = $('#admin-table-tasks').DataTable({
    ajax: {
      url: BASE_URL + 'Group_Tasks/get_all_tasks',
      type: 'GET',
      dataType: 'json',
      dataSrc: function(json) {
        console.log('Datos recibidos del servidor:', json);
        if (json.status === 'OK' && json.data) {
          return json.data;
        }
        return [];
      },
      error: function(xhr, error, code) {
        console.error('Error al cargar tareas:', xhr, error, code);
        toastr.error('Error al cargar las tareas: ' + error);
      }
    },
    columns: [
      { data: 'id' },
      {
        data: null,
        render: function(data) {
          if (!data.group_name && !data.group_code) {
            return '<span class="text-muted">Sin grupo</span>';
          }
          return `<strong>${data.group_name || 'N/A'}</strong><br><small class="text-muted">${data.group_code || 'N/A'}</small>`;
        }
      },
      { data: 'task_title' },
      {
        data: 'task_description',
        render: function(data) {
          if (!data) return '<span class="text-muted">Sin descripción</span>';
          return data.length > 50 ? data.substring(0, 50) + '...' : data;
        }
      },
      {
        data: 'due_date',
        render: function(data) {
          if (!data) return '<span class="text-muted">Sin fecha</span>';
          // Formatear fecha sin Moment.js
          const date = new Date(data);
          const day = String(date.getDate()).padStart(2, '0');
          const month = String(date.getMonth() + 1).padStart(2, '0');
          const year = date.getFullYear();
          return `${day}/${month}/${year}`;
        }
      },
      {
        data: 'status',
        render: function(data) {
          const badges = {
            'pendiente': '<span class="badge bg-warning">Pendiente</span>',
            'en_progreso': '<span class="badge bg-info">En Progreso</span>',
            'completada': '<span class="badge bg-success">Completada</span>'
          };
          return badges[data] || data;
        }
      },
      {
        data: 'priority',
        render: function(data) {
          const badges = {
            'baja': '<span class="badge bg-secondary">Baja</span>',
            'media': '<span class="badge bg-primary">Media</span>',
            'alta': '<span class="badge bg-danger">Alta</span>'
          };
          return badges[data] || data;
        }
      },
      {
        data: 'created_by_name',
        render: function(data) {
          return data || '<span class="text-muted">N/A</span>';
        }
      },
      {
        data: null,
        orderable: false,
        render: function(data) {
          return `
            <button class="btn btn-sm btn-warning" onclick="edit_admin_task(${data.id})" title="Editar">
              <i data-feather="edit-2" style="width: 14px; height: 14px;"></i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="delete_admin_task(${data.id})" title="Eliminar">
              <i data-feather="trash-2" style="width: 14px; height: 14px;"></i>
            </button>
          `;
        }
      }
    ],
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
      emptyTable: 'No hay tareas registradas',
      zeroRecords: 'No se encontraron tareas',
      loadingRecords: 'Cargando tareas...'
    },
    order: [[0, 'desc']],
    responsive: true,
    drawCallback: function() {
      feather.replace();
    }
  });
}

/**
 * Vincular eventos de administración de tareas
 */
function bind_admin_task_events() {
  // Botón nueva tarea
  $('#admin-btn-new-task').off('click').on('click', function() {
    isEditingAdminTask = false;
    $('#admin-modal-task-title').text('Nueva Tarea');
    $('#admin-form-task')[0].reset();
    $('#admin-task-id').val('');
    $('#admin-modal-task').modal('show');
  });

  // Guardar tarea
  $('#admin-btn-save-task').off('click').on('click', function() {
    if (validate_admin_task_form()) {
      save_admin_task();
    }
  });
}

/**
 * Validar formulario de tarea
 */
function validate_admin_task_form() {
  const title = $('#admin-task-title').val().trim();
  const description = $('#admin-task-description').val().trim();

  if (!title) {
    toastr.warning('Ingrese el título de la tarea');
    return false;
  }

  if (!description) {
    toastr.warning('Ingrese la descripción de la tarea');
    return false;
  }

  return true;
}

/**
 * Guardar tarea (crear o actualizar)
 */
function save_admin_task() {
  // Obtener y validar la fecha
  let dueDate = $('#admin-task-due-date').val();
  if (dueDate) {
    // Validar formato de fecha (YYYY-MM-DD)
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (!dateRegex.test(dueDate)) {
      toastr.error('Formato de fecha inválido. Use el selector de fecha.');
      return;
    }

    // Validar que sea una fecha válida
    const dateObj = new Date(dueDate);
    if (isNaN(dateObj.getTime())) {
      toastr.error('La fecha ingresada no es válida.');
      return;
    }

    // Validar año razonable (entre 2020 y 2100)
    const year = dateObj.getFullYear();
    if (year < 2020 || year > 2100) {
      toastr.error('El año debe estar entre 2020 y 2100.');
      return;
    }
  }

  const groupValue = $('#admin-task-group').val();
  const formData = {
    group_id: groupValue && groupValue !== '' ? groupValue : null,
    task_title: $('#admin-task-title').val().trim(),
    task_description: $('#admin-task-description').val().trim(),
    due_date: dueDate || null,
    status: $('#admin-task-status').val(),
    priority: $('#admin-task-priority').val()
  };

  let url, method;

  if (isEditingAdminTask) {
    formData.id = $('#admin-task-id').val();
    url = BASE_URL + 'Group_Tasks/update_task';
    method = 'PUT';
  } else {
    url = BASE_URL + 'Group_Tasks/create_task';
    method = 'POST';
  }

  $.ajax({
    url: url,
    type: method,
    data: JSON.stringify(formData),
    contentType: 'application/json',
    dataType: 'json',
    success: function(response) {
      if (response.status === 'OK') {
        toastr.success(response.msg);
        $('#admin-modal-task').modal('hide');
        adminTaskTable.ajax.reload();
        load_admin_stats();
      } else {
        toastr.error(response.msg || 'Error al guardar la tarea');
      }
    },
    error: function() {
      toastr.error('Error al comunicarse con el servidor');
    }
  });
}

/**
 * Editar tarea (función global)
 */
window.edit_admin_task = function(id) {
  isEditingAdminTask = true;
  $('#admin-modal-task-title').text('Editar Tarea');

  const rowData = adminTaskTable.rows().data().toArray();
  const task = rowData.find(t => t.id == id);

  if (task) {
    $('#admin-task-id').val(task.id);
    $('#admin-task-group').val(task.group_id);
    $('#admin-task-title').val(task.task_title);
    $('#admin-task-description').val(task.task_description || '');
    $('#admin-task-due-date').val(task.due_date || '');
    $('#admin-task-status').val(task.status);
    $('#admin-task-priority').val(task.priority);
    $('#admin-modal-task').modal('show');
  }
};

/**
 * Eliminar tarea (función global)
 */
window.delete_admin_task = function(id) {
  Swal.fire({
    title: '¿Está seguro?',
    text: 'Esta acción no se puede deshacer',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: BASE_URL + 'Group_Tasks/delete_task',
        type: 'DELETE',
        data: JSON.stringify({ id: id }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
          if (response.status === 'OK') {
            toastr.success(response.msg);
            adminTaskTable.ajax.reload();
            load_admin_stats();
          } else {
            toastr.error(response.msg || 'Error al eliminar la tarea');
          }
        },
        error: function() {
          toastr.error('Error al comunicarse con el servidor');
        }
      });
    }
  });
};

/**
 * ============================================================================
 * INICIALIZACIÓN DEL MÓDULO
 * ============================================================================
 *
 * Se ejecuta cuando el DOM está completamente cargado.
 *
 * ACCIONES:
 * 1. Llama a load_my_info() para cargar los datos del usuario
 * 2. Los datos se renderizan automáticamente en la interfaz
 */
$(document).ready(function() {
  console.log('✓ P_Info: JavaScript cargado correctamente');
  console.log('✓ BASE_URL:', BASE_URL);
  // Cargar la información del usuario al iniciar el módulo
  load_my_info();

  // Botón actualizar tareas
  $(document).on('click', '#btn-refresh-tasks', function() {
    const btn = $(this);
    btn.find('svg').addClass('spin-animation');
    btn.prop('disabled', true);

    load_group_tasks();

    setTimeout(function() {
      btn.find('svg').removeClass('spin-animation');
      btn.prop('disabled', false);
    }, 1000);
  });

  // Botón Iniciar Jornada - cambia todas las tareas pendientes a en_progreso
  $(document).on('click', '#btn-start-work-session', function() {
    const btn = $(this);

    Swal.fire({
      title: '¿Iniciar Jornada Laboral?',
      html: `
        <div class="text-center mb-3">
          <div style="width: 80px; height: 80px; margin: 0 auto 15px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-play" style="font-size: 30px; color: white;"></i>
          </div>
          <p class="text-muted mb-0">Al iniciar tu jornada, todas las tareas <strong>pendientes</strong> de tu grupo cambiarán automáticamente a <span class="badge bg-info">En Progreso</span></p>
        </div>
      `,
      icon: null,
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      confirmButtonText: '<i class="fas fa-play me-1"></i> Sí, Iniciar Jornada',
      cancelButtonText: 'Cancelar',
      customClass: {
        popup: 'swal-wide'
      }
    }).then((result) => {
      if (result.isConfirmed) {
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Iniciando...');

        $.ajax({
          url: BASE_URL + 'Group_Tasks/start_work_session_tasks',
          type: 'POST',
          dataType: 'json',
          contentType: 'application/json',
          success: function(response) {
            btn.prop('disabled', false);
            btn.html('<i data-feather="play-circle" style="width: 16px; height: 16px;"></i> Iniciar Jornada');
            feather.replace();

            if (response.status === 'OK') {
              const tasksUpdated = response.data?.tasks_updated || 0;

              if (tasksUpdated > 0) {
                Swal.fire({
                  icon: 'success',
                  title: '¡Jornada Iniciada!',
                  html: `<p class="mb-0">Se han iniciado <strong>${tasksUpdated}</strong> tarea(s).</p><p class="text-muted small">¡Buen trabajo! Tus tareas ahora están en progreso.</p>`,
                  confirmButtonColor: '#28a745'
                });
              } else {
                Swal.fire({
                  icon: 'info',
                  title: 'Jornada Iniciada',
                  text: 'No había tareas pendientes para iniciar.',
                  confirmButtonColor: '#17a2b8'
                });
              }

              // Recargar las tareas
              load_group_tasks();
            } else {
              toastr.error(response.msg || 'Error al iniciar jornada');
            }
          },
          error: function() {
            btn.prop('disabled', false);
            btn.html('<i data-feather="play-circle" style="width: 16px; height: 16px;"></i> Iniciar Jornada');
            feather.replace();
            toastr.error('Error de comunicación con el servidor');
          }
        });
      }
    });
  });
});
