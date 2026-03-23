<?php
// --
class C_Cotize_Details extends Controller
{

    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function index()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            $this->functions->check_permissions($this->segment->get('modules'), 'Cotize');

            // Load clients
            $obj_clients = $this->load_model('Clients');
            $response = $obj_clients->get_clients();

            // Prepare data for the view
            $viewData = array(
                'clients' => ($response['status'] === 'OK') ? $response['result'] : array(),
                'modules' => $this->segment->get('modules'),
                'selected_menu' => 'Cotize',
                'page_title' => 'Nueva Cotización', // Set page title
                'cotize' => array(), // Initialize as an empty array
                'view_mode' => 'create' // Set to 'create' for new quotation
            );

            // Configure view
            $this->view->set_js('index');
            $this->view->set_menu(array(
                'modules' => $viewData['modules'],
                'view' => $viewData['selected_menu']
            ));

            // Pass data to the view
            $this->view->set_data($viewData);
            $this->view->set_view('index');
        } catch (Exception $e) {
            return array(
                'status' => 'ERROR',
                'message' => $e->getMessage()
            );
        }
    }

    // --
    public function save_cotize_details()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }

            // Add user_id to the POST data
            $_POST['user_id'] = $this->segment->get('id');

            // Validate required data
            if (empty($_POST['business_name_cli'])) {
                throw new Exception('El cliente es requerido');
            }

            if (empty($_POST['details'])) {
                throw new Exception('No hay productos agregados');
            }

            // Decode details
            $_POST['details'] = json_decode($_POST['details'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al procesar los detalles de la cotización');
            }

            // Generate a unique reference number or check if it already exists
            if (!empty($_POST['referencia'])) {
                // Check if a quotation with this reference already exists
                $model = $this->load_model('Cotize_Details');
                $exists = $model->check_if_reference_exists($_POST['referencia']);

                if ($exists) {
                    throw new Exception('Ya existe una cotización con esta referencia');
                }
            }

            // Save quotation
            $model = $this->load_model('Cotize_Details');
            $response = $model->save_cotize_details($_POST);

            ob_clean(); // Clean any previous output
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } catch (Exception $e) {
            ob_clean(); // Clean any previous output
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    // --
    public function get_products()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));

            $model = $this->load_model('Products');
            $products = $model->get_active_products();

            header('Content-Type: application/json');
            ob_clean(); // Limpiar cualquier salida previa

            echo json_encode([
                'status' => 'OK',
                'data' => $products
            ]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            ob_clean(); // Limpiar cualquier salida previa

            echo json_encode([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    // --
    public function get_client_data()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));

            if (empty($_GET['id_client'])) {
                throw new Exception('ID de cliente no proporcionado');
            }

            $clientModel = $this->load_model('Clients');
            $response = $clientModel->get_client_by_id([
                'id_client' => intval($_GET['id_client'])
            ]);

            // Asegurarse de que no haya output antes del JSON
            ob_clean();
            header('Content-Type: application/json');

            if ($response['status'] === 'OK') {
                echo json_encode([
                    'status' => 'OK',
                    'data' => $response['result']
                ]);
            } else {
                echo json_encode([
                    'status' => 'ERROR',
                    'message' => $response['message'] ?? 'Error al obtener datos del cliente'
                ]);
            }
            exit;
        } catch (Exception $e) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    // --
    public function print($id = null)
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));

            if (!$id) {
                echo "ID de cotización no proporcionado";
                exit;
            }

            // Cargar modelo y obtener datos de la cotización
            $model = $this->load_model('Cotize_Details');
            $cotize_data = $model->get_cotize_by_id($id);

            if ($cotize_data['status'] !== 'OK') {
                echo "Cotización no encontrada";
                exit;
            }

            // Preparar datos para la vista
            $viewData = array(
                'cotize' => $cotize_data['result'],
                'page_title' => 'Imprimir Cotización'
            );

            // Cargar vista de impresión
            $this->view->set_data($viewData);
            $this->view->set_view('print', false); // false para no cargar el layout

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            exit;
        }
    }

    // --
    public function get_cotize_data()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));

            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido');
            }

            if (empty($_GET['id'])) {
                throw new Exception('ID de cotización no proporcionado');
            }

            $model = $this->load_model('Cotize_Details');
            $response = $model->get_cotize_by_id($_GET['id']);

            header('Content-Type: application/json');

            if ($response['status'] === 'OK') {
                echo json_encode([
                    'status' => 'OK',
                    'data' => $response['result']
                ]);
            } else {
                echo json_encode([
                    'status' => 'ERROR',
                    'message' => $response['message']
                ]);
            }
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
    
    public function view($id = null)
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            $this->functions->check_permissions($this->segment->get('modules'), 'Cotize');

            if (!$id) {
                echo "ID de cotización no proporcionado";
                exit;
            }

            // Cargar modelo y obtener datos de la cotización
            $model = $this->load_model('Cotize_Details');
            $cotize_data = $model->get_cotize_by_id($id);

            if ($cotize_data['status'] !== 'OK') {
                echo "Cotización no encontrada";
                exit;
            }

            // Preparar datos para la vista
            $viewData = array(
                'modules' => $this->segment->get('modules'),
                'selected_menu' => 'Cotize',
                'page_title' => 'Ver Cotización #' . $id,
                'cotize' => $cotize_data['result'],
                'view_mode' => 'view' // Modo de solo visualización
            );

            // Configurar vista
            $this->view->set_js('index');
            $this->view->set_menu(array(
                'modules' => $viewData['modules'],
                'view' => $viewData['selected_menu']
            ));

            // Pasar datos a la vista
            $this->view->set_data($viewData);
            $this->view->set_view('index');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    // --
    public function edit($id = null)
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            $this->functions->check_permissions($this->segment->get('modules'), 'Cotize');

            if (!$id) {
                echo "ID de cotización no proporcionado";
                exit;
            }

            // Cargar modelo y obtener datos de la cotización
            $model = $this->load_model('Cotize_Details');
            $cotize_data = $model->get_cotize_by_id($id);

            if ($cotize_data['status'] !== 'OK') {
                echo "Cotización no encontrada";
                exit;
            }

            // Cargar clientes para el formulario
            $obj_clients = $this->load_model('Clients');
            $clients_response = $obj_clients->get_clients();

            // Preparar datos para la vista
            $viewData = array(
                'clients' => ($clients_response['status'] === 'OK') ? $clients_response['result'] : array(),
                'modules' => $this->segment->get('modules'),
                'selected_menu' => 'Cotize',
                'page_title' => 'Editar Cotización #' . $id,
                'cotize' => $cotize_data['result'],
                'view_mode' => 'edit' // Modo de edición
            );

            // Configurar vista
            $this->view->set_js('index');
            $this->view->set_menu(array(
                'modules' => $viewData['modules'],
                'view' => $viewData['selected_menu']
            ));

            // Pasar datos a la vista
            $this->view->set_data($viewData);
            $this->view->set_view('index');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}