<?php
// Login Model - Attendance System
class M_Login extends Model {
    
    public function __construct() {
        parent::__construct();
    }

    public function get_user($bind) {
        try {
            $sql = 'SELECT
                    e.id,
                    e.name,
                    e.email,
                    e.password,
                    e.status,
                    e.role_person_id,
                    e.position,
                    e.work_area
                FROM employees e
                WHERE
                    e.email = :email AND
                    e.status = 1';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(array('email' => $bind['email']));
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                // Verificar contraseña usando password_verify
                if (password_verify($bind['password'], $result[0]['password'])) {
                    // Actualizar last_login
                    $update_sql = 'UPDATE employees SET last_login = NOW() WHERE id = :id';
                    $update_stmt = $this->pdo->prepare($update_sql);
                    $update_stmt->execute(array('id' => $result[0]['id']));

                    $response = array('status' => 'OK', 'result' => $result);
                } else {
                    $response = array('status' => 'ERROR', 'result' => array());
                }
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        return $response;
    }
}
