<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <!-- Breadcrumbs -->
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-left mb-0">Estadísticas de Jornadas Laborales</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Practicantes</a></li>
                                <li class="breadcrumb-item active">Estadísticas</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Breadcrumbs -->

        <!-- Content -->
        <div class="content-body">

            <!-- Filtros -->
            <div class="card mb-2">
                <div class="card-body">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" value="<?php echo date('Y-m-d', strtotime('-7 days')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">Fecha Fin</label>
                            <input type="date" class="form-control" id="fecha_fin" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Grupo</label>
                            <select class="form-select" id="id_grupo">
                                <option value="">Todos los grupos</option>
                                <?php if (isset($grupos) && is_array($grupos)): ?>
                                    <?php foreach ($grupos as $grupo): ?>
                                        <option value="<?php echo $grupo['id']; ?>"><?php echo $grupo['name']; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" id="btn-aplicar-filtros">
                                <i data-feather="search" class="me-50"></i>
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas Generales -->
            <div class="row match-height mb-2">
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="fw-bolder mb-0" id="stat-practicantes">0</h2>
                                    <p class="card-text">Total Practicantes</p>
                                </div>
                                <div class="avatar bg-light-primary p-50 m-0">
                                    <div class="avatar-content">
                                        <i data-feather="users" class="font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="fw-bolder mb-0" id="stat-horas-normales">0 hrs</h2>
                                    <p class="card-text">Horas Normales</p>
                                </div>
                                <div class="avatar bg-light-success p-50 m-0">
                                    <div class="avatar-content">
                                        <i data-feather="clock" class="font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="fw-bolder mb-0" id="stat-horas-extras">0 hrs</h2>
                                    <p class="card-text">Horas Extras</p>
                                </div>
                                <div class="avatar bg-light-warning p-50 m-0">
                                    <div class="avatar-content">
                                        <i data-feather="zap" class="font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h2 class="fw-bolder mb-0" id="stat-total-horas">0 hrs</h2>
                                    <p class="card-text">Total Horas</p>
                                </div>
                                <div class="avatar bg-light-info p-50 m-0">
                                    <div class="avatar-content">
                                        <i data-feather="activity" class="font-medium-5"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="activos-tab" data-bs-toggle="tab" href="#activos" role="tab">
                                <i data-feather="activity"></i>
                                <span class="d-none d-sm-inline ms-1">Activos Ahora</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="practicantes-tab" data-bs-toggle="tab" href="#practicantes" role="tab">
                                <i data-feather="users"></i>
                                <span class="d-none d-sm-inline ms-1">Por Practicante</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content">
                        <!-- Tab Activos Ahora -->
                        <div class="tab-pane fade show active" id="activos" role="tabpanel">
                            <div class="p-2">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-success pulse me-2"></span>
                                    <h5 class="mb-0">Practicantes Trabajando Ahora</h5>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Practicante</th>
                                                <th>Grupo</th>
                                                <th>Tipo</th>
                                                <th>Hora Inicio</th>
                                                <th>Tiempo</th>
                                                <th>Tarea</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-activos">
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-3">
                                                    <i data-feather="inbox" class="me-1"></i>
                                                    No hay practicantes trabajando en este momento
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Por Practicante -->
                        <div class="tab-pane fade" id="practicantes" role="tabpanel">
                            <div class="p-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">Resumen por Practicante</h5>
                                    <button type="button" class="btn btn-success btn-sm" id="btn-export-excel">
                                        <i data-feather="download" class="me-50"></i>
                                        <span class="d-none d-sm-inline">Exportar</span>
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Practicante</th>
                                                <th>Grupo</th>
                                                <th>Días</th>
                                                <th>Hrs Normales</th>
                                                <th>Hrs Extras</th>
                                                <th>Total</th>
                                                <th>Evidencias</th>
                                                <th>%</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-practicantes">
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-3">
                                                    Selecciona un rango de fechas y presiona Buscar
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- /Content -->
    </div>
</div>
<!-- END: Content-->

<style>
.pulse {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    animation: pulse-animation 2s infinite;
}

@keyframes pulse-animation {
    0% {
        box-shadow: 0 0 0 0 rgba(40, 199, 111, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(40, 199, 111, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(40, 199, 111, 0);
    }
}
</style>
