<?php
class C_Proforma_Details extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'Proforma');
        $this->view->set_js('index'); // Load JS
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Proforma'));
        $this->view->set_view('index'); // Load View
    }

    public function create_proforma()
{
    ob_start();
    $this->functions->validate_session($this->segment->get('isActive'));
    $request = $_SERVER['REQUEST_METHOD'];

    if ($request === 'POST') {
        $input = filter_input_array(INPUT_POST);

        // Validación de campos
        if ($this->validateInput($input)) {
            // Preparar los datos
            $billingData = $this->prepareBillingData($input);
            $productsData = $this->prepareProductData($input); // Obtener los datos de los productos

            $obj = $this->load_model('Proforma_Detail');
            $response = $obj->create_proforma($billingData, $productsData); // Inserta la proforma y los detalles

            if ($response['status'] === 'OK') {
                $json = array(
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Proforma creada en el sistema con éxito.',
                    'data' => array('proforma_id' => $response['proforma_id'])
                );
            } else {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No fue posible crear la proforma: ' . $response['message'],
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

    private function validateInput($input)
    {
        return !empty($input['business_name_cli']) &&
            !empty($input['date_issue']) &&
            !empty($input['coins']) &&
            !empty($input['igv']) &&
            !empty($input['delivery_time']) &&
            !empty($input['offer_validity']) &&
            !empty($input['total_importe']) &&
            isset($input['id_product']) &&
            is_array($input['id_product']) &&
            !empty($input['id_campus']) &&
            !empty($input['id_user']);
    }
    private function prepareBillingData($input)
    {
        return [
            'id_clients' => $this->functions->clean_string($input['business_name_cli']),
            'id_user' => $this->functions->clean_string($input['id_user']),
            'id_voucher_type' => 10, // Asegúrate de que este valor sea correcto
            'igv' => $this->functions->clean_string($input['igv']),
            'igv_total' => $this->functions->clean_string($input['total_igv']), // Asegúrate de que este campo esté presente
            'date_issue' => $this->functions->clean_string($input['date_issue']),
            'reference' => $this->functions->clean_string($input['reference']), 
            'total_sale' => $this->functions->clean_string($input['total_importe']),
            'delivery_time' => $this->functions->clean_string($input['delivery_time']),
            'offer_validity' => $this->functions->clean_string($input['offer_validity']),
            'products' => [] // Inicializa como vacío, puedes agregar productos más tarde
        ];
    }
    private function prepareProductData($input)
{
    $products = [];
    foreach ($input['id_product'] as $key => $id_product) {
        $products[] = [
            'id_products' => $this->functions->clean_string($id_product),
            'amount' => $this->functions->clean_string($input['cantidad'][$key]),
            'series' => $this->functions->clean_string($input['serie'][$key]),
            'price_sale' => $this->functions->clean_string($input['price_u'][$key]),
        ];
    }
    return $products;
}
}
