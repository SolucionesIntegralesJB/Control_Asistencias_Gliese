<?php
// --
use Spipu\Html2Pdf\Html2Pdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class C_Billingpersale extends Controller
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
        $this->functions->check_permissions($this->segment->get('modules'), 'Billingpersale');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Billingpersale')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }


    // --
    public function get_billingpersale()
    {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_GET);
            }

            $campus_id = $this->segment->get('current_campus_id');

            if (!$campus_id) {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se ha seleccionado una ubicación.',
                    'data' => array()
                );
            } else {
                $obj = $this->load_model('Billingpersale');
                $response = $obj->get_billingpersale($campus_id);

                switch ($response['status']) {
                    case 'OK':
                        // Verificar documentos con status 1
                        $pending_docs = array_filter($response['result'], function ($item) {
                            return $item['status'] === '1';
                        });

                        $warning_message = '';
                        if (!empty($pending_docs)) {
                            $count_pending = count($pending_docs);
                            $warning_message = array(
                                'show' => true,
                                'count' => $count_pending,
                                'message' => "¡Atención! Tiene {$count_pending} documento(s) pendiente(s) de declarar a SUNAT. " .
                                    "Estos documentos deben ser declarados dentro del plazo establecido para evitar multas.",
                                'action' => 'showPending'
                            );
                        }

                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Listado de registros encontrados.',
                            'data' => $response['result'],
                            'warning' => $warning_message
                        );
                        break;

                    case 'ERROR':
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontraron registros en el sistema.',
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

    // --
    public function get_billingpersale_by_id()
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
            if (!empty($input['id_billingpersale'])) {
                // --
                $obj = $this->load_model('Billingpersale');
                // --
                $bind = array(
                    'id_billingpersale' => intval($input['id_billingpersale'])
                );
                // --
                $response = $obj->get_billingpersale_by_id($bind);
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
    public function create_clients()
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
                !empty($input['document_type']) &&
                !empty($input['name']) &&
                !empty($input['document_number']) &&
                !empty($input['address']) &&
                !empty($input['phone']) &&
                !empty($input['business_name']) &&
                !empty($input['email']) &&
                !empty($input['description_document_type'])
            ) {
                // --
                $document_type = $this->functions->clean_string($input['document_type']);
                $name = $this->functions->clean_string(strtoupper(ucfirst($input['name'])));
                $document_number = $this->functions->clean_string($input['document_number']);
                $description_document_type = $this->functions->clean_string($input['description_document_type']);
                $address = $this->functions->clean_string($input['address']);
                $phone = $this->functions->clean_string($input['phone']);
                $business_name = $this->functions->clean_string($input['business_name']);
                $email = $this->functions->clean_string($input['email']);
                // --
                $is_verified = $this->functions->verified_document_type($description_document_type, $document_number); // -- verified document type
                // --
                if ($is_verified) {
                    $bind = array(
                        'id_document_type' => $document_type,
                        'name' => $name,
                        'document_number' => $document_number,
                        'address' => $address,
                        'phone' => $phone,
                        'business_name' => $business_name,
                        'email' => $email
                    );

                    // --
                    $obj = $this->load_model('Clients');
                    $response = $obj->create_clients($bind);
                    // --
                    switch ($response['status']) {
                            // --
                        case 'OK':
                            // --
                            $json = array(
                                'status' => 'OK',
                                'type' => 'success',
                                'msg' => 'Registro almacenado en el sistema con éxito.',
                                // 'msg' => ,
                                'data' => array()
                            );
                            // --
                            break;

                        case 'ERROR':
                            // --
                            $json = array(
                                'status' => 'ERROR',
                                'type' => 'warning',
                                'msg' => 'No fue posible guardar el registro ingresado, verificar.',
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
    public function update_clients()
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
                !empty($input['id_clients']) &&
                !empty($input['document_type']) &&
                !empty($input['name']) &&
                !empty($input['document_number']) &&
                !empty($input['address']) &&
                !empty($input['phone']) &&
                !empty($input['business_name']) &&
                !empty($input['email']) &&
                !empty($input['description_document_type'])
            ) {
                // --
                $id_clients = $this->functions->clean_string($input['id_clients']);
                $document_type = $this->functions->clean_string($input['document_type']);
                $name = $this->functions->clean_string(strtoupper(ucfirst($input['name'])));
                $document_number = $this->functions->clean_string($input['document_number']);
                $description_document_type = $this->functions->clean_string($input['description_document_type']);
                $address = $this->functions->clean_string($input['address']);
                $phone = $this->functions->clean_string($input['phone']);
                $business_name = $this->functions->clean_string($input['business_name']);
                $email = $this->functions->clean_string($input['email']);
                // --
                $is_verified = $this->functions->verified_document_type($description_document_type, $document_number); // -- verified document type
                // --
                if ($is_verified) {
                    // -- 
                    $bind = array(
                        'id_clients' => $id_clients,
                        'id_document_type' => $document_type,
                        'name' => $name,
                        'document_number' => $document_number,
                        'address' => $address,
                        'phone' => $phone,
                        'business_name' => $business_name,
                        'email' => $email

                    );
                    // --
                    $obj = $this->load_model('Clients');
                    $response = $obj->update_clients($bind);
                    // --
                    switch ($response['status']) {
                            // --
                        case 'OK':
                            // --
                            $json = array(
                                'status' => 'OK',
                                'type' => 'success',
                                'msg' => 'Registro actualizado en el sistema con éxito.',
                                'data' => array()
                            );
                            // --
                            break;

                        case 'ERROR':
                            // --
                            $json = array(
                                'status' => 'ERROR',
                                'type' => 'warning',
                                'msg' => 'No fue posible guardar el registro ingresado, verificar.',
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
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'Número de documento invalido, verificar.',
                    );
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
    public function delete_proforma()
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
            if (!empty($input['id_proforma'])) {
                // --
                $id_proforma = $this->functions->clean_string($input['id_proforma']);
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
                        // --
                        break;

                    case 'ERROR':
                        // --
                        $json = array(
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No fue posible eliminar el registro, verificar.',
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

    //--
    public function get_business_name()
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

    public function get_billingpersale_Report()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));

            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido.');
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? filter_input_array(INPUT_GET);

            if (empty($input['id_billingpersale']) || empty($input['tipo'])) {
                throw new Exception('ID de billingpersale o tipo no proporcionado.');
            }

            $id_billingpersale = intval($input['id_billingpersale']);
            $tipo = intval($input['tipo']);

            $billingModel = $this->load_model('Billingpersale');

            $companyData = $billingModel->get_company();
            $reportData = $billingModel->get_billingpersale_Report($id_billingpersale);

            if ($companyData['status'] !== 'OK' || empty($companyData['result'])) {
                throw new Exception('No se pudo obtener la información de la compañía.');
            }

            if ($reportData['status'] !== 'OK' || empty($reportData['result'])) {
                throw new Exception('No se encontró el reporte de billingpersale.');
            }

            $rucE = $companyData['result']['ruc'] ?? '';
            $regc = $reportData['result'];
            $codigo_voucher = $regc['voucher_type'];
            $serie = $regc['series'];
            $correlativo = $regc['correlative'];

            ob_start();
            switch ($tipo) {
                case 1:
                    include 'application/Reporte/Factura.php';
                    break;
                case 2:
                    include 'application/Reporte/Ticket.php';
                    break;
                default:
                    throw new Exception('Tipo de reporte no válido');
            }
            $content = ob_get_clean();

            $html2pdf = new Html2Pdf();
            $html2pdf->writeHTML($content);
            $html2pdf->output("{$rucE}-0{$codigo_voucher}-{$serie}-{$correlativo}.pdf");
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
            exit;
        }
    }
    public function Emitir_comprobante()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            $input = filter_input_array(INPUT_GET);

            if (empty($input['id_billingpersale'])) {
                throw new Exception('ID de factura no proporcionado');
            }

            $id_billingpersale = intval($input['id_billingpersale']);

            ob_start();
            include_once(__DIR__ . '/../efactura/template/factura.php');
            $output = ob_get_clean();

            if (isset($json_respuesta)) {
                $respuesta = json_decode($json_respuesta, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $billingModel = $this->load_model('Billingpersale');
                    $status = 1;
                    $response_message = '';

                    if (!$respuesta['success']) {
                        // Caso de error
                        $status = 3;
                        $response_message = $respuesta['mensaje_error'] ?? 'Error desconocido';
                    } else {
                        // Caso de éxito
                        switch ($respuesta['estado']) {
                            case 'ACEPTADA':
                                if (!empty($respuesta['observaciones'])) {
                                    $status = 4; // Aceptado con observaciones
                                } else {
                                    $status = 2; // Aceptado sin observaciones
                                }
                                $response_message = $respuesta['descripcion'];
                                if (!empty($respuesta['observaciones'])) {
                                    $response_message .= '. ' . implode('. ', $respuesta['observaciones']);
                                }
                                break;

                            case 'RECHAZADA':
                            case 'EXCEPCIÓN':
                                $status = 3;
                                $response_message = $respuesta['descripcion'];
                                break;

                            default:
                                $status = 3;
                                $response_message = 'Estado no reconocido';
                        }
                    }

                    // Actualizar estado en la base de datos
                    $updateData = array(
                        'id_billingpersale' => $id_billingpersale,
                        'status' => $status,
                        'response' => $response_message
                    );
                    $billingModel->get_billingpersale_status($updateData);

                    $this->sendJsonResponse($respuesta);
                } else {
                    throw new Exception('Respuesta inválida del servidor');
                }
            } else {
                $this->sendJsonResponse(['status' => 'OK', 'output' => $output]);
            }
        } catch (Exception $e) {
            // En caso de error, actualizar el estado como rechazado
            if (isset($id_billingpersale)) {
                $billingModel = $this->load_model('Billingpersale');
                $updateData = array(
                    'id_billingpersale' => $id_billingpersale,
                    'status' => 3,
                    'response' => $e->getMessage()
                );
                $billingModel->get_billingpersale_status($updateData);
            }

            $this->sendJsonResponse([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        }
    }
    private function sendJsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    public function send_email()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            $input = filter_input_array(INPUT_GET);

            if (empty($input['id_billingpersale']) || empty($input['email'])) {
                throw new Exception('ID de factura o dirección de correo electrónico no proporcionados');
            }

            $id_billingpersale = intval($input['id_billingpersale']);
            $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Dirección de correo electrónico no válida');
            }

            $billingModel = $this->load_model('Billingpersale');

            $companyData = $billingModel->get_company();
            $reportData = $billingModel->get_billingpersale_Report($id_billingpersale);

            if (
                $companyData['status'] !== 'OK' || empty($companyData['result']) ||
                $reportData['status'] !== 'OK' || empty($reportData['result'])
            ) {
                throw new Exception('No se pudieron obtener los datos necesarios');
            }

            $rucE = $companyData['result']['ruc'] ?? '';
            $regc = $reportData['result'];
            $codigo_voucher = $regc['code'] ?? '';
            $serie = $regc['series'] ?? '';
            $correlativo = $regc['correlative'] ?? '';

            $baseFileName = "{$rucE}-{$codigo_voucher}-{$serie}-{$correlativo}";
            $xmlFilePath = __DIR__ . '/../../files/FRM/' . $baseFileName . '.xml';

            if (!file_exists($xmlFilePath)) {
                throw new Exception('No se pudo encontrar el archivo XML');
            }

            $obj = $this->load_model('Company');
            $response = $obj->get_config();
            $host = $response['result']['host'];
            $username = $response['result']['email'];
            $password = $response['result']['password'];

            ob_start();
            include 'application/Reporte/Factura.php';
            $content = ob_get_clean();

            $html2pdf = new Html2Pdf();
            $html2pdf->writeHTML($content);
            $pdfContent = $html2pdf->output('', 'S');
            $xmlContent = file_get_contents($xmlFilePath);

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
            $mail->setFrom('facturacion@solucionesintegralesjb.com', 'Facturacion');
            $mail->Subject = 'Factura adjunta';
            $mail->Body    = 'Adjunto encontrará su factura en formato PDF y XML.';
            $mail->addStringAttachment($pdfContent, $baseFileName . '.pdf', 'base64', 'application/pdf');
            $mail->addStringAttachment($xmlContent, $baseFileName . '.xml', 'base64', 'application/xml');
            $mail->send();
            header('Content-Type: application/json');
            echo json_encode(['status' => 'OK', 'message' => 'Correo enviado con éxito']);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
        }
    }

    public function xml_billingpersale()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));

            $input = filter_input_array(INPUT_GET);
            if (empty($input['id_billingpersale'])) {
                throw new Exception('ID de billingpersale no proporcionado.');
            }

            $id_billingpersale = intval($input['id_billingpersale']);
            $billingModel = $this->load_model('Billingpersale');

            $reportData = $billingModel->get_billingpersale_Report($id_billingpersale);
            $companyData = $billingModel->get_company();

            if (
                $reportData['status'] !== 'OK' || empty($reportData['result']) ||
                $companyData['status'] !== 'OK' || empty($companyData['result'])
            ) {
                throw new Exception('No se pudieron obtener los datos necesarios.');
            }

            $rucE = $companyData['result']['ruc'] ?? '';
            $regc = $reportData['result'];
            $cod = $regc['code'] ?? '';
            $serie = $regc['series'] ?? '';
            $correlativo = $regc['correlative'] ?? '';

            $baseFileName = "{$rucE}-{$cod}-{$serie}-{$correlativo}";
            $xmlFilePath = __DIR__ . '/../../files/FRM/' . $baseFileName . '.xml';

            if (!file_exists($xmlFilePath)) {
                throw new Exception('No se pudo encontrar el archivo XML.');
            }

            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename="' . $baseFileName . '.xml"');
            header('Content-Length: ' . filesize($xmlFilePath));
            readfile($xmlFilePath);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['status' => 'ERROR', 'message' => $e->getMessage()]);
            exit;
        }
    }

    public function get_number_email()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));

            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido.');
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? filter_input_array(INPUT_GET);

            if (empty($input['id_billingpersale'])) {
                throw new Exception('ID de billingpersale no proporcionado.');
            }

            $id_billingpersale = intval($input['id_billingpersale']);
            $billingModel = $this->load_model('Billingpersale');

            $bind = array(
                'id_billingpersale' => $id_billingpersale
            );

            $response = $billingModel->get_number_email($bind);

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
                        'msg' => 'No se encontraron datos del comprobante.',
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
}
