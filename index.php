<?php
require_once 'config.php';
require_once 'includes/functions.php';
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container">
    <div class="hero-section">
        <h1>Proyecto de Modelos Probabilísticos</h1>
        <p class="lead">Sistema interactivo para explorar Redes Bayesianas, Cadenas de Markov y Modelos Ocultos de Markov</p>
    </div>

    <div class="row modules-grid">
        <div class="col-md-4">
            <div class="module-card bayesian-card">
                <div class="module-icon">🔷</div>
                <h3>Redes Bayesianas</h3>
                <p>Representación gráfica de relaciones probabilísticas entre variables con algoritmos de inferencia.</p>
                <ul class="features-list">
                    <li>Enumeración exacta</li>
                    <li>Eliminación de variables</li>
                    <li>Visualización de grafos</li>
                    <li>4 ejemplos interactivos</li>
                </ul>
                <a href="modules/bayesian/index.php" class="btn btn-primary">Explorar RB</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="module-card markov-card">
                <div class="module-icon">🔄</div>
                <h3>Cadenas de Markov</h3>
                <p>Modelos estocásticos para sistemas que transicionan entre estados con probabilidades definidas.</p>
                <ul class="features-list">
                    <li>Matrices de transición</li>
                    <li>Probabilidades estacionarias</li>
                    <li>Simulación de cadenas</li>
                    <li>3 ejemplos aplicados</li>
                </ul>
                <a href="modules/markov/index.php" class="btn btn-success">Explorar CM</a>
            </div>
        </div>

        <div class="col-md-4">
            <div class="module-card hmm-card">
                <div class="module-icon">🎯</div>
                <h3>Modelos Ocultos de Markov</h3>
                <p>HMM para secuencias observables con estados ocultos subyacentes.</p>
                <ul class="features-list">
                    <li>Algoritmo Forward</li>
                    <li>Algoritmo Viterbi</li>
                    <li>Forward-Backward</li>
                    <li>3 ejemplos avanzados</li>
                </ul>
                <a href="modules/hmm/index.php" class="btn btn-warning">Explorar HMM</a>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h2>Acerca del Proyecto</h2>
        <p>Este proyecto educativo implementa tres modelos probabilísticos fundamentales en inteligencia artificial y aprendizaje automático. Cada módulo incluye implementaciones algorítmicas, visualizaciones interactivas y ejemplos prácticos.</p>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <h4>Tecnologías Utilizadas</h4>
                <ul>
                    <li><strong>Backend:</strong> PHP 7.4+</li>
                    <li><strong>Frontend:</strong> HTML5, CSS3, JavaScript</li>
                    <li><strong>Visualización:</strong> D3.js, Vis.js</li>
                    <li><strong>Estilos:</strong> Bootstrap 5</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h4>Referencias Académicas</h4>
                <ul>
                    <li>Russell & Norvig - Inteligencia Artificial</li>
                    <li>Barber - Bayesian Reasoning and ML</li>
                    <li>Ibe - Markov Processes</li>
                    <li>Rabiner - Tutorial on HMM</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
