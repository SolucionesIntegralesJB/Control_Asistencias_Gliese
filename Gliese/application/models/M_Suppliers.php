<?php 
// --
class M_Suppliers extends Model {
    // --
    public function __construct() {
		parent::__construct();
    }
    
    // --
	public function get_suppliers() {
        // --
        try {
            // --
                $sql = 'SELECT 
                        s.id AS id_supplier,
                        s.document_type_id,
                        dt.description AS document_type,
                        s.role_person_id,
                        s.name,
                        s.document_number,
                        s.address,
                        s.phone,
                        s.manager,
                        s.email,
                        s.status
                    FROM person s
                    INNER JOIN document_type dt ON dt.id = s.document_type_id
                    WHERE s.status = 1 AND s.role_person_id = 2';
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
	public function get_supplier_by_id($bind) {
        // --
        try {
            // --
            $sql = 'SELECT 
                        s.id AS id_supplier,
                        s.document_type_id,
                        dt.description AS document_type,
                        s.role_person_id,
                        s.name,
                        s.document_number,
                        s.address,
                        s.phone,
                        s.manager,
                        s.email,
                        s.status
                    FROM person s
                    INNER JOIN document_type dt ON dt.id = s.document_type_id
                WHERE s.id = :id_supplier';
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
    public function create_supplier($bind) {
        // --
        try {
            // --
            $sql = 'INSERT INTO person
            (
                document_type_id,
                name,
                document_number,
                address,
                phone,
                manager,
                email,
                status,
                role_person_id
            ) 
            VALUES 
            (
                :document_type,
                :name,
                :document_number,
                :address,
                :phone,
                :manager,
                :email,
                1,
                2   
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
     public function update_supplier($bind) {
        // --
        try {
            // --
            $sql = 'UPDATE person
                SET
                    document_type_id = :id_document_type,
                    name = :name,
                    document_number = :document_number,
                    address = :address,
                    phone = :phone,
                    manager = :business_name,
                    email = :email
                WHERE id = :id_supplier';
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
    public function delete_supplier($bind) {
        // --
        try {
            // --
            $sql = 'UPDATE person
                SET
                    status = 0
            where id = :id_supplier';
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
	public function get_business_name() {
        // --
        try {
            // --
            $sql = 'SELECT 
                    id,
                    business_name,
                    status
                FROM supplier';
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

    

}