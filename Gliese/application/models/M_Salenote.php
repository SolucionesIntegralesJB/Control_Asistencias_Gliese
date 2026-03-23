<?php
class M_Salenote extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    public function get_salenote_report($salenoteId)
    {
        try {
            $sql = 'SELECT 
                s.id AS id_salenote,
                u.first_name AS name_user,
                per.name AS client_name,
                dt.description AS document_type_client,
                per.document_number AS client_document,
                per.address AS client_address,
                s.date_issue,
                s.correlative,
                s.series,
                s.igv,
                s.igv_total,
                s.total_sale,
                s.time_delivery,
                s.validity,
                s.reference,
                s.status,
                vt.description AS voucher_type,
                CASE 
                    WHEN s.id_voucher_type = 1 THEN "FACTURA"
                    WHEN s.id_voucher_type = 2 THEN "BOLETA"
                    ELSE "NOTA DE VENTA"
                END AS id_voucher_type
            FROM sale s
            INNER JOIN person per ON per.id = s.id_clients
            INNER JOIN user u ON u.id = s.id_user
            INNER JOIN document_type dt ON dt.id = per.document_type_id
            INNER JOIN voucher_type vt ON vt.id = s.id_voucher_type
            WHERE s.id = :salenoteId';
 
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':salenoteId', $salenoteId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
 
            if ($result) {
                return array('status' => 'OK', 'result' => $result);
            } else {
                return array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
    }
    

    public function get_salenote_details_report($salenoteId)
    {
        try {
            $sql = 'SELECT 
                sd.id AS id_detail,
                sd.id_products,
                sd.amount,
                sd.series,
                sd.price_sale,
                sd.status,
                pr.code AS product_code,
                pr.name AS product_name
            FROM sale_detail sd
            LEFT JOIN products pr ON pr.id = sd.id_products
            WHERE sd.id_sale = :salenoteId';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':salenoteId', $salenoteId, PDO::PARAM_INT);
            $stmt->execute();
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($details) {
                return array('status' => 'OK', 'result' => $details);
            } else {
                return array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e->getMessage());
        }
    }

    public function get_salenote()
{
    try {
        $sql = 'SELECT 
            s.date_issue AS issue_date,
            per.name AS clients,
            u.user AS user,
            vt.description AS voucher_type,
            CONCAT(s.series, "-", s.correlative) AS document_number,
            s.total_sale,
            s.status,
            s.id AS id_salenote
        FROM sale s
        LEFT JOIN person per ON per.id = s.id_clients
        LEFT JOIN user u ON u.id = s.id_user
        LEFT JOIN voucher_type vt ON vt.id = s.id_voucher_type
        ORDER BY s.date_issue DESC';

        $result = $this->pdo->fetchAll($sql);

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

    // --
    public function get_salenote_by_id($bind)
    {
        try {
            $sql = 'SELECT 
                    s.id AS id_salenote,
                    per.name AS clients,
                    u.user AS user,
                    vt.description AS voucher_type,
                    s.date_issue AS issue_date,
                    s.series,
                    s.correlative,
                    s.total_sale,
                    s.status
                FROM sale s
                INNER JOIN person per ON per.id = s.id_clients
                INNER JOIN user u ON u.id = s.id_user
                INNER JOIN voucher_type vt ON vt.id = s.id_voucher_type
                WHERE s.id = :id_salenote';

            $result = $this->pdo->fetchOne($sql, $bind);

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

    // --
    public function create_salenote($bind)
    {
        try {
            $sql = 'INSERT INTO sale
            (
                id_clients,
                id_user,
                id_voucher_type,
                date_issue,
                series,
                correlative,
                total_sale,
                status
            ) 
            VALUES 
            (
                :id_clients,
                :id_user,
                :id_voucher_type,
                :date_issue,
                :series,
                :correlative,
                :total_sale,
                :status   
            )';
            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }

    // --
    public function update_salenote($bind)
    {
        try {
            $sql = 'UPDATE sale 
                SET
                    id_clients = :id_clients,
                    id_user = :id_user,
                    id_voucher_type = :id_voucher_type,
                    date_issue = :date_issue,
                    series = :series,
                    correlative = :correlative,
                    total_sale = :total_sale,
                    status = :status
                WHERE id = :id_salenote';
            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }

    // --
    public function delete_salenote($bind)
    {
        try {
            $sql = 'DELETE FROM sale 
            WHERE id = :id_salenote';
            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }

    public function update_salenote_status($bind)
    {
        try {
            $sql = 'UPDATE sale 
                SET
                    status = :status,
                    response = :response
                WHERE id = :id_salenote';
            $result = $this->pdo->perform($sql, $bind);

            if ($result) {
                $response = array('status' => 'OK', 'result' => array());
            } else {
                $response = array('status' => 'ERROR', 'result' => array());
            }
        } catch (PDOException $e) {
            $response = array('status' => 'EXCEPTION', 'result' => $e);
        }
        return $response;
    }

    public function get_client_email($bind)
    {
        try {
            $sql = 'SELECT 
                p.email
            FROM sale s
            INNER JOIN person p ON p.id = s.id_clients
            WHERE s.id = :id_salenote';

            $result = $this->pdo->fetchOne($sql, $bind);

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