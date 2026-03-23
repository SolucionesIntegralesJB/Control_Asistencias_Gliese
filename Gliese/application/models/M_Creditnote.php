<?php

class M_Creditnote extends Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_creditnote()
    {
        try {
            $sql = 'SELECT 
                        id AS id_creditnote,
                        id_user,
                        id_products,
                        id_sale,
                        amount,
                        price_sale,
                        discount,
                        correction_description,
                        series,
                        status
                        
                    FROM creditnote
                    ORDER BY id_user ASC';

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        return $response;
    }

    public function get_creditnote_by_id($bind)
    {
        try {
            $sql = 'SELECT 
                        id AS id_creditnote,
                        id_user,
                        id_products,
                        id_sale,
                        amount,
                        price_sale,
                        discount,
                        correction_description,
                        series,
                        status
                    FROM creditnote
                    WHERE id = :id';

            $result = $this->pdo->fetchOne($sql, $bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        return $response;
    }

    public function create_creditnote($bind)
    {
        try {
            $sql = 'INSERT INTO creditnote (
                        id_user,
                        id_products,
                        id_sale,
                        amount,
                        price_sale,
                        discount,
                        correction_description,
                        series,
                        status
                    ) VALUES (
                        :id_user,
                        :id_products,
                        :id_sale,
                        :amount,
                        :price_sale,
                        :discount,
                        :correction_description,
                        :series,
                        :status
                    )';

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute($bind);

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

    public function update_creditnote($bind)
    {
        try {
            $sql = 'UPDATE creditnote SET
                        id_user = :id_user,
                        id_products = :id_products,
                        id_sale = :id_sale,
                        amount = :amount,
                        price_sale = :price_sale,
                        discount = :discount,
                        correction_description = :correction_description,
                        series = :series,
                        status = :status
                    WHERE id = :id';

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

    public function delete_creditnote($bind)
    {
        try {
            $sql = 'UPDATE creditnote SET status = 0 WHERE id = :id';

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

    public function get_billingpersale()
    {
        try {
            $sql = 'SELECT 
                bp.id,
                bp.due_date,
                bp.person_id,
                bp.currency,
                bp.correlative,
                bp.user_id AS id_user,
                bp.voucher_type,
                bp.series,
                bp.status,
                bp.response as sunat_response,
                bp.leyend,
                bp.item_description AS article,
                bp.item_serie AS serie,
                bp.tax_affectation,
                bp.quantity,
                bp.unit_sale_value AS unit_value,
                bp.discount,
                bp.tax_amount,
                ROUND(bp.unit_sale_value + bp.tax_amount, 2) AS sale_price,
                ROUND(bp.unit_sale_value * bp.quantity, 2) AS total_sale_taxed,
                ROUND((bp.unit_sale_value * bp.quantity) + bp.tax_amount, 2) AS amount,
                u.first_name AS user_name,
                p.name AS clients,
                p.document_number AS document_number_client,
                p.document_type_id AS email_client,
                vt.description AS voucher_type_description,
                bp.id_creditnote_type,
                ct.description AS creditnote_type_description
            FROM billingpersale bp
            INNER JOIN user u ON u.id = bp.user_id
            INNER JOIN person p ON p.id = bp.person_id
            INNER JOIN voucher_type vt ON vt.id = bp.voucher_type
            LEFT JOIN creditnote_type ct ON ct.id = bp.id_creditnote_type
            ORDER BY bp.id ASC';

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        return $response;
    }

    public function get_billingpersale_by_id($bind)
    {
        try {
            $sql = 'SELECT 
            bp.id,
            bp.due_date,
            bp.person_id,
            bp.currency,
            bp.correlative,
            bp.user_id AS id_user,
            bp.voucher_type,
            bp.series,
            bp.status,
            bp.leyend,
            u.first_name AS user_name,
            p.name AS clients,
            p.document_number AS document_number_client,
            vt.description AS voucher_type_description
            FROM billingpersale bp
            INNer JOIN user u ON u.id = bp.user_id
            INNer JOIN person p ON p.id = bp.person_id
            INNer JOIN voucher_type vt ON vt.id = bp.voucher_type
            WHERE bp.id = :id';

            $result = $this->pdo->fetchOne($sql, $bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => $result);
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }

        return $response;
    }

    public function save_billingpersale_update($bind)
    {
        try {
            $sql = 'UPDATE billingpersale 
                SET id_creditnote_type = :id_creditnote_type, leyend = :leyend 
                WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                ':id_creditnote_type' => $bind['id_creditnote_type'],
                ':leyend' => $bind['leyend'],
                ':id' => $bind['id']
            ]);

            if ($result) {
                return ['status' => 'OK'];
            } else {
                return ['status' => 'ERROR', 'msg' => 'No se pudo actualizar el registro.'];
            }
        } catch (PDOException $e) {
            return ['status' => 'ERROR', 'msg' => $e->getMessage()];
        }
    }
}
