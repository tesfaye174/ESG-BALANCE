// app.js

document.addEventListener('DOMContentLoaded', function () {
    // Faccio sparire gli alert dopo 4 secondi
    document.querySelectorAll('.alert').forEach(function (alert) {
        if (!alert.classList.contains('alert-dismissible')) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.6s';
                alert.style.opacity = '0';
                setTimeout(function () { if (alert.parentNode) alert.remove(); }, 600);
            }, 4000);
        }
    });

    // Scroll all'errore se presente
    var firstError = document.querySelector('.alert-danger, .alert-error');
    if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Tooltip Bootstrap
    if (window.bootstrap && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    }
});
