<?php
// --
class M_Bot_Configuration extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // ========== ENCRYPTION FUNCTIONS (AES-256-GCM) ==========

    /**
     * Get master encryption key from .env file
     * @return string|null The encryption key or null if not found
     */
    private function get_master_key()
    {
        try {
            $env_path = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . '.env';

            if (!file_exists($env_path)) {
                error_log('[Encryption] .env file not found at: ' . $env_path);
                return null;
            }

            $env_content = file_get_contents($env_path);
            if ($env_content === false) {
                error_log('[Encryption] Failed to read .env file');
                return null;
            }

            if (
                preg_match(
                    '/ENCRYPTION_KEY\s*=\s*["\']?([a-f0-9]{64})["\']?/i',
                    $env_content,
                    $matches,
                )
            ) {
                return $matches[1];
            }

            error_log('[Encryption] ENCRYPTION_KEY not found in .env');
            return null;
        } catch (Exception $e) {
            error_log('[Encryption] Error reading .env: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Encrypt data using AES-256-GCM
     * @param string $plaintext The text to encrypt
     * @return string|null Format: iv:tag:ciphertext or null on failure
     */
    private function encrypt_aes256($plaintext)
    {
        try {
            if (empty($plaintext)) {
                return null;
            }

            $master_key_hex = $this->get_master_key();
            if ($master_key_hex === null) {
                error_log('[Encryption] Master key not available');
                return null;
            }

            $key = hex2bin($master_key_hex);
            if ($key === false || strlen($key) !== 32) {
                error_log('[Encryption] Invalid master key format');
                return null;
            }

            $iv = openssl_random_pseudo_bytes(16);

            $ciphertext = openssl_encrypt(
                $plaintext,
                'aes-256-gcm',
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
            );

            if ($ciphertext === false) {
                error_log('[Encryption] Encryption failed');
                return null;
            }

            return bin2hex($iv) . ':' . bin2hex($tag) . ':' . bin2hex($ciphertext);
        } catch (Exception $e) {
            error_log('[Encryption] Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Decrypt data using AES-256-GCM
     * @param string $encrypted Format: iv:tag:ciphertext
     * @return string|null The decrypted text or null on failure
     */
    private function decrypt_aes256($encrypted)
    {
        try {
            if (empty($encrypted) || strpos($encrypted, ':') === false) {
                return null;
            }

            $parts = explode(':', $encrypted);
            if (count($parts) !== 3) {
                error_log('[Encryption] Invalid encrypted format');
                return null;
            }

            $master_key_hex = $this->get_master_key();
            if ($master_key_hex === null) {
                error_log('[Encryption] Master key not available');
                return null;
            }

            $key = hex2bin($master_key_hex);
            if ($key === false || strlen($key) !== 32) {
                error_log('[Encryption] Invalid master key format');
                return null;
            }

            $iv = hex2bin($parts[0]);
            $tag = hex2bin($parts[1]);
            $ciphertext = hex2bin($parts[2]);

            if ($iv === false || $tag === false || $ciphertext === false) {
                error_log('[Encryption] Invalid hex data in encrypted string');
                return null;
            }

            $plaintext = openssl_decrypt(
                $ciphertext,
                'aes-256-gcm',
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
            );

            if ($plaintext === false) {
                error_log('[Encryption] Decryption failed');
                return null;
            }

            return $plaintext;
        } catch (Exception $e) {
            error_log('[Encryption] Exception: ' . $e->getMessage());
            return null;
        }
    }

    // ========== COMPANY INFO ==========

    // --
    public function get_company_info()
    {
        try {
            // Define all company info keys explicitly
            $company_keys = [
                // Información Básica
                'company_name',
                'company_phone',
                'company_email',
                'company_website',
                // Ubicación
                'company_address',
                'company_city',
                'company_region',
                'company_country',
                // Redes Sociales
                'social_facebook',
                'social_instagram',
                // Horarios
                'schedule_weekdays',
                'schedule_saturday',
                'schedule_sunday',
                'schedule_emergency',
                // Bot y Enlaces
                'assistant_name',
                'google_sheet_id',
                'google_form_url',
                'whatsapp_group_link',
                'media_storage_path',
            ];

            // Create placeholders for IN clause
            $placeholders = [];
            $params = [];
            foreach ($company_keys as $index => $key) {
                $placeholder = 'key' . $index;
                $placeholders[] = ':' . $placeholder;
                $params[$placeholder] = $key;
            }

            $sql =
                'SELECT `key`, `value`, `category`, `data_type`
                    FROM bot_company_info
                    WHERE `key` IN (' .
                implode(',', $placeholders) .
                ')
                    AND is_active = 1
                    ORDER BY category, display_order';

            $result = $this->pdo->fetchAll($sql, $params);

            if ($result) {
                // Convert array of key-value pairs to associative array
                $data = [];
                foreach ($result as $row) {
                    $data[$row['key']] = $row['value'];
                }

                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Información de empresa obtenida correctamente.',
                    'data' => $data,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontró información de empresa.',
                    'data' => [],
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener información: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }

    // --
    public function update_company_info($bind)
    {
        try {
            $updated = 0;
            $errors = [];

            // Update each key-value pair individually
            foreach ($bind as $key => $value) {
                // Skip empty keys
                if (empty($key)) {
                    continue;
                }

                // Update or insert the key-value pair
                $sql = 'UPDATE bot_company_info
                        SET value = :value, updated_at = NOW()
                        WHERE `key` = :key';

                $params = ['key' => $key, 'value' => $value];
                $result = $this->pdo->perform($sql, $params);

                if ($result) {
                    $updated++;
                } else {
                    $errors[] = $key;
                }
            }

            if ($updated > 0 && count($errors) == 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' =>
                        'Información de empresa actualizada correctamente. (' .
                        $updated .
                        ' campos)',
                ];
            } elseif ($updated > 0 && count($errors) > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'warning',
                    'msg' =>
                        'Se actualizaron ' .
                        $updated .
                        ' campos. Errores en: ' .
                        implode(', ', $errors),
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar información de empresa.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ========== GENERAL CONFIG ==========

    // --
    public function get_general_config()
    {
        try {
            // Define all student config keys explicitly
            $student_keys = [
                'min_required_hours',
                'max_supervisors_per_group',
                'system_url',
            ];

            // Define security config keys (ALL cache/logs fields are in security_config table)
            $security_keys = [
                'redis_enabled',
                'redis_host',
                'redis_port',
                'redis_db',
                'cache_ttl_general_ms',
                'cache_ttl_config_ms',
                'log_cleanup_schedule',
                'audit_log_retention_days',
            ];

            $data = [];

            // Fetch from student_config
            $placeholders = [];
            $params = [];
            foreach ($student_keys as $index => $key) {
                $placeholder = 'key' . $index;
                $placeholders[] = ':' . $placeholder;
                $params[$placeholder] = $key;
            }
            $sql = 'SELECT `key`, value FROM student_config WHERE `key` IN (' . implode(',', $placeholders) . ')';
            $result = $this->pdo->fetchAll($sql, $params);
            if ($result) {
                foreach ($result as $row) {
                    $data[$row['key']] = $row['value'];
                }
            }

            // Fetch from security_config
            $placeholders = [];
            $params = [];
            foreach ($security_keys as $index => $key) {
                $placeholder = 'key' . $index;
                $placeholders[] = ':' . $placeholder;
                $params[$placeholder] = $key;
            }
            $sql = 'SELECT `key`, value FROM security_config WHERE `key` IN (' . implode(',', $placeholders) . ')';
            $result = $this->pdo->fetchAll($sql, $params);
            if ($result) {
                foreach ($result as $row) {
                    $data[$row['key']] = $row['value'];
                }
            }

            $response = [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Configuración general obtenida correctamente.',
                'data' => $data,
            ];
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function update_general_config($bind)
    {
        try {
            $updated = 0;
            $errors = [];

            // Define all student config keys explicitly (TAB 2: General)
            $student_keys = [
                'min_required_hours',
                'max_supervisors_per_group',
                'system_url',
            ];

            // Define security config keys (ALL cache/logs fields are in security_config table)
            $security_keys = [
                'redis_enabled',
                'redis_host',
                'redis_port',
                'redis_db',
                'cache_ttl_general_ms',
                'cache_ttl_config_ms',
                'log_cleanup_schedule',
                'audit_log_retention_days',
            ];

            // Update student_config table
            foreach ($student_keys as $key) {
                if (isset($bind[$key])) {
                    $sql = 'UPDATE student_config SET value = :value WHERE `key` = :key';
                    $result = $this->pdo->perform($sql, ['key' => $key, 'value' => $bind[$key]]);
                    if ($result) {
                        $updated++;
                    } else {
                        $errors[] = $key;
                    }
                }
            }

            // Update security_config table
            foreach ($security_keys as $key) {
                if (isset($bind[$key])) {
                    $sql = 'UPDATE security_config SET value = :value WHERE `key` = :key';
                    $result = $this->pdo->perform($sql, ['key' => $key, 'value' => $bind[$key]]);
                    if ($result) {
                        $updated++;
                    } else {
                        $errors[] = $key;
                    }
                }
            }

            if ($updated > 0 && count($errors) == 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' =>
                        'Configuración general actualizada correctamente. (' .
                        $updated .
                        ' campos)',
                ];
            } elseif ($updated > 0 && count($errors) > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'warning',
                    'msg' =>
                        'Se actualizaron ' .
                        $updated .
                        ' campos. Errores en: ' .
                        implode(', ', $errors),
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar configuración general.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ========== SECURITY CONFIG ==========

    // --
    public function get_security_config()
    {
        try {
            $data = [];

            // Security config keys - 6 capas (25 campos totales)
            $security_keys = [
                // CAPA 1: Rate Limiting Global (5 campos)
                'enable_rate_limiting',
                'rate_limit_window_ms',
                'rate_limit_max_requests',
                'security_violation_block_minutes',
                'rate_limit_block_after_violations',

                // CAPA 2: Action Rate Limiting (7 campos)
                'enable_action_rate_limiting',
                'enable_phone_blocking',
                'max_messages_per_minute',
                'max_verification_attempts',
                'max_ai_queries_per_hour',
                'spam_block_minutes',
                'otp_code_expiration_minutes',

                // Backoff fields (merged into Action Rate Limiting)
                'verification_block_minutes',
                'user_block_escalation_threshold',

                // CAPA 4: Alertas de Seguridad (5 campos)
                'enable_security_alerts',
                'alert_on_suspicious_activity',
                'admin_email_alerts',

                // CAPA 5: Configuracion General (4 switches)
                'enable_signature_validation',
                'enable_cors_protection',
                'enable_helmet_protection',
                'trust_proxy',

                // CAPA 6: Slowloris Protection (6 campos)
                'slowloris_protection_enabled',
                'slowloris_headers_timeout',
                'slowloris_request_timeout',
                'slowloris_keepalive_timeout',
                'slowloris_max_connections_per_ip',
                'slowloris_slow_request_threshold',
            ];

            // Build placeholders for IN clause
            $placeholders = [];
            $params = [];
            foreach ($security_keys as $index => $key) {
                $placeholder = 'key' . $index;
                $placeholders[] = ':' . $placeholder;
                $params[$placeholder] = $key;
            }

            $sql =
                'SELECT `key`, value FROM security_config WHERE `key` IN (' .
                implode(',', $placeholders) .
                ')';
            $result = $this->pdo->fetchAll($sql, $params);

            if ($result) {
                foreach ($result as $row) {
                    $data[$row['key']] = $row['value'];
                }
            }

            $response = [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Configuración de seguridad obtenida correctamente.',
                'data' => $data,
            ];
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function update_security_config($bind)
    {
        try {
            $updated = 0;
            $errors = [];

            // Update each security config field
            foreach ($bind as $key => $value) {
                $sql = 'UPDATE security_config SET value = :value WHERE `key` = :key';
                $result = $this->pdo->perform($sql, ['key' => $key, 'value' => $value]);
                if ($result) {
                    $updated++;
                } else {
                    $errors[] = $key;
                }
            }

            if ($updated > 0 && count($errors) == 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' =>
                        'Configuración de seguridad actualizada correctamente. (' .
                        $updated .
                        ' campos)',
                ];
            } elseif ($updated > 0 && count($errors) > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'warning',
                    'msg' =>
                        'Se actualizaron ' .
                        $updated .
                        ' campos. Errores en: ' .
                        implode(', ', $errors),
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar configuración de seguridad.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ========== EMAIL CONFIG ==========

    // --
    public function get_email_config()
    {
        try {
            $data = [];

            // Load Functions for decrypt
            $functions = new Functions();

            // Get email configuration keys from security_config (9 campos: 6 email + 3 google/polling)
            $email_keys = [
                'email_user',
                'email_password',
                'smtp_server',
                'smtp_port',
                'auto_email_sending',
                'sender_name',
                'google_sheets_polling_interval_ms',
                'google_service_account_email',
                'google_private_key',
            ];

            $placeholders = [];
            $params = [];
            foreach ($email_keys as $index => $key) {
                $placeholder = 'key' . $index;
                $placeholders[] = ':' . $placeholder;
                $params[$placeholder] = $key;
            }

            $sql =
                'SELECT `key`, value FROM security_config WHERE `key` IN (' .
                implode(',', $placeholders) .
                ')';
            $result = $this->pdo->fetchAll($sql, $params);

            if ($result) {
                foreach ($result as $row) {
                    $key = $row['key'];
                    $value = $row['value'];

                    if (
                        in_array($key, ['email_password', 'google_private_key']) &&
                        !empty($value)
                    ) {
                        $decrypted = $this->decrypt_aes256($value);
                        if ($decrypted !== null) {
                            // Convert real line breaks back to literal \n for display
                            if ($key === 'google_private_key') {
                                $decrypted = str_replace("\n", '\\n', $decrypted);
                            }
                            $data[$key] = $decrypted;
                        } else {
                            $data[$key] = $value;
                        }
                    } else {
                        $data[$key] = $value;
                    }
                }
            }

            $response = [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Configuración de email obtenida correctamente.',
                'data' => $data,
            ];
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function update_email_config($bind)
    {
        try {
            $updated = 0;
            $errors = [];

            // Load Functions for encrypt
            $functions = new Functions();

            // Define expected email config keys
            $email_keys = [
                'email_user',
                'email_password',
                'smtp_server',
                'smtp_port',
                'auto_email_sending',
                'sender_name',
                'google_sheets_polling_interval_ms',
                'google_service_account_email',
                'google_private_key',
            ];

            foreach ($email_keys as $key) {
                if (isset($bind[$key])) {
                    $value = $bind[$key];

                    // For encrypted fields: skip if empty (preserve existing)
                    if (in_array($key, ['email_password', 'google_private_key'])) {
                        if (empty($value)) {
                            continue;
                        }

                        // Convert literal \n to real line breaks for google_private_key
                        if ($key === 'google_private_key') {
                            $value = str_replace('\\n', "\n", $value);
                        }

                        $encrypted = $this->encrypt_aes256($value);
                        if ($encrypted !== null) {
                            $value = $encrypted;
                        } else {
                            error_log('[Email Config] Failed to encrypt ' . $key);
                            $errors[] = $key;
                            continue;
                        }
                    }

                    $sql = 'UPDATE security_config SET value = :value WHERE `key` = :key';
                    $result = $this->pdo->perform($sql, ['key' => $key, 'value' => $value]);
                    if ($result) {
                        $updated++;
                    } else {
                        $errors[] = $key;
                    }
                }
            }

            if ($updated > 0 && count($errors) == 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' =>
                        'Configuración de email actualizada correctamente. (' .
                        $updated .
                        ' campos)',
                ];
            } elseif ($updated > 0 && count($errors) > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'warning',
                    'msg' =>
                        'Se actualizaron ' .
                        $updated .
                        ' campos. Errores en: ' .
                        implode(', ', $errors),
                ];
            } else {
                $response = [
                    'status' => 'OK',
                    'type' => 'info',
                    'msg' => 'No se realizaron cambios.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ========== MENUS ==========

    // --
    public function get_menus()
    {
        try {
            $sql = 'SELECT m.*,
                           ma.name as action_name,
                           ma.key as action_key_value
                    FROM bot_menu m
                    LEFT JOIN bot_menu_actions ma ON m.action_key = ma.key
                    ORDER BY m.order';
            $result = $this->pdo->fetchAll($sql, []);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Menús obtenidos correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontraron menús.',
                    'data' => [],
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener menús: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }

    // --
    public function get_menu_options()
    {
        try {
            $sql = 'SELECT mo.id, mo.menu_id, mo.parent_option_id, mo.`key`, mo.state_key,
                           mo.label, mo.description, mo.icon, mo.color, mo.`order`,
                           mo.`level`, mo.`path`, mo.action_key,
                           mo.is_active, mo.created_at, mo.updated_at,
                           m.label as menu_label,
                           m.color as menu_color,
                           ma.name as action_name,
                           ma.`key` as action_key_value
                    FROM bot_menu_options mo
                    LEFT JOIN bot_menu m ON mo.menu_id = m.id
                    LEFT JOIN bot_menu_actions ma ON mo.action_key = ma.`key`
                    ORDER BY mo.menu_id, mo.`order`';

            $result = $this->pdo->fetchAll($sql, []);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Opciones de menú obtenidas correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontraron opciones.',
                    'data' => [],
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener opciones: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }

    // --
    public function get_next_order_for_menu($bind)
    {
        try {
            $sql = 'SELECT MAX(`order`) as max_order
                    FROM bot_menu_options
                    WHERE menu_id = :menu_id';

            $result = $this->pdo->fetchOne($sql, $bind);

            $next_order = 1; // Default if no options exist for this menu

            if ($result && $result['max_order'] !== null) {
                $next_order = intval($result['max_order']) + 1;
            }

            $response = [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Siguiente orden obtenido correctamente.',
                'data' => ['next_order' => $next_order],
            ];
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener siguiente orden: ' . $e->getMessage(),
                'data' => ['next_order' => 1],
            ];
        }

        return $response;
    }

    // --
    public function get_menu_option($bind)
    {
        try {
            $sql = 'SELECT * FROM bot_menu_options WHERE id = :id';
            $result = $this->pdo->fetchOne($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Opción obtenida correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontró la opción.',
                    'data' => null,
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener opción: ' . $e->getMessage(),
                'data' => null,
            ];
        }

        return $response;
    }

    // --
    public function save_menu_option($bind)
    {
        try {
            $data = $bind;
            unset($data['option_id']);
            unset($data['state_name']); // No se guarda en bot_menu_options

            if (!empty($bind['option_id'])) {
                // Update existing option
                $data['option_id'] = $bind['option_id'];
                $sql = 'UPDATE bot_menu_options
                        SET menu_id = :menu_id,
                            parent_option_id = :parent_option_id,
                            `key` = :option_key,
                            state_key = :state_key,
                            label = :label,
                            description = :description,
                            icon = :icon,
                            color = :color,
                            `order` = :option_order,
                            `level` = :level,
                            `path` = :path,
                            action_key = :action_key,
                            is_active = :is_active,
                            updated_at = NOW()
                        WHERE id = :option_id';
                $stmt = $this->pdo->perform($sql, $data);
                $msg = 'Opción actualizada correctamente.';
            } else {
                // Crear nueva opción
                // Primero, crear el estado del sistema
                if (!empty($bind['state_name']) && !empty($bind['option_key'])) {
                    // Verificar si el estado ya existe
                    $checkSql = 'SELECT COUNT(*) as count FROM bot_system_states WHERE `key` = :key';
                    $exists = $this->pdo->fetchOne($checkSql, ['key' => $bind['option_key']]);

                    if (!$exists || $exists['count'] == 0) {
                        // Determinar el tipo de estado según el nivel
                        $stateType = 'sub_menu_n2'; // Default para nivel 2
                        if (isset($data['level']) && $data['level'] == 3) {
                            $stateType = 'sub_menu_n3';
                        }

                        // Crear el estado con el tipo correspondiente
                        $stateSql = 'INSERT INTO bot_system_states (`key`, name, `type`, is_active, created_at, updated_at)
                                     VALUES (:key, :name, :type, 1, NOW(), NOW())';
                        $this->pdo->perform($stateSql, [
                            'key' => $bind['option_key'],
                            'name' => $bind['state_name'],
                            'type' => $stateType
                        ]);
                    }

                    // Asignar el state_key igual al option_key
                    $data['state_key'] = $bind['option_key'];
                }

                // Insert new option
                $sql = 'INSERT INTO bot_menu_options
                        (menu_id, parent_option_id, `key`, state_key, label, description, icon, color, `order`, `level`, `path`, action_key, is_active, created_at, updated_at)
                        VALUES
                        (:menu_id, :parent_option_id, :option_key, :state_key, :label, :description, :icon, :color, :option_order, :level, :path, :action_key, :is_active, NOW(), NOW())';
                $stmt = $this->pdo->perform($sql, $data);
                $msg = 'Opción y estado creados correctamente.';
            }

            if ($stmt->rowCount() > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => $msg,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al guardar opción.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function check_option_children($bind)
    {
        try {
            $sql = 'SELECT COUNT(*) as children_count FROM bot_menu_options WHERE parent_option_id = :id';
            $result = $this->pdo->fetchOne($sql, $bind);

            $children_count = $result ? (int) $result['children_count'] : 0;

            return [
                'status' => 'OK',
                'type' => 'success',
                'has_children' => $children_count > 0,
                'children_count' => $children_count,
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al verificar sub-opciones: ' . $e->getMessage(),
                'has_children' => false,
                'children_count' => 0,
            ];
        }
    }

    // --
    public function delete_menu_option($bind, $delete_children = true)
    {
        try {
            // Primero obtener el state_key de la opción para eliminarlo después
            $optionSql = 'SELECT state_key FROM bot_menu_options WHERE id = :id';
            $option = $this->pdo->fetchOne($optionSql, $bind);
            $stateKey = $option ? $option['state_key'] : null;

            if (!$delete_children) {
                // Solo eliminar esta opción, dejar hijos huérfanos (parent_option_id = NULL)
                $sql_orphan = 'UPDATE bot_menu_options SET parent_option_id = NULL WHERE parent_option_id = :id';
                $this->pdo->perform($sql_orphan, $bind);
            }
            // Si delete_children = true, el CASCADE de la BD eliminará automáticamente los hijos

            $sql = 'DELETE FROM bot_menu_options WHERE id = :id';
            $stmt = $this->pdo->perform($sql, $bind);

            if ($stmt->rowCount() > 0) {
                // Eliminar el estado asociado (incondicionalmente)
                // Porque fue creado específicamente para esta opción
                if ($stateKey) {
                    $deleteStateSql = 'DELETE FROM bot_system_states WHERE `key` = :state_key';
                    $this->pdo->perform($deleteStateSql, ['state_key' => $stateKey]);
                }

                $msg = $delete_children ? 'Opción y sub-opciones eliminadas correctamente.' : 'Opción eliminada correctamente.';
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => $msg,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'No se pudo eliminar la opción. Puede que no exista.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al eliminar opción: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function get_menu_options_n3()
    {
        try {
            $sql = 'SELECT mo.id, mo.menu_id, mo.parent_option_id, mo.`key`, mo.state_key,
                           mo.label, mo.description, mo.icon, mo.color, mo.`order`,
                           mo.`level`, mo.`path`, mo.action_key,
                           mo.is_active, mo.created_at, mo.updated_at,
                           parent.label as parent_label,
                           m.color as menu_color,
                           ma.name as action_name,
                           ma.`key` as action_key_value
                    FROM bot_menu_options mo
                    LEFT JOIN bot_menu_options parent ON mo.parent_option_id = parent.id
                    LEFT JOIN bot_menu m ON mo.menu_id = m.id
                    LEFT JOIN bot_menu_actions ma ON mo.action_key = ma.`key`
                    WHERE mo.level = 3 AND mo.parent_option_id IS NOT NULL
                    ORDER BY mo.parent_option_id, mo.`order`';

            $result = $this->pdo->fetchAll($sql, []);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Opciones N3 obtenidas correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontraron opciones N3.',
                    'data' => [],
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener opciones N3: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }

    // --
    public function get_next_order_for_parent_option($bind)
    {
        try {
            $sql = 'SELECT MAX(`order`) as max_order
                    FROM bot_menu_options
                    WHERE parent_option_id = :parent_option_id';

            $result = $this->pdo->fetchOne($sql, $bind);

            $next_order = 1; // Default if no options exist for this parent

            if ($result && $result['max_order'] !== null) {
                $next_order = intval($result['max_order']) + 1;
            }

            $response = [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Siguiente orden obtenido correctamente.',
                'data' => ['next_order' => $next_order],
            ];
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener siguiente orden: ' . $e->getMessage(),
                'data' => ['next_order' => 1],
            ];
        }

        return $response;
    }

    // ========== MENU ACTIONS ==========

    // --
    public function get_menu_actions($category = null)
    {
        try {
            // Si se especifica categoría, filtrar por ella y solo las activas (para dropdowns)
            if ($category) {
                $sql = 'SELECT * FROM bot_menu_actions
                        WHERE (category = :category OR category = "ambos")
                        AND is_active = 1
                        ORDER BY name';
                $result = $this->pdo->fetchAll($sql, ['category' => $category]);
            } else {
                // Sin filtro, obtener todas (activas e inactivas) para la tabla de administración
                $sql = 'SELECT * FROM bot_menu_actions
                        ORDER BY name';
                $result = $this->pdo->fetchAll($sql, []);
            }

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Acciones obtenidas correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontraron acciones.',
                    'data' => [],
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener acciones: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }

    // --
    public function get_menu_action($bind)
    {
        try {
            $sql = 'SELECT * FROM bot_menu_actions WHERE id = :id';
            $result = $this->pdo->fetchOne($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Acción obtenida correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontró la acción.',
                    'data' => null,
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener acción: ' . $e->getMessage(),
                'data' => null,
            ];
        }

        return $response;
    }

    // --
    public function save_menu_action($bind)
    {
        try {
            if (!empty($bind['action_id'])) {
                // Update existing action
                $sql = 'UPDATE bot_menu_actions
                        SET `key` = :action_key,
                            name = :title,
                            description = :description,
                            handler_type = :action_type,
                            category = :category,
                            handler_config = :configuration,
                            requires_auth = :requires_auth,
                            is_system = :is_system,
                            is_active = :is_active,
                            updated_at = NOW()
                        WHERE id = :action_id';
                $stmt = $this->pdo->perform($sql, $bind);
                $msg = 'Acción actualizada correctamente.';
            } else {
                // Insert new action
                $sql = 'INSERT INTO bot_menu_actions
                        (`key`, name, description, handler_type, category, handler_config, requires_auth, is_system, is_active, created_at, updated_at)
                        VALUES
                        (:action_key, :title, :description, :action_type, :category, :configuration, :requires_auth, :is_system, :is_active, NOW(), NOW())';
                $stmt = $this->pdo->perform($sql, $bind);
                $msg = 'Acción creada correctamente.';
            }

            if ($stmt->rowCount() > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => $msg,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al guardar acción.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function delete_menu_action($bind)
    {
        try {
            $sql = 'DELETE FROM bot_menu_actions WHERE id = :id';
            $stmt = $this->pdo->perform($sql, $bind);

            if ($stmt->rowCount() > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Acción eliminada correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'No se pudo eliminar la acción. Puede que no exista.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al eliminar acción: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function get_menu($bind)
    {
        try {
            $sql = 'SELECT * FROM bot_menu WHERE id = :id';
            $result = $this->pdo->fetchOne($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Menú obtenido correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontró el menú.',
                    'data' => null,
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener menú: ' . $e->getMessage(),
                'data' => null,
            ];
        }

        return $response;
    }

    // --
    public function save_menu($bind)
    {
        try {
            $data = $bind;
            unset($data['menu_id']);
            unset($data['state_name']); // No se guarda en bot_menu

            if (!empty($bind['menu_id'])) {
                // Actualizar menú existente
                $data['menu_id'] = $bind['menu_id'];
                $sql = 'UPDATE bot_menu
                        SET `key` = :key,
                            state_key = :state_key,
                            label = :label,
                            description = :description,
                            icon = :icon,
                            color = :color,
                            `order` = :order,
                            allows_registration = :allows_registration,
                            action_key = :action_key,
                            is_active = :is_active,
                            updated_at = NOW()
                        WHERE id = :menu_id';
                $this->pdo->perform($sql, $data);
                $msg = 'Menú actualizado correctamente.';
            } else {
                // Crear nuevo menú
                // Primero, crear el estado del sistema
                if (!empty($bind['state_name']) && !empty($bind['key'])) {
                    // Verificar si el estado ya existe
                    $checkSql = 'SELECT COUNT(*) as count FROM bot_system_states WHERE `key` = :key';
                    $exists = $this->pdo->fetchOne($checkSql, ['key' => $bind['key']]);

                    if (!$exists || $exists['count'] == 0) {
                        // Crear el estado
                        $stateSql = 'INSERT INTO bot_system_states (`key`, name, `type`, is_active, created_at, updated_at)
                                     VALUES (:key, :name, :type, 1, NOW(), NOW())';
                        $this->pdo->perform($stateSql, [
                            'key' => $bind['key'],
                            'name' => $bind['state_name'],
                            'type' => 'menu'
                        ]);
                    }

                    // Asignar el state_key igual al key del menú
                    $data['state_key'] = $bind['key'];
                }

                // Insertar nuevo menú
                $sql = 'INSERT INTO bot_menu (`key`, state_key, label, description, icon, color, `order`, allows_registration, action_key, is_active, created_at, updated_at)
                        VALUES (:key, :state_key, :label, :description, :icon, :color, :order, :allows_registration, :action_key, :is_active, NOW(), NOW())';
                $this->pdo->perform($sql, $data);
                $msg = 'Menú y estado creados correctamente.';
            }

            return [
                'status' => 'OK',
                'type' => 'success',
                'msg' => $msg,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Error al guardar: ' . $e->getMessage(),
            ];
        }
    }

    // --
    public function check_menu_options($bind)
    {
        try {
            $sql = 'SELECT COUNT(*) as options_count FROM bot_menu_options WHERE menu_id = :id';
            $result = $this->pdo->fetchOne($sql, $bind);

            $options_count = $result ? (int) $result['options_count'] : 0;

            return [
                'status' => 'OK',
                'type' => 'success',
                'has_options' => $options_count > 0,
                'options_count' => $options_count,
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al verificar opciones: ' . $e->getMessage(),
                'has_options' => false,
                'options_count' => 0,
            ];
        }
    }

    // --
    public function delete_menu($bind, $delete_options = true)
    {
        try {
            // Primero obtener el state_key del menú para eliminarlo después
            $menuSql = 'SELECT state_key FROM bot_menu WHERE id = :id';
            $menu = $this->pdo->fetchOne($menuSql, $bind);
            $stateKey = $menu ? $menu['state_key'] : null;

            if ($delete_options) {
                // Obtener todas las opciones N2 del menú
                $n2OptionsSql = 'SELECT id, state_key FROM bot_menu_options WHERE menu_id = :id AND level = 2';
                $n2Options = $this->pdo->fetchAll($n2OptionsSql, $bind);

                if ($n2Options) {
                    foreach ($n2Options as $n2Option) {
                        // Para cada opción N2, obtener sus opciones N3 hijas
                        $n3OptionsSql = 'SELECT state_key FROM bot_menu_options WHERE parent_option_id = :parent_id AND level = 3';
                        $n3Options = $this->pdo->fetchAll($n3OptionsSql, ['parent_id' => $n2Option['id']]);

                        // Eliminar estados de las opciones N3
                        if ($n3Options) {
                            foreach ($n3Options as $n3Option) {
                                if ($n3Option['state_key']) {
                                    $deleteN3StateSql = 'DELETE FROM bot_system_states WHERE `key` = :state_key';
                                    $this->pdo->perform($deleteN3StateSql, ['state_key' => $n3Option['state_key']]);
                                }
                            }
                        }

                        // Eliminar estado de la opción N2
                        if ($n2Option['state_key']) {
                            $deleteN2StateSql = 'DELETE FROM bot_system_states WHERE `key` = :state_key';
                            $this->pdo->perform($deleteN2StateSql, ['state_key' => $n2Option['state_key']]);
                        }
                    }
                }

                // Ahora eliminar las opciones (el CASCADE de la BD eliminará las N3)
                $sql = 'DELETE FROM bot_menu_options WHERE menu_id = :id';
                $this->pdo->perform($sql, $bind);
            } else {
                // Only delete menu, leave options orphaned (set menu_id to NULL)
                $sql = 'UPDATE bot_menu_options SET menu_id = NULL WHERE menu_id = :id';
                $this->pdo->perform($sql, $bind);
            }

            // Then delete menu
            $sql = 'DELETE FROM bot_menu WHERE id = :id';
            $stmt = $this->pdo->perform($sql, $bind);

            if ($stmt->rowCount() > 0) {
                // Eliminar el estado asociado (incondicionalmente)
                // Porque fue creado específicamente para este menú
                if ($stateKey) {
                    $deleteStateSql = 'DELETE FROM bot_system_states WHERE `key` = :state_key';
                    $this->pdo->perform($deleteStateSql, ['state_key' => $stateKey]);
                }

                $msg = $delete_options ? 'Menú y opciones eliminados correctamente.' : 'Menú eliminado correctamente.';
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => $msg,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'No se encontró el menú o ya fue eliminado.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        } catch (Exception $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error inesperado: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ==================== SYSTEM STATES ====================

    // --
    public function get_system_states($type = null)
    {
        try {
            // Si se especifica tipo, filtrar por él
            if ($type) {
                $sql = 'SELECT id, `key`, name, description, `type`,
                               is_active, created_at, updated_at
                        FROM bot_system_states
                        WHERE `type` = :type
                        ORDER BY name';
                $result = $this->pdo->fetchAll($sql, ['type' => $type]);
            } else {
                // Sin filtro, obtener todos
                $sql = 'SELECT id, `key`, name, description, `type`,
                               is_active, created_at, updated_at
                        FROM bot_system_states
                        ORDER BY name';
                $result = $this->pdo->fetchAll($sql, []);
            }

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Estados obtenidos correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontraron estados.',
                    'data' => [],
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener estados: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }

    // --
    public function get_system_state($bind)
    {
        try {
            $sql = 'SELECT * FROM bot_system_states WHERE id = :id';
            $result = $this->pdo->fetchAll($sql, $bind);

            if ($result && count($result) > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Estado obtenido correctamente.',
                    'data' => $result[0],
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'Estado no encontrado.',
                    'data' => null,
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener estado: ' . $e->getMessage(),
                'data' => null,
            ];
        }

        return $response;
    }

    // --
    public function save_system_state($bind)
    {
        try {
            $data = $bind;
            unset($data['state_id']);

            if (!empty($bind['state_id'])) {
                // Actualizar estado existente
                $data['state_id'] = $bind['state_id'];
                $sql = 'UPDATE bot_system_states
                        SET `key` = :key,
                            name = :name,
                            description = :description,
                            `type` = :type,
                            is_active = :is_active,
                            updated_at = NOW()
                        WHERE id = :state_id';
                $this->pdo->perform($sql, $data);
                // La FK ON UPDATE CASCADE actualizará automáticamente state_key en bot_menu y bot_menu_options

                $msg = 'Estado actualizado correctamente.';
            } else {
                // Insertar nuevo estado
                $sql = 'INSERT INTO bot_system_states
                        (`key`, name, description, `type`,
                         is_active, created_at, updated_at)
                        VALUES (:key, :name, :description, :type,
                                :is_active, NOW(), NOW())';
                $this->pdo->perform($sql, $data);
                $msg = 'Estado creado correctamente.';
            }

            return [
                'status' => 'OK',
                'type' => 'success',
                'msg' => $msg,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'ERROR',
                'type' => 'error',
                'msg' => 'Error al guardar estado: ' . $e->getMessage(),
            ];
        }
    }

    // --
    public function delete_system_state($bind)
    {
        try {
            $sql = 'DELETE FROM bot_system_states WHERE id = :id';
            $stmt = $this->pdo->perform($sql, $bind);

            if ($stmt->rowCount() > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Estado eliminado correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'No se pudo eliminar el estado. Puede que no exista.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al eliminar estado: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function update_menu($bind)
    {
        try {
            $sql = 'UPDATE bot_menu
                    SET title = :title,
                        description = :description,
                        is_active = :is_active,
                        updated_at = NOW()
                    WHERE id = :id';

            $result = $this->pdo->query($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Menú actualizado correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar menú.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ========== SYSTEM MESSAGES ==========

    // --
    public function get_system_messages()
    {
        try {
            $sql = 'SELECT * FROM bot_system_messages ORDER BY category';
            $result = $this->pdo->fetchAll($sql, []);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Mensajes del sistema obtenidos correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontraron mensajes del sistema.',
                    'data' => [],
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener mensajes: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }

    // --

    // --
    public function get_system_message($bind)
    {
        try {
            $sql = 'SELECT * FROM bot_system_messages WHERE id = :id';
            $result = $this->pdo->fetchOne($sql, $bind);

            if ($result) {
                return [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Mensaje obtenido correctamente.',
                    'data' => $result,
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'Mensaje no encontrado.',
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    // --
    public function save_system_message($bind)
    {
        try {
            // Convert empty variables to NULL for JSON field
            if (empty($bind['variables']) || trim($bind['variables']) === '') {
                $bind['variables'] = null;
            }

            if (!empty($bind['message_id'])) {
                // Update existing message
                $sql = 'UPDATE bot_system_messages
                        SET `key` = :key,
                            label = :label,
                            description = :description,
                            category = :category,
                            message = :message,
                            variables = :variables,
                            is_active = :is_active,
                            updated_at = NOW()
                        WHERE id = :message_id';

                $stmt = $this->pdo->perform($sql, [
                    'message_id' => $bind['message_id'],
                    'key' => $bind['key'],
                    'label' => $bind['label'],
                    'description' => $bind['description'],
                    'category' => $bind['category'],
                    'message' => $bind['message'],
                    'variables' => $bind['variables'],
                    'is_active' => $bind['is_active'],
                ]);

                if ($stmt->rowCount() > 0) {
                    return [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Mensaje actualizado correctamente.',
                    ];
                } else {
                    return [
                        'status' => 'ERROR',
                        'type' => 'warning',
                        'msg' => 'No se realizaron cambios.',
                    ];
                }
            } else {
                // Insert new message
                $sql = 'INSERT INTO bot_system_messages (`key`, label, description, category, message, variables, is_active, created_at, updated_at)
                        VALUES (:key, :label, :description, :category, :message, :variables, :is_active, NOW(), NOW())';

                $stmt = $this->pdo->perform($sql, [
                    'key' => $bind['key'],
                    'label' => $bind['label'],
                    'description' => $bind['description'],
                    'category' => $bind['category'],
                    'message' => $bind['message'],
                    'variables' => $bind['variables'],
                    'is_active' => $bind['is_active'],
                ]);

                if ($stmt->rowCount() > 0) {
                    return [
                        'status' => 'OK',
                        'type' => 'success',
                        'msg' => 'Mensaje creado correctamente.',
                    ];
                } else {
                    return [
                        'status' => 'ERROR',
                        'type' => 'error',
                        'msg' => 'No se pudo crear el mensaje.',
                    ];
                }
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    // --
    public function delete_system_message($bind)
    {
        try {
            $sql = 'DELETE FROM bot_system_messages WHERE id = :id';
            $stmt = $this->pdo->perform($sql, $bind);

            if ($stmt->rowCount() > 0) {
                return [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Mensaje eliminado correctamente.',
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontró el mensaje para eliminar.',
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    // ========== FAQ ==========

    // --
    public function get_faqs()
    {
        try {
            $sql = 'SELECT * FROM bot_faq ORDER BY `order`';
            $result = $this->pdo->fetchAll($sql, []);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'FAQs obtenidas correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontraron FAQs.',
                    'data' => [],
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error al obtener FAQs: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }

    // --
    public function create_faq($bind)
    {
        try {
            $sql = 'INSERT INTO bot_faq
                    (`order`, question, answer, category, is_active, created_at, updated_at)
                    VALUES
                    (:order, :question, :answer, :category, :is_active, NOW(), NOW())';

            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'FAQ creada correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al crear FAQ.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function update_faq($bind)
    {
        try {
            $sql = 'UPDATE bot_faq
                    SET question = :question,
                        answer = :answer,
                        category = :category,
                        `order` = :order,
                        is_active = :is_active,
                        updated_at = NOW()
                    WHERE id = :id';

            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'FAQ actualizada correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar FAQ.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function delete_faq($bind)
    {
        try {
            $sql = 'DELETE FROM bot_faq WHERE id = :id';

            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'FAQ eliminada correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al eliminar FAQ.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ========== WHATSAPP CONFIG ==========

    // --
    public function get_whatsapp_config()
    {
        try {
            $whatsapp_keys = [
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

            // Create placeholders for IN clause
            $placeholders = [];
            $params = [];
            foreach ($whatsapp_keys as $index => $key) {
                $placeholder = 'key' . $index;
                $placeholders[] = ':' . $placeholder;
                $params[$placeholder] = $key;
            }

            $sql =
                'SELECT `key`, value FROM security_config
                    WHERE `key` IN (' .
                implode(',', $placeholders) .
                ')';

            $result = $this->pdo->fetchAll($sql, $params);

            $data = [];
            $functions = new Functions();

            if ($result) {
                foreach ($result as $row) {
                    $key = $row['key'];
                    $value = $row['value'];

                    if (
                        in_array($key, [
                            'whatsapp_access_token',
                            'whatsapp_verify_token',
                            'meta_app_secret',
                        ]) &&
                        !empty($value)
                    ) {
                        $decrypted = $this->decrypt_aes256($value);
                        $data[$key] = $decrypted !== null ? $decrypted : $value;
                    } else {
                        $data[$key] = $value;
                    }
                }
            }

            $response = [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Configuración de WhatsApp obtenida correctamente.',
                'data' => $data,
            ];
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function update_whatsapp_config($bind)
    {
        try {
            $updated = 0;
            $errors = [];
            $functions = new Functions();

            $whatsapp_keys = [
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

            foreach ($whatsapp_keys as $key) {
                if (isset($bind[$key])) {
                    $value = $bind[$key];

                    if (
                        in_array($key, [
                            'whatsapp_access_token',
                            'whatsapp_verify_token',
                            'meta_app_secret',
                        ])
                    ) {
                        if (empty($value)) {
                            continue;
                        }

                        $encrypted = $this->encrypt_aes256($value);
                        if ($encrypted !== null) {
                            $value = $encrypted;
                        } else {
                            error_log('[WhatsApp Config] Failed to encrypt ' . $key);
                            $errors[] = $key;
                            continue;
                        }
                    }

                    $sql = 'UPDATE security_config SET value = :value WHERE `key` = :key';
                    $result = $this->pdo->perform($sql, ['key' => $key, 'value' => $value]);
                    if ($result) {
                        $updated++;
                    } else {
                        $errors[] = $key;
                    }
                }
            }

            if ($updated > 0 && count($errors) == 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' =>
                        'Configuración de WhatsApp actualizada correctamente. (' .
                        $updated .
                        ' campos)',
                ];
            } elseif ($updated > 0 && count($errors) > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'warning',
                    'msg' =>
                        'Se actualizaron ' .
                        $updated .
                        ' campos. Errores en: ' .
                        implode(', ', $errors),
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar configuración de WhatsApp.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ========== AI CONFIG ==========

    // --
    public function get_ai_config()
    {
        try {
            $data = [];
            $functions = new Functions();

            // Get openai_api_key from security_config (encrypted with AES-256-GCM)
            $sql = 'SELECT `key`, value FROM security_config WHERE `key` = :key';
            $row = $this->pdo->fetchOne($sql, ['key' => 'openai_api_key']);

            if ($row && isset($row['value']) && !empty($row['value'])) {
                $decrypted = $this->decrypt_aes256($row['value']);
                $data['openai_api_key'] = $decrypted !== null ? $decrypted : $row['value'];
            } else {
                $data['openai_api_key'] = '';
            }

            // Get ALL prompts from bot_ai_prompts table, grouped by context
            $sql = 'SELECT id, `key`, prompt_text, description, context, display_order
                    FROM bot_ai_prompts
                    WHERE is_active = 1
                    ORDER BY
                        CASE WHEN context IS NULL THEN 0 ELSE 1 END,
                        context,
                        display_order,
                        id';
            $prompts = $this->pdo->fetchAll($sql);

            // Group prompts by context (NULL becomes 'main')
            $grouped = [];
            if ($prompts) {
                foreach ($prompts as $prompt) {
                    $ctx = !empty($prompt['context']) ? $prompt['context'] : 'main';
                    if (!isset($grouped[$ctx])) {
                        $grouped[$ctx] = [];
                    }
                    $grouped[$ctx][] = [
                        'id' => $prompt['id'],
                        'key' => $prompt['key'],
                        'prompt_text' => $prompt['prompt_text'],
                        'description' => $prompt['description'],
                    ];
                }
            }

            $data['prompts'] = $grouped;

            $response = [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Configuración de IA obtenida correctamente.',
                'data' => $data,
            ];
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
                'data' => [],
            ];
        } catch (Exception $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error general: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }

    // --
    public function update_ai_config($bind)
    {
        try {
            $updated = 0;
            $errors = [];
            $functions = new Functions();

            // Update openai_api_key in security_config (encrypted with AES-256-GCM)
            if (isset($bind['openai_api_key'])) {
                if (!empty($bind['openai_api_key'])) {
                    $encrypted_key = $this->encrypt_aes256($bind['openai_api_key']);
                    if ($encrypted_key !== null) {
                        $sql = 'UPDATE security_config SET value = :value WHERE `key` = :key';
                        $result = $this->pdo->perform($sql, [
                            'key' => 'openai_api_key',
                            'value' => $encrypted_key,
                        ]);
                        if ($result) {
                            $updated++;
                        } else {
                            $errors[] = 'openai_api_key';
                        }
                    } else {
                        error_log('[IA Config] Failed to encrypt openai_api_key');
                        $errors[] = 'openai_api_key';
                    }
                }
                // If empty, skip (preserve existing value)
            }

            // Update ANY prompt dynamically from bot_ai_prompts table
            // First, get all prompt keys from the table
            $sql = 'SELECT `key` FROM bot_ai_prompts WHERE is_active = 1';
            $all_prompts = $this->pdo->fetchAll($sql);

            if ($all_prompts) {
                foreach ($all_prompts as $prompt_row) {
                    $key = $prompt_row['key'];
                    // If this prompt key exists in the bind data, update it
                    if (isset($bind[$key])) {
                        $sql =
                            'UPDATE bot_ai_prompts SET prompt_text = :text, updated_at = NOW() WHERE `key` = :key';
                        $result = $this->pdo->perform($sql, ['key' => $key, 'text' => $bind[$key]]);
                        if ($result) {
                            $updated++;
                        } else {
                            $errors[] = $key;
                        }
                    }
                }
            }

            if ($updated > 0 && count($errors) == 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' =>
                        'Configuración de IA actualizada correctamente. (' . $updated . ' campos)',
                ];
            } elseif ($updated > 0 && count($errors) > 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'warning',
                    'msg' =>
                        'Se actualizaron ' .
                        $updated .
                        ' campos. Errores en: ' .
                        implode(', ', $errors),
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar configuración de IA.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function get_ai_prompts()
    {
        try {
            $sql = 'SELECT id, `key`, prompt_text, description, context, display_order, is_active, usage_count
                    FROM bot_ai_prompts
                    ORDER BY
                        CASE WHEN context IS NULL THEN 0 ELSE 1 END,
                        context,
                        display_order,
                        id';
            $prompts = $this->pdo->fetchAll($sql);

            $response = [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Prompts obtenidos correctamente.',
                'data' => $prompts,
            ];
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }

    // --
    public function get_ai_prompt($id)
    {
        try {
            $sql = 'SELECT id, `key`, prompt_text, description, context, display_order,
                           model, max_tokens, temperature, is_active
                    FROM bot_ai_prompts
                    WHERE id = :id';
            $prompt = $this->pdo->fetchOne($sql, ['id' => $id]);

            if ($prompt) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Prompt obtenido correctamente.',
                    'data' => $prompt,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Prompt no encontrado.',
                    'data' => null,
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
                'data' => null,
            ];
        }

        return $response;
    }

    // --
    public function create_ai_prompt($bind)
    {
        try {
            // Check if key already exists
            $sql = 'SELECT id FROM bot_ai_prompts WHERE `key` = :key';
            $exists = $this->pdo->fetchOne($sql, ['key' => $bind['key']]);

            if ($exists) {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'Ya existe un prompt con ese key.',
                ];
                return $response;
            }

            // Insert new prompt
            $sql = 'INSERT INTO bot_ai_prompts
                    (`key`, prompt_text, description, context, display_order, model, max_tokens, temperature, is_active, created_at, updated_at)
                    VALUES
                    (:key, :prompt_text, :description, :context, :display_order, :model, :max_tokens, :temperature, :is_active, NOW(), NOW())';

            $params = [
                'key' => $bind['key'],
                'prompt_text' => $bind['prompt_text'],
                'description' => isset($bind['description']) ? $bind['description'] : null,
                'context' => isset($bind['context']) ? $bind['context'] : null,
                'display_order' => isset($bind['display_order']) ? $bind['display_order'] : 0,
                'model' =>
                    isset($bind['model']) && $bind['model'] !== null && $bind['model'] !== ''
                        ? $bind['model']
                        : null,
                'max_tokens' =>
                    isset($bind['max_tokens']) &&
                    $bind['max_tokens'] !== null &&
                    $bind['max_tokens'] !== ''
                        ? intval($bind['max_tokens'])
                        : null,
                'temperature' =>
                    isset($bind['temperature']) &&
                    $bind['temperature'] !== null &&
                    $bind['temperature'] !== ''
                        ? floatval($bind['temperature'])
                        : null,
                'is_active' => isset($bind['is_active']) ? $bind['is_active'] : 1,
            ];

            $result = $this->pdo->perform($sql, $params);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Prompt creado correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al crear el prompt.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function update_ai_prompt($bind)
    {
        try {
            $sql = 'UPDATE bot_ai_prompts
                    SET prompt_text = :prompt_text,
                        description = :description,
                        context = :context,
                        display_order = :display_order,
                        model = :model,
                        max_tokens = :max_tokens,
                        temperature = :temperature,
                        is_active = :is_active,
                        updated_at = NOW()
                    WHERE id = :id';

            $params = [
                'id' => $bind['id'],
                'prompt_text' => $bind['prompt_text'],
                'description' => isset($bind['description']) ? $bind['description'] : null,
                'context' => isset($bind['context']) ? $bind['context'] : null,
                'display_order' => isset($bind['display_order']) ? $bind['display_order'] : 0,
                'model' =>
                    isset($bind['model']) && $bind['model'] !== null && $bind['model'] !== ''
                        ? $bind['model']
                        : null,
                'max_tokens' =>
                    isset($bind['max_tokens']) &&
                    $bind['max_tokens'] !== null &&
                    $bind['max_tokens'] !== ''
                        ? intval($bind['max_tokens'])
                        : null,
                'temperature' =>
                    isset($bind['temperature']) &&
                    $bind['temperature'] !== null &&
                    $bind['temperature'] !== ''
                        ? floatval($bind['temperature'])
                        : null,
                'is_active' => isset($bind['is_active']) ? $bind['is_active'] : 1,
            ];

            $result = $this->pdo->perform($sql, $params);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Prompt actualizado correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar el prompt.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function delete_ai_prompt($id)
    {
        try {
            // Hard delete - permanently remove from database
            $sql = 'DELETE FROM bot_ai_prompts WHERE id = :id';
            $result = $this->pdo->perform($sql, ['id' => $id]);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Prompt eliminado correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al eliminar el prompt.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ========== INFRASTRUCTURE CONFIG ==========

    // --
    public function get_infrastructure_config()
    {
        try {
            $data = [];

            // All from security_config (11 campos: 7 Redis + 3 CORS + 1 Proxy)
            $infra_keys = [
                'redis_enabled',
                'redis_host',
                'redis_port',
                'redis_db',
                'redis_default_ttl',
                'cache_ttl_general',
                'cache_ttl_config_ms',
                'allowed_cors_origin_graph',
                'allowed_cors_origin_facebook',
                'allowed_cors_origin_business',
                'trust_proxy',
            ];

            foreach ($infra_keys as $key) {
                $sql = 'SELECT value FROM security_config WHERE `key` = :key';
                $row = $this->pdo->fetchOne($sql, ['key' => $key]);
                if ($row) {
                    $data[$key] = $row['value'];
                }
            }

            $response = [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Configuración de infraestructura obtenida correctamente.',
                'data' => $data,
            ];
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function update_infrastructure_config($bind)
    {
        try {
            $updated = 0;
            $errors = [];

            foreach ($bind as $key => $value) {
                $sql = 'UPDATE security_config SET value = :value WHERE `key` = :key';
                $result = $this->pdo->perform($sql, ['key' => $key, 'value' => $value]);
                if ($result) {
                    $updated++;
                } else {
                    $errors[] = $key;
                }
            }

            if ($updated > 0 && count($errors) == 0) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' =>
                        'Configuración de infraestructura actualizada correctamente. (' .
                        $updated .
                        ' campos)',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar configuración de infraestructura.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ========== EMAIL TEMPLATES ==========

    // --
    public function get_email_templates()
    {
        try {
            $sql = 'SELECT * FROM email_templates ORDER BY id';
            $result = $this->pdo->fetchAll($sql, []);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Plantillas de email obtenidas correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontraron plantillas de email.',
                    'data' => [],
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function get_email_template($bind)
    {
        try {
            $sql = 'SELECT * FROM email_templates WHERE id = :id';
            $result = $this->pdo->fetchOne($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Plantilla obtenida correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Plantilla no encontrada.',
                    'data' => null,
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function update_email_template($bind)
    {
        try {
            $sql =
                'UPDATE email_templates SET subject = :subject, content = :content, active = :active WHERE id = :id';
            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Plantilla actualizada correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar plantilla.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // ========== EMAIL DOMAINS ==========

    // --
    public function get_email_domains()
    {
        try {
            $sql = 'SELECT * FROM bot_email_domains ORDER BY domain';
            $result = $this->pdo->fetchAll($sql, []);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Dominios de email obtenidos correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'warning',
                    'msg' => 'No se encontraron dominios de email.',
                    'data' => [],
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function get_email_domain($bind)
    {
        try {
            $sql = 'SELECT * FROM bot_email_domains WHERE id = :id';
            $result = $this->pdo->fetchOne($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Dominio obtenido correctamente.',
                    'data' => $result,
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Dominio no encontrado.',
                    'data' => null,
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function create_email_domain($bind)
    {
        try {
            $sql = 'INSERT INTO bot_email_domains (domain, description, is_allowed, is_corporate, requires_verification, created_at)
                    VALUES (:domain, :description, :is_allowed, :is_corporate, 1, NOW())';
            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Dominio creado correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al crear dominio.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function update_email_domain($bind)
    {
        try {
            $sql =
                'UPDATE bot_email_domains SET domain = :domain, description = :description, is_allowed = :is_allowed, is_corporate = :is_corporate WHERE id = :id';
            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Dominio actualizado correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al actualizar dominio.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }

    // --
    public function delete_email_domain($bind)
    {
        try {
            $sql = 'DELETE FROM bot_email_domains WHERE id = :id';
            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'type' => 'success',
                    'msg' => 'Dominio eliminado correctamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'type' => 'error',
                    'msg' => 'Error al eliminar dominio.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }

        return $response;
    }
}
