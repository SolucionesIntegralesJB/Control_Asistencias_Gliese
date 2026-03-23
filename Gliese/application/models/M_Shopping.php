<?php

class M_Shopping extends Model {
    public function __construct() {
        parent::__construct();
    }

    // --------------------------------- LISTADO DE INCOME ---------------------------------

    public function get_Shopping() {
        try {
            $sql = "SELECT 
                    o.id,
                    o.series,
                    o.correlative,
                    o.issue_date,
                    o.due_date,
                    o.total_amount,
                    o.estado_pago,
                    o.estado_entrega,
                    p.name AS client_name
                FROM online_order o
                INNER JOIN person p ON o.cliente_id = p.id
                WHERE o.estado_pago IN ('Pagado', 'Confirmado', 'Anulado') and o.status =1
                ORDER BY 
                    CASE 
                        WHEN o.estado_pago = 'Pagado' THEN 0
                        WHEN o.estado_pago = 'Confirmado' THEN 1
                        WHEN o.estado_pago = 'Anulado' THEN 2
                        ELSE 3
                    END,
                    o.id DESC";

    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (!$result) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'No se encontraron registros en la base de datos.',
                    'result' => []
                ];
            }
    
            return [
                'status' => 'OK',
                'msg' => 'Datos recuperados correctamente.',
                'result' => $result
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error en la consulta SQL.',
                'error' => $e->getMessage()
            ];
        }
    }
    public function confirmarPedido($id) {
        try {
            $sql = "UPDATE online_order 
                    SET estado_pago = 'Confirmado' 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
    
            if ($stmt->execute([$id])) {
                return [
                    "success" => true,
                    "message" => "Pedido confirmado correctamente"
                ];
            } else {
                return [
                    "success" => false,
                    "message" => "No se pudo confirmar el pedido"
                ];
            }
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ];
        }
    }
    public function anularPedido($id) {
        try {
            $sql = "UPDATE online_order 
                    SET estado_pago = 'Anulado' 
                    WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
    
            if ($stmt->execute([$id])) {
                return [
                    "success" => true,
                    "message" => "Pedido anulado correctamente"
                ];
            } else {
                return [
                    "success" => false,
                    "message" => "No se pudo anular el pedido"
                ];
            }
        } catch (Exception $e) {
            return [
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ];
        }
    }
    public function getOrderDetails($order_id) {
        try {
            // Traer información del pedido principal
            $sqlOrder = "SELECT 
                            o.id,
                            o.series,
                            o.correlative,
                            o.issue_date,
                            o.due_date,
                            o.total_amount,
                            o.estado_pago,
                            o.estado_entrega,
                            p.name AS client_name,
                            c.code AS coin_name,
                            v.description AS voucher_name,
                            ps.description AS payment_shape,
                            o.transfer_reference,
                            o.transfer_date,
                            o.transfer_proof,
                            o.notes,
                            u.first_name AS user_name
                         FROM online_order o
                         JOIN person p ON o.cliente_id = p.id
                         JOIN user u ON o.user_id = u.id
                         LEFT JOIN coin c ON o.coin_id = c.id
                         LEFT JOIN voucher_type v ON o.voucher_id = v.id
                         LEFT JOIN payment_shape ps ON o.payment_shape = ps.id
                         WHERE o.id = :id AND o.status = 1";
    
            $stmt = $this->pdo->prepare($sqlOrder);
            $stmt->execute([':id' => $order_id]);
            $orderRow = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$orderRow) {
                return ["success" => false, "message" => "Pedido no encontrado"];
            }
    
            // Traer los detalles del pedido (productos)
            $sqlDetails = "SELECT 
                               d.product_id,
                               pr.name AS product_name,
                               d.quantity,
                               d.item_unit_price,
                               d.subtotal
                            FROM online_order_detail d
                            LEFT JOIN products pr ON pr.id = d.product_id AND pr.status = 1
                            WHERE d.order_id = :id AND d.status = 1";
    
            $stmt2 = $this->pdo->prepare($sqlDetails);
            $stmt2->execute([':id' => $order_id]);
            $details = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
            return ["success" => true, "order" => $orderRow, "details" => $details ?: []];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
    
    
    
    // public function update_expired_incomes() {
    //     try {
    //         // Ejemplo: Actualizar ingresos pendientes con fecha mayor a X días
    //         $sql = "UPDATE income 
    //                 SET status = 3  
    //                 WHERE date_expiration < NOW() 
    //                 AND status = 1";  // 1 = Pendiente
    
    //         $stmt = $this->pdo->prepare($sql);
    //         $stmt->execute();
    
    //     } catch (PDOException $e) {
    //         error_log("Error actualizando ingresos vencidos: " . $e->getMessage());
    //     }
    // }


}
