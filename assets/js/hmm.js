/**
 * JavaScript para Módulo de HMM (Hidden Markov Models)
 * Universidad Michoacana de San Nicolás de Hidalgo
 * VERSIÓN ROBUSTA - VITERBI & RESULTADOS
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
    let currentView = 'combined';

    // ========== INICIALIZACIÓN ==========
    document.addEventListener('DOMContentLoaded', () => {
        console.log('✅ Módulo de HMM iniciado');
        
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
        // Aseguramos que la UI de resultados exista desde el inicio
        setTimeout(ensureResultsTab, 500);
    }

    // ========== GESTIÓN INTELIGENTE DE TABS ==========
    function initTabs() {
        // Delegación de eventos para manejar botones creados dinámicamente
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.tab-button');
            if (btn && document.querySelector('.hmm-tabs').contains(btn)) {
                e.preventDefault();
                const tabId = btn.dataset.tab;
                if (tabId) activateTab(tabId);
            }
        });
    }

    function activateTab(tabId) {
        console.log('🔍 Activando tab:', tabId);
        
        // 1. Desactivar todos
        document.querySelectorAll('.hmm-tabs .tab-button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => {
            p.classList.remove('active');
            p.style.display = 'none';
        });
        
        // 2. Buscar elementos (incluso si se crearon dinámicamente)
        const btn = document.querySelector(`.hmm-tabs .tab-button[data-tab="${tabId}"]`);
        const panel = document.getElementById(`tab-${tabId}`);
        
        // 3. Activar
        if (btn) btn.classList.add('active');
        if (panel) {
            panel.classList.add('active');
            panel.style.display = 'block'; // Forzar display block
            
            // Acciones específicas
            if (tabId === 'matrices' && currentHMM) displayMatrices();
            if (tabId === 'diagram' && hmmDiagram) {
                setTimeout(() => hmmDiagram.fit({ animation: true }), 100);
            }
        } else {
            console.warn(`⚠️ No se encontró el panel para el tab: ${tabId}`);
            // Si falta el panel de resultados, intentar crearlo de nuevo y activar
            if (tabId === 'results') {
                ensureResultsTab();
                // Reintentar activación tras un breve delay
                setTimeout(() => activateTab('results'), 50);
            }
        }
    }

    // ✅ FUNCIÓN MEJORADA: Asegurar que existe el contenedor de resultados Y el botón
    function ensureResultsTab() {
        // 1. Verificar si existe el tab de resultados
        let resultsTab = document.getElementById('tab-results');
        
        if (!resultsTab) {
            console.log('⚠️ Tab de resultados no existe, creándolo...');
            
            // Buscar el contenedor de tabs
            const tabsContainer = document.querySelector('.tab-content');
            
            if (tabsContainer) {
                resultsTab = document.createElement('div');
                resultsTab.id = 'tab-results';
                resultsTab.className = 'tab-panel';
                resultsTab.style.display = 'none';
                resultsTab.innerHTML = `
                    <h3>Resultados del Análisis (Viterbi)</h3>
                    <p class="text-muted">Ruta más probable de estados ocultos</p>
                    <div id="results-container">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Ejecuta el algoritmo de Viterbi para ver resultados aquí.
                        </div>
                    </div>`;
                tabsContainer.appendChild(resultsTab);
                console.log('✅ Tab de resultados creado');
            } else {
                console.error('❌ No se encontró .tab-content');
            }
        }
        
        // 2. Verificar si existe el botón del tab
        let resultsButton = document.querySelector('.hmm-tabs .tab-button[data-tab="results"]');
        
        if (!resultsButton) {
            console.log('⚠️ Botón de resultados no existe, creándolo...');
            
            const tabsNav = document.querySelector('.hmm-tabs');
            
            if (tabsNav) {
                resultsButton = document.createElement('button');
                resultsButton.className = 'tab-button';
                resultsButton.setAttribute('data-tab', 'results');
                resultsButton.innerHTML = '<i class="fas fa-chart-bar"></i> Resultados';
                // El event listener ya está delegado en initTabs
                tabsNav.appendChild(resultsButton);
                console.log('✅ Botón de resultados creado');
            } else {
                console.error('❌ No se encontró .hmm-tabs');
            }
        }
    }

    // ========== DIAGRAMA VIS.JS ==========
    function initHMMDiagram() {
        const container = document.getElementById('hmm-visualization');
        if (!container) return;
        
        const data = { nodes: new vis.DataSet([]), edges: new vis.DataSet([]) };
        
        const options = {
            nodes: {
                shape: 'box',
                margin: 10,
                font: { size: 14, color: '#581c87' },
                borderWidth: 2,
                shadow: true
            },
            edges: {
                arrows: { to: { enabled: true, scaleFactor: 1 } },
                color: { color: '#6b7280', highlight: '#9333ea' },
                width: 2,
                smooth: { type: 'cubicBezier' }
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
            physics: { enabled: false },
            interaction: { hover: true, multiselect: true }
        };
        
        hmmDiagram = new vis.Network(container, data, options);
        hmmDiagram.on('click', params => {
            if (params.nodes.length > 0) showNodeDetails(params.nodes[0]);
        });
    }

    // ========== RENDERIZADO ==========
    function renderHMM(hmm) {
        if (!hmmDiagram) return;
        
        const nodes = new vis.DataSet();
        const edges = new vis.DataSet();
        
        // Estados Ocultos
        hmm.hiddenStates.forEach(state => {
            nodes.add({
                id: `hidden_${state.id}`,
                label: `🔒 ${state.label || state.id}`,
                title: `Estado Oculto: ${state.description || ''}`,
                level: 0,
                color: { background: '#f3e8ff', border: '#9333ea', highlight: { background: '#9333ea', border: '#7e22ce' } },
                font: { color: '#581c87', face: 'arial bold' }
            });
        });
        
        // Observaciones
        hmm.observations.forEach(obs => {
            nodes.add({
                id: `obs_${obs.id}`,
                label: `👁️ ${obs.label || obs.id}`,
                title: `Observación: ${obs.description || ''}`,
                level: 1,
                color: { background: '#ddd6fe', border: '#7c3aed', highlight: { background: '#7c3aed', border: '#6d28d9' } },
                font: { color: '#581c87' }
            });
        });
        
        // Transiciones
        if (hmm.transitionMatrix) {
            Object.entries(hmm.transitionMatrix).forEach(([from, targets]) => {
                Object.entries(targets).forEach(([to, prob]) => {
                    if (prob > 0) edges.add({
                        from: `hidden_${from}`, to: `hidden_${to}`,
                        label: prob.toFixed(2), color: { color: '#9333ea' }, width: 2, arrows: 'to'
                    });
                });
            });
        }
        
        // Emisiones
        if (hmm.emissionMatrix) {
            Object.entries(hmm.emissionMatrix).forEach(([state, emissions]) => {
                Object.entries(emissions).forEach(([obs, prob]) => {
                    if (prob > 0) edges.add({
                        from: `hidden_${state}`, to: `obs_${obs}`,
                        label: prob.toFixed(2), color: { color: '#c026d3' }, width: 1, dashes: true, arrows: 'to'
                    });
                });
            });
        }
        
        hmmDiagram.setData({ nodes, edges });
        setTimeout(() => hmmDiagram.fit({ animation: true }), 100);
        updateHMMInfo(hmm);
    }

    // ========== ALGORITMOS (VITERBI) ==========
    
    // Funciones globales para botones
    window.runForward = () => showAlert('⚠️ Solo Viterbi está disponible', 'warning');
    window.runBaumWelch = () => showAlert('⚠️ Solo Viterbi está disponible', 'warning');

    window.runViterbi = function() {
        console.log('🚀 Iniciando algoritmo de Viterbi...');

        // ✅ DEPURACIÓN: Verificar estado del DOM
        console.log('🔍 Verificando estructura del DOM:');
        console.log('- Tab results existe:', !!document.getElementById('tab-results'));
        console.log('- Botón results existe:', !!document.querySelector('.tab-button[data-tab="results"]'));
        console.log('- Container tab-content existe:', !!document.querySelector('.tab-content'));
        console.log('- HMM cargado:', !!currentHMM);
        console.log('- Secuencia:', observationSequence);
        
        // 1. Diagnóstico de estado
        if (!currentHMM) return showAlert('Carga un HMM primero', 'error');
        if (observationSequence.length === 0) return showAlert('Define una secuencia de observación primero (Tab Observación)', 'error');

        // 2. Validación de datos
        const states = currentHMM.hiddenStates;
        const N = states.length;
        const T = observationSequence.length;
        
        if (N === 0) return showAlert('El modelo no tiene estados ocultos', 'error');

        try {
            // Estructuras (Logs para precisión)
            let V = Array(T).fill(0).map(() => Array(N).fill(-Infinity));
            let path = Array(T).fill(0).map(() => Array(N).fill(0));
            
            // --- INICIALIZACIÓN ---
            const firstObs = observationSequence[0].id;
            let validStart = false;

            for (let s = 0; s < N; s++) {
                const id = states[s].id;
                const pi = currentHMM.initialProbabilities[id] || 0;
                const b = currentHMM.emissionMatrix[id]?.[firstObs] || 0;
                
                if (pi > 0 && b > 0) {
                    V[0][s] = Math.log(pi) + Math.log(b);
                    validStart = true;
                }
                path[0][s] = -1;
            }

            if (!validStart) return showAlert('Imposible iniciar: probabilidad 0 para la primera observación', 'error');

            // --- RECURSIÓN ---
            for (let t = 1; t < T; t++) {
                const obs = observationSequence[t].id;
                let validStep = false;

                for (let s = 0; s < N; s++) {
                    const currId = states[s].id;
                    let maxLogP = -Infinity;
                    let bestPrev = -1;

                    for (let prev = 0; prev < N; prev++) {
                        const prevId = states[prev].id;
                        const trans = currentHMM.transitionMatrix[prevId]?.[currId] || 0;
                        
                        if (V[t-1][prev] !== -Infinity && trans > 0) {
                            const p = V[t-1][prev] + Math.log(trans);
                            if (p > maxLogP) {
                                maxLogP = p;
                                bestPrev = prev;
                            }
                        }
                    }

                    const emit = currentHMM.emissionMatrix[currId]?.[obs] || 0;
                    if (maxLogP !== -Infinity && emit > 0) {
                        V[t][s] = maxLogP + Math.log(emit);
                        path[t][s] = bestPrev;
                        validStep = true;
                    }
                }
                if (!validStep) return showAlert(`Secuencia rota en el paso ${t+1} (probabilidad 0)`, 'error');
            }

            // --- TERMINACIÓN ---
            let maxFinalLogP = -Infinity;
            let lastStateIdx = -1;
            
            for (let s = 0; s < N; s++) {
                if (V[T-1][s] > maxFinalLogP) {
                    maxFinalLogP = V[T-1][s];
                    lastStateIdx = s;
                }
            }

            if (lastStateIdx === -1) return showAlert('La secuencia es imposible con este modelo', 'error');

            // --- BACKTRACKING ---
            const bestPathIndices = new Array(T);
            bestPathIndices[T-1] = lastStateIdx;
            
            for (let t = T - 2; t >= 0; t--) {
                bestPathIndices[t] = path[t+1][bestPathIndices[t+1]];
            }

            const resultSequence = bestPathIndices.map(idx => states[idx]);
            const probability = Math.exp(maxFinalLogP);
            const V_display = V.map(row => row.map(v => v === -Infinity ? 0 : Math.exp(v)));

            // Mostrar resultados
            displayViterbiResults(resultSequence, probability, V_display, states, maxFinalLogP);

        } catch (e) {
            console.error(e);
            showAlert('Error matemático: ' + e.message, 'error');
        }
    };

    // ✅ FUNCIÓN MEJORADA Y ROBUSTA PARA MOSTRAR RESULTADOS
    function displayViterbiResults(sequence, probability, V, states, logProb) {
        console.log('📊 Mostrando resultados de Viterbi...');
        
        // 1. Asegurar que la UI existe
        ensureResultsTab();
        
        // 2. Activar Tab
        activateTab('results');
        
        // 3. Buscar contenedor de forma flexible
        let container = document.getElementById('tab-results');
        
        // Si no existe, intentar crearlo de emergencia
        if (!container) {
            console.error('❌ No se encontró #tab-results, creándolo de emergencia...');
            
            const tabContent = document.querySelector('.tab-content') || document.querySelector('.hmm-main');
            
            if (tabContent) {
                container = document.createElement('div');
                container.id = 'tab-results';
                container.className = 'tab-panel active';
                container.style.display = 'block';
                tabContent.appendChild(container);
            } else {
                showAlert('❌ Error crítico: No se pudo crear el contenedor de resultados', 'error');
                return;
            }
        }
        
        // Asegurarse de que el tab esté visible
        container.style.display = 'block';
        container.classList.add('active');

        // Construir HTML
        let html = `
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2px; border-radius: 14px; margin-bottom: 25px;">
                <div style="background: white; padding: 30px; border-radius: 12px;">
                    <div style="text-align: center; margin-bottom: 25px;">
                        <h2 style="color: #7e22ce; margin-bottom: 10px;">
                            <i class="fas fa-magic"></i> Resultado Algoritmo Viterbi
                        </h2>
                        <p style="color: #6b7280; font-size: 1.1em;">
                            Secuencia de estados ocultos más probable
                        </p>
                    </div>
                    
                    <!-- Secuencia de Observaciones -->
                    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 3px solid #f59e0b; border-radius: 12px; padding: 20px; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2);">
                        <h5 style="color: #92400e; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-eye"></i> Secuencia de Observaciones
                        </h5>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                            ${observationSequence.map((obs, i) => 
                                `<div style="text-align: center;">
                                    <div style="background: #f59e0b; color: white; padding: 10px 16px; border-radius: 10px; font-weight: 700; box-shadow: 0 2px 4px rgba(0,0,0,0.15);">
                                        ${obs.label}
                                    </div>
                                    <div style="font-size: 0.75em; color: #78716c; margin-top: 5px; font-weight: 600;">t=${i}</div>
                                </div>`
                            ).join('<i class="fas fa-arrow-right" style="color: #d97706; font-size: 1.3em; align-self: center;"></i>')}
                        </div>
                    </div>
                    
                    <!-- Camino Óptimo -->
                    <div style="background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); border: 3px solid #9333ea; border-radius: 12px; padding: 25px; margin-bottom: 25px; box-shadow: 0 6px 12px rgba(147, 51, 234, 0.2);">
                        <h4 style="color: #581c87; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-route"></i> Camino Óptimo (Hidden States Path)
                        </h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center; justify-content: center; margin-bottom: 20px;">
        `;
        
        sequence.forEach((state, idx) => {
            html += `
                <div style="text-align: center; animation: fadeInScale 0.5s ease ${idx * 0.1}s both;">
                    <div style="background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%); color: white; padding: 14px 24px; border-radius: 25px; font-weight: bold; box-shadow: 0 4px 8px rgba(147, 51, 234, 0.4); border: 2px solid #c084fc;">
                        ${state.label || state.id}
                    </div>
                    <div style="font-size: 0.8em; color: #6b7280; margin-top: 6px; font-weight: 600;">paso ${idx}</div>
                </div>
            `;
            if (idx < sequence.length - 1) {
                html += `<i class="fas fa-long-arrow-alt-right" style="color: #d8b4fe; font-size: 2em;"></i>`;
            }
        });
        
        html += `
                        </div>
                        
                        <!-- Estadísticas del Resultado -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px;">
                            <div style="background: rgba(147, 51, 234, 0.1); padding: 15px; border-radius: 10px; border-left: 4px solid #9333ea;">
                                <div style="font-size: 0.9em; color: #6b7280; margin-bottom: 5px;">Log-Probabilidad</div>
                                <div style="font-size: 1.3em; font-weight: bold; color: #7e22ce;">${logProb.toFixed(4)}</div>
                            </div>
                            <div style="background: rgba(147, 51, 234, 0.1); padding: 15px; border-radius: 10px; border-left: 4px solid #9333ea;">
                                <div style="font-size: 0.9em; color: #6b7280; margin-bottom: 5px;">Probabilidad</div>
                                <div style="font-size: 1.3em; font-weight: bold; color: #7e22ce;">${probability.toExponential(6)}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabla Detallada -->
                    <div style="background: white; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                        <h5 style="color: #6b7280; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-table"></i> Matriz Viterbi V[t][estado]
                        </h5>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: center;">
                                <thead>
                                    <tr style="background: #f9fafb;">
                                        <th style="padding: 12px; border: 2px solid #e5e7eb; background: #9333ea; color: white; font-weight: bold;">Estado \\ Tiempo</th>
                                        ${observationSequence.map((obs, t) => 
                                            `<th style="padding: 12px; border: 2px solid #e5e7eb; background: #f3e8ff;">
                                                <div style="color: #581c87; font-weight: bold;">t=${t}</div>
                                                <small style="color: #9333ea;">${obs.label}</small>
                                            </th>`
                                        ).join('')}
                                    </tr>
                                </thead>
                                <tbody>
        `;
        
        states.forEach((state, sIdx) => {
            html += `<tr><td style="font-weight: bold; color: #581c87; background: #faf5ff; padding: 12px; border: 2px solid #e5e7eb;">${state.label}</td>`;
            for (let t = 0; t < V.length; t++) {
                const val = V[t][sIdx];
                const isMax = val > 0 && Math.abs(val - Math.max(...V[t])) < 1e-10;
                const style = isMax 
                    ? 'background: linear-gradient(135deg, #9333ea 0%, #7e22ce 100%); color: white; font-weight: bold; box-shadow: inset 0 0 0 2px #c084fc;' 
                    : val > 0 
                        ? 'background: #f3e8ff; color: #581c87;' 
                        : 'background: #f9fafb; color: #d1d5db;';
                        
                const displayVal = val > 0 ? (val < 0.001 ? val.toExponential(2) : val.toFixed(6)) : '—';
                html += `<td style="${style} padding: 10px; border: 2px solid #e5e7eb;">${displayVal}</td>`;
            }
            html += `</tr>`;
        });
        
        html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div style="display: flex; gap: 10px; justify-content: center; margin-top: 25px;">
                        <button onclick="window.clearSequence()" class="btn btn-outline-secondary">
                            <i class="fas fa-eraser"></i> Limpiar Secuencia
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
        
        // ✅ Añadir animación CSS
        if (!document.getElementById('viterbi-animations')) {
            const style = document.createElement('style');
            style.id = 'viterbi-animations';
            style.textContent = `
                @keyframes fadeInScale {
                    from { opacity: 0; transform: scale(0.8); }
                    to { opacity: 1; transform: scale(1); }
                }
            `;
            document.head.appendChild(style);
        }
        
        showAlert('✅ Resultados de Viterbi calculados correctamente', 'success');
        console.log('✅ Resultados mostrados correctamente');
    }

    // ========== UTILIDADES & HELPERS ==========
    
    // Carga de Ejemplos
    function initExampleButtons() {
        document.querySelectorAll('.btn-example').forEach(btn => {
            btn.addEventListener('click', () => {
                if (btn.dataset.example) loadExample(btn.dataset.example);
            });
        });
    }

    function loadExample(name) {
        const baseUrl = document.body.dataset.baseUrl || window.location.origin;
        const url = `${baseUrl}/modules/hmm/examples/${name}.json`;
        
        fetch(url)
            .then(res => {
                if(!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                currentHMM = data;
                renderHMM(data);
                updateObservationSelector(data);
                showAlert(`Ejemplo ${data.name} cargado`, 'success');
                activateTab('diagram');
            })
            .catch(e => showAlert('Error cargando ejemplo: ' + e.message, 'error'));
    }

    // Manejo de Secuencia
    function updateObservationSelector(hmm) {
        const container = document.getElementById('observation-selector');
        if (!container) return;
        container.innerHTML = '';
        hmm.observations.forEach(obs => {
            const btn = document.createElement('button');
            btn.className = 'btn btn-outline-primary btn-sm';
            btn.style.margin = '4px';
            btn.textContent = obs.label || obs.id;
            btn.onclick = () => window.addToSequence(obs.id, obs.label || obs.id);
            container.appendChild(btn);
        });
    }

    // Funciones globales (window)
    window.addToSequence = function(id, label) {
        observationSequence.push({ id, label });
        updateSequenceDisplay();
    };

    window.clearSequence = function() {
        observationSequence = [];
        updateSequenceDisplay();
    };

    window.setManualSequence = function() {
        const input = document.getElementById('manual-sequence');
        if (!input || !input.value.trim()) return;
        observationSequence = input.value.trim().split(/\s+/).map(id => ({ id, label: id }));
        updateSequenceDisplay();
    };

    function updateSequenceDisplay() {
        const container = document.getElementById('observation-sequence');
        if (!container) return;
        if (observationSequence.length === 0) {
            container.innerHTML = '<p class="text-muted">Sin secuencia definida</p>';
            return;
        }
        container.innerHTML = observationSequence.map((o, i) => 
            `<span style="background:#9333ea;color:white;padding:4px 8px;border-radius:4px;margin:2px;display:inline-block;">${i+1}. ${o.label}</span>`
        ).join(' ');
    }

    // CRUD HMM
    window.createNewHMM = function() {
        if(currentHMM && !confirm('¿Crear nuevo?')) return;
        currentHMM = { name: 'Nuevo', hiddenStates: [], observations: [], transitionMatrix: {}, emissionMatrix: {}, initialProbabilities: {} };
        if(hmmDiagram) hmmDiagram.setData({nodes:[], edges:[]});
        renderHMM(currentHMM);
        showAlert('Nuevo HMM creado', 'success');
    };

    window.saveHMM = function() {
        if(!currentHMM) return showAlert('Nada que guardar', 'warning');
        const blob = new Blob([JSON.stringify(currentHMM, null, 2)], {type:'application/json'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = 'hmm_model.json';
        a.click();
    };
    window.exportHMM = window.saveHMM;

    // Helpers UI
    window.toggleView = () => showAlert('Vista cambiada (simulado)', 'info');
    window.fitDiagram = () => hmmDiagram && hmmDiagram.fit({ animation: true });
    window.addHiddenState = () => showAlert('Edición en desarrollo', 'info');
    window.addObservation = () => showAlert('Edición en desarrollo', 'info');

    function updateHMMInfo(hmm) {
        const details = document.getElementById('hmm-details');
        if(details) details.innerHTML = `<h5>${hmm.name}</h5><p>${hmm.description || ''}</p>`;
        const info = document.getElementById('hmm-info');
        if(info) info.style.display = 'block';
    }

    function showNodeDetails(nodeId) { /* Detalle básico de nodos */ }
    function displayMatrices() { /* Lógica de matrices (simplificada para brevedad) */ 
        const container = document.getElementById('matrices-container');
        if(container && currentHMM) container.innerHTML = '<div class="alert alert-info">Matrices disponibles en JSON</div>';
    }

    function showAlert(msg, type='info') {
        const div = document.createElement('div');
        div.style.cssText = `position:fixed;top:80px;right:20px;padding:15px;background:${type==='error'?'#ef4444':'#10b981'};color:white;border-radius:8px;z-index:9999;box-shadow:0 4px 6px rgba(0,0,0,0.1);`;
        div.textContent = msg;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }
}