<?php
// --
class M_Proforma extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function get_proforma_report($proformaId)
{
    try {
        $sql = 'SELECT 
            p.id AS id_proforma,
            u.first_name AS name_user,
            per.name AS client_name,
            dt.description AS document_type_client,
            per.document_number AS client_document,
            per.address AS client_address,
            p.date_issue,
            p.correlative,
            p.series_proforma,
            p.igv,
            p.igv_total,
            p.total_sale,
            p.delivery_time,
            p.offer_validity,
            p.reference,
            p.status,
            vt.description AS voucher_type,
            CASE 
                WHEN p.id_voucher_type = 1 THEN "PROFORMA - FACTURA"
                WHEN p.id_voucher_type = 2 THEN "PROFORMA - BOLETA"
                ELSE "PROFORMA"
            END AS id_voucher_type
        FROM proforma p
        INNER JOIN person per ON per.id = p.id_clients
        INNER JOIN user u ON u.id = p.id_user
        INNER JOIN document_type dt ON dt.id = per.document_type_id
        INNER JOIN voucher_type vt ON vt.id = p.id_voucher_type
        WHERE p.id = :proformaId';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':proformaId', $proformaId, PDO::PARAM_INT);
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

public function get_proforma_details_report($proformaId)
{
    try {
        $sql = 'SELECT 
            pd.id AS id_detail,
            pd.id_products,
            pd.amount,
            pd.series,
            pd.price_sale,
            pd.status,
            pr.code AS product_code,
            pr.name AS product_name
        FROM proforma_detail pd
        LEFT JOIN products pr ON pr.id = pd.id_products
        WHERE pd.id_proforma = :proformaId';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':proformaId', $proformaId, PDO::PARAM_INT);
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
    public function get_proforma()
    {
        try {
            $sql = 'SELECT 
                p.id AS id_proforma,
                per.name AS clients,
                u.user AS user,
                vt.description AS voucher_type,
                p.date_issue AS issue_date,
                p.series_proforma,
                p.correlative,
                p.total_sale,
                p.status
            FROM proforma p
            LEFT JOIN person per ON per.id = p.id_clients
            LEFT JOIN user u ON u.id = p.id_user
            LEFT JOIN voucher_type vt ON vt.id = p.id_voucher_type';

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
    public function get_proforma_by_id($bind)
    {
        try {
            $sql = 'SELECT 
                    p.id AS id_proforma,
                    per.name AS clients,
                    u.user AS user,
                    vt.description AS voucher_type,
                    p.date_issue AS issue_date,
                    p.series_proforma,
                    p.correlative,
                    p.total_sale,
                    p.status
                FROM proforma p
                INNER JOIN person per ON per.id = p.id_clients
                INNER JOIN user u ON u.id = p.id_user
                INNER JOIN voucher_type vt ON vt.id = p.id_voucher_type
                WHERE p.id = :id_proforma';  // Cambié la condición para buscar por ID de proforma

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
    public function create_proforma($bind)
    {
        try {
            $sql = 'INSERT INTO proforma
            (
                id_clients,
                id_user,
                id_voucher_type,
                date_issue,
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
    public function update_proforma($bind)
    {
        try {
            $sql = 'UPDATE proforma 
                SET
                    id_clients = :id_clients,
                    id_user = :id_user,
                    id_voucher_type = :id_voucher_type,
                    date_issue = :date_issue,
                    correlative = :correlative,
                    total_sale = :total_sale,
                    status = :status
                WHERE id = :id_proforma';
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
    public function delete_proforma($bind)
    {
        try {
            $sql = 'DELETE FROM proforma 
            WHERE id = :id_proforma';
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
}
