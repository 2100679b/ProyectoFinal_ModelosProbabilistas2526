/**
 * ==============================================================================
 * MODELOS OCULTOS DE MARKOV (HMM) - LÓGICA PRINCIPAL
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Archivo: hmm.js
 * ==============================================================================
 */

// ==============================================================================
// CLASE PRINCIPAL: Hidden Markov Model
// ==============================================================================
class HiddenMarkovModel {
    constructor() {
        this.states = [];                 // Estados ocultos
        this.observations = [];           // Observaciones posibles
        this.transitionMatrix = [];       // Probabilidades de transición entre estados
        this.emissionMatrix = [];         // Probabilidades de emisión (estado -> observación)
        this.initialDistribution = [];    // Distribución inicial de estados
    }

    /**
     * Inicializa el modelo con estados y observaciones
     * @param {Array} states - Estados ocultos
     * @param {Array} observations - Observaciones posibles
     */
    initialize(states, observations) {
        this.states = states;
        this.observations = observations;
        
        const nStates = states.length;
        const nObs = observations.length;
        
        // Inicializar matrices con distribuciones uniformes
        this.transitionMatrix = Array(nStates).fill(null).map(() => 
            Array(nStates).fill(1/nStates)
        );
        
        this.emissionMatrix = Array(nStates).fill(null).map(() => 
            Array(nObs).fill(1/nObs)
        );
        
        this.initialDistribution = Array(nStates).fill(1/nStates);
    }

    /**
     * Establece la matriz de transición
     */
    setTransitionMatrix(matrix) {
        if (!this.validateMatrix(matrix, this.states.length, this.states.length)) {
            console.error('Matriz de transición inválida');
            return false;
        }
        this.transitionMatrix = matrix;
        return true;
    }

    /**
     * Establece la matriz de emisión
     */
    setEmissionMatrix(matrix) {
        if (!this.validateMatrix(matrix, this.states.length, this.observations.length)) {
            console.error('Matriz de emisión inválida');
            return false;
        }
        this.emissionMatrix = matrix;
        return true;
    }

    /**
     * Establece la distribución inicial
     */
    setInitialDistribution(distribution) {
        if (!this.validateDistribution(distribution)) {
            console.error('Distribución inicial inválida');
            return false;
        }
        this.initialDistribution = distribution;
        return true;
    }

    /**
     * Valida una matriz (filas deben sumar 1)
     */
    validateMatrix(matrix, rows, cols) {
        if (matrix.length !== rows) return false;
        
        return matrix.every(row => {
            if (row.length !== cols) return false;
            const sum = row.reduce((a, b) => a + b, 0);
            return Math.abs(sum - 1.0) < 0.001;
        });
    }

    /**
     * Valida un vector de probabilidad
     */
    validateDistribution(dist) {
        const sum = dist.reduce((a, b) => a + b, 0);
        return Math.abs(sum - 1.0) < 0.001 && dist.every(p => p >= 0 && p <= 1);
    }

    /**
     * Algoritmo Forward: Calcula la probabilidad de una secuencia de observaciones
     * @param {Array} obsSequence - Secuencia de índices de observaciones
     * @returns {Object} Probabilidad total y matriz alpha
     */
    forward(obsSequence) {
        const T = obsSequence.length;
        const N = this.states.length;
        
        // Matriz alpha: alpha[t][i] = P(o_1,...,o_t, q_t=i | λ)
        const alpha = Array(T).fill(null).map(() => Array(N).fill(0));
        
        // Inicialización (t=0)
        for (let i = 0; i < N; i++) {
            alpha[0][i] = this.initialDistribution[i] * this.emissionMatrix[i][obsSequence[0]];
        }
        
        // Recursión (t=1 hasta T-1)
        for (let t = 1; t < T; t++) {
            for (let j = 0; j < N; j++) {
                let sum = 0;
                for (let i = 0; i < N; i++) {
                    sum += alpha[t-1][i] * this.transitionMatrix[i][j];
                }
                alpha[t][j] = sum * this.emissionMatrix[j][obsSequence[t]];
            }
        }
        
        // Terminación: P(O|λ) = suma de alpha[T-1][i] para todo i
        const probability = alpha[T-1].reduce((a, b) => a + b, 0);
        
        return { probability, alpha };
    }

    /**
     * Algoritmo Backward: Complemento del algoritmo Forward
     * @param {Array} obsSequence - Secuencia de índices de observaciones
     * @returns {Array} Matriz beta
     */
    backward(obsSequence) {
        const T = obsSequence.length;
        const N = this.states.length;
        
        // Matriz beta: beta[t][i] = P(o_{t+1},...,o_T | q_t=i, λ)
        const beta = Array(T).fill(null).map(() => Array(N).fill(0));
        
        // Inicialización (t=T-1)
        for (let i = 0; i < N; i++) {
            beta[T-1][i] = 1;
        }
        
        // Recursión (t=T-2 hasta 0)
        for (let t = T - 2; t >= 0; t--) {
            for (let i = 0; i < N; i++) {
                let sum = 0;
                for (let j = 0; j < N; j++) {
                    sum += this.transitionMatrix[i][j] * 
                           this.emissionMatrix[j][obsSequence[t+1]] * 
                           beta[t+1][j];
                }
                beta[t][i] = sum;
            }
        }
        
        return beta;
    }

    /**
     * Algoritmo de Viterbi: Encuentra la secuencia de estados más probable
     * @param {Array} obsSequence - Secuencia de índices de observaciones
     * @returns {Object} Mejor camino y su probabilidad
     */
    viterbi(obsSequence) {
        const T = obsSequence.length;
        const N = this.states.length;
        
        // Matrices para el algoritmo
        const delta = Array(T).fill(null).map(() => Array(N).fill(0));
        const psi = Array(T).fill(null).map(() => Array(N).fill(0));
        
        // Inicialización (t=0)
        for (let i = 0; i < N; i++) {
            delta[0][i] = this.initialDistribution[i] * this.emissionMatrix[i][obsSequence[0]];
            psi[0][i] = 0;
        }
        
        // Recursión (t=1 hasta T-1)
        for (let t = 1; t < T; t++) {
            for (let j = 0; j < N; j++) {
                let maxProb = -1;
                let maxState = 0;
                
                for (let i = 0; i < N; i++) {
                    const prob = delta[t-1][i] * this.transitionMatrix[i][j];
                    if (prob > maxProb) {
                        maxProb = prob;
                        maxState = i;
                    }
                }
                
                delta[t][j] = maxProb * this.emissionMatrix[j][obsSequence[t]];
                psi[t][j] = maxState;
            }
        }
        
        // Terminación: encontrar el estado final más probable
        let maxProb = -1;
        let lastState = 0;
        for (let i = 0; i < N; i++) {
            if (delta[T-1][i] > maxProb) {
                maxProb = delta[T-1][i];
                lastState = i;
            }
        }
        
        // Retroceder para encontrar la mejor secuencia
        const bestPath = Array(T);
        bestPath[T-1] = lastState;
        
        for (let t = T - 2; t >= 0; t--) {
            bestPath[t] = psi[t+1][bestPath[t+1]];
        }
        
        return {
            path: bestPath,
            probability: maxProb
        };
    }

    /**
     * Algoritmo Baum-Welch: Aprendizaje de parámetros del HMM
     * @param {Array} obsSequence - Secuencia de observaciones
     * @param {number} maxIterations - Número máximo de iteraciones
     * @param {number} tolerance - Tolerancia para convergencia
     */
    baumWelch(obsSequence, maxIterations = 100, tolerance = 1e-6) {
        const T = obsSequence.length;
        const N = this.states.length;
        const M = this.observations.length;
        
        let oldLogProb = -Infinity;
        
        for (let iter = 0; iter < maxIterations; iter++) {
            // E-step: calcular probabilidades forward y backward
            const { probability, alpha } = this.forward(obsSequence);
            const beta = this.backward(obsSequence);
            
            const logProb = Math.log(probability);
            
            // Verificar convergencia
            if (Math.abs(logProb - oldLogProb) < tolerance) {
                console.log(`Convergencia alcanzada en iteración ${iter}`);
                break;
            }
            oldLogProb = logProb;
            
            // Calcular gamma y xi
            const gamma = this.computeGamma(alpha, beta);
            const xi = this.computeXi(alpha, beta, obsSequence);
            
            // M-step: re-estimar parámetros
            this.reestimateParameters(gamma, xi, obsSequence);
        }
    }

    /**
     * Calcula gamma (probabilidad de estar en un estado en un tiempo t)
     */
    computeGamma(alpha, beta) {
        const T = alpha.length;
        const N = this.states.length;
        const gamma = Array(T).fill(null).map(() => Array(N).fill(0));
        
        for (let t = 0; t < T; t++) {
            let sum = 0;
            for (let i = 0; i < N; i++) {
                gamma[t][i] = alpha[t][i] * beta[t][i];
                sum += gamma[t][i];
            }
            // Normalizar
            for (let i = 0; i < N; i++) {
                gamma[t][i] /= sum;
            }
        }
        
        return gamma;
    }

    /**
     * Calcula xi (probabilidad de transición entre estados)
     */
    computeXi(alpha, beta, obsSequence) {
        const T = obsSequence.length;
        const N = this.states.length;
        const xi = Array(T-1).fill(null).map(() => 
            Array(N).fill(null).map(() => Array(N).fill(0))
        );
        
        for (let t = 0; t < T - 1; t++) {
            let sum = 0;
            for (let i = 0; i < N; i++) {
                for (let j = 0; j < N; j++) {
                    xi[t][i][j] = alpha[t][i] * 
                                  this.transitionMatrix[i][j] * 
                                  this.emissionMatrix[j][obsSequence[t+1]] * 
                                  beta[t+1][j];
                    sum += xi[t][i][j];
                }
            }
            // Normalizar
            for (let i = 0; i < N; i++) {
                for (let j = 0; j < N; j++) {
                    xi[t][i][j] /= sum;
                }
            }
        }
        
        return xi;
    }

    /**
     * Re-estima los parámetros del modelo
     */
    reestimateParameters(gamma, xi, obsSequence) {
        const N = this.states.length;
        const M = this.observations.length;
        const T = obsSequence.length;
        
        // Re-estimar distribución inicial
        for (let i = 0; i < N; i++) {
            this.initialDistribution[i] = gamma[0][i];
        }
        
        // Re-estimar matriz de transición
        for (let i = 0; i < N; i++) {
            let denominator = 0;
            for (let t = 0; t < T - 1; t++) {
                denominator += gamma[t][i];
            }
            
            for (let j = 0; j < N; j++) {
                let numerator = 0;
                for (let t = 0; t < T - 1; t++) {
                    numerator += xi[t][i][j];
                }
                this.transitionMatrix[i][j] = numerator / denominator;
            }
        }
        
        // Re-estimar matriz de emisión
        for (let i = 0; i < N; i++) {
            let denominator = 0;
            for (let t = 0; t < T; t++) {
                denominator += gamma[t][i];
            }
            
            for (let k = 0; k < M; k++) {
                let numerator = 0;
                for (let t = 0; t < T; t++) {
                    if (obsSequence[t] === k) {
                        numerator += gamma[t][i];
                    }
                }
                this.emissionMatrix[i][k] = numerator / denominator;
            }
        }
    }

    /**
     * Exporta el modelo a JSON
     */
    toJSON() {
        return {
            states: this.states,
            observations: this.observations,
            transitionMatrix: this.transitionMatrix,
            emissionMatrix: this.emissionMatrix,
            initialDistribution: this.initialDistribution
        };
    }

    /**
     * Importa el modelo desde JSON
     */
    fromJSON(data) {
        this.states = data.states;
        this.observations = data.observations;
        this.transitionMatrix = data.transitionMatrix;
        this.emissionMatrix = data.emissionMatrix;
        this.initialDistribution = data.initialDistribution;
    }
}

// ==============================================================================
// EJEMPLOS PREDEFINIDOS
// ==============================================================================
const HMM_EXAMPLES = {
    weather: {
        name: "Clima Oculto",
        description: "Predice el clima basado en observaciones (con/sin paraguas)",
        model: {
            states: ['Soleado', 'Lluvioso'],
            observations: ['Con paraguas', 'Sin paraguas'],
            transitionMatrix: [
                [0.7, 0.3],  // Desde Soleado
                [0.4, 0.6]   // Desde Lluvioso
            ],
            emissionMatrix: [
                [0.1, 0.9],  // Soleado -> observaciones
                [0.8, 0.2]   // Lluvioso -> observaciones
            ],
            initialDistribution: [0.6, 0.4]
        }
    },
    
    casino: {
        name: "Casino Deshonesto",
        description: "Detecta si un casino usa dados justos o cargados",
        model: {
            states: ['Justo', 'Cargado'],
            observations: ['1', '2', '3', '4', '5', '6'],
            transitionMatrix: [
                [0.95, 0.05],   // Desde Justo
                [0.10, 0.90]    // Desde Cargado
            ],
            emissionMatrix: [
                [1/6, 1/6, 1/6, 1/6, 1/6, 1/6],           // Justo
                [0.1, 0.1, 0.1, 0.1, 0.1, 0.5]            // Cargado (favorece 6)
            ],
            initialDistribution: [0.5, 0.5]
        }
    }
};

// ==============================================================================
// EXPORTAR
// ==============================================================================
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { HiddenMarkovModel, HMM_EXAMPLES };
}