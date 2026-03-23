<?php
// --
class M_S_Groups extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --

    public function get_all_groups()
    {
        // Obtiene todos los grupos de estudiantes activos.
        // Retorna ID, código, nombre, tipo, color, descripción, capacidad máxima,
        // miembros actuales, espacios disponibles, fecha de creación, nombre del creador,
        // lista de supervisores y total de supervisores.
        try {
            // Aumentar el límite de GROUP_CONCAT para evitar truncamiento
            $this->pdo->query('SET SESSION group_concat_max_len = 10000');

            $sql = 'SELECT
                sg.id,
                sg.group_code AS code,
                sg.group_name AS name,
                sg.group_type,
                sg.color,
                sg.description,
                sg.max_capacity,
                sg.current_members,
                (sg.max_capacity - sg.current_members) AS available_spaces,
                DATE_FORMAT(sg.created_at, "%d/%m/%Y") AS created_date,
                COALESCE(CONCAT(uc.first_name, " ", uc.last_name), "No especificado") AS created_by_name,
                sg.status,
                COALESCE(ml.meet_url, sg.meet_link) AS meet_link,
                COALESCE(ml.description, sg.schedule) AS schedule,
                ml.name AS meet_link_name
            FROM student_groups sg
            LEFT JOIN user uc ON sg.created_by = uc.id
            LEFT JOIN group_meet_links gml ON gml.group_id = sg.id
            LEFT JOIN meet_links ml ON ml.id = gml.meet_link_id
            ORDER BY sg.created_at DESC';

            $result = $this->pdo->fetchAll($sql);

            // Para cada grupo, obtener los supervisores (nombre, tipo, etc.)
            if ($result) {
                foreach ($result as &$group) {
                    $group_id = $group['id'];
                    $supervisors = $this->get_group_supervisors($group_id);
                    if (isset($supervisors['status']) && $supervisors['status'] === 'OK') {
                        $group['supervisors'] = $supervisors['data'];
                        $group['total_supervisors'] = count($supervisors['data']);
                    } else {
                        $group['supervisors'] = [];
                        $group['total_supervisors'] = 0;
                    }
                }
                unset($group);
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' => 'No se encontraron grupos en el sistema.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al obtener los grupos: ' . $e->getMessage(),
            ];
        }
        return $response;
    }
    // --

    public function create_group($data)
    {
        // Crea un nuevo grupo de practicantes.
        // Valida que el código del grupo sea único, inserta el grupo en la base de datos
        // y asigna supervisores si se proporcionan. Retorna el estado de la operación y el ID del grupo creado.
        try {
            // Validar usuario autenticado en sesión
            if (!isset($_SESSION)) {
                session_start();
            }
            $user_id = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;
            if (!$user_id) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'No se puede crear el grupo: usuario no autenticado.',
                ];
            }

            // Verificar que el código de grupo no exista
            $sql_check =
                'SELECT COUNT(*) as total FROM student_groups WHERE group_code = :group_code';
            $check = $this->pdo->fetchOne($sql_check, ['group_code' => $data['group_code']]);

            if ($check['total'] > 0) {
                return ['status' => 'ERROR', 'msg' => 'El código de grupo ya existe'];
            }

            // Preparar supervisores para validación
            $supervisors = [];
            if (isset($data['supervisor_id']) && !empty($data['supervisor_id'])) {
                $supervisors = [
                    [
                        'id' => $data['supervisor_id'],
                        'type' => 'principal',
                    ],
                ];
            } elseif (isset($data['supervisors']) && is_array($data['supervisors'])) {
                $supervisors = $data['supervisors'];
            }
            if (!empty($supervisors)) {
                $validation = $this->validate_supervisors($supervisors);
                if ($validation['status'] !== 'OK') {
                    return $validation;
                }
            }

            // Buscar supervisor principal
            $supervisor_id = null;
            if (!empty($supervisors)) {
                foreach ($supervisors as $sup) {
                    if (isset($sup['type']) && strtoupper($sup['type']) === 'PRINCIPAL') {
                        $supervisor_id = $sup['id'];
                        break;
                    }
                }
                if (!$supervisor_id && isset($supervisors[0]['id'])) {
                    $supervisor_id = $supervisors[0]['id'];
                }
            }

            // Insertar grupo incluyendo supervisor_id
            $sql = 'INSERT INTO student_groups (
            group_code,
            group_name,
            group_type,
            color,
            description,
            supervisor_id,
            max_capacity,
            created_by,
            status
        ) VALUES (
            :group_code,
            :group_name,
            :group_type,
            :color,
            :description,
            :supervisor_id,
            :max_capacity,
            :created_by,
            1
        )';

            $params = [
                'group_code' => $data['group_code'],
                'group_name' => $data['group_name'],
                'group_type' => isset($data['group_type']) ? $data['group_type'] : 'PRACTICANTE',
                'color' => isset($data['color']) ? $data['color'] : '#28a745',
                'description' => isset($data['description']) ? $data['description'] : null,
                'supervisor_id' => $supervisor_id,
                'max_capacity' => $data['max_capacity'],
                'created_by' => $user_id,
            ];

            $result = $this->pdo->perform($sql, $params);

            if ($result) {
                $group_id = $this->pdo->lastInsertId();
                if (!empty($supervisors)) {
                    $this->assign_supervisors_to_group($group_id, $supervisors);
                }
                $response = [
                    'status' => 'OK',
                    'msg' => 'Grupo creado exitosamente',
                    'group_id' => $group_id,
                ];
            } else {
                $response = ['status' => 'ERROR', 'msg' => 'Error al crear el grupo'];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al crear el grupo: ' . $e->getMessage(),
            ];
        }
        return $response;
    }

    // --
    public function edit_group($data)
    {
        // Actualiza un grupo de practicantes existente.
        // Valida que el código del grupo sea único (excepto para el grupo actual),
        // verifica que la capacidad máxima no sea menor a los miembros actuales,
        // actualiza los datos del grupo y sus supervisores si se proporcionan.
        try {
            // Verificar que el código de grupo no exista en otro registro
            $sql_check = 'SELECT COUNT(*) as total FROM student_groups
                     WHERE group_code = :group_code AND id != :id';
            $check = $this->pdo->fetchOne($sql_check, [
                'group_code' => $data['group_code'],
                'id' => $data['id'],
            ]);

            if ($check['total'] > 0) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El código de grupo ya existe en otro registro',
                ];
            }

            // Verificar capacidad vs miembros actuales
            $sql_members = 'SELECT current_members FROM student_groups WHERE id = :id';
            $members = $this->pdo->fetchOne($sql_members, ['id' => $data['id']]);

            if ($members && $data['max_capacity'] < $members['current_members']) {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'La capacidad no puede ser menor a los miembros actuales (' .
                        $members['current_members'] .
                        ')',
                ];
            }

            // Preparar supervisores para validación
            $supervisors = [];
            // Prioridad: usar siempre el array 'supervisors' si existe
            if (isset($data['supervisors']) && is_array($data['supervisors'])) {
                $supervisors = $data['supervisors'];
            } elseif (isset($data['supervisor_id']) && !empty($data['supervisor_id'])) {
                $supervisors = [
                    [
                        'id' => $data['supervisor_id'],
                        'type' => 'principal',
                    ],
                ];
            }

            // Validar supervisores
            if (!empty($supervisors)) {
                $validation = $this->validate_supervisors($supervisors);
                if ($validation['status'] !== 'OK') {
                    return $validation;
                }
            }

            // Buscar supervisor principal
            $supervisor_id = null;
            if (!empty($supervisors)) {
                foreach ($supervisors as $sup) {
                    if (isset($sup['type']) && strtolower($sup['type']) === 'principal') {
                        $supervisor_id = $sup['id'];
                        break;
                    }
                }
                if (!$supervisor_id && isset($supervisors[0]['id'])) {
                    $supervisor_id = $supervisors[0]['id'];
                }
            }

            // Actualizar grupo incluyendo supervisor_id

            $sql = 'UPDATE student_groups SET
            group_code = :group_code,
            group_name = :group_name,
            group_type = :group_type,
            color = :color,
            description = :description,
            supervisor_id = :supervisor_id,
            max_capacity = :max_capacity,
            status = :status
            WHERE id = :id';

            $params = [
                'group_code' => $data['group_code'],
                'group_name' => $data['group_name'],
                'group_type' => isset($data['group_type']) ? $data['group_type'] : 'PRACTICANTE',
                'color' => isset($data['color']) ? $data['color'] : '#28a745',
                'description' => isset($data['description']) ? $data['description'] : null,
                'supervisor_id' => $supervisor_id,
                'max_capacity' => $data['max_capacity'],
                'status' => isset($data['status']) ? $data['status'] : 1,
                'id' => $data['id'],
            ];

            $result = $this->pdo->perform($sql, $params);

            if ($result !== false) {
                // Actualizar supervisores si se proporcionaron
                if (
                    !empty($supervisors) ||
                    isset($data['supervisor_id']) ||
                    isset($data['supervisors'])
                ) {
                    $this->update_group_supervisors($data['id'], $supervisors);
                }

                $response = ['status' => 'OK', 'msg' => 'Grupo actualizado exitosamente'];
            } else {
                $response = ['status' => 'ERROR', 'msg' => 'Error al actualizar el grupo'];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al actualizar el grupo: ' . $e->getMessage(),
            ];
        }
        return $response;
    }

    // --
    public function delete_group($id)
    {
        // Elimina un grupo de practicantes.
        // Verifica que el grupo no tenga miembros asignados antes de proceder con la eliminación.
        try {
            // Verificar si el grupo tiene miembros asignados
            $sql_check = 'SELECT current_members FROM student_groups WHERE id = :id';
            $check = $this->pdo->fetchOne($sql_check, ['id' => $id]);

            if ($check && $check['current_members'] > 0) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'No se puede eliminar un grupo que tiene miembros asignados',
                ];
            }

            // Eliminar el grupo
            $sql = 'DELETE FROM student_groups WHERE id = :id';
            $result = $this->pdo->perform($sql, ['id' => $id]);

            if ($result) {
                $response = ['status' => 'OK', 'msg' => 'Grupo eliminado exitosamente'];
            } else {
                $response = ['status' => 'ERROR', 'msg' => 'Error al eliminar el grupo'];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al eliminar el grupo: ' . $e->getMessage(),
            ];
        }
        return $response;
    }

    // --
    public function get_group_members($group_id)
    {
        // Obtiene los miembros de un grupo de practicantes.
        // Devuelve información detallada de los practicantes, incluyendo nombres, apellidos, documento, email,
        // teléfono, institución, especialidad, área de interés, estado, fecha de asignación, horas requeridas,
        // horas completadas, estado de horas, porcentaje de completitud, porcentaje de asistencia,
        // estado del usuario y cantidad de grupos supervisados.
        try {
            $sql = 'SELECT
                s.id,
                s.first_names AS names,
                s.last_names AS surnames,
                s.document_number,
                s.email,
                s.phone,
                s.institution,
                s.specialty,
                s.interest_area,
                s.status,
                DATE_FORMAT(s.group_assignment_date, "%d/%m/%Y") AS assignment_date,
                CONCAT(ua.first_name, " ", ua.last_name) AS assigned_by_name,
                s.required_hours,
                s.completed_hours,
                CASE
                    WHEN s.completed_hours >= s.required_hours THEN "Completado"
                    ELSE "En progreso"
                END AS hours_status,
                ROUND((s.completed_hours / s.required_hours) * 100, 1) AS completion_percentage,
                COALESCE(
                    (SELECT ROUND(
                        (SUM(
                            CASE
                                WHEN h.attendance_status = 1 THEN 1
                                WHEN h.attendance_status = 2 THEN 0.75
                                WHEN h.attendance_status = 3 THEN 0.5
                                ELSE 0
                            END
                        ) * 100.0) / COUNT(*), 0)
                     FROM student_attendance_history h
                     WHERE h.student_id = s.id),
                    0
                ) AS attendance_percentage,
                COALESCE(u.active, 0) AS user_active,
                u.status AS account_enabled,
                COALESCE(
                    (SELECT COUNT(DISTINCT gs.group_id)
                     FROM group_supervisors gs
                     WHERE gs.supervisor_id = u.id AND gs.status = 1),
                    0
                ) AS groups_supervised,
                sg.group_type
            FROM students s
            INNER JOIN student_groups sg ON s.group = sg.group_code
            LEFT JOIN user u ON s.user_id = u.id
            LEFT JOIN user ua ON s.assigned_by = ua.id
            WHERE sg.id = :group_id
            AND s.has_credentials = 1
            ORDER BY s.group_assignment_date DESC';
            $result = $this->pdo->fetchAll($sql, ['group_id' => $group_id]);
            if ($result) {
                $response = ['status' => 'OK', 'result' => $result];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'msg' => 'No se encontraron miembros en el grupo',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al obtener los miembros del grupo: ' . $e->getMessage(),
            ];
        }
        return $response;
    }

    // -- Obtiene todos los supervisores activos en el sistema.
    public function get_supervisors()
    {
        // Obtiene todos los usuarios con rol de SUPERVISOR.
        // Prioridad 1: Usuarios en grupos de tipo SUPERVISOR con estado activo.
        // Prioridad 2: Otros supervisores activos con registro en students.
        // Prioridad 3: Supervisores activos sin registro en students.
        try {
            $sql = <<<'SQL'
SELECT
    u.id,
    CONCAT(u.first_name, " ", u.last_name) AS full_name,
    u.email,
    COALESCE(r.description, ur.description) AS role,
    COALESCE(s.`group`, "N/A") AS group_code,
    COALESCE(sg.group_type, "N/A") AS group_type,
    CASE
        WHEN UPPER(TRIM(sg.group_type)) = "SUPERVISOR" AND s.status = 1 THEN 1
        WHEN s.id IS NOT NULL THEN 2
        ELSE 3
    END AS priority
FROM user u
    LEFT JOIN role r ON u.id_role = r.id
LEFT JOIN students s ON u.id = s.user_id
LEFT JOIN student_groups sg ON s.`group` = sg.group_code
WHERE u.status = 1
        AND (
    (r.id = 5 OR UPPER(TRIM(COALESCE(r.description, ''))) = 'SUPERVISOR')
)
ORDER BY priority ASC, u.first_name ASC
SQL;

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' => 'No se encontraron supervisores activos en el sistema.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al obtener supervisores: ' . $e->getMessage(),
            ];
        }
        return $response;
    }

    // --
    // Obtiene supervisores pero basándose exclusivamente en la tabla `user_roles`.
    // Esta función se usa únicamente desde la interfaz de "Practicantes" para
    // listar roles personalizados (user_roles) sin afectar el login ni permisos
    // administrativos.
    public function get_practitioner_supervisors()
    {
        try {
            $sql = 'SELECT
                u.id,
                CONCAT(u.first_name, " ", u.last_name) AS full_name,
                u.email,
                ur.description AS role,
                COALESCE(s.`group`, "N/A") AS group_code,
                COALESCE(sg.group_type, "N/A") AS group_type,
                CASE
                    WHEN UPPER(TRIM(sg.group_type)) = "SUPERVISOR" AND s.status = 1 THEN 1
                    WHEN s.id IS NOT NULL THEN 2
                    ELSE 3
                END AS priority
            FROM user u
            LEFT JOIN user_roles ur ON u.id_user_role = ur.id
            LEFT JOIN students s ON u.id = s.user_id
            LEFT JOIN student_groups sg ON s.`group` = sg.group_code
            WHERE u.status = 1
            AND u.id_user_role IS NOT NULL
            AND UPPER(TRIM(COALESCE(ur.description, ""))) = "SUPERVISOR"
            ORDER BY priority ASC, u.first_name ASC';

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                $response = ['status' => 'ERROR', 'data' => [], 'msg' => 'No se encontraron supervisores en user_roles.'];
            }
        } catch (PDOException $e) {
            // Si la columna o tabla no existen, devolver un error manejable
            $response = ['status' => 'EXCEPTION', 'msg' => 'Error al obtener supervisores (practicantes): ' . $e->getMessage(), 'data' => []];
        }
        return $response;
    }

    // --
    public function get_group_supervisors($group_id)
    {
        // Obtiene los supervisores asignados a un grupo específico.
        // Devuelve ID, ID del supervisor, nombre completo, email, tipo de supervisor y fecha de asignación.
        try {
            // Validar si el grupo existe
            $sql_check = 'SELECT id FROM student_groups WHERE id = :group_id';
            $group_exists = $this->pdo->fetchOne($sql_check, ['group_id' => $group_id]);

            if (!$group_exists) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El grupo especificado no existe.',
                    'data' => [],
                ];
            }

            $sql = 'SELECT
            gs.id,
            gs.supervisor_id,
            CONCAT(u.first_name, " ", u.last_name) AS full_name,
            u.email,
            gs.supervisor_type,
            DATE_FORMAT(gs.assignment_date, "%d/%m/%Y %H:%i:%s") AS assignment_date
        FROM group_supervisors gs
        INNER JOIN user u ON gs.supervisor_id = u.id
        WHERE gs.group_id = :group_id AND gs.status = 1
        ORDER BY
            CASE gs.supervisor_type
                WHEN "principal" THEN 1
                WHEN "technical" THEN 2
                WHEN "assistant" THEN 3
            END';

            $result = $this->pdo->fetchAll($sql, ['group_id' => $group_id]);

            if ($result) {
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' => 'No se encontraron supervisores activos para este grupo.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al obtener supervisores del grupo: ' . $e->getMessage(),
                'data' => [],
            ];
        }
        return $response;
    }

    // --
    // --
    public function change_group_status($id, $status)
    {
        // Actualiza el estado de un grupo de practicantes.
        // Modifica el campo status en la tabla student_groups para el grupo especificado.
        // Valida que el grupo exista y que el estado sea válido (0 o 1).
        try {
            // Validar estado
            if (!in_array($status, [0, 1])) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El estado debe ser 0 (inactivo) o 1 (activo).',
                ];
            }

            // Validar si el grupo existe
            $sql_check = 'SELECT id FROM student_groups WHERE id = :id';
            $group_exists = $this->pdo->fetchOne($sql_check, ['id' => $id]);

            if (!$group_exists) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El grupo especificado no existe.',
                ];
            }

            $sql = 'UPDATE student_groups SET status = :status WHERE id = :id';
            $result = $this->pdo->perform($sql, ['status' => $status, 'id' => $id]);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'msg' => 'Estado del grupo actualizado exitosamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'msg' => 'No se pudo actualizar el estado del grupo.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al actualizar el estado del grupo: ' . $e->getMessage(),
            ];
        }
        return $response;
    }

    // ============================================
    // MÉTODOS PARA GESTIÓN DE MÚLTIPLES SUPERVISORES
    // ============================================

    // --
    // --
    private function validate_supervisors($supervisors)
    {
        // Valida la lista de supervisores para un grupo.
        // Verifica que el número de supervisores no exceda el máximo permitido,
        // que haya solo un supervisor principal y que los tipos de supervisor sean válidos.
        try {
            // Validar que $supervisors sea un array
            if (!is_array($supervisors)) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'La lista de supervisores debe ser un array válido.',
                ];
            }

            // Obtener el máximo de supervisores permitido
            $sql_config =
                'SELECT value FROM student_config WHERE `key` = "max_supervisors_per_group"';
            $config = $this->pdo->fetchOne($sql_config);
            $max_supervisors = isset($config['value']) ? (int) $config['value'] : 3;

            // Validar cantidad máxima
            if (count($supervisors) > $max_supervisors) {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'El grupo no puede tener más de ' .
                        $max_supervisors .
                        ' supervisores. Enviados: ' .
                        count($supervisors) .
                        '.',
                ];
            }

            // Validar tipos de supervisor y contar principales
            $valid_types = ['principal', 'technical', 'assistant'];
            $principals = 0;

            foreach ($supervisors as $index => $sup) {
                // Verificar estructura del supervisor
                if (!isset($sup['type']) || !isset($sup['id'])) {
                    return [
                        'status' => 'ERROR',
                        'msg' =>
                            'El supervisor en la posición ' .
                            ($index + 1) .
                            ' no tiene tipo o ID válido.',
                    ];
                }

                // Validar tipo de supervisor
                if (!in_array($sup['type'], $valid_types)) {
                    return [
                        'status' => 'ERROR',
                        'msg' =>
                            'El tipo de supervisor "' .
                            $sup['type'] .
                            '" en la posición ' .
                            ($index + 1) .
                            ' no es válido. Tipos permitidos: ' .
                            implode(', ', $valid_types) .
                            '.',
                    ];
                }

                // Contar supervisores principales
                if ($sup['type'] === 'principal') {
                    $principals++;
                }
            }

            // Validar que haya máximo un supervisor principal
            if ($principals > 1) {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'Solo puede haber un supervisor principal por grupo. Encontrados: ' .
                        $principals .
                        '.',
                ];
            }

            return ['status' => 'OK'];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al validar supervisores: ' . $e->getMessage(),
            ];
        }
    }

    // --
    // --
    private function assign_supervisors_to_group($group_id, $supervisors)
    {
        // Asigna supervisores a un grupo específico.
        // Inserta cada supervisor en la tabla group_supervisors con su tipo y el ID del usuario que realiza la asignación.
        // Valida que el grupo exista y que los supervisores tengan una estructura válida.
        try {
            // Validar que $supervisors sea un array
            if (!is_array($supervisors) || empty($supervisors)) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'La lista de supervisores debe ser un array no vacío.',
                ];
            }

            // Validar si el grupo existe
            $sql_check = 'SELECT id FROM student_groups WHERE id = :group_id';
            $group_exists = $this->pdo->fetchOne($sql_check, ['group_id' => $group_id]);

            if (!$group_exists) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El grupo especificado no existe.',
                ];
            }

            $valid_types = ['principal', 'technical', 'assistant'];
            $user_id = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;
            $inserted = 0;

            foreach ($supervisors as $index => $sup) {
                // Validar estructura del supervisor
                if (!isset($sup['id']) || !isset($sup['type'])) {
                    return [
                        'status' => 'ERROR',
                        'msg' =>
                            'El supervisor en la posición ' .
                            ($index + 1) .
                            ' no tiene ID o tipo válido.',
                    ];
                }

                // Validar tipo de supervisor
                if (!in_array($sup['type'], $valid_types)) {
                    return [
                        'status' => 'ERROR',
                        'msg' =>
                            'El tipo de supervisor "' .
                            $sup['type'] .
                            '" en la posición ' .
                            ($index + 1) .
                            ' no es válido. Tipos permitidos: ' .
                            implode(', ', $valid_types) .
                            '.',
                    ];
                }

                $sql = 'INSERT INTO group_supervisors (group_id, supervisor_id, supervisor_type, assigned_by, status)
                    VALUES (:group_id, :supervisor_id, :supervisor_type, :assigned_by, 1)';

                $params = [
                    'group_id' => $group_id,
                    'supervisor_id' => $sup['id'],
                    'supervisor_type' => $sup['type'],
                    'assigned_by' => $user_id,
                ];

                $result = $this->pdo->perform($sql, $params);
                if ($result) {
                    $inserted++;
                }
            }

            return [
                'status' => 'OK',
                'msg' => 'Se asignaron ' . $inserted . ' supervisores exitosamente.',
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al asignar supervisores: ' . $e->getMessage(),
            ];
        }
    }

    // --
    private function update_group_supervisors($group_id, $supervisors)
    {
        // Actualiza los supervisores de un grupo.
        // Desactiva todos los supervisores actuales y asigna los nuevos proporcionados.
        // Valida que el grupo exista y retorna el estado de la operación.
        try {
            // Validar si el grupo existe
            $sql_check = 'SELECT id FROM student_groups WHERE id = :group_id';
            $group_exists = $this->pdo->fetchOne($sql_check, ['group_id' => $group_id]);

            if (!$group_exists) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El grupo especificado no existe.',
                ];
            }

            // Si no se enviaron supervisores (propiedad ausente o null), no hacemos cambios.
            // Esto evita desactivar todos los supervisores cuando el frontend no incluye la propiedad.
            if (!isset($supervisors) || !is_array($supervisors)) {
                return [
                    'status' => 'OK',
                    'msg' => 'No se proporcionaron supervisores para actualizar; sin cambios.',
                ];
            }

            // Si el array está vacío explícitamente, interpretamos que se quiere remover todos
            // los supervisores: desactivar todos.
            if (empty($supervisors)) {
                // Eliminar todas las asignaciones de supervisores para este grupo
                $sql_delete_all = 'DELETE FROM group_supervisors WHERE group_id = :group_id';
                $this->pdo->perform($sql_delete_all, ['group_id' => $group_id]);
                // También limpiar supervisor_id en student_groups
                $sql_clear = 'UPDATE student_groups SET supervisor_id = NULL WHERE id = :group_id';
                $this->pdo->perform($sql_clear, ['group_id' => $group_id]);

                return [
                    'status' => 'OK',
                    'msg' => 'Todas las asignaciones de supervisores eliminadas para el grupo.',
                ];
            }

            // Construir lista de supervisor_id proporcionados
            $provided_ids = array_map(function ($s) {
                return isset($s['id']) ? $s['id'] : null;
            }, $supervisors);
            $provided_ids = array_filter($provided_ids, function ($v) {
                return $v !== null && $v !== '';
            });

            // Desactivar solo supervisores que NO están en la nueva lista
            if (!empty($provided_ids)) {
                // Preparar placeholders para IN
                $placeholders = implode(', ', array_fill(0, count($provided_ids), '?'));
                // Eliminar asignaciones que no están en la nueva lista
                $sql_delete = "DELETE FROM group_supervisors WHERE group_id = ? AND supervisor_id NOT IN ($placeholders)";
                $params = array_merge([$group_id], array_values($provided_ids));
                $this->pdo->perform($sql_delete, $params);
            } else {
                // Si no hay ids válidos, desactivar todos
                $sql_deactivate_all =
                    'UPDATE group_supervisors SET status = 0 WHERE group_id = :group_id';
                $this->pdo->perform($sql_deactivate_all, ['group_id' => $group_id]);
            }

            // Insertar o reactivar cada supervisor enviado (upsert-like)
            $user_id = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;
            $inserted = 0;
            foreach ($supervisors as $sup) {
                if (!isset($sup['id'])) {
                    continue;
                }

                $sup_id = $sup['id'];
                $sup_type = isset($sup['type']) ? $sup['type'] : 'principal';

                // Verificar si ya existe la asignación
                $sql_exists =
                    'SELECT id FROM group_supervisors WHERE group_id = :group_id AND supervisor_id = :supervisor_id LIMIT 1';
                $exists = $this->pdo->fetchOne($sql_exists, [
                    'group_id' => $group_id,
                    'supervisor_id' => $sup_id,
                ]);

                if ($exists) {
                    // Actualizar tipo y assigned_by (si se desea conservar un registro existente)
                    // Nota: con la política de eliminar historial, normalmente no habrá registros previos.
                    $sql_update =
                        'UPDATE group_supervisors SET supervisor_type = :supervisor_type, assigned_by = :assigned_by, assignment_date = NOW() WHERE id = :id';
                    $this->pdo->perform($sql_update, [
                        'supervisor_type' => $sup_type,
                        'assigned_by' => $user_id,
                        'id' => $exists['id'],
                    ]);
                } else {
                    // Insertar nueva asignación
                    $sql_insert = 'INSERT INTO group_supervisors (group_id, supervisor_id, supervisor_type, assigned_by, status, assignment_date)
                        VALUES (:group_id, :supervisor_id, :supervisor_type, :assigned_by, 1, NOW())';
                    $this->pdo->perform($sql_insert, [
                        'group_id' => $group_id,
                        'supervisor_id' => $sup_id,
                        'supervisor_type' => $sup_type,
                        'assigned_by' => $user_id,
                    ]);
                    $inserted++;
                }
            }

            // Actualizar supervisor_id en student_groups al principal proporcionado (si existe)
            $principal_id = null;
            foreach ($supervisors as $s) {
                if (isset($s['type']) && strtolower($s['type']) === 'principal') {
                    $principal_id = $s['id'];
                    break;
                }
            }
            if (!$principal_id && !empty($provided_ids)) {
                $principal_id = reset($provided_ids);
            }

            if ($principal_id) {
                $sql_update_supervisor =
                    'UPDATE student_groups SET supervisor_id = :supervisor_id WHERE id = :group_id';
                $this->pdo->perform($sql_update_supervisor, [
                    'supervisor_id' => $principal_id,
                    'group_id' => $group_id,
                ]);
            }

            return [
                'status' => 'OK',
                'msg' => 'Supervisores actualizados correctamente',
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al actualizar supervisores del grupo: ' . $e->getMessage(),
            ];
        }
    }

    // -- Remover supervisor
    // --
    public function remove_supervisor($group_id, $supervisor_id)
    {
        // Remueve un supervisor de un grupo.
        // Cambia el estado del supervisor a inactivo en la tabla group_supervisors.
        // Valida la existencia del grupo y la asignación activa del supervisor.
        try {
            // Validar si el grupo existe
            $sql_check_group = 'SELECT id FROM student_groups WHERE id = :group_id';
            $group_exists = $this->pdo->fetchOne($sql_check_group, ['group_id' => $group_id]);

            if (!$group_exists) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El grupo especificado no existe.',
                ];
            }

            // Validar si el supervisor está asignado y activo
            $sql_check_supervisor = 'SELECT gs.id, CONCAT(u.first_name, " ", u.last_name) AS full_name
                                FROM group_supervisors gs
                                INNER JOIN user u ON gs.supervisor_id = u.id
                                WHERE gs.group_id = :group_id 
                                AND gs.supervisor_id = :supervisor_id 
                                AND gs.status = 1';
            $supervisor = $this->pdo->fetchOne($sql_check_supervisor, [
                'group_id' => $group_id,
                'supervisor_id' => $supervisor_id,
            ]);

            if (!$supervisor) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El supervisor no está asignado a este grupo o ya está inactivo.',
                ];
            }

            // Eliminar todas las asignaciones del supervisor (borrado definitivo)
            // Esto asegura que, al remover al usuario como supervisor de un grupo,
            // no quede asignación en otros grupos.
            $sql = 'DELETE FROM group_supervisors WHERE supervisor_id = :supervisor_id';
            $result = $this->pdo->perform($sql, [
                'supervisor_id' => $supervisor_id,
            ]);

            // Limpiar cualquier supervisor_id apuntando a este usuario en student_groups
            try {
                $sql_clear_sg =
                    'UPDATE student_groups SET supervisor_id = NULL WHERE supervisor_id = :supervisor_id';
                $this->pdo->perform($sql_clear_sg, ['supervisor_id' => $supervisor_id]);
            } catch (Exception $e) {
                error_log(
                    'Error al limpiar supervisor_id en student_groups para supervisor ' .
                        $supervisor_id .
                        ': ' .
                        $e->getMessage(),
                );
            }

            if ($result) {
                return [
                    'status' => 'OK',
                    'msg' =>
                        'Todas las asignaciones de supervisión para ' .
                        $supervisor['full_name'] .
                        ' han sido eliminadas.',
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'No se pudieron eliminar las asignaciones de supervisión del supervisor.',
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al remover el supervisor: ' . $e->getMessage(),
            ];
        }
    }

    // --
    // --
    public function add_supervisor($group_id, $supervisor_id, $supervisor_type)
    {
        // Agrega un supervisor a un grupo de practicantes.
        // Valida el límite de supervisores, verifica que no esté duplicado, asegura que solo haya un supervisor principal,
        // y confirma la existencia del grupo y el usuario supervisor.
        try {
            // Validar si el grupo existe
            $sql_check_group = 'SELECT id FROM student_groups WHERE id = :group_id';
            $group_exists = $this->pdo->fetchOne($sql_check_group, ['group_id' => $group_id]);

            if (!$group_exists) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El grupo especificado no existe.',
                ];
            }

            // Validar si el supervisor existe y tiene rol de SUPERVISOR
            $sql_check_supervisor = 'SELECT id, CONCAT(first_name, " ", last_name) AS full_name
                                FROM user
                                WHERE id = :supervisor_id AND status = 1
                                AND (id_role = 5 OR UPPER(TRIM(id_role IN (SELECT id FROM role WHERE description = "SUPERVISOR"))))';
            $supervisor = $this->pdo->fetchOne($sql_check_supervisor, [
                'supervisor_id' => $supervisor_id,
            ]);

            if (!$supervisor) {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'El supervisor especificado no existe o no tiene el rol de SUPERVISOR.',
                ];
            }

            // Validar tipo de supervisor
            $valid_types = ['principal', 'technical', 'assistant'];
            if (!in_array($supervisor_type, $valid_types)) {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'El tipo de supervisor "' .
                        $supervisor_type .
                        '" no es válido. Tipos permitidos: ' .
                        implode(', ', $valid_types) .
                        '.',
                ];
            }

            // Verificar límite de supervisores
            $sql_count =
                'SELECT COUNT(*) AS total FROM group_supervisors WHERE group_id = :group_id AND status = 1';
            $count = $this->pdo->fetchOne($sql_count, ['group_id' => $group_id]);

            $sql_config =
                'SELECT value FROM student_config WHERE `key` = "max_supervisors_per_group"';
            $config = $this->pdo->fetchOne($sql_config);
            $max_supervisors = isset($config['value']) ? (int) $config['value'] : 3;

            if ($count['total'] >= $max_supervisors) {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'El grupo ya tiene el máximo de supervisores permitidos (' .
                        $max_supervisors .
                        ').',
                ];
            }

            // Verificar que no esté duplicado
            $sql_check =
                'SELECT id FROM group_supervisors WHERE group_id = :group_id AND supervisor_id = :supervisor_id AND status = 1';
            $exists = $this->pdo->fetchOne($sql_check, [
                'group_id' => $group_id,
                'supervisor_id' => $supervisor_id,
            ]);

            if ($exists) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El supervisor ya está asignado a este grupo.',
                ];
            }

            // Validar supervisor principal único
            if ($supervisor_type === 'principal') {
                $sql_principal =
                    'SELECT id FROM group_supervisors WHERE group_id = :group_id AND supervisor_type = "principal" AND status = 1';
                $principal_exists = $this->pdo->fetchOne($sql_principal, ['group_id' => $group_id]);

                if ($principal_exists) {
                    return [
                        'status' => 'ERROR',
                        'msg' => 'El grupo ya tiene un supervisor principal.',
                    ];
                }
            }

            // Insertar supervisor
            $sql = 'INSERT INTO group_supervisors (group_id, supervisor_id, supervisor_type, assigned_by, status)
                VALUES (:group_id, :supervisor_id, :supervisor_type, :assigned_by, 1)';

            $user_id = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;

            $result = $this->pdo->perform($sql, [
                'group_id' => $group_id,
                'supervisor_id' => $supervisor_id,
                'supervisor_type' => $supervisor_type,
                'assigned_by' => $user_id,
            ]);

            if ($result) {
                return [
                    'status' => 'OK',
                    'msg' => 'Supervisor ' . $supervisor['full_name'] . ' agregado exitosamente.',
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'msg' => 'No se pudo agregar el supervisor.',
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al agregar el supervisor: ' . $e->getMessage(),
            ];
        }
    }

    // --
    // --
    public function remove_group_member($student_id)
    {
        // Remueve un practicante de un grupo.
        // Obtiene la información del practicante, elimina su asignación al grupo (establece grupo como NULL),
        // actualiza el contador de miembros del grupo, ajusta el rol si es necesario y retorna el estado de la operación.
        try {
            // Obtener información del practicante y el tipo de grupo
            $sql_info = 'SELECT s.id, s.first_names, s.last_names, s.group, s.user_id, 
                            sg.group_name, sg.group_type 
                     FROM students s 
                     LEFT JOIN student_groups sg ON s.group = sg.group_code 
                     WHERE s.id = :student_id';
            $info = $this->pdo->fetchOne($sql_info, ['student_id' => $student_id]);

            if (!$info) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'Practicante no encontrado.',
                ];
            }

            // Verificar si el practicante ya no está en un grupo
            if (!$info['group']) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El practicante no está asignado a ningún grupo.',
                ];
            }

            // Guardar información del grupo antes de eliminar
            $previous_group = $info['group'];
            $previous_group_name = $info['group_name'] ?? 'sin nombre';
            $previous_group_type = $info['group_type'];

            // Remover del grupo (poner grupo como NULL)
            $sql_update = 'UPDATE students SET 
                       `group` = NULL, 
                       group_assignment_date = NULL 
                       WHERE id = :student_id';
            $result = $this->pdo->perform($sql_update, ['student_id' => $student_id]);

            if ($result) {
                // Actualizar contador de miembros del grupo
                if ($previous_group) {
                    $this->update_member_count_by_code($previous_group);
                }

                // Actualizar rol si el practicante estaba en un grupo de tipo SUPERVISOR
                if ($previous_group_type === 'SUPERVISOR' && $info['user_id']) {
                    $sql_role_student =
                        'SELECT id FROM user_roles WHERE UPPER(TRIM(description)) = "PRACTICANTE" LIMIT 1';
                    $role_student = $this->pdo->fetchOne($sql_role_student);
                    $id_user_role_student = $role_student ? $role_student['id'] : null;

                    if ($id_user_role_student) {
                        $sql_update_role = 'UPDATE user SET id_user_role = :id_user_role WHERE id = :user_id';
                        $this->pdo->perform($sql_update_role, [
                            'id_user_role' => $id_user_role_student,
                            'user_id' => $info['user_id'],
                        ]);
                        // Política: eliminar cualquier asignación de supervisión que el usuario tenga
                        // al ser removido del grupo y demotado a PRACTICANTE.
                        try {
                            $sql_delete_supervisions =
                                'DELETE FROM group_supervisors WHERE supervisor_id = :supervisor_id';
                            $this->pdo->perform($sql_delete_supervisions, [
                                'supervisor_id' => $info['user_id'],
                            ]);
                            // Limpiar supervisor_id en cualquier grupo que lo tenga
                            $sql_clear_sg =
                                'UPDATE student_groups SET supervisor_id = NULL WHERE supervisor_id = :supervisor_id';
                            $this->pdo->perform($sql_clear_sg, [
                                'supervisor_id' => $info['user_id'],
                            ]);
                        } catch (Exception $e) {
                            error_log(
                                'Error al eliminar asignaciones de supervisor durante remove_group_member para user ' .
                                    $info['user_id'] .
                                    ': ' .
                                    $e->getMessage(),
                            );
                        }
                    }
                }

                $response = [
                    'status' => 'OK',
                    'msg' =>
                        $info['first_names'] .
                        ' ' .
                        $info['last_names'] .
                        ' ha sido removido del grupo "' .
                        $previous_group_name .
                        '" exitosamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'msg' => 'No se pudo remover el practicante del grupo.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al remover el practicante del grupo: ' . $e->getMessage(),
            ];
        }
        return $response;
    }

    // --
    // --
    public function reassign_group_member($student_id, $new_group_id)
    {
        // Reasigna un practicante a un nuevo grupo.
        // Verifica que el nuevo grupo exista, esté activo y tenga espacio disponible.
        // Actualiza el grupo del practicante, ajusta el rol del usuario según el tipo de grupo
        // y actualiza los contadores de miembros de ambos grupos.
        try {
            // Validar el nuevo grupo
            $sql_group = 'SELECT group_code, group_name, group_type, max_capacity, current_members 
                      FROM student_groups 
                      WHERE id = :group_id AND status = 1';
            $group = $this->pdo->fetchOne($sql_group, ['group_id' => $new_group_id]);

            if (!$group) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El grupo seleccionado no existe o está inactivo.',
                ];
            }

            if ($group['current_members'] >= $group['max_capacity']) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El grupo seleccionado no tiene espacios disponibles.',
                ];
            }

            // Obtener información del practicante
            $sql_student = 'SELECT s.id, s.first_names, s.last_names, s.group AS previous_group, 
                               s.user_id, sg.group_type AS previous_group_type
                        FROM students s
                        LEFT JOIN student_groups sg ON s.group = sg.group_code
                        WHERE s.id = :student_id';
            $student = $this->pdo->fetchOne($sql_student, ['student_id' => $student_id]);

            if (!$student) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'Practicante no encontrado.',
                ];
            }

            // Verificar si el practicante ya está en el grupo destino
            if ($student['previous_group'] === $group['group_code']) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El practicante ya pertenece a este grupo.',
                ];
            }

            // Obtener IDs de user_roles
            $sql_role_supervisor =
                'SELECT id FROM user_roles WHERE UPPER(TRIM(description)) = "SUPERVISOR" LIMIT 1';
            $role_supervisor = $this->pdo->fetchOne($sql_role_supervisor);
            if (!$role_supervisor) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El rol SUPERVISOR no está configurado en el sistema.',
                ];
            }
            $id_user_role_supervisor = $role_supervisor['id'];

            $sql_role_student =
                'SELECT id FROM user_roles WHERE UPPER(TRIM(description)) = "PRACTICANTE" LIMIT 1';
            $role_student = $this->pdo->fetchOne($sql_role_student);
            if (!$role_student) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El rol PRACTICANTE no está configurado en el sistema.',
                ];
            }
            $id_user_role_student = $role_student['id'];

            // Reasignar al nuevo grupo
            $sql_reassign = 'UPDATE students SET 
                         `group` = :group_code,
                         group_assignment_date = NOW(),
                         assigned_by = :assigned_by
                         WHERE id = :student_id';

            $user_id = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;
            $result = $this->pdo->perform($sql_reassign, [
                'group_code' => $group['group_code'],
                'assigned_by' => $user_id,
                'student_id' => $student_id,
            ]);

            if ($result) {
                // Actualizar rol del usuario si tiene credenciales
                if ($student['user_id']) {
                    // Si entra a un grupo de tipo SUPERVISOR → cambiar a rol Supervisor
                    if ($group['group_type'] === 'SUPERVISOR') {
                        $sql_update_role = 'UPDATE user SET id_user_role = :id_user_role WHERE id = :user_id';
                        $this->pdo->perform($sql_update_role, [
                            'id_user_role' => $id_user_role_supervisor,
                            'user_id' => $student['user_id'],
                        ]);
                    }
                    // Si sale de un grupo de tipo SUPERVISOR a uno de tipo PRACTICANTE → cambiar a rol Practicante
                    elseif (
                        $student['previous_group_type'] === 'SUPERVISOR' &&
                        $group['group_type'] === 'PRACTICANTE'
                    ) {
                        $sql_update_role = 'UPDATE user SET id_user_role = :id_user_role WHERE id = :user_id';
                        $this->pdo->perform($sql_update_role, [
                            'id_user_role' => $id_user_role_student,
                            'user_id' => $student['user_id'],
                        ]);
                        // Política: eliminar asignaciones de supervisión del usuario al ser demotado
                        try {
                            $sql_delete_supervisions =
                                'DELETE FROM group_supervisors WHERE supervisor_id = :supervisor_id';
                            $this->pdo->perform($sql_delete_supervisions, [
                                'supervisor_id' => $student['user_id'],
                            ]);
                            // Limpiar supervisor_id en student_groups
                            $sql_clear_sg =
                                'UPDATE student_groups SET supervisor_id = NULL WHERE supervisor_id = :supervisor_id';
                            $this->pdo->perform($sql_clear_sg, [
                                'supervisor_id' => $student['user_id'],
                            ]);
                        } catch (Exception $e) {
                            // Registrar el error pero no evitar la reasignación
                            error_log(
                                'Error al eliminar asignaciones de supervisor para el usuario ' .
                                    $student['user_id'] .
                                    ': ' .
                                    $e->getMessage(),
                            );
                        }
                    }
                }

                // Actualizar contadores de ambos grupos
                if ($student['previous_group']) {
                    $this->update_member_count_by_code($student['previous_group']);
                }
                $this->update_member_count_by_code($group['group_code']);

                $response = [
                    'status' => 'OK',
                    'msg' =>
                        $student['first_names'] .
                        ' ' .
                        $student['last_names'] .
                        ' ha sido reasignado a "' .
                        $group['group_name'] .
                        '" exitosamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'msg' => 'No se pudo reasignar el practicante.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al reasignar el practicante: ' . $e->getMessage(),
            ];
        }
        return $response;
    }

    // --
    // --
    public function get_available_groups($exclude_group_id = null)
    {
        // Obtiene todos los grupos de practicantes activos con espacios disponibles.
        // Excluye el grupo especificado por ID si se proporciona, y retorna información como ID, código, nombre,
        // tipo de grupo, capacidad máxima, miembros actuales y espacios disponibles.
        try {
            // Validar exclude_group_id si se proporciona
            if ($exclude_group_id) {
                $sql_check = 'SELECT id FROM student_groups WHERE id = :exclude_group_id';
                $group_exists = $this->pdo->fetchOne($sql_check, [
                    'exclude_group_id' => $exclude_group_id,
                ]);

                if (!$group_exists) {
                    return [
                        'status' => 'ERROR',
                        'data' => [],
                        'msg' => 'El grupo a excluir no existe.',
                    ];
                }
            }

            $sql = 'SELECT 
            id,
            group_code, 
            group_name,
            group_type,
            max_capacity,
            current_members,
            (max_capacity - current_members) AS available_spaces
        FROM student_groups 
        WHERE status = 1 
        AND (max_capacity - current_members) > 0';

            $params = [];

            if ($exclude_group_id) {
                $sql .= ' AND id != :exclude_group_id';
                $params['exclude_group_id'] = $exclude_group_id;
            }

            $sql .= ' ORDER BY group_name ASC';

            $result = $this->pdo->fetchAll($sql, $params);

            if ($result) {
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' => 'No se encontraron grupos activos con espacios disponibles.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' => 'Error al obtener grupos disponibles: ' . $e->getMessage(),
                'data' => [],
            ];
        }
        return $response;
    }

    // --
    // --
    private function update_member_count_by_code($group_code)
    {
        // Actualiza el contador de miembros de un grupo basado en su código.
        // Cuenta los practicantes asignados al grupo y actualiza el campo current_members en la tabla student_groups.
        // Valida la existencia del grupo y retorna el estado de la operación.
        try {
            if (empty($group_code)) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El código del grupo no puede estar vacío.',
                ];
            }

            // Validar si el grupo existe
            $sql_check =
                'SELECT id, max_capacity FROM student_groups WHERE group_code = :group_code';
            $group = $this->pdo->fetchOne($sql_check, ['group_code' => $group_code]);

            if (!$group) {
                return [
                    'status' => 'ERROR',
                    'msg' => "El grupo con código '$group_code' no existe.",
                ];
            }

            // Contar miembros actuales
            $sql_count = 'SELECT COUNT(*) AS total FROM students WHERE `group` = :group_code';
            $count = $this->pdo->fetchOne($sql_count, ['group_code' => $group_code]);

            $total_members = $count['total'] ?? 0;

            // Validar que el conteo no exceda max_capacity
            if ($total_members > $group['max_capacity']) {
                error_log(
                    "Advertencia: El conteo de miembros ($total_members) excede la capacidad máxima ({$group['max_capacity']}) para el grupo '$group_code'.",
                );
                $total_members = $group['max_capacity'];
            }

            // Actualizar contador en la tabla de grupos
            $sql_update =
                'UPDATE student_groups SET current_members = :total WHERE group_code = :group_code';
            $result = $this->pdo->perform($sql_update, [
                'total' => $total_members,
                'group_code' => $group_code,
            ]);

            if ($result) {
                return [
                    'status' => 'OK',
                    'msg' => "Contador de miembros actualizado exitosamente para el grupo '$group_code'.",
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'msg' => "No se pudo actualizar el contador de miembros para el grupo '$group_code'.",
                ];
            }
        } catch (PDOException $e) {
            error_log(
                "Error actualizando contador para el grupo '$group_code': " . $e->getMessage(),
            );
            return [
                'status' => 'EXCEPTION',
                'msg' =>
                    "Error al actualizar el contador de miembros para el grupo '$group_code': " .
                    $e->getMessage(),
            ];
        }
    }

    // --
    public function save_attendance($data)
    {
        // Registra o actualiza la asistencia de un practicante para una fecha específica.
        // Valida los datos requeridos, ajusta las horas trabajadas según el estado de asistencia,
        // guarda o actualiza el registro en student_hours y student_attendance_history,
        // y actualiza las horas completadas del practicante.
        try {
            // Validar datos requeridos
            if (
                !isset($data['student_id']) ||
                !isset($data['date']) ||
                !isset($data['attendance_status'])
            ) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'Faltan datos requeridos: student_id, date o attendance_status.',
                ];
            }

            $student_id = $data['student_id'];
            $date = $data['date'];
            $attendance_status = $data['attendance_status'];
            $worked_hours = isset($data['worked_hours']) ? floatval($data['worked_hours']) : 0;
            $activity_performed = isset($data['activity_performed'])
                ? $data['activity_performed']
                : null;
            $entry_time = isset($data['entry_time']) ? $data['entry_time'] : null;
            $exit_time = isset($data['exit_time']) ? $data['exit_time'] : null;
            $remarks = isset($data['remarks']) ? $data['remarks'] : null;
            $user_id = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;

            // Validar student_id
            $sql_check_student =
                'SELECT first_names, last_names FROM students WHERE id = :student_id';
            $student = $this->pdo->fetchOne($sql_check_student, ['student_id' => $student_id]);
            if (!$student) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El practicante con ID ' . $student_id . ' no existe.',
                ];
            }

            // Validar attendance_status
            $valid_statuses = [0, 1, 2, 3]; // Ausente, Presente, Tardanza, Justificado
            if (!in_array($attendance_status, $valid_statuses)) {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'El estado de asistencia ' .
                        $attendance_status .
                        ' no es válido. Valores permitidos: ' .
                        implode(', ', $valid_statuses) .
                        '.',
                ];
            }

            // Validar formato de fecha
            try {
                $date_obj = new DateTime($date);
                $formatted_date = $date_obj->format('Y-m-d');
            } catch (Exception $e) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El formato de la fecha ' . $date . ' no es válido.',
                ];
            }

            // Validar worked_hours
            if ($worked_hours < 0 || $worked_hours > 24) {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'Las horas trabajadas (' . $worked_hours . ') deben estar entre 0 y 24.',
                ];
            }

            // Ajustar horas según estado de asistencia
            if ($attendance_status == 0 || $attendance_status == 3) {
                // Ausente o Justificado
                $worked_hours = 0;
            } elseif (($attendance_status == 1 || $attendance_status == 2) && $worked_hours == 0) {
                // Presente o Tardanza
                $worked_hours = 8; // Horas por defecto
            }

            // Verificar si ya existe registro en student_hours
            $sql_check =
                'SELECT id FROM student_hours WHERE student_id = :student_id AND date = :date';
            $exists = $this->pdo->fetchOne($sql_check, [
                'student_id' => $student_id,
                'date' => $formatted_date,
            ]);

            if ($exists) {
                // Actualizar registro existente
                $sql = 'UPDATE student_hours
                    SET attendance_status = :attendance_status,
                        worked_hours = :worked_hours,
                        activity_performed = :activity_performed,
                        entry_time = :entry_time,
                        exit_time = :exit_time,
                        remarks = :remarks,
                        modified_by = :user_id,
                        modified_at = NOW()
                    WHERE id = :id';

                $result = $this->pdo->perform($sql, [
                    'id' => $exists['id'],
                    'attendance_status' => $attendance_status,
                    'worked_hours' => $worked_hours,
                    'activity_performed' => $activity_performed,
                    'entry_time' => $entry_time,
                    'exit_time' => $exit_time,
                    'remarks' => $remarks,
                    'user_id' => $user_id,
                ]);

                $msg =
                    'Asistencia actualizada correctamente para ' .
                    $student['first_names'] .
                    ' ' .
                    $student['last_names'] .
                    ' el ' .
                    $date .
                    '.';
            } else {
                // Insertar nuevo registro
                $sql = 'INSERT INTO student_hours
                    (student_id, date, worked_hours, activity_performed, attendance_status,
                     entry_time, exit_time, remarks, recorded_by, created_at, approved)
                    VALUES (:student_id, :date, :worked_hours, :activity_performed, :attendance_status,
                            :entry_time, :exit_time, :remarks, :user_id, NOW(), 0)';

                $result = $this->pdo->perform($sql, [
                    'student_id' => $student_id,
                    'date' => $formatted_date,
                    'worked_hours' => $worked_hours,
                    'activity_performed' => $activity_performed,
                    'attendance_status' => $attendance_status,
                    'entry_time' => $entry_time,
                    'exit_time' => $exit_time,
                    'remarks' => $remarks,
                    'user_id' => $user_id,
                ]);

                $msg =
                    'Asistencia registrada correctamente para ' .
                    $student['first_names'] .
                    ' ' .
                    $student['last_names'] .
                    ' el ' .
                    $date .
                    '.';
            }

            if ($result) {
                // Guardar en historial de asistencias
                $history_result = true;
                try {
                    // Verificar si ya existe registro en student_attendance_history
                    $sql_check_history = 'SELECT id FROM student_attendance_history
                                     WHERE student_id = :student_id AND attendance_date = :date';
                    $exists_history = $this->pdo->fetchOne($sql_check_history, [
                        'student_id' => $student_id,
                        'date' => $formatted_date,
                    ]);

                    if ($exists_history) {
                        // Actualizar registro existente
                        $sql_history = 'UPDATE student_attendance_history SET
                                    attendance_status = :attendance_status,
                                    recorded_by = :user_id,
                                    remarks = :remarks,
                                    recorded_at = NOW()
                                    WHERE id = :id';

                        $history_result = $this->pdo->perform($sql_history, [
                            'id' => $exists_history['id'],
                            'attendance_status' => $attendance_status,
                            'user_id' => $user_id,
                            'remarks' => $remarks,
                        ]);
                    } else {
                        // Insertar nuevo registro
                        $sql_history = 'INSERT INTO student_attendance_history
                                    (student_id, attendance_date, attendance_status, recorded_by, remarks)
                                    VALUES (:student_id, :date, :attendance_status, :user_id, :remarks)';

                        $history_result = $this->pdo->perform($sql_history, [
                            'student_id' => $student_id,
                            'date' => $formatted_date,
                            'attendance_status' => $attendance_status,
                            'user_id' => $user_id,
                            'remarks' => $remarks,
                        ]);
                    }
                } catch (Exception $e) {
                    error_log(
                        'Error al guardar en historial de asistencia para el estudiante ' .
                            $student_id .
                            ': ' .
                            $e->getMessage(),
                    );
                    $history_result = false;
                }

                // Actualizar horas completadas del practicante (solo sumar horas aprobadas)
                $sql_update_hours = 'UPDATE students
                                 SET completed_hours = (
                                     SELECT COALESCE(SUM(worked_hours), 0)
                                     FROM student_hours
                                     WHERE student_id = :student_id
                                     AND attendance_status = 1
                                     AND approved = 1
                                 )
                                 WHERE id = :student_id';

                $this->pdo->perform($sql_update_hours, ['student_id' => $student_id]);

                if (!$history_result) {
                    return [
                        'status' => 'WARNING',
                        'msg' =>
                            $msg .
                            ' Advertencia: No se pudo guardar en el historial de asistencia.',
                    ];
                }

                return [
                    'status' => 'OK',
                    'msg' => $msg,
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'No se pudo guardar la asistencia para ' .
                        $student['first_names'] .
                        ' ' .
                        $student['last_names'] .
                        '.',
                ];
            }
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error al guardar la asistencia: ' . $e->getMessage(),
            ];
        }
    }

    // --
    // --
    public function get_group_attendance_history($group_id)
    {
        // Obtiene el historial de asistencias de los practicantes de un grupo específico.
        // Devuelve información como ID del practicante, nombre completo, última fecha de asistencia,
        // total de registros, conteo de presentes, ausentes, tardanzas, permisos y porcentaje de asistencia.
        try {
            // Validar si el grupo existe
            $sql_check = 'SELECT id FROM student_groups WHERE id = :group_id';
            $group_exists = $this->pdo->fetchOne($sql_check, ['group_id' => $group_id]);

            if (!$group_exists) {
                return [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' => "El grupo con ID $group_id no existe.",
                ];
            }

            $sql = 'SELECT
            s.id AS student_id,
            CONCAT(s.first_names, " ", s.last_names) AS student_name,
            MAX(h.attendance_date) AS last_date,
            COUNT(h.id) AS total_records,
            SUM(CASE WHEN h.attendance_status = 1 THEN 1 ELSE 0 END) AS present,
            SUM(CASE WHEN h.attendance_status = 0 THEN 1 ELSE 0 END) AS absent,
            SUM(CASE WHEN h.attendance_status = 2 THEN 1 ELSE 0 END) AS late,
            SUM(CASE WHEN h.attendance_status = 3 THEN 1 ELSE 0 END) AS permissions,
            ROUND(
                (SUM(
                    CASE
                        WHEN h.attendance_status = 1 THEN 1
                        WHEN h.attendance_status = 2 THEN 0.75
                        WHEN h.attendance_status = 3 THEN 0.5
                        ELSE 0
                    END
                ) * 100.0) / NULLIF(COUNT(h.id), 0), 1
            ) AS attendance_percentage
        FROM students s
        INNER JOIN student_groups sg ON s.group = sg.group_code
        LEFT JOIN student_attendance_history h ON h.student_id = s.id
        WHERE sg.id = :group_id
        AND s.has_credentials = 1
        GROUP BY s.id, s.first_names, s.last_names
        ORDER BY s.last_names ASC, s.first_names ASC';

            $result = $this->pdo->fetchAll($sql, ['group_id' => $group_id]);

            if ($result) {
                foreach ($result as &$row) {
                    if ($row['last_date']) {
                        $date = new DateTime($row['last_date']);
                        $row['formatted_date'] = $date->format('d/m/Y');
                    } else {
                        $row['formatted_date'] = 'Sin registros';
                    }
                }
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                // No hay registros: devolver OK con data vacía para que el frontend
                // muestre el mensaje 'No hay registros de asistencia' en la tabla.
                $response = [
                    'status' => 'OK',
                    'data' => [],
                    'msg' => "No se encontraron registros de asistencia para el grupo con ID $group_id.",
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' =>
                    "Error al obtener el historial de asistencias para el grupo con ID $group_id: " .
                    $e->getMessage(),
                'data' => [],
            ];
        }
        return $response;
    }

    // --
    // --
    private function translate_day($day_english)
    {
        // Traduce un día de la semana de inglés a español.
        // Recibe el nombre del día en inglés (case-insensitive) y retorna su equivalente en español,
        // o el valor original si no es un día válido.
        if (!is_string($day_english) || empty(trim($day_english))) {
            return $day_english;
        }

        $days = [
            'monday' => 'Lunes',
            'tuesday' => 'Martes',
            'wednesday' => 'Miércoles',
            'thursday' => 'Jueves',
            'friday' => 'Viernes',
            'saturday' => 'Sábado',
            'sunday' => 'Domingo',
        ];

        $day_key = strtolower(trim($day_english));
        return isset($days[$day_key]) ? $days[$day_key] : $day_english;
    }

    // --
    // --
    public function mark_attendance($group_id, $date, $attendances)
    {
        // Registra o actualiza la asistencia de múltiples practicantes para una fecha específica en un grupo.
        // Valida el grupo, la fecha y las asistencias, procesa cada registro en student_attendance_history,
        // y retorna el estado de la operación con el número de asistencias procesadas.
        try {
            // Validar group_id
            $sql_check_group = 'SELECT id FROM student_groups WHERE id = :group_id';
            $group_exists = $this->pdo->fetchOne($sql_check_group, ['group_id' => $group_id]);
            if (!$group_exists) {
                return [
                    'status' => 'ERROR',
                    'msg' => "El grupo con ID $group_id no existe.",
                ];
            }

            // Validar fecha
            try {
                $date_obj = new DateTime($date);
                $formatted_date = $date_obj->format('Y-m-d');
            } catch (Exception $e) {
                return [
                    'status' => 'ERROR',
                    'msg' => "El formato de la fecha '$date' no es válido.",
                ];
            }

            // Validar attendances
            if (!is_array($attendances) || empty($attendances)) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'La lista de asistencias debe ser un array no vacío.',
                ];
            }

            $valid_statuses = [0, 1, 2, 3]; // Ausente, Presente, Tardanza, Justificado
            $user_id = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;
            $processed = 0;
            $errors = [];

            // Iniciar transacción
            $this->pdo->beginTransaction();

            foreach ($attendances as $index => $attendance) {
                // Validar estructura de asistencia
                if (!isset($attendance['student_id']) || !isset($attendance['status'])) {
                    $errors[] =
                        'Asistencia en posición ' . ($index + 1) . ': Faltan student_id o status.';
                    continue;
                }

                $student_id = $attendance['student_id'];
                $status = $attendance['status'];
                $remarks = isset($attendance['remarks']) ? $attendance['remarks'] : '';

                // Validar student_id

                $sql_check_student =
                    'SELECT id FROM students WHERE id = :student_id AND `group` = (SELECT group_code FROM student_groups WHERE id = :group_id)';

                $student_exists = $this->pdo->fetchOne($sql_check_student, [
                    'student_id' => $student_id,
                    'group_id' => $group_id,
                ]);
                if (!$student_exists) {
                    $errors[] =
                        'Asistencia en posición ' .
                        ($index + 1) .
                        ": El estudiante con ID $student_id no pertenece al grupo.";
                    continue;
                }

                // Validar status
                if (!in_array($status, $valid_statuses)) {
                    $errors[] =
                        'Asistencia en posición ' .
                        ($index + 1) .
                        ": Estado de asistencia $status no válido.";
                    continue;
                }

                // Guardar en historial de asistencias
                try {
                    $sql_check_history = 'SELECT id FROM student_attendance_history
                                     WHERE student_id = :student_id AND attendance_date = :date';
                    $exists_history = $this->pdo->fetchOne($sql_check_history, [
                        'student_id' => $student_id,
                        'date' => $formatted_date,
                    ]);

                    if ($exists_history) {
                        // Actualizar registro existente
                        $sql_history = 'UPDATE student_attendance_history SET
                                    attendance_status = :attendance_status,
                                    recorded_by = :user_id,
                                    remarks = :remarks,
                                    recorded_at = NOW()
                                    WHERE id = :id';
                        $this->pdo->perform($sql_history, [
                            'id' => $exists_history['id'],
                            'attendance_status' => $status,
                            'user_id' => $user_id,
                            'remarks' => $remarks,
                        ]);
                    } else {
                        // Insertar nuevo registro
                        $sql_history = 'INSERT INTO student_attendance_history
                                    (student_id, attendance_date, attendance_status, recorded_by, remarks)
                                    VALUES (:student_id, :date, :attendance_status, :user_id, :remarks)';
                        $this->pdo->perform($sql_history, [
                            'student_id' => $student_id,
                            'date' => $formatted_date,
                            'attendance_status' => $status,
                            'user_id' => $user_id,
                            'remarks' => $remarks,
                        ]);
                    }
                    $processed++;
                } catch (Exception $e) {
                    $errors[] = "Asistencia para estudiante $student_id: " . $e->getMessage();
                    error_log(
                        "Error al guardar asistencia para estudiante $student_id: " .
                            $e->getMessage(),
                    );
                }
            }

            // Confirmar transacción
            $this->pdo->commit();

            if ($processed === count($attendances)) {
                return [
                    'status' => 'OK',
                    'msg' => "Se procesaron $processed asistencias exitosamente para el grupo con ID $group_id el $formatted_date.",
                ];
            } else {
                return [
                    'status' => 'WARNING',
                    'msg' =>
                        "Se procesaron $processed de " .
                        count($attendances) .
                        " asistencias para el grupo con ID $group_id el $formatted_date. Errores: " .
                        implode('; ', $errors) .
                        '.',
                ];
            }
        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return [
                'status' => 'EXCEPTION',
                'msg' =>
                    "Error al guardar las asistencias para el grupo con ID $group_id: " .
                    $e->getMessage(),
            ];
        }
    }

    // --
    // --
    private function update_student_hours($student_id)
    {
        // Actualiza las horas completadas de un practicante.
        // Calcula la suma de horas trabajadas aprobadas (estado de asistencia = 1 y aprobado = 1)
        // y actualiza el campo completed_hours en la tabla students.
        // Valida la existencia del estudiante y retorna el estado de la operación.
        try {
            // Validar si el estudiante existe
            $sql_check = 'SELECT first_names, last_names, required_hours 
                      FROM students 
                      WHERE id = :student_id';
            $student = $this->pdo->fetchOne($sql_check, ['student_id' => $student_id]);

            if (!$student) {
                return [
                    'status' => 'ERROR',
                    'msg' => "El practicante con ID $student_id no existe.",
                ];
            }

            // Calcular horas completadas
            $sql_hours = 'SELECT COALESCE(SUM(worked_hours), 0) AS total_hours
                      FROM student_hours
                      WHERE student_id = :student_id
                      AND attendance_status = 1
                      AND approved = 1';
            $hours = $this->pdo->fetchOne($sql_hours, ['student_id' => $student_id]);
            $total_hours = $hours['total_hours'];

            // Validar que las horas no excedan required_hours
            if ($student['required_hours'] > 0 && $total_hours > $student['required_hours']) {
                error_log(
                    "Advertencia: Las horas completadas ($total_hours) exceden las horas requeridas ({$student['required_hours']}) para el estudiante $student_id.",
                );
                $total_hours = $student['required_hours'];
            }

            // Actualizar horas completadas
            $sql_update = 'UPDATE students
                       SET completed_hours = :total_hours
                       WHERE id = :student_id';
            $result = $this->pdo->perform($sql_update, [
                'total_hours' => $total_hours,
                'student_id' => $student_id,
            ]);

            if ($result) {
                return [
                    'status' => 'OK',
                    'msg' =>
                        'Horas completadas actualizadas exitosamente para ' .
                        $student['first_names'] .
                        ' ' .
                        $student['last_names'] .
                        " ($total_hours horas).",
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        'No se pudo actualizar las horas completadas para ' .
                        $student['first_names'] .
                        ' ' .
                        $student['last_names'] .
                        '.',
                ];
            }
        } catch (PDOException $e) {
            error_log(
                "Error al actualizar horas para el estudiante $student_id: " . $e->getMessage(),
            );
            return [
                'status' => 'EXCEPTION',
                'msg' =>
                    "Error al actualizar las horas completadas para el estudiante con ID $student_id: " .
                    $e->getMessage(),
            ];
        }
    }

    // --
    // --
    public function get_student_attendance_history($student_id, $date = null)
    {
        // Obtiene el historial de asistencia de un practicante específico.
        // Devuelve información como ID, fecha de asistencia, estado de asistencia, hora de registro,
        // observaciones, nombre del usuario que registró y estado en texto legible.
        // Filtra por fecha si se proporciona, valida el estudiante y la fecha.
        try {
            // Validar student_id
            $sql_check = 'SELECT first_names, last_names FROM students WHERE id = :student_id';
            $student = $this->pdo->fetchOne($sql_check, ['student_id' => $student_id]);

            if (!$student) {
                return [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' => "El practicante con ID $student_id no existe.",
                ];
            }

            // Validar formato de fecha si se proporciona
            if ($date) {
                try {
                    $date_obj = new DateTime($date);
                    $formatted_date = $date_obj->format('Y-m-d');
                } catch (Exception $e) {
                    return [
                        'status' => 'ERROR',
                        'data' => [],
                        'msg' => "El formato de la fecha '$date' no es válido.",
                    ];
                }
            }

            $sql = 'SELECT
            h.id,
            h.attendance_date,
            h.attendance_status,
            h.recorded_at,
            h.remarks,
            COALESCE(CONCAT(u.first_name, " ", u.last_name), "No especificado") AS recorded_by_name,
            CASE h.attendance_status
                WHEN 0 THEN "Ausente"
                WHEN 1 THEN "Presente"
                WHEN 2 THEN "Tardanza"
                WHEN 3 THEN "Justificado"
            END AS status_text
        FROM student_attendance_history h
        LEFT JOIN user u ON h.recorded_by = u.id
        WHERE h.student_id = :student_id';

            $params = ['student_id' => $student_id];

            if ($date) {
                $sql .= ' AND h.attendance_date = :date';
                $params['date'] = $formatted_date;
            }

            $sql .= ' ORDER BY h.recorded_at DESC';

            $result = $this->pdo->fetchAll($sql, $params);

            if ($result) {
                foreach ($result as &$row) {
                    $date_obj = new DateTime($row['attendance_date']);
                    $row['formatted_date'] = $date_obj->format('d/m/Y');
                    $time_obj = new DateTime($row['recorded_at']);
                    $row['formatted_time'] = $time_obj->format('d/m/Y H:i:s');
                }
                $response = ['status' => 'OK', 'data' => $result];
            } else {
                // No hay registros: devolver OK con data vacía para que el frontend
                // pueda manejar la situación mostrando "No hay historial de asistencia".
                $response = [
                    'status' => 'OK',
                    'data' => [],
                    'msg' =>
                        'No se encontraron registros de asistencia para ' .
                        $student['first_names'] .
                        ' ' .
                        $student['last_names'] .
                        '.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'msg' =>
                    "Error al obtener el historial de asistencia para el estudiante con ID $student_id: " .
                    $e->getMessage(),
                'data' => [],
            ];
        }

        return $response;
    }
    // --
    // --
    public function debug_check_attendance_history()
    {
        // Obtiene los últimos 10 registros del historial de asistencia para depuración.
        // Devuelve el estado, el total de registros y los datos obtenidos, incluyendo nombres de estudiantes
        // y usuarios que registraron, con fechas formateadas y estados en texto legible.
        try {
            $sql = 'SELECT 
            h.id,
            h.student_id,
            CONCAT(s.first_names, " ", s.last_names) AS student_name,
            h.attendance_date,
            h.attendance_status,
            CASE h.attendance_status
                WHEN 0 THEN "Ausente"
                WHEN 1 THEN "Presente"
                WHEN 2 THEN "Tardanza"
                WHEN 3 THEN "Justificado"
                ELSE "Desconocido"
            END AS status_text,
            h.recorded_at,
            h.remarks,
            COALESCE(CONCAT(u.first_name, " ", u.last_name), "No especificado") AS recorded_by_name
        FROM student_attendance_history h
        LEFT JOIN students s ON h.student_id = s.id
        LEFT JOIN user u ON h.recorded_by = u.id
        ORDER BY h.recorded_at DESC 
        LIMIT 10';

            $result = $this->pdo->fetchAll($sql);

            if ($result) {
                foreach ($result as &$row) {
                    $date_obj = new DateTime($row['attendance_date']);
                    $row['formatted_attendance_date'] = $date_obj->format('d/m/Y');
                    $time_obj = new DateTime($row['recorded_at']);
                    $row['formatted_recorded_at'] = $time_obj->format('d/m/Y H:i:s');
                }
                $response = [
                    'status' => 'OK',
                    'total' => count($result),
                    'data' => $result,
                    'msg' =>
                        'Últimos ' .
                        count($result) .
                        ' registros de asistencia obtenidos exitosamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'total' => 0,
                    'data' => [],
                    'msg' => 'No se encontraron registros de asistencia para depuración.',
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'total' => 0,
                'data' => [],
                'msg' =>
                    'Error al obtener el historial de asistencia para depuración: ' .
                    $e->getMessage() .
                    '.',
            ];
        }

        return $response;
    }

    // -- DEBUG: Verificar supervisores
    // --
    public function debug_supervisors()
    {
        // Obtiene información de depuración sobre supervisores.
        // Devuelve datos sobre usuarios con rol SUPERVISOR, grupos de tipo SUPERVISOR,
        // practicantes en grupos de tipo SUPERVISOR y la relación completa entre usuarios y practicantes con rol SUPERVISOR.
        try {
            $result = [];

            // 1. Verificar usuarios con rol SUPERVISOR
            $sql1 = 'SELECT 
            u.id, 
            CONCAT(u.first_name, " ", u.last_name) AS name, 
            r.description AS role,
            u.email
        FROM user u
        INNER JOIN role r ON u.id_role = r.id
        WHERE r.id = 5 OR UPPER(TRIM(r.description)) = "SUPERVISOR"
        AND u.status = 1 
        AND u.active = 1';
            $result['users_with_supervisor_role'] = $this->pdo->fetchAll($sql1);

            // 2. Verificar grupos de tipo SUPERVISOR
            $sql2 = 'SELECT 
            id, 
            group_code, 
            group_name, 
            group_type,
            DATE_FORMAT(created_at, "%d/%m/%Y") AS created_at_formatted,
            max_capacity,
            current_members
        FROM student_groups 
        WHERE group_type = "SUPERVISOR"';
            $result['supervisor_groups'] = $this->pdo->fetchAll($sql2);

            // 3. Verificar practicantes en grupos de tipo SUPERVISOR
            $sql3 = 'SELECT 
            s.id, 
            s.user_id, 
            s.first_names, 
            s.last_names, 
            s.group, 
            s.status, 
            COALESCE(CONCAT(u.first_name, " ", u.last_name), "No especificado") AS user_name,
            DATE_FORMAT(s.group_assignment_date, "%d/%m/%Y") AS group_assignment_date_formatted
        FROM students s
        INNER JOIN student_groups sg ON s.group = sg.group_code
        LEFT JOIN user u ON s.user_id = u.id
        WHERE sg.group_type = "SUPERVISOR"';
            $result['students_in_supervisor_groups'] = $this->pdo->fetchAll($sql3);

            // 4. Verificar relación completa
            $sql4 = 'SELECT 
            u.id, 
            CONCAT(u.first_name, " ", u.last_name) AS full_name,
            r.description AS role, 
            COALESCE(s.group, "N/A") AS group, 
            s.status AS student_status,
            COALESCE(sg.group_type, "N/A") AS group_type
        FROM user u
        INNER JOIN role r ON u.id_role = r.id
        LEFT JOIN students s ON u.id = s.user_id
        LEFT JOIN student_groups sg ON s.group = sg.group_code
        WHERE r.id = 5 OR UPPER(TRIM(r.description)) = "SUPERVISOR"';
            $result['complete_relationship'] = $this->pdo->fetchAll($sql4);

            // Verificar si todas las consultas devolvieron datos vacíos
            if (
                empty($result['users_with_supervisor_role']) &&
                empty($result['supervisor_groups']) &&
                empty($result['students_in_supervisor_groups']) &&
                empty($result['complete_relationship'])
            ) {
                return [
                    'status' => 'ERROR',
                    'data' => $result,
                    'msg' =>
                        'No se encontraron datos de supervisores o grupos de tipo SUPERVISOR para depuración.',
                ];
            }

            return [
                'status' => 'OK',
                'data' => $result,
                'msg' => 'Datos de depuración de supervisores obtenidos exitosamente.',
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'data' => [],
                'msg' =>
                    'Error al obtener información de depuración de supervisores: ' .
                    $e->getMessage() .
                    '.',
            ];
        }
    }

    // Obtener grupos que supervisa un usuario
    public function get_supervisions_by_user($user_id)
    {
        $response = ['status' => 'ERROR', 'data' => [], 'msg' => ''];
        try {
            $sql = 'SELECT sg.id, sg.group_code, sg.group_name
                    FROM group_supervisors gs
                    INNER JOIN student_groups sg ON gs.group_id = sg.id
                    WHERE gs.supervisor_id = :user_id AND gs.status = 1 AND sg.status = 1
                    ORDER BY sg.group_name ASC';

            $rows = $this->pdo->fetchAll($sql, ['user_id' => $user_id]);

            $response['status'] = 'OK';
            $response['data'] = $rows ?: [];
        } catch (PDOException $e) {
            error_log('[M_S_Groups::get_supervisions_by_user] ' . $e->getMessage());
            $response['status'] = 'EXCEPTION';
            $response['msg'] = 'Error consultando supervisiones.';
            $response['data'] = [];
        }

        return $response;
    }

    // -
    public function get_available_student_users($group_id = null)
    {
        // Obtiene usuarios con rol PRACTICANTE o SUPERVISOR que no están asignados a un grupo
        // o que están en un grupo diferente al especificado.
        // Devuelve información como ID, nombre completo, email, teléfono, rol, ID de practicante,
        // grupo actual, tipo de grupo y prioridad para ordenamiento.
        try {
            // Obtener IDs de user_roles (tabla para practicantes/supervisores)
            $sql_roles =
                'SELECT id, description FROM user_roles WHERE UPPER(TRIM(description)) IN ("PRACTICANTE", "SUPERVISOR")';
            $roles = $this->pdo->fetchAll($sql_roles);
            $user_role_ids = array_column($roles, 'id');
            if (empty($user_role_ids)) {
                return [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' => 'No se encontraron roles PRACTICANTE o SUPERVISOR.',
                ];
            }

            $sql =
                '
                SELECT
                    u.id,
                    CONCAT(u.first_name, " ", u.last_name) AS full_name,
                    u.email,
                    u.telephone AS phone,
                    ur.description AS role,
                    s.id AS student_id,
                    COALESCE(s.`group`, "Sin grupo") AS group_display,
                    COALESCE(sg.group_type, "N/A") AS group_type,
                    CASE
                        WHEN s.`group` IS NULL THEN 1
                        ELSE 2
                    END AS priority
                FROM user u
                INNER JOIN user_roles ur ON u.id_user_role = ur.id
                LEFT JOIN students s ON u.id = s.user_id
                LEFT JOIN student_groups sg ON s.`group` = sg.group_code
                WHERE u.status = 1
                AND u.id_user_role IN (' .
                implode(',', array_fill(0, count($user_role_ids), '?')) .
                ')
            ';

            $params = $user_role_ids;

            // Excluir usuarios del grupo especificado
            if ($group_id) {
                $sql .=
                    ' AND (s.`group` IS NULL OR s.`group` != (SELECT group_code FROM student_groups WHERE id = ?))';
                $params[] = $group_id;
            }

            $sql .= ' ORDER BY priority ASC, u.first_name ASC';

            $result = $this->pdo->fetchAll($sql, $params);

            if ($result) {
                $response = [
                    'status' => 'OK',
                    'data' => $result,
                    'msg' => 'Usuarios disponibles obtenidos exitosamente.',
                ];
            } else {
                $response = [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' =>
                        'No se encontraron usuarios disponibles' .
                        ($group_id ? " para el grupo con ID $group_id." : '.'),
                ];
            }
        } catch (PDOException $e) {
            $response = [
                'status' => 'EXCEPTION',
                'data' => [],
                'msg' => 'Error al obtener usuarios disponibles: ' . $e->getMessage() . '.',
            ];
        }

        return $response;
    }
    // --
    public function add_user_to_group($user_id, $group_id)
    {
        // Agrega un usuario a un grupo de practicantes.
        // Valida la existencia y capacidad del grupo, verifica el usuario y su rol,
        // actualiza o inserta el registro en students, ajusta el rol según el tipo de grupo
        // y actualiza los contadores de miembros.
        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();

            // Validar grupo
            $sql_group = 'SELECT group_code, group_name, group_type, max_capacity, current_members
                      FROM student_groups
                      WHERE id = :group_id AND status = 1';
            $group = $this->pdo->fetchOne($sql_group, ['group_id' => $group_id]);

            if (!$group) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                return [
                    'status' => 'ERROR',
                    'msg' => "El grupo con ID $group_id no existe o está inactivo.",
                ];
            }

            // Verificar capacidad del grupo
            if ($group['current_members'] >= $group['max_capacity']) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                return [
                    'status' => 'ERROR',
                    'msg' => "El grupo '{$group['group_name']}' ha alcanzado su capacidad máxima.",
                ];
            }

            // Validar usuario y su rol (usar user_roles para practicantes/supervisores)
            $sql_user = 'SELECT u.id, u.first_name, u.last_name, u.email, u.telephone,
                            u.id_document_type, u.document_number, ur.id AS role_id,
                            ur.description AS role_description
                     FROM user u
                     INNER JOIN user_roles ur ON u.id_user_role = ur.id
                     WHERE u.id = :user_id AND u.status = 1';
            $user = $this->pdo->fetchOne($sql_user, ['user_id' => $user_id]);

            if (!$user) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                return [
                    'status' => 'ERROR',
                    'msg' => "El usuario con ID $user_id no existe o está inactivo.",
                ];
            }

            // Validar rol permitido
            $valid_roles = ['PRACTICANTE', 'SUPERVISOR'];
            if (!in_array(strtoupper(trim($user['role_description'])), $valid_roles)) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                return [
                    'status' => 'ERROR',
                    'msg' =>
                        "El usuario tiene un rol no permitido: {$user['role_description']}. Roles válidos: " .
                        implode(', ', $valid_roles) .
                        '.',
                ];
            }

            // Verificar si ya existe en students
            $sql_check = 'SELECT id, `group` FROM students WHERE user_id = :user_id';
            $student = $this->pdo->fetchOne($sql_check, ['user_id' => $user_id]);

            $user_id_session = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : null;

            if ($student) {
                // Ya existe en students, solo actualizar el grupo
                if ($student['group'] === $group['group_code']) {
                    if ($this->pdo->inTransaction()) {
                        $this->pdo->rollBack();
                    }
                    return [
                        'status' => 'ERROR',
                        'msg' => "El usuario ya pertenece al grupo '{$group['group_name']}'.",
                    ];
                }

                // Actualizar grupo existente
                $sql_update = 'UPDATE students
                           SET `group` = :group_code,
                               group_assignment_date = NOW(),
                               assigned_by = :assigned_by
                           WHERE id = :student_id';
                $result = $this->pdo->perform($sql_update, [
                    'group_code' => $group['group_code'],
                    'assigned_by' => $user_id_session,
                    'student_id' => $student['id'],
                ]);

                if ($result) {
                    // Actualizar contadores
                    if ($student['group']) {
                        $this->update_member_count_by_code($student['group']);
                    }
                    $this->update_member_count_by_code($group['group_code']);

                    // Actualizar rol según el tipo de grupo
                    $this->update_role_by_group($user_id, $student['group'], $group['group_code']);

                    $this->pdo->commit();
                    return [
                        'status' => 'OK',
                        'msg' => "{$user['first_name']} {$user['last_name']} ha sido reasignado al grupo '{$group['group_name']}' exitosamente.",
                    ];
                } else {
                    if ($this->pdo->inTransaction()) {
                        $this->pdo->rollBack();
                    }
                    return [
                        'status' => 'ERROR',
                        'msg' => "No se pudo reasignar el usuario al grupo '{$group['group_name']}'.",
                    ];
                }
            } else {
                // No existe en students, crear nuevo registro
                // Validar document_number y id_document_type
                if (empty($user['document_number']) || empty($user['id_document_type'])) {
                    if ($this->pdo->inTransaction()) {
                        $this->pdo->rollBack();
                    }
                    return [
                        'status' => 'ERROR',
                        'msg' =>
                            'El usuario debe tener un número de documento y tipo de documento válidos.',
                    ];
                }

                $sql_insert = 'INSERT INTO students (
                user_id,
                first_names,
                last_names,
                document_type_id,
                document_number,
                email,
                phone,
                `group`,
                group_assignment_date,
                assigned_by,
                has_credentials,
                required_hours,
                completed_hours,
                status
            ) VALUES (
                :user_id,
                :first_names,
                :last_names,
                :document_type_id,
                :document_number,
                :email,
                :phone,
                :group,
                NOW(),
                :assigned_by,
                1,
                0,
                0,
                1
            )';

                $result = $this->pdo->perform($sql_insert, [
                    'user_id' => $user_id,
                    'first_names' => $user['first_name'],
                    'last_names' => $user['last_name'],
                    'document_type_id' => $user['id_document_type'],
                    'document_number' => $user['document_number'],
                    'email' => $user['email'],
                    'phone' => $user['telephone'],
                    'group' => $group['group_code'],
                    'assigned_by' => $user_id_session,
                ]);

                if ($result) {
                    // Actualizar contador del grupo
                    $this->update_member_count_by_code($group['group_code']);

                    // Actualizar rol según el tipo de grupo
                    $this->update_role_by_group($user_id, null, $group['group_code']);

                    $this->pdo->commit();
                    return [
                        'status' => 'OK',
                        'msg' => "{$user['first_name']} {$user['last_name']} ha sido agregado al grupo '{$group['group_name']}' exitosamente.",
                    ];
                } else {
                    if ($this->pdo->inTransaction()) {
                        $this->pdo->rollBack();
                    }
                    return [
                        'status' => 'ERROR',
                        'msg' => "No se pudo agregar el usuario al grupo '{$group['group_name']}'.",
                    ];
                }
            }
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return [
                'status' => 'EXCEPTION',
                'msg' =>
                    "Error al agregar el usuario con ID $user_id al grupo con ID $group_id: " .
                    $e->getMessage() .
                    '.',
            ];
        }
    }

    // --
    // --
    private function update_role_by_group($user_id, $previous_group, $new_group)
    {
        // Actualiza el rol de un usuario según el tipo de grupo al que se asigna.
        // Cambia a rol SUPERVISOR si entra a un grupo tipo SUPERVISOR, o a rol PRACTICANTE
        // si sale de un grupo tipo SUPERVISOR y entra a uno tipo PRACTICANTE.
        // Valida el usuario, los grupos y los roles, retornando el estado de la operación.
        $manageTransaction = !$this->pdo->inTransaction();
        try {
            // Validar usuario
            $sql_check_user =
                'SELECT id, CONCAT(first_name, " ", last_name) AS full_name FROM user WHERE id = :user_id AND status = 1';
            $user = $this->pdo->fetchOne($sql_check_user, ['user_id' => $user_id]);
            if (!$user) {
                return [
                    'status' => 'ERROR',
                    'msg' => "El usuario con ID $user_id no existe o está inactivo.",
                ];
            }
            // Validar nuevo grupo
            $sql_new_group_type =
                'SELECT group_type FROM student_groups WHERE group_code = :group_code';
            $new_group_info = $this->pdo->fetchOne($sql_new_group_type, [
                'group_code' => $new_group,
            ]);
            if (!$new_group_info) {
                return [
                    'status' => 'ERROR',
                    'msg' => "El grupo con código '$new_group' no existe.",
                ];
            }
            // Obtener tipo del grupo anterior (si existe)
            $previous_group_type = null;
            if ($previous_group) {
                $sql_previous_group_type =
                    'SELECT group_type FROM student_groups WHERE group_code = :group_code';
                $previous_group_info = $this->pdo->fetchOne($sql_previous_group_type, [
                    'group_code' => $previous_group,
                ]);
                if (!$previous_group_info) {
                    return [
                        'status' => 'ERROR',
                        'msg' => "El grupo anterior con código '$previous_group' no existe.",
                    ];
                }
                $previous_group_type = $previous_group_info['group_type'];
            }
            // Obtener IDs de user_roles
            $sql_role_supervisor =
                'SELECT id FROM user_roles WHERE UPPER(TRIM(description)) = "SUPERVISOR" LIMIT 1';
            $role_supervisor = $this->pdo->fetchOne($sql_role_supervisor);
            if (!$role_supervisor) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El rol SUPERVISOR no está configurado en el sistema.',
                ];
            }
            $id_user_role_supervisor = $role_supervisor['id'];
            $sql_role_student =
                'SELECT id FROM user_roles WHERE UPPER(TRIM(description)) = "PRACTICANTE" LIMIT 1';
            $role_student = $this->pdo->fetchOne($sql_role_student);
            if (!$role_student) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'El rol PRACTICANTE no está configurado en el sistema.',
                ];
            }
            $id_user_role_student = $role_student['id'];
            // Iniciar transacción solo si no hay una activa
            if ($manageTransaction) {
                $this->pdo->beginTransaction();
            }
            // Actualizar rol según el tipo de grupo
            $updated = false;
            if ($new_group_info['group_type'] === 'SUPERVISOR') {
                $sql_update_role = 'UPDATE user SET id_user_role = :id_user_role WHERE id = :user_id';
                $result = $this->pdo->perform($sql_update_role, [
                    'id_user_role' => $id_user_role_supervisor,
                    'user_id' => $user_id,
                ]);
                $updated = $result;
            } elseif (
                $previous_group_type === 'SUPERVISOR' &&
                $new_group_info['group_type'] === 'PRACTICANTE'
            ) {
                $sql_update_role = 'UPDATE user SET id_user_role = :id_user_role WHERE id = :user_id';
                $result = $this->pdo->perform($sql_update_role, [
                    'id_user_role' => $id_user_role_student,
                    'user_id' => $user_id,
                ]);
                $updated = $result;
                // Política: al demotar de SUPERVISOR a PRACTICANTE, eliminar todas las asignaciones
                // de supervisión que el usuario tenga y limpiar supervisor_id en student_groups.
                try {
                    $sql_delete_supervisions =
                        'DELETE FROM group_supervisors WHERE supervisor_id = :supervisor_id';
                    $this->pdo->perform($sql_delete_supervisions, ['supervisor_id' => $user_id]);
                    $sql_clear_sg =
                        'UPDATE student_groups SET supervisor_id = NULL WHERE supervisor_id = :supervisor_id';
                    $this->pdo->perform($sql_clear_sg, ['supervisor_id' => $user_id]);
                } catch (Exception $e) {
                    // Registrar el error pero no detener la operación principal
                    error_log(
                        'Error al eliminar asignaciones de supervisor en update_role_by_group para user ' .
                            $user_id .
                            ': ' .
                            $e->getMessage(),
                    );
                }
            }
            if ($manageTransaction) {
                $this->pdo->commit();
            }
            if ($updated) {
                return [
                    'status' => 'OK',
                    'msg' => "Rol actualizado exitosamente para {$user['full_name']} al grupo '$new_group'.",
                ];
            } else {
                return [
                    'status' => 'OK',
                    'msg' => "No se requirió actualización de rol para {$user['full_name']}.",
                ];
            }
        } catch (PDOException $e) {
            if ($manageTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log(
                "Error al actualizar rol para el usuario $user_id, grupo anterior: '$previous_group', grupo nuevo: '$new_group': " .
                    $e->getMessage(),
            );
            return [
                'status' => 'EXCEPTION',
                'msg' =>
                    "Error al actualizar el rol del usuario con ID $user_id: " .
                    $e->getMessage() .
                    '.',
            ];
        }
    }

    // --

    // ...existing code...
    // --
    public function get_config($keys = [])
    {
        // Obtiene valores de configuración desde student_config.
        // Filtra por una lista de claves si se proporciona, o devuelve todas las configuraciones.
        // Retorna un array con estado, datos de configuración y mensaje.
        try {
            // Validar que $keys sea un array y no contenga elementos vacíos
            if (!is_array($keys)) {
                return [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' => 'Las claves deben ser proporcionadas como un array.',
                ];
            }

            if (!empty($keys)) {
                // Eliminar claves vacías o no válidas
                $keys = array_filter($keys, function ($key) {
                    return is_string($key) && !empty(trim($key));
                });

                if (empty($keys)) {
                    return [
                        'status' => 'ERROR',
                        'data' => [],
                        'msg' => 'No se proporcionaron claves válidas.',
                    ];
                }

                // Escapar claves usando reemplazo de comillas simples (SQL estándar)
                // NOTA: El wrapper PDO del framework no maneja correctamente named placeholders en IN()
                $escaped_keys = array_map(function ($key) {
                    return "'" . str_replace("'", "''", $key) . "'";
                }, $keys);
                $keys_str = implode(',', $escaped_keys);
                $sql = "SELECT `key`, `value` FROM student_config WHERE `key` IN ($keys_str)";
                $result = $this->pdo->fetchAll($sql);
            } else {
                $sql = 'SELECT `key`, `value` FROM student_config';
                $result = $this->pdo->fetchAll($sql);
            }

            $config = [];
            if ($result) {
                foreach ($result as $row) {
                    $config[$row['key']] = $row['value'];
                }
                return [
                    'status' => 'OK',
                    'data' => $config,
                    'msg' => 'Configuraciones obtenidas exitosamente.',
                ];
            } else {
                return [
                    'status' => 'ERROR',
                    'data' => [],
                    'msg' => empty($keys)
                        ? 'No se encontraron configuraciones en el sistema.'
                        : 'No se encontraron las configuraciones solicitadas.',
                ];
            }
        } catch (PDOException $e) {
            $error_msg =
                'Error al obtener configuraciones' .
                (!empty($keys) ? ' para las claves: ' . implode(', ', $keys) : '') .
                ': ' .
                $e->getMessage();
            error_log($error_msg);
            return [
                'status' => 'EXCEPTION',
                'data' => [],
                'msg' => $error_msg . '.',
            ];
        }
    }

    // Actualiza la configuración de Meet y horarios de un grupo
    public function update_meeting_config($data)
    {
        try {
            // Si se está actualizando el horario, verificar restricciones
            if (isset($data['schedule'])) {
                require_once APP_PATH . 'models' . DS . 'M_Schedule_Config.php';
                $scheduleConfig = new M_Schedule_Config();

                // 1. Verificar límite de modificaciones del GRUPO
                $maxMods = $scheduleConfig->get_config('max_schedule_modifications');
                if ($maxMods['status'] === 'OK') {
                    $max_modifications = (int)$maxMods['value'];

                    // Contar cuántas veces ha modificado este grupo
                    $sql_count = 'SELECT COUNT(*) as total FROM schedule_modification_log WHERE group_id = ?';
                    $stmt_count = $this->pdo->prepare($sql_count);
                    $stmt_count->execute([$data['id']]);
                    $count_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
                    $current_modifications = (int)$count_result['total'];

                    if ($current_modifications >= $max_modifications) {
                        return [
                            'status' => 'ERROR',
                            'msg' => 'Este grupo ha alcanzado el límite de ' . $max_modifications . ' modificaciones de horario permitidas.'
                        ];
                    }
                }

                // 2. Validar que el horario no se repita con otros grupos (automático)
                $new_schedules = is_array($data['schedule']) ? $data['schedule'] : json_decode($data['schedule'], true);

                if ($new_schedules && is_array($new_schedules)) {
                    foreach ($new_schedules as $new_schedule) {
                        // Buscar conflictos con otros grupos activos
                        $sql_conflict = "SELECT sg.id, sg.name, sg.schedule
                                       FROM student_groups sg
                                       WHERE sg.id != ?
                                       AND sg.status = 'active'
                                       AND sg.schedule IS NOT NULL
                                       AND sg.schedule != ''";

                        $stmt_conflict = $this->pdo->prepare($sql_conflict);
                        $stmt_conflict->execute([$data['id']]);
                        $other_groups = $stmt_conflict->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($other_groups as $other_group) {
                            $other_schedules = json_decode($other_group['schedule'], true);
                            if ($other_schedules && is_array($other_schedules)) {
                                foreach ($other_schedules as $other_schedule) {
                                    // Verificar si coinciden día y horarios
                                    if ($other_schedule['day'] === $new_schedule['day']) {
                                        $new_start = strtotime($new_schedule['start_time']);
                                        $new_end = strtotime($new_schedule['end_time']);
                                        $other_start = strtotime($other_schedule['start_time']);
                                        $other_end = strtotime($other_schedule['end_time']);

                                        // Hay conflicto si los horarios se solapan
                                        if (($new_start >= $other_start && $new_start < $other_end) ||
                                            ($new_end > $other_start && $new_end <= $other_end) ||
                                            ($new_start <= $other_start && $new_end >= $other_end)) {

                                            $days = [
                                                'monday' => 'Lunes',
                                                'tuesday' => 'Martes',
                                                'wednesday' => 'Miércoles',
                                                'thursday' => 'Jueves',
                                                'friday' => 'Viernes',
                                                'saturday' => 'Sábado',
                                                'sunday' => 'Domingo'
                                            ];
                                            $day_name = $days[$new_schedule['day']] ?? $new_schedule['day'];

                                            return [
                                                'status' => 'ERROR',
                                                'msg' => 'El horario del ' . $day_name . ' de ' . $new_schedule['start_time'] . ' a ' . $new_schedule['end_time'] . ' ya está ocupado por el grupo "' . $other_group['name'] . '". Por favor, elige otro horario.'
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // 3. Registrar la modificación en el historial
                $sql_old = 'SELECT schedule FROM student_groups WHERE id = ?';
                $stmt_old = $this->pdo->prepare($sql_old);
                $stmt_old->execute([$data['id']]);
                $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);
                $old_schedule = $old_data ? $old_data['schedule'] : null;

                $scheduleConfig->log_schedule_modification(
                    null, // No es por estudiante individual
                    $data['id'],
                    'Actualización de horario de reuniones',
                    $data['modified_by'] ?? null,
                    $old_schedule ? json_decode($old_schedule, true) : null,
                    $new_schedules,
                    'Modificación de horario de reuniones del grupo'
                );
            }

            $fields = [];
            $params = ['id' => $data['id']];

            if (isset($data['meet_link'])) {
                $fields[] = 'meet_link = :meet_link';
                $params['meet_link'] = $data['meet_link'];
            }

            if (isset($data['schedule'])) {
                $fields[] = 'schedule = :schedule';
                $params['schedule'] = is_array($data['schedule'])
                    ? json_encode($data['schedule'])
                    : $data['schedule'];
            }

            if (empty($fields)) {
                return [
                    'status' => 'ERROR',
                    'msg' => 'No hay datos para actualizar.',
                ];
            }

            $sql = 'UPDATE student_groups SET ' . implode(', ', $fields) . ' WHERE id = :id';
            $result = $this->pdo->perform($sql, $params);

            if ($result !== false) {
                return ['status' => 'OK', 'msg' => 'Configuración actualizada correctamente.'];
            } else {
                return ['status' => 'ERROR', 'msg' => 'Error al actualizar la configuración.'];
            }
        } catch (PDOException $e) {
            error_log('Error en update_meeting_config: ' . $e->getMessage());
            return [
                'status' => 'EXCEPTION',
                'msg' => 'Error en la base de datos: ' . $e->getMessage(),
            ];
        }
    }
}
