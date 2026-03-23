<?php 
// --
class C_Technicals extends Controller {

  // --
  public function __construct() {
   parent::__construct();
  }

  // --
  public function index() {
      // --
      $this->functions->validate_session($this->segment->get('isActive'));
      $this->functions->check_permissions($this->segment->get('modules'), 'Technicals');
      // --
      $this->view->set_js('index');       // -- Load JS
      $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Technicals')); // -- Active Menu
      $this->view->set_view('index');     // -- Load View
  }

  // --
  public function get_technicals() { 
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
        $obj = $this->load_model('Technicals');
        // --
        $response = $obj->get_technicals();
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

  // --
  public function get_technicals_by_id() {
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
        if (!empty($input['id_technicals'])) {
            // --
            $obj = $this->load_model('Technicals');
            // --
            $bind = array(
                'id_technicals' => intval($input['id_technicals']));
            // --
            $response = $obj->get_technicals_by_id($bind);
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
  public function create_technicals() {
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
          if (!empty($input['document_type']) &&
              !empty($input['name']) &&
              !empty($input['document_number']) &&
              !empty($input['phone']) &&
              !empty($input['area']) &&
              !empty($input['cargo']) &&
              !empty($input['technical_type']) &&
              !empty($input['description_document_type'])
          ) {
              // --
              $document_type = $this->functions->clean_string($input['document_type']);
              $name = $this->functions->clean_string(strtoupper(ucfirst($input['name'])));
              $document_number = $this->functions->clean_string($input['document_number']);
              $description_document_type = $this->functions->clean_string($input['description_document_type']);
              $phone = $this->functions->clean_string($input['phone']);
              $area = $this->functions->clean_string($input['area']);
              $cargo = $this->functions->clean_string($input['cargo']); 
              $technical_type = $this->functions->clean_string($input['technical_type']);
              // --
              $is_verified = $this->functions->verified_document_type($description_document_type, $document_number); // -- verified document type
              // --
              if ($is_verified) {
                  $bind = array(
                      'id_document_type' => $document_type,
                      'name' => $name,
                      'document_number' => $document_number,
                      'phone' => $phone,
                      'area' => $area,
                      'cargo' => $cargo,
                      'technical_type' => $technical_type
                  );
                  
                  // --
                  $obj = $this->load_model('Technicals');
                  $response = $obj->create_technicals($bind);
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
  public function update_technicals() {
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
        if (!empty($input['id_technicals']) && 
            !empty($input['document_type']) && 
            !empty($input['name']) &&
            !empty($input['document_number']) &&
            !empty($input['phone']) &&
            !empty($input['area'])&&
            !empty($input['cargo']) &&
            !empty($input['technical_type']) &&
            !empty($input['description_document_type'])
        ) {
            // --
            $id_technicals = $this->functions->clean_string($input['id_technicals']);
            $document_type = $this->functions->clean_string($input['document_type']);
            $name = $this->functions->clean_string(strtoupper(ucfirst($input['name'])));
            $document_number = $this->functions->clean_string($input['document_number']);
            $description_document_type = $this->functions->clean_string($input['description_document_type']);   
            $phone = $this->functions->clean_string($input['phone']);
            $area = $this->functions->clean_string($input['area']);
            $cargo = $this->functions->clean_string($input['cargo']);
            $technical_type = $this->functions->clean_string($input['technical_type']);
            // --
            $is_verified = $this->functions->verified_document_type($description_document_type, $document_number); // -- verified document type
            // --
            if ($is_verified) {
                // -- 
                $bind = array(
                    'id_technicals' => $id_technicals,
                    'id_document_type' => $document_type,
                    'name' => $name,
                    'document_number' => $document_number,
                    'phone' => $phone,
                    'area' => $area,
                    'cargo' => $cargo,
                    'technical_type' => $technical_type

                );
                // --
                $obj = $this->load_model('Technicals');
                $response = $obj->update_technicals($bind);
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
  public function delete_technicals() {
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
        if (!empty($input['id_technicals'])) {
            // --
            $id_technicals = $this->functions->clean_string($input['id_technicals']);
            // --
            $bind = array(
                'id_technicals' => $id_technicals
            );
            // --
            $obj = $this->load_model('Technicals');
            $response = $obj->delete_technicals($bind);
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

}