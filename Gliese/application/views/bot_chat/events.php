<?php
// Vista profesional para gestión de eventos automatizados
?>
<style>
  /* Ocultar header y footer del modal padre */
  #modal-events-config .modal-header,
  #modal-events-config .modal-footer {
    display: none !important;
  }
  
  #modal-events-config .modal-body {
    padding: 0 !important;
  }
  
  /* ========================================
     WRAPPER: Sin altura mínima excesiva
     ======================================== */
  .events-wrapper {
    background: #f8f9fa;
  }
  
  /* ========================================
     HEADER: Gris oscuro como módulos del sidebar
     Texto claro para buen contraste
     ======================================== */
  .events-header {
    background: #2c3e50;
    padding: 0.75rem 1rem;
    color: #ecf0f1;
    position: relative;
  }
  
  .events-header h4 {
    margin: 0 0 0.15rem 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: #ffffff;
  }
  
  .events-header p {
    margin: 0;
    opacity: 0.85;
    font-size: 0.85rem;
    color: #bdc3c7;
  }
  
  /* ========================================
     BOTÓN DE CERRAR: Adaptado al fondo oscuro
     ======================================== */
  .close-modal-btn {
    position: absolute;
    top: 0.6rem;
    right: 0.75rem;
    background: rgba(255,255,255,0.15);
    border: none;
    color: #ecf0f1;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
  }
  
  .close-modal-btn:hover {
    background: rgba(255,255,255,0.25);
    color: white;
    transform: scale(1.1);
  }
  
  /* ========================================
     CUERPO: Muy reducido
     ======================================== */
  .events-content {
    padding: 0.75rem 1rem;
  }
  
  /* ========================================
     STATUS BADGE: Adaptado al fondo oscuro
     ======================================== */
  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    background: rgba(46, 204, 113, 0.2);
    padding: 0.25rem 0.6rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-top: 0.3rem;
    color: #2ecc71;
    border: 1px solid rgba(46, 204, 113, 0.3);
  }
  
  .status-badge::before {
    content: '';
    width: 6px;
    height: 6px;
    background: #2ecc71;
    border-radius: 50%;
    box-shadow: 0 0 4px rgba(46, 204, 113, 0.6);
  }
  
  /* ========================================
     FORMULARIO: Padding aumentado
     ======================================== */
  .form-card {
    background: white;
    border-radius: 6px;
    padding: 0.9rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #e3e8ef;
  }
  
  /* ========================================
     GRID: UNA SOLA FILA HORIZONTAL
     Todos los campos en línea para ocupar menos altura
     ======================================== */
  .form-section {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
    gap: 0.85rem;
    margin-bottom: 0.5rem;
  }
  
  .form-field {
    display: flex;
    flex-direction: column;
  }
  
  /* ========================================
     LABELS: Más grandes y legibles
     ======================================== */
  .form-field label {
    font-weight: 600;
    font-size: 0.75rem;
    color: #32325d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 0.35rem;
  }
  
  /* ========================================
     ÍCONOS: Color gris para combinar
     ======================================== */
  .form-field label i {
    margin-right: 0.25rem;
    color: #5a6c7d;
    font-size: 0.85rem;
  }
  
  /* ========================================
     INPUTS: Más grandes y legibles
     ======================================== */
  .form-field input,
  .form-field select {
    border: 1px solid #dee2e6;
    border-radius: 3px;
    padding: 0.5rem 0.65rem;
    font-size: 0.9rem;
    transition: all 0.2s;
    background: white;
  }
  
  /* ========================================
     FOCUS: Color gris al enfocar campos
     border-color: Color del borde al enfocar
     ======================================== */
  .form-field input:focus,
  .form-field select:focus {
    border-color: #5a6c7d;
    box-shadow: 0 0 0 3px rgba(90,108,125,0.1);
    outline: none;
  }
  
  .form-field input:disabled,
  .form-field input[readonly] {
    background: #f7fafc;
    cursor: not-allowed;
    color: #8898aa;
  }
  
  /* ========================================
     TEXTO DE AYUDA: Más grande
     ======================================== */
  .form-field small {
    margin-top: 0.25rem;
    color: #8898aa;
    font-size: 0.72rem;
  }
  
  /* ========================================
     DUAL INPUT: Muy compacto
     ======================================== */
  .dual-input {
    display: flex;
    gap: 0.4rem;
  }
  
  .dual-input input {
    flex: 0 0 75px;
  }
  
  .dual-input select {
    flex: 1;
  }
  
  /* ========================================
     BARRA DE ACCIONES: Muy reducida
     ======================================== */
  .action-bar {
    display: flex;
    gap: 0.5rem;
    padding-top: 0.75rem;
    margin-top: 0.75rem;
    border-top: 1px solid #e3e8ef;
  }
  
  /* ========================================
     BOTONES: Más grandes y legibles
     ======================================== */
  .btn-modern {
    padding: 0.5rem 0.9rem;
    border-radius: 3px;
    font-weight: 600;
    font-size: 0.8rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
  }
  
  .btn-modern:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 11px rgba(0,0,0,0.15);
  }
  
  .btn-modern:active {
    transform: translateY(0);
  }
  
  /* ========================================
     BOTÓN GUARDAR: Color gris oscuro
     ======================================== */
  .btn-primary-custom {
    background: linear-gradient(135deg, #5a6c7d 0%, #3e4b5b 100%);
    color: white;
  }
  
  /* ========================================
     BOTÓN EJECUTAR: Color verde success
     ======================================== */
  .btn-secondary-custom {
    background: #1cc88a;
    color: white;
  }
  
  .btn-outline-custom {
    background: white;
    color: #525f7f;
    border: 1px solid #dee2e6;
  }
  
  /* ========================================
     BOTÓN REFRESCAR: Hover con gris
     ======================================== */
  .btn-outline-custom:hover {
    background: #f7fafc;
    border-color: #5a6c7d;
    color: #5a6c7d;
  }
  
  /* ========================================
     CAJA DE INFORMACIÓN: Más grande
     ======================================== */
  .info-text {
    background: #d1ecf1;
    border-left: 3px solid #0c5460;
    padding: 0.6rem 0.75rem;
    border-radius: 3px;
    margin-bottom: 0.75rem;
    font-size: 0.8rem;
    color: #0c5460;
  }
</style>

<div class="events-wrapper">
  <div class="events-header">
    <button class="close-modal-btn" type="button" data-bs-dismiss="modal" aria-label="Cerrar">
      <i class="fas fa-times"></i>
    </button>
    <h4><i class="fas fa-calendar-check me-2"></i>Configuración de Eventos Automatizados</h4>
    <p>Gestiona la ejecución automática y retención de mensajes del sistema</p>
    <span class="status-badge">Sistema Activo</span>
  </div>

  <div class="events-content">

    <?php
    $srv_first = null;
    if (isset($bot_events_data) && is_array($bot_events_data) && count($bot_events_data) > 0) {
        $srv_first = $bot_events_data[0];
    }
    $srv_id = $srv_first['id'] ?? '';
    $srv_name = $srv_first['name'] ?? '';
    $srv_enabled = isset($srv_first['enabled']) ? $srv_first['enabled'] : '1';
    $srv_interval_seconds = isset($srv_first['interval_seconds']) ? intval($srv_first['interval_seconds']) : 300;
    $srv_retention_minutes = isset($srv_first['retention_minutes']) ? intval($srv_first['retention_minutes']) : 43200;
    $srv_batch = isset($srv_first['batch_size']) ? intval($srv_first['batch_size']) : 1000;
    
    // Si interval_seconds es 0 o inválido, usar un default razonable (5 minutos = 300 segundos)
    if ($srv_interval_seconds <= 0) {
        $srv_interval_seconds = 300;
    }
    
    $srv_interval_minutes = (int) round($srv_interval_seconds / 60);
  $srv_interval_val = $srv_interval_minutes;
  $srv_interval_unit = 'minute';
  if ($srv_interval_minutes % 525600 === 0 && $srv_interval_minutes > 0) { $srv_interval_val = $srv_interval_minutes / 525600; $srv_interval_unit = 'year'; }
  else if ($srv_interval_minutes % 43200 === 0 && $srv_interval_minutes > 0) { $srv_interval_val = $srv_interval_minutes / 43200; $srv_interval_unit = 'month'; }
  else if ($srv_interval_minutes % 10080 === 0 && $srv_interval_minutes > 0) { $srv_interval_val = $srv_interval_minutes / 10080; $srv_interval_unit = 'week'; }
  else if ($srv_interval_minutes % 1440 === 0 && $srv_interval_minutes > 0) { $srv_interval_val = $srv_interval_minutes / 1440; $srv_interval_unit = 'day'; }
  else if ($srv_interval_minutes % 60 === 0 && $srv_interval_minutes > 0) { $srv_interval_val = $srv_interval_minutes / 60; $srv_interval_unit = 'hour'; }
    $srv_ret_val = $srv_retention_minutes;
    $srv_ret_unit = 'minute';
    if ($srv_retention_minutes % 525600 === 0) { $srv_ret_val = $srv_retention_minutes / 525600; $srv_ret_unit = 'year'; }
    else if ($srv_retention_minutes % 43200 === 0) { $srv_ret_val = $srv_retention_minutes / 43200; $srv_ret_unit = 'month'; }
    else if ($srv_retention_minutes % 10080 === 0) { $srv_ret_val = $srv_retention_minutes / 10080; $srv_ret_unit = 'week'; }
    else if ($srv_retention_minutes % 1440 === 0) { $srv_ret_val = $srv_retention_minutes / 1440; $srv_ret_unit = 'day'; }
    else if ($srv_retention_minutes % 60 === 0) { $srv_ret_val = $srv_retention_minutes / 60; $srv_ret_unit = 'hour'; }
    ?>

    <form id="event-form">
      <input type="hidden" name="id" value="<?php echo htmlentities($srv_id); ?>" />

      <!-- TODOS LOS CAMPOS EN UNA SOLA FILA -->
      <div class="info-text">
        <i class="fas fa-info-circle me-1"></i>
        <strong>Evento:</strong> Limpieza automática de mensajes antiguos
      </div>

      <div class="form-card">
        <div class="form-section">
          <!-- Campo 1: Nombre -->
          <div class="form-field">
            <label><i class="fas fa-tag"></i> Nombre</label>
            <input name="name" type="text" value="<?php echo htmlentities($srv_name); ?>" readonly>
            <small>ID: <?php echo htmlentities($srv_id); ?></small>
          </div>

          <!-- Campo 2: Estado -->
          <div class="form-field">
            <label><i class="fas fa-power-off"></i> Estado</label>
            <select name="enabled">
              <option value="1" <?php if (strval($srv_enabled) === '1') echo 'selected'; ?>>✓ Activado</option>
              <option value="0" <?php if (strval($srv_enabled) === '0') echo 'selected'; ?>>○ Desactivado</option>
            </select>
          </div>

          <!-- Campo 3: Intervalo (valor + unidad) -->
          <div class="form-field">
            <label><i class="fas fa-clock"></i> Intervalo</label>
            <div class="dual-input">
              <input name="interval_value" type="number" value="<?php echo htmlentities($srv_interval_val); ?>" min="1">
              <select name="interval_unit">
                <option value="minute" <?php if ($srv_interval_unit === 'minute') echo 'selected'; ?>>Min</option>
                <option value="hour" <?php if ($srv_interval_unit === 'hour') echo 'selected'; ?>>Hrs</option>
                <option value="day" <?php if ($srv_interval_unit === 'day') echo 'selected'; ?>>Días</option>
                <option value="week" <?php if ($srv_interval_unit === 'week') echo 'selected'; ?>>Sem</option>
                <option value="month" <?php if ($srv_interval_unit === 'month') echo 'selected'; ?>>Mes</option>
                <option value="year" <?php if ($srv_interval_unit === 'year') echo 'selected'; ?>>Año</option>
              </select>
            </div>
            <small>Tiempo</small>
          </div>

          <!-- Campo 4: Retención -->
          <div class="form-field">
            <label><i class="fas fa-hourglass-half"></i> Retención</label>
            <div class="dual-input">
              <input name="retention_value" type="number" value="<?php echo htmlentities($srv_ret_val); ?>" min="1">
              <select name="retention_unit">
                <option value="minute" <?php if ($srv_ret_unit === 'minute') echo 'selected'; ?>>Min</option>
                <option value="hour" <?php if ($srv_ret_unit === 'hour') echo 'selected'; ?>>Hrs</option>
                <option value="day" <?php if ($srv_ret_unit === 'day') echo 'selected'; ?>>Días</option>
                <option value="week" <?php if ($srv_ret_unit === 'week') echo 'selected'; ?>>Sem</option>
                <option value="month" <?php if ($srv_ret_unit === 'month') echo 'selected'; ?>>Mes</option>
                <option value="year" <?php if ($srv_ret_unit === 'year') echo 'selected'; ?>>Año</option>
              </select>
            </div>
            <small>Tiempo</small>
          </div>

          <!-- Campo 5: Registros -->
          <div class="form-field">
            <label><i class="fas fa-database"></i> Registros</label>
            <input name="batch_size" type="number" value="<?php echo htmlentities($srv_batch); ?>" min="100" step="100">
            <small>Por lote</small>
          </div>
        </div>

        <!-- Botones -->
        <div class="action-bar">
          <button id="save-event" class="btn-modern btn-primary-custom" type="button">
            <i class="fas fa-save"></i> Guardar
          </button>
          <button id="run-event" class="btn-modern btn-secondary-custom" type="button">
            <i class="fas fa-play"></i> Ejecutar
          </button>
        </div>
      </div>
    </form>

    <script type="text/javascript">
      (function() {
        console.log('🟢 INICIANDO HANDLER FINAL v7...');
        
        setTimeout(function() {
          var btn = document.getElementById('save-event');
          if (!btn) {
            console.error('❌ Botón save-event no encontrado');
            return;
          }
          
          console.log('✅ Botón encontrado!');
          
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('🔴🔴🔴 CLICK DETECTADO - EJECUTANDO GUARDADO!');
            
            var form = document.getElementById('event-form');
            if (!form) { 
              alert('❌ Formulario no encontrado'); 
              return; 
            }
            
            var getVal = function(name) {
              var f = form.querySelector('[name="' + name + '"]');
              return f ? f.value : null;
            };
            
            var retValue = parseInt(getVal('retention_value') || '0');
            var retUnit = (getVal('retention_unit') || 'minute').toLowerCase();

            var mult = {minute:1, hour:60, day:1440, week:10080, month:43200, year:525600}[retUnit] || 1;

            // interval_seconds: prefer interval_value + interval_unit -> seconds; fallback to interval_minutes or interval_seconds
            var iv = parseInt(getVal('interval_value') || '0');
            var iu = (getVal('interval_unit') || '').toLowerCase();
            console.log('🔍 DEBUG interval_value:', getVal('interval_value'), '-> parsed:', iv);
            console.log('🔍 DEBUG interval_unit:', getVal('interval_unit'), '-> lowercase:', iu);
            var interval_seconds = 0;
            if (!isNaN(iv) && iv > 0 && iu) {
                var secMult = {minute:60, hour:3600, day:86400, week:604800, month:2592000, year:31536000}[iu] || 60;
                console.log('🔍 DEBUG secMult para "' + iu + '":', secMult);
                interval_seconds = iv * secMult;
                console.log('🔍 DEBUG interval_seconds calculado:', interval_seconds);
            } else {
                console.log('⚠️ DEBUG fallback - iv:', iv, 'isNaN:', isNaN(iv), 'iv>0:', iv>0, 'iu:', iu);
                var intervalMin = parseInt(getVal('interval_minutes') || '0');
                if (!isNaN(intervalMin) && intervalMin > 0) interval_seconds = intervalMin * 60;
                else {
                    var isec = parseInt(getVal('interval_seconds') || '0');
                    interval_seconds = isNaN(isec) ? 0 : isec;
                }
            }

            var data = {
              id: getVal('id') || '2',
              name: getVal('name') || 'clean_old_messages',
              enabled: getVal('enabled') || '1',
              interval_seconds: interval_seconds,
              retention_minutes: retValue * mult,
              batch_size: parseInt(getVal('batch_size') || '1000')
            };
            
            console.log('📤 DATOS A ENVIAR:', JSON.stringify(data, null, 2));
            console.log('📤 interval_seconds final:', interval_seconds, typeof interval_seconds);
            
            var base = (typeof BASE_URL !== 'undefined' && BASE_URL) ? BASE_URL : '/gliese/';
            if (base.charAt(base.length - 1) !== '/') base += '/';
            
            fetch(base + 'index.php?url=Bot_Events/save', {
              method: 'POST',
              credentials: 'same-origin',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify(data)
            })
            .then(function(r) { 
              console.log('📥 Status:', r.status);
              return r.json(); 
            })
            .then(function(json) {
              console.log('📦 Respuesta:', json);
              if (json.status === 'OK' || json.success) {
                alert('✅ Tabla actualizada correctamente');
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
              console.error('❌ Error:', err);
              alert('❌ Error: ' + err.message);
            });
          });
          
          console.log('✅ Handler agregado correctamente v7');
        }, 200);
      })();
    </script>
    </form>

    <script type="text/javascript">
      window.saveEventNow = function() {
        var form = document.getElementById('event-form');
        if (!form) { alert(' Formulario no encontrado'); return; }
        
        var getVal = function(name) {
          var f = form.querySelector('[name="' + name + '"]');
          return f ? f.value : null;
        };
        
        var retValue = parseInt(getVal('retention_value') || '0');
        var retUnit = (getVal('retention_unit') || 'minute').toLowerCase();
        var mult = {minute:1, hour:60, day:1440, week:10080, month:43200, year:525600}[retUnit] || 1;

        var iv = parseInt(getVal('interval_value') || '0');
        var iu = (getVal('interval_unit') || '').toLowerCase();
        console.log('🔍 [saveEventNow] interval_value:', getVal('interval_value'), '-> parsed:', iv);
        console.log('🔍 [saveEventNow] interval_unit:', getVal('interval_unit'), '-> lowercase:', iu);
        var interval_seconds = 0;
        if (!isNaN(iv) && iv > 0 && iu) {
            var secMult = {minute:60, hour:3600, day:86400, week:604800, month:2592000, year:31536000}[iu] || 60;
            console.log('🔍 [saveEventNow] secMult:', secMult);
            interval_seconds = iv * secMult;
            console.log('🔍 [saveEventNow] interval_seconds:', interval_seconds);
        } else {
            console.log('⚠️ [saveEventNow] fallback activado');
            var intervalMin = parseInt(getVal('interval_minutes') || '0');
            if (!isNaN(intervalMin) && intervalMin > 0) interval_seconds = intervalMin * 60;
            else {
                var isec = parseInt(getVal('interval_seconds') || '0');
                interval_seconds = isNaN(isec) ? 0 : isec;
            }
        }

        var data = {
          id: getVal('id') || '2',
          name: getVal('name') || 'clean_old_messages',
          enabled: getVal('enabled') || '1',
          interval_seconds: interval_seconds,
          retention_minutes: retValue * mult,
          batch_size: parseInt(getVal('batch_size') || '1000')
        };
        
        console.log('📤 [saveEventNow] DATOS:', JSON.stringify(data, null, 2));
        console.log('📤 [saveEventNow] interval_seconds:', interval_seconds, typeof interval_seconds);
        
        var base = (typeof BASE_URL !== 'undefined' && BASE_URL) ? BASE_URL : '/gliese/';
        if (base.charAt(base.length - 1) !== '/') base += '/';
        
        fetch(base + 'index.php?url=Bot_Events/save', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(json) {
          if (json.status === 'OK' || json.success) {
            alert(' Tabla actualizada correctamente');
            setTimeout(function() {
              var modal = document.getElementById('modal-events-config');
              if (modal && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                var inst = bootstrap.Modal.getInstance(modal);
                if (inst) inst.hide();
              }
            }, 300);
          } else {
            alert(' Error: ' + (json.msg || json.error || 'Error desconocido'));
          }
        })
        .catch(function(err) {
          alert(' Error: ' + err.message);
        });
      };
    </script>

        <!-- BOT_EVENTS_MODAL_OK -->
  </div>
</div>

