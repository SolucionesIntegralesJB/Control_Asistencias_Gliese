<?php
// --
class C_Salenote_Details extends Controller
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
        $this->functions->check_permissions($this->segment->get('modules'), 'Salenote');
        // --
        $this->view->set_js('index');       // -- Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Salenote')); // -- Active Menu
        $this->view->set_view('index');     // -- Load View
    }

    public function create_salenote()
    {
        ob_start();
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = $_POST;
            }

            // Validación de campos requeridos
            if (
                !empty($input['id_clients']) &&
                !empty($input['id_user']) &&
                !empty($input['id_voucher_type']) &&
                !empty($input['id_coins']) &&
                !empty($input['date_issue']) &&
                !empty($input['total_sale']) &&
                isset($input['id_product']) && is_array($input['id_product'])
            ) {

                $saleData = [
                    'id_clients' => $this->functions->clean_string($input['id_clients']),
                    'id_user' => $this->functions->clean_string($input['id_user']),
                    'id_voucher_type' => $this->functions->clean_string($input['id_voucher_type']),
                    'id_coins' => $this->functions->clean_string($input['id_coins']),
                    'id_document_reason' => isset($input['id_document_reason']) ? $this->functions->clean_string($input['id_document_reason']) : null,
                    'id_payment_type' => isset($input['id_payment_type']) ? $this->functions->clean_string($input['id_payment_type']) : 1,
                    'doc_related' => isset($input['doc_related']) ? $this->functions->clean_string($input['doc_related']) : null,
                    'series' => isset($input['series']) ? $this->functions->clean_string($input['series']) : '',
                    'correlative' => isset($input['correlative']) ? $this->functions->clean_string($input['correlative']) : '',
                    'date_issue' => $this->functions->clean_string($input['date_issue']),
                    'date_expiration' => isset($input['date_expiration']) ? $this->functions->clean_string($input['date_expiration']) : null,
                    'date_transfer' => isset($input['date_transfer']) ? $this->functions->clean_string($input['date_transfer']) : null,
                    'igv' => isset($input['igv']) ? $this->functions->clean_string($input['igv']) : 18.00,
                    'igv_total' => isset($input['igv_total']) ? $this->functions->clean_string($input['igv_total']) : 0.00,
                    'op_taxed' => isset($input['op_taxed']) ? $this->functions->clean_string($input['op_taxed']) : 0.00,
                    'op_unaffected' => isset($input['op_unaffected']) ? $this->functions->clean_string($input['op_unaffected']) : 0.00,
                    'op_exonerated' => isset($input['op_exonerated']) ? $this->functions->clean_string($input['op_exonerated']) : 0.00,
                    'op_free' => isset($input['op_free']) ? $this->functions->clean_string($input['op_free']) : 0.00,
                    'isc' => isset($input['isc']) ? $this->functions->clean_string($input['isc']) : 0.00,
                    'total_discount' => isset($input['total_discount']) ? $this->functions->clean_string($input['total_discount']) : 0.00,
                    'total_sale' => $this->functions->clean_string($input['total_sale']),
                    'legend' => isset($input['legend']) ? $this->functions->clean_string($input['legend']) : '',
                    'sustent' => isset($input['sustent']) ? $this->functions->clean_string($input['sustent']) : '',
                    'reference' => isset($input['reference']) ? $this->functions->clean_string($input['reference']) : '',
                    'validity' => isset($input['validity']) ? $this->functions->clean_string($input['validity']) : '',
                    'time_delivery' => isset($input['time_delivery']) ? $this->functions->clean_string($input['time_delivery']) : '',
                    'modality_transport' => isset($input['modality_transport']) ? $this->functions->clean_string($input['modality_transport']) : '',
                    'status' => isset($input['status']) ? $this->functions->clean_string($input['status']) : 2
                ];

                $products = [];
                foreach ($input['id_product'] as $key => $id_product) {
                    $products[] = [
                        'id_product' => $this->functions->clean_string($id_product),
                        'quantity' => $this->functions->clean_string($input['quantity'][$key]),
                        'price' => $this->functions->clean_string($input['price'][$key]),
                        'discount' => isset($input['discount'][$key]) ? $this->functions->clean_string($input['discount'][$key]) : 0.00,
                        'item' => $key + 1,
                        'series' => isset($input['serie'][$key]) ? $this->functions->clean_string($input['serie'][$key]) : '',
                        'status' => 1
                    ];
                }

                $obj = $this->load_model('Salenote_Details');
                $response = $obj->create_salenote($saleData, $products);

                if ($response['status'] === 'OK') {
                    $this->update_product_stock($products);
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Nota de venta registrada en el sistema con éxito.',
                        'data' => $response['data']
                    );
                } else {
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No fue posible registrar la nota de venta: ' . $response['message'],
                        'data' => array(),
                    );
                }
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se enviaron los campos necesarios, verificar.',
                    'data' => array()
                );
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
        ob_end_flush();
        exit;
    }

    private function update_product_stock($products)
    {
        $obj = $this->load_model('Salenote_Details');
        foreach ($products as $product) {
            $obj->update_stock($product['id_product'], $product['quantity']);
        }
    }

    public function get_salenote_details()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $id = $this->segment->get('id');
            if (!empty($id)) {
                $obj = $this->load_model('Salenote_Details');
                $response = $obj->get_salenote_details($id);

                if ($response['status'] === 'OK') {
                    $json = array(
                        'status' => 'OK',
                        'data' => $response['data']
                    );
                } else {
                    $json = array(
                        'status' => 'ERROR',
                        'msg' => 'No se encontraron datos de la nota de venta.'
                    );
                }
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'No se proporcionó un ID válido.'
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido.'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }

    public function list_salenotes()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $params = [
                'date_from' => $this->segment->get('date_from'),
                'date_to' => $this->segment->get('date_to'),
                'client' => $this->segment->get('client'),
                'voucher_type' => $this->segment->get('voucher_type'),
                'status' => $this->segment->get('status')
            ];

            $obj = $this->load_model('Salenote_Details');
            $response = $obj->list_salenotes($params);

            if ($response['status'] === 'OK') {
                $json = array(
                    'status' => 'OK',
                    'data' => $response['data']
                );
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'No se encontraron notas de venta.'
                );
            }
        } else {
            $json = array(
                'status' => 'ERROR',
                'msg' => 'Método no permitido.'
            );
        }

        header('Content-Type: application/json');
        echo json_encode($json);
    }
}