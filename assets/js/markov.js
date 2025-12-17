/**
 * JavaScript para Módulo de Cadenas de Markov
 * Universidad Michoacana de San Nicolás de Hidalgo
 * VERSIÓN FINAL: Cálculo Local + Edición Completa
 */

// ========== PREVENIR DOBLE CARGA ==========
if (typeof window.markovModuleLoaded !== 'undefined') {
    console.warn('⚠️ markov.js ya está cargado, evitando duplicación');
} else {
    window.markovModuleLoaded = true;

    // ========== VARIABLES GLOBALES ==========
    let currentChain = {
        name: 'Nueva Cadena',
        description: 'Cadena vacía',
        domain: 'General',
        states: [],          // Array: {id, label}
        transitionMatrix: {} // Objeto: { from_id: { to_id: probability } }
    };
    let markovDiagram = null;
    let selectedInitialState = null;
    let simulationHistory = [];

    // ========== INICIALIZACIÓN ==========
    document.addEventListener('DOMContentLoaded', () => {
        console.log('✅ Módulo de Cadenas de Markov iniciado');
        
        // Verificar dependencia crítica: Vis.js
        if (typeof vis === 'undefined') {
            console.error('❌ ERROR: vis.js no está cargado');
            const container = document.getElementById('markov-visualization');
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
        initMarkovDiagram();
        initExampleButtons();
        ensureAnalysisTab(); // Asegurar UI
        
        // Si no hay cadena cargada, iniciar una nueva
        if (!currentChain.states || currentChain.states.length === 0) {
            createNewChain(false);
        }
    }

    // ========== GESTIÓN DE TABS ==========
    function initTabs() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.tab-button');
            if (btn && document.querySelector('.markov-tabs').contains(btn)) {
                e.preventDefault();
                const tabId = btn.dataset.tab;
                if (tabId) activateTab(tabId);
            }
        });
    }

    function activateTab(tabId) {
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
            
            // Acciones específicas
            if (tabId === 'matrix') displayTransitionMatrix();
            if (tabId === 'simulation') updateSimulationUI();
            if (tabId === 'analysis') calculateSteadyState(); // Auto-calcular al entrar
            
            if (tabId === 'diagram' && markovDiagram) {
                setTimeout(() => markovDiagram.fit({ animation: true }), 100);
            }
        }
    }

    function ensureAnalysisTab() {
        // Asegurar que el contenedor de resultados de análisis existe
        let container = document.getElementById('analysis-results');
        if (!container) {
            const tabAnalysis = document.getElementById('tab-analysis');
            if (tabAnalysis) {
                container = document.createElement('div');
                container.id = 'analysis-results';
                tabAnalysis.appendChild(container);
            }
        }
    }

    // ========== DIAGRAMA (VIS.JS) ==========
    function initMarkovDiagram() {
        const container = document.getElementById('markov-visualization');
        if (!container) return;
        
        const data = { nodes: new vis.DataSet([]), edges: new vis.DataSet([]) };
        
        const options = {
            nodes: {
                shape: 'circle',
                margin: 10,
                color: { background: '#d1fae5', border: '#059669', highlight: '#34d399' },
                font: { size: 14, color: '#064e3b', face: 'arial bold' },
                borderWidth: 2,
                shadow: true
            },
            edges: {
                arrows: { to: { enabled: true, scaleFactor: 1 } },
                color: { color: '#6b7280', highlight: '#059669' },
                smooth: { type: 'curvedCW', roundness: 0.2 },
                font: { align: 'horizontal', size: 12, background: 'rgba(255,255,255,0.8)' }
            },
            interaction: { multiselect: true, hover: true, navigationButtons: true },
            physics: { 
                enabled: true, 
                stabilization: false,
                barnesHut: { gravitationalConstant: -2000, springLength: 150 } 
            }
        };
        
        markovDiagram = new vis.Network(container, data, options);
        
        markovDiagram.on("selectNode", function (params) {
            if (params.nodes.length > 0) {
                showStateDetails(params.nodes[0]);
            }
        });
        
        markovDiagram.on("doubleClick", function (params) {
            if (params.nodes.length > 0) {
                editState(params.nodes[0]);
            }
        });
    }

    // ========== GESTIÓN DE ESTRUCTURA ==========

    window.addState = function() {
        const name = prompt("Nombre del Estado (ej. A, Sol, 1):", "S" + (currentChain.states.length + 1));
        if (!name) return;
        
        const id = name.trim().replace(/\s+/g, '_').toLowerCase();
        
        if (currentChain.states.find(s => s.id === id)) { 
            showAlert("¡Ya existe un estado con ese ID!", 'warning'); 
            return; 
        }
        
        // Agregar estado
        currentChain.states.push({ id: id, label: name, description: '' });
        
        // Inicializar fila en matriz
        if (!currentChain.transitionMatrix) currentChain.transitionMatrix = {};
        currentChain.transitionMatrix[id] = {};
        
        renderChain();
        showAlert(`Estado "${name}" agregado`, 'success');
    };

    window.addTransition = function() {
        if (!currentChain.states || currentChain.states.length < 2) { 
            showAlert("Necesitas al menos 2 estados.", 'warning'); 
            return; 
        }
        
        const stateList = currentChain.states.map(s => s.label).join(', ');
        
        const fromLabel = prompt(`Estado Origen (${stateList}):`);
        if (!fromLabel) return;
        const source = currentChain.states.find(s => s.label === fromLabel || s.id === fromLabel);
        if (!source) { showAlert("Origen no encontrado", 'error'); return; }

        const toLabel = prompt(`Estado Destino (${stateList}):`);
        if (!toLabel) return;
        const target = currentChain.states.find(s => s.label === toLabel || s.id === toLabel);
        if (!target) { showAlert("Destino no encontrado", 'error'); return; }

        let prob = prompt(`Probabilidad de ${source.label} -> ${target.label} (0.0 - 1.0):`, "0.5");
        if (prob === null) return;
        
        prob = parseFloat(prob);
        if (isNaN(prob) || prob < 0 || prob > 1) { 
            showAlert("Probabilidad inválida.", 'error'); 
            return; 
        }

        if (!currentChain.transitionMatrix[source.id]) {
            currentChain.transitionMatrix[source.id] = {};
        }
        currentChain.transitionMatrix[source.id][target.id] = prob;
        
        renderChain();
        showAlert('Transición agregada', 'success');
    };

    window.removeSelected = function() {
        if (!markovDiagram) return;
        const sel = markovDiagram.getSelection();
        
        if (sel.nodes.length === 0 && sel.edges.length === 0) {
            showAlert("Selecciona elementos para eliminar.", 'info');
            return;
        }
        
        if (!confirm("¿Eliminar elementos seleccionados?")) return;
        
        if (sel.nodes.length > 0) {
            const nodesToRemove = sel.nodes;
            
            // Filtrar estados
            currentChain.states = currentChain.states.filter(s => !nodesToRemove.includes(s.id));
            
            // Limpiar matriz
            nodesToRemove.forEach(id => delete currentChain.transitionMatrix[id]);
            Object.keys(currentChain.transitionMatrix).forEach(fromId => {
                nodesToRemove.forEach(toId => {
                    if (currentChain.transitionMatrix[fromId][toId] !== undefined) {
                        delete currentChain.transitionMatrix[fromId][toId];
                    }
                });
            });
        }
        
        renderChain();
        markovDiagram.unselectAll();
        showAlert('Elementos eliminados', 'success');
    };

    window.editState = function(id) {
        const state = currentChain.states.find(s => s.id === id);
        if (!state) return;
        
        const newLabel = prompt("Editar nombre del estado:", state.label);
        if (newLabel && newLabel !== state.label) {
            state.label = newLabel;
            renderChain();
            showAlert('Estado actualizado', 'success');
        }
    };

    function renderChain() {
        if (!markovDiagram) return;

        const nodes = new vis.DataSet(currentChain.states.map(s => ({ 
            id: s.id, 
            label: s.label 
        })));
        
        const edges = new vis.DataSet();
        
        if (currentChain.transitionMatrix) {
            Object.keys(currentChain.transitionMatrix).forEach(fromId => {
                const transitions = currentChain.transitionMatrix[fromId];
                if (!transitions) return;

                Object.keys(transitions).forEach(toId => {
                    const prob = transitions[toId];
                    if (prob > 0) {
                        // Verificar existencia
                        const fromExists = currentChain.states.some(s => s.id === fromId);
                        const toExists = currentChain.states.some(s => s.id === toId);
                        
                        if (fromExists && toExists) {
                            edges.add({
                                from: fromId,
                                to: toId,
                                label: prob.toFixed(2),
                                arrows: 'to',
                                color: { color: prob > 0.8 ? '#047857' : '#6b7280' } 
                            });
                        }
                    }
                });
            });
        }
        
        markovDiagram.setData({ nodes, edges });
        updateChainInfo(currentChain);
    }

    window.fitDiagram = function() {
        if (markovDiagram) markovDiagram.fit({ animation: true });
    };

    // ========== MATRIZ DE TRANSICIÓN ==========

    window.showTransitionMatrix = function() {
        activateTab('matrix');
    };

    function displayTransitionMatrix() {
        const container = document.getElementById('matrix-container');
        if (!container) return;

        if (!currentChain.states || currentChain.states.length === 0) {
            container.innerHTML = '<div class="alert alert-warning">Agrega estados primero para ver la matriz.</div>';
            return;
        }

        let html = `<div class="table-responsive"><table class="table table-bordered text-center align-middle">`;
        
        // Header
        html += `<thead class="table-light"><tr><th>Origen \\ Destino</th>`;
        currentChain.states.forEach(s => html += `<th>${s.label}</th>`);
        html += `<th class="bg-light border-start">Suma</th></tr></thead><tbody>`;

        // Filas
        currentChain.states.forEach(from => {
            let rowSum = 0;
            html += `<tr><td class="fw-bold text-start">${from.label}</td>`;
            
            currentChain.states.forEach(to => {
                let val = 0;
                if (currentChain.transitionMatrix[from.id] && 
                    currentChain.transitionMatrix[from.id][to.id] !== undefined) {
                    val = currentChain.transitionMatrix[from.id][to.id];
                }
                rowSum += val;
                
                html += `<td class="p-1">
                    <input type="number" class="form-control form-control-sm text-center mx-auto" 
                           style="width: 70px;"
                           step="0.1" min="0" max="1" 
                           value="${val}" 
                           onchange="window.updateMatrixProb('${from.id}', '${to.id}', this.value)">
                </td>`;
            });
            
            const sumClass = Math.abs(rowSum - 1.0) < 0.01 ? 'text-success fw-bold' : 'text-danger fw-bold';
            const icon = Math.abs(rowSum - 1.0) < 0.01 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-exclamation-circle"></i>';
            html += `<td class="bg-light border-start ${sumClass}">${rowSum.toFixed(2)} ${icon}</td></tr>`;
        });

        html += `</tbody></table></div>`;
        html += `<div class="mt-2 small text-muted"><i class="fas fa-info-circle"></i> Asegúrate de que todas las filas sumen 1.0.</div>`;
        
        container.innerHTML = html;
    }

    window.updateMatrixProb = function(fromId, toId, value) {
        let prob = parseFloat(value);
        if (isNaN(prob)) prob = 0;
        if (prob < 0) prob = 0;
        if (prob > 1) prob = 1;
        
        if (!currentChain.transitionMatrix[fromId]) {
            currentChain.transitionMatrix[fromId] = {};
        }
        
        currentChain.transitionMatrix[fromId][toId] = prob;
        displayTransitionMatrix();
    };

    // ========== ANÁLISIS Y ESTADO ESTACIONARIO (CLIENTE) ==========
    
    window.calculateSteadyState = function() {
        const container = document.getElementById('analysis-results');
        if (!container) return; // Si no existe, no hacemos nada (ya se creó en ensureAnalysisTab)

        if (!currentChain.states || currentChain.states.length === 0) {
            container.innerHTML = '<div class="alert alert-warning">No hay cadena para analizar.</div>';
            return;
        }

        // Validar estocasticidad
        const valid = verifyStochasticMatrix();
        if (!valid) {
            container.innerHTML = '<div class="alert alert-danger">La matriz no es estocástica. Revisa que las filas sumen 1.0.</div>';
            return;
        }

        // Cálculo Local
        const result = computeSteadyStateClient(currentChain);
        renderSteadyStateResults(result, container);
    };

    function verifyStochasticMatrix() {
        if (!currentChain.transitionMatrix) return false;
        
        for (const state of currentChain.states) {
            const stateId = state.id;
            if (!currentChain.transitionMatrix[stateId]) continue;
            
            let sum = 0;
            Object.values(currentChain.transitionMatrix[stateId]).forEach(prob => sum += prob);
            
            if (Math.abs(sum - 1.0) > 0.01) return false;
        }
        return true;
    }

    function computeSteadyStateClient(chain) {
        const states = chain.states;
        const n = states.length;
        const idToIndex = {};
        states.forEach((s, i) => idToIndex[s.id] = i);

        // Construir matriz P
        let P = Array(n).fill(0).map(() => Array(n).fill(0));
        
        Object.keys(chain.transitionMatrix).forEach(fromId => {
            const rowIdx = idToIndex[fromId];
            if (rowIdx === undefined) return;
            const transitions = chain.transitionMatrix[fromId];
            Object.keys(transitions).forEach(toId => {
                const colIdx = idToIndex[toId];
                if (colIdx !== undefined) P[rowIdx][colIdx] = parseFloat(transitions[toId]);
            });
        });

        // Iteración de potencias
        let v = Array(n).fill(1/n);
        const maxIter = 1000;
        let iter = 0;
        
        while(iter < maxIter) {
            let vNext = Array(n).fill(0);
            for(let j=0; j<n; j++) {
                for(let i=0; i<n; i++) {
                    vNext[j] += v[i] * P[i][j];
                }
            }
            // Normalizar (evitar errores de punto flotante)
            const sum = vNext.reduce((a,b)=>a+b, 0);
            v = vNext.map(x => x/sum);
            iter++;
        }

        const distribution = states.map((s, i) => ({
            state: s.label,
            probability: v[i]
        }));

        return { distribution, iterations: iter };
    }

    function renderSteadyStateResults(data, container) {
        let html = `
        <div class="card border-success mb-3 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-balance-scale"></i> Estado Estacionario</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Probabilidades a largo plazo (Calculado localmente):</p>
                <div class="row text-center justify-content-center">`;
        
        data.distribution.forEach(item => {
            const pct = (item.probability * 100).toFixed(2);
            html += `
                <div class="col-md-3 mb-3">
                    <div class="p-3 border rounded bg-light">
                        <h6 class="text-secondary mb-1">${item.state}</h6>
                        <h3 class="text-success mb-0">${pct}%</h3>
                        <small class="text-muted">${item.probability.toFixed(4)}</small>
                    </div>
                </div>`;
        });
        
        html += `</div></div></div>`;
        container.innerHTML = html;
    }

    // ========== SIMULACIÓN (CLIENTE) ==========

    window.simulateSteps = function() {
        activateTab('simulation');
    };

    function updateSimulationUI() {
        const select = document.getElementById('initial-state-selector');
        if (!select) return;
        
        select.innerHTML = '';
        currentChain.states.forEach(s => {
            const btn = document.createElement('button');
            btn.className = 'btn btn-outline-primary btn-sm m-1';
            btn.innerText = s.label;
            
            if (selectedInitialState === s.id) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
            }
            
            btn.onclick = () => {
                select.querySelectorAll('button').forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-primary');
                });
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
                selectedInitialState = s.id;
            };
            select.appendChild(btn);
        });
    }

    window.runSimulation = function() {
        if (!selectedInitialState) return showAlert("Selecciona un estado inicial.", 'warning');
        
        const stepsInput = document.getElementById('num-steps');
        const steps = parseInt(stepsInput ? stepsInput.value : 10) || 10;
        const container = document.getElementById('simulation-results');
        
        let current = selectedInitialState;
        let history = [current];
        
        for (let i = 0; i < steps; i++) {
            const transitions = currentChain.transitionMatrix[current];
            if (!transitions) break;
            
            const rand = Math.random();
            let cumulative = 0;
            let next = current;
            
            for (const [target, prob] of Object.entries(transitions)) {
                cumulative += prob;
                if (rand <= cumulative) {
                    next = target;
                    break;
                }
            }
            current = next;
            history.push(current);
        }
        
        const labels = history.map(id => currentChain.states.find(st => st.id === id)?.label || id);
        
        if (container) {
            container.innerHTML = `
                <div class="card shadow-sm mt-3">
                    <div class="card-header bg-dark text-white">Trayectoria (${steps} pasos)</div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                            ${labels.map((l, i) => `<span class="badge bg-primary p-2">${i}: ${l}</span>`).join('<i class="fas fa-arrow-right text-muted"></i>')}
                        </div>
                    </div>
                </div>
            `;
        }
    };

    // ========== UTILIDADES Y EJEMPLOS ==========

    window.createNewChain = function(confirmAction = true) {
        if (confirmAction && currentChain.states.length > 0 && !confirm("¿Borrar cadena actual?")) return;
        
        currentChain = { 
            name: "Nueva Cadena", 
            states: [], 
            transitionMatrix: {},
            domain: "General"
        };
        renderChain();
        activateTab('diagram');
    };

    window.saveChain = function() {
        window.exportChain();
    };

    window.exportChain = function() {
        const dataStr = JSON.stringify(currentChain, null, 2);
        const blob = new Blob([dataStr], { type: "application/json" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = (currentChain.name || "markov").replace(/\s/g, '_') + ".json";
        a.click();
    };

    function initExampleButtons() {
        document.querySelectorAll('.btn-example').forEach(btn => {
            btn.addEventListener('click', () => loadExample(btn.dataset.example));
        });
    }

    function loadExample(type) {
        const baseUrl = document.body.dataset.baseUrl || window.location.origin;
        const url = `${baseUrl}/modules/markov/examples/${type}.json`;
        
        fetch(url)
            .then(res => {
                if(!res.ok) throw new Error('Error cargando ejemplo');
                return res.json();
            })
            .then(data => {
                currentChain = data;
                renderChain();
                activateTab('diagram');
                showAlert(`Ejemplo ${data.name} cargado`, 'success');
            })
            .catch(e => showAlert(e.message, 'error'));
    }

    // ========== INFO Y UTILS ==========
    function updateChainInfo(chain) {
        const details = document.getElementById('state-details');
        const info = document.getElementById('state-info');
        if (info) info.style.display = 'block';
        if (details) details.innerHTML = `<p>${chain.states.length} Estados</p>`;
    }

    function showStateDetails(id) {
        const state = currentChain.states.find(s => s.id === id);
        if (!state) return;
        
        const details = document.getElementById('state-details');
        if (!details) return;
        
        let html = `<h5>${state.label}</h5><hr><h6>Transiciones:</h6><ul>`;
        if (currentChain.transitionMatrix[id]) {
            Object.entries(currentChain.transitionMatrix[id]).forEach(([to, p]) => {
                if(p > 0) {
                    const toLabel = currentChain.states.find(s=>s.id===to)?.label || to;
                    html += `<li>Hacia <strong>${toLabel}</strong>: ${(p*100).toFixed(1)}%</li>`;
                }
            });
            html += '</ul>';
        }
        html += '</div>';
        details.innerHTML = html;
    }

    function showAlert(msg, type = 'info') {
        const div = document.createElement('div');
        const color = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#3b82f6');
        
        div.style.cssText = `
            position: fixed; top: 80px; right: 20px; z-index: 9999;
            background-color: ${color}; color: white;
            padding: 15px 25px; border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease;
        `;
        div.innerHTML = `<strong>${type.toUpperCase()}:</strong> ${msg}`;
        
        document.body.appendChild(div);
        
        if (!document.getElementById('anim-style')) {
            const style = document.createElement('style');
            style.id = 'anim-style';
            style.textContent = `@keyframes slideIn { from { transform: translateX(100%); opacity:0; } to { transform: translateX(0); opacity:1; } }`;
            document.head.appendChild(style);
        }

        setTimeout(() => {
            div.style.opacity = '0';
            setTimeout(() => div.remove(), 300);
        }, 3000);
    }
}