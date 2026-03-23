<?php
// --
class M_S_Assignment extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function get_students_accepted()
    {
        try {
            $sql = 'SELECT
                    s.id,
                    s.first_names,
                    s.last_names,
                    s.document_number,
                    s.email,
                    s.phone,
                    s.institution,
                    s.specialty,
                    s.interest_area
                FROM students s
                WHERE s.application_status = "aceptado"
                AND s.has_credentials = 0
                ORDER BY s.first_names ASC';
            $result = $this->pdo->fetchAll($sql);
            if ($result) {
                $response = ['status' => 'OK', 'result' => $result];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        return $response;
    }

    // --
    public function get_groups_active()
    {
        /**
         * Obtiene todos los grupos de estudiantes activos.
         * Devuelve el código, nombre, capacidad máxima, miembros actuales y espacios disponibles.
         */
        try {
            $sql = 'SELECT
                        id,
                        group_code,
                        group_name,
                        group_type,
                        max_capacity,
                        current_members,
                        (max_capacity - current_members) AS available_spaces
                    FROM student_groups
                    WHERE status = 1
                    ORDER BY group_name ASC';
            $result = $this->pdo->fetchAll($sql);
            if ($result) {
                $response = ['status' => 'OK', 'result' => $result];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        return $response;
    }

    // -- Obtiene los roles activos
    public function get_roles_active()
    {
        // Obtiene los roles activos de tipo Practicante o Supervisor desde user_roles.
        // Devuelve el id y la descripción de cada rol.
        try {
            $sql = 'SELECT id, description
                    FROM user_roles
                    WHERE status = 1 AND (UPPER(TRIM(description)) = "PRACTICANTE" OR UPPER(TRIM(description)) = "SUPERVISOR")
                    ORDER BY description ASC';
            $result = $this->pdo->fetchAll($sql);
            if ($result) {
                $response = ['status' => 'OK', 'result' => $result];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        return $response;
    }

    // -- Genera una contraseña aleatoria
    private function generate_random_password($length = 8)
    {
        // Genera una contraseña aleatoria de la longitud especificada.
        // Utiliza letras (mayúsculas y minúsculas) y números, excluyendo caracteres ambiguos.
        $characters = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $password;
    }

    // -- Verifica que el username sea único
    private function check_unique_username($username)
    {
        // Verifica si el nombre de usuario es único en la tabla user.
        // Retorna true si el username no existe, false en caso contrario o si ocurre un error.
        try {
            $sql = 'SELECT COUNT(*) as total FROM user WHERE user = :username';
            $result = $this->pdo->fetchOne($sql, ['username' => $username]);
            return $result['total'] == 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    // --
    public function assign_student($data)
    {
        try {
            /**
             * Asigna un estudiante a un grupo, crea su usuario y actualiza la información correspondiente.
             * - Obtiene los datos del estudiante y del grupo.
             * - Verifica la capacidad del grupo y la unicidad del username.
             * - Genera y encripta una contraseña aleatoria.
             * - Inserta el usuario en la tabla user.
             * - Asigna campus al usuario si corresponde.
             * - Actualiza el registro del estudiante con el usuario, grupo y credenciales.
             * - Incrementa el contador de miembros del grupo.
             * Retorna el username, la contraseña generada y el nombre del grupo asignado.
             */

            // Obtener datos del estudiante
            $sql_student = 'SELECT * FROM students WHERE id = :id';
            $student = $this->pdo->fetchOne($sql_student, ['id' => $data['id_student']]);

            if (!$student) {
                return ['status' => 'ERROR', 'msg' => 'Estudiante no encontrado'];
            }

            // Obtener datos del grupo
            $sql_group = 'SELECT * FROM student_groups WHERE id = :id';
            $group = $this->pdo->fetchOne($sql_group, ['id' => $data['id_group']]);

            if (!$group) {
                return ['status' => 'ERROR', 'msg' => 'Grupo no encontrado'];
            }

            // Verificar capacidad del grupo
            if ($group['current_members'] >= $group['max_capacity']) {
                return ['status' => 'ERROR', 'msg' => 'El grupo ha alcanzado su capacidad máxima'];
            }

            // Determinar el user_role según el tipo de grupo
            if ($group['group_type'] === 'SUPERVISOR') {
                // Obtener el id del user_role SUPERVISOR
                $sql_user_role =
                    'SELECT id FROM user_roles WHERE UPPER(TRIM(description)) = "SUPERVISOR" LIMIT 1';
                $role_result = $this->pdo->fetchOne($sql_user_role);
                $id_user_role = $role_result
                    ? $role_result['id']
                    : (isset($data['id_user_role'])
                        ? $data['id_user_role']
                        : null);
            } else {
                // Obtener el id del user_role PRACTICANTE
                $sql_user_role =
                    'SELECT id FROM user_roles WHERE UPPER(TRIM(description)) = "PRACTICANTE" LIMIT 1';
                $role_result = $this->pdo->fetchOne($sql_user_role);
                $id_user_role = $role_result
                    ? $role_result['id']
                    : (isset($data['id_user_role'])
                        ? $data['id_user_role']
                        : null);
            }

            // id_role básico (puede ser cualquier rol base, por ejemplo "ESTUDIANTE" o NULL)
            $id_role = isset($data['id_role']) ? $data['id_role'] : 5; // 5 = rol base estudiante

            // Generar username (DNI)
            $username = $student['document_number'];

            // Verificar que el username sea único
            if (!$this->check_unique_username($username)) {
                return ['status' => 'ERROR', 'msg' => 'El DNI ya está registrado como usuario'];
            }

            // Generar password aleatorio
            $password_plain = $this->generate_random_password(8);

            // Encriptar password usando la función del sistema
            $functions = new Functions();
            $password_encrypted = $functions->encrypt_password($password_plain);

            // Insertar usuario en la tabla user
            $sql_insert_user = 'INSERT INTO user (
                id_role,
                id_user_role,
                id_document_type,
                first_name,
                last_name,
                document_number,
                address,
                telephone,
                email,
                user,
                password,
                status,
                active
            ) VALUES (
                :id_role,
                :id_user_role,
                :id_document_type,
                :first_name,
                :last_name,
                :document_number,
                :address,
                :telephone,
                :email,
                :user,
                :password,
                1,
                1
            )';

            $user_data = [
                'id_role' => $id_role,
                'id_user_role' => $id_user_role,
                'id_document_type' => $student['document_type_id'],
                'first_name' => $student['first_names'],
                'last_name' => $student['last_names'],
                'document_number' => $student['document_number'],
                'address' => $student['address'],
                'telephone' => $student['phone'],
                'email' => $student['email'],
                'user' => $username,
                'password' => $password_encrypted,
            ];

            $result_user = $this->pdo->perform($sql_insert_user, $user_data);

            if (!$result_user) {
                return ['status' => 'ERROR', 'msg' => 'Error al crear el usuario'];
            }

            // Obtener el ID del usuario creado
            $id_user = $this->pdo->lastInsertId();

            // Obtener horas requeridas desde student_config
            $sql_hours = 'SELECT `value` FROM student_config WHERE `key` = :key';
            $hours_result = $this->pdo->fetchOne($sql_hours, ['key' => 'min_required_hours']);
            $required_hours = $hours_result ? (int) $hours_result['value'] : 0;

            // Actualizar estudiante con id_user, grupo, credenciales, fecha de inicio y horas requeridas
            $sql_update_student = 'UPDATE students SET
                user_id = :user_id,
                `group` = :group,
                has_credentials = 1,
                group_assignment_date = NOW(),
                start_date = CURDATE(),
                required_hours = :required_hours,
                assigned_by = :assigned_by
                WHERE id = :id_student';

            $update_data = [
                'user_id' => $id_user,
                'group' => $group['group_code'],
                'required_hours' => $required_hours,
                'assigned_by' => isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null,
                'id_student' => $data['id_student'],
            ];

            $result_student = $this->pdo->perform($sql_update_student, $update_data);

            if (!$result_student) {
                return ['status' => 'ERROR', 'msg' => 'Error al actualizar el estudiante'];
            }

            // Actualizar contador de miembros del grupo
            $sql_update_group = 'UPDATE student_groups SET
                current_members = current_members + 1
                WHERE id = :id_group';

            $this->pdo->perform($sql_update_group, ['id_group' => $data['id_group']]);

            // Actualizar group_assigned en user_state (busca por phone o email)
            $sql_update_state = 'UPDATE user_state SET group_assigned = 1
                WHERE phone LIKE :phone OR email = :email';
            $this->pdo->perform($sql_update_state, [
                'phone' => '%' . $student['phone'],
                'email' => $student['email'],
            ]);

            // Enviar correo con credenciales
            $email_data = [
                'email' => $student['email'],
                'first_names' => $student['first_names'],
                'last_names' => $student['last_names'],
                'username' => $username,
                'password' => $password_plain,
                'group' => $group['group_name'],
                'start_date' => date('d/m/Y'),
            ];

            // Intentar enviar email y registrar resultado
            $email_result = $this->send_credentials_email($email_data);
            error_log('[M_S_Assignment] Email result: ' . json_encode($email_result));

            $response = [
                'status' => 'OK',
                'result' => [
                    'username' => $username,
                    'password' => $password_plain,
                    'group' => $group['group_name'],
                ],
            ];
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        return $response;
    }
    // --

    public function get_credentials_template()
    {
        try {
            // Obtiene la plantilla de correo para credenciales.
            // Retorna el asunto y el contenido de la plantilla de tipo "credenciales".
            $sql = 'SELECT subject, content FROM email_templates WHERE type = "credenciales"';
            $result = $this->pdo->fetchOne($sql);

            if ($result) {
                $response = ['status' => 'OK', 'result' => $result];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        return $response;
    }

    // --
    public function update_credentials_template($data)
    {
        try {
            // Actualiza la plantilla de correo para credenciales.
            // Recibe los nuevos valores de asunto y contenido para la plantilla de tipo "credenciales".
            // Retorna el estado de la operación.
            $sql = 'UPDATE email_templates SET
                        subject = :subject,
                        content = :content
                    WHERE type = "credenciales"';

            $result = $this->pdo->perform($sql, $data);

            if ($result) {
                $response = ['status' => 'OK', 'result' => []];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        return $response;
    }

    // -- Enviar correo con credenciales
    private function send_credentials_email($data)
    {
        try {
            // Obtener configuración SMTP desde security_config
            $sql_smtp = 'SELECT `key`, `value` FROM security_config WHERE `key` IN (
                "smtp_server", "smtp_port", "email_user", "email_password", "sender_name"
            )';
            $smtp_result = $this->pdo->fetchAll($sql_smtp);

            // Obtener configuración del sistema desde student_config
            $sql_system = 'SELECT `key`, `value` FROM student_config WHERE `key` IN (
                "min_required_hours", "system_url"
            )';
            $system_result = $this->pdo->fetchAll($sql_system);

            if (!$smtp_result) {
                return ['status' => 'ERROR', 'msg' => 'No se encontró configuración SMTP'];
            }

            $config = [];
            foreach ($smtp_result as $row) {
                $config[$row['key']] = $row['value'];
            }
            foreach ($system_result as $row) {
                $config[$row['key']] = $row['value'];
            }

            // Desencriptar la contraseña de email usando AES-256-GCM
            if (isset($config['email_password']) && !empty($config['email_password'])) {
                $env_path = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . '.env';
                if (file_exists($env_path)) {
                    $env_content = file_get_contents($env_path);
                    if (preg_match('/ENCRYPTION_KEY\s*=\s*["\']?([a-f0-9]{64})["\']?/i', $env_content, $matches)) {
                        $key = hex2bin($matches[1]);
                        if ($key !== false && strlen($key) === 32 && strpos($config['email_password'], ':') !== false) {
                            $parts = explode(':', $config['email_password']);
                            if (count($parts) === 3) {
                                $iv = hex2bin($parts[0]);
                                $tag = hex2bin($parts[1]);
                                $ciphertext = hex2bin($parts[2]);
                                if ($iv !== false && $tag !== false && $ciphertext !== false) {
                                    $decrypted = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
                                    if ($decrypted !== false) {
                                        $config['email_password'] = $decrypted;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Validar configuraciones SMTP requeridas
            $required_keys = ['smtp_server', 'smtp_port', 'email_user', 'email_password', 'sender_name'];
            $missing_keys = [];
            foreach ($required_keys as $key) {
                if (!isset($config[$key]) || empty($config[$key])) {
                    $missing_keys[] = $key;
                }
            }

            if (!empty($missing_keys)) {
                error_log('[M_S_Assignment] Faltan configuraciones SMTP: ' . implode(', ', $missing_keys));
                return [
                    'status' => 'ERROR',
                    'msg' => 'Configuración SMTP incompleta. Faltan: ' . implode(', ', $missing_keys)
                ];
            }

            // Obtener plantilla de correo
            $sql_template =
                'SELECT subject, content FROM email_templates WHERE type = "credenciales"';
            $template = $this->pdo->fetchOne($sql_template);

            if (!$template) {
                return ['status' => 'ERROR', 'msg' => 'No se encontró la plantilla de correo'];
            }

            // Reemplazar variables en la plantilla
            $replacements = [
                '{first_names}' => $data['first_names'],
                '{last_names}' => $data['last_names'],
                '{username}' => $data['username'],
                '{password}' => $data['password'],
                '{group}' => $data['group'],
                '{start_date}' => isset($data['start_date']) ? $data['start_date'] : date('d/m/Y'),
                '{required_hours}' => isset($config['min_required_hours'])
                    ? $config['min_required_hours']
                    : '480',
                '{url_sistema}' => isset($config['system_url'])
                    ? $config['system_url']
                    : BASE_URL,
            ];

            $subject = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $template['subject'],
            );

            // Convertir saltos de línea \n a <br> para formato HTML
            $content = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $template['content'],
            );
            $content = nl2br($content);

            // Configurar PHPMailer
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            $mail->isSMTP();
            $mail->Host = $config['smtp_server'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['email_user'];
            $mail->Password = $config['email_password'];
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)$config['smtp_port'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($config['email_user'], $config['sender_name']);
            $mail->addAddress($data['email'], $data['first_names'] . ' ' . $data['last_names']);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $content;

            $mail->send();
            return ['status' => 'OK', 'msg' => 'Correo enviado correctamente'];
        } catch (\Exception $e) {
            return ['status' => 'ERROR', 'msg' => 'Error al enviar correo: ' . $e->getMessage()];
        }
    }
}
