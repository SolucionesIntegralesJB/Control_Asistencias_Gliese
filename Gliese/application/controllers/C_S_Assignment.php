<?php
// --
class C_S_Assignment extends Controller
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
        $this->functions->check_permissions($this->segment->get('modules'), 'S_Assignment');
        // --
        $this->view->set_js('index');
        $this->view->set_menu([
            'modules' => $this->segment->get('modules'),
            'view' => 'S_Assignment',
        ]);
        $this->view->set_view('index');
    }

    // -- Obtiene los estudiantes aceptados que aún no tienen credenciales asignadas
    public function get_students_accepted()
    {
        // Valida la sesión del usuario
        // Obtiene los estudiantes aceptados que aún no tienen credenciales asignadas.
        // Retorna un listado con los datos de los estudiantes.
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $obj = $this->load_model('S_Assignment');
            $response = $obj->get_students_accepted();
            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Estudiantes aceptados sin credenciales.',
                        'data' => $response['result'],
                    ];
                    break;
                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No hay estudiantes para asignar.',
                    ];
                    break;
                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['result']->getMessage(),
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
    public function get_groups()
    {
        // Obtiene todos los grupos de estudiantes activos.
        // Retorna un listado con los datos de los grupos.
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $obj = $this->load_model('S_Assignment');
            $response = $obj->get_groups_active();
            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'data' => $response['result'],
                    ];
                    break;
                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No hay grupos disponibles.',
                    ];
                    break;
                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['result']->getMessage(),
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

    // -- Obtiene los roles activos
    public function get_roles()
    {
        // Obtiene los roles activos de tipo estudiante o supervisor.
        // Retorna un listado con los datos de los roles.
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $obj = $this->load_model('S_Assignment');
            $response = $obj->get_roles_active();
            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'data' => $response['result'],
                    ];
                    break;
                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No hay roles disponibles.',
                    ];
                    break;
                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['result']->getMessage(),
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
    public function assign_student()
    {
        // Asigna un estudiante a un grupo, crea su usuario y actualiza la información correspondiente.
        // Retorna el resultado de la operación.
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (
                isset($input['id_student']) &&
                isset($input['id_group']) &&
                isset($input['id_role'])
            ) {
                $obj = $this->load_model('S_Assignment');
                $response = $obj->assign_student($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Estudiante asignado correctamente.',
                            'data' => $response['result'],
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al asignar el estudiante.',
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Parámetros requeridos: id_student, id_group, id_role.',
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
    public function get_credentials_template()
    {
        // Obtiene la plantilla de correo para credenciales.
        // Retorna el asunto y el contenido de la plantilla de tipo "credenciales".
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $obj = $this->load_model('S_Assignment');
            $response = $obj->get_credentials_template();
            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'data' => $response['result'],
                    ];
                    break;
                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontró la plantilla.',
                    ];
                    break;
                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['result']->getMessage(),
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

    // -- Actualiza la plantilla de correo para credenciales
    public function update_credentials_template()
    {
        // Actualiza la plantilla de correo para credenciales.
        // Recibe los nuevos valores de asunto y contenido para la plantilla de tipo "credenciales".
        // Retorna el estado de la operación.
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (isset($input['subject']) && isset($input['content'])) {
                $obj = $this->load_model('S_Assignment');
                $response = $obj->update_credentials_template($input);

                switch ($response['status']) {
                    case 'OK':
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Plantilla actualizada correctamente.',
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => isset($response['msg'])
                                ? $response['msg']
                                : 'Error al actualizar la plantilla.',
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                        ];
                        break;
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Parámetros requeridos: subject, content.',
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
