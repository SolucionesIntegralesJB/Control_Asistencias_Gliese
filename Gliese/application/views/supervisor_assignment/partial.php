<!-- Contenido parcial de Asignación de Supervisores para tab -->
<div class="row mb-3">
    <div class="col-12">
        <button type="button" class="btn btn-primary btn-new-assignment">
            <i data-feather="user-plus" class="me-50"></i>
            Asignar Supervisor a Grupo
        </button>
    </div>
</div>

<!-- Tabla de asignaciones -->
<div class="row mt-2">
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-hover" id="assignments-table">
                <thead>
                    <tr>
                        <th>Supervisor</th>
                        <th>Email</th>
                        <th>Grupo</th>
                        <th class="text-center">Practicantes</th>
                        <th>Fecha Asignación</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="assignments-tbody">
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                            <span class="ms-2">Cargando asignaciones...</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Nota: el JS de asignación de supervisores se carga dinámicamente
     desde P_Group mediante $.getScript para evitar cargas duplicadas. -->
