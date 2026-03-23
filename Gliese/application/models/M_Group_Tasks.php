<?php
// --
class M_Group_Tasks extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // -- Obtener todas las tareas de todos los grupos
    public function get_all_tasks()
    {
        try {
            $sql = 'SELECT
                    gt.id,
                    gt.group_id,
                    sg.group_name,
                    sg.group_code,
                    gt.task_title,
                    gt.task_description,
                    gt.assigned_date,
                    gt.due_date,
                    gt.status,
                    gt.priority,
                    gt.created_by,
                    u.user as created_by_name,
                    gt.created_at,
                    gt.updated_at
                FROM group_tasks gt
                LEFT JOIN student_groups sg ON gt.group_id = sg.id
                LEFT JOIN user u ON gt.created_by = u.id
                ORDER BY gt.created_at DESC';

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                $response = ['status' => 'ERROR', 'data' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
        return $response;
    }

    // -- Obtener tareas de un grupo específico
    public function get_tasks_by_group($group_id)
    {
        try {
            $sql = 'SELECT
                    gt.id,
                    gt.group_id,
                    sg.group_name,
                    sg.group_code,
                    gt.task_title,
                    gt.task_description,
                    gt.assigned_date,
                    gt.due_date,
                    gt.status,
                    gt.priority,
                    gt.created_by,
                    u.user as created_by_name,
                    gt.created_at,
                    gt.updated_at
                FROM group_tasks gt
                LEFT JOIN student_groups sg ON gt.group_id = sg.id
                LEFT JOIN user u ON gt.created_by = u.id
                WHERE gt.group_id = :group_id
                ORDER BY
                    CASE gt.status
                        WHEN "pendiente" THEN 1
                        WHEN "en_progreso" THEN 2
                        WHEN "completada" THEN 3
                    END,
                    CASE gt.priority
                        WHEN "alta" THEN 1
                        WHEN "media" THEN 2
                        WHEN "baja" THEN 3
                    END,
                    gt.due_date ASC';

            $result = $this->pdo->fetchAll($sql, ['group_id' => $group_id]);

            if ($result) {
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                $response = ['status' => 'ERROR', 'data' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
        return $response;
    }

    // -- Obtener tareas del grupo de un practicante específico
    public function get_tasks_by_student($student_id)
    {
        try {
            // Primero obtener el grupo del practicante
            $sql_group = 'SELECT `group` FROM students WHERE user_id = :student_id';
            $student = $this->pdo->fetchOne($sql_group, ['student_id' => $student_id]);

            if (!$student || !$student['group']) {
                return ['status' => 'ERROR', 'msg' => 'Practicante sin grupo asignado', 'data' => []];
            }

            // Obtener el ID del grupo
            $sql_group_id = 'SELECT id FROM student_groups WHERE group_code = :group_code';
            $group = $this->pdo->fetchOne($sql_group_id, ['group_code' => $student['group']]);

            if (!$group) {
                return ['status' => 'ERROR', 'msg' => 'Grupo no encontrado', 'data' => []];
            }

            // Obtener las tareas del grupo
            return $this->get_tasks_by_group($group['id']);

        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
        return $response;
    }

    // -- Crear nueva tarea
    public function create_task($data)
    {
        try {
            $sql = 'INSERT INTO group_tasks (
                    group_id,
                    task_title,
                    task_description,
                    assigned_date,
                    due_date,
                    status,
                    priority,
                    created_by
                ) VALUES (
                    :group_id,
                    :task_title,
                    :task_description,
                    NOW(),
                    :due_date,
                    :status,
                    :priority,
                    :created_by
                )';

            $params = [
                'group_id' => isset($data['group_id']) ? $data['group_id'] : null,
                'task_title' => $data['task_title'],
                'task_description' => isset($data['task_description']) ? $data['task_description'] : null,
                'due_date' => isset($data['due_date']) ? $data['due_date'] : null,
                'status' => isset($data['status']) ? $data['status'] : 'pendiente',
                'priority' => isset($data['priority']) ? $data['priority'] : 'media',
                'created_by' => isset($data['created_by']) ? $data['created_by'] : null,
            ];

            $result = $this->pdo->perform($sql, $params);

            if ($result) {
                $task_id = $this->pdo->lastInsertId();
                $response = ['status' => 'OK', 'task_id' => $task_id];
            } else {
                $response = ['status' => 'ERROR', 'msg' => 'Error al crear la tarea'];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
        return $response;
    }

    // -- Actualizar tarea
    public function update_task($data)
    {
        try {
            $sql = 'UPDATE group_tasks SET
                    group_id = :group_id,
                    task_title = :task_title,
                    task_description = :task_description,
                    due_date = :due_date,
                    status = :status,
                    priority = :priority
                WHERE id = :id';

            $params = [
                'id' => $data['id'],
                'group_id' => $data['group_id'],
                'task_title' => $data['task_title'],
                'task_description' => isset($data['task_description']) ? $data['task_description'] : null,
                'due_date' => isset($data['due_date']) ? $data['due_date'] : null,
                'status' => isset($data['status']) ? $data['status'] : 'pendiente',
                'priority' => isset($data['priority']) ? $data['priority'] : 'media',
            ];

            $result = $this->pdo->perform($sql, $params);

            if ($result) {
                $response = ['status' => 'OK'];
            } else {
                $response = ['status' => 'ERROR', 'msg' => 'Error al actualizar la tarea'];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
        return $response;
    }

    // -- Actualizar solo el estado de una tarea
    public function update_task_status($task_id, $status)
    {
        try {
            $sql = 'UPDATE group_tasks SET status = :status WHERE id = :id';

            $params = [
                'id' => $task_id,
                'status' => $status,
            ];

            $result = $this->pdo->perform($sql, $params);

            if ($result) {
                $response = ['status' => 'OK'];
            } else {
                $response = ['status' => 'ERROR', 'msg' => 'Error al actualizar el estado'];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
        return $response;
    }

    // -- Eliminar tarea
    public function delete_task($task_id)
    {
        try {
            $sql = 'DELETE FROM group_tasks WHERE id = :id';

            $result = $this->pdo->perform($sql, ['id' => $task_id]);

            if ($result) {
                $response = ['status' => 'OK'];
            } else {
                $response = ['status' => 'ERROR', 'msg' => 'Error al eliminar la tarea'];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
        return $response;
    }

    // -- Obtener todos los grupos activos para el selector
    public function get_active_groups()
    {
        try {
            $sql = 'SELECT
                    id,
                    group_code,
                    group_name,
                    group_type,
                    current_members,
                    max_capacity
                FROM student_groups
                WHERE status = 1
                ORDER BY group_name ASC';

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                $response = ['status' => 'ERROR', 'data' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
        return $response;
    }

    // -- Obtener estadísticas de tareas
    public function get_tasks_stats()
    {
        try {
            $sql = 'SELECT
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = "pendiente" THEN 1 ELSE 0 END) as pending_tasks,
                    SUM(CASE WHEN status = "en_progreso" THEN 1 ELSE 0 END) as in_progress_tasks,
                    SUM(CASE WHEN status = "completada" THEN 1 ELSE 0 END) as completed_tasks,
                    SUM(CASE WHEN priority = "alta" THEN 1 ELSE 0 END) as high_priority_tasks,
                    SUM(CASE WHEN due_date < CURDATE() AND status != "completada" THEN 1 ELSE 0 END) as overdue_tasks
                FROM group_tasks';

            $result = $this->pdo->fetchOne($sql);

            if ($result) {
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                $response = ['status' => 'ERROR', 'data' => []];
            }
        } catch (PDOException $e) {
            $response = ['status' => 'EXCEPTION', 'msg' => $e->getMessage()];
        }
        return $response;
    }

    /**
     * Iniciar tareas pendientes cuando el practicante abre su jornada laboral.
     * Cambia el estado de tareas "pendiente" a "en_progreso" para el grupo del usuario.
     *
     * @param int $user_id ID del usuario que inicia la jornada
     * @return array Respuesta con el estado de la operación
     */
    public function start_pending_tasks_for_user($user_id)
    {
        try {
            // Obtener el grupo del practicante
            $sql_group = 'SELECT s.`group`, sg.id as group_id
                          FROM students s
                          INNER JOIN student_groups sg ON sg.group_code = s.`group`
                          WHERE s.user_id = :user_id';
            $student = $this->pdo->fetchOne($sql_group, ['user_id' => $user_id]);

            if (!$student || !$student['group_id']) {
                return ['status' => 'ERROR', 'msg' => 'Practicante sin grupo asignado', 'tasks_updated' => 0];
            }

            $group_id = $student['group_id'];

            // Actualizar todas las tareas pendientes del grupo a en_progreso
            $sql_update = 'UPDATE group_tasks
                           SET status = "en_progreso", updated_at = NOW()
                           WHERE group_id = :group_id
                           AND status = "pendiente"';

            $result = $this->pdo->perform($sql_update, ['group_id' => $group_id]);

            // Contar cuántas tareas se actualizaron
            $sql_count = 'SELECT COUNT(*) as updated
                          FROM group_tasks
                          WHERE group_id = :group_id
                          AND status = "en_progreso"';
            $count = $this->pdo->fetchOne($sql_count, ['group_id' => $group_id]);

            return [
                'status' => 'OK',
                'msg' => 'Tareas iniciadas correctamente',
                'tasks_updated' => $result ? $this->pdo->rowCount() : 0,
                'total_in_progress' => $count ? $count['updated'] : 0
            ];

        } catch (PDOException $e) {
            return ['status' => 'EXCEPTION', 'msg' => $e->getMessage(), 'tasks_updated' => 0];
        }
    }

    /**
     * Obtener el ID del grupo de un practicante
     *
     * @param int $user_id ID del usuario
     * @return int|null ID del grupo o null si no tiene
     */
    public function get_user_group_id($user_id)
    {
        try {
            $sql = 'SELECT sg.id as group_id
                    FROM students s
                    INNER JOIN student_groups sg ON sg.group_code = s.`group`
                    WHERE s.user_id = :user_id';
            $result = $this->pdo->fetchOne($sql, ['user_id' => $user_id]);

            return $result ? $result['group_id'] : null;
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>
