/**
 * ==============================================================================
 * REDES BAYESIANAS - LÓGICA PRINCIPAL
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Archivo: bayesian.js
 * ==============================================================================
 */

// ==============================================================================
// CLASE PRINCIPAL: Red Bayesiana
// ==============================================================================
class BayesianNetwork {
    constructor() {
        this.nodes = new Map(); // Almacena los nodos
        this.edges = new Map(); // Almacena las conexiones
        this.cpts = new Map();  // Tablas de Probabilidad Condicional
    }

    /**
     * Agrega un nodo a la red
     * @param {string} id - ID único del nodo
     * @param {string} name - Nombre del nodo
     * @param {Array} states - Estados posibles del nodo
     */
    addNode(id, name, states = ['true', 'false']) {
        this.nodes.set(id, {
            id: id,
            name: name,
            states: states,
            parents: [],
            children: []
        });
        
        // Inicializar CPT vacía
        this.cpts.set(id, []);
    }

    /**
     * Agrega una arista (conexión) entre dos nodos
     * @param {string} fromId - ID del nodo padre
     * @param {string} toId - ID del nodo hijo
     */
    addEdge(fromId, toId) {
        if (!this.nodes.has(fromId) || !this.nodes.has(toId)) {
            console.error('Uno o ambos nodos no existen');
            return false;
        }

        // Verificar ciclos antes de agregar
        if (this.wouldCreateCycle(fromId, toId)) {
            console.error('Esta conexión crearía un ciclo');
            return false;
        }

        // Agregar padre al nodo hijo
        const childNode = this.nodes.get(toId);
        if (!childNode.parents.includes(fromId)) {
            childNode.parents.push(fromId);
        }

        // Agregar hijo al nodo padre
        const parentNode = this.nodes.get(fromId);
        if (!parentNode.children.includes(toId)) {
            parentNode.children.push(toId);
        }

        return true;
    }

    /**
     * Verifica si agregar una arista crearía un ciclo
     */
    wouldCreateCycle(fromId, toId) {
        const visited = new Set();
        const stack = [toId];

        while (stack.length > 0) {
            const current = stack.pop();
            if (current === fromId) return true;
            
            if (!visited.has(current)) {
                visited.add(current);
                const node = this.nodes.get(current);
                if (node && node.children) {
                    stack.push(...node.children);
                }
            }
        }
        return false;
    }

    /**
     * Establece la Tabla de Probabilidad Condicional para un nodo
     * @param {string} nodeId - ID del nodo
     * @param {Array} cpt - Tabla de probabilidad condicional
     */
    setCPT(nodeId, cpt) {
        if (!this.nodes.has(nodeId)) {
            console.error('El nodo no existe');
            return;
        }
        this.cpts.set(nodeId, cpt);
    }

    /**
     * Genera una CPT vacía para un nodo
     */
    generateEmptyCPT(nodeId) {
        const node = this.nodes.get(nodeId);
        if (!node) return [];

        const parents = node.parents;
        const numRows = Math.pow(2, parents.length);
        const cpt = [];

        for (let i = 0; i < numRows; i++) {
            const row = { conditions: {}, probabilities: {} };
            
            // Generar combinaciones de estados de padres
            parents.forEach((parentId, index) => {
                const bit = (i >> (parents.length - 1 - index)) & 1;
                row.conditions[parentId] = bit === 1 ? 'true' : 'false';
            });

            // Inicializar probabilidades para cada estado del nodo
            node.states.forEach(state => {
                row.probabilities[state] = 0.5;
            });

            cpt.push(row);
        }

        return cpt;
    }

    /**
     * Realiza inferencia por enumeración
     * @param {string} queryVar - Variable a consultar
     * @param {Object} evidence - Evidencia observada
     */
    enumerationInference(queryVar, evidence = {}) {
        const node = this.nodes.get(queryVar);
        if (!node) return null;

        const results = {};
        
        // Calcular probabilidad para cada estado de la variable de consulta
        node.states.forEach(state => {
            const prob = this.enumerateAll(
                Array.from(this.nodes.keys()),
                { ...evidence, [queryVar]: state }
            );
            results[state] = prob;
        });

        // Normalizar
        const sum = Object.values(results).reduce((a, b) => a + b, 0);
        Object.keys(results).forEach(state => {
            results[state] = sum > 0 ? results[state] / sum : 0;
        });

        return results;
    }

    /**
     * Función auxiliar para enumeración recursiva
     */
    enumerateAll(vars, evidence) {
        if (vars.length === 0) return 1.0;

        const Y = vars[0];
        const rest = vars.slice(1);

        if (evidence.hasOwnProperty(Y)) {
            // Y tiene un valor observado
            return this.getProbability(Y, evidence[Y], evidence) * 
                   this.enumerateAll(rest, evidence);
        } else {
            // Sumar sobre todos los valores posibles de Y
            const node = this.nodes.get(Y);
            let sum = 0;
            
            node.states.forEach(state => {
                const newEvidence = { ...evidence, [Y]: state };
                sum += this.getProbability(Y, state, newEvidence) * 
                       this.enumerateAll(rest, newEvidence);
            });
            
            return sum;
        }
    }

    /**
     * Obtiene la probabilidad de un nodo dado su estado y evidencia
     */
    getProbability(nodeId, state, evidence) {
        const node = this.nodes.get(nodeId);
        const cpt = this.cpts.get(nodeId);
        
        if (!node || !cpt || cpt.length === 0) return 0.5;

        // Si no tiene padres, usar probabilidad marginal
        if (node.parents.length === 0) {
            const entry = cpt[0];
            return entry.probabilities[state] || 0.5;
        }

        // Buscar la fila correspondiente en la CPT
        const row = cpt.find(entry => {
            return node.parents.every(parentId => {
                return entry.conditions[parentId] === evidence[parentId];
            });
        });

        return row ? (row.probabilities[state] || 0.5) : 0.5;
    }

    /**
     * Algoritmo de Eliminación de Variables
     */
    variableElimination(queryVar, evidence = {}) {
        // Simplificación: usar enumeración por ahora
        // En una implementación completa, aquí iría el algoritmo de eliminación
        return this.enumerationInference(queryVar, evidence);
    }

    /**
     * Exportar red a formato JSON
     */
    toJSON() {
        return {
            nodes: Array.from(this.nodes.entries()).map(([id, node]) => ({
                id,
                ...node
            })),
            edges: this.getEdges(),
            cpts: Array.from(this.cpts.entries()).map(([id, cpt]) => ({
                nodeId: id,
                cpt: cpt
            }))
        };
    }

    /**
     * Obtener lista de aristas
     */
    getEdges() {
        const edges = [];
        this.nodes.forEach((node, nodeId) => {
            node.parents.forEach(parentId => {
                edges.push({ from: parentId, to: nodeId });
            });
        });
        return edges;
    }

    /**
     * Importar red desde JSON
     */
    fromJSON(data) {
        this.nodes.clear();
        this.edges.clear();
        this.cpts.clear();

        // Importar nodos
        data.nodes.forEach(node => {
            this.nodes.set(node.id, {
                id: node.id,
                name: node.name,
                states: node.states,
                parents: node.parents || [],
                children: node.children || []
            });
        });

        // Importar CPTs
        data.cpts.forEach(({ nodeId, cpt }) => {
            this.cpts.set(nodeId, cpt);
        });
    }
}

// ==============================================================================
// EJEMPLOS PREDEFINIDOS
// ==============================================================================
const BAYESIAN_EXAMPLES = {
    sprinkler: {
        name: "Sprinkler (Aspersor)",
        description: "Ejemplo clásico: Lluvia, Aspersor y Césped Mojado",
        network: {
            nodes: [
                { id: 'rain', name: 'Lluvia', states: ['true', 'false'] },
                { id: 'sprinkler', name: 'Aspersor', states: ['true', 'false'] },
                { id: 'wet', name: 'Césped Mojado', states: ['true', 'false'] }
            ],
            edges: [
                { from: 'rain', to: 'sprinkler' },
                { from: 'rain', to: 'wet' },
                { from: 'sprinkler', to: 'wet' }
            ],
            cpts: [
                {
                    nodeId: 'rain',
                    cpt: [{
                        conditions: {},
                        probabilities: { 'true': 0.2, 'false': 0.8 }
                    }]
                },
                {
                    nodeId: 'sprinkler',
                    cpt: [
                        {
                            conditions: { 'rain': 'true' },
                            probabilities: { 'true': 0.01, 'false': 0.99 }
                        },
                        {
                            conditions: { 'rain': 'false' },
                            probabilities: { 'true': 0.4, 'false': 0.6 }
                        }
                    ]
                },
                {
                    nodeId: 'wet',
                    cpt: [
                        {
                            conditions: { 'rain': 'true', 'sprinkler': 'true' },
                            probabilities: { 'true': 0.99, 'false': 0.01 }
                        },
                        {
                            conditions: { 'rain': 'true', 'sprinkler': 'false' },
                            probabilities: { 'true': 0.8, 'false': 0.2 }
                        },
                        {
                            conditions: { 'rain': 'false', 'sprinkler': 'true' },
                            probabilities: { 'true': 0.9, 'false': 0.1 }
                        },
                        {
                            conditions: { 'rain': 'false', 'sprinkler': 'false' },
                            probabilities: { 'true': 0.0, 'false': 1.0 }
                        }
                    ]
                }
            ]
        }
    },
    
    medical: {
        name: "Diagnóstico Médico",
        description: "Modelo simple de diagnóstico médico",
        network: {
            nodes: [
                { id: 'disease', name: 'Enfermedad', states: ['true', 'false'] },
                { id: 'symptom1', name: 'Fiebre', states: ['true', 'false'] },
                { id: 'symptom2', name: 'Tos', states: ['true', 'false'] }
            ],
            edges: [
                { from: 'disease', to: 'symptom1' },
                { from: 'disease', to: 'symptom2' }
            ],
            cpts: [
                {
                    nodeId: 'disease',
                    cpt: [{
                        conditions: {},
                        probabilities: { 'true': 0.01, 'false': 0.99 }
                    }]
                },
                {
                    nodeId: 'symptom1',
                    cpt: [
                        {
                            conditions: { 'disease': 'true' },
                            probabilities: { 'true': 0.8, 'false': 0.2 }
                        },
                        {
                            conditions: { 'disease': 'false' },
                            probabilities: { 'true': 0.1, 'false': 0.9 }
                        }
                    ]
                },
                {
                    nodeId: 'symptom2',
                    cpt: [
                        {
                            conditions: { 'disease': 'true' },
                            probabilities: { 'true': 0.7, 'false': 0.3 }
                        },
                        {
                            conditions: { 'disease': 'false' },
                            probabilities: { 'true': 0.05, 'false': 0.95 }
                        }
                    ]
                }
            ]
        }
    }
};

// ==============================================================================
// EXPORTAR
// ==============================================================================
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { BayesianNetwork, BAYESIAN_EXAMPLES };
}