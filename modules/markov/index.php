<?php
/**
 * CADENAS DE MARKOV - Interfaz Principal
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

// Incluir configuración
require_once __DIR__ . '/../../config.php';

// Título personalizado
$pageTitle = 'Cadenas de Markov - Procesos Estocásticos';
$moduleCSS = 'markov';
$moduleJS = 'markov';

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/navbar.php';
?>

<!-- Header del módulo -->
<div class="markov-header">
    <div class="container">
        <h1><i class="fas fa-chart-line"></i> Cadenas de Markov</h1>
        <p>Modelos estocásticos que describen una secuencia de eventos donde la probabilidad de cada evento depende únicamente del estado alcanzado en el evento anterior</p>
    </div>
</div>

<!-- Contenedor principal -->
<div class="container">
    <div class="markov-container">
        
        <!-- Panel lateral (Sidebar) -->
        <aside class="markov-sidebar">
            
            <!-- Sección: Ejemplos -->
            <div class="sidebar-section">
                <h3><i class="fas fa-folder-open"></i> Ejemplos</h3>
                <div class="example-buttons">
                    <button class="btn-example" data-example="weather">
                        <i class="fas fa-cloud-sun"></i> Predicción del Clima
                    </button>
                    <button class="btn-example" data-example="text_generation">
                        <i class="fas fa-font"></i> Generación de Texto
                    </button>
                    <button class="btn-example" data-example="stock_market">
                        <i class="fas fa-chart-line"></i> Mercado de Valores
                    </button>
                    <button class="btn-example" data-example="customer_behavior">
                        <i class="fas fa-users"></i> Comportamiento del Cliente
                    </button>
                </div>
            </div>
            
            <!-- Sección: Análisis (CORREGIDA CON IDS) -->
            <div class="sidebar-section">
                <h3><i class="fas fa-calculator"></i> Análisis</h3>
                <div class="analysis-buttons">
                    <button class="btn btn-primary" id="btn-steady-state" onclick="calculateSteadyState()" style="width: 100%; margin-bottom: 0.5rem;">
                        <i class="fas fa-balance-scale"></i> Estado Estacionario
                    </button>
                    <button class="btn btn-secondary" id="btn-transition-matrix" onclick="showTransitionMatrix()" style="width: 100%; margin-bottom: 0.5rem;">
                        <i class="fas fa-table"></i> Matriz de Transición
                    </button>
                    <button class="btn btn-secondary" id="btn-simulate-steps" onclick="simulateSteps()" style="width: 100%;">
                        <i class="fas fa-forward"></i> Simular Pasos
                    </button>
                </div>
            </div>
            
            <!-- Sección: Acciones -->
            <div class="sidebar-section">
                <h3><i class="fas fa-tools"></i> Acciones</h3>
                <button class="btn btn-primary" onclick="createNewChain()" style="width: 100%; margin-bottom: 0.5rem;">
                    <i class="fas fa-plus"></i> Nueva Cadena
                </button>
                <button class="btn btn-secondary" onclick="saveChain()" style="width: 100%; margin-bottom: 0.5rem;">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <button class="btn btn-secondary" onclick="exportChain()" style="width: 100%;">
                    <i class="fas fa-download"></i> Exportar JSON
                </button>
            </div>
            
        </aside>
        
        <!-- Área principal -->
        <main class="markov-main">
            
            <!-- Tabs -->
            <div class="markov-tabs">
                <button class="tab-button active" data-tab="diagram">
                    <i class="fas fa-project-diagram"></i> Diagrama
                </button>
                <button class="tab-button" data-tab="matrix">
                    <i class="fas fa-table"></i> Matriz
                </button>
                <button class="tab-button" data-tab="simulation">
                    <i class="fas fa-play"></i> Simulación
                </button>
                <button class="tab-button" data-tab="analysis">
                    <i class="fas fa-chart-bar"></i> Análisis
                </button>
            </div>
            
            <!-- Contenido de tabs -->
            <div class="tab-content">
                
                <!-- Tab 1: Diagrama de Estados -->
                <div class="tab-panel active" id="tab-diagram">
                    <h3>Diagrama de Estados</h3>
                    
                    <!-- Controles -->
                    <div class="diagram-controls">
                        <button class="btn btn-secondary btn-sm" onclick="addState()">
                            <i class="fas fa-plus-circle"></i> Agregar Estado
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="addTransition()">
                            <i class="fas fa-arrows-alt-h"></i> Agregar Transición
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="removeSelected()">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="fitDiagram()">
                            <i class="fas fa-expand"></i> Ajustar Vista
                        </button>
                    </div>
                    
                    <!-- Canvas de visualización -->
                    <div id="markov-visualization" class="markov-visualization"></div>
                    
                    <!-- Información de estado -->
                    <div id="state-info" class="state-info-panel" style="display: none;">
                        <h4><i class="fas fa-info-circle"></i> Información del Estado</h4>
                        <div id="state-details"></div>
                    </div>
                </div>
                
                <!-- Tab 2: Matriz de Transición -->
                <div class="tab-panel" id="tab-matrix">
                    <h3>Matriz de Transición</h3>
                    <p class="text-muted">La matriz de transición muestra las probabilidades de pasar de un estado a otro</p>
                    
                    <div id="matrix-container">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Carga un ejemplo para ver la matriz de transición
                        </div>
                    </div>
                </div>
                
                <!-- Tab 3: Simulación -->
                <div class="tab-panel" id="tab-simulation">
                    <h3>Simulación de Cadena de Markov</h3>
                    <p class="text-muted">Simula el comportamiento de la cadena a lo largo del tiempo</p>
                    
                    <div class="simulation-builder">
                        
                        <!-- Estado inicial -->
                        <div class="simulation-section">
                            <h4><i class="fas fa-flag-checkered"></i> Estado Inicial</h4>
                            <p class="text-muted" style="font-size: 0.9em;">Selecciona el estado de partida</p>
                            <div id="initial-state-selector" class="state-selector">
                                <p class="text-muted">Carga una cadena primero</p>
                            </div>
                        </div>
                        
                        <!-- Número de pasos -->
                        <div class="simulation-section">
                            <h4><i class="fas fa-step-forward"></i> Número de Pasos</h4>
                            <input type="number" id="num-steps" class="form-control" min="1" max="1000" value="10" placeholder="Número de pasos a simular">
                        </div>
                        
                        <!-- Botón de ejecución -->
                        <button class="btn btn-primary btn-run" onclick="runSimulation()" id="btn-run-simulation">
                            <i class="fas fa-play"></i> Ejecutar Simulación
                        </button>
                        
                        <!-- Contenedor de resultados de simulación -->
                        <div id="simulation-results">
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i> Los resultados de la simulación aparecerán aquí después de ejecutar.
                            </div>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Tab 4: Análisis -->
                <div class="tab-panel" id="tab-analysis">
                    <h3>Análisis de la Cadena</h3>
                    <p class="text-muted">Propiedades y características de la cadena de Markov</p>
                    
                    <!-- ID sincronizado con markov.js para mostrar resultados de Steady State -->
                    <div id="analysis-results">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Los resultados del análisis aparecerán aquí
                        </div>
                    </div>
                </div>
                
            </div>
        </main>
        
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>