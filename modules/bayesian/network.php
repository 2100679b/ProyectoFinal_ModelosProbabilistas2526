<?php
/**
 * Clase Network - Manejo de Redes Bayesianas
 * UMSNH - Modelos Probabilísticos
 */

class BayesianNetwork {
    private $nodes = [];
    private $edges = [];
    private $cpt = []; // Conditional Probability Tables
    private $parents = [];
    private $children = [];
    
    /**
     * Constructor
     */
    public function __construct($data = null) {
        if ($data) {
            $this->loadFromArray($data);
        }
    }
    
    /**
     * Cargar red desde array
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
            
            $this->parents[$to][] = $from;
            $this->children[$from][] = $to;
        }
    }
    
    /**
     * Obtener nodos
     */
    public function getNodes() {
        return $this->nodes;
    }
    
    /**
     * Obtener aristas
     */
    public function getEdges() {
        return $this->edges;
    }
    
    /**
     * Obtener padres de un nodo
     */
    public function getParents($node) {
        return isset($this->parents[$node]) ? $this->parents[$node] : [];
    }
    
    /**
     * Obtener hijos de un nodo
     */
    public function getChildren($node) {
        return isset($this->children[$node]) ? $this->children[$node] : [];
    }
    
    /**
     * Obtener CPT de un nodo
     */
    public function getCPT($node) {
        return isset($this->cpt[$node]) ? $this->cpt[$node] : null;
    }
    
    /**
     * Obtener probabilidad condicional
     */
    public function getConditionalProbability($node, $value, $parentValues = []) {
        $cpt = $this->getCPT($node);
        
        if (!$cpt) {
            return 0;
        }
        
        // Si no hay padres, es probabilidad marginal
        if (empty($parentValues)) {
            return isset($cpt[$value]) ? $cpt[$value] : 0;
        }
        
        // Buscar en tabla condicional
        $key = $this->buildCPTKey($parentValues);
        
        if (isset($cpt[$key]) && isset($cpt[$key][$value])) {
            return $cpt[$key][$value];
        }
        
        return 0;
    }
    
    /**
     * Construir clave para CPT
     */
    private function buildCPTKey($parentValues) {
        ksort($parentValues);
        return json_encode($parentValues);
    }
    
    /**
     * Obtener todos los valores posibles de un nodo
     */
    public function getNodeValues($node) {
        $cpt = $this->getCPT($node);
        
        if (!$cpt) {
            return [];
        }
        
        // Si es array simple, las claves son los valores
        if (isset($cpt['true']) || isset($cpt['false'])) {
            return array_keys($cpt);
        }
        
        // Si tiene padres, tomar valores del primer registro
        $first = reset($cpt);
        return is_array($first) ? array_keys($first) : [];
    }
    
    /**
     * Validar red
     */
    public function validate() {
        $errors = [];
        
        // Verificar que todos los nodos existen
        foreach ($this->edges as $edge) {
            $from = is_array($edge) ? $edge['from'] : $edge[0];
            $to = is_array($edge) ? $edge['to'] : $edge[1];
            
            if (!$this->nodeExists($from)) {
                $errors[] = "Nodo '$from' no existe";
            }
            if (!$this->nodeExists($to)) {
                $errors[] = "Nodo '$to' no existe";
            }
        }
        
        // Verificar CPTs
        foreach ($this->nodes as $node) {
            $nodeId = is_array($node) ? $node['id'] : $node;
            if (!isset($this->cpt[$nodeId])) {
                $errors[] = "Falta CPT para nodo '$nodeId'";
            }
        }
        
        // Verificar ciclos (debe ser DAG)
        if ($this->hasCycles()) {
            $errors[] = "La red contiene ciclos (debe ser DAG)";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Verificar si un nodo existe
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
     */
    private function hasCyclesUtil($node, &$visited, &$recursionStack) {
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
     * Convertir a JSON
     */
    public function toJSON() {
        return json_encode([
            'nodes' => $this->nodes,
            'edges' => $this->edges,
            'cpt' => $this->cpt
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
?>
