<style>
    /* ============================================================ */
    /* ESTILOS MODERNOS PARA BOT CHAT - INTERFAZ PROFESIONAL */
    /* ============================================================ */

    /* Evitar que la cabecera fija del layout tape el contenido */
    .bot-chat-top-offset { padding-top: 90px; }
    @media (max-width: 768px) { .bot-chat-top-offset { padding-top: 110px; } }
    
    /* Ocultar posibles títulos/breadcrumbs residuales que muestran texto como "tsApp" */
    .content-header h2.content-header-title, .content-header .breadcrumb-wrapper { display: none !important; }

    /* ============================================================ */
    /* ANIMACIÓN DE FLECHA PULSANTE */
    /* ============================================================ */
    @keyframes pulse-arrow {
        0%, 100% {
            transform: translateX(0);
            opacity: 0.4;
        }
        50% {
            transform: translateX(-10px);
            opacity: 0.8;
        }
    }

    /* ============================================================ */
    /* TARJETAS DE ESTADÍSTICAS - Estilo limpio con bordes de color */
    /* ============================================================ */
    .stats-card-primary { 
        background: #ffffff;
        border: 1px solid #e0e0e0 !important;
        border-top: 3px solid #7367f0 !important;
        color: #5e5873;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px 0 rgba(34, 41, 47, 0.08);
        border-radius: 8px !important;
    }
    .stats-card-primary:hover { 
        box-shadow: 0 4px 12px 0 rgba(34, 41, 47, 0.12) !important;
    }
    
    .stats-card-warning { 
        background: #ffffff;
        border: 1px solid #e0e0e0 !important;
        border-top: 3px solid #ff9f43 !important;
        color: #5e5873;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px 0 rgba(34, 41, 47, 0.08);
        border-radius: 8px !important;
    }
    .stats-card-warning:hover { 
        box-shadow: 0 4px 12px 0 rgba(34, 41, 47, 0.12) !important;
    }
    
    .stats-card-success { 
        background: #ffffff;
        border: 1px solid #e0e0e0 !important;
        border-top: 3px solid #28c76f !important;
        color: #5e5873;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 8px 0 rgba(34, 41, 47, 0.08);
        border-radius: 8px !important;
    }
    .stats-card-success:hover { 
        box-shadow: 0 4px 12px 0 rgba(34, 41, 47, 0.12) !important;
    }

    .stats-card-primary .card-body, 
    .stats-card-warning .card-body, 
    .stats-card-success .card-body {
        color: #5e5873 !important;
    }
    
    .stats-card-primary h5, .stats-card-primary small,
    .stats-card-warning h5, .stats-card-warning small,
    .stats-card-success h5, .stats-card-success small {
        color: #5e5873 !important;
    }
    
    /* Iconos con colores específicos */
    .stats-card-primary .fas {
        color: #7367f0;
    }
    
    .stats-card-warning .fas {
        color: #ff9f43;
    }
    
    .stats-card-success .fas {
        color: #28c76f;
    }

    /* ============================================================ */
    /* LISTA DE CONVERSACIONES - Estilo limpio minimalista */
    /* ============================================================ */
    #conversations-list { 
        background: #ffffff; 
        padding: 12px;
    }
    
    .conversation-item {
        background: #ffffff;
        border: 1px solid #e0e0e0 !important;
        border-radius: 6px !important;
        margin-bottom: 6px;
        padding: 12px 14px !important;
        transition: all 0.2s ease;
        box-shadow: none;
        border-left: 3px solid #e0e0e0 !important;
    }
    
    .conversation-item:hover {
        background: #f8f9fa;
        box-shadow: 0 2px 4px rgba(115, 103, 240, 0.08);
        border-left: 3px solid #7367f0 !important;
    }
    
    .conversation-item.active {
        background: #f8f9fa;
        border: 1px solid #7367f0 !important;
        border-left: 3px solid #7367f0 !important;
        box-shadow: 0 2px 8px rgba(115, 103, 240, 0.15);
        color: #5e5873 !important;
    }
    
    .conversation-item.active * {
        color: #5e5873 !important;
    }
    
    .conversation-item.active .badge {
        background: #7367f0 !important;
        color: white !important;
    }
    
    .conversation-item strong {
        font-weight: 600;
        font-size: 14px;
        color: #5e5873;
    }
    
    .conversation-item .text-muted {
        font-size: 12px;
        color: #b9b9c3;
    }

    /* ============================================================ */
    /* PANEL DE CHAT - Fondo limpio y neutral */
    /* ============================================================ */
    #chat-messages { 
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; 
        font-size: 14px; 
        background: #f8f9fa;
        padding: 20px;
    }
    
    #chat-messages .chat-message { 
        display:flex; 
        align-items:flex-end; 
        margin: 10px 0; 
        animation: slideIn 0.25s ease;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    #chat-messages .chat-message.message-in { justify-content: flex-start; }
    #chat-messages .chat-message.message-out { justify-content: flex-end; }

    /* Burbujas de mensaje */
    #chat-messages .message-body { 
        max-width: 70%; 
        padding: 10px 14px; 
        border-radius: 8px; 
        line-height:1.45; 
        white-space:pre-wrap; 
        word-break:break-word; 
        box-shadow: 0 1px 3px rgba(0,0,0,0.08); 
        display:inline-block;
        position: relative;
    }
    
    #chat-messages .chat-message.message-in .message-body { 
        background: #f8f9fa; 
        color: #5e5873; 
        border-bottom-left-radius: 4px; 
        text-align:left;
        border: 1px solid #e0e0e0;
        border-left: 3px solid #7367f0;
    }
    
    #chat-messages .chat-message.message-out .message-body { 
        background: #dcf8c6;
        color: #303030; 
        border-bottom-right-radius: 4px; 
        text-align:left;
        border: 1px solid #c1eaa0;
        border-right: 3px solid #4caf50;
    }

    /* Indicador de cola de burbuja */
    #chat-messages .chat-message.message-in .message-body::before {
        content: '';
        position: absolute;
        left: -7px;
        bottom: 0;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 0 10px 10px;
        border-color: transparent transparent #f8f9fa transparent;
        filter: drop-shadow(-1px 0px 0px #e0e0e0);
    }
    
    #chat-messages .chat-message.message-out .message-body::before {
        content: '';
        position: absolute;
        right: -7px;
        bottom: 0;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 10px 10px 0;
        border-color: transparent transparent #dcf8c6 transparent;
        filter: drop-shadow(1px 0px 0px #c1eaa0);
    }

    /* Metadatos de tiempo */
    #chat-messages .message-meta { 
        display:block; 
        font-size:10px; 
        color: #b9b9c3; 
        margin-top:3px; 
        font-weight: 500;
    }
    
    #chat-messages .message-time { 
        font-size:10px; 
        color: #5a5a5a; 
        margin-top:3px; 
        display:block; 
        text-align:right;
        font-weight: 500;
    }

    /* Clases de servidor para fallback HTML */
    .gl-chat { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    .gl-row { display:flex; align-items:flex-end; margin:10px 0; }
    .gl-row.in { justify-content:flex-start; }
    .gl-row.out { justify-content:flex-end; }
    .gl-bubble { 
        max-width:70%; 
        padding:10px 14px; 
        border-radius:8px; 
        line-height:1.4; 
        white-space:pre-wrap; 
        word-break:break-word; 
        display:inline-block;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .gl-row.in .gl-bubble { 
        background:#f8f9fa; 
        color:#5e5873; 
        border: 1px solid #e0e0e0;
        border-left: 3px solid #7367f0;
    }
    .gl-row.out .gl-bubble { 
        background: #dcf8c6; 
        color:#303030;
        border: 1px solid #c1eaa0;
        border-right: 3px solid #4caf50;
    }

    /* Imágenes dentro de burbujas */
    #chat-messages img { max-width:240px; border-radius:10px; display:block; box-shadow: 0 2px 8px rgba(0,0,0,0.12); }

    /* ============================================================ */
    /* HEADER DEL CHAT - Diseño limpio y profesional */
    /* ============================================================ */
    .card-header {
        background: #ffffff !important;
        color: #5e5873 !important;
        border-bottom: 2px solid #e0e0e0 !important;
        padding: 12px 16px !important;
        box-shadow: 0 2px 4px 0 rgba(34, 41, 47, 0.04);
        min-height: 58px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        position: relative !important;
        z-index: 9999 !important;
    }
    
    .card-header h3, .card-header small, .card-header .text-muted {
        color: #5e5873 !important;
    }
    
    .card-header h3.card-title {
        margin-bottom: 0 !important;
        font-weight: 600;
    }
    
    .card-header .fas {
        color: #7367f0;
    }
    
    /* Modal de Eventos - Asegurar que se muestre por encima de todo */
    #modal-events-config {
        z-index: 99999 !important;
    }

    #modal-events-config .modal-backdrop {
        z-index: 99998 !important;
    }

    /* Ajustar el ancho del modal de eventos para que ocupe solo el área del chat */
    #modal-events-config .modal-dialog {
        max-width: calc(100% - 260px) !important;
        margin-left: 260px !important;
        margin-right: 0 !important;
    }

    /* Card tools para el buscador */
    .card-tools {
        margin-top: 0 !important;
    }
    
    .card-header .btn-outline-secondary,
    .card-header .btn-outline-info {
        border-color: #e0e0e0 !important;
        color: #5e5873 !important;
        transition: all 0.2s ease;
        font-size: 13px;
        padding: 5px 12px;
        background: #ffffff;
    }
    
    .card-header .btn-outline-secondary:hover,
    .card-header .btn-outline-info:hover {
        background: #7367f0 !important;
        color: #ffffff !important;
        border-color: #7367f0 !important;
    }

    /* ============================================================ */
    /* FOOTER DEL CHAT - Panel de entrada limpio */
    /* ============================================================ */
    .card-footer {
        background: #ffffff;
        border-top: 1px solid #e0e0e0;
        padding: 12px 16px;
    }
    
    #chat-input {
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        padding: 10px 14px !important;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    #chat-input:focus {
        border-color: #7367f0;
        box-shadow: 0 0 0 2px rgba(115, 103, 240, 0.1);
    }
    
    #btn-emoji {
        border-radius: 6px;
        width: 38px;
        height: 38px;
        padding: 0 !important;
        border: 1px solid #e0e0e0;
        background: white;
        font-size: 18px;
        transition: all 0.2s ease;
    }
    
    #btn-emoji:hover {
        background: #f8f9fa;
        border-color: #7367f0;
    }
    
    #btn-send {
        background: #7367f0;
        border: none;
        border-radius: 6px;
        padding: 9px 20px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px 0 rgba(115, 103, 240, 0.4);
    }
    
    #btn-send:hover {
        background: #5e50ee;
        box-shadow: 0 4px 8px 0 rgba(115, 103, 240, 0.5);
    }

    /* ============================================================ */
    /* EMOJI PICKER - Panel de emojis limpio */
    /* ============================================================ */
    #emoji-panel { 
        background: white;
        color: #5e5873;
        border: 1px solid #e0e0e0;
        box-shadow: 0 4px 16px rgba(34, 41, 47, 0.12);
        border-radius: 8px;
    }
    
    #emoji-panel #emoji-search { 
        background: #ffffff;
        color: #5e5873;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    #emoji-panel #emoji-search:focus {
        border-color: #7367f0;
        box-shadow: 0 0 0 2px rgba(115, 103, 240, 0.15);
    }
    
    #emoji-panel .emoji-btn { 
        background: #ffffff;
        color: #5e5873;
        border: 1px solid transparent;
        width: 36px;
        height: 36px;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        border-radius: 6px;
        cursor: pointer;
        margin: 2px;
        font-size: 18px;
        transition: all 0.15s ease;
    }
    
    #emoji-panel .emoji-btn:hover { 
        background: #f8f9fa;
        border-color: #7367f0;
        transform: scale(1.1);
    }
    
    #emoji-panel .emoji-cat { 
        background: #ffffff;
        color: #5e5873;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    #emoji-panel .emoji-cat.active { 
        background: #7367f0;
        color: #fff;
        border-color: #7367f0;
    }
    
    #emoji-panel .btn-light { 
        background: #ffffff;
        color: #5e5873;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    #emoji-panel .btn-light:hover {
        background: #ea5455;
        color: white;
        border-color: #ea5455;
    }

    /* ============================================================ */
    /* TARJETAS GENERALES - Estilo limpio */
    /* ============================================================ */
    .card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(34, 41, 47, 0.04);
        background: #ffffff;
    }
    
    .card-primary {
        border-top: 3px solid #7367f0 !important;
    }
    
    /* Alinear perfectamente los card-body para que las líneas divisorias coincidan */
    .chat-row .card-body {
        border-top: 1px solid #e0e0e0 !important;
        margin-top: 0 !important;
    }
    
    /* Lista de conversaciones - altura calculada para alinearse perfectamente */
    #conversations-list {
        height: calc(100vh - 400px) !important;
        min-height: 480px !important;
        overflow-y: auto;
        border-top: none !important;
        padding: 8px !important; /* Padding uniforme */
    }
    
    /* Chat de mensajes - misma altura que conversaciones */
    #chat-messages {
        height: calc(100vh - 400px) !important;
        min-height: 480px !important;
        overflow-y: auto;
        padding: 20px;
    }
    
    /* Items de conversación con mejor espaciado */
    .conversation-item {
        border-radius: 8px;
        margin-bottom: 4px;
        transition: all 0.2s ease;
    }
    
    .conversation-item:hover {
        background-color: rgba(115, 103, 240, 0.08) !important;
    }
    
    .conversation-item.active {
        background-color: rgba(115, 103, 240, 0.15) !important;
        border-left: 3px solid #7367f0 !important;
    }

    /* ============================================================ */
    /* BÚSQUEDA DE CONVERSACIONES */
    /* ============================================================ */
    #search-conversations {
        border: 1px solid #e0e0e0;
        border-radius: 6px 0 0 6px;
        padding: 6px 12px;
        transition: all 0.2s ease;
        font-size: 13px;
        height: 32px;
        border-right: none;
    }
    
    #search-conversations:focus {
        border-color: #7367f0;
        box-shadow: none;
        outline: none;
    }
    
    .card-tools .btn-primary,
    .card-header .btn-primary {
        background: #7367f0;
        border: none;
        border-radius: 0 6px 6px 0;
        transition: all 0.2s ease;
        box-shadow: none;
        height: 32px;
        padding: 0 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
    }
    
    .card-tools .btn-primary:hover,
    .card-header .btn-primary:hover {
        background: #5e50ee;
        color: #ffffff;
    }
    
    .card-tools .btn-primary i,
    .card-header .btn-primary i {
        color: #ffffff;
        font-size: 13px;
    }
    
    .card-tools .input-group,
    .card-header .input-group {
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border-radius: 6px;
        overflow: hidden;
    }    /* ============================================================ */
    /* ALTURA DE COLUMNAS - Flexbox para layout consistente */
    /* ============================================================ */
    .chat-row { 
        align-items: stretch; 
        min-height: calc(100vh - 260px); 
        gap: 0 !important; /* Sin espacio entre columnas - estilo WhatsApp */
    }
    
    .chat-row > [class*="col-"] { 
        display: flex; 
        flex-direction: column; 
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    
    /* Primera columna (conversaciones) - bordes redondeados solo a la izquierda */
    .chat-row > .col-md-4 .card {
        border-radius: 8px 0 0 8px !important;
        border-right: 2px solid #7367f0 !important;
        margin-bottom: 0 !important;
        overflow: visible !important;
    }
    
    /* Segunda columna (chat) - bordes redondeados solo a la derecha */
    .chat-row > .col-md-8 .card {
        border-radius: 0 8px 8px 0 !important;
        border-left: none !important;
        margin-bottom: 0 !important;
        overflow: visible !important;
    }
    
    /* Headers de ambas columnas - sin bordes redondeados individuales */
    .chat-row .card-header {
        border-radius: 0 !important;
    }
    
    /* Primera columna header - borde redondeado superior izquierdo */
    .chat-row > .col-md-4 .card-header {
        border-radius: 8px 0 0 0 !important;
    }
    
    /* Segunda columna header - borde redondeado superior derecho */
    .chat-row > .col-md-8 .card-header {
        border-radius: 0 8px 0 0 !important;
    }
    
    .chat-row > [class*="col-"] .card { 
        flex: 1 1 auto; 
        display:flex; 
        flex-direction:column; 
    }
    
    .chat-row > [class*="col-"] .card .card-body { 
        flex:1 1 auto; 
        overflow:auto; 
    }
    
    /* Asegurar que ambos card-body empiecen exactamente a la misma altura */
    .chat-row .card-header + .card-body {
        border-top: 1px solid #e0e0e0 !important;
        margin: 0 !important;
        padding-top: 0 !important;
    }
    
    /* La línea divisoria debe continuar perfectamente de izquierda a derecha */
    .chat-row > .col-md-4 .card-header {
        border-bottom: none !important;
    }
    
    .chat-row > .col-md-8 .card-header {
        border-bottom: none !important;
    }
    
    /* Alinear las líneas horizontales perfectamente */
    .chat-row > .col-md-4 .card-body,
    .chat-row > .col-md-8 .card-body {
        border-top: 1px solid #e0e0e0 !important;
    }

    /* ============================================================ */
    /* SCROLLBARS PERSONALIZADOS */
    /* ============================================================ */
    #conversations-list::-webkit-scrollbar,
    #chat-messages::-webkit-scrollbar {
        width: 6px;
    }
    
    #conversations-list::-webkit-scrollbar-track,
    #chat-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
    }
    
    #conversations-list::-webkit-scrollbar-thumb,
    #chat-messages::-webkit-scrollbar-thumb {
        background: #b0b0b0;
        border-radius: 6px;
    }
    
    #conversations-list::-webkit-scrollbar-thumb:hover,
    #chat-messages::-webkit-scrollbar-thumb:hover {
        background: #7367f0;
    }

    /* ============================================================ */
    /* OCULTAR CONTENIDO DEL CHAT INICIALMENTE */
    /* ============================================================ */
    /* Al entrar al módulo, mostrar mensaje de bienvenida pero ocultar footer */
    .col-md-8 .card-footer {
        display: none;
    }
    
    /* El card-body del chat siempre visible para mostrar el mensaje de bienvenida */
    .col-md-8 .card-body {
        background: #f8f9fa; /* Fondo suave */
    }

</style>
<div class="content-wrapper bot-chat-top-offset">
    <section class="content" style="padding-top: 10px;">
        <div class="container-fluid">
            <!-- Estadísticas compactas -->
            <div class="row" style="margin-bottom: 0; margin-left: 0; margin-right: 0;">
                <div class="col-lg-4 col-12" style="margin-bottom: 0; padding-bottom: 0;">
                    <div class="card shadow-sm stats-card-primary" style="border-radius: 8px; margin-bottom: 0;">
                        <div class="card-body d-flex align-items-center py-1 px-2">
                            <div class="me-2"><i class="fas fa-comments" style="font-size: 20px;"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold" id="stat-total" style="font-size: 16px;"><?= $stats['total_conversations'] ?? 0 ?></h6>
                                <small style="font-size: 10px;">Total Conversaciones</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-12" style="margin-bottom: 0; padding-bottom: 0;">
                    <div class="card shadow-sm stats-card-warning" style="border-radius: 8px; margin-bottom: 0;">
                        <div class="card-body d-flex align-items-center py-1 px-2">
                            <div class="me-2"><i class="fas fa-robot" style="font-size: 20px;"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold" id="stat-unread" style="font-size: 16px;"><?= $stats['unread_messages'] ?? 0 ?></h6>
                                <small style="font-size: 10px;">Conversaciones con Bot ON</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-12" style="margin-bottom: 0; padding-bottom: 0;">
                    <div class="card shadow-sm stats-card-success" style="border-radius: 8px; margin-bottom: 0;">
                        <div class="card-body d-flex align-items-center py-1 px-2">
                            <div class="me-2"><i class="fas fa-user-headset" style="font-size: 20px;"></i></div>
                            <div>
                                <h6 class="mb-0 fw-bold" id="stat-admin-chats" style="font-size: 16px;"><?= $stats['admin_active_chats'] ?? 0 ?></h6>
                                <small style="font-size: 10px;">Chats Atendidos por Admin</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de conversaciones -->
            <div class="row" style="margin-top: 10px; padding-top: 0;">
                <div class="col-12" style="padding-top: 0;">
                    <div class="card card-primary" style="margin-bottom: 0;">
                        <div class="card-header d-flex p-0" style="border-bottom: 1px solid #e0e0e0; position: relative; z-index: 1;">
                            <!-- Header Conversaciones -->
                            <div class="col-md-4 d-flex justify-content-between align-items-center" style="padding: 10px 14px; border-right: 2px solid #7367f0;">
                                <h3 class="card-title mb-0" style="font-size: 15px;"><i class="fas fa-comments me-2" style="font-size: 15px;"></i>Conversaciones</h3>
                                <div class="input-group" style="width: auto; max-width: 220px; flex: 0 1 auto;">
                                    <input type="text" id="search-conversations" class="form-control form-control-sm" placeholder="Buscar..." style="min-width: 130px; font-size: 13px; padding: 5px 10px;">
                                    <button class="btn btn-primary btn-sm" onclick="searchConversations()" style="padding: 5px 12px;"><i class="fas fa-search" style="font-size: 13px;"></i></button>
                                </div>
                            </div>

                            <!-- Header Chat -->
                            <div class="col-md-8 d-flex justify-content-between align-items-center" id="chat-header" style="padding: 10px 14px;">
                                <div>
                                    <h3 class="card-title mb-0" style="font-size: 15px;"><i class="fas fa-user-circle me-2" style="font-size: 15px;"></i><span id="chat-contact">Conversación</span></h3>
                                    <small id="chat-phone" style="opacity: 0.9; font-size: 12px;">Número: -</small>
                                </div>
                                <div class="d-flex align-items-center">
                                    <small id="chat-last-activity" class="me-2" style="opacity: 0.9; font-size: 12px;"></small>
                                    <button id="btn-force-load" class="btn btn-sm btn-outline-secondary me-2" style="padding: 5px 12px; font-size: 13px;"><i class="fas fa-sync-alt me-1" style="font-size: 12px;"></i>Recargar</button>
                                    <button id="btn-open-events" class="btn btn-sm btn-outline-info ms-2" style="padding: 5px 12px; font-size: 13px;"><i class="fas fa-cog me-1" style="font-size: 12px;"></i>Eventos</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body p-0 d-flex" style="height: calc(100vh - 250px);">
            <!-- Lista de Conversaciones -->
            <div class="col-md-4 p-2" id="conversations-list" style="border-right: 2px solid #7367f0; overflow-y: auto; height: 100%;">
                <div class="text-center p-3 text-muted"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
            </div>                            <!-- Panel de Mensajes -->
                            <div class="col-md-8 p-0 d-flex flex-column" style="height: 100%;">
                                <div id="chat-messages" style="flex: 1; overflow-y: auto; padding: 20px; max-height: calc(100% - 80px);">
                                    <div class="d-flex align-items-center justify-content-center" style="height: 100%; flex-direction: column;">
                                        <div class="text-center" style="max-width: 400px; padding: 40px 20px;">
                                            <div style="margin-bottom: 30px;">
                                                <i class="fas fa-comments" style="font-size: 80px; color: #7367f0; opacity: 0.2;"></i>
                                            </div>
                                            <h4 style="color: #5e5873; font-weight: 600; margin-bottom: 12px;">
                                                Bot Chat de WhatsApp
                                            </h4>
                                            <p style="color: #b9b9c3; font-size: 15px; line-height: 1.6; margin-bottom: 0;">
                                                Seleccione una conversación de la lista para visualizar los mensajes y poder responder
                                            </p>
                                            <div style="margin-top: 30px;">
                                                <i class="fas fa-arrow-left" style="font-size: 24px; color: #7367f0; opacity: 0.4; animation: pulse-arrow 2s infinite;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Footer para enviar mensajes -->
                                <div class="card-footer">
                                    <div class="container-fluid p-2">
                                        <div class="d-flex align-items-center emoji-anchor" style="gap:10px; position:relative;">
                                            <button id="btn-emoji" class="btn btn-light" title="Emojis">😊</button>
                                            <textarea id="chat-input" class="form-control" style="flex:1; min-height:42px; max-height:120px; resize:vertical;" placeholder="Escribe un mensaje..."></textarea>
                                            <button id="btn-send" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Enviar</button>
                                            <div id="emoji-panel" style="display:none; position: absolute; z-index:1100; background:#fff; border:2px solid #e0e0e0; padding:12px; box-shadow:0 12px 32px rgba(0,0,0,0.15); width:360px; border-radius:16px; box-sizing:border-box; right:16px; left:auto; bottom:calc(100% + 10px); max-width:calc(100% - 16px);">
                                                <div class="d-flex mb-2" style="gap:8px;">
                                                    <input id="emoji-search" class="form-control form-control-sm" placeholder="Buscar emojis..." style="flex:1;padding:8px 12px;" />
                                                    <button id="emoji-clear" class="btn btn-sm btn-light" title="Limpiar">✖</button>
                                                </div>
                                                <div class="mb-2" style="display:flex;gap:6px;flex-wrap:wrap">
                                                    <button class="emoji-cat btn btn-sm btn-outline-secondary active" data-cat="all">Todos</button>
                                                    <button class="emoji-cat btn btn-sm btn-outline-secondary" data-cat="smileys">😊</button>
                                                    <button class="emoji-cat btn btn-sm btn-outline-secondary" data-cat="people">👤</button>
                                                    <button class="emoji-cat btn btn-sm btn-outline-secondary" data-cat="gestures">👍</button>
                                                    <button class="emoji-cat btn btn-sm btn-outline-secondary" data-cat="objects">💼</button>
                                                    <button class="emoji-cat btn btn-sm btn-outline-secondary" data-cat="symbols">🔣</button>
                                                </div>
                                                <div id="emoji-list" style="display:flex;flex-wrap:wrap;gap:4px; max-height:280px; overflow:auto;">
                                                    <!-- emojis inyectados dinámicamente -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Enviar Mensaje (mantener para compatibilidad con JS antiguo) -->
    <div class="modal fade" id="modal-send-message" tabindex="-1" aria-labelledby="modalSendLabel">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSendLabel">Enviar Mensaje</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="chat-id-modal">
                    <div class="mb-3">
                        <label class="form-label">Mensaje</label>
                        <textarea id="chat-message-modal" class="form-control" rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btn-send-modal">Enviar</button>
                </div>
            </div>
        </div>
    </div>
    <script>
    (function(){
        var btn = document.getElementById('btn-emoji');
        var panel = document.getElementById('emoji-panel');
        var anchor = document.querySelector('.emoji-anchor');
        if (!btn || !panel || !anchor) return;

        function positionPanel(){
            panel.style.display = 'block';
            panel.style.transform = 'none';
            var panelWidth = panel.offsetWidth || 340;
            var btnRect = btn.getBoundingClientRect();
            var anchorRect = anchor.getBoundingClientRect();
            // calculate left relative to anchor
            var left = btn.offsetLeft + btn.offsetWidth - panelWidth;
            if (left < 8) left = 8;
            var maxLeft = Math.max(8, anchor.clientWidth - panelWidth - 8);
            if (left > maxLeft) left = maxLeft;
            panel.style.left = left + 'px';
            panel.style.right = 'auto';
            panel.style.bottom = (anchor.clientHeight + 8) + 'px';
        }

        btn.addEventListener('click', function(e){
            e.stopPropagation();
            if (panel.style.display === 'block'){
                panel.style.display = 'none';
            } else {
                positionPanel();
                panel.style.display = 'block';
            }
        });

        document.addEventListener('click', function(e){
            if (panel.style.display === 'block' && !panel.contains(e.target) && e.target !== btn){
                panel.style.display = 'none';
            }
        });

        window.addEventListener('resize', function(){
            if (panel.style.display === 'block') positionPanel();
        });
    })();
    </script>
</div>

    <!-- Modal Configurar Eventos (cargado dinámicamente) -->
<div class="modal fade" id="modal-events-config" tabindex="-1" aria-labelledby="modalEventsLabel">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEventsLabel">Configuración de Eventos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-events-body">
                <div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Ver Imagen Ampliada -->
<style>
    #modal-image-viewer .modal-dialog {
        max-width: 90vw;
        margin: 1.75rem auto;
    }
    
    #modal-image-viewer .modal-content {
        background: transparent;
        border: none;
        box-shadow: none;
    }
    
    #modal-image-viewer .modal-backdrop {
        background-color: rgba(0, 0, 0, 0.92);
    }
    
    .image-viewer-container {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 85vh;
    }
    
    .image-viewer-close {
        position: absolute;
        top: -50px;
        right: 0;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 1050;
    }
    
    .image-viewer-close:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: rotate(90deg) scale(1.1);
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    .image-viewer-img {
        max-width: 100%;
        max-height: 80vh;
        object-fit: contain;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
        animation: zoomIn 0.3s ease;
    }
    
    @keyframes zoomIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .image-viewer-actions {
        margin-top: 24px;
        display: flex;
        gap: 12px;
        animation: slideUp 0.4s ease;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .btn-image-action {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 10px 24px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-image-action:hover {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
        color: white;
    }
    
    .btn-image-action i {
        font-size: 16px;
    }
</style>

<div class="modal fade" id="modal-image-viewer" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="image-viewer-container">
                <button type="button" class="image-viewer-close" data-bs-dismiss="modal" aria-label="Close">
                    ×
                </button>
                <img id="modal-image-src" src="" alt="Imagen" class="image-viewer-img">
                <div class="image-viewer-actions">
                    <a id="modal-image-download" href="" download class="btn-image-action">
                        <i class="fas fa-download"></i>
                        <span>Descargar</span>
                    </a>
                    <button type="button" class="btn-image-action" onclick="window.open(document.getElementById('modal-image-src').src, '_blank')">
                        <i class="fas fa-external-link-alt"></i>
                        <span>Abrir en nueva pestaña</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para abrir modal de imagen
function openImageModal(imageUrl) {
    const modalEl = document.getElementById('modal-image-viewer');
    const imgEl = document.getElementById('modal-image-src');
    const downloadLink = document.getElementById('modal-image-download');
    
    if (modalEl && imgEl && downloadLink) {
        imgEl.src = imageUrl;
        downloadLink.href = imageUrl;
        
        // Usar Bootstrap 5 modal API
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}
</script>

