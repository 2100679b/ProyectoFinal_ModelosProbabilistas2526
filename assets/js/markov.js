/**
 * JavaScript para Módulo de Cadenas de Markov
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Funcionalidad Completa: Diagrama, Matriz, Simulación y Análisis
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
        states: [],          // Array de objetos: {id, label}
        transitionMatrix: {} // Objeto: { from_id: { to_id: probability } }
    };
    let markovDiagram = null;
    let selectedInitialState = null;
    let simulationHistory = [];
    
    // ✅ VARIABLE PARA INTERVALO DE AUTO-ACTUALIZACIÓN
    let analysisUpdateInterval = null;

    // ========== INICIALIZACIÓN ==========
    document.addEventListener('DOMContentLoaded', () => {
        console.log('✅ Módulo de Cadenas de Markov iniciado');
        
        // Verificar dependencia crítica: Vis.js
        if (typeof vis === 'undefined') {
            console.error('❌ ERROR: vis.js no está cargado');
            // Intentar carga de emergencia si falta
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/vis-network@latest/dist/vis-network.min.js';
            script.onload = () => {
                console.log('✅ vis.js cargado dinámicamente');
                initializeModule();
            };
            document.head.appendChild(script);
        } else {
            initializeModule();
        }
    });

    function initializeModule() {
        initTabs();
        initMarkovDiagram();
        initExampleButtons();
        
        // Si no hay cadena cargada, iniciar una nueva
        if (!currentChain.states || currentChain.states.length === 0) {
            createNewChain(false);
        }
    }

    // ========== GESTIÓN DE TABS Y AUTO-UPDATE ==========
    
    // Función para iniciar auto-actualización (cada 3 segundos)
    function startAnalysisAutoUpdate() {
        // Limpiar intervalo previo si existe
        if (analysisUpdateInterval) {
            clearInterval(analysisUpdateInterval);
        }
        
        // Actualizar cada 3 segundos si el tab de análisis está activo
        analysisUpdateInterval = setInterval(() => {
            const analysisTab = document.getElementById('tab-analysis');
            if (analysisTab && (analysisTab.classList.contains('active') || analysisTab.style.display === 'block')) {
                // Solo actualizar si NO hay un resultado de estado estacionario visible para no borrarlo
                if (!document.querySelector('.card.border-success.shadow-lg')) {
                    displayAnalysis();
                }
            }
        }, 3000);
    }

    // Función para detener auto-actualización
    function stopAnalysisAutoUpdate() {
        if (analysisUpdateInterval) {
            clearInterval(analysisUpdateInterval);
            analysisUpdateInterval = null;
        }
    }

    function initTabs() {
        document.querySelectorAll('.markov-tabs .tab-button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const tabId = btn.dataset.tab;
                if (tabId) activateTab(tabId);
            });
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
            
            // Acciones específicas al entrar a un tab
            if (tabId === 'matrix') displayTransitionMatrix();
            if (tabId === 'simulation') updateSimulationUI();
            
            // ✅ LÓGICA DE ANÁLISIS ACTUALIZADA
            if (tabId === 'analysis') {
                // Solo renderizar análisis local si está vacío
                const container = document.getElementById('analysis-container');
                if (!container || container.innerHTML.trim() === '') {
                    displayAnalysis();
                }
                startAnalysisAutoUpdate(); // Iniciar auto-update
            } else {
                stopAnalysisAutoUpdate(); // Detener al salir
            }
            
            if (tabId === 'diagram' && markovDiagram) {
                setTimeout(() => markovDiagram.fit({ animation: true }), 100);
            }
        }
    }

    // ========== DIAGRAMA (VIS.JS) ==========
    function initMarkovDiagram() {
        const container = document.getElementById('markov-visualization');
        if (!container) return;
        
        // Asegurar altura mínima
        if (container.offsetHeight === 0) container.style.height = '500px';
        
        const data = { nodes: new vis.DataSet([]), edges: new vis.DataSet([]) };
        
        const options = {
            nodes: {
                shape: 'circle',
                margin: 10,
                color: { background: '#d1fae5', border: '#059669', highlight: '#34d399' },
                font: { size: 14, color: '#064e3b', face: 'arial bold' }, // ✅ CORREGIDO
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
        
        // Eventos del diagrama
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

    // ========== GESTIÓN DE ESTRUCTURA (NODOS/ARISTAS) ==========

    window.addState = function() {
        const name = prompt("Nombre del Estado (ej. Soleado, A, 1):", "S" + (currentChain.states.length + 1));
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
        if (!currentChain.states || currentChain.states.length < 1) { 
            showAlert("Agrega estados primero.", 'warning'); 
            return; 
        }
        
        const stateList = currentChain.states.map(s => `${s.label}`).join('\n');
        
        const fromLabel = prompt(`Origen (Nombre):\n${stateList}`);
        if (!fromLabel) return;
        
        // Buscar por label o id
        const source = currentChain.states.find(s => s.label === fromLabel || s.id === fromLabel);
        if (!source) { showAlert("Estado origen no encontrado", 'error'); return; }

        const toLabel = prompt(`Destino (desde ${source.label}):`);
        if (!toLabel) return;
        const target = currentChain.states.find(s => s.label === toLabel || s.id === toLabel);
        if (!target) { showAlert("Estado destino no encontrado", 'error'); return; }

        let prob = prompt(`Probabilidad de ${source.label} -> ${target.label} (0.0 - 1.0):`, "0.5");
        if (prob === null) return;
        
        prob = parseFloat(prob);
        if (isNaN(prob) || prob < 0 || prob > 1) { 
            showAlert("Probabilidad inválida. Debe ser 0-1.", 'error'); 
            return; 
        }

        // Guardar transición
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
        
        // Eliminar Nodos
        if (sel.nodes.length > 0) {
            const nodesToRemove = sel.nodes;
            
            // Filtrar estados
            currentChain.states = currentChain.states.filter(s => !nodesToRemove.includes(s.id));
            
            // Limpiar matriz (filas)
            nodesToRemove.forEach(id => {
                delete currentChain.transitionMatrix[id];
            });
            
            // Limpiar matriz (columnas)
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

    // ✅ FUNCIÓN RENDERCHAIN ACTUALIZADA
    function renderChain() {
        if (!markovDiagram) return;

        const nodes = new vis.DataSet(currentChain.states.map(s => ({ 
            id: s.id, 
            label: s.label 
        })));
        
        const edges = new vis.DataSet();
        
        // Convertir matriz a aristas
        if (currentChain.transitionMatrix) {
            Object.keys(currentChain.transitionMatrix).forEach(fromId => {
                const transitions = currentChain.transitionMatrix[fromId];
                if (!transitions) return;

                Object.keys(transitions).forEach(toId => {
                    const prob = transitions[toId];
                    if (prob > 0) {
                        // Verificar que ambos nodos existan antes de dibujar
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
        
        // ✅ ACTUALIZAR ANÁLISIS SI EL TAB ESTÁ ACTIVO
        const analysisTab = document.getElementById('tab-analysis');
        if (analysisTab && (analysisTab.classList.contains('active') || analysisTab.style.display === 'block')) {
            displayAnalysis();
        }
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
                // Obtener valor actual o 0
                let val = 0;
                if (currentChain.transitionMatrix[from.id] && 
                    currentChain.transitionMatrix[from.id][to.id] !== undefined) {
                    val = currentChain.transitionMatrix[from.id][to.id];
                }
                rowSum += val;
                
                // Input
                html += `<td class="p-1">
                    <input type="number" class="form-control form-control-sm text-center mx-auto" 
                           style="width: 70px;"
                           step="0.1" min="0" max="1" 
                           value="${val}" 
                           onchange="updateMatrixProb('${from.id}', '${to.id}', this.value)">
                </td>`;
            });
            
            // Columna Suma
            const sumClass = Math.abs(rowSum - 1.0) < 0.01 ? 'text-success fw-bold' : 'text-danger fw-bold';
            const icon = Math.abs(rowSum - 1.0) < 0.01 ? '<i class="fas fa-check"></i>' : '<i class="fas fa-exclamation-circle"></i>';
            html += `<td class="bg-light border-start ${sumClass}">${rowSum.toFixed(2)} ${icon}</td></tr>`;
        });

        html += `</tbody></table></div>`;
        html += `<div class="mt-2 small text-muted"><i class="fas fa-info-circle"></i> Asegúrate de que todas las filas sumen 1.0.</div>`;
        
        container.innerHTML = html;
    }

    // ✅ FUNCIÓN UPDATE MATRIX ACTUALIZADA
    window.updateMatrixProb = function(fromId, toId, value) {
        let prob = parseFloat(value);
        if (isNaN(prob)) prob = 0;
        if (prob < 0) prob = 0;
        if (prob > 1) prob = 1;
        
        if (!currentChain.transitionMatrix[fromId]) {
            currentChain.transitionMatrix[fromId] = {};
        }
        
        currentChain.transitionMatrix[fromId][toId] = prob;
        
        // Actualizar visualmente la matriz
        displayTransitionMatrix();
        
        // ✅ ACTUALIZAR ANÁLISIS SI EL TAB ESTÁ ACTIVO
        const analysisTab = document.getElementById('tab-analysis');
        if (analysisTab && (analysisTab.classList.contains('active') || analysisTab.style.display === 'block')) {
            displayAnalysis();
        }
    };

    // ========== ANÁLISIS Y ESTADO ESTACIONARIO ==========

    // --- CÁLCULO EN SERVIDOR (PHP) ---
    window.calculateSteadyState = function() {
        // Buscar contenedor
        let container = document.getElementById('analysis-container');
        
        if (!container) {
            // Intentar activar el tab primero
            activateTab('analysis');
            setTimeout(() => {
                container = document.getElementById('analysis-container');
                if (container) {
                    calculateSteadyState();
                }
            }, 200);
            return;
        }

        // Validar que hay estados
        if (!currentChain.states || currentChain.states.length === 0) {
            container.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>No hay estados</strong><br>
                    Carga un ejemplo o crea estados primero.
                </div>
            `;
            return;
        }

        // Validar que la matriz es estocástica
        const validationErrors = [];
        currentChain.states.forEach(state => {
            const stateId = state.id;
            if (!currentChain.transitionMatrix[stateId]) {
                validationErrors.push(`Estado "${state.label}" no tiene transiciones`);
                return;
            }
            
            let sum = 0;
            Object.values(currentChain.transitionMatrix[stateId]).forEach(prob => {
                sum += prob;
            });
            
            if (Math.abs(sum - 1.0) > 0.01) {
                validationErrors.push(`Estado "${state.label}" suma ${sum.toFixed(4)} (debe ser 1.0)`);
            }
        });

        if (validationErrors.length > 0) {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-times-circle"></i> Matriz no válida</h5>
                    <p>La matriz de transición tiene errores:</p>
                    <ul class="mb-0">
                        ${validationErrors.map(err => `<li>${err}</li>`).join('')}
                    </ul>
                    <hr>
                    <p class="mb-0">
                        <strong>Solución:</strong> Ve a la pestaña "Matriz" y ajusta las probabilidades 
                        para que cada fila sume exactamente 1.0
                    </p>
                </div>
            `;
            return;
        }

        // Mostrar loader
        container.innerHTML = `
            <div class="text-center p-5">
                <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Calculando...</span>
                </div>
                <p class="mt-3 text-muted">Calculando estado estacionario (Servidor)...</p>
            </div>
        `;

        // Construir URL
        const baseUrl = document.body.dataset.baseUrl || window.location.origin;
        const cleanBaseUrl = baseUrl.endsWith('/') ? baseUrl.slice(0, -1) : baseUrl;
        const url = `${cleanBaseUrl}/modules/markov/stationary.php`;

        console.log('📡 Enviando petición a:', url);
        console.log('📦 Datos:', currentChain);

        fetch(url, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(currentChain)
        })
        .then(response => {
            console.log('📥 Response status:', response.status);
            console.log('📥 Content-Type:', response.headers.get('content-type'));
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            return response.text();
        })
        .then(text => {
            console.log('📄 Response:', text.substring(0, 500));
            
            // Verificar si es HTML (error del servidor)
            if (text.trim().startsWith('<')) {
                throw new Error('SERVER_RETURNED_HTML');
            }
            
            // Parsear JSON
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('❌ Error parseando JSON:', e);
                console.error('Respuesta completa:', text);
                throw new Error('INVALID_JSON: ' + text.substring(0, 100));
            }
        })
        .then(data => {
            console.log('✅ Datos recibidos:', data);
            
            if (!data.success) {
                throw new Error(data.error || 'Error desconocido del servidor');
            }
            
            renderSteadyStateResults(data, container);
            showAlert('Estado estacionario calculado (Servidor)', 'success');
        })
        .catch(error => {
            console.error('❌ Error:', error);
            
            let errorHTML = '';
            
            if (error.message === 'SERVER_RETURNED_HTML') {
                errorHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-circle"></i> Error del Servidor</h5>
                        <p>El archivo <code>stationary.php</code> no existe o hay un error de configuración.</p>
                        <p><strong>Ubicación esperada:</strong> <code>modules/markov/stationary.php</code></p>
                        <hr>
                        <p class="mb-0">
                            <button class="btn btn-outline-danger btn-sm" onclick="calculateSteadyStateClient()">
                                <i class="fas fa-calculator"></i> Probar cálculo local (Cliente)
                            </button>
                        </p>
                    </div>
                `;
            } else if (error.message.includes('Failed to fetch')) {
                errorHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-wifi"></i> Error de Conexión</h5>
                        <p>No se pudo conectar con el servidor.</p>
                        <hr>
                        <p class="mb-0">
                            <button class="btn btn-outline-danger btn-sm" onclick="calculateSteadyStateClient()">
                                <i class="fas fa-calculator"></i> Probar cálculo local (Cliente)
                            </button>
                        </p>
                    </div>
                `;
            } else {
                errorHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-times-circle"></i> Error</h5>
                        <p><strong>${error.message}</strong></p>
                    </div>
                `;
            }
            
            container.innerHTML = errorHTML;
            
            // Mostrar análisis local después de 4 segundos
            setTimeout(() => {
                displayLocalAnalysis(container);
            }, 4000);
        });
    };

    // ========== CÁLCULO DE ESTADO ESTACIONARIO EN EL CLIENTE ==========
    window.calculateSteadyStateClient = function() {
        let container = document.getElementById('analysis-container');
        
        if (!container) {
            activateTab('analysis');
            setTimeout(() => {
                container = document.getElementById('analysis-container');
                if (container) calculateSteadyStateClient();
            }, 200);
            return;
        }

        // Validar que hay estados
        if (!currentChain.states || currentChain.states.length === 0) {
            container.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>No hay estados</strong><br>
                    Carga un ejemplo o crea estados primero.
                </div>
            `;
            return;
        }

        // Validar matriz estocástica
        const validationErrors = [];
        currentChain.states.forEach(state => {
            const stateId = state.id;
            if (!currentChain.transitionMatrix[stateId]) {
                validationErrors.push(`Estado "${state.label}" no tiene transiciones`);
                return;
            }
            
            let sum = 0;
            Object.values(currentChain.transitionMatrix[stateId]).forEach(prob => {
                sum += prob;
            });
            
            if (Math.abs(sum - 1.0) > 0.01) {
                validationErrors.push(`Estado "${state.label}" suma ${sum.toFixed(4)} (debe ser 1.0)`);
            }
        });

        if (validationErrors.length > 0) {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="fas fa-times-circle"></i> Matriz no válida</h5>
                    <p>La matriz de transición tiene errores:</p>
                    <ul class="mb-0">
                        ${validationErrors.map(err => `<li>${err}</li>`).join('')}
                    </ul>
                    <hr>
                    <p class="mb-0">
                        <strong>Solución:</strong> Ve a la pestaña "Matriz" y ajusta las probabilidades 
                        para que cada fila sume exactamente 1.0
                    </p>
                </div>
            `;
            return;
        }

        // Mostrar loader
        container.innerHTML = `
            <div class="text-center p-5">
                <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Calculando...</span>
                </div>
                <p class="mt-3 text-muted">Calculando estado estacionario (cliente)...</p>
            </div>
        `;

        // Ejecutar cálculo con un pequeño delay para que se vea el loader
        setTimeout(() => {
            const result = computeSteadyStateClient(currentChain);
            
            if (!result.success) {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i> 
                        <strong>Error:</strong> ${result.error}
                    </div>
                `;
                return;
            }

            // Agregar método al resultado
            result.method = 'power_iteration';
            
            // Renderizar resultados
            renderSteadyStateResults(result, container);
            showAlert('Estado estacionario calculado (cliente)', 'success');
        }, 300);
    };

    function computeSteadyStateClient(chain) {
        // Implementación del Método de las Potencias (Power Iteration)
        const states = chain.states;
        const n = states.length;
        if (n === 0) return { success: false, error: "Sin estados" };

        // 1. Mapear IDs a índices de matriz 0..n-1
        const idToIndex = {};
        states.forEach((s, i) => idToIndex[s.id] = i);

        // 2. Construir matriz P (n x n)
        let P = Array(n).fill(0).map(() => Array(n).fill(0));
        
        Object.keys(chain.transitionMatrix).forEach(fromId => {
            const rowIdx = idToIndex[fromId];
            if (rowIdx === undefined) return;
            const transitions = chain.transitionMatrix[fromId];
            
            Object.keys(transitions).forEach(toId => {
                const colIdx = idToIndex[toId];
                if (colIdx !== undefined) {
                    P[rowIdx][colIdx] = parseFloat(transitions[toId]);
                }
            });
        });

        // 3. Validar Estocasticidad y normalizar
        for(let i = 0; i < n; i++) {
            let sum = P[i].reduce((a, b) => a + b, 0);
            if(sum === 0) {
                // Estado absorbente (se queda en sí mismo)
                P[i][i] = 1.0;
            } else if (Math.abs(sum - 1.0) > 0.0001) {
                // Normalizar para que sume exactamente 1.0
                P[i] = P[i].map(v => v / sum);
            }
        }

        // 4. Algoritmo iterativo: v_new = v_old * P
        let v = Array(n).fill(1 / n); // Distribución inicial uniforme
        const maxIter = 2000;
        const epsilon = 1e-8;
        let iter = 0;
        let converged = false;

        while(iter < maxIter) {
            let vNext = Array(n).fill(0);
            
            // Multiplicación Vector(fila) x Matriz: vNext[j] = sum(v[i] * P[i][j])
            for(let j = 0; j < n; j++) { // Para cada columna destino
                for(let i = 0; i < n; i++) { // Desde cada fila origen
                    vNext[j] += v[i] * P[i][j];
                }
            }

            // Checar convergencia (norma L1 de la diferencia)
            let diff = 0;
            for(let i = 0; i < n; i++) {
                diff += Math.abs(vNext[i] - v[i]);
            }
            
            v = vNext;
            
            if(diff < epsilon) {
                converged = true;
                break;
            }
            iter++;
        }

        // 5. Normalizar resultado final (por seguridad)
        const sum = v.reduce((a, b) => a + b, 0);
        if (sum > 0 && Math.abs(sum - 1.0) > 0.0001) {
            v = v.map(val => val / sum);
        }

        // 6. Crear distribución ordenada por probabilidad
        const distribution = states.map((s, i) => ({
            state: s.label,
            id: s.id,
            probability: v[i]
        }));

        // Ordenar descendente por probabilidad
        distribution.sort((a, b) => b.probability - a.probability);

        return {
            success: true,
            iterations: iter + 1,
            converged: converged,
            distribution: distribution
        };
    }
    // -------------------------------------------------------------

    function renderSteadyStateResults(data, container) {
        if (!data.distribution || data.distribution.length === 0) {
            container.innerHTML = '<div class="alert alert-warning">No hay distribución para mostrar</div>';
            return;
        }

        let html = `
            <div class="card border-success shadow-lg mb-4">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                    <h4 class="mb-0">
                        <i class="fas fa-balance-scale"></i> 
                        Estado Estacionario (Largo Plazo)
                    </h4>
                </div>
                <div class="card-body" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle"></i> 
                                Convergió en ${data.iterations} iteraciones
                            </span>
                            <span class="badge bg-info ms-2">
                                Método: ${data.method === 'power_iteration' ? 'Iteración de Potencias' : 'Desconocido'}
                            </span>
                        </div>
                        <button onclick="displayAnalysis()" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-arrow-left"></i> Volver
                        </button>
                    </div>
                    
                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle"></i> 
                        Probabilidades límite después de un tiempo muy largo:
                    </p>
                    
                    <div class="row g-3">
        `;
        
        // Renderizar cada estado
        data.distribution.forEach((item, index) => {
            const name = item.state || item.label || 'Estado';
            const prob = parseFloat(item.probability || 0);
            const pct = (prob * 100).toFixed(2);
            
            // Gradiente de color según probabilidad
            let colorClass = 'success';
            let bgGradient = 'linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%)';
            
            if (prob < 0.2) {
                colorClass = 'secondary';
                bgGradient = 'linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%)';
            } else if (prob < 0.4) {
                colorClass = 'info';
                bgGradient = 'linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%)';
            } else if (prob < 0.6) {
                colorClass = 'primary';
                bgGradient = 'linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%)';
            }
            
            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-${colorClass}" style="background: ${bgGradient};">
                        <div class="card-body text-center">
                            <h5 class="card-title text-${colorClass} mb-3">
                                <i class="fas fa-circle" style="font-size: 0.6em;"></i>
                                ${name}
                            </h5>
                            <h2 class="display-4 fw-bold text-${colorClass} mb-2">
                                ${pct}%
                            </h2>
                            <p class="text-muted mb-3">
                                <small>Probabilidad: ${prob.toFixed(6)}</small>
                            </p>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-${colorClass}" 
                                      role="progressbar" 
                                      style="width: ${pct}%;" 
                                      aria-valuenow="${pct}" 
                                      aria-valuemin="0" 
                                      aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
                    </div>
                    
                    <div class="mt-4 p-3 bg-white rounded border border-success">
                        <h6 class="text-success mb-2">
                            <i class="fas fa-lightbulb"></i> Interpretación
                        </h6>
                        <p class="mb-0 small text-muted">
                            Después de muchas transiciones, el sistema pasará aproximadamente 
                            <strong>${(data.distribution[0].probability * 100).toFixed(1)}%</strong> 
                            del tiempo en el estado 
                            <strong>${data.distribution[0].state}</strong>, 
                            independientemente del estado inicial.
                        </p>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
        
        // Activar el tab de análisis si no está activo
        activateTab('analysis');
    }

    function displayAnalysis() {
        let container = document.getElementById('analysis-container');
        
        if (!container) {
            console.warn('⚠️ No se encontró #analysis-container, buscando alternativa...');
            // Intentar buscar en el tab
            container = document.querySelector('#tab-analysis');
            if (container) {
                // Si encontramos el tab pero no el container específico, creamos uno o usamos el tab directamente si está vacío
                if (!container.querySelector('#analysis-container')) {
                    const newDiv = document.createElement('div');
                    newDiv.id = 'analysis-container';
                    container.appendChild(newDiv);
                    container = newDiv;
                } else {
                    container = container.querySelector('#analysis-container');
                }
            } else {
                console.error('❌ No se encontró el tab de análisis');
                return;
            }
        }
        
        // MODIFICADO: Siempre actualizar el análisis local al entrar
        displayLocalAnalysis(container);
    }
    // Hacer displayAnalysis global para que el botón funcione
    window.displayAnalysis = displayAnalysis;

    function displayLocalAnalysis(container) {
        if (!currentChain.states || currentChain.states.length === 0) {
            container.innerHTML = `
                <div style="padding:60px 20px;text-align:center;background:linear-gradient(135deg,#f9fafb 0%,#f3f4f6 100%);border-radius:16px;box-shadow:0 4px 12px rgba(0,0,0,0.05);margin:20px;">
                    <i class="fas fa-info-circle" style="font-size:4em;color:#d1d5db;margin-bottom:20px;"></i>
                    <h3 style="color:#6b7280;margin-bottom:10px;">No hay cadena para analizar</h3>
                    <p style="color:#9ca3af;font-size:1.1em;">Carga un ejemplo o crea una nueva cadena de Markov</p>
                </div>
            `;
            return;
        }

        const stateCount = currentChain.states.length;
        let transitionCount = 0;
        
        if (currentChain.transitionMatrix) {
            Object.values(currentChain.transitionMatrix).forEach(row => {
                Object.values(row).forEach(p => { 
                    if(p > 0) transitionCount++; 
                });
            });
        }

        // Calcular análisis local aproximado
        const analysis = analyzeChainLocally();

        let html = `
            <div style="padding:20px;">
                <!-- Título principal con BOTÓN DE ACTUALIZAR MANUAL -->
                <div style="background:linear-gradient(135deg,#059669 0%,#047857 100%);color:#fff;padding:30px;border-radius:16px;margin-bottom:30px;box-shadow:0 8px 24px rgba(5,150,105,0.3);">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <h2 style="margin:0;display:flex;align-items:center;gap:12px;font-size:2em;">
                                <i class="fas fa-chart-line"></i> 
                                Análisis de la Cadena de Markov
                            </h2>
                            <p style="margin:10px 0 0 0;opacity:0.9;font-size:1.1em;">
                                Propiedades y características de la cadena
                            </p>
                        </div>
                        <button onclick="displayAnalysis()" class="btn btn-sm btn-light text-success shadow-sm" style="font-weight:bold; padding: 8px 16px;">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>

                <!-- Resumen General -->
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;">
                    <div style="background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);padding:20px;border-radius:12px;text-align:center;border:2px solid #059669;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                        <i class="fas fa-circle" style="font-size:2.5em;color:#059669;margin-bottom:10px;"></i>
                        <div style="font-size:2.5em;font-weight:bold;color:#047857;">${stateCount}</div>
                        <div style="color:#065f46;margin-top:5px;font-weight:600;">Estados Totales</div>
                    </div>
                    <div style="background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%);padding:20px;border-radius:12px;text-align:center;border:2px solid #3b82f6;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                        <i class="fas fa-arrows-alt" style="font-size:2.5em;color:#3b82f6;margin-bottom:10px;"></i>
                        <div style="font-size:2.5em;font-weight:bold;color:#1e40af;">${transitionCount}</div>
                        <div style="color:#1e3a8a;margin-top:5px;font-weight:600;">Transiciones</div>
                    </div>
                    <div style="background:linear-gradient(135deg,#fef3c7 0%,#fde68a 100%);padding:20px;border-radius:12px;text-align:center;border:2px solid #f59e0b;box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                        <i class="fas fa-network-wired" style="font-size:2.5em;color:#f59e0b;margin-bottom:10px;"></i>
                        <div style="font-size:1.5em;font-weight:bold;color:#d97706;">${currentChain.domain || 'General'}</div>
                        <div style="color:#92400e;margin-top:5px;font-weight:600;">Dominio</div>
                    </div>
                </div>

                <!-- Clasificación de Estados -->
                <div style="background:#fff;border-radius:16px;padding:25px;box-shadow:0 6px 20px rgba(0,0,0,0.1);margin-bottom:30px;">
                    <h3 style="color:#047857;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:1.6em;border-bottom:3px solid #d1fae5;padding-bottom:15px;">
                        <i class="fas fa-project-diagram"></i> 
                        Clasificación de Estados
                    </h3>
                    ${analysis.stateClassifications}
                </div>

                <!-- Propiedades de la Cadena -->
                <div style="background:#fff;border-radius:16px;padding:25px;box-shadow:0 6px 20px rgba(0,0,0,0.1);margin-bottom:30px;">
                    <h3 style="color:#047857;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:1.6em;border-bottom:3px solid #d1fae5;padding-bottom:15px;">
                        <i class="fas fa-clipboard-check"></i> 
                        Propiedades de la Cadena
                    </h3>
                    ${analysis.properties}
                </div>

                <!-- Distribución Aproximada -->
                <div style="background:#fff;border-radius:16px;padding:25px;box-shadow:0 6px 20px rgba(0,0,0,0.1);">
                    <h3 style="color:#047857;margin-bottom:10px;display:flex;align-items:center;gap:10px;font-size:1.6em;">
                        <i class="fas fa-balance-scale"></i> 
                        Distribución Aproximada
                    </h3>
                    <p style="color:#6b7280;margin-bottom:20px;font-size:1.05em;padding-bottom:15px;border-bottom:3px solid #d1fae5;">
                        Estimación basada en análisis de transiciones
                    </p>
                    ${analysis.distribution}
                    <div style="margin-top:20px;text-align:center;">
                        <button class="btn btn-primary btn-lg me-2" onclick="calculateSteadyStateClient()" style="background:linear-gradient(135deg,#059669 0%,#047857 100%);border:none;padding:12px 30px;box-shadow:0 4px 12px rgba(5,150,105,0.3);">
                            <i class="fas fa-calculator"></i> Calcular (Cliente)
                        </button>
                        <button class="btn btn-outline-primary btn-lg" onclick="calculateSteadyState()" style="padding:12px 30px;">
                            <i class="fas fa-server"></i> Calcular (Servidor PHP)
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
    }

    // ✅ NUEVA FUNCIÓN AÑADIDA
    function verifyStochasticMatrix() {
        if (!currentChain.transitionMatrix) return false;
        
        for (const state of currentChain.states) {
            const stateId = state.id;
            if (!currentChain.transitionMatrix[stateId]) continue;
            
            // Sumar todas las probabilidades de salida
            let sum = 0;
            Object.values(currentChain.transitionMatrix[stateId]).forEach(prob => {
                sum += prob;
            });
            
            // Verificar que la suma sea aproximadamente 1.0 (tolerancia de 0.01)
            if (Math.abs(sum - 1.0) > 0.01) {
                return false;
            }
        }
        
        return true;
    }

    function analyzeChainLocally() {
        const states = currentChain.states;
        const matrix = currentChain.transitionMatrix;
        
        // Clasificación de estados
        let stateClassifications = '';
        
        states.forEach((state, idx) => {
            const stateId = state.id;
            const outgoing = matrix[stateId] ? Object.values(matrix[stateId]).filter(p => p > 0).length : 0;
            const selfLoop = matrix[stateId]?.[stateId] || 0;
            
            // Contar transiciones entrantes
            let incoming = 0;
            states.forEach(s => {
                if (matrix[s.id] && matrix[s.id][stateId] > 0) {
                    incoming++;
                }
            });
            
            // Clasificar tipo de estado
            let stateType = '';
            let typeColor = '';
            let typeIcon = '';
            
            if (outgoing === 0) {
                stateType = 'Estado Absorbente';
                typeColor = '#dc2626';
                typeIcon = 'fa-stop-circle';
            } else if (selfLoop > 0 && selfLoop === 1) {
                stateType = 'Estado Absorbente';
                typeColor = '#dc2626';
                typeIcon = 'fa-stop-circle';
            } else if (selfLoop > 0) {
                stateType = 'Estado Recurrente';
                typeColor = '#059669';
                typeIcon = 'fa-sync-alt';
            } else {
                stateType = 'Estado Transitorio';
                typeColor = '#3b82f6';
                typeIcon = 'fa-share';
            }
            
            const bgGradient = idx % 2 === 0 ? 
                'linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%)' : 
                'linear-gradient(135deg, #ffffff 0%, #f9fafb 100%)';
            
            stateClassifications += `
                <div style="background:${bgGradient};padding:20px;border-radius:12px;margin-bottom:15px;border-left:6px solid ${typeColor};box-shadow:0 2px 8px rgba(0,0,0,0.05);">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                        <div>
                            <h4 style="color:#047857;margin:0;font-size:1.3em;display:flex;align-items:center;gap:10px;">
                                <i class="fas fa-circle" style="color:${typeColor};font-size:0.6em;"></i>
                                ${state.label}
                            </h4>
                            <span style="display:inline-block;background:${typeColor};color:#fff;padding:4px 12px;border-radius:20px;font-size:0.9em;margin-top:8px;">
                                <i class="fas ${typeIcon}"></i> ${stateType}
                            </span>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:15px;margin-top:15px;">
                        <div style="background:#fff;padding:12px;border-radius:8px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                            <i class="fas fa-arrow-right" style="color:#059669;font-size:1.5em;"></i>
                            <div style="font-size:1.8em;font-weight:bold;color:#047857;margin-top:5px;">${outgoing}</div>
                            <div style="color:#6b7280;font-size:0.9em;">Salientes</div>
                        </div>
                        <div style="background:#fff;padding:12px;border-radius:8px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                            <i class="fas fa-arrow-left" style="color:#3b82f6;font-size:1.5em;"></i>
                            <div style="font-size:1.8em;font-weight:bold;color:#1e40af;margin-top:5px;">${incoming}</div>
                            <div style="color:#6b7280;font-size:0.9em;">Entrantes</div>
                        </div>
                        <div style="background:#fff;padding:12px;border-radius:8px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                            <i class="fas fa-sync-alt" style="color:#f59e0b;font-size:1.5em;"></i>
                            <div style="font-size:1.8em;font-weight:bold;color:#d97706;margin-top:5px;">${selfLoop.toFixed(2)}</div>
                            <div style="color:#6b7280;font-size:0.9em;">Auto-bucle</div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        // Propiedades de la cadena
        const isStochastic = verifyStochasticMatrix();
        const isIrreducible = checkIrreducibility();
        const isAperiodic = checkAperiodicity();
        
        const propertyData = [
            { 
                name: 'Estocástica', 
                value: isStochastic, 
                description: 'Todas las filas de la matriz suman 1',
                icon: 'fa-check-double',
                trueColor: '#10b981',
                falseColor: '#ef4444'
            },
            { 
                name: 'Irreducible', 
                value: isIrreducible, 
                description: 'Todos los estados son accesibles entre sí',
                icon: 'fa-link',
                trueColor: '#3b82f6',
                falseColor: '#f59e0b'
            },
            { 
                name: 'Aperiódica', 
                value: isAperiodic, 
                description: 'No tiene ciclos periódicos regulares',
                icon: 'fa-random',
                trueColor: '#8b5cf6',
                falseColor: '#f59e0b'
            }
        ];
        
        let properties = '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;">';
        propertyData.forEach(prop => {
            const color = prop.value ? prop.trueColor : prop.falseColor;
            const icon = prop.value ? 'fa-check-circle' : 'fa-times-circle';
            const status = prop.value ? '✓ Sí' : '✗ No';
            
            properties += `
                <div style="background:linear-gradient(135deg,${color}15 0%,${color}25 100%);padding:20px;border-radius:12px;border:2px solid ${color};box-shadow:0 4px 12px rgba(0,0,0,0.08);">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                        <i class="fas ${prop.icon}" style="font-size:2em;color:${color};"></i>
                        <h4 style="color:${color};margin:0;font-size:1.3em;">${prop.name}</h4>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                        <i class="fas ${icon}" style="color:${color};font-size:1.5em;"></i>
                        <span style="font-size:1.4em;font-weight:bold;color:${color};">${status}</span>
                    </div>
                    <p style="color:#6b7280;margin:0;font-size:0.95em;">${prop.description}</p>
                </div>
            `;
        });
        properties += '</div>';
        
        // Distribución aproximada
        const approxDist = calculateApproximateDistribution();
        let distribution = '<div style="background:#f9fafb;padding:20px;border-radius:12px;border:2px solid #d1fae5;">';
        
        Object.entries(approxDist).forEach(([stateId, prob]) => {
            const state = states.find(s => s.id === stateId);
            const label = state ? state.label : stateId;
            const pct = (prob * 100).toFixed(2);
            
            distribution += `
                <div style="margin-bottom:20px;background:#fff;padding:15px;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,0.05);">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <span style="color:#047857;font-weight:700;font-size:1.2em;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-circle" style="color:#10b981;font-size:0.5em;"></i>
                            ${label}
                        </span>
                        <span style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;padding:6px 16px;border-radius:20px;font-weight:bold;font-size:1.1em;">
                            ${prob.toFixed(4)} <span style="opacity:0.8;font-size:0.9em;">(${pct}%)</span>
                        </span>
                    </div>
                    <div style="background:#e5e7eb;border-radius:8px;height:12px;overflow:hidden;box-shadow:inset 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="background:linear-gradient(90deg,#10b981 0%,#059669 100%);width:${pct}%;height:100%;transition:width 0.8s ease;box-shadow:0 0 8px rgba(16,185,129,0.5);"></div>
                    </div>
                </div>
            `;
        });
        distribution += '</div>';
        
        return {
            stateClassifications,
            properties,
            distribution
        };
    }

    function checkIrreducibility() {
        // Simplificación: verificar si todos los estados tienen transiciones salientes
        for (const state of currentChain.states) {
            if (!currentChain.transitionMatrix[state.id] || 
                Object.values(currentChain.transitionMatrix[state.id]).filter(p => p > 0).length === 0) {
                return false;
            }
        }
        return true;
    }

    function checkAperiodicity() {
        // Simplificación: si algún estado tiene auto-bucle, es aperiódica
        for (const state of currentChain.states) {
            if (currentChain.transitionMatrix[state.id] && 
                currentChain.transitionMatrix[state.id][state.id] > 0) {
                return true;
            }
        }
        return false;
    }

    function calculateApproximateDistribution() {
        const states = currentChain.states;
        const matrix = currentChain.transitionMatrix;
        const n = states.length;
        const result = {};
        
        // Método simple: probabilidad basada en transiciones entrantes
        states.forEach(state => {
            let incomingProb = 0;
            states.forEach(fromState => {
                if (matrix[fromState.id] && matrix[fromState.id][state.id]) {
                    incomingProb += matrix[fromState.id][state.id];
                }
            });
            result[state.id] = incomingProb / n || 1/n;
        });
        
        // Normalizar
        const sum = Object.values(result).reduce((a, b) => a + b, 0);
        if (sum > 0) {
            Object.keys(result).forEach(id => {
                result[id] = result[id] / sum;
            });
        }
        
        return result;
    }

    // ========== SIMULACIÓN (CLIENTE) ==========

    window.simulateSteps = function() {
        activateTab('simulation');
    };

    function updateSimulationUI() {
        updateStateSelectors();
    }

    function updateStateSelectors() {
        const select = document.getElementById('initial-state-selector');
        if (!select) return;
        
        select.innerHTML = '';
        if (!currentChain.states || currentChain.states.length === 0) {
            select.innerHTML = '<p class="text-muted">Sin estados disponibles</p>';
            return;
        }

        currentChain.states.forEach(s => {
            const btn = document.createElement('button');
            btn.className = 'btn btn-outline-primary btn-sm m-1';
            btn.innerText = s.label;
            
            if (selectedInitialState === s.id) {
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
            }
            
            btn.onclick = () => {
                // Limpiar selección previa
                const allBtns = select.querySelectorAll('button');
                allBtns.forEach(b => {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-primary');
                });
                // Activar actual
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
                
                selectedInitialState = s.id;
                showAlert('Estado inicial: ' + s.label, 'info');
            };
            select.appendChild(btn);
        });
    }

    window.runSimulation = function() {
        if (!selectedInitialState) {
            showAlert("Selecciona un estado inicial.", 'warning');
            return;
        }
        
        const stepsInput = document.getElementById('num-steps');
        const steps = parseInt(stepsInput ? stepsInput.value : 10) || 10;
        
        // Buscar el contenedor específico de simulación
        let container = document.getElementById('simulation-results');
        
        // Si no existe, crearlo explícitamente en el tab de simulación
        if (!container) {
            const tabSim = document.getElementById('tab-simulation');
            if (tabSim) {
                container = document.createElement('div');
                container.id = 'simulation-results';
                container.style.marginTop = '20px';
                tabSim.appendChild(container);
            } else {
                console.error("No se encontró el tab de simulación");
                return;
            }
        }
        
        // Simulación Monte Carlo
        let current = selectedInitialState;
        let history = [current];
        
        for (let i = 0; i < steps; i++) {
            const transitions = currentChain.transitionMatrix[current];
            // Si es absorbente o sin salidas
            if (!transitions || Object.keys(transitions).length === 0) break; 
            
            const rand = Math.random();
            let cumulative = 0;
            let next = current; // Default quedarse si no suma 1
            
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
        
        // Renderizar Historial
        const labels = history.map(id => {
            const s = currentChain.states.find(st => st.id === id);
            return s ? s.label : id;
        });

        let html = `<div class="card shadow-sm"><div class="card-header bg-dark text-white">Trayectoria (${steps} pasos)</div><div class="card-body">`;
        html += `<div class="d-flex flex-wrap align-items-center justify-content-center">`;
        
        labels.forEach((label, idx) => {
            html += `
                <div class="text-center mx-2 mb-3">
                    <span class="badge bg-primary rounded-pill fs-6 p-2 shadow-sm">${label}</span>
                    <div class="small text-muted mt-1">t=${idx}</div>
                </div>
            `;
            if(idx < labels.length - 1) html += `<i class="fas fa-arrow-right text-muted mb-4 mx-2"></i>`;
        });
        
        html += `</div></div></div>`;
        
        // Si hay contenedor, ponerlo
        if(container) {
            container.innerHTML = html;
        }
        
        showAlert("Simulación completada", 'success');
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
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };

    function initExampleButtons() {
        document.querySelectorAll('.btn-example').forEach(btn => {
            btn.addEventListener('click', () => loadExample(btn.dataset.example));
        });
    }

    function loadExample(type) {
        if (currentChain.states.length > 0 && !confirm("Cargar ejemplo borrará el trabajo actual. ¿Continuar?")) return;
        
        // Uso rutas relativas para los JSON de ejemplo
        const url = `./examples/${type}.json`;
        
        // Si no existe el archivo, usamos datos hardcoded para garantizar funcionamiento
        let exampleData = null;

        if (type === 'weather') {
            exampleData = {
                name: "Predicción del Clima",
                states: [
                    {id:'sol', label:'Soleado'}, 
                    {id:'nube', label:'Nublado'}, 
                    {id:'lluvia', label:'Lluvia'}
                ],
                transitionMatrix: {
                    'sol': {'sol': 0.7, 'nube': 0.2, 'lluvia': 0.1},
                    'nube': {'sol': 0.3, 'nube': 0.4, 'lluvia': 0.3},
                    'lluvia': {'sol': 0.2, 'nube': 0.3, 'lluvia': 0.5}
                }
            };
        } else if (type === 'stock_market' || type === 'market') {
            exampleData = {
                name: "Mercado de Valores",
                states: [
                    {id:'alza', label:'Alza (Bull)'}, 
                    {id:'baja', label:'Baja (Bear)'}, 
                    {id:'est', label:'Estable'}
                ],
                transitionMatrix: {
                    'alza': {'alza': 0.9, 'baja': 0.075, 'est': 0.025},
                    'baja': {'alza': 0.15, 'baja': 0.8, 'est': 0.05},
                    'est': {'alza': 0.25, 'baja': 0.25, 'est': 0.5}
                }
            };
        } else if (type === 'text_generation') {
            exampleData = {
                name: "Generación de Texto",
                states: [
                    {id:'yo', label:'Yo'}, 
                    {id:'quiero', label:'Quiero'}, 
                    {id:'comer', label:'Comer'},
                    {id:'dormir', label:'Dormir'}
                ],
                transitionMatrix: {
                    'yo': {'quiero': 1.0},
                    'quiero': {'comer': 0.6, 'dormir': 0.4},
                    'comer': {'yo': 0.5, 'dormir': 0.5},
                    'dormir': {'yo': 0.8, 'quiero': 0.2}
                }
            };
        } else if (type === 'customer_behavior') {
            exampleData = {
                name: "Comportamiento del Cliente",
                states: [
                    {id:'visita', label:'Visita'}, 
                    {id:'compra', label:'Compra'}, 
                    {id:'salida', label:'Salida'}
                ],
                transitionMatrix: {
                    'visita': {'visita': 0.2, 'compra': 0.3, 'salida': 0.5},
                    'compra': {'visita': 0.1, 'compra': 0.4, 'salida': 0.5},
                    'salida': {'salida': 1.0}
                }
            };
        }

        if (exampleData) {
            currentChain = exampleData;
            renderChain();
            activateTab('diagram');
            
            // ✅ ACTUALIZAR ANÁLISIS SI EL TAB ESTÁ ACTIVO
            const analysisTab = document.getElementById('tab-analysis');
            if (analysisTab && (analysisTab.classList.contains('active') || analysisTab.style.display === 'block')) {
                displayAnalysis();
            }
            
            showAlert(`Ejemplo "${currentChain.name}" cargado`, 'success');
        } else {
            alert("Ejemplo no definido: " + type);
        }
    }

    // ========== INFO Y UTILS ==========
    function updateChainInfo(chain) {
        const info = document.getElementById('state-info');
        const details = document.getElementById('state-details');
        if (!info || !details) return;
        
        info.style.display = 'block';
    }

    function showStateDetails(id) {
        if (!currentChain) return;
        const state = currentChain.states.find(s => s.id === id);
        if (!state) return;
        
        const info = document.getElementById('state-info');
        const details = document.getElementById('state-details');
        if (!info || !details) return;
        
        info.style.display = 'block';
        let html = `
            <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);padding:15px;border-radius:8px;border:1px solid #059669;">
                <h5 style="color:#047857;margin-bottom:5px;">${state.label || state.id}</h5>
                <small class="text-muted">ID: ${state.id}</small>
        `;
        
        if (currentChain.transitionMatrix && currentChain.transitionMatrix[id]) {
            html += '<hr><h6 style="color:#065f46;">Transiciones:</h6><ul style="padding-left:20px;margin-bottom:0;">';
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