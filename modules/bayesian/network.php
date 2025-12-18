<?php
/**
 * Clase BayesianNetwork - Manejo de Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * VERSIÓN MEJORADA: Compatible con formato JS + Validaciones robustas
 */

class BayesianNetwork {
    private $name = 'Red Bayesiana';
    private $description = '';
    private $nodes = [];
    private $edges = [];
    private $cpt = []; // Conditional Probability Tables
    private $parents = [];
    private $children = [];
    private $nodeValues = []; // Cache de valores posibles por nodo
    
    /**
     * Constructor
     * @param array|null $data Datos de la red en formato array
     */
    public function __construct($data = null) {
        if ($data) {
            $this->loadFromArray($data);
        }
    }
    
    /**
     * Cargar red desde array
     * @param array $data Estructura de la red
     */
    public function loadFromArray($data) {
        // Cargar metadatos
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
        
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }
        
        // Cargar nodos
        if (isset($data['nodes'])) {
            $this->nodes = $data['nodes'];
        }
        
        // Cargar aristas y construir relaciones
        if (isset($data['edges'])) {
            $this->edges = $data['edges'];
            $this->buildParentChildRelations();
        }
        
        // Cargar CPT
        if (isset($data['cpt'])) {
            $this->cpt = $data['cpt'];
            $this->buildNodeValuesCache();
        }
    }
    
    /**
     * Construir relaciones padre-hijo desde edges
     */
    private function buildParentChildRelations() {
        // Inicializar arrays vacíos para cada nodo
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            $this->parents[$nodeId] = [];
            $this->children[$nodeId] = [];
        }
        
        // Construir relaciones desde edges
        foreach ($this->edges as $edge) {
            $from = is_array($edge) ? $edge['from'] : $edge[0];
            $to = is_array($edge) ? $edge['to'] : $edge[1];
            
            if (!isset($this->parents[$to])) {
                $this->parents[$to] = [];
            }
            if (!isset($this->children[$from])) {
                $this->children[$from] = [];
            }
            
            // Evitar duplicados
            if (!in_array($from, $this->parents[$to])) {
                $this->parents[$to][] = $from;
            }
            if (!in_array($to, $this->children[$from])) {
                $this->children[$from][] = $to;
            }
        }
        
        // Ordenar alfabéticamente para consistencia
        foreach ($this->parents as &$p) {
            sort($p);
        }
        foreach ($this->children as &$c) {
            sort($c);
        }
    }
    
    /**
     * Construir caché de valores posibles para cada nodo
     */
    private function buildNodeValuesCache() {
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            $this->nodeValues[$nodeId] = $this->extractNodeValues($nodeId);
        }
    }
    
    /**
     * Extraer valores posibles de un nodo desde su CPT
     * @param string $node ID del nodo
     * @return array Valores posibles (típicamente ['True', 'False'])
     */
    private function extractNodeValues($node) {
        $cpt = $this->getCPT($node);
        
        if (!$cpt || empty($cpt)) {
            // Sin CPT: asumir binario por defecto
            return ['True', 'False'];
        }
        
        $firstKey = array_key_first($cpt);
        
        // Caso 1: Nodo sin padres - formato {"True": 0.3, "False": 0.7}
        if (isset($cpt['True']) || isset($cpt['False'])) {
            return ['True', 'False'];
        }
        
        // Caso 2: Nodo sin padres - formato {"root": 0.3}
        if (isset($cpt['root'])) {
            return ['True', 'False'];
        }
        
        // Caso 3: Nodo con padres - formato condicional
        // Buscar en primer registro los valores posibles
        $first = reset($cpt);
        
        if (is_numeric($first)) {
            // Es un valor directo, asumir binario
            return ['True', 'False'];
        }
        
        // Si es array, las claves son los valores
        if (is_array($first)) {
            return array_keys($first);
        }
        
        // Fallback
        return ['True', 'False'];
    }
    
    // ========== GETTERS BÁSICOS ==========
    
    /**
     * Obtener nombre de la red
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Obtener descripción de la red
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * Obtener todos los nodos
     * @return array
     */
    public function getNodes() {
        return $this->nodes;
    }
    
    /**
     * Obtener un nodo por ID
     * @param string $nodeId
     * @return array|null
     */
    public function getNode($nodeId) {
        foreach ($this->nodes as $node) {
            $id = is_array($node) ? $node['id'] : $node;
            if ($id === $nodeId) {
                return $node;
            }
        }
        return null;
    }
    
    /**
     * Obtener todas las aristas
     * @return array
     */
    public function getEdges() {
        return $this->edges;
    }
    
    /**
     * Obtener padres de un nodo
     * @param string $node ID del nodo
     * @return array
     */
    public function getParents($node) {
        return isset($this->parents[$node]) ? $this->parents[$node] : [];
    }
    
    /**
     * Obtener hijos de un nodo
     * @param string $node ID del nodo
     * @return array
     */
    public function getChildren($node) {
        return isset($this->children[$node]) ? $this->children[$node] : [];
    }
    
    /**
     * Verificar si un nodo tiene padres
     * @param string $node ID del nodo
     * @return bool
     */
    public function hasParents($node) {
        return !empty($this->getParents($node));
    }
    
    /**
     * Obtener CPT de un nodo
     * @param string $node ID del nodo
     * @return array|null
     */
    public function getCPT($node) {
        return isset($this->cpt[$node]) ? $this->cpt[$node] : null;
    }
    
    /**
     * Establecer CPT de un nodo
     * @param string $node ID del nodo
     * @param array $cpt Tabla de probabilidades
     */
    public function setCPT($node, $cpt) {
        $this->cpt[$node] = $cpt;
        // Actualizar caché de valores
        $this->nodeValues[$node] = $this->extractNodeValues($node);
    }
    
    // ========== PROBABILIDAD CONDICIONAL (MEJORADA) ==========
    
    /**
     * Obtener probabilidad condicional P(node=value | parents)
     * Compatible con AMBOS formatos de CPT:
     *   - String: "parent1=True,parent2=False"
     *   - JSON: {"parent1":"True","parent2":"False"}
     * 
     * @param string $node ID del nodo
     * @param mixed $value Valor del nodo ('True' o 'False')
     * @param array $parentValues Valores de los padres ['parent1' => 'True', ...]
     * @return float Probabilidad entre 0 y 1
     */
    public function getConditionalProbability($node, $value, $parentValues = []) {
        $cpt = $this->getCPT($node);
        
        if (!$cpt) {
            // Sin CPT: probabilidad uniforme
            return 0.5;
        }
        
        // Normalizar valor a string
        $value = $this->normalizeValue($value);
        
        // CASO 1: Nodo sin padres (raíz)
        if (empty($parentValues)) {
            // Formato 1: {"root": 0.3} -> probabilidad de True
            if (isset($cpt['root'])) {
                $pTrue = (float)$cpt['root'];
                return $value === 'True' ? $pTrue : (1.0 - $pTrue);
            }
            
            // Formato 2: {"True": 0.3, "False": 0.7}
            if (isset($cpt[$value])) {
                return (float)$cpt[$value];
            }
            
            // Formato 3: Primer valor es la probabilidad de True
            $firstValue = reset($cpt);
            if (is_numeric($firstValue)) {
                $pTrue = (float)$firstValue;
                return $value === 'True' ? $pTrue : (1.0 - $pTrue);
            }
            
            // Fallback
            return 0.5;
        }
        
        // CASO 2: Nodo con padres
        // Normalizar valores de padres
        $normalizedParents = [];
        foreach ($parentValues as $p => $v) {
            $normalizedParents[$p] = $this->normalizeValue($v);
        }
        
        // Ordenar alfabéticamente para consistencia
        ksort($normalizedParents);
        
        // Intentar FORMATO 1: String "parent1=True,parent2=False"
        $keyString = $this->buildCPTKeyString($normalizedParents);
        
        if (isset($cpt[$keyString])) {
            // Puede ser:
            // a) Probabilidad directa de True: 0.8
            // b) Array de probabilidades: {"True": 0.8, "False": 0.2}
            
            $entry = $cpt[$keyString];
            
            if (is_numeric($entry)) {
                // Es probabilidad directa de True
                $pTrue = (float)$entry;
                return $value === 'True' ? $pTrue : (1.0 - $pTrue);
            }
            
            if (is_array($entry) && isset($entry[$value])) {
                return (float)$entry[$value];
            }
        }
        
        // Intentar FORMATO 2: JSON {"parent1":"True","parent2":"False"}
        $keyJSON = $this->buildCPTKeyJSON($normalizedParents);
        
        if (isset($cpt[$keyJSON])) {
            $entry = $cpt[$keyJSON];
            
            if (is_numeric($entry)) {
                $pTrue = (float)$entry;
                return $value === 'True' ? $pTrue : (1.0 - $pTrue);
            }
            
            if (is_array($entry) && isset($entry[$value])) {
                return (float)$entry[$value];
            }
        }
        
        // No se encontró: retornar probabilidad uniforme
        return 0.5;
    }
    
    /**
     * Construir clave CPT en formato string
     * Ejemplo: "lluvia=True,aspersor=False"
     * 
     * @param array $parentValues ['parent' => 'value', ...]
     * @return string
     */
    private function buildCPTKeyString($parentValues) {
        $parts = [];
        foreach ($parentValues as $parent => $value) {
            $parts[] = "$parent=$value";
        }
        return implode(',', $parts);
    }
    
    /**
     * Construir clave CPT en formato JSON
     * Ejemplo: {"lluvia":"True","aspersor":"False"}
     * 
     * @param array $parentValues ['parent' => 'value', ...]
     * @return string
     */
    private function buildCPTKeyJSON($parentValues) {
        return json_encode($parentValues, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Normalizar valor a 'True' o 'False'
     * @param mixed $value
     * @return string
     */
    private function normalizeValue($value) {
        if ($value === true || $value === 1 || $value === 'True' || $value === 'true' || $value === 'TRUE') {
            return 'True';
        }
        return 'False';
    }
    
    // ========== VALORES DE NODOS ==========
    
    /**
     * Obtener todos los valores posibles de un nodo
     * @param string $node ID del nodo
     * @return array Típicamente ['True', 'False']
     */
    public function getNodeValues($node) {
        return isset($this->nodeValues[$node]) ? $this->nodeValues[$node] : ['True', 'False'];
    }
    
    // ========== ESTADÍSTICAS ==========
    
    /**
     * Obtener número de nodos
     * @return int
     */
    public function getNodeCount() {
        return count($this->nodes);
    }
    
    /**
     * Obtener número de aristas
     * @return int
     */
    public function getEdgeCount() {
        return count($this->edges);
    }
    
    // ========== VALIDACIÓN ==========
    
    /**
     * Validar red completa
     * @return array ['valid' => bool, 'errors' => array, 'warnings' => array]
     */
    public function validate() {
        $errors = [];
        $warnings = [];
        
        // 1. Verificar que hay nodos
        if (empty($this->nodes)) {
            $errors[] = "La red no tiene nodos";
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }
        
        // 2. Verificar que todos los nodos en edges existen
        foreach ($this->edges as $edge) {
            $from = is_array($edge) ? $edge['from'] : $edge[0];
            $to = is_array($edge) ? $edge['to'] : $edge[1];
            
            if (!$this->nodeExists($from)) {
                $errors[] = "Nodo origen '$from' en arista no existe";
            }
            if (!$this->nodeExists($to)) {
                $errors[] = "Nodo destino '$to' en arista no existe";
            }
        }
        
        // 3. Verificar CPTs
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            
            if (!isset($this->cpt[$nodeId])) {
                $warnings[] = "Falta CPT para nodo '$nodeId'";
            } else {
                // Validar estructura de CPT
                $cptErrors = $this->validateCPT($nodeId);
                $errors = array_merge($errors, $cptErrors);
            }
        }
        
        // 4. Verificar que no hay ciclos (debe ser DAG)
        if ($this->hasCycles()) {
            $errors[] = "La red contiene ciclos (debe ser un DAG - Grafo Acíclico Dirigido)";
        }
        
        // 5. Verificar auto-bucles
        foreach ($this->edges as $edge) {
            $from = is_array($edge) ? $edge['from'] : $edge[0];
            $to = is_array($edge) ? $edge['to'] : $edge[1];
            
            if ($from === $to) {
                $errors[] = "Auto-bucle detectado en nodo '$from'";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Validar CPT de un nodo específico
     * @param string $nodeId
     * @return array Errores encontrados
     */
    private function validateCPT($nodeId) {
        $errors = [];
        $cpt = $this->getCPT($nodeId);
        $parents = $this->getParents($nodeId);
        
        if (empty($parents)) {
            // Sin padres: verificar estructura simple
            if (isset($cpt['root'])) {
                $prob = (float)$cpt['root'];
                if ($prob < 0 || $prob > 1) {
                    $errors[] = "CPT de '$nodeId': probabilidad fuera de rango [0,1]";
                }
            } elseif (isset($cpt['True']) && isset($cpt['False'])) {
                $sum = (float)$cpt['True'] + (float)$cpt['False'];
                if (abs($sum - 1.0) > 0.01) {
                    $errors[] = "CPT de '$nodeId': probabilidades no suman 1.0 (suma: " . round($sum, 4) . ")";
                }
            }
        } else {
            // Con padres: validar cada combinación
            // Esto es complejo porque depende del formato, simplificamos
            foreach ($cpt as $key => $value) {
                if (is_numeric($value)) {
                    if ($value < 0 || $value > 1) {
                        $errors[] = "CPT de '$nodeId' clave '$key': probabilidad fuera de rango";
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Verificar si un nodo existe
     * @param string $nodeId
     * @return bool
     */
    private function nodeExists($nodeId) {
        foreach ($this->nodes as $node) {
            $id = is_array($node) ? $node['id'] : $node;
            if ($id === $nodeId) {
                return true;
            }
        }
        return false;
    }
    
    // ========== DETECCIÓN DE CICLOS ==========
    
    /**
     * Detectar ciclos usando DFS
     * @return bool True si hay ciclos
     */
    public function hasCycles() {
        $visited = [];
        $recursionStack = [];
        
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            $visited[$nodeId] = false;
            $recursionStack[$nodeId] = false;
        }
        
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            if ($this->hasCyclesUtil($nodeId, $visited, $recursionStack)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Utilidad recursiva para detectar ciclos
     * @param string $node
     * @param array $visited
     * @param array $recursionStack
     * @return bool
     */
    private function hasCyclesUtil($node, &$visited, &$recursionStack) {
        if (!isset($visited[$node])) {
            return false;
        }
        
        if (!$visited[$node]) {
            $visited[$node] = true;
            $recursionStack[$node] = true;
            
            $children = $this->getChildren($node);
            foreach ($children as $child) {
                if (!$visited[$child] && $this->hasCyclesUtil($child, $visited, $recursionStack)) {
                    return true;
                } elseif ($recursionStack[$child]) {
                    return true;
                }
            }
        }
        
        $recursionStack[$node] = false;
        return false;
    }
    
    // ========== ORDENAMIENTO TOPOLÓGICO ==========
    
    /**
     * Obtener orden topológico de los nodos
     * Útil para optimizar inferencia
     * @return array IDs de nodos ordenados
     */
    public function getTopologicalOrder() {
        $visited = [];
        $stack = [];
        
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            $visited[$nodeId] = false;
        }
        
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            if (!$visited[$nodeId]) {
                $this->topologicalSortUtil($nodeId, $visited, $stack);
            }
        }
        
        return array_reverse($stack);
    }
    
    /**
     * Utilidad recursiva para ordenamiento topológico (DFS)
     * @param string $node
     * @param array $visited
     * @param array $stack
     */
    private function topologicalSortUtil($node, &$visited, &$stack) {
        $visited[$node] = true;
        
        $children = $this->getChildren($node);
        foreach ($children as $child) {
            if (!$visited[$child]) {
                $this->topologicalSortUtil($child, $visited, $stack);
            }
        }
        
        array_push($stack, $node);
    }
    
    // ========== EXPORTACIÓN ==========
    
    /**
     * Convertir red a JSON
     * @return string
     */
    public function toJSON() {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Convertir red a array
     * @return array
     */
    public function toArray() {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'nodes' => $this->nodes,
            'edges' => $this->edges,
            'cpt' => $this->cpt
        ];
    }
    
    /**
     * Obtener información resumida de la red
     * @return array
     */
    public function getSummary() {
        $validation = $this->validate();
        
        return [
            'name' => $this->name,
            'node_count' => $this->getNodeCount(),
            'edge_count' => $this->getEdgeCount(),
            'is_valid' => $validation['valid'],
            'has_cycles' => $this->hasCycles(),
            'topological_order' => $this->getTopologicalOrder(),
            'errors' => $validation['errors'],
            'warnings' => $validation['warnings']
        ];
    }
    
    /**
     * Imprimir información de debug
     * @return string
     */
    public function debug() {
        $output = "=== RED BAYESIANA: {$this->name} ===\n";
        $output .= "Descripción: {$this->description}\n";
        $output .= "Nodos: " . $this->getNodeCount() . "\n";
        $output .= "Aristas: " . $this->getEdgeCount() . "\n\n";
        
        $output .= "Relaciones:\n";
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            $nodeLabel = is_array($node) && isset($node['label']) ? $node['label'] : $nodeId;
            $parents = $this->getParents($nodeId);
            $children = $this->getChildren($nodeId);
            
            $output .= "- $nodeLabel ($nodeId):\n";
            $output .= "  Padres: " . (empty($parents) ? "ninguno" : implode(", ", $parents)) . "\n";
            $output .= "  Hijos: " . (empty($children) ? "ninguno" : implode(", ", $children)) . "\n";
            $output .= "  Valores: " . implode(", ", $this->getNodeValues($nodeId)) . "\n";
        }
        
        $validation = $this->validate();
        $output .= "\nValidación: " . ($validation['valid'] ? "✓ Válida" : "✗ Inválida") . "\n";
        
        if (!empty($validation['errors'])) {
            $output .= "Errores:\n";
            foreach ($validation['errors'] as $error) {
                $output .= "  - $error\n";
            }
        }
        
        if (!empty($validation['warnings'])) {
            $output .= "Advertencias:\n";
            foreach ($validation['warnings'] as $warning) {
                $output .= "  - $warning\n";
            }
        }
        
        return $output;
    }
}
?>