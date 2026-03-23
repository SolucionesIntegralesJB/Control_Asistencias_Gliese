
<?php 

class C_Income extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Income');
        $this->view->set_js('index');
        $this->view->set_menu([
            'modules' => $this->segment->get('modules'), 
            'view' => 'Income'
        ]);
        $this->view->set_view('index');
    }

    // ------------------ LISTADO DE INGRESOS ------------------
    public function get_income() {
        $this->functions->validate_session($this->segment->get('isActive'));
    
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $obj = $this->load_model('Income');
            
            // Primero actualizamos registros vencidos (si aplica)
            $obj->update_expired_incomes(); // Descomentar si necesitas esta funcionalidad
            
            $response = $obj->get_income();
    
            // Verificar registros pendientes
            $warning_message = null;
            if ($response['status'] === 'OK') {
                $incomes_pending = array_filter($response['result'], function($item) {
                    return $item['status'] == "1"; // 1 = Pendiente
                });
    
                if (!empty($incomes_pending)) {
                    $count_pending = count($incomes_pending);
                    $warning_message = [
                        'show' => true,
                        'count' => $count_pending,
                        'message' => "¡Atención! Tiene {$count_pending} ingreso(s) pendiente(s) por verificar.",
                        'action' => 'showPending'
                    ];
                }
            }
    
            $json = [
                'status' => $response['status'],
                'type' => $response['status'] === 'OK' ? 'success' : 'warning',
                'msg' => $response['msg'],
                'data' => $response['result'] ?? [],
                'warning' => $warning_message // Añadimos la advertencia si hay pendientes
            ];
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
        exit;
    }

    // ------------------ DETALLES POR ID DE INGRESO ------------------
    public function get_income_details() {
        $this->functions->validate_session($this->segment->get('isActive'));

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

            if ($id <= 0) {
                echo json_encode([
                    'status' => 'ERROR',
                    'type'   => 'error',
                    'msg'    => 'ID inválido.',
                    'data'   => []
                ]);
                exit;
            }

            $obj = $this->load_model('Income');
            $response = $obj->get_income_details($id);

            $json = [
                'status' => $response['status'],
                'type'   => $response['status'] === 'OK' ? 'success' : 'warning',
                'msg'    => $response['status'] === 'OK' ? 'Detalles del ingreso encontrados.' : 'No se encontraron detalles para este ingreso.',
                'data'   => $response['result'] ?? []
            ];
        } else {
            $json = [
                'status' => 'ERROR',
                'type'   => 'error',
                'msg'    => 'Método no permitido.',
                'data'   => []
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ------------------ ELIMINAR INGRESO ------------------
    public function delete_income() {
        $this->functions->validate_session($this->segment->get('isActive'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

            if (!empty($input['id_income'])) {
                $bind = ['id_income' => intval($input['id_income'])];
                $obj = $this->load_model('Income');
                $response = $obj->delete_income($bind);

                $json = [
                    'status' => $response['status'],
                    'type'   => $response['status'] === 'OK' ? 'success' : 'error',
                    'msg'    => $response['status'] === 'OK' ? 'Registro eliminado exitosamente.' : $response['result'],
                    'data'   => []
                ];
            } else {
                $json = [
                    'status' => 'ERROR',
                    'type'   => 'warning',
                    'msg'    => 'No se enviaron los campos necesarios.',
                    'data'   => []
                ];
            }
        } else {
            $json = [
                'status' => 'ERROR',
                'type'   => 'error',
                'msg'    => 'Método no permitido.',
                'data'   => []
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($json);
        exit;
    }


//-------------------------------------------------------------------------------
//--------------------------------- falta arreglar-------------------------------
    


    //--------------------------- UPDATE -----------------------------------

    // public function update_income_product() {
    //     $this->functions->validate_session($this->segment->get('isActive'));
    
    //     if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //         $input = json_decode(file_get_contents('php://input'), true);
    //         if (empty($input)) {
    //             $input = filter_input_array(INPUT_POST);
    //         }
    
           
    //         if (isset($input['products']) && count($input['products']) > 0) {
    //             $products = $input['products'];  
    //             unset($input['products']);      
    
             
    //             $obj = $this->load_model('Income_Products');
    //             $response = $obj->update_income_product($input);
    
    //             if ($response['status'] === 'OK') {
    //                 foreach ($products as $producto) {
    //                     $productoData = [
    //                         'id_income_products' => $input['id_income_product'], 
    //                         'id_product' => $producto['id_product'], 
    //                         'quantity' => $producto['quantity'], 
    //                         'subtotal' => $producto['subtotal'], 
    //                         'full_purchase' => $producto['full_purchase'],
    //                         'product_serie' => $producto['product_serie'],
    //                         'product_sale_price' => $producto['product_sale_price'],
    //                         'product_expiration_date' => $producto['product_expiration_date'],
    //                     ];
    //                     $obj->insertIncomeProductDetails($productoData);
    //                 }
    //             }

    //             $json = [
    //                 'status' => $response['status'],
    //                 'type' => $response['status'] === 'OK' ? 'success' : 'error',
    //                 'msg' => $response['message'] ?? 'Ocurrió un error al actualizar la compra.'
    //             ];
    //         } else {
               
    //             $obj = $this->load_model('Income_Products');
    //             $response = $obj->update_income_product($input);
                
    //             $json = [
    //                 'status' => $response['status'],
    //                 'type' => $response['status'] === 'OK' ? 'success' : 'error',
    //                 'msg' => $response['message'] ?? 'Ocurrió un error al actualizar la compra.'
    //             ];
    //         }
    //     } else {
    //         $json = [
    //             'status' => 'ERROR',
    //             'type' => 'error',
    //             'msg' => 'Método no permitido.'
    //         ];
    //     }
    
    //     header('Content-Type: application/json');
    //     echo json_encode($json);
    // }
    
} 