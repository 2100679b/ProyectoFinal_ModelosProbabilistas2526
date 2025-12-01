<?php
/**
 * Algoritmo Baum-Welch (Expectation-Maximization) para HMM
 * Entrena los parámetros del HMM dado un conjunto de observaciones
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

require_once __DIR__ . '/../../../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['hmm']) || !isset($data['observations'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos. Se requiere hmm y observations']);
    exit;
}

$hmm = $data['hmm'];
$observations = $data['observations'];
$maxIterations = $data['maxIterations'] ?? 100;
$convergenceThreshold = $data['convergenceThreshold'] ?? 1e-6;

// Extraer componentes del HMM
$hiddenStates = array_column($hmm['hiddenStates'], 'id');
$observationSymbols = array_column($hmm['observations'], 'id');

// Copiar parámetros iniciales
$A = $hmm['transitionMatrix'];  // Matriz de transición
$B = $hmm['emissionMatrix'];    // Matriz de emisión
$pi = $hmm['initialProbabilities']; // Probabilidades iniciales

$T = count($observations);
$N = count($hiddenStates);
$M = count($observationSymbols);

// ==================================================
// FUNCIONES AUXILIARES
// ==================================================

/**
 * Algoritmo Forward
 */
function forward($observations, $hiddenStates, $A, $B, $pi) {
    $T = count($observations);
    $alpha = [];
    
    // Inicialización
    $alpha[0] = [];
    foreach ($hiddenStates as $state) {
        $alpha[0][$state] = $pi[$state] * $B[$state][$observations[0]];
    }
    
    // Recursión
    for ($t = 1; $t < $T; $t++) {
        $alpha[$t] = [];
        foreach ($hiddenStates as $state) {
            $sum = 0.0;
            foreach ($hiddenStates as $prevState) {
                $sum += $alpha[$t-1][$prevState] * $A[$prevState][$state];
            }
            $alpha[$t][$state] = $sum * $B[$state][$observations[$t]];
        }
    }
    
    return $alpha;
}

/**
 * Algoritmo Backward
 */
function backward($observations, $hiddenStates, $A, $B) {
    $T = count($observations);
    $beta = [];
    
    // Inicialización
    $beta[$T-1] = [];
    foreach ($hiddenStates as $state) {
        $beta[$T-1][$state] = 1.0;
    }
    
    // Recursión (hacia atrás)
    for ($t = $T - 2; $t >= 0; $t--) {
        $beta[$t] = [];
        foreach ($hiddenStates as $state) {
            $sum = 0.0;
            foreach ($hiddenStates as $nextState) {
                $sum += $A[$state][$nextState] * $B[$nextState][$observations[$t+1]] * $beta[$t+1][$nextState];
            }
            $beta[$t][$state] = $sum;
        }
    }
    
    return $beta;
}

/**
 * Calcular gamma (probabilidad de estar en estado i en tiempo t)
 */
function calculateGamma($alpha, $beta, $T, $hiddenStates) {
    $gamma = [];
    
    for ($t = 0; $t < $T; $t++) {
        $gamma[$t] = [];
        $denominator = 0.0;
        
        foreach ($hiddenStates as $state) {
            $denominator += $alpha[$t][$state] * $beta[$t][$state];
        }
        
        foreach ($hiddenStates as $state) {
            $gamma[$t][$state] = ($denominator > 0) ? 
                ($alpha[$t][$state] * $beta[$t][$state]) / $denominator : 0.0;
        }
    }
    
    return $gamma;
}

/**
 * Calcular xi (probabilidad de transición de i a j en tiempo t)
 */
function calculateXi($alpha, $beta, $observations, $hiddenStates, $A, $B, $T) {
    $xi = [];
    
    for ($t = 0; $t < $T - 1; $t++) {
        $xi[$t] = [];
        $denominator = 0.0;
        
        foreach ($hiddenStates as $i) {
            foreach ($hiddenStates as $j) {
                $denominator += $alpha[$t][$i] * $A[$i][$j] * 
                               $B[$j][$observations[$t+1]] * $beta[$t+1][$j];
            }
        }
        
        foreach ($hiddenStates as $i) {
            $xi[$t][$i] = [];
            foreach ($hiddenStates as $j) {
                $xi[$t][$i][$j] = ($denominator > 0) ? 
                    ($alpha[$t][$i] * $A[$i][$j] * $B[$j][$observations[$t+1]] * $beta[$t+1][$j]) / $denominator : 0.0;
            }
        }
    }
    
    return $xi;
}

// ==================================================
// ALGORITMO BAUM-WELCH
// ==================================================

$iterationHistory = [];
$previousLogLikelihood = -INF;

for ($iter = 0; $iter < $maxIterations; $iter++) {
    
    // E-STEP: Forward-Backward
    $alpha = forward($observations, $hiddenStates, $A, $B, $pi);
    $beta = backward($observations, $hiddenStates, $A, $B);
    $gamma = calculateGamma($alpha, $beta, $T, $hiddenStates);
    $xi = calculateXi($alpha, $beta, $observations, $hiddenStates, $A, $B, $T);
    
    // Calcular log-likelihood
    $logLikelihood = 0.0;
    foreach ($hiddenStates as $state) {
        $logLikelihood += $alpha[$T-1][$state];
    }
    $logLikelihood = ($logLikelihood > 0) ? log($logLikelihood) : -INF;
    
    $iterationHistory[] = [
        'iteration' => $iter + 1,
        'logLikelihood' => $logLikelihood
    ];
    
    // Verificar convergencia
    if (abs($logLikelihood - $previousLogLikelihood) < $convergenceThreshold) {
        break;
    }
    $previousLogLikelihood = $logLikelihood;
    
    // M-STEP: Re-estimar parámetros
    
    // Re-estimar pi
    foreach ($hiddenStates as $state) {
        $pi[$state] = $gamma[0][$state];
    }
    
    // Re-estimar A
    foreach ($hiddenStates as $i) {
        $denominator = 0.0;
        for ($t = 0; $t < $T - 1; $t++) {
            $denominator += $gamma[$t][$i];
        }
        
        foreach ($hiddenStates as $j) {
            $numerator = 0.0;
            for ($t = 0; $t < $T - 1; $t++) {
                $numerator += $xi[$t][$i][$j];
            }
            $A[$i][$j] = ($denominator > 0) ? $numerator / $denominator : 0.0;
        }
    }
    
    // Re-estimar B
    foreach ($hiddenStates as $state) {
        $denominator = 0.0;
        for ($t = 0; $t < $T; $t++) {
            $denominator += $gamma[$t][$state];
        }
        
        foreach ($observationSymbols as $obs) {
            $numerator = 0.0;
            for ($t = 0; $t < $T; $t++) {
                if ($observations[$t] === $obs) {
                    $numerator += $gamma[$t][$state];
                }
            }
            $B[$state][$obs] = ($denominator > 0) ? $numerator / $denominator : 0.0;
        }
    }
}

// ==================================================
// RESPUESTA
// ==================================================
echo json_encode([
    'success' => true,
    'algorithm' => 'Baum-Welch',
    'result' => [
        'trainedModel' => [
            'transitionMatrix' => $A,
            'emissionMatrix' => $B,
            'initialProbabilities' => $pi
        ],
        'iterations' => count($iterationHistory),
        'finalLogLikelihood' => $previousLogLikelihood,
        'converged' => count($iterationHistory) < $maxIterations,
        'iterationHistory' => $iterationHistory
    ],
    'interpretation' => [
        'title' => 'Algoritmo Baum-Welch (EM)',
        'description' => 'Entrena los parámetros del HMM para maximizar la probabilidad de las observaciones.',
        'iterations' => count($iterationHistory),
        'finalLogLikelihood' => number_format($previousLogLikelihood, 4),
        'status' => count($iterationHistory) < $maxIterations ? 'Convergió' : 'Alcanzó máximo de iteraciones',
        'meaning' => 'El modelo ha sido ajustado para explicar mejor las observaciones de entrenamiento.'
    ]
], JSON_PRETTY_PRINT);
