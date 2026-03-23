<?php
// --
class M_Subcategories extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function get_subcategories()
    {
        // --
        try {
            // --
            $sql = 'SELECT
                sc.id AS id_subcategory,
                sc.name,
                sc.id_category,
                c.name AS name_category,
                c.id_section,
                s.name AS name_section,
                sc.status
                FROM subcategories sc 
                INNER JOIN categories c ON sc.id_category = c.id
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
    public function get_subcategory_by_id($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT
                    sc.id AS id_subcategory,
                    sc.name,
                    sc.id_category,
                    c.name AS name_category,
                    c.id_section,
                    s.name AS name_section,
                    sc.status
                    FROM subcategories sc 
                    INNER JOIN categories c ON sc.id_category = c.id
                    INNER JOIN sections s ON c.id_section = s.id
                    WHERE sc.id = :id_subcategory';
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
    public function create_subcategory($bind)
    {
        // --
        try {
            // --
            $sql = 'INSERT INTO subcategories (name, id_category) VALUES (:name, :id_category)';
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
    public function update_subcategory($bind)
    {
        // --
        try {
            // --
            $sql = 'UPDATE subcategories SET 
                    name = :name,
                    id_category = :id_category 
                    WHERE id = :id_subcategory';
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
    public function delete_subcategory($bind)
    {
        // --
        try {
            // --
            $sql = 'DELETE FROM subcategories where id = :id_subcategory';
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
    public function get_categories_by_section($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT
                    c.id AS id_category,
                    c.name,
                    c.status
                    FROM categories c 
                    WHERE id_section = :id_section AND status = 1
            ';
            // --
            $result = $this->pdo->fetchAll($sql, $bind);
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

    public function get_subcategories_by_category($bind)
    {
        try {
            $sql = 'SELECT 
                    c.id AS id_subcategory,
                    c.name,
                    c.status
                    FROM subcategories c 
                    WHERE id_category = :id_category AND status = 1
            ';
            $result = $this->pdo->fetchAll($sql, $bind);
            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }
}
