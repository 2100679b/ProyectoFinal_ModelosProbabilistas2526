<?php
// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Rutas base
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost:8000');

// Rutas de módulos
define('BAYESIAN_PATH', BASE_PATH . '/modules/bayesian');
define('MARKOV_PATH', BASE_PATH . '/modules/markov');
define('HMM_PATH', BASE_PATH . '/modules/hmm');
define('EXAMPLES_PATH', BASE_PATH . '/modules');

// Configuración
define('MAX_NODES', 50);
define('MAX_ITERATIONS', 1000);
define('PRECISION', 6);

// Función para headers JSON
function setJsonHeaders() {
    header('Content-Type: application/json; charset=utf-8');
}

// Función para respuesta JSON
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para error JSON
function json_error($message, $status_code = 400) {
    json_response([
        'success' => false,
        'error' => $message
    ], $status_code);
}

// Autoload de clases
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
