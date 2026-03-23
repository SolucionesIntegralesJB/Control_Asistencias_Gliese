<?php
// --
class M_TwoFactor extends Model {

    // --
    public function __construct() {
        parent::__construct();
    }

    // ============================================
    // SWITCH GLOBAL ON/OFF (para mantenimiento)
    // ============================================

    // Obtener estado del switch global (desde student_config para practicantes)
    public function get_2fa_config() {
        try {
            // Lee desde student_config para controlar 2FA de practicantes
            $sql = 'SELECT `value` FROM student_config WHERE `key` = "twofactor_email_enabled" LIMIT 1';
            $result = $this->pdo->fetchAll($sql);

            if (!empty($result)) {
                $is_enabled = ($result[0]['value'] == '1');
            } else {
                // Default: desactivado
                $is_enabled = false;
            }

            return array(
                'status' => 'OK',
                'result' => array(
                    'is_enabled' => $is_enabled
                )
            );
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Activar 2FA GLOBAL (actualiza student_config)
    public function enable_2fa() {
        try {
            // Actualizar/insertar en student_config
            $query = "INSERT INTO student_config (`key`, `value`)
                     VALUES ('twofactor_email_enabled', '1')
                     ON DUPLICATE KEY UPDATE `value` = '1'";
            $this->pdo->perform($query, array());

            return array(
                'status' => 'OK',
                'type' => 'success',
                'msg' => '2FA activado para TODO el sistema de practicantes. Los practicantes recibirán códigos por email.'
            );
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Desactivar 2FA GLOBAL (actualiza student_config)
    public function disable_2fa() {
        try {
            // Actualizar/insertar en student_config
            $query = "INSERT INTO student_config (`key`, `value`)
                     VALUES ('twofactor_email_enabled', '0')
                     ON DUPLICATE KEY UPDATE `value` = '0'";
            $this->pdo->perform($query, array());

            return array(
                'status' => 'OK',
                'type' => 'success',
                'msg' => '2FA desactivado para TODO el sistema de practicantes.'
            );
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // ============================================
    // AUTENTICACIÓN POR EMAIL (Códigos temporales de 6 dígitos)
    // ============================================

    // Generar y guardar código temporal de 6 dígitos
    public function generate_email_code($bind) {
        try {
            // Generar código de 6 dígitos
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

            // Eliminar códigos anteriores del usuario
            $delete_query = "DELETE FROM user_2fa_email_codes WHERE id_user = :id_user";
            $this->pdo->perform($delete_query, array('id_user' => $bind['id_user']));

            // Guardar código (expira en 5 minutos)
            $insert_query = "INSERT INTO user_2fa_email_codes (id_user, code, expires_at)
                            VALUES (:id_user, :code, DATE_ADD(NOW(), INTERVAL 5 MINUTE))";
            $this->pdo->perform($insert_query, array(
                'id_user' => $bind['id_user'],
                'code' => $code
            ));

            return array(
                'status' => 'OK',
                'code' => $code
            );
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Verificar código de email
    public function verify_email_code($bind) {
        try {
            // Buscar código válido y no expirado
            $query = "SELECT id FROM user_2fa_email_codes
                     WHERE id_user = :id_user
                     AND code = :code
                     AND expires_at > NOW()
                     AND is_used = 0
                     LIMIT 1";

            $result = $this->pdo->fetchAll($query, array(
                'id_user' => $bind['id_user'],
                'code' => $bind['code']
            ));

            if (empty($result)) {
                return array('status' => 'ERROR', 'msg' => 'Código inválido o expirado.');
            }

            // Marcar código como usado
            $update_query = "UPDATE user_2fa_email_codes SET is_used = 1 WHERE id = :id";
            $this->pdo->perform($update_query, array('id' => $result[0]['id']));

            return array('status' => 'OK', 'msg' => 'Código válido.');
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Verificar si el usuario necesita configurar 2FA por primera vez
    public function is_first_time_2fa($bind) {
        try {
            $query = "SELECT id FROM user_2fa_config WHERE id_user = :id_user AND is_enabled = 1 LIMIT 1";
            $result = $this->pdo->fetchAll($query, array('id_user' => $bind['id_user']));

            if (empty($result)) {
                // Primera vez, crear registro
                $insert_query = "INSERT INTO user_2fa_config (id_user, is_enabled) VALUES (:id_user, 1)";
                $this->pdo->perform($insert_query, array('id_user' => $bind['id_user']));
                return true;
            }

            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Enviar código por email
    public function send_code_by_email($bind) {
        try {
            // Obtener configuración SMTP desde la tabla de configuración (mismo método que usan los módulos Pendiente/Asignacion)
            $config_resp = $this->get_smtp_config();
            if ($config_resp['status'] !== 'OK') {
                return array('status' => 'ERROR', 'msg' => 'No hay configuración SMTP disponible.');
            }

            $config = $config_resp['result'];

            // Verificar si el envío automático de correos está activado
            if (empty($config['auto_email_sending']) || $config['auto_email_sending'] == '0') {
                return array('status' => 'DISABLED', 'msg' => 'Envío de correos deshabilitado');
            }

            require_once __DIR__ . '/../../vendor/autoload.php';

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Configuración SMTP desde BD
            $mail->isSMTP();
            $mail->Host = $config['smtp_server'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_user'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = intval($config['smtp_port']);
            $mail->CharSet = 'UTF-8';

            // Remitente y destinatario
            $senderEmail = !empty($config['smtp_user']) ? $config['smtp_user'] : 'noreply@gliese.com';
            $senderName = !empty($config['sender_name']) ? $config['sender_name'] : 'Sistema Gliese - Practicantes';

            $mail->setFrom($senderEmail, $senderName);
            $mail->addAddress($bind['email']);

            // Contenido del email
            $mail->isHTML(true);
            $mail->Subject = 'Código de verificación 2FA - Sistema Gliese';
            $mail->Body = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                    <h2 style="color: #7367f0;">🔐 Código de Verificación</h2>
                    <p>Hola,</p>
                    <p>Tu código de autenticación de doble factor es:</p>
                    <div style="background: #f3f2f7; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;">
                        <h1 style="color: #7367f0; font-size: 48px; margin: 0; letter-spacing: 8px;">' . $bind['code'] . '</h1>
                    </div>
                    <p><strong>Este código expira en 5 minutos.</strong></p>
                    <p>Si no solicitaste este código, ignora este mensaje.</p>
                    <hr style="border: 1px solid #eee; margin: 30px 0;">
                    <p style="color: #999; font-size: 12px;">Sistema de Practicantes Gliese<br>Este es un mensaje automático, por favor no responder.</p>
                </div>
            ';
            $mail->AltBody = 'Tu código de verificación 2FA es: ' . $bind['code'] . '. Este código expira en 5 minutos.';

            $mail->send();

            return array(
                'status' => 'OK',
                'msg' => 'Código enviado a tu correo electrónico.'
            );
        } catch (\Exception $e) {
            error_log("Error enviando email 2FA: " . $e->getMessage());
            return array(
                'status' => 'ERROR',
                'msg' => 'No se pudo enviar el email. Contacta al administrador.'
            );
        }
    }

    // Obtener configuración SMTP (misma lógica que M_S_Pending)
    private function get_smtp_config()
    {
        try {
            $sql = 'SELECT `key`, `value` FROM student_config WHERE `key` IN (
                "smtp_server", "smtp_port", "smtp_user", "smtp_password", "sender_name", "auto_email_sending"
              )';
            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                $config = [];
                foreach ($result as $row) {
                    $config[$row['key']] = $row['value'];
                }
                return ['status' => 'OK', 'result' => $config];
            } else {
                return ['status' => 'ERROR', 'msg' => 'No se encontró configuración SMTP'];
            }
        } catch (PDOException $e) {
            return ['status' => 'ERROR', 'msg' => $e->getMessage()];
        }
    }

    // ============================================
    // CÓDIGOS DE EMERGENCIA (BACKUP CODES)
    // ============================================

    // Generar códigos de emergencia (10 códigos)
    public function generate_backup_codes($bind) {
        try {
            // Eliminar códigos anteriores
            $this->delete_backup_codes($bind);

            $codes = array();

            for ($i = 0; $i < 10; $i++) {
                // Formato: XXXX-XXXX (ej: A7F2-8K9P)
                $code = strtoupper(substr(md5(random_bytes(10)), 0, 4) . '-' . substr(md5(random_bytes(10)), 0, 4));

                $insert_bind = array(
                    'id_user' => $bind['id_user'],
                    'code' => $code
                );

                $query = "INSERT INTO user_2fa_codes (id_user, code) VALUES (:id_user, :code)";
                $this->pdo->perform($query, $insert_bind);

                $codes[] = $code;
            }

            return $codes;
        } catch (PDOException $e) {
            return array();
        }
    }

    // Obtener códigos de emergencia
    public function get_backup_codes($bind) {
        try {
            $query = "SELECT code, is_used, used_at FROM user_2fa_codes WHERE id_user = :id_user ORDER BY id";
            $result = $this->pdo->fetchAll($query, $bind);

            if (empty($result)) {
                return array('status' => 'ERROR', 'msg' => 'No hay códigos de emergencia.');
            }

            return array(
                'status' => 'OK',
                'result' => $result
            );
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Eliminar códigos de emergencia
    public function delete_backup_codes($bind) {
        try {
            $query = "DELETE FROM user_2fa_codes WHERE id_user = :id_user";
            $this->pdo->perform($query, $bind);
            return array('status' => 'OK');
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Validar código de emergencia
    public function validate_backup_code($bind) {
        try {
            $query = "SELECT id FROM user_2fa_codes
                      WHERE id_user = :id_user
                      AND code = :code
                      AND is_used = 0";

            $result = $this->pdo->fetchAll($query, $bind);

            if (empty($result)) {
                return array('status' => 'ERROR', 'msg' => 'Código inválido o ya usado.');
            }

            // Marcar código como usado
            $update_query = "UPDATE user_2fa_codes SET is_used = 1, used_at = CURRENT_TIMESTAMP WHERE id = :id";
            $this->pdo->perform($update_query, array('id' => $result[0]['id']));

            return array('status' => 'OK', 'msg' => 'Código válido.');
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Actualizar cantidad de códigos de emergencia
    public function update_backup_codes_quantity($bind) {
        try {
            $quantity = isset($bind['backup_codes_quantity']) ? intval($bind['backup_codes_quantity']) : 10;

            // Actualizar en student_config (para el sistema de practicantes)
            $query = "INSERT INTO student_config (`key`, `value`)
                     VALUES ('backup_codes_quantity', :quantity)
                     ON DUPLICATE KEY UPDATE `value` = :quantity";

            $this->pdo->perform($query, array('quantity' => $quantity));

            return array(
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Cantidad de códigos actualizada correctamente.'
            );
        } catch (PDOException $e) {
            return array('status' => 'ERROR', 'type' => 'error', 'msg' => $e->getMessage());
        }
    }

    // ============================================
    // TIEMPO DE EXPIRACIÓN DE CÓDIGOS 2FA
    // ============================================

    // Obtener tiempo de expiración desde student_config
    public function get_code_expiration_time() {
        try {
            $sql = 'SELECT `value` FROM student_config WHERE `key` = "2fa_code_expiration_minutes" LIMIT 1';
            $result = $this->pdo->fetchAll($sql);

            if (!empty($result)) {
                $minutes = intval($result[0]['value']);
                return array(
                    'status' => 'OK',
                    'result' => array('expiration_minutes' => $minutes)
                );
            } else {
                // Default: 5 minutos
                return array(
                    'status' => 'OK',
                    'result' => array('expiration_minutes' => 5)
                );
            }
        } catch (PDOException $e) {
            return array('status' => 'ERROR', 'msg' => $e->getMessage());
        }
    }

    // Guardar tiempo de expiración en student_config
    public function save_code_expiration_time($bind) {
        try {
            $minutes = intval($bind['expiration_minutes']);

            // Actualizar/insertar en student_config
            $query = "INSERT INTO student_config (`key`, `value`)
                     VALUES ('2fa_code_expiration_minutes', :minutes)
                     ON DUPLICATE KEY UPDATE `value` = :minutes";

            $this->pdo->perform($query, array('minutes' => $minutes));

            return array(
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Tiempo de expiración actualizado correctamente a ' . $minutes . ' minutos.'
            );
        } catch (PDOException $e) {
            return array('status' => 'ERROR', 'type' => 'error', 'msg' => $e->getMessage());
        }
    }
}
