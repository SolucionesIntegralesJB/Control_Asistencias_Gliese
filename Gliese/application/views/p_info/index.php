    <!--
    ============================================================================
    VISTA: MI INFORMACIÓN (P_Info/index.php)
    ============================================================================
    
    PROPÓSITO:
    Muestra la interfaz de información personal del usuario logueado
    (practicante o supervisor) con diseño profesional y moderno.
    
    ESTRUCTURA:
    1. Breadcrumbs de navegación
    2. Card principal con gradiente púrpura
    3. Header del perfil (avatar + nombre + rol)
    4. Tarjeta de tarea asignada (solo practicantes)
    5. Grid de tarjetas con información detallada
    
    CARACTERÍSTICAS UI:
    - Diseño con gradientes modernos (púrpura, cyan)
    - Avatar grande (160px) con badge de estado online
    - Tarjetas con iconos Feather y animaciones
    - Efectos hover en las tarjetas
    - Diseño completamente responsive
    - Animaciones de entrada escalonadas
    
    ESTILOS CSS:
    - Todos los estilos están embebidos en esta vista
    - Usa clases: profile-main-card, profile-header, profile-avatar,
      task-card, pf-field-card, icon-wrapper
    
    JAVASCRIPT ASOCIADO:
    - application/views/P_Info/js/index.js
    - Se carga automáticamente desde el controlador
    
    DEPENDENCIAS:
    - Bootstrap 5 (grid, cards, utilities)
    - Feather Icons (iconos SVG modernos)
    - jQuery 3.x (manipulación DOM y AJAX)
    
    AUTOR: Sistema Gliese
    FECHA: 2025
    ============================================================================
    -->
    
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row"></div>
            <div class="content-body">
                
                <!-- ================================================ -->
                <!-- SECCIÓN: MI INFORMACIÓN                          -->
                <!-- ================================================ -->
                <section id="info-list">

                    <!-- Breadcrumbs de navegación -->
                    <div class="content-header row">
                        <div class="content-header-left col-md-9 col-12 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Información</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="#"><?php echo $selected_menu; ?></a></li>
                                            <li class="breadcrumb-item active"><span><?php echo $selected_sub_menu; ?></span></li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

<!--
============================================================================
ESTILOS CSS PERSONALIZADOS
============================================================================
Todos los estilos del módulo están definidos aquí para facilitar
la personalización y el mantenimiento.

CLASES PRINCIPALES:
- .profile-main-card: Contenedor principal con sombra
- .profile-header: Header con gradiente púrpura
- .profile-avatar: Avatar circular grande con hover
- .task-card: Tarjeta de tarea asignada (gradiente cyan)
- .pf-field-card: Tarjetas individuales de información
- .icon-wrapper: Contenedor de iconos con fondo suave
============================================================================
-->
<style>
/* ===== GRADIENTES BASE ===== */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.bg-gradient-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
}

/* ===== CARD PRINCIPAL ===== */
.profile-main-card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
}

/* Acento superior para el card principal (franja similar a las tarjetas pequeñas) */
.profile-main-card {
    position: relative; /* permite pseudo-elemento */
}

.profile-main-card::before {
    content: '';
    position: absolute;
    /* full-width decorative accent aligned with card corners */
    left: 0;
    right: 0;
    top: 0;
    height: 10px;
    background: linear-gradient(90deg, #9aa7ff 0%, #7a57d1 60%, #764ba2 100%);
    border-top-left-radius: 20px;
    border-top-right-radius: 20px;
    z-index: 3;
}

.profile-main-card .card-body {
    position: relative;
    z-index: 2; /* garantizar que el contenido esté sobre la franja */
    padding-top: 0.75rem; /* espacio para la franja superior */
}

/* ===== HEADER DEL PERFIL ===== */
/* Contiene: Avatar, nombre, rol */
.profile-header {
    /* ahora fondo blanco para que todo el contenido destaque */
    background: #ffffff;
    padding: 3rem 2rem;
    color: #111827; /* texto oscuro */
    position: relative;
    border-bottom: 1px solid rgba(16,24,32,0.04); /* borde muy sutil */
    box-shadow: inset 0 -12px 28px rgba(16,24,32,0.03); /* sombra interna ligera */
}

.profile-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    /* textura sutil en gris muy claro para no quedar completamente plana */
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(0,0,0,0.03)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
    background-size: cover;
    opacity: 0.06;
}

/* ===== AVATAR DEL USUARIO ===== */
/* Avatar grande circular con efectos */
.profile-avatar {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    border: 6px solid rgba(16,24,32,0.06); /* borde neutro en lugar de blanco */
    box-shadow: 0 8px 24px rgba(16,24,32,0.06);
    object-fit: cover; /* Asegura que la imagen se ajuste bien */
    position: relative;
    z-index: 1;
    transition: all 0.3s ease;
}

/* Efecto hover: zoom suave */
.profile-avatar:hover {
    transform: scale(1.05);
    box-shadow: 0 15px 40px rgba(0,0,0,0.4);
}

/* ===== TEXTOS DEL HEADER ===== */
.profile-name {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0;
    color: #111827;
    line-height: 1.2;
}

.profile-subtitle {
    font-size: 1.05rem;
    color: #58616a;
    margin-top: 0.45rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* ===== BADGE DE ESTADO ONLINE ===== */
/* Círculo verde en la esquina del avatar */
.status-badge {
    width: 24px;
    height: 24px;
    border: 3px solid white;
    border-radius: 50%;
    background: #28c76f;
    position: absolute;
    bottom: 8px;
    right: 8px;
    z-index: 2;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

/* ===== TARJETA DE TAREA ASIGNADA ===== */
/* Solo visible para practicantes */
.task-card {
    /* cambiar celeste a un plomo sutil para menos saturación */
    background: linear-gradient(135deg, #f5f6f8 0%, #eef0f2 100%);
    border: none;
    border-radius: 15px;
    box-shadow: 0 8px 24px rgba(16, 124, 182, 0.06);
    color: #213547; /* texto ligeramente oscuro para contraste */
    padding: 1.5rem;
    margin: 1.5rem 0;
    border: 1px solid rgba(102,126,234,0.08); /* borde sutil con tono púrpura similar */
}

/* Acento superior pequeño para tarjetas grandes (task-card) */
.task-card {
    position: relative;
    overflow: hidden;
    /* fondo más neutro/plomito muy claro para que destaque la franja púrpura */
    background: #f8fafc;
    border: 1px solid rgba(118,75,162,0.06); /* borde sutil con tono púrpura */
    box-shadow: 0 6px 18px rgba(33,37,41,0.04);
}

.task-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 8px; /* ligeramente más delgada que antes */
    /* franja superior en púrpura para coincidir con las tarjetas pequeñas */
    background: linear-gradient(90deg, #9aa7ff 0%, #7a57d1 60%, #764ba2 100%);
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    opacity: 1;
}

.task-card > * {
    position: relative; /* asegurar que el contenido quede sobre la franja */
    z-index: 2;
}

.task-card h5 {
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.task-badge {
    background: linear-gradient(90deg,#9aa7ff 0%, #7a57d1 60%, #764ba2 100%);
    color: #fff;
    padding: 0.45rem 1rem;
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    box-shadow: 0 6px 18px rgba(118,75,162,0.06);
}

/* ===== TARJETAS DE INFORMACIÓN ===== */
/* Cada campo se muestra en una tarjeta individual */
.pf-field-card {
    border: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    background: white;
    border-left: 4px solid transparent;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: relative; /* necesario para el pseudo-elemento de acento */
    overflow: hidden; /* para que la franja superior respete el border-radius */
}

/* Franja superior degradada (acento) similar a P_Recommendations */
.pf-field-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 10px; /* alto de la franja */
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
}

/* Efecto hover: elevación y borde coloreado */
.pf-field-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    border-left-color: #667eea;
}

.pf-field-card .card-body {
    padding: 1.5rem;
    padding-top: 1.25rem; /* pequeño ajuste para separación respecto a la franja */
}

/* Título del campo (ej: "Documento", "Email") */
.pf-field-title {
    color: #667eea;
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Valor del campo (ej: "12345678", "email@ejemplo.com") */
.pf-field-value {
    color: #2b2b2b;
    font-weight: 600;
    font-size: 1.1rem;
    display: block;
    padding: 0.5rem 0;
    border-bottom: 2px solid rgba(102, 126, 234, 0.15);
}

/* ===== ANIMACIONES ===== */
/* Animación de entrada desde abajo */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.pf-field-card {
    animation: fadeInUp 0.5s ease-out;
    /* El delay se aplica inline en el HTML (escalonado) */
}

/* ===== LOADING SKELETON ===== */
/* Animación de carga para estados de loading */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* ===== DISEÑO RESPONSIVE ===== */
/* Adaptación para tablets */
@media (max-width: 768px) {
    .profile-name {
        font-size: 1.5rem;
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
    }
    
    .profile-header {
        padding: 2rem 1rem;
    }
    
    .task-card {
        margin: 1rem 0;
        padding: 1rem;
    }
}

/* Adaptación para móviles */
@media (max-width: 576px) {
    /* En móvil, avatar y nombre se apilan verticalmente */
    .profile-header .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-avatar {
        margin-bottom: 1rem;
    }
}

/* ===== WRAPPER DE ICONOS ===== */
/* Contenedor circular para los iconos Feather */
.icon-wrapper {
    width: 35px;
    height: 35px;
    border-radius: 8px;
    background: rgba(102, 126, 234, 0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
}

/* ===== ANIMACIÓN DE ROTACIÓN ===== */
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.spin-animation {
    animation: spin 1s linear infinite;
}

/* ===== ESTILOS PARA TARJETAS DE TAREAS ===== */
.task-item .card {
    transition: all 0.3s ease;
}

.task-item .card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important;
}

.btn-start-task:hover,
.btn-complete-task:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Asegurar que los botones tengan cursor pointer */
.btn-start-task,
.btn-complete-task,
#btn-refresh-tasks,
#btn-start-work-session {
    cursor: pointer;
    transition: all 0.2s ease;
}

/* Estilo especial para el botón de Iniciar Jornada */
#btn-start-work-session:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4) !important;
}

#btn-start-work-session:active {
    transform: translateY(0);
}

/* Ajuste para SweetAlert personalizado */
.swal-wide {
    width: 450px !important;
}

/* ===== PROFILE CONTENT (cuadro grande que contiene las tarjetas) ===== */
.profile-content {
    background: #f3f4f6; /* plomito muy claro para el cuadro grande */
    border-radius: 12px;
    padding: 1.5rem 1.5rem 2rem 1.5rem; /* mantener el espacio interior */
    box-shadow: 0 6px 20px rgba(16,24,32,0.03);
    border: 1px solid rgba(16,24,32,0.03); /* borde neutro y más sutil */
    position: relative;
    overflow: visible;
}


</style>

                    <!--
                    ================================================
                    CARD PRINCIPAL DEL PERFIL
                    ================================================
                    Contiene toda la información del usuario con diseño moderno.
                    
                    ESTRUCTURA:
                    1. Header con gradiente (avatar + nombre + rol)
                    2. Tarea asignada (solo practicantes)
                    3. Grid de tarjetas con información
                    
                    RENDERIZADO:
                    Todo el contenido se genera dinámicamente desde JavaScript
                    usando los datos obtenidos del backend.
                    -->
                    <div class="card profile-main-card">
                        <div class="card-body p-0">
                            <div id="practicante-profile">
                                
                                <!-- ======================================== -->
                                <!-- HEADER DEL PERFIL                        -->
                                <!-- ======================================== -->
                                <!-- Contiene: Avatar, nombre completo y rol -->
                                <div class="profile-header">
                                    <div class="container-fluid">
                                        <div class="d-flex align-items-center gap-4">
                                            <!-- Avatar con badge de estado online -->
                                            <div class="position-relative">
                                                <img id="pf-avatar" 
                                                     src="<?php echo BASE_URL; ?>public/assets/images/avatar-placeholder.png" 
                                                     alt="Avatar" 
                                                     class="profile-avatar">
                                                <!-- Badge verde de estado online -->
                                                <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" 
                                                      style="width: 24px; height: 24px; border-width: 3px !important;">
                                                </span>
                                            </div>
                                            
                                            <!-- Información del usuario -->
                                            <div class="flex-grow-1">
                                                <!-- Nombre completo (se actualiza desde JS) -->
                                                <h2 id="pf-nombre" class="profile-name">-</h2>
                                                
                                                <!-- Rol del usuario (Practicante/Supervisor) -->
                                                <p class="profile-subtitle mb-0">
                                                    <i data-feather="user" style="width: 18px; height: 18px;"></i>
                                                    <span id="pf-role-text">Perfil del usuario</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ======================================== -->
                                <!-- CONTENIDO DEL PERFIL                     -->
                                <!-- ======================================== -->
                                <div class="p-4 profile-content">
                                    
                                    <!-- ===================================== -->
                                    <!-- TAREA ASIGNADA (Solo Practicantes)   -->
                                    <!-- ===================================== -->
                                    <!-- Se oculta para supervisores desde JS -->
                                    <div id="pf-tarea" style="display:none;">
                                        <div class="task-card">
                                            <h5>
                                                <i data-feather="clipboard" style="width: 22px; height: 22px;"></i>
                                                Tarea Asignada
                                            </h5>
                                            <!-- Texto de la tarea (se actualiza desde JS) -->
                                            <p id="pf-tarea-text" class="mb-0" style="font-size: 1.1rem; font-weight: 500;">
                                                Sin tarea asignada
                                            </p>
                                            <!-- Badge de importancia -->
                                            <span class="task-badge mt-2">
                                                <i data-feather="alert-circle" style="width: 14px; height: 14px;"></i>
                                                Importante
                                            </span>
                                        </div>
                                    </div>

                                    <!-- ===================================== -->
                                    <!-- TAREAS DEL GRUPO (Solo Practicantes) -->
                                    <!-- ===================================== -->
                                    <div id="pf-group-tasks" style="display:none;">
                                        <div class="mt-4">
                                            <!-- Header de sección con estilo mejorado -->
                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3" style="width: 50px; height: 50px; border-radius: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                                                        <i data-feather="clipboard" style="width: 24px; height: 24px; color: white;"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="mb-0 fw-bold" style="color: #1e293b;">Mis Tareas</h4>
                                                        <small class="text-muted">Tareas asignadas a tu grupo de trabajo</small>
                                                    </div>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm" id="btn-start-work-session" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 8px; padding: 8px 16px; font-weight: 600; box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);">
                                                        <i data-feather="play-circle" style="width: 16px; height: 16px;"></i>
                                                        Iniciar Jornada
                                                    </button>
                                                    <button class="btn btn-sm" id="btn-refresh-tasks" style="background: rgba(102, 126, 234, 0.1); color: #667eea; border: none; border-radius: 8px; padding: 8px 16px;">
                                                        <i data-feather="refresh-cw" style="width: 16px; height: 16px;"></i>
                                                        Actualizar
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="group-tasks-container">
                                                <!-- Las tareas se cargarán aquí desde JavaScript -->
                                                <div class="text-center py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 16px;">
                                                    <div class="spinner-border text-primary mb-3" role="status">
                                                        <span class="visually-hidden">Cargando...</span>
                                                    </div>
                                                    <p class="text-muted mb-0">Cargando tareas...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ===================================== -->
                                    <!-- GRID DE TARJETAS DE INFORMACIÓN      -->
                                    <!-- ===================================== -->
                                    <!--
                                    Este contenedor se llena dinámicamente desde JavaScript.

                                    PRACTICANTES (12 tarjetas):
                                    - Documento, Tipo documento, Email, Teléfono
                                    - Institución, Especialidad, Área de interés
                                    - Fecha inicio, Fecha término, Fecha nacimiento
                                    - Ciclo, Modalidad

                                    SUPERVISORES (7 tarjetas):
                                    - Documento, Tipo documento, Email, Teléfono
                                    - Cargo, Área de supervisión, Fecha ingreso
                                    -->
                                    <div id="practicante-cards" class="row g-4">
                                        <!-- Las tarjetas se insertan aquí desde JavaScript -->
                                    </div>

                                    <!-- ===================================== -->
                                    <!-- GESTIÓN DE TAREAS (Solo Administrador) -->
                                    <!-- ===================================== -->
                                    <div id="admin-tasks-section" style="display:none;">
                                        <!-- Estadísticas -->
                                        <div class="row mb-4">
                                            <div class="col-xl-3 col-lg-6 col-12 mb-3">
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar bg-light-primary p-3 me-3 rounded">
                                                                <i data-feather="list" class="text-primary" style="width: 24px; height: 24px;"></i>
                                                            </div>
                                                            <div>
                                                                <h3 id="admin-stat-total" class="mb-0 fw-bold">0</h3>
                                                                <p class="mb-0 text-muted small">Total de Tareas</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-lg-6 col-12 mb-3">
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar bg-light-warning p-3 me-3 rounded">
                                                                <i data-feather="clock" class="text-warning" style="width: 24px; height: 24px;"></i>
                                                            </div>
                                                            <div>
                                                                <h3 id="admin-stat-pending" class="mb-0 fw-bold">0</h3>
                                                                <p class="mb-0 text-muted small">Pendientes</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-lg-6 col-12 mb-3">
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar bg-light-info p-3 me-3 rounded">
                                                                <i data-feather="activity" class="text-info" style="width: 24px; height: 24px;"></i>
                                                            </div>
                                                            <div>
                                                                <h3 id="admin-stat-in-progress" class="mb-0 fw-bold">0</h3>
                                                                <p class="mb-0 text-muted small">En Progreso</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-xl-3 col-lg-6 col-12 mb-3">
                                                <div class="card border-0 shadow-sm">
                                                    <div class="card-body">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar bg-light-success p-3 me-3 rounded">
                                                                <i data-feather="check-circle" class="text-success" style="width: 24px; height: 24px;"></i>
                                                            </div>
                                                            <div>
                                                                <h3 id="admin-stat-completed" class="mb-0 fw-bold">0</h3>
                                                                <p class="mb-0 text-muted small">Completadas</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Botón para crear nueva tarea -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="mb-0">
                                                <i data-feather="clipboard" style="width: 24px; height: 24px;"></i>
                                                Listado de Tareas
                                            </h4>
                                            <button class="btn btn-primary" id="admin-btn-new-task">
                                                <i data-feather="plus" style="width: 18px; height: 18px;"></i>
                                                Nueva Tarea
                                            </button>
                                        </div>

                                        <!-- Tabla de tareas -->
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <table class="table table-hover" id="admin-table-tasks">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Grupo</th>
                                                            <th>Tarea</th>
                                                            <th>Descripción</th>
                                                            <th>Fecha Límite</th>
                                                            <th>Estado</th>
                                                            <th>Prioridad</th>
                                                            <th>Creado Por</th>
                                                            <th>Acciones</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </section>
                <!-- Info Ends -->
            </div>
        </div>
    </div>
    <!-- END: Content-->

    <!-- Modal Detalle Info -->
    <div class="modal fade" id="modal-info-detail" tabindex="-1" aria-labelledby="modalInfoDetailLabel">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInfoDetailLabel">Detalle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="detail-id">
                    <h5 id="detail-titulo" class="fw-bold"></h5>
                    <p id="detail-subtitulo" class="text-muted"></p>
                    <p><strong>Autor:</strong> <span id="detail-autor"></span></p>
                    <hr>
                    <div id="detail-contenido"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Info -->
    <div class="modal fade" id="modal-info-form" tabindex="-1" aria-labelledby="modalInfoFormLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInfoFormLabel">Nuevo Registro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="form-id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Título</label>
                        <input type="text" class="form-control" id="form-titulo" placeholder="Título">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subtítulo</label>
                        <input type="text" class="form-control" id="form-subtitulo" placeholder="Subtítulo (opcional)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Autor</label>
                        <input type="text" class="form-control" id="form-autor" placeholder="Autor (opcional)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contenido</label>
                        <textarea class="form-control" id="form-contenido" rows="8" placeholder="Contenido"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-save-info">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva/Editar Tarea (Admin) -->
    <div class="modal fade" id="admin-modal-task" tabindex="-1" aria-labelledby="adminModalTaskLabel">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="admin-modal-task-title">Nueva Tarea</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="admin-form-task">
                        <input type="hidden" id="admin-task-id" name="id">

                        <div class="mb-3">
                            <label for="admin-task-group" class="form-label">Grupo (opcional)</label>
                            <select class="form-control" id="admin-task-group" name="group_id">
                                <option value="">Sin grupo asignado</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="admin-task-title" class="form-label">Título de la Tarea <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="admin-task-title" name="task_title" required maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label for="admin-task-description" class="form-label">Descripción <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="admin-task-description" name="task_description" rows="4" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="admin-task-due-date" class="form-label">Fecha Límite</label>
                                    <input type="date" class="form-control" id="admin-task-due-date" name="due_date" min="2020-01-01" max="2100-12-31">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="admin-task-status" class="form-label">Estado</label>
                                    <select class="form-control" id="admin-task-status" name="status">
                                        <option value="pendiente">Pendiente</option>
                                        <option value="en_progreso">En Progreso</option>
                                        <option value="completada">Completada</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="admin-task-priority" class="form-label">Prioridad</label>
                                    <select class="form-control" id="admin-task-priority" name="priority">
                                        <option value="baja">Baja</option>
                                        <option value="media" selected>Media</option>
                                        <option value="alta">Alta</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="admin-btn-save-task">Guardar Tarea</button>
                </div>
            </div>
        </div>
    </div>
