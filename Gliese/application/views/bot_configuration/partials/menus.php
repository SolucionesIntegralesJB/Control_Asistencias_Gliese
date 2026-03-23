<!-- Acordeón Principal: Menús -->
<div class="accordion accordion-margin" id="accordionMenus">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingMenus">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMenus" aria-expanded="true">
                <i data-feather="menu" class="me-2"></i> Menús del Bot
            </button>
        </h2>
        <div id="collapseMenus" class="accordion-collapse collapse show" aria-labelledby="headingMenus">
            <div class="accordion-body">
                <p class="text-muted mb-4">Gestione los menús principales del bot de WhatsApp.</p>

                <button type="button" class="btn btn-success btn-sm mb-3" id="btn-add-menu">
                    <i data-feather="plus" class="me-1"></i> Nuevo Menú
                </button>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Etiqueta</th>
                                <th>Acción</th>
                                <th>Permite Registro</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-menus">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acordeón: Opciones de Menú -->
<div class="accordion accordion-margin mt-3" id="accordionMenuOptions">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingMenuOptions">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMenuOptions" aria-expanded="false">
                <i data-feather="list" class="me-2"></i> Opciones de Menú
            </button>
        </h2>
        <div id="collapseMenuOptions" class="accordion-collapse collapse" aria-labelledby="headingMenuOptions">
            <div class="accordion-body">
                <p class="text-muted mb-4">Gestione las opciones de cada menú.</p>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm" id="btn-add-option">
                            <i data-feather="plus" class="me-1"></i> Nueva Opción
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <select class="form-select form-select-sm" id="filter-menu-options" style="max-width: 300px;">
                                <option value="">Todos los menús</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Menú</th>
                                <th>Sub Menú</th>
                                <th>Orden</th>
                                <th>Acción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-options">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acordeón: Sub Menús Nivel 3 -->
<div class="accordion accordion-margin mt-3" id="accordionSubMenusN3">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingSubMenusN3">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubMenusN3" aria-expanded="false">
                <i data-feather="layers" class="me-2"></i> Sub Menús Nivel 3
            </button>
        </h2>
        <div id="collapseSubMenusN3" class="accordion-collapse collapse" aria-labelledby="headingSubMenusN3">
            <div class="accordion-body">
                <p class="text-muted mb-4">Gestione las opciones de tercer nivel que pertenecen a los sub menús.</p>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm" id="btn-add-option-n3">
                            <i data-feather="plus" class="me-1"></i> Nuevo Sub Menú N3
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <select class="form-select form-select-sm" id="filter-submenu-n3" style="max-width: 300px;">
                                <option value="">Todos los sub menús</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Pertenece al Sub Menú</th>
                                <th>Sub Menú N3</th>
                                <th>Orden</th>
                                <th>Acción</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-submenu-n3">
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acordeón: Acciones -->
<div class="accordion accordion-margin mt-3" id="accordionMenuActions">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingMenuActions">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMenuActions" aria-expanded="false">
                <i data-feather="zap" class="me-2"></i> Acciones Disponibles
            </button>
        </h2>
        <div id="collapseMenuActions" class="accordion-collapse collapse" aria-labelledby="headingMenuActions">
            <div class="accordion-body">
                <p class="text-muted mb-4">Gestione las acciones que pueden ejecutar las opciones de menú.</p>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm" id="btn-add-action">
                            <i data-feather="plus" class="me-1"></i> Nueva Acción
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <select class="form-select form-select-sm" id="filter-menu-actions" style="max-width: 300px;">
                                <option value="">Todas las categorías</option>
                                <option value="menu_principal">Menú Principal</option>
                                <option value="menu_opcion">Sub Menú</option>
                                <option value="ambos">Ambos</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre de acción</th>
                                <th>Categoría</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-actions">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Acordeón: Estados del Sistema -->
<div class="accordion accordion-margin mt-3" id="accordionStates">
    <div class="accordion-item">
        <h2 class="accordion-header" id="headingStates">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStates" aria-expanded="false">
                <i data-feather="layers" class="me-2"></i> Estados del Sistema
            </button>
        </h2>
        <div id="collapseStates" class="accordion-collapse collapse" aria-labelledby="headingStates">
            <div class="accordion-body">
                <p class="text-muted mb-4">Gestione los estados del sistema del bot.</p>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success btn-sm" id="btn-add-state">
                            <i data-feather="plus" class="me-1"></i> Nuevo Estado
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end">
                            <select class="form-select form-select-sm" id="filter-system-states" style="max-width: 300px;">
                                <option value="">Todos los tipos</option>
                                <option value="menu">Menu</option>
                                <option value="sub_menu_n2">Sub menu N2</option>
                                <option value="sub_menu_n3">Sub menu N3</option>
                                <option value="form">Form</option>
                                <option value="registration">Registration</option>
                                <option value="conversation">Conversation</option>
                                <option value="system">System</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-states">
                            <tr>
                                <td colspan="4" class="text-center">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar Menú -->
<div class="modal fade" id="modal-edit-menu" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-menu-title">Editar Menú</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-menu">
                    <input type="hidden" id="menu_id" name="menu_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="menu_label">Nombre del menú <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="menu_label" name="menu_label" required>
                            <small class="text-muted">Nombre del menú visible para el usuario</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="menu_key">Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="menu_key" name="menu_key" required>
                            <small class="text-muted">Identificador único (sin espacios, usar guiones bajos)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="state_name_col" style="display: none;">
                            <label class="form-label" for="menu_state_name">Nombre del Estado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="menu_state_name" name="menu_state_name">
                            <small class="text-muted">Nombre descriptivo para el estado del sistema</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="menu_state_key">Estado</label>
                            <select class="form-select" id="menu_state_key" name="menu_state_key">
                                <option value="">Sin estado</option>
                            </select>
                            <small class="text-muted">Estado asociado al menú</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="menu_description">Descripción del menú</label>
                        <textarea class="form-control" id="menu_description" name="menu_description" rows="2" placeholder="Descripción opcional del menú"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="menu_icon">Icono</label>
                            <input type="text" class="form-control" id="menu_icon" name="menu_icon" placeholder="🔧">
                            <small class="text-muted">Emoji o icono</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="menu_color">Color</label>
                            <input type="color" class="form-control form-control-color" id="menu_color" name="menu_color" value="#87dde3">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="menu_order">Orden <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="menu_order" name="menu_order" min="0" value="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="menu_action_id">Acción</label>
                            <select class="form-select" id="menu_action_id" name="menu_action_id">
                                <option value="">Sin acción</option>
                            </select>
                            <small class="text-muted">Acción a ejecutar cuando se selecciona este menú</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="menu_allows_registration">Permite registro <span class="text-danger">*</span></label>
                            <select class="form-select" id="menu_allows_registration" name="menu_allows_registration">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="menu_is_active">Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="menu_is_active" name="menu_is_active">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-menu">
                    <i data-feather="save" class="me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar Opción -->
<div class="modal fade" id="modal-edit-option" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-option-title">Editar Opción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-option">
                    <input type="hidden" id="option_id" name="option_id">
                    <input type="hidden" id="option_parent_id" name="option_parent_id">
                    <input type="hidden" id="option_level" name="option_level" value="2">
                    <input type="hidden" id="option_path" name="option_path">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_menu_id">Pertenece al menú <span class="text-danger">*</span></label>
                            <select class="form-select" id="option_menu_id" name="option_menu_id" required>
                                <option value="">Seleccione un menú</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_text">Nombre del sub menú <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="option_text" name="option_text" required>
                            <small class="text-muted">Nombre de la opción visible para el usuario</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_key">Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="option_key" name="option_key" required>
                            <small class="text-muted">Identificador único (sin espacios, usar guiones bajos)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="option_state_name_col" style="display: none;">
                            <label class="form-label" for="option_state_name">Nombre del Estado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="option_state_name" name="option_state_name">
                            <small class="text-muted">Nombre descriptivo para el estado del sistema</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_state_key">Estado</label>
                            <select class="form-select" id="option_state_key" name="option_state_key">
                                <option value="">Sin estado</option>
                            </select>
                            <small class="text-muted">Estado asociado a esta opción</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="option_description">Descripción / Mensaje del bot</label>
                        <textarea class="form-control" id="option_description" name="option_description" rows="3" placeholder="Puedes usar este campo como mensaje que el bot enviará al usuario cuando seleccione esta opción"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_icon">Icono</label>
                            <input type="text" class="form-control" id="option_icon" name="option_icon" placeholder="💻">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_order">Orden <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="option_order" name="option_order" min="0" value="1">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="option_action_id">Acción</label>
                            <select class="form-select" id="option_action_id" name="option_action_id">
                                <option value="">Sin acción</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="option_is_active">Estado</label>
                        <select class="form-select" id="option_is_active" name="option_is_active">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-option">
                    <i data-feather="save" class="me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar Sub Menú N3 -->
<div class="modal fade" id="modal-edit-option-n3" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-option-n3-title">Editar Sub Menú N3</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-option-n3">
                    <input type="hidden" id="option_n3_id" name="option_n3_id">
                    <input type="hidden" id="option_n3_menu_id" name="option_n3_menu_id">
                    <input type="hidden" id="option_n3_level" name="option_n3_level" value="3">
                    <input type="hidden" id="option_n3_path" name="option_n3_path">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_n3_parent_id">Pertenece al Sub Menú <span class="text-danger">*</span></label>
                            <select class="form-select" id="option_n3_parent_id" name="option_n3_parent_id" required>
                                <option value="">Seleccione un sub menú</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_n3_text">Nombre del sub menú N3 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="option_n3_text" name="option_n3_text" required>
                            <small class="text-muted">Nombre de la opción visible para el usuario</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_n3_key">Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="option_n3_key" name="option_n3_key" required>
                            <small class="text-muted">Identificador único (sin espacios, usar guiones bajos)</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3" id="option_n3_state_name_col" style="display: none;">
                            <label class="form-label" for="option_n3_state_name">Nombre del Estado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="option_n3_state_name" name="option_n3_state_name">
                            <small class="text-muted">Nombre descriptivo para el estado del sistema</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_n3_state_key">Estado</label>
                            <select class="form-select" id="option_n3_state_key" name="option_n3_state_key">
                                <option value="">Sin estado</option>
                            </select>
                            <small class="text-muted">Estado asociado a esta opción</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="option_n3_description">Descripción / Mensaje del bot</label>
                        <textarea class="form-control" id="option_n3_description" name="option_n3_description" rows="3" placeholder="Puedes usar este campo como mensaje que el bot enviará al usuario cuando seleccione esta opción"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_n3_icon">Icono</label>
                            <input type="text" class="form-control" id="option_n3_icon" name="option_n3_icon" placeholder="💻">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="option_n3_order">Orden <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="option_n3_order" name="option_n3_order" min="0" value="1">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="option_n3_action_id">Acción</label>
                            <select class="form-select" id="option_n3_action_id" name="option_n3_action_id">
                                <option value="">Sin acción</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="option_n3_is_active">Estado</label>
                        <select class="form-select" id="option_n3_is_active" name="option_n3_is_active">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-option-n3">
                    <i data-feather="save" class="me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar Acción -->
<div class="modal fade" id="modal-edit-action" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-action-title">Editar Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-action">
                    <input type="hidden" id="action_id" name="action_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="action_key">Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="action_key" name="action_key" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="action_title">Nombre de acción <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="action_title" name="action_title" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="action_description">Descripción</label>
                        <textarea class="form-control" id="action_description" name="action_description" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="action_type">Tipo</label>
                            <select class="form-select" id="action_type" name="action_type">
                                <option value="service_method">service_method</option>
                                <option value="custom_query">custom_query</option>
                                <option value="redirect">redirect</option>
                                <option value="form">form</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="action_category">Categoría</label>
                            <select class="form-select" id="action_category" name="action_category">
                                <option value="menu_principal">Menú Principal</option>
                                <option value="menu_opcion">Sub Menú</option>
                                <option value="ambos">Ambos</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="action_configuration">Configuración (JSON)</label>
                        <textarea class="form-control font-monospace" id="action_configuration" name="action_configuration" rows="6" placeholder='{"method": "getFaqListMessage", "params": [], "service": "FaqService"}'></textarea>
                        <small class="text-muted">Configuración en formato JSON</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="action_requires_auth">Requiere autenticación</label>
                            <select class="form-select" id="action_requires_auth" name="action_requires_auth">
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="action_is_active">Estado</label>
                            <select class="form-select" id="action_is_active" name="action_is_active">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-action">
                    <i data-feather="save" class="me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Editar Estado -->
<div class="modal fade" id="modal-edit-state" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-state-title">Editar Estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-edit-state">
                    <input type="hidden" id="state_id" name="state_id">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="state_key">Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="state_key" name="state_key" required>
                            <small class="text-muted">Identificador único del estado</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="state_name">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="state_name" name="state_name" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="state_description">Descripción</label>
                        <textarea class="form-control" id="state_description" name="state_description" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="state_type">Tipo <span class="text-danger">*</span></label>
                        <select class="form-select" id="state_type" name="state_type" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="menu">Menu</option>
                            <option value="sub_menu_n2">Sub menu N2</option>
                            <option value="sub_menu_n3">Sub menu N3</option>
                            <option value="form">Form</option>
                            <option value="registration">Registration</option>
                            <option value="conversation">Conversation</option>
                            <option value="system">System</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="state_is_active">Estado</label>
                            <select class="form-select" id="state_is_active" name="state_is_active">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-state">
                    <i data-feather="save" class="me-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>
