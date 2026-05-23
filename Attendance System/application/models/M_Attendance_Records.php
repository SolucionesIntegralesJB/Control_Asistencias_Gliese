<?php
// Attendance Records Model - Attendance System
class M_Attendance_Records extends Model {
    
    public function __construct() {
        parent::__construct();
    }

    public function create_record($bind) {
        try {
            $sql = 'INSERT INTO attendance_records 
                    (shift_id, record_type, record_time, location, ip_address, user_agent)
                    VALUES 
                    (:shift_id, :record_type, :record_time, :location, :ip_address, :user_agent)';
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($bind);
            
            if ($result) {
                $record_id = $this->pdo->lastInsertId();
                $response = array('status' => 'OK', 'result' => array('record_id' => $record_id));
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        
        return $response;
    }

    public function get_records_by_shift($bind) {
        try {
            $sql = 'SELECT * FROM attendance_records 
                    WHERE shift_id = :shift_id 
                    ORDER BY record_time ASC';
            
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

    public function get_last_record_by_type($bind) {
        try {
            $sql = 'SELECT * FROM attendance_records 
                    WHERE shift_id = :shift_id AND record_type = :record_type
                    ORDER BY record_time DESC 
                    LIMIT 1';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bind);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
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

    public function has_break_started($bind) {
        try {
            $sql = 'SELECT COUNT(*) as count FROM attendance_records 
                    WHERE shift_id = :shift_id AND record_type = :record_type';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bind);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $response = array('status' => 'OK', 'result' => $result['count'] > 0);
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => false);
        }
        
        return $response;
    }

    public function has_break_ended($bind) {
        try {
            $sql = 'SELECT COUNT(*) as count FROM attendance_records 
                    WHERE shift_id = :shift_id AND record_type = :record_type';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bind);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $response = array('status' => 'OK', 'result' => $result['count'] > 0);
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => false);
        }
        
        return $response;
    }
}
