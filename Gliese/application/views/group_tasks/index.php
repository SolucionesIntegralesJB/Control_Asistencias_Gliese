<div class="content-wrapper">
    <div class="content-header row">
        <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
            <h3 class="content-header-title mb-0 d-inline-block">Gestión de Tareas</h3>
            <div class="row breadcrumbs-top d-inline-block">
                <div class="breadcrumb-wrapper col-12">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>home">Inicio</a></li>
                        <li class="breadcrumb-item active">Tareas de Grupos</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="content-header-right col-md-6 col-12">
            <div class="btn-group float-md-right">
                <button class="btn btn-info" type="button" id="btn-new-task">
                    <i class="ft-plus"></i> Nueva Tarea
                </button>
            </div>
        </div>
    </div>

    <div class="content-body">
        <!-- Estadísticas -->
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="align-self-center">
                                    <i class="ft-list primary font-large-2 float-left"></i>
                                </div>
                                <div class="media-body text-right">
                                    <h3 id="stat-total">0</h3>
                                    <span>Total de Tareas</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="align-self-center">
                                    <i class="ft-clock warning font-large-2 float-left"></i>
                                </div>
                                <div class="media-body text-right">
                                    <h3 id="stat-pending">0</h3>
                                    <span>Pendientes</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="align-self-center">
                                    <i class="ft-activity info font-large-2 float-left"></i>
                                </div>
                                <div class="media-body text-right">
                                    <h3 id="stat-in-progress">0</h3>
                                    <span>En Progreso</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-12">
                <div class="card">
                    <div class="card-content">
                        <div class="card-body">
                            <div class="media d-flex">
                                <div class="align-self-center">
                                    <i class="ft-check-circle success font-large-2 float-left"></i>
                                </div>
                                <div class="media-body text-right">
                                    <h3 id="stat-completed">0</h3>
                                    <span>Completadas</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Tareas -->
        <section id="configuration">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Listado de Tareas</h4>
                            <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                            <div class="heading-elements">
                                <ul class="list-inline mb-0">
                                    <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                    <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                    <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-content collapse show">
                            <div class="card-body card-dashboard">
                                <table class="table table-striped table-bordered zero-configuration" id="table-tasks">
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
        </section>
    </div>
</div>

<!-- Modal para Nueva/Editar Tarea -->
<div class="modal fade" id="modal-task" tabindex="-1" role="dialog" aria-labelledby="modalTaskLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-task-title">Nueva Tarea</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-task">
                    <input type="hidden" id="task-id" name="id">

                    <div class="form-group">
                        <label for="task-group">Grupo <span class="text-danger">*</span></label>
                        <select class="form-control" id="task-group" name="group_id" required>
                            <option value="">Seleccione un grupo...</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="task-title">Título de la Tarea <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="task-title" name="task_title" required maxlength="255">
                    </div>

                    <div class="form-group">
                        <label for="task-description">Descripción</label>
                        <textarea class="form-control" id="task-description" name="task_description" rows="4"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="task-due-date">Fecha Límite</label>
                                <input type="date" class="form-control" id="task-due-date" name="due_date">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="task-status">Estado</label>
                                <select class="form-control" id="task-status" name="status">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="en_progreso">En Progreso</option>
                                    <option value="completada">Completada</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="task-priority">Prioridad</label>
                                <select class="form-control" id="task-priority" name="priority">
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
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-save-task">Guardar Tarea</button>
            </div>
        </div>
    </div>
</div>
