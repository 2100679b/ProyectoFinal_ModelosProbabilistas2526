<?php
/**
 * REDES BAYESIANAS - Interfaz Principal
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

// Incluir configuración
require_once __DIR__ . '/../../config.php';

// Título personalizado
$pageTitle = 'Redes Bayesianas - Modelos Probabilísticos';
$moduleCSS = 'bayesian';
$moduleJS = 'bayesian';

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/navbar.php';
?>

<!-- Header del módulo -->
<div class="bayesian-header">
    <div class="container">
        <h1><i class="fas fa-project-diagram"></i> Redes Bayesianas</h1>
        <p>Modelos gráficos probabilísticos que representan relaciones de dependencia entre variables aleatorias</p>
    </div>
</div>

<!-- Contenedor principal -->
<div class="container">
    <div class="bayesian-container">
        
        <!-- Panel lateral (Sidebar) -->
        <aside class="bayesian-sidebar">
            
            <!-- Sección: Ejemplos -->
            <div class="sidebar-section">
                <h3><i class="fas fa-folder-open"></i> Ejemplos</h3>
                <div class="example-buttons">
                    <button class="btn-example" data-example="alarm">
                        <i class="fas fa-bell"></i> Alarma-Terremoto-Ladrón
                    </button>
                    <button class="btn-example" data-example="medical">
                        <i class="fas fa-heartbeat"></i> Red Médica
                    </button>
                    <button class="btn-example" data-example="diagnostic">
                        <i class="fas fa-wrench"></i> Diagnóstico de Fallas
                    </button>
                    <button class="btn-example" data-example="weather">
                        <i class="fas fa-cloud-sun"></i> Predicción Climática
                    </button>
                </div>
            </div>
            
            <!-- Sección: Algoritmos (Ahora funcionales) -->
            <div class="sidebar-section">
                <h3><i class="fas fa-cogs"></i> Algoritmo de Inferencia</h3>
                <div class="algorithm-selector">
                    <div class="algorithm-option">
                        <!-- El atributo onchange notifica al JS del cambio -->
                        <input type="radio" name="algorithm" id="algo-enum" value="enumeration" checked onchange="window.onAlgorithmChange()">
                        <label for="algo-enum">Enumeración Exacta</label>
                    </div>
                    <div class="algorithm-option">
                        <input type="radio" name="algorithm" id="algo-elim" value="variable_elimination" onchange="window.onAlgorithmChange()">
                        <label for="algo-elim">Eliminación de Variables</label>
                    </div>
                </div>
                <small class="text-muted" style="display:block; margin-top:10px; font-size:0.85em;">
                    <i class="fas fa-info-circle"></i> Selecciona el método para calcular probabilidades.
                </small>
            </div>
            
            <!-- Sección: Acciones -->
            <div class="sidebar-section">
                <h3><i class="fas fa-tools"></i> Acciones</h3>
                <button class="btn btn-primary" onclick="createNewNetwork()" style="width: 100%; margin-bottom: 0.5rem;">
                    <i class="fas fa-plus"></i> Nueva Red
                </button>
                <!-- Botón de guardar manual (sin autoguardado) -->
                <button class="btn btn-secondary" onclick="saveNetwork()" style="width: 100%; margin-bottom: 0.5rem;">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <button class="btn btn-secondary" onclick="exportNetwork()" style="width: 100%; margin-bottom: 0.5rem;">
                    <i class="fas fa-download"></i> Exportar JSON
                </button>
                <!-- Botón de Limpiar Todo (Nuevo) -->
                <button class="btn btn-outline-danger" onclick="window.clearAll()" style="width: 100%;">
                    <i class="fas fa-trash-alt"></i> Limpiar Todo
                </button>
            </div>
            
        </aside>
        
        <!-- Área principal -->
        <main class="bayesian-main">
            
            <!-- Tabs -->
            <div class="bayesian-tabs">
                <button class="tab-button active" data-tab="network">
                    <i class="fas fa-network-wired"></i> Red
                </button>
                <button class="tab-button" data-tab="probabilities">
                    <i class="fas fa-table"></i> Probabilidades
                </button>
                <button class="tab-button" data-tab="inference">
                    <i class="fas fa-search"></i> Inferencia
                </button>
                <button class="tab-button" data-tab="results">
                    <i class="fas fa-chart-bar"></i> Resultados
                </button>
            </div>
            
            <!-- Contenido de tabs -->
            <div class="tab-content">
                
                <!-- Tab 1: Visualización de la Red -->
                <div class="tab-panel active" id="tab-network">
                    <h3>Estructura de la Red Bayesiana</h3>
                    
                    <div class="network-controls">
                        <button class="btn btn-secondary btn-sm" onclick="addNode()">
                            <i class="fas fa-plus-circle"></i> Agregar Nodo
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="addEdge()">
                            <i class="fas fa-link"></i> Agregar Arista
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="removeSelected()">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="fitNetwork()">
                            <i class="fas fa-expand"></i> Ajustar Vista
                        </button>
                    </div>
                    
                    <div id="network-visualization" class="network-visualization"></div>
                    
                    <div id="node-info" class="node-info-panel" style="display: none;">
                        <h4><i class="fas fa-info-circle"></i> Información del Nodo</h4>
                        <div id="node-details"></div>
                    </div>
                </div>
                
                <!-- Tab 2: Probabilidades -->
                <div class="tab-panel" id="tab-probabilities">
                    <h3>Tablas de Probabilidad Condicional (CPT)</h3>
                    <p class="text-muted">Las tablas definen cómo cada nodo depende de sus padres.</p>
                    <div id="cpt-container">
                        <div class="alert alert-info">Carga un ejemplo para ver las tablas.</div>
                    </div>
                </div>
                
                <!-- Tab 3: Inferencia -->
                <div class="tab-panel" id="tab-inference">
                    <h3>Realizar Consulta</h3>
                    <p class="text-muted">Configura una consulta de inferencia bayesiana.</p>
                    
                    <div class="query-builder">
                        <div class="query-section">
                            <h4><i class="fas fa-question-circle"></i> Variable a Consultar</h4>
                            <div id="query-variables" class="variable-selector"></div>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-secondary" onclick="window.clearQuery()">
                                    <i class="fas fa-times"></i> Limpiar selección
                                </button>
                            </div>
                        </div>
                        
                        <div class="query-section">
                            <h4><i class="fas fa-check-circle"></i> Evidencia (Valores Conocidos)</h4>
                            <div id="evidence-variables" class="variable-selector"></div>
                            <div id="evidence-inputs" class="evidence-inputs"></div>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-secondary" onclick="window.clearEvidence()">
                                    <i class="fas fa-eraser"></i> Limpiar evidencia
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-3">
                            <button class="btn btn-primary btn-run" onclick="runInference()" id="btn-run-inference" style="padding: 12px 30px; font-size: 1.1em;">
                                <i class="fas fa-play"></i> Ejecutar Inferencia
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Tab 4: Resultados -->
                <div class="tab-panel" id="tab-results">
                    <h3>Resultados de la Inferencia</h3>
                    <div id="results-container">
                        <div class="alert alert-info">Los resultados aparecerán aquí después de ejecutar una consulta.</div>
                    </div>
                </div>
                
            </div>
        </main>
        
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>