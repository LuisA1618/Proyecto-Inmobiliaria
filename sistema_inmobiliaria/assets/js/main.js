/**
 * Scripts interactivos del Portal Público - Inmobiliaria Altavista
 */
document.addEventListener('DOMContentLoaded', function () {
    const navToggle = document.getElementById('navToggle');
    const mainNav = document.getElementById('mainNav');

    if (navToggle && mainNav) {
        navToggle.addEventListener('click', function () {
            const isOpen = mainNav.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    const consultaForm = document.getElementById('consultaForm');
    if (consultaForm) {
        consultaForm.addEventListener('submit', function (e) {
            const nombre = document.getElementById('nombre_cliente');
            const telefono = document.getElementById('telefono_cliente');
            const email = document.getElementById('email_cliente');
            const mensaje = document.getElementById('mensaje');

            let errors = [];
            if (!nombre || nombre.value.trim().length < 2) {
                errors.push('Ingresa tu nombre completo.');
            }
            if (!telefono || telefono.value.trim().length < 6) {
                errors.push('Ingresa un teléfono o WhatsApp válido.');
            }
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                errors.push('Ingresa un correo electrónico válido.');
            }
            if (!mensaje || mensaje.value.trim().length < 5) {
                errors.push('Escribe un breve mensaje para el agente.');
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert('Por favor corrige los siguientes datos:\n\n• ' + errors.join('\n• '));
            }
        });
    }
});
