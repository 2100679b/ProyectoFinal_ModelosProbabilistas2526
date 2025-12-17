<?php
/**
 * Algoritmo de Simulación de Cadena de Markov de Primer Orden
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Modelos Probabilísticos
 */

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../chain.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Use POST');
    }
    
    // Recibir datos
    $chainData = json_decode($_POST['chain'] ?? '{}', true);
    $initialState = isset($_POST['initial_state']) ? (int)$_POST['initial_state'] : null;
    $numSteps = isset($_POST['num_steps']) ? (int)$_POST['num_steps'] : 10;
    
    // Validaciones
    if (empty($chainData)) {
        throw new Exception('No se proporcionó cadena de Markov');
    }
    
    if ($initialState === null) {
        throw new Exception('Estado inicial no especificado');
    }
    
    if ($numSteps < 1 || $numSteps > 1000) {
        throw new Exception('Número de pasos debe estar entre 1 y 1000');
    }
    
    // Crear cadena
    $chain = new MarkovChain($chainData);
    
    // Validar cadena
    $validation = $chain->validate();
    if (!$validation['valid']) {
        throw new Exception('Cadena inválida: ' . implode(', ', $validation['errors']));
    }
    
    // Validar estado inicial
    if ($initialState < 0 || $initialState >= $chain->getStateCount()) {
        throw new Exception('Estado inicial fuera de rango');
    }
    
    // Ejecutar simulación
    $startTime = microtime(true);
    $sequence = $chain->simulate($initialState, $numSteps);
    $executionTime = (microtime(true) - $startTime) * 1000; // en ms
    
    // Calcular estadísticas
    $statistics = calculateStatistics($chain, $sequence);
    
    // Calcular transiciones
    $transitions = calculateTransitions($chain, $sequence);
    
    // Calcular tiempo de permanencia en cada estado
    $dwellTimes = calculateDwellTimes($chain, $sequence);
    
    // Responder
    echo json_encode([
        'success' => true,
        'algorithm' => 'first_order_markov',
        'parameters' => [
            'initial_state' => $initialState,
            'initial_state_name' => $chain->getStateName($initialState),
            'num_steps' => $numSteps,
            'actual_length' => count($sequence)
        ],
        'sequence' => $sequence,
        'sequence_names' => array_map(function($state) use ($chain) {
            return $chain->getStateName($state);
        }, $sequence),
        'statistics' => $statistics,
        'transitions' => $transitions,
        'dwell_times' => $dwellTimes,
        'execution_time_ms' => round($executionTime, 2)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'algorithm' => 'first_order_markov'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * Calcular estadísticas de la secuencia
 * @param MarkovChain $chain
 * @param array $sequence Secuencia de estados
 * @return array
 */
function calculateStatistics($chain, $sequence) {
    $stateCount = $chain->getStateCount();
    $totalSteps = count($sequence);
    
    // Contar visitas a cada estado
    $visits = array_fill(0, $stateCount, 0);
    foreach ($sequence as $state) {
        $visits[$state]++;
    }
    
    // Calcular frecuencias
    $frequencies = [];
    $frequencyPercent = [];
    foreach ($visits as $state => $count) {
        $frequencies[$state] = $count / $totalSteps;
        $frequencyPercent[$state] = round(($count / $totalSteps) * 100, 2);
    }
    
    // Estado más visitado
    $maxVisits = max($visits);
    $mostVisited = array_search($maxVisits, $visits);
    
    // Estado menos visitado (excluyendo 0 visitas)
    $minVisits = PHP_INT_MAX;
    $leastVisited = 0;
    foreach ($visits as $state => $count) {
        if ($count > 0 && $count < $minVisits) {
            $minVisits = $count;
            $leastVisited = $state;
        }
    }
    
    // Calcular entropía de la secuencia
    $entropy = 0.0;
    foreach ($frequencies as $freq) {
        if ($freq > 0) {
            $entropy -= $freq * log($freq, 2);
        }
    }
    
    return [
        'total_steps' => $totalSteps,
        'visits' => $visits,
        'frequencies' => $frequencies,
        'frequency_percent' => $frequencyPercent,
        'most_visited' => [
            'state' => $mostVisited,
            'name' => $chain->getStateName($mostVisited),
            'visits' => $maxVisits,
            'percentage' => round(($maxVisits / $totalSteps) * 100, 2)
        ],
        'least_visited' => [
            'state' => $leastVisited,
            'name' => $chain->getStateName($leastVisited),
            'visits' => $minVisits,
            'percentage' => round(($minVisits / $totalSteps) * 100, 2)
        ],
        'entropy' => round($entropy, 4),
        'max_entropy' => round(log($stateCount, 2), 4)
    ];
}

/**
 * Calcular matriz de transiciones observadas
 * @param MarkovChain $chain
 * @param array $sequence
 * @return array
 */
function calculateTransitions($chain, $sequence) {
    $stateCount = $chain->getStateCount();
    $transitionCounts = [];
    $transitionMatrix = [];
    
    // Inicializar matrices
    for ($i = 0; $i < $stateCount; $i++) {
        $transitionCounts[$i] = array_fill(0, $stateCount, 0);
        $transitionMatrix[$i] = array_fill(0, $stateCount, 0.0);
    }
    
    // Contar transiciones
    for ($t = 0; $t < count($sequence) - 1; $t++) {
        $from = $sequence[$t];
        $to = $sequence[$t + 1];
        $transitionCounts[$from][$to]++;
    }
    
    // Calcular probabilidades empíricas
    for ($i = 0; $i < $stateCount; $i++) {
        $rowSum = array_sum($transitionCounts[$i]);
        if ($rowSum > 0) {
            for ($j = 0; $j < $stateCount; $j++) {
                $transitionMatrix[$i][$j] = $transitionCounts[$i][$j] / $rowSum;
            }
        }
    }
    
    // Comparar con matriz teórica
    $theoreticalMatrix = $chain->getTransitionMatrix();
    $differences = [];
    
    for ($i = 0; $i < $stateCount; $i++) {
        for ($j = 0; $j < $stateCount; $j++) {
            $empirical = $transitionMatrix[$i][$j];
            $theoretical = $chain->getTransitionProbability($i, $j);
            $diff = abs($empirical - $theoretical);
            
            if ($diff > 0.01) { // Solo reportar diferencias significativas
                $differences[] = [
                    'from' => $chain->getStateName($i),
                    'to' => $chain->getStateName($j),
                    'theoretical' => round($theoretical, 4),
                    'empirical' => round($empirical, 4),
                    'difference' => round($diff, 4)
                ];
            }
        }
    }
    
    return [
        'counts' => $transitionCounts,
        'empirical_matrix' => $transitionMatrix,
        'theoretical_matrix' => $theoreticalMatrix,
        'differences' => $differences,
        'total_transitions' => count($sequence) - 1
    ];
}

/**
 * Calcular tiempo de permanencia en cada estado
 * @param MarkovChain $chain
 * @param array $sequence
 * @return array
 */
function calculateDwellTimes($chain, $sequence) {
    $stateCount = $chain->getStateCount();
    $dwellTimes = [];
    
    for ($i = 0; $i < $stateCount; $i++) {
        $dwellTimes[$i] = [
            'state' => $chain->getStateName($i),
            'runs' => [],
            'total_time' => 0,
            'average_time' => 0.0,
            'max_time' => 0,
            'min_time' => PHP_INT_MAX
        ];
    }
    
    // Identificar runs (secuencias continuas en un estado)
    $currentState = $sequence[0];
    $currentRunLength = 1;
    
    for ($t = 1; $t < count($sequence); $t++) {
        if ($sequence[$t] === $currentState) {
            $currentRunLength++;
        } else {
            // Registrar run terminado
            $dwellTimes[$currentState]['runs'][] = $currentRunLength;
            $dwellTimes[$currentState]['total_time'] += $currentRunLength;
            $dwellTimes[$currentState]['max_time'] = max($dwellTimes[$currentState]['max_time'], $currentRunLength);
            $dwellTimes[$currentState]['min_time'] = min($dwellTimes[$currentState]['min_time'], $currentRunLength);
            
            // Iniciar nuevo run
            $currentState = $sequence[$t];
            $currentRunLength = 1;
        }
    }
    
    // Registrar último run
    $dwellTimes[$currentState]['runs'][] = $currentRunLength;
    $dwellTimes[$currentState]['total_time'] += $currentRunLength;
    $dwellTimes[$currentState]['max_time'] = max($dwellTimes[$currentState]['max_time'], $currentRunLength);
    $dwellTimes[$currentState]['min_time'] = min($dwellTimes[$currentState]['min_time'], $currentRunLength);
    
    // Calcular promedios
    foreach ($dwellTimes as $state => &$data) {
        $numRuns = count($data['runs']);
        if ($numRuns > 0) {
            $data['average_time'] = round($data['total_time'] / $numRuns, 2);
            $data['num_runs'] = $numRuns;
        } else {
            $data['min_time'] = 0;
            $data['num_runs'] = 0;
        }
    }
    
    return $dwellTimes;
}

/**
 * Realizar múltiples simulaciones y promediar resultados
 * @param MarkovChain $chain
 * @param int $initialState
 * @param int $numSteps
 * @param int $numSimulations
 * @return array
 */
function runMultipleSimulations($chain, $initialState, $numSteps, $numSimulations = 1000) {
    $stateCount = $chain->getStateCount();
    $aggregatedVisits = array_fill(0, $stateCount, 0);
    
    for ($sim = 0; $sim < $numSimulations; $sim++) {
        $sequence = $chain->simulate($initialState, $numSteps);
        
        foreach ($sequence as $state) {
            $aggregatedVisits[$state]++;
        }
    }
    
    // Calcular frecuencias promedio
    $totalVisits = array_sum($aggregatedVisits);
    $averageFrequencies = [];
    
    foreach ($aggregatedVisits as $state => $visits) {
        $averageFrequencies[$state] = $visits / $totalVisits;
    }
    
    return [
        'num_simulations' => $numSimulations,
        'aggregated_visits' => $aggregatedVisits,
        'average_frequencies' => $averageFrequencies
    ];
}
?>
