<?php
/**
 * Algoritmo de Enumeración Exacta para Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Modelos Probabilísticos
 * 
 * Este algoritmo calcula probabilidades exactas mediante enumeración
 * completa de todas las asignaciones posibles de variables.
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
    
    // Obtener valores posibles de la variable de consulta
    $queryValues = $network->getNodeValues($queryVariable);
    
    if (empty($queryValues)) {
        throw new Exception("No se encontraron valores posibles para '$queryVariable'");
    }
    
    // Iniciar medición de tiempo
    $startTime = microtime(true);
    
    // Calcular probabilidades para cada valor
    $probabilities = [];
    $steps = [];
    
    foreach ($queryValues as $value) {
        // Agregar la variable de consulta a la evidencia
        $queryEvidence = array_merge($evidence, [$queryVariable => $value]);
        
        // Calcular probabilidad mediante enumeración
        $prob = enumerateAll($network, $queryEvidence, $steps);
        $probabilities[$value] = $prob;
        
        $steps[] = [
            'type' => 'calculation',
            'query_value' => $value,
            'unnormalized_probability' => $prob
        ];
    }
    
    // Normalizar probabilidades
    $sum = array_sum($probabilities);
    
    if ($sum > 0) {
        foreach ($probabilities as $key => $value) {
            $probabilities[$key] = $value / $sum;
        }
        
        $steps[] = [
            'type' => 'normalization',
            'sum' => $sum,
            'message' => 'Dividiendo cada probabilidad entre la suma total para normalizar'
        ];
    } else {
        throw new Exception('La suma de probabilidades es cero. Verifique la evidencia y la red.');
    }
    
    // Tiempo de ejecución
    $executionTime = microtime(true) - $startTime;
    
    // Responder
    echo json_encode([
        'success' => true,
        'algorithm' => 'enumeration',
        'query' => $queryVariable,
        'evidence' => $evidence,
        'probabilities' => $probabilities,
        'execution_time' => round($executionTime, 4),
        'steps' => $steps,
        'metadata' => [
            'node_count' => $network->getNodeCount(),
            'edge_count' => $network->getEdgeCount(),
            'query_values' => $queryValues
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
 * Enumeración recursiva sobre todas las variables
 * 
 * @param BayesianNetwork $network Red bayesiana
 * @param array $evidence Evidencia actual (incluyendo query)
 * @param array &$steps Pasos del algoritmo (por referencia)
 * @param array|null $vars Variables restantes a enumerar
 * @return float Probabilidad sin normalizar
 */
function enumerateAll($network, $evidence, &$steps = [], $vars = null) {
    // Si vars es null, obtener todas las variables no observadas
    if ($vars === null) {
        $vars = [];
        foreach ($network->getNodes() as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            if (!isset($evidence[$nodeId])) {
                $vars[] = $nodeId;
            }
        }
        
        $steps[] = [
            'type' => 'initialization',
            'hidden_variables' => $vars,
            'evidence' => $evidence,
            'message' => 'Identificando variables ocultas (no observadas)'
        ];
    }
    
    // Caso base: no hay más variables por enumerar
    if (empty($vars)) {
        $prob = probabilityOfEvidence($network, $evidence);
        
        $steps[] = [
            'type' => 'base_case',
            'assignment' => $evidence,
            'probability' => $prob,
            'message' => 'Calculando probabilidad de esta asignación completa'
        ];
        
        return $prob;
    }
    
    // Tomar primera variable
    $var = array_shift($vars);
    $values = $network->getNodeValues($var);
    
    $steps[] = [
        'type' => 'recursive_step',
        'current_variable' => $var,
        'possible_values' => $values,
        'remaining_variables' => $vars,
        'message' => "Enumerando sobre la variable '$var'"
    ];
    
    // Sumar sobre todos los valores posibles de la variable
    $sum = 0.0;
    
    foreach ($values as $value) {
        $newEvidence = array_merge($evidence, [$var => $value]);
        $prob = enumerateAll($network, $newEvidence, $steps, $vars);
        $sum += $prob;
    }
    
    return $sum;
}

/**
 * Calcular probabilidad de la evidencia usando la regla de la cadena
 * P(X1, X2, ..., Xn) = ∏ P(Xi | Parents(Xi))
 * 
 * @param BayesianNetwork $network Red bayesiana
 * @param array $evidence Asignación completa de variables
 * @return float Probabilidad conjunta
 */
function probabilityOfEvidence($network, $evidence) {
    $prob = 1.0;
    $factors = [];
    
    // Calcular producto de probabilidades condicionales
    foreach ($evidence as $node => $value) {
        $parents = $network->getParents($node);
        $parentValues = [];
        
        // Extraer valores de los padres
        foreach ($parents as $parent) {
            if (isset($evidence[$parent])) {
                $parentValues[$parent] = $evidence[$parent];
            } else {
                // Si falta un padre, hay un error
                error_log("Advertencia: Padre '$parent' no encontrado en evidencia para nodo '$node'");
                return 0.0;
            }
        }
        
        // Obtener probabilidad condicional
        $condProb = $network->getConditionalProbability($node, $value, $parentValues);
        
        $factors[] = [
            'node' => $node,
            'value' => $value,
            'parents' => $parentValues,
            'probability' => $condProb
        ];
        
        $prob *= $condProb;
        
        // Si la probabilidad es 0, no tiene sentido continuar
        if ($prob == 0) {
            break;
        }
    }
    
    return $prob;
}

/**
 * Calcular probabilidad marginal (sin evidencia)
 * P(Query = value)
 * 
 * @param BayesianNetwork $network
 * @param string $queryVariable
 * @param mixed $queryValue
 * @return float
 */
function calculateMarginalProbability($network, $queryVariable, $queryValue) {
    $evidence = [$queryVariable => $queryValue];
    $steps = [];
    
    $prob = enumerateAll($network, $evidence, $steps);
    
    return $prob;
}

/**
 * Calcular probabilidad posterior con evidencia
 * P(Query = value | Evidence)
 * 
 * @param BayesianNetwork $network
 * @param string $queryVariable
 * @param mixed $queryValue
 * @param array $evidence
 * @return float
 */
function calculatePosteriorProbability($network, $queryVariable, $queryValue, $evidence) {
    // P(Q=q, E=e)
    $jointEvidence = array_merge($evidence, [$queryVariable => $queryValue]);
    $steps = [];
    $jointProb = enumerateAll($network, $jointEvidence, $steps);
    
    // P(E=e) - calcular sumando sobre todos los valores de Q
    $evidenceProb = 0.0;
    $queryValues = $network->getNodeValues($queryVariable);
    
    foreach ($queryValues as $val) {
        $tempEvidence = array_merge($evidence, [$queryVariable => $val]);
        $evidenceProb += enumerateAll($network, $tempEvidence, $steps);
    }
    
    // P(Q=q | E=e) = P(Q=q, E=e) / P(E=e)
    if ($evidenceProb > 0) {
        return $jointProb / $evidenceProb;
    }
    
    return 0.0;
}

/**
 * Calcular distribución completa para una variable
 * 
 * @param BayesianNetwork $network
 * @param string $queryVariable
 * @param array $evidence
 * @return array Distribución de probabilidad
 */
function calculateDistribution($network, $queryVariable, $evidence = []) {
    $distribution = [];
    $queryValues = $network->getNodeValues($queryVariable);
    
    foreach ($queryValues as $value) {
        if (empty($evidence)) {
            $distribution[$value] = calculateMarginalProbability($network, $queryVariable, $value);
        } else {
            $distribution[$value] = calculatePosteriorProbability($network, $queryVariable, $value, $evidence);
        }
    }
    
    // Normalizar
    $sum = array_sum($distribution);
    if ($sum > 0) {
        foreach ($distribution as $key => $value) {
            $distribution[$key] = $value / $sum;
        }
    }
    
    return $distribution;
}

/**
 * Validar evidencia contra la red
 * 
 * @param BayesianNetwork $network
 * @param array $evidence
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateEvidence($network, $evidence) {
    $errors = [];
    
    foreach ($evidence as $node => $value) {
        // Verificar que el nodo existe
        if (!$network->getNode($node)) {
            $errors[] = "Nodo '$node' no existe en la red";
            continue;
        }
        
        // Verificar que el valor es válido
        $validValues = $network->getNodeValues($node);
        if (!in_array($value, $validValues)) {
            $errors[] = "Valor '$value' no es válido para nodo '$node'. Valores válidos: " . implode(', ', $validValues);
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
?>
