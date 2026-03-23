<?php
// --
class M_S_Pending extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function get_pending($status = 'pendiente')
    {
        // --
        try {
            // --
            $sql = 'SELECT
                    s.id,
                    s.first_names,
                    s.last_names,
                    s.document_number,
                    s.location,
                    dt.description AS document_type_desc,
                    s.email,
                    s.phone,
                    s.institution,
                    s.specialty,
                    s.cycle,
                    s.modality,
                    s.application_status,
                    DATE_FORMAT(s.created_at, "%d/%m/%Y") AS created_at
                FROM students s
                INNER JOIN document_type dt ON dt.id = s.document_type_id
                WHERE s.application_status = :status
                ORDER BY s.created_at DESC';
            // --
            $result = $this->pdo->fetchAll($sql, ['status' => $status]);
            // --
            if ($result) {
                $response = ['status' => 'OK', 'result' => $result];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        // --
        return $response;
    }

    // --
    public function get_student_by_id($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT
                    s.*,
                    s.location,  -- se refiere a "sede"
                    dt.description AS document_type_desc,
                    DATE_FORMAT(s.birth_date, "%d/%m/%Y") AS birth_date_formatted,
                    DATE_FORMAT(s.start_date, "%d/%m/%Y") AS start_date_formatted,
                    DATE_FORMAT(s.end_date, "%d/%m/%Y") AS end_date_formatted,
                    DATE_FORMAT(s.created_at, "%d/%m/%Y %H:%i") AS created_at_formatted
                FROM students s
                INNER JOIN document_type dt ON dt.id = s.document_type_id
                WHERE s.id = :id';
            // --
            $result = $this->pdo->fetchOne($sql, $bind);
            // --
            if ($result) {
                $response = ['status' => 'OK', 'result' => $result];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        // --
        return $response;
    }

    // --
    public function aceptar_solicitud($bind)
    {
        try {
            // Actualiza el estado y la plantilla usada
            $sql = 'UPDATE students SET
                    application_status = "aceptado",
                    approved_at = NOW(),
                    email_template_used = :template_used
                WHERE id = :id_student';
            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                // Obtén los datos actualizados del estudiante
                $student = $this->get_student_by_id(['id' => $bind['id_student']]);
                $student_data = $student['result'];

                // Envía el correo con los datos actualizados
                $email_result = $this->send_email_with_template('aceptado', $student_data);

                $response = ['status' => 'OK', 'result' => []];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        return $response;
    }

    // --
    public function rechazar_solicitud($bind)
    {
        try {
            // Actualiza el estado, remarks y la plantilla usada
            $sql = 'UPDATE students SET
                    application_status = "rechazado",
                    remarks = :remarks,
                    email_template_used = :template_used
                WHERE id = :id_student';
            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                // Obtén los datos actualizados del estudiante
                $student = $this->get_student_by_id(['id' => $bind['id_student']]);
                $student_data = $student['result'];

                // Envía el correo con el motivo actualizado
                $email_result = $this->send_email_with_template('rechazado', $student_data);

                $response = ['status' => 'OK', 'result' => []];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        return $response;
    }

    // --
    public function get_templates()
    {
        // --
        try {
            // --
            $sql = 'SELECT type, subject, content
                FROM email_templates
                WHERE type IN ("aceptado", "rechazado")';
            // --
            $result = $this->pdo->fetchAll($sql);
            // --
            if ($result) {
                // Organizar por tipo
                $templates = [];
                foreach ($result as $row) {
                    $templates[$row['type']] = [
                        'subject' => $row['subject'],
                        'content' => $row['content'],
                    ];
                }
                $response = ['status' => 'OK', 'result' => $templates];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        // --
        return $response;
    }

    // --
    public function update_templates($bind)
    {
        // --
        try {
            // --
            $sql = 'UPDATE email_templates SET
                    subject = :subject,
                    content = :content
                WHERE type = :type';
            // --
            $result = $this->pdo->perform($sql, $bind);
            // --
            if ($result) {
                $response = ['status' => 'OK', 'result' => []];
            } else {
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        // --
        return $response;
    }

    // -- //Cambiado para que concida con el correo
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

    // --
    private function send_email_with_template($type, $student_data)
    {
        // --
        try {
            // Obtener configuración SMTP
            $config_response = $this->get_smtp_config();
            if ($config_response['status'] !== 'OK') {
                return $config_response;
            }
            $config = $config_response['result'];

            // Verificar si el envío está habilitado
            if (!$config['auto_email_sending']) {
                return ['status' => 'DISABLED', 'msg' => 'Envío de correos deshabilitado'];
            }

            // Obtener plantilla
            $template_response = $this->get_templates();
            if ($template_response['status'] !== 'OK') {
                return ['status' => 'ERROR', 'msg' => 'No se encontró la plantilla'];
            }

            $templates = $template_response['result'];
            if (!isset($templates[$type])) {
                return ['status' => 'ERROR', 'msg' => 'Tipo de plantilla no encontrado'];
            }

            $template = $templates[$type];

            // Variables para reemplazar
            $variables = [
                '{first_names}' => isset($student_data['first_names'])
                    ? $student_data['first_names']
                    : '',
                '{last_names}' => isset($student_data['last_names'])
                    ? $student_data['last_names']
                    : '',
                '{institution}' => isset($student_data['institution'])
                    ? $student_data['institution']
                    : '',
                '{specialty}' => isset($student_data['specialty'])
                    ? $student_data['specialty']
                    : '',
                '{email}' => isset($student_data['email']) ? $student_data['email'] : '',
                '{interest_area}' => isset($student_data['interest_area'])
                    ? $student_data['interest_area']
                    : '',
                '{remarks}' => isset($student_data['remarks']) ? $student_data['remarks'] : '',
            ];

            // Reemplazar variables en asunto y contenido
            $subject = str_replace(
                array_keys($variables),
                array_values($variables),
                $template['subject'],
            );
            $content = str_replace(
                array_keys($variables),
                array_values($variables),
                $template['content'],
            );

            // Convierte saltos de línea en <br> para HTML
            $content = nl2br($content);

            // Configurar PHPMailer
            /** @var \PHPMailer\PHPMailer\PHPMailer $mail */
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Configuración del servidor
            $mail->isSMTP();
            $mail->Host = $config['smtp_server'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp_user'];
            $mail->Password = $config['smtp_password'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = $config['smtp_port'];
            $mail->CharSet = 'UTF-8';

            // Remitente y destinatario
            $mail->setFrom($config['smtp_user'], $config['sender_name']);
            $mail->addAddress(
                $student_data['email'],
                $student_data['first_names'] . ' ' . $student_data['last_names'],
            );

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $content;

            $mail->send();
            return ['status' => 'OK', 'msg' => 'Correo enviado correctamente'];
        } catch (Exception $e) {
            return ['status' => 'ERROR', 'msg' => 'Error al enviar correo: ' . $e->getMessage()];
        }
    }
}
