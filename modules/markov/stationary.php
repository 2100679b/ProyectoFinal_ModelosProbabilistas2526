<?php
/**
 * Cálculo de Estado Estacionario de Cadenas de Markov
 * Resuelve π = πP donde π es el vector estacionario
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Leer datos JSON del body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

// Extraer estados y matriz
$states = $data['states'] ?? [];
$matrix = $data['transitionMatrix'] ?? [];

if (empty($states)) {
    echo json_encode(['success' => false, 'error' => 'No hay estados definidos']);
    exit;
}

$n = count($states);

// Verificar que la matriz sea estocástica
foreach ($states as $state) {
    $id = $state['id'];
    if (!isset($matrix[$id])) continue;
    
    $sum = array_sum($matrix[$id]);
    if (abs($sum - 1.0) > 0.01) {
        echo json_encode([
            'success' => false, 
            'error' => "La fila del estado '{$state['label']}' no suma 1.0 (suma: $sum)"
        ]);
        exit;
    }
}

// Método de potencias para encontrar el estado estacionario
$maxIterations = 1000;
$tolerance = 1e-6;

// Inicializar vector uniforme
$pi = array_fill(0, $n, 1.0 / $n);
$stateIds = array_map(fn($s) => $s['id'], $states);

for ($iter = 0; $iter < $maxIterations; $iter++) {
    $newPi = array_fill(0, $n, 0.0);
    
    // π(t+1) = π(t) * P
    for ($j = 0; $j < $n; $j++) {
        $toId = $stateIds[$j];
        
        for ($i = 0; $i < $n; $i++) {
            $fromId = $stateIds[$i];
            $prob = $matrix[$fromId][$toId] ?? 0.0;
            $newPi[$j] += $pi[$i] * $prob;
        }
    }
    
    // Verificar convergencia
    $diff = 0;
    for ($i = 0; $i < $n; $i++) {
        $diff += abs($newPi[$i] - $pi[$i]);
    }
    
    $pi = $newPi;
    
    if ($diff < $tolerance) {
        break;
    }
}

// Normalizar (por seguridad)
$sum = array_sum($pi);
if ($sum > 0) {
    $pi = array_map(fn($p) => $p / $sum, $pi);
}

// Preparar respuesta
$distribution = [];
for ($i = 0; $i < $n; $i++) {
    $distribution[] = [
        'state' => $states[$i]['label'],
        'probability' => $pi[$i]
    ];
}

echo json_encode([
    'success' => true,
    'distribution' => $distribution,
    'iterations' => $iter + 1,
    'converged' => $iter < $maxIterations
]);