<?php
// --
class C_Bot_Configuration extends Controller
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
        $this->functions->check_permissions($this->segment->get('modules'), 'Bot_Configuration');
        $this->view->set_js('index');
        $this->view->set_menu([
            'modules' => $this->segment->get('modules'),
            'view' => 'Bot_Configuration',
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

            $partial_path = __DIR__ . '/../views/bot_configuration/partials/' . $partial . '.php';

            if (file_exists($partial_path)) {
                include $partial_path;
            } else {
                echo '<div class="alert alert-warning">Contenido no disponible.</div>';
            }
        }
    }

    // ========== COMPANY INFO ==========

    // --
    public function get_company_info()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_company_info();
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Metodo no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function update_company_info()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            $bind = [
                // Contacto
                'company_name' => $_POST['company_name'] ?? '',
                'company_phone' => $_POST['company_phone'] ?? '',
                'company_email' => $_POST['company_email'] ?? '',
                'company_website' => $_POST['company_website'] ?? '',
                // Ubicación
                'company_address' => $_POST['company_address'] ?? '',
                'company_city' => $_POST['company_city'] ?? '',
                'company_region' => $_POST['company_region'] ?? '',
                'company_country' => $_POST['company_country'] ?? '',
                // Redes Sociales
                'social_facebook' => $_POST['social_facebook'] ?? '',
                'social_instagram' => $_POST['social_instagram'] ?? '',
                // Horarios
                'schedule_weekdays' => $_POST['schedule_weekdays'] ?? '',
                'schedule_saturday' => $_POST['schedule_saturday'] ?? '',
                'schedule_sunday' => $_POST['schedule_sunday'] ?? '',
                'schedule_emergency' => $_POST['schedule_emergency'] ?? '',
                // Bot y Enlaces
                'assistant_name' => $_POST['assistant_name'] ?? '',
                'google_sheet_id' => $_POST['google_sheet_id'] ?? '',
                'google_form_url' => $_POST['google_form_url'] ?? '',
                'whatsapp_group_link' => $_POST['whatsapp_group_link'] ?? '',
                'media_storage_path' => $_POST['media_storage_path'] ?? '',
            ];
            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_company_info($bind);
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Metodo no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== GENERAL CONFIG ==========

    // --
    public function get_general_config()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_general_config();
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function update_general_config()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // Only include fields that are present in POST (partial updates allowed)
            $bind = [];
            $allowed_fields = [
                // Configuraciones de Estudiantes
                'min_required_hours',
                // Configuraciones de Grupos
                'max_supervisors_per_group',
                // Sistema
                'system_url',
                // Caché y Logs - Redis
                'redis_enabled',
                'redis_host',
                'redis_port',
                'redis_db',
                // Caché y Logs - TTL
                'cache_ttl_general_ms',
                'cache_ttl_config_ms',
                // Caché y Logs - Logs
                'log_cleanup_schedule',
                'audit_log_retention_days',
            ];

            foreach ($allowed_fields as $field) {
                if (isset($_POST[$field])) {
                    $bind[$field] = $_POST[$field];
                }
            }

            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_general_config($bind);
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== SECURITY CONFIG ==========

    // --
    public function get_security_config()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_security_config();
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Metodo no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    public function update_security_config()
    {
        $this->functions->validate_session($this->segment->get('isExist'));

        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $allowed_fields = [
                'enable_rate_limiting',
                'rate_limit_window_ms',
                'rate_limit_max_requests',
                'security_violation_block_minutes',
                'rate_limit_block_after_violations',
                'enable_action_rate_limiting',
                'enable_phone_blocking',
                'max_messages_per_minute',
                'max_verification_attempts',
                'max_ai_queries_per_hour',
                'spam_block_minutes',
                'otp_code_expiration_minutes',
                'verification_block_minutes',
                'user_block_escalation_threshold',
                'enable_security_alerts',
                'alert_on_suspicious_activity',
                'admin_email_alerts',
                'enable_signature_validation',
                'enable_cors_protection',
                'enable_helmet_protection',
                'trust_proxy',
                'slowloris_protection_enabled',
                'slowloris_headers_timeout',
                'slowloris_request_timeout',
                'slowloris_keepalive_timeout',
                'slowloris_max_connections_per_ip',
                'slowloris_slow_request_threshold',
            ];

            $bind = [];
            foreach ($_POST as $key => $value) {
                if (in_array($key, $allowed_fields)) {
                    if ($value === 'true' || $value === 'false' || $value !== '') {
                        $bind[$key] = $value;
                    }
                }
            }

            if (empty($bind)) {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No hay campos para actualizar',
                ];
                header('Content-Type: application/json');
                echo json_encode($response);
                return;
            }
            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_security_config($bind);
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Metodo no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== VERIFY ADMIN PASSWORD ==========

    // Verificar contraseña del administrador para desactivar switches críticos
    public function verify_admin_password()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            $password = $_POST['password'] ?? '';

            if (empty($password)) {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Ingrese su contraseña'
                ];
                header('Content-Type: application/json');
                echo json_encode($response);
                return;
            }

            // Obtener el ID del usuario de la sesión
            $session_data = $this->segment->get('data');
            $user_id = $session_data['id_user'] ?? null;

            if (!$user_id) {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Sesión no válida'
                ];
                header('Content-Type: application/json');
                echo json_encode($response);
                return;
            }

            // Verificar contraseña contra la base de datos
            $obj_user = $this->load_model('Users');
            $encrypted_input = $this->functions->encrypt_password($password);
            $user_response = $obj_user->validate_user_password([
                'password' => $encrypted_input,
                'id_user' => $user_id
            ]);

            if ($user_response['status'] === 'OK') {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Contraseña verificada'
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Contraseña incorrecta'
                ];
            }

            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Metodo no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== EMAIL CONFIG ==========

    // --
    public function get_email_config()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_email_config();
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function update_email_config()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // Only include fields that are present in POST (partial updates allowed)
            $bind = [];
            $allowed_fields = [
                'email_user',
                'email_password',
                'smtp_server',
                'smtp_port',
                'sender_name',
                'google_sheets_polling_interval_ms',
                'auto_email_sending',
                'google_service_account_email',
                'google_private_key',
            ];

            foreach ($allowed_fields as $field) {
                if (isset($_POST[$field])) {
                    $bind[$field] = $_POST[$field];
                }
            }

            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_email_config($bind);
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function send_test_email()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // TODO: Implement test email sending
            $json = [
                'status' => 'OK',
                'type' => 'info',
                'msg' => 'Funcionalidad de email de prueba pendiente de implementar.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== MENUS ==========

    // --
    public function get_menus()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_menus();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_menu($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_menu($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function save_menu()
    {
        if (ob_get_length()) {
            ob_clean();
        }

        $this->functions->validate_session($this->segment->get('isExist'));

        $data = $_REQUEST;

        $bind = [
            'menu_id' =>
                isset($data['menu_id']) && $data['menu_id'] !== '' ? $data['menu_id'] : null,
            'key' => isset($data['key']) ? trim($data['key']) : '',
            'state_key' =>
                isset($data['state_key']) && $data['state_key'] !== ''
                    ? trim($data['state_key'])
                    : null,
            'state_name' =>
                isset($data['state_name']) && $data['state_name'] !== ''
                    ? trim($data['state_name'])
                    : null,
            'label' => isset($data['label']) ? trim($data['label']) : '',
            'description' => isset($data['description']) ? trim($data['description']) : '',
            'icon' => isset($data['icon']) ? trim($data['icon']) : '',
            'color' => isset($data['color']) ? trim($data['color']) : '#87dde3',
            'order' => isset($data['order']) ? intval($data['order']) : 0,
            'allows_registration' => isset($data['allows_registration'])
                ? intval($data['allows_registration'])
                : 0,
            'action_key' =>
                isset($data['action_key']) && $data['action_key'] !== ''
                    ? trim($data['action_key'])
                    : null,
            'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
        ];

        $obj_config = $this->load_model('Bot_Configuration');
        $response = $obj_config->save_menu($bind);

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // --
    public function check_menu_options($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->check_menu_options($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function delete_menu($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = ['id' => $id];
            // Properly handle boolean conversion from POST data
            $delete_options = true; // default
            if (isset($_POST['delete_options'])) {
                // Handle both boolean and string "false"/"true"
                $delete_options = filter_var($_POST['delete_options'], FILTER_VALIDATE_BOOLEAN);
            }
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->delete_menu($bind, $delete_options);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }

    // ========== MENU OPTIONS ==========

    // --
    public function get_menu_options()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_menu_options();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_next_order_for_menu($menu_id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['menu_id' => $menu_id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_next_order_for_menu($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_menu_option($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_menu_option($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function save_menu_option()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = [
                'option_id' =>
                    isset($_POST['option_id']) && $_POST['option_id'] !== ''
                        ? $_POST['option_id']
                        : null,
                'menu_id' =>
                    isset($_POST['menu_id']) && $_POST['menu_id'] !== '' ? $_POST['menu_id'] : null,
                'parent_option_id' =>
                    isset($_POST['parent_option_id']) && $_POST['parent_option_id'] !== ''
                        ? $_POST['parent_option_id']
                        : null,
                'option_key' => $_POST['option_key'] ?? '',
                'state_key' =>
                    isset($_POST['state_key']) && $_POST['state_key'] !== ''
                        ? $_POST['state_key']
                        : null,
                'state_name' =>
                    isset($_POST['state_name']) && $_POST['state_name'] !== ''
                        ? trim($_POST['state_name'])
                        : null,
                'label' => $_POST['label'] ?? '',
                'description' => $_POST['description'] ?? '',
                'icon' => $_POST['icon'] ?? '',
                'color' => $_POST['color'] ?? '#2ecc71',
                'option_order' => $_POST['option_order'] ?? 0,
                'level' => $_POST['level'] ?? 1,
                'path' => $_POST['path'] ?? null,
                'action_key' =>
                    isset($_POST['action_key']) && $_POST['action_key'] !== ''
                        ? $_POST['action_key']
                        : null,
                'is_active' => $_POST['is_active'] ?? 1,
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->save_menu_option($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function check_option_children($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->check_option_children($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_menu_options_n3()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_menu_options_n3();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_next_order_for_parent_option($parent_option_id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['parent_option_id' => $parent_option_id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_next_order_for_parent_option($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function delete_menu_option($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = ['id' => $id];
            // Properly handle boolean conversion from POST data
            $delete_children = true; // default
            if (isset($_POST['delete_children'])) {
                // Handle both boolean and string "false"/"true"
                $delete_children = filter_var($_POST['delete_children'], FILTER_VALIDATE_BOOLEAN);
            }
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->delete_menu_option($bind, $delete_children);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== MENU ACTIONS ==========

    // --
    public function get_menu_actions()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            // Obtener categoría del query string (?category=menu_principal)
            $category = isset($_GET['category']) ? $_GET['category'] : null;

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_menu_actions($category);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_menu_action($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_menu_action($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function save_menu_action()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = [
                'action_id' =>
                    isset($_POST['action_id']) && $_POST['action_id'] !== ''
                        ? $_POST['action_id']
                        : null,
                'action_key' => $_POST['action_key'] ?? '',
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'action_type' => $_POST['action_type'] ?? 'service_method',
                'category' => $_POST['category'] ?? 'menu_opcion',
                'configuration' => $_POST['configuration'] ?? null,
                'requires_auth' => $_POST['requires_auth'] ?? 0,
                'is_system' => $_POST['is_system'] ?? 0,
                'is_active' => $_POST['is_active'] ?? 1,
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->save_menu_action($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function delete_menu_action($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->delete_menu_action($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== SYSTEM STATES ==========

    // --
    public function get_system_states()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            // Obtener tipo del query string (?type=menu)
            $type = isset($_GET['type']) ? $_GET['type'] : null;

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_system_states($type);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_all_system_states()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            // Sin filtro de tipo, obtener todos los estados
            $response = $obj_config->get_system_states(null);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_system_state($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_system_state($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function save_system_state()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $data = $_REQUEST;

            $bind = [
                'state_id' =>
                    isset($data['state_id']) && $data['state_id'] !== '' ? $data['state_id'] : null,
                'key' => isset($data['key']) ? trim($data['key']) : '',
                'name' => isset($data['name']) ? trim($data['name']) : '',
                'description' =>
                    isset($data['description']) && $data['description'] !== ''
                        ? trim($data['description'])
                        : null,
                'type' => isset($data['type']) ? trim($data['type']) : 'system',
                'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->save_system_state($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function delete_system_state($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->delete_system_state($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== SYSTEM MESSAGES ==========

    // --
    public function get_system_messages()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_system_messages();
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Metodo no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_system_message($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_system_message($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }

    // --
    public function save_system_message()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = [
                'message_id' => $_POST['message_id'] ?? null,
                'key' => $_POST['key'],
                'label' => $_POST['label'],
                'description' => $_POST['description'] ?? null,
                'category' => $_POST['category'],
                'message' => $_POST['message'],
                'variables' => $_POST['variables'] ?? null,
                'is_active' => $_POST['is_active'] ?? 1,
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->save_system_message($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }

    // --
    public function delete_system_message($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->delete_system_message($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }
    // --

    // ========== FAQ ==========

    // --
    public function get_faqs()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'GET') {
            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_faqs();
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Metodo no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function create_faq()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            $bind = [
                'question' => $_POST['question'],
                'answer' => $_POST['answer'],
                'category' => $_POST['category'] ?? 'hours',
                'order' => isset($_POST['order']) ? intval($_POST['order']) : 0,
                'is_active' => isset($_POST['is_active']) ? intval($_POST['is_active']) : 1,
            ];
            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->create_faq($bind);
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Metodo no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function update_faq()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            $bind = [
                'id' => isset($_POST['id']) ? intval($_POST['id']) : null,
                'order' => isset($_POST['order']) ? intval($_POST['order']) : 0,
                'question' => isset($_POST['question']) ? $_POST['question'] : '',
                'answer' => isset($_POST['answer']) ? $_POST['answer'] : '',
                'category' => isset($_POST['category']) ? $_POST['category'] : 'hours',
                'is_active' => isset($_POST['is_active']) ? intval($_POST['is_active']) : 0,
            ];

            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_faq($bind);
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Metodo no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function delete_faq()
    {
        // --
        $this->functions->validate_session($this->segment->get('isExist'));
        // --
        $request = $_SERVER['REQUEST_METHOD'];
        // --
        if ($request === 'POST') {
            // --
            $bind = ['id' => isset($_POST['id']) ? intval($_POST['id']) : null];
            // --
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->delete_faq($bind);
            // --
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            // --
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Metodo no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== WHATSAPP CONFIG ==========

    // --
    public function get_whatsapp_config()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_whatsapp_config();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function update_whatsapp_config()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            // Only include fields that are present in POST (partial updates allowed)
            $bind = [];
            $allowed_fields = [
                'enable_auto_download_media',
                'whatsapp_api_url',
                'whatsapp_access_token',
                'whatsapp_phone_number_id',
                'meta_app_secret',
                'whatsapp_verify_token',
                'message_retention_days',
                'message_storage_mode',
                'max_history_messages',
                'outgoing_polling_interval_ms',
                'outgoing_max_retries',
                'max_image_size_bytes',
                'max_document_size_bytes',
                'max_video_size_bytes',
                'max_audio_size_bytes',
            ];

            foreach ($allowed_fields as $field) {
                if (isset($_POST[$field])) {
                    $bind[$field] = $_POST[$field];
                }
            }

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_whatsapp_config($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== AI CONFIG ==========

    // --
    public function get_ai_config()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_ai_config();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function update_ai_config()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = [
                // AI Configuration (bot_ai_prompts) - 3 campos
                'openai_api_key' => $_POST['openai_api_key'] ?? '',
                'carla_main_prompt' => $_POST['carla_main_prompt'] ?? '',
                'ai_error_message' => $_POST['ai_error_message'] ?? '',
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_ai_config($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_ai_prompts()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_ai_prompts();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_ai_prompt($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_ai_prompt($id);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function create_ai_prompt()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = [
                'key' => $_POST['key'] ?? '',
                'prompt_text' => $_POST['prompt_text'] ?? '',
                'description' => $_POST['description'] ?? null,
                'context' => $_POST['context'] ?? null,
                'display_order' => $_POST['display_order'] ?? 0,
                'model' => $_POST['model'] ?? 'gpt-4o-mini',
                'max_tokens' => $_POST['max_tokens'] ?? 300,
                'temperature' => $_POST['temperature'] ?? 0.7,
                'is_active' => $_POST['is_active'] ?? 1,
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->create_ai_prompt($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function update_ai_prompt()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = [
                'id' => $_POST['id'] ?? '',
                'prompt_text' => $_POST['prompt_text'] ?? '',
                'description' => $_POST['description'] ?? null,
                'context' => $_POST['context'] ?? null,
                'display_order' => $_POST['display_order'] ?? 0,
                'model' => $_POST['model'] ?? 'gpt-4o-mini',
                'max_tokens' => $_POST['max_tokens'] ?? 300,
                'temperature' => $_POST['temperature'] ?? 0.7,
                'is_active' => $_POST['is_active'] ?? 1,
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_ai_prompt($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function delete_ai_prompt($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->delete_ai_prompt($id);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== INFRASTRUCTURE CONFIG ==========

    // --
    public function get_infrastructure_config()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_infrastructure_config();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function update_infrastructure_config()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = [
                // Redis
                'redis_enabled' => $_POST['redis_enabled'] ?? '',
                'redis_host' => $_POST['redis_host'] ?? '',
                'redis_port' => $_POST['redis_port'] ?? '',
                'redis_db' => $_POST['redis_db'] ?? '',
                'redis_default_ttl' => $_POST['redis_default_ttl'] ?? '',
                'cache_ttl_general' => $_POST['cache_ttl_general'] ?? '',
                'cache_ttl_config_ms' => $_POST['cache_ttl_config_ms'] ?? '',
                // CORS
                'allowed_cors_origin_graph' => $_POST['allowed_cors_origin_graph'] ?? '',
                'allowed_cors_origin_facebook' => $_POST['allowed_cors_origin_facebook'] ?? '',
                'allowed_cors_origin_business' => $_POST['allowed_cors_origin_business'] ?? '',
                // Servidor
                'trust_proxy' => $_POST['trust_proxy'] ?? '',
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_infrastructure_config($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== EMAIL TEMPLATES ==========

    // --
    public function get_email_templates()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_email_templates();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_email_template($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_email_template($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function update_email_template()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = [
                'id' => $_POST['id'],
                'subject' => $_POST['subject'],
                'content' => $_POST['content'],
                'active' => $_POST['active'] ?? 1,
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_email_template($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // ========== EMAIL DOMAINS ==========

    // --
    public function get_email_domains()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_email_domains();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function get_email_domain($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'GET') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->get_email_domain($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function create_email_domain()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = [
                'domain' => $_POST['domain'],
                'description' => $_POST['description'] ?? '',
                'is_allowed' => $_POST['is_allowed'] ?? 1,
                'is_corporate' => $_POST['is_corporate'] ?? 0,
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->create_email_domain($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function update_email_domain()
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = [
                'id' => $_POST['id'],
                'domain' => $_POST['domain'],
                'description' => $_POST['description'],
                'is_allowed' => $_POST['is_allowed'],
                'is_corporate' => $_POST['is_corporate'],
            ];

            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->update_email_domain($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }

    // --
    public function delete_email_domain($id)
    {
        $this->functions->validate_session($this->segment->get('isExist'));
        $request = $_SERVER['REQUEST_METHOD'];

        if ($request === 'POST') {
            $bind = ['id' => $id];
            $obj_config = $this->load_model('Bot_Configuration');
            $response = $obj_config->delete_email_domain($bind);
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $json = [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Método no permitido.',
            ];
            header('Content-Type: application/json');
            echo json_encode($json);
        }
    }
}
