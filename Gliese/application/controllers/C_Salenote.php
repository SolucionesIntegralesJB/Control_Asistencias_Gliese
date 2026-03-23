<?php 
use Spipu\Html2Pdf\Html2Pdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class C_Salenote extends Controller {

    // --
    public function __construct() {
        parent::__construct();
    }
    
    // --
    public function index() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Salenote');
        // --
        $this->view->set_js('index');
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Salenote'));
        $this->view->set_view('index');
    }

    // --
    public function get_salenote() { 
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
            $obj = $this->load_model('Salenote');
            // --
            $response = $obj->get_salenote();
            // --
            switch ($response['status']) {
                // --
                case 'OK':
                    // --
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Listado de notas de venta encontradas.',
                        'data' => $response['result']
                    );
                    break;
  
                case 'ERROR':
                    // --
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron notas de venta en el sistema.',
                        'data' => array(),
                    );
                    break;
  
                case 'EXCEPTION':
                    // --
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['result']->getMessage(),
                        'data' => array()
                    );
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
    public function get_salenote_by_id() {
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
            if (!empty($input['id_salenote'])) {
                // --
                $obj = $this->load_model('Salenote');
                // --
                $bind = array('id_salenote' => intval($input['id_salenote']));
                // --
                $response = $obj->get_salenote_by_id($bind);
                // --
                switch ($response['status']) {
                    // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Nota de venta encontrada.',
                            'data' => $response['result']
                        );
                        break;
  
                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontró la nota de venta en el sistema.',
                            'data' => array(),
                        );
                        break;
  
                    case 'EXCEPTION':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
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
    public function create_salenote() {
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
            if (!empty($input['id_clients']) &&
                !empty($input['id_user']) &&
                !empty($input['id_voucher_type']) &&
                !empty($input['date_issue']) &&
                !empty($input['correlative']) &&
                !empty($input['total_sale']) &&
                !empty($input['status'])
            ) {
                // --
                $bind = array(
                    'id_clients' => $this->functions->clean_string($input['id_clients']),
                    'id_user' => $this->functions->clean_string($input['id_user']),
                    'id_voucher_type' => $this->functions->clean_string($input['id_voucher_type']),
                    'date_issue' => $this->functions->clean_string($input['date_issue']),
                    'correlative' => $this->functions->clean_string($input['correlative']),
                    'total_sale' => $this->functions->clean_string($input['total_sale']),
                    'status' => $this->functions->clean_string($input['status'])
                );

                // --
                $obj = $this->load_model('Salenote');
                $response = $obj->create_salenote($bind);
                // --
                switch ($response['status']) {
                    // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Nota de venta creada con éxito.',
                            'data' => array()
                        );
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible crear la nota de venta, verificar.',
                            'data' => array());
                        break;

                    case 'EXCEPTION':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
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
    public function update_salenote() {
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
            if (!empty($input['id_salenote']) && 
                !empty($input['id_clients']) &&
                !empty($input['id_user']) &&
                !empty($input['id_voucher_type']) &&
                !empty($input['date_issue']) &&
                !empty($input['correlative']) &&
                !empty($input['total_sale']) &&
                !empty($input['status'])
            ) {
                // --
                $bind = array(
                    'id_salenote' => $this->functions->clean_string($input['id_salenote']),
                    'id_clients' => $this->functions->clean_string($input['id_clients']),
                    'id_user' => $this->functions->clean_string($input['id_user']),
                    'id_voucher_type' => $this->functions->clean_string($input['id_voucher_type']),
                    'date_issue' => $this->functions->clean_string($input['date_issue']),
                    'correlative' => $this->functions->clean_string($input['correlative']),
                    'total_sale' => $this->functions->clean_string($input['total_sale']),
                    'status' => $this->functions->clean_string($input['status'])
                );
    
                // --
                $obj = $this->load_model('Salenote');
                $response = $obj->update_salenote($bind);
                // --
                switch ($response['status']) {
                    // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Nota de venta actualizada con éxito.',
                            'data' => array()
                        );
                        break;
    
                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible actualizar la nota de venta, verificar.',
                            'data' => array(),
                        );
                        break;
    
                    case 'EXCEPTION':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
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
    public function delete_salenote() {
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
            if (!empty($input['id_salenote'])) {
                // --
                $id_salenote = $this->functions->clean_string($input['id_salenote']);
                // --
                $bind = array(
                    'id_salenote' => $id_salenote
                );
                // --
                $obj = $this->load_model('Salenote');
                $response = $obj->delete_salenote($bind);
                // --
                switch ($response['status']) {
                    // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Nota de venta eliminada del sistema con éxito.',
                            'data' => array()
                        );
                        break;
    
                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible eliminar la nota de venta, verificar.',
                            'data' => array(),
                        );
                        break;
    
                    case 'EXCEPTION':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => array()
                        );
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
    public function get_salenote_Report() {
    try {
        $this->functions->validate_session($this->segment->get('isActive'));

        // Validar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            throw new Exception('Método no permitido.');
        }

        // Obtener parámetros
        $input = $_GET;
        if (empty($input['id_salenote']) || empty($input['tipo'])) {
            throw new Exception('ID de nota de venta o tipo no proporcionado.');
        }

        // Convertir y validar parámetros
        $id_salenote = intval($input['id_salenote']);
        $tipo = intval($input['tipo']);
        
        if ($id_salenote <= 0) {
            throw new Exception('ID de nota de venta inválido.');
        }
        
        if ($tipo !== 1 && $tipo !== 2) {
            throw new Exception('Tipo de reporte no válido (1: PDF, 2: Ticket)');
        }

        // Cargar modelos
        $salenoteModel = $this->load_model('Salenote');
        $companyModel = $this->load_model('Company');

        // Obtener datos de la empresa
        $companyData = $companyModel->get_company();
        if ($companyData['status'] !== 'OK' || empty($companyData['result'])) {
            throw new Exception('Datos de la empresa no disponibles.');
        }

        // Obtener datos de la nota de venta
        $salenoteData = $salenoteModel->get_salenote_report($id_salenote);
        if ($salenoteData['status'] !== 'OK' || empty($salenoteData['result'])) {
            throw new Exception("Nota de venta #$id_salenote no encontrada.");
        }

        // Obtener detalles
        $salenoteDetails = $salenoteModel->get_salenote_details_report($id_salenote);
        if ($salenoteDetails['status'] !== 'OK') {
            throw new Exception('Detalles de nota de venta no disponibles.');
        }

        // Preparar datos para la vista
        $data = [
            'companyData' => [
                'status' => 'OK',
                'result' => $companyData['result']
            ],
            'proformaData' => [
                'status' => 'OK',
                'result' => array_merge($salenoteData['result'], [
                    'id_voucher_type' => 'NOTA DE VENTA',      
                    'total_sale' => $salenoteData['result']['total'] ?? 0,
                    'igv_total' => $salenoteData['result']['igv'] ?? 0,
                    'igv' => 18
                ])
            ],
            'detalles' => $salenoteDetails['result']
        ];

        // Generar contenido HTML según el tipo
        ob_start();
        extract($data);
        
        $basePath = realpath(__DIR__.'/../../');
        if ($basePath === false) {
            throw new Exception("No se pudo resolver la ruta base del proyecto");
        }
        
        // Seleccionar vista según el tipo
        $viewFile = ($tipo == 1) ? 'salesnote.php' : 'salenote_ticket.php';
        $viewPath = $basePath.'/application/Reporte/'.$viewFile;
        
        if (!file_exists($viewPath)) {
            throw new Exception("Archivo de vista no encontrado: ".$viewFile);
        }
        
        include $viewPath;
        $content = ob_get_clean();

        // Configurar PDF según el tipo
        if ($tipo == 1) {
            // Configuración para PDF normal (A4)
            $html2pdf = new Html2Pdf(
                'P',      // Portrait
                'A4',     // Tamaño
                'es',     // Idioma
                true,     // Unicode
                'UTF-8',  // Codificación
                [10, 10, 10, 10] // Márgenes
            );
        } else {
            // Configuración para Ticket (80mm de ancho)
            $html2pdf = new Html2Pdf(
                'P',           // Portrait
                array(80, 297), // Tamaño (80mm ancho x 297mm alto)
                'es',          // Idioma
                true,          // Unicode
                'UTF-8',       // Codificación
                [5, 5, 5, 5]   // Márgenes más pequeños
            );
        }

        // Configurar cabeceras
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="'.($tipo == 1 ? 'salesnote' : 'ticket').'.pdf"');
        header('Cache-Control: public, must-revalidate, max-age=0');

        // Generar y mostrar PDF
        $html2pdf->writeHTML($content);
        $html2pdf->output(
            ($tipo == 1 ? "SALENOTE-" : "TICKET-")."{$salenoteData['result']['series']}-{$salenoteData['result']['correlative']}.pdf", 
            'I' // Modo Inline
        );

        exit;

    } catch (Exception $e) {
        error_log("Error generando reporte: " . $e->getMessage());
        
        $response = [
            'status' => 'ERROR',
            'message' => $e->getMessage(),
            'debug' => [
                'id_salenote' => $id_salenote ?? 'N/A',
                'tipo' => $tipo ?? 'N/A',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ];

        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode($response);
        exit;
    }
}

    public function send_salenote_email() {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            $input = filter_input_array(INPUT_GET);
    
            if (empty($input['id_salenote']) || empty($input['email'])) {
                throw new Exception('ID de nota de venta o dirección de correo electrónico no proporcionados');
            }
    
            $id_salenote = intval($input['id_salenote']);
            $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
    
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Dirección de correo electrónico no válida');
            }
    
            $salenoteModel = $this->load_model('Salenote');
            $companyModel = $this->load_model('Company');
    
            $companyData = $companyModel->get_company();
            $salenoteData = $salenoteModel->get_salenote_report($id_salenote);
    
            if ($companyData['status'] !== 'OK' || empty($companyData['result']) ||
                $salenoteData['status'] !== 'OK' || empty($salenoteData['result'])) {
                throw new Exception('No se pudieron obtener los datos necesarios');
            }
    
            $regc = $salenoteData['result'];
            $serie = $regc['series'];
            $correlativo = $regc['correlative'];
    
            ob_start();
            include __DIR__ . '/../views/reports/salenote_pdf.php';
            $content = ob_get_clean();
    
            $html2pdf = new Html2Pdf();
            $html2pdf->writeHTML($content);
            $pdfContent = $html2pdf->output('', 'S');
    
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = 'mail.solucionesintegralesjb.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'facturacion@solucionesintegralesjb.com';
            $mail->Password   = 'N!6zW&skzDy,';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->setFrom('facturacion@solucionesintegralesjb.com', 'Notas de Venta');
            $mail->Subject = 'Nota de venta adjunta';
            $mail->Body    = 'Adjunto encontrará su nota de venta en formato PDF.';
            $mail->addStringAttachment($pdfContent, "SALENOTE-{$serie}-{$correlativo}.pdf", 'base64', 'application/pdf');
            $mail->send();
    
            header('Content-Type: application/json');
            echo json_encode(['status' => 'OK', 'message' => 'Correo enviado con éxito']);
    
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
        }
    }

    public function update_salenote_status() {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            $input = filter_input_array(INPUT_GET);
    
            if (empty($input['id_salenote']) || !isset($input['status'])) {
                throw new Exception('ID de nota de venta o estado no proporcionado');
            }
    
            $id_salenote = intval($input['id_salenote']);
            $status = intval($input['status']);
            $response_message = $input['response_message'] ?? '';
    
            $salenoteModel = $this->load_model('Salenote');
            $updateData = array(
                'id_salenote' => $id_salenote,
                'status' => $status,
                'response' => $response_message
            );
    
            $response = $salenoteModel->update_salenote_status($updateData);
    
            if ($response['status'] !== 'OK') {
                throw new Exception('Error al actualizar el estado de la nota de venta');
            }
    
            $this->sendJsonResponse(['status' => 'OK', 'message' => 'Estado actualizado correctamente']);
    
        } catch (Exception $e) {
            $this->sendJsonResponse([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function get_client_email() {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
    
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido.');
            }
    
            $input = json_decode(file_get_contents('php://input'), true) ?? filter_input_array(INPUT_GET);
    
            if (empty($input['id_salenote'])) {
                throw new Exception('ID de nota de venta no proporcionado.');
            }
    
            $id_salenote = intval($input['id_salenote']);
            $salenoteModel = $this->load_model('Salenote');
    
            $bind = array('id_salenote' => $id_salenote);
            $response = $salenoteModel->get_client_email($bind);
    
            switch ($response['status']) {
                case 'OK':
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Datos obtenidos correctamente.',
                        'data' => $response['result']
                    );
                    break;
    
                case 'ERROR':
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron datos del cliente.',
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
    
                default:
                    throw new Exception('Respuesta no válida del modelo.');
            }
    
            header('Content-Type: application/json');
            echo json_encode($json);
    
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => $e->getMessage(),
                'data' => array()
            ]);
        }
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}