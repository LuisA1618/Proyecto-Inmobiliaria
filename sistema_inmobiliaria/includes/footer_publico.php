    </main>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-col">
                <div class="brand footer-brand">
                    <span class="brand-icon" aria-hidden="true">&#9670;</span>
                    <div class="brand-text">
                        <strong>ALTAVISTA</strong>
                        <span>Inmobiliaria &amp; Bienes Raíces</span>
                    </div>
                </div>
                <p class="footer-desc">
                    Conectamos propietarios con compradores e inversionistas mediante asesoramiento comercial transparente, gestión integral y atención personalizada.
                </p>
            </div>

            <div class="footer-col">
                <h4>Navegación</h4>
                <ul class="footer-links">
                    <li><a href="<?= url('index.php') ?>">Inicio</a></li>
                    <li><a href="<?= url('catalogo.php') ?>">Todas las Propiedades</a></li>
                    <li><a href="<?= url('catalogo.php?operacion=Venta') ?>">Inmuebles en Venta</a></li>
                    <li><a href="<?= url('catalogo.php?operacion=Alquiler') ?>">Inmuebles en Alquiler</a></li>
                    <li><a href="<?= url('admin/login.php') ?>">Portal de Agentes</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Atención Comercial</h4>
                <ul class="footer-contact">
                    <li>Av. San Martín #450, Equipetrol</li>
                    <li>Tel: +591 (3) 342-8900</li>
                    <li>contacto@altavista-inmobiliaria.com</li>
                    <li>Lunes a Sábado: 08:30 - 19:00</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container footer-bottom-inner">
                <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Sistema Web de Gestión Inmobiliaria.</p>
                <p>Desarrollado con PHP, MySQL, HTML, CSS y JavaScript.</p>
            </div>
        </div>
    </footer>
    <script src="<?= url('assets/js/main.js') ?>"></script>
</body>
</html>
