<?php
// --
class C_Productdetails extends Controller
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
        $this->functions->check_permissions($this->segment->get('modules'), 'Productdetails');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Productdetails'));
        $this->view->set_view('index');     // -- Load View
    }

    public function create_product_stock_file()
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
                !empty($input['id_product']) &&
                !empty($input['code']) &&
                !empty($input['name']) &&
                !empty($input['description']) &&
                !empty($input['id_u_medida'])
            ) {
                // --
                $id_product = $this->functions->clean_string($input['id_product']);
                $code = $this->functions->clean_string($input['code']);
                $name = $this->functions->clean_string($input['name']);
                $description = $this->functions->clean_string($input['description']);
                $id_u_medida = $this->functions->clean_string($input['id_u_medida']);
                $id_label = $this->functions->clean_string($input['id_label']);
                $id_label = empty($id_label) ? null : $id_label;

                $bind = array(
                    'id_product' => $id_product,
                    'code' => $code,
                    'name' => $name,
                    'description' => $description,
                    'id_u_medida' => $id_u_medida,
                    'id_label' => $id_label,
                );

                $obj = $this->load_model('Productdetails');
                $response = $obj->create_product_stock_file($bind);

                switch ($response['status']) {
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Producto creado en el sistema con éxito.',
                            'data' => $response['result']
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible crear el producto ingresado, verificar.',
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
    public function get_subcategories_by_categories()
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
            if (!empty($input['id_category'])) {
                // --
                $obj = $this->load_model('Productdetails');
                // --
                $bind = array('id_category' => intval($input['id_category']));
                // --
                $response = $obj->get_subcategories_by_categories($bind);
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
    public function create_inventory()
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
            $stockValues = true;
            if (isset($_POST['stock'])) {
                $stocks = $_POST['stock'];
                foreach ($stocks as $stockValue) {
                    if (empty($stockValue)) {
                        $stockValues = false;
                        break;
                    }
                }
            }
            // --
            if (
                !empty($input['id_product']) &&
                !empty($input['id_section']) &&
                !empty($input['id_category']) &&
                !empty($input['id_subcategory']) &&
                $stockValues
            ) {
                // --
                $id_product = $this->functions->clean_string($input['id_product']);
                $id_section = $this->functions->clean_string($input['id_section']);
                $id_category = $this->functions->clean_string($input['id_category']);
                $id_subcategory = $this->functions->clean_string($input['id_subcategory']);

                // --
                $bind = array(
                    'id_product' => $id_product,
                    'id_section' => $id_section,
                    'id_category' => $id_category,
                    'id_subcategory' => $id_subcategory
                );
                // --
                $obj = $this->load_model('Productdetails');
                $response = $obj->create_inventory($bind);
                // --
                switch ($response['status']) {
                        // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Inventario creado en el sistema con éxito.',
                            'data' => $response['result']
                        );
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible crear el inventario ingresado, verificar',
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
                    'msg' => 'No se enviaron los campos de inventario necesarios, verificar.',
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
    public function get_product_by_id()
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
            if (!empty($input['id_product'])) {
                // --
                $obj = $this->load_model('Productdetails');
                // --
                $bind = array(
                    'id_product' => intval($input['id_product'])
                );
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

    public function create_detail_head()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }

            if (
                !empty($input['id_product']) &&
                !empty($input['id_header']) &&
                !empty($input['position']) &&
                !empty($input['content'])
            ) {
                $obj = $this->load_model('Productdetails');
                $data = array();

                foreach ($input['position'] as $index => $position) {
                    foreach ($input['id_header'] as $header_index => $id_header) {
                        $data[] = array(
                            'id_product' => $input['id_product'],
                            'id_header' => $id_header,
                            'position' => $position,
                            'content' => $input['content'][$index][$header_index] ?? ''
                        );
                    }
                }
                $result = $obj->create_detail_head($data);
                if ($result['status'] === 'OK') {
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Detalles del producto guardados correctamente.',
                        'data' => $result['result']
                    );
                } else {
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => 'Error al guardar los detalles del producto.',
                        'data' => $result['result']
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

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function img_product()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            if (!empty($input['id_product']) && !empty($_FILES['images'])) {
                $routeFile = $_SERVER['SCRIPT_FILENAME'];
                $uploadDir = substr($routeFile, 0, strrpos($routeFile, "../")) . 'public/app-assets/images/product/';
                $id_product = $this->functions->clean_string($input['id_product']);
                $bind = [];
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    $nameImage = basename($_FILES['images']['name'][$key]);
                    $typeImage = strtolower(pathinfo($nameImage, PATHINFO_EXTENSION));
                    if (in_array($typeImage, ['jpeg', 'jpg', 'png'])) {
                        $productDir = $uploadDir . $id_product . '/';
                        if (!is_dir($productDir)) {
                            mkdir($productDir, 0777, true);
                        }
                        $newName = $id_product . '_' . uniqid() . '.' . $typeImage;
                        $routFinal = $productDir . $newName;
                        if (move_uploaded_file($tmp_name, $routFinal)) {
                            $image = $id_product . '/' . $newName;
                            $bind[] = [
                                'id_product' => $id_product,
                                'image_url' => BASE_URL . 'public/app-assets/images/product/' . $image
                            ];
                        }
                    }
                }
                if (!empty($bind)) {
                    $obj = $this->load_model('Productdetails');
                    $response = $obj->img_product($bind);
                    if ($response['status'] === 'OK') {
                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Imágenes actualizadas con éxito.',
                            'data' => $bind
                        ];
                    } else {
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => 'No fue posible actualizar las imágenes. ' . ($response['result'] ?? ''),
                            'data' => []
                        ];
                    }
                } else {
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se pudieron mover los archivos o el tipo de archivo no está permitido.',
                        'data' => []
                    ];
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'Faltan datos necesarios para procesar las imágenes.',
                    'data' => []
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => []
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function delete_img_product()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            if (!empty($input['id_product']) && !empty($input['image_url']) && !empty($input['id'])) {
                $id_product = $this->functions->clean_string($input['id_product']);
                $image_url = $this->functions->clean_string($input['image_url']);
                $id = $this->functions->clean_string($input['id']);
                $routeFile = $_SERVER['SCRIPT_FILENAME'];
                $uploadDir = substr($routeFile, 0, strrpos($routeFile, "../")) . 'public/app-assets/images/product/';
                $fileName = basename($image_url);
                $filePath = $uploadDir . $id_product . '/' . $fileName;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $obj = $this->load_model('Productdetails');
                $response = $obj->delete_img_product($id, $id_product, $image_url);
                if ($response['status'] === 'OK') {
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Imagen eliminada con éxito.',
                        'data' => []
                    ];
                } else {
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => 'No fue posible eliminar la imagen.',
                        'data' => []
                    ];
                }
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'Faltan datos necesarios para eliminar la imagen.',
                    'data' => []
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
                'data' => []
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }
}
