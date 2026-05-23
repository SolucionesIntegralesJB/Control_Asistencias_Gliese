/* Attendance System - Main JavaScript */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Attendance System loaded');
});

// Utility functions
function showAlert(message, type = 'info') {
    alert(message);
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required]');
    for (let input of inputs) {
        if (!input.value.trim()) {
            alert('Por favor complete todos los campos requeridos');
            input.focus();
            return false;
        }
    }
    return true;
}
