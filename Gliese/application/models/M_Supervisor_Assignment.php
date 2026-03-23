<?php
/**
 * ============================================================================
 * MODELO: Asignación de Supervisores a Grupos (M_Supervisor_Assignment)
 * ============================================================================
 *
 * PROPÓSITO:
 * Gestiona la asignación de supervisores a grupos de practicantes.
 *
 * FUNCIONALIDAD:
 * - Asignar supervisores a grupos
 * - Desasignar supervisores de grupos
 * - Listar supervisores disponibles
 * - Listar grupos de un supervisor
 * - Listar supervisores de un grupo
 *
 * TABLA PRINCIPAL: supervisor_groups
 *
 * AUTOR: Sistema Gliese
 * FECHA: 2025-12-07
 * ============================================================================
 */
class M_Supervisor_Assignment extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtiene todos los supervisores disponibles en el sistema
     *
     * @return array Array con status y datos de supervisores
     */
    public function get_all_supervisors()
    {
        try {
            $sql = 'SELECT
                        u.id,
                        u.user AS username,
                        CONCAT(COALESCE(u.first_name, ""), " ", COALESCE(u.last_name, "")) AS nombre_completo,
                        u.email,
                        u.telephone AS telefono,
                        ur.description AS cargo,
                        "" AS area_supervision,
                        u.status
                    FROM user u
                    LEFT JOIN user_roles ur ON u.id_user_role = ur.id
                    WHERE u.id_user_role = 3
                    AND u.status = 1
                    ORDER BY u.first_name ASC';

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                return ['status' => 'OK', 'data' => $result];
            } else {
                return [
                    'status' => 'ERROR',
                    'msg' => 'No se encontraron supervisores en el sistema.',
                    'data' => []
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al obtener supervisores: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene todos los grupos disponibles
     *
     * @return array Array con status y datos de grupos
     */
    public function get_all_groups()
    {
        try {
            $sql = 'SELECT
                        id,
                        group_code,
                        group_name,
                        group_type,
                        current_members,
                        max_capacity,
                        status,
                        DATE_FORMAT(created_at, "%d/%m/%Y") AS created_date
                    FROM student_groups
                    WHERE status = 1
                    ORDER BY group_name ASC';

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                return ['status' => 'OK', 'data' => $result];
            } else {
                return [
                    'status' => 'ERROR',
                    'msg' => 'No se encontraron grupos en el sistema.',
                    'data' => []
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al obtener grupos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Asigna un supervisor a un grupo
     *
     * @param int $supervisor_id ID del supervisor
     * @param int $group_id ID del grupo
     * @param int $assigned_by ID del usuario que hace la asignación
     * @param string $supervisor_type Tipo de supervisor (principal/assistant/technical)
     * @return array Array con status y mensaje
     */
    public function assign_supervisor($supervisor_id, $group_id, $assigned_by = null, $supervisor_type = 'principal')
    {
        try {
            // Verificar si ya existe la asignación activa
            $sql_check = 'SELECT id, status FROM group_supervisors
                         WHERE supervisor_id = ? AND group_id = ?
                         LIMIT 1';

            $stmt = $this->pdo->prepare($sql_check);
            $stmt->execute([$supervisor_id, $group_id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Si ya existe pero está inactiva, reactivarla
                if ($existing['status'] == 0) {
                    $sql_update = 'UPDATE group_supervisors
                                  SET status = 1,
                                      assigned_by = ?,
                                      assignment_date = CURRENT_TIMESTAMP
                                  WHERE id = ?';

                    $stmt_update = $this->pdo->prepare($sql_update);
                    $stmt_update->execute([$assigned_by, $existing['id']]);

                    return [
                        'status' => 'OK',
                        'msg' => 'Asignación reactivada correctamente.'
                    ];
                } else {
                    return [
                        'status' => 'ERROR',
                        'msg' => 'Este supervisor ya está asignado a este grupo y la asignación está activa. Si deseas cambiar el tipo de supervisor, primero desasigna y vuelve a asignar.'
                    ];
                }
            }

            // Insertar nueva asignación
            $sql = 'INSERT INTO group_supervisors
                    (supervisor_id, group_id, supervisor_type, assigned_by, status)
                    VALUES (?, ?, ?, ?, 1)';

            $stmt_insert = $this->pdo->prepare($sql);
            $stmt_insert->execute([$supervisor_id, $group_id, $supervisor_type, $assigned_by]);

            return [
                'status' => 'OK',
                'msg' => 'Supervisor asignado correctamente al grupo.'
            ];

        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al asignar supervisor: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Desasigna un supervisor de un grupo (marca como inactivo)
     *
     * @param int $supervisor_id ID del supervisor
     * @param int $group_id ID del grupo
     * @return array Array con status y mensaje
     */
    public function unassign_supervisor($supervisor_id, $group_id)
    {
        try {
            $sql = 'UPDATE group_supervisors
                   SET status = 0
                   WHERE supervisor_id = ? AND group_id = ?';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$supervisor_id, $group_id]);

            return [
                'status' => 'OK',
                'msg' => 'Supervisor desasignado correctamente.'
            ];

        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al desasignar supervisor: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene los grupos asignados a un supervisor
     *
     * @param int $supervisor_id ID del supervisor
     * @return array Array con status y datos de grupos
     */
    public function get_supervisor_groups($supervisor_id)
    {
        try {
            $sql = 'SELECT
                        gs.id AS assignment_id,
                        grp.id AS group_id,
                        grp.group_code,
                        grp.group_name,
                        grp.group_type,
                        grp.current_members,
                        grp.max_capacity,
                        DATE_FORMAT(gs.assignment_date, "%d/%m/%Y") AS assigned_date,
                        gs.status,
                        gs.supervisor_type
                    FROM group_supervisors gs
                    INNER JOIN student_groups grp ON grp.id = gs.group_id
                    WHERE gs.supervisor_id = ?
                    AND gs.status = 1
                    ORDER BY gs.assignment_date DESC';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$supervisor_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                return ['status' => 'OK', 'data' => $result];
            } else {
                return [
                    'status' => 'ERROR',
                    'msg' => 'Este supervisor no tiene grupos asignados.',
                    'data' => []
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al obtener grupos del supervisor: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene los supervisores asignados a un grupo
     *
     * @param int $group_id ID del grupo
     * @return array Array con status y datos de supervisores
     */
    public function get_group_supervisors($group_id)
    {
        try {
            $sql = 'SELECT
                        gs.id AS assignment_id,
                        gs.supervisor_id,
                        u.user AS username,
                        CONCAT(COALESCE(u.first_name, ""), " ", COALESCE(u.last_name, "")) AS nombre_completo,
                        u.email,
                        u.telephone AS telefono,
                        ur.description AS cargo,
                        DATE_FORMAT(gs.assignment_date, "%d/%m/%Y") AS assigned_date,
                        gs.status,
                        gs.supervisor_type
                    FROM group_supervisors gs
                    LEFT JOIN user u ON u.id = gs.supervisor_id
                    LEFT JOIN user_roles ur ON u.id_user_role = ur.id
                    WHERE gs.group_id = ?
                    AND gs.status = 1
                    ORDER BY gs.assignment_date DESC';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$group_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                return ['status' => 'OK', 'data' => $result];
            } else {
                return [
                    'status' => 'ERROR',
                    'msg' => 'Este grupo no tiene supervisores asignados.',
                    'data' => []
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al obtener supervisores del grupo: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene todas las asignaciones con información completa
     *
     * @return array Array con status y datos de asignaciones
     */
    public function get_all_assignments()
    {
        try {
            $sql = 'SELECT
                        gs.id,
                        gs.supervisor_id,
                        u.user AS supervisor_username,
                        CONCAT(COALESCE(u.first_name, ""), " ", COALESCE(u.last_name, "")) AS supervisor_nombre,
                        u.email AS supervisor_email,
                        gs.group_id,
                        grp.group_code,
                        grp.group_name,
                        grp.current_members AS total_practicantes,
                        DATE_FORMAT(gs.assignment_date, "%d/%m/%Y") AS assigned_date,
                        gs.status,
                        gs.supervisor_type,
                        CONCAT(COALESCE(admin_u.first_name, ""), " ", COALESCE(admin_u.last_name, "")) AS assigned_by_name
                    FROM group_supervisors gs
                    LEFT JOIN user u ON u.id = gs.supervisor_id
                    INNER JOIN student_groups grp ON grp.id = gs.group_id
                    LEFT JOIN user admin_u ON admin_u.id = gs.assigned_by
                    WHERE gs.status = 1
                    ORDER BY gs.assignment_date DESC';

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($result) {
                return ['status' => 'OK', 'data' => $result];
            } else {
                return [
                    'status' => 'ERROR',
                    'msg' => 'No hay asignaciones registradas.',
                    'data' => []
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al obtener asignaciones: ' . $e->getMessage()
            ];
        }
    }
}
