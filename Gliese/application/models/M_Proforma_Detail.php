<?php

class M_Proforma_Detail extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create_proforma($data, $products)
{
    error_log("🔹 Datos recibidos en create_proforma: " . json_encode($data));
    
    $this->validateFields($data);
    $proformaId = $this->insertProforma($data);

    // Agregar detalles de la proforma
    $this->addProductDetails($proformaId, $products);

    error_log("✅ Proforma creada con ID: " . $proformaId);
    
    return array('status' => 'OK', 'proforma_id' => $proformaId);
}

    private function insertProforma($data)
    {
        error_log("🔹 Insertando proforma...");

        $sql = "INSERT INTO proforma (
                    id_clients, id_user, id_voucher_type, 
                    igv, igv_total, date_issue,
                    reference, total_sale, 
                    delivery_time, offer_validity, status
                ) VALUES (
                    :id_clients, :id_user, :id_voucher_type, 
                    :igv, :igv_total, :date_issue, 
                    :reference, :total_sale, 
                    :delivery_time, :offer_validity, 2
                )";

        $params = $this->prepareProformaParams($data);
        error_log("🔹 Parámetros: " . json_encode($params));

        try {
            $stmt = $this->pdo->prepare($sql);
            if ($stmt->execute($params) === false) {
                throw new Exception("Error en la consulta SQL: " . implode(" - ", $stmt->errorInfo()));
            }
        } catch (Exception $e) {
            error_log("❌ Error al insertar proforma: " . $e->getMessage());
            throw new Exception("Error al insertar proforma: " . $e->getMessage());
        }

        $lastId = $this->pdo->lastInsertId();
        error_log("✅ Proforma insertada con ID: " . $lastId);
        return $lastId;
    }

    private function validateFields($data)
    {
        $requiredFields = ['id_voucher_type', 'id_clients', 'id_user', 'date_issue', 'total_sale'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                error_log("⚠️ Campo requerido faltante: $field");
                throw new Exception("Campo requerido faltante: $field");
            }
        }
    }

    private function prepareProformaParams($data)
    {
        $params = [
            ':id_clients' => $data['id_clients'],
            ':id_user' => $data['id_user'],
            ':id_voucher_type' => $data['id_voucher_type'],
            ':igv' => $data['igv'] ?? 18,
            ':igv_total' => $this->calculateIgv($data),
            ':date_issue' => $data['date_issue'],
            ':reference' => $data['reference'] ?? '',
            ':total_sale' => $data['total_sale'],
            ':delivery_time' => $data['delivery_time'] ?? '24 horas',
            ':offer_validity' => $data['offer_validity'] ?? '15 días',
        ];
        
        error_log("🔹 Parámetros de la proforma: " . json_encode($params));
        return $params;
    }

    private function calculateIgv($data)
    {
        $igv = ($data['total_sale'] * ($data['igv'] ?? 18)) / 100;
        error_log("🔹 IGV calculado: " . $igv);
        return $igv;
    }


    private function addProductDetails($proformaId, $products)
    {
        $sql = "INSERT INTO proforma_detail (
                id_proforma, id_products, 
                amount, series, price_sale, status
            ) VALUES (
                :id_proforma, :id_products, 
                :amount, :series, :price_sale, 1
            )";

        $stmt = $this->pdo->prepare($sql);

        foreach ($products as $product) {
            $stmt->execute([
                ':id_proforma' => $proformaId,
                ':id_products' => $product['id_products'],
                ':amount' => $product['amount'],
                ':series' => $product['series'] ?? 'N/A',
                ':price_sale' => $product['price_sale']
            ]);
            error_log("✅ Producto agregado: " . json_encode($product));
        }
    }

    private function updateProductStock($products)
    {
        foreach ($products as $product) {
            $this->modifyStock($product['id_products'], $product['amount']);
        }
    }

    private function modifyStock($productId, $quantity)
    {
        $sql = "UPDATE product_stock 
            SET stock = stock - :quantity 
            WHERE id_product = :product_id";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':quantity' => $quantity,
            ':product_id' => $productId
        ]);

        error_log("🔹 Stock actualizado para producto ID $productId, cantidad: $quantity");
    }
}
?>
