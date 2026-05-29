    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <!-- Attendance Starts -->
                <section id="attendance">
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
                    <!-- Header title -->
                    <div class="content-header row">
                        <div class="content-header-left col-md-9 col-12 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Lista de <?php echo strtolower($selected_sub_menu); ?></h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="#"><?php echo $selected_menu; ?></a>
                                            </li>
                                            <li class="breadcrumb-item active"><span><?php echo $selected_sub_menu; ?></span>
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Header title-->

                    <!-- Filters -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-1">
                                            <label class="form-label">Empleado</label>
                                            <select id="filter_employee" class="form-select select2" data-placeholder="Todos">
                                                <option value="">Todos</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-1">
                                            <label class="form-label">Fecha Inicio</label>
                                            <input type="date" id="filter_start_date" class="form-control">
                                        </div>
                                        <div class="col-md-2 mb-1">
                                            <label class="form-label">Fecha Fin</label>
                                            <input type="date" id="filter_end_date" class="form-control">
                                        </div>
                                        <div class="col-md-2 mb-1">
                                            <label class="form-label">Estado</label>
                                            <select id="filter_status" class="form-select">
                                                <option value="">Todos</option>
                                                <option value="pending">Pendiente</option>
                                                <option value="in_progress">En Progreso</option>
                                                <option value="completed">Completado</option>
                                                <option value="cancelled">Cancelado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 mb-1">
                                            <label class="form-label">Sede</label>
                                            <select id="filter_campus" class="form-select select2" data-placeholder="Todas">
                                                <option value="">Todas</option>
                                            </select>
                                        </div>
                                        <div class="col-md-1 mb-1 d-flex align-items-end">
                                            <button id="btn_filter" class="btn btn-primary w-100">
                                                <i class="bi bi-filter"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /Filters -->

                    <!-- Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <table class="table" id="datatable-attendance">
                                    <thead>
                                        <tr>
                                            <th>Empleado</th>
                                            <th>Fecha</th>
                                            <th>Rol</th>
                                            <th>Calle</th>
                                            <th>Trabajo Realizado</th>
                                            <th>Entrada</th>
                                            <th>Salida</th>
                                            <th>Horas Regulares</th>
                                            <th>Horas Extra</th>
                                            <th>Estado</th>
                                            <th>Pago Total</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /Table -->

                    <!-- Detail Modal -->
                    <div class="modal fade" id="detail_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content">
                                <div class="modal-header bg-transparent border-0 pb-0">
                                    <div>
                                        <h5 class="modal-title fw-bold">Detalle del Turno</h5>
                                        <p class="text-muted small mb-0" id="detail_employee_name">-</p>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body pt-3">
                                    <!-- Grid Layout -->
                                    <div class="row g-4">
                                        <!-- Left Column: Timeline -->
                                        <div class="col-lg-5">
                                            <!-- General Info Card -->
                                            <div class="card mb-4 border-0 shadow-sm">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-3">
                                                        <i class="bi bi-person-circle me-2 text-primary"></i>Información General
                                                    </h6>
                                                    <div class="row g-3">
                                                        <div class="col-6">
                                                            <label class="text-muted small mb-1">Fecha</label>
                                                            <div class="fw-semibold" id="detail_shift_date">-</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small mb-1">Estado</label>
                                                            <div id="detail_status">-</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small mb-1">Rol</label>
                                                            <div class="fw-semibold" id="detail_job_role">-</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small mb-1">Calle</label>
                                                            <div class="fw-semibold" id="detail_campus">-</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small mb-1">Entrada</label>
                                                            <div class="fw-semibold" id="detail_actual_start">-</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="text-muted small mb-1">Salida</label>
                                                            <div class="fw-semibold" id="detail_actual_end">-</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Work Description Card -->
                                            <div class="card mb-4 border-0 shadow-sm">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-3">
                                                        <i class="bi bi-card-text me-2 text-primary"></i>Trabajo Realizado
                                                    </h6>
                                                    <div class="p-3 bg-light rounded-3">
                                                        <p class="mb-0 text-secondary" id="detail_work_description">-</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Timeline Card -->
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-4">
                                                        <i class="bi bi-clock-history me-2 text-primary"></i>Timeline del Turno
                                                    </h6>
                                                    <div id="timeline_container" class="timeline">
                                                        <!-- Timeline items will be loaded here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Column: Hours & Financial -->
                                        <div class="col-lg-7">
                                            <!-- Hours Summary Card -->
                                            <div class="card mb-4 border-0 shadow-sm">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-4">
                                                        <i class="bi bi-hourglass-split me-2 text-primary"></i>Resumen de Horas
                                                    </h6>
                                                    <div class="row g-4">
                                                        <div class="col-md-4">
                                                            <div class="text-center p-3 bg-light rounded-3">
                                                                <div class="text-muted small mb-1">Horas Regulares</div>
                                                                <div class="h3 mb-0 fw-bold text-primary" id="detail_regular_hours">0.00h</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="text-center p-3 bg-light rounded-3">
                                                                <div class="text-muted small mb-1">Horas Extra</div>
                                                                <div class="h3 mb-0 fw-bold text-warning" id="detail_overtime_hours">0.00h</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="text-center p-3 bg-light rounded-3">
                                                                <div class="text-muted small mb-1">Break Total</div>
                                                                <div class="h3 mb-0 fw-bold text-info" id="detail_break_duration">0 min</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Financial Summary Card -->
                                            <div class="card mb-4 border-0 shadow-sm">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-4">
                                                        <i class="bi bi-cash-coin me-2 text-primary"></i>Resumen Financiero
                                                    </h6>
                                                    <div class="row g-3 mb-4">
                                                        <div class="col-md-6">
                                                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                                                                <div>
                                                                    <div class="text-muted small">Tarifa Regular</div>
                                                                    <div class="fw-semibold" id="detail_hourly_rate">S/ 0.00</div>
                                                                </div>
                                                                <div class="text-end">
                                                                    <div class="text-muted small">Pago Regular</div>
                                                                    <div class="fw-bold text-success" id="detail_regular_payment">S/ 0.00</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3">
                                                                <div>
                                                                    <div class="text-muted small">Tarifa Extra</div>
                                                                    <div class="fw-semibold" id="detail_overtime_rate">S/ 0.00</div>
                                                                </div>
                                                                <div class="text-end">
                                                                    <div class="text-muted small">Pago Extra</div>
                                                                    <div class="fw-bold text-success" id="detail_overtime_payment">S/ 0.00</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="p-4 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <div class="text-muted small mb-1">TOTAL A PAGAR</div>
                                                                <div class="h2 mb-0 fw-bold text-primary" id="detail_total_payment">S/ 0.00</div>
                                                            </div>
                                                            <i class="bi bi-currency-dollar display-4 text-primary opacity-25"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Edit Section Card -->
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-body">
                                                    <h6 class="card-title fw-bold mb-4">
                                                        <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Turno
                                                    </h6>
                                                    <form id="edit_shift_form">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label small text-muted">Entrada</label>
                                                                <input type="time" id="edit_actual_start" name="actual_start" class="form-control">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small text-muted">Salida</label>
                                                                <input type="time" id="edit_actual_end" name="actual_end" class="form-control">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small text-muted">Inicio Break</label>
                                                                <input type="time" id="edit_break_start" name="break_start" class="form-control">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small text-muted">Fin Break</label>
                                                                <input type="time" id="edit_break_end" name="break_end" class="form-control">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small text-muted">Tarifa Regular (S/)</label>
                                                                <input type="number" step="0.01" min="0" id="edit_hourly_rate" name="hourly_rate" class="form-control" placeholder="0.00">
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label small text-muted">Tarifa Extra (S/)</label>
                                                                <input type="number" step="0.01" min="0" id="edit_overtime_rate" name="overtime_rate" class="form-control" placeholder="0.00">
                                                            </div>
                                                            <div class="col-12 mt-2">
                                                                <button type="submit" class="btn btn-primary w-100 py-2">
                                                                    <i class="bi bi-save me-2"></i>Guardar y Recalcular
                                                                </button>
                                                            </div>
                                                            <input type="hidden" id="edit_shift_id" name="id">
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--/ Detail Modal -->

                </section>
                <!-- Attendance ends -->

            </div>
        </div>
    </div>
    <!-- END: Content-->

    <style>
        .timeline {
            position: relative;
            padding: 10px 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 24px;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding-left: 60px;
            margin-bottom: 24px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 16px;
            top: 0;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #7367f0;
            border: 4px solid #fff;
            box-shadow: 0 0 0 3px #7367f0;
            z-index: 1;
        }
        .timeline-item.shift_start::before {
            background: #28c76f;
            box-shadow: 0 0 0 3px #28c76f;
        }
        .timeline-item.break_start::before {
            background: #ff9f43;
            box-shadow: 0 0 0 3px #ff9f43;
        }
        .timeline-item.break_end::before {
            background: #00cfe8;
            box-shadow: 0 0 0 3px #00cfe8;
        }
        .timeline-item.shift_end::before {
            background: #ea5455;
            box-shadow: 0 0 0 3px #ea5455;
        }
        .timeline-item.manual_edit::before {
            background: #7367f0;
            box-shadow: 0 0 0 3px #7367f0;
        }
        .timeline-item.rate_change::before {
            background: #28c76f;
            box-shadow: 0 0 0 3px #28c76f;
        }
        .timeline-content {
            background: #f8f8f8;
            padding: 16px 20px;
            border-radius: 12px;
            border-left: 4px solid #7367f0;
        }
        .timeline-item.shift_start .timeline-content {
            border-left-color: #28c76f;
        }
        .timeline-item.break_start .timeline-content {
            border-left-color: #ff9f43;
        }
        .timeline-item.break_end .timeline-content {
            border-left-color: #00cfe8;
        }
        .timeline-item.shift_end .timeline-content {
            border-left-color: #ea5455;
        }
        .timeline-time {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 4px;
            font-weight: 500;
        }
        .timeline-title {
            font-weight: 700;
            margin-bottom: 4px;
            color: #2c3e50;
        }
        .timeline-description {
            font-size: 0.85rem;
            color: #6c757d;
        }
    </style>
