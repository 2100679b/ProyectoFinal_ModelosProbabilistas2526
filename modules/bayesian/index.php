<?php
$pageTitle = 'Redes Bayesianas - Modelos Probabilísticos';
$moduleCSS = 'bayesian';
$moduleJS = 'bayesian';

// IMPORTANTE: Cargar config.php con ruta relativa correcta
require_once __DIR__ . '/../../config.php';
include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>


<div class="bayesian-container">
    <div class="bayesian-header">
        <h1><i class="fas fa-project-diagram"></i> Redes Bayesianas</h1>
        <p>Representación gráfica de dependencias probabilísticas con algoritmos de inferencia exacta</p>
    </div>

    <div class="control-panel">
        <h3>Configuración de Red</h3>
        
        <div class="control-row">
            <div class="form-group">
                <label for="example-select">Seleccionar Ejemplo:</label>
                <select id="example-select" class="form-control">
                    <option value="">-- Seleccionar ejemplo --</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Algoritmo de Inferencia:</label>
                <div class="algorithm-selector">
                    <button class="algorithm-btn active" data-algorithm="enumeration">
                        <i class="fas fa-list-ol"></i> Enumeración
                    </button>
                    <button class="algorithm-btn" data-algorithm="variable_elimination">
                        <i class="fas fa-compress-alt"></i> Eliminación de Variables
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="network-structure">
        <h4>Estructura de la Red</h4>
        <div id="network-nodes-list"></div>
    </div>

    <div class="visualization-container">
        <h3>Visualización de la Red Bayesiana</h3>
        <div id="bayesian-network-graph"></div>
    </div>

    <div id="probability-tables"></div>

    <div class="query-panel">
        <h4>Configurar Consulta</h4>
        <div id="query-variables"></div>
        <div id="evidence-variables"></div>
        
        <button id="run-inference" class="btn btn-primary">
            <i class="fas fa-play"></i> Ejecutar Inferencia
        </button>
    </div>

    <div class="inference-results" id="inference-results">
        <h3>Resultados</h3>
        <p>Configura una consulta y ejecuta la inferencia para ver los resultados.</p>
    </div>
</div>

<?php include BASE_PATH . '/includes/footer.php'; ?>
