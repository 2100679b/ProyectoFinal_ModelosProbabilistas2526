<?php
/**
 * Configuración general del proyecto
 * Modelos Probabilísticos - UMSNH
 */

// ========================================
// CONFIGURACIÓN DE ERRORES
// ========================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ========================================
// ZONA HORARIA
// ========================================
date_default_timezone_set('America/Mexico_City');

// ========================================
// RUTAS BASE
// ========================================
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost:8000');

// ========================================
// RUTAS DE MÓDULOS
// ========================================
define('BAYESIAN_PATH', BASE_PATH . '/modules/bayesian');
define('MARKOV_PATH', BASE_PATH . '/modules/markov');
define('HMM_PATH', BASE_PATH . '/modules/hmm');
define('EXAMPLES_PATH', BASE_PATH . '/modules');

// ========================================
// CONFIGURACIÓN DE ALGORITMOS
// ========================================
define('MAX_NODES', 50);
define('MAX_ITERATIONS', 1000);
define('PRECISION', 6);
define('TOLERANCE', 1e-6);

// ========================================
// FUNCIONES AUXILIARES
// ========================================

/**
 * Configurar headers para JSON
 */
function setJsonHeaders() {
    header('Content-Type: application/json; charset=utf-8');
}

/**
 * Enviar respuesta JSON
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Enviar error JSON
 */
function json_error($message, $status_code = 400) {
    json_response([
        'success' => false,
        'error' => $message
    ], $status_code);
}

/**
 * Sanitizar entrada de usuario
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validar matriz estocástica
 */
function validate_stochastic_matrix($matrix) {
    $n = count($matrix);
    
    for ($i = 0; $i < $n; $i++) {
        $sum = array_sum($matrix[$i]);
        if (abs($sum - 1.0) > 0.001) {
            return [
                'valid' => false,
                'row' => $i,
                'sum' => $sum,
                'message' => "La fila $i suma $sum (debe ser 1.0)"
            ];
        }
    }
    
    return ['valid' => true];
}

/**
 * Formatear número con precisión
 */
function format_number($number, $decimals = PRECISION) {
    return round($number, $decimals);
}

/**
 * Validar probabilidad
 */
function validate_probability($value) {
    return is_numeric($value) && $value >= 0 && $value <= 1;
}

/**
 * Normalizar distribución de probabilidad
 */
function normalize_distribution($distribution) {
    $sum = array_sum($distribution);
    
    if ($sum == 0) {
        return array_fill(0, count($distribution), 0);
    }
    
    return array_map(function($val) use ($sum) {
        return $val / $sum;
    }, $distribution);
}

/**
 * Registrar error en log
 */
function log_error($message, $context = []) {
    $log_file = BASE_PATH . '/logs/error.log';
    $log_dir = dirname($log_file);
    
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
    $log_message = "[$timestamp] $message $context_str\n";
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// ========================================
// AUTOLOAD DE CLASES
// ========================================
spl_autoload_register(function ($class_name) {
    $paths = [
        BASE_PATH . '/modules/bayesian/',
        BASE_PATH . '/modules/markov/',
        BASE_PATH . '/modules/hmm/',
        BASE_PATH . '/includes/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ========================================
// INICIALIZACIÓN DE SESIÓN
// ========================================
// Solo iniciar sesión si no está ya iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================================
// VERIFICACIÓN DE DIRECTORIOS
// ========================================
$required_dirs = [
    BASE_PATH . '/logs',
    BASE_PATH . '/assets/css',
    BASE_PATH . '/assets/js',
    BASE_PATH . '/modules/bayesian/examples',
    BASE_PATH . '/modules/markov/examples',
    BASE_PATH . '/modules/hmm/examples'
];

foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
?>
