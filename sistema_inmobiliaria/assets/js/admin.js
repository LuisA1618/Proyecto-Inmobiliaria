/**
 * Scripts interactivos del Panel Administrativo - Inmobiliaria Altavista
 */
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');

    if (sidebar && sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.add('open');
        });
    }

    if (sidebar && sidebarClose) {
        sidebarClose.addEventListener('click', function () {
            sidebar.classList.remove('open');
        });
    }

    const imagenInput = document.getElementById('imagen');
    const imagePreview = document.getElementById('imagePreview');

    if (imagenInput && imagePreview) {
        imagenInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;

            const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Formato no permitido. Selecciona una imagen JPG, PNG o WEBP.');
                this.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('La imagen supera el límite de 5 MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (ev) {
                imagePreview.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    const propiedadForm = document.getElementById('propiedadForm');
    if (propiedadForm) {
        propiedadForm.addEventListener('submit', function (e) {
            const propietario = document.getElementById('id_propietario')?.value;
            const agente = document.getElementById('id_agente')?.value;
            const titulo = document.getElementById('titulo')?.value.trim();
            const direccion = document.getElementById('direccion')?.value.trim();
            const precio = parseFloat(document.getElementById('precio')?.value || '0');
            const superficie = parseFloat(document.getElementById('superficie_m2')?.value || '0');

            let errors = [];
            if (!propietario) errors.push('Debes seleccionar un propietario.');
            if (!agente) errors.push('Debes seleccionar un agente responsable.');
            if (!titulo) errors.push('El título de la propiedad es obligatorio.');
            if (!direccion) errors.push('La dirección es obligatoria.');
            if (isNaN(precio) || precio <= 0) errors.push('El precio debe ser mayor a 0.');
            if (isNaN(superficie) || superficie <= 0) errors.push('La superficie en m² debe ser mayor a 0.');

            if (errors.length > 0) {
                e.preventDefault();
                alert('Verifica los campos obligatorios:\n\n• ' + errors.join('\n• '));
            }
        });
    }

    const propietarioForm = document.getElementById('propietarioForm');
    if (propietarioForm) {
        propietarioForm.addEventListener('submit', function (e) {
            const nombre = document.getElementById('nombre')?.value.trim();
            const apellido = document.getElementById('apellido')?.value.trim();
            const doc = document.getElementById('documento_identidad')?.value.trim();
            const tel = document.getElementById('telefono')?.value.trim();

            if (!nombre || !apellido || !doc || !tel) {
                e.preventDefault();
                alert('Completa todos los campos obligatorios: Nombre, Apellido, Documento de Identidad y Teléfono.');
            }
        });
    }
});
