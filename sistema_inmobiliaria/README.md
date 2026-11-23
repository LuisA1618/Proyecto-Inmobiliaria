# Sistema Web de Gestión Inmobiliaria — Inmobiliaria Altavista

Proyecto universitario completo desarrollado con **PHP 8+**, **MySQL / MariaDB**, **HTML5**, **CSS3** y **JavaScript**, listo para ejecutarse localmente mediante **XAMPP** y **phpMyAdmin** sin dependencias externas de pago.

---

## 1. Requisitos del Entorno Local

- **XAMPP** (incluye Apache, PHP 7.4 / 8.x, MySQL / MariaDB y phpMyAdmin).
- Navegador web moderno (Chrome, Edge, Firefox, Brave).

---

## 2. Guía de Instalación y Ejecución Paso a Paso

1. **Descomprimir el archivo ZIP**
   Extrae la carpeta `sistema_inmobiliaria` contenida en el archivo comprimido.

2. **Copiar al directorio de XAMPP**
   Copia la carpeta `sistema_inmobiliaria` dentro de:
   ```text
   C:\xampp\htdocs\sistema_inmobiliaria
   ```
   *(El sistema detecta dinámicamente el nombre de la carpeta en `htdocs`, por lo que funcionará incluso si decides renombrarla).*

3. **Iniciar servicios en XAMPP**
   Abre el **XAMPP Control Panel** e inicia:
   - **Apache** (`Start`)
   - **MySQL** (`Start`)

4. **Importar la Base de Datos en phpMyAdmin**
   - Abre en tu navegador: `http://localhost/phpmyadmin`
   - Haz clic en la pestaña **Importar** (Import).
   - Selecciona el archivo:
     ```text
     C:\xampp\htdocs\sistema_inmobiliaria\database\inmobiliaria_db.sql
     ```
   - Presiona **Continuar / Importar** al final de la página.
   - Se creará automáticamente la base de datos `inmobiliaria_db` con todas sus tablas (`agentes`, `propietarios`, `propiedades`, `publicaciones`, `consultas_clientes`) y datos de prueba listos para usar.

5. **Ejecutar el Sistema en el Navegador**
   - **Portal Público (Clientes e Interesados):**
     ```text
     http://localhost/sistema_inmobiliaria/
     ```
   - **Panel Administrativo (Agentes Inmobiliarios):**
     ```text
     http://localhost/sistema_inmobiliaria/admin/login.php
     ```

---

## 3. Credenciales de Prueba para el Panel Administrativo

La base de datos precargada incluye 3 agentes inmobiliarios activos (todos con la contraseña **`Admin123*`**):

| Agente | Correo Electrónico | Contraseña | Rol |
| :--- | :--- | :--- | :--- |
| **Carlos Mendoza** | `carlos.mendoza@altavista.com` | `Admin123*` | Director Comercial |
| **Valeria Rojas** | `valeria.rojas@altavista.com` | `Admin123*` | Asesora Inmobiliaria Senior |
| **Diego Fernández** | `diego.fernandez@altavista.com` | `Admin123*` | Especialista Corporativo y Terrenos |

*(En la pantalla de inicio de sesión también dispones de un botón **"Autocompletar acceso de prueba"** para ingresar con un solo clic, además de la opción para registrar nuevos agentes).*

---

## 4. Estructura de Carpetas del Proyecto

```text
sistema_inmobiliaria/
├── index.php                    # Portada pública de la inmobiliaria y buscador rápido
├── catalogo.php                 # Catálogo público de propiedades con filtros dinámicos
├── propiedad.php                # Ficha detallada del inmueble y contacto directo con el agente
├── README.md                    # Manual de instalación, arquitectura y guía de pruebas
├── database/
│   └── inmobiliaria_db.sql      # Script SQL completo (estructura PK/FK + datos de prueba)
├── config/
│   ├── database.php             # Conexión PDO a MySQL con manejo de errores
│   └── app.php                  # Configuración global, rutas dinámicas, CSRF y helpers
├── includes/
│   ├── header_publico.php       # Cabecera y navegación pública
│   ├── footer_publico.php       # Pie de página público
│   ├── header_admin.php         # Barra lateral y superior del panel administrativo
│   └── footer_admin.php         # Cierre del layout administrativo
├── admin/
│   ├── login.php                # Inicio de sesión de agentes
│   ├── registro.php             # Registro de nuevos agentes
│   ├── logout.php               # Cierre de sesión seguro
│   ├── index.php                # Dashboard principal con métricas KPI
│   ├── propietarios.php         # Listado y gestión de propietarios
│   ├── propietario_form.php     # Formulario de registro/edición de propietarios
│   ├── propiedades.php          # Listado completo de propiedades en cartera
│   ├── propiedad_form.php       # Formulario de registro/edición y asociación propietario-agente
│   ├── publicaciones.php        # Gestión de publicaciones web (Publicar / Retirar / Destacar)
│   ├── consultas.php            # Bandeja de mensajes enviados por clientes interesados
│   └── perfil.php               # Actualización de datos de contacto del agente
├── actions/
│   ├── auth_login.php           # Procesamiento de autenticación con password_verify()
│   ├── auth_registro.php        # Alta de nuevos agentes con password_hash()
│   ├── propietario_guardar.php  # Alta y edición de propietarios
│   ├── propietario_eliminar.php # Eliminación protegida de propietarios
│   ├── propiedad_guardar.php    # Alta y edición de propiedades + subida segura de fotografías
│   ├── propiedad_eliminar.php   # Eliminación controlada de propiedades
│   ├── publicacion_estado.php   # Transacciones de publicación y retiro sin borrar propiedad
│   └── consulta_enviar.php      # Recepción de consultas desde la página pública
├── assets/
│   ├── css/
│   │   ├── style.css            # Hoja de estilos del portal público
│   │   └── admin.css            # Hoja de estilos del panel administrativo
│   ├── js/
│   │   ├── main.js              # Validaciones y menú responsivo público
│   │   └── admin.js             # Vista previa de fotos, validaciones y UI administrativa
│   └── img/
│       ├── hero-bg.jpg          # Banner principal
│       └── no-image.jpg         # Imagen de respaldo
└── uploads/
    └── propiedades/             # Directorio de fotografías de propiedades subidas y de muestra
```

---

## 5. Verificación de los Casos de Uso Obligatorios

1. **Caso de Uso 1 — Registrar propiedad y asociarla a un propietario:**
   - Ingresa al panel administrativo &rarr; **Propietarios** &rarr; **+ Nuevo Propietario** y registra los datos del dueño.
   - Ve a **Propiedades** &rarr; **+ Registrar Nueva Propiedad**, completa los datos del inmueble (dirección, descripción, precio, tipo, habitaciones, baños, superficie, fotografía), selecciona al propietario recién registrado y al agente responsable, y guarda.
2. **Caso de Uso 2 — Publicar propiedad en la página web:**
   - Ingresa a **Publicaciones Web** (o desde **Propiedades**).
   - Observa la diferenciación entre propiedades **Registradas**, **Publicadas** y **No Publicadas**.
   - Haz clic en **Publicar en Web** sobre una propiedad registrada; inmediatamente aparecerá en `catalogo.php`.
   - Haz clic en **Retirar Publicación**; la propiedad dejará de mostrarse en el portal público pero conservará intacto su registro en la base de datos.
3. **Caso de Uso 3 — Consultar propiedades y contactar al agente de ventas:**
   - Abre `http://localhost/sistema_inmobiliaria/` sin iniciar sesión.
   - Entra a **Catálogo de Propiedades**, filtra por operación o tipo y haz clic en **Ver Detalles** en cualquier propiedad.
   - Visualiza las características completas, la fotografía y la tarjeta del **Agente Responsable de la Venta** con enlaces directos a **Teléfono**, **WhatsApp**, **Correo Electrónico** y formulario de consulta directa.
