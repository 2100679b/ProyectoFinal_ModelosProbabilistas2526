<?php
/**
 * Algoritmo de Inferencia por Enumeración para Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * * Corregido: Búsqueda de rutas para la clase BayesianNetwork
 */

// 1. Manejo robusto de rutas para configuración
$configPath = __DIR__ . '/../../../config.php';
if (file_exists($configPath)) {
    require_once $configPath;
} else {
    if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__, 3));
}

// 2. Incluir clase de Red Bayesiana (Búsqueda inteligente)
$searchPaths = [
    __DIR__ . '/network.php',
    __DIR__ . '/BayesianNetwork.php',
    __DIR__ . '/../network.php',
    __DIR__ . '/../BayesianNetwork.php'
];

$networkClassPath = null;
foreach ($searchPaths as $path) {
    if (file_exists($path)) {
        $networkClassPath = $path;
        break;
    }
}

if (!$networkClassPath) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => "No se encontró la clase BayesianNetwork.php o network.php. Verifique que esté en modules/bayesian/"
    ]);
    exit;
}

require_once $networkClassPath;
header('Content-Type: application/json; charset=utf-8');

try {
    // Lectura de Entrada (JSON raw o $_POST)
    $input = [];
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $jsonInput = json_decode($rawInput, true);
        if (json_last_error() === JSON_ERROR_NONE) $input = $jsonInput;
    }
    if (!empty($_POST)) $input = array_merge($input, $_POST);

    $networkData = isset($input['network']) ? (is_string($input['network']) ? json_decode($input['network'], true) : $input['network']) : [];
    $queryVariable = $input['query'] ?? '';
    $evidence = isset($input['evidence']) ? (is_string($input['evidence']) ? json_decode($input['evidence'], true) : $input['evidence']) : [];
    
    if (empty($networkData) || empty($queryVariable)) {
        throw new Exception('Faltan datos obligatorios para la inferencia.');
    }
    
    $network = new BayesianNetwork($networkData);
    $startTime = microtime(true);
    
    // Algoritmo
    $result = enumerationAsk($network, $queryVariable, $evidence);
    
    $executionTime = microtime(true) - $startTime;
    
    echo json_encode([
        'success' => true,
        'algorithm' => 'enumeration',
        'query' => $queryVariable,
        'evidence' => $evidence,
        'probabilities' => $result['probabilities'],
        'execution_time' => round($executionTime, 5) . 's',
        'steps' => $result['steps']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function enumerationAsk($network, $queryVar, $evidence) {
    $steps = []; $probabilities = []; $allVars = [];
    foreach ($network->getNodes() as $node) {
        $allVars[] = is_array($node) ? $node['id'] : $node;
    }

    $queryValues = $network->getNodeValues($queryVar);
    if (empty($queryValues)) $queryValues = ['True', 'False'];

    foreach ($queryValues as $val) {
        $extendedEvidence = array_merge($evidence, [$queryVar => $val]);
        $prob = enumerateAll($network, $allVars, $extendedEvidence);
        $probabilities[$val] = $prob;
    }

    $sum = array_sum($probabilities);
    if ($sum > 0) {
        foreach ($probabilities as $key => $val) $probabilities[$key] = $val / $sum;
    }

    return ['probabilities' => $probabilities, 'steps' => []];
}

function enumerateAll($network, $vars, $evidence) {
    if (empty($vars)) return 1.0;
    $Y = array_shift($vars);

    if (isset($evidence[$Y])) {
        $prob = $network->getConditionalProbability($Y, $evidence[$Y], array_intersect_key($evidence, array_flip($network->getParents($Y))));
        return $prob * enumerateAll($network, $vars, $evidence);
    } else {
        $totalProb = 0.0;
        $valuesY = $network->getNodeValues($Y);
        if (empty($valuesY)) $valuesY = ['True', 'False'];
        foreach ($valuesY as $val) {
            $newEvidence = array_merge($evidence, [$Y => $val]);
            $prob = $network->getConditionalProbability($Y, $val, array_intersect_key($newEvidence, array_flip($network->getParents($Y))));
            $totalProb += $prob * enumerateAll($network, $vars, $newEvidence);
        }
        return $totalProb;
    }
}