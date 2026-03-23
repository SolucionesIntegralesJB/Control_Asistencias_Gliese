<?php
class M_Igvinvoicing extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    // Obtener lista de facturas IGV
    public function get_igvinvoicing()
    {
        try {
            $sql = 'SELECT 
                i.invoice_id,
                i.client_id,
                p.name AS client_name,
                p.document_number,
                i.voucher_type_code,
                i.series,
                i.correlative,
                i.date_time,
                i.due_date,
                i.currency,
                i.payment_type_code,
                i.tax AS igv,
                i.taxable_operations,
                i.total_igv,
                i.total_sale,
                i.legend,
                i.status,
                i.sunat_response_description,
                i.exempt_operations,
                i.unaffected_operations,
                i.free_operations,
                i.type,
                i.time AS issue_time,
                i.unique_voucher,
                i.related_document
            FROM igvinvoice i
            INNER JOIN person p ON p.id = i.client_id
            ORDER BY i.invoice_id DESC';

            $stmt = $this->pdo->prepare($sql);
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

    // Obtener datos para reporte de factura
    public function get_igvinvoicing_report($invoice_id)
    {
        try {
            $sql = 'SELECT 
                i.invoice_id,
                u.first_name as name_user,
                p.name AS client_name,
                p.document_type_id,
                dt.description AS document_type,
                p.document_number,
                p.address as client_address,
                i.currency,
                c.description AS currency_desc,
                i.voucher_type_code,
                vt.description AS voucher_type,
                pt.description AS payment_type,
                i.date_time AS issue_date,
                i.due_date,
                i.series,
                i.correlative,
                i.currency,
                i.tax AS igv,
                i.legend,
                i.total_sale,
                p.address,
                i.due_date,
                i.taxable_operations,
                i.free_operations,
                i.exempt_operations,
                i.unaffected_operations,
                i.status,
                i.time AS issue_time,
                i.sunat_response_description,
                i.unique_voucher,
                i.related_document
            FROM igvinvoice i
            INNER JOIN person p ON p.id = i.client_id
            INNER JOIN voucher_type vt ON vt.code = i.voucher_type_code
            INNER JOIN payment_type pt ON pt.id = i.payment_type_code
            INNER JOIN document_type dt ON dt.id = p.document_type_id
            INNER JOIN coin c ON c.code = i.currency
            INNER JOIN user u ON u.id = i.user_id
            WHERE i.invoice_id = :invoice_id';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return ['status' => 'OK', 'result' => $result];
            } else {
                return ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'result' => $e->getMessage()];
        }
    }

    public function get_igvinvoicing_details($invoice_id)
    {
        try {
            $sql = 'SELECT 
                id.invoice_detail_id,
                id.product_code,
                id.product_description,
                id.unit_of_measure,
                id.quantity,
                id.sale_price AS item_unit_price,
                id.affectation,
                id.series,
                id.discount,
                (id.sale_price * id.quantity) AS subtotal,
                ((id.sale_price * id.quantity) * (i.tax/100)) AS tax_amount
            FROM igvinvoice_detail id
            INNER JOIN igvinvoice i ON i.invoice_id = id.invoice_id
            WHERE id.invoice_id = :invoice_id';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($result) {
                return ['status' => 'OK', 'result' => $result];
            } else {
                return ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'result' => $e->getMessage()];
        }
    }

    // Obtener información de contacto del cliente
    public function get_client_contact($invoice_id)
    {
        try {
            $sql = 'SELECT 
                p.name AS client_name,
                p.email,
                p.phone,
                p.document_number,
                p.address
            FROM igvinvoice i
            INNER JOIN person p ON p.id = i.client_id
            WHERE i.invoice_id = :invoice_id';
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
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

    // Crear nueva factura
    public function create_invoice($header_data, $details)
    {
        try {
            $this->pdo->beginTransaction();

            // Insertar encabezado de factura
            $sql_header = 'INSERT INTO igvinvoice (
                user_id, client_id, voucher_type_code, series, correlative, 
                date_time, due_date, currency, payment_type_code, tax,
                taxable_operations, total_igv, total_sale, legend, status,
                time, assigned_igv, type, exempt_operations, unaffected_operations,
                free_operations, unique_voucher, related_document
            ) VALUES (
                :user_id, :client_id, :voucher_type_code, :series, :correlative, 
                :date_time, :due_date, :currency, :payment_type_code, :tax,
                :taxable_operations, :total_igv, :total_sale, :legend, :status,
                :time, :assigned_igv, :type, :exempt_operations, :unaffected_operations,
                :free_operations, :unique_voucher, :related_document
            )';
            
            $stmt_header = $this->pdo->prepare($sql_header);
            $stmt_header->execute($header_data);
            
            $invoice_id = $this->pdo->lastInsertId();

            // Insertar detalles de factura
            foreach ($details as $detail) {
                $sql_detail = 'INSERT INTO igvinvoice_detail (
                    invoice_id, product_code, product_description, unit_of_measure, 
                    quantity, sale_price, affectation, series, discount
                ) VALUES (
                    :invoice_id, :product_code, :product_description, :unit_of_measure, 
                    :quantity, :sale_price, :affectation, :series, :discount
                )';
                
                $stmt_detail = $this->pdo->prepare($sql_detail);
                $stmt_detail->execute([
                    ':invoice_id' => $invoice_id,
                    ':product_code' => $detail['product_code'],
                    ':product_description' => $detail['product_description'],
                    ':unit_of_measure' => $detail['unit_of_measure'],
                    ':quantity' => $detail['quantity'],
                    ':sale_price' => $detail['sale_price'],
                    ':affectation' => $detail['affectation'],
                    ':series' => $detail['series'] ?? '',
                    ':discount' => $detail['discount'] ?? 0
                ]);
            }

            $this->pdo->commit();
            return ['status' => 'OK', 'message' => 'Factura creada correctamente', 'data' => ['invoice_id' => $invoice_id]];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return ['status' => 'ERROR', 'message' => $e->getMessage()];
        }
    }

    // Obtener información de la compañía
    public function get_company()
    {
        try {
            $sql = 'SELECT * FROM company LIMIT 1';
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
}