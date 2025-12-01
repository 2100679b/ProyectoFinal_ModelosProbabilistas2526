/**
 * JavaScript para Módulo de HMM (Hidden Markov Models)
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

// ========== PREVENIR DOBLE CARGA ==========
if (typeof window.hmmModuleLoaded !== 'undefined') {
    console.warn('⚠️ hmm.js ya está cargado, evitando duplicación');
} else {
    window.hmmModuleLoaded = true;

// ========== VARIABLES GLOBALES ==========
let currentHMM = null;
let hmmDiagram = null;
let observationSequence = [];
let currentView = 'combined'; // 'combined', 'hidden', 'observation'

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ Módulo de HMM iniciado');
    
    // Verificar si vis está disponible
    if (typeof vis === 'undefined') {
        console.error('❌ ERROR CRÍTICO: vis.js no está cargado');
        const container = document.getElementById('hmm-visualization');
        if (container) {
            container.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626; background: #fee2e2; border-radius: 8px; margin: 20px;"><i class="fas fa-exclamation-triangle" style="font-size: 3em; margin-bottom: 15px;"></i><h4>Error de Configuración</h4><p>La biblioteca vis.js no está disponible. Verifica includes/footer.php</p></div>';
        }
        return;
    }
    
    console.log('✅ vis.js disponible');
    initializeModule();
});

function initializeModule() {
    initTabs();
    initHMMDiagram();
    initExampleButtons();
}

// ========== TABS ==========
function initTabs() {
    document.querySelectorAll('.hmm-tabs .tab-button').forEach(btn => {
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
    document.querySelectorAll('.hmm-tabs .tab-button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => {
        p.classList.remove('active');
        p.style.display = 'none';
    });
    
    // Activar seleccionado
    const btn = document.querySelector(`.hmm-tabs .tab-button[data-tab="${tabId}"]`);
    const panel = document.getElementById(`tab-${tabId}`);
    
    if (btn) btn.classList.add('active');
    if (panel) {
        panel.classList.add('active');
        panel.style.display = 'block';
        
        // Cargar contenido específico del tab
        if (tabId === 'matrices' && currentHMM) {
            console.log('📊 Cargando matrices...');
            displayMatrices();
        }
    }
}

// ========== DIAGRAMA VIS.JS ==========
function initHMMDiagram() {
    const container = document.getElementById('hmm-visualization');
    if (!container) {
        console.error('❌ No se encontró #hmm-visualization');
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
            shape: 'box',
            margin: 10,
            font: { 
                size: 14, 
                color: '#581c87'
            },
            borderWidth: 2
        },
        edges: {
            arrows: { to: { enabled: true, scaleFactor: 1 } },
            color: { color: '#6b7280', highlight: '#9333ea' },
            width: 2,
            smooth: { type: 'cubicBezier' },
            font: { 
                size: 11, 
                align: 'middle', 
                color: '#9333ea'
            }
        },
        layout: {
            hierarchical: {
                enabled: true,
                direction: 'LR',
                sortMethod: 'directed',
                levelSeparation: 200,
                nodeSpacing: 150
            }
        },
        physics: {
            enabled: false
        },
        interaction: {
            hover: true,
            tooltipDelay: 200,
            dragNodes: true,
            dragView: true,
            zoomView: true
        }
    };
    
    hmmDiagram = new vis.Network(container, data, options);
    
    hmmDiagram.on('click', (params) => {
        if (params.nodes.length > 0) {
            showNodeDetails(params.nodes[0]);
        }
    });
    
    console.log('✅ Diagrama HMM inicializado');
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
    const url = `${baseUrl}/modules/hmm/examples/${name}.json`;
    
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
            currentHMM = data;
            renderHMM(data);
            updateObservationSelector(data);
            showAlert('Ejemplo cargado: ' + (data.name || name), 'success');
            activateTab('diagram');
        })
        .catch(error => {
            console.error('❌ Error al cargar ejemplo:', error);
            showAlert('Error: ' + error.message, 'error');
        });
}

// ========== RENDER HMM ==========
function renderHMM(hmm) {
    if (!hmmDiagram) {
        console.error('❌ hmmDiagram no inicializado');
        return;
    }
    
    const nodes = new vis.DataSet();
    const edges = new vis.DataSet();
    
    // Crear nodos de estados ocultos
    hmm.hiddenStates.forEach((state, index) => {
        nodes.add({
            id: `hidden_${state.id}`,
            label: `🔒 ${state.label || state.id}`,
            title: `Estado Oculto: ${state.description || state.label}`,
            level: 0,
            color: {
                background: '#f3e8ff',
                border: '#9333ea',
                highlight: { background: '#9333ea', border: '#7e22ce' }
            },
            font: { color: '#581c87', bold: true }
        });
    });
    
    // Crear nodos de observaciones
    hmm.observations.forEach((obs, index) => {
        nodes.add({
            id: `obs_${obs.id}`,
            label: `👁️ ${obs.label || obs.id}`,
            title: `Observación: ${obs.description || obs.label}`,
            level: 1,
            color: {
                background: '#ddd6fe',
                border: '#7c3aed',
                highlight: { background: '#7c3aed', border: '#6d28d9' }
            },
            font: { color: '#581c87' }
        });
    });
    
    // Crear aristas de transición entre estados ocultos
    if (hmm.transitionMatrix) {
        Object.entries(hmm.transitionMatrix).forEach(([fromState, transitions]) => {
            Object.entries(transitions).forEach(([toState, probability]) => {
                if (probability > 0) {
                    edges.add({
                        from: `hidden_${fromState}`,
                        to: `hidden_${toState}`,
                        label: probability.toFixed(2),
                        title: `P(${toState}|${fromState}) = ${probability}`,
                        color: { color: '#9333ea' },
                        width: 2
                    });
                }
            });
        });
    }
    
    // Crear aristas de emisión (estados ocultos → observaciones)
    if (hmm.emissionMatrix) {
        Object.entries(hmm.emissionMatrix).forEach(([state, emissions]) => {
            Object.entries(emissions).forEach(([observation, probability]) => {
                if (probability > 0) {
                    edges.add({
                        from: `hidden_${state}`,
                        to: `obs_${observation}`,
                        label: probability.toFixed(2),
                        title: `P(${observation}|${state}) = ${probability}`,
                        color: { color: '#c026d3' },
                        width: 1,
                        dashes: true
                    });
                }
            });
        });
    }
    
    hmmDiagram.setData({ nodes, edges });
    
    setTimeout(() => {
        hmmDiagram.fit({
            animation: {
                duration: 1000,
                easingFunction: 'easeInOutQuad'
            }
        });
    }, 100);
    
    updateHMMInfo(hmm);
    console.log('✅ HMM renderizado:', hmm.name);
}

// ========== MATRICES ==========
function displayMatrices() {
    const container = document.getElementById('matrices-container');
    
    if (!container || !currentHMM) {
        console.error('❌ No se pueden mostrar matrices');
        return;
    }
    
    let html = '';
    
    // Matriz de transición
    if (currentHMM.transitionMatrix) {
        html += generateMatrixHTML(
            'Matriz de Transición (A)',
            'Probabilidades de transición entre estados ocultos',
            currentHMM.transitionMatrix,
            currentHMM.hiddenStates.map(s => s.id),
            currentHMM.hiddenStates.map(s => s.id)
        );
    }
    
    // Matriz de emisión
    if (currentHMM.emissionMatrix) {
        html += generateMatrixHTML(
            'Matriz de Emisión (B)',
            'Probabilidades de emisión de observaciones desde estados ocultos',
            currentHMM.emissionMatrix,
            currentHMM.hiddenStates.map(s => s.id),
            currentHMM.observations.map(o => o.id)
        );
    }
    
    // Vector de probabilidades iniciales
    if (currentHMM.initialProbabilities) {
        html += generateInitialProbHTML(
            'Probabilidades Iniciales (π)',
            'Probabilidad de comenzar en cada estado oculto',
            currentHMM.initialProbabilities
        );
    }
    
    container.innerHTML = html;
    console.log('✅ Matrices mostradas');
}

function generateMatrixHTML(title, description, matrix, rowLabels, colLabels) {
    let html = '<div class="matrix-card" style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">';
    html += `<h4 style="color: #7e22ce; margin-bottom: 10px; display: flex; align-items: center; gap: 10px;"><i class="fas fa-table"></i> ${title}</h4>`;
    html += `<p style="color: #6b7280; margin-bottom: 15px; font-size: 0.95em;">${description}</p>`;
    html += '<div style="overflow-x: auto;">';
    html += '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
    
    // Encabezado
    html += '<thead style="background: #9333ea; color: white;">';
    html += '<tr><th style="padding: 12px; text-align: center; border: 1px solid #ddd;"></th>';
    colLabels.forEach(col => {
        html += `<th style="padding: 12px; text-align: center; border: 1px solid #ddd;">${col}</th>`;
    });
    html += '</tr></thead>';
    
    // Cuerpo
    html += '<tbody>';
    rowLabels.forEach(row => {
        html += '<tr style="background: #f9fafb;">';
        html += `<td style="padding: 12px; border: 1px solid #ddd; font-weight: 600; background: #f3e8ff;">${row}</td>`;
        
        colLabels.forEach(col => {
            const prob = matrix[row]?.[col] || 0;
            const percentage = (prob * 100).toFixed(1);
            const color = prob > 0 ? '#9333ea' : '#9ca3af';
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
    html += '</table></div></div>';
    
    return html;
}

function generateInitialProbHTML(title, description, probabilities) {
    let html = '<div class="matrix-card" style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">';
    html += `<h4 style="color: #7e22ce; margin-bottom: 10px; display: flex; align-items: center; gap: 10px;"><i class="fas fa-play"></i> ${title}</h4>`;
    html += `<p style="color: #6b7280; margin-bottom: 15px; font-size: 0.95em;">${description}</p>`;
    html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">';
    
    Object.entries(probabilities).forEach(([state, prob]) => {
        const percentage = (prob * 100).toFixed(1);
        html += `
            <div style="background: #f3e8ff; padding: 15px; border-radius: 8px; text-align: center; border: 2px solid #9333ea;">
                <div style="font-weight: 600; color: #581c87; margin-bottom: 5px;">${state}</div>
                <div style="font-size: 1.5em; font-weight: bold; color: #9333ea;">${prob.toFixed(3)}</div>
                <div style="color: #6b7280; font-size: 0.9em;">(${percentage}%)</div>
            </div>
        `;
    });
    
    html += '</div></div>';
    return html;
}

// ========== INFORMACIÓN ==========
function updateHMMInfo(hmm) {
    const info = document.getElementById('hmm-info');
    const details = document.getElementById('hmm-details');
    
    if (!info || !details) return;
    
    info.style.display = 'block';
    details.innerHTML = `
        <div class="hmm-metadata">
            <h5>${hmm.name || 'Hidden Markov Model'}</h5>
            <p>${hmm.description || ''}</p>
            <div class="hmm-stats" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 15px;">
                <div class="stat-item" style="background: #f3f4f6; padding: 10px; border-radius: 6px; text-align: center;">
                    <div style="font-size: 0.9em; color: #6b7280;">Estados Ocultos</div>
                    <div style="font-size: 1.5em; font-weight: bold; color: #9333ea;">${hmm.hiddenStates.length}</div>
                </div>
                <div class="stat-item" style="background: #f3f4f6; padding: 10px; border-radius: 6px; text-align: center;">
                    <div style="font-size: 0.9em; color: #6b7280;">Observaciones</div>
                    <div style="font-size: 1.5em; font-weight: bold; color: #9333ea;">${hmm.observations.length}</div>
                </div>
                <div class="stat-item" style="background: #f3f4f6; padding: 10px; border-radius: 6px; text-align: center;">
                    <div style="font-size: 0.9em; color: #6b7280;">Dominio</div>
                    <div style="font-size: 1em; font-weight: bold; color: #9333ea;">${hmm.domain || 'General'}</div>
                </div>
            </div>
        </div>
    `;
}

function showNodeDetails(nodeId) {
    if (!currentHMM) return;
    
    const info = document.getElementById('hmm-info');
    const details = document.getElementById('hmm-details');
    
    if (info && details) {
        info.style.display = 'block';
        
        let html = '';
        
        if (nodeId.startsWith('hidden_')) {
            const stateId = nodeId.replace('hidden_', '');
            const state = currentHMM.hiddenStates.find(s => s.id === stateId);
            if (state) {
                html = `<h5 style="color: #7e22ce;">🔒 Estado Oculto: ${state.label || state.id}</h5>`;
                html += `<p style="color: #6b7280;">${state.description || 'Sin descripción'}</p>`;
            }
        } else if (nodeId.startsWith('obs_')) {
            const obsId = nodeId.replace('obs_', '');
            const obs = currentHMM.observations.find(o => o.id === obsId);
            if (obs) {
                html = `<h5 style="color: #7e22ce;">👁️ Observación: ${obs.label || obs.id}</h5>`;
                html += `<p style="color: #6b7280;">${obs.description || 'Sin descripción'}</p>`;
            }
        }
        
        details.innerHTML = html;
    }
}

function updateObservationSelector(hmm) {
    const container = document.getElementById('observation-selector');
    if (!container) return;
    
    container.innerHTML = '';
    hmm.observations.forEach(obs => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-primary btn-sm';
        btn.style.margin = '5px';
        btn.textContent = obs.label || obs.id;
        btn.onclick = () => addToSequence(obs.id, obs.label || obs.id);
        container.appendChild(btn);
    });
}

function addToSequence(obsId, obsLabel) {
    observationSequence.push({ id: obsId, label: obsLabel });
    updateSequenceDisplay();
}

function updateSequenceDisplay() {
    const container = document.getElementById('observation-sequence');
    if (!container) return;
    
    if (observationSequence.length === 0) {
        container.innerHTML = '<p class="text-muted">Sin secuencia definida</p>';
        return;
    }
    
    container.innerHTML = observationSequence.map((obs, index) => 
        `<span style="background: #9333ea; color: white; padding: 8px 12px; border-radius: 6px; margin: 5px; display: inline-block;">${index + 1}. ${obs.label}</span>`
    ).join('');
}

function clearSequence() {
    observationSequence = [];
    updateSequenceDisplay();
    showAlert('Secuencia limpiada', 'info');
}

function setManualSequence() {
    const input = document.getElementById('manual-sequence');
    if (!input || !input.value.trim()) {
        showAlert('Ingresa una secuencia válida', 'warning');
        return;
    }
    
    const sequence = input.value.trim().split(/\s+/);
    observationSequence = sequence.map(id => ({ id, label: id }));
    updateSequenceDisplay();
    showAlert('Secuencia establecida', 'success');
}

// ========== ALGORITMOS ==========
function runForward() {
    if (!currentHMM) {
        showAlert('No hay HMM cargado', 'warning');
        return;
    }
    if (observationSequence.length === 0) {
        showAlert('Define una secuencia de observación primero', 'warning');
        return;
    }
    showAlert('Algoritmo Forward en desarrollo', 'info');
    activateTab('results');
}

function runViterbi() {
    if (!currentHMM) {
        showAlert('No hay HMM cargado', 'warning');
        return;
    }
    if (observationSequence.length === 0) {
        showAlert('Define una secuencia de observación primero', 'warning');
        return;
    }
    showAlert('Algoritmo Viterbi en desarrollo', 'info');
    activateTab('results');
}

function runBaumWelch() {
    if (!currentHMM) {
        showAlert('No hay HMM cargado', 'warning');
        return;
    }
    showAlert('Algoritmo Baum-Welch en desarrollo', 'info');
    activateTab('results');
}

// ========== ALERTAS ==========
function showAlert(msg, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${msg}`);
    
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#9333ea'
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
function createNewHMM() {
    if (currentHMM && !confirm('¿Descartar HMM actual?')) return;
    currentHMM = { name: 'Nuevo HMM', hiddenStates: [], observations: [], transitionMatrix: {}, emissionMatrix: {}, initialProbabilities: {} };
    renderHMM(currentHMM);
    showAlert('Nuevo HMM creado', 'success');
    activateTab('diagram');
}

function exportHMM() {
    if (!currentHMM) {
        showAlert('No hay HMM para exportar', 'warning');
        return;
    }
    const dataStr = JSON.stringify(currentHMM, null, 2);
    const blob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = (currentHMM.name || 'hmm').replace(/\s+/g, '_') + '.json';
    a.click();
    URL.revokeObjectURL(url);
    showAlert('HMM exportado', 'success');
}

function saveHMM() {
    exportHMM();
}

function addHiddenState() {
    showAlert('Función en desarrollo', 'info');
}

function addObservation() {
    showAlert('Función en desarrollo', 'info');
}

function toggleView() {
    const views = ['combined', 'hidden', 'observation'];
    const currentIndex = views.indexOf(currentView);
    currentView = views[(currentIndex + 1) % views.length];
    
    const viewNames = { combined: 'Vista Combinada', hidden: 'Solo Estados Ocultos', observation: 'Solo Observaciones' };
    showAlert(`Cambiado a: ${viewNames[currentView]}`, 'info');
    
    if (currentHMM) renderHMM(currentHMM);
}

function fitDiagram() {
    if (hmmDiagram) {
        hmmDiagram.fit({
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
