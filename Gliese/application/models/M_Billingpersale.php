<?php
// --
class M_Billingpersale extends Model
{

    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function get_billingpersale_Report($billingpersale)
    {
        try {
            $sql = 'SELECT 
                bp.id AS id_billingpersale,
                u.first_name as name_user,
                p.name AS clients,
                p.document_type_id,
                dt.description AS documento_client,
                p.document_number,
                p.address as address_cliente,
                bp.currency,
                c.description AS DescCurrency,
                bp.operation_type,
                bp.voucher_type,
                vt.code,
                pt.description AS payment_medium,
                bp.issue_date,
                bp.due_date,
                bp.series,
                bp.correlative,
                bp.currency,
                bp.igv,
                bp.leyend,
                bp.total_amount,
                p.address,
                bp.due_date,
                bp.taxable_operations,
                bp.free_operations,
                bp.exempt_operations,
                bp.unaffected_operations,
                bp.status
            FROM billingpersale bp
            INNER JOIN person p ON p.id = bp.person_id
            INNER JOIN voucher_type vt ON vt.id = bp.voucher_type
            INNER JOIN payment_shape pt ON pt.id = bp.payment_medium
            INNER JOIN document_type dt ON dt.id = p.document_type_id
            INNER JOIN coin c ON c.code = bp.currency
            INNER JOIN user u ON u.id = bp.user_id
            WHERE bp.id = :billingpersale';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':billingpersale', $billingpersale, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function get_billingpersale_details_Report($billingpersale)
    {
        try {
            $sql = 'SELECT 
                    p.name AS articulo, 
                    p.code, 
                    bd.serie,
                    bd.tax_percentage, 
                    bd.tax_amount,
                    bd.quantity, 
                    bd.item_unit_price
                FROM 
                    billingpersale_detail bd
                INNER JOIN 
                    products p ON bd.product_id = p.id
                WHERE 
                    bd.sale_id = :billingpersale';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':billingpersale', $billingpersale, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function get_billingpersale_xml($billingpersale)
    {
        try {
            $sql = 'SELECT 
                bp.id AS id_billingpersale,
                p.name AS clients,
                p.document_type_id,
                dt.description AS documento_client,
                p.document_number,
                p.address as address_cliente,
                bp.currency,
                c.description AS DescCurrency,
                bp.operation_type,
                bp.voucher_type,
                vt.code,
                pt.description AS payment_medium,
                CONCAT(bp.issue_date, " ", bp.issue_time) as issue_date,
                bp.due_date,
                bp.series,
                bp.correlative,
                bp.currency,
                bp.igv,
                bp.leyend,
                bp.total_amount,
                p.address,
                bp.due_date,
                bp.taxable_operations,
                bp.free_operations,
                bp.exempt_operations,
                bp.unaffected_operations,
                bp.status
            FROM billingpersale bp
            INNER JOIN person p ON p.id = bp.person_id
            INNER JOIN voucher_type vt ON vt.id = bp.voucher_type
            INNER JOIN payment_shape pt ON pt.id = bp.payment_medium
            INNER JOIN document_type dt ON dt.id = p.document_type_id
            INNER JOIN coin c ON c.code = bp.currency
            WHERE bp.id = :billingpersale';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':billingpersale', $billingpersale, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function get_billingpersale_details_xml($billingpersale)
    {
        try {
            $sql = 'SELECT 
                    p.name AS articulo, 
                    p.code, 
                    bd.serie,
                    bd.unit_type,
                    bd.unit_value,
                    bd.free_unit_value,
                    bd.tax_affectation_type,
                    bd.tax_percentage, 
                    bd.tax_amount,
                    bd.quantity, 
                    bd.item_unit_price
                FROM 
                    billingpersale_detail bd
                INNER JOIN 
                    products p ON bd.product_id = p.id
                WHERE 
                    bd.sale_id = :billingpersale';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':billingpersale', $billingpersale, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function get_company()
    {
        try {
            $sql = 'SELECT * FROM company';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function get_billingpersale($campus_id)
    {
        try {
            $sql = 'SELECT 
                bp.id AS id_billingpersale,
                p.name AS clients,
                vt.description AS voucher_type,
                pt.description AS payment_type,
                u.first_name AS user_name,
                bp.issue_date,
                bp.series,
                bp.correlative,
                bp.currency,
                bp.igv,
                bp.leyend,
                bp.total_amount,
                p.address,
                bp.due_date,
                bp.taxable_operations,
                bp.status,
                bp.response
            FROM billingpersale bp
            LEFT JOIN user u ON u.id = bp.user_id
            INNER JOIN person p ON p.id = bp.person_id
            INNER JOIN voucher_type vt ON vt.id = bp.voucher_type
            INNER JOIN payment_type pt ON pt.id = bp.payment_method
            WHERE bp.campus_id = :campus_id
            ORDER BY bp.id DESC';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':campus_id', $campus_id, PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    public function get_billingpersale_by_id($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT 
                    p.id AS id_billingpersale,
                    c.business_name AS clients,
                    u.user AS user,
                    vt.description AS voucher_type,
                    pt.description AS payment_type,
                    p.date_issue,
                    p.series,
                    p.correlative,
                    p.coins,
                    p.igv,
                    p.igv_total,
                    p.legend,
                    p.total_sale,
                    p.address,
                    p.date_expiration,
                    p.op_taxed,
                    p.sustent,
                    p.doc_related,
                    p.proof_unique,
                    p.status
                FROM billingpersale p
                LEFT JOIN clients c ON c.id = p.id_clients
                INNER JOIN user u ON u.id = p.id_user
                INNER JOIN voucher_type vt ON vt.id = p.id_voucher_type
                INNER JOIN payment_type pt ON pt.id = p.id_payment_type
                WHERE c.id = :id_billingpersale';
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
    public function create_billingpersale($bind)
    {
        // --
        try {
            // --
            $sql = 'INSERT INTO billingpersale
            (
                id_document_type,
                name,
                document_number,
                address,
                phone,
                business_name,
                email
            ) 
            VALUES 
            (
                :id_document_type,
                :name,
                :document_number,
                :address,
                :phone,
                :business_name,
                :email   
            )';
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
    public function update_billingpersale($bind)
    {
        // --
        try {
            // --
            $sql = 'UPDATE billingpersale
                SET
                    id_document_type = :id_document_type,
                    name = :name,
                    document_number = :document_number,
                    address = :address,
                    phone = :phone,
                    email = :email,
                    business_name = :business_name
                WHERE id = :id_billingpersale';
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
    public function delete_billingpersale($bind)
    {
        // --
        try {
            // --
            $sql = 'DELETE FROM billingpersale 
            where id = :id_billingpersale';
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

    public function get_billingpersale_status($bind)
    {
        try {
            $sql = 'UPDATE billingpersale 
                    SET status = :status,
                        response = :response
                    WHERE id = :id_billingpersale';
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

    public function get_number_email($bind)
    {
        try {
            $sql = 'SELECT 
                bp.id AS id_billingpersale,
                p.name AS clients,
                p.email,
                p.phone
            FROM billingpersale bp
            INNER JOIN person p ON p.id = bp.person_id
            WHERE bp.id = :id_billingpersale';
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