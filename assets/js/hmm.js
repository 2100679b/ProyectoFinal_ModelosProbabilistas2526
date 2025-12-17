/**
 * JavaScript para Módulo de HMM (Hidden Markov Models)
 * Universidad Michoacana de San Nicolás de Hidalgo
 * VERSIÓN MEJORADA - VITERBI CON VALIDACIÓN Y DEBUG
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
    let lastViterbiResults = null; // Guardar últimos resultados

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
        initKeyboardShortcuts();
        setTimeout(ensureResultsTab, 500);
    }

    // ========== GESTIÓN INTELIGENTE DE TABS ==========
    function initTabs() {
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
        
        document.querySelectorAll('.hmm-tabs .tab-button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => {
            p.classList.remove('active');
            p.style.display = 'none';
        });
        
        const btn = document.querySelector(`.hmm-tabs .tab-button[data-tab="${tabId}"]`);
        const panel = document.getElementById(`tab-${tabId}`);
        
        if (btn) btn.classList.add('active');
        if (panel) {
            panel.classList.add('active');
            panel.style.display = 'block';
            
            if (tabId === 'matrices' && currentHMM) displayMatrices();
            if (tabId === 'diagram' && hmmDiagram) {
                setTimeout(() => hmmDiagram.fit({ animation: true }), 100);
            }
        } else {
            console.warn(`⚠️ No se encontró el panel para el tab: ${tabId}`);
            if (tabId === 'results') {
                ensureResultsTab();
                setTimeout(() => activateTab('results'), 50);
            }
        }
    }

    function ensureResultsTab() {
        let resultsTab = document.getElementById('tab-results');
        
        if (!resultsTab) {
            console.log('⚠️ Tab de resultados no existe, creándolo...');
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
            }
        }
        
        let resultsButton = document.querySelector('.hmm-tabs .tab-button[data-tab="results"]');
        
        if (!resultsButton) {
            console.log('⚠️ Botón de resultados no existe, creándolo...');
            const tabsNav = document.querySelector('.hmm-tabs');
            
            if (tabsNav) {
                resultsButton = document.createElement('button');
                resultsButton.className = 'tab-button';
                resultsButton.setAttribute('data-tab', 'results');
                resultsButton.innerHTML = '<i class="fas fa-chart-bar"></i> Resultados';
                tabsNav.appendChild(resultsButton);
                console.log('✅ Botón de resultados creado');
            }
        }
    }

    // ========== ATAJOS DE TECLADO ==========
    function initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            
            // Ctrl/Cmd + R: Ejecutar Viterbi
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'r' && !e.shiftKey) {
                e.preventDefault();
                if (currentHMM && observationSequence.length > 0) {
                    window.runViterbi(false);
                } else {
                    showAlert('⚠️ Carga un HMM y define una secuencia primero', 'warning');
                }
            }
            
            // Ctrl/Cmd + Shift + R: Ejecutar con Debug
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'r') {
                e.preventDefault();
                if (currentHMM && observationSequence.length > 0) {
                    window.runViterbi(true);
                } else {
                    showAlert('⚠️ Carga un HMM y define una secuencia primero', 'warning');
                }
            }
            
            // Ctrl/Cmd + E: Exportar resultados
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'e') {
                e.preventDefault();
                if (lastViterbiResults) {
                    window.exportViterbiResults(
                        lastViterbiResults.sequence,
                        lastViterbiResults.probability,
                        lastViterbiResults.V,
                        lastViterbiResults.states,
                        lastViterbiResults.logProb
                    );
                } else {
                    showAlert('⚠️ Ejecuta Viterbi primero', 'warning');
                }
            }
        });
        
        console.log('⌨️ Atajos de teclado habilitados:');
        console.log('   Ctrl+R: Ejecutar Viterbi');
        console.log('   Ctrl+Shift+R: Ejecutar con Debug');
        console.log('   Ctrl+E: Exportar resultados');
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

    // ========== VALIDACIÓN PARA VITERBI ==========
    function validateForViterbi() {
        const errors = [];
        
        if (!currentHMM) {
            errors.push('No hay modelo HMM cargado');
            return errors;
        }
        
        if (observationSequence.length === 0) {
            errors.push('No hay secuencia de observaciones definida');
            return errors;
        }
        
        // Validar que todas las observaciones existen en el modelo
        const validObs = new Set(currentHMM.observations.map(o => o.id));
        observationSequence.forEach((obs, i) => {
            if (!validObs.has(obs.id)) {
                errors.push(`Observación "${obs.id}" en posición ${i} no existe en el modelo`);
            }
        });
        
        // Validar probabilidades iniciales
        const hasInitialProbs = currentHMM.hiddenStates.some(s => 
            (currentHMM.initialProbabilities[s.id] || 0) > 0
        );
        if (!hasInitialProbs) {
            errors.push('No hay probabilidades iniciales definidas (todas son 0)');
        }
        
        // Validar que hay al menos un camino posible
        const firstObs = observationSequence[0].id;
        const canStart = currentHMM.hiddenStates.some(s => {
            const pi = currentHMM.initialProbabilities[s.id] || 0;
            const b = currentHMM.emissionMatrix[s.id]?.[firstObs] || 0;
            return pi > 0 && b > 0;
        });
        
        if (!canStart) {
            errors.push(`Imposible iniciar: ningún estado puede emitir "${firstObs}" con probabilidad > 0`);
        }
        
        return errors;
    }

    // ========== ALGORITMO DE VITERBI MEJORADO ==========
    window.runViterbi = function(debugMode = false) {
        console.log('🚀 Iniciando algoritmo de Viterbi...');
        
        // Validación robusta
        const errors = validateForViterbi();
        if (errors.length > 0) {
            const errorMsg = '❌ Errores de validación:\n• ' + errors.join('\n• ');
            console.error(errorMsg);
            showAlert(errorMsg, 'error');
            return;
        }
        
        const states = currentHMM.hiddenStates;
        const N = states.length;
        const T = observationSequence.length;

        try {
            let V = Array(T).fill(0).map(() => Array(N).fill(-Infinity));
            let path = Array(T).fill(0).map(() => Array(N).fill(0));
            
            // --- INICIALIZACIÓN ---
            const firstObs = observationSequence[0].id;
            
            if (debugMode) console.group('🔧 Inicialización (t=0)');
            
            for (let s = 0; s < N; s++) {
                const id = states[s].id;
                const pi = currentHMM.initialProbabilities[id] || 0;
                const b = currentHMM.emissionMatrix[id]?.[firstObs] || 0;
                
                if (pi > 0 && b > 0) {
                    V[0][s] = Math.log(pi) + Math.log(b);
                    if (debugMode) {
                        console.log(`${states[s].label}: π=${pi.toFixed(4)}, b=${b.toFixed(4)}, V=${Math.exp(V[0][s]).toFixed(6)}`);
                    }
                }
                path[0][s] = -1;
            }
            
            if (debugMode) console.groupEnd();

            // --- RECURSIÓN ---
            for (let t = 1; t < T; t++) {
                const obs = observationSequence[t].id;
                
                if (debugMode) console.group(`🔧 Paso t=${t} (obs: ${observationSequence[t].label})`);
                
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
                        
                        if (debugMode) {
                            console.log(`${states[s].label}: mejor_anterior=${states[bestPrev].label}, V=${Math.exp(V[t][s]).toFixed(6)}`);
                        }
                    }
                }
                
                if (debugMode) console.groupEnd();
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

            if (lastStateIdx === -1) {
                return showAlert('La secuencia es imposible con este modelo', 'error');
            }

            // --- BACKTRACKING ---
            const bestPathIndices = new Array(T);
            bestPathIndices[T-1] = lastStateIdx;
            
            for (let t = T - 2; t >= 0; t--) {
                bestPathIndices[t] = path[t+1][bestPathIndices[t+1]];
            }

            const resultSequence = bestPathIndices.map(idx => states[idx]);
            const probability = Math.exp(maxFinalLogP);
            const V_display = V.map(row => row.map(v => v === -Infinity ? 0 : Math.exp(v)));

            if (debugMode) {
                console.group('📊 Resultados Finales');
                console.log('Secuencia óptima:', resultSequence.map(s => s.label).join(' → '));
                console.log('Log-Probabilidad:', maxFinalLogP);
                console.log('Probabilidad:', probability);
                console.table(V_display);
                console.groupEnd();
            }

            // Guardar resultados para exportación
            lastViterbiResults = {
                sequence: resultSequence,
                probability: probability,
                V: V_display,
                states: states,
                logProb: maxFinalLogP
            };

            displayViterbiResults(resultSequence, probability, V_display, states, maxFinalLogP);

        } catch (e) {
            console.error('❌ Error en Viterbi:', e);
            showAlert('Error matemático: ' + e.message, 'error');
        }
    };

    // ========== EXPORTACIÓN DE RESULTADOS ==========
    window.exportViterbiResults = function(sequence, probability, V, states, logProb) {
        const results = {
            metadata: {
                timestamp: new Date().toISOString(),
                modelName: currentHMM.name,
                algorithm: 'Viterbi'
            },
            input: {
                observations: observationSequence.map(o => o.label),
                observationIds: observationSequence.map(o => o.id)
            },
            output: {
                hiddenStatePath: sequence.map(s => s.label),
                hiddenStateIds: sequence.map(s => s.id),
                logProbability: logProb,
                probability: probability
            },
            viterbiMatrix: V.map((row, t) => {
                const obj = { 
                    time: t, 
                    observation: observationSequence[t].label 
                };
                states.forEach((s, i) => {
                    obj[s.label] = row[i];
                });
                return obj;
            })
        };
        
        const blob = new Blob([JSON.stringify(results, null, 2)], {
            type: 'application/json'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `viterbi_${currentHMM.name}_${Date.now()}.json`;
        a.click();
        URL.revokeObjectURL(url);
        
        showAlert('✅ Resultados exportados correctamente', 'success');
    };

    // ========== MOSTRAR RESULTADOS VITERBI ==========
    function displayViterbiResults(sequence, probability, V, states, logProb) {
        console.log('📊 Mostrando resultados de Viterbi...');
        
        ensureResultsTab();
        activateTab('results');
        
        let container = document.getElementById('tab-results');
        
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
        
        container.style.display = 'block';
        container.classList.add('active');

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
                    <div style="display: flex; gap: 10px; justify-content: center; margin-top: 25px; flex-wrap: wrap;">
                        <button onclick="window.exportViterbiResults(
                            ${JSON.stringify(sequence).replace(/"/g, '&quot;')},
                            ${probability},
                            ${JSON.stringify(V).replace(/"/g, '&quot;')},
                            ${JSON.stringify(states).replace(/"/g, '&quot;')},
                            ${logProb}
                        )" class="btn btn-success">
                            <i class="fas fa-download"></i> Exportar Resultados JSON
                        </button>
                        <button onclick="window.runViterbi(true)" class="btn btn-info">
                            <i class="fas fa-bug"></i> Ejecutar con Debug
                        </button>
                        <button onclick="window.clearSequence()" class="btn btn-outline-secondary">
                            <i class="fas fa-eraser"></i> Limpiar Secuencia
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
        
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

    window.toggleView = () => showAlert('Vista cambiada (simulado)', 'info');
    window.fitDiagram = () => hmmDiagram && hmmDiagram.fit({ animation: true });

    window.addHiddenState = function() {
        if (!currentHMM) return showAlert('Carga un HMM primero', 'error');
        const name = prompt("Nombre del Estado Oculto:");
        if (!name) return;
        const id = name.trim().replace(/\s+/g, '_').toLowerCase();
        if (currentHMM.hiddenStates.find(s => s.id === id)) return showAlert('ID duplicado', 'error');
        
        currentHMM.hiddenStates.push({id, label: name});
        if(!currentHMM.transitionMatrix) currentHMM.transitionMatrix = {};
        currentHMM.transitionMatrix[id] = {};
        
        currentHMM.hiddenStates.forEach(s => {
            if(!currentHMM.transitionMatrix[s.id]) currentHMM.transitionMatrix[s.id] = {};
            currentHMM.transitionMatrix[s.id][id] = 0;
            currentHMM.transitionMatrix[id][s.id] = 0;
        });
        
        if(!currentHMM.emissionMatrix) currentHMM.emissionMatrix = {};
        currentHMM.emissionMatrix[id] = {};
        currentHMM.observations.forEach(o => currentHMM.emissionMatrix[id][o.id] = 0);
        
        if(!currentHMM.initialProbabilities) currentHMM.initialProbabilities = {};
        currentHMM.initialProbabilities[id] = 0;
        
        renderHMM(currentHMM);
        showAlert('Estado agregado', 'success');
    };

    window.addObservation = function() {
        if (!currentHMM) return showAlert('Carga un HMM primero', 'error');
        const name = prompt("Nombre de la Observación:");
        if (!name) return;
        const id = name.trim().replace(/\s+/g, '_').toLowerCase();
        if (currentHMM.observations.find(o => o.id === id)) return showAlert('ID duplicado', 'error');
        
        currentHMM.observations.push({id, label: name});
        if(!currentHMM.emissionMatrix) currentHMM.emissionMatrix = {};
        
        currentHMM.hiddenStates.forEach(s => {
             if(!currentHMM.emissionMatrix[s.id]) currentHMM.emissionMatrix[s.id] = {};
             currentHMM.emissionMatrix[s.id][id] = 0;
        });
        
        renderHMM(currentHMM);
        updateObservationSelector(currentHMM);
        showAlert('Observación agregada', 'success');
    };

    function updateHMMInfo(hmm) {
        const details = document.getElementById('hmm-details');
        if(details) details.innerHTML = `<h5>${hmm.name}</h5><p>${hmm.description || ''}</p>`;
        const info = document.getElementById('hmm-info');
        if(info) info.style.display = 'block';
    }

    function showNodeDetails(nodeId) { /* Detalle básico de nodos */ }
    
function displayMatrices() {
    const container = document.getElementById('matrices-container');
    if (!container || !currentHMM) return;
    
    let html = `
        <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            
            <!-- MATRIZ DE PROBABILIDADES INICIALES -->
            <div style="margin-bottom: 30px;">
                <h4 style="color: #7e22ce; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-play-circle"></i> Probabilidades Iniciales (π)
                </h4>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: center;">
                        <thead>
                            <tr style="background: #f3e8ff;">
                                <th style="padding: 12px; border: 2px solid #e5e7eb; color: #7e22ce; font-weight: bold;">Estado</th>
                                <th style="padding: 12px; border: 2px solid #e5e7eb; color: #7e22ce; font-weight: bold;">Probabilidad</th>
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    currentHMM.hiddenStates.forEach(state => {
        const prob = currentHMM.initialProbabilities[state.id] || 0;
        html += `
            <tr>
                <td style="padding: 12px; border: 2px solid #e5e7eb; font-weight: bold; background: #faf5ff; color: #581c87;">
                    ${state.label || state.id}
                </td>
                <td style="padding: 12px; border: 2px solid #e5e7eb; ${prob > 0 ? 'background: #f3e8ff; color: #581c87; font-weight: 600;' : 'background: #f9fafb; color: #d1d5db;'}">
                    ${prob.toFixed(4)}
                </td>
            </tr>
        `;
    });
    
    html += `
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- MATRIZ DE TRANSICIÓN -->
            <div style="margin-bottom: 30px;">
                <h4 style="color: #7e22ce; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exchange-alt"></i> Matriz de Transición A (Estado → Estado)
                </h4>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: center;">
                        <thead>
                            <tr style="background: #f3e8ff;">
                                <th style="padding: 12px; border: 2px solid #e5e7eb; color: #7e22ce; font-weight: bold;">Desde \\ Hacia</th>
    `;
    
    currentHMM.hiddenStates.forEach(state => {
        html += `<th style="padding: 12px; border: 2px solid #e5e7eb; color: #7e22ce; font-weight: bold;">${state.label || state.id}</th>`;
    });
    
    html += `
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    currentHMM.hiddenStates.forEach(fromState => {
        html += `<tr>`;
        html += `<td style="padding: 12px; border: 2px solid #e5e7eb; font-weight: bold; background: #faf5ff; color: #581c87;">${fromState.label || fromState.id}</td>`;
        
        currentHMM.hiddenStates.forEach(toState => {
            const prob = currentHMM.transitionMatrix[fromState.id]?.[toState.id] || 0;
            html += `<td style="padding: 12px; border: 2px solid #e5e7eb; ${prob > 0 ? 'background: #f3e8ff; color: #581c87; font-weight: 600;' : 'background: #f9fafb; color: #d1d5db;'}">${prob.toFixed(4)}</td>`;
        });
        
        html += `</tr>`;
    });
    
    html += `
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- MATRIZ DE EMISIÓN -->
            <div style="margin-bottom: 30px;">
                <h4 style="color: #7e22ce; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-broadcast-tower"></i> Matriz de Emisión B (Estado → Observación)
                </h4>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: center;">
                        <thead>
                            <tr style="background: #f3e8ff;">
                                <th style="padding: 12px; border: 2px solid #e5e7eb; color: #7e22ce; font-weight: bold;">Estado \\ Observación</th>
    `;
    
    currentHMM.observations.forEach(obs => {
        html += `<th style="padding: 12px; border: 2px solid #e5e7eb; color: #7e22ce; font-weight: bold;">${obs.label || obs.id}</th>`;
    });
    
    html += `
                            </tr>
                        </thead>
                        <tbody>
    `;
    
    currentHMM.hiddenStates.forEach(state => {
        html += `<tr>`;
        html += `<td style="padding: 12px; border: 2px solid #e5e7eb; font-weight: bold; background: #faf5ff; color: #581c87;">${state.label || state.id}</td>`;
        
        currentHMM.observations.forEach(obs => {
            const prob = currentHMM.emissionMatrix[state.id]?.[obs.id] || 0;
            html += `<td style="padding: 12px; border: 2px solid #e5e7eb; ${prob > 0 ? 'background: #f3e8ff; color: #581c87; font-weight: 600;' : 'background: #f9fafb; color: #d1d5db;'}">${prob.toFixed(4)}</td>`;
        });
        
        html += `</tr>`;
    });
    
    html += `
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Verificación de Probabilidades -->
            <div style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border: 2px solid #3b82f6; border-radius: 10px; padding: 20px;">
                <h5 style="color: #1e40af; margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i> Verificación de Sumas (deben ser ≈ 1.0)
                </h5>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
    `;
    
    // Verificar probabilidades iniciales
    const piSum = Object.values(currentHMM.initialProbabilities || {}).reduce((a, b) => a + b, 0);
    const piValid = Math.abs(piSum - 1) < 0.01;
    html += `
        <div style="background: white; padding: 12px; border-radius: 8px; border-left: 4px solid ${piValid ? '#10b981' : '#ef4444'};">
            <div style="font-size: 0.9em; color: #6b7280; margin-bottom: 5px;">Σ Probabilidades Iniciales</div>
            <div style="font-size: 1.2em; font-weight: bold; color: ${piValid ? '#059669' : '#dc2626'};">
                ${piSum.toFixed(4)} ${piValid ? '✓' : '✗'}
            </div>
        </div>
    `;
    
    // Verificar transiciones
    currentHMM.hiddenStates.forEach(state => {
        const transSum = Object.values(currentHMM.transitionMatrix[state.id] || {}).reduce((a, b) => a + b, 0);
        const transValid = Math.abs(transSum - 1) < 0.01 || transSum === 0;
        html += `
            <div style="background: white; padding: 12px; border-radius: 8px; border-left: 4px solid ${transValid ? '#10b981' : '#ef4444'};">
                <div style="font-size: 0.9em; color: #6b7280; margin-bottom: 5px;">Trans. desde ${state.label}</div>
                <div style="font-size: 1.2em; font-weight: bold; color: ${transValid ? '#059669' : '#dc2626'};">
                    ${transSum.toFixed(4)} ${transValid ? '✓' : '✗'}
                </div>
            </div>
        `;
    });
    
    // Verificar emisiones
    currentHMM.hiddenStates.forEach(state => {
        const emisSum = Object.values(currentHMM.emissionMatrix[state.id] || {}).reduce((a, b) => a + b, 0);
        const emisValid = Math.abs(emisSum - 1) < 0.01 || emisSum === 0;
        html += `
            <div style="background: white; padding: 12px; border-radius: 8px; border-left: 4px solid ${emisValid ? '#10b981' : '#ef4444'};">
                <div style="font-size: 0.9em; color: #6b7280; margin-bottom: 5px;">Emis. desde ${state.label}</div>
                <div style="font-size: 1.2em; font-weight: bold; color: ${emisValid ? '#059669' : '#dc2626'};">
                    ${emisSum.toFixed(4)} ${emisValid ? '✓' : '✗'}
                </div>
            </div>
        `;
    });
    
    html += `
                </div>
            </div>
        </div>
    `;
    
    container.innerHTML = html;
}
    function showAlert(msg, type='info') {
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        
        const div = document.createElement('div');
        div.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            padding: 15px 20px;
            background: ${colors[type]};
            color: white;
            border-radius: 8px;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
            max-width: 400px;
        `;
        div.innerHTML = `<span style="font-size: 1.2em;">${icons[type]}</span><span>${msg}</span>`;
        document.body.appendChild(div);
        
        setTimeout(() => {
            div.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => div.remove(), 300);
        }, 3000);
    }
    
    // Agregar CSS para animaciones de alertas
    if (!document.getElementById('hmm-alert-animations')) {
        const style = document.createElement('style');
        style.id = 'hmm-alert-animations';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(400px); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(400px); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
}