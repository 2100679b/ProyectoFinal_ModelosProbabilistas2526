<?php
/**
 * Generación de visualización para Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Modelos Probabilísticos
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/network.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Recibir datos
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Recibir red desde POST
        $networkData = json_decode($_POST['network'] ?? '{}', true);
        
        if (empty($networkData)) {
            throw new Exception('No se proporcionó red bayesiana');
        }
        
        // Crear red
        $network = new BayesianNetwork($networkData);
        
        // Validar red
        $validation = $network->validate();
        if (!$validation['valid']) {
            throw new Exception('Red inválida: ' . implode(', ', $validation['errors']));
        }
        
        // Tipo de visualización
        $visualizationType = $_POST['type'] ?? 'network';
        
        switch ($visualizationType) {
            case 'network':
                $result = generateNetworkVisualization($network);
                break;
                
            case 'cpt':
                $nodeId = $_POST['node'] ?? '';
                if (empty($nodeId)) {
                    throw new Exception('No se especificó nodo para CPT');
                }
                $result = generateCPTVisualization($network, $nodeId);
                break;
                
            case 'hierarchy':
                $result = generateHierarchicalVisualization($network);
                break;
                
            default:
                throw new Exception('Tipo de visualización no válido');
        }
        
        // Responder
        echo json_encode([
            'success' => true,
            'type' => $visualizationType,
            'data' => $result
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
    } else {
        throw new Exception('Método HTTP no permitido');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

/**
 * Generar datos para visualización de red con Vis.js
 * @param BayesianNetwork $network
 * @return array
 */
function generateNetworkVisualization($network) {
    $nodes = [];
    $edges = [];
    
    // Procesar nodos
    foreach ($network->getNodes() as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        $nodeLabel = is_array($node) && isset($node['label']) ? $node['label'] : $nodeId;
        
        // Determinar color según número de padres
        $parents = $network->getParents($nodeId);
        $children = $network->getChildren($nodeId);
        $parentCount = count($parents);
        
        // Color según tipo de nodo
        if ($parentCount === 0) {
            // Nodo raíz (sin padres)
            $bgColor = '#d1fae5';
            $borderColor = '#10b981';
        } elseif (empty($children)) {
            // Nodo hoja (sin hijos)
            $bgColor = '#fee2e2';
            $borderColor = '#ef4444';
        } else {
            // Nodo intermedio
            $bgColor = '#dbeafe';
            $borderColor = '#2563eb';
        }
        
        $nodes[] = [
            'id' => $nodeId,
            'label' => $nodeLabel,
            'title' => generateNodeTooltip($network, $nodeId),
            'shape' => 'box',
            'color' => [
                'background' => $bgColor,
                'border' => $borderColor,
                'highlight' => [
                    'background' => $borderColor,
                    'border' => darkenColor($borderColor)
                ],
                'hover' => [
                    'background' => lightenColor($bgColor),
                    'border' => $borderColor
                ]
            ],
            'font' => [
                'color' => '#1f2937',
                'size' => 16,
                'face' => 'Arial, sans-serif',
                'bold' => [
                    'color' => '#111827'
                ]
            ],
            'margin' => 10,
            'borderWidth' => 2,
            'borderWidthSelected' => 4,
            'shapeProperties' => [
                'borderRadius' => 6
            ]
        ];
    }
    
    // Procesar aristas
    foreach ($network->getEdges() as $edge) {
        $from = is_array($edge) ? $edge['from'] : $edge[0];
        $to = is_array($edge) ? $edge['to'] : $edge[1];
        
        $edges[] = [
            'from' => $from,
            'to' => $to,
            'arrows' => [
                'to' => [
                    'enabled' => true,
                    'scaleFactor' => 1.2,
                    'type' => 'arrow'
                ]
            ],
            'color' => [
                'color' => '#6b7280',
                'highlight' => '#2563eb',
                'hover' => '#4b5563'
            ],
            'width' => 2,
            'smooth' => [
                'type' => 'cubicBezier',
                'roundness' => 0.5
            ],
            'hoverWidth' => 0.5
        ];
    }
    
    // Opciones de visualización
    $options = [
        'layout' => [
            'hierarchical' => [
                'enabled' => true,
                'direction' => 'UD',
                'sortMethod' => 'directed',
                'levelSeparation' => 150,
                'nodeSpacing' => 200,
                'treeSpacing' => 250
            ]
        ],
        'physics' => [
            'enabled' => false
        ],
        'interaction' => [
            'dragNodes' => true,
            'dragView' => true,
            'zoomView' => true,
            'hover' => true,
            'tooltipDelay' => 200,
            'navigationButtons' => true,
            'keyboard' => [
                'enabled' => true
            ]
        ],
        'nodes' => [
            'chosen' => [
                'node' => true,
                'label' => true
            ]
        ],
        'edges' => [
            'chosen' => true
        ]
    ];
    
    return [
        'nodes' => $nodes,
        'edges' => $edges,
        'options' => $options
    ];
}

/**
 * Generar tooltip para un nodo
 * @param BayesianNetwork $network
 * @param string $nodeId
 * @return string
 */
function generateNodeTooltip($network, $nodeId) {
    $node = $network->getNode($nodeId);
    $nodeLabel = is_array($node) && isset($node['label']) ? $node['label'] : $nodeId;
    
    $parents = $network->getParents($nodeId);
    $children = $network->getChildren($nodeId);
    $values = $network->getNodeValues($nodeId);
    
    $tooltip = "<div style='text-align: left;'>";
    $tooltip .= "<strong>{$nodeLabel}</strong><br>";
    $tooltip .= "<hr style='margin: 5px 0;'>";
    
    if (!empty($parents)) {
        $tooltip .= "<strong>Padres:</strong> " . implode(", ", $parents) . "<br>";
    } else {
        $tooltip .= "<strong>Tipo:</strong> Nodo raíz<br>";
    }
    
    if (!empty($children)) {
        $tooltip .= "<strong>Hijos:</strong> " . implode(", ", $children) . "<br>";
    }
    
    if (!empty($values)) {
        $tooltip .= "<strong>Valores:</strong> " . implode(", ", $values) . "<br>";
    }
    
    $tooltip .= "</div>";
    
    return $tooltip;
}

/**
 * Generar visualización de CPT (Tabla de Probabilidad Condicional)
 * @param BayesianNetwork $network
 * @param string $nodeId
 * @return array
 */
function generateCPTVisualization($network, $nodeId) {
    $cpt = $network->getCPT($nodeId);
    $parents = $network->getParents($nodeId);
    $nodeValues = $network->getNodeValues($nodeId);
    
    if (!$cpt) {
        throw new Exception("No se encontró CPT para el nodo '$nodeId'");
    }
    
    $result = [
        'node' => $nodeId,
        'parents' => $parents,
        'values' => $nodeValues,
        'table' => []
    ];
    
    if (empty($parents)) {
        // Sin padres: probabilidad marginal
        $result['type'] = 'marginal';
        
        foreach ($nodeValues as $value) {
            $result['table'][] = [
                'value' => $value,
                'probability' => $cpt[$value] ?? 0
            ];
        }
    } else {
        // Con padres: tabla condicional
        $result['type'] = 'conditional';
        
        foreach ($cpt as $parentKey => $probs) {
            if (is_array($probs)) {
                $parentValues = json_decode($parentKey, true);
                
                foreach ($probs as $value => $prob) {
                    $result['table'][] = [
                        'parent_values' => $parentValues,
                        'value' => $value,
                        'probability' => $prob
                    ];
                }
            }
        }
    }
    
    return $result;
}

/**
 * Generar visualización jerárquica
 * @param BayesianNetwork $network
 * @return array
 */
function generateHierarchicalVisualization($network) {
    $levels = [];
    $visited = [];
    
    // Encontrar nodos raíz (sin padres)
    $roots = [];
    foreach ($network->getNodes() as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        $parents = $network->getParents($nodeId);
        
        if (empty($parents)) {
            $roots[] = $nodeId;
        }
    }
    
    // Construir niveles usando BFS
    $currentLevel = $roots;
    $levelIndex = 0;
    
    while (!empty($currentLevel)) {
        $levels[$levelIndex] = [];
        $nextLevel = [];
        
        foreach ($currentLevel as $nodeId) {
            if (!isset($visited[$nodeId])) {
                $visited[$nodeId] = true;
                $levels[$levelIndex][] = [
                    'id' => $nodeId,
                    'label' => $nodeId,
                    'level' => $levelIndex
                ];
                
                // Agregar hijos al siguiente nivel
                $children = $network->getChildren($nodeId);
                foreach ($children as $child) {
                    if (!isset($visited[$child])) {
                        $nextLevel[] = $child;
                    }
                }
            }
        }
        
        $currentLevel = $nextLevel;
        $levelIndex++;
    }
    
    return [
        'levels' => $levels,
        'total_levels' => count($levels),
        'topological_order' => $network->getTopologicalOrder()
    ];
}

/**
 * Oscurecer un color hexadecimal
 * @param string $hex Color en formato #RRGGBB
 * @param float $percent Porcentaje a oscurecer (0-1)
 * @return string
 */
function darkenColor($hex, $percent = 0.2) {
    $hex = str_replace('#', '', $hex);
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, $r - ($r * $percent));
    $g = max(0, $g - ($g * $percent));
    $b = max(0, $b - ($b * $percent));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

/**
 * Aclarar un color hexadecimal
 * @param string $hex Color en formato #RRGGBB
 * @param float $percent Porcentaje a aclarar (0-1)
 * @return string
 */
function lightenColor($hex, $percent = 0.2) {
    $hex = str_replace('#', '', $hex);
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = min(255, $r + ((255 - $r) * $percent));
    $g = min(255, $g + ((255 - $g) * $percent));
    $b = min(255, $b + ((255 - $b) * $percent));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

/**
 * Generar visualización en formato DOT (Graphviz)
 * @param BayesianNetwork $network
 * @return string
 */
function generateDotFormat($network) {
    $dot = "digraph BayesianNetwork {\n";
    $dot .= "    rankdir=TB;\n";
    $dot .= "    node [shape=box, style=rounded];\n\n";
    
    // Nodos
    foreach ($network->getNodes() as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        $nodeLabel = is_array($node) && isset($node['label']) ? $node['label'] : $nodeId;
        
        $dot .= "    \"{$nodeId}\" [label=\"{$nodeLabel}\"];\n";
    }
    
    $dot .= "\n";
    
    // Aristas
    foreach ($network->getEdges() as $edge) {
        $from = is_array($edge) ? $edge['from'] : $edge[0];
        $to = is_array($edge) ? $edge['to'] : $edge[1];
        
        $dot .= "    \"{$from}\" -> \"{$to}\";\n";
    }
    
    $dot .= "}\n";
    
    return $dot;
}
?>
