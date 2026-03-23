<?php
/**
 * ========================================================================
 * MODELO: M_Bot_Event
 * ========================================================================
 * 
 * Propósito: Gestión de eventos automatizados del sistema Bot
 * 
 * Tablas que maneja:
 * - bot_event_settings: Configuración de eventos (intervalo, retención, etc.)
 * - bot_event_logs: Historial de ejecuciones y resultados
 * 
 * Funcionalidades:
 * - CRUD de configuraciones de eventos
 * - Ejecución manual de eventos
 * - Registro de logs de ejecución
 * - Importación de eventos desde MySQL information_schema.EVENTS
 * 
 * Políticas de seguridad:
 * - Solo permite actualizar el evento con ID=2 (evento administrativo fijo)
 * - Valida que interval_seconds y retention_minutes sean > 0
 * - Usa defaults si los valores son inválidos (300s, 43200min, 1000 batch)
 * 
 * ========================================================================
 */

class M_Bot_Event extends Model {

    public function __construct() {
        parent::__construct();
    }

    // ========================================================================
    // MÉTODOS DE LECTURA
    // ========================================================================

    /**
     * Obtiene todos los eventos configurados
     * @return array Lista de eventos ordenados por ID
     */
    public function get_settings() {
        try {
            $rows = $this->pdo->fetchAll("SELECT * FROM bot_event_settings ORDER BY id ASC");
            return $rows ?: array();
        } catch (Exception $e) { 
            return array(); 
        }
    }

    /**
     * Obtiene un evento específico por ID
     * @param int $id - ID del evento a buscar
     * @return array|null Datos del evento o null si no existe
     */
    public function get_setting($id) {
        try {
            $r = $this->pdo->fetchOne(
                "SELECT * FROM bot_event_settings WHERE id = :id LIMIT 1", 
                array('id'=>$id)
            );
            return $r ?: null;
        } catch (Exception $e) { 
            return null; 
        }
    }

    // ========================================================================
    // MÉTODO DE GUARDADO (UPDATE ONLY)
    // ========================================================================

    /**
     * Guarda la configuración de un evento
     * 
     * IMPORTANTE: Por seguridad, siempre actualiza el evento ID=2 (fijo)
     * No permite crear nuevos eventos ni modificar otros IDs
     * 
     * Validaciones:
     * - interval_seconds debe ser > 0 (default: 300 = 5 minutos)
     * - retention_minutes debe ser > 0 (default: 43200 = 30 días)
     * - batch_size debe ser > 0 (default: 1000)
     * 
     * @param array $data - Datos del evento (id, name, enabled, interval_seconds, retention_minutes, batch_size, config, updated_by)
     * @return array Array con 'status' => 'OK'|'ERROR' y 'msg' en caso de error
     */
    public function save_setting($data) {
        try {
            // ============================================================
            // POLÍTICA DE SEGURIDAD: Solo actualizar evento ID=2
            // ============================================================
            // Normalizar inputs
            $id = (isset($data['id']) && $data['id'] !== '') ? $data['id'] : null;
            $name = (isset($data['name']) && trim($data['name']) !== '') ? trim($data['name']) : null;

            // Forzar actualización del evento fijo ID=2 (política de seguridad)
            // Esto previene creación accidental o modificación no autorizada de otros eventos
            $targetId = 2;

            // Verificar que el evento objetivo existe
            try {
                $check = $this->pdo->fetchOne(
                    "SELECT * FROM bot_event_settings WHERE id = :id LIMIT 1", 
                    array('id'=>$targetId)
                );
                if (!($check && isset($check['id']))) {
                    return array('status'=>'ERROR','msg'=>'Fixed setting row not found');
                }
            } catch (Exception $e) {
                return array('status'=>'ERROR','msg'=>'Database check failed');
            }

            // ============================================================
            // MERGE CON VALORES EXISTENTES
            // ============================================================
            // Obtener valores actuales de la BD para no sobrescribir con nulls
            $existing = $this->get_setting($targetId);
            
            // Determinar nombre final (para ID=2 siempre es 'clean_old_messages')
            $finalName = ($targetId == 2) 
                ? ($existing['name'] ?? 'clean_old_messages') 
                : ($name ?: ($existing['name'] ?? null));
            
            // Enabled: Usar enviado si es válido, si no usar existente
            $enabled = (isset($data['enabled']) && $data['enabled'] !== null && $data['enabled'] !== '' && is_numeric($data['enabled'])) 
                ? intval($data['enabled']) 
                : intval($existing['enabled']);
            
            // ============================================================
            // INTERVAL_SECONDS: Validación estricta > 0
            // ============================================================
            // Si el valor enviado es válido (> 0), usarlo
            // Si no, usar el existente si es válido (> 0)
            // Si el existente también es inválido, usar default 300 (5 minutos)
            $interval_seconds = (isset($data['interval_seconds']) && $data['interval_seconds'] !== null && $data['interval_seconds'] !== '' && is_numeric($data['interval_seconds']) && intval($data['interval_seconds']) > 0) 
                ? intval($data['interval_seconds']) 
                : (intval($existing['interval_seconds']) > 0 ? intval($existing['interval_seconds']) : 300);
            
            // ============================================================
            // DEBUG LOG: Rastrear qué se recibe y qué se guarda
            // ============================================================
            @file_put_contents(__DIR__ . '/../logs/events_save_debug.log', 
                date('Y-m-d H:i:s') . " - MODELO recibió interval_seconds: " . ($data['interval_seconds'] ?? 'NULL') . 
                " | Valor a guardar: " . $interval_seconds . " | Existente era: " . $existing['interval_seconds'] . "\n", 
                FILE_APPEND);
            
            // ============================================================
            // RETENTION_MINUTES: Validación estricta > 0
            // ============================================================
            // Default: 43200 minutos = 30 días
            $retention_minutes = (isset($data['retention_minutes']) && $data['retention_minutes'] !== null && $data['retention_minutes'] !== '' && is_numeric($data['retention_minutes']) && intval($data['retention_minutes']) > 0) 
                ? intval($data['retention_minutes']) 
                : (intval($existing['retention_minutes']) > 0 ? intval($existing['retention_minutes']) : 43200);
            
            // ============================================================
            // BATCH_SIZE: Validación estricta > 0
            // ============================================================
            // Default: 1000 registros por lote
            $batch_size = (isset($data['batch_size']) && $data['batch_size'] !== null && $data['batch_size'] !== '' && is_numeric($data['batch_size']) && intval($data['batch_size']) > 0) 
                ? intval($data['batch_size']) 
                : (intval($existing['batch_size']) > 0 ? intval($existing['batch_size']) : 1000);
            
            // Config: JSON de configuración adicional
            $config = (isset($data['config']) && $data['config'] !== null) 
                ? json_encode($data['config']) 
                : $existing['config'];

            // ============================================================
            // EJECUTAR UPDATE
            // ============================================================
            $sql = "UPDATE bot_event_settings 
                    SET name = :name, 
                        enabled = :enabled, 
                        interval_seconds = :interval_seconds, 
                        retention_minutes = :retention_minutes, 
                        batch_size = :batch_size, 
                        config = :config, 
                        updated_by = :updated_by 
                    WHERE id = :id";
            
            $params = array(
                'name' => $finalName,
                'enabled' => intval($enabled),
                'interval_seconds' => intval($interval_seconds),
                'retention_minutes' => intval($retention_minutes),
                'batch_size' => intval($batch_size),
                'config' => $config,
                'updated_by' => isset($data['updated_by']) ? $data['updated_by'] : null,
                'id' => $targetId
            );
            
            // Ejecutar actualización (NO permite INSERTs en este método)
            $this->pdo->perform($sql, $params);
            
            return array('status'=>'OK','id'=>$targetId);
            
        } catch (Exception $e) {
            return array('status'=>'ERROR','msg'=>$e->getMessage());
        }
    }

    // ========================================================================
    // LOGS DE EVENTOS
    // ========================================================================

    /**
     * Obtiene el historial de ejecuciones de un evento
     * @param int $setting_id - ID del evento
     * @return array Lista de logs (máximo 200 registros más recientes)
     */
    public function get_logs($setting_id) {
        try {
            $rows = $this->pdo->fetchAll(
                "SELECT * FROM bot_event_logs 
                 WHERE event_setting_id = :id 
                 ORDER BY created_at DESC 
                 LIMIT 200", 
                array('id'=>$setting_id)
            );
            return $rows ?: array();
        } catch (Exception $e) { 
            return array(); 
        }
    }

    // ========================================================================
    // EJECUCIÓN MANUAL DE EVENTOS
    // ========================================================================

    /**
     * Ejecuta manualmente la tarea asociada a un evento
     * 
     * Lógica de ejecución:
     * 1. Obtiene configuración del evento (retención, batch_size)
     * 2. Calcula fecha de corte (ahora - retention_minutes)
     * 3. Elimina mensajes antiguos en lotes (DELETE con LIMIT)
     * 4. Registra resultado en bot_event_logs
     * 5. Actualiza last_run_at en bot_event_settings
     * 
     * @param int $id - ID del evento a ejecutar
     * @return array Array con 'status' => 'OK'|'ERROR', 'rows' => cantidad eliminada
     */
    public function run_setting($id) {
        try {
            // Obtener configuración del evento
            $s = $this->get_setting($id);
            if (!$s) return array('status'=>'ERROR','msg'=>'Setting no encontrado');

            // Extraer parámetros
            $retentionMinutes = intval($s['retention_minutes']);
            $batchSize = intval($s['batch_size']);
            $table = 'bot_messages';
            $datetimeCol = 'created_at';
            
            // CRÍTICO: Usar MySQL NOW() en lugar de PHP date() para evitar problemas de zona horaria
            // MySQL y PHP pueden estar en zonas horarias diferentes (MySQL en UTC, PHP en local)
            // Solución: Hacer toda la comparación en el lado de MySQL
            
            // Log para debug
            error_log("Bot Event - Retención configurada: {$retentionMinutes} minutos");

            // ============================================================
            // TRANSACCIÓN: Eliminar mensajes antiguos
            // ============================================================
            $this->pdo->perform("START TRANSACTION", array());
            
            // Primero contar cuántos mensajes cumplen el criterio (para debug)
            try {
                $count_result = $this->pdo->fetchOne(
                    "SELECT COUNT(*) as total FROM {$table} 
                     WHERE {$datetimeCol} < DATE_SUB(NOW(), INTERVAL :minutes MINUTE)",
                    array('minutes'=>$retentionMinutes)
                );
                $total_old = isset($count_result['total']) ? intval($count_result['total']) : 0;
                error_log("Bot Event - Mensajes que cumplen criterio (más de {$retentionMinutes} min antiguos): {$total_old}");
            } catch (Exception $e) {
                $total_old = 0;
                error_log("Bot Event - Error contando: " . $e->getMessage());
            }
            
            // Usar fetchAffected() que es el método correcto de Aura SQL
            // para obtener el número de filas afectadas por DELETE
            try {
                // CRÍTICO: Usar DATE_SUB(NOW(), INTERVAL X MINUTE) directamente en MySQL
                // Esto evita problemas de zona horaria entre PHP y MySQL
                $rows = $this->pdo->fetchAffected(
                    "DELETE FROM {$table} 
                     WHERE {$datetimeCol} < DATE_SUB(NOW(), INTERVAL :minutes MINUTE) 
                     LIMIT " . intval($batchSize),
                    array('minutes'=>$retentionMinutes)
                );
            } catch (Exception $e) {
                error_log("Bot Event - Error en DELETE: " . $e->getMessage());
                throw $e;
            }
            
            error_log("Bot Event - Mensajes eliminados: {$rows}");
            
            $this->pdo->perform("COMMIT", array());

            // ============================================================
            // REGISTRAR LOG DE EJECUCIÓN EXITOSA
            // ============================================================
            $this->pdo->perform(
                "INSERT INTO bot_event_logs 
                 (event_setting_id, started_at, finished_at, rows_affected, status, message) 
                 VALUES (:id, :started, :finished, :rows, :status, :msg)", 
                array(
                    'id'=>$id,
                    'started'=>date('Y-m-d H:i:s'),
                    'finished'=>date('Y-m-d H:i:s'),
                    'rows'=>intval($rows),
                    'status'=>'ok',
                    'msg'=>null
                )
            );
            
            // Actualizar última ejecución
            $this->pdo->perform(
                "UPDATE bot_event_settings SET last_run_at = :last WHERE id = :id", 
                array('last'=>date('Y-m-d H:i:s'),'id'=>$id)
            );
            
            return array('status'=>'OK','rows'=>intval($rows));
            
        } catch (Exception $e) {
            // Rollback en caso de error
            try { 
                $this->pdo->perform("ROLLBACK", array()); 
            } catch (Exception $x) {}
            
            // Registrar log de error
            $this->pdo->perform(
                "INSERT INTO bot_event_logs 
                 (event_setting_id, started_at, finished_at, rows_affected, status, message) 
                 VALUES (:id, :started, :finished, 0, :status, :msg)", 
                array(
                    'id'=>$id,
                    'started'=>date('Y-m-d H:i:s'),
                    'finished'=>date('Y-m-d H:i:s'),
                    'status'=>'error',
                    'msg'=>$e->getMessage()
                )
            );
            
            return array('status'=>'ERROR','msg'=>$e->getMessage());
        }
    }

    // ========================================================================
    // IMPORTACIÓN DESDE MYSQL information_schema.EVENTS
    // ========================================================================

    /**
     * Importa eventos programados desde el scheduler de MySQL
     * 
     * Proceso:
     * 1. Lee eventos de information_schema.EVENTS
     * 2. Convierte INTERVAL_VALUE + INTERVAL_FIELD a segundos
     * 3. Crea entradas en bot_event_settings si no existen
     * 4. Actualiza config con 'source_event' para rastreo
     * 
     * Mapeo de intervalos:
     * - SECOND: 1 segundo
     * - MINUTE: 60 segundos
     * - HOUR: 3600 segundos
     * - DAY: 86400 segundos
     * - WEEK: 604800 segundos
     * - MONTH: 2592000 segundos (30 días)
     * - YEAR: 31536000 segundos (365 días)
     * 
     * @return array Array con 'status' => 'OK'|'ERROR', 'imported' => cantidad creada, 'found' => cantidad total
     */
    public function import_mysql_events() {
        try {
            // Obtener eventos de MySQL
            $rows = $this->pdo->fetchAll(
                "SELECT EVENT_NAME, EVENT_DEFINITION, EVENT_TYPE, INTERVAL_VALUE, INTERVAL_FIELD, STATUS 
                 FROM information_schema.EVENTS 
                 WHERE EVENT_SCHEMA = DATABASE()"
            );
            
            if (!$rows) return array('status'=>'OK','imported'=>0,'found'=>0);
            
            $imported = 0; 
            $found = 0;
            
            foreach ($rows as $r) {
                $found++;
                $ename = $r['EVENT_NAME'];
                
                // ============================================================
                // VERIFICAR SI YA EXISTE
                // ============================================================
                $exists = $this->pdo->fetchOne(
                    "SELECT id, config FROM bot_event_settings WHERE name = :name LIMIT 1", 
                    array('name'=>$ename)
                );
                
                if ($exists) {
                    // Ya existe: actualizar config para asegurar que tenga source_event
                    $cfg = isset($exists['config']) ? $exists['config'] : null;
                    $j = array();
                    if ($cfg) $j = json_decode($cfg, true) ?: array();
                    
                    if (!isset($j['source_event']) || $j['source_event'] !== $ename) {
                        $j['source_event'] = $ename;
                        $this->pdo->perform(
                            "UPDATE bot_event_settings SET config = :config WHERE id = :id", 
                            array('config'=>json_encode($j),'id'=>$exists['id'])
                        );
                    }
                    continue;
                }

                // ============================================================
                // CONVERTIR INTERVALO A SEGUNDOS
                // ============================================================
                $ival = intval($r['INTERVAL_VALUE']);
                $ifield = strtoupper(trim($r['INTERVAL_FIELD'] ?? ''));
                $mult = 60; // default: minuto
                
                switch ($ifield) {
                    case 'SECOND': $mult = 1; break;
                    case 'MINUTE': $mult = 60; break;
                    case 'HOUR': $mult = 3600; break;
                    case 'DAY': $mult = 86400; break;
                    case 'WEEK': $mult = 604800; break;
                    case 'MONTH': $mult = 2592000; break;
                    case 'YEAR': $mult = 31536000; break;
                    default: $mult = 60; break;
                }
                
                $interval_seconds = ($ival > 0) ? ($ival * $mult) : 300;

                // ============================================================
                // DEFAULTS PARA NUEVOS EVENTOS
                // ============================================================
                $retention = 43200; // 30 días en minutos
                $batch = 1000;

                // Guardar definición original en config
                $config = json_encode(array(
                    'source_event'=>$ename,
                    'event_definition'=>$r['EVENT_DEFINITION']
                ));
                
                // ============================================================
                // CREAR NUEVO EVENTO
                // ============================================================
                $this->pdo->perform(
                    "INSERT INTO bot_event_settings 
                     (name, enabled, interval_seconds, retention_minutes, batch_size, config) 
                     VALUES (:name, 1, :interval, :retention, :batch, :config)", 
                    array(
                        'name'=>$ename,
                        'interval'=>$interval_seconds,
                        'retention'=>$retention,
                        'batch'=>$batch,
                        'config'=>$config
                    )
                );
                
                $imported++;
            }
            
            return array('status'=>'OK','imported'=>$imported,'found'=>$found);
            
        } catch (Exception $e) {
            return array('status'=>'ERROR','msg'=>$e->getMessage());
        }
    }

}
