<?php
/**
 * Cálculo de Distribución Estacionaria
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
    $method = $_POST['method'] ?? 'iterative'; // iterative, power, eigenvalue
    $maxIterations = isset($_POST['max_iterations']) ? (int)$_POST['max_iterations'] : 1000;
    $tolerance = isset($_POST['tolerance']) ? (float)$_POST['tolerance'] : 1e-6;
    
    // Validaciones
    if (empty($chainData)) {
        throw new Exception('No se proporcionó cadena de Markov');
    }
    
    // Crear cadena
    $chain = new MarkovChain($chainData);
    
    // Validar cadena
    $validation = $chain->validate();
    if (!$validation['valid']) {
        throw new Exception('Cadena inválida: ' . implode(', ', $validation['errors']));
    }
    
    // Verificar si la cadena es ergódica
    $isErgodic = $chain->isErgodic();
    $isIrreducible = $chain->isIrreducible();
    $isAperiodic = $chain->isAperiodic();
    
    if (!$isErgodic) {
        $reason = [];
        if (!$isIrreducible) {
            $reason[] = 'no es irreducible (no todos los estados son accesibles)';
        }
        if (!$isAperiodic) {
            $reason[] = 'no es aperiódica (tiene periodicidad)';
        }
        
        echo json_encode([
            'success' => false,
            'has_stationary' => false,
            'reason' => 'La cadena no es ergódica: ' . implode(' y ', $reason),
            'properties' => [
                'is_irreducible' => $isIrreducible,
                'is_aperiodic' => $isAperiodic,
                'is_ergodic' => $isErgodic
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Calcular distribución estacionaria
    $startTime = microtime(true);
    
    switch ($method) {
        case 'iterative':
            $result = calculateStationaryIterative($chain, $maxIterations, $tolerance);
            break;
        case 'power':
            $result = calculateStationaryPowerMethod($chain, $maxIterations, $tolerance);
            break;
        case 'eigenvalue':
            $result = calculateStationaryEigenvalue($chain);
            break;
        default:
            throw new Exception('Método desconocido: ' . $method);
    }
    
    $executionTime = (microtime(true) - $startTime) * 1000;
    
    if ($result === null) {
        throw new Exception('No se pudo calcular la distribución estacionaria (no convergió)');
    }
    
    // Verificar que es una distribución válida
    $sum = array_sum($result['distribution']);
    $isValid = abs($sum - 1.0) < 0.01;
    
    // Formatear resultados
    $states = $chain->getStates();
    $formattedDistribution = [];
    
    foreach ($result['distribution'] as $index => $prob) {
        $formattedDistribution[] = [
            'state' => $states[$index],
            'probability' => $prob,
            'percentage' => round($prob * 100, 2) . '%',
            'formatted' => number_format($prob, 6)
        ];
    }
    
    // Verificar distribución estacionaria (π = πP)
    $verification = verifyStationaryDistribution($chain, $result['distribution']);
    
    // Responder
    echo json_encode([
        'success' => true,
        'has_stationary' => true,
        'method' => $method,
        'distribution' => $result['distribution'],
        'formatted_distribution' => $formattedDistribution,
        'sum' => round($sum, 10),
        'is_valid' => $isValid,
        'iterations' => $result['iterations'] ?? null,
        'converged' => $result['converged'] ?? true,
        'max_difference' => $result['max_difference'] ?? null,
        'verification' => $verification,
        'execution_time_ms' => round($executionTime, 2),
        'properties' => [
            'is_irreducible' => $isIrreducible,
            'is_aperiodic' => $isAperiodic,
            'is_ergodic' => $isErgodic
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * Calcular distribución estacionaria - Método Iterativo
 * @param MarkovChain $chain
 * @param int $maxIterations
 * @param float $tolerance
 * @return array|null
 */
function calculateStationaryIterative($chain, $maxIterations, $tolerance) {
    $stateCount = $chain->getStateCount();
    
    // Iniciar con distribución uniforme
    $distribution = array_fill(0, $stateCount, 1.0 / $stateCount);
    
    for ($iter = 0; $iter < $maxIterations; $iter++) {
        $newDistribution = [];
        
        // π(t+1) = π(t) * P
        for ($j = 0; $j < $stateCount; $j++) {
            $prob = 0.0;
            for ($i = 0; $i < $stateCount; $i++) {
                $prob += $distribution[$i] * $chain->getTransitionProbability($i, $j);
            }
            $newDistribution[$j] = $prob;
        }
        
        // Verificar convergencia
        $maxDiff = 0.0;
        for ($i = 0; $i < $stateCount; $i++) {
            $diff = abs($newDistribution[$i] - $distribution[$i]);
            if ($diff > $maxDiff) {
                $maxDiff = $diff;
            }
        }
        
        $distribution = $newDistribution;
        
        if ($maxDiff < $tolerance) {
            return [
                'distribution' => $distribution,
                'iterations' => $iter + 1,
                'converged' => true,
                'max_difference' => $maxDiff
            ];
        }
    }
    
    // No convergió
    return [
        'distribution' => $distribution,
        'iterations' => $maxIterations,
        'converged' => false,
        'max_difference' => $maxDiff ?? null
    ];
}

/**
 * Calcular distribución estacionaria - Método de Potencia
 * @param MarkovChain $chain
 * @param int $maxIterations
 * @param float $tolerance
 * @return array|null
 */
function calculateStationaryPowerMethod($chain, $maxIterations, $tolerance) {
    $stateCount = $chain->getStateCount();
    $matrix = $chain->getTransitionMatrix();
    
    // P^n para n grande
    $powerMatrix = $matrix;
    
    for ($iter = 0; $iter < $maxIterations; $iter++) {
        $newMatrix = multiplyMatrices($powerMatrix, $matrix, $stateCount);
        
        // Verificar convergencia (todas las filas son iguales)
        $converged = true;
        for ($i = 1; $i < $stateCount; $i++) {
            for ($j = 0; $j < $stateCount; $j++) {
                if (abs($newMatrix[$i][$j] - $newMatrix[0][$j]) > $tolerance) {
                    $converged = false;
                    break 2;
                }
            }
        }
        
        if ($converged) {
            return [
                'distribution' => $newMatrix[0],
                'iterations' => $iter + 1,
                'converged' => true
            ];
        }
        
        $powerMatrix = $newMatrix;
    }
    
    // Retornar primera fila como aproximación
    return [
        'distribution' => $powerMatrix[0],
        'iterations' => $maxIterations,
        'converged' => false
    ];
}

/**
 * Calcular distribución estacionaria - Método de Eigenvalores
 * (Requiere resolver el sistema lineal (P^T - I)π = 0 con Σπ = 1)
 * Simplificación: usar método iterativo como fallback
 * @param MarkovChain $chain
 * @return array|null
 */
function calculateStationaryEigenvalue($chain) {
    // Por simplicidad, usar método iterativo con alta precisión
    return calculateStationaryIterative($chain, 10000, 1e-10);
}

/**
 * Multiplicar dos matrices
 * @param array $A
 * @param array $B
 * @param int $n Dimensión
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

/**
 * Verificar que π = πP
 * @param MarkovChain $chain
 * @param array $distribution
 * @return array
 */
function verifyStationaryDistribution($chain, $distribution) {
    $stateCount = $chain->getStateCount();
    $result = [];
    
    // Calcular πP
    for ($j = 0; $j < $stateCount; $j++) {
        $prob = 0.0;
        for ($i = 0; $i < $stateCount; $i++) {
            $prob += $distribution[$i] * $chain->getTransitionProbability($i, $j);
        }
        $result[$j] = $prob;
    }
    
    // Calcular diferencias
    $maxDiff = 0.0;
    $differences = [];
    
    for ($i = 0; $i < $stateCount; $i++) {
        $diff = abs($result[$i] - $distribution[$i]);
        $differences[$i] = $diff;
        if ($diff > $maxDiff) {
            $maxDiff = $diff;
        }
    }
    
    return [
        'pi_times_P' => $result,
        'differences' => $differences,
        'max_difference' => $maxDiff,
        'is_stationary' => $maxDiff < 1e-4
    ];
}
?>
