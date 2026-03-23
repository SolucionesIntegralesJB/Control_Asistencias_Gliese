<?php

class M_Income extends Model {
    public function __construct() {
        parent::__construct();
    }

    // --------------------------------- LISTADO DE INCOME ---------------------------------

    public function get_income() {
        try {
            $sql = 'SELECT 
                        i.id,
                        p.name AS person_name,
                        i.date_issue,
                        i.proof_series,
                        i.voucher_series,
                        vt.description AS voucher_type_description,
                        pt.description AS payment_type_description,
                        i.full_purchase,
                        i.status
                    FROM income i
                    LEFT JOIN person p ON i.id_person = p.id  
                    LEFT JOIN voucher_type vt ON i.id_voucher_type = vt.id  
                    LEFT JOIN payment_type pt ON i.id_payment_type = pt.id
                    ORDER BY i.id DESC;';
    
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

    public function update_expired_incomes() {
        try {
            // Ejemplo: Actualizar ingresos pendientes con fecha mayor a X días
            $sql = "UPDATE income 
                    SET status = 3  
                    WHERE date_expiration < NOW() 
                    AND status = 1";  // 1 = Pendiente
    
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
    
        } catch (PDOException $e) {
            error_log("Error actualizando ingresos vencidos: " . $e->getMessage());
        }
    }

    //------------------------------------------ DETAILS -------------------------------------

    public function get_income_details($id) {
        try {
            $sql = "SELECT 
                        i.id AS id_income,
                        p.name AS person_name,
                        u.first_name AS user_name,
                        i.date_issue,
                        i.date_expiration,
                        i.proof_series,
                        i.voucher_series,
                        i.igv,
                        i.number_installments,
                        i.value_installment,
                        i.full_purchase,
                        vt.description AS voucher_type_description,
                        pt.description AS payment_type_description,
                        i.status,
                        d.id AS id_income_details,
                        d.quantity,
                        d.unit_price,
                        d.subtotal,
                        pr.id AS id_product,
                        pr.name AS product_name,
                        pr.code AS product_code
                    FROM income i
                    LEFT JOIN person p ON i.id_person = p.id  
                    LEFT JOIN user u ON i.id_user = u.id  
                    LEFT JOIN voucher_type vt ON i.id_voucher_type = vt.id  
                    LEFT JOIN payment_type pt ON i.id_payment_type = pt.id
                    LEFT JOIN income_detail d ON i.id = d.id_income
                    LEFT JOIN products pr ON d.id_product = pr.id
                    WHERE i.id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if (empty($result)) {
                return ['status' => 'ERROR', 'result' => 'No se encontraron detalles.'];
            }

            $incomeDetails = [
                'id_income' => $result[0]['id_income'],
                'person_name' => $result[0]['person_name'],
                'user_name' => $result[0]['user_name'],
                'date_issue' => $result[0]['date_issue'],
                'date_expiration' => $result[0]['date_expiration'],
                'proof_series' => $result[0]['proof_series'],
                'voucher_series' => $result[0]['voucher_series'],
                'igv' => $result[0]['igv'],
                'number_installments' => $result[0]['number_installments'],
                'value_installment' => $result[0]['value_installment'],
                'full_purchase' => $result[0]['full_purchase'],
                'voucher_type_description' => $result[0]['voucher_type_description'],
                'payment_type_description' => $result[0]['payment_type_description'],
                'status' => $result[0]['status'],
                'products' => []
            ];
    
            foreach ($result as $row) {
                $incomeDetails['products'][] = [
                    'id_income_details' => $row['id_income_details'],
                    'id_product' => $row['id_product'],
                    'product_name' => $row['product_name'],
                    'product_code' => $row['product_code'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['subtotal']
                ];
            }
    
            return ['status' => 'OK', 'result' => $incomeDetails];
        
        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'result' => $e->getMessage()];
        }
    }

    // --------------------------------- ELIMINAR INGRESO ---------------------------------

    // public function delete_income($bind) {
    //     try {
    //         $this->pdo->beginTransaction();
    
    //         // Obtener los productos y cantidades antes de eliminar
    //         $sqlGetDetails = 'SELECT id_product, quantity FROM income_detail WHERE id_income = :id_income';
    //         $stmtGetDetails = $this->pdo->prepare($sqlGetDetails);
    //         $stmtGetDetails->execute($bind);
    //         $products = $stmtGetDetails->fetchAll(PDO::FETCH_ASSOC);
    
    //         // Eliminar detalles
    //         $sqlDeleteDetails = 'DELETE FROM income_detail WHERE id_income = :id_income';
    //         $stmtDeleteDetails = $this->pdo->prepare($sqlDeleteDetails);
    //         $stmtDeleteDetails->execute($bind);
    
    //         // Eliminar ingreso
    //         $sqlDeleteIncome = 'DELETE FROM income WHERE id = :id_income';
    //         $stmtDeleteIncome = $this->pdo->prepare($sqlDeleteIncome);
    //         $stmtDeleteIncome->execute($bind);

    //         // Si deseas actualizar stock, aquí iría el código correspondiente.
    //             $updateSql = "UPDATE product_stock 
    //             SET stock = stock - :quantity 
    //             WHERE id_product = :id_product";

    //         $updateStmt = $this->pdo->prepare($updateSql);

    //         foreach ($products as $producto) {
    //         $updateStmt->bindParam(':quantity', $producto['quantity'], PDO::PARAM_INT);
    //         $updateStmt->bindParam(':id_product', $producto['id_product'], PDO::PARAM_INT);
    //         $updateStmt->execute();
    //         }



    //         $this->pdo->commit();
    //         return ['status' => 'OK', 'result' => 'Ingreso eliminado correctamente'];
    //     } catch (PDOException $e) {
    //         $this->pdo->rollBack();
    //         return ['status' => 'EXCEPTION', 'result' => $e->getMessage()];
    //     }
    // }


    public function delete_income($bind)
    {
        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();

            // 1️⃣ Obtener productos y cantidades del ingreso
           // $sqlGetDetails = 'SELECT id_product, quantity FROM income_detail WHERE id_income = :id_income';
           $sqlGetDetails = 'SELECT id_products, stock FROM income_detail WHERE id_income = :id_income';
 
           $stmtGetDetails = $this->pdo->prepare($sqlGetDetails);
            $stmtGetDetails->execute($bind);
            $products = $stmtGetDetails->fetchAll(PDO::FETCH_ASSOC);

            // 2️⃣ Eliminar detalles del ingreso
            $sqlDeleteDetails = 'DELETE FROM income_detail WHERE id_income = :id_income';
            $stmtDeleteDetails = $this->pdo->prepare($sqlDeleteDetails);
            $stmtDeleteDetails->execute($bind);

            // 3️⃣ Eliminar el ingreso
            $sqlDeleteIncome = 'DELETE FROM income WHERE id = :id_income';
            $stmtDeleteIncome = $this->pdo->prepare($sqlDeleteIncome);
            $stmtDeleteIncome->execute($bind);

            $updateSql = "UPDATE product_stock SET stock = stock - :quantity WHERE id_product = :id_product";
            $updateStmt = $this->pdo->prepare($updateSql);
            
            foreach ($products as $producto) {
                $updateStmt->bindValue(':quantity', $producto['stock'], PDO::PARAM_INT);
                $updateStmt->bindValue(':id_product', $producto['id_products'], PDO::PARAM_INT);
                $updateStmt->execute();
            }
            

            // 5️⃣ Confirmar transacción
            $this->pdo->commit();

            return [
                'status' => 'OK',
                'result' => 'Ingreso eliminado correctamente'
            ];

        } catch (PDOException $e) {
            // Revertir transacción ante cualquier error
            $this->pdo->rollBack();

            return [
                'status' => 'EXCEPTION',
                'result' => $e->getMessage()
            ];
        }
    }

//-------------------------------------------------------------------------------
//--------------------------------- falta arreglar-------------------------------



    // --------------------------------- ACTUALIZAR INGRESO ---------------------------------
    // public function update_income($bind) {
    //     try {
    //         $sql = "UPDATE income SET 
    //                     person_id = :person_id,
    //                     voucher_type_id = :voucher_type_id,
    //                     payment_type_id = :payment_type_id,
    //                     proof_series = :proof_series,
    //                     voucher_series = :voucher_series,
    //                     date_issue = NOW(),
    //                     full_purchase = :full_purchase
    //                 WHERE id = :income_id";

    //         $stmt = $this->pdo->prepare($sql);
    //         $stmt->execute($bind);
    //         return ['status' => 'OK'];
    //     } catch (PDOException $e) {
    //         return ['status' => 'ERROR', 'result' => $e->getMessage()];
    //     }
    // }
}
