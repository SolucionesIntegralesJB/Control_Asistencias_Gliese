<?php
// --
class C_Clients extends Controller
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
        $this->functions->check_permissions($this->segment->get('modules'), 'Clients');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Clients')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    // --
    public function get_clients()
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
            $obj = $this->load_model('Clients');
            // --
            $response = $obj->get_clients();
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
    public function get_client_by_id()
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
            if (!empty($input['id_clients'])) {
                // --
                $obj = $this->load_model('Clients');
                // --
                $bind = array(
                    'id_clients' => intval($input['id_clients'])
                );
                // --
                $response = $obj->get_client_by_id($bind);
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
    public function create_clients()
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
                // --
                $document_type = $this->functions->clean_string($input['document_type']);
                $document_number = $this->functions->clean_string($input['document_number']);
                $name = strtoupper($this->functions->clean_string($input['name']));
                $address = $this->functions->clean_string($input['address']);
                $reference = $this->functions->clean_string($input['reference']);
                $phone = $this->functions->clean_string($input['phone']);
                $email = $this->functions->clean_string($input['email']);
                // --
                $bind = array(
                    'document_type' => $document_type,
                    'document_number' => $document_number,
                    'name' => $name,
                    'address' => $address,
                    'reference' => $reference,
                    'phone' => $phone,
                    'email' => $email
                );

                // --
                $obj = $this->load_model('Clients');
                $response = $obj->create_clients($bind);
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
    public function update_clients()
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
                !empty($input['id_clients']) &&
                !empty($input['document_number']) &&
                !empty($input['document_type']) &&
                !empty($input['name']) &&
                !empty($input['phone'])
            ) {
                // --
                $id_clients = $this->functions->clean_string($input['id_clients']);
                $document_type = $this->functions->clean_string($input['document_type']);
                $document_number = $this->functions->clean_string($input['document_number']);
                $name = strtoupper($this->functions->clean_string($input['name']));
                $address = $this->functions->clean_string($input['address']);
                $reference = $this->functions->clean_string($input['reference']);
                $phone = $this->functions->clean_string($input['phone']);
                $email = $this->functions->clean_string($input['email']);
                // --
                $bind = array(
                    'id_clients' => $id_clients,
                    'document_type' => $document_type,
                    'document_number' => $document_number,
                    'name' => $name,
                    'address' => $address,
                    'reference' => $reference,
                    'phone' => $phone,
                    'email' => $email
                );
                // --
                $obj = $this->load_model('Clients');
                $response = $obj->update_clients($bind);
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
    public function delete_clients()
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
            if (!empty($input['id_clients'])) {
                // --
                $id_clients = $this->functions->clean_string($input['id_clients']);
                // --
                $bind = array(
                    'id_clients' => $id_clients
                );
                // --
                $obj = $this->load_model('Clients');
                $response = $obj->delete_clients($bind);
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
            $obj = $this->load_model('Clients');
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
