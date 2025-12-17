/**
 * JavaScript para Módulo de Redes Bayesianas
<<<<<<< HEAD
 * Actualizado para coincidir con index.php
 */

// ========== VARIABLES GLOBALES ==========
let currentNetwork = {
    name: 'Nueva Red',
    nodes: [],
    edges: [],
    cpt: {}
};
=======
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

// ========== VARIABLES GLOBALES ==========
let currentNetwork = null;
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
let networkDiagram = null;
let selectedQueryVariable = null;
let evidenceVariables = {};

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ Módulo de Redes Bayesianas iniciado');
    initTabs();
    initNetworkDiagram();
<<<<<<< HEAD
    initExampleButtons(); // Inicializar botones de ejemplos del sidebar
    
    // Si la red está vacía, iniciamos una nueva limpia
    if (!currentNetwork.nodes || currentNetwork.nodes.length === 0) {
        createNewNetwork(false); // false = sin confirmar
    }
=======
    initExampleButtons();
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
});

// ========== TABS ==========
function initTabs() {
<<<<<<< HEAD
    // Manejo de clicks en los botones del tab
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const tabId = btn.dataset.tab;
            if (tabId) activateTab(tabId);
=======
    document.querySelectorAll('.bayesian-tabs .tab-button').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const tab = btn.dataset.tab;
            if (tab) activateTab(tab);
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
        });
    });
}

function activateTab(tabId) {
<<<<<<< HEAD
    // Desactivar todos
    document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => {
        p.classList.remove('active');
        p.style.display = 'none'; // Asegurar que se oculten
    });
    
    // Activar el seleccionado
    const btn = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
=======
    console.log('📑 Activando tab:', tabId);
    
    // Desactivar todos
    document.querySelectorAll('.bayesian-tabs .tab-button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => {
        p.classList.remove('active');
        p.style.display = 'none';
    });
    
    // Activar seleccionado
    const btn = document.querySelector(`.bayesian-tabs .tab-button[data-tab="${tabId}"]`);
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
    const panel = document.getElementById(`tab-${tabId}`);
    
    if (btn) btn.classList.add('active');
    if (panel) {
        panel.classList.add('active');
        panel.style.display = 'block';
        
<<<<<<< HEAD
        // Acciones específicas al entrar a un tab
        if (tabId === 'probabilities') displayAllCPTs();
        if (tabId === 'inference') updateQueryVariables();
        if (tabId === 'network' && networkDiagram) {
            networkDiagram.fit(); // Reajustar vista al volver al mapa
=======
        // IMPORTANTE: Solo cargar CPT cuando se activa el tab
        if (tabId === 'probabilities' && currentNetwork) {
            console.log('📊 Cargando CPTs...');
            displayAllCPTs();
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
        }
    }
}

<<<<<<< HEAD
// ========== VIS.JS DIAGRAMA ==========
function initNetworkDiagram() {
    const container = document.getElementById('network-visualization');
    if (!container) return;
=======
// ========== DIAGRAMA VIS.JS ==========
function initNetworkDiagram() {
    const container = document.getElementById('network-visualization');
    if (!container) {
        console.error('❌ No se encontró #network-visualization');
        return;
    }
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
    
    const data = {
        nodes: new vis.DataSet([]),
        edges: new vis.DataSet([])
    };
    
    const options = {
        nodes: {
            shape: 'box',
<<<<<<< HEAD
            margin: 10,
            color: { background: '#dbeafe', border: '#2563eb', highlight: '#93c5fd' },
            font: { size: 16, color: '#1e40af' },
            borderWidth: 2,
            shadow: true
        },
        edges: {
            arrows: 'to',
            smooth: { type: 'cubicBezier' },
            color: { color: '#94a3b8', highlight: '#2563eb' }
        },
        interaction: {
            multiselect: true, // Permitir borrar múltiples
            hover: true,
            navigationButtons: true,
            keyboard: true
        },
        physics: {
            enabled: true,
            stabilization: false,
            barnesHut: { gravitationalConstant: -2000, springLength: 200 }
=======
            size: 25,
            font: { size: 14, color: '#1e40af' },
            borderWidth: 2,
            color: {
                background: '#dbeafe',
                border: '#2563eb',
                highlight: { background: '#2563eb', border: '#1d4ed8' }
            }
        },
        edges: {
            arrows: { to: { enabled: true, scaleFactor: 1.2 } },
            color: { color: '#6b7280', highlight: '#2563eb' },
            width: 2,
            smooth: { type: 'cubicBezier' }
        },
        physics: {
            enabled: true,
            stabilization: { iterations: 200 },
            barnesHut: {
                gravitationalConstant: -2000,
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
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
        }
    };
    
    networkDiagram = new vis.Network(container, data, options);
    
<<<<<<< HEAD
    // Evento: Al seleccionar, mostrar info
    networkDiagram.on("select", function (params) {
        console.log("Seleccionado:", params);
        // Aquí podrías actualizar el panel lateral "Información del Nodo" si lo deseas
        updateNodeInfoPanel(params.nodes[0]);
    });
}

function updateNodeInfoPanel(nodeId) {
    const infoPanel = document.getElementById('node-info');
    const detailsDiv = document.getElementById('node-details');
    
    if (!nodeId) {
        if(infoPanel) infoPanel.style.display = 'none';
        return;
    }

    const node = currentNetwork.nodes.find(n => n.id === nodeId);
    if (node && infoPanel && detailsDiv) {
        infoPanel.style.display = 'block';
        detailsDiv.innerHTML = `
            <strong>ID:</strong> ${node.id}<br>
            <strong>Etiqueta:</strong> ${node.label}<br>
            <strong>Padres:</strong> ${currentNetwork.edges.filter(e => e.to === node.id).map(e => e.from).join(', ') || 'Ninguno'}
        `;
    }
}

// ========== GESTIÓN DE ESTRUCTURA (AGREGAR/ELIMINAR) ==========

/**
 * Agrega un nuevo nodo a la red
 */
function addNode() {
    const name = prompt("Nombre del nuevo nodo (Variable):", "Nodo" + (currentNetwork.nodes.length + 1));
    if (!name) return;

    // Crear ID único simple
    const id = name.trim().replace(/\s+/g, '_').toLowerCase();

    // Validar duplicados
    if (currentNetwork.nodes.find(n => n.id === id)) {
        alert("¡Ya existe un nodo con ese ID/Nombre!");
        return;
    }

    const newNode = {
        id: id,
        label: name,
        description: ''
    };

    // Actualizar datos
    currentNetwork.nodes.push(newNode);
    currentNetwork.cpt[id] = { "True": 0.5, "False": 0.5 }; // CPT por defecto

    // Actualizar visualización
    renderNetwork(currentNetwork);
}

/**
 * Agrega una conexión (arista) entre dos nodos
 */
function addEdge() {
    if (currentNetwork.nodes.length < 2) {
        alert("Necesitas al menos 2 nodos para crear una conexión.");
        return;
    }

    const nodeNames = currentNetwork.nodes.map(n => `${n.label} (${n.id})`).join('\n');
    
    const fromId = prompt(`ID del nodo ORIGEN:\n\n${nodeNames}`);
    if (!fromId) return;
    
    // Buscar nodo origen (por ID o etiqueta)
    const sourceNode = currentNetwork.nodes.find(n => n.id === fromId || n.label === fromId);
    if (!sourceNode) {
        alert("Nodo origen no encontrado.");
        return;
    }

    const toId = prompt(`ID del nodo DESTINO (conectado desde ${sourceNode.label}):`);
    if (!toId) return;

    // Buscar nodo destino
    const targetNode = currentNetwork.nodes.find(n => n.id === toId || n.label === toId);
    if (!targetNode) {
        alert("Nodo destino no encontrado.");
        return;
    }

    // Validaciones
    if (sourceNode.id === targetNode.id) {
        alert("No se permiten auto-bucles.");
        return;
    }

    const exists = currentNetwork.edges.find(e => e.from === sourceNode.id && e.to === targetNode.id);
    if (exists) {
        alert("Esa conexión ya existe.");
        return;
    }

    // Agregar arista
    currentNetwork.edges.push({ from: sourceNode.id, to: targetNode.id });
    
    // Resetear CPT del destino
    currentNetwork.cpt[targetNode.id] = {}; 

    renderNetwork(currentNetwork);
}

/**
 * Elimina los elementos seleccionados
 */
function removeSelected() {
    if (!networkDiagram) return;

    const selection = networkDiagram.getSelection();
    const selectedNodes = selection.nodes;
    const selectedEdges = selection.edges;

    if (selectedNodes.length === 0 && selectedEdges.length === 0) {
        alert("Selecciona nodos o líneas en el gráfico para eliminar.");
        return;
    }

    if (!confirm(`¿Estás seguro de eliminar los elementos seleccionados?`)) {
        return;
    }

    // 1. Eliminar Aristas Seleccionadas explícitamente
    // Vis.js devuelve los IDs de las aristas visuales. Necesitamos mapearlos.
    const visEdges = networkDiagram.body.data.edges;
    
    if (selectedEdges.length > 0 && selectedNodes.length === 0) {
        // Solo borrar aristas si no se borran nodos (si se borran nodos, las aristas se van solas)
        selectedEdges.forEach(visId => {
             const edgeObj = visEdges.get(visId);
             if (edgeObj) {
                 currentNetwork.edges = currentNetwork.edges.filter(e => !(e.from === edgeObj.from && e.to === edgeObj.to));
             }
         });
    }

    // 2. Eliminar Nodos (y sus aristas incidentes automáticamente)
    if (selectedNodes.length > 0) {
        // Filtrar nodos
        currentNetwork.nodes = currentNetwork.nodes.filter(n => !selectedNodes.includes(n.id));
        
        // Borrar CPTs
        selectedNodes.forEach(id => delete currentNetwork.cpt[id]);

        // Borrar aristas conectadas a estos nodos en el modelo de datos
        currentNetwork.edges = currentNetwork.edges.filter(e => {
            return !selectedNodes.includes(e.from) && !selectedNodes.includes(e.to);
        });
    }

    renderNetwork(currentNetwork);
    networkDiagram.unselectAll();
    updateNodeInfoPanel(null); // Limpiar panel lateral
}

// ========== RENDERIZADO Y SINCRONIZACIÓN ==========

function renderNetwork(net) {
    if (!networkDiagram) return;
=======
    networkDiagram.on('click', (params) => {
        if (params.nodes.length > 0) {
            showNodeDetails(params.nodes[0]);
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
    // SOLUCIÓN: Construir URL correctamente
    // Obtener BASE_URL desde el body data attribute (lo agregaremos en PHP)
    const baseUrl = document.body.dataset.baseUrl || window.location.origin;
    const url = `${baseUrl}/modules/bayesian/examples/${name}.json`;
    
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
            currentNetwork = data;
            renderNetwork(data);
            updateQueryVariables(data);
            showAlert('Ejemplo cargado: ' + (data.name || name), 'success');
            activateTab('network');
        })
        .catch(error => {
            console.error('❌ Error al cargar ejemplo:', error);
            showAlert('Error: ' + error.message, 'error');
        });
}

// ========== RENDER ==========
function renderNetwork(net) {
    if (!networkDiagram) {
        console.error('❌ networkDiagram no inicializado');
        return;
    }
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
    
    const nodes = new vis.DataSet(
        net.nodes.map(n => ({
            id: n.id,
<<<<<<< HEAD
            label: n.label,
            title: `Variable: ${n.label}` // Tooltip simple
=======
            label: n.label || n.id,
            title: n.description || n.label || n.id
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
        }))
    );
    
    const edges = new vis.DataSet(
        net.edges.map(e => ({
            from: e.from,
            to: e.to,
<<<<<<< HEAD
            arrows: 'to'
=======
            label: e.label || ''
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
        }))
    );
    
    networkDiagram.setData({ nodes, edges });
<<<<<<< HEAD
}

function fitNetwork() {
    if (networkDiagram) networkDiagram.fit({ animation: true });
}

// ========== UTILIDADES DE ARCHIVO Y EJEMPLOS ==========

function createNewNetwork(confirmAction = true) {
    if (confirmAction && !confirm("¿Borrar todo y crear una red nueva?")) return;
    
    currentNetwork = { name: 'Nueva Red', nodes: [], edges: [], cpt: {} };
    renderNetwork(currentNetwork);
    activateTab('network');
}

function saveNetwork() {
    // Aquí podrías implementar una llamada AJAX a PHP para guardar en base de datos
    alert("Función de guardar en servidor: En desarrollo.\nSe usará exportación local por ahora.");
    exportNetwork();
}

function exportNetwork() {
    const dataStr = JSON.stringify(currentNetwork, null, 2);
    const blob = new Blob([dataStr], { type: "application/json" });
    const url = URL.createObjectURL(blob);
    
    const a = document.createElement('a');
    a.href = url;
    a.download = (currentNetwork.name || "red_bayesiana").replace(/\s/g, '_') + ".json";
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

// Inicializar botones de ejemplos del sidebar
function initExampleButtons() {
    document.querySelectorAll('.btn-example').forEach(btn => {
        btn.addEventListener('click', () => {
            const exampleName = btn.dataset.example;
            if(exampleName) loadExample(exampleName);
        });
    });
}

function loadExample(name) {
    let exampleData = null;

    if (name === 'alarm') {
        exampleData = {
            name: "Alarma",
            nodes: [
                { id: "robo", label: "Robo" },
                { id: "terremoto", label: "Terremoto" },
                { id: "alarma", label: "Alarma" },
                { id: "juan", label: "Juan Llama" },
                { id: "maria", label: "Maria Llama" }
            ],
            edges: [
                { from: "robo", to: "alarma" },
                { from: "terremoto", to: "alarma" },
                { from: "alarma", to: "juan" },
                { from: "alarma", to: "maria" }
            ],
            cpt: {} 
        };
    } else if (name === 'medical') {
         exampleData = {
            name: "Diagnóstico Médico",
            nodes: [
                { id: "gripe", label: "Gripe" },
                { id: "absceso", label: "Absceso" },
                { id: "fiebre", label: "Fiebre" },
                { id: "cansancio", label: "Cansancio" }
            ],
            edges: [
                { from: "gripe", to: "fiebre" },
                { from: "gripe", to: "cansancio" },
                { from: "absceso", to: "fiebre" }
            ],
            cpt: {}
        };
    } else if (name === 'diagnostic') {
        exampleData = {
            name: "Diagnóstico de Fallas",
            nodes: [
                { id: "bateria", label: "Batería" },
                { id: "combustible", label: "Combustible" },
                { id: "encendido", label: "Encendido" },
                { id: "luces", label: "Luces" }
            ],
            edges: [
                { from: "bateria", to: "encendido" },
                { from: "bateria", to: "luces" },
                { from: "combustible", to: "encendido" }
            ],
            cpt: {}
        };
    } else if (name === 'weather') {
        exampleData = {
            name: "Predicción Climática",
            nodes: [
                { id: "nublado", label: "Nublado" },
                { id: "lluvia", label: "Lluvia" },
                { id: "aspersor", label: "Aspersor" },
                { id: "mojado", label: "Pasto Mojado" }
            ],
            edges: [
                { from: "nublado", to: "lluvia" },
                { from: "nublado", to: "aspersor" },
                { from: "lluvia", to: "mojado" },
                { from: "aspersor", to: "mojado" }
            ],
            cpt: {}
        };
    }

    if (exampleData) {
        if(confirm(`¿Cargar ejemplo "${exampleData.name}"? Se perderán los cambios actuales.`)) {
            currentNetwork = exampleData;
            renderNetwork(currentNetwork);
            // Asegurarse de ir al tab de red
            const tabBtn = document.querySelector('.tab-button[data-tab="network"]');
            if(tabBtn) tabBtn.click();
        }
    } else {
        alert("Ejemplo no encontrado: " + name);
    }
}

// ========== PLACEHOLDERS CPT ==========
function displayAllCPTs() {
    const container = document.getElementById('cpt-container');
    container.innerHTML = '';
    
    if (!currentNetwork.nodes || currentNetwork.nodes.length === 0) {
        container.innerHTML = '<div class="alert alert-warning">Agrega nodos primero para ver las tablas.</div>';
        return;
    }

    currentNetwork.nodes.forEach(node => {
        // Encontrar padres para mostrar estructura correcta
        const parents = currentNetwork.edges.filter(e => e.to === node.id).map(e => e.from);
        
        let html = `
            <div class="card mb-3 shadow-sm" style="max-width: 100%;">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <strong>${node.label}</strong>
                    <span class="badge bg-secondary">Padres: ${parents.length > 0 ? parents.join(', ') : 'Ninguno'}</span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-bordered mb-0 text-center table-sm">
                        <thead class="table-primary">
                            <tr>
                                ${parents.length > 0 ? '<th>Condición (Padres)</th>' : ''}
                                <th>True</th>
                                <th>False</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                ${parents.length > 0 ? '<td>...</td>' : ''}
                                <td>0.5</td>
                                <td>0.5</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        container.innerHTML += html;
    });
}

// ========== INFERENCIA (VISUAL) ==========
function updateQueryVariables() {
    const qContainer = document.getElementById('query-variables');
    const eContainer = document.getElementById('evidence-variables');
    
    if(!qContainer || !eContainer) return;

    qContainer.innerHTML = '';
    eContainer.innerHTML = '';
    
    if (!currentNetwork.nodes || currentNetwork.nodes.length === 0) {
        qContainer.innerHTML = '<p class="text-muted">Carga una red primero</p>';
        return;
    }

    // Botones para Query
    currentNetwork.nodes.forEach(n => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-primary btn-sm m-1';
        btn.innerText = n.label;
        btn.onclick = () => {
             // Marcar visualmente
             document.querySelectorAll('#query-variables .btn').forEach(b => b.classList.remove('active'));
             btn.classList.add('active');
             selectedQueryVariable = n.id;
        };
        qContainer.appendChild(btn);
    });

    // Botones para Evidencia
    currentNetwork.nodes.forEach(n => {
        const btn = document.createElement('button');
        btn.className = 'btn btn-outline-secondary btn-sm m-1';
        btn.innerText = n.label;
        btn.onclick = () => {
             const val = prompt(`Valor para ${n.label} (True/False):`, "True");
             if (val) {
                 evidenceVariables[n.id] = val;
                 updateEvidenceDisplay();
             }
        };
        eContainer.appendChild(btn);
    });
}

function updateEvidenceDisplay() {
    const container = document.getElementById('evidence-inputs');
    if(!container) return;

    if (Object.keys(evidenceVariables).length === 0) {
        container.innerHTML = '<p class="text-muted"><i class="fas fa-info-circle"></i> No hay evidencia seleccionada</p>';
        return;
    }
    
    let html = '<ul class="list-group">';
    for (const [id, val] of Object.entries(evidenceVariables)) {
        html += `<li class="list-group-item d-flex justify-content-between align-items-center py-1">
                    ${id} = ${val}
                    <button class="btn btn-danger btn-sm py-0 px-2" onclick="delete evidenceVariables['${id}']; updateEvidenceDisplay();">x</button>
                 </li>`;
    }
    html += '</ul>';
    container.innerHTML = html;
}

function runInference() {
    const resContainer = document.getElementById('results-container');
    
    if (!selectedQueryVariable) {
        alert("Selecciona una variable a consultar primero.");
        return;
    }

    // Obtener información del nodo consultado
    const queryNode = currentNetwork.nodes.find(n => n.id === selectedQueryVariable);
    const parents = currentNetwork.edges.filter(e => e.to === selectedQueryVariable).map(e => e.from);
    
    // Calcular resultado simulado
    const resultProb = (Math.random() * 0.5 + 0.2).toFixed(4);
    const falseProb = (1 - parseFloat(resultProb)).toFixed(4);
    
    // Generar explicación del proceso
    let evidenceText = Object.keys(evidenceVariables).length > 0 
        ? Object.entries(evidenceVariables).map(([k, v]) => `${k}=${v}`).join(', ')
        : 'Sin evidencia';
    
    if(resContainer) {
        resContainer.innerHTML = `
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient text-white text-center py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h4 class="mb-0"><i class="fas fa-brain"></i> Resultado de la Inferencia</h4>
                </div>
                
                <div class="card-body p-4">
                    <!-- Consulta -->
                    <div class="alert alert-info border-start border-4 border-info">
                        <h5 class="alert-heading mb-2"><i class="fas fa-question-circle"></i> Consulta</h5>
                        <p class="mb-0"><strong>Variable:</strong> ${queryNode.label} (${selectedQueryVariable})</p>
                        <p class="mb-0"><strong>Evidencia:</strong> ${evidenceText}</p>
                    </div>
                    
                    <!-- Proceso de Cálculo -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><i class="fas fa-calculator"></i> Proceso de Cálculo</h5>
                            <ol class="mb-0">
                                <li class="mb-2">
                                    <strong>Identificación de dependencias:</strong><br>
                                    <small class="text-muted">
                                        ${parents.length > 0 
                                            ? `La variable <code>${selectedQueryVariable}</code> depende de: <code>${parents.join(', ')}</code>`
                                            : `La variable <code>${selectedQueryVariable}</code> no tiene padres (es independiente)`
                                        }
                                    </small>
                                </li>
                                <li class="mb-2">
                                    <strong>Aplicación de evidencia:</strong><br>
                                    <small class="text-muted">
                                        ${Object.keys(evidenceVariables).length > 0
                                            ? `Se aplicaron las observaciones: ${evidenceText}`
                                            : 'No se aplicó evidencia, usando probabilidades a priori'
                                        }
                                    </small>
                                </li>
                                <li class="mb-2">
                                    <strong>Algoritmo utilizado:</strong><br>
                                    <small class="text-muted">Eliminación de Variables (Variable Elimination)</small>
                                </li>
                                <li class="mb-0">
                                    <strong>Normalización:</strong><br>
                                    <small class="text-muted">Las probabilidades se normalizaron para sumar 1.0</small>
                                </li>
                            </ol>
                        </div>
                    </div>
                    
                    <!-- Resultado Principal -->
                    <div class="text-center p-4 mb-3" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 15px;">
                        <h3 class="text-primary mb-3">
                            <i class="fas fa-chart-pie"></i> Distribución de Probabilidad
                        </h3>
                        <div class="row justify-content-center">
                            <div class="col-md-5">
                                <div class="card border-success shadow-sm">
                                    <div class="card-body">
                                        <h6 class="text-success mb-2">P(${selectedQueryVariable} = True | E)</h6>
                                        <h2 class="display-4 text-success mb-0">${resultProb}</h2>
                                        <small class="text-muted">${(parseFloat(resultProb) * 100).toFixed(2)}%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="card border-danger shadow-sm">
                                    <div class="card-body">
                                        <h6 class="text-danger mb-2">P(${selectedQueryVariable} = False | E)</h6>
                                        <h2 class="display-4 text-danger mb-0">${falseProb}</h2>
                                        <small class="text-muted">${(parseFloat(falseProb) * 100).toFixed(2)}%</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Barra de progreso visual -->
                        <div class="mt-4">
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: ${parseFloat(resultProb) * 100}%"
                                     aria-valuenow="${parseFloat(resultProb) * 100}" aria-valuemin="0" aria-valuemax="100">
                                    True: ${(parseFloat(resultProb) * 100).toFixed(1)}%
                                </div>
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: ${parseFloat(falseProb) * 100}%"
                                     aria-valuenow="${parseFloat(falseProb) * 100}" aria-valuemin="0" aria-valuemax="100">
                                    False: ${(parseFloat(falseProb) * 100).toFixed(1)}%
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Interpretación -->
                    <div class="alert alert-light border-start border-4 border-warning">
                        <h5 class="alert-heading mb-2"><i class="fas fa-lightbulb"></i> Interpretación</h5>
                        <p class="mb-0">
                            ${parseFloat(resultProb) > 0.5 
                                ? `Hay una <strong>alta probabilidad (${(parseFloat(resultProb) * 100).toFixed(1)}%)</strong> de que <code>${selectedQueryVariable}</code> sea <strong>verdadero</strong> dada la evidencia observada.`
                                : parseFloat(resultProb) < 0.3
                                    ? `Hay una <strong>baja probabilidad (${(parseFloat(resultProb) * 100).toFixed(1)}%)</strong> de que <code>${selectedQueryVariable}</code> sea verdadero, es más probable que sea <strong>falso</strong>.`
                                    : `La probabilidad de que <code>${selectedQueryVariable}</code> sea verdadero es <strong>moderada (${(parseFloat(resultProb) * 100).toFixed(1)}%)</strong>, cercana a la incertidumbre.`
                            }
                        </p>
                    </div>
                    
                    <!-- Nota -->
                    <div class="text-center text-muted mt-3">
                        <small><i class="fas fa-info-circle"></i> Este es un resultado simulado para demostración. 
                        En producción, se utilizaría el algoritmo real de inferencia bayesiana.</small>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Activar tab resultados si existe
    const resultTabBtn = document.querySelector('.tab-button[data-tab="results"]');
    if(resultTabBtn) resultTabBtn.click();
}
=======
    
    setTimeout(() => {
        networkDiagram.fit({
            animation: {
                duration: 1000,
                easingFunction: 'easeInOutQuad'
            }
        });
    }, 100);
    
    updateNetworkInfo(net);
    console.log('✅ Red renderizada:', net.name);
}

// ========== CPT ==========
function displayAllCPTs() {
    const container = document.getElementById('cpt-container');
    
    if (!container || !currentNetwork) {
        console.error('❌ No se puede mostrar CPT');
        return;
    }
    
    if (!currentNetwork.cpt || Object.keys(currentNetwork.cpt).length === 0) {
        container.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> Sin tablas CPT definidas
            </div>
        `;
        return;
    }
    
    let html = '<h3 style="color: #2563eb; margin-bottom: 20px;">Tablas de Probabilidad Condicional</h3>';
    
    currentNetwork.nodes.forEach(node => {
        const cpt = currentNetwork.cpt[node.id];
        if (!cpt) return;
        
        html += `
            <div class="cpt-card" style="background: #fff; border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h4 style="color: #1e40af; margin-bottom: 10px; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-table"></i> ${node.label || node.id}
                </h4>
                <p style="color: #6b7280; margin-bottom: 15px; font-size: 0.95em;">${node.description || 'Sin descripción'}</p>
                ${generateCPTTable(node.id, cpt)}
            </div>
        `;
    });
    
    container.innerHTML = html;
    console.log('✅ CPTs mostradas');
}

function generateCPTTable(nodeId, cptData) {
    let html = '<div style="overflow-x: auto;"><table class="cpt-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
    
    const entries = Object.entries(cptData);
    
    // CPT simple (sin padres)
    if (entries.length > 0 && typeof entries[0][1] === 'number') {
        html += '<thead style="background: #2563eb; color: white;"><tr>';
        html += '<th style="padding: 12px; border: 1px solid #ddd;">Valor</th>';
        html += '<th style="padding: 12px; border: 1px solid #ddd;">Probabilidad</th>';
        html += '</tr></thead><tbody>';
        
        entries.forEach(([k, p]) => {
            const pct = (p * 100).toFixed(2);
            html += `
                <tr style="background: #f9fafb;">
                    <td style="padding: 12px; border: 1px solid #ddd; font-weight: 600;">${k}</td>
                    <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                        <span style="color: #2563eb; font-weight: bold;">${p.toFixed(4)}</span>
                        <span style="color: #6b7280; margin-left: 8px;">(${pct}%)</span>
                    </td>
                </tr>
            `;
        });
    } else {
        // CPT condicional (con padres)
        html += '<thead style="background: #2563eb; color: white;"><tr>';
        html += '<th style="padding: 12px; border: 1px solid #ddd;">Condición</th>';
        html += '<th style="padding: 12px; border: 1px solid #ddd;">Distribución</th>';
        html += '</tr></thead><tbody>';
        
        entries.forEach(([cond, dist]) => {
            let condText = cond;
            try {
                const o = JSON.parse(cond);
                condText = Object.entries(o).map(([k, v]) => `${k}=${v}`).join(', ');
            } catch (e) {}
            
            html += `<tr style="background: #f9fafb;">`;
            html += `<td style="padding: 12px; border: 1px solid #ddd; font-weight: 600;">${condText}</td>`;
            html += `<td style="padding: 12px; border: 1px solid #ddd;">`;
            html += '<div style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center;">';
            
            Object.entries(dist).forEach(([v, p]) => {
                const pct = (p * 100).toFixed(2);
                html += `
                    <div style="text-align: center;">
                        <div style="font-size: 0.85em; color: #6b7280;">${v}</div>
                        <div style="color: #2563eb; font-weight: bold; font-size: 1.1em;">${p.toFixed(3)}</div>
                        <div style="font-size: 0.8em; color: #9ca3af;">${pct}%</div>
                    </div>
                `;
            });
            
            html += '</div></td></tr>';
        });
    }
    
    html += '</tbody></table></div>';
    return html;
}

// ========== UTILIDADES ==========
function updateNetworkInfo(net) {
    const info = document.getElementById('node-info');
    const details = document.getElementById('node-details');
    
    if (!info || !details) return;
    
    info.style.display = 'block';
    details.innerHTML = `
        <div class="network-metadata">
            <h5>${net.name || 'Red Bayesiana'}</h5>
            <p>${net.description || ''}</p>
            <div class="network-stats" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 15px;">
                <div class="stat-item" style="background: #f3f4f6; padding: 10px; border-radius: 6px; text-align: center;">
                    <div style="font-size: 0.9em; color: #6b7280;">Nodos</div>
                    <div style="font-size: 1.5em; font-weight: bold; color: #2563eb;">${net.nodes.length}</div>
                </div>
                <div class="stat-item" style="background: #f3f4f6; padding: 10px; border-radius: 6px; text-align: center;">
                    <div style="font-size: 0.9em; color: #6b7280;">Aristas</div>
                    <div style="font-size: 1.5em; font-weight: bold; color: #2563eb;">${net.edges.length}</div>
                </div>
                <div class="stat-item" style="background: #f3f4f6; padding: 10px; border-radius: 6px; text-align: center;">
                    <div style="font-size: 0.9em; color: #6b7280;">Dominio</div>
                    <div style="font-size: 1em; font-weight: bold; color: #2563eb;">${net.domain || 'General'}</div>
                </div>
            </div>
        </div>
    `;
}

function updateQueryVariables(net) {
    const qv = document.getElementById('query-variables');
    const ev = document.getElementById('evidence-variables');
    
    if (qv) {
        qv.innerHTML = '';
        net.nodes.forEach(n => {
            const b = document.createElement('button');
            b.className = 'btn btn-outline-primary btn-sm';
            b.style.margin = '5px';
            b.textContent = n.label || n.id;
            b.onclick = () => selectQueryVariable(n.id, n.label || n.id);
            qv.appendChild(b);
        });
    }
    
    if (ev) {
        ev.innerHTML = '';
        net.nodes.forEach(n => {
            const b = document.createElement('button');
            b.className = 'btn btn-outline-secondary btn-sm';
            b.style.margin = '5px';
            b.textContent = n.label || n.id;
            b.onclick = () => selectEvidenceVariable(n.id, n.label || n.id);
            ev.appendChild(b);
        });
    }
}

function showNodeDetails(nodeId) {
    if (!currentNetwork) return;
    
    const node = currentNetwork.nodes.find(n => n.id === nodeId);
    if (!node) return;
    
    const info = document.getElementById('node-info');
    const details = document.getElementById('node-details');
    
    if (info && details) {
        info.style.display = 'block';
        details.innerHTML = `
            <h5 style="color: #2563eb; margin-bottom: 10px;">${node.label || node.id}</h5>
            <p style="color: #6b7280; margin-bottom: 15px;">${node.description || 'Sin descripción'}</p>
        `;
        
        if (currentNetwork.cpt && currentNetwork.cpt[nodeId]) {
            details.innerHTML += '<h6 style="color: #1e40af; margin-top: 15px; margin-bottom: 10px;">Tabla CPT:</h6>';
            details.innerHTML += generateCPTTable(nodeId, currentNetwork.cpt[nodeId]);
        }
    }
}

function selectQueryVariable(nodeId, label) {
    selectedQueryVariable = nodeId;
    
    document.querySelectorAll('#query-variables .btn').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline-primary');
    });
    
    event.target.classList.remove('btn-outline-primary');
    event.target.classList.add('btn-primary');
    
    showAlert('Variable seleccionada: ' + label, 'info');
}

function selectEvidenceVariable(nodeId, label) {
    if (evidenceVariables[nodeId]) {
        delete evidenceVariables[nodeId];
        event.target.classList.remove('btn-secondary');
        event.target.classList.add('btn-outline-secondary');
    } else {
        const v = prompt(`Valor conocido para ${label}:`);
        if (v) {
            evidenceVariables[nodeId] = v;
            event.target.classList.remove('btn-outline-secondary');
            event.target.classList.add('btn-secondary');
            showAlert(`Evidencia: ${label} = ${v}`, 'success');
        }
    }
}

function runInference() {
    if (!currentNetwork) {
        showAlert('No hay red cargada', 'warning');
        return;
    }
    
    if (!selectedQueryVariable) {
        showAlert('Selecciona una variable primero', 'warning');
        return;
    }
    
    showAlert('Función de inferencia en desarrollo', 'info');
    activateTab('results');
}

// ========== MENSAJES ==========
function showAlert(msg, type = 'info') {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
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

// ========== ACCIONES RÁPIDAS ==========
function createNewNetwork() {
    if (currentNetwork && !confirm('¿Descartar red actual?')) return;
    currentNetwork = { name: 'Nueva Red', nodes: [], edges: [], cpt: {} };
    renderNetwork(currentNetwork);
    showAlert('Nueva red creada', 'success');
    activateTab('network');
}

function exportNetwork() {
    if (!currentNetwork) {
        showAlert('No hay red para exportar', 'warning');
        return;
    }
    
    const dataStr = JSON.stringify(currentNetwork, null, 2);
    const blob = new Blob([dataStr], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = (currentNetwork.name || 'red_bayesiana').replace(/\s+/g, '_') + '.json';
    a.click();
    URL.revokeObjectURL(url);
    showAlert('Red exportada', 'success');
}

function addNode() {
    const name = prompt('Nombre del nodo:');
    if (!name) return;
    if (!currentNetwork) createNewNetwork();
    
    const id = name.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '');
    if (currentNetwork.nodes.find(n => n.id === id)) {
        showAlert('Nodo duplicado', 'warning');
        return;
    }
    
    currentNetwork.nodes.push({ id, label: name, description: '' });
    renderNetwork(currentNetwork);
    updateQueryVariables(currentNetwork);
    showAlert('Nodo agregado: ' + name, 'success');
}

function addEdge() {
    if (!currentNetwork || currentNetwork.nodes.length < 2) {
        showAlert('Necesitas al menos 2 nodos', 'warning');
        return;
    }
    
    const nodesList = currentNetwork.nodes.map(n => n.id).join(', ');
    const from = prompt(`Nodo origen (${nodesList}):`);
    const to = prompt(`Nodo destino (${nodesList}):`);
    
    if (!from || !to) return;
    
    if (!currentNetwork.nodes.find(n => n.id === from) || !currentNetwork.nodes.find(n => n.id === to)) {
        showAlert('Nodos no existen', 'error');
        return;
    }
    
    currentNetwork.edges.push({ from, to });
    renderNetwork(currentNetwork);
    showAlert('Arista agregada', 'success');
}

function removeSelected() {
    showAlert('Función en desarrollo', 'info');
}

function saveNetwork() {
    exportNetwork();
}

function fitNetwork() {
    networkDiagram?.fit({
        animation: {
            duration: 500,
            easingFunction: 'easeInOutQuad'
        }
    });
}

// ========== ANIMACIÓN ==========
const bayesianStyle = document.createElement('style');
bayesianStyle.textContent = `
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(400px); }
        to { opacity: 1; transform: translateX(0); }
    }
`;
document.head.appendChild(bayesianStyle);
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
