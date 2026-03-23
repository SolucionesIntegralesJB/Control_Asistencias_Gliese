<?php
/**
 * Modelo de Grupos (M_P_Group)
 * Operaciones de BD para tabla mi_grupo
 */
class M_P_Group extends Model {

    public function __construct() {
        parent::__construct();
    }

    // Obtiene grupos según el rol del usuario
    public function get_groups_by_role($id_user, $role) {
        try {
            if ($role === 'practicante') {
                // Si es practicante, obtener solo su grupo asignado
                $sql = "SELECT DISTINCT
                        mg.id,
                        mg.nombre,
                        mg.descripcion,
                        mg.responsable,
                        COALESCE(ml.meet_url, mg.meet_link) as meet_link,
                        COALESCE(ml.description, mg.schedule) as schedule,
                        ml.name as meet_link_name,
                        DATE_FORMAT(mg.fecha_creacion, '%d/%m/%Y') as fecha_creacion,
                        (SELECT COUNT(*) FROM practicantes WHERE grupo_id = mg.id) as current_members,
                        mg.capacity
                        FROM mi_grupo mg
                        INNER JOIN practicantes p ON p.grupo_id = mg.id
                        LEFT JOIN student_groups sg ON p.group = sg.group_code
                        LEFT JOIN group_meet_links gml ON gml.group_id = sg.id
                        LEFT JOIN meet_links ml ON ml.id = gml.meet_link_id
                        WHERE p.id_user = :id_user
                        ORDER BY mg.fecha_creacion DESC";
                $result = $this->pdo->fetchAll($sql, array('id_user' => $id_user));
            } else if ($role === 'supervisor') {
                // Si es supervisor, obtener todos los grupos que supervisa
                $sql = "SELECT DISTINCT
                        mg.id,
                        mg.nombre,
                        mg.descripcion,
                        mg.responsable,
                        COALESCE(ml.meet_url, mg.meet_link) as meet_link,
                        COALESCE(ml.description, mg.schedule) as schedule,
                        ml.name as meet_link_name,
                        DATE_FORMAT(mg.fecha_creacion, '%d/%m/%Y') as fecha_creacion,
                        (SELECT COUNT(*) FROM practicantes WHERE grupo_id = mg.id) as current_members,
                        mg.capacity
                        FROM mi_grupo mg
                        INNER JOIN supervisores s ON s.grupo_id = mg.id
                        LEFT JOIN student_groups sg ON sg.group_code = (
                            SELECT p.group FROM practicantes p WHERE p.grupo_id = mg.id LIMIT 1
                        )
                        LEFT JOIN group_meet_links gml ON gml.group_id = sg.id
                        LEFT JOIN meet_links ml ON ml.id = gml.meet_link_id
                        WHERE s.id_user = :id_user
                        ORDER BY mg.fecha_creacion DESC";
                $result = $this->pdo->fetchAll($sql, array('id_user' => $id_user));
            } else {
                // Para administradores u otros roles, mostrar todos los grupos
                $sql = "SELECT
                        mg.id,
                        mg.nombre,
                        mg.descripcion,
                        mg.responsable,
                        COALESCE(ml.meet_url, mg.meet_link) as meet_link,
                        COALESCE(ml.description, mg.schedule) as schedule,
                        ml.name as meet_link_name,
                        DATE_FORMAT(mg.fecha_creacion, '%d/%m/%Y') as fecha_creacion,
                        (SELECT COUNT(*) FROM practicantes WHERE grupo_id = mg.id) as current_members,
                        mg.capacity
                        FROM mi_grupo mg
                        LEFT JOIN group_meet_links gml ON gml.group_id = mg.id
                        LEFT JOIN meet_links ml ON ml.id = gml.meet_link_id
                        ORDER BY mg.fecha_creacion DESC";
                $result = $this->pdo->fetchAll($sql);
            }

            if ($result) {
                return array('status' => 'OK', 'result' => $result);
            } else {
                return array('status' => 'ERROR', 'msg' => 'No hay grupos asignados.');
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Obtiene todos los grupos ordenados por fecha
    public function get_group() {
        try {
            $sql = "SELECT id, nombre, descripcion, responsable, 
                    DATE_FORMAT(fecha_creacion, '%d/%m/%Y') as fecha_creacion
                    FROM mi_grupo 
                    ORDER BY fecha_creacion DESC";
            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                return array('status' => 'OK', 'result' => $result);
            } else {
                return array('status' => 'ERROR', 'msg' => 'No hay grupos registrados.');
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Obtiene un grupo por ID
    public function get_group_by_id($id) {
        try {
            $sql = "SELECT id, nombre, descripcion, responsable,
                    DATE_FORMAT(fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion
                    FROM mi_grupo 
                    WHERE id = :id";
            $result = $this->pdo->fetchOne($sql, array('id' => $id));

            if ($result) {
                return array('status' => 'OK', 'result' => $result);
            } else {
                return array('status' => 'ERROR', 'msg' => 'Grupo no encontrado.');
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Crea un nuevo grupo
    public function crear_group($data) {
        try {
            $sql = "INSERT INTO mi_grupo (nombre, descripcion, responsable, fecha_creacion) 
                    VALUES (:nombre, :descripcion, :responsable, NOW())";
            
            $params = array(
                'nombre' => $data['nombre'],
                'descripcion' => isset($data['descripcion']) ? $data['descripcion'] : null,
                'responsable' => isset($data['responsable']) ? $data['responsable'] : null
            );
            
            $result = $this->pdo->perform($sql, $params);
            
            if ($result !== false) {
                return array('status' => 'OK', 'result' => $this->pdo->lastInsertId());
            } else {
                return array('status' => 'ERROR', 'msg' => 'Error al insertar el grupo.');
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Actualiza un grupo existente
    public function editar_group($data) {
        try {
            $fields = array();
            $params = array('id' => $data['id']);
            
            if (isset($data['nombre'])) {
                $fields[] = "nombre = :nombre";
                $params['nombre'] = $data['nombre'];
            }
            if (isset($data['descripcion'])) {
                $fields[] = "descripcion = :descripcion";
                $params['descripcion'] = $data['descripcion'];
            }
            if (isset($data['responsable'])) {
                $fields[] = "responsable = :responsable";
                $params['responsable'] = $data['responsable'];
            }

            if (empty($fields)) {
                return array('status' => 'ERROR', 'msg' => 'No hay campos para actualizar.');
            }

            $sql = "UPDATE mi_grupo SET " . implode(', ', $fields) . " WHERE id = :id";
            $result = $this->pdo->perform($sql, $params);

            if ($result !== false) {
                return array('status' => 'OK', 'result' => 'Grupo actualizado.');
            } else {
                return array('status' => 'ERROR', 'msg' => 'Error al actualizar el grupo.');
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // Elimina un grupo
    public function eliminar_group($id) {
        try {
            $sql = "DELETE FROM mi_grupo WHERE id = :id";
            $result = $this->pdo->perform($sql, array('id' => $id));

            if ($result !== false) {
                return array('status' => 'OK', 'result' => 'Grupo eliminado.');
            } else {
                return array('status' => 'ERROR', 'msg' => 'Error al eliminar el grupo.');
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'result' => $e);
        }
    }

    // ==================== GESTIÓN DE ENLACES MEET ====================

    // Obtener todos los enlaces Meet con cantidad de grupos asignados
    public function get_all_meet_links() {
        try {
            // Obtener enlaces junto con los grupos asignados (lista de ids) y el conteo
            $sql = "SELECT
                ml.id,
                ml.name,
                ml.meet_url,
                ml.description,
                ml.created_at,
                COUNT(gml.group_id) as groups_count,
                GROUP_CONCAT(gml.group_id SEPARATOR ',') as assigned_group_ids
                FROM meet_links ml
                LEFT JOIN group_meet_links gml ON gml.meet_link_id = ml.id
                GROUP BY ml.id
                ORDER BY ml.created_at DESC";

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                return array('status' => 'OK', 'result' => $result);
            } else {
                return array('status' => 'ERROR', 'msg' => 'No hay enlaces registrados.');
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'msg' => $e->getMessage());
        }
    }

    // Crear nuevo enlace Meet
    public function create_meet_link($data) {
        try {
            $sql = "INSERT INTO meet_links (name, meet_url, description)
                    VALUES (:name, :meet_url, :description)";

            $params = array(
                'name' => $data['name'],
                'meet_url' => $data['meet_url'],
                'description' => $data['description'] ?? null
            );

            $result = $this->pdo->perform($sql, $params);

            if ($result !== false) {
                return array('status' => 'OK', 'result' => $this->pdo->lastInsertId());
            } else {
                return array('status' => 'ERROR', 'msg' => 'Error al crear el enlace.');
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return array('status' => 'ERROR', 'msg' => 'Ya existe un enlace con ese nombre.');
            }
            return array('status' => 'EXCEPTION', 'msg' => $e->getMessage());
        }
    }

    // Asignar enlace Meet a múltiples grupos
    public function assign_meet_link_to_groups($meet_link_id, $group_ids, $assigned_by) {
        try {
            // Primero eliminar asignaciones anteriores de estos grupos
            if (!empty($group_ids)) {
                $placeholders = implode(',', array_fill(0, count($group_ids), '?'));
                $sql_delete = "DELETE FROM group_meet_links WHERE group_id IN ($placeholders)";
                $this->pdo->perform($sql_delete, $group_ids);
            }

            // Insertar nuevas asignaciones
            $sql = "INSERT INTO group_meet_links (group_id, meet_link_id, assigned_by)
                    VALUES (:group_id, :meet_link_id, :assigned_by)";

            foreach ($group_ids as $group_id) {
                $params = array(
                    'group_id' => $group_id,
                    'meet_link_id' => $meet_link_id,
                    'assigned_by' => $assigned_by
                );
                $this->pdo->perform($sql, $params);
            }

            return array('status' => 'OK');
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'msg' => $e->getMessage());
        }
    }

    // Eliminar un enlace Meet y sus asignaciones
    public function delete_meet_link($id) {
        try {
            // Eliminar asignaciones en group_meet_links primero
            $this->pdo->perform('DELETE FROM group_meet_links WHERE meet_link_id = :id', array('id' => $id));

            // Eliminar el enlace
            $result = $this->pdo->perform('DELETE FROM meet_links WHERE id = :id', array('id' => $id));

            if ($result !== false) {
                return array('status' => 'OK', 'msg' => 'Enlace eliminado correctamente.');
            } else {
                return array('status' => 'ERROR', 'msg' => 'Error al eliminar el enlace.');
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'msg' => $e->getMessage());
        }
    }

    // Obtener todos los horarios de practicantes
    public function get_all_student_schedules() {
        try {
            $sql = "SELECT
                    ss.id,
                    ss.student_id,
                    ss.group_id,
                    ss.schedule_file,
                    ss.file_type,
                    ss.uploaded_at,
                    ss.status,
                    ss.admin_notes,
                    p.name as student_name,
                    p.last_name as student_lastname,
                    mg.nombre as group_name
                    FROM student_schedules ss
                    INNER JOIN practicantes p ON p.id = ss.student_id
                    LEFT JOIN mi_grupo mg ON mg.id = ss.group_id
                    ORDER BY ss.uploaded_at DESC";

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                return array('status' => 'OK', 'result' => $result);
            } else {
                return array('status' => 'ERROR', 'msg' => 'No hay horarios registrados.');
            }
        } catch (PDOException $e) {
            return array('status' => 'EXCEPTION', 'msg' => $e->getMessage());
        }
    }
}
