<?php
// --
class C_Creditnote_Details extends Controller
{

  // --
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

  public function get_creditnote_type()
  {
    $this->functions->validate_session($this->segment->get('isActive'));

    if (!isset($_SESSION['credit_note_type'])) {
      $json = [
        'status' => 'WARNING', // Cambiado a WARNING para diferenciarlo de un error real
        'msg' => 'No se ha seleccionado un tipo de nota.',
        'data' => ['credit_note_type' => '[No definido]']
      ];
    } else {
      $json = [
        'status' => 'OK',
        'msg' => 'Tipo de nota obtenido correctamente.',
        'data' => ['credit_note_type' => $_SESSION['credit_note_type']]
      ];
    }

    header('Content-Type: application/json');
    echo json_encode($json);
  }

  public function get_billingpersale_by_id()
  {
    $this->functions->validate_session($this->segment->get('isActive'));

    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) {
      echo json_encode(['status' => 'ERROR', 'msg' => 'ID no proporcionado']);
      return;
    }

    $model = $this->load_model('M_Creditnote');
    $response = $model->get_billingpersale_by_id(['id' => $id]);

    header('Content-Type: application/json');
    echo json_encode($response);
  }
}
