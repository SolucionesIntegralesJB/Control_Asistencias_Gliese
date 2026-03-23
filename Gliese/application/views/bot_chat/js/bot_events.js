// ============================================================================
// BOT EVENTS MODULE - Gestión de Configuración de Eventos Automatizados
// ============================================================================
// Este módulo maneja la configuración de eventos automatizados del bot como
// la limpieza de mensajes antiguos. Permite crear, editar y ejecutar eventos
// con intervalos personalizables (minutos, horas, días, semanas, meses, años).
//
// Funcionalidades principales:
// - Listar eventos configurados desde la base de datos
// - Editar configuración de intervalos y retención de mensajes
// - Ejecutar eventos manualmente
// - Importar eventos desde MySQL scheduler
// - Visualizar logs de ejecución
// ============================================================================

// ============================================================================
// PREVENCIÓN DE INICIALIZACIÓN DUPLICADA
// ============================================================================
// Bandera global para evitar registrar múltiples veces los event handlers
// cuando el módulo se carga dinámicamente o se inyecta vía AJAX
window.bot_events_handlers_initialized = window.bot_events_handlers_initialized || false;

// ============================================================================
// FUNCIÓN PRINCIPAL DE INICIALIZACIÓN
// ============================================================================
/**
 * Inicializa el módulo de eventos del bot
 * @param {HTMLElement} root - Elemento raíz del DOM donde se buscarán los elementos
 */
function initBotEvents(root) {
    root = root || document;

    // ========================================================================
    // UTILIDADES Y HELPERS
    // ========================================================================

    /**
     * Helper simplificado para querySelector dentro del root
     * @param {string} s - Selector CSS
     * @returns {HTMLElement} Elemento encontrado o null
     */
    function qs(s) {
        return root.querySelector(s);
    }

    /**
     * Resuelve la URL base de la aplicación
     * Intenta usar BASE_URL del servidor, si no existe detecta automáticamente
     * @returns {string} URL base con / al final (ej: "http://localhost/gliese/")
     */
    function resolveBase() {
        // Preferir BASE_URL explícita del servidor si está disponible
        if (typeof BASE_URL !== 'undefined' && BASE_URL) {
            var b = BASE_URL;
            if (b.charAt(b.length - 1) !== '/') b += '/';
            return b;
        }
        
        // Fallback: construir desde origin + primer segmento de path
        var origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
        var path = window.location.pathname || '/';
        
        // Detectar raíz de la app (ej: '/gliese/' del path completo)
        var parts = path.split('/').filter(Boolean);
        if (parts.length > 0) {
            var appRoot = '/' + parts[0] + '/';
            return origin + appRoot;
        }
        return origin + '/';
    }

    var BASE = resolveBase();

    /**
     * Intenta múltiples URLs candidatas en orden, retorna la primera exitosa
     * Útil para adaptar el código a diferentes configuraciones de rutas
     * @param {Array} candidates - Array de URLs a intentar
     * @param {Object} options - Opciones para fetch (método, headers, etc)
     * @returns {Promise} Promesa con {resp, url} de la primera URL exitosa
     */
    function smartFetch(candidates, options) {
        options = options || {};
        return new Promise(function(resolve, reject) {
            var i = 0;
            
            // Función recursiva para probar cada URL
            var next = function() {
                // Si ya probamos todas, rechazar con error
                if (i >= candidates.length) {
                    return reject(new Error('Todas las URLs candidatas fallaron'));
                }
                
                var url = candidates[i++];
                
                // Intentar fetch con la URL actual
                fetch(url, options).then(function(r) {
                    // Aceptar respuestas 2xx (200-299)
                    if (r && (r.status === 200 || r.ok)) {
                        return resolve({resp: r, url: url});
                    }
                    // Si falla, intentar la siguiente
                    next();
                }).catch(function(err) {
                    // En caso de error de red, intentar la siguiente
                    next();
                });
            };
            
            // Iniciar el proceso
            next();
        });
    }

    /**
     * Normaliza un valor a entero, retorna null si no es válido
     * @param {*} x - Valor a normalizar
     * @returns {number|null} Entero o null si es inválido
     */
    function normalizeInt(x) {
        var n = parseInt(x);
        return isNaN(n) ? null : n;
    }

    /**
     * Compara los datos enviados con los guardados en el servidor
     * Verifica que los campos numéricos coincidan
     * @param {Object} sent - Datos enviados al servidor
     * @param {Object} srv - Datos devueltos por el servidor
     * @returns {boolean} true si coinciden, false si hay diferencias
     */
    function compareSaved(sent, srv) {
        try {
            if (!srv) return false;
            // compare key numeric fields: interval_seconds, retention_minutes, batch_size, enabled
            var s_interval = normalizeInt(sent.interval_seconds);
            var s_ret = normalizeInt(sent.retention_minutes);
            var s_batch = normalizeInt(sent.batch_size);
            var s_en = normalizeInt(sent.enabled);
            var r_interval = normalizeInt(srv.interval_seconds);
            var r_ret = normalizeInt(srv.retention_minutes);
            var r_batch = normalizeInt(srv.batch_size);
            var r_en = normalizeInt(srv.enabled);
            // If any of the numeric fields are non-null in sent, require equality
            if (s_interval !== null && r_interval !== s_interval) return false;
            if (s_ret !== null && r_ret !== s_ret) return false;
            if (s_batch !== null && r_batch !== s_batch) return false;
            if (s_en !== null && r_en !== s_en) return false;
            return true;
        } catch(e) {
            return false;
        }
    }

    // ========================================================================
    // HELPERS DE FORMULARIO
    // ========================================================================

    /**
     * Obtiene un campo del formulario por su atributo name
     * @param {HTMLFormElement} form - Formulario donde buscar
     * @param {string} name - Nombre del campo (atributo name)
     * @returns {HTMLElement|null} Elemento encontrado o null
     */
    function getField(form, name) {
        if (!form) return null;
        return form.querySelector('[name="' + name + '"]');
    }

    /**
     * Obtiene el valor de un campo del formulario
     * @param {HTMLFormElement} form - Formulario donde buscar
     * @param {string} name - Nombre del campo
     * @returns {string|null} Valor del campo o null si no existe
     */
    function getVal(form, name) {
        var el = getField(form, name);
        return el ? el.value : null;
    }

    /**
     * Establece el valor de un campo del formulario
     * @param {HTMLFormElement} form - Formulario donde buscar
     * @param {string} name - Nombre del campo
     * @param {*} value - Valor a establecer
     */
    function setVal(form, name, value) {
        var el = getField(form, name);
        if (el) {
            el.value = (value === null || typeof value === 'undefined') ? '' : value;
        }
    }

    // ========================================================================
    // GESTIÓN DE MODALES
    // ========================================================================

    /**
     * Cierra el modal de configuración de eventos de forma robusta
     * Intenta varios métodos y limpia manualmente backdrops residuales
     * @returns {boolean} true si se cerró exitosamente
     */
    function closeEventModal() {
        console.log('🚪 Intentando cerrar modal...');
        try {
            // Método 1: Buscar el modal específico por ID
            var modalEl = document.getElementById('modal-events-config');
            if (modalEl) {
                console.log('✅ Modal encontrado por ID: #modal-events-config');
                
                // Intentar con Bootstrap Modal API
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        console.log('✅ Usando instancia existente de Bootstrap Modal');
                        modalInstance.hide();
                    } else {
                        console.log('✅ No hay instancia, cerrando manualmente');
                        // Si no hay instancia, cerrar manualmente
                        modalEl.classList.remove('show');
                        modalEl.style.display = 'none';
                        modalEl.setAttribute('aria-hidden', 'true');
                        modalEl.removeAttribute('aria-modal');
                    }
                    
                    // LIMPIEZA FORZADA Y AGRESIVA de TODO
                    setTimeout(function() {
                        console.log('🧹 Iniciando limpieza forzada...');
                        
                        // 1. Remover TODOS los backdrops sin excepción
                        var backdrops = document.querySelectorAll('.modal-backdrop');
                        console.log('🧹 Encontrados ' + backdrops.length + ' backdrops');
                        backdrops.forEach(function(bd) {
                            if (bd && bd.parentNode) {
                                bd.parentNode.removeChild(bd);
                                console.log('🗑️ Backdrop eliminado');
                            }
                        });
                        
                        // 2. Limpiar clases del body
                        document.body.classList.remove('modal-open');
                        document.body.style.overflow = '';
                        document.body.style.paddingRight = '';
                        document.body.style.removeProperty('overflow');
                        document.body.style.removeProperty('padding-right');
                        
                        // 3. Asegurar que el modal esté oculto
                        if (modalEl) {
                            modalEl.style.display = 'none';
                            modalEl.classList.remove('show');
                            modalEl.setAttribute('aria-hidden', 'true');
                            modalEl.removeAttribute('aria-modal');
                        }
                        
                        // 4. Limpiar TODOS los modales por si acaso
                        var allModals = document.querySelectorAll('.modal');
                        allModals.forEach(function(m) {
                            if (m.id !== 'modal-events-config') {
                                m.style.display = 'none';
                                m.classList.remove('show');
                            }
                        });
                        
                        console.log('✅ Limpieza completa terminada');
                    }, 350);
                    
                    return true;
                }
            }

            // Método 2: Buscar botón close del modal
            var closeBtn = document.querySelector('#modal-events-config .btn-close');
            if (closeBtn) {
                console.log('✅ Método 2: Click en botón close');
                closeBtn.click();
                
                // Forzar limpieza
                setTimeout(function() {
                    var backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(function(bd) {
                        if (bd && bd.parentNode) bd.parentNode.removeChild(bd);
                    });
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 400);
                
                return true;
            }

            // Método 3: Buscar cualquier modal visible y cerrarlo manualmente
            var visibleModal = document.querySelector('.modal.show');
            if (visibleModal) {
                console.log('✅ Método 3: Cerrando modal visible manualmente');
                visibleModal.classList.remove('show');
                visibleModal.style.display = 'none';
                visibleModal.setAttribute('aria-hidden', 'true');
                visibleModal.removeAttribute('aria-modal');
                document.body.classList.remove('modal-open');
                
                // Remover backdrop
                setTimeout(function() {
                    var backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(function(bd) {
                        if (bd && bd.parentNode) bd.parentNode.removeChild(bd);
                    });
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    console.log('✅ Modal y backdrop limpiados (método manual)');
                }, 100);
                
                return true;
            }

            console.warn('⚠️ No se pudo encontrar el modal para cerrar');
            return false;
        } catch(e) {
            console.error('❌ Error al cerrar modal:', e);
            return false;
        }
    }

    // ==========================================
    // NOTIFICACIONES DE USUARIO
    // ==========================================

    /**
     * Muestra una notificación flotante al usuario
     * @param {string} msg - Mensaje a mostrar
     * @param {string} type - Tipo: 'success', 'error' o 'info'
     */
    function showNotification(msg, type) {
        try {
            // Floating top-center notice (high z-index to overcome app modals)
            var id = 'bot-events-notice';
            var existing = document.getElementById(id);
            if (existing) existing.parentNode.removeChild(existing);

            var d = document.createElement('div');
            d.id = id;
            d.textContent = msg;
            d.style.position = 'fixed';
            d.style.left = '50%';
            d.style.top = '12px';
            d.style.transform = 'translateX(-50%)';
            d.style.zIndex = 2147483647; // use a very large z-index to try to be above any modal overlays
            d.style.padding = '10px 18px';
            d.style.borderRadius = '6px';
            d.style.boxShadow = '0 4px 12px rgba(0,0,0,0.12)';
            d.style.fontSize = '14px';
            d.style.color = '#fff';
            d.style.opacity = '0';
            d.style.transition = 'opacity 200ms ease';

            if (type === 'success') {
                d.style.background = '#2f855a';
            } else if (type === 'error') {
                d.style.background = '#c53030';
            } else {
                d.style.background = '#4a5568';
            }

            document.body.appendChild(d);
            // force reflow then show
            void d.offsetWidth;
            d.style.opacity = '1';

            setTimeout(function() {
                try {
                    d.style.opacity = '0';
                    setTimeout(function() {
                        try {
                            if (d && d.parentNode) d.parentNode.removeChild(d);
                        } catch(e) {}
                    }, 250);
                } catch(e) {}
            }, 4000);

            // Also show an inline, persistent message near the form (helps when floating notice is hidden)
            try {
                var inlineId = 'bot-events-inline-notice';
                var form = document.querySelector('#event-form');
                if (form) {
                    var container = form.querySelector('.events-inline-notice-container');
                    if (!container) {
                        container = document.createElement('div');
                        container.className = 'events-inline-notice-container';
                        container.style.marginTop = '12px';
                        form.appendChild(container);
                    }
                    var inline = document.getElementById(inlineId);
                    if (!inline) {
                        inline = document.createElement('div');
                        inline.id = inlineId;
                        inline.style.padding = '8px 12px';
                        inline.style.borderRadius = '6px';
                        inline.style.color = '#fff';
                        inline.style.display = 'inline-block';
                        inline.style.fontSize = '13px';
                        inline.style.marginRight = '8px';
                        container.appendChild(inline);
                    }
                    inline.textContent = msg;
                    if (type === 'success') {
                        inline.style.background = '#2f855a';
                    } else if (type === 'error') {
                        inline.style.background = '#c53030';
                    } else {
                        inline.style.background = '#4a5568';
                    }
                    // remove inline after a longer timeout so user can notice it
                    setTimeout(function() {
                        try {
                            var el = document.getElementById(inlineId);
                            if (el && el.parentNode) el.parentNode.removeChild(el);
                        } catch(e) {}
                    }, 6000);
                }
            } catch(e) {
                console.warn('inline notify failed', e);
            }
        } catch(e) {
            console.warn('showNotification failed', e);
        }
    }

    // ==========================================
    // FUNCIONES DE DATOS - CARGA Y SINCRONIZACIÓN
    // ==========================================

    /**
     * Asegura que el formulario muestre el id/name autoritativo del evento (prefer id=2)
     */
    function ensureFormNameSync() {
        try {
            var f = document.querySelector('#event-form');
            if (!f) return;
            var nameEl = f.querySelector('[name="name"]');
            var idEl = f.querySelector('[name="id"]');
            var curName = nameEl ? String(nameEl.value||'').trim() : '';
            if (curName && curName.length>0) return; // already populated

            // 1) prefer current cached value if available
            if (window.bot_events_current && window.bot_events_current.name) {
                if (nameEl) { nameEl.value = window.bot_events_current.name; try{ nameEl.readOnly = true; }catch(e){} }
                if (idEl && window.bot_events_current.id) idEl.value = window.bot_events_current.id;
                return;
            }

            // 2) fetch authoritative list and prefer id=2 (fallback to first)
            var listCandidates = [ BASE + 'index.php?url=Bot_Events/list', BASE + 'index.php?url=C_Bot_Events/list', BASE + 'index.php/C_Bot_Events/list', BASE + 'C_Bot_Events/list' ];
            smartFetch(listCandidates, { credentials: 'same-origin' }).then(function(res){ return res.resp.json().catch(function(){ return null; }); }).then(function(j){
                if (!j || !j.success || !j.data || !Array.isArray(j.data) || j.data.length===0) return;
                var found = j.data.find(function(x){ return String(x.id) === '2'; }) || j.data[0];
                if (!found) return;
                window.bot_events_current = window.bot_events_current || {};
                window.bot_events_current.id = found.id;
                window.bot_events_current.name = found.name || '';
                if (idEl) idEl.value = found.id;
                if (nameEl) { nameEl.value = found.name || ''; try{ nameEl.readOnly = true; }catch(e){} }
            }).catch(function(){ /* ignore */ });
        } catch(e) { console.warn('ensureFormNameSync failed', e); }
    }

    /**
     * Carga la lista de eventos y renderiza la tabla
     * Intenta múltiples endpoints candidatos con fallback
     */
    function loadList() {
        var candidates = [
            BASE + 'index.php?url=Bot_Events/list',
            BASE + 'index.php?url=C_Bot_Events/list',
            BASE + 'C_Bot_Events/list',
            BASE + 'index.php/C_Bot_Events/list'
        ];
        
        var tryFetchList = function(i) {
            if (i >= candidates.length) {
                console.warn('All candidates failed to fetch events list');
                return;
            }
            var url = candidates[i];
            fetch(url, { credentials: 'same-origin' })
                .then(function(r) { return r.json().catch(function() { return null; }); })
                .then(function(j) {
                    if (!j || j.msg === 'session_expired') {
                        if (j && j.msg === 'session_expired') {
                            console.warn('Session expired while loading events from', url);
                        } else {
                            console.warn('Empty or non-JSON response from', url);
                        }
                        // try next candidate
                        tryFetchList(i + 1);
                        return;
                    }
                    
                    // success: render table and optionally auto-select first
                    const table = document.getElementById('events-table');
                    if (table) table.innerHTML = '';
                    
                    if (j.data && Array.isArray(j.data)) {
                        j.data.forEach(function(s) {
                            try {
                                const div = document.createElement('div');
                                div.className = 'p-2 border mb-1';
                                var intervalMin = Math.round((s.interval_seconds || 0) / 60);
                                div.innerHTML = '<strong>' + s.name + '</strong> &nbsp; <small>enabled:' + s.enabled + ' interval:' + intervalMin + 'm retention:' + s.retention_minutes + 'm batch:' + s.batch_size + '</small> <button class="btn btn-sm btn-link load" data-id="' + s.id + '">Editar</button> <button class="btn btn-sm btn-link logs" data-id="' + s.id + '">Logs</button>';
                                if (table) table.appendChild(div);
                            } catch(e) {
                                console.warn('render row failed', e);
                            }
                        });
                    }
                    
                    // attach handlers
                    try {
                        document.querySelectorAll('#events-table .load').forEach(function(btn) {
                            btn.addEventListener('click', function() { loadOne(this.dataset.id); });
                        });
                    } catch(e) {}
                    
                    try {
                        document.querySelectorAll('#events-table .logs').forEach(function(btn) {
                            btn.addEventListener('click', function() { loadLogs(this.dataset.id); });
                        });
                    } catch(e) {}

                    // Auto-select first event so the form edits an existing setting instead of creating a new one
                    try {
                        // If the server already populated the form (modal rendered server-side), do NOT overwrite it on load.
                        if (window.bot_events_server_populated) {
                            // leave the existing form values as-is; provide a manual refresh button to sync.
                            // but still cache the first event id for saves
                            if (j.data && j.data.length > 0) {
                                try {
                                    window.bot_events_current = window.bot_events_current || {};
                                    window.bot_events_current.id = j.data[0].id;
                                    window.bot_events_current.name = j.data[0].name;
                                } catch(e) {}
                            }
                        } else {
                            if (j.data && j.data.length > 0) {
                                var first = j.data[0];
                                try {
                                    window.bot_events_current = { id: first.id, name: first.name };
                                } catch(e) {}
                                if (first && first.id) {
                                    loadOne(first.id);
                                }
                            }
                        }
                    } catch(e) {
                        console.warn('auto-select first event failed', e);
                    }

                    // Ensure the form shows the authoritative id/name after the list is loaded
                    try {
                        ensureFormNameSync();
                    } catch(e) {}
                })
                .catch(function(err) {
                    console.warn('Fetch error for', url, err);
                    tryFetchList(i + 1);
                });
        };
        
        tryFetchList(0);
    }

    // ========================================================================
    // IMPORTACIÓN DE EVENTOS DESDE MYSQL
    // ========================================================================

    /**
     * Manejador para importar eventos desde el scheduler de MySQL
     * Escucha clicks en el botón de importar y sincroniza eventos
     */
    document.addEventListener('click', function(e) {
        if (e.target && e.target.id === 'btn-import-events') {
            // Confirmar acción con el usuario
            if (!confirm('¿Importar eventos desde la lista de eventos MySQL a la configuración?')) return;
            
            // URLs candidatas para el endpoint de importación
            var importCandidates = [
                BASE + 'index.php?url=Bot_Events/import',
                BASE + 'index.php?url=C_Bot_Events/import',
                BASE + 'C_Bot_Events/import'
            ];
            
            // Realizar la importación
            smartFetch(importCandidates, { method: 'POST', credentials: 'same-origin' })
                .then(function(res) { return res.resp.json().catch(function() { return null; }); })
                .then(function(j) {
                    if (j.status === 'OK' || j.success) {
                        alert('Importados: ' + (j.imported || 0) + ' de ' + (j.found || 0));
                        loadList(); // Recargar la lista para mostrar los nuevos eventos
                    } else {
                        alert('Error: ' + (j.msg || JSON.stringify(j)));
                    }
                })
                .catch(function(err) {
                    alert('Error: ' + err.message);
                });
        }
    });

    // ========================================================================
    // CARGA DE EVENTOS INDIVIDUALES
    // ========================================================================

    /**
     * Carga un evento específico por ID y rellena el formulario con sus datos
     * Convierte interval_seconds a la unidad más apropiada (año, mes, semana, día, hora, minuto)
     * @param {number|string} id - ID del evento a cargar desde la base de datos
     */
    function loadOne(id){
        // URLs candidatas para obtener la lista de eventos
        var listCandidates2 = [ 
            BASE + 'index.php?url=Bot_Events/list', 
            BASE + 'index.php?url=C_Bot_Events/list', 
            BASE + 'index.php/C_Bot_Events/list', 
            BASE + 'C_Bot_Events/list' 
        ];
        
        // Obtener lista de eventos y buscar el evento específico por ID
        smartFetch(listCandidates2, { credentials: 'same-origin' })
            .then(function(res){ 
                return res.resp.json().catch(function(){ return null; }); 
            })
            .then(j=>{
                // Validar respuesta del servidor
                if (!j || !j.success) {
                    if (j && j.msg === 'session_expired') {
                        alert('Sesión expirada. Inicia sesión de nuevo y vuelve a abrir la configuración de eventos.');
                    }
                    return;
                }
                
                // Buscar el evento específico en la lista
                const s = j.data.find(x=>x.id==id);
                if (!s) return; // Evento no encontrado
                
                // Obtener referencia al formulario
                const form = qs('#event-form');
                
                // Rellenar campos básicos del evento
                setVal(form, 'id', s.id);
                setVal(form, 'name', s.name);
                
                // El campo name debe ser visible pero no editable (es identificador único)
                try { 
                    var nf = getField(form, 'name'); 
                    if (nf) { nf.readOnly = true; } 
                } catch(e) {}
                
                setVal(form, 'enabled', s.enabled);
                
                // ============================================================
                // INTERVALO: Convertir interval_seconds a unidad apropiada
                // Busca la unidad más grande que represente un valor entero
                // Orden de preferencia: año > mes > semana > día > hora > minuto
                // ============================================================
                try {
                    var intervalSec = parseInt(s.interval_seconds || 0) || 0;
                    var mins = Math.round(intervalSec / 60); // Convertir segundos a minutos
                    
                    var elIntVal = getField(form, 'interval_value');
                    var elIntUnit = getField(form, 'interval_unit');
                    
                    if (elIntVal && elIntUnit) {
                        // Intentar cada unidad de mayor a menor
                        // 525600 min = 1 año (365 días)
                        if (mins % 525600 === 0) { 
                            elIntVal.value = mins / 525600; 
                            elIntUnit.value = 'year'; 
                        }
                        // 43200 min = 1 mes (30 días)
                        else if (mins % 43200 === 0) { 
                            elIntVal.value = mins / 43200; 
                            elIntUnit.value = 'month'; 
                        }
                        // 10080 min = 1 semana (7 días)
                        else if (mins % 10080 === 0) { 
                            elIntVal.value = mins / 10080; 
                            elIntUnit.value = 'week'; 
                        }
                        // 1440 min = 1 día (24 horas)
                        else if (mins % 1440 === 0) { 
                            elIntVal.value = mins / 1440; 
                            elIntUnit.value = 'day'; 
                        }
                        // 60 min = 1 hora
                        else if (mins % 60 === 0) { 
                            elIntVal.value = mins / 60; 
                            elIntUnit.value = 'hour'; 
                        }
                        // Unidad mínima: minutos
                        else { 
                            elIntVal.value = mins; 
                            elIntUnit.value = 'minute'; 
                        }
                    } else {
                        // Fallback: campos antiguos (interval_minutes o interval_seconds)
                        var elIntervalMin = getField(form, 'interval_minutes');
                        var elIntervalSec = getField(form, 'interval_seconds');
                        if (elIntervalMin) elIntervalMin.value = mins;
                        else if (elIntervalSec) elIntervalSec.value = s.interval_seconds;
                    }
                } catch(e) {
                    console.warn('Error al rellenar campos de intervalo', e);
                }
                
                // ============================================================
                // RETENCIÓN: Convertir retention_minutes a unidad apropiada
                // Misma lógica que el intervalo para mejor UX
                // ============================================================
                var rm = parseInt(s.retention_minutes || 0);
                var elRetVal = getField(form, 'retention_value');
                var elRetUnit = getField(form, 'retention_unit');
                
                if (!isNaN(rm) && rm > 0 && elRetVal && elRetUnit) {
                    // Preferir la unidad más grande que genere un valor entero
                    if (rm % 525600 === 0) { 
                        elRetVal.value = rm / 525600; 
                        elRetUnit.value = 'year'; 
                    }
                    else if (rm % 43200 === 0) { 
                        elRetVal.value = rm / 43200; 
                        elRetUnit.value = 'month'; 
                    }
                    else if (rm % 10080 === 0) { 
                        elRetVal.value = rm / 10080; 
                        elRetUnit.value = 'week'; 
                    }
                    else if (rm % 1440 === 0) { 
                        elRetVal.value = rm / 1440; 
                        elRetUnit.value = 'day'; 
                    }
                    else if (rm % 60 === 0) { 
                        elRetVal.value = rm / 60; 
                        elRetUnit.value = 'hour'; 
                    }
                    else { 
                        elRetVal.value = rm; 
                        elRetUnit.value = 'minute'; 
                    }
                } else {
                    // Fallback: campo antiguo retention_minutes
                    var elRetMin = getField(form, 'retention_minutes');
                    if (elRetMin) elRetMin.value = s.retention_minutes;
                }
                
                // Rellenar tamaño de lote (batch_size)
                setVal(form, 'batch_size', s.batch_size);
                
                // Actualizar caché global con el evento actual
                try { 
                    window.bot_events_current = { id: s.id, name: s.name }; 
                } catch(e){}
                
                // Mostrar respuesta del servidor en área de debug (útil para diagnósticos)
                try {
                    var dbg = qs('#events-debug');
                    if (dbg) { 
                        dbg.style.display = 'block'; 
                        dbg.textContent = JSON.stringify(j, null, 2); 
                    }
                } catch(e) {}
            })
            .catch(function(err){ 
                console.warn('loadOne smartFetch falló', err); 
            });
    }

    // ========================================================================
    // CARGA DE LOGS DE EVENTOS
    // ========================================================================

    /**
     * Carga y muestra los logs de ejecución de un evento específico
     * Obtiene el historial de ejecuciones, estados y mensajes
     * @param {number|string} id - ID del evento cuyos logs se desean ver
     */
    function loadLogs(id){
        // URLs candidatas para obtener los logs del evento
        var logsCandidates = [ 
            BASE + 'index.php?url=Bot_Events/logs&id=' + encodeURIComponent(id), 
            BASE + 'index.php?url=C_Bot_Events/logs&id=' + encodeURIComponent(id), 
            BASE + 'C_Bot_Events/logs?id=' + encodeURIComponent(id) 
        ];
        
        // Realizar petición al servidor
        smartFetch(logsCandidates, { credentials: 'same-origin' })
            .then(function(res){ 
                return res.resp.json().catch(function(){ return null; }); 
            })
            .then(j=>{
                // Validar respuesta del servidor
                if (!j || !j.success) {
                    if (j && j.msg === 'session_expired') {
                        alert('Sesión expirada. Inicia sesión de nuevo y vuelve a abrir la configuración de eventos.');
                    }
                    return;
                }
                
                // Renderizar los logs en el contenedor
                const el = qs('#event-logs');
                el.innerHTML = '<h5>Logs</h5>' + j.data.map(l=>
                    `<div class='p-2 border mb-1'>
                        <strong>${l.status}</strong> ${l.rows_affected} filas - ${l.created_at}
                        <div>${l.message||''}</div>
                    </div>`
                ).join('');
            });
    }

    // ========================================================================
    // LIMPIEZA AUTOMÁTICA DE MODALES
    // ========================================================================

    /**
     * Limpia los backdrops residuales de Bootstrap después de cerrar el modal
     * Previene acumulación de capas de fondo que pueden bloquear la interfaz
     * Bootstrap a veces deja elementos .modal-backdrop en el DOM
     */
    var modalEl = document.getElementById('modal-events-config');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function() {
            setTimeout(function() {
                // Buscar y eliminar todos los backdrops huérfanos
                var backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(function(bd) {
                    try {
                        if (bd && bd.parentNode) {
                            bd.parentNode.removeChild(bd);
                        }
                    } catch(e) {
                        console.warn('Error eliminando backdrop', e);
                    }
                });
                
                // Restaurar estado del body (Bootstrap agrega clases/estilos)
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }, 150); // Delay para que Bootstrap termine su animación
        });
    }

    // ========================================================================
    // MANEJO DE EVENTOS DEL FORMULARIO
    // ========================================================================

    /**
     * Intercepta el submit nativo del formulario para evitar recarga de página
     * Se activa al presionar Enter en inputs o hacer clic en botones de submit
     * Fase de captura (bubbling=true) para interceptar temprano
     */
    try {
        var formEl = document.querySelector('#event-form');
        if (formEl) {
            formEl.addEventListener('submit', function(e){
                console.log('Form submit interceptado - previniendo recarga');
                try { 
                    e.preventDefault(); 
                    e.stopPropagation();
                    if (e.stopImmediatePropagation) e.stopImmediatePropagation();
                } catch(ex) { 
                    console.warn('Error al prevenir submit', ex);
                }
                
                // Simular click en el botón guardar para activar handlers existentes
                var saveBtn = document.getElementById('save-event');
                if (saveBtn) {
                    try {
                        // Dispatch click sintético que burbujea y es cancelable
                        saveBtn.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true }));
                    } catch(err) {
                        try { 
                            saveBtn.click(); 
                        } catch(e2) { 
                            console.warn('No se pudo activar botón guardar', e2); 
                        }
                    }
                }
                return false;
            }, true); // Fase de captura para interceptar antes que otros handlers
        }
    } catch(e) { 
        console.warn('No se pudo adjuntar interceptor de submit', e); 
    }
    
    /**
     * Delegado de submit a nivel de documento (fase de captura)
     * Captura formularios inyectados dinámicamente después de la inicialización
     * Útil cuando el modal se carga después de que se ejecute este script
     */
    try {
        document.addEventListener('submit', function(e){
            try {
                var form = e.target || e.srcElement;
                if (!form) return;
                if (form.id !== 'event-form') return; // Solo interceptar nuestro formulario
                
                console.log('Submit capturado a nivel documento - previniendo recarga');
                
                // Prevenir submit nativo y detener propagación
                e.preventDefault();
                e.stopPropagation();
                if (e.stopImmediatePropagation) e.stopImmediatePropagation();
                
                console.debug('Submit nativo interceptado en #event-form, invocando flujo de guardado');
                
                // Activar flujo de guardado mediante click en botón (handlers delegados esperan click)
                var saveBtn = document.getElementById('save-event');
                if (saveBtn) {
                    try { 
                        saveBtn.dispatchEvent(new MouseEvent('click', { bubbles:true, cancelable:true })); 
                    } catch(err) { 
                        try { 
                            saveBtn.click(); 
                        } catch(e2) { 
                            console.warn('No se pudo activar botón desde interceptor de submit', e2); 
                        } 
                    }
                }
                return false;
            } catch(err) { 
                console.warn('Error en handler de submit del documento', err); 
            }
        }, true); // Fase de captura
    } catch(e) { 
        console.warn('No se pudo instalar interceptor de submit a nivel documento', e); 
    }

    // ========================================================================
    // BOTÓN "EJECUTAR EVENTO"
    // ========================================================================

    /**
     * Maneja el click del botón "Ejecutar evento"
     * Ejecuta manualmente un evento específico sin esperar al scheduler
     * Útil para testing o ejecuciones bajo demanda
     */
    var runBtn = qs('#run-event');
    if (runBtn) runBtn.addEventListener('click', function(e){
        e.preventDefault();
        
        // Obtener el ID del evento del formulario
        // Nota: No usar qs('#event-form').id.value porque .id es una propiedad
        // que devuelve el ID del elemento HTML, no el campo del formulario
        const id = getVal(qs('#event-form'), 'id');
        if (!id) return alert('Selecciona un evento');
        
        // URLs candidatas para ejecutar el evento
        var runCandidates = [ 
            BASE + 'index.php?url=Bot_Events/run', 
            BASE + 'index.php?url=C_Bot_Events/run', 
            BASE + 'index.php/C_Bot_Events/run', 
            BASE + 'C_Bot_Events/run' 
        ];
        
        // Enviar petición de ejecución
        smartFetch(runCandidates, { 
            method:'POST', 
            credentials: 'same-origin', 
            body: JSON.stringify({id:id}), 
            headers:{'Content-Type':'application/json'} 
        })
        .then(function(res){ 
            return res.resp.json().catch(function(){ return null; }); 
        })
        .then(function(j){ 
            if (j && j.status === 'OK') { 
                alert('Ejecutado, filas afectadas: '+ (j.rows||0)); 
                loadLogs(id); // Recargar logs para ver resultado de la ejecución
            } else {
                alert('Error: '+(j && (j.msg||JSON.stringify(j)) || 'Respuesta inválida')); 
            }
        })
        .catch(function(err){ 
            alert('Error ejecutando: revisa consola'); 
            console.warn('Ejecución de evento falló', err); 
        });
    });

    // ========================================================================
    // BOTÓN "REFRESCAR DESDE BD"
    // ========================================================================

    /**
     * Maneja el click del botón "Refrescar desde BD"
     * Recarga el formulario con los valores exactos desde la base de datos
     * Útil para descartar cambios locales y ver el estado actual guardado
     */
    document.addEventListener('click', function(e){
        if (!e.target) return;
        if (e.target.id === 'btn-refresh-db') {
            e.preventDefault();
            
            // URLs candidatas para obtener la lista de settings
            var listCandidates4 = [ 
                BASE + 'index.php?url=Bot_Events/list', 
                BASE + 'index.php?url=C_Bot_Events/list', 
                BASE + 'index.php/C_Bot_Events/list', 
                BASE + 'C_Bot_Events/list' 
            ];
            
            // Obtener lista de eventos y cargar el primero
            smartFetch(listCandidates4, { credentials: 'same-origin' })
                .then(function(res){ 
                    return res.resp.json().catch(function(){ return null; }); 
                })
                .then(j=>{
                    if (!j || !j.success) { 
                        alert('No se pudieron obtener settings desde el servidor'); 
                        return; 
                    }
                    
                    if (j.data && j.data.length>0) {
                        loadOne(j.data[0].id); // Cargar primer evento de la lista
                        
                        // Limpiar hint de población del servidor para permitir auto-selección en futuros loads
                        try { 
                            window.bot_events_server_populated = false; 
                        } catch(e){}
                    } else {
                        alert('No hay settings configurados en la base de datos');
                    }
                })
                .catch(err=>{ 
                    console.warn('Refresh desde BD falló', err); 
                    alert('Error al refrescar: '+err.message); 
                });
        }
    });

    // ========================================================================
    // INICIALIZACIÓN DEL MÓDULO
    // ========================================================================

    /**
     * Carga inicial: Renderiza la lista de eventos disponibles
     */
    loadList();

    /**
     * Fallback de inicialización:
     * Si después de 500ms el formulario aún no tiene nombre (campo vacío),
     * fuerza la carga del setting autoritativo id=2
     * 
     * Esto es útil cuando:
     * - El modal se inyectó desde un fallback inline
     * - No se usó el fragmento renderizado del servidor
     * - La carga asíncrona falló parcialmente
     */
    try {
        setTimeout(function(){
            try {
                var f = document.querySelector('#event-form');
                if (!f) return; // Formulario no existe
                
                var nameEl = f.querySelector('[name="name"]');
                if (!nameEl || String(nameEl.value||'').trim() !== '') return; // Ya tiene valor
                
                // Forzar carga del evento por defecto (ID=2)
                try { 
                    loadOne(2); 
                } catch(e) { 
                    console.warn('Carga forzada de loadOne(2) falló', e); 
                }
            } catch(e) { 
                console.warn('Verificación post-carga falló', e); 
            }
        }, 500);
    } catch(e) {}
}

// ========================================================================
// AUTO-INICIALIZACIÓN DEL MÓDULO
// ========================================================================

/**
 * Auto-inicialización cuando el script se carga en el nivel superior del documento
 * Compatible con DOMContentLoaded
 */
try {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function(){ initBotEvents(document); });
    else initBotEvents(document);
} catch(e){ console.warn('initBotEvents failed', e); }

// ==========================================
// HANDLER GLOBAL DE GUARDAR (FALLBACK SIMPLE)
// ==========================================
/**
 * Handler global simplificado que NO depende de funciones internas
 * Se registra cuando el documento está completamente listo
 */
(function() {
    var handlerRegistered = false;
    
    function registerSaveHandler() {
        if (handlerRegistered) {
            console.log('⚠️ Handler ya registrado, evitando duplicado');
            return;
        }
        
        console.log('🟢 Registrando handler global de Guardar...');
        
        document.addEventListener('click', function(e) {
            console.log('👆 Click detectado en documento, target:', e.target);
            
            var target = e.target;
            if (!target) {
                console.log('❌ No hay target');
                return;
            }
            
            console.log('🔍 Target ID:', target.id, 'Tag:', target.tagName);
            
            // Verificar si es el botón de guardar o está dentro de él
            var saveBtn = null;
            if (target.id === 'save-event') {
                saveBtn = target;
                console.log('✅ Click directo en botón save-event');
            } else if (target.closest) {
                saveBtn = target.closest('#save-event');
                if (saveBtn) console.log('✅ Click en elemento hijo de save-event');
            }
            
            if (!saveBtn) {
                // No es el botón guardar, ignorar silenciosamente
                return;
            }
            
            console.log('🟢 🟢 🟢 HANDLER GLOBAL: Click en botón Guardar detectado!');
            e.preventDefault();
            e.stopPropagation();
            
            // ============================================================
            // OBTENER REFERENCIA AL FORMULARIO
            // ============================================================
            var form = document.getElementById('event-form') || document.querySelector('#event-form');
            if (!form) {
                console.error('❌ Formulario #event-form no encontrado en DOM');
                alert('❌ Formulario no encontrado');
                return;
            }
            
            console.log('✅ Formulario encontrado, preparando datos...');
            
            // ============================================================
            // HELPER: Extraer valor de campo por nombre
            // ============================================================
            var getFieldValue = function(name) {
                var field = form.querySelector('[name="' + name + '"]');
                var value = field ? field.value : null;
                console.log('  Campo "' + name + '":', value);
                return value;
            };
            
            // ============================================================
            // CALCULAR INTERVAL_SECONDS
            // Soporta nueva UI (interval_value + interval_unit)
            // Con fallback a campos antiguos (interval_minutes/interval_seconds)
            // ============================================================
            var interval_seconds = 0;
            try {
                var ivRaw = getFieldValue('interval_value');
                var iuRaw = getFieldValue('interval_unit') || '';
                var iv = parseInt(ivRaw || '');
                var iu = String(iuRaw).toLowerCase();
                
                if (!isNaN(iv) && iv > 0 && iu) {
                    // Usar nueva UI con conversión de unidades
                    var multSec = 60; // default: minuto
                    switch (iu) {
                        case 'minute': multSec = 60; break;        // 1 min = 60 seg
                        case 'hour':   multSec = 3600; break;      // 1 hr = 3600 seg
                        case 'day':    multSec = 86400; break;     // 1 día = 86400 seg
                        case 'week':   multSec = 604800; break;    // 1 sem = 604800 seg
                        case 'month':  multSec = 2592000; break;   // 1 mes (30d) = 2592000 seg
                        case 'year':   multSec = 31536000; break;  // 1 año (365d) = 31536000 seg
                    }
                    interval_seconds = iv * multSec;
                } else {
                    // Fallback: Intentar con campos antiguos
                    var intervalMin = parseInt(getFieldValue('interval_minutes') || '0');
                    if (!isNaN(intervalMin) && intervalMin > 0) {
                        interval_seconds = intervalMin * 60;
                    } else {
                        var isec = parseInt(getFieldValue('interval_seconds') || '0');
                        interval_seconds = isNaN(isec) ? 0 : isec;
                    }
                }
            } catch(e) {
                console.warn('Cálculo de interval_seconds falló', e);
                // Fallback final: intentar con interval_minutes
                var intervalMin = parseInt(getFieldValue('interval_minutes') || '0');
                interval_seconds = (isNaN(intervalMin) ? 0 : intervalMin * 60);
            }
            
            // ============================================================
            // CALCULAR RETENTION_MINUTES
            // Similar a interval, convierte retention_value + unit a minutos
            // ============================================================
            var retValue = parseInt(getFieldValue('retention_value') || '0');
            var retUnit = (getFieldValue('retention_unit') || 'minute').toLowerCase();
            var mult = 1; // default: minutos
            switch(retUnit) {
                case 'minute': mult = 1; break;        // 1 min = 1 min
                case 'hour':   mult = 60; break;       // 1 hr = 60 min
                case 'day':    mult = 1440; break;     // 1 día = 1440 min
                case 'week':   mult = 10080; break;    // 1 sem = 10080 min
                case 'month':  mult = 43200; break;    // 1 mes (30d) = 43200 min
                case 'year':   mult = 525600; break;   // 1 año (365d) = 525600 min
            }
            var retention_minutes = retValue * mult;
            
            // ============================================================
            // CONSTRUIR PAYLOAD DE DATOS
            // ============================================================
            var data = {
                id: getFieldValue('id') || '2',
                name: getFieldValue('name') || 'clean_old_messages',
                enabled: getFieldValue('enabled') || '1',
                interval_seconds: interval_seconds,
                retention_minutes: retention_minutes,
                batch_size: parseInt(getFieldValue('batch_size') || '1000')
            };
            
            console.log('📤 Datos a enviar:', JSON.stringify(data, null, 2));
            
            // ============================================================
            // DETECTAR BASE_URL DEL PROYECTO
            // Intenta usar BASE_URL global, o lo construye desde la URL actual
            // ============================================================
            var base = '/';
            if (typeof BASE_URL !== 'undefined' && BASE_URL) {
                base = BASE_URL;
                if (base.charAt(base.length - 1) !== '/') base += '/';
            } else {
                // Construir desde window.location
                var origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
                var path = window.location.pathname || '/';
                var parts = path.split('/').filter(Boolean);
                if (parts.length > 0) {
                    base = origin + '/' + parts[0] + '/';
                } else {
                    base = origin + '/';
                }
            }
            
            // ============================================================
            // CONSTRUIR URL Y ENVIAR PETICIÓN
            // ============================================================
            var url = base + 'index.php?url=Bot_Events/save';
            console.log('🌐 URL del endpoint:', url);
            
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            })
            .then(function(r) {
                console.log('📥 Respuesta recibida, status HTTP:', r.status);
                return r.text();
            })
            .then(function(text) {
                console.log('📄 Respuesta (primeros 200 chars):', text.substring(0, 200));
                try {
                    var json = JSON.parse(text);
                    console.log('✅ JSON parseado:', json);
                    
                    if (json.status === 'OK' || json.success) {
                        console.log('✅ Guardado exitoso!');
                        alert('✅ Tabla actualizada correctamente');
                        
                        // Cerrar modal automáticamente después de guardar
                        setTimeout(function() {
                            var modal = document.getElementById('modal-events-config');
                            if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                                var inst = bootstrap.Modal.getInstance(modal);
                                if (inst) inst.hide();
                            }
                        }, 300);
                    } else {
                        console.error('❌ Error en respuesta:', json);
                        alert('❌ Error: ' + (json.msg || json.error || 'Error desconocido'));
                    }
                } catch(err) {
                    console.error('❌ Error parseando JSON:', err);
                    console.log('Texto recibido completo:', text);
                    alert('❌ Error: Respuesta inválida del servidor');
                }
            })
            .catch(function(err) {
                console.error('❌ Error en fetch:', err);
                alert('❌ Error de conexión: ' + err.message);
            });
        }, true); // FASE DE CAPTURA para interceptar temprano en el flujo de eventos
        
        handlerRegistered = true;
        console.log('✅ ✅ ✅ Handler global de Guardar registrado EXITOSAMENTE');
    }
    
    // ============================================================
    // REGISTRO AUTOMÁTICO DEL HANDLER
    // Espera a que el documento esté listo antes de registrar
    // ============================================================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', registerSaveHandler);
    } else {
        registerSaveHandler();
    }
})();
