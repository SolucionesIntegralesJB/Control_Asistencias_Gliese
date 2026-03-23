<?php
use Spipu\Html2Pdf\Html2Pdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class C_Igvinvoicing extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Igvinvoicing');
        
        $this->view->set_js('index');
        $this->view->set_menu([
            'modules' => $this->segment->get('modules'), 
            'view' => 'Igvinvoicing'
        ]);
        $this->view->set_view('index');
    }

    public function get_igvinvoicing()
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
                $json = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se ha seleccionado una ubicación.',
                    'data' => []
                ];
            } else {
                $obj = $this->load_model('Igvinvoicing');
                $response = $obj->get_igvinvoicing($campus_id);

                switch ($response['status']) {
                    case 'OK':
                        $pending_docs = array_filter($response['result'], function ($item) {
                            return $item['status'] === '1';
                        });

                        $warning_message = '';
                        if (!empty($pending_docs)) {
                            $count_pending = count($pending_docs);
                            $warning_message = [
                                'show' => true,
                                'count' => $count_pending,
                                'message' => "¡Atención! Tiene {$count_pending} documento(s) pendiente(s) de declarar a SUNAT.",
                                'action' => 'showPending'
                            ];
                        }

                        $json = [
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Listado de registros encontrados.',
                            'data' => $response['result'],
                            'warning' => $warning_message
                        ];
                        break;

                    case 'ERROR':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'warning',
                            'msg' => 'No se encontraron registros en el sistema.',
                            'data' => [],
                        ];
                        break;

                    case 'EXCEPTION':
                        $json = [
                            'status' => 'ERROR',
                            'type' => 'error',
                            'msg' => $response['result']->getMessage(),
                            'data' => []
                        ];
                        break;
                }
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

    public function get_igvinvoicing_report() 
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));

            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido.');
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? filter_input_array(INPUT_GET);

            if (empty($input['id_igvinvoice']) || empty($input['tipo'])) {
                throw new Exception('ID de factura IGV o tipo no proporcionado.');
            }

            $id_igvinvoice = intval($input['id_igvinvoice']);
            $tipo = intval($input['tipo']);

            $model = $this->load_model('Igvinvoicing');

            // Obtener todos los datos necesarios
            $companyData = $model->get_company();
            $reportData = $model->get_igvinvoicing_report($id_igvinvoice);
            $detailsData = $model->get_igvinvoicing_details($id_igvinvoice);

            if ($companyData['status'] !== 'OK' || empty($companyData['result'])) {
                throw new Exception('No se pudo obtener la información de la compañía.');
            }

            if ($reportData['status'] !== 'OK' || empty($reportData['result'])) {
                throw new Exception('No se encontró el reporte de factura IGV.');
            }

            if ($detailsData['status'] !== 'OK' || empty($detailsData['result'])) {
                throw new Exception('No se encontraron detalles para la factura IGV.');
            }

            // Preparar datos para la vista
            $data = [
                'companyData' => $companyData,
                'reportData' => $reportData,
                'detailsData' => $detailsData,
                'useIntl' => extension_loaded('intl')
            ];
            if (!extension_loaded('gd') || !function_exists('imagecreate')) {
                throw new Exception('La extensión GD para PHP no está instalada. Es necesaria para generar códigos QR.');
            }
            $rucE = $companyData['result']['ruc'] ?? '';
            $regc = $reportData['result'];
            $codigo_voucher = $regc['code'] ?? '';
            $serie = $regc['series'] ?? '';
            $correlativo = $regc['correlative'] ?? '';
            ob_start();
            switch ($tipo) {
                case 1:
                    include 'application/Reporte/factura-igv.php';
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
    public function emit_comprobante()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            $input = filter_input_array(INPUT_GET);

            if (empty($input['id_igvinvoice'])) {
                throw new Exception('ID de factura IGV no proporcionado');
            }

            $id_igvinvoice = intval($input['id_igvinvoice']);

            ob_start();
            include_once(__DIR__ . '/../efactura/template/factura_igv.php');
            $output = ob_get_clean();

            if (isset($json_respuesta)) {
                $respuesta = json_decode($json_respuesta, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $model = $this->load_model('Igvinvoicing');
                    $status = 1;
                    $response_message = '';

                    if (!$respuesta['success']) {
                        $status = 3;
                        $response_message = $respuesta['mensaje_error'] ?? 'Error desconocido';
                    } else {
                        switch ($respuesta['estado']) {
                            case 'ACEPTADA':
                                $status = !empty($respuesta['observaciones']) ? 4 : 2;
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

                    $updateData = [
                        'id_igvinvoice' => $id_igvinvoice,
                        'status' => $status,
                        'response' => $response_message
                    ];
                    $model->update_igvinvoicing_status($updateData);

                    $this->sendJsonResponse($respuesta);
                } else {
                    throw new Exception('Respuesta inválida del servidor');
                }
            } else {
                $this->sendJsonResponse(['status' => 'OK', 'output' => $output]);
            }
        } catch (Exception $e) {
            if (isset($id_igvinvoice)) {
                $model = $this->load_model('Igvinvoicing');
                $updateData = [
                    'id_igvinvoice' => $id_igvinvoice,
                    'status' => 3,
                    'response' => $e->getMessage()
                ];
                $model->update_igvinvoicing_status($updateData);
            }

            $this->sendJsonResponse([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function send_email()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            $input = filter_input_array(INPUT_GET);

            if (empty($input['id_igvinvoice']) || empty($input['email'])) {
                throw new Exception('ID de factura IGV o dirección de correo electrónico no proporcionados');
            }

            $id_igvinvoice = intval($input['id_igvinvoice']);
            $email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Dirección de correo electrónico no válida');
            }

            $model = $this->load_model('Igvinvoicing');

            $companyData = $model->get_company();
            $reportData = $model->get_igvinvoicing_report($id_igvinvoice);

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
            include 'application/Reporte/factura-igv.php';
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
            $mail->Subject = 'Factura IGV adjunta';
            $mail->Body    = 'Adjunto encontrará su factura IGV en formato PDF y XML.';
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

    public function xml_igvinvoicing()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));
            $input = filter_input_array(INPUT_GET);
            
            if (empty($input['id_igvinvoice'])) {
                throw new Exception('ID de factura IGV no proporcionado.');
            }

            $id_igvinvoice = intval($input['id_igvinvoice']);
            $model = $this->load_model('Igvinvoicing');

            $reportData = $model->get_igvinvoicing_report($id_igvinvoice);
            $companyData = $model->get_company();

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

    public function get_client_contact()
    {
        try {
            $this->functions->validate_session($this->segment->get('isActive'));

            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new Exception('Método no permitido.');
            }

            $input = json_decode(file_get_contents('php://input'), true) ?? filter_input_array(INPUT_GET);

            if (empty($input['id_igvinvoice'])) {
                throw new Exception('ID de factura IGV no proporcionado.');
            }

            $id_igvinvoice = intval($input['id_igvinvoice']);
            $model = $this->load_model('Igvinvoicing');

            $bind = ['id_igvinvoice' => $id_igvinvoice];
            $response = $model->get_client_contact($bind);

            switch ($response['status']) {
                case 'OK':
                    $json = [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Datos obtenidos correctamente.',
                        'data' => $response['result']
                    ];
                    break;

                case 'ERROR':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se encontraron datos del comprobante.',
                        'data' => []
                    ];
                    break;

                case 'EXCEPTION':
                    $json = [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['result']->getMessage(),
                        'data' => []
                    ];
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
                'data' => []
            ]);
        }
    }

    private function sendJsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
}