<?php
// ============================================================================
// MODELO: M_Bot_Chat
// ============================================================================
/**
 * Modelo para gestionar conversaciones y mensajes del Bot de WhatsApp
 * 
 * CARACTERÍSTICAS PRINCIPALES:
 * - Manejo de múltiples estructuras de tablas (compatibilidad con diferentes esquemas)
 * - Sistema de fallback para detectar automáticamente qué tablas existen
 * - Soporte para bot_chats, bot_conversations, messenger, bot_messages
 * - Detección inteligente de columnas y estructuras
 * - Normalización de datos de diferentes fuentes
 * - Gestión de triggers de base de datos para evitar duplicación de lógica
 * 
 * TABLAS SOPORTADAS:
 * 1. v_bot_chat_dashboard (vista): Dashboard consolidado de conversaciones
 * 2. bot_chats: Tabla legacy de chats
 * 3. messenger: Sistema alternativo de mensajería
 * 4. bot_conversations: Tabla principal de conversaciones
 * 5. bot_messages: Tabla de mensajes individuales
 * 6. messenger_messages: Mensajes del sistema messenger
 * 
 * FLUJO DE OPERACIÓN:
 * - Detecta qué tablas existen en la BD
 * - Intenta usar la mejor opción disponible (vista > tabla específica > fallback)
 * - Normaliza datos para que el frontend reciba siempre el mismo formato
 * - Respeta triggers existentes para evitar duplicar actualizaciones
 * 
 * CAMPOS NORMALIZADOS PARA FRONTEND:
 * - id: Identificador único de conversación
 * - contact_name: Nombre del contacto
 * - contact_phone: Teléfono del contacto
 * - last_message: Último mensaje de la conversación
 * - updated_at: Fecha de última actividad (formato: dd/mm/YYYY HH:ii)
 * - metadata: Información adicional en JSON
 * 
 * @author Sistema Bot WhatsApp
 * @version 2.0
 */
class M_Bot_Chat extends Model {

    public function __construct() {
        parent::__construct();
    }

    // ========================================================================
    // HELPERS: DETECCIÓN DE ESTRUCTURA DE BASE DE DATOS
    // ========================================================================

    /**
     * Verifica si una tabla existe en la base de datos actual
     * 
     * Consulta el catálogo information_schema para determinar
     * si una tabla específica existe en el esquema actual.
     * 
     * Uso típico:
     * - Antes de ejecutar consultas, verificar que la tabla exista
     * - Implementar lógica de fallback entre diferentes estructuras
     * - Evitar errores SQL por tablas inexistentes
     * 
     * @param string $table Nombre de la tabla a verificar
     * @return bool TRUE si la tabla existe, FALSE en caso contrario
     */
    private function table_exists($table) {
        try {
            $sql = "SELECT COUNT(*) as c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t";
            $r = $this->pdo->fetchOne($sql, array('t' => $table));
            return ($r && isset($r['c']) && intval($r['c']) > 0);
        } catch (Exception $e) { return false; }
    }

    /**
     * Verifica si un trigger existe en la base de datos actual
     * 
     * Consulta el catálogo information_schema.triggers para determinar
     * si un trigger específico está definido en el esquema actual.
     * 
     * Importancia de detección de triggers:
     * - Evita DUPLICAR lógica que ya está en triggers SQL
     * - Si existe trigger 'after_bot_message_insert', NO actualizar desde PHP
     * - Mejora performance al delegar trabajo a la BD
     * - Previene inconsistencias y race conditions
     * 
     * Triggers comunes en el sistema:
     * - after_bot_message_insert: Actualiza bot_conversations al insertar mensaje
     * - trg_bot_messages_after_insert: Trigger alternativo con misma función
     * 
     * @param string $name Nombre del trigger a verificar
     * @return bool TRUE si el trigger existe, FALSE en caso contrario
     */
    private function trigger_exists($name) {
        try {
            $sql = "SELECT COUNT(*) as c FROM information_schema.triggers WHERE trigger_schema = DATABASE() AND trigger_name = :n";
            $r = $this->pdo->fetchOne($sql, array('n' => $name));
            return ($r && isset($r['c']) && intval($r['c']) > 0);
        } catch (Exception $e) { return false; }
    }

    // ========================================================================
    // SECCIÓN 1: GESTIÓN DE CONVERSACIONES
    // ========================================================================

    /**
     * Obtiene lista completa de conversaciones del bot
     * 
     * ESTRATEGIA DE FALLBACK EN CASCADA:
     * Intenta obtener conversaciones en este orden de prioridad:
     * 
     * 1. v_bot_chat_dashboard (VISTA): 
     *    - Vista consolidada optimizada para dashboard
     *    - Mejor rendimiento, datos pre-procesados
     * 
     * 2. bot_chats (TABLA LEGACY):
     *    - Tabla antigua, puede no tener todas las columnas
     *    - Detecta dinámicamente qué campos existen
     * 
     * 3. messenger (TABLA ALTERNATIVA):
     *    - Sistema de mensajería alternativo
     *    - Normaliza nombres de campos diferentes
     * 
     * 4. bot_conversations (TABLA PRINCIPAL):
     *    - Tabla moderna de conversaciones
     *    - Extrae metadata JSON para completar información
     *    - Intenta decodificar metadata (JSON normal o gzip+JSON)
     * 
     * 5. bot_messages (FALLBACK FINAL):
     *    - Agrupa mensajes por conversación
     *    - Usa GROUP_CONCAT para último mensaje
     *    - Genera conversation_id sintético si no existe
     * 
     * NORMALIZACIÓN DE DATOS:
     * Todos los fallbacks retornan el mismo formato:
     * - id: Identificador único de conversación
     * - contact_name: Nombre del contacto
     * - contact_phone: Teléfono del contacto
     * - last_message: Último mensaje de la conversación
     * - updated_at: Fecha formateada (dd/mm/YYYY HH:ii)
     * - metadata: Información adicional (opcional)
     * 
     * EXTRACCIÓN INTELIGENTE DE METADATA:
     * Si contact_name o contact_phone están vacíos, intenta extraerlos de metadata:
     * - metadata.phone, metadata.contact.phone, metadata.history[0].from
     * - metadata.contact.name, metadata.contact_name, metadata.history[0].contact.name
     * - Soporta metadata como JSON string o array PHP
     * - Soporta metadata comprimida con gzip
     * 
     * LÍMITE DE RESULTADOS:
     * - Máximo 500 conversaciones por consulta
     * - Ordenadas por última actividad (más recientes primero)
     * 
     * @return array Array con 'status', 'msg' (opcional) y 'result' (array de conversaciones)
     *               - status: 'OK', 'ERROR' o 'EXCEPTION'
     *               - result: Array de conversaciones normalizadas
     */
    public function get_chats() {
        try {
            // Cargar conversaciones desde bot_conversations
            if ($this->table_exists('bot_conversations')) {
                try {
                    $sql = "SELECT
                            conversation_id AS id,
                            phone AS contact_phone,
                            metadata,
                            DATE_FORMAT(last_activity, '%d/%m/%Y %H:%i') AS updated_at,
                            (SELECT COUNT(*) FROM bot_messages m WHERE m.conversation_id = bot_conversations.conversation_id AND m.direction = 'incoming' AND m.read_at IS NULL) AS unread_count,
                            (SELECT COUNT(*) FROM bot_messages m WHERE m.conversation_id = bot_conversations.conversation_id) AS message_count,
                            (SELECT message FROM bot_messages m WHERE m.conversation_id = bot_conversations.conversation_id ORDER BY m.id DESC LIMIT 1) AS last_message
                            FROM bot_conversations
                            WHERE (SELECT COUNT(*) FROM bot_messages m WHERE m.conversation_id = bot_conversations.conversation_id) > 0
                            ORDER BY last_activity DESC
                            LIMIT 500";
                    $rows = $this->pdo->fetchAll($sql);
                    if ($rows) {
                        foreach ($rows as &$r) {
                            if (!isset($r['contact_name'])) $r['contact_name'] = isset($r['contactName']) ? $r['contactName'] : null;
                            if (!isset($r['contact_phone'])) $r['contact_phone'] = isset($r['phone']) ? $r['phone'] : null;
                            $need_phone = empty($r['contact_phone']) || $r['contact_phone'] === '';
                            $need_name = empty($r['contact_name']) || $r['contact_name'] === '';
                            if (($need_phone || $need_name) && isset($r['metadata']) && $r['metadata']) {
                                $m = null;
                                if (is_string($r['metadata'])) {
                                    $m = json_decode($r['metadata'], true);
                                    if ($m === null && function_exists('gzdecode')) {
                                        try { $dec = @gzdecode($r['metadata']); if ($dec) $m = json_decode($dec, true); } catch (Exception $e) { }
                                    }
                                } elseif (is_array($r['metadata'])) {
                                    $m = $r['metadata'];
                                }
                                if (is_array($m)) {
                                    if ($need_phone) {
                                        if (isset($m['phone']) && $m['phone']) $r['contact_phone'] = $m['phone'];
                                        else if (isset($m['contact']['phone']) && $m['contact']['phone']) $r['contact_phone'] = $m['contact']['phone'];
                                        else if (isset($m['history'][0]['from']) && $m['history'][0]['from']) $r['contact_phone'] = $m['history'][0]['from'];
                                        else if (isset($m['from']) && $m['from']) $r['contact_phone'] = $m['from'];
                                        else if (isset($m['raw']['from']) && $m['raw']['from']) $r['contact_phone'] = $m['raw']['from'];
                                    }
                                    if ($need_name) {
                                        if (isset($m['contact']['name']) && $m['contact']['name']) $r['contact_name'] = $m['contact']['name'];
                                        else if (isset($m['contact_name']) && $m['contact_name']) $r['contact_name'] = $m['contact_name'];
                                        else if (isset($m['history'][0]['contact']['name']) && $m['history'][0]['contact']['name']) $r['contact_name'] = $m['history'][0]['contact']['name'];
                                        else if (isset($m['raw']['contact_name']) && $m['raw']['contact_name']) $r['contact_name'] = $m['raw']['contact_name'];
                                    }
                                }
                            }
                            if (!isset($r['contact_phone']) && isset($r['phone'])) $r['contact_phone'] = $r['phone'];
                            if (!isset($r['contact_name']) && isset($r['title'])) $r['contact_name'] = $r['title'];
                        }
                        unset($r);
                        return array('status'=>'OK','result'=>$rows);
                    }
                } catch (Exception $e) { /* ignore */ }
            }

            // 5) fallback from bot_messages
            if ($this->table_exists('bot_messages')) {
                try { $this->pdo->perform("SET SESSION group_concat_max_len = 1000000", array()); } catch (Exception $e) { }
                $sql = "SELECT COALESCE(conversation_id, CONCAT('phone_', REPLACE(phone,'+',''))) AS id, phone AS contact_phone, SUBSTRING_INDEX(GROUP_CONCAT(message ORDER BY created_at DESC SEPARATOR '||MSGSEP||'), '||MSGSEP||', 1) AS last_message, MAX(created_at) AS last_activity, COUNT(*) AS total_messages FROM bot_messages GROUP BY COALESCE(conversation_id, phone) ORDER BY last_activity DESC LIMIT 500";
                $rows = $this->pdo->fetchAll($sql);
                if ($rows) {
                    foreach ($rows as &$r) {
                        if (!isset($r['contact_name'])) $r['contact_name'] = null;
                        if (isset($r['last_activity']) && $r['last_activity']) $r['updated_at'] = date('d/m/Y H:i', strtotime($r['last_activity']));
                    }
                    unset($r);
                    return array('status'=>'OK','result'=>$rows);
                }
            }

            return array('status'=>'ERROR','msg'=>'No se encontraron chats o vistas relacionadas','result'=>array());
        } catch (PDOException $e) {
            return array('status'=>'EXCEPTION','msg'=>$e->getMessage());
        }
    }

    /**
     * Obtiene detalle de una conversación específica por su ID
     * 
     * ESTRATEGIA DE BÚSQUEDA EN CASCADA:
     * Intenta localizar la conversación en este orden:
     * 
     * 1. bot_chats (TABLA LEGACY):
     *    - Busca por id numérico
     *    - Retorna: id, contact_name, contact_phone, last_message, updated_at
     * 
     * 2. messenger (TABLA ALTERNATIVA):
     *    - Busca por id, conversation_id o thread_id
     *    - Normaliza campos de diferentes nombres
     *    - Formatea fecha de última actividad
     * 
     * 3. bot_conversations (TABLA PRINCIPAL):
     *    - Busca por conversation_id (puede ser string UUID o sintético)
     *    - Retorna datos normalizados
     * 
     * VALIDACIÓN:
     * - Requiere ID válido (no vacío)
     * - Retorna ERROR si no se encuentra en ninguna tabla
     * 
     * NORMALIZACIÓN DE DATOS:
     * - Convierte nombres de campos variados al estándar
     * - Formatea fechas a dd/mm/YYYY HH:ii
     * - Asegura que contact_name y contact_phone existan
     * 
     * @param mixed $id ID de la conversación (numérico o string UUID)
     * @return array Array con 'status', 'msg' (opcional) y 'result' (datos de la conversación)
     *               - status: 'OK', 'ERROR'
     *               - result: Array con datos normalizados de la conversación
     */
    public function get_chat_by_id($id) {
        try {
            if (!$id) return array('status'=>'ERROR','msg'=>'ID inválido');

            // Buscar en bot_conversations
            if ($this->table_exists('bot_conversations')) {
                $sql = "SELECT
                        conversation_id AS id,
                        phone AS contact_phone,
                        metadata,
                        DATE_FORMAT(last_activity, '%d/%m/%Y %H:%i') AS updated_at,
                        (SELECT message FROM bot_messages m WHERE m.conversation_id = bot_conversations.conversation_id ORDER BY m.id DESC LIMIT 1) AS last_message
                        FROM bot_conversations
                        WHERE conversation_id = :id
                        LIMIT 1";
                $row = $this->pdo->fetchOne($sql, array('id'=>$id));

                if ($row) {
                    // Extraer contact_name desde metadata si existe
                    $row['contact_name'] = null;
                    if (isset($row['metadata']) && $row['metadata']) {
                        $m = is_string($row['metadata']) ? json_decode($row['metadata'], true) : $row['metadata'];
                        if (is_array($m)) {
                            if (isset($m['contact']['name'])) $row['contact_name'] = $m['contact']['name'];
                            elseif (isset($m['contact_name'])) $row['contact_name'] = $m['contact_name'];
                        }
                    }
                    return array('status'=>'OK','result'=>$row);
                }
            }

            return array('status'=>'ERROR','msg'=>'Chat no encontrado');
        } catch (PDOException $e) {
            return array('status'=>'EXCEPTION','msg'=>$e->getMessage());
        }
    }

    // ========================================================================
    // SECCIÓN 2: GESTIÓN DE MENSAJES
    // ========================================================================

    /**
     * Obtiene todos los mensajes de una conversación específica
     * 
     * DETECCIÓN AUTOMÁTICA DE ESTRUCTURA:
     * Detecta dinámicamente qué tabla y columna usar para relacionar mensajes:
     * 
     * Tablas soportadas:
     * - bot_messages (tabla principal)
     * - messenger_messages (sistema alternativo)
     * - messenger (si contiene columna 'message')
     * 
     * Columnas de relación:
     * - conversation_id (preferida, string UUID)
     * - chat_id (legacy, numérico)
     * 
     * ENRIQUECIMIENTO DE DATOS:
     * - El campo responded_by_user_name contiene el nombre completo del agente
     * - Si responded_by_user_name existe → muestra el nombre guardado
     * - Si no hay nombre → muestra phone o sender
     * 
     * NORMALIZACIÓN DE MENSAJES:
     * Convierte diferentes estructuras a formato estándar para frontend:
     * - id: ID del mensaje
     * - message: Texto del mensaje (de 'message', 'text' o 'content')
     * - direction: Dirección del mensaje ('incoming', 'outgoing', 'in', 'out')
     * - created_at: Fecha de creación
     * - sender: Quién envió (nombre de agente o teléfono)
     * - message_type: Tipo de mensaje (texto, imagen, audio, etc.)
     * - raw: Datos originales completos de la BD
     * 
     * ORDEN Y LÍMITE:
     * - Ordena por created_at ASC (más antiguos primero, cronológico)
     * - Límite por defecto: 500 mensajes
     * - Si no encuentra tabla válida → retorna array vacío
     * 
     * @param mixed $conversation_id ID de la conversación (string UUID o numérico)
     * @param int $limit Máximo número de mensajes a retornar (default: 500)
     * @return array Array de mensajes normalizados, o array vacío si no hay datos
     *               Cada mensaje tiene: id, message, direction, created_at, sender, message_type, raw
     */
    public function get_messages($conversation_id, $after_id = null, $limit = 500) {
        if (!$conversation_id) return array();
        
        // ============================================================
        // Query con JOIN a bot_media_files y filtro opcional after_id
        // Mantener todos los mensajes sin filtro
        // ============================================================
        $sql = "SELECT m.*, 
                '' AS first_name, 
                '' AS last_name,
                mf.id AS media_file_id,
                mf.media_id AS media_wamid,
                mf.media_type,
                mf.mime_type,
                mf.filename,
                mf.file_path
                FROM bot_messages m 
                LEFT JOIN bot_media_files mf ON m.provider_message_id COLLATE utf8mb4_unicode_ci = mf.message_id COLLATE utf8mb4_unicode_ci
                WHERE m.conversation_id = :cid ";
        
        // Si hay after_id, solo mensajes posteriores (para polling incremental)
        if ($after_id) {
            $sql .= " AND m.id > :after_id ";
        }

        // ORDEN: Por created_at ASC para soportar buffer circular
        // Cuando se reutiliza un ID viejo con nuevo contenido, el created_at se actualiza
        // y el mensaje aparece en su posición cronológica correcta
        $sql .= " ORDER BY m.created_at ASC LIMIT " . intval($limit);
        
        $params = array('cid' => $conversation_id);
        if ($after_id) {
            $params['after_id'] = $after_id;
        }
        
        try {
            $rows = $this->pdo->fetchAll($sql, $params);
            
            // DEBUG LOG deshabilitado - descomentar solo para debugging local
            // $log = "=== M_Bot_Chat::get_messages ===\n";
            // $log .= "conversation_id: {$conversation_id}\n";
            // $log .= "after_id: " . ($after_id ? $after_id : 'NULL') . "\n";
            // $log .= "rows is_array: " . (is_array($rows) ? 'YES' : 'NO') . "\n";
            // $log .= "rows count: " . (is_array($rows) ? count($rows) : 'NULL') . "\n";
            // file_put_contents(__DIR__ . '/../../debug_model.log', $log, FILE_APPEND);
            
            if (!$rows || !is_array($rows)) {
                return array();
            }
        } catch (Exception $e) {
            error_log("get_messages ERROR: " . $e->getMessage() . " | SQL: " . $sql);
            // DEBUG deshabilitado - descomentar solo para debugging local
            // file_put_contents(__DIR__ . '/../../debug_model.log', "EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
            return array();
        }
        
        if (!$rows) return array();

            // Normalize output to a simple, consistent shape for the frontend
            $out = array();
            foreach ($rows as $r) {
                $id = isset($r['id']) ? $r['id'] : (isset($r['ID']) ? $r['ID'] : null);
                $message = '';
                if (isset($r['message'])) $message = $r['message'];
                elseif (isset($r['text'])) $message = $r['text'];
                elseif (isset($r['content'])) $message = $r['content'];
                // Ensure string
                $message = is_null($message) ? '' : (string)$message;
                $direction = isset($r['direction']) ? $r['direction'] : (isset($r['type']) ? $r['type'] : 'incoming');
                $created = isset($r['created_at']) ? $r['created_at'] : (isset($r['created']) ? $r['created'] : '');
                $sender = '';
                if (isset($r['first_name']) || isset($r['last_name'])) {
                    $sender = trim((isset($r['first_name']) ? $r['first_name'] : '') . ' ' . (isset($r['last_name']) ? $r['last_name'] : ''));
                }
                if ($sender === '') {
                    if (isset($r['phone'])) $sender = $r['phone'];
                    elseif (isset($r['sender'])) $sender = $r['sender'];
                }

                // Información de archivos multimedia (JOIN con bot_media_files)
                $media_info = null;
                if (isset($r['media_file_id']) && $r['media_file_id']) {
                    $media_info = array(
                        'media_file_id' => $r['media_file_id'],
                        'media_wamid' => isset($r['media_wamid']) ? $r['media_wamid'] : null,
                        'media_type' => isset($r['media_type']) ? $r['media_type'] : null,
                        'mime_type' => isset($r['mime_type']) ? $r['mime_type'] : null,
                        'filename' => isset($r['filename']) ? $r['filename'] : null,
                        'file_path' => isset($r['file_path']) ? $r['file_path'] : null
                    );
                }

                $out[] = array(
                    'id' => $id,
                    'message' => $message,
                    'direction' => $direction,
                    'created_at' => $created,
                    'sender' => $sender,
                    'message_type' => isset($r['message_type']) ? $r['message_type'] : null,
                    'media' => $media_info,  // Nueva propiedad con info de archivos multimedia
                    'raw' => $r
                );
            }
            return $out;
    }

    /**
     * Envía y guarda un mensaje en la base de datos
     * 
     * FUNCIONALIDAD DUAL:
     * 1. Inserta mensaje en tabla de mensajes (bot_messages o messenger_messages)
     * 2. Actualiza resumen de conversación (bot_conversations, bot_chats o messenger)
     * 
     * NORMALIZACIÓN DE IDENTIFICADORES:
     * - Acepta 'conversation_id' (preferido, string UUID)
     * - Acepta 'chat_id' (legacy, numérico)
     * - Si no viene conversation_id pero SÍ 'phone':
     *   * Busca conversación existente por phone
     *   * Si no existe → genera conversation_id sintético: 'phone_123456789'
     *   * Crea entrada en bot_conversations con UPSERT (evita duplicados)
     * 
     * DETECCIÓN DE ESTRUCTURA:
     * - Detecta automáticamente qué tabla usar: bot_messages o messenger_messages
     * - Detecta qué columna de relación: conversation_id o chat_id
     * - Detecta columnas opcionales para metadata:
     *   * responded_by_user_name: Nombre completo del agente que respondió
     *   * payload: Datos adicionales en JSON
     *   * is_automated: Si es respuesta automática del bot (0/1)
     *   * message_type: Tipo de mensaje (text, image, audio, video, document, etc.)
     * 
     * GESTIÓN DE TRIGGERS:
     * - Detecta si existe trigger 'after_bot_message_insert' o 'trg_bot_messages_after_insert'
     * - Si existe trigger → NO actualiza conversación desde PHP (evita duplicación)
     * - Si NO existe trigger → actualiza manualmente bot_conversations con:
     *   * Incrementa total_messages
     *   * Actualiza last_activity a NOW()
     *   * Si incoming → actualiza last_message y incrementa unread_count
     *   * Si outgoing → actualiza last_bot_response
     *   * Actualiza current_section si viene en $data['bot_section']
     * 
     * ACTUALIZACIÓN ATÓMICA DE CONVERSACIÓN:
     * - total_messages: COALESCE(total_messages,0) + 1 (incremento atómico)
     * - unread_count: Solo incrementa si direction = 'incoming'
     * - last_message: Solo actualiza si es mensaje entrante
     * - last_bot_response: Solo actualiza si es mensaje saliente
     * 
     * FALLBACK PARA TABLAS ALTERNATIVAS:
     * - Si no existe bot_conversations pero SÍ bot_chats → actualiza bot_chats
     * - Si existe messenger → actualiza tabla messenger
     * 
     * VALIDACIÓN:
     * - Requiere conversation_id (o phone para generarlo)
     * - Requiere message no vacío
     * - direction por defecto: 'outgoing'
     * 
     * @param array $data Array con datos del mensaje:
     *        - conversation_id: ID de conversación (string UUID)
     *        - chat_id: ID de chat (numérico, legacy)
     *        - phone: Teléfono (opcional, para generar conversation_id)
     *        - message: Texto del mensaje (requerido)
     *        - direction: 'incoming', 'outgoing', 'in', 'out' (default: 'outgoing')
     *        - contact_name: Nombre del contacto (opcional)
     *        - responded_by_user_name: Nombre completo del agente (opcional)
     *        - payload: Metadata adicional (opcional, JSON string o array)
     *        - is_automated: Si es respuesta automatizada (opcional, 0/1)
     *        - message_type: Tipo de mensaje (opcional: text, image, etc.)
     *        - bot_section: Sección actual del bot (opcional)
     * 
     * @return array Array con 'status' y 'msg':
     *               - status: 'OK', 'ERROR' o 'EXCEPTION'
     *               - msg: Mensaje descriptivo del resultado
     */
    public function send_message($data) {
        // DEBUG: Log para verificar que el método se ejecuta
        $log_file = __DIR__ . '/../../send_message_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        $call_count = file_exists($log_file) ? count(file($log_file)) : 0;
        file_put_contents($log_file, "[{$timestamp}] LLAMADA #{$call_count} - send_message() ejecutado\n", FILE_APPEND);
        file_put_contents($log_file, "[{$timestamp}] Data recibida: " . json_encode($data) . "\n", FILE_APPEND);
        file_put_contents($log_file, "[{$timestamp}] Stack trace: " . json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)) . "\n\n", FILE_APPEND);
        
        try {
            // Normalizar identificador de conversación
            $conversation_id = null;
            if (isset($data['conversation_id'])) $conversation_id = $data['conversation_id'];
            if (isset($data['chat_id']) && !$conversation_id) $conversation_id = $data['chat_id'];

            // Si no viene conversation_id pero sí phone, intentar reutilizar/crear una conversación por phone
            if (!$conversation_id && isset($data['phone']) && trim($data['phone']) !== '') {
                $phone = trim($data['phone']);
                // intentar buscar una conversación existente para este phone
                try {
                    if ($this->table_exists('bot_conversations')) {
                        $r = $this->pdo->fetchOne("SELECT conversation_id FROM bot_conversations WHERE phone = :phone LIMIT 1", array('phone' => $phone));
                        if ($r && isset($r['conversation_id']) && $r['conversation_id']) {
                            $conversation_id = $r['conversation_id'];
                        } else {
                            // generar un conversation_id estable basado en el phone (evita crear múltiples hilos)
                            // usamos prefijo para distinguirlo de posibles UUIDs del proveedor
                            $conversation_id = 'phone_' . preg_replace('/[^0-9a-zA-Z]/', '', $phone);
                            // insertar/actualizar una fila inicial en bot_conversations (usamos upsert para cuando exista un índice único en phone)
                            $insert_conv = "INSERT INTO bot_conversations (conversation_id, phone, contact_name, created_at, last_activity, total_messages) VALUES (:conversation_id, :phone, :contact_name, NOW(), NOW(), 0) ON DUPLICATE KEY UPDATE contact_name = COALESCE(VALUES(contact_name), contact_name), last_activity = NOW()";
                            $name = isset($data['contact_name']) ? $data['contact_name'] : null;
                            try { $this->pdo->perform($insert_conv, array('conversation_id'=>$conversation_id, 'phone'=>$phone, 'contact_name'=>$name)); } catch (Exception $e) { /* ignore insert errors */ }
                        }
                    }
                } catch (Exception $e) { /* silent fallback */ }
            }

            $message = isset($data['message']) ? trim($data['message']) : '';
            $direction = isset($data['direction']) ? $data['direction'] : 'outgoing';

            if (!$conversation_id || $message === '') return array('status'=>'ERROR','msg'=>'Parámetros inválidos');

            // Usar bot_messages (tabla principal)
            $msg_table = 'bot_messages';
            $use_conv_col = true; // bot_messages usa conversation_id

            // Construir insert dependiendo de la estructura
            // Detectar columnas opcionales para preservar metadata (responded_by_user_name, payload, is_automated, message_type, phone, agent_mode, status)
            $has_responded_by_user_name = false; $has_payload = false; $has_is_automated = false; $has_message_type = false; $has_phone = false; $has_agent_mode = false; $has_status = false;
            try {
                if ($this->table_exists($msg_table)) {
                    $r = $this->pdo->fetchOne("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :tbl AND column_name = 'responded_by_user_name'", array('tbl'=>$msg_table));
                    $has_responded_by_user_name = ($r && isset($r['c']) && intval($r['c'])>0);
                    $r = $this->pdo->fetchOne("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :tbl AND column_name = 'payload'", array('tbl'=>$msg_table));
                    $has_payload = ($r && isset($r['c']) && intval($r['c'])>0);
                    $r = $this->pdo->fetchOne("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :tbl AND column_name = 'is_automated'", array('tbl'=>$msg_table));
                    $has_is_automated = ($r && isset($r['c']) && intval($r['c'])>0);
                    $r = $this->pdo->fetchOne("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :tbl AND column_name = 'message_type'", array('tbl'=>$msg_table));
                    $has_message_type = ($r && isset($r['c']) && intval($r['c'])>0);
                    $r = $this->pdo->fetchOne("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :tbl AND column_name = 'phone'", array('tbl'=>$msg_table));
                    $has_phone = ($r && isset($r['c']) && intval($r['c'])>0);
                    $r = $this->pdo->fetchOne("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :tbl AND column_name = 'agent_mode'", array('tbl'=>$msg_table));
                    $has_agent_mode = ($r && isset($r['c']) && intval($r['c'])>0);
                    $r = $this->pdo->fetchOne("SELECT COUNT(*) AS c FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :tbl AND column_name = 'status'", array('tbl'=>$msg_table));
                    $has_status = ($r && isset($r['c']) && intval($r['c'])>0);
                }
            } catch (Exception $e) { /* ignore */ }

            if ($use_conv_col) {
                $sql = "INSERT INTO {$msg_table} (conversation_id, message, direction";
                $sql .= ($has_phone ? ", phone" : "");
                $sql .= ($has_responded_by_user_name ? ", responded_by_user_name" : "");
                $sql .= ($has_payload ? ", payload" : "");
                $sql .= ($has_is_automated ? ", is_automated" : "");
                $sql .= ($has_message_type ? ", message_type" : "");
                $sql .= ($has_agent_mode ? ", agent_mode" : "");
                $sql .= ($has_status ? ", status" : "");
                $sql .= ", created_at) VALUES (:conversation_id, :message, :direction";
                $sql .= ($has_phone ? ", :phone" : "");
                $sql .= ($has_responded_by_user_name ? ", :responded_by_user_name" : "");
                $sql .= ($has_payload ? ", :payload" : "");
                $sql .= ($has_is_automated ? ", :is_automated" : "");
                $sql .= ($has_message_type ? ", :message_type" : "");
                $sql .= ($has_agent_mode ? ", :agent_mode" : "");
                $sql .= ($has_status ? ", :status" : "");
                $sql .= ", NOW())";

                $params = array('conversation_id'=>$conversation_id, 'message'=>$message, 'direction'=>$direction);
                if ($has_phone) $params['phone'] = isset($data['phone']) ? $data['phone'] : null;
                if ($has_responded_by_user_name) $params['responded_by_user_name'] = isset($data['responded_by_user_name']) ? $data['responded_by_user_name'] : null;
                if ($has_payload) $params['payload'] = isset($data['payload']) ? (is_string($data['payload']) ? $data['payload'] : json_encode($data['payload'])) : null;
                if ($has_is_automated) $params['is_automated'] = isset($data['is_automated']) ? ($data['is_automated'] ? 1 : 0) : 0;
                if ($has_message_type) $params['message_type'] = isset($data['message_type']) ? $data['message_type'] : 'text';
                if ($has_agent_mode) $params['agent_mode'] = isset($data['agent_mode']) ? intval($data['agent_mode']) : 1; // Por defecto 1 = mensaje manual del panel
                if ($has_status) $params['status'] = isset($data['status']) ? $data['status'] : 'pending';
            }

            $res = $this->pdo->perform($sql, $params);
            if (!$res) return array('status'=>'ERROR','msg'=>'No se pudo insertar mensaje');

            // Si existe un trigger que actualiza la conversación, NO duplicar la lógica
            $trigger_present = $this->trigger_exists('after_bot_message_insert') || $this->trigger_exists('trg_bot_messages_after_insert');

            if (!$trigger_present) {
                // Actualizar manualmente la conversación resumen (si existe)
                if ($this->table_exists('bot_conversations')) {
                    // Actualizar solo columnas que existen en bot_conversations
                    $inc_total = "COALESCE(total_messages,0) + 1";
                    $update_sql = "UPDATE bot_conversations
                                   SET total_messages = {$inc_total},
                                       last_activity = NOW(),
                                       current_section = COALESCE(:bot_section, current_section)
                                   WHERE conversation_id = :conversation_id";
                    $params_up = array(
                        'bot_section' => isset($data['bot_section']) ? $data['bot_section'] : null,
                        'conversation_id' => $conversation_id
                    );
                    try {
                        $this->pdo->perform($update_sql, $params_up);
                    } catch (Exception $e) {
                        /* no bloquear por fallo de update */
                    }
                }
            }

            // NOTA: El mensaje se guarda en la BD con status='pending' 
            // El bot de WhatsApp (Node.js/webhook) debe consultar periódicamente
            // los mensajes pendientes y enviarlos, luego actualizar status='sent'
            
            return array('status'=>'OK','msg'=>'Mensaje guardado');
        } catch (PDOException $e) {
            return array('status'=>'EXCEPTION','msg'=>$e->getMessage());
        }
    }

    // ========================================================================
    // SECCIÓN: ESTADÍSTICAS DEL DASHBOARD
    // ========================================================================

    /**
     * Obtiene estadísticas generales del chat para el dashboard
     * 
     * Calcula métricas en tiempo real:
     * - Total de conversaciones únicas
     * - Mensajes sin leer (status != 'read')
     * - Conversaciones creadas hoy
     * - Mensajes recibidos hoy
     * 
     * @return array Array asociativo con las estadísticas
     */
    /**
     * Obtiene estadísticas del dashboard de conversaciones
     * ACTUALIZADO: 2025-11-07 - Cuenta solo conversaciones activas visibles en interfaz
     * 
     * @return array Estadísticas con contadores actualizados
     */
    public function get_stats() {
        try {
            $stats = array(
                'total_conversations' => 0,
                'unread_messages' => 0,
                'today_conversations' => 0,
                'today_messages' => 0,
                'pending_requests' => 0,
                'admin_active_chats' => 0  // Chats con Bot Automático OFF
            );

            // 1. Total de conversaciones ACTIVAS (que aparecen en la interfaz)
            // NUEVO: Solo cuenta conversaciones con MÁS DE 2 MENSAJES (filtro del panel)
            $stats['total_conversations'] = 0;
            
            if ($this->table_exists('bot_messages')) {
                // Cuenta solo conversaciones con más de 2 mensajes (las que se muestran en el panel)
                $sql = "SELECT COUNT(DISTINCT conversation_id) as total 
                        FROM (
                            SELECT conversation_id, COUNT(*) as msg_count
                            FROM bot_messages 
                            WHERE conversation_id IS NOT NULL 
                            AND conversation_id != ''
                            GROUP BY conversation_id
                            HAVING msg_count > 2
                        ) as active_conversations";
                $r = $this->pdo->fetchOne($sql);
                $stats['total_conversations'] = isset($r['total']) ? intval($r['total']) : 0;
            }
            
            // Si bot_messages no existe o no dio resultado, intentar con bot_conversations
            if ($stats['total_conversations'] === 0 && $this->table_exists('bot_conversations')) {
                $sql = "SELECT COUNT(DISTINCT conversation_id) as total 
                        FROM bot_conversations 
                        WHERE (SELECT COUNT(*) FROM bot_messages m WHERE m.conversation_id = bot_conversations.conversation_id) > 2";
                $r = $this->pdo->fetchOne($sql);
                $stats['total_conversations'] = isset($r['total']) ? intval($r['total']) : 0;
            }

            // 2. Conversaciones con Bot Automático ON (agent_mode = 0)
            // Cuenta cuántas conversaciones VISIBLES EN EL PANEL están siendo atendidas por el bot
            // REQUISITO: Deben tener más de 2 mensajes (las que aparecen en la lista)
            $stats['unread_messages'] = 0;
            
            if ($this->table_exists('bot_conversations')) {
                // Verificar si existe columna agent_mode
                $col_check = $this->pdo->fetchOne(
                    "SELECT COUNT(*) AS c FROM information_schema.columns 
                     WHERE table_schema = DATABASE() 
                     AND table_name = 'bot_conversations' 
                     AND column_name = 'agent_mode'"
                );
                
                if (isset($col_check['c']) && intval($col_check['c']) > 0) {
                    // Contar conversaciones con agent_mode = 0 (Bot ON) 
                    // Y que tengan más de 2 mensajes (visibles en panel)
                    $sql = "SELECT COUNT(DISTINCT bc.conversation_id) as total 
                            FROM bot_conversations bc
                            WHERE bc.agent_mode = 0
                            AND (
                                SELECT COUNT(*) 
                                FROM bot_messages m 
                                WHERE m.conversation_id = bc.conversation_id
                            ) > 2";
                    $r = $this->pdo->fetchOne($sql);
                    $stats['unread_messages'] = isset($r['total']) ? intval($r['total']) : 0;
                }
            }

            // 3. Conversaciones creadas hoy
            $today = date('Y-m-d');
            if ($this->table_exists('bot_conversations')) {
                $sql = "SELECT COUNT(DISTINCT conversation_id) as total 
                        FROM bot_conversations 
                        WHERE DATE(created_at) = :today";
                $r = $this->pdo->fetchOne($sql, array('today' => $today));
                $stats['today_conversations'] = isset($r['total']) ? intval($r['total']) : 0;
            } elseif ($this->table_exists('bot_chats')) {
                $sql = "SELECT COUNT(DISTINCT id) as total 
                        FROM bot_chats 
                        WHERE DATE(created_at) = :today";
                $r = $this->pdo->fetchOne($sql, array('today' => $today));
                $stats['today_conversations'] = isset($r['total']) ? intval($r['total']) : 0;
            }

            // 4. Chats Atendidos por Administrador (Bot Automático OFF)
            // Cuenta SOLO conversaciones VISIBLES EN EL PANEL con agent_mode = 1 (Bot OFF, humano responde)
            // REQUISITO: Deben tener más de 2 mensajes (las que aparecen en la lista)
            $stats['admin_active_chats'] = 0;
            
            if ($this->table_exists('bot_conversations')) {
                // Verificar si existe columna agent_mode
                $col_check = $this->pdo->fetchOne(
                    "SELECT COUNT(*) AS c FROM information_schema.columns 
                     WHERE table_schema = DATABASE() 
                     AND table_name = 'bot_conversations' 
                     AND column_name = 'agent_mode'"
                );
                
                if (isset($col_check['c']) && intval($col_check['c']) > 0) {
                    // Contar conversaciones con agent_mode = 1 (Bot Automático OFF)
                    // Y que tengan más de 2 mensajes (visibles en panel)
                    $sql = "SELECT COUNT(DISTINCT bc.conversation_id) as total 
                            FROM bot_conversations bc
                            WHERE bc.agent_mode = 1
                            AND (
                                SELECT COUNT(*) 
                                FROM bot_messages m 
                                WHERE m.conversation_id = bc.conversation_id
                            ) > 2";
                    $r = $this->pdo->fetchOne($sql);
                    $stats['admin_active_chats'] = isset($r['total']) ? intval($r['total']) : 0;
                }
            }
            
            // Mantener pending_requests para compatibilidad (igual a admin_active_chats)
            $stats['pending_requests'] = $stats['admin_active_chats'];

            return $stats;

        } catch (Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return array(
                'total_conversations' => 0,
                'unread_messages' => 0,
                'today_conversations' => 0,
                'today_messages' => 0,
                'pending_requests' => 0
            );
        }
    }

    // ========================================================================
    // SECCIÓN: MARCAR MENSAJES COMO LEÍDOS
    // ========================================================================

    /**
     * Marca todos los mensajes entrantes de una conversación como leídos
     * Se ejecuta automáticamente cuando el usuario abre una conversación
     * 
     * @param string $conversation_id ID de la conversación
     * @return bool True si se marcaron correctamente, false en caso de error
     */
    public function mark_as_read($conversation_id) {
        try {
            if (!$this->table_exists('bot_messages')) {
                return false;
            }

            // Actualiza el status y read_at de todos los mensajes entrantes no leídos de esta conversación
            $sql = "UPDATE bot_messages
                    SET status = 'read',
                        read_at = NOW()
                    WHERE conversation_id = :conversation_id
                    AND direction = 'incoming'
                    AND status != 'read'";
            
            $affected = $this->pdo->fetchAffected($sql, array('conversation_id' => $conversation_id));
            
            return $affected !== false;

        } catch (Exception $e) {
            error_log("Error marcando mensajes como leídos: " . $e->getMessage());
            return false;
        }
    }

    // ========================================================================
    // GESTIÓN DE MODO AGENTE (Bot ON/OFF por conversación)
    // ========================================================================

    /**
     * Activa o desactiva el modo agente para una conversación específica
     * 
     * @param string $conversation_id ID de la conversación
     * @param bool $agent_mode TRUE = Bot OFF (humano responde), FALSE = Bot ON (automático)
     * @return array ['status'=>'OK'|'ERROR', 'msg'=>'...', 'agent_mode'=>bool]
     */
    public function toggle_agent_mode($conversation_id, $agent_mode) {
        try {
            // DEBUG: Log entrada
            $log_file = __DIR__ . '/../../toggle_agent_mode_debug.log';
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($log_file, "[{$timestamp}] Entrada: conversation_id={$conversation_id}, agent_mode=" . ($agent_mode ? 'true' : 'false') . "\n", FILE_APPEND);
            
            if (empty($conversation_id)) {
                file_put_contents($log_file, "[{$timestamp}] ERROR: conversation_id vacío\n", FILE_APPEND);
                return array('status'=>'ERROR', 'msg'=>'conversation_id requerido');
            }
            
            // Convertir a entero para BD (1 o 0)
            $agent_mode_int = $agent_mode ? 1 : 0;
            file_put_contents($log_file, "[{$timestamp}] agent_mode_int: {$agent_mode_int}\n", FILE_APPEND);
            
            // Intentar actualizar en bot_conversations
            if ($this->table_exists('bot_conversations')) {
                file_put_contents($log_file, "[{$timestamp}] Tabla bot_conversations existe\n", FILE_APPEND);
                
                // Verificar si existe la conversación
                $exists = $this->pdo->fetchOne(
                    "SELECT conversation_id FROM bot_conversations WHERE conversation_id = :id LIMIT 1",
                    array('id' => $conversation_id)
                );
                file_put_contents($log_file, "[{$timestamp}] Registro existe: " . ($exists ? 'SÍ' : 'NO') . "\n", FILE_APPEND);
                
                if ($exists) {
                    // Actualizar registro existente
                    $sql = "UPDATE bot_conversations SET agent_mode = :agent_mode WHERE conversation_id = :id";
                    file_put_contents($log_file, "[{$timestamp}] Ejecutando UPDATE...\n", FILE_APPEND);
                    $result = $this->pdo->perform($sql, array(
                        'agent_mode' => $agent_mode_int,
                        'id' => $conversation_id
                    ));
                    file_put_contents($log_file, "[{$timestamp}] Resultado UPDATE: " . ($result ? 'OK' : 'FAIL') . "\n", FILE_APPEND);
                } else {
                    // Crear registro si no existe
                    $sql = "INSERT INTO bot_conversations (conversation_id, agent_mode, created_at, last_activity) 
                            VALUES (:id, :agent_mode, NOW(), NOW())";
                    file_put_contents($log_file, "[{$timestamp}] Ejecutando INSERT...\n", FILE_APPEND);
                    $result = $this->pdo->perform($sql, array(
                        'id' => $conversation_id,
                        'agent_mode' => $agent_mode_int
                    ));
                    file_put_contents($log_file, "[{$timestamp}] Resultado INSERT: " . ($result ? 'OK' : 'FAIL') . "\n", FILE_APPEND);
                }
                
                if ($result) {
                    $mode_text = $agent_mode ? 'OFF (Manual)' : 'ON (Automático)';
                    file_put_contents($log_file, "[{$timestamp}] ✅ Éxito: {$mode_text}\n", FILE_APPEND);
                    return array(
                        'status' => 'OK',
                        'msg' => "Bot Automático: {$mode_text}",
                        'agent_mode' => $agent_mode
                    );
                } else {
                    file_put_contents($log_file, "[{$timestamp}] ❌ No se pudo actualizar\n", FILE_APPEND);
                    return array('status'=>'ERROR', 'msg'=>'No se pudo actualizar el modo');
                }
            }
            
            file_put_contents($log_file, "[{$timestamp}] ERROR: Tabla no existe\n", FILE_APPEND);
            return array('status'=>'ERROR', 'msg'=>'Tabla bot_conversations no existe');
            
        } catch (Exception $e) {
            file_put_contents($log_file, "[{$timestamp}] EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
            return array('status'=>'ERROR', 'msg'=>$e->getMessage());
        }
    }

    /**
     * Obtiene el estado actual del modo agente para una conversación
     * 
     * @param string $conversation_id ID de la conversación
     * @return array ['status'=>'OK'|'ERROR', 'agent_mode'=>bool]
     */
    public function get_agent_mode($conversation_id) {
        try {
            if (empty($conversation_id)) {
                return array('status'=>'ERROR', 'agent_mode'=>false);
            }
            
            if ($this->table_exists('bot_conversations')) {
                $result = $this->pdo->fetchOne(
                    "SELECT agent_mode FROM bot_conversations WHERE conversation_id = :id LIMIT 1",
                    array('id' => $conversation_id)
                );
                
                if ($result && isset($result['agent_mode'])) {
                    $agent_mode = (bool)$result['agent_mode'];
                    return array(
                        'status' => 'OK',
                        'agent_mode' => $agent_mode
                    );
                }
            }
            
            // Por defecto: Bot automático ON (agent_mode = false)
            return array('status'=>'OK', 'agent_mode'=>false);
            
        } catch (Exception $e) {
            return array('status'=>'ERROR', 'agent_mode'=>false);
        }
    }

}


