<?php

class M_Online_Order extends Model
{
    public function __construct()
    {
        parent::__construct();
    }
    public function get_onlineOrder_Report($id_pedido)
    {
        try {
            $sql = 'SELECT 
                o.id AS id_pedido,
                p.name AS n_cliente,
                 u.first_name AS user_name,
                o.issue_date,
                o.issue_time,
                o.cliente_id,
                o.voucher_id,
                o.series,
                o.correlative,
                o.total_amount,
                o.estado_pago,
                o.estado_entrega,
                o.user_id,
                o.coin_id,
                o.payment_shape,
                o.estado_pago,
                o.igv,
                o.due_date,
                o.taxable_amount,
                o.taxable_operations,
                o.free_operations,
                o.exempt_operations,
                o.unaffected_operations,
                o.leyend,
                o.status,
                p.document_type_id,
                p.document_number,
                dt.description AS tipo_documento,
                p.address AS address_client,
                c.description AS moneda,
                vt.code,
                vt.description AS voucher,
                ps.description AS medio_pago
         
            FROM online_order o
            INNER JOIN person p ON p.id = o.cliente_id
            INNER JOIN document_type dt ON dt.id = p.document_type_id
            INNER JOIN coin c ON c.id = o.coin_id
            INNER JOIN user u ON u.id = o.user_id
            INNER JOIN voucher_type vt ON vt.id = o.voucher_id
            INNER JOIN payment_shape ps ON ps.id = o.payment_shape
            WHERE o.id = :id_pedido';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':id_pedido', $id_pedido, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return ['status' => !empty($result) ? 'OK' : 'ERROR', 'result' => $result];
        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'result' => $e->getMessage()];
        }
    }

    public function get_online_orders()
    {
        try {
            $sql = "SELECT o.id AS id_pedido, 
                        o.issue_date AS Fecha, 
                        p.name AS Cliente,
                        o.series AS Series,
                        o.correlative AS Correlative, 
                        o.total_amount AS Total_venta, 
                        o.estado_pago AS Estado_pago, 
                        o.estado_entrega AS Estado_entrega
                    FROM online_order o
                    JOIN person p ON o.cliente_id = p.id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['status' => $result ? 'OK' : 'ERROR', 'result' => $result ?: []];
        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'result' => $e->getMessage()];
        }
    }
    public function get_order_by_id($bind)
    {
        try {
            $sql = 'SELECT 
                        o.id AS id_pedido,
                        o.issue_date,
                        o.cliente_id,
                        p.name AS nombre_cliente,
                        c.description AS monedas,
                        p.address,
                        o.due_date,
                        o.igv,
                        o.taxable_operations,
                        o.taxable_amount,
                        o.exempt_operations,
                        o.free_operations,
                        o.unaffected_operations,
                        o.leyend,
                        o.series,
                        o.correlative,
                        o.total_amount,
                        o.estado_pago,
                        o.estado_entrega
                    FROM online_order o
                    INNER JOIN person p ON p.id = o.cliente_id
                    INNER JOIN coin c ON c.id = o.coin_id
                    WHERE o.id = :id_pedido';
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


public function update_online_order($bind)
{
    try{
        $sql = 'UPDATE online_order
                SET 
                estado_pago = :estado_pago,
                 estado_entrega = :estado_entrega
                WHERE id = :id_pedido';
        $result = $this->pdo->perform($sql,$bind);
        if ($result) {
            $response = array('status' => 'OK', 'result' => $result);
        } else {
            $response = array('status' => 'ERROR', 'result' => array());
        }
    }catch (PDOException $e) {
        $response = array('status' => 'EXCEPTION', 'result' => $e);
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
        if($result){
            $response = array('status' => 'OK', 'result' => $result);
        }else{
            $response = array('status' => 'ERROR', 'result' => array());
        }
    }catch(PDOException $e){
        $response = array('status' => 'EXCEPTION', 'result' => $e->getMessage());
    }
    return $response;
}


    public function get_online_order_details($id_pedido)
    {
        try {
            $sql = 'SELECT 
                p.name AS articulo,
                p.code,
                od.quantity AS cantidad,
                od.item,
                od.unit_type,
                od.description,
                od.serie,
                od.tax_percentage,
                od.Type_taxation,
                od.tax_amount,
                od.tax_affectation_type,
                od.unit_value,
                od.free_unit_value,
                od.item_unit_price AS precio_unitario,
                (od.quantity * od.item_unit_price) AS subtotal,
                od.sale_date
            FROM online_order_detail od
            INNER JOIN products p ON od.product_id = p.id
            WHERE od.order_id = :order_id
            ORDER BY od.item ASC';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':order_id', $id_pedido, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['status' => !empty($result) ? 'OK' : 'ERROR', 'result' => $result];
        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'result' => $e->getMessage()];
        }
    }
}


