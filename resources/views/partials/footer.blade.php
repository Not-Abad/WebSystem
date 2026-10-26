<footer class="site-footer">
    <canvas id="footer-particles"></canvas>
    
    <div class="footer-container">
        
        <div class="footer-col footer-info">
            <div class="footer-logo">
                <img src="{{ asset('imagenes/logo.png') }}" alt="Logo Escuela">
            </div>
            <p class="school-name">Escuela de Bachilleres Lic. Antonio María de Rivera</p>
            <p class="school-details">Turno Vespertino · CCT 30EBH0389X</p>
        </div>

        <div class="footer-col footer-location">
            <h4>Ubicación</h4>
            <div class="map-container">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3760.564460854783!2d-96.91987328938124!3d19.517365481704175!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85db32084e08c17b%3A0xed2187743d1720c6!2sBachilleres%20Antonio%20María%20de%20Rivera!5e0!3m2!1ses-419!2smx!4v1793037526412!5m2!1ses-419!2smx" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
            </div>
            <p class="address">Circuito Gonzalo Aguirre Beltrán s/n Zona Universitaria<br>Xalapa-Enríquez, Veracruz C.P. 91090</p>
        </div>

        <div class="footer-col footer-contact">
            <h4>Contacto</h4>
            <ul class="contact-links">
                <li><a href="https://www.facebook.com/antoniomariaderiveravesp" target="_blank"><i class="fab fa-facebook"></i> Facebook</a></li>
                <li><a href="https://www.instagram.com/antoniovespertina/" target="_blank"><i class="fab fa-instagram"></i> Instagram</a></li>
                <li><a href="mailto:hola@amrv.mx"><i class="fas fa-envelope"></i> Correo</a></li>
                <li><a href="tel:+522282996487"><i class="fas fa-phone"></i> Teléfono</a></li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom-bar">
        <div class="gov-banner">
            <img src="{{ asset('imagenes/veracruz.png') }}" alt="Gobierno del Estado de Veracruz, SEV, SEMSyS y DGB">
        </div>
    </div>
</footer>

<script>
    const footerCanvas = document.getElementById('footer-particles');
    if (footerCanvas) {
        const fCtx = footerCanvas.getContext('2d');

        function ajustarFooterCanvas() {
            footerCanvas.width = footerCanvas.offsetWidth;
            footerCanvas.height = footerCanvas.offsetHeight;
        }
        ajustarFooterCanvas();
        window.addEventListener('resize', ajustarFooterCanvas);

        const footerParticulas = [];
        const footerCantidad = 45;

        for (let i = 0; i < footerCantidad; i++) {
            footerParticulas.push({
                x: Math.random() * footerCanvas.width,
                y: Math.random() * footerCanvas.height,
                velocidad: 0.4 + Math.random() * 1.0,
                opacidad: 0.15 + Math.random() * 0.5
            });
        }

        function animarFooter() {
            fCtx.clearRect(0, 0, footerCanvas.width, footerCanvas.height);

            footerParticulas.forEach(p => {
                p.y += p.velocidad;
                if (p.y > footerCanvas.height) {
                    p.y = 0;
                    p.x = Math.random() * footerCanvas.width;
                }
                // Partículas verdes cyberpunk (0, 255, 102) de 2x2 px
                fCtx.fillStyle = `rgba(0, 255, 102, ${p.opacidad})`;
                fCtx.fillRect(p.x, p.y, 2, 2);
            });

            requestAnimationFrame(animarFooter);
        }
        animarFooter();
    }
</script>