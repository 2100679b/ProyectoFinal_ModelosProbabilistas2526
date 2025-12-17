<?php
/**
 * Algoritmo de Eliminación de Variables para Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
<<<<<<< HEAD
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
=======
 * Modelos Probabilísticos
 * 
 * Este algoritmo optimiza la inferencia mediante eliminación estratégica
 * de variables, reduciendo la complejidad computacional comparado con
 * enumeración exacta.
 */

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../network.php';
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036

header('Content-Type: application/json; charset=utf-8');

try {
<<<<<<< HEAD
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
=======
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
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
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
<<<<<<< HEAD
        'algorithm' => 'Eliminación de Variables',
        'query' => $queryVariable,
        'evidence' => $evidence,
        'results' => $result['probabilities'], // Estandarizado como 'results' para el frontend
        'probabilities' => $result['probabilities'],
        'execution_time' => round($executionTime, 5) . 's',
        'steps' => $result['steps'],
        'elimination_order' => $result['elimination_order'],
        'metadata' => [
=======
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
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
            'factors_created' => $result['factors_created'],
            'max_factor_size' => $result['max_factor_size']
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
<<<<<<< HEAD
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// ==========================================
// ALGORITMO DE ELIMINACIÓN DE VARIABLES
// ==========================================

=======
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
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
function variableElimination($network, $queryVar, $evidence, $eliminationOrder = null) {
    $steps = [];
    $factorsCreated = 0;
    $maxFactorSize = 0;
    
<<<<<<< HEAD
    // 1. Identificar variables
=======
    // Obtener todas las variables
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
    $allVars = [];
    foreach ($network->getNodes() as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        $allVars[] = $nodeId;
    }
    
<<<<<<< HEAD
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
=======
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
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
    
    return [
        'probabilities' => $probabilities,
        'steps' => $steps,
        'elimination_order' => $eliminationOrder,
        'factors_created' => $factorsCreated,
        'max_factor_size' => $maxFactorSize
    ];
}

<<<<<<< HEAD
// ==========================================
// FUNCIONES AUXILIARES (FACTORES)
// ==========================================

function createInitialFactors($network, $evidence) {
=======
/**
 * Crear factores iniciales a partir de las CPTs
 * 
 * @param BayesianNetwork $network
 * @param array $evidence
 * @param array &$steps
 * @return array
 */
function createInitialFactors($network, $evidence, &$steps) {
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
    $factors = [];
    
    foreach ($network->getNodes() as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
<<<<<<< HEAD
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
=======
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
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
        if (!empty($factor['table'])) {
            $factors[] = $factor;
        }
    }
    
    return $factors;
}

<<<<<<< HEAD
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
=======
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
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
    }
    
    return $result;
}

<<<<<<< HEAD
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
=======
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
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
        
        if (!isset($newTable[$newKey])) {
            $newTable[$newKey] = 0.0;
        }
<<<<<<< HEAD
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
=======
        
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
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
