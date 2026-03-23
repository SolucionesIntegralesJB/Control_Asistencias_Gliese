<?php
// --
class M_Cotize_Details extends Model {
    
    // --
    protected $pdo;
    
    // --


    /**
     * Verifica si una referencia ya existe en la tabla cotize
     * @param string $reference La referencia a verificar
     * @param int|null $exclude_id ID de cotización a excluir de la verificación (opcional)
     * @return bool True si la referencia existe, False si no existe
     */
    public function check_if_reference_exists($reference, $exclude_id = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM cotize WHERE reference = :reference";
            $params = ['reference' => $reference];
            
            if ($exclude_id !== null) {
                $sql .= " AND id != :exclude_id";
                $params['exclude_id'] = $exclude_id;
            }
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return ($result['count'] > 0);
            
        } catch (PDOException $e) {
            error_log("Error al verificar referencia: " . $e->getMessage());
            return false;
        }
    }
    
    public function get_client_by_id($id_client) {
        try {
            $sql = "SELECT 
                        p.id,
                        p.document_type_id,
                        p.document_number,
                        p.name,
                        p.address,
                        p.phone,
                        p.email,
                        p.contact_person,
                        dt.description as document_type_name
                    FROM person p 
                    LEFT JOIN document_type dt ON dt.id = p.document_type_id
                    WHERE p.id = :id_client 
                    AND p.status = 1";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_client' => $id_client]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return [
                    'status' => 'OK',
                    'result' => [
                        'id' => $result['id'],
                        'document_number' => $result['document_number'],
                        'name' => $result['name'],
                        'address' => $result['address'],
                        'phone' => $result['phone'],
                        'email' => $result['email'],
                        'contact_person' => $result['contact_person'],
                        'document_type' => $result['document_type_name']
                    ]
                ];
            }

            return [
                'status' => 'ERROR',
                'message' => 'Cliente no encontrado'
            ];

        } catch (PDOException $e) {
            return [
                'status' => 'ERROR',
                'message' => 'Error al obtener datos del cliente: ' . $e->getMessage()
            ];
        }
    }

    public function save_cotize_details($data) {
        try {
            $this->pdo->beginTransaction();

            // Validar y formatear valores numéricos
            $subtotal = !empty($data['subtotal']) ? number_format((float)$data['subtotal'], 2, '.', '') : '0.00';
            $igv = !empty($data['igv']) ? number_format((float)$data['igv'], 2, '.', '') : '0.00';
            $total = !empty($data['total']) ? number_format((float)$data['total'], 2, '.', '') : '0.00';
            
            // Verificar si ya existe una cotización con la misma referencia y fecha
            $checkSql = "SELECT COUNT(*) FROM cotize WHERE reference = :reference AND date_issue = :date_issue";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->execute([
                'reference' => $data['reference'],
                'date_issue' => $data['date_issue']
            ]);
            
            if ($checkStmt->fetchColumn() > 0) {
                throw new Exception('Ya existe una cotización con la misma referencia y fecha');
            }

            // Insertar en la tabla cotize
            $sql = "INSERT INTO cotize (
                person_id,
                user_id,
                date_issue,
                reference,
                cotize_type,
                offer_validity,
                subtotal,
                igv,
                total,
                status
            ) VALUES (
                :person_id,
                :user_id,
                :date_issue,
                :reference,
                :cotize_type,
                :offer_validity,
                :subtotal,
                :igv,
                :total,
                1
            )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'person_id' => $data['business_name_cli'],
                'user_id' => $data['user_id'],
                'date_issue' => date('Y-m-d'),
                'reference' => $data['referencia'] ?? '',
                'cotize_type' => $data['pt_description'] ?? 'Regular',
                'offer_validity' => $data['validez_oferta'],
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total
            ]);

            $cotize_id = $this->pdo->lastInsertId();

            // Insertar detalles
            $sql_detail = "INSERT INTO cotize_details (
                cotize_id,
                description,
                quantity,
                unit_price,
                subtotal
            ) VALUES (
                :cotize_id,
                :description,
                :quantity,
                :unit_price,
                :subtotal
            )";

            $stmt_detail = $this->pdo->prepare($sql_detail);

            foreach ($data['details'] as $detail) {
                $detail_subtotal = number_format((float)$detail['quantity'] * (float)$detail['unit_price'], 2, '.', '');
                
                $stmt_detail->execute([
                    'cotize_id' => $cotize_id,
                    'description' => $detail['description'],
                    'quantity' => number_format((float)$detail['quantity'], 2, '.', ''),
                    'unit_price' => number_format((float)$detail['unit_price'], 2, '.', ''),
                    'subtotal' => $detail_subtotal
                ]);
            }

            $this->pdo->commit();
            return [
                'status' => 'OK',
                'message' => 'Cotización guardada correctamente',
                'cotize_id' => $cotize_id
            ];

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return [
                'status' => 'ERROR',
                'message' => 'Error al guardar la cotización: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }
    }

    public function get_quotations() {
        try {
            $sql = "SELECT 
                    c.id,
                    c.person_id,
                    c.user_id,
                    c.date_issue,
                    c.reference,
                    c.cotize_type,
                    c.offer_validity,
                    c.subtotal,
                    c.igv,
                    c.total,
                    c.status,
                    p.name as client_name,
                    p.document_number,
                    u.username as user_name
                FROM cotize c
                INNER JOIN person p ON p.id = c.person_id
                INNER JOIN users u ON u.id = c.user_id
                WHERE c.status = 1
                ORDER BY c.date_issue DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return [
                'status' => 'OK',
                'result' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];

        } catch (PDOException $e) {
            return [
                'status' => 'ERROR',
                'message' => 'Error al obtener las cotizaciones: ' . $e->getMessage()
            ];
        }
    }

    public function get_quotation_details($cotize_id) {
        try {
            $sql = "SELECT 
                    cd.id,
                    cd.cotize_id,
                    cd.description,
                    cd.quantity,
                    cd.unit_price,
                    cd.subtotal
                FROM cotize_details cd
                WHERE cd.cotize_id = :cotize_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['cotize_id' => $cotize_id]);

            return [
                'status' => 'OK',
                'result' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];

        } catch (PDOException $e) {
            return [
                'status' => 'ERROR',
                'message' => 'Error al obtener los detalles de la cotización: ' . $e->getMessage()
            ];
        }
    }

    public function get_cotize_by_id($id) {
        try {
            // Consulta para obtener los datos de la cotización
            $sql = "SELECT c.*, p.name as client_name, p.document_number as client_document_number, p.address as client_address 
                    FROM cotize c 
                    LEFT JOIN person p ON c.person_id = p.id 
                    WHERE c.id = :id";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $cotize = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cotize) {
                return [
                    'status' => 'ERROR',
                    'message' => 'Cotización no encontrada'
                ];
            }
            
            // Consulta para obtener los detalles de la cotización
            $sql_details = "SELECT * FROM cotize_details WHERE cotize_id = :cotize_id";
            $stmt_details = $this->pdo->prepare($sql_details);
            $stmt_details->execute(['cotize_id' => $id]);
            $details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);
            
            // Agregar los detalles a la cotización
            $cotize['details'] = $details;
            
            return [
                'status' => 'OK',
                'result' => $cotize
            ];
            
        } catch (PDOException $e) {
            return [
                'status' => 'ERROR',
                'message' => 'Error al obtener la cotización: ' . $e->getMessage()
            ];
        }
    }
    
    public function update_cotize_details($data) {
        try {
            $this->pdo->beginTransaction();
            
            // Validar y formatear valores numéricos
            $subtotal = !empty($data['subtotal']) ? number_format((float)$data['subtotal'], 2, '.', '') : '0.00';
            $igv = !empty($data['igv']) ? number_format((float)$data['igv'], 2, '.', '') : '0.00';
            $total = !empty($data['total']) ? number_format((float)$data['total'], 2, '.', '') : '0.00';
            
            // Actualizar la tabla cotize
            $sql = "UPDATE cotize SET 
                person_id = :person_id,
                reference = :reference,
                cotize_type = :cotize_type,
                offer_validity = :offer_validity,
                subtotal = :subtotal,
                igv = :igv,
                total = :total
                WHERE id = :cotize_id";
                
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'person_id' => $data['business_name_cli'],
                'reference' => $data['referencia'] ?? '',
                'cotize_type' => $data['pt_description'] ?? 'Regular',
                'offer_validity' => $data['validez_oferta'],
                'subtotal' => $subtotal,
                'igv' => $igv,
                'total' => $total,
                'cotize_id' => $data['cotize_id']
            ]);
            
            // Eliminar detalles anteriores
            $sql_delete = "DELETE FROM cotize_details WHERE cotize_id = :cotize_id";
            $stmt_delete = $this->pdo->prepare($sql_delete);
            $stmt_delete->execute(['cotize_id' => $data['cotize_id']]);
            
            // Insertar nuevos detalles
            $sql_detail = "INSERT INTO cotize_details (
                cotize_id,
                description,
                quantity,
                unit_price,
                subtotal
            ) VALUES (
                :cotize_id,
                :description,
                :quantity,
                :unit_price,
                :subtotal
            )";
            
            $stmt_detail = $this->pdo->prepare($sql_detail);
            
            foreach ($data['details'] as $detail) {
                $detail_subtotal = number_format((float)$detail['quantity'] * (float)$detail['unit_price'], 2, '.', '');
                
                $stmt_detail->execute([
                    'cotize_id' => $data['cotize_id'],
                    'description' => $detail['description'],
                    'quantity' => number_format((float)$detail['quantity'], 2, '.', ''),
                    'unit_price' => number_format((float)$detail['unit_price'], 2, '.', ''),
                    'subtotal' => $detail_subtotal
                ]);
            }
            
            $this->pdo->commit();
            return [
                'status' => 'OK',
                'message' => 'Cotización actualizada correctamente',
                'cotize_id' => $data['cotize_id']
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return [
                'status' => 'ERROR',
                'message' => 'Error al actualizar la cotización: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }
    }
}