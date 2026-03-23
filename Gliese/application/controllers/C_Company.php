<?php
class C_Company extends Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'company');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'company')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    public function get_company()
    {
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
            $obj = $this->load_model('Company');
            $response = $obj->get_company();
            switch ($response['status']) {
                case 'OK':
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Registro encontrado.',
                        'data' => $response['result']
                    );
                    break;
                case 'ERROR':
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontró el registro.',
                        'data' => array()
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
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function update_company()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            // Verificar si se está subiendo un nuevo logo
            if (!empty($input['id_company']) && !empty($_FILES['logo_sitio'])) {
                $routeFile = $_SERVER['SCRIPT_FILENAME'];
                $uploadDir = substr($routeFile, 0, strrpos($routeFile, "../")) . 'public/app-assets/images/logo/';
                $id_company = $this->functions->clean_string($input['id_company']);

                $nameImage = basename($_FILES['logo_sitio']['name']);
                $typeImage = strtolower(pathinfo($nameImage, PATHINFO_EXTENSION));
                if (in_array($typeImage, ['jpeg', 'jpg', 'png'])) {
                    $newName = $id_company . '_' . uniqid() . '.' . $typeImage;
                    $routFinal = $uploadDir . $newName;
                    if (move_uploaded_file($_FILES['logo_sitio']['tmp_name'], $routFinal)) {
                        $input['logo_sitio'] = BASE_URL . 'public/app-assets/images/logo/' . $newName;
                    }
                }
            }

            if (
                !empty($input['id_company']) &&
                !empty($input['razon_social']) &&
                !empty($input['nombre_comercial']) &&
                !empty($input['ruc']) &&
                !empty($input['telefono']) &&
                !empty($input['direccion']) &&
                !empty($input['distrito']) &&
                !empty($input['provincia']) &&
                !empty($input['departamento']) &&
                !empty($input['codigo_postal']) &&
                !empty($input['ubigeo']) &&
                !empty($input['pais']) &&
                !empty($input['email']) &&
                !empty($input['web']) &&
                !empty($input['fecha_autorizacion']) &&
                !empty($input['direccion_secundaria']) &&
                !empty($input['publicidad'])
            ) {
                $bind = array(
                    'id_company' => $this->functions->clean_string($input['id_company']),
                    'razon_social' => $this->functions->clean_string($input['razon_social']),
                    'nombre_comercial' => $this->functions->clean_string($input['nombre_comercial']),
                    'ruc' => $this->functions->clean_string($input['ruc']),
                    'telefono' => $this->functions->clean_string($input['telefono']),
                    'direccion' => $this->functions->clean_string($input['direccion']),
                    'distrito' => $this->functions->clean_string($input['distrito']),
                    'provincia' => $this->functions->clean_string($input['provincia']),
                    'departamento' => $this->functions->clean_string($input['departamento']),
                    'codigo_postal' => $this->functions->clean_string($input['codigo_postal']),
                    'ubigeo' => $this->functions->clean_string($input['ubigeo']),
                    'pais' => $this->functions->clean_string($input['pais']),
                    'email' => $this->functions->clean_string($input['email']),
                    'web' => $this->functions->clean_string($input['web']),
                    'fecha_autorizacion' => $this->functions->clean_string($input['fecha_autorizacion']),
                    'direccion_secundaria' => $this->functions->clean_string($input['direccion_secundaria']),
                    'publicidad' => $this->functions->clean_string($input['publicidad'])
                );

                // Solo agregar logo_sitio si se ha subido un nuevo archivo
                if (!empty($input['logo_sitio'])) {
                    $bind['logo_sitio'] = $this->functions->clean_string($input['logo_sitio']);
                }

                $obj = $this->load_model('Company');
                $response = $obj->update_company($bind);
                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Registro actualizado en el sistema con éxito.',
                            'data' => array('logo_url' => $input['logo_sitio'] ?? 'No se actualizó el logo')
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
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function create_config()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            if (!empty($_POST['modo_emision']) && !empty($_POST['usuario_sunat']) && !empty($_POST['contrasena_sunat']) && !empty($_POST['contrasena_certificado'])) {
                $bind = array(
                    'modo_emision' => $this->functions->clean_string($_POST['modo_emision']),
                    'usuario_sunat' => $this->functions->clean_string($_POST['usuario_sunat']),
                    'contrasena_sunat' => $this->functions->clean_string($_POST['contrasena_sunat']),
                    'contrasena_certificado' => $this->functions->clean_string($_POST['contrasena_certificado'])
                );
                $certificado_actualizado = false;
                if (!empty($_FILES['certificado']) && $_FILES['certificado']['error'] === UPLOAD_ERR_OK) {
                    $certificado = $_FILES['certificado'];
                    if ($certificado['type'] === 'application/x-pkcs12') {
                        $nombre_certificado = uniqid('cert_') . '.p12';
                        $ruta_certificado = __DIR__ . '/../../files/CERT/' . $nombre_certificado;
                        if (move_uploaded_file($certificado['tmp_name'], $ruta_certificado)) {
                            $bind['certificado'] = $nombre_certificado;
                            $certificado_actualizado = true;
                        } else {
                            $json = array(
                                'status' => 'ERROR',
                                'type' => 'error',
                                'msg' => 'No se pudo guardar el archivo del certificado.',
                                'data' => array('error' => error_get_last())
                            );
                            header('Content-Type: application/json');
                            echo json_encode($json);
                            return;
                        }
                    } else {
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'El archivo del certificado no es válido.',
                            'data' => array('type' => $certificado['type'])
                        );
                        header('Content-Type: application/json');
                        echo json_encode($json);
                        return;
                    }
                }

                $obj = $this->load_model('Company');
                $response = $obj->create_config($bind, $certificado_actualizado);
                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Configuración guardada con éxito.',
                            'data' => array('nombre_certificado' => $certificado_actualizado ? $bind['certificado'] : null)
                        );
                        break;
                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible guardar la configuración, por favor verifique.',
                            'data' => array(),
                        );
                        break;
                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result'],
                            'data' => array()
                        );
                        break;
                }
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron todos los campos necesarios, por favor verifique.',
                    'data' => array('post' => $_POST, 'files' => $_FILES)
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
        header('Content-Type: application/json');
        echo json_encode($json);
    }
    public function get_sunat()
    {
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
            $obj = $this->load_model('Company');
            $response = $obj->get_sunat();
            switch ($response['status']) {
                case 'OK':
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Registro encontrado.',
                        'data' => $response['result']
                    );
                    break;
                case 'ERROR':
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontró el registro.',
                        'data' => array()
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
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function get_config()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $obj = $this->load_model('Company');
            $response = $obj->get_config();
            switch ($response['status']) {
                case 'OK':
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Configuración encontrada.',
                        'data' => $response['result']
                    );
                    break;
                case 'ERROR':
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontró la configuración.',
                        'data' => array()
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
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => array()
            );
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function update_token()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            if (!empty($input['token']) && !empty($input['host']) && !empty($input['email']) && !empty($input['password'])) {
                $bind = array(
                    'token' => $this->functions->clean_string($input['token']),
                    'host' => $this->functions->clean_string($input['host']),
                    'email' => $this->functions->clean_string($input['email']),
                    'password' => $this->functions->clean_string($input['password'])
                );
                $obj = $this->load_model('Company');
                $response = $obj->update_token($bind);
                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Token actualizado con éxito.',
                            'data' => array()
                        );
                        break;
                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible actualizar el token, por favor verifique.',
                            'data' => array()
                        );
                        break;
                    case 'EXCEPTION':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result'],
                            'data' => array()
                        );
                        break;
                }
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron todos los campos necesarios, por favor verifique.',
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
        header('Content-Type: application/json');
        echo json_encode($json);
    }
}
