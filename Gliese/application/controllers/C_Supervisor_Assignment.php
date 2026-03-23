<?php
/**
 * ============================================================================
 * CONTROLADOR: Asignación de Supervisores (C_Supervisor_Assignment)
 * ============================================================================
 *
 * PROPÓSITO:
 * Gestiona la asignación de supervisores a grupos de practicantes
 *
 * ENDPOINTS:
 * - index() - Vista principal
 * - get_supervisors() - Lista de supervisores
 * - get_groups() - Lista de grupos
 * - get_assignments() - Lista de asignaciones
 * - assign() - Asignar supervisor a grupo
 * - unassign() - Desasignar supervisor de grupo
 *
 * AUTOR: Sistema Gliese
 * FECHA: 2025-12-07
 * ============================================================================
 */
class C_Supervisor_Assignment extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // Puente de sesión
        if (!isset($_SESSION['id_user'])) {
            $segment_data = $this->segment->get('data');
            if (is_array($segment_data) && isset($segment_data['id_user'])) {
                $_SESSION['id_user'] = $segment_data['id_user'];
            }
        }
    }

    /**
     * Vista principal del módulo
     */
    public function index()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        // Si es una petición AJAX, devolver solo el contenido parcial
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // Cargar solo la vista parcial sin layout
            $partial_path = __DIR__ . '/../views/supervisor_assignment/partial.php';
            if (file_exists($partial_path)) {
                include $partial_path;
            } else {
                echo '<div class="alert alert-danger">Vista parcial no disponible.</div>';
            }
            return;
        }

        // Vista completa (cuando se accede directamente)
        $this->functions->check_permissions($this->segment->get('modules'), 'Supervisor_Assignment');
        $this->view->set_js('index');
        $this->view->set_menu([
            'modules' => $this->segment->get('modules'),
            'view' => 'Supervisor_Assignment'
        ]);
        $this->view->set_view('index');
    }

    /**
     * Obtiene todos los supervisores disponibles
     * GET /Supervisor_Assignment/get_supervisors
     */
    public function get_supervisors()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj = $this->load_model('Supervisor_Assignment');
            $response = $obj->get_all_supervisors();

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Supervisores obtenidos correctamente.',
                        'data' => $response['data']
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => $response['msg'],
                        'data' => []
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg']
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Obtiene todos los grupos disponibles
     * GET /Supervisor_Assignment/get_groups
     */
    public function get_groups()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj = $this->load_model('Supervisor_Assignment');
            $response = $obj->get_all_groups();

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Grupos obtenidos correctamente.',
                        'data' => $response['data']
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => $response['msg'],
                        'data' => []
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg']
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Obtiene todas las asignaciones actuales
     * GET /Supervisor_Assignment/get_assignments
     */
    public function get_assignments()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj = $this->load_model('Supervisor_Assignment');
            $response = $obj->get_all_assignments();

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Asignaciones obtenidas correctamente.',
                        'data' => $response['data']
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => $response['msg'],
                        'data' => []
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg']
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Asigna un supervisor a un grupo
     * POST /Supervisor_Assignment/assign
     * Parámetros: supervisor_id, group_id, notes (opcional)
     */
    public function assign()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            // Obtener datos del POST
            $supervisor_id = filter_input(INPUT_POST, 'supervisor_id', FILTER_VALIDATE_INT);
            $group_id = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);
            $supervisor_type = filter_input(INPUT_POST, 'supervisor_type', FILTER_SANITIZE_SPECIAL_CHARS);

            // Si no se especifica el tipo, usar 'principal' por defecto
            if (!$supervisor_type || !in_array($supervisor_type, ['principal', 'assistant', 'technical'])) {
                $supervisor_type = 'principal';
            }

            // Validar datos obligatorios
            if (!$supervisor_id || !$group_id) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'Debe seleccionar un supervisor y un grupo.'
                ];
            } else {
                // Obtener ID del usuario que hace la asignación
                $segment_data = $this->segment->get('data');
                $assigned_by = isset($segment_data['id_user']) ? $segment_data['id_user'] : null;

                $obj = $this->load_model('Supervisor_Assignment');
                $response = $obj->assign_supervisor($supervisor_id, $group_id, $assigned_by, $supervisor_type);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => $response['msg']
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg']
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg']
                        ];
                        break;
                }
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Desasigna un supervisor de un grupo
     * POST /Supervisor_Assignment/unassign
     * Parámetros: supervisor_id, group_id
     */
    public function unassign()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            // Obtener datos del POST
            $supervisor_id = filter_input(INPUT_POST, 'supervisor_id', FILTER_VALIDATE_INT);
            $group_id = filter_input(INPUT_POST, 'group_id', FILTER_VALIDATE_INT);

            // Validar datos obligatorios
            if (!$supervisor_id || !$group_id) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'Datos incompletos para desasignar.'
                ];
            } else {
                $obj = $this->load_model('Supervisor_Assignment');
                $response = $obj->unassign_supervisor($supervisor_id, $group_id);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => $response['msg']
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg']
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg']
                        ];
                        break;
                }
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    /**
     * Obtiene los grupos de un supervisor específico
     * GET /Supervisor_Assignment/get_supervisor_groups/{supervisor_id}
     */
    public function get_supervisor_groups($supervisor_id = null)
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET' && $supervisor_id) {
            $obj = $this->load_model('Supervisor_Assignment');
            $response = $obj->get_supervisor_groups($supervisor_id);

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Grupos del supervisor obtenidos correctamente.',
                        'data' => $response['data']
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => $response['msg'],
                        'data' => []
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg']
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Solicitud inválida.'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
}
