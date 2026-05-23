<?php
// Attendance Settings Model - Attendance System
class M_Attendance_Settings extends Model {
    
    public function __construct() {
        parent::__construct();
    }

    public function get_setting($bind) {
        try {
            $sql = 'SELECT setting_value FROM attendance_settings 
                    WHERE setting_key = :setting_key';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bind);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $response = array('status' => 'OK', 'result' => $result['setting_value']);
            } else {
                $response = array('status' => 'ERROR', 'result' => null);
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        
        return $response;
    }

    public function get_all_settings() {
        try {
            $sql = 'SELECT setting_key, setting_value FROM attendance_settings';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($result) {
                $settings = array();
                foreach ($result as $row) {
                    $settings[$row['setting_key']] = $row['setting_value'];
                }
                $response = array('status' => 'OK', 'result' => $settings);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        
        return $response;
    }

    public function update_setting($bind) {
        try {
            $sql = 'UPDATE attendance_settings 
                    SET setting_value = :setting_value, updated_at = CURRENT_TIMESTAMP
                    WHERE setting_key = :setting_key';
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($bind);
            
            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        
        return $response;
    }
}
