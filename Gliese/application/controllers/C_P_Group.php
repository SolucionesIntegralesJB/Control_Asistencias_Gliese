<?php
/**
 * Controlador de Grupos (P_Group)
 * CRUD de grupos: crear, listar, editar, eliminar
 */
class C_P_Group extends Controller {

    public function __construct() {
        parent::__construct();
    }

    // Carga la vista principal
    public function index() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'P_Group');
        $this->view->set_js('index');
        $this->view->set_js('schedule_config'); // JavaScript para configuración de horarios
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'P_Group'));
        $this->view->set_view('index');
    }

    // GET: Lista grupos según el rol del usuario
    public function get_group() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj = $this->load_model('P_Group');
            
            // Obtener rol e id del usuario de la sesión
            $id_user = $this->segment->get('id_user');
            $role = $this->segment->get('role'); // Asumiendo que tienes el rol en sesión
            
            // Obtener grupos según el rol
            $response = $obj->get_groups_by_role($id_user, $role);

            switch ($response['status']) {
                case 'OK':
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Registros obtenidos correctamente.',
                        'data' => isset($response['result']) ? $response['result'] : array()
                    );
                    break;
                case 'ERROR':
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => $response['msg'] ?? 'No se encontraron registros.',
                        'data' => array()
                    );
                    break;
                case 'EXCEPTION':
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg'] ?? $response['result']->getMessage(),
                        'data' => array()
                    );
                    break;
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // GET: Obtiene detalle de un grupo por ID
    public function get_group_detail() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $input = filter_input_array(INPUT_GET);
            if (isset($input['id'])) {
                $obj = $this->load_model('P_Group');
                $response = $obj->get_group_by_id($input['id']);

                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'data' => $response['result']
                        );
                        break;
                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg'] ?? 'Registro no encontrado.'
                        );
                        break;
                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'] ?? $response['result']->getMessage()
                        );
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'ID no proporcionado.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // POST: Crea un nuevo grupo
    public function crear_group() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $input = filter_input_array(INPUT_POST);
            
            if (isset($input['nombre']) && !empty(trim($input['nombre']))) {
                $obj = $this->load_model('P_Group');
                $response = $obj->crear_group($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Grupo creado correctamente.',
                            'data' => $response['result']
                        );
                        break;
                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg'] ?? 'Error al crear el grupo.'
                        );
                        break;
                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'] ?? $response['result']->getMessage()
                        );
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'El nombre es requerido.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // PUT: Actualiza un grupo existente
    public function editar_group() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['id']) && !empty($input['id'])) {
                $obj = $this->load_model('P_Group');
                $response = $obj->editar_group($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Grupo actualizado correctamente.'
                        );
                        break;
                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg'] ?? 'Error al actualizar el grupo.'
                        );
                        break;
                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'] ?? $response['result']->getMessage()
                        );
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'ID no proporcionado.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // DELETE: Elimina un grupo
    public function eliminar_group() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'DELETE') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['id']) && !empty($input['id'])) {
                $obj = $this->load_model('P_Group');
                $response = $obj->eliminar_group($input['id']);

                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Grupo eliminado correctamente.'
                        );
                        break;
                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg'] ?? 'Error al eliminar el grupo.'
                        );
                        break;
                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'] ?? $response['result']->getMessage()
                        );
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'ID no proporcionado.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // ==================== GESTIÓN DE ENLACES MEET ====================

    // GET: Obtener todos los enlaces Meet
    public function get_meet_links() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj = $this->load_model('P_Group');
            $response = $obj->get_all_meet_links();

            switch ($response['status']) {
                case 'OK':
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'data' => $response['result']
                    );
                    break;
                case 'ERROR':
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => $response['msg'],
                        'data' => array()
                    );
                    break;
                case 'EXCEPTION':
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg'],
                        'data' => array()
                    );
                    break;
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // POST: Crear enlace Meet
    public function create_meet_link() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['name']) && isset($input['meet_url'])) {
                $obj = $this->load_model('P_Group');
                $response = $obj->create_meet_link($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Enlace Meet creado correctamente.',
                            'data' => $response['result']
                        );
                        break;
                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg']
                        );
                        break;
                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg']
                        );
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Nombre y URL son requeridos.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // PUT: Asignar enlace Meet a grupos
    public function assign_meet_link() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['meet_link_id']) && isset($input['group_ids'])) {
                $id_user = $this->segment->get('id_user');
                $obj = $this->load_model('P_Group');
                $response = $obj->assign_meet_link_to_groups($input['meet_link_id'], $input['group_ids'], $id_user);

                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Enlace asignado a ' . count($input['group_ids']) . ' grupo(s).'
                        );
                        break;
                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg']
                        );
                        break;
                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg']
                        );
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Datos incompletos.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // DELETE: Eliminar un enlace Meet
    public function delete_meet_link() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'DELETE') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['id']) && !empty($input['id'])) {
                $obj = $this->load_model('P_Group');
                $response = $obj->delete_meet_link($input['id']);

                switch ($response['status']) {
                    case 'OK':
                        $json = array('status' => 'OK', 'type' => 'success', 'msg' => $response['msg']);
                        break;
                    case 'ERROR':
                        $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => $response['msg']);
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['msg']);
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'ID no proporcionado.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // GET: Obtener horarios de practicantes
    public function get_student_schedules() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj = $this->load_model('P_Group');
            $response = $obj->get_all_student_schedules();

            switch ($response['status']) {
                case 'OK':
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'data' => $response['result']
                    );
                    break;
                case 'ERROR':
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => $response['msg'],
                        'data' => array()
                    );
                    break;
                case 'EXCEPTION':
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg'],
                        'data' => array()
                    );
                    break;
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Carga la vista parcial de configuración de horarios
     */
    public function load_schedule_config_partial()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        // Cargar la vista parcial directamente
        $partial_path = __DIR__ . '/../views/p_group/partials/schedule_config/config_form.php';

        if (file_exists($partial_path)) {
            include $partial_path;
        } else {
            echo '<div class="alert alert-danger">
                    <i data-feather="alert-circle"></i>
                    <strong>Error:</strong> No se encontró el archivo de configuración de horarios.
                    <br><small>Ruta buscada: ' . htmlspecialchars($partial_path) . '</small>
                  </div>';
        }
    }

    /**
     * Descarga la plantilla CSV para horarios del grupo
     */
    public function download_schedule_template()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        // Nombre del archivo
        $filename = 'Plantilla_Horario_Grupo.csv';

        // Headers para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Abrir output stream
        $output = fopen('php://output', 'w');

        // BOM para UTF-8 (para que Excel lo abra correctamente con tildes)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabeceras del CSV
        fputcsv($output, ['TIPO', 'DÍA', 'HORA_INICIO', 'HORA_FIN', 'DESCRIPCIÓN'], ';');

        // Ejemplos pre-llenados
        fputcsv($output, ['trabajo', 'lunes', '08:00', '11:00', 'Trabajo en oficina'], ';');
        fputcsv($output, ['estudio', 'lunes', '14:00', '18:00', 'Clases'], ';');
        fputcsv($output, ['reunion', 'lunes', '18:00', '19:00', 'Reunión semanal'], ';');
        fputcsv($output, ['trabajo', 'martes', '08:00', '11:00', 'Trabajo en oficina'], ';');
        fputcsv($output, ['', '', '', '', 'Agregar más filas según sea necesario...'], ';');

        fclose($output);
        exit;
    }

    /**
     * Procesa y guarda el archivo CSV de horarios subido
     */
    public function upload_schedule_csv()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            try {
                // Validar que se subió un archivo
                if (!isset($_FILES['schedule_file']) || $_FILES['schedule_file']['error'] !== UPLOAD_ERR_OK) {
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se subió ningún archivo o hubo un error en la subida.'
                    ];
                } else {
                    $file = $_FILES['schedule_file'];
                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                    // Validar extensión (solo CSV por ahora)
                    if (!in_array($extension, ['csv'])) {
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'Por favor, convierta su archivo Excel a formato CSV antes de subirlo. En Excel: Archivo → Guardar como → CSV (delimitado por comas).'
                        ];
                    } else {
                        // Obtener ID del grupo desde POST
                        $group_id = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
                        $user_id = $this->segment->get('id_user');

                        if ($group_id <= 0) {
                            throw new Exception('ID de grupo no válido');
                        }

                        // Obtener configuraciones del sistema
                        $obj = $this->load_model('P_Group');
                        $pdo = $obj->get_pdo();

                        // Cargar configuraciones de validación
                        $sql_configs = 'SELECT config_key, config_value FROM schedule_config WHERE config_key IN (
                            "min_work_days_per_week",
                            "max_work_days_per_week",
                            "min_work_hours_per_day",
                            "max_work_hours_per_day",
                            "required_meetings_per_week",
                            "max_meetings_per_week",
                            "min_meeting_duration_hours",
                            "max_meeting_duration_hours"
                        )';
                        $stmt_configs = $pdo->prepare($sql_configs);
                        $stmt_configs->execute();
                        $config_rows = $stmt_configs->fetchAll(PDO::FETCH_ASSOC);

                        // Mapear configuraciones
                        $config = [];
                        foreach ($config_rows as $row) {
                            $config[$row['config_key']] = floatval($row['config_value']);
                        }

                        // Leer el archivo CSV
                        $schedules = [];
                        $handle = fopen($file['tmp_name'], 'r');

                        if ($handle) {
                            // Saltar la primera línea (cabeceras)
                            $first_line = true;
                            $line_number = 1;

                            while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {
                                $line_number++;

                                if ($first_line) {
                                    $first_line = false;
                                    continue;
                                }

                                // Validar que tenga al menos 4 columnas y no esté vacía
                                if (count($data) >= 4 && !empty(trim($data[0])) && !empty(trim($data[1]))) {
                                    $activity_type = strtolower(trim($data[0]));
                                    $day = trim($data[1]);
                                    $start = trim($data[2]);
                                    $end = trim($data[3]);
                                    $description = isset($data[4]) ? trim($data[4]) : trim($data[0]);

                                    // Calcular duración en horas
                                    $start_time = strtotime($start);
                                    $end_time = strtotime($end);

                                    if ($start_time === false || $end_time === false) {
                                        throw new Exception("Línea $line_number: Formato de hora inválido ($start - $end)");
                                    }

                                    $duration_hours = ($end_time - $start_time) / 3600;

                                    if ($duration_hours <= 0) {
                                        throw new Exception("Línea $line_number: La hora de fin debe ser mayor a la hora de inicio");
                                    }

                                    // VALIDAR DURACIÓN DE REUNIONES
                                    if ($activity_type === 'reunion') {
                                        $min_duration = $config['min_meeting_duration_hours'] ?? 1;
                                        $max_duration = $config['max_meeting_duration_hours'] ?? 3;

                                        if ($duration_hours < $min_duration) {
                                            throw new Exception("Línea $line_number: La reunión de '$description' dura $duration_hours hrs, pero el mínimo permitido es $min_duration hrs");
                                        }

                                        if ($duration_hours > $max_duration) {
                                            throw new Exception("Línea $line_number: La reunión de '$description' dura $duration_hours hrs, pero el máximo permitido es $max_duration hrs");
                                        }
                                    }

                                    // VALIDAR HORAS DE TRABAJO POR DÍA
                                    if ($activity_type === 'trabajo') {
                                        $min_hours = $config['min_work_hours_per_day'] ?? 4;
                                        $max_hours = $config['max_work_hours_per_day'] ?? 6;

                                        if ($duration_hours < $min_hours) {
                                            throw new Exception("Línea $line_number: El bloque de trabajo en '$day' dura $duration_hours hrs, pero el mínimo es $min_hours hrs");
                                        }

                                        if ($duration_hours > $max_hours) {
                                            throw new Exception("Línea $line_number: El bloque de trabajo en '$day' dura $duration_hours hrs, pero el máximo es $max_hours hrs");
                                        }
                                    }

                                    $schedules[] = [
                                        'activity_type' => $activity_type,
                                        'day_of_week' => $day,
                                        'start_time' => $start,
                                        'end_time' => $end,
                                        'description' => $description,
                                        'duration_hours' => $duration_hours
                                    ];
                                }
                            }

                            fclose($handle);

                            if (count($schedules) === 0) {
                                throw new Exception('El archivo no contiene horarios válidos.');
                            }

                            // VALIDAR NÚMERO DE DÍAS DE TRABAJO POR SEMANA
                            $work_days = [];
                            foreach ($schedules as $s) {
                                if ($s['activity_type'] === 'trabajo') {
                                    $work_days[$s['day_of_week']] = true;
                                }
                            }
                            $total_work_days = count($work_days);

                            $min_days = $config['min_work_days_per_week'] ?? 5;
                            $max_days = $config['max_work_days_per_week'] ?? 5;

                            if ($total_work_days < $min_days) {
                                throw new Exception("Debe trabajar al menos $min_days días por semana (tiene $total_work_days días)");
                            }

                            if ($total_work_days > $max_days) {
                                throw new Exception("No puede trabajar más de $max_days días por semana (tiene $total_work_days días)");
                            }

                            // VALIDAR NÚMERO DE REUNIONES POR SEMANA
                            // Contar días únicos con reuniones (no bloques individuales)
                            $meeting_days = [];
                            foreach ($schedules as $s) {
                                if ($s['activity_type'] === 'reunion') {
                                    $meeting_days[$s['day_of_week']] = true;
                                }
                            }
                            $meeting_count = count($meeting_days);

                            $min_meetings = $config['required_meetings_per_week'] ?? 1;
                            $max_meetings = $config['max_meetings_per_week'] ?? 2;

                            if ($meeting_count < $min_meetings) {
                                throw new Exception("Debe tener al menos $min_meetings día(s) con reunión por semana (tiene $meeting_count)");
                            }

                            if ($meeting_count > $max_meetings) {
                                throw new Exception("No puede tener más de $max_meetings día(s) con reunión por semana (tiene $meeting_count)");
                            }

                            // Eliminar horarios anteriores de este grupo
                            $sql_delete = 'DELETE FROM group_schedules WHERE group_id = :group_id';
                            $obj = $this->load_model('P_Group');
                            $pdo = $obj->get_pdo();
                            $stmt = $pdo->prepare($sql_delete);
                            $stmt->execute(['group_id' => $group_id]);

                            // Insertar nuevos horarios
                            $sql_insert = 'INSERT INTO group_schedules (group_id, activity_type, day_of_week, start_time, end_time, description, created_by, created_at)
                                          VALUES (:group_id, :activity_type, :day_of_week, :start_time, :end_time, :description, :created_by, NOW())';

                            $stmt = $pdo->prepare($sql_insert);

                            foreach ($schedules as $schedule) {
                                $stmt->execute([
                                    'group_id' => $group_id,
                                    'activity_type' => $schedule['activity_type'],
                                    'day_of_week' => $schedule['day_of_week'],
                                    'start_time' => $schedule['start_time'],
                                    'end_time' => $schedule['end_time'],
                                    'description' => $schedule['description'],
                                    'created_by' => $user_id
                                ]);
                            }

                            // Registrar la modificación (subida/actualización de horario)
                            $sql_log = 'INSERT INTO schedule_modification_log (group_id, user_id, action_type, modified_at)
                                        VALUES (:group_id, :user_id, "upload", NOW())';
                            $stmt_log = $pdo->prepare($sql_log);
                            $stmt_log->execute([
                                'group_id' => $group_id,
                                'user_id' => $user_id
                            ]);

                            // Contar modificaciones totales
                            $sql_count = 'SELECT COUNT(*) as modification_count
                                          FROM schedule_modification_log
                                          WHERE group_id = :group_id';
                            $stmt_count = $pdo->prepare($sql_count);
                            $stmt_count->execute(['group_id' => $group_id]);
                            $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
                            $modification_count = $count_result['modification_count'] ?? 0;

                            // Obtener configuración de máximo de modificaciones
                            $sql_config = 'SELECT config_value FROM schedule_config WHERE config_key = "max_schedule_modifications"';
                            $stmt_config = $pdo->prepare($sql_config);
                            $stmt_config->execute();
                            $config_result = $stmt_config->fetch(PDO::FETCH_ASSOC);
                            $max_modifications = intval($config_result['config_value'] ?? 3);

                            $json = [
                                'status' => 'OK',
                                'type' => 'success',
                                'msg' => 'Horario actualizado correctamente. Se cargaron ' . count($schedules) . ' entradas. Modificaciones: ' . $modification_count . '/' . $max_modifications,
                                'modification_count' => $modification_count,
                                'max_modifications' => $max_modifications
                            ];
                        } else {
                            throw new Exception('No se pudo abrir el archivo.');
                        }
                    }
                }
            } catch (Exception $e) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al procesar el archivo: ' . $e->getMessage()
                ];
            }
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.'];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Obtiene el horario guardado de un grupo específico
     */
    public function get_group_schedule()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            try {
                $group_id = isset($_GET['group_id']) ? intval($_GET['group_id']) : 0;

                if ($group_id <= 0) {
                    throw new Exception('ID de grupo inválido.');
                }

                // Obtener horarios del grupo
                $sql = 'SELECT id, activity_type, day_of_week, start_time, end_time, description, created_at
                        FROM group_schedules
                        WHERE group_id = :group_id
                        ORDER BY
                            FIELD(day_of_week, "lunes", "martes", "miercoles", "jueves", "viernes", "sabado", "domingo"),
                            start_time';

                $obj = $this->load_model('P_Group');
                $pdo = $obj->get_pdo();
                $stmt = $pdo->prepare($sql);
                $stmt->execute(['group_id' => $group_id]);
                $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Contar modificaciones realizadas
                $sql_count = 'SELECT COUNT(*) as modification_count
                              FROM schedule_modification_log
                              WHERE group_id = :group_id';
                $stmt_count = $pdo->prepare($sql_count);
                $stmt_count->execute(['group_id' => $group_id]);
                $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
                $modification_count = $count_result['modification_count'] ?? 0;

                // Obtener configuración de máximo de modificaciones
                $sql_config = 'SELECT config_value FROM schedule_config WHERE config_key = "max_schedule_modifications"';
                $stmt_config = $pdo->prepare($sql_config);
                $stmt_config->execute();
                $config_result = $stmt_config->fetch(PDO::FETCH_ASSOC);
                $max_modifications = intval($config_result['config_value'] ?? 3);

                $json = [
                    'status' => 'OK',
                    'data' => $schedules,
                    'modification_count' => $modification_count,
                    'max_modifications' => $max_modifications,
                    'can_delete' => ($modification_count < $max_modifications)
                ];
            } catch (Exception $e) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al obtener horarios: ' . $e->getMessage()
                ];
            }
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.'];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Elimina el horario de un grupo y registra la modificación
     */
    public function delete_group_schedule()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'DELETE' || $request === 'POST') {
            try {
                $raw_input = file_get_contents('php://input');
                $data = json_decode($raw_input, true);

                $group_id = isset($data['group_id']) ? intval($data['group_id']) : 0;
                $user_id = $this->segment->get('userId');

                if ($group_id <= 0) {
                    throw new Exception('ID de grupo inválido.');
                }

                $obj = $this->load_model('P_Group');
                $pdo = $obj->get_pdo();

                // Contar modificaciones actuales
                $sql_count = 'SELECT COUNT(*) as modification_count
                              FROM schedule_modification_log
                              WHERE group_id = :group_id';
                $stmt_count = $pdo->prepare($sql_count);
                $stmt_count->execute(['group_id' => $group_id]);
                $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
                $modification_count = $count_result['modification_count'] ?? 0;

                // Obtener configuración de máximo de modificaciones
                $sql_config = 'SELECT config_value FROM schedule_config WHERE config_key = "max_schedule_modifications"';
                $stmt_config = $pdo->prepare($sql_config);
                $stmt_config->execute();
                $config_result = $stmt_config->fetch(PDO::FETCH_ASSOC);
                $max_modifications = intval($config_result['config_value'] ?? 3);

                // Verificar si ya alcanzó el límite
                if ($modification_count >= $max_modifications) {
                    throw new Exception('Has alcanzado el límite de ' . $max_modifications . ' modificaciones permitidas.');
                }

                // Eliminar horarios del grupo
                $sql_delete = 'DELETE FROM group_schedules WHERE group_id = :group_id';
                $stmt = $pdo->prepare($sql_delete);
                $stmt->execute(['group_id' => $group_id]);

                // Registrar la modificación (eliminación)
                $sql_log = 'INSERT INTO schedule_modification_log (group_id, user_id, action_type, modified_at)
                            VALUES (:group_id, :user_id, "delete", NOW())';
                $stmt_log = $pdo->prepare($sql_log);
                $stmt_log->execute([
                    'group_id' => $group_id,
                    'user_id' => $user_id
                ]);

                $json = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Horario eliminado correctamente. Modificaciones: ' . ($modification_count + 1) . '/' . $max_modifications
                ];
            } catch (Exception $e) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al eliminar horario: ' . $e->getMessage()
                ];
            }
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.'];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
}
