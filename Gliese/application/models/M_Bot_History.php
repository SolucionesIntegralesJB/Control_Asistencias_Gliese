<?php
// --
class M_Bot_History extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // --
    public function get_history()
    {
        // --
        try {
            // --
            $sql = 'SELECT
                    id,
                    phone,
                    state,
                    name,
                    email,
                    email_verified,
                    group_assigned,
                    form_status
                FROM user_state
                ORDER BY id DESC';
            // --
            $result = $this->pdo->fetchAll($sql);
            // --
            if ($result) {
                // --
                $response = ['status' => 'OK', 'result' => $result];
            } else {
                // --
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            // --
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        // --
        return $response;
    }

    // --
    public function get_statistics()
    {
        // --
        try {
            // --
            $sql = 'SELECT
                    COUNT(*) AS total_usuarios,
                    SUM(CASE WHEN email_verified = 1 THEN 1 ELSE 0 END) AS email_verificados,
                    SUM(CASE WHEN form_completed IS NOT NULL THEN 1 ELSE 0 END) AS formularios_completados,
                    SUM(CASE WHEN group_assigned = 1 THEN 1 ELSE 0 END) AS grupos_enviados,
                    SUM(CASE WHEN form_status = "completado" THEN 1 ELSE 0 END) AS registros_completos,
                    SUM(CASE WHEN form_status = "en_progreso" THEN 1 ELSE 0 END) AS registros_en_progreso,
                    SUM(CASE WHEN form_status = "no_iniciado" THEN 1 ELSE 0 END) AS registros_no_iniciados
                FROM user_state';
            // --
            $result = $this->pdo->fetchOne($sql);
            // --
            if ($result) {
                // --
                $response = ['status' => 'OK', 'result' => $result];
            } else {
                // --
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            // --
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        // --
        return $response;
    }

    // --
    public function get_user_detail($bind)
    {
        // --
        try {
            // --
            $sql = 'SELECT
                    us.*,
                    DATE_FORMAT(NOW(), "%d/%m/%Y %H:%i:%s") AS fecha_creacion_formatted,
                    DATE_FORMAT(NOW(), "%d/%m/%Y %H:%i:%s") AS fecha_actualizacion_formatted,
                    DATE_FORMAT(us.code_expiration, "%d/%m/%Y %H:%i:%s") AS code_expiration_formatted,
                    DATE_FORMAT(us.form_submitted, "%d/%m/%Y %H:%i:%s") AS form_submitted_formatted,
                    DATE_FORMAT(us.form_completed, "%d/%m/%Y %H:%i:%s") AS form_completed_formatted
                FROM user_state us
                WHERE us.phone = :phone';
            // --
            $result = $this->pdo->fetchOne($sql, $bind);
            // --
            if ($result) {
                // -- Si tienes tabla de logs, úsala. Si no, retorna array vacío
                $logs = []; // Cambiar esto si tienes tabla de logs
                $result['logs'] = $logs;

                $response = ['status' => 'OK', 'result' => $result];
            } else {
                // --
                $response = ['status' => 'ERROR', 'result' => []];
            }
        } catch (PDOException $e) {
            // --
            $response = ['status' => 'EXCEPTION', 'result' => $e];
        }
        // --
        return $response;
    }
}
