<?php
/**
 * Módulo de Redes Bayesianas
 * UMSNH - Modelos Probabilísticos
 */

require_once __DIR__ . '/../../config.php';

$pageTitle = 'Redes Bayesianas - Modelos Probabilísticos';
$moduleCSS = 'bayesian';
$moduleJS = 'bayesian';

include BASE_PATH . '/includes/header.php';
include BASE_PATH . '/includes/navbar.php';
?>

<div class="bayesian-container">
    <!-- Header del módulo -->
    <div class="bayesian-header">
        <h1><i class="fas fa-project-diagram"></i> Redes Bayesianas</h1>
        <p>Representación gráfica de dependencias probabilísticas con algoritmos de inferencia exacta</p>
    </div>

    <!-- Sección de Ejemplos -->
    <div class="examples-section">
        <h3><i class="fas fa-folder-open"></i> Ejemplos Predefinidos</h3>
        <div class="examples-grid">
            <div class="example-card" onclick="loadExample('medical_diagnosis')">
                <span class="example-icon">🏥</span>
                <h4>Diagnóstico Médico</h4>
                <p>Red bayesiana para diagnóstico de enfermedades basado en síntomas</p>
            </div>
            <div class="example-card" onclick="loadExample('burglar_alarm')">
                <span class="example-icon">🚨</span>
                <h4>Alarma de Robo</h4>
                <p>Ejemplo clásico de inferencia probabilística con alarmas</p>
            </div>
            <div class="example-card" onclick="loadExample('student_performance')">
                <span class="example-icon">🎓</span>
                <h4>Rendimiento Estudiantil</h4>
                <p>Predicción de calificaciones basada en factores académicos</p>
            </div>
            <div class="example-card" onclick="loadExample('weather_forecast')">
                <span class="example-icon">🌤️</span>
                <h4>Pronóstico del Tiempo</h4>
                <p>Predicción meteorológica usando relaciones probabilísticas</p>
            </div>
        </div>
    </div>

    <!-- Sección de Controles -->
    <div class="controls-section">
        <h3><i class="fas fa-sliders-h"></i> Configuración de la Red</h3>
        
        <div class="control-group">
            <label for="network-input">Definición de la Red (JSON):</label>
            <textarea id="network-input" placeholder='{"nodes": [...], "edges": [...], "cpt": {...}}'></textarea>
            <button class="btn-bayesian" onclick="loadCustomNetwork()">
                <i class="fas fa-upload"></i> Cargar Red Personalizada
            </button>
        </div>

        <div class="controls-grid">
            <div class="control-group">
                <label for="algorithm-select">Algoritmo de Inferencia:</label>
                <select id="algorithm-select">
                    <option value="enumeration">Enumeración Exacta</option>
                    <option value="variable_elimination">Eliminación de Variables</option>
                </select>
            </div>

            <div class="control-group">
                <label for="query-variable">Variable de Consulta:</label>
                <select id="query-variable">
                    <option value="">Seleccione una variable</option>
                </select>
            </div>

            <div class="control-group">
                <label for="evidence-input">Evidencia (JSON):</label>
                <input type="text" id="evidence-input" placeholder='{"Variable": "valor"}'>
            </div>
        </div>

        <div class="action-buttons">
            <button class="btn-bayesian" onclick="runInference()">
                <i class="fas fa-play"></i> Ejecutar Inferencia
            </button>
            <button class="btn-secondary-bayesian" onclick="clearNetwork()">
                <i class="fas fa-eraser"></i> Limpiar
            </button>
        </div>
    </div>

    <!-- Sección de Visualización -->
    <div class="visualization-section">
        <h3><i class="fas fa-eye"></i> Visualización de la Red</h3>
        <div id="network-graph"></div>
    </div>

    <!-- Sección de Resultados -->
    <div class="results-section">
        <h3><i class="fas fa-chart-bar"></i> Resultados de Inferencia</h3>
        <div id="inference-results">
            <p class="text-muted">Ejecute una consulta para ver los resultados...</p>
        </div>
    </div>

    <!-- Información del Algoritmo -->
    <div class="info-section">
        <h3><i class="fas fa-info-circle"></i> Acerca de las Redes Bayesianas</h3>
        <p>
            Las Redes Bayesianas son modelos gráficos probabilísticos que representan 
            un conjunto de variables y sus dependencias condicionales mediante un grafo 
            acíclico dirigido (DAG).
        </p>
        
        <h4>Algoritmos Implementados:</h4>
        <ul>
            <li><strong>Enumeración Exacta:</strong> Calcula probabilidades exactas sumando sobre todas las asignaciones posibles</li>
            <li><strong>Eliminación de Variables:</strong> Optimiza la inferencia eliminando variables de forma estratégica</li>
        </ul>

        <h4>Casos de Uso:</h4>
        <ul>
            <li>Diagnóstico médico y sistemas expertos</li>
            <li>Detección de fraude y análisis de riesgos</li>
            <li>Sistemas de recomendación</li>
            <li>Procesamiento de lenguaje natural</li>
        </ul>
    </div>
</div>

<script>
// Variables globales
let currentNetwork = null;
let networkGraph = null;

// Cargar ejemplo predefinido
function loadExample(exampleName) {
    fetch(`examples/${exampleName}.json`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('network-input').value = JSON.stringify(data, null, 2);
            loadCustomNetwork();
        })
        .catch(error => {
            alert('Error al cargar el ejemplo: ' + error.message);
        });
}

// Cargar red personalizada
function loadCustomNetwork() {
    try {
        const input = document.getElementById('network-input').value;
        currentNetwork = JSON.parse(input);
        
        // Actualizar selector de variables
        updateQueryVariableSelect();
        
        // Visualizar red
        visualizeNetwork();
        
        showAlert('Red cargada correctamente', 'success');
    } catch (error) {
        showAlert('Error al cargar la red: ' + error.message, 'error');
    }
}

// Actualizar selector de variables de consulta
function updateQueryVariableSelect() {
    const select = document.getElementById('query-variable');
    select.innerHTML = '<option value="">Seleccione una variable</option>';
    
    if (currentNetwork && currentNetwork.nodes) {
        currentNetwork.nodes.forEach(node => {
            const option = document.createElement('option');
            option.value = node.id;
            option.textContent = node.label || node.id;
            select.appendChild(option);
        });
    }
}

// Visualizar red con Vis.js
function visualizeNetwork() {
    if (!currentNetwork) return;
    
    const container = document.getElementById('network-graph');
    
    const nodes = new vis.DataSet(
        currentNetwork.nodes.map(node => ({
            id: node.id,
            label: node.label || node.id,
            shape: 'box',
            color: {
                background: '#dbeafe',
                border: '#2563eb',
                highlight: {
                    background: '#2563eb',
                    border: '#1d4ed8'
                }
            },
            font: { color: '#1e40af', size: 16, face: 'Arial' }
        }))
    );
    
    const edges = new vis.DataSet(
        currentNetwork.edges.map(edge => ({
            from: edge.from,
            to: edge.to,
            arrows: 'to',
            color: { color: '#6b7280' },
            width: 2
        }))
    );
    
    const data = { nodes, edges };
    const options = {
        layout: {
            hierarchical: {
                direction: 'UD',
                sortMethod: 'directed',
                levelSeparation: 150,
                nodeSpacing: 200
            }
        },
        physics: false,
        interaction: {
            dragNodes: true,
            dragView: true,
            zoomView: true
        }
    };
    
    networkGraph = new vis.Network(container, data, options);
}

// Ejecutar inferencia
function runInference() {
    if (!currentNetwork) {
        showAlert('Primero cargue una red bayesiana', 'warning');
        return;
    }
    
    const algorithm = document.getElementById('algorithm-select').value;
    const queryVariable = document.getElementById('query-variable').value;
    const evidenceInput = document.getElementById('evidence-input').value;
    
    if (!queryVariable) {
        showAlert('Seleccione una variable de consulta', 'warning');
        return;
    }
    
    let evidence = {};
    if (evidenceInput.trim()) {
        try {
            evidence = JSON.parse(evidenceInput);
        } catch (error) {
            showAlert('Error en el formato de evidencia: ' + error.message, 'error');
            return;
        }
    }
    
    // Llamar al algoritmo correspondiente
    const url = `algorithms/${algorithm}.php`;
    const formData = new FormData();
    formData.append('network', JSON.stringify(currentNetwork));
    formData.append('query', queryVariable);
    formData.append('evidence', JSON.stringify(evidence));
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        displayResults(result);
    })
    .catch(error => {
        showAlert('Error en la inferencia: ' + error.message, 'error');
    });
}

// Mostrar resultados
function displayResults(result) {
    const container = document.getElementById('inference-results');
    
    if (result.success) {
        let html = '<div class="result-item">';
        html += `<h4>Probabilidades para ${result.query}</h4>`;
        
        for (const [value, prob] of Object.entries(result.probabilities)) {
            const percentage = (prob * 100).toFixed(2);
            html += `
                <div style="margin: 15px 0;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span><strong>${value}:</strong></span>
                        <span class="probability">${percentage}%</span>
                    </div>
                    <div class="probability-bar">
                        <div class="probability-fill" style="width: ${percentage}%"></div>
                    </div>
                </div>
            `;
        }
        
        html += '</div>';
        container.innerHTML = html;
        showAlert('Inferencia completada exitosamente', 'success');
    } else {
        showAlert('Error: ' + result.error, 'error');
        container.innerHTML = '<p class="text-muted">Error en la inferencia</p>';
    }
}

// Limpiar red
function clearNetwork() {
    currentNetwork = null;
    document.getElementById('network-input').value = '';
    document.getElementById('query-variable').innerHTML = '<option value="">Seleccione una variable</option>';
    document.getElementById('evidence-input').value = '';
    document.getElementById('inference-results').innerHTML = '<p class="text-muted">Ejecute una consulta para ver los resultados...</p>';
    document.getElementById('network-graph').innerHTML = '';
    showAlert('Red limpiada', 'info');
}

// Mostrar alerta
function showAlert(message, type) {
    // Implementar sistema de alertas
    console.log(`[${type.toUpperCase()}] ${message}`);
}

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('Módulo de Redes Bayesianas cargado');
});
</script>

<?php include BASE_PATH . '/includes/footer.php'; ?>
