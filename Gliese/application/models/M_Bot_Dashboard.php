<?php
// --
class M_Bot_Dashboard extends Model
{
    // --
    public function __construct()
    {
        parent::__construct();
    }

    // ==================================================
    // SECURITY RATE LIMITS
    // ==================================================

    /**
     * Get rate limits with filters
     */
    public function get_rate_limits($limit = 100, $offset = 0, $identifier = null, $action_type = null, $status = null, $severity = null)
    {
        try {
            $sql = 'SELECT id, identifier_value, action_type as action_types,
                        first_attempt, last_attempt, blocked_until, alerts_sent_count, severity,
                        pattern_detected, metadata, is_permanent, block_count
                    FROM security_rate_limits WHERE action_type != "http_rate_limit"';
            $params = [];

            if ($identifier) {
                $sql .= ' AND identifier_value LIKE :identifier';
                $params['identifier'] = '%' . $identifier . '%';
            }

            if ($action_type) {
                $sql .= ' AND action_type = :action_type';
                $params['action_type'] = $action_type;
            }

            if ($status === 'blocked') {
                $sql .= ' AND is_permanent = 0 AND blocked_until IS NOT NULL AND blocked_until > NOW()';
            } elseif ($status === 'permanent') {
                $sql .= ' AND is_permanent = 1';
            } elseif ($status === 'unblocked') {
                $sql .= ' AND is_permanent = 0 AND (blocked_until IS NULL OR blocked_until <= NOW())';
            }

            if ($severity) {
                $sql .= ' AND severity = :severity';
                $params['severity'] = $severity;
            }

            $sql .= ' ORDER BY last_attempt DESC LIMIT :limit OFFSET :offset';
            $params['limit'] = $limit;
            $params['offset'] = $offset;

            $result = $this->pdo->fetchAll($sql, $params);

            // Get total count (excluding http_rate_limit)
            $count_sql = 'SELECT COUNT(*) as total FROM security_rate_limits WHERE action_type != "http_rate_limit"';
            if ($identifier) $count_sql .= ' AND identifier_value LIKE :identifier';
            if ($action_type) $count_sql .= ' AND action_type = :action_type';
            if ($status === 'blocked') $count_sql .= ' AND is_permanent = 0 AND blocked_until IS NOT NULL AND blocked_until > NOW()';
            elseif ($status === 'permanent') $count_sql .= ' AND is_permanent = 1';
            elseif ($status === 'unblocked') $count_sql .= ' AND is_permanent = 0 AND (blocked_until IS NULL OR blocked_until <= NOW())';
            if ($severity) $count_sql .= ' AND severity = :severity';

            $count_params = $params;
            unset($count_params['limit']);
            unset($count_params['offset']);

            $total_result = $this->pdo->fetchOne($count_sql, $count_params);

            // Get escalation threshold from config
            $config_sql = 'SELECT value FROM security_config WHERE `key` = :key';
            $config_result = $this->pdo->fetchOne($config_sql, ['key' => 'rate_limit_block_after_violations']);
            $max_blocks = $config_result ? (int) $config_result['value'] : 5;

            return [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Rate limits obtenidos correctamente',
                'data' => $result,
                'total' => $total_result['total'] ?? 0,
                'max_blocks' => $max_blocks,
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get rate limit statistics
     */
    public function get_rate_limit_stats()
    {
        try {
            // Total rate limits
            $total_sql = 'SELECT COUNT(*) as total FROM security_rate_limits WHERE action_type != "http_rate_limit"';
            $total_result = $this->pdo->fetchOne($total_sql, []);

            // Currently blocked (temporary)
            $blocked_sql = 'SELECT COUNT(*) as count FROM security_rate_limits WHERE action_type != "http_rate_limit" AND is_permanent = 0 AND blocked_until IS NOT NULL AND blocked_until > NOW()';
            $blocked_result = $this->pdo->fetchOne($blocked_sql, []);

            // Unblocked
            $unblocked_sql = 'SELECT COUNT(*) as count FROM security_rate_limits WHERE action_type != "http_rate_limit" AND is_permanent = 0 AND (blocked_until IS NULL OR blocked_until <= NOW())';
            $unblocked_result = $this->pdo->fetchOne($unblocked_sql, []);

            // Permanent
            $permanent_sql = 'SELECT COUNT(*) as count FROM security_rate_limits WHERE action_type != "http_rate_limit" AND is_permanent = 1';
            $permanent_result = $this->pdo->fetchOne($permanent_sql, []);

            return [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Estadisticas obtenidas correctamente',
                'data' => [
                    'total' => $total_result['total'] ?? 0,
                    'currently_blocked' => $blocked_result['count'] ?? 0,
                    'unblocked' => $unblocked_result['count'] ?? 0,
                    'permanent' => $permanent_result['count'] ?? 0,
                ],
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Clear rate limit for a specific record
     */
    public function clear_rate_limit($id)
    {
        try {
            $sql = 'UPDATE security_rate_limits SET blocked_until = NULL, is_permanent = 0, block_count = 0 WHERE id = :id';
            $result = $this->pdo->perform($sql, ['id' => $id]);

            return [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Rate limit desbloqueado correctamente',
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }
    }



    // ==================================================
    // SECURITY BLOCKED PHONES
    // ==================================================

    /**
     * Get blocked phones statistics
     */
    public function get_blocked_phones_stats()
    {
        try {
            // Total
            $total_sql = 'SELECT COUNT(*) as total FROM security_blocked_phones';
            $total_result = $this->pdo->fetchOne($total_sql, []);

            // Active (blocked: permanent or temporary not expired)
            $active_sql = 'SELECT COUNT(*) as count FROM security_blocked_phones WHERE permanent = 1 OR (expires_at IS NOT NULL AND expires_at > NOW())';
            $active_result = $this->pdo->fetchOne($active_sql, []);

            // Inactive (unblocked: not permanent and expired or no expiry)
            $inactive_sql = 'SELECT COUNT(*) as count FROM security_blocked_phones WHERE permanent = 0 AND (expires_at IS NULL OR expires_at <= NOW())';
            $inactive_result = $this->pdo->fetchOne($inactive_sql, []);

            // Permanent
            $permanent_sql = 'SELECT COUNT(*) as count FROM security_blocked_phones WHERE permanent = 1';
            $permanent_result = $this->pdo->fetchOne($permanent_sql, []);

            return [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Estadísticas obtenidas correctamente',
                'result' => [
                    'total' => (int) ($total_result['total'] ?? 0),
                    'active' => (int) ($active_result['count'] ?? 0),
                    'inactive' => (int) ($inactive_result['count'] ?? 0),
                    'permanent' => (int) ($permanent_result['count'] ?? 0),
                ],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get blocked phones with filters
     */
    public function get_blocked_phones($limit = 100, $offset = 0, $status = null, $block_type = null)
    {
        try {
            $sql = 'SELECT *,
                    CASE
                        WHEN permanent = 1 OR (expires_at IS NOT NULL AND expires_at > NOW()) THEN 1
                        ELSE 0
                    END AS is_active
                FROM security_blocked_phones WHERE 1=1';
            $params = [];

            if ($status === 'blocked') {
                $sql .= ' AND (permanent = 1 OR (expires_at IS NOT NULL AND expires_at > NOW()))';
            } elseif ($status === 'unblocked') {
                $sql .= ' AND permanent = 0 AND (expires_at IS NULL OR expires_at <= NOW())';
            } elseif ($status === 'permanent') {
                $sql .= ' AND permanent = 1';
            }

            if ($block_type) {
                $sql .= ' AND block_type = :block_type';
                $params['block_type'] = $block_type;
            }

            $sql .= ' ORDER BY blocked_at DESC LIMIT :limit OFFSET :offset';
            $params['limit'] = $limit;
            $params['offset'] = $offset;

            $result = $this->pdo->fetchAll($sql, $params);

            // Get total count
            $count_sql = 'SELECT COUNT(*) as total FROM security_blocked_phones WHERE 1=1';
            if ($status === 'blocked') $count_sql .= ' AND (permanent = 1 OR (expires_at IS NOT NULL AND expires_at > NOW()))';
            elseif ($status === 'unblocked') $count_sql .= ' AND permanent = 0 AND (expires_at IS NULL OR expires_at <= NOW())';
            elseif ($status === 'permanent') $count_sql .= ' AND permanent = 1';
            if ($block_type) $count_sql .= ' AND block_type = :block_type';

            $count_params = $params;
            unset($count_params['limit']);
            unset($count_params['offset']);

            $total_result = $this->pdo->fetchOne($count_sql, $count_params);

            return [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Teléfonos bloqueados obtenidos correctamente',
                'result' => $result,
                'total' => $total_result['total'] ?? 0,
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Unblock a phone number
     */
    public function unblock_phone($phone, $block_type = 'message')
    {
        try {
            $sql = 'UPDATE security_blocked_phones SET
                    expires_at = NOW(),
                    permanent = 0,
                    unblock_attempts = unblock_attempts + 1,
                    block_count = 0,
                    failed_attempts = 0
                WHERE phone = :phone AND block_type = :block_type';
            $result = $this->pdo->perform($sql, [
                'phone' => $phone,
                'block_type' => $block_type
            ]);

            return [
                'status' => 'OK',
                'type' => 'success',
                'msg' => 'Teléfono desbloqueado correctamente',
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'EXCEPTION',
                'type' => 'error',
                'msg' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
}
