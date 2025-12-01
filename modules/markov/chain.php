<?php
/**
 * Clase MarkovChain - Manejo de Cadenas de Markov
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Modelos Probabilísticos
 */

class MarkovChain {
    private $states = [];
    private $transitionMatrix = [];
    private $stateCount = 0;
    private $stateIndexMap = [];
    private $initialDistribution = [];
    
    /**
     * Constructor
     * @param array|null $data Datos de la cadena en formato array
     */
    public function __construct($data = null) {
        if ($data) {
            $this->loadFromArray($data);
        }
    }
    
    /**
     * Cargar cadena desde array
     * @param array $data Estructura de la cadena
     */
    public function loadFromArray($data) {
        if (isset($data['states'])) {
            $this->states = $data['states'];
            $this->stateCount = count($this->states);
            $this->buildStateIndexMap();
        }
        
        if (isset($data['transition_matrix'])) {
            $this->transitionMatrix = $data['transition_matrix'];
        }
        
        if (isset($data['initial_distribution'])) {
            $this->initialDistribution = $data['initial_distribution'];
        } else {
            // Distribución uniforme por defecto
            $this->initialDistribution = array_fill(0, $this->stateCount, 1.0 / $this->stateCount);
        }
    }
    
    /**
     * Construir mapa de índices de estados
     */
    private function buildStateIndexMap() {
        $this->stateIndexMap = [];
        foreach ($this->states as $index => $state) {
            $this->stateIndexMap[$state] = $index;
        }
    }
    
    /**
     * Obtener estados
     * @return array
     */
    public function getStates() {
        return $this->states;
    }
    
    /**
     * Obtener número de estados
     * @return int
     */
    public function getStateCount() {
        return $this->stateCount;
    }
    
    /**
     * Obtener índice de un estado
     * @param string $state Nombre del estado
     * @return int|null
     */
    public function getStateIndex($state) {
        return isset($this->stateIndexMap[$state]) ? $this->stateIndexMap[$state] : null;
    }
    
    /**
     * Obtener nombre de estado por índice
     * @param int $index Índice del estado
     * @return string|null
     */
    public function getStateName($index) {
        return isset($this->states[$index]) ? $this->states[$index] : null;
    }
    
    /**
     * Obtener matriz de transición completa
     * @return array
     */
    public function getTransitionMatrix() {
        return $this->transitionMatrix;
    }
    
    /**
     * Obtener probabilidades de transición desde un estado
     * @param int $fromState Índice del estado origen
     * @return array
     */
    public function getTransitionsFrom($fromState) {
        if (isset($this->transitionMatrix[$fromState])) {
            return $this->transitionMatrix[$fromState];
        }
        return [];
    }
    
    /**
     * Obtener probabilidad de transición específica
     * @param int $fromState Estado origen
     * @param int $toState Estado destino
     * @return float
     */
    public function getTransitionProbability($fromState, $toState) {
        if (isset($this->transitionMatrix[$fromState][$toState])) {
            return (float)$this->transitionMatrix[$fromState][$toState];
        }
        return 0.0;
    }
    
    /**
     * Establecer probabilidad de transición
     * @param int $fromState Estado origen
     * @param int $toState Estado destino
     * @param float $probability Probabilidad
     */
    public function setTransitionProbability($fromState, $toState, $probability) {
        if (!isset($this->transitionMatrix[$fromState])) {
            $this->transitionMatrix[$fromState] = [];
        }
        $this->transitionMatrix[$fromState][$toState] = (float)$probability;
    }
    
    /**
     * Obtener distribución inicial
     * @return array
     */
    public function getInitialDistribution() {
        return $this->initialDistribution;
    }
    
    /**
     * Establecer distribución inicial
     * @param array $distribution
     */
    public function setInitialDistribution($distribution) {
        $this->initialDistribution = $distribution;
    }
    
    /**
     * Simular un paso de la cadena
     * @param int $currentState Estado actual
     * @return int Siguiente estado
     */
    public function step($currentState) {
        $transitions = $this->getTransitionsFrom($currentState);
        
        if (empty($transitions)) {
            return $currentState; // Quedarse en el mismo estado
        }
        
        // Selección aleatoria basada en probabilidades
        $random = mt_rand() / mt_getrandmax();
        $cumulative = 0.0;
        
        foreach ($transitions as $nextState => $probability) {
            $cumulative += $probability;
            if ($random <= $cumulative) {
                return $nextState;
            }
        }
        
        // Fallback: retornar último estado
        return array_key_last($transitions);
    }
    
    /**
     * Simular múltiples pasos
     * @param int $initialState Estado inicial
     * @param int $steps Número de pasos
     * @return array Secuencia de estados
     */
    public function simulate($initialState, $steps) {
        $sequence = [$initialState];
        $currentState = $initialState;
        
        for ($i = 0; $i < $steps; $i++) {
            $currentState = $this->step($currentState);
            $sequence[] = $currentState;
        }
        
        return $sequence;
    }
    
    /**
     * Calcular distribución de probabilidad después de n pasos
     * @param array $initialDistribution Distribución inicial
     * @param int $steps Número de pasos
     * @return array Distribución después de n pasos
     */
    public function getDistributionAfterSteps($initialDistribution, $steps) {
        $distribution = $initialDistribution;
        
        for ($i = 0; $i < $steps; $i++) {
            $distribution = $this->multiplyDistributionByMatrix($distribution);
        }
        
        return $distribution;
    }
    
    /**
     * Multiplicar distribución por matriz de transición
     * @param array $distribution
     * @return array
     */
    private function multiplyDistributionByMatrix($distribution) {
        $result = array_fill(0, $this->stateCount, 0.0);
        
        for ($j = 0; $j < $this->stateCount; $j++) {
            for ($i = 0; $i < $this->stateCount; $i++) {
                $prob = $this->getTransitionProbability($i, $j);
                $result[$j] += $distribution[$i] * $prob;
            }
        }
        
        return $result;
    }
    
    /**
     * Calcular distribución estacionaria (método iterativo)
     * @param int $maxIterations Máximo de iteraciones
     * @param float $tolerance Tolerancia de convergencia
     * @return array|null Distribución estacionaria o null si no converge
     */
    public function getStationaryDistribution($maxIterations = 1000, $tolerance = 1e-6) {
        // Iniciar con distribución uniforme
        $distribution = array_fill(0, $this->stateCount, 1.0 / $this->stateCount);
        
        for ($iter = 0; $iter < $maxIterations; $iter++) {
            $newDistribution = $this->multiplyDistributionByMatrix($distribution);
            
            // Verificar convergencia
            $maxDiff = 0.0;
            for ($i = 0; $i < $this->stateCount; $i++) {
                $diff = abs($newDistribution[$i] - $distribution[$i]);
                if ($diff > $maxDiff) {
                    $maxDiff = $diff;
                }
            }
            
            if ($maxDiff < $tolerance) {
                return $newDistribution;
            }
            
            $distribution = $newDistribution;
        }
        
        // No convergió
        return null;
    }
    
    /**
     * Calcular probabilidad de absorción
     * (para cadenas con estados absorbentes)
     * @param int $startState Estado inicial
     * @param int $absorbingState Estado absorbente
     * @param int $maxSteps Máximo de pasos
     * @return float Probabilidad de absorción
     */
    public function getAbsorptionProbability($startState, $absorbingState, $maxSteps = 100) {
        if ($this->isAbsorbingState($absorbingState)) {
            $distribution = array_fill(0, $this->stateCount, 0.0);
            $distribution[$startState] = 1.0;
            
            for ($i = 0; $i < $maxSteps; $i++) {
                $distribution = $this->multiplyDistributionByMatrix($distribution);
            }
            
            return $distribution[$absorbingState];
        }
        
        return 0.0;
    }
    
    /**
     * Verificar si un estado es absorbente
     * @param int $state Índice del estado
     * @return bool
     */
    public function isAbsorbingState($state) {
        $prob = $this->getTransitionProbability($state, $state);
        return abs($prob - 1.0) < 1e-6;
    }
    
    /**
     * Obtener estados absorbentes
     * @return array
     */
    public function getAbsorbingStates() {
        $absorbing = [];
        
        for ($i = 0; $i < $this->stateCount; $i++) {
            if ($this->isAbsorbingState($i)) {
                $absorbing[] = $i;
            }
        }
        
        return $absorbing;
    }
    
    /**
     * Validar cadena de Markov
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate() {
        $errors = [];
        
        // Verificar que hay estados
        if (empty($this->states)) {
            $errors[] = "La cadena no tiene estados";
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Verificar matriz de transición
        if (empty($this->transitionMatrix)) {
            $errors[] = "La matriz de transición está vacía";
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Verificar que cada fila suma 1 (es estocástica)
        for ($i = 0; $i < $this->stateCount; $i++) {
            $rowSum = 0.0;
            $transitions = $this->getTransitionsFrom($i);
            
            if (empty($transitions)) {
                $errors[] = "Estado '{$this->states[$i]}' no tiene transiciones";
                continue;
            }
            
            foreach ($transitions as $prob) {
                $rowSum += $prob;
            }
            
            if (abs($rowSum - 1.0) > 0.01) {
                $errors[] = "Las probabilidades del estado '{$this->states[$i]}' no suman 1.0 (suma: " . round($rowSum, 4) . ")";
            }
            
            // Verificar que todas las probabilidades están entre 0 y 1
            foreach ($transitions as $toState => $prob) {
                if ($prob < 0 || $prob > 1) {
                    $errors[] = "Probabilidad inválida de '{$this->states[$i]}' a '{$this->states[$toState]}': {$prob}";
                }
            }
        }
        
        // Verificar distribución inicial
        if (!empty($this->initialDistribution)) {
            $sum = array_sum($this->initialDistribution);
            if (abs($sum - 1.0) > 0.01) {
                $errors[] = "La distribución inicial no suma 1.0 (suma: " . round($sum, 4) . ")";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Verificar si la cadena es irreducible
     * (todos los estados son accesibles desde cualquier otro)
     * @return bool
     */
    public function isIrreducible() {
        // Usar búsqueda en profundidad (DFS)
        for ($start = 0; $start < $this->stateCount; $start++) {
            $visited = array_fill(0, $this->stateCount, false);
            $this->dfs($start, $visited);
            
            // Si no todos fueron visitados, no es irreducible
            foreach ($visited as $v) {
                if (!$v) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Búsqueda en profundidad (DFS)
     * @param int $state Estado actual
     * @param array &$visited Array de visitados
     */
    private function dfs($state, &$visited) {
        $visited[$state] = true;
        $transitions = $this->getTransitionsFrom($state);
        
        foreach ($transitions as $nextState => $prob) {
            if ($prob > 0 && !$visited[$nextState]) {
                $this->dfs($nextState, $visited);
            }
        }
    }
    
    /**
     * Verificar si la cadena es aperiódica
     * @return bool
     */
    public function isAperiodic() {
        // Una cadena es aperiódica si el GCD de las longitudes de todos los ciclos es 1
        // Simplificación: verificar si existe al menos un self-loop
        for ($i = 0; $i < $this->stateCount; $i++) {
            if ($this->getTransitionProbability($i, $i) > 0) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Verificar si la cadena es ergódica
     * (irreducible y aperiódica)
     * @return bool
     */
    public function isErgodic() {
        return $this->isIrreducible() && $this->isAperiodic();
    }
    
    /**
     * Convertir a JSON
     * @return string
     */
    public function toJSON() {
        return json_encode([
            'states' => $this->states,
            'transition_matrix' => $this->transitionMatrix,
            'initial_distribution' => $this->initialDistribution
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Convertir a array
     * @return array
     */
    public function toArray() {
        return [
            'states' => $this->states,
            'transition_matrix' => $this->transitionMatrix,
            'initial_distribution' => $this->initialDistribution,
            'state_count' => $this->stateCount
        ];
    }
    
    /**
     * Obtener información resumida de la cadena
     * @return array
     */
    public function getSummary() {
        $validation = $this->validate();
        
        return [
            'state_count' => $this->stateCount,
            'is_valid' => $validation['valid'],
            'is_irreducible' => $this->isIrreducible(),
            'is_aperiodic' => $this->isAperiodic(),
            'is_ergodic' => $this->isErgodic(),
            'absorbing_states' => $this->getAbsorbingStates(),
            'has_stationary_distribution' => $this->isErgodic()
        ];
    }
    
    /**
     * Calcular tiempo promedio de primer paso
     * (tiempo esperado para llegar de un estado a otro)
     * @param int $fromState Estado origen
     * @param int $toState Estado destino
     * @param int $maxSteps Máximo de pasos a considerar
     * @return float|null
     */
    public function getFirstPassageTime($fromState, $toState, $maxSteps = 1000) {
        if ($fromState === $toState) {
            return 0.0;
        }
        
        $expectedTime = 0.0;
        $distribution = array_fill(0, $this->stateCount, 0.0);
        $distribution[$fromState] = 1.0;
        
        for ($t = 1; $t <= $maxSteps; $t++) {
            $distribution = $this->multiplyDistributionByMatrix($distribution);
            $probAtTarget = $distribution[$toState];
            $expectedTime += $t * $probAtTarget;
            
            // Si la probabilidad acumulada es alta, podemos parar
            if ($probAtTarget > 0.99) {
                break;
            }
        }
        
        return $expectedTime;
    }
    
    /**
     * Imprimir información de debug
     * @return string
     */
    public function debug() {
        $output = "=== CADENA DE MARKOV ===\n";
        $output .= "Estados: " . $this->stateCount . "\n";
        $output .= "Lista de estados: " . implode(", ", $this->states) . "\n\n";
        
        $output .= "Matriz de Transición:\n";
        $output .= str_pad("", 12);
        foreach ($this->states as $state) {
            $output .= str_pad($state, 10);
        }
        $output .= "\n";
        
        for ($i = 0; $i < $this->stateCount; $i++) {
            $output .= str_pad($this->states[$i], 12);
            for ($j = 0; $j < $this->stateCount; $j++) {
                $prob = $this->getTransitionProbability($i, $j);
                $output .= str_pad(number_format($prob, 4), 10);
            }
            $output .= "\n";
        }
        
        $summary = $this->getSummary();
        $output .= "\nPropiedades:\n";
        $output .= "- Irreducible: " . ($summary['is_irreducible'] ? 'Sí' : 'No') . "\n";
        $output .= "- Aperiódica: " . ($summary['is_aperiodic'] ? 'Sí' : 'No') . "\n";
        $output .= "- Ergódica: " . ($summary['is_ergodic'] ? 'Sí' : 'No') . "\n";
        
        return $output;
    }
}
?>
