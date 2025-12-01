<?php
/**
 * Algoritmo Viterbi para HMM
 * Encuentra la secuencia de estados ocultos más probable
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

// Extraer componentes del HMM
$hiddenStates = array_column($hmm['hiddenStates'], 'id');
$transitionMatrix = $hmm['transitionMatrix'];
$emissionMatrix = $hmm['emissionMatrix'];
$initialProb = $hmm['initialProbabilities'];

$T = count($observations);
$N = count($hiddenStates);

// Matrices
$delta = [];  // Probabilidades máximas
$psi = [];    // Punteros para backtracking

// ==================================================
// PASO 1: INICIALIZACIÓN (t = 0)
// ==================================================
$delta[0] = [];
$psi[0] = [];

foreach ($hiddenStates as $state) {
    $obs = $observations[0];
    $delta[0][$state] = $initialProb[$state] * $emissionMatrix[$state][$obs];
    $psi[0][$state] = null;
}

// ==================================================
// PASO 2: RECURSIÓN (t = 1 hasta T-1)
// ==================================================
for ($t = 1; $t < $T; $t++) {
    $delta[$t] = [];
    $psi[$t] = [];
    $obs = $observations[$t];
    
    foreach ($hiddenStates as $state) {
        $maxProb = -INF;
        $maxState = null;
        
        // Encontrar el estado anterior que maximiza la probabilidad
        foreach ($hiddenStates as $prevState) {
            $prob = $delta[$t-1][$prevState] * $transitionMatrix[$prevState][$state];
            
            if ($prob > $maxProb) {
                $maxProb = $prob;
                $maxState = $prevState;
            }
        }
        
        $delta[$t][$state] = $maxProb * $emissionMatrix[$state][$obs];
        $psi[$t][$state] = $maxState;
    }
}

// ==================================================
// PASO 3: TERMINACIÓN
// ==================================================
$maxProb = -INF;
$lastState = null;

foreach ($hiddenStates as $state) {
    if ($delta[$T-1][$state] > $maxProb) {
        $maxProb = $delta[$T-1][$state];
        $lastState = $state;
    }
}

// ==================================================
// PASO 4: BACKTRACKING (reconstruir el camino óptimo)
// ==================================================
$optimalPath = [$lastState];

for ($t = $T - 1; $t > 0; $t--) {
    $lastState = $psi[$t][$lastState];
    array_unshift($optimalPath, $lastState);
}

$logProbability = ($maxProb > 0) ? log($maxProb) : -INF;

// ==================================================
// ANÁLISIS ADICIONAL
// ==================================================

// Crear secuencia detallada
$pathDetails = [];
for ($t = 0; $t < $T; $t++) {
    $pathDetails[] = [
        'time' => $t,
        'observation' => $observations[$t],
        'hiddenState' => $optimalPath[$t],
        'probability' => round($delta[$t][$optimalPath[$t]], 8)
    ];
}

// Formatear delta para respuesta
$deltaFormatted = [];
for ($t = 0; $t < $T; $t++) {
    $deltaFormatted[$t] = [];
    foreach ($hiddenStates as $state) {
        $deltaFormatted[$t][$state] = round($delta[$t][$state], 8);
    }
}

// Obtener labels de estados
$stateLabels = [];
foreach ($hmm['hiddenStates'] as $state) {
    $stateLabels[$state['id']] = $state['label'] ?? $state['id'];
}

$pathWithLabels = array_map(function($stateId) use ($stateLabels) {
    return $stateLabels[$stateId] ?? $stateId;
}, $optimalPath);

// ==================================================
// RESPUESTA
// ==================================================
echo json_encode([
    'success' => true,
    'algorithm' => 'Viterbi',
    'result' => [
        'optimalPath' => $optimalPath,
        'optimalPathLabels' => $pathWithLabels,
        'probability' => $maxProb,
        'logProbability' => $logProbability,
        'pathDetails' => $pathDetails,
        'delta' => $deltaFormatted,
        'observations' => $observations,
        'sequenceLength' => $T
    ],
    'interpretation' => [
        'title' => 'Algoritmo Viterbi',
        'description' => 'Encuentra la secuencia de estados ocultos más probable que generó las observaciones.',
        'optimalSequence' => implode(' → ', $pathWithLabels),
        'probability' => number_format($maxProb, 10),
        'logProbability' => number_format($logProbability, 4),
        'meaning' => 'Esta es la secuencia de estados más probable que explica las observaciones dadas.'
    ]
], JSON_PRETTY_PRINT);
