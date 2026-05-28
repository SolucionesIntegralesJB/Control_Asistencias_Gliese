<?php
// --
class C_Employees extends Controller
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
        $this->functions->check_permissions($this->segment->get('modules'), 'Employees');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Employees')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    // --
    public function get_employees()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_GET);
            }
            // --
            $obj = $this->load_model('Employees');
            // --
            $response = $obj->get_employees();
            // --
            switch ($response['status']) {
                    // --
                case 'OK':
                    // --
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Listado de registros encontrados.',
                        'data' => $response['result']
                    );
                    // --
                    break;

                case 'ERROR':
                    // --
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron registros en el sistema.',
                        'data' => array(),
                    );
                    // --
                    break;

                case 'EXCEPTION':
                    // --
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['result']->getMessage(),
                        'data' => array()
                    );
                    // --
                    break;
            }
        } else {
            // --
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }

        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function get_employee_by_id()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_GET);
            }
            // --
            if (!empty($input['id_employees'])) {
                // --
                $obj = $this->load_model('Employees');
                // --
                $bind = array(
                    'id_employees' => intval($input['id_employees'])
                );
                // --
                $response = $obj->get_employee_by_id($bind);
                // --
                switch ($response['status']) {
                        // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Listado de registros encontrados.',
                            'data' => $response['result']
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontraron registros en el sistema.',
                            'data' => array(),
                        );
                        // --
                        break;

                    case 'EXCEPTION':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
                        // --
                        break;
                }
            } else {
                // --
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron los campos necesarios, verificar.',
                    'data' => array()
                );
            }
        } else {
            // --
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }

        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // -- 
    public function create_employees()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            // --
            if (
                !empty($input['document_number']) &&
                !empty($input['document_type']) &&
                !empty($input['name']) &&
                !empty($input['phone'])
            ) {
                // Validar contraseña si se proporciona
                $password = null;
                if (!empty($input['password'])) {
                    if (strlen($input['password']) < 6) {
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'La contraseña debe tener al menos 6 caracteres.',
                            'data' => array()
                        );
                        header('Content-Type: application/json');
                        echo json_encode($json);
                        return;
                    }
                    if ($input['password'] !== $input['password_confirm']) {
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'Las contraseñas no coinciden.',
                            'data' => array()
                        );
                        header('Content-Type: application/json');
                        echo json_encode($json);
                        return;
                    }
                    $password = password_hash($input['password'], PASSWORD_DEFAULT);
                }

                // --
                $document_type = $this->functions->clean_string($input['document_type']);
                $document_number = $this->functions->clean_string($input['document_number']);
                $name = strtoupper($this->functions->clean_string($input['name']));
                $address = $this->functions->clean_string($input['address']);
                $reference = $this->functions->clean_string($input['reference']);
                $phone = $this->functions->clean_string($input['phone']);
                $email = $this->functions->clean_string($input['email']);
                $work_area = !empty($input['work_area']) ? $this->functions->clean_string($input['work_area']) : null;
                $position = !empty($input['position']) ? $this->functions->clean_string($input['position']) : null;
                $salary = !empty($input['salary']) ? floatval($input['salary']) : 0;
                $role_person_id = !empty($input['role_person_id']) ? intval($input['role_person_id']) : 1;
                // --
                $bind = array(
                    'document_type' => $document_type,
                    'document_number' => $document_number,
                    'name' => $name,
                    'address' => $address,
                    'reference' => $reference,
                    'phone' => $phone,
                    'email' => $email,
                    'work_area' => $work_area,
                    'position' => $position,
                    'salary' => $salary,
                    'role_person_id' => $role_person_id,
                    'password' => $password
                );

                // --
                $obj = $this->load_model('Employees');
                $response = $obj->create_employees($bind);
                // --
                switch ($response['status']) {
                        // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Registro almacenado en el sistema con éxito.',
                            // 'msg' => ,
                            'data' => array()
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible guardar el registro ingresado, verificar.',
                            'data' => array(),
                        );
                        // --
                        break;

                    case 'EXCEPTION':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
                        // --
                        break;
                }
            } else {
                // --
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron los campos necesarios, verificar.',
                    'data' => array()
                );
            }
        } else {
            // --
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }

        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }


    // --
    public function update_employees()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            // --
            if (
                !empty($input['id_employees']) &&
                !empty($input['document_number']) &&
                !empty($input['document_type']) &&
                !empty($input['name']) &&
                !empty($input['phone'])
            ) {
                // Validar contraseña si se proporciona
                $password = null;
                if (!empty($input['password'])) {
                    if (strlen($input['password']) < 6) {
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'La contraseña debe tener al menos 6 caracteres.',
                            'data' => array()
                        );
                        header('Content-Type: application/json');
                        echo json_encode($json);
                        return;
                    }
                    if ($input['password'] !== $input['password_confirm']) {
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'Las contraseñas no coinciden.',
                            'data' => array()
                        );
                        header('Content-Type: application/json');
                        echo json_encode($json);
                        return;
                    }
                    $password = password_hash($input['password'], PASSWORD_DEFAULT);
                }

                // --
                $id_employees = $this->functions->clean_string($input['id_employees']);
                $document_type = $this->functions->clean_string($input['document_type']);
                $document_number = $this->functions->clean_string($input['document_number']);
                $name = strtoupper($this->functions->clean_string($input['name']));
                $address = $this->functions->clean_string($input['address']);
                $reference = $this->functions->clean_string($input['reference']);
                $phone = $this->functions->clean_string($input['phone']);
                $email = $this->functions->clean_string($input['email']);
                $work_area = !empty($input['work_area']) ? $this->functions->clean_string($input['work_area']) : null;
                $position = !empty($input['position']) ? $this->functions->clean_string($input['position']) : null;
                $salary = !empty($input['salary']) ? floatval($input['salary']) : 0;
                $role_person_id = !empty($input['role_person_id']) ? intval($input['role_person_id']) : 1;
                // --
                $bind = array(
                    'id_employees' => $id_employees,
                    'document_type' => $document_type,
                    'document_number' => $document_number,
                    'name' => $name,
                    'address' => $address,
                    'reference' => $reference,
                    'phone' => $phone,
                    'email' => $email,
                    'work_area' => $work_area,
                    'position' => $position,
                    'salary' => $salary,
                    'role_person_id' => $role_person_id,
                    'password' => $password
                );
                // --
                $obj = $this->load_model('Employees');
                $response = $obj->update_employees($bind);
                // --
                switch ($response['status']) {
                        // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Registro actualizado en el sistema con éxito.',
                            'data' => array()
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible guardar el registro ingresado, verificar.',
                            'data' => array(),
                        );
                        // --
                        break;

                    case 'EXCEPTION':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
                        // --
                        break;
                }
            } else {
                // --
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron los campos necesarios, verificar.',
                    'data' => array()
                );
            }
        } else {
            // --
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }


        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function delete_employees()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            // --
            if (!empty($input['id_employees'])) {
                // --
                $id_employees = $this->functions->clean_string($input['id_employees']);
                // --
                $bind = array(
                    'id_employees' => $id_employees
                );
                // --
                $obj = $this->load_model('Employees');
                $response = $obj->delete_employees($bind);
                // --
                switch ($response['status']) {
                        // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Registro desactivado del sistema con éxito.',
                            'data' => array()
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible desactivar el registro, verificar.',
                            'data' => array(),
                        );
                        // --
                        break;

                    case 'EXCEPTION':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
                        // --
                        break;
                }
            } else {
                // --
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron los campos necesarios, verificar.',
                    'data' => array()
                );
            }
        } else {
            // --
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }

        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function get_business_name_cli()
    {
        // --
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_GET);
            }
            // --
            $obj = $this->load_model('Employees');
            // --
            $response = $obj->get_business_name_cli();
            // --
            switch ($response['status']) {
                    // --
                case 'OK':
                    // --
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Listado de registros encontrados.',
                        'data' => $response['result']
                    );
                    // --
                    break;

                case 'ERROR':
                    // --
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron registros en el sistema.',
                        'data' => array(),
                    );
                    // --
                    break;

                case 'EXCEPTION':
                    // --
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['result']->getMessage(),
                        'data' => array()
                    );
                    // --
                    break;
            }
        } else {
            // --
            $json = array(
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }

        // --
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    //--
    public function get_company_data()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $input = filter_input_array(INPUT_GET);
            // --
            if (!empty($input['nroDoc'])) {
                // --
                $nroDoc = $this->functions->clean_string($input['nroDoc']);
                $obj = $this->load_model('Company');
                $response = $obj->get_config();
                $text = $response['result']['token'];
                // Configuración de la API
                $token = $text;
                // Determinar la URL de la API según la longitud del RUC
                if (strlen($nroDoc) == 8) {
                    $url = 'https://api.apis.net.pe/v2/reniec/dni?numero=' . $nroDoc;
                } elseif (strlen($nroDoc) == 11) {
                    $url = 'https://api.apis.net.pe/v2/sunat/ruc?numero=' . $nroDoc;
                } else {
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => ' No se enviaron los campos necesarios, verificar.',
                        'data' => array()
                    );
                    header('Content-Type: application/json');
                    echo json_encode($json);
                    return;
                }
                // Iniciar llamada a API
                $curl = curl_init();
                // Configurar opciones de cURL
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Referer: http://apis.net.pe/api-ruc',
                        'Authorization: Bearer ' . $token
                    ),
                ));
                // Ejecutar la llamada a la API
                $response = curl_exec($curl);
                // Cerrar la conexión cURL
                curl_close($curl);
                // Decodificar la respuesta JSON
                $empresa = json_decode($response);
                // Verificar si se obtuvo una respuesta válida
                if ($empresa) {
                    if (isset($empresa->message)) {
                        // Verificar el tipo de error y establecer el mensaje correspondiente
                        if ($empresa->message === 'ruc no valido' || $empresa->message === 'dni no valido' || $empresa->message === 'not found') {
                            $json = array(
                                'status' => 'ERROR',
                                'type' => 'error',
                                'msg' => 'No se encontraron datos para el número de documento ingresado.',
                                'data' => array()
                            );
                        } else {
                            $json = array(
                                'status' => 'ERROR',
                                'type' => 'error',
                                'msg' => 'Error desconocido.',
                                'data' => array()
                            );
                        }
                    } else {
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Datos obtenidos con éxito.',
                            'data' => $empresa
                        );
                    }
                } else {
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => 'No se pudo obtener una respuesta de la API.',
                        'data' => array()
                    );
                }
                // Enviar la respuesta JSON
                echo json_encode($json);
            }
        }
    }
}
