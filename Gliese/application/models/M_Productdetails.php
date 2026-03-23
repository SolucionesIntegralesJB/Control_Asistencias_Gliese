<?php
// --
class M_Productdetails extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    public function create_product_stock_file($bind)
    {
        try {
            $sql = 'UPDATE products 
                    SET
                        id_unit = :id_u_medida,
                        name = :name,
                        description = :description,
                        code = :code,
                        id_label = :id_label
                    WHERE id = :id_product';
            $result = $this->pdo->perform($sql, $bind);
            if ($result) {
                $response = array('status' => 'OK', 'result' => array('id' => $bind['id_product']));
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }

    // --

    public function get_subcategories_by_categories($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT
                    s.id AS id_subcategory,
                    s.name,
                    s.status
                    FROM subcategories s 
                    WHERE id_category = :id_category AND status = 1
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

    // --
    public function create_inventory($bind)
    {
        try {
            $checkSql = 'SELECT COUNT(*) FROM product_inventories WHERE id_product = :id_product';
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute(['id_product' => $bind['id_product']]);
            $exists = $checkStmt->fetchColumn() > 0;

            if ($exists) {
                $sql = 'UPDATE product_inventories 
                        SET id_section = :id_section,
                            id_category = :id_category,
                            id_subcategory = :id_subcategory
                        WHERE id_product = :id_product';
            } else {
                $sql = 'INSERT INTO product_inventories 
                            (id_product, id_section, id_category, id_subcategory) 
                        VALUES (:id_product, :id_section, :id_category, :id_subcategory)';
            }

            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        return $response;
    }

    // --
    public function get_product_by_id($bind)
    {
        try {
            $sql = 'SELECT 
                p.id AS id_product,
                p.code,
                p.id_unit,
                u.description AS u_medida,
                p.name,
                p.description,
                p.price,
                p.id_label,
                l.name AS label_name,
                p.status                                       
            FROM products p
            INNER JOIN measuring_unit u ON u.id = p.id_unit
            LEFT JOIN labels l ON l.id = p.id_label               
            WHERE p.id = :id_product';
            $result = $this->pdo->fetchOne($sql, $bind);

            if ($result) {
                $stockSql = 'SELECT id_campus, stock FROM product_stock WHERE id_product = :id_product';
                $stockResult = $this->pdo->fetchAll($stockSql, $bind);
                $sectionSql = 'SELECT id_section, id_category, id_subcategory FROM product_inventories WHERE id_product = :id_product';
                $sectionResult = $this->pdo->fetchOne($sectionSql, $bind);
                $headersSql = 'SELECT id_header, position, content FROM content_headers WHERE id_product = :id_product';
                $headersResult = $this->pdo->fetchAll($headersSql, $bind);
                $imagesSql = 'SELECT image_url,id FROM product_images WHERE id_product = :id_product';
                $imagesResult = $this->pdo->fetchAll($imagesSql, $bind);
                $result['stock_by_campus'] = !empty($stockResult) ? $stockResult : null;
                $result['id_section'] = $sectionResult['id_section'] ?? null;
                $result['id_category'] = $sectionResult['id_category'] ?? null;
                $result['id_subcategory'] = $sectionResult['id_subcategory'] ?? null;
                $result['images'] = !empty($imagesResult) ? $imagesResult : null;
                $result['content_headers'] = !empty($headersResult) ? $headersResult : null;
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        return $response;
    }

    public function create_detail_head($data)
    {
        try {
            $this->pdo->beginTransaction();
            $deleteSql = 'DELETE FROM content_headers WHERE id_product = :id_product';
            $deleteStmt = $this->pdo->prepare($deleteSql);
            $deleteStmt->execute(['id_product' => $data[0]['id_product']]);
            $insertSql = 'INSERT INTO content_headers (id_product, id_header, position, content) VALUES (:id_product, :id_header, :position, :content)';
            $insertStmt = $this->pdo->prepare($insertSql);
            foreach ($data as $bind) {
                $result = $insertStmt->execute($bind);
                if (!$result) {
                    throw new PDOException("Error al insertar fila");
                }
            }
            $this->pdo->commit();
            $response = array('status' => 'OK', 'result' => array());
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        return $response;
    }

    public function img_product($data)
    {
        try {
            $this->pdo->beginTransaction();
            $insertSql = 'INSERT INTO product_images (id_product, image_url) VALUES (:id_product, :image_url)';
            $insertStmt = $this->pdo->prepare($insertSql);
            foreach ($data as $bind) {
                $result = $insertStmt->execute($bind);
                if (!$result) {
                    throw new PDOException("Error al insertar fila");
                }
            }
            $this->pdo->commit();
            $response = array('status' => 'OK', 'result' => array());
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        return $response;
    }

    public function delete_img_product($id, $id_product, $image_url)
    {
        try {
            $sql = 'DELETE FROM product_images WHERE id = :id AND id_product = :id_product AND image_url = :image_url';
            $bind = [
                'id' => $id,
                'id_product' => $id_product,
                'image_url' => $image_url
            ];
            $result = $this->pdo->perform($sql, $bind);
            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
        return $response;
    }
}
