/**
 * JavaScript para Módulo de Cadenas de Markov
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

// ========== PREVENIR DOBLE CARGA ==========
if (typeof window.markovModuleLoaded !== 'undefined') {
    console.warn('⚠️ markov.js ya está cargado, evitando duplicación');
} else {
    window.markovModuleLoaded = true;

// ========== VARIABLES GLOBALES ==========
let currentChain = null;
let markovDiagram = null;
let selectedInitialState = null;

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ Módulo de Cadenas de Markov iniciado');
    
    // Verificar si vis está disponible
    if (typeof vis === 'undefined') {
        console.error('❌ ERROR: vis.js no está cargado');
        console.log('🔧 Intentando cargar vis.js dinámicamente...');
        
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/vis-network@latest/dist/vis-network.min.js';
        script.onload = () => {
            console.log('✅ vis.js cargado correctamente');
            initializeModule();
        };
        script.onerror = () => {
            console.error('❌ No se pudo cargar vis.js');
            showAlert('Error: No se pudo cargar la biblioteca de visualización.', 'error');
        };
        document.head.appendChild(script);
    } else {
        console.log('✅ vis.js ya está disponible');
        initializeModule();
    }
});

function initializeModule() {
    initTabs();
    initMarkovDiagram();
    initExampleButtons();
}

// ========== TABS ==========
function initTabs() {
    document.querySelectorAll('.markov-tabs .tab-button').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const tab = btn.dataset.tab;
            if (tab) activateTab(tab);
        });
    });
}

function activateTab(tabId) {
    console.log('📑 Activando tab:', tabId);
    
    // Desactivar todos
    document.querySelectorAll('.markov-tabs .tab-button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => {
        p.classList.remove('active');
        p.style.display = 'none';
    });
    
    // Activar seleccionado
    const btn = document.querySelector(`.markov-tabs .tab-button[data-tab="${tabId}"]`);
    const panel = document.getElementById(`tab-${tabId}`);
    
    if (btn) btn.classList.add('active');
    if (panel) {
        panel.classList.add('active');
        panel.style.display = 'block';
        
        // IMPORTANTE: Solo cargar matriz cuando se activa el tab
        if (tabId === 'matrix' && currentChain) {
            console.log('📊 Cargando matriz de transición...');
            displayTransitionMatrix();
        }
    }
}

// ========== DIAGRAMA VIS.JS ==========
function initMarkovDiagram() {
    const container = document.getElementById('markov-visualization');
    if (!container) {
        console.error('❌ No se encontró #markov-visualization');
        return;
    }
    
    if (typeof vis === 'undefined') {
        console.error('❌ vis.js todavía no está disponible');
        container.innerHTML = '<div style="padding: 20px; text-align: center; color: #dc2626;"><i class="fas fa-exclamation-triangle"></i> Error: No se pudo cargar la biblioteca de visualización</div>';
        return;
    }
    
    const data = {
        nodes: new vis.DataSet([]),
        edges: new vis.DataSet([])
    };
    
    const options = {
        nodes: {
            shape: 'circle',
            size: 30,
            font: { 
                size: 16, 
                color: '#065f46'
            },
            borderWidth: 3,
            color: {
                background: '#d1fae5',
                border: '#059669',
                highlight: { background: '#059669', border: '#047857' }
            }
        },
        edges: {
            arrows: { to: { enabled: true, scaleFactor: 1.2 } },
            color: { color: '#6b7280', highlight: '#059669' },
            width: 2,
            smooth: { type: 'curvedCW', roundness: 0.2 },
            font: { 
                size: 12, 
                align: 'middle', 
                color: '#059669'
            }
        },
        physics: {
            enabled: true,
            stabilization: { iterations: 200 },
            barnesHut: {
                gravitationalConstant: -3000,
                springConstant: 0.04,
                springLength: 150
            }
        },
        interaction: {
            hover: true,
            tooltipDelay: 200,
            dragNodes: true,
            dragView: true,
            zoomView: true
        }
    };
    
    markovDiagram = new vis.Network(container, data, options);
    
    markovDiagram.on('click', (params) => {
        if (params.nodes.length > 0) {
            showStateDetails(params.nodes[0]);
        }
    });
    
    console.log('✅ Diagrama inicializado');
}

// ========== EJEMPLOS ==========
function initExampleButtons() {
    document.querySelectorAll('.btn-example').forEach(btn => {
        btn.addEventListener('click', () => {
            const file = btn.dataset.example;
            if (file) {
                console.log('📂 Cargando ejemplo:', file);
                loadExample(file);
            }
        });
    });
}

function loadExample(name) {
    const baseUrl = document.body.dataset.baseUrl || window.location.origin;
    const url = `${baseUrl}/modules/markov/examples/${name}.json`;
    
    console.log('🔗 URL completa:', url);
    
    fetch(url)
        .then(response => {
            console.log('📡 Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: No se pudo cargar ${name}.json`);
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Ejemplo cargado:', data);
            currentChain = data;
            renderChain(data);
            updateStateSelectors(data);
            showAlert('Ejemplo cargado: ' + (data.name || name), 'success');
            activateTab('diagram');
        })
        .catch(error => {
            console.error('❌ Error al cargar ejemplo:', error);
            showAlert('Error: ' + error.message, 'error');
        });
}

// ========== RENDER ==========
function renderChain(chain) {
    if (!markovDiagram) {
        console.error('❌ markovDiagram no inicializado');
        return;
    }
    
    const nodes = new vis.DataSet(
        chain.states.map(state => ({
            id: state.id,
            label: state.label || state.id,
            title: state.description || state.label || state.id
        }))
    );
    
    const edges = new vis.DataSet();
    
    // Crear aristas desde la matriz de transición
    if (chain.transitionMatrix) {
        Object.entries(chain.transitionMatrix).forEach(([fromState, transitions]) => {
            Object.entries(transitions).forEach(([toState, probability]) => {
                if (probability > 0) {
                    edges.add({
                        from: fromState,
                        to: toState,
                        label: probability.toFixed(2),
                        title: `P(${fromState} → ${toState}) = ${probability}`
                    });
                }
            });
        });
    }
    
    markovDiagram.setData({ nodes, edges });
    
    setTimeout(() => {
        markovDiagram.fit({
            animation: {
                duration: 1000,
                easingFunction: 'easeInOutQuad'
            }
        });
    }, 100);
    
    updateChainInfo(chain);
    console.log('✅ Cadena renderizada:', chain.name);
}

// ========== MATRIZ DE TRANSICIÓN ==========
function displayTransitionMatrix() {
    const container = document.getElementById('matrix-container');
    
    if (!container || !currentChain) {
        console.error('❌ No se puede mostrar matriz');
        return;
    }
    
    if (!currentChain.transitionMatrix || Object.keys(currentChain.transitionMatrix).length === 0) {
        container.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                Esta cadena no tiene matriz de transición definida.
            </div>
        `;
        return;
    }
    
    const states = currentChain.states.map(s => s.id);
    
    let html = '<div class="matrix-card" style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">';
    html += '<h4 style="color: #047857; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;"><i class="fas fa-table"></i> Matriz de Transición P</h4>';
    html += '<div style="overflow-x: auto;">';
    html += '<table class="transition-matrix" style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
    
    // Encabezado
    html += '<thead style="background: #059669; color: white;">';
    html += '<tr><th style="padding: 12px; text-align: center; border: 1px solid #ddd;">Estado</th>';
    states.forEach(state => {
        html += `<th style="padding: 12px; text-align: center; border: 1px solid #ddd;">${state}</th>`;
    });
    html += '</tr></thead>';
    
    // Cuerpo
    html += '<tbody>';
    states.forEach(fromState => {
        html += '<tr style="background: #f9fafb;">';
        html += `<td style="padding: 12px; border: 1px solid #ddd; font-weight: 600; background: #d1fae5;">${fromState}</td>`;
        
        states.forEach(toState => {
            const prob = currentChain.transitionMatrix[fromState]?.[toState] || 0;
            const percentage = (prob * 100).toFixed(1);
            const color = prob > 0 ? '#059669' : '#9ca3af';
            html += `
                <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                    <span style="color: ${color}; font-weight: bold; font-size: 1.1em;">${prob.toFixed(3)}</span>
                    <br>
                    <span style="color: #6b7280; font-size: 0.85em;">(${percentage}%)</span>
                </td>
            `;
        });
        
        html += '</tr>';
    });
    html += '</tbody>';
    html += '</table></div>';
    
    // Verificar si es estocástica
    html += '<div style="margin-top: 20px; padding: 15px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #059669;">';
    html += '<h5 style="color: #047857; margin-bottom: 10px;"><i class="fas fa-check-circle"></i> Propiedades de la Matriz</h5>';
    
    const isStochastic = verifyStochasticMatrix(currentChain.transitionMatrix, states);
    if (isStochastic) {
        html += '<p style="color: #065f46; margin: 0;"><i class="fas fa-check"></i> ✓ Es una matriz estocástica (las filas suman 1)</p>';
    } else {
        html += '<p style="color: #dc2626; margin: 0;"><i class="fas fa-times"></i> ✗ No es una matriz estocástica válida</p>';
    }
    
    html += '</div>';
    html += '</div>';
    
    container.innerHTML = html;
    console.log('✅ Matriz de transición mostrada');
}

function verifyStochasticMatrix(matrix, states) {
    for (const fromState of states) {
        const rowSum = states.reduce((sum, toState) => {
            return sum + (matrix[fromState]?.[toState] || 0);
        }, 0);
        
        if (Math.abs(rowSum - 1.0) > 0.001) {
            return false;
        }
    }
    return true;
}

// ========== INFORMACIÓN ==========
function updateChainInfo(chain) {
    const info = document.getElementById('state-info');
    const details = document.getElementById('state-details');
    
    if (!info || !details) return;
    
    info.style.display = 'block';
    details.innerHTML = `
        <div class="chain-metadata">
            <h5>${chain.name || 'Cadena de Markov'}</h5>
            <p>${chain.description || ''}</p>
            <div class="chain-stats" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 15px;">
                <div class="stat-item" style="background: #f3f4f6; padding: 10px; border-radius: 6px; text-align: center;">
                    <div style="font-size: 0.9em; color: #6b7280;">Estados</div>
                    <div style="font-size: 1.5em; font-weight: bold; color: #059669;">${chain.states.length}</div>
                </div>
                <div class="stat-item" style="background: #f3f4f6; padding: 10px; border-radius: 6px; text-align: center;">
                    <div style="font-size: 0.9em; color: #6b7280;">Transiciones</div>
                    <div style="font-size: 1.5em; font-weight: bold; color: #059669;">${countTransitions(chain)}</div>
                </div>
                <div class="stat-item" style="background: #f3f4f6; padding: 10px; border-radius: 6px; text-align: center;">
                    <div style="font-size: 0.9em; color: #6b7280;">Dominio</div>
                    <div style="font-size: 1em; font-weight: bold; color: #059669;">${chain.domain || 'General'}</div>
                </div>
            </div>
        </div>
    `;
}

function countTransitions(chain) {
    let count = 0;
    if (chain.transitionMatrix) {
        Object.values(chain.transitionMatrix).forEach(transitions => {
            count += Object.values(transitions).filter(p => p > 0).length;
        });
    }
    return count;
}

function updateStateSelectors(chain) {
    const container = document.getElementById('initial-state-selector');
    if (!container) return;
    
    container.innerHTML = '';
    chain.states.forEach(state => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-primary btn-sm';
        btn.style.margin = '5px';
        btn.textContent = state.label || state.id;
        btn.onclick = () => selectInitialState(state.id, state.label || state.id);
        container.appendChild(btn);
    });
}

function selectInitialState(stateId, stateLabel) {
    selectedInitialState = stateId;
    
    document.querySelectorAll('#initial-state-selector .btn').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-primary');
    });
    
    event.target.classList.remove('btn-outline-primary');
    event.target.classList.add('btn-primary');
    
    showAlert('Estado inicial: ' + stateLabel, 'info');
}

function showStateDetails(stateId) {
    if (!currentChain) return;
    
    const state = currentChain.states.find(s => s.id === stateId);
    if (!state) return;
    
    const info = document.getElementById('state-info');
    const details = document.getElementById('state-details');
    
    if (info && details) {
        info.style.display = 'block';
        
        let html = `
            <h5 style="color: #047857; margin-bottom: 10px;">${state.label || state.id}</h5>
            <p style="color: #6b7280; margin-bottom: 15px;">${state.description || 'Sin descripción'}</p>
        `;
        
        // Mostrar transiciones salientes
        if (currentChain.transitionMatrix && currentChain.transitionMatrix[stateId]) {
            html += '<h6 style="color: #065f46; margin-top: 15px; margin-bottom: 10px;">Transiciones desde este estado:</h6>';
            html += '<div style="background: #f0fdf4; padding: 12px; border-radius: 6px;">';
            
            Object.entries(currentChain.transitionMatrix[stateId]).forEach(([toState, prob]) => {
                if (prob > 0) {
                    const percentage = (prob * 100).toFixed(1);
                    html += `
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="color: #065f46;">→ ${toState}</span>
                            <span style="color: #059669; font-weight: bold;">${prob.toFixed(3)} (${percentage}%)</span>
                        </div>
                    `;
                }
            });
            
            html += '</div>';
        }
        
        details.innerHTML = html;
    }
}

// ========== ANÁLISIS ==========
function calculateSteadyState() {
    if (!currentChain) {
        showAlert('No hay cadena cargada', 'warning');
        return;
    }
    showAlert('Función de estado estacionario en desarrollo', 'info');
    activateTab('analysis');
}

function showTransitionMatrix() {
    if (!currentChain) {
        showAlert('No hay cadena cargada', 'warning');
        return;
    }
    activateTab('matrix');
}

function simulateSteps() {
    if (!currentChain) {
        showAlert('No hay cadena cargada', 'warning');
        return;
    }
    activateTab('simulation');
}

function runSimulation() {
    if (!currentChain) {
        showAlert('No hay cadena cargada', 'warning');
        return;
    }
    
    if (!selectedInitialState) {
        showAlert('Selecciona un estado inicial primero', 'warning');
        return;
    }
    
    showAlert('Función de simulación en desarrollo', 'info');
}

// ========== ALERTAS ==========
function showAlert(msg, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${msg}`);
    
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#059669'
    };
    
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    
    const div = document.createElement('div');
    div.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideInRight 0.3s ease;
    `;
    div.style.backgroundColor = colors[type] || colors.info;
    div.innerHTML = `<i class="fas fa-${icons[type]}"></i><span>${msg}</span>`;
    
    document.body.appendChild(div);
    
    setTimeout(() => {
        div.style.opacity = '0';
        div.style.transform = 'translateX(400px)';
        setTimeout(() => div.remove(), 300);
    }, 4000);
}

// ========== ACCIONES ==========
function createNewChain() {
    if (currentChain && !confirm('¿Descartar cadena actual?')) return;
    currentChain = { name: 'Nueva Cadena', states: [], transitionMatrix: {} };
    renderChain(currentChain);
    showAlert('Nueva cadena creada', 'success');
    activateTab('diagram');
}

function exportChain() {
    if (!currentChain) {
        showAlert('No hay cadena para exportar', 'warning');
        return;
    }
    const dataStr = JSON.stringify(currentChain, null, 2);
    const blob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = (currentChain.name || 'cadena_markov').replace(/\s+/g, '_') + '.json';
    a.click();
    URL.revokeObjectURL(url);
    showAlert('Cadena exportada', 'success');
}

function saveChain() {
    exportChain();
}

function addState() {
    showAlert('Función en desarrollo', 'info');
}

function addTransition() {
    showAlert('Función en desarrollo', 'info');
}

function removeSelected() {
    showAlert('Función en desarrollo', 'info');
}

function fitDiagram() {
    if (markovDiagram) {
        markovDiagram.fit({
            animation: {
                duration: 500,
                easingFunction: 'easeInOutQuad'
            }
        });
    }
}

// Animación CSS
const style = document.createElement('style');
style.textContent = `@keyframes slideInRight { from { opacity: 0; transform: translateX(400px); } to { opacity: 1; transform: translateX(0); } }`;
document.head.appendChild(style);

// FIN DEL BLOQUE DE PREVENCIÓN
}
