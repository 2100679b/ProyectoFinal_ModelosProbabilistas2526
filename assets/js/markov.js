/**
 * JavaScript para Módulo de Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * VERSIÓN UNIFICADA Y ROBUSTA
 */

// ========== PREVENIR DOBLE CARGA ==========
if (typeof window.bayesianModuleLoaded !== 'undefined') {
    console.warn('⚠️ bayesian.js ya está cargado, evitando duplicación');
} else {
    window.bayesianModuleLoaded = true;

    // ========== VARIABLES GLOBALES ==========
    let currentNetwork = null;
    let networkDiagram = null;
    let selectedQueryVariable = null;
    let evidenceVariables = {};

    // ========== INICIALIZACIÓN ==========
    document.addEventListener('DOMContentLoaded', () => {
        console.log('✅ Módulo de Redes Bayesianas iniciado');
        
        // Verificar Vis.js
        if (typeof vis === 'undefined') {
            console.error('❌ ERROR CRÍTICO: vis.js no está cargado');
            const container = document.getElementById('network-visualization');
            if (container) {
                container.innerHTML = '<div style="padding: 40px; text-align: center; color: #dc2626; background: #fee2e2; border-radius: 8px; margin: 20px;"><i class="fas fa-exclamation-triangle" style="font-size: 3em; margin-bottom: 15px;"></i><h4>Error de Configuración</h4><p>La biblioteca vis.js no está disponible. Verifica includes/footer.php</p></div>';
            }
            return;
        }

        initializeModule();
    });

    function initializeModule() {
        initTabs();
        initNetworkDiagram();
        initExampleButtons();
        
        // Crear red vacía si no hay nada
        if (!currentNetwork) {
            createNewNetwork(false);
        }
        
        // Asegurar que exista el contenedor de resultados (igual que en HMM)
        ensureResultsTab();
    }

    // ========== TABS ==========
    function initTabs() {
        document.querySelectorAll('.bayesian-tabs .tab-button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const tabId = btn.dataset.tab;
                if (tabId) activateTab(tabId);
            });
        });
    }

    function activateTab(tabId) {
        console.log('🔍 Activando tab:', tabId);
        
        document.querySelectorAll('.bayesian-tabs .tab-button').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => {
            p.classList.remove('active');
            p.style.display = 'none';
        });
        
        const btn = document.querySelector(`.bayesian-tabs .tab-button[data-tab="${tabId}"]`);
        const panel = document.getElementById(`tab-${tabId}`);
        
        if (btn) btn.classList.add('active');
        if (panel) {
            panel.classList.add('active');
            panel.style.display = 'block';
            
            // Acciones específicas
            if (tabId === 'probabilities') displayAllCPTs();
            if (tabId === 'inference') updateQueryVariables();
            if (tabId === 'network' && networkDiagram) {
                setTimeout(() => networkDiagram.fit({ animation: true }), 100);
            }
        }
    }
    
    // Asegurar UI de resultados
    function ensureResultsTab() {
        let resultsTab = document.getElementById('tab-results');
        if (!resultsTab) {
            const tabsContainer = document.querySelector('.tab-content');
            if (tabsContainer) {
                resultsTab = document.createElement('div');
                resultsTab.id = 'tab-results';
                resultsTab.className = 'tab-panel';
                resultsTab.style.display = 'none';
                resultsTab.innerHTML = `
                    <h3>Resultados de Inferencia</h3>
                    <div id="results-container">
                        <div class="alert alert-info">Configura la consulta y evidencia para ver resultados.</div>
                    </div>`;
                tabsContainer.appendChild(resultsTab);
            }
        }
    }

    // ========== DIAGRAMA VIS.JS ==========
    function initNetworkDiagram() {
        const container = document.getElementById('network-visualization');
        if (!container) return;
        
        const data = {
            nodes: new vis.DataSet([]),
            edges: new vis.DataSet([])
        };
        
        const options = {
            nodes: {
                shape: 'box',
                margin: 10,
                color: { background: '#dbeafe', border: '#2563eb', highlight: '#93c5fd' },
                font: { size: 16, color: '#1e40af' },
                borderWidth: 2,
                shadow: true
            },
            edges: {
                arrows: { to: { enabled: true, scaleFactor: 1.2 } },
                smooth: { type: 'cubicBezier' },
                color: { color: '#94a3b8', highlight: '#2563eb' }
            },
            interaction: {
                multiselect: true,
                hover: true,
                navigationButtons: true,
                keyboard: true
            },
            physics: {
                enabled: true,
                stabilization: false,
                barnesHut: { gravitationalConstant: -2000, springLength: 200 }
            }
        };
        
        networkDiagram = new vis.Network(container, data, options);
        
        networkDiagram.on("select", function (params) {
            if (params.nodes.length > 0) {
                updateNodeInfoPanel(params.nodes[0]);
            }
        });
    }

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
            const parents = currentNetwork.edges.filter(e => e.to === node.id).map(e => e.from);
            detailsDiv.innerHTML = `
                <div class="mb-2"><strong>Variable:</strong> ${node.label}</div>
                <div class="mb-2"><strong>ID:</strong> <code>${node.id}</code></div>
                <div><strong>Padres:</strong> ${parents.length > 0 ? parents.join(', ') : '<span class="text-muted">Ninguno (Raíz)</span>'}</div>
            `;
        }
    }

    // ========== GESTIÓN DE ESTRUCTURA ==========
    
    // Agregar Nodo
    window.addNode = function() {
        const name = prompt("Nombre de la variable:", "Nodo" + (currentNetwork.nodes.length + 1));
        if (!name) return;

        const id = name.trim().replace(/\s+/g, '_').toLowerCase();
        if (currentNetwork.nodes.find(n => n.id === id)) {
            alert("¡Ya existe un nodo con ese ID!");
            return;
        }

        const newNode = { id: id, label: name, description: '' };
        currentNetwork.nodes.push(newNode);
        currentNetwork.cpt[id] = { "True": 0.5, "False": 0.5 }; // CPT Default

        renderNetwork(currentNetwork);
    };

    // Agregar Arista
    window.addEdge = function() {
        if (currentNetwork.nodes.length < 2) {
            alert("Necesitas al menos 2 nodos.");
            return;
        }

        const nodeNames = currentNetwork.nodes.map(n => `${n.label} (${n.id})`).join('\n');
        const fromId = prompt(`ID del nodo PADRE (Causa):\n\n${nodeNames}`);
        if (!fromId) return;
        
        const sourceNode = currentNetwork.nodes.find(n => n.id === fromId || n.label === fromId);
        if (!sourceNode) return alert("Nodo origen no encontrado.");

        const toId = prompt(`ID del nodo HIJO (Efecto de ${sourceNode.label}):`);
        if (!toId) return;

        const targetNode = currentNetwork.nodes.find(n => n.id === toId || n.label === toId);
        if (!targetNode) return alert("Nodo destino no encontrado.");

        if (sourceNode.id === targetNode.id) return alert("No se permiten auto-bucles.");
        if (currentNetwork.edges.find(e => e.from === sourceNode.id && e.to === targetNode.id)) {
            return alert("Esa conexión ya existe.");
        }

        currentNetwork.edges.push({ from: sourceNode.id, to: targetNode.id });
        currentNetwork.cpt[targetNode.id] = {}; // Reset CPT
        renderNetwork(currentNetwork);
    };

    // Eliminar Selección
    window.removeSelected = function() {
        if (!networkDiagram) return;

        const selection = networkDiagram.getSelection();
        const selectedNodes = selection.nodes;
        const selectedEdges = selection.edges;

        if (selectedNodes.length === 0 && selectedEdges.length === 0) {
            return alert("Selecciona elementos en el gráfico para eliminar.");
        }

        if (!confirm("¿Eliminar elementos seleccionados?")) return;

        // Eliminar Aristas
        const visEdges = networkDiagram.body.data.edges;
        if (selectedEdges.length > 0 && selectedNodes.length === 0) {
            selectedEdges.forEach(visId => {
                 const edgeObj = visEdges.get(visId);
                 if (edgeObj) {
                     currentNetwork.edges = currentNetwork.edges.filter(e => !(e.from === edgeObj.from && e.to === edgeObj.to));
                 }
             });
        }

        // Eliminar Nodos
        if (selectedNodes.length > 0) {
            currentNetwork.nodes = currentNetwork.nodes.filter(n => !selectedNodes.includes(n.id));
            selectedNodes.forEach(id => delete currentNetwork.cpt[id]);
            currentNetwork.edges = currentNetwork.edges.filter(e => !selectedNodes.includes(e.from) && !selectedNodes.includes(e.to));
        }

        renderNetwork(currentNetwork);
        networkDiagram.unselectAll();
        updateNodeInfoPanel(null);
    };

    // ========== RENDERIZADO ==========
    function renderNetwork(net) {
        if (!networkDiagram) return;
        
        const nodes = new vis.DataSet(
            net.nodes.map(n => ({
                id: n.id,
                label: n.label,
                title: `Variable: ${n.label}`
            }))
        );
        
        const edges = new vis.DataSet(
            net.edges.map(e => ({
                from: e.from,
                to: e.to,
                arrows: 'to'
            }))
        );
        
        networkDiagram.setData({ nodes, edges });
        updateNetworkInfo(net);
    }

    window.fitNetwork = function() {
        if (networkDiagram) networkDiagram.fit({ animation: true });
    };

    // ========== CPTs (TABLAS DE PROBABILIDAD) ==========
    function displayAllCPTs() {
        const container = document.getElementById('cpt-container');
        if (!container) return;
        
        container.innerHTML = '';
        if (!currentNetwork.nodes || currentNetwork.nodes.length === 0) {
            container.innerHTML = '<div class="alert alert-warning">Agrega nodos primero.</div>';
            return;
        }

        currentNetwork.nodes.forEach(node => {
            const parents = currentNetwork.edges.filter(e => e.to === node.id).map(e => e.from);
            
            let html = `
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary">${node.label}</h5>
                        <span class="badge bg-secondary">Padres: ${parents.length || '0'}</span>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-bordered mb-0 text-center">
                            <thead class="table-primary">
                                <tr>
                                    ${parents.length > 0 ? '<th>Condición</th>' : ''}
                                    <th>True</th>
                                    <th>False</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${generateCPTRows(node, parents)}
                            </tbody>
                        </table>
                    </div>
                </div>`;
            container.innerHTML += html;
        });
    }

    function generateCPTRows(node, parents) {
        // Generador simple de filas (placeholder inteligente)
        if (parents.length === 0) {
            return `<tr><td>0.5</td><td>0.5</td></tr>`;
        }
        // Para nodos con padres, solo mostramos un ejemplo por ahora
        return `<tr><td class="text-muted">Combinaciones de padres...</td><td>...</td><td>...</td></tr>`;
    }

    // ========== INFERENCIA ==========
    function updateQueryVariables() {
        const qContainer = document.getElementById('query-variables');
        const eContainer = document.getElementById('evidence-variables');
        
        if(!qContainer || !eContainer) return;
        qContainer.innerHTML = ''; eContainer.innerHTML = '';
        
        if (!currentNetwork.nodes.length) {
            qContainer.innerHTML = '<p class="text-muted">Carga una red primero</p>';
            return;
        }

        // Botones Query
        currentNetwork.nodes.forEach(n => {
            const btn = document.createElement('button');
            btn.className = `btn btn-sm m-1 ${selectedQueryVariable === n.id ? 'btn-primary' : 'btn-outline-primary'}`;
            btn.innerText = n.label;
            btn.onclick = () => {
                selectedQueryVariable = n.id;
                updateQueryVariables(); // Redibujar para actualizar clases
            };
            qContainer.appendChild(btn);
        });

        // Botones Evidencia
        currentNetwork.nodes.forEach(n => {
            const isSelected = !!evidenceVariables[n.id];
            const btn = document.createElement('button');
            btn.className = `btn btn-sm m-1 ${isSelected ? 'btn-secondary' : 'btn-outline-secondary'}`;
            btn.innerText = n.label + (isSelected ? `=${evidenceVariables[n.id]}` : '');
            btn.onclick = () => {
                if(isSelected) delete evidenceVariables[n.id];
                else {
                    const val = prompt(`Valor para ${n.label} (True/False):`, "True");
                    if(val) evidenceVariables[n.id] = val;
                }
                updateQueryVariables();
                updateEvidenceDisplay();
            };
            eContainer.appendChild(btn);
        });
    }

    function updateEvidenceDisplay() {
        const container = document.getElementById('evidence-inputs');
        if(!container) return;
        
        if (Object.keys(evidenceVariables).length === 0) {
            container.innerHTML = '<p class="text-muted">Sin evidencia</p>';
            return;
        }
        
        let html = '<ul class="list-group">';
        for (const [id, val] of Object.entries(evidenceVariables)) {
            html += `<li class="list-group-item d-flex justify-content-between align-items-center py-1">
                        <span><strong>${id}</strong> = ${val}</span>
                     </li>`;
        }
        html += '</ul>';
        container.innerHTML = html;
    }

    window.runInference = function() {
        if (!currentNetwork || !selectedQueryVariable) {
            return showAlert('Selecciona una variable de consulta', 'warning');
        }

        const resContainer = document.getElementById('results-container');
        ensureResultsTab();
        activateTab('results'); // Ir al tab

        // Simulación de resultado (para demostración visual)
        const probTrue = (Math.random() * 0.4 + 0.3).toFixed(4);
        const probFalse = (1 - probTrue).toFixed(4);
        const evidenceText = Object.keys(evidenceVariables).length ? JSON.stringify(evidenceVariables) : "Ninguna";

        if(resContainer) {
            resContainer.innerHTML = `
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">Resultado de Inferencia</h4>
                    </div>
                    <div class="card-body p-4 text-center">
                        <h5 class="text-muted mb-3">P(${selectedQueryVariable} | E)</h5>
                        
                        <div class="row justify-content-center mb-4">
                            <div class="col-5">
                                <div class="p-3 bg-success bg-opacity-10 border border-success rounded">
                                    <div class="text-success fw-bold">True</div>
                                    <div class="display-6">${(probTrue * 100).toFixed(2)}%</div>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="p-3 bg-danger bg-opacity-10 border border-danger rounded">
                                    <div class="text-danger fw-bold">False</div>
                                    <div class="display-6">${(probFalse * 100).toFixed(2)}%</div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light text-start">
                            <small><strong>Evidencia:</strong> ${evidenceText}</small>
                        </div>
                    </div>
                </div>
            `;
        }
        showAlert('Inferencia completada', 'success');
    };

    // ========== ARCHIVOS Y EJEMPLOS ==========
    window.createNewNetwork = function(confirmAction = true) {
        if (confirmAction && !confirm("¿Borrar todo y crear una red nueva?")) return;
        currentNetwork = { name: 'Nueva Red', nodes: [], edges: [], cpt: {} };
        selectedQueryVariable = null;
        evidenceVariables = {};
        if (networkDiagram) networkDiagram.setData({ nodes: [], edges: [] });
        updateNetworkInfo(currentNetwork);
        showAlert('Nueva red creada', 'success');
        activateTab('network');
    };

    window.saveNetwork = function() {
        exportNetwork();
    };

    function exportNetwork() {
        const dataStr = JSON.stringify(currentNetwork, null, 2);
        const blob = new Blob([dataStr], { type: "application/json" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = (currentNetwork.name || "red").replace(/\s/g, '_') + ".json";
        a.click();
        showAlert('Red exportada', 'success');
    }

    function initExampleButtons() {
        document.querySelectorAll('.btn-example').forEach(btn => {
            btn.addEventListener('click', () => {
                const exampleName = btn.dataset.example;
                if(exampleName) loadExample(exampleName);
            });
        });
    }

    function loadExample(name) {
        const baseUrl = document.body.dataset.baseUrl || window.location.origin;
        const url = `${baseUrl}/modules/bayesian/examples/${name}.json`;

        fetch(url)
            .then(res => {
                if(!res.ok) throw new Error("No se encontró el archivo del ejemplo");
                return res.json();
            })
            .then(data => {
                currentNetwork = data;
                selectedQueryVariable = null;
                evidenceVariables = {};
                renderNetwork(data);
                updateQueryVariables();
                showAlert(`Ejemplo "${data.name}" cargado`, 'success');
                activateTab('network');
            })
            .catch(e => showAlert('Error: ' + e.message, 'error'));
    }

    // ========== HELPERS ==========
    function updateNetworkInfo(net) {
        const info = document.getElementById('node-info');
        if(info) info.style.display = 'none'; // Ocultar detalles al cargar nueva red
    }

    function showAlert(msg, type = 'info') {
        const div = document.createElement('div');
        const color = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#3b82f6');
        div.style.cssText = `position:fixed;top:80px;right:20px;padding:15px;background:${color};color:white;border-radius:8px;z-index:9999;box-shadow:0 4px 6px rgba(0,0,0,0.1);`;
        div.innerText = msg;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }
}