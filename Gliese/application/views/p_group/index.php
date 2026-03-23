<!-- Vista de Grupos (P_Group) - Interfaz Mejorada -->
<style>
    /* Variables de color */
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --card-shadow: 0 2px 8px rgba(0,0,0,0.08);
        --card-shadow-hover: 0 8px 24px rgba(102, 126, 234, 0.2);
    }

    /* Sección principal */
    #my-group {
        animation: fadeInUp 0.5s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Header mejorado */
    .groups-header {
        position: relative; /* permite pseudo-elemento de franja */
        background: #ffffff; /* banner blanco ahora */
        border-radius: 12px;
        padding: 24px;
        color: #111827;
        margin-bottom: 24px;
        box-shadow: 0 6px 18px rgba(16,24,32,0.04);
        overflow: visible;
    }

    /* franja púrpura superior en el banner principal */
    .groups-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 10px;
        background: linear-gradient(90deg, #9aa7ff 0%, #7a57d1 60%, #764ba2 100%);
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        z-index: 3;
    }

    .groups-header h2 {
        color: #111827 !important;
        margin: 0;
        font-weight: 600;
    }

    .groups-stats {
        display: flex;
        gap: 20px;
        margin-top: 12px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(102,126,234,0.06); /* sutil sobre blanco */
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        color: #374151;
    }

    /* Búsqueda mejorada */
    .search-wrapper {
        position: relative;
        margin-bottom: 16px;
    }

    .search-wrapper i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        width: 18px;
        height: 18px;
    }

    #pg-search {
        padding-left: 40px;
        border-radius: 20px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    #pg-search:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Tarjetas de grupos mejoradas */
    .pg-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        background: white;
        box-shadow: var(--card-shadow);
        margin-bottom: 12px;
        overflow: hidden;
        position: relative; /* para pseudo-elemento */
    }

    /* franja púrpura superior para las tarjetas pequeñas */
    .pg-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 8px;
        background: linear-gradient(90deg, #9aa7ff 0%, #7a57d1 60%, #764ba2 100%);
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        z-index: 2;
    }

    .pg-card:hover {
        transform: translateX(4px) translateY(-2px);
        box-shadow: var(--card-shadow-hover);
    }

    .pg-card .card-body {
        padding: 16px;
        position: relative;
        z-index: 3; /* contenido sobre la franja */
    }

    .pg-card-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
        font-size: 15px;
    }

    .pg-card-desc {
        color: #6c757d;
        font-size: 13px;
        line-height: 1.4;
    }

    .pg-card-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        background: var(--success-gradient);
        color: white;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    /* Panel de detalle mejorado */
    .detail-card {
        border: none;
        border-radius: 12px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        position: relative; /* para la franja superior */
    }

    /* franja superior para el cuadro grande (detalle) */
    .detail-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 10px;
        background: linear-gradient(90deg, #9aa7ff 0%, #7a57d1 60%, #764ba2 100%);
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        z-index: 3;
    }

    .detail-header {
        background: #ffffff; /* header blanco para el detalle */
        padding: 32px;
        text-align: center;
        color: #111827;
        position: relative;
        z-index: 2;
        border-bottom: 1px solid rgba(16,24,32,0.03);
    }

    .pg-avatar-lg {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(90deg, #9aa7ff 0%, #7a57d1 60%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin: 0 auto 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        border: 4px solid rgba(255,255,255,0.12);
        transition: all 0.3s ease;
    }

    .pg-avatar-lg:hover {
        transform: scale(1.05);
    }

    .detail-header h4 {
        color: white;
        margin: 0 0 8px 0;
        font-weight: 600;
        font-size: 24px;
    }

    .detail-header p {
        color: rgba(255,255,255,0.9);
        margin: 0;
    }

    .info-card {
        border: none;
        border-radius: 12px;
        background: #ffffff; /* mantener blanco en las info-cards internas */
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }

    .info-card h6 {
        color: #667eea;
        font-weight: 600;
        margin-bottom: 12px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-item {
        display: flex;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-item strong {
        min-width: 120px;
        color: #495057;
        font-size: 13px;
    }

    .info-item span {
        color: #6c757d;
        font-size: 13px;
    }

    /* Botones mejorados */
    .btn-group-action {
        border-radius: 10px;
        padding: 12px 20px;
        font-weight: 500;
        border: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-group-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-group-action.btn-primary {
        background: var(--primary-gradient);
    }

    .btn-group-action.btn-outline-primary {
        border: 2px solid #667eea;
        color: #667eea;
        background: white;
    }

    .btn-group-action.btn-outline-primary:hover {
        background: var(--primary-gradient);
        color: white;
        border-color: transparent;
    }

    .btn-group-action.btn-outline-danger {
        border: 2px solid #dc3545;
        color: #dc3545;
        background: white;
    }

    .btn-group-action.btn-outline-danger:hover {
        background: #dc3545;
        color: white;
    }

    /* Mensaje vacío */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: var(--primary-gradient);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .empty-state h5 {
        color: #495057;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #6c757d;
        margin: 0;
    }

    /* Modal mejorado */
    .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .modal-header {
        background: var(--primary-gradient);
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 20px 24px;
    }

    .modal-header .modal-title {
        color: white;
        font-weight: 600;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-body {
        padding: 24px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }

    .form-control, .form-select {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 10px 14px;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Estilos para DataTable */
    #pg-groups-table {
        font-size: 14px;
    }

    #pg-groups-table thead th {
        background: linear-gradient(90deg, #9aa7ff 0%, #7a57d1 60%, #764ba2 100%);
        color: white;
        font-weight: 600;
        border: none;
        padding: 12px 8px;
        font-size: 13px;
    }

    #pg-groups-table tbody tr {
        transition: all 0.2s ease;
    }

    #pg-groups-table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
        transform: scale(1.01);
    }

    #pg-groups-table tbody td {
        vertical-align: middle;
        padding: 10px 8px;
    }

    /* Estilo de fila de horarios en modal */
    .schedule-row {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 8px;
        border-left: 3px solid #667eea;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .groups-header {
            padding: 20px;
        }
        
        .detail-header {
            padding: 24px;
        }
        
        .pg-avatar-lg {
            width: 80px;
            height: 80px;
        }
    }
</style>

<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-body">
            
            <section id="my-group">

                <!-- Header mejorado con gradiente -->
                <div class="groups-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h2 class="mb-0">
                                <i data-feather="settings" style="width: 32px; height: 32px;"></i>
                                Configuración de Grupos
                            </h2>
                            <p class="text-muted mb-0 mt-1">Gestiona los enlaces de reuniones y la asignación de supervisores para los grupos de practicantes</p>
                        </div>
                    </div>
                </div>

                <!-- Información de Reuniones del Grupo (visible para practicantes) -->
                <div class="row" id="group-meeting-info" style="display: none;">
                    <div class="col-12">
                        <div class="card info-card">
                            <div class="card-body">
                                <h6><i data-feather="video"></i> INFORMACIÓN DE REUNIONES</h6>
                                <div id="meeting-info-content">
                                    <div class="alert alert-info">
                                        <i data-feather="info"></i> No hay enlace de reunión configurado para este grupo.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABS DE NAVEGACIÓN -->
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="links-tab" data-bs-toggle="tab" href="#links-section" role="tab" aria-selected="true">
                                    <i data-feather="link"></i> Enlaces de Grupos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="supervisors-tab" data-bs-toggle="tab" href="#supervisors-section" role="tab" aria-selected="false">
                                    <i data-feather="user-check"></i> Asignación de Supervisores
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="schedules-tab" data-bs-toggle="tab" href="#schedules-section" role="tab" aria-selected="false">
                                    <i data-feather="clock"></i> Configuración de Horarios
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- CONTENIDO DE LOS TABS -->
                <div class="tab-content">
                    <!-- TAB 1: Enlaces de Grupos -->
                    <div class="tab-pane fade show active" id="links-section" role="tabpanel">
                        <div class="row">
                            <div class="col-12">
                                <div class="card detail-card">
                                    <div class="card-body">
                                        <div class="row">
                                    <!-- Panel izquierdo: Crear nuevo enlace -->
                                    <div class="col-lg-5 border-end">
                                        <h6 class="fw-bold mb-3"><i data-feather="plus-circle"></i> Crear Nuevo Enlace</h6>
                                        <div class="mb-3">
                                            <label class="form-label">Nombre del Enlace:</label>
                                            <input type="text" class="form-control" id="new-link-name" placeholder="Ej: Reunión Semanal">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">URL de Google Meet:</label>
                                            <input type="url" class="form-control" id="new-link-url" placeholder="https://meet.google.com/xxx-xxxx-xxx">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Descripción:</label>
                                            <textarea class="form-control" id="new-link-desc" rows="3" placeholder="Descripción opcional"></textarea>
                                        </div>
                                        <button type="button" class="btn btn-success w-100" id="btn-create-link">
                                            <i data-feather="save"></i> Crear Enlace
                                        </button>
                                    </div>

                                    <!-- Panel derecho: Lista de enlaces existentes -->
                                    <div class="col-lg-7">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold mb-0"><i data-feather="list"></i> Enlaces Existentes</h6>
                                        </div>
                                        <div class="mb-2">
                                            <input type="search" id="links-search" class="form-control" placeholder="Buscar enlaces por nombre o descripción">
                                        </div>
                                        <div id="links-list" style="max-height: 480px; overflow-y: auto;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    </div><!-- /links-section -->

                    <!-- TAB 2: Asignación de Supervisores -->
                    <div class="tab-pane fade" id="supervisors-section" role="tabpanel">
                        <div class="row">
                            <div class="col-12">
                                <div class="card detail-card">
                                    <div class="card-body" id="supervisor-assignment-content">
                                        <!-- Contenido de asignación de supervisores -->
                                        <div class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            <p class="mt-3 text-muted">Cargando asignación de supervisores...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Configuración de Horarios -->
                    <div class="tab-pane fade" id="schedules-section" role="tabpanel">
                        <div class="row">
                            <div class="col-12">
                                <!-- Formulario de configuración de horarios -->
                                <?php include APP_PATH . 'views/p_group/partials/schedule_config/config_form.php'; ?>
                            </div>
                        </div>
                    </div>
                </div><!-- /tab-content -->


                <!-- Script de inicialización (gráfica de ejemplo y plantilla de lista) -->
                <script>
                document.addEventListener('DOMContentLoaded', function(){
                    // Cargar enlaces desde backend usando la función existente en js/index.js
                    if (typeof load_meet_links === 'function') {
                        load_meet_links();
                    }

                    // Buscador: filtra meet_links_data usando la función render_meet_links definida en js/index.js
                    const search = document.getElementById('links-search');
                    if(search){
                        search.addEventListener('input', function(){
                            const q = this.value.trim().toLowerCase();
                            if(typeof meet_links_data === 'undefined' || q === ''){
                                // si no hay query o datos, mostrar todos (render_meet_links será llamada por load_meet_links)
                                if(typeof render_meet_links === 'function' && typeof meet_links_data !== 'undefined'){
                                    render_meet_links(meet_links_data);
                                }
                                return;
                            }

                            const filtered = (meet_links_data || []).filter(l => {
                                const name = (l.name || '').toLowerCase();
                                const desc = (l.description || l.desc || '').toLowerCase();
                                const url = (l.meet_url || l.url || '').toLowerCase();
                                return name.includes(q) || desc.includes(q) || url.includes(q);
                            });

                            if(typeof render_meet_links === 'function'){
                                render_meet_links(filtered);
                            }
                        });
                    }
                });
                </script>

            </section>

        </div>
    </div>
</div>
<!-- END: Content-->

<!-- Modal Configurar Reuniones (Meet + Horarios) -->
<div class="modal fade" id="modal-meeting-config" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i data-feather="video"></i> Configurar Reuniones del Grupo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="meeting-group-id">

                <!-- Nombre del grupo (solo información) -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i data-feather="folder"></i> Grupo:
                    </label>
                    <p class="form-control-plaintext fw-bold text-primary" id="meeting-group-name"></p>
                </div>

                <!-- Link de Meet -->
                <div class="mb-3">
                    <label class="form-label fw-bold" for="meeting-link">
                        <i data-feather="link"></i> Enlace de Google Meet:
                    </label>
                    <input type="url" class="form-control" id="meeting-link"
                           placeholder="https://meet.google.com/xxx-xxxx-xxx">
                    <small class="text-muted">Ingresa el enlace completo de Google Meet para las reuniones del grupo</small>
                </div>

                <!-- Horarios de Reunión -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i data-feather="calendar"></i> Horarios de Reunión:
                    </label>
                    <div id="schedules-container"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn-add-schedule">
                        <i data-feather="plus"></i> Agregar Horario
                    </button>
                    <small class="text-muted d-block mt-1">Agrega los días y horarios en que se realizarán las reuniones</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i data-feather="x"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btn-save-meeting-config">
                    <i data-feather="save"></i> Guardar Configuración
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Asignar Supervisor a Grupo (para tab Asignación de Supervisores) -->
<div class="modal fade" id="modal-assign-supervisor" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <!-- Header con gradiente -->
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="modal-title d-flex align-items-center">
                    <i data-feather="user-plus" class="me-2"></i>
                    Asignar Supervisor a Grupo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="form-assign">
                    <div class="row">
                        <!-- Panel izquierdo: Selección de Supervisor -->
                        <div class="col-md-6">
                            <div class="card border-0 bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary mb-3">
                                        <i data-feather="users" style="width: 16px; height: 16px;"></i>
                                        Seleccionar Supervisor
                                    </h6>

                                    <div class="mb-3">
                                        <label for="select-supervisor-group" class="form-label fw-semibold small text-uppercase text-muted">
                                            Grupo de Supervisores
                                        </label>
                                        <select class="form-select" id="select-supervisor-group">
                                            <option value="">Seleccione un grupo...</option>
                                        </select>
                                        <small class="text-muted">Al elegir un grupo, se listarán los supervisores.</small>
                                    </div>

                                    <div class="mb-0">
                                        <label for="select-supervisor" class="form-label fw-semibold small text-uppercase text-muted">
                                            Supervisor <span class="text-secondary fw-normal">(opcional)</span>
                                        </label>
                                        <select class="form-select" id="select-supervisor">
                                            <option value="">Seleccione un supervisor...</option>
                                        </select>
                                        <small class="text-muted">Si no selecciona, se usarán todos los del grupo.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="assignment-notes" class="form-label fw-semibold small text-uppercase text-muted">
                                    <i data-feather="file-text" style="width: 14px; height: 14px;"></i>
                                    Notas <span class="text-secondary fw-normal">(opcional)</span>
                                </label>
                                <textarea class="form-control" id="assignment-notes" rows="3" placeholder="Agregue notas o comentarios sobre esta asignación..."></textarea>
                            </div>
                        </div>

                        <!-- Panel derecho: Grupos de Practicantes -->
                        <div class="col-md-6">
                            <div class="card border-0 h-100" style="background: linear-gradient(180deg, #f8f9fa 0%, #fff 100%);">
                                <div class="card-body">
                                    <h6 class="card-title d-flex justify-content-between align-items-center mb-3">
                                        <span class="text-success">
                                            <i data-feather="check-square" style="width: 16px; height: 16px;"></i>
                                            Grupos de Practicantes
                                        </span>
                                        <span class="badge bg-danger">Requerido</span>
                                    </h6>

                                    <div id="practicant-groups-container" class="border rounded p-2" style="max-height: 280px; overflow-y: auto; background: #fff;">
                                        <div class="text-center text-muted py-3">
                                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                            Cargando grupos...
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2">
                                        <i data-feather="info" style="width: 12px; height: 12px;"></i>
                                        Seleccione uno o más grupos para asignar al supervisor.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i data-feather="x" class="me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary px-4" id="btn-save-supervisor-assignment" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i data-feather="check" class="me-1"></i>
                    Asignar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Gestionar Enlaces Meet -->
<div class="modal fade" id="modal-manage-links" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i data-feather="link"></i> Gestionar Enlaces de Google Meet
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Panel izquierdo: Crear nuevo enlace -->
                    <div class="col-lg-5 border-end">
                        <h6 class="fw-bold mb-3"><i data-feather="plus-circle"></i> Crear Nuevo Enlace</h6>
                        <div class="mb-3">
                            <label class="form-label">Nombre del Enlace:</label>
                            <input type="text" class="form-control" id="new-link-name" placeholder="Ej: Reunión Semanal">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">URL de Google Meet:</label>
                            <input type="url" class="form-control" id="new-link-url" placeholder="https://meet.google.com/xxx-xxxx-xxx">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción:</label>
                            <textarea class="form-control" id="new-link-desc" rows="3" placeholder="Descripción opcional"></textarea>
                        </div>
                        <button type="button" class="btn btn-success w-100" id="btn-create-link">
                            <i data-feather="save"></i> Crear Enlace
                        </button>
                    </div>

                    <!-- Panel derecho: Lista de enlaces existentes -->
                    <div class="col-lg-7">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0"><i data-feather="list"></i> Enlaces Existentes</h6>
                        </div>
                        <div id="links-list" style="max-height: 400px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Asignar Enlace a Grupos -->
<div class="modal fade" id="modal-assign-link" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i data-feather="users"></i> Asignar Enlace a Grupos
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="assign-link-id">
                <div class="alert alert-info">
                    <strong>Enlace:</strong> <span id="assign-link-name"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Selecciona los grupos:</label>
                    <div id="groups-checkboxes" style="max-height: 300px; overflow-y: auto;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-assignment">
                    <i data-feather="check"></i> Asignar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ver Horarios de Practicantes -->
<div class="modal fade" id="modal-student-schedules" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i data-feather="calendar"></i> Horarios de Practicantes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table id="schedules-table" class="table table-hover" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Practicante</th>
                            <th>Grupo</th>
                            <th>Archivo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Subir Horario del Grupo -->
<div class="modal fade" id="modal-upload-schedule" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i data-feather="upload"></i> <span id="modal-schedule-title">Gestión de Horario del Grupo</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="upload-schedule-group-id">

                <!-- Información del grupo -->
                <div class="alert alert-info">
                    <strong>Grupo:</strong> <span id="upload-schedule-group-name"></span>
                    <span id="modification-counter" class="ms-3"></span>
                </div>

                <!-- Contenedor para horario guardado (visible solo si existe) -->
                <div id="saved-schedule-container" style="display: none;">
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i data-feather="calendar"></i> Horario Guardado
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="schedule-calendar-view"></div>
                        </div>
                    </div>
                </div>

                <!-- Contenedor para subir nuevo horario (visible si no existe o después de eliminar) -->
                <div id="upload-schedule-container">
                    <!-- Paso 1: Descargar plantilla -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i data-feather="download"></i> Paso 1: Descargar Plantilla
                            </h6>
                        </div>
                        <div class="card-body">
                            <p>Descarga la plantilla Excel y ábrela para rellenar tu horario.</p>
                            <button type="button" class="btn btn-success" id="btn-download-schedule-template">
                                <i data-feather="download"></i> Descargar Plantilla Excel
                            </button>
                        </div>
                    </div>

                    <!-- Paso 2: Rellenar plantilla -->
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i data-feather="edit"></i> Paso 2: Rellenar la Plantilla
                            </h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Instrucciones:</strong></p>
                            <ul class="small">
                                <li><strong>Tipos de actividad:</strong> Trabajo, Estudio, Reunión o Descanso</li>
                                <li><strong>Las celdas se colorearán automáticamente</strong> según el tipo de actividad</li>
                                <li><strong>Puedes dejar muchas celdas vacías</strong> si no tienes actividades programadas</li>
                            </ul>
                            <div class="alert alert-warning small">
                                <strong>⚠️ Importante:</strong> Las reuniones deben durar exactamente 1 hora y no pueden repetirse con otro grupo.
                            </div>
                        </div>
                    </div>

                    <!-- Paso 3: Subir archivo -->
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i data-feather="upload"></i> Paso 3: Subir Archivo
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Selecciona el archivo CSV rellenado:</label>
                                <input type="file" class="form-control" id="schedule_file_input" accept=".csv">
                                <small class="text-muted">Formato aceptado: CSV (delimitado por comas). Convierte tu Excel a CSV antes de subir.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i data-feather="x"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btn-delete-schedule" style="display: none;">
                    <i data-feather="trash-2"></i> Eliminar Horario
                </button>
                <button type="button" class="btn btn-primary" id="btn-upload-schedule-csv" style="display: none;">
                    <i data-feather="upload"></i> Subir Horario
                </button>
            </div>
        </div>
    </div>
</div>
