<?php
/**
 * Modelo para estadísticas de jornadas laborales
 * Consulta datos de work_sessions del sistema de practicantes
 */
class M_Work_Session extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Obtener grupos activos
     */
    public function getGroups()
    {
        try {
            $sql = "SELECT id, group_name as name
                    FROM student_groups
                    ORDER BY group_name";

            $result = $this->pdo->fetchAll($sql);
            return $result ? $result : [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtener estadísticas generales desde work_sessions y students
     */
    public function getGeneralStatistics($fecha_inicio, $fecha_fin, $id_grupo)
    {
        try {
            $params = [];

            // Contar total de practicantes del grupo
            $sql_practicantes = "SELECT COUNT(DISTINCT s.user_id) as total
                                 FROM students s
                                 INNER JOIN student_groups sg ON sg.group_code = s.`group`
                                 WHERE s.user_id IS NOT NULL";

            if ($id_grupo) {
                $sql_practicantes .= " AND sg.id = :id_grupo_p";
                $params['id_grupo_p'] = $id_grupo;
            }

            $practicantes = $this->pdo->fetchOne($sql_practicantes, $params);
            $total_practicantes = $practicantes ? $practicantes['total'] : 0;

            // Obtener horas trabajadas
            $params2 = [];
            $sql = "SELECT
                        COALESCE(SUM(CASE WHEN ws.is_last_session_of_day = 0 OR ws.is_last_session_of_day IS NULL
                            THEN COALESCE(ws.total_hours, ws.total_worked_minutes/60) ELSE 0 END), 0) as total_horas_normales,
                        COALESCE(SUM(CASE WHEN ws.is_last_session_of_day = 1
                            THEN COALESCE(ws.total_hours, ws.total_worked_minutes/60) ELSE 0 END), 0) as total_horas_extras,
                        COALESCE(SUM(COALESCE(ws.total_hours, ws.total_worked_minutes/60)), 0) as total_horas
                    FROM work_sessions ws
                    WHERE ws.status IN ('completed', 'finalizada')";

            if ($fecha_inicio && $fecha_fin) {
                $sql .= " AND DATE(ws.session_date) BETWEEN :fecha_inicio AND :fecha_fin";
                $params2['fecha_inicio'] = $fecha_inicio;
                $params2['fecha_fin'] = $fecha_fin;
            }

            if ($id_grupo) {
                $sql .= " AND ws.id_group = :id_grupo";
                $params2['id_grupo'] = $id_grupo;
            }

            $result = $this->pdo->fetchOne($sql, $params2);

            return [
                'total_practicantes' => $total_practicantes,
                'total_horas_normales' => $result ? $result['total_horas_normales'] : 0,
                'total_horas_extras' => $result ? $result['total_horas_extras'] : 0,
                'total_horas' => $result ? $result['total_horas'] : 0
            ];
        } catch (PDOException $e) {
            return [
                'total_practicantes' => 0,
                'total_horas_normales' => 0,
                'total_horas_extras' => 0,
                'total_horas' => 0
            ];
        }
    }

    /**
     * Obtener estadísticas por practicante
     * Muestra TODOS los practicantes con sus horas trabajadas (incluso si son 0)
     */
    public function getPracticanteStatistics($fecha_inicio, $fecha_fin, $id_grupo)
    {
        try {
            $params = [];

            // Primero obtener los practicantes
            $sql_practicantes = "SELECT
                        s.user_id as id_user,
                        CONCAT(COALESCE(s.first_names, ''), ' ', COALESCE(s.last_names, '')) as practicante,
                        sg.group_name as grupo,
                        sg.id as group_id
                    FROM students s
                    INNER JOIN student_groups sg ON sg.group_code = s.`group`
                    WHERE s.user_id IS NOT NULL";

            if ($id_grupo) {
                $sql_practicantes .= " AND sg.id = :id_grupo";
                $params['id_grupo'] = $id_grupo;
            }

            $sql_practicantes .= " ORDER BY s.first_names ASC";

            $practicantes = $this->pdo->fetchAll($sql_practicantes, $params);

            if (!$practicantes) {
                return [];
            }

            // Ahora obtener las estadísticas de cada uno
            $result = [];
            foreach ($practicantes as $p) {
                $stats_params = ['id_user' => $p['id_user']];
                $sql_stats = "SELECT
                            COUNT(DISTINCT DATE(ws.session_date)) as dias_trabajados,
                            COALESCE(SUM(CASE WHEN ws.is_last_session_of_day = 0 OR ws.is_last_session_of_day IS NULL
                                THEN COALESCE(ws.total_hours, ws.total_worked_minutes/60) ELSE 0 END), 0) as horas_normales,
                            COALESCE(SUM(CASE WHEN ws.is_last_session_of_day = 1
                                THEN COALESCE(ws.total_hours, ws.total_worked_minutes/60) ELSE 0 END), 0) as horas_extras,
                            COALESCE(SUM(COALESCE(ws.total_hours, ws.total_worked_minutes/60)), 0) as total_horas,
                            SUM(CASE WHEN ws.has_evidence = 1 THEN 1 ELSE 0 END) as dias_con_evidencia
                        FROM work_sessions ws
                        WHERE ws.id_user = :id_user
                          AND ws.status IN ('completed', 'finalizada')";

                if ($fecha_inicio && $fecha_fin) {
                    $sql_stats .= " AND DATE(ws.session_date) BETWEEN :fecha_inicio AND :fecha_fin";
                    $stats_params['fecha_inicio'] = $fecha_inicio;
                    $stats_params['fecha_fin'] = $fecha_fin;
                }

                $stats = $this->pdo->fetchOne($sql_stats, $stats_params);

                $dias_trabajados = intval($stats['dias_trabajados'] ?? 0);
                $dias_con_evidencia = intval($stats['dias_con_evidencia'] ?? 0);

                $result[] = [
                    'id_user' => $p['id_user'],
                    'practicante' => trim($p['practicante']),
                    'grupo' => $p['grupo'],
                    'dias_trabajados' => $dias_trabajados,
                    'horas_normales' => floatval($stats['horas_normales'] ?? 0),
                    'horas_extras' => floatval($stats['horas_extras'] ?? 0),
                    'total_horas' => floatval($stats['total_horas'] ?? 0),
                    'dias_con_evidencia' => $dias_con_evidencia,
                    'dias_sin_evidencia' => $dias_trabajados - $dias_con_evidencia,
                    'porcentaje_evidencia' => $dias_trabajados > 0 ? round(($dias_con_evidencia / $dias_trabajados) * 100, 2) : 0
                ];
            }

            // Ordenar por total_horas DESC
            usort($result, function($a, $b) {
                return $b['total_horas'] <=> $a['total_horas'];
            });

            return $result;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtener sesiones activas (practicantes trabajando ahora)
     */
    public function getActiveSessions()
    {
        try {
            $sql = "SELECT
                        ws.id_user,
                        CONCAT(COALESCE(s.first_names, ''), ' ', COALESCE(s.last_names, '')) as practicante,
                        COALESCE(sg.group_name, 'Sin grupo') as grupo,
                        CASE WHEN ws.is_last_session_of_day = 1 THEN 'overtime' ELSE 'normal' END as tipo_sesion,
                        ws.start_time,
                        TIMESTAMPDIFF(MINUTE, ws.start_time, NOW()) - COALESCE(ws.total_pause_minutes, 0) as minutos_trabajados,
                        COALESCE(ws.daily_goal, ws.task_completed, '') as task_description
                    FROM work_sessions ws
                    INNER JOIN students s ON ws.id_user = s.user_id
                    LEFT JOIN student_groups sg ON ws.id_group = sg.id
                    WHERE ws.status = 'active'
                      AND ws.end_time IS NULL
                    ORDER BY minutos_trabajados DESC";

            $result = $this->pdo->fetchAll($sql);
            return $result ? $result : [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtener jornadas de un usuario
     */
    public function getSessionsByUser($id_user, $fecha_inicio, $fecha_fin)
    {
        try {
            $sql = "SELECT
                        DATE(session_date) as session_date,
                        COALESCE(total_hours, total_worked_minutes/60) as total_hours,
                        has_evidence,
                        is_last_session_of_day as is_overtime,
                        COALESCE(daily_goal, task_completed, '') as task_description
                    FROM work_sessions
                    WHERE id_user = :id_user
                      AND DATE(session_date) BETWEEN :fecha_inicio AND :fecha_fin
                      AND status IN ('completed', 'finalizada')
                    ORDER BY session_date DESC";

            $params = [
                'id_user' => $id_user,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin
            ];

            $result = $this->pdo->fetchAll($sql, $params);
            return $result ? $result : [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtener horas extras de un usuario (sesiones con is_last_session_of_day = 1)
     */
    public function getOvertimeByUser($id_user, $fecha_inicio, $fecha_fin)
    {
        try {
            $sql = "SELECT
                        DATE(session_date) as session_date,
                        COALESCE(total_hours, total_worked_minutes/60) as total_hours,
                        COALESCE(daily_goal, task_completed, '') as reason
                    FROM work_sessions
                    WHERE id_user = :id_user
                      AND DATE(session_date) BETWEEN :fecha_inicio AND :fecha_fin
                      AND status IN ('completed', 'finalizada')
                      AND is_last_session_of_day = 1
                    ORDER BY session_date DESC";

            $params = [
                'id_user' => $id_user,
                'fecha_inicio' => $fecha_inicio,
                'fecha_fin' => $fecha_fin
            ];

            $result = $this->pdo->fetchAll($sql, $params);
            return $result ? $result : [];
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * DEBUG: Verificar tablas y datos
     */
    public function debugTables()
    {
        try {
            // Test 1: Verificar grupos
            $grupos = $this->getGroups();

            // Test 2: Verificar practicantes
            $sql_students = "SELECT s.id, s.user_id, s.first_names, s.last_names, s.`group` as grupo_code,
                            sg.id as group_id, sg.group_name
                            FROM students s
                            LEFT JOIN student_groups sg ON sg.group_code = s.`group`
                            WHERE s.user_id IS NOT NULL
                            LIMIT 10";
            $students = $this->pdo->fetchAll($sql_students);

            // Test 3: Verificar work_sessions
            $sql_sessions = "SELECT id, id_user, id_group, status, session_date, total_hours, total_worked_minutes
                            FROM work_sessions
                            ORDER BY id DESC
                            LIMIT 10";
            $sessions = $this->pdo->fetchAll($sql_sessions);

            // Test 4: Contar registros
            $count_students = $this->pdo->fetchOne("SELECT COUNT(*) as total FROM students WHERE user_id IS NOT NULL");
            $count_groups = $this->pdo->fetchOne("SELECT COUNT(*) as total FROM student_groups");
            $count_sessions = $this->pdo->fetchOne("SELECT COUNT(*) as total FROM work_sessions");

            return [
                'success' => true,
                'grupos' => $grupos,
                'students_sample' => $students,
                'sessions_sample' => $sessions,
                'counts' => [
                    'students' => $count_students['total'] ?? 0,
                    'groups' => $count_groups['total'] ?? 0,
                    'sessions' => $count_sessions['total'] ?? 0
                ]
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
