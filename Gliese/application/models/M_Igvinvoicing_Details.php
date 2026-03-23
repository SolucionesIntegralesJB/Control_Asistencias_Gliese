<?php
class M_Igvinvoicing_Details extends Model {
     private $last_correlative;
    public function create_invoice($header_data, $details) {
        $total_venta = 0;
        $total_igv = 0;
        $total_taxable = 0;
foreach ($details as $item) {
    $cantidad = $item['quantity'] ?? 0;
    $precio_unitario = $item['sale_price'] ?? 0;
    $igv = $header_data['igv'] ?? 18;
    $total_venta += ($cantidad * $precio_unitario);
    $total_taxable += ($cantidad * $precio_unitario) / (1 + ($igv / 100));
    $total_igv += ($total_venta - $total_taxable);
}
$header_data['total_venta'] = $total_venta;
$header_data['total_igv'] = $total_igv;
$header_data['total_taxable'] = $total_taxable;
$header_data['legend'] = 'SON: ' . number_format($total_venta, 2) . ' SOLES';
        try {
            $this->pdo->beginTransaction();

            // 1. Insertar cabecera (sin idUser)
            $invoice_id = $this->insert_header($header_data);

            // 2. Insertar detalles
            $this->insert_details($invoice_id, $details, $header_data['date_time']);

            $this->pdo->commit();

            return [
                'status' => 'OK',
                'message' => 'Factura creada correctamente',
                'data' => [
                    'invoice_id' => $invoice_id,
                    'series' => $header_data['series'],
                    'correlative' => $this->last_correlative  // se guarda al generar
                ]
            ];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'status' => 'ERROR',
                'message' => 'Error al crear factura: ' . $e->getMessage()
            ];
        }
    }

    public function client_exists($idUser) {
        $stmt = $this->pdo->prepare("SELECT id FROM person WHERE id = ?");
        $stmt->execute([$idUser]);
        return (bool)$stmt->fetch();
    }

    private function get_payment_type_id($payment_name) {
        $map = [
            'CONTADO' => 1,
            'CREDITO' => 2,
        ];
        return $map[strtoupper($payment_name)] ?? 1;
    }

    private function insert_header($data) {
        // 1. Validar cliente existente
        $idUser = $data['business_name_cli'] ?? null;
        if (!$this->client_exists($idUser)) {
            throw new Exception("El cliente con ID $idUser no existe", 400);
        }

        // 2. Obtener correlativo automáticamente
        $voucher_type = $data['vt_description'] ?? '01';
        $series = $data['series'] ?? 'F001';
        $correlative = $this->get_new_correlative($voucher_type, $series);
        $this->last_correlative = $correlative; // guardar para retornarlo luego


        
        // 3. Mapeo exacto a tu estructura de BD
        $insertData = [
            'user_id' => $data['user_id'] ?? 1,
            'client_id' => $idUser ?? 1,
            'voucher_type_code' => $voucher_type,
            'series' => $series,
            'correlative' => $correlative,
            'date_time' => $data['date_time'] ?? date('Y-m-d'),
            'due_date' => $data['due_date'] ?? $data['date_time'] ?? date('Y-m-d'),
            'currency' => 'PEN',
            'payment_type_code' => $this->get_payment_type_id($data['fp_description'] ?? 'CONTADO'),
            'tax' => $data['igv'] ?? 18.00,
            'taxable_operations' => round($data['total_taxable'], 2),  // Recibe de tv_gravado
            'total_igv' => round($data['total_igv'], 2),             // Recibe de total_igv
            'total_sale' => round($data['total_venta'], 2),
'legend' => $data['legend'] ?? '',
            'status' => 'PENDIENTE',
            'time' => date('H:i:s'),
            'assigned_igv' => $data['igv_asig'] ?? 18,
            'document_reason_id' => 1,
            'support' => 'ELECTRONICO',
            'related_document' => 0,
            'unique_voucher' => 'NO'
        ];

        // 4. Insertar cabecera
        $columns = implode(', ', array_keys($insertData));
        $placeholders = ':' . implode(', :', array_keys($insertData));

        $sql = "INSERT INTO igvinvoice ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($insertData);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error BD al insertar factura: ".$e->getMessage());
            throw new Exception("Error técnico al guardar factura. Detalles en log.");
        }
    }

    private function insert_details($invoice_id, $details, $sale_date) {
        $sql = "INSERT INTO igvinvoice_detail (
            invoice_id, product_code, product_description, unit_of_measure,
            quantity, sale_price, affectation, sold_date
        ) VALUES (
            :invoice_id, :product_code, :product_description, :unit_of_measure,
            :quantity, :sale_price, :affectation, :sold_date
        )";
        
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($details as $detail) {
            $data = [
                'invoice_id' => $invoice_id,
                'product_code' => $detail['product_code'] ?? 'SERV' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'product_description' => $detail['product_description'] ?? 'Servicio general',
                'unit_of_measure' => $detail['unit_of_measure'] ?? 'NIU',
                'quantity' => $detail['quantity'] ?? 1,
                'sale_price' => $detail['sale_price'] ?? 0,
                'affectation' => $detail['affectation'] ?? 'GRAVADA',
                'sold_date' => $sale_date
            ];
            
            $stmt->execute($data);
        }
    }

    private function get_new_correlative($voucher_type_code, $series) {
        $sql = "SELECT MAX(CAST(correlative AS UNSIGNED)) AS max_correlative
                FROM igvinvoice
                WHERE voucher_type_code = :voucher_type_code AND series = :series";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'voucher_type_code' => $voucher_type_code,
            'series' => $series
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next = (int)$result['max_correlative'] + 1;
        return str_pad($next, 8, '0', STR_PAD_LEFT);
    }

    public function get_series($voucher_type) {
        $sql = "SELECT 
            CASE 
                WHEN :voucher_type = 1 THEN 'F001' 
                WHEN :voucher_type = 2 THEN 'B001' 
                ELSE 'T001' 
            END as series,
            LPAD(IFNULL(
                (SELECT MAX(CAST(correlative AS UNSIGNED)) + 1 
                FROM igvinvoice 
                WHERE voucher_type_code = :voucher_type
            ), 1), 8, '0') as correlative";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['voucher_type' => $voucher_type]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update_correlative($voucher_type, $correlative) {
        return true;
    }
}   