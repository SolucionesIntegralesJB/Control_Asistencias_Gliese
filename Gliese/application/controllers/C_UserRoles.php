<?php 
// --
class C_UserRoles extends Controller {

    // --
    public function __construct() {
        parent::__construct();
    }
    
    // --
    public function index() {
        // --
        $this->functions->validate_session($this->segment->get('isActive'));
        $this->functions->check_permissions($this->segment->get('modules'), 'UserRoles');
        // --
        $this->view->set_js('index');
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'UserRoles'));
        $this->view->set_view('index');
    }

    // --
    public function get_user_roles() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_GET);
            }
            $obj = $this->load_model('UserRoles');
            $response = $obj->get_user_roles();

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
                    $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se encontraron registros en el sistema.');
                    break;
                case 'EXCEPTION':
                    $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage());
                    break;
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function create_user_role() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = filter_input_array(INPUT_POST);

            if (!empty($input['description'])) {
                $description = $this->functions->clean_string(strtoupper($input['description']));
                $bind = array('description' => $description);
                $obj = $this->load_model('UserRoles');
                $response = $obj->create_user_role($bind);

                if ($response['status'] === 'OK') {
                    // Obtener el ID del rol recién creado desde la respuesta del modelo
                    $id_user_role = $response['result']['id'];

                    // Procesar permisos si vienen
                    if (!empty($input['permission'])) {
                        $permissions = json_decode($input['permission'], true);
                        if (is_array($permissions)) {
                            foreach ($permissions as $permission) {
                                if ($permission['status'] == 1) {
                                    $bind_perm = array(
                                        'id_user_role' => $id_user_role,
                                        'id_sub_menu' => $permission['id_sub_menu'],
                                        'status' => 1
                                    );
                                    $obj->insert_permission($bind_perm);
                                }
                            }
                        }
                    }
                }

                switch ($response['status']) {
                    case 'OK':
                        $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registro almacenado en el sistema con éxito.');
                        break;
                    case 'ERROR':
                        $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No fue posible guardar el registro ingresado, verificar.');
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage());
                        break;
                }

            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se enviaron los campos necesarios, verificar.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function update_user_role() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = filter_input_array(INPUT_POST);

            if (!empty($input['id_user_role']) && !empty($input['description'])) {
                $id_user_role = $this->functions->clean_string($input['id_user_role']);
                $description = $this->functions->clean_string(strtoupper($input['description']));
                $bind = array('id_user_role' => $id_user_role, 'description' => $description);
                $obj = $this->load_model('UserRoles');
                $response = $obj->update_user_role($bind);

                if ($response['status'] === 'OK') {
                    // Eliminar permisos anteriores
                    $obj->delete_all_permissions($id_user_role);

                    // Procesar nuevos permisos si vienen
                    if (!empty($input['permission'])) {
                        $permissions = json_decode($input['permission'], true);
                        if (is_array($permissions)) {
                            foreach ($permissions as $permission) {
                                if ($permission['status'] == 1) {
                                    $bind_perm = array(
                                        'id_user_role' => $id_user_role,
                                        'id_sub_menu' => $permission['id_sub_menu'],
                                        'status' => 1
                                    );
                                    $obj->insert_permission($bind_perm);
                                }
                            }
                        }
                    }
                }

                switch ($response['status']) {
                    case 'OK':
                        $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registro actualizado en el sistema con éxito.');
                        break;
                    case 'ERROR':
                        $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No fue posible actualizar el registro ingresado, verificar.');
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage());
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se enviaron los campos necesarios, verificar.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // --
    public function delete_user_role() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }
            if (!empty($input['id_user_role'])) {
                $id_user_role = $this->functions->clean_string($input['id_user_role']);
                $bind = array('id_user_role' => $id_user_role);
                $obj = $this->load_model('UserRoles');
                $response = $obj->delete_user_role($bind);

                switch ($response['status']) {
                    case 'OK':
                        $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Registro eliminado del sistema con éxito.');
                        break;
                    case 'ERROR':
                        $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No fue posible eliminar el registro, verificar.');
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage());
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se enviaron los campos necesarios, verificar.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // ============================================
    // MÉTODOS PARA GESTIÓN DE PERMISOS
    // ============================================

    // -- Obtener solo los menús que están en permission_user_roles
    public function get_menu() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $obj = $this->load_model('Menu');
            $response = $obj->get_menu_for_user_roles();

            switch ($response['status']) {
                case 'OK':
                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Listado de menús obtenido.',
                        'data' => $response['result']
                    );
                    break;
                case 'ERROR':
                    $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'No se encontraron menús.');
                    break;
                case 'EXCEPTION':
                    $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage());
                    break;
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // -- Obtener permisos de un user_role específico
    public function get_permissions_by_user_role() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $input = filter_input_array(INPUT_GET);
            if (!empty($input['id_user_role'])) {
                $bind = array('id_user_role' => $input['id_user_role']);
                $obj = $this->load_model('UserRoles');
                $response = $obj->get_permissions_by_user_role($bind);

                switch ($response['status']) {
                    case 'OK':
                        $json = array(
                            'status' => 'OK',
                            'type' => 'success',
                            'msg' => 'Permisos obtenidos.',
                            'data' => $response['result']
                        );
                        break;
                    case 'ERROR':
                        $json = array('status' => 'OK', 'type' => 'info', 'msg' => 'No hay permisos asignados.', 'data' => array());
                        break;
                    case 'EXCEPTION':
                        $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $response['result']->getMessage());
                        break;
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Falta el parámetro id_user_role.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // -- Actualizar permisos de un user_role
    public function update_permissions() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!empty($input['id_user_role']) && isset($input['permissions'])) {
                $id_user_role = $this->functions->clean_string($input['id_user_role']);
                $permissions = $input['permissions']; // Array de id_sub_menu

                $obj = $this->load_model('UserRoles');

                // Primero eliminar todos los permisos del rol
                $obj->delete_all_permissions($id_user_role);

                // Luego insertar los nuevos permisos
                if (!empty($permissions)) {
                    foreach ($permissions as $id_sub_menu) {
                        $bind = array(
                            'id_user_role' => $id_user_role,
                            'id_sub_menu' => $id_sub_menu,
                            'status' => 1
                        );
                        $obj->insert_permission($bind);
                    }
                }

                $json = array('status' => 'OK', 'type' => 'success', 'msg' => 'Permisos actualizados correctamente.');
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Faltan parámetros necesarios.');
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // ============================================
    // MÉTODOS PARA CARGAR PARTIALS (TABS)
    // ============================================

    public function load_partial() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

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

            $partial_path = __DIR__ . '/../views/userroles/partials/' . $partial . '.php';

            if (file_exists($partial_path)) {
                include $partial_path;
            } else {
                echo '<div class="alert alert-warning">Contenido no disponible.</div>';
            }
        }
    }

    // ============================================
    // MÉTODOS PARA 2FA
    // ============================================

    // Obtener estado de 2FA GLOBAL del sistema
    public function get_2fa_status() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_2fa = $this->load_model('TwoFactor');
            $response = $obj_2fa->get_2fa_config(); // Sin parámetros, es GLOBAL

            // Normalize exception responses for the frontend and log details
            if (isset($response['status']) && $response['status'] === 'EXCEPTION') {
                $err = $response['result'];
                $msg = (is_object($err) && method_exists($err, 'getMessage')) ? $err->getMessage() : (is_string($err) ? $err : json_encode($err));
                // Log full exception for debugging (try file + error_log fallback)
                $root = realpath(__DIR__ . '/../../');
                $logfile = $root !== false ? $root . DIRECTORY_SEPARATOR . 'tmp_2fa_log.txt' : __DIR__ . '/tmp_2fa_log.txt';
                $logEntry = date('c') . " - enable_2fa EXCEPTION:\n" . print_r($response, true) . "\n";
                @file_put_contents($logfile, $logEntry, FILE_APPEND);
                // Also write to PHP error log so we can see it in server logs
                error_log('enable_2fa EXCEPTION: ' . $msg . ' -- details: ' . print_r($response, true));

                $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => $msg);
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode($json);
            } else {
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode($response);
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // Activar 2FA GLOBAL para TODO el sistema
    public function enable_2fa() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            try {
                $obj_2fa = $this->load_model('TwoFactor');

                // Activar 2FA GLOBAL (solo el switch ON/OFF)
                // Primero comprobar estado actual para hacerlo idempotente
                $current = $obj_2fa->get_2fa_config();
                if (isset($current['status']) && $current['status'] === 'OK' && !empty($current['result']['is_enabled'])) {
                    // Ya está activado globalmente
                    $response = array('status' => 'OK', 'type' => 'info', 'msg' => '2FA ya está activado en el sistema.');
                } else {
                    $response = $obj_2fa->enable_2fa();
                }

                // Limpiar cualquier output previo (warnings, espacios, etc.)
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode($response);
            } catch (Exception $e) {
                error_log('enable_2fa EXCEPTION: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
                $response = array(
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al activar 2FA: ' . $e->getMessage()
                );
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode($response);
            }
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // Desactivar 2FA GLOBAL para TODO el sistema
    public function disable_2fa() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $obj_2fa = $this->load_model('TwoFactor');
            // idempotent: if already disabled, return OK
            $current = $obj_2fa->get_2fa_config();
            if (isset($current['status']) && $current['status'] === 'OK' && empty($current['result']['is_enabled'])) {
                $response = array('status' => 'OK', 'type' => 'info', 'msg' => '2FA ya está desactivado en el sistema.');
                error_log('disable_2fa: request while already disabled');
            } else {
                $response = $obj_2fa->disable_2fa(); // Sin parámetros, es GLOBAL
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // Obtener configuración de tokens para practicantes
    public function get_tokens_config() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_2fa = $this->load_model('TwoFactor');
            $config = $obj_2fa->get_2fa_config();

            if ($config['status'] === 'OK' && isset($config['result'])) {
                $response = array(
                    'status' => 'OK',
                    'result' => array(
                        'backup_codes_quantity' => $config['result']['backup_codes_quantity'] ?? 10
                    )
                );
            } else {
                $response = array('status' => 'ERROR', 'msg' => 'No se pudo obtener la configuración');
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // Guardar configuración de tokens para practicantes
    public function save_tokens_config() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $quantity = isset($_POST['backup_codes_quantity']) ? intval($_POST['backup_codes_quantity']) : 10;

            if ($quantity < 2 || $quantity > 20) {
                $response = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'La cantidad debe estar entre 2 y 20');
            } else {
                $obj_2fa = $this->load_model('TwoFactor');
                $response = $obj_2fa->update_backup_codes_quantity(array('backup_codes_quantity' => $quantity));
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // Obtener códigos de emergencia
    public function get_backup_codes() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $dataUser = $this->segment->get('data');
            $id_user = $dataUser['id_user'];

            $obj_2fa = $this->load_model('TwoFactor');
            $response = $obj_2fa->get_backup_codes(array('id_user' => $id_user));

            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // Regenerar códigos de emergencia
    public function regenerate_backup_codes() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $dataUser = $this->segment->get('data');
            $id_user = $dataUser['id_user'];

            $obj_2fa = $this->load_model('TwoFactor');

            // Eliminar códigos viejos
            $obj_2fa->delete_backup_codes(array('id_user' => $id_user));

            // Generar nuevos
            $backup_codes = $obj_2fa->generate_backup_codes(array('id_user' => $id_user));

            $json = array(
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Códigos regenerados exitosamente.',
                'backup_codes' => $backup_codes
            );

            header('Content-Type: application/json');
            echo json_encode($json);
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // Cambiar contraseña
    public function change_password() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = filter_input_array(INPUT_POST);
            }

            if (!empty($input['current_password']) && !empty($input['new_password'])) {
                $dataUser = $this->segment->get('data');
                $id_user = $dataUser['id_user'];

                $current_password = $this->functions->encrypt_password($input['current_password']);
                $new_password = $this->functions->encrypt_password($input['new_password']);

                $obj_login = $this->load_model('Login');

                // Verificar contraseña actual
                $verify_bind = array('id_user' => $id_user, 'password' => $current_password);
                $verify_response = $obj_login->verify_password($verify_bind);

                if ($verify_response['status'] === 'OK') {
                    // Actualizar contraseña
                    $update_bind = array('id_user' => $id_user, 'password' => $new_password);
                    $update_response = $obj_login->update_password($update_bind);

                    $json = $update_response;
                } else {
                    $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'La contraseña actual es incorrecta.');
                }
            } else {
                $json = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'Faltan parámetros necesarios.');
            }

            header('Content-Type: application/json');
            echo json_encode($json);
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ============================================
    // MÉTODOS PARA TIEMPO DE EXPIRACIÓN 2FA
    // ============================================

    // Obtener tiempo de expiración de códigos 2FA
    public function get_code_expiration_time() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_2fa = $this->load_model('TwoFactor');
            $response = $obj_2fa->get_code_expiration_time();

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // Guardar tiempo de expiración de códigos 2FA
    public function save_code_expiration_time() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $minutes = isset($_POST['expiration_minutes']) ? intval($_POST['expiration_minutes']) : 5;

            if ($minutes < 3 || $minutes > 15) {
                $response = array('status' => 'ERROR', 'type' => 'warning', 'msg' => 'El tiempo debe estar entre 3 y 15 minutos');
            } else {
                $obj_2fa = $this->load_model('TwoFactor');
                $response = $obj_2fa->save_code_expiration_time(array('expiration_minutes' => $minutes));
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = array('status' => 'ERROR', 'type' => 'error', 'msg' => 'Método no permitido.');
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // =====================================================
    // RATE LIMITING ENDPOINTS
    // =====================================================

    // Helper method to get PDO instance
    private function get_pdo() {
        return new Aura\Sql\ExtendedPdo(
            'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME.';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'")
        );
    }

    public function get_rate_limit_config() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            try {
                $pdo = $this->get_pdo();

                // Leer configuración de student_config
                $sql = 'SELECT `key`, `value` FROM student_config WHERE `key` LIKE "login_%"';
                $result = $pdo->fetchAll($sql);

                $config = array();
                foreach ($result as $row) {
                    $key = str_replace('login_', '', $row['key']);
                    $config[$key] = $row['value'];
                }

                $json = array(
                    'status' => 'OK',
                    'result' => $config
                );
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Error al obtener configuración: ' . $e->getMessage()
                );
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    public function save_rate_limit_config() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            try {
                $pdo = $this->get_pdo();

                $maxAttemptsUser = intval($_POST['max_attempts_per_user']);
                $userLockoutDuration = intval($_POST['user_lockout_duration']);
                $maxAttemptsIP = intval($_POST['max_attempts_per_ip']);
                $ipLockoutDuration = intval($_POST['ip_lockout_duration']);
                $sleepDelay = isset($_POST['sleep_delay']) ? intval($_POST['sleep_delay']) : 2;

                error_log("Guardando configuración Rate Limit: max_attempts_per_user=$maxAttemptsUser, user_lockout_duration=$userLockoutDuration, max_attempts_per_ip=$maxAttemptsIP, ip_lockout_duration=$ipLockoutDuration, sleep_delay=$sleepDelay");

                // Actualizar en student_config usando prepare/execute
                $sql = 'UPDATE student_config SET value = ? WHERE `key` = ?';

                $stmt = $pdo->prepare($sql);
                $stmt->execute(array($maxAttemptsUser, 'login_max_attempts_per_user'));

                $stmt = $pdo->prepare($sql);
                $stmt->execute(array($userLockoutDuration, 'login_user_lockout_duration'));

                $stmt = $pdo->prepare($sql);
                $stmt->execute(array($maxAttemptsIP, 'login_max_attempts_per_ip'));

                $stmt = $pdo->prepare($sql);
                $stmt->execute(array($ipLockoutDuration, 'login_ip_lockout_duration'));

                // Sleep delay - INSERT si no existe, UPDATE si existe
                $sqlCheck = 'SELECT COUNT(*) as count FROM student_config WHERE `key` = ?';
                $stmtCheck = $pdo->prepare($sqlCheck);
                $stmtCheck->execute(array('login_sleep_delay'));
                $exists = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($exists['count'] > 0) {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute(array($sleepDelay, 'login_sleep_delay'));
                } else {
                    $sqlInsert = 'INSERT INTO student_config (`key`, `value`) VALUES (?, ?)';
                    $stmtInsert = $pdo->prepare($sqlInsert);
                    $stmtInsert->execute(array('login_sleep_delay', $sleepDelay));
                }

                error_log("Configuración Rate Limit guardada exitosamente");

                $json = array(
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Configuración guardada correctamente'
                );
            } catch (Exception $e) {
                error_log("ERROR al guardar configuración Rate Limit: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());

                $json = array(
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al guardar configuración: ' . $e->getMessage()
                );
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    public function get_rate_limit_stats() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            try {
                $pdo = $this->get_pdo();

                // IPs bloqueadas
                $sql = 'SELECT COUNT(*) as count FROM ip_blacklist WHERE blocked_until > NOW() OR is_permanent = 1';
                $resultIPs = $pdo->fetchOne($sql);
                $blockedIPs = $resultIPs['count'] ?? 0;

                // Cuentas atacadas (con intentos fallidos en las últimas 24h)
                $sql = 'SELECT COUNT(DISTINCT username) as count FROM login_attempts WHERE success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)';
                $resultAccounts = $pdo->fetchOne($sql);
                $attackedAccounts = $resultAccounts['count'] ?? 0;

                // Intentos fallidos últimas 24 horas
                $sql = 'SELECT COUNT(*) as count FROM login_attempts WHERE success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)';
                $resultAttempts = $pdo->fetchOne($sql);
                $failedAttempts = $resultAttempts['count'] ?? 0;

                // Intentos exitosos últimas 24 horas
                $sql = 'SELECT COUNT(*) as count FROM login_attempts WHERE success = 1 AND attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)';
                $resultSuccess = $pdo->fetchOne($sql);
                $successLogins = $resultSuccess['count'] ?? 0;

                $json = array(
                    'status' => 'OK',
                    'result' => array(
                        'blocked_ips' => $blockedIPs,
                        'attacked_accounts' => $attackedAccounts,
                        'failed_attempts' => $failedAttempts,
                        'success_logins' => $successLogins
                    )
                );
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Error al obtener estadísticas'
                );
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // =====================================================
    // REPORTS ENDPOINTS
    // =====================================================

    public function get_blocked_ips() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            try {
                $pdo = $this->get_pdo();
                $sql = 'SELECT *,
                        TIMESTAMPDIFF(SECOND, NOW(), blocked_until) as remaining_seconds
                        FROM ip_blacklist
                        WHERE blocked_until > NOW() OR is_permanent = 1
                        ORDER BY blocked_at DESC';

                $result = $pdo->fetchAll($sql);

                $json = array(
                    'status' => 'OK',
                    'result' => $result
                );
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Error al obtener IPs bloqueadas'
                );
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    public function get_attacked_accounts() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            try {
                $pdo = $this->get_pdo();

                // Obtener cuentas que recibieron intentos fallidos en las últimas 24h
                $sql = 'SELECT
                            la.username,
                            COUNT(*) as total_attempts,
                            GROUP_CONCAT(DISTINCT la.ip_address SEPARATOR ", ") as attacker_ips,
                            MIN(la.attempt_time) as first_attempt,
                            MAX(la.attempt_time) as last_attempt
                        FROM login_attempts la
                        WHERE la.success = 0
                        AND la.attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                        GROUP BY la.username
                        ORDER BY total_attempts DESC';

                $accounts = $pdo->fetchAll($sql);

                // Para cada cuenta, verificar si las IPs atacantes están bloqueadas
                foreach ($accounts as &$account) {
                    $ips = array_map('trim', explode(', ', $account['attacker_ips']));
                    $blocked_ips = array();
                    foreach ($ips as $ip) {
                        $sql_check = 'SELECT COUNT(*) as count FROM ip_blacklist
                                      WHERE ip_address = :ip
                                      AND (blocked_until > NOW() OR is_permanent = 1)';
                        $result = $pdo->fetchOne($sql_check, array('ip' => $ip));
                        if ($result['count'] > 0) {
                            $blocked_ips[] = $ip;
                        }
                    }
                    $account['blocked_ips'] = $blocked_ips;
                    $account['all_ips_blocked'] = count($blocked_ips) === count($ips);
                }

                $json = array(
                    'status' => 'OK',
                    'result' => $accounts
                );
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Error al obtener cuentas atacadas: ' . $e->getMessage()
                );
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    public function get_login_attempts() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            try {
                $pdo = $this->get_pdo();
                $filter = isset($_GET['filter']) ? $_GET['filter'] : 'success';

                if ($filter === 'success') {
                    // Mostrar solo el último login exitoso de cada usuario (una fila por usuario)
                    $sql = 'SELECT la1.*
                            FROM login_attempts la1
                            INNER JOIN (
                                SELECT username, MAX(attempt_time) as max_time
                                FROM login_attempts
                                WHERE success = 1
                                AND attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                                GROUP BY username
                            ) la2 ON la1.username = la2.username AND la1.attempt_time = la2.max_time
                            WHERE la1.success = 1
                            ORDER BY la1.attempt_time DESC';
                } elseif ($filter === 'failed') {
                    // Mostrar solo intentos fallidos (para debug)
                    $sql = 'SELECT * FROM login_attempts
                            WHERE success = 0
                            AND attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                            ORDER BY attempt_time DESC LIMIT 100';
                } else {
                    // Mostrar todos
                    $sql = 'SELECT * FROM login_attempts
                            WHERE attempt_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                            ORDER BY attempt_time DESC LIMIT 100';
                }

                $result = $pdo->fetchAll($sql);

                $json = array(
                    'status' => 'OK',
                    'result' => $result
                );
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'msg' => 'Error al obtener intentos de login'
                );
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    public function unblock_ip() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            try {
                $pdo = $this->get_pdo();
                $ip_address = isset($_POST['ip_address']) ? $_POST['ip_address'] : null;

                if (!$ip_address) {
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'IP no especificada'
                    );
                } else {
                    $sql = 'DELETE FROM ip_blacklist WHERE ip_address = :ip';
                    $pdo->perform($sql, array('ip' => $ip_address));

                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'IP desbloqueada correctamente'
                    );
                }
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al desbloquear IP'
                );
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    public function unlock_account() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            try {
                $pdo = $this->get_pdo();
                $username = isset($_POST['username']) ? $_POST['username'] : null;

                if (!$username) {
                    $json = array(
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'Usuario no especificado'
                    );
                } else {
                    $sql = 'DELETE FROM account_lockout WHERE username = :username';
                    $pdo->perform($sql, array('username' => $username));

                    $json = array(
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Cuenta desbloqueada correctamente'
                    );
                }
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al desbloquear cuenta'
                );
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // =====================================================
    // ENABLE/DISABLE RATE LIMITING
    // =====================================================

    public function enable_rate_limit() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            try {
                $pdo = $this->get_pdo();
                $sql = 'UPDATE student_config SET value = ? WHERE `key` = ?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array('1', 'login_rate_limit_enabled'));

                $json = array(
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Rate Limiting activado correctamente. El sistema ahora bloqueará intentos sospechosos de login.'
                );
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al activar Rate Limiting: ' . $e->getMessage()
                );
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    public function disable_rate_limit() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            try {
                $pdo = $this->get_pdo();
                $sql = 'UPDATE student_config SET value = ? WHERE `key` = ?';
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array('0', 'login_rate_limit_enabled'));

                $json = array(
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Rate Limiting desactivado. ADVERTENCIA: El sistema ya no bloqueará ataques de fuerza bruta.'
                );
            } catch (Exception $e) {
                $json = array(
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al desactivar Rate Limiting: ' . $e->getMessage()
                );
            }

            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

}
