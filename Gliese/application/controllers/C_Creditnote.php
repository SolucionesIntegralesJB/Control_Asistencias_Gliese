<?php

class C_Creditnote extends Controller
{

  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $this->functions->validate_session($this->segment->get('isActive'));
    $this->functions->check_permissions($this->segment->get('modules'), 'Creditnote');

    $this->view->set_js('index');
    $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Creditnote'));
    $this->view->set_view('index');
  }

  public function get_creditnote()
  {
    $this->functions->validate_session($this->segment->get('isActive'));

    $request = $_SERVER['REQUEST_METHOD'];

    if ($request === 'GET') {
      $input = json_decode(file_get_contents('php://input'), true);
      if (empty($input)) {
        $input = filter_input_array(INPUT_GET);
      }

      $obj = $this->load_model('Creditnote');
      $response = $obj->get_creditnote();

      switch ($response['status']) {
        case 'OK':
          $json = array(
            'status' => 'OK',
            'type' => 'success',
            'msg' => 'Listado de registros encontrados.',
            'data' => $response['result']
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

  public function get_creditnote_by_id()
  {
    $this->functions->validate_session($this->segment->get('isActive'));

    $request = $_SERVER['REQUEST_METHOD'];

    if ($request === 'GET') {
      $input = json_decode(file_get_contents('php://input'), true);
      if (empty($input)) {
        $input = filter_input_array(INPUT_GET);
      }

      if (!empty($input['id'])) {
        $obj = $this->load_model('Creditnote');
        $bind = array(
          'id' => intval($input['id'])
        );
        $response = $obj->get_creditnote_by_id($bind);

        switch ($response['status']) {
          case 'OK':
            $json = array(
              'status' => 'OK',
              'type' => 'success',
              'msg' => 'Registro encontrado.',
              'data' => $response['result']
            );
            break;

          case 'ERROR':
            $json = array(
              'status' => 'ERROR',
              'type' => 'warning',
              'msg' => 'No se encontró el registro en el sistema.',
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
  }

  public function get_billingpersale()
  {
    $this->functions->validate_session($this->segment->get('isActive'));

    $request = $_SERVER['REQUEST_METHOD'];

    if ($request === 'GET') {
      $campus_id = $this->segment->get('current_campus_id');

      if (!$campus_id) {
        $json = array(
          'status' => 'ERROR',
          'type' => 'warning',
          'msg' => 'No se ha seleccionado una ubicación.',
          'data' => array()
        );
      } else {
        $obj = $this->load_model('Creditnote');
        $response = $obj->get_billingpersale($campus_id);

        switch ($response['status']) {
          case 'OK':
            $json = array(
              'status' => 'OK',
              'type' => 'success',
              'msg' => 'Listado de registros encontrados.',
              'data' => $response['result']
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

  public function get_billingpersale_by_id()
  {
    $input = $this->segment->get('id') ?? filter_input(INPUT_GET, 'id');

    if (!$input) {
      echo json_encode(['status' => 'ERROR', 'msg' => 'ID no proporcionado']);
      return;
    }

    $response = $this->load_model('Billingpersale')->get_billingpersale_by_id(['id' => $input]);

    header('Content-Type: application/json');
    echo json_encode($response);
  }

  public function update_billingersale()
  {
    $this->functions->validate_session($this->segment->get('isActive'));

    $request = $_SERVER['REQUEST_METHOD'];

    if ($request === 'POST') {
      $input = json_decode(file_get_contents('php://input'), true);
      if (empty($input)) {
        $input = filter_input_array(INPUT_POST);
      }

      if (!empty($input['id_billingpersale'])) {
        $obj = $this->load_model('Billingpersale');
        $bind = array(
          'id_billingpersale' => intval($input['id_billingpersale'])
        );
        $response = $obj->update_billingersale($bind);

        switch ($response['status']) {
          case 'OK':
            $json = array(
              'status' => 'OK',
              'type' => 'success',
              'msg' => 'Registro actualizado.',
              'data' => array()
            );
            break;

          case 'ERROR':
            $json = array(
              'status' => 'ERROR',
              'type' => 'warning',
              'msg' => 'No se encontró el registro en el sistema.',
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
  }

  public function save_creditnote_type()
  {
    $this->functions->validate_session($this->segment->get('isActive'));

    $request = $_SERVER['REQUEST_METHOD'];

    if ($request === 'POST') {
      $input = json_decode(file_get_contents('php://input'), true); // Decodifica el JSON enviado
      if (!empty($input['credit_note_type'])) {
        $_SESSION['credit_note_type'] = $input['credit_note_type']; // Guarda el tipo de nota en la sesión
        $json = array(
          'status' => 'OK',
          'type' => 'success',
          'msg' => 'Tipo de nota de crédito guardado correctamente.',
        );
      } else {
        $json = array(
          'status' => 'ERROR',
          'type' => 'error',
          'msg' => 'El tipo de nota de crédito no es válido.',
        );
      }
    } else {
      $json = array(
        'status' => 'ERROR',
        'type' => 'error',
        'msg' => 'Método no permitido.',
      );
    }

    header('Content-Type: application/json');
    echo json_encode($json);
  }

  public function save_billingpersale_update()
  {
    $this->functions->validate_session($this->segment->get('isActive'));

    $request = $_SERVER['REQUEST_METHOD'];

    if ($request === 'POST') {
      $data = json_decode(file_get_contents('php://input'), true);
      $obj = $this->load_model('Creditnote');
      $result = $obj->save_billingpersale_update($data);
      $json = $result;
    } else {
      $json = ['status' => 'ERROR', 'msg' => 'Método no permitido'];
    }

    header('Content-Type: application/json');
    echo json_encode($json);
  }
}
