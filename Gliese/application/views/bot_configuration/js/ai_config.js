// --
function init_ai_config() {
    console.log('[Bot Configuration] ai_config.js initialized');
    load_ai_api_key();
    load_prompts();

    // API Key form submit handler
    $('#form-ai-api-key').on('submit', function(e) {
        e.preventDefault();
        save_api_key();
    });

    // Password toggle for OpenAI API Key
    $('#toggle-openai-key').on('click', function() {
        const input = $('#openai_api_key');
        const icon = $('#eye-icon-openai');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.attr('data-feather', 'eye-off');
        } else {
            input.attr('type', 'password');
            icon.attr('data-feather', 'eye');
        }
        feather.replace();
    });

    // Add prompt button
    $('#btn-add-prompt').on('click', function() {
        open_prompt_modal();
    });

    // Save prompt button
    $('#btn-save-prompt').on('click', function() {
        save_prompt();
    });

    // Handle context select change
    $(document).on('change', '#prompt_context_select', function() {
        const value = $(this).val();
        if (value === '__new__') {
            $('#prompt_context_input').show().focus();
        } else {
            $('#prompt_context_input').hide().val('');
        }
    });
}

// ============ API KEY ============

// --
function load_ai_api_key() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_ai_config',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK' && response.data) {
                const data = response.data;
                // Store original value to detect changes
                $('#openai_api_key').val(data.openai_api_key || '').data('original', data.openai_api_key || '');
                feather.replace();
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al cargar API Key', 'ERROR');
            console.error('[AI Config] Load API Key error:', error);
        }
    });
}

// --
function save_api_key() {
    const formData = {};

    // Only include API key if user changed it
    const apiKeyValue = $('#openai_api_key').val() || '';
    const apiKeyOriginal = $('#openai_api_key').data('original') || '';
    if (apiKeyValue !== apiKeyOriginal && apiKeyValue.trim() !== '') {
        formData.openai_api_key = apiKeyValue;
    } else {
        functions.toast_message('info', 'No hay cambios en la API Key', 'INFO');
        return;
    }

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/update_ai_config',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);
            if (response.status === 'OK') {
                load_ai_api_key();
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al guardar API Key', 'ERROR');
            console.error('[AI Config] Save API Key error:', error);
        }
    });
}

// ============ PROMPTS ============

// Global variable to store prompts data
let allPromptsData = [];

// --
function load_prompts() {
    console.log('[AI Prompts] Loading...');

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_ai_prompts',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('[AI Prompts] Response:', response);

            if (response.status === 'OK' && response.data) {
                allPromptsData = response.data; // Store globally
                render_prompts_table(response.data);
                load_existing_contexts(response.data);
            } else {
                allPromptsData = [];
                $('#tbody-prompts').html('<tr><td colspan="5" class="text-center text-muted">No hay prompts disponibles</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('[AI Prompts] Load error:', error);
            allPromptsData = [];
            $('#tbody-prompts').html('<tr><td colspan="5" class="text-center text-danger">Error al cargar prompts</td></tr>');
        }
    });
}

// --
function load_existing_contexts(prompts) {
    console.log('[AI Prompts] Loading contexts from prompts:', prompts);

    // Extract unique contexts from prompts
    const contexts = new Set();
    prompts.forEach(function(prompt) {
        if (prompt.context && prompt.context.trim() !== '') {
            contexts.add(prompt.context.trim());
        }
    });

    console.log('[AI Prompts] Unique contexts found:', Array.from(contexts));

    // Populate select dropdown
    const select = $('#prompt_context_select');

    // Remove only context options (not "Sin contexto" and "Crear nuevo")
    // Keep first option (Sin contexto) and last option (Crear nuevo)
    select.find('option').not(':first').not(':last').remove();

    // Insert existing contexts before the "Crear nuevo" option
    const createNewOption = select.find('option[value="__new__"]');
    contexts.forEach(function(context) {
        $(`<option value="${context}">${context}</option>`).insertBefore(createNewOption);
    });

    console.log('[AI Prompts] Context dropdown options:', select.find('option').map(function() { return $(this).val(); }).get());
}

// --
function render_prompts_table(prompts) {
    // Step 1: Group prompts by context
    const grouped = {};

    prompts.forEach(function(prompt) {
        const context = (prompt.context && prompt.context.trim() !== '') ? prompt.context.trim() : '__no_context__';

        if (!grouped[context]) {
            grouped[context] = [];
        }
        grouped[context].push(prompt);
    });

    // Step 2: Sort prompts within each group by display_order (ascending)
    Object.keys(grouped).forEach(function(context) {
        grouped[context].sort(function(a, b) {
            const orderA = parseInt(a.display_order) || 0;
            const orderB = parseInt(b.display_order) || 0;
            return orderA - orderB;
        });
    });

    // Step 3: Sort context groups by the minimum display_order in each group
    const contextNames = Object.keys(grouped).sort(function(contextA, contextB) {
        // Get minimum order in each context group
        const minOrderA = Math.min(...grouped[contextA].map(p => parseInt(p.display_order) || 0));
        const minOrderB = Math.min(...grouped[contextB].map(p => parseInt(p.display_order) || 0));
        return minOrderA - minOrderB;
    });

    // Step 4: Render table
    let html = '';

    contextNames.forEach(function(contextName) {
        const promptsInContext = grouped[contextName];

        // Render prompts in this context
        promptsInContext.forEach(function(prompt) {
            // Show actual context from database, or "Sin contexto" if null/empty
            let contextDisplay = '';
            if (prompt.context && prompt.context.trim() !== '') {
                contextDisplay = `<span class="badge bg-info">${prompt.context}</span>`;
            } else {
                contextDisplay = '<span class="badge bg-secondary">Sin contexto</span>';
            }

            const statusBadge = prompt.is_active == 1
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-secondary">Inactivo</span>';

            const description = prompt.description || '-';
            const shortDesc = description.length > 50 ? description.substring(0, 50) + '...' : description;

            const usageCount = prompt.usage_count || 0;

            html += `
                <tr>
                    <td><code>${prompt.key}</code></td>
                    <td>${contextDisplay}</td>
                    <td>${shortDesc}</td>
                    <td><span class="badge bg-light text-dark">${usageCount}</span></td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="open_prompt_modal(${prompt.id})" title="Editar">
                            <i data-feather="edit-2"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="delete_prompt(${prompt.id})" title="Eliminar">
                            <i data-feather="trash-2"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    });

    $('#tbody-prompts').html(html);
    feather.replace();
}

// --
function calculate_next_display_order(context = null) {
    if (allPromptsData.length === 0) {
        return 1;
    }

    // If context is provided, find max display_order within that context
    // Otherwise, find global max
    let maxOrder = 0;
    allPromptsData.forEach(function(prompt) {
        // If filtering by context
        if (context !== null) {
            const promptContext = (prompt.context && prompt.context.trim() !== '') ? prompt.context.trim() : null;
            if (promptContext !== context) {
                return; // Skip prompts not in this context
            }
        }

        const order = parseInt(prompt.display_order) || 0;
        if (order > maxOrder) {
            maxOrder = order;
        }
    });

    return maxOrder + 1;
}

// --
function open_prompt_modal(promptId = null) {
    console.log('[AI Prompts] Opening modal for prompt:', promptId);

    // Clear form
    $('#form-edit-prompt')[0].reset();
    $('#prompt_id').val('');
    $('#prompt_context_input').val('').hide();
    $('#prompt_model').val('');
    $('#prompt_max_tokens').val('');
    $('#prompt_temperature').val('');
    $('#prompt_is_active').val('1');

    if (promptId) {
        // Edit mode
        $('#modal-prompt-title').text('Editar Prompt');

        $.ajax({
            url: BASE_URL + 'Bot_Configuration/get_ai_prompt/' + promptId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'OK' && response.data) {
                    const prompt = response.data;

                    // CRÍTICO: Reload contexts AQUÍ, dentro del AJAX success, antes de establecer valores
                    load_existing_contexts(allPromptsData);

                    $('#prompt_id').val(prompt.id);
                    $('#prompt_key').val(prompt.key).prop('readonly', true); // Key no editable

                    $('#prompt_display_order').val(prompt.display_order || 0);
                    $('#prompt_description').val(prompt.description || '');
                    $('#prompt_text').val(prompt.prompt_text || '');

                    // Set AI fields - empty string for "Sin modelo" option
                    $('#prompt_model').val(prompt.model || '');
                    $('#prompt_max_tokens').val(prompt.max_tokens || '');
                    $('#prompt_temperature').val(prompt.temperature || '');

                    $('#prompt_is_active').val(prompt.is_active == 1 ? '1' : '0');

                    // Set context AFTER contexts are loaded
                    const contextValue = prompt.context || '';
                    console.log('[AI Prompts] Setting context value:', contextValue);
                    $('#prompt_context_select').val(contextValue);

                    // Verify that context was set correctly
                    const selectedValue = $('#prompt_context_select').val();
                    console.log('[AI Prompts] Context select now shows:', selectedValue);

                    if (contextValue !== '' && selectedValue !== contextValue) {
                        console.error('[AI Prompts] ERROR: Context "' + contextValue + '" not found in dropdown!');
                        console.log('[AI Prompts] Available options:', $('#prompt_context_select option').map(function() {
                            return $(this).val() + ' (' + $(this).text() + ')';
                        }).get());
                    }

                    const modal = new bootstrap.Modal(document.getElementById('modal-edit-prompt'));
                    modal.show();
                    feather.replace();
                } else {
                    functions.toast_message('error', 'Error al cargar prompt', 'ERROR');
                }
            },
            error: function(xhr, status, error) {
                console.error('[AI Prompts] Load error:', error);
                functions.toast_message('error', 'Error al cargar prompt', 'ERROR');
            }
        });
    } else {
        // Create mode - Reload contexts to ensure fresh list
        load_existing_contexts(allPromptsData);

        // Create mode - Suggest next display_order
        $('#modal-prompt-title').text('Nuevo Prompt');
        $('#prompt_key').prop('readonly', false); // Key editable
        $('#prompt_context_select').val(''); // Set to "Sin contexto" by default

        const nextOrder = calculate_next_display_order();
        $('#prompt_display_order').val(nextOrder);
        console.log('[AI Prompts] Suggested display_order:', nextOrder);

        const modal = new bootstrap.Modal(document.getElementById('modal-edit-prompt'));
        modal.show();
        feather.replace();
    }
}

// --
function save_prompt() {
    const promptId = $('#prompt_id').val();

    // Check if creating a new context
    const selectValue = $('#prompt_context_select').val();
    let finalContext = null;

    if (selectValue === '__new__') {
        // User is creating new context
        const newContextValue = $('#prompt_context_input').val().trim();
        if (newContextValue === '') {
            functions.toast_message('warning', 'Debe escribir el nombre del nuevo contexto', 'WARNING');
            return;
        }
        finalContext = newContextValue;
    } else {
        // Use selected context (empty string becomes null)
        finalContext = (selectValue === '') ? null : selectValue;
    }

    const formData = {
        key: $('#prompt_key').val().trim(),
        context: finalContext,
        display_order: $('#prompt_display_order').val() || 0,
        description: $('#prompt_description').val(),
        prompt_text: $('#prompt_text').val(),
        is_active: $('#prompt_is_active').val()
    };

    // Include AI fields - if empty, send null; if filled, send values
    const maxTokensValue = $('#prompt_max_tokens').val();
    const temperatureValue = $('#prompt_temperature').val();
    const modelValue = $('#prompt_model').val();

    formData.model = (modelValue && modelValue.trim() !== '') ? modelValue : null;
    formData.max_tokens = (maxTokensValue && maxTokensValue.trim() !== '') ? maxTokensValue : null;
    formData.temperature = (temperatureValue && temperatureValue.trim() !== '') ? temperatureValue : null;

    // Validate
    if (!formData.key || !formData.prompt_text) {
        functions.toast_message('warning', 'Key y Texto del Prompt son obligatorios', 'WARNING');
        return;
    }

    if (promptId) {
        formData.id = promptId;
    }

    console.log('[AI Prompts] Saving:', formData);

    const url = promptId
        ? BASE_URL + 'Bot_Configuration/update_ai_prompt'
        : BASE_URL + 'Bot_Configuration/create_ai_prompt';

    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            functions.toast_message(response.type, response.msg, response.status);

            if (response.status === 'OK') {
                bootstrap.Modal.getInstance(document.getElementById('modal-edit-prompt')).hide();
                load_prompts();
            }
        },
        error: function(xhr, status, error) {
            console.error('[AI Prompts] Save error:', error);
            functions.toast_message('error', 'Error al guardar prompt', 'ERROR');
        }
    });
}

// --
function delete_prompt(promptId) {
    console.log('[AI Prompts] Deleting:', promptId);

    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción no se puede deshacer. El prompt será eliminado permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: BASE_URL + 'Bot_Configuration/delete_ai_prompt/' + promptId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    functions.toast_message(response.type, response.msg, response.status);

                    if (response.status === 'OK') {
                        load_prompts();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('[AI Prompts] Delete error:', error);
                    functions.toast_message('error', 'Error al eliminar prompt', 'ERROR');
                }
            });
        }
    });
}
