<?php
/**
 * Clase BayesianNetwork - Manejo de Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Modelos Probabilísticos
 */

class BayesianNetwork {
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
        if (isset($data['nodes'])) {
            $this->nodes = $data['nodes'];
        }
        
        if (isset($data['edges'])) {
            $this->edges = $data['edges'];
            $this->buildParentChildRelations();
        }
        
        if (isset($data['cpt'])) {
            $this->cpt = $data['cpt'];
            $this->buildNodeValuesCache();
        }
    }
    
    /**
     * Construir relaciones padre-hijo
     */
    private function buildParentChildRelations() {
        // Inicializar arrays
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            $this->parents[$nodeId] = [];
            $this->children[$nodeId] = [];
        }
        
        // Construir relaciones
        foreach ($this->edges as $edge) {
            $from = is_array($edge) ? $edge['from'] : $edge[0];
            $to = is_array($edge) ? $edge['to'] : $edge[1];
            
            if (!isset($this->parents[$to])) {
                $this->parents[$to] = [];
            }
            if (!isset($this->children[$from])) {
                $this->children[$from] = [];
            }
            
            $this->parents[$to][] = $from;
            $this->children[$from][] = $to;
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
     * @return array Valores posibles
     */
    private function extractNodeValues($node) {
        $cpt = $this->getCPT($node);
        
        if (!$cpt || empty($cpt)) {
            return [];
        }
        
        // Si es array simple (sin padres), las claves son los valores
        $firstKey = array_key_first($cpt);
        
        // Verificar si es una clave JSON (tiene padres)
        if ($firstKey && (strpos($firstKey, '{') === 0)) {
            // Tiene padres: tomar valores del primer registro
            $first = reset($cpt);
            return is_array($first) ? array_keys($first) : [];
        } else {
            // Sin padres: las claves son los valores
            return array_keys($cpt);
        }
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
    
    /**
     * Obtener probabilidad condicional
     * @param string $node ID del nodo
     * @param mixed $value Valor del nodo
     * @param array $parentValues Valores de los padres
     * @return float
     */
    public function getConditionalProbability($node, $value, $parentValues = []) {
        $cpt = $this->getCPT($node);
        
        if (!$cpt) {
            return 0.0;
        }
        
        // Si no hay padres, es probabilidad marginal
        if (empty($parentValues)) {
            return isset($cpt[$value]) ? (float)$cpt[$value] : 0.0;
        }
        
        // Buscar en tabla condicional
        $key = $this->buildCPTKey($parentValues);
        
        if (isset($cpt[$key]) && isset($cpt[$key][$value])) {
            return (float)$cpt[$key][$value];
        }
        
        // Si no se encuentra, retornar 0
        return 0.0;
    }
    
    /**
     * Construir clave para CPT ordenada alfabéticamente
     * @param array $parentValues
     * @return string
     */
    private function buildCPTKey($parentValues) {
        ksort($parentValues);
        return json_encode($parentValues, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Obtener todos los valores posibles de un nodo
     * @param string $node ID del nodo
     * @return array
     */
    public function getNodeValues($node) {
        return isset($this->nodeValues[$node]) ? $this->nodeValues[$node] : [];
    }
    
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
    
    /**
     * Validar red
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validate() {
        $errors = [];
        
        // Verificar que hay nodos
        if (empty($this->nodes)) {
            $errors[] = "La red no tiene nodos";
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Verificar que todos los nodos en edges existen
        foreach ($this->edges as $edge) {
            $from = is_array($edge) ? $edge['from'] : $edge[0];
            $to = is_array($edge) ? $edge['to'] : $edge[1];
            
            if (!$this->nodeExists($from)) {
                $errors[] = "Nodo origen '$from' no existe en la red";
            }
            if (!$this->nodeExists($to)) {
                $errors[] = "Nodo destino '$to' no existe en la red";
            }
        }
        
        // Verificar CPTs
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            
            if (!isset($this->cpt[$nodeId])) {
                $errors[] = "Falta CPT para nodo '$nodeId'";
            } else {
                // Verificar que las probabilidades sumen 1
                $cptErrors = $this->validateCPT($nodeId);
                $errors = array_merge($errors, $cptErrors);
            }
        }
        
        // Verificar que no hay ciclos (debe ser DAG)
        if ($this->hasCycles()) {
            $errors[] = "La red contiene ciclos (debe ser un DAG - Grafo Acíclico Dirigido)";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Validar CPT de un nodo
     * @param string $nodeId
     * @return array Errores encontrados
     */
    private function validateCPT($nodeId) {
        $errors = [];
        $cpt = $this->getCPT($nodeId);
        $parents = $this->getParents($nodeId);
        
        if (empty($parents)) {
            // Sin padres: verificar que suma 1
            $sum = array_sum($cpt);
            if (abs($sum - 1.0) > 0.01) {
                $errors[] = "CPT de '$nodeId' no suma 1.0 (suma: $sum)";
            }
        } else {
            // Con padres: verificar cada combinación
            foreach ($cpt as $key => $probs) {
                if (is_array($probs)) {
                    $sum = array_sum($probs);
                    if (abs($sum - 1.0) > 0.01) {
                        $errors[] = "CPT de '$nodeId' con padres $key no suma 1.0 (suma: $sum)";
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
    
    /**
     * Detectar ciclos usando DFS
     * @return bool
     */
    private function hasCycles() {
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
    
    /**
     * Obtener orden topológico
     * @return array
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
     * Utilidad recursiva para ordenamiento topológico
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
    
    /**
     * Convertir red a JSON
     * @return string
     */
    public function toJSON() {
        return json_encode([
            'nodes' => $this->nodes,
            'edges' => $this->edges,
            'cpt' => $this->cpt
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Convertir red a array
     * @return array
     */
    public function toArray() {
        return [
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
        return [
            'node_count' => $this->getNodeCount(),
            'edge_count' => $this->getEdgeCount(),
            'is_valid' => $this->validate()['valid'],
            'has_cycles' => $this->hasCycles(),
            'topological_order' => $this->getTopologicalOrder()
        ];
    }
    
    /**
     * Imprimir información de debug
     * @return string
     */
    public function debug() {
        $output = "=== RED BAYESIANA ===\n";
        $output .= "Nodos: " . $this->getNodeCount() . "\n";
        $output .= "Aristas: " . $this->getEdgeCount() . "\n\n";
        
        $output .= "Relaciones:\n";
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            $parents = $this->getParents($nodeId);
            $children = $this->getChildren($nodeId);
            
            $output .= "- $nodeId:\n";
            $output .= "  Padres: " . (empty($parents) ? "ninguno" : implode(", ", $parents)) . "\n";
            $output .= "  Hijos: " . (empty($children) ? "ninguno" : implode(", ", $children)) . "\n";
        }
        
        return $output;
    }
}
?>
