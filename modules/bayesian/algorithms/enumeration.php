<?php
/**
 * Algoritmo de Enumeración Exacta para Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * * Corrige problemas de rutas y lectura de datos.
 */

// 1. Manejo robusto de rutas (Intenta subir niveles hasta encontrar config)
$configPath = __DIR__ . '/../../config.php';
if (!file_exists($configPath)) {
    // Intento alternativo si la estructura es diferente
    $configPath = __DIR__ . '/../../../config.php';
}

if (file_exists($configPath)) {
    require_once $configPath;
} else {
    // Si no encuentra config, definimos lo básico para no romper
    if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__, 2));
}

// 2. Incluir la clase de la Red (Asumiendo que está en la misma carpeta o un nivel arriba)
$networkClassPath = __DIR__ . '/network.php';
if (!file_exists($networkClassPath)) {
    $networkClassPath = __DIR__ . '/BayesianNetwork.php'; // Nombre alternativo común
}
require_once $networkClassPath;

// Configurar respuesta JSON
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
    
    // Si se envía como Form Data ($.ajax clásico), sobreescribe si existe
    if (!empty($_POST)) {
        $input = array_merge($input, $_POST);
    }

    // Extraer variables
    $networkData = isset($input['network']) ? (is_string($input['network']) ? json_decode($input['network'], true) : $input['network']) : [];
    $queryVariable = $input['query'] ?? '';
    $evidence = isset($input['evidence']) ? (is_string($input['evidence']) ? json_decode($input['evidence'], true) : $input['evidence']) : [];

    // --- VALIDACIONES ---
    if (empty($networkData)) {
        throw new Exception('No se recibieron datos de la red (Network Data empty).');
    }
    
    if (empty($queryVariable)) {
        throw new Exception('No se especificó la variable de consulta (Query Variable).');
    }

    // --- INICIALIZACIÓN ---
    $network = new BayesianNetwork($networkData);
    
    // Validar integridad de la red
    $validation = $network->validate();
    if (!$validation['valid']) {
        throw new Exception('La red es inválida: ' . implode(', ', $validation['errors']));
    }

    // Validar existencia de la variable Query
    if (!$network->getNode($queryVariable)) {
        throw new Exception("La variable '$queryVariable' no existe en la red.");
    }

    // --- ALGORITMO DE ENUMERACIÓN ---
    $startTime = microtime(true);
    $steps = []; // Para mostrar el paso a paso en el frontend
    $probabilities = [];
    
    // 1. Obtener dominio de la variable de consulta (True, False, etc.)
    $domain = $network->getNodeValues($queryVariable);
    if (empty($domain)) {
        // Fallback si no hay dominio explícito
        $domain = ['True', 'False']; 
    }

    // 2. Iterar sobre cada valor posible de la variable de consulta
    // P(Q | E) = alpha * P(Q, E) = alpha * sum_h(P(Q, E, h))
    foreach ($domain as $val) {
        // Extendemos la evidencia: Evidencia observada + Hipótesis actual de Q
        $extendedEvidence = array_merge($evidence, [$queryVariable => $val]);
        
        // Identificar variables ocultas (Hidden) = Todas - (Query + Evidence)
        $hiddenVars = [];
        foreach ($network->getNodes() as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            if (!isset($extendedEvidence[$nodeId])) {
                $hiddenVars[] = $nodeId;
            }
        }

        // Llamada recursiva para sumarizar variables ocultas
        $prob = enumerateAll($network, $hiddenVars, $extendedEvidence);
        $probabilities[$val] = $prob;
        
        $steps[] = "Calculado P($queryVariable = $val, E) = " . number_format($prob, 6);
    }

    // 3. Normalización (alpha)
    $sum = array_sum($probabilities);
    $normalized = [];
    
    if ($sum > 0) {
        foreach ($probabilities as $val => $p) {
            $normalized[$val] = $p / $sum;
        }
    } else {
        throw new Exception("Probabilidad total 0. Revisa si la evidencia es contradictoria.");
    }

    $executionTime = microtime(true) - $startTime;

    // --- RESPUESTA ---
    echo json_encode([
        'success' => true,
        'algorithm' => 'Enumeración Exacta',
        'query' => $queryVariable,
        'results' => $normalized, // Resultados finales normalizados
        'raw_probabilities' => $probabilities, // Antes de normalizar
        'steps' => $steps,
        'time' => round($executionTime, 5) . 's'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// ==========================================
// FUNCIONES AUXILIARES
// ==========================================

/**
 * Función Recursiva Enumerate-All
 * Suma sobre todas las combinaciones de variables ocultas
 */
function enumerateAll($network, $hiddenVars, $evidence) {
    // CASO BASE: Si no hay variables ocultas, calculamos la probabilidad atómica
    if (empty($hiddenVars)) {
        // En este punto, $evidence contiene una asignación completa para TODAS las variables
        return calculateAtomicProbability($network, $evidence);
    }

    // PASO RECURSIVO
    // Tomamos la primera variable oculta (Y)
    $Y = array_shift($hiddenVars); // Extrae y remueve el primer elemento
    
    $domain = $network->getNodeValues($Y);
    if (empty($domain)) $domain = ['True', 'False']; // Fallback
    
    $sum = 0.0;
    
    // Sumamos sobre todos los valores posibles de Y
    foreach ($domain as $yVal) {
        // Extendemos la evidencia con Y = yVal
        $newEvidence = array_merge($evidence, [$Y => $yVal]);
        
        // Llamada recursiva con el resto de variables ocultas
        $sum += enumerateAll($network, $hiddenVars, $newEvidence);
    }
    
    return $sum;
}

/**
 * Calcula la probabilidad de una asignación completa (Atomic Event)
 * P(x1, x2, ... xn) = Product( P(xi | Parents(xi)) )
 */
function calculateAtomicProbability($network, $fullAssignment) {
    $prob = 1.0;
    
    // Iteramos sobre CADA nodo de la red
    foreach ($network->getNodes() as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        
        // Valor actual de este nodo en la asignación
        $val = $fullAssignment[$nodeId];
        
        // Valores de sus padres en la asignación
        $parents = $network->getParents($nodeId);
        $parentValues = [];
        foreach ($parents as $p) {
            $parentValues[$p] = $fullAssignment[$p];
        }
        
        // Obtener probabilidad condicional de la CPT
        // Nota: Asegúrate que tu clase BayesianNetwork tenga getConditionalProbability
        $p_cond = $network->getConditionalProbability($nodeId, $val, $parentValues);
        
        $prob *= $p_cond;
    }
    
    return $prob;
}
?>