
 <?php 
// --
class C_Income_Details extends Controller {

    // --
    public function __construct() {
		parent::__construct();
    }
    
    // --
    public function index() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Income');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Income')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    public function create_income_products() {
        header('Content-Type: application/json');
    
        // Validar sesión
        $this->functions->validate_session($this->segment->get('isActive'));
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Obtener los datos de la solicitud
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
    
            // Registrar los datos recibidos para depuración
            file_put_contents("log.txt", print_r($input, true), FILE_APPEND);
    
            // Verificar que haya productos en la solicitud
            if (empty($input['productos']) || !is_array($input['productos'])) {
                echo json_encode(['status' => 'ERROR', 'msg' => 'Rellene todos los campos de los productos']);
                return;
            }
    
            // Verificar que los campos obligatorios estén presentes
            if (!empty($input['id_person'])) {
    
                // Limpiar los datos de entrada
                $id_person = $this->functions->clean_string($input['id_person']);
                $id_user = $this->functions->clean_string($input['id_user']);
                $id_voucher_type = $this->functions->clean_string($input['id_voucher_type']);
                $id_payment_type = $this->functions->clean_string($input['id_payment_type']);
                $proof_series = $this->functions->clean_string($input['proof_series']);
                $voucher_series = $this->functions->clean_string($input['voucher_series']);
                $date_expiration = $this->functions->clean_string($input['date_expiration']);
                $number_installments = $this->functions->clean_string($input['number_installments']);
                $value_installment = $this->functions->clean_string($input['value_installment']);
    
                // Verificar si la fecha de expiración es válida
                if (!strtotime($date_expiration)) {
                    echo json_encode(['status' => 'ERROR', 'msg' => 'Fecha de expiración no válida.']);
                    return;
                }
    
                // Determinar el estado del pago
                $status = ($id_payment_type == '1') ? 2 : 1;
    
                // Datos para insertar en la tabla de ingresos
                $bind = array(
                    'id_person' => $id_person,
                    'id_user' => $id_user,
                    'id_voucher_type' => $id_voucher_type,
                    'id_payment_type' => $id_payment_type,
                    'proof_series' => $proof_series,
                    'voucher_series' => $voucher_series,
                    'date_expiration' => $date_expiration,
                    'number_installments' => $number_installments,
                    'value_installment' => $value_installment,
                    'status' => $status
                );
    
                // Cargar el modelo
                $obj = $this->load_model('Income_Details');
                
                // Insertar en la tabla `income`
                $insertedId = $obj->create_income_products($bind);
    
                // Verificar si la inserción fue exitosa
                if ($insertedId) {
                    
                    // Insertar los productos asociados
                    $insertCount = 0;
                    foreach ($input['productos'] as $producto) {
                        // Validar los campos de cada producto
                        $producto['id_income'] = $insertedId;
                        $result = $obj->insertIncomeProductDetails($producto);
                        if ($result === true) {
                            $insertCount++;
                        }
                    }
    
                    // Responder si los productos se insertaron correctamente
                    if ($insertCount > 0) {
                        echo json_encode(['status' => 'OK', 'id' => $insertedId, 'productos_insertados' => $insertCount]);
                    } else {
                        echo json_encode(['status' => 'ERROR', 'msg' => 'No se pudieron insertar los productos.']);
                    }
                } else {
                    echo json_encode([
                        'status' => 'ERROR',
                        'msg' => 'NO se insertó la tabla income'
                    ]);
                }
            } else {
                echo json_encode(['status' => 'ERROR', 'msg' => 'Campos obligatorios faltantes.']);
            }
        }
    }
    

    
    //--------------------- INSERT INCOME PRODUCTS DETAILS -------------------------
    public function create_income_products_details() {
        header('Content-Type: application/json');
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
    
            // Asegurarse de que se han enviado productos
            if (empty($input['productos'])) {
                echo json_encode(['status' => 'ERROR', 'msg' => 'No se enviaron productos.']);
                return;
            }
    
            $obj = $this->load_model('Income_Details');
            $insertCount = 0;
    
            foreach ($input['productos'] as $producto) {
                // Validación de datos antes de insertar
                if (empty($producto['id_product']) || empty($producto['quantity']) || empty($producto['unit_price']) || empty($producto['subtotal'])) {
                    echo json_encode(['status' => 'ERROR', 'msg' => 'Faltan datos en el producto.']);
                    return;
                }
    
                $result = $obj->insertIncomeProductDetails($producto);
                if ($result === true) {
                    $insertCount++;
                } else {
                    // Si ocurre un error en la inserción, devolver el mensaje de error
                    echo json_encode($result);
                    return;
                }
            }
    
            if ($insertCount > 0) {
                echo json_encode(['status' => 'OK', 'message' => 'Productos insertados correctamente.', 'insertados' => $insertCount]);
            } else {
                echo json_encode(['status' => 'ERROR', 'msg' => 'No se pudieron insertar los productos.']);
            }
        }
    }
    

//CONTROLLER : C_Income_Details
    //EXCEL 
    public function getById() {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id || !is_numeric($id)) {
            echo json_encode(["status" => "ERROR", "message" => "ID inválido"]);
            return;
        }
        $obj = $this->load_model('Income_Details');
        $producto =  $obj->get_producto_by_id($id);

        if ($producto) {
            echo json_encode(["status" => "OK", "data" => $producto]);
        } else {
            echo json_encode(["status" => "ERROR", "message" => "Producto no encontrado"]);
        }
    }
    //EXCEL full
    public function create_income_full() {
        header('Content-Type: application/json');
    
        $input = json_decode(file_get_contents("php://input"), true);
        if (empty($input)) {
            echo json_encode(["status" => "ERROR", "message" => "Datos vacíos"]);
            return;
        }
    
        if (empty($input['productos']) || !is_array($input['productos'])) {
            echo json_encode(["status" => "ERROR", "message" => "No se han enviado productos."]);
            return;
        }
    
        $model = $this->load_model("Income_Details");
    
        $cleanData = [
            'id_person' => isset($input['id_person']) ? (int)$input['id_person'] : null,
            'id_user' => isset($input['id_user']) ? (int)$input['id_user'] : null,
            'id_voucher_type' => isset($input['id_voucher_type']) ? (int)$input['id_voucher_type'] : null,
            'id_payment_type' => isset($input['id_payment_type']) ? (int)$input['id_payment_type'] : null,
            'proof_series' => !empty($input['proof_series']) ? $input['proof_series'] : null,
            'voucher_series' => $input['voucher_series'],
            'date_expiration' => $input['date_expiration'],
            'number_installments' => is_numeric($input['number_installments']) ? (int)$input['number_installments'] : null,
            'value_installment' => is_numeric($input['value_installment']) ? (float)$input['value_installment'] : null,
            'status' => isset($input['status']) ? (int)$input['status'] : 1
        ];
    
        $id_income = $model->create_income_products($cleanData);
    
        if (!$id_income) {
            echo json_encode(["status" => "ERROR", "message" => "No se pudo registrar el ingreso principal"]);
            return;
        }
    
        foreach ($input["productos"] as $p) {
            // Obtener el precio unitario desde la base de datos
            $unit_price = $model->getProductUnitPrice($p["id_product"]);
    
            if ($unit_price === null) {
                echo json_encode(["status" => "ERROR", "message" => "Producto con ID {$p['id_product']} no encontrado o sin precio."]);
                return;
            }
    
            $p["unit_price"] = $unit_price;
            $p["subtotal"] = $unit_price * (int)$p["quantity"];
    
            $producto = [
                "id_income" => $id_income,
                "id_product" => $p["id_product"],
                "quantity" => $p["quantity"],
                "unit_price" => $p["unit_price"],
                "subtotal" => $p["subtotal"]
            ];
    
            $res = $model->insertIncomeProductDetails($producto);
    
            if (is_array($res) && $res["status"] === "ERROR") {
                echo json_encode($res);
                return;
            }
        }
    
        echo json_encode(["status" => "OK", "message" => "Ingreso y productos guardados"]);
    }





}
