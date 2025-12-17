<?php
/**
 * Operaciones con Matriz de Transición
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
    $operation = $_POST['operation'] ?? 'power';
    $n = isset($_POST['n']) ? (int)$_POST['n'] : 2;
    
    // Validaciones
    if (empty($chainData)) {
        throw new Exception('No se proporcionó cadena de Markov');
    }
    
    if ($n < 1 || $n > 100) {
        throw new Exception('El exponente n debe estar entre 1 y 100');
    }
    
    // Crear cadena
    $chain = new MarkovChain($chainData);
    
    // Validar cadena
    $validation = $chain->validate();
    if (!$validation['valid']) {
        throw new Exception('Cadena inválida: ' . implode(', ', $validation['errors']));
    }
    
    $startTime = microtime(true);
    $result = [];
    
    switch ($operation) {
        case 'power':
            $result = calculateMatrixPower($chain, $n);
            break;
        case 'n_step':
            $result = calculateNStepTransitions($chain, $n);
            break;
        case 'absorbing_probabilities':
            $result = calculateAbsorbingProbabilities($chain);
            break;
        case 'fundamental_matrix':
            $result = calculateFundamentalMatrix($chain);
            break;
        default:
            throw new Exception('Operación desconocida: ' . $operation);
    }
    
    $executionTime = (microtime(true) - $startTime) * 1000;
    
    // Responder
    echo json_encode([
        'success' => true,
        'operation' => $operation,
        'parameters' => ['n' => $n],
        'result' => $result,
        'execution_time_ms' => round($executionTime, 2)
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * Calcular P^n (matriz elevada a la potencia n)
 * @param MarkovChain $chain
 * @param int $n
 * @return array
 */
function calculateMatrixPower($chain, $n) {
    $stateCount = $chain->getStateCount();
    $matrix = $chain->getTransitionMatrix();
    $result = $matrix;
    
    // Multiplicar n-1 veces
    for ($i = 1; $i < $n; $i++) {
        $result = multiplyMatrices($result, $matrix, $stateCount);
    }
    
    // Formatear resultado
    $states = $chain->getStates();
    $formattedMatrix = [];
    
    for ($i = 0; $i < $stateCount; $i++) {
        $row = [];
        for ($j = 0; $j < $stateCount; $j++) {
            $row[] = [
                'value' => $result[$i][$j],
                'formatted' => number_format($result[$i][$j], 6),
                'percentage' => number_format($result[$i][$j] * 100, 2) . '%'
            ];
        }
        $formattedMatrix[$states[$i]] = $row;
    }
    
    return [
        'power' => $n,
        'matrix' => $result,
        'formatted_matrix' => $formattedMatrix,
        'interpretation' => "P^{$n} representa las probabilidades de transición en exactamente {$n} pasos"
    ];
}

/**
 * Calcular probabilidades de transición en n pasos
 * @param MarkovChain $chain
 * @param int $n
 * @return array
 */
function calculateNStepTransitions($chain, $n) {
    $states = $chain->getStates();
    $stateCount = $chain->getStateCount();
    
    // Calcular P^n
    $Pn = calculateMatrixPower($chain, $n)['matrix'];
    
    // Ejemplos de transiciones específicas
    $examples = [];
    
    for ($i = 0; $i < min(3, $stateCount); $i++) {
        for ($j = 0; $j < min(3, $stateCount); $j++) {
            if ($i !== $j) {
                $examples[] = [
                    'from' => $states[$i],
                    'to' => $states[$j],
                    'probability' => $Pn[$i][$j],
                    'percentage' => number_format($Pn[$i][$j] * 100, 2) . '%',
                    'interpretation' => "Probabilidad de ir de {$states[$i]} a {$states[$j]} en {$n} pasos"
                ];
            }
        }
    }
    
    return [
        'n' => $n,
        'matrix' => $Pn,
        'examples' => $examples
    ];
}

/**
 * Calcular probabilidades de absorción
 * @param MarkovChain $chain
 * @return array
 */
function calculateAbsorbingProbabilities($chain) {
    $absorbingStates = $chain->getAbsorbingStates();
    
    if (empty($absorbingStates)) {
        return [
            'has_absorbing_states' => false,
            'message' => 'Esta cadena no tiene estados absorbentes'
        ];
    }
    
    $states = $chain->getStates();
    $stateCount = $chain->getStateCount();
    $probabilities = [];
    
    // Para cada estado no absorbente, calcular probabilidad de absorción
    for ($i = 0; $i < $stateCount; $i++) {
        if (!in_array($i, $absorbingStates)) {
            $stateProbs = [];
            foreach ($absorbingStates as $abs) {
                $prob = $chain->getAbsorptionProbability($i, $abs, 1000);
                $stateProbs[$states[$abs]] = [
                    'probability' => $prob,
                    'percentage' => number_format($prob * 100, 2) . '%'
                ];
            }
            $probabilities[$states[$i]] = $stateProbs;
        }
    }
    
    return [
        'has_absorbing_states' => true,
        'absorbing_states' => array_map(function($idx) use ($states) {
            return $states[$idx];
        }, $absorbingStates),
        'absorption_probabilities' => $probabilities
    ];
}

/**
 * Calcular matriz fundamental (para cadenas absorbentes)
 * @param MarkovChain $chain
 * @return array
 */
function calculateFundamentalMatrix($chain) {
    $absorbingStates = $chain->getAbsorbingStates();
    
    if (empty($absorbingStates)) {
        return [
            'has_fundamental_matrix' => false,
            'message' => 'La matriz fundamental solo existe para cadenas con estados absorbentes'
        ];
    }
    
    // Simplificación: retornar mensaje informativo
    // La implementación completa requiere algebra lineal avanzada
    
    return [
        'has_fundamental_matrix' => true,
        'absorbing_states' => count($absorbingStates),
        'transient_states' => $chain->getStateCount() - count($absorbingStates),
        'note' => 'La matriz fundamental N = (I - Q)^(-1) requiere inversión de matrices',
        'interpretation' => 'N[i,j] representa el número esperado de visitas al estado transitorio j, comenzando desde el estado transitorio i'
    ];
}

/**
 * Multiplicar dos matrices
 * @param array $A
 * @param array $B
 * @param int $n
 * @return array
 */
function multiplyMatrices($A, $B, $n) {
    $result = [];
    
    for ($i = 0; $i < $n; $i++) {
        $result[$i] = [];
        for ($j = 0; $j < $n; $j++) {
            $sum = 0.0;
            for ($k = 0; $k < $n; $k++) {
                $sum += $A[$i][$k] * $B[$k][$j];
            }
            $result[$i][$j] = $sum;
        }
    }
    
    return $result;
}
?>
