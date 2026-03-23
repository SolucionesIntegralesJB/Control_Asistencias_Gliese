/**
 * ========================================================================
 * BOT CHAT - INTERFAZ PRINCIPAL DE CONVERSACIONES
 * ========================================================================
 * 
 * Archivo: index.js
 * Propósito: Manejo de la interfaz de chat del módulo Bot_Chat
 * 
 * Funcionalidades principales:
 * - Carga y renderiza lista de conversaciones activas
 * - Muestra hilos de mensajes (vista HTML y JSON)
 * - Polling automático para actualizaciones en tiempo real
 * - Modo agente para respuestas de operador humano
 * - Inyección del modal de configuración de eventos
 * - Manejo de formularios de envío de mensajes
 * 
 * Dependencias:
 * - jQuery
 * - Bootstrap 5 (para modales)
 * - Font Awesome (iconos)
 * 
 * Variables globales:
 * - BASE_URL o base_url: URL base del proyecto
 * 
 * ========================================================================
 */

(function($){
  console.log('🚀 [BOT_CHAT] Script cargado - versión DEBUG');
  
  // ========================================================================
  // RESOLUCIÓN DE BASE_URL
  // ========================================================================
  
  /**
   * Detecta la URL base del proyecto para construir endpoints
   * Intenta usar variable global BASE_URL, base_url, o detectar de la URL actual
   */
  var base = '/';
  if (typeof BASE_URL !== 'undefined' && BASE_URL) {
    base = BASE_URL;
  } else if (typeof base_url !== 'undefined' && base_url) {
    base = base_url;
  } else {
    // Intentar detectar index.php en la ruta actual para apps montadas en subcarpetas
    try {
      var p = window.location.pathname || '/';
      var idx = p.indexOf('/index.php');
      if (idx !== -1) {
        base = p.substring(0, idx + '/index.php'.length) + '/';
      } else {
        // Fallback: raíz de la ruta actual
        base = p.replace(/\/[^\/]*$/, '/') ;
      }
    } catch (e) { 
      base = '/'; 
    }
  }
  
  // Asegurar que termine con /
  if (base.charAt(base.length-1) !== '/') base += '/';

  // ========================================================================
  // CONFIGURACIÓN DE ENDPOINTS
  // ========================================================================
  var conversationsUrl = base + 'Bot_Chat/get_chats';       // Lista de conversaciones
  var messagesUrl = base + 'Bot_Chat/get_messages';         // Mensajes JSON + metadata
  var messagesRawUrl = base + 'Bot_Chat/get_messages_raw'; // Mensajes JSON raw
  var messagesHtmlUrl = base + 'Bot_Chat/get_messages_html'; // Mensajes HTML renderizados
  
  console.debug('Bot Chat: Endpoints resueltos', {
    conversationsUrl: conversationsUrl, 
    messagesUrl: messagesUrl, 
    messagesRawUrl: messagesRawUrl, 
    messagesHtmlUrl: messagesHtmlUrl
  });

  // ========================================================================
  // PREVENIR INICIALIZACIÓN MÚLTIPLE
  // ========================================================================
  if (window.botChatInitialized) {
    console.warn('[BOT_CHAT] Ya está inicializado, saliendo para evitar duplicados');
    return;
  }
  window.botChatInitialized = true;
  console.log('[BOT_CHAT] Inicializando módulo Bot Chat');

  // ========================================================================
  // VARIABLES GLOBALES DEL MÓDULO
  // ========================================================================
  var pollingInterval = 2000;          // Intervalo de polling en milisegundos (2 segundos para respuesta rápida)
  var conversationsPollingInterval = 5000; // Polling de lista de conversaciones (5 segundos)
  var agentModePollingInterval = 2000; // Polling de agent_mode (2 segundos para detección en tiempo real)
  var currentConversation = null;      // ID de la conversación actualmente seleccionada
  var pollTimer = null;                // Timer para el polling automático de mensajes
  var conversationsPollTimer = null;   // Timer para el polling de lista de conversaciones
  var agentModePollTimer = null;       // Timer para el polling de agent_mode en tiempo real
  var autoRefreshEnabled = true;       // Control de polling (ON por defecto para actualizaciones en tiempo real)
  var agentMode = false;               // Modo agente: operador humano respondiendo
  var DEBUG_DUMP = false;              // Debug: volcar respuesta raw en lugar de renderizar UI
  var lastMessageId = null;            // ID del último mensaje cargado (para carga incremental)
  var userIsScrollingUp = false;       // Detectar si el usuario está viendo mensajes antiguos
  var newMessagesCount = 0;            // Contador de mensajes nuevos sin leer
  var escPressCount = 0;               // Contador de veces que se presiona ESC (para ocultar panel)

  // ========================================================================
  // FUNCIONES HELPER
  // ========================================================================

  /**
   * Limpia TODOS los timers de polling activos
   * Previene timers duplicados y memoria perdida
   */
  function clearAllPolling() {
    if (pollTimer) {
      clearInterval(pollTimer);
      pollTimer = null;
    }
    // NO limpiar conversationsPollTimer ni agentModePollTimer aquí
    // porque son globales y se mantienen durante toda la sesión
    console.log('[POLLING] Timers de mensajes limpiados');
  }

  /**
   * Escapa caracteres HTML para prevenir XSS
   * @param {string} s - Texto a escapar
   * @returns {string} Texto con entidades HTML
   */
  function escapeHtml(s){
    return String(s||'').replace(/[&<>"']/g, function(c){
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
    });
  }

  /**
   * Hace scroll automático del contenedor de chat hacia el final
   * Muestra los mensajes más recientes
   * SOLO hace scroll si el usuario NO está viendo mensajes antiguos
   * @param {jQuery} $container - Contenedor de mensajes
   * @param {boolean} force - Forzar scroll aunque el usuario esté arriba
   */
  function scrollChatToBottom($container, force){
    try{
      if (!$container || !$container.length) return;
      
      // Si el usuario está scrolleando arriba y no es forzado, NO hacer scroll
      if (userIsScrollingUp && !force) {
        return;
      }
      
      // Timeout pequeño para permitir que el DOM se actualice (imágenes, contenido async)
      setTimeout(function(){
        try{ 
          $container.scrollTop($container[0].scrollHeight);
          userIsScrollingUp = false; // Reset después de hacer scroll
          newMessagesCount = 0; // Reset contador
          hideNewMessagesButton(); // Ocultar botón
        }catch(e){}
      }, 60);
    }catch(e){}
  }

  /**
   * Detecta si el usuario está al final del chat o está scrolleando arriba
   * @param {jQuery} $container - Contenedor de mensajes
   * @returns {boolean} true si el usuario está al final
   */
  function isUserAtBottom($container) {
    try {
      if (!$container || !$container.length) return true;
      var scrollTop = $container.scrollTop();
      var scrollHeight = $container[0].scrollHeight;
      var clientHeight = $container.height();
      
      // Considerar que está al final si está a menos de 100px del fondo
      return (scrollHeight - scrollTop - clientHeight) < 100;
    } catch(e) {
      return true;
    }
  }

  /**
   * Muestra el botón de "nuevos mensajes" flotante
   */
  function showNewMessagesButton(count) {
    var btn = $('#btn-new-messages');
    if (!btn.length) {
      // Crear botón si no existe
      var container = $('#chat-messages');
      var buttonHtml = '<button id="btn-new-messages" style="position: absolute; bottom: 20px; right: 20px; z-index: 1000; background: #28c76f; color: white; border: none; border-radius: 50px; padding: 12px 20px; box-shadow: 0 4px 12px rgba(40,199,111,0.4); cursor: pointer; font-weight: 600; display: none; transition: all 0.3s ease;">' +
                        '<i class="fas fa-arrow-down"></i> <span id="new-msg-count">0</span> nuevo(s)</button>';
      container.parent().css('position', 'relative').append(buttonHtml);
      btn = $('#btn-new-messages');
      
      // Click en el botón: hacer scroll al final
      btn.on('click', function(){
        var container = $('#chat-messages');
        scrollChatToBottom(container, true); // Forzar scroll
      });
    }
    
    $('#new-msg-count').text(count);
    btn.fadeIn(300);
  }

  /**
   * Oculta el botón de nuevos mensajes
   */
  function hideNewMessagesButton() {
    var btn = $('#btn-new-messages');
    if (btn.length) {
      btn.fadeOut(300);
    }
  }

  /**
   * Marca una conversación como leída
   * Se llama SOLO cuando los mensajes están completamente renderizados en pantalla
   * @param {string} conversationId - ID de la conversación
   */
  function markConversationAsRead(conversationId) {
    if (!conversationId) return;

    console.log('[MARK_READ] Marcando conversación como leída:', conversationId);

    $.ajax({
      url: base + 'Bot_Chat/mark_messages_read',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ conversation_id: conversationId }),
      success: function(resp) {
        if (resp && resp.status === 'OK') {
          console.log('[MARK_READ] ✓ Mensajes marcados como leídos');
          // Actualizar estadísticas para reflejar los cambios
          updateStats();
        } else {
          console.warn('[MARK_READ] Error al marcar como leídos:', resp.msg);
        }
      },
      error: function(xhr, status, error) {
        console.error('[MARK_READ] Error en la petición:', error);
      }
    });
  }

  /**
   * Renderiza un mensaje individual y retorna el HTML
   * @param {Object} m - Objeto mensaje con datos
   * @returns {string} HTML del mensaje renderizado
   */
  function renderMessage(m) {
    var dir = m.direction || 'incoming';
    var cls = (dir === 'outgoing' || dir === 'sent') ? 'message-out' : 'message-in';
    
    // Limpiar caracteres BOM y espacios
    var msg = String(m.message || '').replace(/^\uFEFF+/, '').replace(/^\uFFFD+/, '').trim();
    var time = m.created_at || '';
    var messageContent = '';
    
    // Detectar tipo de mensaje
    var messageType = (m.message_type || '').toLowerCase();
    
    // Intentar extraer URL de imagen de diferentes fuentes
    var imageUrl = null;
    
    // ============================================================
    // 1. PRIORIDAD: Información de media desde JOIN con bot_media_files
    // ============================================================
    if (m.media && m.media.media_file_id) {
      // Usar URL directa si existe (prioridad más alta)
      if (m.media.url) {
        imageUrl = m.media.url;
        messageType = (m.media.media_type || '').toLowerCase() || 'image';
      }
      // Usar file_path si existe
      else if (m.media.file_path) {
        // file_path viene como: uploads/images/xxx.jpg
        // Construir URL completa: /gliese/uploads/images/xxx.jpg
        imageUrl = '/gliese/' + m.media.file_path;
        messageType = (m.media.media_type || '').toLowerCase() || 'image';
      } 
      // Construir desde filename como fallback
      else if (m.media.filename) {
        // Fallback: construir URL desde filename (caso legacy)
        imageUrl = '/gliese/files/MEDIA/' + m.media.filename;
        messageType = (m.media.media_type || '').toLowerCase() || 'image';
      }
    }
    
    // ============================================================
    // 2. Fallback: Si message_type es 'image', buscar URL en payload o message
    // ============================================================
    if (!imageUrl && messageType === 'image') {
      // Intentar parsear payload como JSON
      if (m.payload) {
        try {
          var payload = typeof m.payload === 'string' ? JSON.parse(m.payload) : m.payload;
          // Buscar URL en diferentes estructuras de payload
          imageUrl = payload.media_url || payload.image_url || payload.url || 
                    payload.attachment_url || payload.file_url ||
                    (payload.media && payload.media.url) ||
                    (payload.message && payload.message.image && payload.message.image.url);
        } catch(e) {
          // Si falla el parse, intentar como string
          if (typeof m.payload === 'string' && m.payload.indexOf('http') !== -1) {
            var urlMatch = m.payload.match(/(https?:\/\/[^\s"']+\.(jpg|jpeg|png|gif|webp|bmp)[^\s"']*)/i);
            if (urlMatch) imageUrl = urlMatch[0];
          }
        }
      }
      
      // Si no hay en payload, usar el mensaje como URL
      if (!imageUrl && msg && msg.match(/^https?:\/\//i)) {
        imageUrl = msg;
      }
    }
    
    // ============================================================
    // 3. Si el mensaje parece ser una URL de imagen (sin importar message_type)
    // ============================================================
    if (!imageUrl && msg.match(/^https?:\/\/.*\.(jpg|jpeg|png|gif|webp|bmp)(\?.*)?$/i)) {
      imageUrl = msg;
      messageType = 'image'; // Forzar tipo imagen
    }
    
    // ============================================================
    // 4. Si hay URL en el mensaje pero no es el mensaje completo
    // ============================================================
    if (!imageUrl && msg.indexOf('http') !== -1) {
      var urlMatch = msg.match(/(https?:\/\/[^\s]+\.(jpg|jpeg|png|gif|webp|bmp)(\?[^\s]*)?)/i);
      if (urlMatch) {
        imageUrl = urlMatch[0];
        messageType = 'image';
      }
    }
    
    // ============================================================
    // RENDERIZADO según el tipo
    // ============================================================
    if (messageType === 'image' && imageUrl) {
      messageContent = '<img src="' + escapeHtml(imageUrl) + '" alt="Imagen" style="max-width: 280px; border-radius: 10px; display: block; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.12);" onclick="openImageModal(\'' + escapeHtml(imageUrl) + '\')" onerror="this.style.display=\'none\'; this.parentNode.innerHTML += \'<div style=\\\'color:red;\\\'>[Imagen no disponible]</div>\';">';
      
      // Si hay caption además de la imagen (mensaje diferente a la URL)
      if (msg && msg !== imageUrl && !msg.match(/^https?:\/\//i)) {
        messageContent += '<div style="margin-top: 6px;">' + $('<div>').text(msg).html() + '</div>';
      }
    } 
    // Texto normal
    else {
      messageContent = $('<div>').text(msg).html(); // Escapar HTML
    }
    
    return '<div class="chat-message ' + cls + '" data-msg-id="' + (m.id || '') + '">'
         + '<div class="message-body">' + messageContent 
         + '<div class="message-time">' + time + '</div>'
         + '</div></div>';
  }

  /**
   * Actualiza las estadísticas del dashboard en tiempo real
   * Recarga TODOS los contadores: Total, Sin Leer, Chats Atendidos
   */
  function updateStats(){
    $.get(base + 'Bot_Chat/index', function(html){
      try {
        // Extraer todos los valores de las estadísticas del HTML
        var tempDiv = $('<div>').html(html);
        
        // Total Conversaciones
        var totalCount = tempDiv.find('#stat-total').text() || '0';
        $('#stat-total').text(totalCount);
        
        // Conversaciones Sin Leer
        var unreadCount = tempDiv.find('#stat-unread').text() || '0';
        $('#stat-unread').text(unreadCount);
        
        // Chats Atendidos por Admin (agent_mode = 1)
        var adminChatsCount = tempDiv.find('#stat-admin-chats').text() || '0';
        $('#stat-admin-chats').text(adminChatsCount);
        
        console.debug('📊 Estadísticas actualizadas:', {
          total: totalCount,
          sin_leer: unreadCount,
          admin_activos: adminChatsCount
        });
      } catch(e) {
        console.error('❌ Error actualizando estadísticas:', e);
      }
    });
  }

  // ========================================================================
  // CARGA DE CONVERSACIONES
  // ========================================================================

  /**
   * Formatea la fecha/hora para la lista de conversaciones
   * Si es dentro de 3 días: muestra solo hora (HH:MM)
   * Si es más de 3 días: muestra fecha (DD/MM/YYYY)
   * @param {string} dateString - Fecha en formato DD/MM/YYYY HH:MM o ISO
   * @returns {string} Fecha formateada
   */
  function formatConversationTime(dateString) {
    if (!dateString) return '';
    
    try {
      var msgDate;
      
      // Detectar si viene en formato DD/MM/YYYY HH:MM (del servidor PHP)
      if (dateString.includes('/')) {
        var parts = dateString.split(' ');
        var dateParts = parts[0].split('/');
        var timeParts = parts[1] ? parts[1].split(':') : ['00', '00'];
        
        // Crear fecha: año, mes-1, día, hora, minuto
        msgDate = new Date(
          parseInt(dateParts[2]), // año
          parseInt(dateParts[1]) - 1, // mes (0-11)
          parseInt(dateParts[0]), // día
          parseInt(timeParts[0]), // hora
          parseInt(timeParts[1])  // minuto
        );
      } else {
        // Formato ISO o estándar
        msgDate = new Date(dateString);
      }
      
      var now = new Date();
      var diffMs = now - msgDate;
      var diffDays = diffMs / (1000 * 60 * 60 * 24);
      
      if (diffDays <= 3) {
        // Dentro de 3 días - mostrar solo hora
        var hours = msgDate.getHours().toString().padStart(2, '0');
        var minutes = msgDate.getMinutes().toString().padStart(2, '0');
        return hours + ':' + minutes;
      } else {
        // Más de 3 días - mostrar fecha
        var day = msgDate.getDate().toString().padStart(2, '0');
        var month = (msgDate.getMonth() + 1).toString().padStart(2, '0');
        var year = msgDate.getFullYear();
        return day + '/' + month + '/' + year;
      }
    } catch(e) {
      console.error('Error formateando fecha:', dateString, e);
      return dateString; // Si hay error, devolver string original
    }
  }

  /**
   * Obtiene y renderiza la lista de conversaciones desde el servidor
   * Auto-selecciona la primera conversación si no hay ninguna activa
   * @param {boolean} silent - Si es true, no resetea la lista (actualización en segundo plano)
   */
  function loadConversations(silent){
    $.get(conversationsUrl, function(resp){
      var list = $('#conversations-list');
      if (!list || !list.length) return; // Contenedor no existe
      
      if (!resp || !resp.success) { 
        if (!silent) {
          list.html('<div class="text-center text-muted p-3">No se pudieron cargar las conversaciones.</div>'); 
        }
        return; 
      }
      
      // Si es actualización silenciosa, guardar la conversación activa
      var activeConvId = currentConversation;
      
      if (silent) {
        // Actualización silenciosa: NO reconstruir, solo actualizar/agregar
        // Crear un conjunto de IDs del servidor para saber cuáles existen
        var serverConvIds = {};
        resp.data.forEach(function(c){
          var id = c.id || (c.raw && (c.raw.conversation_id || c.raw.chat_id || c.raw.id));
          if (id) serverConvIds[id] = c;
        });
        
        // 1. Actualizar conversaciones existentes y eliminar las que ya no existen
        list.find('.conversation-item').each(function(){
          var $item = $(this);
          var id = $item.attr('data-id');
          
          if (serverConvIds[id]) {
            // Existe en servidor - actualizar contenido
            var c = serverConvIds[id];
            var unreadCount = parseInt(c.unread_count || 0);
            var unreadBadge = unreadCount > 0 
              ? '<span class="badge bg-success rounded-pill">' + unreadCount + '</span>' 
              : '';
            
            var formattedTime = formatConversationTime(c.updated_at);
            
            var itemHtml = '<div class="d-flex w-100 justify-content-between align-items-start">'
                         + '<div style="flex: 1;"><strong>' + escapeHtml(c.title || 'Conversación') + '</strong>'
                         + '<div class="text-muted small">' + escapeHtml(c.phone || '') + '</div></div>'
                         + '<div class="text-end" style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">'
                         + '<small class="text-muted">' + escapeHtml(formattedTime) + '</small>'
                         + unreadBadge
                         + '</div>'
                         + '</div>'
                         + '<p class="mb-1 text-truncate" style="max-width: 280px;">' + escapeHtml((c.last_message || '').substring(0, 80) + (c.last_message && c.last_message.length > 80 ? '...' : '')) + '</p>';

            $item.html(itemHtml);
            
            // Mantener clase active si es la conversación actual
            if (id === activeConvId && !$item.hasClass('active')) {
              $item.addClass('active');
            }
            
            // Marcar como procesada
            delete serverConvIds[id];
          } else {
            // No existe en servidor - eliminar
            $item.remove();
          }
        });
        
        // 2. Agregar nuevas conversaciones que no existían
        for (var id in serverConvIds) {
          if (serverConvIds.hasOwnProperty(id)) {
            var c = serverConvIds[id];
            var el = $('<a href="#" class="list-group-item list-group-item-action conversation-item"></a>');
            el.attr('data-id', id);
            
            if (id === activeConvId) {
              el.addClass('active');
            }
            
            var unreadCount = parseInt(c.unread_count || 0);
            var unreadBadge = unreadCount > 0 
              ? '<span class="badge bg-success rounded-pill">' + unreadCount + '</span>' 
              : '';
            
            var formattedTime = formatConversationTime(c.updated_at);
            
            var itemHtml = '<div class="d-flex w-100 justify-content-between align-items-start">'
                         + '<div style="flex: 1;"><strong>' + escapeHtml(c.title || 'Conversación') + '</strong>'
                         + '<div class="text-muted small">' + escapeHtml(c.phone || '') + '</div></div>'
                         + '<div class="text-end" style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">'
                         + '<small class="text-muted">' + escapeHtml(formattedTime) + '</small>'
                         + unreadBadge
                         + '</div>'
                         + '</div>'
                         + '<p class="mb-1 text-truncate" style="max-width: 280px;">' + escapeHtml((c.last_message || '').substring(0, 80) + (c.last_message && c.last_message.length > 80 ? '...' : '')) + '</p>';

            el.html(itemHtml);
            list.prepend(el); // Agregar al inicio (son las más recientes)
          }
        }
        
        // 3. Reordenar según el orden del servidor
        resp.data.forEach(function(c, idx){
          var id = c.id || (c.raw && (c.raw.conversation_id || c.raw.chat_id || c.raw.id));
          if (id) {
            var $item = list.find('.conversation-item[data-id="' + id + '"]');
            if ($item.length) {
              $item.appendTo(list); // Mover al final en orden
            }
          }
        });
        
      } else {
        // Primera carga - limpiar y renderizar todo
        list.empty();
        
        resp.data.forEach(function(c, idx){
          var id = c.id || (c.raw && (c.raw.conversation_id || c.raw.chat_id || c.raw.id)) || ('conv-' + idx);
          
          var el = $('<a href="#" class="list-group-item list-group-item-action conversation-item"></a>');
          el.attr('data-id', id);
          
          var unreadCount = parseInt(c.unread_count || 0);
          var unreadBadge = unreadCount > 0 
            ? '<span class="badge bg-success rounded-pill">' + unreadCount + '</span>' 
            : '';
          
          var formattedTime = formatConversationTime(c.updated_at);
          
          var itemHtml = '<div class="d-flex w-100 justify-content-between align-items-start">'
                       + '<div style="flex: 1;"><strong>' + escapeHtml(c.title || 'Conversación') + '</strong>'
                       + '<div class="text-muted small">' + escapeHtml(c.phone || '') + '</div></div>'
                       + '<div class="text-end" style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">'
                       + '<small class="text-muted">' + escapeHtml(formattedTime) + '</small>'
                       + unreadBadge
                       + '</div>'
                       + '</div>'
                       + '<p class="mb-1 text-truncate" style="max-width: 280px;">' + escapeHtml((c.last_message || '').substring(0, 80) + (c.last_message && c.last_message.length > 80 ? '...' : '')) + '</p>';

          el.html(itemHtml);
          list.append(el);
        });
        
        // NO seleccionar ninguna conversación automáticamente
        // El usuario debe hacer clic en una conversación para verla
        
        // Limpiar localStorage para no restaurar conversaciones antiguas
        try {
          localStorage.removeItem('bot_chat_active_conversation');
        } catch(e) {}
        
        // NO hacer nada aquí - dejar la lista sin selección
        // El contenido del chat permanecerá oculto hasta que el usuario haga clic
      }
    }).fail(function(){ 
      var list = $('#conversations-list'); 
      if (list && list.length && !silent) {
        list.html('<div class="text-center text-muted p-3">Error cargando conversaciones.</div>'); 
      }
    });
  }

  // ========================================================================
  // CARGA DE MENSAJES
  // ========================================================================

  /**
   * Carga y renderiza los mensajes de una conversación
   * Obtiene metadatos en JSON y luego HTML renderizado del servidor
   * @param {string|number} conversationId - ID de la conversación
   * @param {boolean} force - Forzar recarga (ignorar caché)
   * @param {boolean} silent - Actualización silenciosa sin mostrar "Cargando..." (para polling en segundo plano)
   */
  function loadMessages(conversationId, force, silent) {
    if (!conversationId) return;
    currentConversation = conversationId;
    
    var container = $('#chat-messages');
    if (!container || !container.length) return;
    
    // Detectar si el usuario está al final del chat ANTES de actualizar
    var wasAtBottom = isUserAtBottom(container);
    
    // Si es carga inicial (no silent), resetear lastMessageId y limpiar contenedor
    if (!silent) {
      lastMessageId = null;
      container.html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Cargando mensajes...</div>');
    }
    
    // Actualizar estado del usuario: si no está al final, está scrolleando arriba
    if (!wasAtBottom) {
      userIsScrollingUp = true;
    }

    // Primero obtener metadata de conversación y mensajes JSON para actualizar el header
    $.get(messagesUrl, { conversation_id: conversationId }, function(resp){
      if (resp && resp.success) {
        var conv = resp.conversation || null;
        if (conv) {
          var displayName = conv.contact_name || conv.title || conv.name || 'Conversación';
          var phoneNumber = conv.contact_phone || conv.phone || conversationId || '-';
          
          $('#chat-contact').text(displayName);
          $('#chat-phone').text(phoneNumber);
          $('#chat-last-activity').text(conv.last_activity || conv.updated_at || '');
        } else {
          // Si no hay metadata, al menos mostrar el ID de la conversación como número
          $('#chat-contact').text('Conversación');
          $('#chat-phone').text(conversationId);
        }
      }
    }).always(function(){
      // ============================================================
      // RENDERIZADO DE MENSAJES: Estrategia incremental para polling
      // ============================================================
      // Si es polling silencioso Y ya tenemos mensajes, cargar solo mensajes nuevos
      if (silent && lastMessageId) {
        // ============================================================
        // CARGA INCREMENTAL: Solo nuevos mensajes con after_id
        // ============================================================
        console.log('[POLLING] Buscando mensajes nuevos después de ID:', lastMessageId);
        
        $.get(messagesRawUrl, { 
          conversation_id: conversationId, 
          after_id: lastMessageId 
        }, function(r){
          if (!r || !r.success || !r.rows || r.rows.length === 0) {
            // No hay mensajes nuevos
            console.log('[POLLING] No hay mensajes nuevos');
            return;
          }
          
          var newMessages = r.rows;
          console.log('[POLLING] ¡' + newMessages.length + ' mensajes nuevos detectados!');
          
          // Actualizar lastMessageId con el ID del último mensaje nuevo
          if (newMessages.length > 0) {
            var lastMsg = newMessages[newMessages.length - 1];
            var oldLastId = lastMessageId;
            lastMessageId = lastMsg.id || lastMsg.message_id || lastMessageId;
            console.log('[POLLING] lastMessageId actualizado de', oldLastId, 'a', lastMessageId);
            
            // Incrementar contador de nuevos mensajes
            newMessagesCount += newMessages.length;

            // ============================================================
            // INSERTAR MENSAJES NUEVOS SIN RECARGAR
            // ============================================================
            // IMPORTANTE: Los mensajes vienen del servidor ordenados por id ASC (orden secuencial)
            // Por lo tanto, debemos respetar ese orden y simplemente agregarlos al final
            // La verificación de duplicados previene que se agreguen mensajes que ya existen
            // ============================================================
            newMessages.forEach(function(m){
              var msgId = m.id || m.message_id;

              // Verificar si ya existe (prevenir duplicados)
              if (container.find('[data-msg-id="' + msgId + '"]').length > 0) {
                console.log('[POLLING] Mensaje', msgId, 'ya existe, omitiendo');
                return;
              }

              // Renderizar y agregar al final (confiar en el orden del servidor)
              var messageHtml = renderMessage(m);
              container.append(messageHtml);
              console.log('[POLLING] Mensaje', msgId, 'agregado (orden servidor)');
            });

            // Scroll solo si está al final
            if (!userIsScrollingUp) {
              scrollChatToBottom(container, false);
            } else if (typeof showNewMessagesButton === 'function') {
              showNewMessagesButton(newMessagesCount);
            }
          }
        });
        
        return; // Salir aquí para evitar la carga completa
      }
      
      // ============================================================
      // CARGA COMPLETA: Primera vez o forzada (USAR HTML DEL SERVIDOR)
      // ============================================================
      console.log('[CARGA INICIAL] Cargando todos los mensajes para conversación:', conversationId);
      
      fetch(messagesHtmlUrl + '?conversation_id=' + encodeURIComponent(conversationId))
        .then(function(response) { return response.text(); })
        .then(function(html) {
          container.html(html);
          
          // Extraer lastMessageId del último mensaje renderizado
          var allMsgDivs = container.find('.chat-message[data-msg-id]');
          console.log('[CARGA INICIAL] Total mensajes renderados:', allMsgDivs.length);
          
          if (allMsgDivs.length > 0) {
            var lastMsgDiv = allMsgDivs.last();
            lastMessageId = lastMsgDiv.attr('data-msg-id') || null;
            console.log('[CARGA INICIAL] lastMessageId establecido en:', lastMessageId);
          }
          
          scrollChatToBottom(container, false);
          updateStats();

          // ============================================================
          // MARCAR MENSAJES COMO LEÍDOS (solo después de renderizar)
          // ============================================================
          markConversationAsRead(conversationId);
        })
        .catch(function(err) {
          console.error('Error cargando HTML de mensajes:', err);
          container.html('<div class="text-center text-muted p-3">Error al cargar mensajes.</div>');
          updateStats();
        })
        .finally(function(){
          // ============================================================
          // NOTA: El polling de mensajes ahora está en el POLLING UNIFICADO
          // No se crea aquí para evitar timers duplicados
          // ============================================================
          console.log('[CARGA INICIAL] Mensajes cargados, polling unificado se encargará de actualizaciones');
        });
    });
  }

  // ========================================================================
  // INICIALIZACIÓN DEL MÓDULO
  // ========================================================================

  /**
   * Función de inicialización principal
   * Registra event listeners y carga datos iniciales
   */
  function init(){
    console.log('🎯 [BOT_CHAT] Función init() ejecutada');
    console.log('🎯 [BOT_CHAT] Base URL:', base);
    
    // ============================================================
    // INYECCIÓN DE BOTÓN DE MODO AGENTE
    // ============================================================
    try {
      console.log('🔧 [BOT_CHAT] Intentando inyectar botón agent-mode...');
      var forceBtn = $('#btn-force-load');
      var agentBtn = $('<button id="btn-agent-mode" class="btn btn-sm btn-outline-success ms-3"><i class="fas fa-robot me-1"></i>Bot Automático: ON</button>');
      
      if (forceBtn.length) { 
        forceBtn.after(agentBtn); 
        console.log('✅ [BOT_CHAT] Botón agent-mode inyectado después de btn-force-load');
      } else { 
        var headerTools = $('#chat-header .d-flex'); 
        if (headerTools.length) {
          headerTools.append(agentBtn); 
          console.log('✅ [BOT_CHAT] Botón agent-mode inyectado en header');
        } else {
          console.error('❌ [BOT_CHAT] No se encontró lugar para inyectar botón');
        }
      }
    } catch(e){ 
      console.error('❌ [BOT_CHAT] Error inyectando botón:', e); 
    }
    
    /**
     * Actualiza el estado visual del botón de modo agente
     * agentMode = true → Bot OFF (responde el agente humano)
     * agentMode = false → Bot ON (responde automáticamente)
     */
    function updateAgentBtn(){ 
      var b = $('#btn-agent-mode'); 
      if (!b.length) return; 
      
      var chatInput = $('#chat-input');
      var btnSend = $('#btn-send');
      
      if (agentMode) {
        // Modo manual: Bot desactivado, humano responde
        b.removeClass('btn-outline-success').addClass('btn-outline-danger').html('<i class="fas fa-user me-1"></i>Bot Automático: OFF'); 
        
        if (chatInput.length) {
          chatInput.prop('disabled', false);
          chatInput.prop('readonly', false);
          chatInput.attr('placeholder', 'Escribe un mensaje...');
        }
        if (btnSend.length) {
          btnSend.prop('disabled', false);
        }
      } else {
        // Modo automático: Bot responde solo
        b.removeClass('btn-outline-danger').addClass('btn-outline-success').html('<i class="fas fa-robot me-1"></i>Bot Automático: ON'); 
        
        if (chatInput.length) {
          chatInput.prop('disabled', true);
          chatInput.prop('readonly', true);
          chatInput.attr('placeholder', '🤖 Bot automático activo. Cambia a OFF para responder manualmente.');
          chatInput.val('');
        }
        if (btnSend.length) {
          btnSend.prop('disabled', true);
        }
      }
    }
    
    // Toggle modo agente
    console.log('🎧 [BOT_CHAT] Registrando event listener para #btn-agent-mode');
    $(document).on('click', '#btn-agent-mode', function(ev){ 
      ev.preventDefault(); 
      console.log('🔵 [DEBUG] Click en btn-agent-mode detectado');
      console.log('🔵 [DEBUG] currentConversation:', currentConversation);
      console.log('🔵 [DEBUG] agentMode actual:', agentMode);
      
      if (!currentConversation) {
        alert('⚠️ Selecciona una conversación primero');
        return;
      }
      
      agentMode = !agentMode; 
      console.log('🔵 [DEBUG] Nuevo agentMode:', agentMode);
      
      updateAgentBtn(); 
      
      console.log('🔵 [DEBUG] Enviando AJAX a:', base + 'Bot_Chat/toggle_agent_mode');
      $.ajax({
        url: base + 'Bot_Chat/toggle_agent_mode',
        method: 'POST',
        data: JSON.stringify({
          conversation_id: currentConversation,
          agent_mode: agentMode
        }),
        contentType: 'application/json',
        success: function(resp) {
          console.log('🔵 [DEBUG] AJAX success:', resp);
          if (resp && resp.status === 'OK') {
            console.log('✅ Modo agente actualizado:', resp.msg);
            // Actualizar estadísticas inmediatamente después de cambiar agent_mode
            updateStats();
          } else {
            console.error('❌ Error al actualizar modo:', resp.msg);
          }
        },
        error: function(xhr, status, error) {
          console.error('🔵 [DEBUG] AJAX error:', error);
          console.error('🔵 [DEBUG] XHR:', xhr);
          console.error('❌ Error en llamada AJAX:', error);
          agentMode = !agentMode;
          updateAgentBtn();
        }
      });
    });
    
    // ============================================================
    // LISTENER DE SCROLL: Detectar cuando el usuario navega por la conversación
    // ============================================================
    $(document).on('scroll', '#chat-messages', function(){
      var container = $(this);
      var atBottom = isUserAtBottom(container);
      
      if (atBottom) {
        // Usuario llegó al final: resetear estado
        userIsScrollingUp = false;
        newMessagesCount = 0;
        hideNewMessagesButton();
      } else {
        // Usuario está viendo mensajes antiguos
        userIsScrollingUp = true;
      }
    });
    
    // ============================================================
    // CLICK EN CONVERSACIÓN
    // ============================================================
    $(document).on('click', '.conversation-item', function(e){
      e.preventDefault();
      var id = $(this).data('id');

      // Actualizar estado visual
      $('.conversation-item').removeClass('active');
      $(this).addClass('active');

      currentConversation = id;

      // Limpiar timers de polling anteriores ANTES de cargar nueva conversación
      clearAllPolling();

      // Mostrar card completo y contenido si estaba oculto
      $('.col-md-8 .card').fadeIn(300);
      $('.col-md-8 .card-body').fadeIn(300);
      $('.col-md-8 .card-footer').fadeIn(300);

      // Resetear contador de ESC
      escPressCount = 0;

      // Guardar en localStorage para mantener selección al recargar
      try {
        localStorage.setItem('bot_chat_active_conversation', id);
      } catch(e) {
        console.warn('No se pudo guardar conversación activa en localStorage', e);
      }

      // Resetear estado de scroll al cambiar de conversación
      userIsScrollingUp = false;
      newMessagesCount = 0;
      hideNewMessagesButton();
      
      // ============================================================
      // CARGAR ESTADO DEL BOT (agent_mode) DESDE LA BASE DE DATOS
      // ============================================================
      $.get(base + 'Bot_Chat/get_agent_mode', { conversation_id: id }, function(resp){
        if (resp && resp.status === 'OK') {
          agentMode = resp.agent_mode || false;
          updateAgentBtn();
          console.log('✅ Estado del bot cargado:', agentMode ? 'OFF (Manual)' : 'ON (Automático)');
        }
      }).fail(function(){
        // Si falla, asumir bot automático (por defecto)
        agentMode = false;
        updateAgentBtn();
      });
      
      // Cargar mensajes de la conversación seleccionada (primera carga: mostrar "Cargando...")
      try { 
        loadMessages(id, true, false); 
      } catch(e){ 
        console.warn('loadMessages llamada falló', e); 
      }
    });

    // ============================================================
    // CARGA INICIAL DE CONVERSACIONES
    // ============================================================
    try { 
      loadConversations(false); 
    } catch(e){ 
      console.warn('loadConversations llamada falló', e); 
    }
    
    // ============================================================
    // POLLING UNIFICADO - TODO EN UNO
    // ============================================================
    // En lugar de tener 3 pollings separados (conversaciones, mensajes, agent_mode),
    // usamos UN SOLO polling que actualiza todo en cada ciclo
    // Esto reduce las peticiones HTTP significativamente
    // ============================================================
    if (autoRefreshEnabled) {
      var unifiedPollCounter = 0; // Contador para alternar updates pesados

      conversationsPollTimer = setInterval(function(){
        unifiedPollCounter++;

        try {
          // 1. Actualizar mensajes de la conversación activa (siempre)
          if (currentConversation) {
            loadMessages(currentConversation, false, true); // Actualización silenciosa
          }

          // 2. Actualizar lista de conversaciones (cada 6 segundos = cada 3 ciclos)
          if (unifiedPollCounter % 3 === 0) {
            loadConversations(true); // Actualización silenciosa de lista
          }

          // 3. Actualizar estadísticas (siempre)
          updateStats();

          // 4. Actualizar agent_mode si hay conversación activa (siempre)
          if (currentConversation) {
            $.get(base + 'Bot_Chat/get_agent_mode', { conversation_id: currentConversation }, function(resp){
              if (resp && resp.status === 'OK') {
                var newAgentMode = resp.agent_mode || false;

                // Solo actualizar si cambió
                if (newAgentMode !== agentMode) {
                  agentMode = newAgentMode;
                  updateAgentBtn();
                  console.log('🔄 [POLLING UNIFICADO] Estado del bot actualizado:', agentMode ? 'OFF (Manual)' : 'ON (Automático)');
                }
              }
            }).fail(function(){
              // Silencioso: no mostrar errores en polling
            });
          }

        } catch(e) {
          console.warn('Error en polling unificado', e);
        }
      }, pollingInterval); // Usar pollingInterval (2000ms) para todo

      console.log('[POLLING UNIFICADO] Iniciado cada', pollingInterval + 'ms');
    }

    // ============================================================
    // TECLA ESC: Deseleccionar y ocultar panel (estilo WhatsApp)
    // ============================================================
    $(document).on('keydown', function(e){
      if (e.key === 'Escape' || e.keyCode === 27) {
        e.preventDefault();
        
        if (escPressCount === 0 && currentConversation) {
          // Primera vez ESC: Solo deseleccionar conversación (NO mostrar mensaje todavía)
          $('.conversation-item').removeClass('active');
          currentConversation = null;
          
          // Limpiar localStorage
          try {
            localStorage.removeItem('bot_chat_active_conversation');
          } catch(ex) {}
          
          // Resetear contadores
          userIsScrollingUp = false;
          newMessagesCount = 0;
          lastMessageId = null;
          hideNewMessagesButton();
          
          // Ocultar footer pero mantener el contenido del chat visible
          $('.col-md-8 .card-footer').fadeOut(300);
          
          escPressCount = 1;
          console.log('[ESC 1/2] Conversación deseleccionada - Footer oculto');
          
        } else if (escPressCount === 1) {
          // Segunda vez ESC: Ahora SÍ ocultar chat y mostrar mensaje de bienvenida
          
          // Restaurar mensaje de bienvenida
          var welcomeMsg = '<div class="d-flex align-items-center justify-content-center" style="height: 100%; min-height: 480px; flex-direction: column;">'
                         + '<div class="text-center" style="max-width: 400px; padding: 40px 20px;">'
                         + '<div style="margin-bottom: 30px;"><i class="fas fa-comments" style="font-size: 80px; color: #7367f0; opacity: 0.2;"></i></div>'
                         + '<h4 style="color: #5e5873; font-weight: 600; margin-bottom: 12px;">Bot Chat de WhatsApp</h4>'
                         + '<p style="color: #b9b9c3; font-size: 15px; line-height: 1.6; margin-bottom: 0;">Seleccione una conversación de la lista para visualizar los mensajes y poder responder</p>'
                         + '<div style="margin-top: 30px;"><i class="fas fa-arrow-left" style="font-size: 24px; color: #7367f0; opacity: 0.4; animation: pulse-arrow 2s infinite;"></i></div>'
                         + '</div></div>';
          $('#chat-messages').html(welcomeMsg);
          
          escPressCount = 2;
          console.log('[ESC 2/2] Mensaje de bienvenida mostrado');
          
        } else if (escPressCount === 0 && !currentConversation) {
          // Si no hay conversación seleccionada, primer ESC muestra mensaje de bienvenida
          var welcomeMsg = '<div class="d-flex align-items-center justify-content-center" style="height: 100%; min-height: 480px; flex-direction: column;">'
                         + '<div class="text-center" style="max-width: 400px; padding: 40px 20px;">'
                         + '<div style="margin-bottom: 30px;"><i class="fas fa-comments" style="font-size: 80px; color: #7367f0; opacity: 0.2;"></i></div>'
                         + '<h4 style="color: #5e5873; font-weight: 600; margin-bottom: 12px;">Bot Chat de WhatsApp</h4>'
                         + '<p style="color: #b9b9c3; font-size: 15px; line-height: 1.6; margin-bottom: 0;">Seleccione una conversación de la lista para visualizar los mensajes y poder responder</p>'
                         + '<div style="margin-top: 30px;"><i class="fas fa-arrow-left" style="font-size: 24px; color: #7367f0; opacity: 0.4; animation: pulse-arrow 2s infinite;"></i></div>'
                         + '</div></div>';
          $('#chat-messages').html(welcomeMsg);
          escPressCount = 2;
          console.log('[ESC] Mensaje de bienvenida mostrado (sin conversación activa)');
        }
        // Si ya está en estado 2, no hacer nada
      }
    });

    // ============================================================
    // BOTÓN FORZAR RECARGA
    // ============================================================
    $(document).on('click', '#btn-force-load', function(e){ 
      e.preventDefault(); 
      
      if (currentConversation) {
        // Recarga manual: mostrar indicador "Cargando..." (silent = false)
        loadMessages(currentConversation, true, false); 
      } else {
        loadConversations(false); 
      }
    });

    // ========================================================================
    // BOTÓN ABRIR MODAL DE CONFIGURACIÓN DE EVENTOS
    // ========================================================================
    /**
     * Handler para abrir el modal de configuración de eventos
     * 
     * Estrategia de carga:
     * 1. Mostrar fallback HTML inline inmediatamente (respuesta rápida)
     * 2. Intentar cargar fragmento completo desde servidor en background
     * 3. Reemplazar con versión del servidor si está disponible
     * 
     * URLs candidatas probadas en orden:
     * - /index.php?url=Bot_Events/modal
     * - /index.php/C_Bot_Events/modal
     * - /C_Bot_Events/modal
     */
    $(document).on('click', '#btn-open-events', function(e){
      e.preventDefault();
      
      // ============================================================
      // VERIFICAR EXISTENCIA DEL CONTENEDOR DEL MODAL
      // ============================================================
      var modalEl = document.getElementById('modal-events-body');
      if (!modalEl) { 
        alert('Modal no disponible'); 
        return; 
      }
      
      // Mostrar indicador de carga
      modalEl.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>';
      
      var base = (typeof BASE_URL !== 'undefined' && BASE_URL) ? BASE_URL : '/';

      // ============================================================
      // CONSTRUIR URLs CANDIDATAS PARA EL MODAL
      // ============================================================
      // Evitar duplicar el origen si base ya lo incluye
      var modalUrls = [];
      try {
        var origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
        
        if (base.indexOf('http') === 0) {
          // base ya incluye el origen completo
          modalUrls.push(base + 'index.php?url=Bot_Events/modal');
          modalUrls.push(base + 'index.php/C_Bot_Events/modal');
          modalUrls.push(base + 'C_Bot_Events/modal');
        } else {
          // base es relativo, agregar origen
          modalUrls.push(origin + base + 'index.php?url=Bot_Events/modal');
          modalUrls.push(origin + base + 'index.php/C_Bot_Events/modal');
          modalUrls.push(origin + base + 'C_Bot_Events/modal');
        }
      } catch(e) { 
        // Fallback simple
        modalUrls.push(base + 'C_Bot_Events/modal'); 
        modalUrls.push(base + 'index.php/C_Bot_Events/modal'); 
      }

      /**
       * Helper para cargar script dinámicamente
       * @param {string} src - URL del script
       * @param {function} cbOk - Callback de éxito
       * @param {function} cbErr - Callback de error
       */
      function loadScript(src, cbOk, cbErr){
        var s = document.createElement('script'); 
        s.src = src; 
        s.async = true;
        s.onload = cbOk; 
        s.onerror = cbErr; 
        document.head.appendChild(s);
      }

      // ============================================================
      // FALLBACK HTML INLINE (RESPUESTA RÁPIDA)
      // ============================================================
      // Este HTML se muestra inmediatamente mientras se carga el fragmento del servidor
      // Contiene un formulario funcional básico con todos los campos necesarios
      var inlineHtml = ''+
        '<div class="card">'
          + '<div class="card-body p-3">'
            + '<div id="events-table" class="mb-2 small text-muted"></div>'
            + '<form id="event-form">'
              + '<input type="hidden" name="id" value="" />'
              + '<div class="row gx-2">'
                // Nombre: mostrar pero no permitir edición (valor de BD)
                + '<div class="col-3"><label>Nombre</label><input class="form-control form-control-sm" name="name" type="text" readonly disabled /></div>'
                + '<div class="col-3"><label>Activado</label><select class="form-control form-control-sm" name="enabled"><option value="1">Sí</option><option value="0">No</option></select></div>'
                + '<div class="col-3"><label>Ejecución (min)</label><input class="form-control form-control-sm" name="interval_minutes" type="number" value="5"/></div>'
                + '<div class="col-3"><label>Expiración</label><div class="d-flex">'
                  + '<input class="form-control form-control-sm me-1" name="retention_value" type="number" value="30" style="width:80px;"/>'
                  + '<select class="form-control form-control-sm" name="retention_unit">'
                    + '<option value="minute">Minutos</option>'
                    + '<option value="hour">Horas</option>'
                    + '<option value="day" selected>Días</option>'
                    + '<option value="week">Semanas</option>'
                    + '<option value="month">Meses (30d)</option>'
                    + '<option value="year">Años</option>'
                  + '</select>'
                + '</div></div>'
                + '<div class="col-3"><label>Filas</label><input class="form-control form-control-sm" name="batch_size" type="number" value="1000"/></div>'
              + '</div>'
              + '<div class="mt-2"><button class="btn btn-sm btn-primary" id="save-event">Guardar</button> <button class="btn btn-sm btn-secondary" id="run-event" type="button">Forzar ejecución</button></div>'
            + '</form>'
              + '<div id="events-debug" class="mt-2 small text-monospace text-break" style="display:none; white-space:pre-wrap;"></div>'
              + '<div id="event-logs" class="mt-2 small"></div>'
          + '</div>'
        + '</div>';

      // ============================================================
      // RENDERIZAR FALLBACK Y POBLAR CON DATOS DEL SERVIDOR
      // ============================================================
      try {
        modalEl.innerHTML = inlineHtml;
        
        /**
         * Función para poblar la lista inline de eventos
         * Obtiene datos desde el servidor y rellena el formulario
         */
        function populateInlineList() {
          try {
            var origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
            var listUrl = (base.indexOf('http')===0? base : (origin + base)) + 'index.php?url=Bot_Events/list';
            
            fetch(listUrl, { credentials: 'same-origin' })
              .then(function(r){ return r.json(); })
              .then(function(j){
                if (!j || !j.success || !j.data) return;
                
                var tbl = modalEl.querySelector('#events-table'); 
                if (!tbl) return;
                
                tbl.innerHTML = '';
                
                // Renderizar cada evento como item editable
                j.data.forEach(function(s){
                  var div = document.createElement('div');
                  div.className = 'p-2 border mb-1 small';
                  var intervalMin = Math.round((s.interval_seconds||0)/60);
                  div.innerHTML = '<strong>'+ (s.name||'Evento') +'</strong> &nbsp; <small>run:'+intervalMin+'m retention:'+ (s.retention_minutes||0) +'m</small> <button class="btn btn-sm btn-link inline-edit" data-id="'+s.id+'">Editar</button>';
                  tbl.appendChild(div);
                });
                
                // Adjuntar handlers de edición para poblar el formulario
                var edits = tbl.querySelectorAll('.inline-edit');
                edits.forEach(function(b){
                  b.addEventListener('click', function(ev){
                    ev.preventDefault();
                    var id = this.dataset.id; 
                    if (!id) return;
                    
                    var item = j.data.find(function(x){ return String(x.id) === String(id); }); 
                    if (!item) return;
                    
                    var f = modalEl.querySelector('#event-form'); 
                    if (!f) return;
                    
                    // Rellenar todos los campos del formulario
                    try { if (f.querySelector('[name="id"]')) f.querySelector('[name="id"]').value = item.id; }catch(e){}
                    try { var nf = f.querySelector('[name="name"]'); if (nf) { nf.value = item.name || ''; nf.readOnly = true; } }catch(e){}
                    try { if (f.querySelector('[name="enabled"]')) f.querySelector('[name="enabled"]').value = item.enabled; }catch(e){}
                    try { if (f.querySelector('[name="batch_size"]')) f.querySelector('[name="batch_size"]').value = item.batch_size; }catch(e){}
                    try { 
                      var iim = f.querySelector('[name="interval_minutes"]'); 
                      if (iim) {
                        iim.value = Math.round((item.interval_seconds||0)/60); 
                      } else if (f.querySelector('[name="interval_seconds"]')) {
                        f.querySelector('[name="interval_seconds"]').value = item.interval_seconds; 
                      }
                    }catch(e){}
                    
                    // Convertir retention_minutes a valor + unidad apropiada
                    try {
                      var rv = f.querySelector('[name="retention_value"]'); 
                      var ru = f.querySelector('[name="retention_unit"]');
                      if (rv && ru) {
                        var rm = parseInt(item.retention_minutes||0);
                        if (rm % 525600 === 0) { rv.value = rm/525600; ru.value='year'; }
                        else if (rm % 43200 === 0) { rv.value = rm/43200; ru.value='month'; }
                        else if (rm % 10080 === 0) { rv.value = rm/10080; ru.value='week'; }
                        else if (rm % 1440 === 0) { rv.value = rm/1440; ru.value='day'; }
                        else if (rm % 60 === 0) { rv.value = rm/60; ru.value='hour'; }
                        else { rv.value = rm; ru.value='minute'; }
                      } else if (f.querySelector('[name="retention_minutes"]')) {
                        try { f.querySelector('[name="retention_minutes"]').value = item.retention_minutes; }catch(e){}
                      }
                    }catch(e){}
                  });
                });
                
                // Auto-seleccionar primer evento para poblar formulario
                if (j.data.length>0) {
                  var first = j.data[0]; 
                  var f = modalEl.querySelector('#event-form'); 
                  if (f) { 
                    try { 
                      if (f.querySelector('[name="id"]')) f.querySelector('[name="id"]').value = first.id; 
                    }catch(e){} 
                    try { 
                      var nf = f.querySelector('[name="name"]'); 
                      if (nf) { nf.value = first.name||''; nf.readOnly=true; nf.disabled=true; } 
                    }catch(e){} 
                  }
                }
              }).catch(function(){ /* ignorar errores */ });
          } catch(e) { /* ignorar errores */ }
        }
        
        populateInlineList();
        
        // ============================================================
        // MOSTRAR MODAL CON FALLBACK INLINE
        // ============================================================
        try { 
            var modalElement = document.getElementById('modal-events-config');
            if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                var modalInstance = bootstrap.Modal.getInstance(modalElement) || bootstrap.Modal.getOrCreateInstance(modalElement);
                modalInstance.show();
            } else if (window.jQuery) {
                jQuery('#modal-events-config').modal('show');
            }
        } catch(e){ 
          console.warn('Error mostrando modal', e); 
        }
      } catch(e) { 
        modalEl.innerHTML = inlineHtml; 
      }

      // ============================================================
      // INTENTAR CARGAR CONTENIDO DESDE SERVIDOR (BACKGROUND)
      // ============================================================
      /**
       * Función recursiva para intentar fetch de múltiples URLs
       * @param {number} i - Índice de la URL actual a intentar
       */
      (function tryFetch(i){
        if (i >= modalUrls.length) {
          // Todas las URLs fallaron, mantener fallback inline
          return;
        }
        
        fetch(modalUrls[i], { credentials: 'same-origin' })
          .then(function(r){ return r.text(); })
          .then(function(txt){
            // Verificar marcador de éxito en la respuesta
            if (txt && txt.indexOf('<!-- BOT_EVENTS_MODAL_OK -->') !== -1) {
              // HTML válido recibido: reemplazar fallback
              modalEl.innerHTML = txt;
              
              // ============================================================
              // HANDLER DE GUARDADO (REGISTRADO DESPUÉS DE INYECTAR HTML)
              // ============================================================
              setTimeout(function() {
                var saveBtn = document.getElementById('save-event');
                if (saveBtn) {
                  saveBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    var form = document.getElementById('event-form');
                    if (!form) { 
                      alert('❌ Formulario no encontrado'); 
                      return; 
                    }
                    
                    /**
                     * Helper para obtener valores del formulario
                     * @param {string} name - Nombre del campo
                     * @returns {string|null} Valor del campo
                     */
                    var getVal = function(name) {
                      var field = form.querySelector('[name="' + name + '"]');
                      return field ? field.value : null;
                    };
                    
                    // ============================================================
                    // CONVERSIÓN DE UNIDADES: interval_value + unit → seconds
                    // ============================================================
                    // Nueva UI: interval_value + interval_unit (con fallback a interval_minutes)
                    var iv = parseInt(getVal('interval_value') || '0');
                    var iu = (getVal('interval_unit') || '').toLowerCase();
                    var interval_seconds = 0;
                    
                    if (!isNaN(iv) && iv > 0 && iu) {
                        // Mapeo de unidades a segundos
                        var secMult = {
                          minute: 60,
                          hour: 3600,
                          day: 86400,
                          week: 604800,
                          month: 2592000,
                          year: 31536000
                        }[iu] || 60;
                        interval_seconds = iv * secMult;
                    } else {
                        // Fallback: interval_minutes (campo antiguo)
                        var intervalMin = parseInt(getVal('interval_minutes') || '0');
                        interval_seconds = intervalMin * 60;
                    }
                    
                    // ============================================================
                    // CONVERSIÓN DE UNIDADES: retention_value + unit → minutes
                    // ============================================================
                    var retValue = parseInt(getVal('retention_value') || '0');
                    var retUnit = (getVal('retention_unit') || 'minute').toLowerCase();
                    var mult = {
                      minute: 1,
                      hour: 60,
                      day: 1440,
                      week: 10080,
                      month: 43200,
                      year: 525600
                    }[retUnit] || 1;
                    
                    // ============================================================
                    // CONSTRUIR PAYLOAD DE DATOS
                    // ============================================================
                    var data = {
                      id: getVal('id') || '2',
                      name: getVal('name') || 'clean_old_messages',
                      enabled: getVal('enabled') || '1',
                      interval_seconds: interval_seconds,
                      retention_minutes: retValue * mult,
                      batch_size: parseInt(getVal('batch_size') || '1000')
                    };
                    
                    // ============================================================
                    // ENVIAR DATOS AL SERVIDOR
                    // ============================================================
                    fetch(base + 'index.php?url=Bot_Events/save', {
                      method: 'POST',
                      credentials: 'same-origin',
                      headers: {'Content-Type': 'application/json'},
                      body: JSON.stringify(data)
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(json) {
                      if (json.status === 'OK' || json.success) {
                        alert('✅ Tabla actualizada correctamente');
                        
                        // Cerrar modal después de guardar exitosamente
                        setTimeout(function() {
                          var modal = document.getElementById('modal-events-config');
                          if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            var inst = bootstrap.Modal.getInstance(modal);
                            if (inst) inst.hide();
                          }
                                  }, 300);
                                } else {
                                  alert('❌ Error: ' + (json.msg || json.error || 'Error desconocido'));
                                }
                              })
                              .catch(function(err) {
                                alert('❌ Error: ' + err.message);
                              });
                            });
                          }
                        }, 100);
                        
                        // ============================================================
                        // HANDLER DEL BOTÓN "EJECUTAR"
                        // ============================================================
                        setTimeout(function() {
                          var runBtn = document.getElementById('run-event');
                          if (runBtn) {
                            runBtn.addEventListener('click', function(e) {
                              e.preventDefault();
                              e.stopPropagation();
                              
                              var form = document.getElementById('event-form');
                              if (!form) { 
                                alert('❌ Formulario no encontrado'); 
                                return; 
                              }
                              
                              /**
                               * Helper para obtener valores del formulario
                               */
                              var getVal = function(name) {
                                var field = form.querySelector('[name="' + name + '"]');
                                return field ? field.value : null;
                              };
                              
                              // Obtener el ID del evento
                              var id = getVal('id');
                              if (!id) {
                                alert('⚠️ No se pudo obtener el ID del evento');
                                return;
                              }
                              
                              // Obtener configuración de retención para mostrar en mensaje
                              var retValue = parseInt(getVal('retention_value') || '0');
                              var retUnit = (getVal('retention_unit') || 'minute').toLowerCase();
                              
                              // Convertir a minutos
                              var retMinutes = 0;
                              var unitMult = {
                                minute: 1,
                                hour: 60,
                                day: 1440,
                                week: 10080,
                                month: 43200,
                                year: 525600
                              }[retUnit] || 1;
                              retMinutes = retValue * unitMult;
                              
                              // Calcular tiempo para mostrar mensaje amigable
                              var totalDays = Math.floor(retMinutes / 1440);
                              var remainingHours = Math.floor((retMinutes % 1440) / 60);
                              var remainingMinutes = Math.floor(retMinutes % 60);
                              var remainingSeconds = Math.floor((retMinutes * 60) % 60);
                              
                              var timeMsg = '';
                              if (totalDays > 0) {
                                timeMsg = totalDays + ' día' + (totalDays !== 1 ? 's' : '');
                                if (remainingHours > 0) {
                                  timeMsg += ' y ' + remainingHours + ' hora' + (remainingHours !== 1 ? 's' : '');
                                }
                              } else if (remainingHours > 0) {
                                timeMsg = remainingHours + ' hora' + (remainingHours !== 1 ? 's' : '');
                                if (remainingMinutes > 0) {
                                  timeMsg += ' y ' + remainingMinutes + ' minuto' + (remainingMinutes !== 1 ? 's' : '');
                                }
                              } else if (retMinutes >= 1) {
                                timeMsg = Math.floor(retMinutes) + ' minuto' + (Math.floor(retMinutes) !== 1 ? 's' : '');
                              } else {
                                // Menos de 1 minuto = mostrar en segundos
                                timeMsg = retValue + ' segundo' + (retValue !== 1 ? 's' : '');
                              }
                              
                              // NO mostrar alert antes - ejecutar directamente
                              console.log('🗑️ Ejecutando limpieza de mensajes más antiguos que ' + timeMsg + '...');
                              
                              // Ejecutar el evento (usar la misma URL base que el botón Guardar)
                              fetch(base + 'index.php?url=Bot_Events/run', {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({id: id})
                              })
                              .then(function(r) { return r.json(); })
                              .then(function(json) {
                                if (json && json.status === 'OK') {
                                  var rows = parseInt(json.rows || 0);
                                  if (rows > 0) {
                                    alert('✅ Ejecución completada!\n\n' +
                                          '🗑️ Eliminando mensajes más antiguos que ' + timeMsg + '\n\n' +
                                          '📊 Mensajes eliminados: ' + rows + '\n' +
                                          '⏱️ Antigüedad: más de ' + timeMsg);
                                  } else {
                                    alert('ℹ️ Ejecución completada\n\n' +
                                          '🗑️ Buscando mensajes más antiguos que ' + timeMsg + '\n\n' +
                                          '✨ No se encontraron mensajes para eliminar.\n' +
                                          'Todos los mensajes tienen menos de ' + timeMsg + ' de antigüedad.');
                                  }
                                } else {
                                  alert('❌ Error al ejecutar: ' + (json.msg || JSON.stringify(json)));
                                }
                              })
                              .catch(function(err) {
                                alert('❌ Error de conexión: ' + err.message);
                                console.error('Error ejecutando evento:', err);
                              });
                            });
                          }
                        }, 100);
                        
                        // Mostrar modal usando Bootstrap
                        try {
                            var modalElement = document.getElementById('modal-events-config');
                            if (window.bootstrap && typeof bootstrap.Modal === 'function') {
                                var modalInstance = bootstrap.Modal.getInstance(modalElement) || bootstrap.Modal.getOrCreateInstance(modalElement);
                                modalInstance.show();
                            } else if (window.jQuery) {
                                jQuery('#modal-events-config').modal('show');
                            }
                        } catch(e){ console.warn('Error showing modal', e); }
                      } else {
                        tryFetch(i+1);
                      }
                    }).catch(function(){ tryFetch(i+1); });
                  })(0);

                });

              } // end init

              // Auto-init when script loaded
              try {
                if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
                else init();
              } catch(e){ console.warn('Bot Chat init failed', e); }

              // Small helper: send message from the input area
              try {
                $(document).on('click', '#btn-send', function(e){
                  e.preventDefault();
                  
                  // VALIDACIÓN: No permitir envío manual si Bot Automático está ON
                  if (!agentMode) {
                    alert('⚠️ No puedes enviar mensajes manualmente.\n\n' +
                          'El Bot Automático está ACTIVO (ON).\n\n' +
                          'Para responder manualmente, primero desactiva el bot:\n' +
                          '1. Haz clic en el botón "Bot Automático: ON" (verde)\n' +
                          '2. Cambiará a "Bot Automático: OFF" (rojo)\n' +
                          '3. Luego podrás escribir tus respuestas');
                    return;
                  }
                  
                  var txt = $('#chat-input').val() || '';
                  if (!txt.trim()) return;
                  
                  var payload = { 
                    conversation_id: currentConversation, 
                    message: txt, 
                    direction: 'outgoing',
                    agent_mode: 1,  // IMPORTANTE: 1 = mensaje manual del panel
                    status: 'pending'  // Pendiente de envío por el bot
                  };
                  
                  console.log('📤 [SEND] Enviando mensaje:', payload);
                  
                  $.ajax({ 
                    url: base + 'Bot_Chat/send_message', 
                    method: 'POST', 
                    data: JSON.stringify(payload), 
                    contentType: 'application/json', 
                    success: function(resp){ 
                      console.log('📤 [SEND] Respuesta:', resp);
                      try{ 
                        if (resp && (resp.status==='OK' || resp.success)) { 
                          $('#chat-input').val(''); 
                          
                          // NO recargar todos los mensajes, dejar que el polling lo traiga
                          // Esto evita duplicados visuales
                          console.log('✅ Mensaje enviado, el polling lo mostrará en 2 segundos');
                        } else { 
                          alert('Error al enviar: ' + (resp.msg||JSON.stringify(resp))); 
                        } 
                      }catch(e){
                        console.error('📤 [SEND] Error procesando respuesta:', e);
                      } 
                    }, 
                    error: function(xhr, status, error){ 
                      console.error('📤 [SEND] Error AJAX:', error, xhr);
                      alert('Error enviando mensaje'); 
                    } 
                  });
                });
                // send on Enter (shift+enter for newline)
                $(document).on('keydown', '#chat-input', function(e){ if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); $('#btn-send').trigger('click'); } });
              } catch(e) { console.warn('Send input init failed', e); }

              // Enhanced emoji picker: categories, search, populate and insertion
              try {
                // small emoji dataset
                var EMOJIS = [
                  {ch:'😀',cat:'smileys'},{ch:'😁',cat:'smileys'},{ch:'😂',cat:'smileys'},{ch:'🤣',cat:'smileys'},{ch:'😊',cat:'smileys'},{ch:'😍',cat:'smileys'},{ch:'😎',cat:'smileys'},{ch:'😉',cat:'smileys'},
                  {ch:'🙂',cat:'people'},{ch:'🙃',cat:'people'},{ch:'😇',cat:'people'},{ch:'🤗',cat:'people'},{ch:'🤝',cat:'gestures'},{ch:'👍',cat:'gestures'},{ch:'👎',cat:'gestures'},{ch:'👏',cat:'gestures'},
                  {ch:'🙏',cat:'gestures'},{ch:'💪',cat:'gestures'},{ch:'🎉',cat:'objects'},{ch:'🔥',cat:'objects'},{ch:'💡',cat:'objects'},{ch:'📎',cat:'objects'},{ch:'📷',cat:'objects'},{ch:'🎁',cat:'objects'},
                  {ch:'❤️',cat:'symbols'},{ch:'💔',cat:'symbols'},{ch:'✨',cat:'symbols'},{ch:'⭐',cat:'symbols'},{ch:'⚠️',cat:'symbols'},{ch:'✅',cat:'symbols'},{ch:'❌',cat:'symbols'},{ch:'🔒',cat:'symbols'}
                ];

                function showEmojiPanel() {
                  var panel = document.getElementById('emoji-panel');
                  if (!panel) return;
                  panel.style.display = 'block';
                  var footer = document.querySelector('.card-footer');
                  if (footer) {
                    var r = footer.getBoundingClientRect();
                    var left = (r.left + 16 + window.scrollX);
                    var top = (r.top + window.scrollY - panel.offsetHeight - 8);
                    // keep inside viewport horizontally
                    var maxLeft = window.scrollX + document.documentElement.clientWidth - panel.offsetWidth - 8;
                    if (left > maxLeft) left = maxLeft;
                    if (left < window.scrollX + 8) left = window.scrollX + 8;
                    // if not enough space above, place below footer
                    if (top < window.scrollY + 8) top = r.bottom + window.scrollY + 8;
                    panel.style.top = top + 'px';
                    panel.style.left = left + 'px';
                  }
                }
                function hideEmojiPanel(){ var p = document.getElementById('emoji-panel'); if (p) p.style.display='none'; }

                function populateEmojiList(filter, category){
                  var el = document.getElementById('emoji-list'); if (!el) return; el.innerHTML = '';
                  var f = (filter||'').toLowerCase();
                  EMOJIS.forEach(function(e){
                    if (category && category !== 'all' && e.cat !== category) return;
                    // simple filter: match character (no textual names)
                    if (f && e.ch.indexOf(f) === -1) {
                      // keep all when searching by name is not implemented
                    }
                    var btn = document.createElement('button'); btn.type='button'; btn.className='btn btn-sm btn-light emoji-btn'; btn.style.padding='6px 8px'; btn.style.margin='2px'; btn.textContent = e.ch;
                    el.appendChild(btn);
                  });
                }

                populateEmojiList();

                $(document).on('click', '#btn-emoji', function(e){ e.preventDefault(); var p = document.getElementById('emoji-panel'); if (!p) return; if (p.style.display === 'block') hideEmojiPanel(); else { populateEmojiList(document.getElementById('emoji-search').value || '', document.querySelector('.emoji-cat.active')?.dataset?.cat || 'all'); showEmojiPanel(); } });

                $(document).on('click', '#emoji-clear', function(e){ e.preventDefault(); var s = document.getElementById('emoji-search'); if (s) s.value=''; populateEmojiList(); });
                $(document).on('input', '#emoji-search', function(e){ var v = (this.value||'').trim().toLowerCase(); populateEmojiList(v, document.querySelector('.emoji-cat.active')?.dataset?.cat || 'all'); });
                $(document).on('click', '.emoji-cat', function(e){ e.preventDefault(); document.querySelectorAll('.emoji-cat').forEach(x=>{ x.classList.remove('active'); x.style.background=''; x.style.color=''; }); this.classList.add('active'); this.style.background='#6f42c1'; this.style.color='#fff'; var cat = this.dataset.cat || 'all'; populateEmojiList(document.getElementById('emoji-search').value, cat); });

                // insert at cursor
                function insertAtCursor(el, text) {
                  if (!el) return;
                  var start = el.selectionStart || 0;
                  var end = el.selectionEnd || 0;
                  var val = el.value || '';
                    el.value = val.substring(0, start) + text + val.substring(end);
                    var pos = start + text.length;
                    el.selectionStart = el.selectionEnd = pos;
                    el.focus();
                  }

                  $(document).on('click', '.emoji-btn', function(e){ e.preventDefault(); var ch = $(this).text() || $(this).html(); var ta = document.getElementById('chat-input'); insertAtCursor(ta, ch); });

                  // hide when clicking outside
                  document.addEventListener('click', function(e){ var p = document.getElementById('emoji-panel'); var btn = document.getElementById('btn-emoji'); if (!p) return; if (p.style.display !== 'block') return; if (p.contains(e.target) || (btn && btn.contains(e.target))) return; hideEmojiPanel(); });
                } catch(e) { console.warn('Emoji init failed', e); }

  // ========================================================================
  // BÚSQUEDA DE CONVERSACIONES
  // ========================================================================
  
  /**
   * Busca conversaciones por número de teléfono o nombre
   * Filtra la lista visible de conversaciones
   */
  window.searchConversations = function() {
    try {
      var searchTerm = $('#search-conversations').val() || '';
      searchTerm = searchTerm.trim().toLowerCase();
      
      // Si está vacío, mostrar todas
      if (!searchTerm) {
        $('.conversation-item').show();
        return;
      }
      
      // Filtrar conversaciones
      $('.conversation-item').each(function(){
        var $item = $(this);
        var text = $item.text().toLowerCase();
        
        if (text.indexOf(searchTerm) !== -1) {
          $item.show();
        } else {
          $item.hide();
        }
      });
    } catch(e) {
      console.warn('Error en búsqueda:', e);
    }
  };
  
  // Búsqueda en tiempo real mientras se escribe
  try {
    $(document).on('input', '#search-conversations', function(){
      window.searchConversations();
    });
    
    // También buscar al presionar Enter
    $(document).on('keypress', '#search-conversations', function(e){
      if (e.which === 13) {
        e.preventDefault();
        window.searchConversations();
      }
    });
  } catch(e) {
    console.warn('Error inicializando búsqueda:', e);
  }

  // ========================================================================
  // AUTO-HIDE NAVBAR Y ESTADÍSTICAS AL HACER SCROLL
  // ========================================================================

  /**
   * Oculta automáticamente el navbar superior y las estadísticas
   * cuando el usuario hace scroll en el área de conversaciones
   */
  let lastScrollTop = 0;
  let scrollTimeout = null;

  // Detectar scroll en los contenedores específicos
  $('#conversations-list, #chat-messages').on('scroll', function() {
    const currentScroll = $(this).scrollTop();

    // Limpiar timeout previo
    if (scrollTimeout) {
      clearTimeout(scrollTimeout);
    }

    // Si está haciendo scroll hacia abajo (más de 50px desde el inicio)
    if (currentScroll > 50 && currentScroll > lastScrollTop) {
      // Ocultar navbar y estadísticas con el mismo método (max-height + opacity)
      $('.header-navbar').css('transition', 'all 0.3s ease');
      $('.header-navbar').css({
        'max-height': '0',
        'overflow': 'hidden',
        'opacity': '0',
        'padding-top': '0',
        'padding-bottom': '0',
        'margin-bottom': '0'
      });

      $('.row').has('.stats-card-primary').css('transition', 'all 0.3s ease');
      $('.row').has('.stats-card-primary').css({
        'max-height': '0',
        'overflow': 'hidden',
        'opacity': '0',
        'margin-top': '0',
        'margin-bottom': '0'
      });

      // Reducir el padding-top del contenedor para eliminar el espacio vacío
      $('.bot-chat-top-offset').css({
        'transition': 'padding-top 0.3s ease',
        'padding-top': '10px'
      });
    }
    // Si está haciendo scroll hacia arriba o cerca del inicio
    else if (currentScroll < lastScrollTop || currentScroll < 50) {
      // Mostrar navbar y estadísticas después de un pequeño delay
      scrollTimeout = setTimeout(function() {
        $('.header-navbar').css({
          'max-height': '100px',
          'overflow': 'visible',
          'opacity': '1',
          'padding-top': '',
          'padding-bottom': '',
          'margin-bottom': ''
        });

        $('.row').has('.stats-card-primary').css({
          'max-height': '200px',
          'overflow': 'visible',
          'opacity': '1',
          'margin-top': '',
          'margin-bottom': '0'
        });

        // Restaurar el padding-top original del contenedor
        $('.bot-chat-top-offset').css({
          'padding-top': '90px'
        });
      }, 200);
    }

    lastScrollTop = currentScroll;
  });

  // ========================================================================
  // OCULTAR ESTADÍSTICAS AL HACER SCROLL EN LA PÁGINA (WINDOW SCROLL)
  // ========================================================================

  /**
   * Detecta cuando las estadísticas quedan debajo del navbar al hacer scroll
   * en la página principal y las oculta con opacity
   */
  $(window).on('scroll', function() {
    const navbar = $('.header-navbar');
    const statsRow = $('.row').has('.stats-card-primary');

    if (statsRow.length > 0 && navbar.length > 0) {
      const navbarBottom = navbar.offset().top + navbar.outerHeight();
      const statsTop = statsRow.offset().top;
      const statsBottom = statsTop + statsRow.outerHeight();

      // Si las estadísticas están en la zona del navbar (superpuestas)
      // Comparamos si el top de las estadísticas está por encima del bottom del navbar
      // pero el bottom de las estadísticas está por debajo del top del navbar
      if (statsTop < navbarBottom && statsBottom > navbar.offset().top) {
        statsRow.css({
          'transition': 'opacity 0.3s ease',
          'opacity': '0',
          'pointer-events': 'none'
        });
      } else {
        statsRow.css({
          'opacity': '1',
          'pointer-events': 'auto'
        });
      }
    }
  });

              })(jQuery);
