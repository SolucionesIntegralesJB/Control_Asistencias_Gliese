

    <?php 
// --
class M_Income_Details extends Model {

  // --
    public function __construct() {
    parent::__construct();
    }


    // Método para insertar un ingreso
    public function create_income_products($data) {
        try {
            $sql = "INSERT INTO income (id_person, id_user, id_voucher_type, id_payment_type, proof_series, voucher_series, date_expiration, number_installments, value_installment, status) 
                    VALUES (:id_person, :id_user, :id_voucher_type, :id_payment_type, :proof_series, :voucher_series, :date_expiration, :number_installments, :value_installment, :status)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            
            return $this->pdo->lastInsertId();  
        } catch (PDOException $e) {
          
            return false; 
        }
    }
    
    
    public function insertIncomeProductDetails($producto) {
        try {

            $this->pdo->beginTransaction();
    
            
            $sql = "INSERT INTO income_detail(id_income, id_product, quantity, unit_price, subtotal)
                    VALUES (:id_income, :id_product, :quantity, :unit_price, :subtotal)";
    
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id_income', $producto['id_income'], PDO::PARAM_INT);
            $stmt->bindParam(':id_product', $producto['id_product'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $producto['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':unit_price', $producto['unit_price'], PDO::PARAM_STR);
            $stmt->bindParam(':subtotal', $producto['subtotal'], PDO::PARAM_STR);
    
           
            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                file_put_contents("log.txt", "Error al insertar income_detail: " . implode(", ", $error) . "\n", FILE_APPEND);
                $this->pdo->rollBack();
                return ['status' => 'ERROR', 'msg' => 'Error al insertar producto en income_detail.'];
            }
    
            // Actualizar el full_purchase sumando todos los subtotales de income_detail
            $updateSql = "UPDATE income 
                          SET full_purchase = (
                              SELECT IFNULL(SUM(subtotal), 0) 
                              FROM income_detail 
                              WHERE id_income = :id_income
                          )
                          WHERE id = :id_income";
    
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->bindParam(':id_income', $producto['id_income'], PDO::PARAM_INT);
            $updateStmt->execute();



            $updateSql = "UPDATE product_stock
              SET stock = stock + :quantity
              WHERE id_product = :id_product"; 

            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->bindParam(':quantity', $producto['quantity'], PDO::PARAM_INT);  // Debería ser entero, no cadena
            $updateStmt->bindParam(':id_product', $producto['id_product'], PDO::PARAM_INT);  // Usamos id_product
            $updateStmt->execute();


            // Confirmar transacción
            $this->pdo->commit();
            return true;
    
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            file_put_contents("log.txt", "ERROR SQL: " . $e->getMessage() . "\n", FILE_APPEND);
            return ['status' => 'ERROR', 'msg' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

   //MODELO : M_Income_Details
    //EXCEL 
    public function get_producto_by_id($id) {
        try {
            $sql = "SELECT id, code, name, description, price FROM products WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
    
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : null;
    
        } catch (PDOException $e) {
            file_put_contents("log.txt", "ERROR SQL (get_producto_by_id): " . $e->getMessage() . "\n", FILE_APPEND);
            return null;
        }
    }
    public function getProductUnitPrice($id_product) {
        $stmt = $this->pdo->prepare("SELECT price FROM products WHERE id = :id LIMIT 1");
        $stmt->bindParam(":id", $id_product, PDO::PARAM_INT);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $product ? (float)$product["price"] : null;
    }
    

}    


