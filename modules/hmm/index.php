<?php
/**
 * MODELOS OCULTOS DE MARKOV (HMM) - Interfaz Principal
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

// Incluir configuración
require_once __DIR__ . '/../../config.php';

// Título personalizado
$pageTitle = 'Hidden Markov Models - Modelos Probabilísticos';
$moduleCSS = 'hmm';
$moduleJS = 'hmm';

require_once BASE_PATH . '/includes/header.php';
require_once BASE_PATH . '/includes/navbar.php';
?>

<!-- Header del módulo -->
<div class="hmm-header">
    <div class="container">
        <h1><i class="fas fa-eye-slash"></i> Modelos Ocultos de Markov (HMM)</h1>
        <p>Modelos estadísticos que asumen un sistema con estados ocultos que generan observaciones visibles, útiles en reconocimiento de patrones temporales</p>
    </div>
</div>

<!-- Contenedor principal -->
<div class="container">
    <div class="hmm-container">
        
        <!-- Panel lateral (Sidebar) -->
        <aside class="hmm-sidebar">
            
            <!-- Sección: Ejemplos -->
            <div class="sidebar-section">
                <h3><i class="fas fa-folder-open"></i> Ejemplos</h3>
                <div class="example-buttons">
                    <button class="btn-example" data-example="weather_prediction">
                        <i class="fas fa-cloud-sun"></i> Predicción Climática
                    </button>
                    <button class="btn-example" data-example="speech_recognition">
                        <i class="fas fa-microphone"></i> Reconocimiento de Voz
                    </button>
                    <button class="btn-example" data-example="bioinformatics">
                        <i class="fas fa-dna"></i> Bioinformática
                    </button>
                    <button class="btn-example" data-example="stock_trading">
                        <i class="fas fa-chart-candlestick"></i> Trading Bursátil
                    </button>
                </div>
            </div>
            
            <!-- Sección: Algoritmos -->
            <div class="sidebar-section">
                <h3><i class="fas fa-calculator"></i> Algoritmos</h3>
                <div class="algorithm-buttons">
                    <!-- Único botón disponible: Viterbi -->
                    <button class="btn btn-primary" onclick="runViterbi()" style="width: 100%; margin-bottom: 0.5rem;">
                        <i class="fas fa-route"></i> Viterbi (Decodificación)
                    </button>
                </div>
            </div>
            
            <!-- Sección: Acciones -->
            <div class="sidebar-section">
                <h3><i class="fas fa-tools"></i> Acciones</h3>
                <button class="btn btn-primary" onclick="createNewHMM()" style="width: 100%; margin-bottom: 0.5rem;">
                    <i class="fas fa-plus"></i> Nuevo HMM
                </button>
                <button class="btn btn-secondary" onclick="saveHMM()" style="width: 100%; margin-bottom: 0.5rem;">
                    <i class="fas fa-save"></i> Guardar
                </button>
                <button class="btn btn-secondary" onclick="exportHMM()" style="width: 100%;">
                    <i class="fas fa-download"></i> Exportar JSON
                </button>
            </div>
            
        </aside>
        
        <!-- Área principal -->
        <main class="hmm-main">
            
            <!-- Tabs -->
            <div class="hmm-tabs">
                <button class="tab-button active" data-tab="diagram">
                    <i class="fas fa-project-diagram"></i> Diagrama
                </button>
                <button class="tab-button" data-tab="matrices">
                    <i class="fas fa-table"></i> Matrices
                </button>
                <button class="tab-button" data-tab="observation">
                    <i class="fas fa-eye"></i> Observación
                </button>
                <button class="tab-button" data-tab="results">
                    <i class="fas fa-chart-bar"></i> Resultados
                </button>
            </div>
            
            <!-- Contenido de tabs -->
            <div class="tab-content">
                
                <!-- Tab 1: Diagrama de Estados -->
                <div class="tab-panel active" id="tab-diagram">
                    <h3>Diagrama de Estados Ocultos y Observaciones</h3>
                    
                    <!-- Controles -->
                    <div class="diagram-controls">
                        <button class="btn btn-secondary btn-sm" onclick="addHiddenState()">
                            <i class="fas fa-plus-circle"></i> Estado Oculto
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="addObservation()">
                            <i class="fas fa-eye"></i> Observación
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="toggleView()">
                            <i class="fas fa-layer-group"></i> Cambiar Vista
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="fitDiagram()">
                            <i class="fas fa-expand"></i> Ajustar
                        </button>
                    </div>
                    
                    <!-- Canvas de visualización -->
                    <div id="hmm-visualization" class="hmm-visualization"></div>
                    
                    <!-- Información -->
                    <div id="hmm-info" class="hmm-info-panel" style="display: none;">
                        <h4><i class="fas fa-info-circle"></i> Información del Modelo</h4>
                        <div id="hmm-details"></div>
                    </div>
                </div>
                
                <!-- Tab 2: Matrices -->
                <div class="tab-panel" id="tab-matrices">
                    <h3>Matrices del HMM</h3>
                    <p class="text-muted">Probabilidades de transición, emisión e iniciales</p>
                    
                    <div id="matrices-container">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Carga un ejemplo para ver las matrices del HMM
                        </div>
                    </div>
                </div>
                
                <!-- Tab 3: Secuencia de Observación -->
                <div class="tab-panel" id="tab-observation">
                    <h3>Secuencia de Observación</h3>
                    <p class="text-muted">Define la secuencia de observaciones para el análisis</p>
                    
                    <div class="observation-builder">
                        
                        <!-- Selector de observaciones -->
                        <div class="observation-section">
                            <h4><i class="fas fa-list"></i> Observaciones Disponibles</h4>
                            <div id="observation-selector" class="observation-selector">
                                <p class="text-muted">Carga un HMM primero</p>
                            </div>
                        </div>
                        
                        <!-- Secuencia construida -->
                        <div class="observation-section">
                            <h4><i class="fas fa-stream"></i> Secuencia Actual</h4>
                            <div id="observation-sequence" class="observation-sequence">
                                <p class="text-muted">Sin secuencia definida</p>
                            </div>
                            <button class="btn btn-secondary btn-sm" onclick="clearSequence()" style="margin-top: 10px;">
                                <i class="fas fa-trash"></i> Limpiar Secuencia
                            </button>
                        </div>
                        
                        <!-- Entrada manual -->
                        <div class="observation-section">
                            <h4><i class="fas fa-keyboard"></i> Entrada Manual</h4>
                            <input type="text" id="manual-sequence" class="form-control" placeholder="Ej: A B A C B A" style="margin-bottom: 10px;">
                            <button class="btn btn-primary" onclick="setManualSequence()">
                                <i class="fas fa-check"></i> Establecer Secuencia
                            </button>
                        </div>
                        
                    </div>
                </div>
                
                <!-- Tab 4: Resultados (Viterbi) -->
                <div class="tab-panel" id="tab-results">
                    <h3>Resultados del Análisis (Viterbi)</h3>
                    <p class="text-muted">Ruta más probable de estados ocultos</p>
                    
                    <div id="results-container">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Ejecuta el algoritmo Viterbi para ver los resultados aquí.
                        </div>
                    </div>
                </div>
                
            </div>
        </main>
        
    </div>
</div>

<?php require_once BASE_PATH . '/includes/footer.php'; ?>