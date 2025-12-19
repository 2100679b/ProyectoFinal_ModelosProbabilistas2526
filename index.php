<?php
/**
 * Página Principal - Modelos Probabilísticos
 * UMSNH - Facultad de Ingeniería Eléctrica
 */

// Cargar configuración
require_once __DIR__ . '/config.php';

// Definir título de página
$pageTitle = 'Modelos Probabilísticos';

// Cargar componentes comunes
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<div class="container">
    <!-- Sección Hero -->
    <div class="hero-section">
        <h1>Modelos Probabilísticos</h1>
        <p class="lead">Universidad Michoacana de San Nicolás de Hidalgo</p>
        <p class="text-muted">Proyecto Académico - Facultad de Ingeniería Eléctrica</p>
    </div>

    <!-- Grid de Módulos -->
    <div class="row modules-grid">
        <!-- Módulo: Redes Bayesianas -->
        <div class="col-md-4">
            <div class="module-card bayesian-card">
                <div class="module-icon">🔷</div>
                <h3>Redes Bayesianas</h3>
                <p>Representación gráfica de relaciones probabilísticas entre variables.</p>
                <a href="<?php echo BASE_URL; ?>/modules/bayesian/index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Abrir Módulo
                </a>
            </div>
        </div>

        <!-- Módulo: Cadenas de Markov -->
        <div class="col-md-4">
            <div class="module-card markov-card">
                <div class="module-icon">🔄</div>
                <h3>Cadenas de Markov</h3>
                <p>Modelos de transición entre estados con probabilidades definidas.</p>
                <a href="<?php echo BASE_URL; ?>/modules/markov/index.php" class="btn btn-success">
                    <i class="fas fa-arrow-right"></i> Abrir Módulo
                </a>
            </div>
        </div>

        <!-- Módulo: Modelos Ocultos de Markov -->
        <div class="col-md-4">
            <div class="module-card hmm-card">
                <div class="module-icon">🎯</div>
                <h3>HMM</h3>
                <p>Secuencias observables con estados ocultos subyacentes.</p>
                <a href="<?php echo BASE_URL; ?>/modules/hmm/index.php" class="btn btn-purple">
                    <i class="fas fa-arrow-right"></i> Abrir Módulo
                </a>
            </div>
        </div>
    </div>

    <!-- Sección de Información -->
    <div class="info-section">
        <h2>Información del Proyecto</h2>
        <p>
            Proyecto académico desarrollado para la materia de <strong>Modelos Probabilísticos (CI7300-T)</strong>.
            Implementa tres modelos fundamentales en inteligencia artificial y aprendizaje automático.
        </p>
        
        <div class="row mt-4">
            <!-- Información Académica -->
            <div class="col-md-6">
                <h4><i class="fas fa-graduation-cap"></i> Información Académica</h4>
                <ul>
                    <li><strong>Materia:</strong> Modelos Probabilísticos</li>
                    <li><strong>Clave:</strong> CI7300-T</li>
                    <li><strong>Universidad:</strong> UMSNH</li>
                    <li><strong>Facultad:</strong> Ingeniería Eléctrica</li>
                </ul>
            </div>
            
            <!-- Contenido -->
            <div class="col-md-6">
                <h4><i class="fas fa-book"></i> Contenido del Proyecto</h4>
                <ul>
                    <li>Redes Bayesianas e Inferencia</li>
                    <li>Cadenas de Markov</li>
                    <li>Modelos Ocultos de Markov</li>
                    <li>Ejemplos interactivos</li>
                </ul>
            </div>
        </div>

        <!-- Referencias -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h4><i class="fas fa-book-open"></i> Referencias Principales</h4>
                <ul>
                    <li>Russell & Norvig - Inteligencia Artificial: Un Enfoque Moderno</li>
                    <li>David Barber - Bayesian Reasoning and Machine Learning</li>
                    <li>Oliver C. Ibe - Markov Processes for Stochastic Modelling</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Estilos simplificados -->
<style>
.hero-section {
    text-align: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    margin-bottom: 40px;
}

.hero-section h1 {
    font-size: 2.5em;
    margin-bottom: 10px;
    font-weight: 700;
}

.hero-section .lead {
    font-size: 1.2em;
    margin-bottom: 5px;
    opacity: 0.95;
}

.hero-section .text-muted {
    color: rgba(255, 255, 255, 0.8) !important;
    font-size: 1em;
}

.modules-grid {
    margin-bottom: 40px;
}

.module-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
    height: 100%;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Borde superior morado para HMM */
.hmm-card {
    border-top: 4px solid #8b5cf6;
}

.module-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

/* Borde superior morado para la tarjeta HMM */
.hmm-card {
    border-top: 4px solid #8b5cf6;
}

.module-icon {
    font-size: 3.5em;
    margin-bottom: 20px;
}

.module-card h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 1.5em;
    font-weight: 600;
}

.module-card p {
    color: #666;
    margin-bottom: 25px;
    min-height: 60px;
    line-height: 1.6;
}

.module-card .btn {
    padding: 10px 25px;
    font-weight: 600;
    border-radius: 8px;
}

/* Botón morado personalizado para HMM */
.btn-purple {
    background-color: #8b5cf6;
    color: white;
    border: none;
}

.btn-purple:hover {
    background-color: #7c3aed;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.btn-purple:active {
    background-color: #6d28d9;
    transform: translateY(0);
}

.info-section {
    background: #f8f9fa;
    padding: 40px;
    border-radius: 10px;
    margin-top: 40px;
}

.info-section h2 {
    color: #333;
    margin-bottom: 20px;
    font-weight: 700;
}

.info-section h4 {
    color: #667eea;
    margin-bottom: 15px;
    font-size: 1.2em;
    font-weight: 600;
}

.info-section ul {
    list-style: none;
    padding-left: 0;
}

.info-section ul li {
    padding: 8px 0;
    color: #555;
    line-height: 1.6;
}

.info-section ul li strong {
    color: #333;
}

.info-section ul li i {
    color: #667eea;
    margin-right: 8px;
}

@media (max-width: 768px) {
    .hero-section {
        padding: 30px 15px;
    }
    
    .hero-section h1 {
        font-size: 2em;
    }
    
    .hero-section .lead {
        font-size: 1em;
    }
    
    .module-card {
        margin-bottom: 20px;
    }
    
    .module-card p {
        min-height: auto;
    }
    
    .info-section {
        padding: 25px;
    }
}
</style>

<?php include BASE_PATH . '/includes/footer.php'; ?>