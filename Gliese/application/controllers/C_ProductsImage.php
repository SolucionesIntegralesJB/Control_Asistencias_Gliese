<?php 
// --
class C_ProductsImage extends Controller {

    // --
    public function __construct() {
		parent::__construct();
    }
    
    // --
    public function index() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'ProductsImage');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'ProductsImage')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    // --
    public function get_products() { 
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
            $obj = $this->load_model('ProductsImage');
            // --
            $response = $obj->get_products();
            // --
            switch ($response['status']) {
                // --
                case 'OK':
                    $data = array();
                    // --
                    foreach ($response['result'] as $item) {
                        // --
                        $data[] = array(
                            'id_product' => $item['id_product'],
                            'code' => $item['code'],
                            'name' => $item['name'],
                            'image' => $item['image'],
                        );
                    }
                    // --
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => $this->messages->message['list'],
                        'data' => $data
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
    public function get_product_by_id() {
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
            if (!empty($input['id_product'])) {
                // --
                $obj = $this->load_model('ProductsImage');
                // --
                $bind = array(
                    'id_product' => intval($input['id_product']));
                // --
                $response = $obj->get_product_by_id($bind);
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
    public function update_product() {
        // Validar la sesión
        $this->functions->validate_session($this->segment->get('isActive'));
    
        // Obtener el método de la solicitud
        $request = $_SERVER['REQUEST_METHOD'];
    
        // Verificar si la solicitud es un POST
        if ($request === 'POST') {
            // Obtener los datos del formulario
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
    
            // Verificar si los campos necesarios están presentes
            if (!empty($input['id_product']) &&
                !empty($input['code']) &&
                !empty($input['name']) &&
                !empty($_FILES['image'])
            ) {
                // Procesar la imagen
                $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Views/productsimage/images/';
                $uploadFile = $uploadDir . basename($_FILES['image']['name']);
    
                // Verificar si la imagen se subió correctamente
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                    // Limpiar los datos
                    $id_product = $this->functions->clean_string($input['id_product']);
                    $code = $this->functions->clean_string(strtoupper($input['code']));
                    $name = $this->functions->clean_string(strtoupper($input['name']));
                    $image = $this->functions->clean_string(strtoupper($_FILES['image']));
    
                    // Datos para la actualización
                    $bind = array(
                        'id_product' => $id_product,
                        'code' => $code,
                        'name' => $name,
                        'image' => $image
                    );
    
                    // Modelo de productos
                    $obj = $this->load_model('ProductsImage');
    
                    // Actualizar el producto
                    $response = $obj->update_product($bind);
    
                    // Manejar la respuesta
                    switch ($response['status']) {
                        case 'OK':
                            $json = array(
                                'status' => 'OK',
                                'type' => 'success',
                                'msg' => 'Registro actualizado en el sistema con éxito.',
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
                        'msg' => 'Error al subir la imagen, verificar.',
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
    
        // Enviar respuesta JSON
        header('Content-Type: application/json');
        echo json_encode($json);
    }
// --
}