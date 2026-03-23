<?php
// --
class M_Carrier extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function get_carrier()
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    c.id AS id_carrier,
                    c.document_type_id,
                    dt.description AS document_type,
                    c.role_person_id,
                    c.name,
                    c.manager,
                    c.document_number,
                    c.address,
                    c.phone,
                    c.email,
                    c.brand,
                    c.license_plate,
                    c.driver_license,
                    c.status
                  FROM person c
                  INNER JOIN document_type dt ON dt.id = c.document_type_id
                  WHERE c.status = 1 AND C.role_person_id = 3';
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
    public function get_carrier_by_id($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    c.id AS id_carrier,
                    c.document_type_id,
                    dt.description AS document_type,
                    c.role_person_id,
                    c.name,
                    c.manager,
                    c.document_number,
                    c.address,
                    c.phone,
                    c.email,
                    c.brand,
                    c.license_plate,
                    c.driver_license,
                    c.status
                FROM person c
                INNER JOIN document_type dt ON dt.id = c.document_type_id
                WHERE c.id = :id_carrier';
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
    public function create_carrier($bind)
    {
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
            brand,
            license_plate,
            driver_license,
            status,
            role_person_id
        ) 
        VALUES 
        (
            :id_document_type,
            :name,
            :document_number,
            :address,
            :phone,
            :manager,
            :email,
            :brand,
            :plate,
            :drivers_license,
            1,
            3
        )';
            // --
            $result = $this->pdo->perform($sql, $bind);
            // --
            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        // --
        return $response;
    }

    // --
    public function update_carrier($bind)
    {
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
                    manager = :manager,
                    email = :email,
                    brand = :brand,
                    license_plate = :plate,
                    driver_license = :drivers_license
                WHERE id  = :id_carrier';
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
    public function delete_carrier($bind)
    {
        // --
        try {
            // --
            $sql = 'UPDATE person
                SET
                    status = 0 
                WHERE id = :id_carrier';
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
