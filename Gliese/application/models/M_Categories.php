<?php 
// --
class M_Categories extends Model {
    // --
    public function __construct() {
		  parent::__construct();
    }
    
    // --
	public function get_categories() {
        // --
        try {
            // --
            $sql = 'SELECT
                    c.id AS id_category,
                    c.name,
                    c.id_section,
                    s.name AS name_section,
                    c.status
                    FROM categories c 
                    INNER JOIN sections s ON c.id_section = s.id
            ';
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
	public function get_category_by_id($bind) {
        // --
        try {
            // --
            $sql = 'SELECT
                    c.id AS id_category,
                    c.name,
                    c.id_section,
                    c.status
                    FROM categories c 
                    WHERE id = :id_category';
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
    public function create_category($bind) {
        // --
        try {
            // --
            $sql = 'INSERT INTO categories (name, id_section) VALUES (:name, :id_section)';
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
    public function update_category($bind) {
        // --
        try {
            // --
            $sql = 'UPDATE categories SET 
                    name = :name,
                    id_section = :id_section 
                    WHERE id = :id_category';
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
    public function delete_category($bind) {
        // --
        try {
            // --
            $sql = 'DELETE FROM categories where id = :id_category';
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