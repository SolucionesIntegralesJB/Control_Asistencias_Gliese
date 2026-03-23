<?php
// --
class C_Billingpersale_Details extends Controller
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

  public function create_billingpersale()
  {
    ob_start();
    $this->functions->validate_session($this->segment->get('isActive'));
    $request = $_SERVER['REQUEST_METHOD'];
    if ($request === 'POST') {
      $input = json_decode(file_get_contents('php://input'), true);
      if (empty($input)) {
        $input = filter_input_array(INPUT_POST);
      }
      if (
        !empty($input['business_name_cli']) &&
        !empty($input['fecha_emision']) &&
        !empty($input['fecha_vencimiento']) &&
        !empty($input['coins']) &&
        !empty($input['igv']) &&
        !empty($input['document_number_cli']) &&
        !empty($input['address_cli']) &&
        !empty($input['fp_description']) &&
        !empty($input['vt_description']) &&
        !empty($input['pt_description']) &&
        !empty($input['tv_gravado']) &&
        !empty($input['tv_exonerado']) &&
        !empty($input['tv_inafectas']) &&
        !empty($input['tv_gratuitas']) &&
        !empty($input['total_igv']) &&
        !empty($input['total_importe']) &&
        isset($input['id_product']) && is_array($input['id_product']) &&
        !empty($input['id_campus']) &&
        !empty($input['id_user'])
      ) {
        $billingData = [
          'business_name_cli' => $this->functions->clean_string($input['business_name_cli']),
          'fecha_emision' => $this->functions->clean_string($input['fecha_emision']),
          'fecha_vencimiento' => $this->functions->clean_string($input['fecha_vencimiento']),
          'coins' => $this->functions->clean_string($input['coins']),
          'igv' => $this->functions->clean_string($input['igv']),
          'document_number_cli' => $this->functions->clean_string($input['document_number_cli']),
          'address_cli' => $this->functions->clean_string($input['address_cli']),
          'fp_description' => $this->functions->clean_string($input['fp_description']),
          'vt_description' => $this->functions->clean_string($input['vt_description']),
          'pt_description' => $this->functions->clean_string($input['pt_description']),
          'tv_gravado' => $this->functions->clean_string($input['tv_gravado']),
          'tv_exonerado' => $this->functions->clean_string($input['tv_exonerado']),
          'tv_inafectas' => $this->functions->clean_string($input['tv_inafectas']),
          'tv_gratuitas' => $this->functions->clean_string($input['tv_gratuitas']),
          'total_igv' => $this->functions->clean_string($input['total_igv']),
          'total_importe' => $this->functions->clean_string($input['total_importe']),
          'id_user' => $this->functions->clean_string($input['id_user']),
          'id_campus' => $this->functions->clean_string($input['id_campus'])
        ];

        $products = [];
        foreach ($input['id_product'] as $key => $id_product) {
          $products[] = [
            'id_product' => $this->functions->clean_string($id_product),
            'code' => $this->functions->clean_string($input['code'][$key]),
            'name' => $this->functions->clean_string($input['name'][$key]),
            'serie' => $this->functions->clean_string($input['serie'][$key]),
            'u_medida' => $this->functions->clean_string($input['u_medida'][$key]),
            'cantidad' => $this->functions->clean_string($input['cantidad'][$key]),
            'price_u' => $this->functions->clean_string($input['price_u'][$key]),
            'tributo' => $this->functions->clean_string($input['tributo'][$key]),
            'impuesto' => $this->functions->clean_string($input['impuesto'][$key]),
            'venta_t' => $this->functions->clean_string($input['venta_t'][$key]),
            'id_campus' => $this->functions->clean_string($input['id_campus']) 
          ];
        }

        $obj = $this->load_model('Billingpersale_Details');
        $response = $obj->create_billingpersale($billingData, $products);

        if ($response['status'] === 'OK') {
          $this->update_product_stock($products);
          $json = array(
            'status' => 'OK',
            'type' => 'success',
            'msg' => 'Factura creada en el sistema con éxito.',
            'data' => array()
          );
        } else {
          $json = array(
            'status' => 'ERROR',
            'type' => 'warning',
            'msg' => 'No fue posible crear la factura: ' . $response['message'],
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
    $obj = $this->load_model('Billingpersale_Details');
    foreach ($products as $product) {
      if (isset($product['id_campus'])) { 
        $obj->update_stock($product['id_product'], $product['cantidad'], $product['id_campus']);
      } else {
        error_log("El producto con ID {$product['id_product']} no tiene 'id_campus' definido.");
      }
    }
  }
}
