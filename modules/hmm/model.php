<?php
/**
 * Modelo y operaciones de HMM
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

class HMMModel {
    private $hiddenStates;
    private $observations;
    private $transitionMatrix;
    private $emissionMatrix;
    private $initialProbabilities;
    
    public function __construct($hmm) {
        $this->hiddenStates = $hmm['hiddenStates'] ?? [];
        $this->observations = $hmm['observations'] ?? [];
        $this->transitionMatrix = $hmm['transitionMatrix'] ?? [];
        $this->emissionMatrix = $hmm['emissionMatrix'] ?? [];
        $this->initialProbabilities = $hmm['initialProbabilities'] ?? [];
    }
    
    /**
     * Validar que el HMM sea válido
     */
    public function validate() {
        $errors = [];
        
        // Validar que haya al menos un estado oculto
        if (empty($this->hiddenStates)) {
            $errors[] = 'Debe haber al menos un estado oculto';
        }
        
        // Validar que haya al menos una observación
        if (empty($this->observations)) {
            $errors[] = 'Debe haber al menos una observación';
        }
        
        // Validar matriz de transición
        foreach ($this->hiddenStates as $state) {
            $stateId = $state['id'];
            if (!isset($this->transitionMatrix[$stateId])) {
                $errors[] = "Falta definir transiciones para el estado $stateId";
                continue;
            }
            
            $sum = array_sum($this->transitionMatrix[$stateId]);
            if (abs($sum - 1.0) > 0.001) {
                $errors[] = "Las probabilidades de transición del estado $stateId no suman 1 (suma: $sum)";
            }
        }
        
        // Validar matriz de emisión
        foreach ($this->hiddenStates as $state) {
            $stateId = $state['id'];
            if (!isset($this->emissionMatrix[$stateId])) {
                $errors[] = "Falta definir emisiones para el estado $stateId";
                continue;
            }
            
            $sum = array_sum($this->emissionMatrix[$stateId]);
            if (abs($sum - 1.0) > 0.001) {
                $errors[] = "Las probabilidades de emisión del estado $stateId no suman 1 (suma: $sum)";
            }
        }
        
        // Validar probabilidades iniciales
        $initialSum = array_sum($this->initialProbabilities);
        if (abs($initialSum - 1.0) > 0.001) {
            $errors[] = "Las probabilidades iniciales no suman 1 (suma: $initialSum)";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
<<<<<<< HEAD
=======
     * Algoritmo Forward (α)
     * Calcula la probabilidad de una secuencia de observaciones
     */
    public function forward($observationSequence) {
        $T = count($observationSequence);
        $N = count($this->hiddenStates);
        
        // Inicializar matriz alpha
        $alpha = [];
        
        // Inicialización (t=0)
        foreach ($this->hiddenStates as $state) {
            $stateId = $state['id'];
            $obs = $observationSequence[0];
            $alpha[0][$stateId] = $this->initialProbabilities[$stateId] * 
                                  $this->emissionMatrix[$stateId][$obs];
        }
        
        // Recursión (t=1 hasta T-1)
        for ($t = 1; $t < $T; $t++) {
            foreach ($this->hiddenStates as $state) {
                $stateId = $state['id'];
                $obs = $observationSequence[$t];
                
                $sum = 0;
                foreach ($this->hiddenStates as $prevState) {
                    $prevStateId = $prevState['id'];
                    $sum += $alpha[$t-1][$prevStateId] * 
                            $this->transitionMatrix[$prevStateId][$stateId];
                }
                
                $alpha[$t][$stateId] = $sum * $this->emissionMatrix[$stateId][$obs];
            }
        }
        
        // Terminación: probabilidad total
        $probability = 0;
        foreach ($this->hiddenStates as $state) {
            $probability += $alpha[$T-1][$state['id']];
        }
        
        return [
            'alpha' => $alpha,
            'probability' => $probability,
            'logProbability' => log($probability)
        ];
    }
    
    /**
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
     * Algoritmo Viterbi
     * Encuentra la secuencia de estados más probable
     */
    public function viterbi($observationSequence) {
        $T = count($observationSequence);
        $N = count($this->hiddenStates);
        
        // Inicializar matrices
        $delta = [];
        $psi = [];
        
        // Inicialización (t=0)
        foreach ($this->hiddenStates as $state) {
            $stateId = $state['id'];
            $obs = $observationSequence[0];
<<<<<<< HEAD
            $delta[0][$stateId] = $this->initialProbabilities[$stateId] * $this->emissionMatrix[$stateId][$obs];
=======
            $delta[0][$stateId] = $this->initialProbabilities[$stateId] * 
                                  $this->emissionMatrix[$stateId][$obs];
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
            $psi[0][$stateId] = null;
        }
        
        // Recursión (t=1 hasta T-1)
        for ($t = 1; $t < $T; $t++) {
            foreach ($this->hiddenStates as $state) {
                $stateId = $state['id'];
                $obs = $observationSequence[$t];
                
                $maxProb = -INF;
                $maxState = null;
                
                foreach ($this->hiddenStates as $prevState) {
                    $prevStateId = $prevState['id'];
<<<<<<< HEAD
                    $prob = $delta[$t-1][$prevStateId] * $this->transitionMatrix[$prevStateId][$stateId];
=======
                    $prob = $delta[$t-1][$prevStateId] * 
                            $this->transitionMatrix[$prevStateId][$stateId];
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
                    
                    if ($prob > $maxProb) {
                        $maxProb = $prob;
                        $maxState = $prevStateId;
                    }
                }
                
                $delta[$t][$stateId] = $maxProb * $this->emissionMatrix[$stateId][$obs];
                $psi[$t][$stateId] = $maxState;
            }
        }
        
        // Terminación: encontrar el estado final más probable
        $maxProb = -INF;
        $lastState = null;
        
        foreach ($this->hiddenStates as $state) {
            $stateId = $state['id'];
            if ($delta[$T-1][$stateId] > $maxProb) {
                $maxProb = $delta[$T-1][$stateId];
                $lastState = $stateId;
            }
        }
        
        // Backtracking: reconstruir la secuencia óptima
        $path = [$lastState];
        for ($t = $T - 1; $t > 0; $t--) {
            $lastState = $psi[$t][$lastState];
            array_unshift($path, $lastState);
        }
        
        return [
            'path' => $path,
            'probability' => $maxProb,
            'logProbability' => log($maxProb),
            'delta' => $delta,
            'psi' => $psi
        ];
    }
    
    /**
     * Obtener información del modelo
     */
    public function getInfo() {
        return [
            'numHiddenStates' => count($this->hiddenStates),
            'numObservations' => count($this->observations),
            'hiddenStateLabels' => array_map(function($s) { return $s['label'] ?? $s['id']; }, $this->hiddenStates),
            'observationLabels' => array_map(function($o) { return $o['label'] ?? $o['id']; }, $this->observations)
        ];
    }
}

// Si se llama directamente via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['hmm'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No se recibieron datos del HMM']);
        exit;
    }
    
    $model = new HMMModel($data['hmm']);
    
    // Validar modelo
    if (isset($data['action']) && $data['action'] === 'validate') {
        echo json_encode($model->validate());
        exit;
    }
    
<<<<<<< HEAD
    // Algoritmo Viterbi (Único permitido)
=======
    // Algoritmo Forward
    if (isset($data['action']) && $data['action'] === 'forward') {
        if (!isset($data['observations'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No se especificó la secuencia de observaciones']);
            exit;
        }
        
        $result = $model->forward($data['observations']);
        echo json_encode(['success' => true, 'result' => $result]);
        exit;
    }
    
    // Algoritmo Viterbi
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
    if (isset($data['action']) && $data['action'] === 'viterbi') {
        if (!isset($data['observations'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No se especificó la secuencia de observaciones']);
            exit;
        }
        
        $result = $model->viterbi($data['observations']);
        echo json_encode(['success' => true, 'result' => $result]);
        exit;
    }
    
    // Info del modelo
    echo json_encode([
        'success' => true,
        'info' => $model->getInfo()
    ]);
<<<<<<< HEAD
}
=======
}
>>>>>>> bd8104b9392cada77b6306706fd249f89b486036
