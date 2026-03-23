<?php 
// --
class M_Creditsale extends Model {
    // --
    public function __construct() {
		parent::__construct();
    }
    public function get_creditsale()
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    id AS id_clients,
                    c.document_type_id,
                    dt.description AS document_description,
                    c.name,
                    c.document_number,                    
                    c.address,
                    c.phone,
                    c.email,
                    c.reference,
                    c.role_person_id 
                    FROM person  ';
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
    public function get_creditsale_by_id($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    c.id AS id_clients,
                    c.document_type_id,
                    dt.description AS document_description,
                    c.name,
                    c.document_number,                    
                    c.address,
                    c.phone,
                    c.email,
                    c.reference
                FROM person c
                INNER JOIN document_type dt ON dt.id = document_type_id
                WHERE c.id = :id_clients';
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
}