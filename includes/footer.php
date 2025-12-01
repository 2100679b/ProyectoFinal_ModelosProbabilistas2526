<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h4>Modelos Probabilísticos</h4>
                <p>Sistema interactivo para el aprendizaje de modelos probabilísticos en inteligencia artificial.</p>
            </div>
            
            <div class="footer-section">
                <h4>Módulos</h4>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/modules/bayesian/index.php">Redes Bayesianas</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/modules/markov/index.php">Cadenas de Markov</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/modules/hmm/index.php">Modelos Ocultos de Markov</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Referencias</h4>
                <ul>
                    <li>Russell & Norvig - IA: Un Enfoque Moderno</li>
                    <li>Barber - Bayesian Reasoning and ML</li>
                    <li>Rabiner - Tutorial on HMM</li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Universidad</h4>
                <p><strong>Universidad Michoacana de San Nicolás de Hidalgo</strong></p>
                <p>Facultad de Ingeniería Eléctrica</p>
                <p>Materia: Modelos Probabilistas (C17300-T)</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <span id="current-year"><?php echo date('Y'); ?></span> UMSNH - Facultad de Ingeniería Eléctrica</p>
            <p>Proyecto Educativo - Modelos Probabilísticos</p>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/visualization.js"></script>
<?php if (isset($moduleJS)): ?>
    <script src="<?php echo BASE_URL; ?>/assets/js/<?php echo $moduleJS; ?>.js"></script>
<?php endif; ?>

</body>
</html>

<style>
.footer {
    background: #1f2937;
    color: #e5e7eb;
    padding: 40px 0 20px 0;
    margin-top: 60px;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.footer-section h4 {
    color: white;
    margin-bottom: 15px;
    font-size: 1.2em;
}

.footer-section p {
    margin: 8px 0;
    line-height: 1.6;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin: 8px 0;
}

.footer-section ul li a {
    color: #9ca3af;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-section ul li a:hover {
    color: #60a5fa;
}

.footer-bottom {
    text-align: center;
    padding-top: 20px;
    border-top: 1px solid #374151;
}

.footer-bottom p {
    margin: 5px 0;
    color: #9ca3af;
}
</style>
