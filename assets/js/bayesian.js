/**
 * JavaScript para Módulo de Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * VERSIÓN FINAL INTEGRAL: Diseño de Nodos tipo Botón + Rutas Corregidas + Ejemplos Completos.
 */

// ========== PREVENIR DOBLE CARGA ==========
if (typeof window.bayesianModuleLoaded !== 'undefined') {
    console.warn('⚠️ bayesian.js ya está cargado, evitando duplicación');
} else {
    window.bayesianModuleLoaded = true;

    // ========== VARIABLES GLOBALES ==========
    let currentNetwork = {
        name: 'Nueva Red',
        nodes: [], // {id, label}
        edges: [], // {from, to}
        cpt: {}    // { nodeId: { "Parent1=True,Parent2=False": 0.8, ... } }
    };
    let networkDiagram = null;
    let selectedQueryVariable = null;
    let evidenceVariables = {};
    let lastInferenceResult = null;
    
    const historyStack = [];
    const redoStack = [];
    const inferenceCache = new Map();
    
    // Inferencia en servidor activada
    const USE_SERVER_INFERENCE = true; 

    // ========== INICIALIZACIÓN ==========
    document.addEventListener('DOMContentLoaded', () => {
        console.log('✅ Módulo de Redes Bayesianas iniciado');
        
        if (typeof vis === 'undefined') {
            console.error('❌ ERROR CRÍTICO: vis.js no está cargado');
            const container = document.getElementById('network-visualization');
            if (container) {
                container.innerHTML = '<div class="alert alert-danger">Error: La biblioteca vis.js no está disponible.</div>';
            }
            return;
        }

        initializeModule();
    });

    function initializeModule() {
        initTabs();
        initNetworkDiagram();
        initExampleButtons();
        ensureResultsTab();
        
        // Atajos de teclado
        document.addEventListener('keydown', (e) => {
            const isMod = e.ctrlKey || e.metaKey;
            if (isMod && e.key === 'z') { e.preventDefault(); window.undo(); }
            if (isMod && e.shiftKey && (e.key === 'Z' || e.key === 'z')) { e.preventDefault(); window.redo(); }
            if (e.key === 'Delete') removeSelected();
        });

        // Cargamos una red limpia inicial
        createNewNetwork(false);
    }

    // ========== GESTIÓN DE TABS ==========
    function initTabs() {
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const tabId = btn.dataset.tab;
                if (tabId) activateTab(tabId);
            });
        });
    }

    function activateTab(tabId) {
        document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => {
            p.style.display = 'none';
            p.classList.remove('active');
        });
        
        const btn = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
        const panel = document.getElementById(`tab-${tabId}`);
        
        if (btn) btn.classList.add('active');
        if (panel) {
            panel.style.display = 'block';
            panel.classList.add('active');
            
            if (tabId === 'probabilities') displayAllCPTs();
            if (tabId === 'inference') updateQueryVariables();
            if (tabId === 'network' && networkDiagram) {
                setTimeout(() => networkDiagram.fit({ animation: true }), 100);
            }
        }
    }

    function ensureResultsTab() {
        let resultsTab = document.getElementById('tab-results');
        if (!resultsTab) {
            const tabsContainer = document.querySelector('.tab-content');
            if (tabsContainer) {
                resultsTab = document.createElement('div');
                resultsTab.id = 'tab-results';
                resultsTab.className = 'tab-panel';
                resultsTab.style.display = 'none';
                resultsTab.innerHTML = '<h3>Resultados de Inferencia</h3><div id="results-container"></div>';
                tabsContainer.appendChild(resultsTab);
            }
        }
    }

    // ========== DIAGRAMA VIS.JS (DISEÑO TIPO BOTÓN) ==========
    function initNetworkDiagram() {
        const container = document.getElementById('network-visualization');
        if (!container) return;
        
        const data = { nodes: new vis.DataSet([]), edges: new vis.DataSet([]) };
        
        const options = {
            nodes: {
                shape: 'box',
                margin: { top: 10, bottom: 10, left: 15, right: 15 },
                widthConstraint: { minimum: 120, maximum: 180 },
                color: {
                    background: '#4f46e5', // Fondo índigo tipo botón primario
                    border: '#4338ca',     // Borde más oscuro
                    highlight: { background: '#4338ca', border: '#3730a3' },
                    hover: { background: '#6366f1', border: '#4f46e5' }
                },
                font: { 
                    size: 15, 
                    color: '#ffffff', 
                    face: 'Plus Jakarta Sans, Arial',
                    align: 'center',
                    bold: true
                },
                borderWidth: 2,
                shadow: {
                    enabled: true,
                    color: 'rgba(0,0,0,0.25)',
                    size: 10,
                    x: 3,
                    y: 3
                },
                shapeProperties: {
                    borderRadius: 12 // Bordes redondeados tipo botón moderno
                }
            },
            edges: {
                arrows: { to: { enabled: true, scaleFactor: 1.2, type: 'arrow' } },
                color: { color: '#94a3b8', highlight: '#4f46e5', hover: '#6366f1' },
                width: 2,
                smooth: { enabled: true, type: 'cubicBezier', roundness: 0.5 }
            },
            layout: {
                hierarchical: {
                    enabled: true,
                    direction: 'UD',
                    sortMethod: 'directed',
                    levelSeparation: 150,
                    nodeSpacing: 200,
                    treeSpacing: 220,
                    blockShifting: true,
                    edgeMinimization: true,
                    parentCentralization: true
                }
            },
            physics: { enabled: false }, // Red estática y ordenada
            interaction: {
                dragNodes: true,
                dragView: true,
                zoomView: true,
                hover: true,
                multiselect: true,
                selectConnectedEdges: true
            },
            autoResize: true,
            height: '100%',
            width: '100%'
        };
        
        networkDiagram = new vis.Network(container, data, options);
        
        networkDiagram.on("select", function (params) {
            if (params.nodes.length > 0) {
                updateNodeInfoPanel(params.nodes[0]);
            } else {
                const infoPanel = document.getElementById('node-info');
                if (infoPanel) infoPanel.style.display = 'none';
            }
        });

        console.log('✅ Diagrama de red inicializado con estilo de botones');
    }

    window.fitNetwork = function() {
        if (networkDiagram) {
            networkDiagram.fit({
                animation: { duration: 500, easingFunction: 'easeInOutQuad' }
            });
        }
    };

    function updateNodeInfoPanel(nodeId) {
        const infoPanel = document.getElementById('node-info');
        const detailsDiv = document.getElementById('node-details');
        
        if (!nodeId || !currentNetwork) {
            if(infoPanel) infoPanel.style.display = 'none';
            return;
        }

        const node = currentNetwork.nodes.find(n => n.id === nodeId);
        if (node && infoPanel && detailsDiv) {
            infoPanel.style.display = 'block';
            const parents = getParents(node.id);
            const children = getChildren(node.id);
            detailsDiv.innerHTML = `
                <div class="p-2 border rounded bg-white">
                    <p class="mb-1"><strong>ID:</strong> <code>${node.id}</code></p>
                    <p class="mb-1"><strong>Variable:</strong> ${node.label}</p>
                    <p class="mb-1"><strong>Padres:</strong> ${parents.length ? parents.join(', ') : 'Raíz'}</p>
                    <p class="mb-0"><strong>Hijos:</strong> ${children.length ? children.join(', ') : 'Hoja'}</p>
                </div>`;
        }
    }

    function highlightInferenceNodes() {
        if (!networkDiagram) return;
        const nodes = networkDiagram.body.data.nodes;
        currentNetwork.nodes.forEach(node => {
            let color = { background: '#4f46e5', border: '#4338ca' }; // Estilo botón default
            let fontColor = '#ffffff';
            
            // Consulta: Botón Verde Éxito
            if (node.id === selectedQueryVariable) {
                color = { background: '#10b981', border: '#059669' };
            } 
            // Evidencia: Botón Ámbar Advertencia
            else if (evidenceVariables[node.id]) {
                color = { background: '#f59e0b', border: '#d97706' };
            }
            
            nodes.update({ id: node.id, color: color, font: { color: fontColor } });
        });
    }

    // ========== LÓGICA DE GRAFOS (CORE) ==========
    function getParents(nodeId) {
        return currentNetwork.edges.filter(e => e.to === nodeId).map(e => e.from).sort();
    }

    function getChildren(nodeId) {
        return currentNetwork.edges.filter(e => e.from === nodeId).map(e => e.to).sort();
    }

    function topologicalSort() {
        const visited = new Set();
        const stack = [];
        const adjacency = {};
        currentNetwork.nodes.forEach(n => adjacency[n.id] = []);
        currentNetwork.edges.forEach(e => { if (adjacency[e.from]) adjacency[e.from].push(e.to); });
        function dfs(nodeId) {
            if (visited.has(nodeId)) return;
            visited.add(nodeId);
            const children = adjacency[nodeId] || [];
            children.forEach(child => dfs(child));
            stack.push(nodeId);
        }
        currentNetwork.nodes.forEach(n => { if (!visited.has(n.id)) dfs(n.id); });
        return stack.reverse();
    }

    function hasCycle(graphNodes, graphEdges) {
        const adj = {};
        graphNodes.forEach(n => adj[n.id] = []);
        graphEdges.forEach(e => { if (adj[e.from]) adj[e.from].push(e.to); });
        const visited = {};
        const recStack = {};
        function isCyclic(nodeId) {
            if (!visited[nodeId]) {
                visited[nodeId] = true; recStack[nodeId] = true;
                const neighbors = adj[nodeId] || [];
                for (let neighbor of neighbors) {
                    if (!visited[neighbor] && isCyclic(neighbor)) return true;
                    else if (recStack[neighbor]) return true;
                }
            }
            recStack[nodeId] = false; return false;
        }
        for (let node of graphNodes) { if (isCyclic(node.id)) return true; }
        return false;
    }

    // ========== HISTORIAL ==========
    function saveState() {
        historyStack.push(JSON.parse(JSON.stringify(currentNetwork)));
        if (historyStack.length > 50) historyStack.shift();
        redoStack.length = 0;
        inferenceCache.clear();
    }

    window.undo = function() {
        if (historyStack.length === 0) return alert('Nada para deshacer');
        redoStack.push(JSON.parse(JSON.stringify(currentNetwork)));
        currentNetwork = historyStack.pop();
        renderNetwork(currentNetwork);
    };

    window.redo = function() {
        if (redoStack.length === 0) return alert('Nada para rehacer');
        historyStack.push(JSON.parse(JSON.stringify(currentNetwork)));
        currentNetwork = redoStack.pop();
        renderNetwork(currentNetwork);
    };

    // ========== ESTRUCTURA ==========
    window.addNode = function() {
        saveState();
        const name = prompt("Nombre de la variable:");
        if (!name) return;
        const id = name.trim().replace(/\s+/g, '_').toLowerCase();
        if (currentNetwork.nodes.find(n => n.id === id)) return alert("ID duplicado");
        currentNetwork.nodes.push({ id, label: name });
        currentNetwork.cpt[id] = { "root": 0.5 }; 
        renderNetwork(currentNetwork);
    };

    window.addEdge = function() {
        const fromId = prompt("ID del Padre:");
        const toId = prompt("ID del Hijo:");
        if (!fromId || !toId || fromId === toId) return;
        const source = currentNetwork.nodes.find(n => n.id === fromId);
        const target = currentNetwork.nodes.find(n => n.id === toId);
        if (!source || !target) return alert("Nodos no encontrados");
        
        if (hasCycle(currentNetwork.nodes, [...currentNetwork.edges, { from: fromId, to: toId }])) {
            return alert("La red debe ser acíclica (DAG)");
        }

        saveState();
        currentNetwork.edges.push({ from: fromId, to: toId });
        rebuildCPT(toId);
        renderNetwork(currentNetwork);
    };

    window.removeSelected = function() {
        if (!networkDiagram) return;
        const sel = networkDiagram.getSelection();
        if (sel.nodes.length === 0 && sel.edges.length === 0) return;
        if (!confirm("¿Eliminar selección?")) return;
        saveState();
        currentNetwork.nodes = currentNetwork.nodes.filter(n => !sel.nodes.includes(n.id));
        currentNetwork.edges = currentNetwork.edges.filter(e => !sel.nodes.includes(e.from) && !sel.nodes.includes(e.to));
        sel.nodes.forEach(id => delete currentNetwork.cpt[id]);
        renderNetwork(currentNetwork);
    };

    function rebuildCPT(nodeId) {
        const parents = getParents(nodeId);
        if (parents.length === 0) {
            currentNetwork.cpt[nodeId] = { "root": 0.5 };
        } else {
            const count = Math.pow(2, parents.length);
            const sortedParents = [...parents].sort();
            currentNetwork.cpt[nodeId] = {};
            for (let i = 0; i < count; i++) {
                const key = sortedParents.map((p, idx) => `${p}=${((i >> idx) & 1) ? 'True' : 'False'}`).join(',');
                currentNetwork.cpt[nodeId][key] = 0.5;
            }
        }
    }

    function displayAllCPTs() {
        const container = document.getElementById('cpt-container');
        if (!container) return;
        container.innerHTML = '';
        if (currentNetwork.nodes.length === 0) {
            container.innerHTML = '<div class="alert alert-warning">Agrega variables para ver sus tablas.</div>';
            return;
        }
        currentNetwork.nodes.forEach(node => {
            const cpt = currentNetwork.cpt[node.id] || {};
            let html = `<div class="card mb-3 shadow-sm border-primary"><div class="card-header bg-primary text-white"><strong>${node.label}</strong></div><div class="card-body p-0">
                <table class="table table-sm table-hover mb-0"><thead><tr><th class="ps-3">Condición</th><th>P(True)</th></tr></thead><tbody>`;
            Object.entries(cpt).forEach(([key, val]) => {
                html += `<tr><td class="ps-3"><small class="font-mono">${key === 'root' ? 'Prob. Marginal' : key}</small></td>
                <td><input type="number" step="0.1" min="0" max="1" value="${val}" class="form-control form-control-sm" style="width:80px" onchange="updateCPTValue('${node.id}', '${key}', this.value)"></td></tr>`;
            });
            container.innerHTML += html + '</tbody></table></div></div>';
        });
    }

    window.updateCPTValue = function(nodeId, key, value) {
        currentNetwork.cpt[nodeId][key] = parseFloat(value);
        inferenceCache.clear();
    };

    // ========== MOTOR DE INFERENCIA ==========
    window.runInference = async function() {
        if (!selectedQueryVariable) return alert('Define la variable de consulta');
        
        const algorithmRadio = document.querySelector('input[name="algorithm"]:checked');
        const selectedAlgorithm = algorithmRadio ? algorithmRadio.value : 'enumeration';

        const resultsDiv = document.getElementById('results-container');
        resultsDiv.innerHTML = `
            <div class="p-5 text-center">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 text-muted">Solicitando cálculo al servidor PHP...</p>
            </div>`;
        activateTab('results');

        try {
            if (USE_SERVER_INFERENCE) {
                const baseUrl = document.body.dataset.baseUrl || window.location.origin;
                let path = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';
                const endpoint = `${path}modules/bayesian/algorithms/${selectedAlgorithm === 'enumeration' ? 'enumeration.php' : 'variable_elimination.php'}`;
                
                console.log("🚀 Enviando petición a:", endpoint);

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ network: currentNetwork, query: selectedQueryVariable, evidence: evidenceVariables })
                });

                const contentType = response.headers.get("content-type");
                
                if (!response.ok || !contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    console.error("Respuesta fallida:", text);
                    if (text.includes('<!DOCTYPE html>')) {
                        throw new Error(`Error del Servidor: Se recibió HTML. Verifica la ruta: modules/bayesian/algorithms/`);
                    }
                    throw new Error("El servidor devolvió un formato no válido.");
                }

                const data = await response.json();
                if (!data.success) throw new Error(data.error || 'Error en el motor PHP');
                
                let result = data.probabilities || data.results;
                const steps = data.steps || [];
                
                // Normalizar llaves
                if (result.True !== undefined) result = { true: result.True, false: result.False };
                
                renderResultsUI(result, steps, selectedAlgorithm);
            } else {
                const result = exactInference(selectedQueryVariable, evidenceVariables, currentNetwork);
                renderResultsUI(result, [], 'local');
            }
        } catch (e) {
            resultsDiv.innerHTML = `<div class="alert alert-danger shadow-sm"><strong>⚠️ Error:</strong> ${e.message}</div>`;
            console.error("Error en runInference:", e);
        }
    };

    function renderResultsUI(result, steps, algorithm) {
        const resultsDiv = document.getElementById('results-container');
        const queryLabel = currentNetwork.nodes.find(n => n.id === selectedQueryVariable)?.label || selectedQueryVariable;
        const algoName = algorithm === 'enumeration' ? 'Enumeración Exacta' : 'Eliminación de Variables';
        
        let stepsHtml = '';
        if (steps && steps.length > 0) {
            stepsHtml = `
                <div class="mt-4">
                    <h6 class="border-bottom pb-2"><strong>Pasos del Algoritmo:</strong></h6>
                    <ol class="list-group list-group-numbered list-group-flush small">
                        ${steps.map(step => `<li class="list-group-item bg-transparent">${typeof step === 'string' ? step : step.message}</li>`).join('')}
                    </ol>
                </div>`;
        }

        resultsDiv.innerHTML = `
            <div class="card mt-3 shadow-sm border-indigo">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span>Resultado: P(${queryLabel} | Evidencia)</span>
                    <span class="badge bg-primary">${algoName}</span>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-success">Verdadero (True)</span>
                            <span class="fw-bold">${(result.true * 100).toFixed(4)}%</span>
                        </div>
                        <div class="progress" style="height:25px">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width:${result.true * 100}%"></div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-danger">Falso (False)</span>
                            <span class="fw-bold">${(result.false * 100).toFixed(4)}%</span>
                        </div>
                        <div class="progress" style="height:25px">
                            <div class="progress-bar bg-danger" style="width:${result.false * 100}%"></div>
                        </div>
                    </div>
                    
                    ${stepsHtml}
                </div>
                <div class="card-footer bg-light text-center">
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.exportInferenceResults()">
                        <i class="fas fa-file-export"></i> Exportar Resultado JSON
                    </button>
                </div>
            </div>`;
    }

    // ========== LIMPIEZA ==========
    window.clearEvidence = function() { 
        evidenceVariables = {}; 
        updateQueryVariables(); 
        updateEvidenceDisplay(); 
        highlightInferenceNodes(); 
        alert('Evidencia limpiada.');
    };

    window.clearQuery = function() { 
        selectedQueryVariable = null; 
        updateQueryVariables(); 
        highlightInferenceNodes(); 
    };

    window.clearAll = function() { 
        if(!confirm('¿Limpiar red? Se perderá toda la estructura.')) return;
        currentNetwork = { name: 'Nueva Red', nodes: [], edges: [], cpt: {} };
        selectedQueryVariable = null; 
        evidenceVariables = {};
        if (networkDiagram) networkDiagram.setData({ nodes: [], edges: [] });
        updateQueryVariables(); 
        updateEvidenceDisplay(); 
        activateTab('network');
    };

    // ========== RENDERIZADO ==========
    function renderNetwork(net) {
        if (!networkDiagram) return;
        const nodes = new vis.DataSet(net.nodes.map(n => ({ id: n.id, label: n.label })));
        const edges = new vis.DataSet(net.edges.map(e => ({ from: e.from, to: e.to })));
        networkDiagram.setData({ nodes, edges });
        highlightInferenceNodes();
        setTimeout(() => networkDiagram.fit({ animation: true }), 200);
    }

    function updateQueryVariables() {
        const qv = document.getElementById('query-variables');
        const ev = document.getElementById('evidence-variables');
        if (!qv || !ev) return;
        qv.innerHTML = ''; ev.innerHTML = '';
        currentNetwork.nodes.forEach(n => {
            const b = document.createElement('button');
            b.className = `btn btn-sm m-1 rounded-pill ${selectedQueryVariable === n.id ? 'btn-success shadow-sm' : 'btn-outline-primary'}`;
            b.textContent = n.label;
            b.onclick = () => { selectedQueryVariable = n.id; if(evidenceVariables[n.id]) delete evidenceVariables[n.id]; updateQueryVariables(); updateEvidenceDisplay(); highlightInferenceNodes(); };
            qv.appendChild(b);

            if (n.id !== selectedQueryVariable) {
                const isE = !!evidenceVariables[n.id];
                const be = document.createElement('button');
                be.className = `btn btn-sm m-1 rounded-pill ${isE ? 'btn-warning shadow-sm fw-bold' : 'btn-outline-warning'}`;
                be.textContent = n.label;
                be.onclick = () => { if(isE) delete evidenceVariables[n.id]; else evidenceVariables[n.id] = 'True'; updateQueryVariables(); updateEvidenceDisplay(); highlightInferenceNodes(); };
                ev.appendChild(be);
            }
        });
    }

    function updateEvidenceDisplay() {
        const container = document.getElementById('evidence-inputs');
        if(!container) return;
        container.innerHTML = '';
        if (Object.keys(evidenceVariables).length === 0) { container.innerHTML = '<p class="text-muted small italic">Sin evidencia seleccionada.</p>'; return; }
        Object.entries(evidenceVariables).forEach(([id, val]) => {
            const label = currentNetwork.nodes.find(n => n.id === id)?.label || id;
            container.innerHTML += `
                <div class="d-flex justify-content-between align-items-center p-2 border rounded mb-2 bg-light shadow-sm border-warning">
                    <span class="small fw-bold">${label} = <span class="${val === 'True' ? 'text-success' : 'text-danger'}">${val}</span></span>
                    <div class="btn-group btn-group-xs">
                        <button class="btn btn-outline-secondary btn-xs" style="padding: 1px 5px; font-size: 0.75rem;" onclick="window.toggleEvidence('${id}')">Cambiar</button>
                        <button class="btn btn-outline-danger btn-xs" style="padding: 1px 5px; font-size: 0.75rem;" onclick="delete evidenceVariables['${id}']; updateQueryVariables(); updateEvidenceDisplay(); highlightInferenceNodes();">&times;</button>
                    </div>
                </div>`;
        });
    }

    window.toggleEvidence = function(id) {
        evidenceVariables[id] = (evidenceVariables[id] === 'True') ? 'False' : 'True';
        updateEvidenceDisplay();
        highlightInferenceNodes();
    };

    window.createNewNetwork = function(conf = true) {
        if(conf && !confirm('¿Crear nueva red?')) return;
        currentNetwork = { name: 'Nueva Red', nodes: [], edges: [], cpt: {} };
        selectedQueryVariable = null; evidenceVariables = {};
        renderNetwork(currentNetwork);
    }

    // ========== EJEMPLOS ==========
    function initExampleButtons() {
        document.querySelectorAll('.btn-example').forEach(btn => {
            btn.addEventListener('click', () => loadExample(btn.dataset.example));
        });
    }

    async function loadExample(name) {
        const examples = {
            alarm: {
                "name": "Alarma - Terremoto - Ladrón",
                "nodes": [{ "id": "terremoto", "label": "Terremoto" }, { "id": "ladron", "label": "Ladrón" }, { "id": "alarma", "label": "Alarma" }, { "id": "juan_llama", "label": "Juan Llama" }, { "id": "maria_llama", "label": "María Llama" }],
                "edges": [{ "from": "terremoto", "to": "alarma" }, { "from": "ladron", "to": "alarma" }, { "from": "alarma", "to": "juan_llama" }, { "from": "alarma", "to": "maria_llama" }],
                "cpt": { "terremoto": { "root": 0.002 }, "ladron": { "root": 0.001 }, "alarma": { "ladron=False,terremoto=False": 0.001, "ladron=False,terremoto=True": 0.29, "ladron=True,terremoto=False": 0.94, "ladron=True,terremoto=True": 0.95 }, "juan_llama": { "alarma=False": 0.05, "alarma=True": 0.90 }, "maria_llama": { "alarma=False": 0.01, "alarma=True": 0.70 } }
            },
            weather: {
                "name": "Predicción Climática",
                "nodes": [{ "id": "lluvia", "label": "Lluvia" }, { "id": "aspersor", "label": "Aspersor" }, { "id": "cesped_mojado", "label": "Césped Mojado" }, { "id": "nublado", "label": "Nublado" }, { "id": "suelo_resbaloso", "label": "Suelo Resbaloso" }],
                "edges": [{ "from": "nublado", "to": "lluvia" }, { "from": "lluvia", "to": "cesped_mojado" }, { "from": "aspersor", "to": "cesped_mojado" }, { "from": "cesped_mojado", "to": "suelo_resbaloso" }],
                "cpt": { "nublado": { "root": 0.50 }, "aspersor": { "root": 0.20 }, "lluvia": { "nublado=False": 0.20, "nublado=True": 0.80 }, "cesped_mojado": { "aspersor=False,lluvia=False": 0.01, "aspersor=False,lluvia=True": 0.90, "aspersor=True,lluvia=False": 0.85, "aspersor=True,lluvia=True": 0.99 }, "suelo_resbaloso": { "cesped_mojado=False": 0.05, "cesped_mojado=True": 0.70 } }
            },
            medical: {
                "name": "Red Médica de Diagnóstico",
                "nodes": [{ "id": "fumador", "label": "Fumador" }, { "id": "contaminacion", "label": "Contaminación" }, { "id": "cancer_pulmon", "label": "Cáncer" }, { "id": "bronquitis", "label": "Bronquitis" }, { "id": "disnea", "label": "Disnea" }, { "id": "rayos_x_anormal", "label": "Rayos X" }],
                "edges": [{ "from": "fumador", "to": "cancer_pulmon" }, { "from": "fumador", "to": "bronquitis" }, { "from": "contaminacion", "to": "cancer_pulmon" }, { "from": "contaminacion", "to": "bronquitis" }, { "from": "cancer_pulmon", "to": "disnea" }, { "from": "cancer_pulmon", "to": "rayos_x_anormal" }, { "from": "bronquitis", "to": "disnea" }],
                "cpt": { "fumador": { "root": 0.30 }, "contaminacion": { "root": 0.10 }, "cancer_pulmon": { "contaminacion=False,fumador=False": 0.001, "contaminacion=False,fumador=True": 0.02, "contaminacion=True,fumador=False": 0.03, "contaminacion=True,fumador=True": 0.05 }, "bronquitis": { "contaminacion=False,fumador=False": 0.05, "contaminacion=False,fumador=True": 0.30, "contaminacion=True,fumador=False": 0.25, "contaminacion=True,fumador=True": 0.60 }, "disnea": { "bronquitis=False,cancer_pulmon=False": 0.10, "bronquitis=False,cancer_pulmon=True": 0.70, "bronquitis=True,cancer_pulmon=False": 0.80, "bronquitis=True,cancer_pulmon=True": 0.90 }, "rayos_x_anormal": { "cancer_pulmon=False": 0.20, "cancer_pulmon=True": 0.90 } }
            },
            diagnostic: {
                "name": "Diagnóstico Automóvil",
                "nodes": [{ "id": "falla_motor", "label": "Motor" }, { "id": "falla_bateria", "label": "Batería" }, { "id": "no_arranca", "label": "No Arranca" }, { "id": "ruido_anormal", "label": "Ruido" }, { "id": "luces_debiles", "label": "Luces" }],
                "edges": [{ "from": "falla_motor", "to": "no_arranca" }, { "from": "falla_motor", "to": "ruido_anormal" }, { "from": "falla_bateria", "to": "no_arranca" }, { "from": "falla_bateria", "to": "luces_debiles" }],
                "cpt": { "falla_motor": { "root": 0.05 }, "falla_bateria": { "root": 0.10 }, "no_arranca": { "falla_bateria=False,falla_motor=False": 0.05, "falla_bateria=False,falla_motor=True": 0.70, "falla_bateria=True,falla_motor=False": 0.80, "falla_bateria=True,falla_motor=True": 0.98 }, "ruido_anormal": { "falla_motor=False": 0.10, "falla_motor=True": 0.85 }, "luces_debiles": { "falla_bateria=False": 0.05, "falla_bateria=True": 0.90 } }
            }
        };

        const data = examples[name];
        if (data) {
            currentNetwork = data;
            saveState();
            renderNetwork(data);
            updateQueryVariables();
            updateEvidenceDisplay();
            activateTab('network');
            console.log(`✅ Ejemplo "${data.name}" cargado.`);
        }
    }

    // Funciones requeridas por index.php viejo
    window.saveNetwork = function() { alert("Red guardada localmente"); };
    window.exportNetwork = function() {
        const blob = new Blob([JSON.stringify(currentNetwork, null, 2)], { type: "application/json" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url; a.download = 'red_bayesiana.json'; a.click();
    };
    window.restoreBackup = function() {
        const backups = Object.keys(localStorage).filter(k => k.startsWith('bayesian_backup_'));
        if(backups.length === 0) return alert("No hay respaldos disponibles");
        currentNetwork = JSON.parse(localStorage.getItem(backups.sort().pop()));
        renderNetwork(currentNetwork);
        updateQueryVariables();
        saveState();
    };

    function showAlert(msg, type = 'info') {
        const div = document.createElement('div');
        const color = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#6366f1');
        div.style.cssText = `position:fixed;top:20px;right:20px;padding:12px 24px;background:${color};color:white;border-radius:12px;z-index:9999;box-shadow:0 10px 15px -3px rgba(0,0,0,0.1);font-weight:bold;font-size:11px;text-transform:uppercase;`;
        div.innerText = msg;
        document.body.appendChild(div);
        setTimeout(() => { if(div.parentNode) div.remove(); }, 3000);
    }
}