/**
 * ==============================================================================
 * VISUALIZACIÓN - FUNCIONES AUXILIARES
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Archivo: visualization.js
 * Descripción: Clases reutilizables para gráficos con estilos unificados.
 * ==============================================================================
 */

// ==============================================================================
// 1. VISUALIZACIÓN DE REDES BAYESIANAS (Tema Azul)
// ==============================================================================
class BayesianNetworkVisualizer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.network = null;
        this.nodes = new vis.DataSet();
        this.edges = new vis.DataSet();
        
        // Opciones por defecto (Tema Azul)
        this.options = {
            nodes: {
                shape: 'box',
                margin: 10,
                color: { 
                    background: '#dbeafe', 
                    border: '#2563eb', 
                    highlight: { background: '#bfdbfe', border: '#1d4ed8' } 
                },
                font: { size: 16, color: '#1e40af', face: 'arial' },
                borderWidth: 2,
                shadow: true
            },
            edges: {
                arrows: { to: { enabled: true, scaleFactor: 1.2 } },
                smooth: { type: 'cubicBezier' },
                color: { color: '#94a3b8', highlight: '#2563eb' }
            },
            physics: {
                enabled: true,
                stabilization: false,
                barnesHut: { gravitationalConstant: -2000, springLength: 200 }
            },
            interaction: {
                hover: true,
                tooltipDelay: 200
            }
        };
    }

    render(bayesianNet) {
        if (!this.container) return;
        
        this.nodes.clear();
        this.edges.clear();

        // Agregar nodos
        bayesianNet.nodes.forEach((node) => {
            this.nodes.add({
                id: node.id,
                label: node.label || node.name || node.id,
                title: node.description || ''
            });
        });

        // Agregar aristas
        const edgesList = bayesianNet.edges || bayesianNet.getEdges();
        edgesList.forEach(edge => {
            this.edges.add({
                from: edge.from,
                to: edge.to
            });
        });

        const data = { nodes: this.nodes, edges: this.edges };
        
        // Destruir red previa si existe para liberar memoria
        if (this.network) this.network.destroy();
        
        this.network = new vis.Network(this.container, data, this.options);
        return this.network;
    }
}

// ==============================================================================
// 2. VISUALIZACIÓN DE CADENAS DE MARKOV (Tema Esmeralda/Verde)
// ==============================================================================
class MarkovChainVisualizer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.network = null;
        this.nodes = new vis.DataSet();
        this.edges = new vis.DataSet();

        // Opciones por defecto (Tema Esmeralda)
        this.options = {
            nodes: {
                shape: 'circle',
                margin: 10,
                color: { 
                    background: '#d1fae5', 
                    border: '#059669', 
                    highlight: { background: '#a7f3d0', border: '#047857' }
                },
                font: { size: 14, color: '#064e3b', face: 'arial bold' }, // ✅ Corregido
                borderWidth: 2,
                shadow: true
            },
            edges: {
                arrows: { to: { enabled: true, scaleFactor: 1 } },
                color: { color: '#6b7280', highlight: '#059669' },
                smooth: { type: 'curvedCW', roundness: 0.2 },
                font: { align: 'horizontal', size: 12, background: 'rgba(255,255,255,0.7)' }
            },
            physics: {
                enabled: true,
                stabilization: false,
                barnesHut: { gravitationalConstant: -2000, springLength: 150 }
            },
            interaction: { hover: true }
        };
    }

    render(markovChain) {
        if (!this.container) return;

        this.nodes.clear();
        this.edges.clear();

        // Agregar nodos
        const states = markovChain.states || [];
        states.forEach(state => {
            this.nodes.add({
                id: state.id,
                label: state.label
            });
        });

        // Agregar transiciones
        const matrix = markovChain.transitionMatrix || {};
        Object.keys(matrix).forEach(fromId => {
            const targets = matrix[fromId];
            Object.keys(targets).forEach(toId => {
                const prob = targets[toId];
                if (prob > 0) {
                    this.edges.add({
                        from: fromId,
                        to: toId,
                        label: prob.toFixed(2),
                        color: { color: prob > 0.8 ? '#059669' : '#6b7280' }
                    });
                }
            });
        });

        const data = { nodes: this.nodes, edges: this.edges };
        
        if (this.network) this.network.destroy();
        this.network = new vis.Network(this.container, data, this.options);
        return this.network;
    }
}

// ==============================================================================
// 3. VISUALIZACIÓN DE HMM (Tema Púrpura)
// ==============================================================================
class HMMVisualizer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.network = null;
        this.nodes = new vis.DataSet();
        this.edges = new vis.DataSet();

        this.options = {
            nodes: {
                shape: 'box',
                margin: 10,
                borderWidth: 2,
                shadow: true,
                font: { face: 'arial' }
            },
            edges: {
                arrows: { to: { enabled: true, scaleFactor: 1 } },
                smooth: { type: 'cubicBezier' },
                font: { align: 'middle', size: 11 }
            },
            layout: {
                hierarchical: {
                    enabled: true,
                    direction: 'LR',
                    sortMethod: 'directed',
                    levelSeparation: 200,
                    nodeSpacing: 100
                }
            },
            physics: { enabled: false }, // HMM se ve mejor estático/jerárquico
            interaction: { hover: true, dragNodes: true }
        };
    }

    render(hmm) {
        if (!this.container) return;

        this.nodes.clear();
        this.edges.clear();

        // Estados Ocultos (Nivel 0)
        hmm.hiddenStates.forEach(state => {
            this.nodes.add({
                id: `hidden_${state.id}`,
                label: `🔒 ${state.label}`,
                level: 0,
                color: { 
                    background: '#f3e8ff', 
                    border: '#9333ea', 
                    highlight: { background: '#e9d5ff', border: '#7e22ce' }
                },
                font: { color: '#581c87', face: 'arial bold' }
            });
        });

        // Observaciones (Nivel 1)
        hmm.observations.forEach(obs => {
            this.nodes.add({
                id: `obs_${obs.id}`,
                label: `👁️ ${obs.label}`,
                level: 1,
                color: { 
                    background: '#ddd6fe', 
                    border: '#7c3aed', 
                    highlight: { background: '#c4b5fd', border: '#6d28d9' }
                },
                font: { color: '#4c1d95' }
            });
        });

        // Transiciones
        if (hmm.transitionMatrix) {
            Object.entries(hmm.transitionMatrix).forEach(([from, targets]) => {
                Object.entries(targets).forEach(([to, prob]) => {
                    if (prob > 0) {
                        this.edges.add({
                            from: `hidden_${from}`,
                            to: `hidden_${to}`,
                            label: prob.toFixed(2),
                            color: { color: '#9333ea' }
                        });
                    }
                });
            });
        }

        // Emisiones
        if (hmm.emissionMatrix) {
            Object.entries(hmm.emissionMatrix).forEach(([state, emissions]) => {
                Object.entries(emissions).forEach(([obs, prob]) => {
                    if (prob > 0) {
                        this.edges.add({
                            from: `hidden_${state}`,
                            to: `obs_${obs}`,
                            label: prob.toFixed(2),
                            color: { color: '#c026d3' },
                            dashes: true
                        });
                    }
                });
            });
        }

        const data = { nodes: this.nodes, edges: this.edges };
        
        if (this.network) this.network.destroy();
        this.network = new vis.Network(this.container, data, this.options);
        return this.network;
    }
}

// ==============================================================================
// 4. GRÁFICOS DE PROBABILIDAD (Chart.js Wrapper)
// ==============================================================================
class ProbabilityChart {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        this.chart = null;
    }

    renderBarChart(labels, data, title = 'Distribución de Probabilidad', color = '#3b82f6') {
        if (!this.canvas) return;
        
        const ctx = this.canvas.getContext('2d');
        if (this.chart) this.chart.destroy();

        // Verificar si Chart.js está cargado
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js no está cargado');
            return;
        }

        this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Probabilidad',
                    data: data,
                    backgroundColor: color + '80', // Opacidad 50%
                    borderColor: color,
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 1,
                        ticks: { format: { style: 'percent' } }
                    }
                },
                plugins: {
                    title: { display: true, text: title },
                    legend: { display: false }
                }
            }
        });
    }
}

// ==============================================================================
// 5. UTILIDADES DE ANIMACIÓN
// ==============================================================================
const AnimationUtils = {
    /**
     * Anima un número en un elemento DOM
     */
    animateValue(element, start, end, duration = 1000) {
        if (!element) return;
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            element.innerHTML = value; // O toFixed si son decimales
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    },

    /**
     * Resalta un elemento temporalmente (Flash)
     */
    flashElement(element, color = '#fef3c7', duration = 500) {
        if (!element) return;
        const originalTransition = element.style.transition;
        const originalBg = element.style.backgroundColor;

        element.style.transition = 'background-color 0.2s ease';
        element.style.backgroundColor = color;

        setTimeout(() => {
            element.style.backgroundColor = originalBg;
            setTimeout(() => {
                element.style.transition = originalTransition;
            }, 200);
        }, duration);
    }
};

// ==============================================================================
// EXPORTAR
// ==============================================================================
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        BayesianNetworkVisualizer,
        MarkovChainVisualizer,
        HMMVisualizer,
        ProbabilityChart,
        AnimationUtils
    };
} else {
    // Exponer al scope global del navegador
    window.AppViz = {
        Bayesian: BayesianNetworkVisualizer,
        Markov: MarkovChainVisualizer,
        HMM: HMMVisualizer,
        Chart: ProbabilityChart,
        Anim: AnimationUtils
    };
}