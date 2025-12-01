<?php
/**
 * ==============================================================================
 * FOOTER - Pie de Página
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Footer común para todas las páginas
 * ==============================================================================
 */
?>

    </main> <!-- Cierre del main-wrapper abierto en header.php -->

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                
                <!-- Información principal -->
                <div class="footer-info">
                    <p><strong><?php echo INSTITUTION_NAME; ?></strong></p>
                    <p><?php echo FACULTY_NAME; ?></p>
                    <p><?php echo CAREER_NAME; ?></p>
                    <p><?php echo SUBJECT_NAME; ?> - <?php echo PROJECT_DATE; ?></p>
                </div>
                
                <!-- Enlaces rápidos -->
                <div class="footer-links">
                    <a href="<?php echo url('index.php'); ?>">Inicio</a>
                    <a href="<?php echo url('modules/bayesian/index.php'); ?>">Redes Bayesianas</a>
                    <a href="<?php echo url('modules/markov/index.php'); ?>">Cadenas de Markov</a>
                    <a href="<?php echo url('modules/hmm/index.php'); ?>">HMM</a>
                    <a href="<?php echo url('docs/manual_usuario.pdf'); ?>" target="_blank">Documentación</a>
                </div>
                
                <!-- Copyright -->
                <div class="footer-copyright">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo INSTITUTION_NAME; ?></p>
                    <p>Proyecto Final - <?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?></p>
                    <p>
                        Desarrollado por: 
                        <strong>[Nombre Estudiante 1]</strong> y 
                        <strong>[Nombre Estudiante 2]</strong>
                    </p>
                </div>
                
                <!-- Contacto (opcional) -->
                <div class="footer-contact">
                    <p>
                        <small>
                            Para dudas o soporte: 
                            <a href="mailto:mauricio.reyes@umich.mx">mauricio.reyes@umich.mx</a>
                        </small>
                    </p>
                </div>
                
            </div>
        </div>
    </footer>

    <!-- JavaScript Principal -->
    <script src="<?php echo js('main.js'); ?>"></script>
    
    <!-- JavaScript específico del módulo (si existe) -->
    <?php if (isset($currentModule) && $currentModule): ?>
        <script src="<?php echo js($currentModule . '.js'); ?>"></script>
    <?php endif; ?>
    
    <!-- JavaScript adicional (si se define en la página) -->
    <?php if (isset($additionalJS) && is_array($additionalJS)): ?>
        <?php foreach ($additionalJS as $jsFile): ?>
            <script src="<?php echo js($jsFile); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Scripts inline (si se definen) -->
    <?php if (isset($inlineScripts)): ?>
        <script>
            <?php echo $inlineScripts; ?>
        </script>
    <?php endif; ?>
    
    <!-- Script de inicialización (ejecutado al final) -->
    <script>
        // Inicialización general al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            console.log('<?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?> cargado correctamente');
            
            // Añadir año actual a todos los elementos con clase 'current-year'
            document.querySelectorAll('.current-year').forEach(function(el) {
                el.textContent = new Date().getFullYear();
            });
            
            // Smooth scroll para enlaces ancla
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            // Botón "volver arriba" (si existe)
            const backToTopBtn = document.getElementById('backToTop');
            if (backToTopBtn) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopBtn.style.display = 'block';
                    } else {
                        backToTopBtn.style.display = 'none';
                    }
                });
                
                backToTopBtn.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        });
    </script>
    
    <!-- Botón flotante "Volver arriba" -->
    <button id="backToTop" class="back-to-top" style="display: none;" title="Volver arriba">
        ↑
    </button>
    
    <!-- Estilos adicionales para footer -->
    <style>
        .footer-info {
            margin-bottom: 1.5rem;
        }
        
        .footer-info p {
            margin-bottom: 0.5rem;
            opacity: 0.9;
        }
        
        .footer-copyright {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .footer-copyright p {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .footer-contact {
            margin-top: 1rem;
        }
        
        .footer-contact a {
            color: white;
            text-decoration: underline;
        }
        
        .footer-contact a:hover {
            opacity: 0.8;
        }
        
        /* Botón volver arriba */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            z-index: 999;
        }
        
        .back-to-top:hover {
            background-color: var(--primary-color);
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }
        
        @media (max-width: 768px) {
            .back-to-top {
                width: 40px;
                height: 40px;
                bottom: 20px;
                right: 20px;
                font-size: 1.2rem;
            }
        }
    </style>

</body>
</html>