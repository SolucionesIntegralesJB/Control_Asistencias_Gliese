<?php
class C_Bot_Events extends Controller {

    public function __construct() {
        parent::__construct();
    }

    // Página para gestionar eventos
    public function index() {
        $this->functions->check_permissions($this->segment->get('modules'), 'Bot_Chat');
        $this->view->set_js('bot_events');
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Bot_Chat'));
        // Load current settings and provide them to the view so the embedded modal shows DB values
        try {
            $m = $this->load_model('Bot_Event');
            // Preferently expose the admin/fixed setting row (id=2) to the view so the modal
            // clearly shows the exact setting that the backend will update.
            $preferred = null;
            try { $preferred = $m->get_setting(2); } catch (Exception $e) { $preferred = null; }
            if ($preferred && isset($preferred['id'])) {
                $this->view->set_data(array('bot_events_data' => array($preferred)));
            } else {
                $rows = $m->get_settings();
                if (is_array($rows)) {
                    $this->view->set_data(array('bot_events_data' => $rows));
                }
            }
        } catch (Exception $e) { /* ignore, view will render defaults */ }
        $this->view->set_view('Bot_Chat/events');
    }

    // Devuelve solo el fragmento HTML de la vista para cargar en modal (AJAX)
    public function modal() {
        $this->functions->validate_session($this->segment->get('isActive'));
        // Prevent caching of modal fragment so clients always fetch fresh HTML
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Content-Type: text/html; charset=utf-8');
        $viewFile = __DIR__ . '/../views/bot_chat/events.php';
        try {
            // load current settings so the view can render server-side values (safer when JS is stale)
            // Prefer the fixed admin setting (id=2) so the modal shows the exact row the backend will update.
            $bot_events_data = array();
            try {
                $m = $this->load_model('Bot_Event');
                $preferred = null;
                try { $preferred = $m->get_setting(2); } catch (Exception $e) { $preferred = null; }
                if ($preferred && isset($preferred['id'])) {
                    $bot_events_data = array($preferred);
                } else {
                    $bot_events_data = $m->get_settings();
                }
            } catch (Exception $e) { /* ignore, view will render defaults */ }

            ob_start();
            if (file_exists($viewFile)) {
                include $viewFile;
            } else {
                echo '<div class="p-3 text-danger">Vista no encontrada (file missing)</div>';
            }
            $out = ob_get_clean();
            // add a debug marker to help detect the response
            echo "<!-- BOT_EVENTS_MODAL_OK -->\n" . $out;
        } catch (Exception $e) {
            ob_end_clean();
            echo '<div class="p-3 text-danger">Error al renderizar la vista: ' . htmlentities($e->getMessage()) . '</div>';
        }
    }

    // Devuelve el JS del gestor de eventos para cargar dinámicamente
    public function js() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $jsFile = __DIR__ . '/../views/bot_chat/js/bot_events.js';
        if (file_exists($jsFile)) {
            // Prevent caching so clients pick the updated JS quickly
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Content-Type: application/javascript; charset=utf-8');
            readfile($jsFile);
        } else {
            header('HTTP/1.1 404 Not Found');
            echo "// JS not found";
        }
    }

    // Listar settings (AJAX)
    public function list() {
        // Para endpoints AJAX devolvemos JSON claro cuando la sesión expiró
        if (!$this->segment->get('isActive')) {
            header('Content-Type: application/json');
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo json_encode(array('success'=>false,'msg'=>'session_expired'));
            return;
        }
        $model = $this->load_model('Bot_Event');
        $rows = $model->get_settings();
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo json_encode(array('success'=>true,'data'=>$rows));
    }

    // Guardar setting (AJAX POST)
    public function save() {
        // TEMP DEBUG: log every incoming request to this endpoint at the very start
        try { @file_put_contents(__DIR__ . '/../logs/events_save_debug.log', date('c') . " - REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . " METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? '') . " RAW: " . file_get_contents('php://input') . "\n", FILE_APPEND); } catch(Exception $e) {}
    // NOTE: removed temporary debug bypass of validate_session — keep normal session validation
    // (the controller methods use $this->functions->validate_session elsewhere)
        // DEBUG: log headers and cookies to help diagnose session issues
        try {
            $hdrs = function_exists('getallheaders') ? getallheaders() : array();
            @file_put_contents(__DIR__ . '/../logs/events_save_debug.log', date('c') . " - HEADERS: " . json_encode($hdrs) . " COOKIES: " . json_encode($_COOKIE) . "\n", FILE_APPEND);
        } catch (Exception $e) {}
        // If session is not active, return JSON so clients (and tests) don't get back HTML login page
        if (!$this->segment->get('isActive')) {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'msg'=>'session_expired'));
            return;
        }

        $req = $_SERVER['REQUEST_METHOD'];
        if ($req !== 'POST') { header('Content-Type: application/json'); echo json_encode(array('success'=>false,'msg'=>'Método no permitido')); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) { 
            // write debug log for empty payload
            @file_put_contents(__DIR__ . '/../logs/events_save_debug.log', date('c') . " - empty payload\n" , FILE_APPEND);
            header('Content-Type: application/json'); echo json_encode(array('success'=>false,'msg'=>'Payload inválido')); return; }
        $model = $this->load_model('Bot_Event');
        // DEBUG: dump incoming payload to log for diagnosis (temporary)
        try { @file_put_contents(__DIR__ . '/../logs/events_save_debug.log', date('c') . " - incoming: " . json_encode($input) . "\n", FILE_APPEND); } catch(Exception $e) {}

        // Obtener id_user de la sesión correctamente
        $session_data = $this->segment->get('data');
        $input['updated_by'] = isset($session_data['id_user']) ? $session_data['id_user'] : null;

        $res = $model->save_setting($input);
        // DEBUG: log model response
        try { @file_put_contents(__DIR__ . '/../logs/events_save_debug.log', date('c') . " - model_res: " . json_encode($res) . "\n", FILE_APPEND); } catch(Exception $e) {}
        // If save was successful, return the updated row so client can sync immediately
        if (isset($res['status']) && $res['status'] === 'OK' && isset($res['id'])) {
            try {
                $row = $model->get_setting($res['id']);
                if ($row) $res['data'] = $row;
            } catch (Exception $e) { /* ignore */ }
        }
        header('Content-Type: application/json');
        // also add no-cache headers for AJAX responses
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        try { @file_put_contents(__DIR__ . '/../logs/events_save_debug.log', date('c') . " - response_sent: " . json_encode(
            is_array($res) ? $res : array('res'=>$res)
        ) . "\n", FILE_APPEND); } catch(Exception $e) {}
        echo json_encode($res);
    }

    // Forzar ejecución de un setting (AJAX POST)
    public function run() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $req = $_SERVER['REQUEST_METHOD'];
        if ($req !== 'POST') { header('Content-Type: application/json'); echo json_encode(array('success'=>false,'msg'=>'Método no permitido')); return; }
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['id'])) { header('Content-Type: application/json'); echo json_encode(array('success'=>false,'msg'=>'id requerido')); return; }
        $model = $this->load_model('Bot_Event');
        $res = $model->run_setting($input['id']);
        header('Content-Type: application/json');
        echo json_encode($res);
    }

    // Obtener logs (AJAX GET)
    public function logs() {
        // Para endpoints AJAX devolvemos JSON claro cuando la sesión expiró
        if (!$this->segment->get('isActive')) {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'msg'=>'session_expired'));
            return;
        }
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if (!$id) { header('Content-Type: application/json'); echo json_encode(array('success'=>false,'msg'=>'id requerido')); return; }
        $model = $this->load_model('Bot_Event');
        $rows = $model->get_logs($id);
        header('Content-Type: application/json'); echo json_encode(array('success'=>true,'data'=>$rows));
    }

    // Importar eventos desde MySQL -> crea settings en bot_event_settings
    public function import() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $m = $this->load_model('Bot_Event');
        $res = $m->import_mysql_events();
        header('Content-Type: application/json'); echo json_encode($res);
    }

    // TEMP: connectivity test endpoint — writes to debug log and returns JSON
    public function ping() {
        try { @file_put_contents(__DIR__ . '/../logs/events_save_debug.log', date('c') . " - PING reached\n", FILE_APPEND); } catch(Exception $e) {}
        header('Content-Type: application/json'); echo json_encode(array('status'=>'OK','msg'=>'ping'));
    }

    // Verify: quick endpoint to return current settings (first row) in JSON for client verification
    public function verify() {
        if (!$this->segment->get('isActive')) {
            header('Content-Type: application/json'); echo json_encode(array('success'=>false,'msg'=>'session_expired')); return;
        }
        $m = $this->load_model('Bot_Event');
        $rows = $m->get_settings();
        if ($rows && count($rows) > 0) {
            header('Content-Type: application/json'); echo json_encode(array('success'=>true,'data'=>$rows)); return;
        }
        header('Content-Type: application/json'); echo json_encode(array('success'=>true,'data'=>array()));
    }

}
