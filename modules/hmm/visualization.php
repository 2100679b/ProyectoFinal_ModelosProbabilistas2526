<?php
/**
 * Visualización de HMM
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['hmm'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No se recibieron datos del HMM']);
    exit;
}

$hmm = $data['hmm'];

// Generar datos para visualización
$visualization = [
    'nodes' => [],
    'edges' => []
];

// Nodos de estados ocultos
if (isset($hmm['hiddenStates'])) {
    foreach ($hmm['hiddenStates'] as $index => $state) {
        $visualization['nodes'][] = [
            'id' => 'hidden_' . $state['id'],
            'label' => '🔒 ' . ($state['label'] ?? $state['id']),
            'title' => 'Estado Oculto: ' . ($state['description'] ?? $state['label']),
            'level' => 0,
            'group' => 'hidden',
            'color' => [
                'background' => '#f3e8ff',
                'border' => '#9333ea',
                'highlight' => [
                    'background' => '#9333ea',
                    'border' => '#7e22ce'
                ]
            ]
        ];
    }
}

// Nodos de observaciones
if (isset($hmm['observations'])) {
    foreach ($hmm['observations'] as $index => $obs) {
        $visualization['nodes'][] = [
            'id' => 'obs_' . $obs['id'],
            'label' => '👁️ ' . ($obs['label'] ?? $obs['id']),
            'title' => 'Observación: ' . ($obs['description'] ?? $obs['label']),
            'level' => 1,
            'group' => 'observation',
            'color' => [
                'background' => '#ddd6fe',
                'border' => '#7c3aed',
                'highlight' => [
                    'background' => '#7c3aed',
                    'border' => '#6d28d9'
                ]
            ]
        ];
    }
}

// Aristas de transición (entre estados ocultos)
if (isset($hmm['transitionMatrix'])) {
    foreach ($hmm['transitionMatrix'] as $fromState => $transitions) {
        foreach ($transitions as $toState => $probability) {
            if ($probability > 0) {
                $visualization['edges'][] = [
                    'from' => 'hidden_' . $fromState,
                    'to' => 'hidden_' . $toState,
                    'label' => number_format($probability, 2),
                    'title' => "P($toState|$fromState) = $probability",
                    'arrows' => 'to',
                    'color' => ['color' => '#9333ea'],
                    'width' => 2
                ];
            }
        }
    }
}

// Aristas de emisión (estados ocultos → observaciones)
if (isset($hmm['emissionMatrix'])) {
    foreach ($hmm['emissionMatrix'] as $state => $emissions) {
        foreach ($emissions as $observation => $probability) {
            if ($probability > 0) {
                $visualization['edges'][] = [
                    'from' => 'hidden_' . $state,
                    'to' => 'obs_' . $observation,
                    'label' => number_format($probability, 2),
                    'title' => "P($observation|$state) = $probability",
                    'arrows' => 'to',
                    'color' => ['color' => '#c026d3'],
                    'width' => 1,
                    'dashes' => true
                ];
            }
        }
    }
}

// Calcular estadísticas
$stats = [
    'numHiddenStates' => count($hmm['hiddenStates'] ?? []),
    'numObservations' => count($hmm['observations'] ?? []),
    'numTransitions' => count($visualization['edges']),
    'domain' => $hmm['domain'] ?? 'General'
];

echo json_encode([
    'success' => true,
    'visualization' => $visualization,
    'stats' => $stats
]);
