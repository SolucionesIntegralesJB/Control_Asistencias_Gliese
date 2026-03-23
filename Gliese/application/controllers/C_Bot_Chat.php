<?php
// --
class C_Bot_Chat extends Controller {

    public function __construct() {
        parent::__construct();
    }
    // Página principal del controlador
    public function index() {
        $this->functions->check_permissions($this->segment->get('modules'), 'Bot_Chat');
        
        // Obtener estadísticas del dashboard
        $obj = $this->load_model('Bot_Chat');
        $stats = $obj->get_stats();
        
        // Pasar estadísticas a la vista
        $this->view->set_data(array('stats' => $stats));
        
        $this->view->set_js('index');
        $this->view->set_menu(array('modules' => $this->segment->get('modules'), 'view' => 'Bot_Chat'));
        $this->view->set_view('index');
    }
    // List chats (placeholder)
    public function get_chats() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request !== 'GET') {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'message'=>'Método no permitido'));
            return;
        }

        $obj = $this->load_model('Bot_Chat');
        $res = $obj->get_chats();
        if (!is_array($res) || !isset($res['status'])) {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'message'=>'Error interno'));
            return;
        }

        if ($res['status'] === 'OK') {
            $rows = isset($res['result']) ? $res['result'] : array();
            // debug helper: optionally return sample data if none found and debug_sample=1
            if (empty($rows) && isset($_GET['debug_sample']) && $_GET['debug_sample']=='1') {
                $rows = array(
                    array('id'=>'51907344803','contact_name'=>'Sin título','contact_phone'=>'51907344803','last_message'=>'hola','updated_at'=>'2025-10-09 05:02'),
                );
            }
            $out = array();
            foreach ($rows as $r) {
                // Prefer conversation identifier (conversation_id) when available; fallback to numeric id
                $legacyId = isset($r['id']) ? $r['id'] : null;
                $convId = null;
                if (isset($r['conversation_id']) && $r['conversation_id']) $convId = $r['conversation_id'];
                elseif (isset($r['chat_id']) && $r['chat_id']) $convId = $r['chat_id'];
                elseif (isset($r['phone']) && $r['phone']) $convId = $r['phone'];
                // If still null, try inside raw payload
                if (!$convId && isset($r['raw']) && is_array($r['raw'])) {
                    if (isset($r['raw']['conversation_id']) && $r['raw']['conversation_id']) $convId = $r['raw']['conversation_id'];
                    elseif (isset($r['raw']['chat_id']) && $r['raw']['chat_id']) $convId = $r['raw']['chat_id'];
                }
                // final fallback to legacy id
                if (!$convId) $convId = $legacyId;

                $title = isset($r['contact_name']) ? $r['contact_name'] : (isset($r['title']) ? $r['title'] : (isset($r['name']) ? $r['name'] : 'Conversación'));
                $phone = isset($r['contact_phone']) ? $r['contact_phone'] : (isset($r['phone']) ? $r['phone'] : '');
                $updated = isset($r['updated_at']) ? $r['updated_at'] : (isset($r['last_activity']) ? $r['last_activity'] : '');
                $preview = isset($r['last_message']) ? $r['last_message'] : (isset($r['preview']) ? $r['preview'] : '');
                // If preview is empty, try to pull a sensible snippet from metadata (safe decoding)
                if (empty($preview) && isset($r['metadata']) && $r['metadata']) {
                    $meta = null;
                    if (is_string($r['metadata'])) {
                        $meta = json_decode($r['metadata'], true);
                    } elseif (is_array($r['metadata'])) {
                        $meta = $r['metadata'];
                    }
                    if (is_array($meta)) {
                        // common locations
                        if (isset($meta['last_bot_response']) && $meta['last_bot_response']) $preview = $meta['last_bot_response'];
                        elseif (isset($meta['metaResponse']) && $meta['metaResponse']) $preview = (is_string($meta['metaResponse'])? $meta['metaResponse'] : (isset($meta['metaResponse']['text'])? $meta['metaResponse']['text'] : null));
                        elseif (isset($meta['history']) && is_array($meta['history']) && isset($meta['history'][0])) {
                            $h0 = $meta['history'][0];
                            if (is_array($h0) && isset($h0['text'])) $preview = $h0['text'];
                            else if (is_array($h0) && isset($h0['message'])) $preview = $h0['message'];
                            else if (isset($h0['from']) && isset($h0['body'])) $preview = $h0['body'];
                        } elseif (isset($meta['raw']) && is_array($meta['raw'])) {
                            if (isset($meta['raw']['text'])) $preview = $meta['raw']['text'];
                            else if (isset($meta['raw']['message'])) $preview = $meta['raw']['message'];
                        }
                    }
                    // final fallback: if still array, stringify small fields
                        if (is_array($preview)) $preview = json_encode($preview);
                    // Normalize whitespace and truncate preview for UI (WhatsApp-like snippet)
                    if (!empty($preview)) {
                        // replace any sequence of whitespace/newlines with single space
                        $preview = preg_replace('/\s+/u', ' ', trim($preview));
                        $preview_max = 60; // chars (short snippet)
                        if (mb_strlen($preview) > $preview_max) {
                            $preview = mb_substr($preview, 0, $preview_max - 3) . '...';
                        }
                    }
                }
                $out[] = array('id'=>$convId,'legacy_id'=>$legacyId,'title'=>$title,'phone'=>$phone,'updated_at'=>$updated,'last_message'=>$preview,'raw'=>$r);
            }
            header('Content-Type: application/json');
            echo json_encode(array('success'=>true,'data'=>$out));
            return;
        }

        header('Content-Type: application/json');
        echo json_encode(array('success'=>false,'message'=>$res['msg'] ?? 'No encontrado','data'=>array()));
    }

    /**
     * DEBUG ONLY: devuelve raw JSON del resultado de get_chats() sin validar sesión.
     * Uso: /Bot_Chat/debug_get_chats
     */
    public function debug_get_chats() {
        try {
            $obj = $this->load_model('Bot_Chat');
            $res = $obj->get_chats();
            header('Content-Type: application/json');
            echo json_encode(array('debug'=>true,'result'=>$res));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(array('debug'=>true,'error'=>$e->getMessage()));
        }
    }
    

    // Get messages for a conversation (AJAX)
    public function get_messages() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request !== 'GET') {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'message'=>'Método no permitido'));
            return;
        }
        $conversation_id = isset($_GET['conversation_id']) ? $_GET['conversation_id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        if (!$conversation_id) {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'message'=>'conversation_id requerido'));
            return;
        }
        $obj = $this->load_model('Bot_Chat');
        $requested = $conversation_id;
        $messages = $obj->get_messages($conversation_id);
        $tried = array($conversation_id);

        // NO marcar como leído aquí - se hará desde el frontend cuando los mensajes estén visibles

        // If no messages, try a couple of fallbacks (int cast, string cast) to detect mismatches
        if (empty($messages)) {
            // try integer form
            $alt1 = intval($conversation_id);
            if ($alt1 && $alt1 != $conversation_id) {
                $tried[] = $alt1;
                $messages = $obj->get_messages($alt1);
            }
        }

        // also return conversation meta (if available) so UI can show phone/title; try same fallbacks
        $conversation_meta = null;
        $conv = $obj->get_chat_by_id($conversation_id);
        if ((!is_array($conv) || !isset($conv['status']) || $conv['status'] !== 'OK') && !empty($tried[1])) {
            $conv = $obj->get_chat_by_id($tried[1]);
        }
        if (is_array($conv) && isset($conv['status']) && $conv['status'] === 'OK' && isset($conv['result'])) {
            $conversation_meta = $conv['result'];
        }

        header('Content-Type: application/json');
        echo json_encode(array('success'=>true,'requested'=>$requested,'tried'=>$tried,'data'=>$messages,'conversation'=>$conversation_meta));
    }

    // Send message
    public function send_message() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!empty($input) && (isset($input['chat_id']) || isset($input['conversation_id'])) && isset($input['message'])) {
                // Normalizar payload
                $payload = $input;
                if (!isset($payload['conversation_id']) && isset($payload['chat_id'])) $payload['conversation_id'] = $payload['chat_id'];
                // Normalizar direction
                if (!isset($payload['direction'])) $payload['direction'] = 'outgoing';
                
                // Añadir nombre completo del usuario que responde
                $session_data = $this->segment->get('data');
                $first_name = isset($session_data['first_name']) ? trim($session_data['first_name']) : '';
                $last_name = isset($session_data['last_name']) ? trim($session_data['last_name']) : '';

                // Construir nombre completo
                if ($first_name && $last_name) {
                    $user_name = $first_name . ' ' . $last_name;
                } elseif ($first_name) {
                    $user_name = $first_name;
                } elseif ($last_name) {
                    $user_name = $last_name;
                } else {
                    $user_name = 'Admin'; // Fallback
                }

                // Limitar a 100 caracteres por seguridad (first_name 45 + espacio 1 + last_name 45 = 91 máximo)
                $user_name = substr($user_name, 0, 100);

                $payload['responded_by_user_name'] = $user_name;

                $obj = $this->load_model('Bot_Chat');
                // Asegurar que haya phone en el payload para permitir reutilizar/conversación por número
                if ((empty($payload['phone']) || $payload['phone'] === '') && !empty($payload['conversation_id'])) {
                    $conv = $obj->get_chat_by_id($payload['conversation_id']);
                    if (is_array($conv) && isset($conv['status']) && $conv['status'] === 'OK' && isset($conv['result']['contact_phone'])) {
                        $payload['phone'] = $conv['result']['contact_phone'];
                    } else if (is_array($conv) && isset($conv['status']) && $conv['status'] === 'OK' && isset($conv['result']['phone'])) {
                        $payload['phone'] = $conv['result']['phone'];
                    }
                    
                    // Si aún no hay phone, intentar extraerlo del conversation_id (si es un número)
                    if (empty($payload['phone']) && preg_match('/^(\+?[0-9]{8,15})$/', $payload['conversation_id'], $matches)) {
                        $payload['phone'] = $matches[1];
                    }
                    
                    // Último recurso: si conversation_id parece un número de teléfono
                    if (empty($payload['phone'])) {
                        $cleaned = preg_replace('/[^0-9]/', '', $payload['conversation_id']);
                        if (strlen($cleaned) >= 8 && strlen($cleaned) <= 15) {
                            $payload['phone'] = $cleaned;
                        }
                    }
                }
                
                // Si después de todo no hay phone, retornar error
                if (empty($payload['phone'])) {
                    $json = array('status'=>'ERROR','type'=>'error','msg'=>'No se pudo determinar el número de teléfono del destinatario');
                    header('Content-Type: application/json');
                    echo json_encode($json);
                    return;
                }

                $response = $obj->send_message($payload);
                if ($response['status'] === 'OK') {
                    $json = array('status'=>'OK','type'=>'success','msg'=>$response['msg'] ?? 'Mensaje enviado.');
                } else {
                    $json = array('status'=>'ERROR','type'=>'error','msg'=>$response['msg'] ?? 'Error al enviar');
                }
            } else {
                $json = array('status'=>'ERROR','type'=>'error','msg'=>'Parámetros requeridos');
            }
        } else {
            $json = array('status'=>'ERROR','type'=>'error','msg'=>'Método no permitido.');
        }
        header('Content-Type: application/json');
        echo json_encode($json);
    }

    // DEBUG: return raw rows from bot_messages for a given conversation_id
    public function get_messages_raw() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request !== 'GET') {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'message'=>'Método no permitido'));
            return;
        }
        $conversation_id = isset($_GET['conversation_id']) ? $_GET['conversation_id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        $after_id = isset($_GET['after_id']) ? $_GET['after_id'] : null;
        
        if (!$conversation_id) {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'message'=>'conversation_id requerido'));
            return;
        }
        
        $obj = $this->load_model('Bot_Chat');
        
        try {
            // ============================================================
            // USAR EL MÉTODO DEL MODELO QUE HACE JOIN CON bot_media_files
            // ============================================================
            $rows = $obj->get_messages($conversation_id, $after_id);
            
            // ============================================================
            // AUTO-DESCARGAR MEDIAS PENDIENTES SI ES LA PRIMERA CARGA (sin after_id)
            // ============================================================
            if (!$after_id) {
                $this->_auto_download_pending_media($conversation_id);
            }
            
            header('Content-Type: application/json');
            echo json_encode(array('success'=>true,'count'=>count($rows),'rows'=>$rows));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'error'=>$e->getMessage()));
        }
    }

    // Return server-rendered HTML for a conversation's messages (safe fallback for client)
    public function get_messages_html() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request !== 'GET') {
            header('Content-Type: text/html');
            echo '<div class="text-muted p-3">Método no permitido</div>';
            return;
        }
        $conversation_id = isset($_GET['conversation_id']) ? $_GET['conversation_id'] : (isset($_GET['id']) ? $_GET['id'] : null);
        if (!$conversation_id) {
            header('Content-Type: text/html');
            echo '<div class="text-muted p-3">conversation_id requerido</div>';
            return;
        }
        $obj = $this->load_model('Bot_Chat');
        $rows = $obj->get_messages($conversation_id);
        
        // ============================================================
        // AUTO-DESCARGAR MEDIAS PENDIENTES DE ESTA CONVERSACIÓN
        // ============================================================
        $this->_auto_download_pending_media($conversation_id);
        
        // ============================================================
        // MARCAR MENSAJES COMO LEÍDOS
        // ============================================================
        $this->_mark_messages_as_read($conversation_id);
        
        // DEBUG FORZADO: Escribir a archivo para ver qué pasa
        $debug_info = "=== DEBUG get_messages_html ===\n";
        // DEBUG deshabilitado - descomentar solo para debugging local
        // $debug_info .= "Timestamp: " . date('Y-m-d H:i:s') . "\n";
        // $debug_info .= "conversation_id: " . $conversation_id . "\n";
        // $debug_info .= "Rows es array: " . (is_array($rows) ? 'SI' : 'NO') . "\n";
        // $debug_info .= "Count: " . (is_array($rows) ? count($rows) : 'N/A') . "\n";
        // if (is_array($rows) && count($rows) > 0) {
        //     $debug_info .= "Primera fila keys: " . implode(', ', array_keys($rows[0])) . "\n";
        //     $debug_info .= "Primera fila message: " . (isset($rows[0]['message']) ? substr($rows[0]['message'], 0, 50) : 'NO MESSAGE') . "\n";
        // }
        // $debug_info .= "\n";
        // file_put_contents(__DIR__ . '/../../debug_messages.log', $debug_info, FILE_APPEND);
        
        // DEBUG: Ver qué retorna
        if (isset($_GET['debug'])) {
            header('Content-Type: text/plain');
            echo "conversation_id: " . $conversation_id . "\n";
            echo "Rows count: " . (is_array($rows) ? count($rows) : 'NOT ARRAY') . "\n";
            if (is_array($rows) && count($rows) > 0) {
                echo "First row: " . print_r($rows[0], true);
            }
            return;
        }
        
        header('Content-Type: text/html');
        if (!$rows || count($rows) === 0) {
            echo '<div class="text-center text-muted p-4">No hay mensajes en esta conversación aún.</div>';
            return;
        }
        // build simple HTML bubbles server-side (older incorrect concatenation removed)
        // Rebuild properly
        $html = '';
        foreach ($rows as $r) {
            $dir = isset($r['direction']) ? $r['direction'] : 'incoming';
            $cls = ($dir === 'outgoing' || $dir === 'sent') ? 'message-out' : 'message-in';
            $time = isset($r['created_at']) ? $r['created_at'] : '';
            
            // sanitize message: remove leading unicode replacement characters and stray '?' that may appear before formatting
            $rawMsg = '';
            if (isset($r['message'])) $rawMsg = $r['message'];
            // Remove Unicode replacement char U+FFFD at start
            $rawMsg = preg_replace('/^\x{FFFD}+/u', '', $rawMsg);
            // Remove leading '?' followed by space and then a punctuation or formatting marker like '*'
            $rawMsg = preg_replace('/^\?\s+(?=[^0-9A-Za-z])/u', '', $rawMsg);
            // Remove leading '?' immediately before a '*' (no space)
            $rawMsg = preg_replace('/^\?(?=\*)/u', '', $rawMsg);
            $rawMsg = trim($rawMsg);
            
            // Detectar tipo de mensaje
            $messageType = isset($r['message_type']) ? strtolower($r['message_type']) : '';
            $imageUrl = null;
            
            // ============================================================
            // PRIORIDAD 1: Información de media desde JOIN con bot_media_files
            // ============================================================
            // Soporta dos formas de retorno desde el modelo:
            //  - filas con columnas planas (media_file_id, filename, mime_type, media_type)
            //  - filas normalizadas que incluyen un array 'media' con esa información
            $filename_candidate = null;
            $mime = isset($r['mime_type']) ? $r['mime_type'] : null;
            $media_type_val = isset($r['media_type']) ? $r['media_type'] : null;

            if (isset($r['media']) && is_array($r['media']) && !empty($r['media'])) {
                $m = $r['media'];
                if (isset($m['filename']) && $m['filename']) $filename_candidate = $m['filename'];
                if (isset($m['mime_type']) && $m['mime_type']) $mime = $m['mime_type'];
                if (isset($m['media_type']) && $m['media_type']) $media_type_val = $m['media_type'];
                // expose convenience top-level fields for legacy code paths
                if (!isset($r['filename']) && isset($m['filename'])) $r['filename'] = $m['filename'];
                if (!isset($r['mime_type']) && isset($m['mime_type'])) $r['mime_type'] = $m['mime_type'];
                if (!isset($r['media_type']) && isset($m['media_type'])) $r['media_type'] = $m['media_type'];
                if (!isset($r['media_file_id']) && isset($m['media_file_id'])) $r['media_file_id'] = $m['media_file_id'];
            }

            // legacy flat fields fallback
            if (!$filename_candidate && isset($r['filename']) && $r['filename']) {
                $filename_candidate = $r['filename'];
            }
            
            // Si el modelo retornó campos planos de JOIN con bot_media_files, usarlos
            if (!$filename_candidate && isset($r['media_file_id']) && $r['media_file_id']) {
                // prioridad a filename de la BD
                if (isset($r['filename']) && $r['filename']) {
                    $filename_candidate = $r['filename'];
                }
            }

            if ($filename_candidate) {
                // Verificar si el archivo existe localmente (primero en files/MEDIA con nombre exacto)
                $local_path = __DIR__ . '/../../files/MEDIA/' . $filename_candidate;

                // Si no tiene extensión, intentar agregarla según mime_type
                if (!file_exists($local_path) && !preg_match('/\.\w+$/', $filename_candidate)) {
                    $ext_map = [
                        'image/jpeg' => '.jpg', 'image/jpg' => '.jpg', 'image/png' => '.png',
                        'image/gif' => '.gif', 'image/webp' => '.webp', 'application/pdf' => '.pdf',
                        'video/mp4' => '.mp4', 'audio/mpeg' => '.mp3', 'audio/ogg' => '.ogg'
                    ];
                    if ($mime && isset($ext_map[$mime])) {
                        $filename_with_ext = $filename_candidate . $ext_map[$mime];
                        $local_path_with_ext = __DIR__ . '/../../files/MEDIA/' . $filename_with_ext;
                        if (file_exists($local_path_with_ext)) {
                            $local_path = $local_path_with_ext;
                            $r['filename'] = $filename_with_ext;
                            $filename_candidate = $filename_with_ext;
                        }
                    }
                }

                if (file_exists($local_path)) {
                    // Archivo existe: usar ruta relativa web
                    $imageUrl = 'files/MEDIA/' . $filename_candidate;
                    $messageType = $media_type_val ? strtolower($media_type_val) : 'image';
                } else {
                    // Archivo no existe en files/MEDIA: no mostrar imagen rota
                    $imageUrl = null;
                }
            }
            
            // ============================================================
            // PRIORIDAD 2: Si message_type es 'image', buscar URL en payload o message
            // ============================================================
            if (!$imageUrl && $messageType === 'image') {
                // Intentar extraer URL del payload (JSON)
                if (isset($r['payload']) && $r['payload']) {
                    $payload = json_decode($r['payload'], true);
                    if (is_array($payload)) {
                        // Buscar URL en diferentes estructuras
                        $imageUrl = $payload['media_url'] ?? $payload['image_url'] ?? $payload['url'] ?? 
                                   $payload['attachment_url'] ?? $payload['file_url'] ?? null;
                        
                        if (!$imageUrl && isset($payload['media']['url'])) {
                            $imageUrl = $payload['media']['url'];
                        }
                        if (!$imageUrl && isset($payload['message']['image']['url'])) {
                            $imageUrl = $payload['message']['image']['url'];
                        }
                    }
                    
                    // Si payload no es JSON válido pero contiene URL
                    if (!$imageUrl && is_string($r['payload']) && strpos($r['payload'], 'http') !== false) {
                        if (preg_match('/(https?:\/\/[^\s"\']+\.(jpg|jpeg|png|gif|webp|bmp)[^\s"\']*)/i', $r['payload'], $matches)) {
                            $imageUrl = $matches[0];
                        }
                    }
                }
                
                // Si no hay en payload, usar el mensaje como URL
                if (!$imageUrl && preg_match('/^https?:\/\//i', $rawMsg)) {
                    $imageUrl = $rawMsg;
                }
            }
            
            // ============================================================
            // PRIORIDAD 3: Si el mensaje parece ser una URL de imagen (detectar por extensión)
            // ============================================================
            if (!$imageUrl && preg_match('/^https?:\/\/.*\.(jpg|jpeg|png|gif|webp|bmp)(\?.*)?$/i', $rawMsg)) {
                $imageUrl = $rawMsg;
                $messageType = 'image';
            }
            
            // ============================================================
            // PRIORIDAD 4: Si hay URL de imagen en alguna parte del mensaje
            // ============================================================
            if (!$imageUrl && strpos($rawMsg, 'http') !== false) {
                if (preg_match('/(https?:\/\/[^\s]+\.(jpg|jpeg|png|gif|webp|bmp)(\?[^\s]*)?)/i', $rawMsg, $matches)) {
                    $imageUrl = $matches[0];
                    $messageType = 'image';
                }
            }
            
            $messageContent = '';
            
            // Renderizar según el tipo
            if ($messageType === 'image' && $imageUrl) {
                $imgSrcEscaped = htmlspecialchars($imageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                
                // Imagen clickeable con modal (no abre nueva ventana)
                $messageContent = '<img src="' . $imgSrcEscaped . '" alt="Imagen" style="max-width: 280px; border-radius: 10px; display: block; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.12);" onclick="openImageModal(\'' . $imgSrcEscaped . '\')" onerror="this.style.display=\'none\'; this.parentNode.innerHTML += \'<div style=\\\'color:red;\\\'>[Imagen no disponible]</div>\';">';
                
                // SOLO mostrar caption si hay texto y NO es la palabra "[imagen]" ni la URL
                if ($rawMsg && $rawMsg !== $imageUrl && $rawMsg !== '[imagen]' && !preg_match('/^https?:\/\//i', $rawMsg)) {
                    $messageContent .= '<div style="margin-top: 6px;">' . htmlspecialchars($rawMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</div>';
                }
            }
            // Texto normal
            else {
                $messageContent = htmlspecialchars($rawMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
            
            // Obtener el ID del mensaje para el atributo data-msg-id
            $msgId = isset($r['id']) ? $r['id'] : (isset($r['message_id']) ? $r['message_id'] : '');
            
            $html .= '<div class="chat-message '. $cls . '" data-msg-id="' . htmlspecialchars($msgId, ENT_QUOTES) . '"><div class="message-body">'. $messageContent .'<div class="message-time">'. $time .'</div></div></div>';
        }
        echo $html;
    }

    /**
     * Cleanup leading artefacts in bot_messages: remove leading U+FFFD, leading '?' or '¿' artifacts.
     * This is a destructive operation: it will backup affected rows into bot_messages_backup before updating.
     * To run: access /Bot_Chat/clean_message_artifacts?confirm=1 while logged in. The method requires session validation.
     */
    public function clean_message_artifacts() {
        $this->functions->validate_session($this->segment->get('isActive'));
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request !== 'GET') {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'msg'=>'Método no permitido'));
            return;
        }
        if (!isset($_GET['confirm']) || $_GET['confirm'] != '1') {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'msg'=>'Confirme la operación pasando ?confirm=1')); 
            return;
        }

        $obj = $this->load_model('Bot_Chat');
        $pdo = $obj->pdo;

        try {
            // create backup table if not exists
            $pdo->perform("CREATE TABLE IF NOT EXISTS bot_messages_backup LIKE bot_messages", array());

            // fetch candidate rows (heuristic: starting with ? or inverted or replacement char)
            $rows = $pdo->fetchAll("SELECT id, message FROM bot_messages WHERE message LIKE '?%' OR message LIKE '¿%' OR HEX(LEFT(message,3)) = 'EFBFBD' LIMIT 2000");
            $changed = array();
            foreach ($rows as $r) {
                $id = $r['id'];
                $orig = isset($r['message']) ? $r['message'] : '';
                $msg = $orig;
                // PHP sanitization similar to server render
                // remove BOM
                $msg = preg_replace('/^\x{FEFF}/u', '', $msg);
                // remove unicode replacement char at start
                $msg = preg_replace('/^\x{FFFD}+/u', '', $msg);
                // remove leading sequences of ? or ¿ and spaces when followed by formatting or punctuation
                $msg = preg_replace('/^[\?¿\s]+(?=\*)/u', '', $msg);
                $msg = preg_replace('/^[\?¿\s]+(?=[^0-9A-Za-zÀ-ÿ])/u', '', $msg);
                // fallback: remove leading ? or ¿ plus spaces
                $msg = preg_replace('/^[\?¿]+\s*/u', '', $msg);
                $msg = trim($msg);
                if ($msg !== $orig) {
                    // backup row
                    $pdo->perform("INSERT INTO bot_messages_backup SELECT * FROM bot_messages WHERE id = :id", array('id'=>$id));
                    // update message
                    $pdo->perform("UPDATE bot_messages SET message = :m WHERE id = :id", array('m'=>$msg, 'id'=>$id));
                    $changed[] = $id;
                }
            }
            header('Content-Type: application/json');
            echo json_encode(array('success'=>true,'checked'=>count($rows),'changed'=>count($changed),'ids'=>$changed));
            return;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(array('success'=>false,'error'=>$e->getMessage()));
            return;
        }
    }

    /**
     * Descarga automáticamente medias pendientes de una conversación específica
     * Se ejecuta al cargar mensajes para garantizar que las imágenes estén disponibles instantáneamente
     */
    private function _auto_download_pending_media($conversation_id) {
        try {
            // Configuración de WhatsApp API
            define('WHATSAPP_ACCESS_TOKEN_DOWNLOAD', 'EAAK4zXMim2UBPazxjiuurWRyCamL4rpHDPCrW4dyPanGYsEtG2Alm9SPCOCEVYFfdJeFSp4wBtnLWDTY4HMkLDfiV3lOxjE8FcR68ZA570LarD9cHpjCWKPOzwShw1eyoJY1lZB6ZC6WeMrLBu7IAaTuyS6sB5ABMZBznUtF2ZASCkp0YAp5RMsLbgcyJ7WGBzgZDZD');
            define('MEDIA_DIR_DOWNLOAD', __DIR__ . '/../../files/MEDIA/');
            
            // Crear directorio si no existe
            if (!is_dir(MEDIA_DIR_DOWNLOAD)) {
                mkdir(MEDIA_DIR_DOWNLOAD, 0755, true);
            }
            
            // Conectar a BD
            $pdo = new PDO('mysql:host=localhost;dbname=db_gliese;charset=utf8mb4', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Obtener medias pendientes de esta conversación
            $sql = "SELECT mf.id, mf.media_id, mf.media_type, mf.mime_type, mf.filename, mf.phone 
                    FROM bot_media_files mf
                    INNER JOIN bot_messages m ON m.provider_message_id COLLATE utf8mb4_unicode_ci = mf.message_id COLLATE utf8mb4_unicode_ci
                    WHERE m.conversation_id = :cid 
                    AND mf.media_id IS NOT NULL 
                    AND mf.filename IS NOT NULL
                    ORDER BY mf.id DESC
                    LIMIT 20";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array('cid' => $conversation_id));
            $media_files = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($media_files)) {
                return; // No hay medias en esta conversación
            }
            
            $downloaded = 0;
            
            foreach ($media_files as $file) {
                // Determinar extensión
                $extension = '';
                if ($file['mime_type']) {
                    $mime_map = array(
                        'image/jpeg' => '.jpg',
                        'image/jpg' => '.jpg',
                        'image/png' => '.png',
                        'image/gif' => '.gif',
                        'image/webp' => '.webp'
                    );
                    $extension = isset($mime_map[$file['mime_type']]) ? $mime_map[$file['mime_type']] : '';
                }
                
                $filename_with_ext = $file['filename'];
                if ($extension && !preg_match('/\.\w+$/', $filename_with_ext)) {
                    $filename_with_ext .= $extension;
                }
                
                $local_file_path = MEDIA_DIR_DOWNLOAD . $filename_with_ext;
                
                // Si ya existe, saltar
                if (file_exists($local_file_path)) {
                    continue;
                }
                
                // Descargar desde WhatsApp API
                // PASO 1: Obtener URL temporal
                $media_url_api = "https://graph.facebook.com/v21.0/{$file['media_id']}";
                
                $ch = curl_init($media_url_api);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . WHATSAPP_ACCESS_TOKEN_DOWNLOAD));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5); // timeout rápido para no bloquear UI
                
                $response = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code !== 200 || empty($response)) {
                    continue; // Error al obtener URL, saltar
                }
                
                $media_info = json_decode($response, true);
                if (!isset($media_info['url'])) {
                    continue;
                }
                
                $media_download_url = $media_info['url'];
                
                // PASO 2: Descargar archivo
                $ch = curl_init($media_download_url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . WHATSAPP_ACCESS_TOKEN_DOWNLOAD));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                
                $file_content = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($http_code !== 200 || empty($file_content)) {
                    continue;
                }
                
                // PASO 3: Guardar archivo
                $bytes_written = file_put_contents($local_file_path, $file_content);
                
                if ($bytes_written !== false) {
                    $downloaded++;
                }
            }
            
            // Log silencioso del resultado (opcional)
            if ($downloaded > 0) {
                error_log("[BOT_CHAT] Auto-descargadas {$downloaded} imágenes para conversación {$conversation_id}");
            }
            
        } catch (Exception $e) {
            // Error silencioso, no interrumpir el flujo de mensajes
            error_log("[BOT_CHAT] Error en auto-descarga: " . $e->getMessage());
        }
    }

    /**
     * Marca todos los mensajes entrantes de una conversación como leídos
     * 
     * @param string $conversation_id ID de la conversación
     */
    private function _mark_messages_as_read($conversation_id) {
        if (!$conversation_id) return;
        
        try {
            $obj = $this->load_model('Bot_Chat');
            $obj->mark_as_read($conversation_id);
            
        } catch (Exception $e) {
            // Error silencioso
            error_log("[BOT_CHAT] Error marcando mensajes como leídos: " . $e->getMessage());
        }
    }

    // ========================================================================
    // ENDPOINT: Toggle Agent Mode (Bot ON/OFF)
    // ========================================================================
    
    /**
     * Activa o desactiva el modo agente para una conversación
     * POST: { "conversation_id": "51974444116", "agent_mode": true/false }
     * Respuesta: { "status": "OK", "agent_mode": true/false, "msg": "..." }
     */
    public function toggle_agent_mode() {
        $this->functions->validate_session($this->segment->get('isActive'));
        
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'POST') {
            $payload = json_decode(file_get_contents('php://input'), true);
            
            if (isset($payload['conversation_id'])) {
                $conversation_id = $payload['conversation_id'];
                $agent_mode = isset($payload['agent_mode']) ? (bool)$payload['agent_mode'] : false;
                
                $obj = $this->load_model('Bot_Chat');
                $response = $obj->toggle_agent_mode($conversation_id, $agent_mode);
                
                header('Content-Type: application/json');
                echo json_encode($response);
            } else {
                header('Content-Type: application/json');
                echo json_encode(array('status'=>'ERROR', 'msg'=>'conversation_id requerido'));
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(array('status'=>'ERROR', 'msg'=>'Método no permitido'));
        }
    }
    
    /**
     * Obtiene el estado actual del agent_mode para una conversación
     * GET: ?conversation_id=51974444116
     * Respuesta: { "status": "OK", "agent_mode": true/false }
     */
    public function get_agent_mode() {
        $this->functions->validate_session($this->segment->get('isActive'));
        
        $request = $_SERVER['REQUEST_METHOD'];
        if ($request === 'GET') {
            $conversation_id = isset($_GET['conversation_id']) ? $_GET['conversation_id'] : null;
            
            if ($conversation_id) {
                $obj = $this->load_model('Bot_Chat');
                $response = $obj->get_agent_mode($conversation_id);
                
                header('Content-Type: application/json');
                echo json_encode($response);
            } else {
                header('Content-Type: application/json');
                echo json_encode(array('status'=>'ERROR', 'agent_mode'=>false));
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(array('status'=>'ERROR', 'msg'=>'Método no permitido'));
        }
    }

    // ========================================================================
    // ENDPOINT: Marcar mensajes como leídos (llamado desde el frontend)
    // ========================================================================

    /**
     * Marca los mensajes entrantes de una conversación como leídos
     * Se llama desde el frontend SOLO cuando los mensajes están visibles en pantalla
     * POST: { "conversation_id": "51974444116" }
     * Respuesta: { "status": "OK", "marked": número_de_mensajes_marcados }
     */
    public function mark_messages_read() {
        $this->functions->validate_session($this->segment->get('isActive'));

        $request = $_SERVER['REQUEST_METHOD'];
        if ($request !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(array('status'=>'ERROR', 'msg'=>'Método no permitido'));
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $conversation_id = isset($input['conversation_id']) ? $input['conversation_id'] : null;

        if (!$conversation_id) {
            header('Content-Type: application/json');
            echo json_encode(array('status'=>'ERROR', 'msg'=>'conversation_id requerido'));
            return;
        }

        try {
            $obj = $this->load_model('Bot_Chat');
            $result = $obj->mark_as_read($conversation_id);

            header('Content-Type: application/json');
            echo json_encode(array('status'=>'OK', 'marked'=>$result));
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(array('status'=>'ERROR', 'msg'=>$e->getMessage()));
        }
    }

}

