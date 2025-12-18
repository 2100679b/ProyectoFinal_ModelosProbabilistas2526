<?php
/**
 * Algoritmo de Eliminación de Variables
 * Corregido: Búsqueda de rutas para la clase BayesianNetwork
 */

$configPath = __DIR__ . '/../../../config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

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
    echo json_encode(['success' => false, 'error' => "No se encontró network.php"]);
    exit;
}

require_once $networkClassPath;
header('Content-Type: application/json; charset=utf-8');

try {
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
    
    if (empty($networkData) || empty($queryVariable)) throw new Exception('Datos incompletos.');
    
    $network = new BayesianNetwork($networkData);
    $startTime = microtime(true);
    
    $result = variableElimination($network, $queryVariable, $evidence);
    
    echo json_encode([
        'success' => true,
        'algorithm' => 'variable_elimination',
        'probabilities' => $result['probabilities'],
        'execution_time' => round(microtime(true) - $startTime, 5) . 's'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

// Funciones de apoyo (Igual que antes, pero dentro de este archivo corregido)
function variableElimination($network, $queryVar, $evidence) {
    $allVars = [];
    foreach ($network->getNodes() as $node) $allVars[] = is_array($node) ? $node['id'] : $node;
    
    $varsToEliminate = array_diff($allVars, [$queryVar], array_keys($evidence));
    $factors = createInitialFactors($network, $evidence);
    
    foreach ($varsToEliminate as $var) {
        $relevant = []; $remaining = [];
        foreach ($factors as $f) {
            if (in_array($var, $f['vars'])) $relevant[] = $f;
            else $remaining[] = $f;
        }
        if (empty($relevant)) continue;
        $product = multiplyFactors($relevant);
        $remaining[] = sumOut($product, $var);
        $factors = $remaining;
    }
    
    $final = multiplyFactors($factors);
    return ['probabilities' => normalizeFactor($final, $queryVar)];
}

function createInitialFactors($network, $evidence) {
    $factors = [];
    foreach ($network->getNodes() as $node) {
        $id = is_array($node) ? $node['id'] : $node;
        $parents = $network->getParents($id);
        $vars = array_merge([$id], $parents);
        $factor = ['vars' => $vars, 'table' => []];
        $combinations = generateCombinations($network, $vars);
        foreach ($combinations as $assign) {
            $match = true;
            foreach ($evidence as $eVar => $eVal) {
                if (isset($assign[$eVar]) && $assign[$eVar] !== $eVal) { $match = false; break; }
            }
            if ($match) {
                $prob = $network->getConditionalProbability($id, $assign[$id], array_intersect_key($assign, array_flip($parents)));
                $factor['table'][json_encode($assign)] = $prob;
            }
        }
        if (!empty($factor['table'])) $factors[] = $factor;
    }
    return $factors;
}

function multiplyFactors($factors) {
    if (empty($factors)) return ['vars' => [], 'table' => []];
    $res = array_shift($factors);
    foreach ($factors as $f2) {
        $newVars = array_values(array_unique(array_merge($res['vars'], $f2['vars'])));
        $newTable = [];
        foreach ($res['table'] as $k1 => $v1) {
            $a1 = json_decode($k1, true);
            foreach ($f2['table'] as $k2 => $v2) {
                $a2 = json_decode($k2, true);
                $match = true;
                foreach ($a1 as $v => $val) { if (isset($a2[$v]) && $a2[$v] !== $val) { $match = false; break; } }
                if ($match) $newTable[json_encode(array_merge($a1, $a2))] = $v1 * $v2;
            }
        }
        $res = ['vars' => $newVars, 'table' => $newTable];
    }
    return $res;
}

function sumOut($factor, $var) {
    $newVars = array_values(array_diff($factor['vars'], [$var]));
    $newTable = [];
    foreach ($factor['table'] as $k => $v) {
        $a = json_decode($k, true); unset($a[$var]);
        $nk = json_encode($a);
        $newTable[$nk] = ($newTable[$nk] ?? 0) + $v;
    }
    return ['vars' => $newVars, 'table' => $newTable];
}

function normalizeFactor($factor, $queryVar) {
    $dist = []; $sum = 0;
    foreach ($factor['table'] as $k => $v) {
        $a = json_decode($k, true);
        if (isset($a[$queryVar])) {
            $val = $a[$queryVar];
            $dist[$val] = ($dist[$val] ?? 0) + $v;
            $sum += $v;
        }
    }
    if ($sum > 0) foreach ($dist as &$p) $p /= $sum;
    return $dist;
}

function generateCombinations($network, $vars) {
    if (empty($vars)) return [[]];
    $v = array_shift($vars);
    $vals = $network->getNodeValues($v);
    if (empty($vals)) $vals = ['True', 'False'];
    $rest = generateCombinations($network, $vars);
    $res = [];
    foreach ($vals as $val) foreach ($rest as $r) $res[] = array_merge([$v => $val], $r);
    return $res;
}