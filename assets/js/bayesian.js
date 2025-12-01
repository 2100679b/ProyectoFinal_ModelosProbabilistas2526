/**
 * JavaScript para Módulo de Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

// ========== VARIABLES GLOBALES ==========
let currentNetwork = null;
let networkDiagram = null;
let selectedQueryVariable = null;
let evidenceVariables = {};

// ========== INICIALIZACIÓN ==========
document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ Módulo de Redes Bayesianas iniciado');
    initTabs();
    initNetworkDiagram();
    initExampleButtons();
});

// ========== TABS ==========
function initTabs() {
    document.querySelectorAll('.bayesian-tabs .tab-button').forEach(btn => {
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
    document.querySelectorAll('.bayesian-tabs .tab-button').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => {
        p.classList.remove('active');
        p.style.display = 'none';
    });
    
    // Activar seleccionado
    const btn = document.querySelector(`.bayesian-tabs .tab-button[data-tab="${tabId}"]`);
    const panel = document.getElementById(`tab-${tabId}`);
    
    if (btn) btn.classList.add('active');
    if (panel) {
        panel.classList.add('active');
        panel.style.display = 'block';
        
        // IMPORTANTE: Solo cargar CPT cuando se activa el tab
        if (tabId === 'probabilities' && currentNetwork) {
            console.log('📊 Cargando CPTs...');
            displayAllCPTs();
        }
    }
}

// ========== DIAGRAMA VIS.JS ==========
function initNetworkDiagram() {
    const container = document.getElementById('network-visualization');
    if (!container) {
        console.error('❌ No se encontró #network-visualization');
        return;
    }
    
    const data = {
        nodes: new vis.DataSet([]),
        edges: new vis.DataSet([])
    };
    
    const options = {
        nodes: {
            shape: 'box',
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
        }
    };
    
    networkDiagram = new vis.Network(container, data, options);
    
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
    
    const nodes = new vis.DataSet(
        net.nodes.map(n => ({
            id: n.id,
            label: n.label || n.id,
            title: n.description || n.label || n.id
        }))
    );
    
    const edges = new vis.DataSet(
        net.edges.map(e => ({
            from: e.from,
            to: e.to,
            label: e.label || ''
        }))
    );
    
    networkDiagram.setData({ nodes, edges });
    
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
