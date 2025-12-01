<?php
/**
 * Generación de visualización para Cadenas de Markov
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Modelos Probabilísticos
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/chain.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Recibir datos
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Recibir cadena desde POST
        $chainData = json_decode($_POST['chain'] ?? '{}', true);
        
        if (empty($chainData)) {
            throw new Exception('No se proporcionó cadena de Markov');
        }
        
        // Crear cadena
        $chain = new MarkovChain($chainData);
        
        // Validar cadena
        $validation = $chain->validate();
        if (!$validation['valid']) {
            throw new Exception('Cadena inválida: ' . implode(', ', $validation['errors']));
        }
        
        // Tipo de visualización
        $visualizationType = $_POST['type'] ?? 'diagram';
        
        switch ($visualizationType) {
            case 'diagram':
                $result = generateDiagramVisualization($chain);
                break;
                
            case 'matrix':
                $result = generateMatrixVisualization($chain);
                break;
                
            case 'graph':
                $result = generateGraphData($chain);
                break;
                
            case 'stationary':
                $result = generateStationaryVisualization($chain);
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
 * Generar datos para visualización de diagrama con Vis.js
 * @param MarkovChain $chain
 * @return array
 */
function generateDiagramVisualization($chain) {
    $states = $chain->getStates();
    $matrix = $chain->getTransitionMatrix();
    $stateCount = $chain->getStateCount();
    
    $nodes = [];
    $edges = [];
    
    // Detectar estados especiales
    $absorbingStates = $chain->getAbsorbingStates();
    
    // Procesar nodos
    foreach ($states as $index => $stateName) {
        $isAbsorbing = in_array($index, $absorbingStates);
        
        // Determinar color según tipo de estado
        if ($isAbsorbing) {
            $bgColor = '#ef4444';
            $borderColor = '#dc2626';
        } else {
            $bgColor = '#10b981';
            $borderColor = '#059669';
        }
        
        $nodes[] = [
            'id' => $index,
            'label' => $stateName,
            'title' => generateNodeTooltip($chain, $index, $stateName),
            'shape' => 'circle',
            'size' => 30,
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
                'color' => '#ffffff',
                'size' => 16,
                'face' => 'Arial, sans-serif',
                'bold' => true
            ],
            'borderWidth' => 3,
            'borderWidthSelected' => 5
        ];
    }
    
    // Procesar aristas (transiciones)
    for ($i = 0; $i < $stateCount; $i++) {
        for ($j = 0; $j < $stateCount; $j++) {
            $prob = $chain->getTransitionProbability($i, $j);
            
            if ($prob > 0) {
                // Determinar color según probabilidad
                $edgeColor = getTransitionColor($prob);
                $edgeWidth = 1 + ($prob * 4); // Ancho entre 1 y 5
                
                $edge = [
                    'from' => $i,
                    'to' => $j,
                    'label' => number_format($prob, 2),
                    'value' => $prob,
                    'color' => [
                        'color' => $edgeColor,
                        'highlight' => '#10b981',
                        'hover' => '#34d399'
                    ],
                    'width' => $edgeWidth,
                    'arrows' => [
                        'to' => [
                            'enabled' => true,
                            'scaleFactor' => 1.2,
                            'type' => 'arrow'
                        ]
                    ],
                    'font' => [
                        'size' => 12,
                        'align' => 'top',
                        'color' => '#374151',
                        'strokeWidth' => 2,
                        'strokeColor' => '#ffffff'
                    ],
                    'title' => "P({$states[$i]} → {$states[$j]}) = " . number_format($prob, 4)
                ];
                
                // Si es self-loop, usar smooth diferente
                if ($i === $j) {
                    $edge['smooth'] = [
                        'type' => 'curvedCW',
                        'roundness' => 0.5
                    ];
                } else {
                    $edge['smooth'] = [
                        'type' => 'curvedCW',
                        'roundness' => 0.2
                    ];
                }
                
                $edges[] = $edge;
            }
        }
    }
    
    // Opciones de visualización
    $options = [
        'layout' => [
            'hierarchical' => [
                'enabled' => false
            ]
        ],
        'physics' => [
            'enabled' => true,
            'stabilization' => [
                'iterations' => 200,
                'updateInterval' => 10
            ],
            'barnesHut' => [
                'gravitationalConstant' => -2000,
                'centralGravity' => 0.3,
                'springLength' => 150,
                'springConstant' => 0.04,
                'damping' => 0.09,
                'avoidOverlap' => 0.5
            ]
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
        'options' => $options,
        'summary' => $chain->getSummary()
    ];
}

/**
 * Generar tooltip para un nodo
 * @param MarkovChain $chain
 * @param int $index Índice del estado
 * @param string $stateName Nombre del estado
 * @return string
 */
function generateNodeTooltip($chain, $index, $stateName) {
    $transitions = $chain->getTransitionsFrom($index);
    $isAbsorbing = $chain->isAbsorbingState($index);
    
    $tooltip = "<div style='text-align: left;'>";
    $tooltip .= "<strong style='font-size: 1.1em;'>{$stateName}</strong><br>";
    $tooltip .= "<hr style='margin: 5px 0; border: 1px solid #e5e7eb;'>";
    
    if ($isAbsorbing) {
        $tooltip .= "<span style='color: #ef4444;'>⚠️ Estado Absorbente</span><br>";
    }
    
    $tooltip .= "<strong>Transiciones salientes:</strong><br>";
    
    if (!empty($transitions)) {
        foreach ($transitions as $toState => $prob) {
            $toStateName = $chain->getStateName($toState);
            $percentage = number_format($prob * 100, 1);
            $tooltip .= "→ {$toStateName}: {$percentage}%<br>";
        }
    } else {
        $tooltip .= "<em>Sin transiciones</em><br>";
    }
    
    $tooltip .= "</div>";
    
    return $tooltip;
}

/**
 * Generar visualización de matriz de transición
 * @param MarkovChain $chain
 * @return array
 */
function generateMatrixVisualization($chain) {
    $states = $chain->getStates();
    $matrix = $chain->getTransitionMatrix();
    $stateCount = $chain->getStateCount();
    
    $matrixData = [];
    
    // Construir matriz con metadata
    for ($i = 0; $i < $stateCount; $i++) {
        $row = [];
        for ($j = 0; $j < $stateCount; $j++) {
            $prob = $chain->getTransitionProbability($i, $j);
            
            $row[] = [
                'value' => $prob,
                'formatted' => number_format($prob, 4),
                'percentage' => number_format($prob * 100, 2) . '%',
                'color_class' => getProbabilityColorClass($prob),
                'from' => $states[$i],
                'to' => $states[$j]
            ];
        }
        $matrixData[] = $row;
    }
    
    return [
        'states' => $states,
        'matrix' => $matrixData,
        'state_count' => $stateCount,
        'is_stochastic' => isStochasticMatrix($chain)
    ];
}

/**
 * Generar datos para gráficos (distribuciones, etc.)
 * @param MarkovChain $chain
 * @return array
 */
function generateGraphData($chain) {
    $states = $chain->getStates();
    $stateCount = $chain->getStateCount();
    
    // Distribución inicial
    $initialDist = $chain->getInitialDistribution();
    
    // Calcular evolución de distribución
    $steps = [0, 1, 2, 5, 10, 20, 50, 100];
    $evolution = [];
    
    foreach ($steps as $step) {
        if ($step === 0) {
            $evolution[$step] = $initialDist;
        } else {
            $evolution[$step] = $chain->getDistributionAfterSteps($initialDist, $step);
        }
    }
    
    // Distribución estacionaria (si existe)
    $stationaryDist = null;
    if ($chain->isErgodic()) {
        $stationaryDist = $chain->getStationaryDistribution();
    }
    
    return [
        'states' => $states,
        'initial_distribution' => $initialDist,
        'distribution_evolution' => $evolution,
        'stationary_distribution' => $stationaryDist,
        'steps' => $steps
    ];
}

/**
 * Generar visualización de distribución estacionaria
 * @param MarkovChain $chain
 * @return array
 */
function generateStationaryVisualization($chain) {
    $states = $chain->getStates();
    
    if (!$chain->isErgodic()) {
        return [
            'has_stationary' => false,
            'reason' => 'La cadena no es ergódica (debe ser irreducible y aperiódica)'
        ];
    }
    
    $stationaryDist = $chain->getStationaryDistribution();
    
    if ($stationaryDist === null) {
        return [
            'has_stationary' => false,
            'reason' => 'No se pudo calcular la distribución estacionaria (no convergió)'
        ];
    }
    
    $data = [];
    foreach ($states as $index => $stateName) {
        $data[] = [
            'state' => $stateName,
            'probability' => $stationaryDist[$index],
            'percentage' => number_format($stationaryDist[$index] * 100, 2) . '%',
            'formatted' => number_format($stationaryDist[$index], 6)
        ];
    }
    
    return [
        'has_stationary' => true,
        'distribution' => $data,
        'total' => array_sum($stationaryDist),
        'is_valid' => abs(array_sum($stationaryDist) - 1.0) < 0.01
    ];
}

/**
 * Determinar color de transición según probabilidad
 * @param float $probability
 * @return string Color hexadecimal
 */
function getTransitionColor($probability) {
    if ($probability >= 0.75) {
        return '#047857'; // Verde oscuro (alta probabilidad)
    } elseif ($probability >= 0.50) {
        return '#10b981'; // Verde medio
    } elseif ($probability >= 0.25) {
        return '#6ee7b7'; // Verde claro
    } else {
        return '#a7f3d0'; // Verde muy claro (baja probabilidad)
    }
}

/**
 * Determinar clase de color para probabilidad
 * @param float $probability
 * @return string
 */
function getProbabilityColorClass($probability) {
    if ($probability >= 0.75) {
        return 'high';
    } elseif ($probability >= 0.50) {
        return 'medium-high';
    } elseif ($probability >= 0.25) {
        return 'medium';
    } elseif ($probability > 0) {
        return 'low';
    } else {
        return 'zero';
    }
}

/**
 * Verificar si la matriz es estocástica
 * @param MarkovChain $chain
 * @return bool
 */
function isStochasticMatrix($chain) {
    $stateCount = $chain->getStateCount();
    
    for ($i = 0; $i < $stateCount; $i++) {
        $rowSum = 0.0;
        for ($j = 0; $j < $stateCount; $j++) {
            $rowSum += $chain->getTransitionProbability($i, $j);
        }
        
        if (abs($rowSum - 1.0) > 0.01) {
            return false;
        }
    }
    
    return true;
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
 * @param MarkovChain $chain
 * @return string
 */
function generateDotFormat($chain) {
    $states = $chain->getStates();
    $stateCount = $chain->getStateCount();
    
    $dot = "digraph MarkovChain {\n";
    $dot .= "    rankdir=LR;\n";
    $dot .= "    node [shape=circle, style=filled, fillcolor=lightgreen];\n\n";
    
    // Estados absorbentes con color diferente
    $absorbingStates = $chain->getAbsorbingStates();
    foreach ($absorbingStates as $index) {
        $dot .= "    \"{$states[$index]}\" [fillcolor=lightcoral, peripheries=2];\n";
    }
    
    $dot .= "\n";
    
    // Aristas con probabilidades
    for ($i = 0; $i < $stateCount; $i++) {
        for ($j = 0; $j < $stateCount; $j++) {
            $prob = $chain->getTransitionProbability($i, $j);
            
            if ($prob > 0) {
                $label = number_format($prob, 2);
                $dot .= "    \"{$states[$i]}\" -> \"{$states[$j]}\" [label=\"{$label}\"];\n";
            }
        }
    }
    
    $dot .= "}\n";
    
    return $dot;
}

/**
 * Generar visualización de flujo de probabilidad
 * @param MarkovChain $chain
 * @param int $startState Estado inicial
 * @param int $steps Número de pasos
 * @return array
 */
function generateProbabilityFlow($chain, $startState, $steps) {
    $states = $chain->getStates();
    $flow = [];
    
    $distribution = array_fill(0, $chain->getStateCount(), 0.0);
    $distribution[$startState] = 1.0;
    
    $flow[0] = $distribution;
    
    for ($t = 1; $t <= $steps; $t++) {
        $distribution = $chain->getDistributionAfterSteps($distribution, 1);
        $flow[$t] = $distribution;
    }
    
    return [
        'states' => $states,
        'start_state' => $states[$startState],
        'flow' => $flow,
        'steps' => $steps
    ];
}
?>
