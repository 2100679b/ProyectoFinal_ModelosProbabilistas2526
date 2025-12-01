<?php
/**
 * Algoritmo de Eliminación de Variables para Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Modelos Probabilísticos
 * 
 * Este algoritmo optimiza la inferencia mediante eliminación estratégica
 * de variables, reduciendo la complejidad computacional comparado con
 * enumeración exacta.
 */

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../network.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método HTTP no permitido. Use POST.');
    }
    
    // Recibir datos
    $networkData = json_decode($_POST['network'] ?? '{}', true);
    $queryVariable = $_POST['query'] ?? '';
    $evidence = json_decode($_POST['evidence'] ?? '{}', true);
    $eliminationOrder = json_decode($_POST['elimination_order'] ?? 'null', true);
    
    // Validar entrada
    if (empty($networkData)) {
        throw new Exception('No se proporcionó red bayesiana');
    }
    
    if (empty($queryVariable)) {
        throw new Exception('No se especificó variable de consulta');
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
        'algorithm' => 'variable_elimination',
        'query' => $queryVariable,
        'evidence' => $evidence,
        'probabilities' => $result['probabilities'],
        'execution_time' => round($executionTime, 4),
        'steps' => $result['steps'],
        'elimination_order' => $result['elimination_order'],
        'metadata' => [
            'node_count' => $network->getNodeCount(),
            'edge_count' => $network->getEdgeCount(),
            'factors_created' => $result['factors_created'],
            'max_factor_size' => $result['max_factor_size']
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'line' => $e->getLine(),
        'file' => basename($e->getFile())
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * Algoritmo de Eliminación de Variables
 * 
 * @param BayesianNetwork $network Red bayesiana
 * @param string $queryVar Variable de consulta
 * @param array $evidence Evidencia observada
 * @param array|null $eliminationOrder Orden de eliminación (opcional)
 * @return array Resultado con probabilidades y pasos
 */
function variableElimination($network, $queryVar, $evidence, $eliminationOrder = null) {
    $steps = [];
    $factorsCreated = 0;
    $maxFactorSize = 0;
    
    // Obtener todas las variables
    $allVars = [];
    foreach ($network->getNodes() as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        $allVars[] = $nodeId;
    }
    
    $steps[] = [
        'type' => 'initialization',
        'all_variables' => $allVars,
        'query' => $queryVar,
        'evidence' => $evidence,
        'message' => 'Iniciando algoritmo de eliminación de variables'
    ];
    
    // Variables a eliminar (todas excepto query y evidencia)
    $varsToEliminate = array_diff($allVars, [$queryVar], array_keys($evidence));
    
    $steps[] = [
        'type' => 'variable_identification',
        'variables_to_eliminate' => array_values($varsToEliminate),
        'message' => 'Identificando variables a eliminar'
    ];
    
    // Crear factores iniciales basados en CPTs
    $factors = createInitialFactors($network, $evidence, $steps);
    $factorsCreated = count($factors);
    
    $steps[] = [
        'type' => 'initial_factors',
        'factor_count' => count($factors),
        'message' => 'Factores iniciales creados a partir de las CPTs'
    ];
    
    // Determinar orden de eliminación
    if ($eliminationOrder === null) {
        $eliminationOrder = determineEliminationOrder($network, $varsToEliminate);
        
        $steps[] = [
            'type' => 'elimination_order',
            'order' => $eliminationOrder,
            'heuristic' => 'min-neighbors',
            'message' => 'Orden de eliminación determinado usando heurística min-neighbors'
        ];
    } else {
        $steps[] = [
            'type' => 'elimination_order',
            'order' => $eliminationOrder,
            'heuristic' => 'user-provided',
            'message' => 'Usando orden de eliminación proporcionado por el usuario'
        ];
    }
    
    // Eliminar variables una por una
    foreach ($eliminationOrder as $var) {
        $steps[] = [
            'type' => 'elimination_start',
            'variable' => $var,
            'factors_before' => count($factors),
            'message' => "Eliminando variable: $var"
        ];
        
        $factors = eliminateVariable($var, $factors, $steps);
        
        // Actualizar tamaño máximo de factor
        foreach ($factors as $factor) {
            $size = count($factor['vars']);
            if ($size > $maxFactorSize) {
                $maxFactorSize = $size;
            }
        }
        
        $steps[] = [
            'type' => 'elimination_complete',
            'variable' => $var,
            'factors_after' => count($factors),
            'message' => "Variable $var eliminada"
        ];
    }
    
    // Multiplicar factores restantes
    $steps[] = [
        'type' => 'final_product',
        'remaining_factors' => count($factors),
        'message' => 'Multiplicando factores restantes'
    ];
    
    $resultFactor = multiplyAllFactors($factors);
    
    // Normalizar y extraer probabilidades para queryVar
    $probabilities = normalizeFactor($resultFactor, $queryVar);
    
    $steps[] = [
        'type' => 'normalization',
        'probabilities' => $probabilities,
        'message' => 'Probabilidades normalizadas'
    ];
    
    return [
        'probabilities' => $probabilities,
        'steps' => $steps,
        'elimination_order' => $eliminationOrder,
        'factors_created' => $factorsCreated,
        'max_factor_size' => $maxFactorSize
    ];
}

/**
 * Crear factores iniciales a partir de las CPTs
 * 
 * @param BayesianNetwork $network
 * @param array $evidence
 * @param array &$steps
 * @return array
 */
function createInitialFactors($network, $evidence, &$steps) {
    $factors = [];
    
    foreach ($network->getNodes() as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        $cpt = $network->getCPT($nodeId);
        $parents = $network->getParents($nodeId);
        
        // Crear factor para este nodo
        $factor = [
            'vars' => array_merge([$nodeId], $parents),
            'table' => [],
            'source' => $nodeId
        ];
        
        // Construir tabla del factor
        $nodeValues = $network->getNodeValues($nodeId);
        
        if (empty($parents)) {
            // Sin padres: probabilidad marginal
            foreach ($nodeValues as $value) {
                $assignment = [$nodeId => $value];
                
                // Aplicar evidencia si existe
                if (isset($evidence[$nodeId])) {
                    if ($evidence[$nodeId] === $value) {
                        $factor['table'][json_encode($assignment)] = $cpt[$value] ?? 0;
                    }
                    // Si no coincide con evidencia, no incluir en factor
                } else {
                    $factor['table'][json_encode($assignment)] = $cpt[$value] ?? 0;
                }
            }
        } else {
            // Con padres: probabilidad condicional
            $parentCombinations = generateCombinations($network, $parents);
            
            foreach ($parentCombinations as $parentAssignment) {
                // Verificar evidencia en padres
                $skipParents = false;
                foreach ($evidence as $eVar => $eVal) {
                    if (isset($parentAssignment[$eVar]) && $parentAssignment[$eVar] !== $eVal) {
                        $skipParents = true;
                        break;
                    }
                }
                
                if ($skipParents) continue;
                
                foreach ($nodeValues as $value) {
                    $assignment = array_merge($parentAssignment, [$nodeId => $value]);
                    
                    // Aplicar evidencia en el nodo
                    if (isset($evidence[$nodeId]) && $evidence[$nodeId] !== $value) {
                        continue;
                    }
                    
                    $prob = $network->getConditionalProbability($nodeId, $value, $parentAssignment);
                    $factor['table'][json_encode($assignment)] = $prob;
                }
            }
        }
        
        // Solo agregar factor si tiene entradas
        if (!empty($factor['table'])) {
            $factors[] = $factor;
        }
    }
    
    return $factors;
}

/**
 * Eliminar una variable sumando sobre sus valores
 * 
 * @param string $var Variable a eliminar
 * @param array $factors Lista de factores
 * @param array &$steps
 * @return array Factores actualizados
 */
function eliminateVariable($var, $factors, &$steps) {
    // Encontrar factores que contienen la variable
    $relevantFactors = [];
    $otherFactors = [];
    
    foreach ($factors as $factor) {
        if (in_array($var, $factor['vars'])) {
            $relevantFactors[] = $factor;
        } else {
            $otherFactors[] = $factor;
        }
    }
    
    // Si no hay factores relevantes, retornar sin cambios
    if (empty($relevantFactors)) {
        $steps[] = [
            'type' => 'skip_variable',
            'variable' => $var,
            'message' => "Variable $var no aparece en ningún factor"
        ];
        return $factors;
    }
    
    $steps[] = [
        'type' => 'multiply_factors',
        'variable' => $var,
        'relevant_factors' => count($relevantFactors),
        'message' => "Multiplicando " . count($relevantFactors) . " factores que contienen $var"
    ];
    
    // Multiplicar factores relevantes
    $product = multiplyFactors($relevantFactors);
    
    $steps[] = [
        'type' => 'sum_out',
        'variable' => $var,
        'product_size' => count($product['vars']),
        'message' => "Sumando sobre $var para eliminarlo"
    ];
    
    // Sumar sobre la variable a eliminar
    $newFactor = sumOut($product, $var);
    
    // Agregar nuevo factor a los otros factores
    if (!empty($newFactor['table'])) {
        $otherFactors[] = $newFactor;
    }
    
    return $otherFactors;
}

/**
 * Multiplicar múltiples factores
 * 
 * @param array $factors
 * @return array Factor resultante
 */
function multiplyFactors($factors) {
    if (empty($factors)) {
        return ['vars' => [], 'table' => []];
    }
    
    if (count($factors) === 1) {
        return $factors[0];
    }
    
    $result = $factors[0];
    for ($i = 1; $i < count($factors); $i++) {
        $result = multiplyTwoFactors($result, $factors[$i]);
    }
    
    return $result;
}

/**
 * Multiplicar dos factores
 * 
 * @param array $f1 Factor 1
 * @param array $f2 Factor 2
 * @return array Factor resultante
 */
function multiplyTwoFactors($f1, $f2) {
    // Variables del factor resultante (unión de variables)
    $resultVars = array_unique(array_merge($f1['vars'], $f2['vars']));
    
    $resultTable = [];
    
    // Iterar sobre todas las combinaciones compatibles
    foreach ($f1['table'] as $key1 => $val1) {
        $assign1 = json_decode($key1, true);
        
        foreach ($f2['table'] as $key2 => $val2) {
            $assign2 = json_decode($key2, true);
            
            // Verificar compatibilidad (variables en común deben tener mismo valor)
            $compatible = true;
            foreach ($assign1 as $var => $value) {
                if (isset($assign2[$var]) && $assign2[$var] !== $value) {
                    $compatible = false;
                    break;
                }
            }
            
            if ($compatible) {
                $newAssign = array_merge($assign1, $assign2);
                $newKey = json_encode($newAssign);
                $resultTable[$newKey] = $val1 * $val2;
            }
        }
    }
    
    return [
        'vars' => array_values($resultVars),
        'table' => $resultTable
    ];
}

/**
 * Sumar sobre una variable (marginalización)
 * 
 * @param array $factor Factor
 * @param string $var Variable a sumar
 * @return array Factor resultante
 */
function sumOut($factor, $var) {
    $newVars = array_diff($factor['vars'], [$var]);
    $newTable = [];
    
    foreach ($factor['table'] as $key => $value) {
        $assignment = json_decode($key, true);
        
        // Crear nueva asignación sin la variable
        $newAssignment = $assignment;
        unset($newAssignment[$var]);
        
        $newKey = json_encode($newAssignment);
        
        if (!isset($newTable[$newKey])) {
            $newTable[$newKey] = 0.0;
        }
        
        $newTable[$newKey] += $value;
    }
    
    return [
        'vars' => array_values($newVars),
        'table' => $newTable
    ];
}

/**
 * Multiplicar todos los factores restantes
 * 
 * @param array $factors
 * @return array
 */
function multiplyAllFactors($factors) {
    return multiplyFactors($factors);
}

/**
 * Normalizar factor y extraer probabilidades
 * 
 * @param array $factor
 * @param string $queryVar
 * @return array
 */
function normalizeFactor($factor, $queryVar) {
    $probabilities = [];
    $sum = 0.0;
    
    foreach ($factor['table'] as $key => $value) {
        $assignment = json_decode($key, true);
        if (isset($assignment[$queryVar])) {
            $probabilities[$assignment[$queryVar]] = $value;
            $sum += $value;
        }
    }
    
    // Normalizar
    if ($sum > 0) {
        foreach ($probabilities as $key => $value) {
            $probabilities[$key] = $value / $sum;
        }
    }
    
    return $probabilities;
}

/**
 * Generar todas las combinaciones de valores para variables
 * 
 * @param BayesianNetwork $network
 * @param array $vars
 * @return array
 */
function generateCombinations($network, $vars) {
    if (empty($vars)) {
        return [[]];
    }
    
    $var = array_shift($vars);
    $values = $network->getNodeValues($var);
    $subCombinations = generateCombinations($network, $vars);
    
    $combinations = [];
    foreach ($values as $value) {
        foreach ($subCombinations as $subCombo) {
            $combinations[] = array_merge([$var => $value], $subCombo);
        }
    }
    
    return $combinations;
}

/**
 * Determinar orden de eliminación usando heurística min-neighbors
 * 
 * @param BayesianNetwork $network
 * @param array $varsToEliminate
 * @return array
 */
function determineEliminationOrder($network, $varsToEliminate) {
    $order = [];
    $remaining = array_values($varsToEliminate);
    
    while (!empty($remaining)) {
        // Elegir variable con menos vecinos (padres + hijos)
        $minNeighbors = PHP_INT_MAX;
        $selectedVar = null;
        
        foreach ($remaining as $var) {
            $parents = $network->getParents($var);
            $children = $network->getChildren($var);
            $neighborCount = count($parents) + count($children);
            
            if ($neighborCount < $minNeighbors) {
                $minNeighbors = $neighborCount;
                $selectedVar = $var;
            }
        }
        
        if ($selectedVar !== null) {
            $order[] = $selectedVar;
            $remaining = array_diff($remaining, [$selectedVar]);
            $remaining = array_values($remaining);
        } else {
            // Fallback: tomar el primero
            $order[] = $remaining[0];
            array_shift($remaining);
        }
    }
    
    return $order;
}

/**
 * Calcular orden de eliminación óptimo usando heurística min-fill
 * 
 * @param BayesianNetwork $network
 * @param array $varsToEliminate
 * @return array
 */
function minFillEliminationOrder($network, $varsToEliminate) {
    // Heurística más sofisticada: minimiza el número de aristas añadidas
    // Para simplificar, usamos min-neighbors por ahora
    return determineEliminationOrder($network, $varsToEliminate);
}
?>
