/**
 * ==============================================================================
 * VISUALIZACIÓN - FUNCIONES AUXILIARES
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Archivo: visualization.js
 * ==============================================================================
 */

// ==============================================================================
// VISUALIZACIÓN DE REDES BAYESIANAS
// ==============================================================================
class BayesianNetworkVisualizer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.network = null;
        this.nodes = new vis.DataSet();
        this.edges = new vis.DataSet();
    }

    /**
     * Renderiza una red bayesiana
     * @param {BayesianNetwork} bayesianNet - Red bayesiana a visualizar
     */
    render(bayesianNet) {
        // Limpiar datos previos
        this.nodes.clear();
        this.edges.clear();

        // Agregar nodos
        bayesianNet.nodes.forEach((node, id) => {
            this.nodes.add({
                id: id,
                label: node.name,
                color: {
                    background: '#97C2FC',
                    border: '#2B7CE9',
                    highlight: {
                        background: '#4CAF50',
                        border: '#2B7CE9'
                    }
                },
                font: { color: '#ffffff', size: 14, face: 'Arial', bold: true }
            });
        });

        // Agregar aristas
        const edgesList = bayesianNet.getEdges();
        edgesList.forEach(edge => {
            this.edges.add({
                from: edge.from,
                to: edge.to,
                arrows: 'to',
                color: { color: '#848484', highlight: '#2B7CE9' },
                width: 2
            });
        });

        // Configurar opciones de visualización
        const options = {
            layout: {
                hierarchical: {
                    direction: 'UD',
                    sortMethod: 'directed',
                    nodeSpacing: 150,
                    levelSeparation: 150
                }
            },
            physics: {
                enabled: false
            },
            interaction: {
                hover: true,
                tooltipDelay: 200
            }
        };

        // Crear la red
        const data = { nodes: this.nodes, edges: this.edges };
        this.network = new vis.Network(this.container, data, options);

        return this.network;
    }

    /**
     * Resalta un nodo
     */
    highlightNode(nodeId, color = '#FFD700') {
        this.nodes.update({
            id: nodeId,
            color: { background: color }
        });
    }

    /**
     * Resetea los colores de todos los nodos
     */
    resetColors() {
        this.nodes.forEach(node => {
            this.nodes.update({
                id: node.id,
                color: { background: '#97C2FC', border: '#2B7CE9' }
            });
        });
    }
}

// ==============================================================================
// VISUALIZACIÓN DE CADENAS DE MARKOV
// ==============================================================================
class MarkovChainVisualizer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.network = null;
        this.nodes = new vis.DataSet();
        this.edges = new vis.DataSet();
    }

    /**
     * Renderiza una cadena de Markov
     * @param {MarkovChain} markovChain - Cadena de Markov a visualizar
     */
    render(markovChain) {
        this.nodes.clear();
        this.edges.clear();

        // Agregar nodos (estados)
        markovChain.states.forEach((state, index) => {
            this.nodes.add({
                id: index,
                label: state,
                color: {
                    background: '#9b59b6',
                    border: '#8e44ad'
                },
                font: { color: '#ffffff', size: 14 }
            });
        });

        // Agregar aristas (transiciones)
        markovChain.transitionMatrix.forEach((row, fromIndex) => {
            row.forEach((prob, toIndex) => {
                if (prob > 0.01) { // Solo mostrar transiciones significativas
                    this.edges.add({
                        from: fromIndex,
                        to: toIndex,
                        label: prob.toFixed(2),
                        arrows: 'to',
                        color: { color: '#848484' },
                        width: Math.max(1, prob * 5),
                        font: { size: 12, align: 'middle' }
                    });
                }
            });
        });

        const options = {
            layout: {
                randomSeed: 2
            },
            physics: {
                stabilization: true,
                barnesHut: {
                    gravitationalConstant: -2000,
                    springConstant: 0.001,
                    springLength: 200
                }
            }
        };

        const data = { nodes: this.nodes, edges: this.edges };
        this.network = new vis.Network(this.container, data, options);

        return this.network;
    }

    /**
     * Anima una transición entre estados
     */
    animateTransition(fromState, toState, duration = 1000) {
        // Resaltar estado actual
        this.highlightNode(fromState, '#FFD700');
        
        setTimeout(() => {
            this.highlightNode(toState, '#4CAF50');
            setTimeout(() => {
                this.resetColors();
            }, duration / 2);
        }, duration / 2);
    }

    highlightNode(nodeId, color) {
        this.nodes.update({
            id: nodeId,
            color: { background: color }
        });
    }

    resetColors() {
        this.nodes.forEach(node => {
            this.nodes.update({
                id: node.id,
                color: { background: '#9b59b6', border: '#8e44ad' }
            });
        });
    }
}

// ==============================================================================
// VISUALIZACIÓN DE HMM
// ==============================================================================
class HMMVisualizer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.network = null;
        this.nodes = new vis.DataSet();
        this.edges = new vis.DataSet();
    }

    /**
     * Renderiza un HMM
     * @param {HiddenMarkovModel} hmm - HMM a visualizar
     */
    render(hmm) {
        this.nodes.clear();
        this.edges.clear();

        const stateY = 100;
        const obsY = 300;
        const spacing = 200;

        // Agregar nodos de estados ocultos
        hmm.states.forEach((state, index) => {
            this.nodes.add({
                id: `state_${index}`,
                label: state,
                x: index * spacing,
                y: stateY,
                color: {
                    background: '#16a085',
                    border: '#138d75'
                },
                font: { color: '#ffffff', size: 14 },
                shape: 'ellipse',
                fixed: true
            });
        });

        // Agregar nodos de observaciones
        hmm.observations.forEach((obs, index) => {
            this.nodes.add({
                id: `obs_${index}`,
                label: obs,
                x: index * spacing,
                y: obsY,
                color: {
                    background: '#e67e22',
                    border: '#d35400'
                },
                font: { color: '#ffffff', size: 12 },
                shape: 'box',
                fixed: true
            });
        });

        // Agregar transiciones entre estados
        hmm.transitionMatrix.forEach((row, fromIndex) => {
            row.forEach((prob, toIndex) => {
                if (prob > 0.01) {
                    this.edges.add({
                        from: `state_${fromIndex}`,
                        to: `state_${toIndex}`,
                        label: prob.toFixed(2),
                        arrows: 'to',
                        color: { color: '#16a085' },
                        width: Math.max(1, prob * 3)
                    });
                }
            });
        });

        // Agregar emisiones (estado -> observación)
        hmm.emissionMatrix.forEach((row, stateIndex) => {
            row.forEach((prob, obsIndex) => {
                if (prob > 0.01) {
                    this.edges.add({
                        from: `state_${stateIndex}`,
                        to: `obs_${obsIndex}`,
                        label: prob.toFixed(2),
                        arrows: 'to',
                        color: { color: '#e67e22' },
                        dashes: true,
                        width: Math.max(1, prob * 3)
                    });
                }
            });
        });

        const options = {
            physics: { enabled: false },
            interaction: { dragNodes: false }
        };

        const data = { nodes: this.nodes, edges: this.edges };
        this.network = new vis.Network(this.container, data, options);

        return this.network;
    }
}

// ==============================================================================
// GRÁFICOS DE PROBABILIDAD (usando Chart.js si está disponible)
// ==============================================================================
class ProbabilityChart {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas.getContext('2d');
        this.chart = null;
    }

    /**
     * Crea un gráfico de barras para probabilidades
     */
    renderBarChart(labels, data, title = 'Distribución de Probabilidad') {
        if (this.chart) {
            this.chart.destroy();
        }

        this.chart = new Chart(this.ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Probabilidad',
                    data: data,
                    backgroundColor: 'rgba(52, 152, 219, 0.6)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 1,
                        ticks: {
                            callback: function(value) {
                                return (value * 100).toFixed(0) + '%';
                            }
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: title,
                        font: { size: 16 }
                    },
                    legend: {
                        display: false
                    }
                }
            }
        });
    }

    /**
     * Crea un gráfico de línea para evolución temporal
     */
    renderLineChart(labels, datasets, title = 'Evolución Temporal') {
        if (this.chart) {
            this.chart.destroy();
        }

        this.chart = new Chart(this.ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 1
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: title,
                        font: { size: 16 }
                    }
                }
            }
        });
    }
}

// ==============================================================================
// UTILIDADES DE ANIMACIÓN
// ==============================================================================
const AnimationUtils = {
    /**
     * Anima un número desde un valor inicial hasta un valor final
     */
    animateValue(element, start, end, duration = 1000) {
        const range = end - start;
        const startTime = performance.now();

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const easeOutQuad = progress * (2 - progress);
            const current = start + (range * easeOutQuad);
            
            element.textContent = current.toFixed(4);
            
            if (progress < 1) {
                requestAnimationFrame(update);
            }
        }

        requestAnimationFrame(update);
    },

    /**
     * Resalta un elemento temporalmente
     */
    flashElement(element, color = '#FFD700', duration = 500) {
        const originalBg = element.style.backgroundColor;
        element.style.backgroundColor = color;
        element.style.transition = 'background-color 0.3s ease';
        
        setTimeout(() => {
            element.style.backgroundColor = originalBg;
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
}