<?php 
// --
class M_Income_Pending extends Model {

  // --
    public function __construct() {
    parent::__construct();
    }

public function get_income_products_pending() {
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
                WHERE i.status = 1
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

public function update_income_products_status($ids) {
    try {
        // Validar y limpiar los IDs
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids, fn($id) => $id > 0);

        if (empty($ids)) {
            return ["status" => "ERROR", "msg" => "IDs inválidos."];
        }

        // Construir consulta SQL dinámica con placeholders
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "UPDATE income SET status = 2 WHERE id IN ($placeholders)";

        // Ejecutar consulta preparada
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($ids);

        return [
            "status" => "OK",
            "msg" => "Se actualizaron {$stmt->rowCount()} registros."
        ];
    } catch (PDOException $e) {
        return [
            "status" => "EXCEPTION",
            "msg" => "Error de base de datos: " . $e->getMessage()
        ];
    }
}


}