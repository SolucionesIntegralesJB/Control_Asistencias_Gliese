<?php
// --
class C_S_Statistics extends Controller
{
    // --
    public function __construct()
    {
        parent::__construct();
        // --- Session bridge for S_Statistics ---
        if (!isset($_SESSION['id_user'])) {
            $segment_data = $this->segment->get('data');
            if (is_array($segment_data) && isset($segment_data['id_user'])) {
                $_SESSION['id_user'] = $segment_data['id_user'];
            }
        }
    }

    // --
    public function index()
    {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'S_Statistics');

        // Load model
        $obj = $this->load_model('Work_Session');
        $grupos = $obj->getGroups();

        // Pass data to view
        $this->view->set_data([
            'title' => 'Estadísticas de Jornadas Laborales',
            'grupos' => $grupos
        ]);
        $this->view->set_js('index');
        $this->view->set_menu(['modules' => $this->segment->get('modules'), 'view' => 'S_Statistics']);
        $this->view->set_view('index');
    }

    /**
     * Obtener estadísticas generales
     */
    public function getGeneralStats()
    {
        $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
        $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
        $id_grupo = isset($_GET['id_grupo']) ? $_GET['id_grupo'] : null;

        $obj = $this->load_model('Work_Session');
        $stats = $obj->getGeneralStatistics($fecha_inicio, $fecha_fin, $id_grupo);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }

    /**
     * Obtener estadísticas por practicante
     */
    public function getPracticanteStats()
    {
        $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
        $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
        $id_grupo = isset($_GET['id_grupo']) ? $_GET['id_grupo'] : null;

        $obj = $this->load_model('Work_Session');
        $stats = $obj->getPracticanteStatistics($fecha_inicio, $fecha_fin, $id_grupo);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }

    /**
     * Obtener practicantes activos en este momento
     */
    public function getActiveNow()
    {
        $obj = $this->load_model('Work_Session');
        $active = $obj->getActiveSessions();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $active]);
        exit;
    }

    /**
     * Obtener detalle de jornadas de un practicante
     */
    public function getDetailByUser()
    {
        $id_user = isset($_GET['id_user']) ? $_GET['id_user'] : null;
        $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
        $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

        $obj = $this->load_model('Work_Session');
        $sessions = $obj->getSessionsByUser($id_user, $fecha_inicio, $fecha_fin);
        $overtime = $obj->getOvertimeByUser($id_user, $fecha_inicio, $fecha_fin);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'sessions' => $sessions,
            'overtime' => $overtime
        ]);
        exit;
    }

    /**
     * Exportar estadísticas a Excel
     */
    public function exportExcel()
    {
        $fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
        $fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;
        $id_grupo = isset($_GET['id_grupo']) ? $_GET['id_grupo'] : null;

        $obj = $this->load_model('Work_Session');
        $stats = $obj->getPracticanteStatistics($fecha_inicio, $fecha_fin, $id_grupo);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $stats]);
        exit;
    }

    /**
     * DEBUG: Verificar tablas y datos
     */
    public function debug()
    {
        header('Content-Type: application/json');

        try {
            $obj = $this->load_model('Work_Session');
            $debug_data = $obj->debugTables();

            echo json_encode($debug_data, JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
}
