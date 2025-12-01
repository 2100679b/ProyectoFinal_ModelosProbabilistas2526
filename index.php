<?php
/**
 * Página Principal - Modelos Probabilísticos
 * UMSNH - Facultad de Ingeniería Eléctrica
 */

// Cargar configuración
require_once __DIR__ . '/config.php';

// Definir título de página
$pageTitle = 'Modelos Probabilísticos - Inicio';

// Cargar componentes comunes
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<div class="container">
    <!-- Sección Hero -->
    <div class="hero-section">
        <h1>Proyecto de Modelos Probabilísticos</h1>
        <p class="lead">Sistema interactivo para explorar Redes Bayesianas, Cadenas de Markov y Modelos Ocultos de Markov</p>
    </div>

    <!-- Grid de Módulos -->
    <div class="row modules-grid">
        <!-- Módulo: Redes Bayesianas -->
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
                <a href="<?php echo BASE_URL; ?>/modules/bayesian/index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Explorar RB
                </a>
            </div>
        </div>

        <!-- Módulo: Cadenas de Markov -->
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
                <a href="<?php echo BASE_URL; ?>/modules/markov/index.php" class="btn btn-success">
                    <i class="fas fa-arrow-right"></i> Explorar CM
                </a>
            </div>
        </div>

        <!-- Módulo: Modelos Ocultos de Markov -->
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
                <a href="<?php echo BASE_URL; ?>/modules/hmm/index.php" class="btn btn-warning">
                    <i class="fas fa-arrow-right"></i> Explorar HMM
                </a>
            </div>
        </div>
    </div>

    <!-- Sección de Información -->
    <div class="info-section">
        <h2>Acerca del Proyecto</h2>
        <p>
            Este proyecto educativo implementa tres modelos probabilísticos fundamentales 
            en inteligencia artificial y aprendizaje automático. Cada módulo incluye 
            implementaciones algorítmicas, visualizaciones interactivas y ejemplos prácticos.
        </p>
        
        <div class="row mt-4">
            <!-- Tecnologías -->
            <div class="col-md-6">
                <h4><i class="fas fa-code"></i> Tecnologías Utilizadas</h4>
                <ul>
                    <li><strong>Backend:</strong> PHP <?php echo phpversion(); ?></li>
                    <li><strong>Frontend:</strong> HTML5, CSS3, JavaScript</li>
                    <li><strong>Visualización:</strong> Vis.js</li>
                    <li><strong>Estilos:</strong> CSS personalizado</li>
                    <li><strong>Iconos:</strong> Font Awesome 6</li>
                </ul>
            </div>
            
            <!-- Referencias -->
            <div class="col-md-6">
                <h4><i class="fas fa-book"></i> Referencias Académicas</h4>
                <ul>
                    <li>Russell & Norvig - Inteligencia Artificial: Un Enfoque Moderno</li>
                    <li>Barber - Bayesian Reasoning and Machine Learning</li>
                    <li>Ibe - Markov Processes for Stochastic Modelling</li>
                    <li>Rabiner - Tutorial on Hidden Markov Models</li>
                </ul>
            </div>
        </div>

        <!-- Información adicional -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h4><i class="fas fa-info-circle"></i> Características del Sistema</h4>
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <h5>Visualización Interactiva</h5>
                        <p>Grafos dinámicos para comprender las relaciones entre variables y estados</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-calculator"></i>
                        <h5>Algoritmos Implementados</h5>
                        <p>Inferencia exacta, simulación y cálculo de probabilidades</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-file-code"></i>
                        <h5>Ejemplos Prácticos</h5>
                        <p>Casos de uso reales en diagnóstico, predicción y análisis</p>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-mobile-alt"></i>
                        <h5>Multiplataforma</h5>
                        <p>Funciona en Windows, Linux y Termux (Android)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de inicio rápido -->
    <div class="quick-start">
        <h2><i class="fas fa-rocket"></i> Inicio Rápido</h2>
        <p>Selecciona un módulo para comenzar a explorar los modelos probabilísticos:</p>
        <div class="quick-links">
            <a href="<?php echo BASE_URL; ?>/modules/bayesian/index.php" class="quick-link bayesian">
                <i class="fas fa-project-diagram"></i>
                <span>Redes Bayesianas</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/markov/index.php" class="quick-link markov">
                <i class="fas fa-sync-alt"></i>
                <span>Cadenas de Markov</span>
            </a>
            <a href="<?php echo BASE_URL; ?>/modules/hmm/index.php" class="quick-link hmm">
                <i class="fas fa-eye-slash"></i>
                <span>HMM</span>
            </a>
        </div>
    </div>
</div>

<!-- Estilos adicionales para la página principal -->
<style>
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.feature-item {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    transition: transform 0.3s ease;
}

.feature-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.feature-item i {
    font-size: 2.5em;
    color: #2563eb;
    margin-bottom: 10px;
}

.feature-item h5 {
    color: #1e40af;
    margin: 10px 0;
}

.feature-item p {
    color: #6b7280;
    font-size: 0.9em;
}

.quick-start {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 12px;
    margin-top: 40px;
    text-align: center;
}

.quick-start h2 {
    color: white;
    margin-bottom: 15px;
}

.quick-links {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.quick-link {
    background: white;
    color: #333;
    padding: 20px 30px;
    border-radius: 8px;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    min-width: 150px;
}

.quick-link:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.quick-link i {
    font-size: 2em;
}

.quick-link.bayesian i {
    color: #2563eb;
}

.quick-link.markov i {
    color: #10b981;
}

.quick-link.hmm i {
    color: #f59e0b;
}

.quick-link span {
    font-weight: 600;
}

@media (max-width: 768px) {
    .features-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-links {
        flex-direction: column;
        align-items: stretch;
    }
    
    .quick-link {
        width: 100%;
    }
}
</style>

<?php include BASE_PATH . '/includes/footer.php'; ?>
