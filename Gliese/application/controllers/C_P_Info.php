<?php
/**
 * ============================================================================
 * CONTROLADOR: MI INFORMACIÓN (P_Info)
 * ============================================================================
 * 
 * PROPÓSITO:
 * Gestiona la visualización de la información personal del usuario logueado
 * según su rol (practicante o supervisor).
 * 
 * FUNCIONALIDAD:
 * - Practicantes: Ven su información completa (datos personales + tarea asignada)
 * - Supervisores: Ven su información laboral (datos personales + cargo/área)
 * - Administradores: No tienen acceso (ellos ven todo en otros módulos)
 * 
 * DEPENDENCIAS:
 * - Model: M_P_Info (consultas a tablas practicantes y supervisores)
 * - Tablas BD: practicantes, supervisores
 * - Sesión: Requiere id_user y role del usuario logueado
 * 
 * AUTOR: Sistema Gliese
 * FECHA: 2025
 * ============================================================================
 */
class C_P_Info extends Controller {

    /**
     * Constructor del controlador
     * Inicializa el controlador padre
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * ========================================================================
     * MÉTODO: index()
     * ========================================================================
     * 
     * PROPÓSITO:
     * Carga la vista principal del módulo "Mi Información"
     * 
     * VALIDACIONES:
     * 1. Valida que la sesión esté activa
     * 2. Verifica que el usuario tenga permisos para este módulo
     * 
     * PROCESO:
     * 1. Valida sesión activa
     * 2. Verifica permisos del módulo
     * 3. Carga el archivo JavaScript (index.js)
     * 4. Configura el menú lateral
     * 5. Renderiza la vista (index.php)
     * 
     * VISTA CARGADA:
     * - application/views/P_Info/index.php
     * 
     * JAVASCRIPT CARGADO:
     * - application/views/P_Info/js/index.js
     */
    public function index() {
        // Validar que la sesión esté activa
        $this->functions->validate_session($this->segment->get('isActive'));
        
        // Verificar que el usuario tenga permisos para este módulo
        $this->functions->check_permissions($this->segment->get('modules'), 'P_Info');
        
        // Cargar el archivo JavaScript del módulo
        $this->view->set_js('index');
        
        // Configurar el menú lateral con los módulos del usuario
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'P_Info'));
        
        // Renderizar la vista principal
        $this->view->set_view('index');
    }

    /**
     * ========================================================================
     * MÉTODO: get_my_info()
     * ========================================================================
     * 
     * PROPÓSITO:
     * Obtiene la información personal del usuario actual según su rol
     * (practicante o supervisor)
     * 
     * MÉTODO HTTP: GET
     * ENDPOINT: /P_Info/get_my_info
     * 
     * PROCESO:
     * 1. Valida que la sesión esté activa
     * 2. Obtiene id_user y role de la sesión
     * 3. Detecta automáticamente el tipo de usuario:
     *    - Si role contiene "practicante" → llama a get_practicante_info()
     *    - Si role contiene "supervisor" → llama a get_supervisor_info()
     *    - Si es otro rol (admin) → retorna error
     * 4. Retorna los datos en formato JSON
     * 
     * RESPUESTA JSON:
     * {
     *   "status": "OK|ERROR",
     *   "type": "success|warning|error",
     *   "msg": "Mensaje descriptivo",
     *   "data": {objeto con la información del usuario}
     * }
     * 
     * DATOS PRACTICANTE:
     * - nombre_completo, documento, tipo_documento, email, telefono
     * - institucion, especialidad, area_interes
     * - fecha_inicio, fecha_termino, fecha_nacimiento (formateadas)
     * - ciclo, modalidad, tarea_asignada
     * 
     * DATOS SUPERVISOR:
     * - nombre_completo, documento, tipo_documento, email, telefono
     * - cargo, area_supervision
     * - fecha_ingreso (formateada)
     * 
     * CASOS DE ERROR:
     * - Usuario no identificado (sin id_user en sesión)
     * - Rol no permitido (administradores u otros)
     * - Usuario no encontrado en la base de datos
     * - Error en la consulta SQL
     */
    public function get_my_info() {
        // Validar que la sesión esté activa
        $this->functions->validate_session($this->segment->get('isActive'));
        
        // Obtener el método HTTP de la petición
        $request = $_SERVER['REQUEST_METHOD'];

        // Solo permitir peticiones GET
        if ($request === 'GET') {
            // Cargar el modelo P_Info para acceder a las consultas
            $obj = $this->load_model('P_Info');
            
            // === PASO 1: Obtener datos de la sesión ===
            $sessionData = $this->segment->get('data');
            $id_user = isset($sessionData['id_user']) ? $sessionData['id_user'] : null;
            $role = isset($sessionData['role']) ? strtolower($sessionData['role']) : '';

            // Validar que el id_user exista en la sesión
            if (!$id_user) {
                $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'No se pudo identificar el usuario.');
                header('Content-Type: application/json');
                echo json_encode($json);
                return;
            }

            // === PASO 2: Detectar tipo de usuario y obtener su información ===
            // Verificar si el role contiene la palabra "practicante"
            if (strpos($role, 'practicante') !== false) {
                // Es un practicante: obtener su información de la tabla practicantes
                $response = $obj->get_practicante_info($id_user);

            } else if (strpos($role, 'supervisor') !== false) {
                // Es un supervisor: obtener su información de la tabla supervisores
                $response = $obj->get_supervisor_info($id_user);

            } else {
                // Es administrador u otro rol: devolver datos básicos con id_role para renderizar tareas
                $id_role = isset($sessionData['id_role']) ? $sessionData['id_role'] : null;
                $json = array(
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Administrador - Vista de gestión de tareas.',
                    'data' => array(
                        'id_role' => $id_role,
                        'id_user' => $id_user,
                        'role' => $role
                    )
                );
                header('Content-Type: application/json');
                echo json_encode($json);
                return;
            }

            // === PASO 3: Procesar respuesta del modelo ===
            switch ($response['status']) {
                case 'OK':
                    // Éxito: se encontró la información del usuario
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Información obtenida correctamente.',
                        'data' => $response['result']
                    );
                    break;
                    
                case 'ERROR':
                    // No se encontró el usuario en la base de datos
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => $response['msg'] ?? 'No se encontró información.',
                        'data' => array()
                    );
                    break;
                    
                case 'EXCEPTION':
                    // Error en la consulta SQL o excepción del sistema
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => $response['msg'] ?? 'Error interno',
                        'data' => array()
                    );
                    break;
            }
        } else {
            // Método HTTP no permitido (solo se acepta GET)
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }

        // === PASO 4: Enviar respuesta JSON ===
        header('Content-Type: application/json');
        echo json_encode($json);
    }
}
