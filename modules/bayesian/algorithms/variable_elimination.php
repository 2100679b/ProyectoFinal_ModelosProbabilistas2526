<?php
/**
 * Algoritmo de Eliminación de Variables para Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * * Corrige problemas de rutas y lectura de datos (POST/JSON).
 */

// 1. Manejo robusto de rutas
$configPath = __DIR__ . '/../../config.php';
if (!file_exists($configPath)) {
    $configPath = __DIR__ . '/../../../config.php';
}

if (file_exists($configPath)) {
    require_once $configPath;
} else {
    if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__, 2));
}

// 2. Incluir clase de Red
$networkClassPath = __DIR__ . '/network.php';
if (!file_exists($networkClassPath)) {
    $networkClassPath = __DIR__ . '/BayesianNetwork.php';
}
require_once $networkClassPath;

header('Content-Type: application/json; charset=utf-8');

try {
    // 3. Lectura Robusta de Entrada (POST vs JSON Raw)
    $input = [];
    
    // Si se envía como JSON en el cuerpo (fetch standard)
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $jsonInput = json_decode($rawInput, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $input = $jsonInput;
        }
    }
    
    // Si se envía como Form Data, mezcla o sobreescribe
    if (!empty($_POST)) {
        $input = array_merge($input, $_POST);
    }

    // Extraer variables con validación de tipo (string JSON vs Array directo)
    $networkData = isset($input['network']) ? (is_string($input['network']) ? json_decode($input['network'], true) : $input['network']) : [];
    $queryVariable = $input['query'] ?? '';
    $evidence = isset($input['evidence']) ? (is_string($input['evidence']) ? json_decode($input['evidence'], true) : $input['evidence']) : [];
    $eliminationOrder = isset($input['elimination_order']) ? (is_string($input['elimination_order']) ? json_decode($input['elimination_order'], true) : $input['elimination_order']) : null;
    
    // Validar entrada
    if (empty($networkData)) {
        throw new Exception('No se proporcionó red bayesiana (Network Data empty).');
    }
    
    if (empty($queryVariable)) {
        throw new Exception('No se especificó variable de consulta.');
    }
    
    // Crear red bayesiana
    $network = new BayesianNetwork($networkData);
    
    // Validar red
    $validation = $network->validate();
    if (!$validation['valid']) {
        throw new Exception('Red inválida: ' . implode(', ', $validation['errors']));
    }
    
    // Verificar que la variable de consulta existe
    $queryNode = $network->getNode($queryVariable);
    if (!$queryNode) {
        throw new Exception("La variable de consulta '$queryVariable' no existe en la red");
    }
    
    // Iniciar medición de tiempo
    $startTime = microtime(true);
    
    // Ejecutar eliminación de variables
    $result = variableElimination($network, $queryVariable, $evidence, $eliminationOrder);
    
    // Tiempo de ejecución
    $executionTime = microtime(true) - $startTime;
    
    // Responder
    echo json_encode([
        'success' => true,
        'algorithm' => 'Eliminación de Variables',
        'query' => $queryVariable,
        'evidence' => $evidence,
        'results' => $result['probabilities'], // Estandarizado como 'results' para el frontend
        'probabilities' => $result['probabilities'],
        'execution_time' => round($executionTime, 5) . 's',
        'steps' => $result['steps'],
        'elimination_order' => $result['elimination_order'],
        'metadata' => [
            'factors_created' => $result['factors_created'],
            'max_factor_size' => $result['max_factor_size']
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// ==========================================
// ALGORITMO DE ELIMINACIÓN DE VARIABLES
// ==========================================

function variableElimination($network, $queryVar, $evidence, $eliminationOrder = null) {
    $steps = [];
    $factorsCreated = 0;
    $maxFactorSize = 0;
    
    // 1. Identificar variables
    $allVars = [];
    foreach ($network->getNodes() as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        $allVars[] = $nodeId;
    }
    
    // Variables a eliminar = Todas - (Query + Evidence)
    $varsToEliminate = array_diff($allVars, [$queryVar], array_keys($evidence));
    
    // 2. Crear Factores Iniciales
    $factors = createInitialFactors($network, $evidence);
    $factorsCreated = count($factors);
    
    $steps[] = "Se crearon " . count($factors) . " factores iniciales basados en las CPTs.";
    
    // 3. Determinar Orden de Eliminación
    if ($eliminationOrder === null) {
        $eliminationOrder = determineEliminationOrder($network, $varsToEliminate);
        $steps[] = "Orden de eliminación (heurística): " . implode(' -> ', $eliminationOrder);
    }
    
    // 4. Proceso de Eliminación (Sum-Product)
    foreach ($eliminationOrder as $var) {
        // Encontrar factores que involucren a esta variable
        $relevantFactors = [];
        $otherFactors = [];
        
        foreach ($factors as $f) {
            if (in_array($var, $f['vars'])) {
                $relevantFactors[] = $f;
            } else {
                $otherFactors[] = $f;
            }
        }
        
        if (empty($relevantFactors)) {
            // Si la variable no está en ningún factor (ej. nodo hoja irrelevante), se ignora
            continue;
        }
        
        // Multiplicar factores relevantes
        $productFactor = multiplyFactors($relevantFactors);
        
        // Sumarizar (marginalizar) la variable fuera
        $newFactor = sumOut($productFactor, $var);
        
        // El nuevo factor se añade al conjunto
        $otherFactors[] = $newFactor;
        $factors = $otherFactors;
        
        $steps[] = "Eliminada variable '$var': Multiplicados " . count($relevantFactors) . " factores -> Generado nuevo factor con vars [" . implode(', ', $newFactor['vars']) . "]";
        
        // Métricas
        if (count($productFactor['vars']) > $maxFactorSize) {
            $maxFactorSize = count($productFactor['vars']);
        }
    }
    
    // 5. Multiplicar factores restantes (que solo deben contener a QueryVar)
    $finalFactor = multiplyFactors($factors);
    
    // 6. Normalizar resultado
    $probabilities = normalizeFactor($finalFactor, $queryVar);
    
    return [
        'probabilities' => $probabilities,
        'steps' => $steps,
        'elimination_order' => $eliminationOrder,
        'factors_created' => $factorsCreated,
        'max_factor_size' => $maxFactorSize
    ];
}

// ==========================================
// FUNCIONES AUXILIARES (FACTORES)
// ==========================================

function createInitialFactors($network, $evidence) {
    $factors = [];
    
    foreach ($network->getNodes() as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        $parents = $network->getParents($nodeId);
        
        // Si el nodo es evidencia conocida, reducimos el factor inmediatamente (slice)
        // Pero para simplificar la implementación general, creamos el factor completo y filtramos valores inválidos
        
        $vars = array_merge([$nodeId], $parents);
        $factor = [
            'vars' => $vars,
            'table' => []
        ];
        
        // Generar combinaciones para la tabla
        // Nota: Esto puede ser costoso para nodos con muchos padres.
        // En una impl. profesional se usaría iteración perezosa o punteros.
        $combinations = generateCombinations($network, $vars);
        
        foreach ($combinations as $assignment) {
            // Verificar consistencia con evidencia
            $consistent = true;
            foreach ($evidence as $evVar => $evVal) {
                if (isset($assignment[$evVar]) && $assignment[$evVar] !== $evVal) {
                    $consistent = false;
                    break;
                }
            }
            
            if ($consistent) {
                // Obtener probabilidad P(Node | Parents)
                $nodeVal = $assignment[$nodeId];
                $parentVals = array_intersect_key($assignment, array_flip($parents));
                $prob = $network->getConditionalProbability($nodeId, $nodeVal, $parentVals);
                
                // Guardar en tabla. Clave es JSON de la asignación de las variables del factor
                // Filtramos la asignación para que solo tenga las claves de $vars
                $factorKeyArr = array_intersect_key($assignment, array_flip($vars));
                ksort($factorKeyArr); // Ordenar claves para consistencia
                $factor['table'][json_encode($factorKeyArr)] = $prob;
            }
        }
        
        // Si el factor quedó vacío por contradicción con evidencia, se ignora (prob 0)
        if (!empty($factor['table'])) {
            $factors[] = $factor;
        }
    }
    
    return $factors;
}

function multiplyFactors($factors) {
    if (empty($factors)) return ['vars' => [], 'table' => []];
    
    // Empezar con el primero
    $result = array_shift($factors);
    
    foreach ($factors as $nextFactor) {
        $newVars = array_unique(array_merge($result['vars'], $nextFactor['vars']));
        sort($newVars);
        $newTable = [];
        
        // Producto cartesiano "inteligente" (Join)
        // Iteramos sobre la tabla del primer factor
        foreach ($result['table'] as $k1 => $v1) {
            $assign1 = json_decode($k1, true);
            
            // Iteramos sobre la tabla del segundo factor
            foreach ($nextFactor['table'] as $k2 => $v2) {
                $assign2 = json_decode($k2, true);
                
                // Verificar si coinciden en las variables comunes
                $match = true;
                foreach ($assign1 as $var => $val) {
                    if (isset($assign2[$var]) && $assign2[$var] !== $val) {
                        $match = false;
                        break;
                    }
                }
                
                if ($match) {
                    // Combinar asignaciones
                    $merged = array_merge($assign1, $assign2);
                    ksort($merged);
                    $newTable[json_encode($merged)] = $v1 * $v2;
                }
            }
        }
        
        $result = ['vars' => $newVars, 'table' => $newTable];
    }
    
    return $result;
}

function sumOut($factor, $varToEliminate) {
    $newVars = array_values(array_diff($factor['vars'], [$varToEliminate]));
    sort($newVars);
    $newTable = [];
    
    foreach ($factor['table'] as $jsonKey => $prob) {
        $assignment = json_decode($jsonKey, true);
        
        // Remover la variable de la clave
        unset($assignment[$varToEliminate]);
        ksort($assignment);
        
        $newKey = json_encode($assignment);
        
        if (!isset($newTable[$newKey])) {
            $newTable[$newKey] = 0.0;
        }
        $newTable[$newKey] += $prob;
    }
    
    return ['vars' => $newVars, 'table' => $newTable];
}

function normalizeFactor($factor, $queryVar) {
    $dist = [];
    $sum = 0.0;
    
    foreach ($factor['table'] as $jsonKey => $prob) {
        $assignment = json_decode($jsonKey, true);
        if (isset($assignment[$queryVar])) {
            $val = $assignment[$queryVar];
            $dist[$val] = $prob;
            $sum += $prob;
        }
    }
    
    if ($sum > 0) {
        foreach ($dist as $k => $v) {
            $dist[$k] = $v / $sum;
        }
    }
    
    return $dist;
}

function determineEliminationOrder($network, $vars) {
    // Heurística simple: eliminar primero las variables con menos conexiones (Min-Degree)
    // Opcional: Implementar Min-Fill
    // Por ahora devolvemos el orden tal cual o aleatorio, ya que para redes pequeñas no es crítico
    return array_values($vars);
}

function generateCombinations($network, $vars) {
    if (empty($vars)) return [[]];
    
    $firstVar = array_shift($vars);
    $domain = $network->getNodeValues($firstVar);
    if (empty($domain)) $domain = ['True', 'False'];
    
    $restCombinations = generateCombinations($network, $vars);
    $result = [];
    
    foreach ($domain as $val) {
        foreach ($restCombinations as $combo) {
            $result[] = array_merge([$firstVar => $val], $combo);
        }
    }
    
    return $result;
}
?>