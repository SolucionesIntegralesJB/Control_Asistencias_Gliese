<?php

use Greenter\Model\Company\Company;
use Spipu\Html2Pdf\Html2Pdf;

class C_Online_Order extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->functions->validate_session($this->segment->get('isActive'));
    }

    public function index()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Online_Order');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Online_Order'));
        $this->view->set_view('index');     // -- Load View
    }

    

    public function get_online_orders() 
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $obj = $this->load_model('Online_Order');
            $response = $obj->get_online_orders();
            
            $json = match ($response['status']) {
                'OK' => ['status' => 'OK', 'type' => 'success', 'msg' => 'Listado de registros encontrados.', 'data' => $response['result']],
                'ERROR' => ['status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se encontraron registros.', 'data' => []],
                'EXCEPTION' => ['status' => 'ERROR', 'type' => 'error', 'msg' => is_string($response['result']) ? $response['result'] : 'Error desconocido.', 'data' => []],
                default => ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Error desconocido.', 'data' => []]
            };
        } else {
            $json = ['status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.', 'data' => []];
        }

        header('Content-Type: application/json');
        echo json_encode($json, JSON_PRETTY_PRINT);
    }

    public function get_order_by_id()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if($request === 'GET'){
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)){
                $input = filter_input_array(INPUT_GET);
            }
            if(!empty($input['id_pedido'])){
                $obj = $this->load_model('Online_Order');
                $bind = array(
                    'id_pedido' => intval($input['id_pedido'])
                );
                $response = $obj->get_order_by_id($bind);
                switch ($response['status']){
                    case 'OK':
                        $json = array(
                            'status'=>'OK',
                            'type'=>'success',
                            'msg'=>'Registro encontrado.',
                            'data'=>$response['result']
                        );
                        break;
                    case 'ERROR':
                        $json = array(
                            'status'=>'ERROR',
                            'type'=>'warning',
                            'msg'=>'Registro no encontrado.',
                            'data'=>[]
                        );
                        break;
                    case 'EXCEPTION':
                        $json = array(
                            'status'=>'ERROR',
                            'type'=>'error',
                            'msg'=>is_string($response['result']) ? $response['result'] : 'Error desconocido.',
                            'data'=>[]
                        );
                        break;
                }
            }else{
                $json = array(
                    'status'=>'ERROR',
                    'type'=>'warning',
                    'msg'=>'No se enviaron los campos necesario, verificar.',
                    'data'=>array()
                );
            }
        }else{
            $json = array(
                'status'=>'ERROR',
                'type'=>'error',
                'msg'=>'Método no permitido.',
                'data'=>array()
            );
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function update_online_order()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST'){
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)){
                $input = filter_input_array(INPUT_POST);
            }
            if(
                !empty($input['id_pedido']) &&
                !empty($input['estado_pago'])&&
                !empty($input['estado_entrega'])
            ){
                $id_pedido = $this->functions->clean_string($input['id_pedido']);
                $estado_pago = $this->functions->clean_string($input['estado_pago']);
                $estado_entrega = $this->functions->clean_string($input['estado_entrega']);
                
                $bind = array(
                    'id_pedido'      => $id_pedido,
                    'estado_pago'    => $estado_pago,
                    'estado_entrega' => $estado_entrega
                );

                $obj = $this->load_model('Online_Order');
                $response = $obj->update_online_order($bind);
                
                switch ($response['status']){
                    case 'OK':
                        $json = array(
                            'status'=>'OK',
                            'type'=>'success',
                            'msg'=>'Registro actualizado en el sistema.',
                            'data'=>[]
                        );
                        break;
                    case 'ERROR':
                        $json = array(
                            'status'=>'ERROR',
                            'type'=>'warning',
                            'msg'=>'No se pudo actualizar el registro.',
                            'data'=>[]
                        );
                        break;
                    case 'EXCEPTION':
                        $json = array(
                            'status'=>'ERROR',
                            'type'=>'error',
                            'msg'=>$response['result']->getMessage(),
                            'data'=>array()
                        );
                        break;
                }
            } else {
                $json = array(
                    'status'=>'ERROR',
                    'type'=>'warning',
                    'msg'=>'No se enviaron los campos necesario, verificar.',
                    'data'=>array()
                );
            }
        } else {
            $json = array(
                'status'=>'ERROR',
                'type'=>'error',
                'msg'=>'Método no permitido.',
                'data'=>array()
            );
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function get_onlineOrder_Report()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
    
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido.');
            }
    
            $input = json_decode(file_get_contents('php://input'), true) ?? filter_input_array(INPUT_GET);
    
            if (empty($input['id_pedido']) || empty($input['tipo'])) {
                throw new Exception('ID de pedido y tipo de reporte son necesarios.');
            }
    
            $id_pedido = intval($input['id_pedido']);
            $tipo = intval($input['tipo']);
    
            $onlineModel = $this->load_model('Online_Order');
    
            // datos de la empresa
            $companyData = $onlineModel->get_company();
            if ($companyData['status'] !== 'OK' || empty($companyData['result'])) {
                throw new Exception($companyData['message'] ?? 'No se encontraron datos de la empresa.');
            }
    
            // aqui los datos del pedido
            $reportData = $onlineModel->get_onlineOrder_Report($id_pedido);
            if ($reportData['status'] !== 'OK' || empty($reportData['result'])) {
                throw new Exception('No se encontraron datos del pedido.');
            }
    
            // aca los detalles del pedido
            $detailsData = $onlineModel->get_online_order_details($id_pedido);
            if ($detailsData['status'] !== 'OK') {
                throw new Exception('No se encontraron detalles del pedido.');
            }
            $detalles = $detailsData['result'];
            
            // Generamos el ticket
            ob_start();
            switch ($tipo) {
                case 1:
                    include 'application/Reporte/Factura_online.php';
                    break;
                case 2:
                    include 'application/Reporte/ticket_online.php';
                    break;
                default:
                    throw new Exception('Tipo de reporte no válido');
            }
            $content = ob_get_clean();
    
            $rucE = $companyData['result']['ruc'] ?? '';
            $regc = $reportData['result'];
            $codigo_voucher = $regc['voucher_id'] ?? '';
            $serie = $regc['series'] ?? '';
            $correlativo = $regc['correlative'] ?? '';
            
            if (empty($codigo_voucher) || empty($serie) || empty($correlativo)) {
                throw new Exception('Faltan datos críticos del pedido (voucher, serie o correlativo).');
            }
    
            // Aca ocurre la magia :v
            $html2pdf = new Html2Pdf('P', '200x80', 'es', true, 'UTF-8');
            $html2pdf->writeHTML($content);
            $html2pdf->output("{$rucE}-{$codigo_voucher}-{$serie}-{$correlativo}.pdf");
    
        } catch (Exception $e) {
            error_log('Error en get_onlineOrder_Report: ' . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'status' => 'ERROR',
                'msg' => $e->getMessage(),
                'details' => 'Consulte el log para más información'
            ]);
            exit;
        }
    }
}


