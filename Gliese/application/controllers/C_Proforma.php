<?php 
// --
use Spipu\Html2Pdf\Html2Pdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
class C_Proforma extends Controller {

    // --
    public function __construct() {
        parent::__construct();
    }
    
    // --
    public function index() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Proforma');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Proforma')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    // --
    public function get_proforma() { 
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
            $obj = $this->load_model('Proforma');
            // --
            $response = $obj->get_proforma();
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
                    break;
  
                case 'ERROR':
                    // --
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron registros en el sistema.',
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
    public function get_proforma_by_id() {
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
            if (!empty($input['id_proforma'])) {
                // --
                $obj = $this->load_model('Proforma');
                // --
                $bind = array('id_proforma' => intval($input['id_proforma']));
                // --
                $response = $obj->get_proforma_by_id($bind);
                // --
                switch ($response['status']) {
                    // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Registro encontrado.',
                            'data' => $response['result']
                        );
                        break;
  
                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontró el registro en el sistema.',
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
    public function create_proforma() {
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
                $obj = $this->load_model('Proforma');
                $response = $obj->create_proforma($bind);
                // --
                switch ($response['status']) {
                    // --
                    case 'OK':
                        // --
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Proforma creada con éxito.',
                            'data' => array()
                        );
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible crear la proforma, verificar.',
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
            public function update_proforma() {
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
                    if (!empty($input['id_proforma']) && 
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
                            'id_proforma' => $this->functions->clean_string($input['id_proforma']),
                            'id_clients' => $this->functions->clean_string($input['id_clients']),
                            'id_user' => $this->functions->clean_string($input['id_user']),
                            'id_voucher_type' => $this->functions->clean_string($input['id_voucher_type']),
                            'date_issue' => $this->functions->clean_string($input['date_issue']),
                            'correlative' => $this->functions->clean_string($input['correlative']),
                            'total_sale' => $this->functions->clean_string($input['total_sale']),
                            'status' => $this->functions->clean_string($input['status'])
                        );
            
                        // --
                        $obj = $this->load_model('Proforma');
                        $response = $obj->update_proforma($bind);
                        // --
                        switch ($response['status']) {
                            // --
                            case 'OK':
                                // --
                                $json = array(
                                    'status' => 'OK',
                                    'type' => 'success',
                                    'msg' => 'Proforma actualizada con éxito.',
                                    'data' => array()
                                );
                                break;
            
                            case 'ERROR':
                                // --
                                $json = array(
                                    'status' => 'ERROR',
                                    'type' => 'warning',
                                    'msg' => 'No fue posible actualizar la proforma, verificar.',
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
            public function delete_proforma() {
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
                    if (!empty($input['id_proforma'])) {
                        // --
                        $id_proforma = $this->functions->clean_string($input['id_proforma']); // Asegúrate de que esta línea esté correcta
                        // --
                        $bind = array(
                            'id_proforma' => $id_proforma
                        );
                        // --
                        $obj = $this->load_model('Proforma');
                        $response = $obj->delete_proforma($bind);
                        // --
                        switch ($response['status']) {
                            // --
                            case 'OK':
                                // --
                                $json = array(
                                    'status' => 'OK',
                                    'type' => 'success',
                                    'msg' => 'Registro eliminado del sistema con éxito.',
                                    'data' => array()
                                );
                                break;
            
                            case 'ERROR':
                                // --
                                $json = array(
                                    'status' => 'ERROR',
                                    'type' => 'warning',
                                    'msg' => 'No fue posible eliminar el registro, verificar.',
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
            public function get_business_name() {
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
                    $obj = $this->load_model('Clients');
                    // --
                    $response = $obj->get_business_name();
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
                            break;
            
                        case 'ERROR':
                            // --
                            $json = array(
                                'status' => 'ERROR',
                                'type' => 'warning',
                                'msg' => 'No se encontraron registros en el sistema.',
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
            public function get_proforma_Report() {
                try {
                    $this->functions->validate_session($this->segment->get('isActive'));
            
                    // Validar método HTTP
                    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                        throw new Exception('Método no permitido.');
                    }
            
                    // Obtener parámetros
                    $input = $_GET;
                    if (empty($input['id_proforma'])) {
                        throw new Exception('ID de proforma no proporcionado.');
                    }
            
                    // Convertir y validar ID
                    $id_proforma = intval($input['id_proforma']);
                    if ($id_proforma <= 0) {
                        throw new Exception('ID de proforma inválido.');
                    }
            
                    // Cargar modelos
                    $proformaModel = $this->load_model('Proforma');
                    $companyModel = $this->load_model('Company');
            
                    // Obtener datos de la empresa
                    $companyData = $companyModel->get_company();
                    if ($companyData['status'] !== 'OK' || empty($companyData['result'])) {
                        throw new Exception('Datos de la empresa no disponibles.');
                    }
            
                    // Obtener datos de la proforma
                    $proformaData = $proformaModel->get_proforma_report($id_proforma);
                    if ($proformaData['status'] !== 'OK' || empty($proformaData['result'])) {
                        throw new Exception("Proforma #$id_proforma no encontrada.");
                    }
            
                    // Obtener detalles
                    $proformaDetails = $proformaModel->get_proforma_details_report($id_proforma);
                    if ($proformaDetails['status'] !== 'OK') {
                        throw new Exception('Detalles de proforma no disponibles.');
                    }
            
                    // Preparar datos para la vista
                    $data = [
                        'companyData' => $companyData,
                        'proformaData' => $proformaData,
                        'detalles' => $proformaDetails['result']
                    ];
            
                    // Generar contenido HTML
                    ob_start();
                    extract($data);
                    include 'application/Reporte/proforma.php';
                    $content = ob_get_clean();
            
                    // Configurar PDF
                    $html2pdf = new Html2Pdf(
                        'P',      // Orientación (Portrait)
                        'A4',     // Formato
                        'es',     // Idioma
                        true,     // Unicode
                        'UTF-8',  // Codificación
                        [10, 10, 10, 10] // Márgenes
                    );
            
                    // Configurar cabeceras para vista en navegador
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="proforma.pdf"');
                    header('Cache-Control: public, must-revalidate, max-age=0');
            
                    // Generar y mostrar PDF
                    $html2pdf->writeHTML($content);
                    $html2pdf->output(
                        "PROFORMA-{$proformaData['result']['series_proforma']}-{$proformaData['result']['correlative']}.pdf", 
                        'I' // Modo Inline (abre en navegador)
                    );
            
                    exit;
            
                } catch (Exception $e) {
                    error_log("Error generando proforma: " . $e->getMessage());
                    
                    // Respuesta de error estructurada
                    $response = [
                        'status' => 'ERROR',
                        'message' => $e->getMessage(),
                        'debug' => [
                            'id_proforma' => $id_proforma ?? 'N/A',
                            'timestamp' => date('Y-m-d H:i:s')
                        ]
                    ];
            
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode($response);
                    exit;
                }
            }
        
            public function send_proforma_email() {
                try {
                    $this->functions->validate_session($this->segment->get('isActive'));
                    $input = filter_input_array(INPUT_GET);
        
                    if (empty($input['id_proforma']) || empty($input['email'])) {
                        throw new Exception('ID de proforma o dirección de correo electrónico no proporcionados');
                    }
        
                    $id_proforma = intval($input['id_proforma']);
                    $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
        
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Dirección de correo electrónico no válida');
                    }
        
                    $proformaModel = $this->load_model('Proforma');
                    $companyModel = $this->load_model('Company');
        
                    $companyData = $companyModel->get_company();
                    $proformaData = $proformaModel->get_proforma_report($id_proforma);
        
                    if ($companyData['status'] !== 'OK' || empty($companyData['result']) ||
                        $proformaData['status'] !== 'OK' || empty($proformaData['result'])) {
                        throw new Exception('No se pudieron obtener los datos necesarios');
                    }
        
                    $regc = $proformaData['result'];
                    $serie = $regc['series_proforma'];
                    $correlativo = $regc['correlative'];
        
                    ob_start();
                    include 'application/views/proforma_pdf.php';
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
                    $mail->setFrom('facturacion@solucionesintegralesjb.com', 'Proformas');
                    $mail->Subject = 'Proforma adjunta';
                    $mail->Body    = 'Adjunto encontrará su proforma en formato PDF.';
                    $mail->addStringAttachment($pdfContent, "PROFORMA-{$serie}-{$correlativo}.pdf", 'base64', 'application/pdf');
                    $mail->send();
        
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'OK', 'message' => 'Correo enviado con éxito']);
        
                } catch (Exception $e) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
                }
            }
        
            public function update_proforma_status() {
                try {
                    $this->functions->validate_session($this->segment->get('isActive'));
                    $input = filter_input_array(INPUT_GET);
        
                    if (empty($input['id_proforma']) || !isset($input['status'])) {
                        throw new Exception('ID de proforma o estado no proporcionado');
                    }
        
                    $id_proforma = intval($input['id_proforma']);
                    $status = intval($input['status']);
                    $response_message = $input['response_message'] ?? '';
        
                    $proformaModel = $this->load_model('Proforma');
                    $updateData = array(
                        'id_proforma' => $id_proforma,
                        'status' => $status,
                        'response' => $response_message
                    );
        
                    $response = $proformaModel->update_proforma_status($updateData);
        
                    if ($response['status'] !== 'OK') {
                        throw new Exception('Error al actualizar el estado de la proforma');
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
        
                    if (empty($input['id_proforma'])) {
                        throw new Exception('ID de proforma no proporcionado.');
                    }
        
                    $id_proforma = intval($input['id_proforma']);
                    $proformaModel = $this->load_model('Proforma');
        
                    $bind = array('id_proforma' => $id_proforma);
                    $response = $proformaModel->get_client_email($bind);
        
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
    
                        
