<?php
require_once __DIR__ . '/../core/Controller.php';

class C_Igvinvoicing_Details extends Controller {
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = $this->load_model('Igvinvoicing_Details');
        
        // Configuración inicial
        $this->view->set_js('index');
        $this->view->set_menu([
            'modules' => $this->segment->get('modules') ?? [],
            'view' => 'Igvinvoicing'
        ]);
    }

    public function index() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->view->set_view('index');
    }

    public function get_initial_data() {
        header('Content-Type: application/json');
        try {
            $data = [
                'clients' => $this->model->get_clients(),
                'voucher_types' => $this->model->get_voucher_type(),
                'payment_types' => $this->model->get_payment_type(),
                'payment_methods' => $this->model->get_payment_method(),
                'igv' => $this->model->get_igv(),
                'coin' => $this->model->get_coin(),
                'current_date' => date('Y-m-d')
            ];
            echo json_encode(['status' => 'OK', 'data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
        }
    }

    public function save_invoice() {
        // Configuración inicial
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        try {
            // 1. Verificar método HTTP
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Método no permitido", 405);
            }

            // 2. Obtener y validar datos de entrada
            $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            if (empty($input)) {
                throw new Exception("No se recibieron datos", 400);
            }

            // 3. Validar campos requeridos
            $required_fields = [
                'business_name_cli' => 'Cliente',
                'fecha_emision' => 'Fecha de emisión',
                'vt_description' => 'Tipo de comprobante',
                'product_code' => 'Productos'
            ];
            
            foreach ($required_fields as $field => $name) {
                if (empty($input[$field])) {
                    throw new Exception("Campo requerido: {$name}", 400);
                }
            }

            // 4. Validar cliente usando el modelo
            $client_id = (int)$input['business_name_cli'];
            if (!$this->model->client_exists($client_id)) {
                throw new Exception("El cliente con ID $client_id no está registrado", 404);
            }

            // 5. Obtener series y correlativo
            $series_info = $this->model->get_series($input['vt_description']);
            if (!$series_info) {
                throw new Exception("No se pudo generar la serie para el comprobante", 500);
            }

            // 6. Preparar datos para el encabezado
            // 1. Preparamos los detalles y obtenemos el sale_price del primer producto
$details = [];
$first_sale_price = 0; // Valor por defecto si no hay detalles

if (!empty($input['product_code']) && is_array($input['product_code'])) {
    foreach ($input['product_code'] as $key => $value) {
        $sale_price = $input['sale_price'][$key] ?? 0;
        $quantity = $input['quantity'][$key] ?? 1;
        
        // Guardamos el primer sale_price para la cabecera (índice 0)
        if ($key == 0) {
            $first_sale_price = $sale_price;
        }
        
        // Construimos el array de detalles
        $details[] = [
            'product_code' => $input['product_code'][$key] ?? 'SERV' . str_pad($key + 1, 4, '0', STR_PAD_LEFT),
            'product_description' => $input['product_description'][$key] ?? 'Servicio ' . ($key + 1),
            'unit_of_measure' => $input['unit_of_measure'][$key] ?? 'NIU',
            'quantity' => $quantity,
            'sale_price' => $sale_price,
            'affectation' => 'GRAVADA',
            'tax_percentage' => $input['igv_asig'] ?? 18
        ];
    }
}

// 2. Construimos la cabecera con el primer sale_price como total_sale
$header_data = [
    'user_id' => $this->segment->get('id') ?? 1,
    'client_id' => $client_id,
    'voucher_type_code' => $input['vt_description'],
    'series' => $series_info['series'],
    'correlative' => $series_info['correlative'],
    'date_time' => $input['fecha_emision'],
    'due_date' => $input['fecha_vencimiento'] ?? $input['fecha_emision'],
    'currency' => 'PEN',
    'payment_type_code' => $input['fp_description'] ?? 'CONTADO',
    'payment_method' => $input['pt_description'] ?? 'EFECTIVO',
    'tax' => $input['igv'] ?? 18.00,
    'taxable_operations' => $input['op_gravadas'] ?? 0,
    'total_igv' => $input['igv_total'] ?? 0,
    'total_sale' =>$input['total_venta']?? 0, 
    'legend' => 'SON: ' . number_format($input['total_venta'] ?? 0, 2) . ' SOLES',
 // Leyenda actualizada
    'status' => 'PENDIENTE',
    'time' => date('H:i:s'),
    'assigned_igv' => $input['igv_asig'] ?? 1,
    'type' => ($input['vt_description']),
    'document_number_cli' => $input['document_number_cli'] ?? '',
    'address_cli' => $input['address_cli'] ?? '',
    'business_name_cli' => $input['business_name_cli']
];
            // 8. Crear factura usando el modelo
            $result = $this->model->create_invoice($header_data, $details);

            if ($result['status'] !== 'OK') {
                throw new Exception($result['message'] ?? "Error al crear factura", 500);
            }

            // 9. Actualizar correlativo
            $this->model->update_correlative($input['vt_description'], $series_info['correlative']);

            // 10. Respuesta exitosa
            echo json_encode([
                'status' => 'OK',
                'message' => 'Factura creada correctamente',
                'data' => [
                    'invoice_id' => $result['data']['invoice_id'],
                    'correlative' => $series_info['series'] . '-' . $series_info['correlative'],
                    
                ]
            ]);

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 500);
            echo json_encode([
                'status' => 'ERROR',
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function get_client_info($id) {
        header('Content-Type: application/json');
        try {
            $client = $this->model->get_client_by_id($id);
            echo json_encode($client);
        } catch (Exception $e) {
            echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
        }
    }


}