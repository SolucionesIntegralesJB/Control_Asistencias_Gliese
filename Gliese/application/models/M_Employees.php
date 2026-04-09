<?php
// --
class M_Employees extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function get_employees()
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    e.id AS id_employees,
                    e.document_type_id,
                    dt.description AS document_description,
                    e.name,
                    e.document_number,                    
                    e.address,
                    e.phone,
                    e.email,
                    e.reference,
                    e.role_person_id,
                    e.work_area,
                    e.position,
                    e.salary
                    FROM employees e
                    INNER JOIN document_type dt ON dt.id = e.document_type_id
                    WHERE e.status = 1';
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
    public function get_employee_by_id($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    e.id AS id_employees,
                    e.document_type_id,
                    dt.description AS document_description,
                    e.name,
                    e.document_number,                    
                    e.address,
                    e.phone,
                    e.email,
                    e.reference,
                    e.work_area,
                    e.position,
                    e.salary
                FROM employees e
                INNER JOIN document_type dt ON dt.id = e.document_type_id
                WHERE e.id = :id_employees';
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
    public function create_employees($bind)
    {
        // --
        try {
            // --
            $sql = 'INSERT INTO employees
                (
                    document_type_id,
                    document_number,
                    name,
                    address, 
                    reference,
                    phone,
                    email,
                    role_person_id,
                    work_area,
                    position,
                    salary,
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
                    :role_person_id,
                    :work_area,
                    :position,
                    :salary,
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
    public function update_employees($bind)
    {
        // --
        try {
            // --
            $sql = 'UPDATE employees
                SET
                    document_type_id = :document_type,
                    document_number = :document_number,
                    name = :name,
                    address = :address,
                    reference = :reference,
                    phone = :phone,
                    email = :email,
                    work_area = :work_area,
                    position = :position,
                    salary = :salary,
                    role_person_id = :role_person_id
                WHERE id = :id_employees';
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
    public function delete_employees($bind)
    {
        // --
        try {
            // --
            $sql = 'UPDATE employees
                SET
                    status = 0
                WHERE id = :id_employees';
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
            $sql = 'SELECT e.id, e.name AS business_name, e.document_number, e.address FROM employees e WHERE e.status = 1';
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