<?php
// --
class M_Job_Role extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function get_job_roles()
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    jr.id AS id_job_role,
                    jr.job_role
                    FROM job_role jr
                    ORDER BY jr.job_role ASC';
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
    public function get_job_role_by_id($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    jr.id AS id_job_role,
                    jr.job_role
                FROM job_role jr
                WHERE jr.id = :id_job_role';
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
    public function create_job_role($bind)
    {
        // --
        try {
            // --
            $sql = 'INSERT INTO job_role
                (
                    job_role
                ) 
                VALUES 
                (
                    :job_role
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
    public function update_job_role($bind)
    {
        // --
        try {
            // --
            $sql = 'UPDATE job_role
                SET
                    job_role = :job_role
                WHERE id = :id_job_role';
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
    public function delete_job_role($bind)
    {
        // --
        try {
            // --
            $sql = 'DELETE FROM job_role WHERE id = :id_job_role';
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
    public function get_job_roles_select()
    {
        // --
        try {
            // --
            $sql = 'SELECT jr.id, jr.job_role FROM job_role jr ORDER BY jr.job_role ASC';
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
