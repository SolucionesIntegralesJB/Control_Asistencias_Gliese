<?php
// --
class M_Cotize extends Model
{
    // --
    protected $pdo;

    // --
    
    // --
    public function get_client_data($id_client)
    {
        try {
            $sql = 'SELECT 
                        p.document_number,
                        p.address,
                        p.name as business_name,
                        dt.description as document_type,
                        p.phone,
                        p.email,
                        p.contact_person
                    FROM person p
                    LEFT JOIN document_type dt ON dt.id = p.document_type_id
                    WHERE p.id = :id_client AND p.status = 1';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_client' => $id_client]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                return array(
                    'status' => 'OK',
                    'result' => $result
                );
            }

            return array(
                'status' => 'ERROR',
                'message' => 'Cliente no encontrado',
                'result' => array()
            );
        } catch (PDOException $e) {
            return array(
                'status' => 'ERROR',
                'message' => 'Error al obtener datos del cliente: ' . $e->getMessage(),
                'result' => array()
            );
        }
    }

    // --
    public function save_cotize($bind)
    {
        try {
            $this->pdo->beginTransaction();

            // Validar datos requeridos
            if (empty($bind['person_id']) || empty($bind['user_id']) || !is_array($bind['details'])) {
                throw new Exception('Faltan datos requeridos para guardar la cotización');
            }

            // Insertar cotización principal
            $sql = 'INSERT INTO cotize (person_id, user_id, date_issue, reference, 
                    cotize_type, offer_validity, subtotal, igv, total, status) 
                    VALUES (:person_id, :user_id, :date_issue, :reference, :cotize_type, 
                    :offer_validity, :subtotal, :igv, :total, 1)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'person_id' => $bind['person_id'],
                'user_id' => $bind['user_id'],
                'date_issue' => $bind['date_issue'],
                'reference' => $bind['reference'],
                'cotize_type' => $bind['cotize_type'],
                'offer_validity' => $bind['offer_validity'],
                'subtotal' => $bind['subtotal'],
                'igv' => $bind['igv'],
                'total' => $bind['total']
            ]);

            $cotize_id = $this->pdo->lastInsertId();

            // Insertar detalles
            $sql_detail = 'INSERT INTO cotize_details (cotize_id, product_id, 
                        description, quantity, unit_price, subtotal) 
                        VALUES (:cotize_id, :product_id, :description, :quantity, 
                        :unit_price, :subtotal)';

            $stmt_detail = $this->pdo->prepare($sql_detail);

            foreach ($bind['details'] as $detail) {
                $stmt_detail->execute([
                    'cotize_id' => $cotize_id,
                    'product_id' => $detail['product_id'] ?? null,
                    'description' => $detail['description'],
                    'quantity' => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal' => $detail['subtotal']
                ]);
            }

            $this->pdo->commit();
            return array(
                'status' => 'OK',
                'result' => array('cotize_id' => $cotize_id)
            );
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return array(
                'status' => 'ERROR',
                'message' => 'Error al guardar la cotización: ' . $e->getMessage()
            );
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return array(
                'status' => 'ERROR',
                'message' => $e->getMessage()
            );
        }
    }

    // --
    public function get_cotize_by_id($id_cotize)
    {
        try {
            $sql = 'SELECT 
                        c.*,
                        p.name as client_name,
                        p.document_number,
                        p.address as client_address,
                        p.phone as client_phone,
                        p.email as client_email,
                        u.user as user_name
                    FROM cotize c
                    LEFT JOIN person p ON p.id = c.person_id
                    LEFT JOIN user u ON u.id = c.user_id
                    WHERE c.id = :id_cotize';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id_cotize' => $id_cotize]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Obtener detalles
                $sql_details = 'SELECT 
                                cd.*,
                                pr.name as product_name,
                                pr.code as product_code
                            FROM cotize_details cd
                            LEFT JOIN products pr ON pr.id = cd.product_id
                            WHERE cd.cotize_id = :cotize_id';

                $stmt_details = $this->pdo->prepare($sql_details);
                $stmt_details->execute(['cotize_id' => $id_cotize]);
                $result['details'] = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

                return array(
                    'status' => 'OK',
                    'result' => $result
                );
            }

            return array(
                'status' => 'ERROR',
                'message' => 'Cotización no encontrada',
                'result' => array()
            );
        } catch (PDOException $e) {
            return array(
                'status' => 'ERROR',
                'message' => 'Error al obtener la cotización: ' . $e->getMessage(),
                'result' => array()
            );
        }
    }

    // --
    public function update_cotize_status($bind)
    {
        try {
            if (empty($bind['id_cotize']) || !isset($bind['status'])) {
                throw new Exception('Faltan datos requeridos para actualizar el estado');
            }
            
            $sql = 'UPDATE cotize SET status = :status WHERE id = :id_cotize';
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'status' => $bind['status'], 
                'id_cotize' => $bind['id_cotize']
            ]);

            if ($result) {
                return array(
                    'status' => 'OK',
                    'message' => 'Estado actualizado correctamente',
                    'result' => array()
                );
            }

            return array(
                'status' => 'ERROR',
                'message' => 'No se pudo actualizar el estado',
                'result' => array()
            );
        } catch (PDOException $e) {
            return array(
                'status' => 'ERROR',
                'message' => 'Error al actualizar el estado: ' . $e->getMessage(),
                'result' => array()
            );
        } catch (Exception $e) {
            return array(
                'status' => 'ERROR',
                'message' => $e->getMessage(),
                'result' => array()
            );
        }
    }

    // --
    public function get_cotize()
    {
        try {
            $sql = "SELECT 
                        c.id,
                        c.date_issue,
                        c.reference as number,
                        c.cotize_type,
                        c.offer_validity,
                        c.subtotal,
                        c.igv,
                        c.total,
                        c.status,
                        p.name as client_name,
                        p.document_number
                    FROM cotize c
                    LEFT JOIN person p ON p.id = c.person_id
                    WHERE c.status != 0
                    ORDER BY c.date_issue DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array(
                'status' => 'OK',
                'result' => $result
            );
        } catch (PDOException $e) {
            return array(
                'status' => 'ERROR',
                'message' => 'Error al obtener las cotizaciones: ' . $e->getMessage(),
                'result' => array()
            );
        }
    }

    // --
    public function delete_cotize($bind)
    {
        try {
            if (empty($bind['id'])) {
                throw new Exception('ID de cotización no proporcionado');
            }
            
            $this->pdo->beginTransaction();

            // Actualizar el estado de la cotización a 0 (eliminado)
            $sql = "UPDATE cotize SET status = 0 WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $bind['id']]);
            
            $this->pdo->commit();
            
            return array(
                'status' => 'OK',
                'message' => 'Cotización eliminada correctamente',
                'result' => array()
            );
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return array(
                'status' => 'ERROR',
                'message' => 'Error al eliminar la cotización: ' . $e->getMessage(),
                'result' => array()
            );
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return array(
                'status' => 'ERROR',
                'message' => $e->getMessage(),
                'result' => array()
            );
        }
    }

    public function cancel_cotize($bind)
    {
        try {
            // Validar que se recibió el ID de la cotización
            if (empty($bind['cotize_id'])) {
                throw new Exception('ID de cotización no proporcionado');
            }
    
            // Actualizar el estado de la cotización a 0 (anulado)
            $sql = 'UPDATE cotize SET status = 0 WHERE id = :cotize_id';
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                'cotize_id' => $bind['cotize_id']
            ]);
    
            if ($result) {
                return [
                    'status' => 'OK',
                    'message' => 'Cotización anulada correctamente'
                ];
            }
    
            return [
                'status' => 'ERROR',
                'message' => 'No se pudo anular la cotización'
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'result' => $e
            ];
        }
    }
}