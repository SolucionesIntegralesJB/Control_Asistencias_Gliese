<?php
// --
class C_Carrier extends Controller
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
        $this->functions->check_permissions($this->segment->get('modules'), 'Carrier');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Carrier')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    // --
    public function get_carrier()
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
            $obj = $this->load_model('Carrier');
            // --
            $response = $obj->get_carrier();
            // --
            switch ($response['status']) {
                    // --
                case 'OK':
                    // --
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => $this->messages->message['list'],
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
    public function get_carrier_by_id()
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
            if (!empty($input['id_carrier'])) {
                // --
                $obj = $this->load_model('Carrier');
                // --
                $bind = array(
                    'id_carrier' => intval($input['id_carrier'])
                );
                // --
                $response = $obj->get_carrier_by_id($bind);
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
    public function create_carrier()
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
                !empty($input['document_type']) &&
                !empty($input['name']) &&
                !empty($input['document_number']) &&
                !empty($input['address']) &&
                !empty($input['phone']) &&
                !empty($input['manager']) &&
                !empty($input['email']) &&
                !empty($input['brand']) &&
                !empty($input['plate']) &&
                !empty($input['drivers_license']) &&
                !empty($input['description_document_type'])
            ) {
                // --
                $document_type = $this->functions->clean_string($input['document_type']);
                $name = $this->functions->clean_string(strtoupper(ucfirst($input['name'])));
                $document_number = $this->functions->clean_string($input['document_number']);
                $description_document_type = $this->functions->clean_string($input['description_document_type']);
                $address = $this->functions->clean_string($input['address']);
                $phone = $this->functions->clean_string($input['phone']);
                $manager = $this->functions->clean_string($input['manager']);
                $email = $this->functions->clean_string($input['email']);
                $brand = $this->functions->clean_string($input['brand']);
                $plate = $this->functions->clean_string($input['plate']);
                $drivers_license = $this->functions->clean_string($input['drivers_license']);
                // --
                $is_verified = $this->functions->verified_document_type($description_document_type, $document_number); // -- verified document type
                // --
                if ($is_verified) {
                    $bind = array(
                        'id_document_type' => $document_type,
                        'name' => $name,
                        'document_number' => $document_number,
                        'address' => $address,
                        'phone' => $phone,
                        'manager' => $manager,
                        'email' => $email,
                        'brand' => $brand,
                        'plate' => $plate,
                        'drivers_license' => $drivers_license
                    );

                    // --
                    $obj = $this->load_model('Carrier');
                    $response = $obj->create_carrier($bind);
                    // --
                    switch ($response['status']) {
                        case 'OK':
                            $json = array(
                                'status' => 'OK',
                                'type' => 'success',
                                'msg' => 'Registro almacenado en el sistema con éxito.',
                                'data' => array()
                            );
                            break;

                        case 'ERROR':
                            $json = array(
                                'status' => 'ERROR',
                                'type' => 'warning',
                                'msg' => 'No fue posible guardar el registro ingresado, verificar.',
                                'data' => array(),
                            );
                            break;

                        case 'EXCEPTION':
                            $json = array(
                                'status' => 'ERROR',
                                'type' => 'error',
                                'msg' => $response['result']->getMessage(),
                                'data' => array()
                            );
                            break;
                    }
                } else {
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se enviaron los campos necesarios, verificar.',
                        'data' => array()
                    );
                }
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron los campos necesarios, verificar.',
                    'data' => array()
                );
            }
        } else {
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
    public function update_carrier()
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
                !empty($input['id_carrier']) &&
                !empty($input['document_type']) &&
                !empty($input['name']) &&
                !empty($input['document_number']) &&
                !empty($input['address']) &&
                !empty($input['phone']) &&
                !empty($input['manager']) &&
                !empty($input['email']) &&
                !empty($input['brand']) && /* New */
                !empty($input['plate']) && /* New */
                !empty($input['drivers_license']) && /* New */
                !empty($input['description_document_type'])
            ) {
                // --
                $id_carrier = $this->functions->clean_string($input['id_carrier']);
                $document_type = $this->functions->clean_string($input['document_type']);
                $name = $this->functions->clean_string(strtoupper(ucfirst($input['name'])));
                $document_number = $this->functions->clean_string($input['document_number']);
                $description_document_type = $this->functions->clean_string($input['description_document_type']);
                $address = $this->functions->clean_string($input['address']);
                $phone = $this->functions->clean_string($input['phone']);
                $manager = $this->functions->clean_string($input['manager']);
                $email = $this->functions->clean_string($input['email']);
                $brand = $this->functions->clean_string($input['brand']);
                $plate = $this->functions->clean_string($input['plate']);
                $drivers_license = $this->functions->clean_string($input['drivers_license']);
                // --
                $is_verified = $this->functions->verified_document_type($description_document_type, $document_number); // -- verified document type
                // --
                if ($is_verified) {
                    // -- 
                    $bind = array(
                        'id_carrier' => $id_carrier,
                        'id_document_type' => $document_type,
                        'name' => $name,
                        'document_number' => $document_number,
                        'address' => $address,
                        'phone' => $phone,
                        'manager' => $manager,
                        'email' => $email,
                        'brand' => $brand,
                        'plate' => $plate,
                        'drivers_license' => $drivers_license

                    );
                    // --
                    $obj = $this->load_model('Carrier');
                    $response = $obj->update_carrier($bind);
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
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'Número de documento invalido, verificar.',
                    );
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
    public function delete_carrier()
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
            if (!empty($input['id_carrier'])) {
                // --
                $id_carrier = $this->functions->clean_string($input['id_carrier']);
                // --
                $bind = array(
                    'id_carrier' => $id_carrier
                );
                // --
                $obj = $this->load_model('Carrier');
                $response = $obj->delete_carrier($bind);
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
                            'msg' => 'No fue posible descativar el registro, verificar.',
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

    //--
    public function get_business_name_carrier()
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
            $obj = $this->load_model('Carrier');
            // --
            $response = $obj->get_business_name();
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
                // Configuración de la API
                $token = 'apis-token-10307.jNJ6K5RZsRvE9MKBg9ZvfHFmEg7v8nLZ';
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
