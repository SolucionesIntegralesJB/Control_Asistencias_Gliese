<?php 
// --
class M_ProductsImage extends Model {
    // --
    public function __construct() {
		parent::__construct();
    }
    
    // --
	public function get_products() {
        // --
        try {
            // --
                $sql = 'SELECT
                        p.id AS id_product,
                        p.code,
                        p.name,
                        p.image
                    FROM products p
                WHERE p.status = 1';
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
	public function get_product_by_id($bind) {
        // --
        try {
            // --
            $sql = 'SELECT 
                    p.id AS id_product,
                    p.code,
                    p.name,
                    p.image
                FROM products p
                WHERE p.id = :id_product AND p.status = 1';
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
    public function update_product($bind) {
        // --
        try {
            // --
            $sql = 'UPDATE products 
                SET
                    image = :image
            WHERE id = :id_product';
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