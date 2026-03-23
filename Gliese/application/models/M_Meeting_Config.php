<?php
/**
 * Modelo de Configuraciones de Reuniones
 * Operaciones de BD para tabla meeting_configs
 */
class M_Meeting_Config extends Model {

    public function __construct() {
        parent::__construct();
    }

    // Obtener todas las configuraciones
    public function get_all_configs() {
        try {
            $sql = "SELECT
                    mc.id,
                    mc.name,
                    mc.meet_link,
                    mc.schedule,
                    mc.description,
                    mc.created_at,
                    mc.updated_at,
                    (SELECT COUNT(*) FROM mi_grupo WHERE meeting_config_id = mc.id) as groups_count
                    FROM meeting_configs mc
                    ORDER BY mc.created_at DESC";

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                return ['status' => 'OK', 'result' => $result];
            } else {
                return ['status' => 'ERROR', 'msg' => 'No hay configuraciones registradas.'];
            }
        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
    }

    // Crear nueva configuración
    public function create_config($data) {
        try {
            $sql = "INSERT INTO meeting_configs (name, meet_link, schedule, description, created_by)
                    VALUES (:name, :meet_link, :schedule, :description, :created_by)";

            $params = [
                'name' => $data['name'],
                'meet_link' => $data['meet_link'],
                'schedule' => isset($data['schedule']) ? json_encode($data['schedule']) : null,
                'description' => $data['description'] ?? null,
                'created_by' => $data['created_by'] ?? null
            ];

            $result = $this->pdo->perform($sql, $params);

            if ($result !== false) {
                return ['status' => 'OK', 'result' => $this->pdo->lastInsertId()];
            } else {
                return ['status' => 'ERROR', 'msg' => 'Error al crear la configuración.'];
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['status' => 'ERROR', 'msg' => 'Ya existe una configuración con ese nombre.'];
            }
            return ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
    }

    // Actualizar configuración existente
    public function update_config($data) {
        try {
            $fields = [];
            $params = ['id' => $data['id']];

            if (isset($data['name'])) {
                $fields[] = 'name = :name';
                $params['name'] = $data['name'];
            }

            if (isset($data['meet_link'])) {
                $fields[] = 'meet_link = :meet_link';
                $params['meet_link'] = $data['meet_link'];
            }

            if (isset($data['schedule'])) {
                $fields[] = 'schedule = :schedule';
                $params['schedule'] = is_array($data['schedule']) ? json_encode($data['schedule']) : $data['schedule'];
            }

            if (isset($data['description'])) {
                $fields[] = 'description = :description';
                $params['description'] = $data['description'];
            }

            if (empty($fields)) {
                return ['status' => 'ERROR', 'msg' => 'No hay datos para actualizar.'];
            }

            $sql = 'UPDATE meeting_configs SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $result = $this->pdo->perform($sql, $params);

            if ($result !== false) {
                return ['status' => 'OK'];
            } else {
                return ['status' => 'ERROR', 'msg' => 'Error al actualizar la configuración.'];
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['status' => 'ERROR', 'msg' => 'Ya existe una configuración con ese nombre.'];
            }
            return ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
    }

    // Eliminar configuración
    public function delete_config($id) {
        try {
            // Primero desasignar de los grupos
            $sql_unassign = "UPDATE mi_grupo SET meeting_config_id = NULL WHERE meeting_config_id = :id";
            $this->pdo->perform($sql_unassign, ['id' => $id]);

            // Luego eliminar la configuración
            $sql = "DELETE FROM meeting_configs WHERE id = :id";
            $result = $this->pdo->perform($sql, ['id' => $id]);

            if ($result !== false) {
                return ['status' => 'OK'];
            } else {
                return ['status' => 'ERROR', 'msg' => 'Error al eliminar la configuración.'];
            }
        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
    }

    // Asignar configuración a múltiples grupos
    public function assign_to_groups($config_id, $group_ids) {
        try {
            // Verificar que la configuración existe
            $sql_check = "SELECT id FROM meeting_configs WHERE id = :id";
            $exists = $this->pdo->fetchOne($sql_check, ['id' => $config_id]);

            if (!$exists) {
                return ['status' => 'ERROR', 'msg' => 'La configuración no existe.'];
            }

            // Actualizar grupos
            $placeholders = implode(',', array_fill(0, count($group_ids), '?'));
            $sql = "UPDATE mi_grupo SET meeting_config_id = ? WHERE id IN ($placeholders)";

            $params = array_merge([$config_id], $group_ids);
            $result = $this->pdo->perform($sql, $params);

            if ($result !== false) {
                return ['status' => 'OK'];
            } else {
                return ['status' => 'ERROR', 'msg' => 'Error al asignar la configuración.'];
            }
        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
    }

    // Obtener grupos asignados a una configuración
    public function get_assigned_groups($config_id) {
        try {
            $sql = "SELECT
                    id,
                    nombre,
                    descripcion,
                    responsable
                    FROM mi_grupo
                    WHERE meeting_config_id = :config_id
                    ORDER BY nombre ASC";

            $result = $this->pdo->fetchAll($sql, ['config_id' => $config_id]);

            if ($result) {
                return ['status' => 'OK', 'result' => $result];
            } else {
                return ['status' => 'ERROR', 'msg' => 'No hay grupos asignados.'];
            }
        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
    }
}
