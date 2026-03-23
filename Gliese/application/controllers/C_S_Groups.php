<?php
// --
class C_S_Groups extends Controller
{
    // --
    public function __construct()
    {
        parent::__construct();
        // --- PUENTE DE SESIÓN SOLO PARA S_Groups ---
        if (!isset($_SESSION['id_user'])) {
            $segment_data = $this->segment->get('data');
            if (is_array($segment_data) && isset($segment_data['id_user'])) {
                $_SESSION['id_user'] = $segment_data['id_user'];
            }
        }
    }

    // --
    public function index()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'S_Groups');
        // --
        $this->view->set_js('index');
        $this->view->set_menu(['modules' => $this->segment->get('modules'), 'view' => 'S_Groups']);
        $this->view->set_view('index');
    }

    // --
    public function supervisors()
    {
        // Valida la sesión del usuario y los permisos para la gestión de supervisores.
        // Carga la vista correspondiente para la gestión de supervisores.
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'S_Groups');
        // --
        // Reuse the existing groups index view for supervisors management to avoid
        // creating a separate view file. Pass the title via set_data and render
        // the standard `s_groups/index` view.
        $this->view->set_data(['title' => 'Gestión de Supervisores']);
        $this->view->set_menu(['modules' => $this->segment->get('modules'), 'view' => 'S_Groups']);
        $this->view->set_js('index');
        $this->view->set_view('index');
    }

    public function get_groups()
    {
        // Obtiene todos los grupos de practicantes activos.
        // Valida la sesión del usuario y retorna un listado de grupos en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj = $this->load_model('S_Groups');
            // --
            $response = $obj->get_all_groups();
            // --
            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Grupos obtenidos correctamente.',
                        'data' => isset($response['data']) ? $response['data'] : [],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No hay grupos registrados.',
                        'data' => [],
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => isset($response['msg']) ? $response['msg'] : 'Error desconocido',
                        'data' => [],
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function create_group()
    {
        // Crea un nuevo grupo de practicantes.
        // Valida la sesión del usuario y procesa la solicitud POST con los datos del grupo.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (
                isset($input['group_code']) &&
                isset($input['group_name']) &&
                isset($input['max_capacity'])
            ) {
                $obj = $this->load_model('S_Groups');
                $response = $obj->create_group($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Grupo creado correctamente.',
                            'data' => $response['group_id'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al crear el grupo.',
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Parámetros requeridos: group_code, group_name, max_capacity.',
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function edit_group()
    {
        // Actualiza un grupo de practicantes existente.
        // Valida la sesión del usuario y procesa la solicitud PUT con los datos del grupo.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (
                isset($input['id']) &&
                isset($input['group_code']) &&
                isset($input['group_name']) &&
                isset($input['max_capacity'])
            ) {
                $obj = $this->load_model('S_Groups');
                $response = $obj->edit_group($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Grupo actualizado correctamente.',
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al actualizar el grupo.',
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Parámetros requeridos: id, group_code, group_name, max_capacity.',
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function delete_group()
    {
        // Elimina un grupo de practicantes.
        // Valida la sesión del usuario y procesa la solicitud DELETE con el ID del grupo.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'DELETE') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['id'])) {
                $obj = $this->load_model('S_Groups');
                $response = $obj->delete_group($input['id']);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Grupo eliminado correctamente.',
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al eliminar el grupo.',
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Parámetro requerido: id.',
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
    // --
    public function get_group_members()
    {
        // Obtiene los miembros de un grupo de practicantes.
        // Valida la sesión del usuario y procesa la solicitud GET con el ID del grupo.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;

            if ($group_id) {
                $obj = $this->load_model('S_Groups');
                $response = $obj->get_group_members($group_id);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Miembros obtenidos correctamente.',
                            'data' => $response['result'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No hay miembros en este grupo.',
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Parámetro requerido: group_id.',
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function get_supervisors()
    {
        // Obtiene la lista de supervisores disponibles.
        // Valida la sesión del usuario y procesa la solicitud GET.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            $obj = $this->load_model('S_Groups');
            // If caller requests supervisors for practicantes only, use user_roles
            if (isset($_GET['for']) && $_GET['for'] === 'practicantes') {
                $response = $obj->get_practitioner_supervisors();
            } else {
                $response = $obj->get_supervisors();
            }

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Supervisores obtenidos correctamente.',
                        'data' => isset($response['data']) ? $response['data'] : [],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No hay supervisores disponibles.',
                        'data' => [],
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => isset($response['msg']) ? $response['msg'] : 'Error desconocido',
                        'data' => [],
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
    // --
    public function get_all_supervisors()
    {
        // Obtiene todos los supervisores registrados.
        // Valida la sesión del usuario y procesa la solicitud GET.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            $obj = $this->load_model('S_Groups');
            $response = $obj->get_supervisors();

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Supervisores obtenidos correctamente.',
                        'data' => $response['data'],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No hay supervisores registrados.',
                        'data' => [],
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg'],
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // Obtener grupos que supervisa un usuario (AJAX)
    public function get_supervisions_by_user()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
            if (!$user_id) {
                $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Parámetro requerido: user_id', 'data' => []];
            } else {
                $obj = $this->load_model('S_Groups');
                $response = $obj->get_supervisions_by_user($user_id);
                switch ($response['status']) {
                    case 'OK':
                        $json = ['status' => 'OK', 'type' => 'success', 'msg' => 'Supervisiones obtenidas', 'data' => $response['data']];
                        break;
                    case 'ERROR':
                        $json = ['status' => 'ERROR', 'type' => 'warning', 'msg' => 'No hay supervisiones', 'data' => []];
                        break;
                    case 'EXCEPTION':
                    default:
                        $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => $response['msg'], 'data' => []];
                        break;
                }
            }
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido', 'data' => []];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function manage_group_supervisor()
    {
        // Gestiona la asignación o eliminación de un supervisor en un grupo.
        // Valida la sesión del usuario y procesa la solicitud POST para agregar o remover un supervisor.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['user_id']) || !isset($input['action'])) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Datos incompletos.',
                ];
            } else {
                $obj = $this->load_model('S_Groups');

                if ($input['action'] === 'add') {
                    $response = $obj->add_supervisor(
                        $input['user_id'],
                        $input['group_id'],
                        $input['supervisor_type'],
                    );
                } elseif ($input['action'] === 'remove') {
                    $response = $obj->remove_supervisor($input['group_id'], $input['user_id']);
                } else {
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => 'Acción no válida.',
                    ];
                    header('Content-Type: application/json');
                    echo json_encode($json);
                    return;
                }

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => $response['msg'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg'],
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                        ];
                        break;
                }
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function remove_group_member()
    {
        // Elimina un practicante de un grupo.
        // Valida la sesión del usuario y procesa la solicitud POST con el ID del practicante.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['student_id'])) {
                $obj = $this->load_model('S_Groups');
                $response = $obj->remove_group_member($input['student_id']);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => $response['msg'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg'],
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Parámetro requerido: student_id.',
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function reassign_group_member()
    {
        // Reasigna un practicante a un nuevo grupo.
        // Valida la sesión del usuario y procesa la solicitud POST con el ID del practicante y el nuevo grupo.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['student_id']) && isset($input['new_group_id'])) {
                $obj = $this->load_model('S_Groups');
                $response = $obj->reassign_group_member(
                    $input['student_id'],
                    $input['new_group_id'],
                );

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => $response['msg'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => $response['msg'],
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Parámetros requeridos: student_id, new_group_id.',
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function get_available_groups()
    {
        // Obtiene los grupos de practicantes con espacios disponibles.
        // Valida la sesión del usuario y procesa la solicitud GET, excluyendo un grupo específico si se indica.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            $exclude_group_id = isset($_GET['exclude_group_id']) ? $_GET['exclude_group_id'] : null;

            $obj = $this->load_model('S_Groups');
            $response = $obj->get_available_groups($exclude_group_id);

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Grupos disponibles obtenidos correctamente.',
                        'data' => $response['data'],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => isset($response['msg'])
                            ? $response['msg']
                            : 'No hay grupos disponibles.',
                        'data' => [],
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg'],
                        'data' => [],
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function save_attendance()
    {
        // Registra o actualiza la asistencia de un practicante.
        // Valida la sesión del usuario y procesa la solicitud POST con los datos de asistencia.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);

            $obj = $this->load_model('S_Groups');
            $response = $obj->save_attendance($data);

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => $response['msg'],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => $response['msg'],
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg'],
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function get_attendance_history()
    {
        // Obtiene el historial de asistencias de un grupo de practicantes.
        // Valida la sesión del usuario y procesa la solicitud GET con el ID del grupo.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;

            if (!$group_id) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'ID de grupo requerido.',
                ];
            } else {
                $obj = $this->load_model('S_Groups');
                $response = $obj->get_group_attendance_history($group_id);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'msg' => 'Historial de asistencias obtenido correctamente.',
                            'data' => $response['data'],
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => 'Error al obtener historial: ' . $response['msg'],
                        ];
                        break;
                    default:
                        // Asegurarnos de devolver siempre un JSON válido incluso si el modelo
                        // retornó un estado inesperado (por ejemplo 'ERROR'). Si no hacemos
                        // esto, $json puede quedar indefinido y PHP imprimirá un warning
                        // en HTML (rompiendo la respuesta JSON en el cliente).
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error desconocido al obtener historial.',
                        ];
                        break;
                }
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function mark_attendance()
    {
        // Registra la asistencia de múltiples practicantes para una fecha específica en un grupo.
        // Valida la sesión del usuario y procesa la solicitud POST con los datos de asistencia.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (
                !isset($input['group_id']) ||
                !isset($input['date']) ||
                !isset($input['attendances'])
            ) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Datos incompletos.',
                ];
            } else {
                $obj = $this->load_model('S_Groups');
                $response = $obj->mark_attendance(
                    $input['group_id'],
                    $input['date'],
                    $input['attendances'],
                );

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => $response['msg'],
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => 'Error al guardar asistencia: ' . $response['msg'],
                        ];
                        break;
                }
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function get_student_attendance_history()
    {
        // Obtiene el historial de asistencia de un practicante específico.
        // Valida la sesión del usuario y procesa la solicitud POST con el ID del practicante y una fecha opcional.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : null;
            $date = isset($_POST['date']) ? $_POST['date'] : null;

            if (!$student_id) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'ID de practicante requerido.',
                ];
            } else {
                $obj = $this->load_model('S_Groups');
                $response = $obj->get_student_attendance_history($student_id, $date);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'msg' => 'Historial de asistencia obtenido correctamente.',
                            'data' => $response['data'],
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => 'Error al obtener historial: ' . $response['msg'],
                        ];
                        break;

                    default:
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => 'Error desconocido al obtener historial.',
                        ];
                        break;
                }
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // -- TEMPORAL: Verificar tabla historial
    public function debug_attendance_history()
    {
        // Obtiene los últimos 10 registros del historial de asistencia para depuración.
        // Valida la sesión del usuario y muestra la respuesta en formato de texto plano.
        $this->functions->validate_session($this->segment->get('isActive'));

        $obj = $this->load_model('S_Groups');
        $response = $obj->debug_check_attendance_history();

        echo '<pre>';
        print_r($response);
        echo '</pre>';
    }

    public function debug_supervisors()
    {
        // Obtiene información de depuración sobre supervisores.
        // Valida la sesión del usuario y muestra la respuesta en formato de texto plano con un encabezado.
        $this->functions->validate_session($this->segment->get('isActive'));

        $obj = $this->load_model('S_Groups');
        $response = $obj->debug_supervisors();

        echo '<h2>DEBUG: Supervisores</h2>';
        echo '<pre>';
        print_r($response);
        echo '</pre>';
    }

    public function test_supervisors()
    {
        // Prueba la función get_supervisors directamente.
        // Valida la sesión del usuario y muestra la respuesta en formato de texto plano junto con el JSON que se enviaría.
        $this->functions->validate_session($this->segment->get('isActive'));

        $obj = $this->load_model('S_Groups');
        $response = $obj->get_supervisors();

        echo '<h2>TEST: get_supervisors() Response</h2>';
        echo '<pre>';
        print_r($response);
        echo '</pre>';

        echo '<h3>JSON que se enviaría:</h3>';
        echo '<pre>';
        $json = [
            'status' => 'OK',
            'type' => 'success',
            'msg' => 'Supervisores obtenidos correctamente.',
            'data' => isset($response['data']) ? $response['data'] : [],
        ];
        print_r($json);
        echo '</pre>';
    }
    // --
    public function get_available_student_users()
    {
        // Obtiene los usuarios con rol PRACTICANTE o SUPERVISOR disponibles para asignar a un grupo.
        // Valida la sesión del usuario y procesa la solicitud GET, excluyendo usuarios de un grupo específico si se indica.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            $group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;

            $obj = $this->load_model('S_Groups');
            $response = $obj->get_available_student_users($group_id);

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Usuarios disponibles obtenidos correctamente.',
                        'data' => $response['data'],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg'],
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg'],
                    ];
                    break;
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function add_user_to_group()
    {
        // Agrega un usuario a un grupo de practicantes.
        // Valida la sesión del usuario y procesa la solicitud POST con el ID del usuario y del grupo.
        // Retorna el resultado de la operación en formato JSON.
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['user_id']) && isset($input['group_id'])) {
                $obj = $this->load_model('S_Groups');
                $response = $obj->add_user_to_group($input['user_id'], $input['group_id']);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => $response['msg'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Faltan datos requeridos.',
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // Actualiza configuración de Meet y horarios de un grupo
    public function update_group_meeting_config()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['id'])) {
                // Agregar el ID del usuario actual que está modificando
                $input['student_id'] = $_SESSION['id_user'] ?? null;
                $input['modified_by'] = $_SESSION['id_user'] ?? null;

                $obj = $this->load_model('S_Groups');
                $response = $obj->update_meeting_config($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => $response['msg'] ?? 'Configuración de reuniones actualizada correctamente.',
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'] ?? 'Error al actualizar la configuración.',
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['msg'],
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'ID de grupo requerido.',
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
}
