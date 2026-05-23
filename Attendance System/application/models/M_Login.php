<?php
// Login Model - Attendance System
class M_Login extends Model {
    
    public function __construct() {
        parent::__construct();
    }

    public function get_user($bind) {
        try {
            $sql = 'SELECT 
                    u.id,
                    u.id_role,
                    u.id_document_type, 
                    u.document_number,
                    u.first_name, 
                    u.last_name,
                    u.user,
                    u.email,
                    u.status
                FROM user u
                WHERE
                    u.user = :user AND 
                    u.password = :password AND
                    u.status = 1';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bind);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        
        return $response;
    }
}
