<!--
========================================
VISTA: RECOMENDACIONES (index.php)
========================================

DESCRIPCIÓN:
Esta vista muestra el módulo de Recomendaciones del sistema.
Utiliza un diseño de tarjetas (cards) responsive para mostrar las recomendaciones.

ESTRUCTURA:
- Header con breadcrumbs de navegación
- Botón "Agregar Recomendación" (visible solo para administradores)
- Grid de tarjetas con las recomendaciones
- Modal para ver detalle completo
- Modal con formulario para crear/editar

COMPONENTES:

1. CARD PRINCIPAL:
   - Título: "Listado de Recomendaciones"
   - Botón: "Agregar Recomendación" (oculto por defecto, mostrado por JS si es admin)
   - Contenedor: #recomendaciones-cards (grid de tarjetas)
   - Mensaje vacío: #recomendaciones-empty (mostrado si no hay recomendaciones)

2. MODAL DETALLE (#modal-recomendacion-detail):
   - Muestra título y contenido completo de la recomendación
   - Campos: detail-id, detail-titulo, detail-contenido
   - Tamaño: modal-lg (grande)
   - Scrollable: modal-dialog-scrollable

3. MODAL FORMULARIO (#modal-recomendacion-form):
   - Usado para crear o editar recomendaciones
   - Campos: form-id (hidden), form-titulo, form-contenido
   - Botón guardar: #btn-save-recomendacion
   - Tamaño: modal-lg (grande)

LAYOUT RESPONSIVE:
- Cards: col-12 col-sm-6 col-lg-4
  * Móvil: 1 columna
  * Tablet: 2 columnas  
  * Desktop: 3 columnas

ACCIONES:
- Ver detalle (todos los usuarios)
- Crear/Editar/Eliminar (solo administradores)

DEPENDENCIAS:
- Bootstrap 5 (grid, cards, modals)
- Feather Icons (iconos en botones)
- index.js (lógica JavaScript)

AUTOR: Sistema Gliese
FECHA CREACIÓN: Octubre 2025
========================================
-->

<style>
/* Estilos personalizados para el módulo de Recomendaciones
   NOTA: Removidos gradientes locales (se usan overrides globales)
   para que los colores aparezcan sólo como acentos/bordes. */
.recommendation-card {
    animation: fadeInUp 0.5s ease-out;
    position: relative;
    background: #f3f5f8; /* plomito claro */
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(16,24,40,0.06);
    border: 1px solid rgba(0,0,0,0.04);
}

/* Franja superior fina (accent-top) en las tarjetas pequeñas */
.recommendation-card { position: relative; overflow: visible; }
.recommendation-card::before {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    height: 6px; /* grosor de la franja */
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    z-index: 2;
}

/* Acento: hacer que el interior de la tarjeta (contenido) tenga fondo blanco
   para resaltar el contenido sobre el plomito del contenedor */
.recommendation-card .card-body {
    background: #ffffff;
    border-radius: 10px;
    position: relative;
    z-index: 1;
    padding-top: 1rem;
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
}

.badge {
    font-weight: 500;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
}

.btn-outline-info:hover,
.btn-outline-warning:hover,
.btn-outline-danger:hover {
    transform: scale(1.05);
}

.recommendation-card .card-text {
    color: #6c757d;
    text-align: justify;
}

.modal-content {
    border-radius: 15px;
}

#btn-add-recomendacion {
    transition: all 0.3s ease;
}

#btn-add-recomendacion:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.modal-dialog-scrollable .modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-dialog-scrollable .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-dialog-scrollable .modal-body::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 10px;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
    100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0); }
}

#btn-save-recomendacion:hover {
    animation: pulse 1.5s infinite;
}

#form-contenido {
    resize: vertical;
    min-height: 200px;
}

@media (max-width: 768px) {
    .card-header h3 {
        font-size: 1.25rem !important;
    }
    
    .btn-group .btn {
        font-size: 0.85rem;
        padding: 0.5rem 0.75rem;
    }
}
/* Franja superior fina (accent-top) en el cuadro principal */
.main-recommendations { position: relative; overflow: visible; }
.main-recommendations::before {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    height: 6px;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    background: linear-gradient(135deg,#7367F0 0%,#9E95F5 100%);
    z-index: 2;
}
</style>

    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row"></div>
            <div class="content-body">
                <!-- Recomendaciones Starts -->
                <section id="recomendaciones-list">

                    <div class="content-header row">
                        <div class="content-header-left col-md-9 col-12 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Recomendaciones</h2>
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

                    <div class="card shadow-lg border-0 main-recommendations">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center py-3">
                            <div>
                                <h3 class="card-title mb-1 fw-bold">
                                    <i data-feather="bookmark" class="me-2"></i>
                                    Recomendaciones del Sistema
                                </h3>
                                <p class="text-muted mb-0 small">Consejos y mejores prácticas para optimizar tu trabajo</p>
                            </div>
                            <div>
                                <!-- El botón queda oculto por defecto y se mostrará sólo si el usuario es administrador -->
                                <button type="button" class="btn btn-light btn-lg d-none shadow-sm" id="btn-add-recomendacion">
                                    <i data-feather="plus-circle" class="me-1"></i> Nueva Recomendación
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-4" style="background: #f3f5f8;">
                            <!-- Contenedor de tarjetas -->
                            <div id="recomendaciones-cards" class="row g-4">
                                <!-- Cards dinámicas -->
                            </div>
                            <div id="recomendaciones-empty" class="text-center py-5" style="display:none;">
                                <i data-feather="inbox" style="width: 64px; height: 64px; color: #cbd5e0;"></i>
                                <p class="text-muted mt-3 mb-0 fs-5">No hay recomendaciones para mostrar</p>
                                <p class="text-muted small">Las recomendaciones aparecerán aquí cuando sean creadas</p>
                            </div>
                        </div>
                    </div>

                </section>
                <!-- Recomendaciones Ends -->
            </div>
        </div>
    </div>
    <!-- END: Content-->

    <!-- Modal Detalle Recomendacion -->
    <div class="modal fade" id="modal-recomendacion-detail" tabindex="-1" aria-labelledby="modalRecomendacionDetailLabel">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-gradient-info border-0">
                    <h5 class="modal-title fw-bold" id="modalRecomendacionDetailLabel">Detalle de la Recomendación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="detail-id">
                    <div class="mb-4">
                        <h3 id="detail-titulo" class="fw-bold text-primary mb-3"></h3>
                    </div>
                    <hr class="my-4">
                    <div id="detail-contenido" class="fs-6 lh-lg text-dark"></div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Recomendacion (Crear / Editar) -->
    <div class="modal fade" id="modal-recomendacion-form" tabindex="-1" aria-labelledby="modalRecomendacionFormLabel">
        <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient-primary border-0">
                    <h5 class="modal-title fw-bold" id="modalRecomendacionFormLabel">
                        <i data-feather="edit-3" class="me-2"></i>
                        Nueva Recomendación
                    </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="form-id">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2">
                            <i data-feather="type" style="width: 16px; height: 16px;"></i>
                            Título de la Recomendación <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-lg shadow-sm" id="form-titulo" 
                               placeholder="Ej: Mantén actualizado tu inventario" 
                               style="border-radius: 10px;">
                        <small class="form-text text-muted">Escribe un título claro y descriptivo</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-2">
                            <i data-feather="file-text" style="width: 16px; height: 16px;"></i>
                            Contenido <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control shadow-sm" id="form-contenido" rows="10" 
                                  placeholder="Describe la recomendación de manera detallada..."
                                  style="border-radius: 10px;"></textarea>
                        <small class="form-text text-muted">Proporciona información útil y práctica</small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                        <i data-feather="x" class="me-1"></i>
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary px-4 shadow-sm" id="btn-save-recomendacion">
                        <i data-feather="save" class="me-1"></i>
                        Guardar Recomendación
                    </button>
                </div>
            </div>
        </div>
    </div>

