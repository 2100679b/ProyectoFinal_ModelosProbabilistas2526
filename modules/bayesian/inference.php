<?php
/**
 * Algoritmo de Inferencia para Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Implementación de Enumeración Exacta con Memoización
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/network.php';

header('Content-Type: application/json; charset=utf-8');

/**
 * Clase para realizar inferencia bayesiana
 */
class BayesianInference {
    private $network;
    private $cache = [];
    private $debug = false;
    
    public function __construct(BayesianNetwork $network) {
        $this->network = $network;
    }
    
    /**
     * Habilitar modo debug
     * @param bool $enabled
     */
    public function setDebug($enabled) {
        $this->debug = $enabled;
    }
    
    /**
     * Inferencia Exacta por Enumeración con Memoización
     * Calcula P(Query | Evidence)
     * 
     * @param string $queryVar Variable de consulta
     * @param array $evidence Array asociativo de variables observadas ['variable' => 'valor']
     * @return array ['true' => float, 'false' => float, 'metadata' => array]
     */
    public function exactInference($queryVar, $evidence = []) {
        // Limpiar cache
        $this->cache = [];
        
        // Validaciones
        if (!$this->network->getNode($queryVar)) {
            throw new Exception("Variable de consulta '$queryVar' no existe en la red");
        }
        
        // Verificar que evidencia no incluye la query
        if (isset($evidence[$queryVar])) {
            unset($evidence[$queryVar]);
        }
        
        // Obtener todas las variables
        $allVars = array_map(function($node) {
            return is_array($node) ? $node['id'] : $node;
        }, $this->network->getNodes());
        
        // Usar orden topológico para optimización
        $orderedVars = $this->network->getTopologicalOrder();
        
        if ($this->debug) {
            error_log("=== INFERENCIA EXACTA ===");
            error_log("Query: $queryVar");
            error_log("Evidence: " . json_encode($evidence));
            error_log("Variables ordenadas: " . implode(', ', $orderedVars));
        }
        
        // Calcular P(Query=True, Evidence)
        $evidenceTrue = array_merge($evidence, [$queryVar => 'True']);
        $probTrue = $this->enumerateAll($orderedVars, $evidenceTrue);
        
        // Calcular P(Query=False, Evidence)
        $evidenceFalse = array_merge($evidence, [$queryVar => 'False']);
        $probFalse = $this->enumerateAll($orderedVars, $evidenceFalse);
        
        // Normalizar
        $total = $probTrue + $probFalse;
        
        if ($total == 0) {
            // Secuencia imposible
            return [
                'true' => 0.0,
                'false' => 0.0,
                'metadata' => [
                    'valid' => false,
                    'message' => 'La combinación de evidencia es imposible en este modelo',
                    'cache_hits' => count($this->cache)
                ]
            ];
        }
        
        return [
            'true' => $probTrue / $total,
            'false' => $probFalse / $total,
            'metadata' => [
                'valid' => true,
                'unnormalized_true' => $probTrue,
                'unnormalized_false' => $probFalse,
                'normalization_constant' => $total,
                'cache_hits' => count($this->cache),
                'algorithm' => 'exact_enumeration'
            ]
        ];
    }
    
    /**
     * Enumeración recursiva sobre todas las variables
     * Implementa memoización para evitar recalcular subproblemas
     * 
     * @param array $vars Variables restantes por procesar
     * @param array $evidence Valores asignados
     * @return float Probabilidad conjunta
     */
    private function enumerateAll($vars, $evidence) {
        // Caso base: no quedan variables
        if (empty($vars)) {
            return 1.0;
        }
        
        // Generar clave de cache
        $cacheKey = $this->getCacheKey($vars, $evidence);
        
        // Verificar cache
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        // Tomar primera variable
        $Y = $vars[0];
        $rest = array_slice($vars, 1);
        
        $result = 0.0;
        
        if (isset($evidence[$Y])) {
            // Y está fijada en evidencia
            $value = $evidence[$Y];
            $isTrue = ($value === 'True' || $value === true || $value === 1);
            
            $prob = $this->getProbability($Y, $isTrue, $evidence);
            $result = $prob * $this->enumerateAll($rest, $evidence);
            
            if ($this->debug) {
                error_log("Variable fijada: $Y=$value, P=$prob");
            }
        } else {
            // Y es variable oculta: marginalizar sumando sobre True y False
            
            // Caso Y = True
            $evidenceYTrue = array_merge($evidence, [$Y => 'True']);
            $probYTrue = $this->getProbability($Y, true, $evidenceYTrue);
            $sumTrue = $probYTrue * $this->enumerateAll($rest, $evidenceYTrue);
            
            // Caso Y = False
            $evidenceYFalse = array_merge($evidence, [$Y => 'False']);
            $probYFalse = $this->getProbability($Y, false, $evidenceYFalse);
            $sumFalse = $probYFalse * $this->enumerateAll($rest, $evidenceYFalse);
            
            $result = $sumTrue + $sumFalse;
            
            if ($this->debug) {
                error_log("Variable oculta: $Y, P(T)=$probYTrue, P(F)=$probYFalse, Total=$result");
            }
        }
        
        // Guardar en cache
        $this->cache[$cacheKey] = $result;
        
        return $result;
    }
    
    /**
     * Obtener probabilidad condicional P(node=value | parents)
     * 
     * @param string $nodeId ID del nodo
     * @param bool $isTrue Si el nodo es True o False
     * @param array $evidence Valores de todas las variables (incluyendo padres)
     * @return float Probabilidad
     */
    private function getProbability($nodeId, $isTrue, $evidence) {
        $parents = $this->network->getParents($nodeId);
        $cpt = $this->network->getCPT($nodeId);
        
        if (!$cpt) {
            // Sin CPT: retornar probabilidad uniforme
            if ($this->debug) {
                error_log("WARNING: No CPT para $nodeId, usando 0.5");
            }
            return 0.5;
        }
        
        $pTrue = 0.5; // Default
        
        if (empty($parents)) {
            // Nodo sin padres (raíz): buscar probabilidad a priori
            if (isset($cpt['root'])) {
                $pTrue = (float)$cpt['root'];
            } elseif (isset($cpt['True'])) {
                $pTrue = (float)$cpt['True'];
            } else {
                // Tomar primer valor disponible
                $pTrue = (float)reset($cpt);
            }
        } else {
            // Nodo con padres: construir clave para CPT
            
            // Verificar que todos los padres tienen valores asignados
            $parentValues = [];
            $missingParents = [];
            
            foreach ($parents as $parent) {
                if (isset($evidence[$parent])) {
                    $parentValues[$parent] = $evidence[$parent];
                } else {
                    $missingParents[] = $parent;
                }
            }
            
            if (!empty($missingParents)) {
                // En enumeración exacta no debería pasar si el orden es correcto
                if ($this->debug) {
                    error_log("WARNING: Padres sin valor para $nodeId: " . implode(', ', $missingParents));
                }
                return 0.5;
            }
            
            // Intentar ambos formatos de clave
            
            // Formato 1: String "parent1=value1,parent2=value2"
            ksort($parentValues);
            $keyParts = [];
            foreach ($parentValues as $p => $v) {
                $keyParts[] = "$p=$v";
            }
            $keyString = implode(',', $keyParts);
            
            if (isset($cpt[$keyString])) {
                $pTrue = (float)$cpt[$keyString];
            } else {
                // Formato 2: JSON {"parent1":"value1","parent2":"value2"}
                $keyJSON = json_encode($parentValues, JSON_UNESCAPED_UNICODE);
                
                if (isset($cpt[$keyJSON])) {
                    $pTrue = (float)$cpt[$keyJSON];
                } else {
                    if ($this->debug) {
                        error_log("WARNING: No se encontró clave CPT para $nodeId");
                        error_log("  Intentado: $keyString");
                        error_log("  Intentado: $keyJSON");
                        error_log("  Disponibles: " . implode(', ', array_keys($cpt)));
                    }
                }
            }
        }
        
        return $isTrue ? $pTrue : (1.0 - $pTrue);
    }
    
    /**
     * Generar clave única para cache
     * @param array $vars Variables
     * @param array $evidence Evidencia
     * @return string
     */
    private function getCacheKey($vars, $evidence) {
        ksort($evidence);
        return md5(json_encode(['vars' => $vars, 'evidence' => $evidence]));
    }
    
    /**
     * Limpiar cache
     */
    public function clearCache() {
        $this->cache = [];
    }
    
    /**
     * Obtener estadísticas de cache
     * @return array
     */
    public function getCacheStats() {
        return [
            'size' => count($this->cache),
            'memory_usage' => memory_get_usage(true)
        ];
    }
}

// ========== API ENDPOINT ==========

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método HTTP no permitido. Use POST.');
    }
    
    // Leer datos JSON del cuerpo de la petición
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido: ' . json_last_error_msg());
    }
    
    // Validar datos requeridos
    if (!isset($data['network'])) {
        throw new Exception('Falta parámetro "network"');
    }
    
    if (!isset($data['query'])) {
        throw new Exception('Falta parámetro "query"');
    }
    
    $networkData = $data['network'];
    $queryVar = $data['query'];
    $evidence = isset($data['evidence']) ? $data['evidence'] : [];
    $debug = isset($data['debug']) ? (bool)$data['debug'] : false;
    
    // Crear red bayesiana
    $network = new BayesianNetwork($networkData);
    
    // Validar red
    $validation = $network->validate();
    if (!$validation['valid']) {
        throw new Exception('Red inválida: ' . implode(', ', $validation['errors']));
    }
    
    // Crear motor de inferencia
    $inference = new BayesianInference($network);
    $inference->setDebug($debug);
    
    // Medir tiempo de ejecución
    $startTime = microtime(true);
    
    // Ejecutar inferencia
    $result = $inference->exactInference($queryVar, $evidence);
    
    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000; // Convertir a ms
    
    // Agregar información adicional
    $result['metadata']['execution_time_ms'] = round($executionTime, 2);
    $result['metadata']['network_summary'] = $network->getSummary();
    
    // Responder exitosamente
    echo json_encode([
        'success' => true,
        'query' => $queryVar,
        'evidence' => $evidence,
        'result' => $result
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Manejar errores
    http_response_code(400);
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * ========== FUNCIONES AUXILIARES ==========
 */

/**
 * Validar formato de evidencia
 * @param array $evidence
 * @return bool
 */
function validateEvidence($evidence) {
    if (!is_array($evidence)) {
        return false;
    }
    
    foreach ($evidence as $var => $value) {
        if (!is_string($var)) {
            return false;
        }
        
        if (!in_array($value, ['True', 'False', true, false, 1, 0], true)) {
            return false;
        }
    }
    
    return true;
}

/**
 * Normalizar evidencia a formato estándar
 * @param array $evidence
 * @return array
 */
function normalizeEvidence($evidence) {
    $normalized = [];
    
    foreach ($evidence as $var => $value) {
        // Convertir todo a string 'True' o 'False'
        if ($value === true || $value === 1 || $value === 'True' || $value === 'true') {
            $normalized[$var] = 'True';
        } else {
            $normalized[$var] = 'False';
        }
    }
    
    return $normalized;
}
?>