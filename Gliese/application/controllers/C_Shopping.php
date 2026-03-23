
<?php 

class C_Shopping extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Shopping');
        $this->view->set_js('index');
        $this->view->set_menu([
            'modules' => $this->segment->get('modules'), 
            'view' => 'Shopping'
        ]);
        $this->view->set_view('index');
    }

    // ------------------ LISTADO DE INGRESOS ------------------

    public function get_Shopping() {
        $this->functions->validate_session($this->segment->get('isActive'));
    
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $obj = $this->load_model('Shopping');
            
            $response = $obj->get_Shopping();
    
            $json = [
                'status' => $response['status'],
                'type'   => $response['status'] === 'OK' ? 'success' : 'warning',
                'msg'    => $response['msg'],
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
        echo json_encode($json);
        exit;
    }
    public function confirmarPedido() {
        $this->functions->validate_session($this->segment->get('isActive'));

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = $_POST["id"] ?? null;
            if (!$id) {
                echo json_encode(["success" => false, "message" => "Falta el ID del pedido"]);
                exit;
            }

            $obj = $this->load_model("Shopping");
            $response = $obj->confirmarPedido((int)$id);

            echo json_encode($response);
            exit;
        } else {
            echo json_encode(["success" => false, "message" => "Método no permitido"]);
            exit;
        }
    }
    public function anularPedido() {
        $this->functions->validate_session($this->segment->get('isActive'));
    
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $id = $_POST["id"] ?? null;
            if (!$id) {
                echo json_encode(["success" => false, "message" => "Falta el ID del pedido"]);
                exit;
            }
    
            $obj = $this->load_model("Shopping");
            $response = $obj->anularPedido((int)$id); // 🔹 antes estaba confirmarPedido
    
            echo json_encode($response);
            exit;
        } else {
            echo json_encode(["success" => false, "message" => "Método no permitido"]);
            exit;
        }
    } 
    public function getOrderDetails() {
        $this->functions->validate_session($this->segment->get('isActive'));
    
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $order_id = $_POST["order_id"] ?? null;
            if (!$order_id) {
                echo json_encode(["success" => false, "message" => "Falta el ID del pedido"]);
                exit;
            }
    
            $obj = $this->load_model("Shopping");
            $response = $obj->getOrderDetails((int)$order_id); // Llamamos al modelo
    
            echo json_encode($response);
            exit;
        } else {
            echo json_encode(["success" => false, "message" => "Método no permitido"]);
            exit;
        }
    }
} 