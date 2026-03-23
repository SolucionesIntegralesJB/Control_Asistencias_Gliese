<?php 
// --
class M_Technicals extends Model {
    // --
    public function __construct() {
		parent::__construct();
    }
    
    // --
	public function get_technicals() {
        // --
        try {
            // --
            $sql = 'SELECT 
                    c.id AS id_technicals,
                    c.id_document_type,
                    dt.description AS document_type,
                    c.name,
                    c.document_number,
                    c.phone,
                    c.area,
                    c.cargo,
                    c.technical_type,
                    c.status
                FROM technicals c
                INNER JOIN document_type dt ON dt.id = c.id_document_type';
            // --
            $result = $this->pdo->fetchAll($sql);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // --
    public function get_technicals_by_id($bind) {
        // --
        try {
            // --
            $sql = 'SELECT 
                    c.id AS id_technicals,
                    c.id_document_type,
                    dt.description AS document_type,
                    c.name,
                    c.document_number,
                    c.phone,
                    c.area,
                    c.cargo,
                    c.technical_type,
                    c.status
                FROM technicals c
                INNER JOIN document_type dt ON dt.id = c.id_document_type
                WHERE c.id = :id_technicals';
            // --
            $result = $this->pdo->fetchOne($sql, $bind);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // --
    public function create_technicals($bind) {
        // --
        try {
            // --
            $sql = 'INSERT INTO technicals
            (
                id_document_type,
                name,
                document_number,
                phone,
                area,
                cargo,
                technical_type
            ) 
            VALUES 
            (
                :id_document_type,
                :name,
                :document_number,
                :phone,
                :area,
                :cargo,
                :technical_type   
            )';
            // --
            $result = $this->pdo->perform($sql, $bind);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => array());
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // --
    public function update_technicals($bind) {
        // --
        try {
            // --
            $sql = 'UPDATE technicals 
                SET
                    id_document_type = :id_document_type,
                    name = :name,
                    document_number = :document_number,
                    phone = :phone,
                    area = :area,
                    cargo = :cargo,
                    technical_type = :technical_type
                WHERE id = :id_technicals';
            // --
            $result = $this->pdo->perform($sql, $bind);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => array());
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // --
    public function delete_technicals($bind) {
        // --
        try {
            // --
            $sql = 'DELETE FROM technicals 
            where id = :id_technicals';
            // --
            $result = $this->pdo->perform($sql, $bind);
            // --
            if ($result) {
                // --
                $response = array('status' => 'OK', 'result' => array());
            } else {
                // --
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            // --
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

}