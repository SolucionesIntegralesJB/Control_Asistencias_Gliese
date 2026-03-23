<?php
// --
class M_Clients extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function get_clients()
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
                    c.reference,
                    c.role_person_id 
                    FROM person c
                    INNER JOIN document_type dt ON dt.id = c.document_type_id
                    WHERE c.role_person_id = 1
                    AND c.status = 1';
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
    public function get_client_by_id($bind)
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

    // --
    public function create_clients($bind)
    {
        // --
        try {
            // --
            $sql = 'INSERT INTO person
                (
                    document_type_id,
                    document_number,
                    name,
                    address, 
                    reference,
                    phone,
                    email,
                    role_person_id,
                    status
                ) 
                VALUES 
                (
                    :document_type,
                    :document_number,
                    :name,
                    :address,
                    :reference,
                    :phone,
                    :email,
                    1,
                    1
                )';
            // --
            $stmt = $this->pdo->prepare($sql); // Preparar la consulta
            $result = $stmt->execute($bind); // Ejecutar la consulta con los datos vinculados
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
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        // --
        return $response;
    }

    // --
    public function update_clients($bind)
    {
        // --
        try {
            // --
            $sql = 'UPDATE person
                SET
                    document_type_id = :document_type,
                    document_number = :document_number,
                    name = :name,
                    address = :address,
                    reference = :reference,
                    phone = :phone,
                    email = :email
                WHERE id = :id_clients';
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
    public function delete_clients($bind)
    {
        // --
        try {
            // --
            $sql = 'UPDATE person
                SET
                    status = 0
                WHERE id = :id_clients';
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

    public function get_business_name_cli()
    {
        // --
        try {
            // --
            $sql = 'SELECT c.id, c.name AS business_name, c.document_number, c.address FROM person c WHERE c.status = 1';
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