<?php
class C_Bot_Dashboard extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function index()
    {
        $this->functions->check_permissions($this->segment->get('modules'), 'Bot_Dashboard');
        $this->view->set_js('index');
        $this->view->set_menu([
            'modules' => $this->segment->get('modules'),
            'view' => 'Bot_Dashboard',
        ]);
        $this->view->set_view('index');
    }

    // --
    public function load_partial()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            $partial = $_POST['partial'] ?? '';

            if (empty($partial)) {
                echo '<div class="alert alert-danger">Partial no especificado.</div>';
                return;
            }

            // Security: only allow alphanumeric and underscore
            if (!preg_match('/^[a-z_]+$/', $partial)) {
                echo '<div class="alert alert-danger">Nombre de partial inválido.</div>';
                return;
            }

            $partial_path = __DIR__ . '/../views/bot_dashboard/partials/' . $partial . '.php';

            if (file_exists($partial_path)) {
                include $partial_path;
            } else {
                echo '<div class="alert alert-warning">Contenido no disponible.</div>';
            }
        }
    }

    // ==================================================
    // RATE LIMITS
    // ==================================================

    // --
    public function get_rate_limit_stats()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $obj_dashboard = $this->load_model('Bot_Dashboard');
            $response = $obj_dashboard->get_rate_limit_stats();
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }

    // --
    public function get_rate_limits()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 100;
            $offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;
            $identifier = isset($_POST['identifier']) ? $_POST['identifier'] : null;
            $action_type = isset($_POST['action_type']) ? $_POST['action_type'] : null;
            $status = isset($_POST['status']) ? $_POST['status'] : null;
            $severity = isset($_POST['severity']) ? $_POST['severity'] : null;

            $obj_dashboard = $this->load_model('Bot_Dashboard');
            $response = $obj_dashboard->get_rate_limits($limit, $offset, $identifier, $action_type, $status, $severity);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }

    // --
    public function clear_rate_limit()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            header('Content-Type: application/json');

            $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

            if ($id <= 0) {
                echo json_encode([
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'ID invalido',
                ]);
                return;
            }

            $obj_dashboard = $this->load_model('Bot_Dashboard');
            $response = $obj_dashboard->clear_rate_limit($id);
            echo json_encode($response);
        }
    }

    // ==================================================
    // BLOCKED PHONES
    // ==================================================

    // --
    public function get_blocked_phones_stats()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $obj_dashboard = $this->load_model('Bot_Dashboard');
            $response = $obj_dashboard->get_blocked_phones_stats();
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }

    // --
    public function get_blocked_phones()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $limit = isset($_POST['limit']) ? (int) $_POST['limit'] : 100;
            $offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;
            $status = isset($_POST['status']) ? $_POST['status'] : null;
            $block_type = isset($_POST['block_type']) ? $_POST['block_type'] : null;

            $obj_dashboard = $this->load_model('Bot_Dashboard');
            $response = $obj_dashboard->get_blocked_phones($limit, $offset, $status, $block_type);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }

    // --
    public function unblock_phone()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            header('Content-Type: application/json');

            $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
            $block_type = isset($_POST['block_type']) ? $_POST['block_type'] : 'message';

            if (empty($phone)) {
                echo json_encode([
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Teléfono inválido',
                ]);
                return;
            }

            $obj_dashboard = $this->load_model('Bot_Dashboard');
            $response = $obj_dashboard->unblock_phone($phone, $block_type);
            echo json_encode($response);
        }
    }
}
