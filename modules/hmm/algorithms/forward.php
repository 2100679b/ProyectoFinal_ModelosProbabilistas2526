<?php
/**
 * Algoritmo Forward (α) para HMM
 * Calcula la probabilidad de observar una secuencia dada
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

// Matriz alpha: alpha[t][state] = P(O_1, O_2, ..., O_t, q_t = state | λ)
$alpha = [];

// ==================================================
// PASO 1: INICIALIZACIÓN (t = 0)
// ==================================================
$alpha[0] = [];
foreach ($hiddenStates as $state) {
    $obs = $observations[0];
    $alpha[0][$state] = $initialProb[$state] * $emissionMatrix[$state][$obs];
}

// ==================================================
// PASO 2: RECURSIÓN (t = 1 hasta T-1)
// ==================================================
for ($t = 1; $t < $T; $t++) {
    $alpha[$t] = [];
    $obs = $observations[$t];
    
    foreach ($hiddenStates as $state) {
        $sum = 0.0;
        
        // Sumar sobre todos los estados anteriores
        foreach ($hiddenStates as $prevState) {
            $sum += $alpha[$t-1][$prevState] * $transitionMatrix[$prevState][$state];
        }
        
        $alpha[$t][$state] = $sum * $emissionMatrix[$state][$obs];
    }
}

// ==================================================
// PASO 3: TERMINACIÓN
// ==================================================
$probability = 0.0;
foreach ($hiddenStates as $state) {
    $probability += $alpha[$T-1][$state];
}

$logProbability = ($probability > 0) ? log($probability) : -INF;

// ==================================================
// ANÁLISIS ADICIONAL
// ==================================================

// Calcular los estados más probables en cada tiempo
$mostProbableStates = [];
for ($t = 0; $t < $T; $t++) {
    $maxProb = -INF;
    $maxState = null;
    
    foreach ($hiddenStates as $state) {
        if ($alpha[$t][$state] > $maxProb) {
            $maxProb = $alpha[$t][$state];
            $maxState = $state;
        }
    }
    
    $mostProbableStates[] = [
        'time' => $t,
        'observation' => $observations[$t],
        'state' => $maxState,
        'probability' => $maxProb
    ];
}

// Formatear alpha para respuesta (redondear valores)
$alphaFormatted = [];
for ($t = 0; $t < $T; $t++) {
    $alphaFormatted[$t] = [];
    foreach ($hiddenStates as $state) {
        $alphaFormatted[$t][$state] = round($alpha[$t][$state], 8);
    }
}

// ==================================================
// RESPUESTA
// ==================================================
echo json_encode([
    'success' => true,
    'algorithm' => 'Forward',
    'result' => [
        'alpha' => $alphaFormatted,
        'probability' => $probability,
        'logProbability' => $logProbability,
        'mostProbableStates' => $mostProbableStates,
        'observations' => $observations,
        'sequenceLength' => $T
    ],
    'interpretation' => [
        'title' => 'Algoritmo Forward (α)',
        'description' => 'Calcula la probabilidad de observar la secuencia completa dado el modelo HMM.',
        'formula' => 'P(O|λ) donde O es la secuencia de observaciones y λ es el modelo HMM',
        'probability' => number_format($probability, 10),
        'logProbability' => number_format($logProbability, 4),
        'meaning' => $probability > 1e-10 ? 
            'La secuencia observada es probable dado el modelo.' : 
            'La secuencia observada es muy improbable dado el modelo.'
    ]
], JSON_PRETTY_PRINT);
