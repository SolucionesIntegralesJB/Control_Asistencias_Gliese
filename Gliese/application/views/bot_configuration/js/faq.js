// --
function init_faq() {
    console.log('[FAQ] Initializing...');
    load_faqs();

    // Create button
    $('#btn-create-faq').on('click', function() {
        open_faq_modal('create');
    });

    // Edit button
    $(document).on('click', '.btn-edit-faq', function() {
        const faqId = $(this).data('id');
        open_faq_modal('edit', faqId);
    });

    // Delete button
    $(document).on('click', '.btn-delete-faq', function() {
        const faqId = $(this).data('id');
        delete_faq(faqId);
    });

    // Form submit
    $('#form-faq').on('submit', function(e) {
        e.preventDefault();
        save_faq();
    });

    // Fix aria-hidden on modal show
    const modalElement = document.getElementById('modal-faq');
    if (modalElement) {
        modalElement.addEventListener('show.bs.modal', function() {
            this.removeAttribute('aria-hidden');
        });
        modalElement.addEventListener('hidden.bs.modal', function() {
            this.setAttribute('aria-hidden', 'true');
        });
    }
}

// Variable global para almacenar las FAQs
let allFaqsData = [];

// --
function load_faqs() {
    $.ajax({
        url: BASE_URL + 'Bot_Configuration/get_faqs',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            const tbody = $('#faq-tbody');
            tbody.empty();

            // Guardar datos globalmente
            allFaqsData = (response.status === 'OK' && response.data) ? response.data : [];

            if (response.status === 'OK' && response.data && response.data.length > 0) {
                response.data.forEach(function(faq) {
                    const statusBadge = faq.is_active == 1 ? 
                        '<span class="badge bg-success">Activa</span>' : 
                        '<span class="badge bg-secondary">Inactiva</span>';
                    
                    const truncatedAnswer = faq.answer.length > 80 ? 
                        faq.answer.substring(0, 80) + '...' : 
                        faq.answer;
                    
                    const viewsCount = faq.views_count || 0;

                    const row = '<tr>' +
                        '<td>' + faq.order + '</td>' +
                        '<td><strong>' + faq.question + '</strong></td>' +
                        '<td>' + truncatedAnswer + '</td>' +
                        '<td><span class="badge bg-info">' + viewsCount + '</span></td>' +
                        '<td>' + statusBadge + '</td>' +
                        '<td class="text-nowrap">' +
                        '<button type="button" class="btn btn-sm btn-primary btn-edit-faq me-2" data-id="' + faq.id + '"><i data-feather="edit-2"></i></button>' +
                        '<button type="button" class="btn btn-sm btn-danger btn-delete-faq" data-id="' + faq.id + '"><i data-feather="trash-2"></i></button>' +
                        '</td>' +
                        '</tr>';
                    tbody.append(row);
                });
                feather.replace();
            } else {
                tbody.html('<tr><td colspan="6" class="text-center">No hay FAQs disponibles</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al cargar FAQs', 'ERROR');
            console.error('[FAQ] Load error:', error);
        }
    });
}

// --
function calculate_next_order() {
    if (allFaqsData.length === 0) {
        return 1;
    }

    let maxOrder = 0;
    allFaqsData.forEach(function(faq) {
        const order = parseInt(faq.order) || 0;
        if (order > maxOrder) {
            maxOrder = order;
        }
    });

    return maxOrder + 1;
}

// --
function open_faq_modal(mode, faqId) {
    if (mode === 'create') {
        $('#modal-faq-title').text('Nueva FAQ');
        $('#form-faq')[0].reset();
        $('#faq_id').val('');
        $('#faq_is_active').prop('checked', true);

        // Calcular y asignar el siguiente orden automáticamente
        const nextOrder = calculate_next_order();
        $('#faq_order').val(nextOrder);
        console.log('[FAQ] Siguiente orden sugerido:', nextOrder);

        // Abrir modal para crear
        const modal = new bootstrap.Modal(document.getElementById('modal-faq'));
        modal.show();
    } else if (mode === 'edit' && faqId) {
        $('#modal-faq-title').text('Editar FAQ');

        // Load FAQ data
        $.ajax({
            url: BASE_URL + 'Bot_Configuration/get_faqs',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'OK' && response.data) {
                    const faq = response.data.find(f => f.id == faqId);
                    if (faq) {
                        console.log('[FAQ Edit] Cargando FAQ:', faq);

                        $('#faq_id').val(faq.id);
                        $('#faq_order').val(faq.order);
                        $('#faq_question').val(faq.question);
                        $('#faq_answer').val(faq.answer);
                        $('#faq_category').val(faq.category);
                        $('#faq_is_active').prop('checked', faq.is_active == 1);

                        console.log('[FAQ Edit] Categoría seleccionada:', $('#faq_category').val());

                        // Abrir modal después de cargar los datos
                        const modal = new bootstrap.Modal(document.getElementById('modal-faq'));
                        modal.show();
                    }
                }
            },
            error: function() {
                functions.toast_message('error', 'Error al cargar datos de FAQ', 'ERROR');
            }
        });
    }
}

// --
function save_faq() {
    const faqId = $('#faq_id').val();
    const url = faqId ?
        BASE_URL + 'Bot_Configuration/update_faq' :
        BASE_URL + 'Bot_Configuration/create_faq';

    // Construir datos manualmente para incluir is_active correctamente
    const formData = {
        question: $('#faq_question').val(),
        answer: $('#faq_answer').val(),
        category: $('#faq_category').val(),
        order: $('#faq_order').val(),
        is_active: $('#faq_is_active').is(':checked') ? 1 : 0
    };

    // Agregar id solo si estamos editando
    if (faqId) {
        formData.id = faqId;
    }

    console.log('[FAQ] Enviando datos:', formData);
    console.log('[FAQ] URL:', url);

    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK') {
                functions.toast_message(response.type, response.msg, response.status);
                bootstrap.Modal.getInstance(document.getElementById('modal-faq')).hide();
                load_faqs();
            } else {
                functions.toast_message(response.type, response.msg || 'Error al guardar', response.status);
            }
        },
        error: function(xhr, status, error) {
            console.error('[FAQ] Response text:', xhr.responseText);
            console.error('[FAQ] Status:', xhr.status);
            console.error('[FAQ] Error:', error);
            functions.toast_message('error', 'Error al guardar FAQ: ' + xhr.status, 'ERROR');
        }
    });
}

// --
function delete_faq(faqId) {
    if (!confirm('¿Está seguro que desea eliminar esta FAQ?')) {
        return;
    }

    $.ajax({
        url: BASE_URL + 'Bot_Configuration/delete_faq',
        method: 'POST',
        data: { id: faqId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'OK') {
                functions.toast_message(response.type, response.msg, response.status);
                load_faqs();
            } else {
                functions.toast_message(response.type, response.msg || 'Error al eliminar', response.status);
            }
        },
        error: function(xhr, status, error) {
            functions.toast_message('error', 'Error al eliminar FAQ', 'ERROR');
            console.error('[FAQ] Delete error:', error);
        }
    });
}
