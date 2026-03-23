<?php
// --
class C_Group_Tasks extends Controller
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function index()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Group_Tasks');
        // --
        $this->view->set_js('index');
        $this->view->set_menu([
            'modules' => $this->segment->get('modules'),
            'view' => 'Group_Tasks',
        ]);
        $this->view->set_view('index');
    }

    // -- Obtener todas las tareas
    public function get_all_tasks()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj = $this->load_model('Group_Tasks');
            $response = $obj->get_all_tasks();

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Tareas obtenidas correctamente.',
                        'data' => $response['data'],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No hay tareas registradas.',
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

    // -- Obtener tareas de un grupo
    public function get_tasks_by_group()
    {
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
                $obj = $this->load_model('Group_Tasks');
                $response = $obj->get_tasks_by_group($group_id);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Tareas obtenidas correctamente.',
                            'data' => $response['data'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No hay tareas para este grupo.',
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

    // -- Obtener tareas del estudiante (su grupo)
    public function get_my_tasks()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $student_id = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;

            if (!$student_id) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Usuario no identificado.',
                ];
            } else {
                $obj = $this->load_model('Group_Tasks');
                $response = $obj->get_tasks_by_student($student_id);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Tareas obtenidas correctamente.',
                            'data' => $response['data'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'No hay tareas asignadas.',
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

    // -- Obtener grupos activos
    public function get_groups()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj = $this->load_model('Group_Tasks');
            $response = $obj->get_active_groups();

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'data' => $response['data'],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No hay grupos disponibles.',
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

    // -- Crear tarea
    public function create_task()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            // Solo task_title es requerido, group_id es opcional
            if (isset($input['task_title'])) {
                // Agregar el id del usuario que está creando la tarea
                $sessionData = $this->segment->get('data');
                $input['created_by'] = isset($sessionData['id_user']) ? $sessionData['id_user'] : null;

                $obj = $this->load_model('Group_Tasks');
                $response = $obj->create_task($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Tarea creada correctamente.',
                            'data' => $response['task_id'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al crear la tarea.',
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
                    'msg' => 'Parámetro requerido: task_title.',
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

    // -- Actualizar tarea
    public function update_task()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);

            // Solo id y task_title son requeridos, group_id es opcional
            if (isset($input['id']) && isset($input['task_title'])) {
                $obj = $this->load_model('Group_Tasks');
                $response = $obj->update_task($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Tarea actualizada correctamente.',
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al actualizar la tarea.',
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
                    'msg' => 'Parámetros requeridos: id, task_title.',
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

    // -- Actualizar estado de tarea
    public function update_status()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'PUT') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['id']) && isset($input['status'])) {
                $obj = $this->load_model('Group_Tasks');
                $response = $obj->update_task_status($input['id'], $input['status']);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Estado actualizado correctamente.',
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al actualizar el estado.',
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
                    'msg' => 'Parámetros requeridos: id, status.',
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

    // -- Eliminar tarea
    public function delete_task()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'DELETE') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['id'])) {
                $obj = $this->load_model('Group_Tasks');
                $response = $obj->delete_task($input['id']);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Tarea eliminada correctamente.',
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al eliminar la tarea.',
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

    // -- Obtener estadísticas
    public function get_stats()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj = $this->load_model('Group_Tasks');
            $response = $obj->get_tasks_stats();

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'data' => $response['data'],
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se pudieron obtener las estadísticas.',
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

    /**
     * Iniciar tareas pendientes cuando se abre una jornada laboral.
     * Este endpoint debe ser llamado cuando el practicante inicia su sesión de trabajo.
     * Cambia todas las tareas "pendiente" del grupo del usuario a "en_progreso".
     *
     * Método: POST
     * Parámetros opcionales: user_id (si no se provee, usa el usuario de la sesión)
     */
    public function start_work_session_tasks()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            // Obtener user_id del input o de la sesión
            $input = json_decode(file_get_contents('php://input'), true);
            $user_id = isset($input['user_id']) ? $input['user_id'] : null;

            if (!$user_id) {
                $sessionData = $this->segment->get('data');
                $user_id = isset($sessionData['id_user']) ? $sessionData['id_user'] : null;
            }

            if (!$user_id) {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Usuario no identificado.',
                ];
            } else {
                $obj = $this->load_model('Group_Tasks');
                $response = $obj->start_pending_tasks_for_user($user_id);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Tareas iniciadas correctamente.',
                            'data' => [
                                'tasks_updated' => $response['tasks_updated'],
                                'total_in_progress' => $response['total_in_progress']
                            ],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'No se pudieron iniciar las tareas.',
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
}
?>
