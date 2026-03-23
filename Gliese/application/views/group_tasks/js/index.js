$(document).ready(function () {
    let table;
    let isEditing = false;

    // Inicializar
    init();

    function init() {
        loadStats();
        loadGroups();
        initDataTable();
        bindEvents();
    }

    // Cargar estadísticas
    function loadStats() {
        $.ajax({
            url: `${BASE_URL}Group_Tasks/get_stats`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'OK') {
                    $('#stat-total').text(response.data.total_tasks || 0);
                    $('#stat-pending').text(response.data.pending_tasks || 0);
                    $('#stat-in-progress').text(response.data.in_progress_tasks || 0);
                    $('#stat-completed').text(response.data.completed_tasks || 0);
                }
            },
            error: function () {
                console.error('Error al cargar estadísticas');
            },
        });
    }

    // Cargar grupos para el selector
    function loadGroups() {
        $.ajax({
            url: `${BASE_URL}Group_Tasks/get_groups`,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'OK' && response.data) {
                    let options = '<option value="">Seleccione un grupo...</option>';
                    response.data.forEach(function (group) {
                        options += `<option value="${group.id}">${group.group_name} (${group.group_code})</option>`;
                    });
                    $('#task-group').html(options);
                }
            },
            error: function () {
                console.error('Error al cargar grupos');
            },
        });
    }

    // Inicializar DataTable
    function initDataTable() {
        table = $('#table-tasks').DataTable({
            ajax: {
                url: `${BASE_URL}Group_Tasks/get_all_tasks`,
                dataSrc: 'data',
            },
            columns: [
                { data: 'id' },
                {
                    data: null,
                    render: function (data) {
                        return `<strong>${data.group_name}</strong><br><small class="text-muted">${data.group_code}</small>`;
                    },
                },
                { data: 'task_title' },
                {
                    data: 'task_description',
                    render: function (data) {
                        if (!data) return '<span class="text-muted">Sin descripción</span>';
                        return data.length > 50 ? data.substring(0, 50) + '...' : data;
                    },
                },
                {
                    data: 'due_date',
                    render: function (data) {
                        if (!data) return '<span class="text-muted">Sin fecha</span>';
                        return moment(data).format('DD/MM/YYYY');
                    },
                },
                {
                    data: 'status',
                    render: function (data) {
                        const badges = {
                            pendiente: '<span class="badge badge-warning">Pendiente</span>',
                            en_progreso: '<span class="badge badge-info">En Progreso</span>',
                            completada: '<span class="badge badge-success">Completada</span>',
                        };
                        return badges[data] || data;
                    },
                },
                {
                    data: 'priority',
                    render: function (data) {
                        const badges = {
                            baja: '<span class="badge badge-secondary">Baja</span>',
                            media: '<span class="badge badge-primary">Media</span>',
                            alta: '<span class="badge badge-danger">Alta</span>',
                        };
                        return badges[data] || data;
                    },
                },
                {
                    data: 'created_by_name',
                    render: function (data) {
                        return data || '<span class="text-muted">N/A</span>';
                    },
                },
                {
                    data: null,
                    orderable: false,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-icon btn-warning" onclick="editTask(${data.id})" title="Editar">
                                <i class="ft-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-danger" onclick="deleteTask(${data.id})" title="Eliminar">
                                <i class="ft-trash-2"></i>
                            </button>
                        `;
                    },
                },
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            },
            order: [[0, 'desc']],
            responsive: true,
        });
    }

    // Eventos
    function bindEvents() {
        // Botón nueva tarea
        $('#btn-new-task').on('click', function () {
            isEditing = false;
            $('#modal-task-title').text('Nueva Tarea');
            $('#form-task')[0].reset();
            $('#task-id').val('');
            $('#modal-task').modal('show');
        });

        // Guardar tarea
        $('#btn-save-task').on('click', function () {
            if (validateForm()) {
                saveTask();
            }
        });
    }

    // Validar formulario
    function validateForm() {
        const group = $('#task-group').val();
        const title = $('#task-title').val().trim();

        if (!group) {
            toastr.warning('Seleccione un grupo');
            return false;
        }

        if (!title) {
            toastr.warning('Ingrese el título de la tarea');
            return false;
        }

        return true;
    }

    // Guardar tarea
    function saveTask() {
        const formData = {
            group_id: $('#task-group').val(),
            task_title: $('#task-title').val().trim(),
            task_description: $('#task-description').val().trim() || null,
            due_date: $('#task-due-date').val() || null,
            status: $('#task-status').val(),
            priority: $('#task-priority').val(),
        };

        let url, method;

        if (isEditing) {
            formData.id = $('#task-id').val();
            url = `${BASE_URL}Group_Tasks/update_task`;
            method = 'PUT';
        } else {
            url = `${BASE_URL}Group_Tasks/create_task`;
            method = 'POST';
        }

        $.ajax({
            url: url,
            type: method,
            data: JSON.stringify(formData),
            contentType: 'application/json',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'OK') {
                    toastr.success(response.msg);
                    $('#modal-task').modal('hide');
                    table.ajax.reload();
                    loadStats();
                } else {
                    toastr.error(response.msg || 'Error al guardar la tarea');
                }
            },
            error: function () {
                toastr.error('Error al comunicarse con el servidor');
            },
        });
    }

    // Editar tarea (función global)
    window.editTask = function (id) {
        isEditing = true;
        $('#modal-task-title').text('Editar Tarea');

        // Obtener datos de la fila
        const rowData = table.rows().data().toArray();
        const task = rowData.find((t) => t.id == id);

        if (task) {
            $('#task-id').val(task.id);
            $('#task-group').val(task.group_id);
            $('#task-title').val(task.task_title);
            $('#task-description').val(task.task_description || '');
            $('#task-due-date').val(task.due_date || '');
            $('#task-status').val(task.status);
            $('#task-priority').val(task.priority);

            $('#modal-task').modal('show');
        }
    };

    // Eliminar tarea (función global)
    window.deleteTask = function (id) {
        Swal.fire({
            title: '¿Está seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `${BASE_URL}Group_Tasks/delete_task`,
                    type: 'DELETE',
                    data: JSON.stringify({ id: id }),
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'OK') {
                            toastr.success(response.msg);
                            table.ajax.reload();
                            loadStats();
                        } else {
                            toastr.error(response.msg || 'Error al eliminar la tarea');
                        }
                    },
                    error: function () {
                        toastr.error('Error al comunicarse con el servidor');
                    },
                });
            }
        });
    };
});
