<?php
/**
 * ==============================================================================
 * FUNCIONES AUXILIARES
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Archivo: includes/functions.php
 * ==============================================================================
 * 
 * Este archivo contiene funciones útiles para todo el proyecto
 */

// Evitar acceso directo al archivo
if (!defined('INCLUDED')) {
    define('INCLUDED', true);
}

/**
 * ==============================================================================
 * FUNCIONES DE SEGURIDAD
 * ==============================================================================
 */

/**
 * Limpia y sanitiza una cadena de texto
 * @param string $data - Datos a limpiar
 * @return string - Datos limpios
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Valida un email
 * @param string $email - Email a validar
 * @return bool - True si es válido
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida un número
 * @param mixed $number - Número a validar
 * @return bool - True si es un número válido
 */
function validateNumber($number) {
    return is_numeric($number);
}

/**
 * Valida un rango numérico
 * @param float $number - Número a validar
 * @param float $min - Valor mínimo
 * @param float $max - Valor máximo
 * @return bool - True si está en el rango
 */
function validateRange($number, $min, $max) {
    return is_numeric($number) && $number >= $min && $number <= $max;
}

/**
 * ==============================================================================
 * FUNCIONES DE UTILIDAD GENERAL
 * ==============================================================================
 */

/**
 * Obtiene la página actual
 * @return string - Nombre del archivo actual
 */
function getCurrentPage() {
    return basename($_SERVER['PHP_SELF']);
}

/**
 * Verifica si una página está activa
 * @param string|array $page - Página(s) a verificar
 * @return bool - True si está activa
 */
function isPageActive($page) {
    $currentPage = getCurrentPage();
    $pages = is_array($page) ? $page : [$page];
    
    foreach ($pages as $p) {
        if ($currentPage === $p || $currentPage === str_replace('.html', '.php', $p)) {
            return true;
        }
    }
    return false;
}

/**
 * Genera una URL completa
 * @param string $path - Ruta relativa
 * @return string - URL completa
 */
function getFullUrl($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseDir = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $baseDir . '/' . $path;
}

/**
 * Redirige a otra página
 * @param string $url - URL de destino
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * ==============================================================================
 * FUNCIONES DE FORMATO
 * ==============================================================================
 */

/**
 * Formatea un número con decimales
 * @param float $number - Número a formatear
 * @param int $decimals - Cantidad de decimales
 * @return string - Número formateado
 */
function formatNumber($number, $decimals = 2) {
    return number_format($number, $decimals, '.', ',');
}

/**
 * Formatea una fecha
 * @param string $date - Fecha a formatear
 * @param string $format - Formato deseado
 * @return string - Fecha formateada
 */
function formatDate($date, $format = 'd/m/Y') {
    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Convierte un array a JSON
 * @param array $data - Array a convertir
 * @param bool $pretty - Si se debe formatear bonito
 * @return string - JSON resultante
 */
function arrayToJson($data, $pretty = false) {
    $options = $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE : JSON_UNESCAPED_UNICODE;
    return json_encode($data, $options);
}

/**
 * Convierte JSON a array
 * @param string $json - JSON a convertir
 * @return array|null - Array resultante o null si hay error
 */
function jsonToArray($json) {
    return json_decode($json, true);
}

/**
 * ==============================================================================
 * FUNCIONES MATEMÁTICAS
 * ==============================================================================
 */

/**
 * Normaliza un array de valores (suma = 1)
 * @param array $values - Valores a normalizar
 * @return array - Valores normalizados
 */
function normalize($values) {
    $sum = array_sum($values);
    if ($sum == 0) return $values;
    
    return array_map(function($v) use ($sum) {
        return $v / $sum;
    }, $values);
}

/**
 * Valida una distribución de probabilidad
 * @param array $probabilities - Array de probabilidades
 * @param float $tolerance - Tolerancia para la suma
 * @return bool - True si es válida
 */
function validateProbabilityDistribution($probabilities, $tolerance = 0.001) {
    // Verificar que todos sean números entre 0 y 1
    foreach ($probabilities as $p) {
        if (!is_numeric($p) || $p < 0 || $p > 1) {
            return false;
        }
    }
    
    // Verificar que sumen aproximadamente 1
    $sum = array_sum($probabilities);
    return abs($sum - 1.0) < $tolerance;
}

/**
 * Valida una matriz estocástica
 * @param array $matrix - Matriz a validar
 * @param float $tolerance - Tolerancia
 * @return bool - True si es válida
 */
function validateStochasticMatrix($matrix, $tolerance = 0.001) {
    foreach ($matrix as $row) {
        if (!validateProbabilityDistribution($row, $tolerance)) {
            return false;
        }
    }
    return true;
}

/**
 * Multiplica dos matrices
 * @param array $a - Primera matriz
 * @param array $b - Segunda matriz
 * @return array|null - Resultado o null si las dimensiones no coinciden
 */
function multiplyMatrices($a, $b) {
    $rowsA = count($a);
    $colsA = count($a[0]);
    $colsB = count($b[0]);
    
    if ($colsA !== count($b)) {
        return null; // Dimensiones incompatibles
    }
    
    $result = array_fill(0, $rowsA, array_fill(0, $colsB, 0));
    
    for ($i = 0; $i < $rowsA; $i++) {
        for ($j = 0; $j < $colsB; $j++) {
            for ($k = 0; $k < $colsA; $k++) {
                $result[$i][$j] += $a[$i][$k] * $b[$k][$j];
            }
        }
    }
    
    return $result;
}

/**
 * ==============================================================================
 * FUNCIONES DE MANEJO DE ARCHIVOS
 * ==============================================================================
 */

/**
 * Lee un archivo JSON
 * @param string $filepath - Ruta del archivo
 * @return array|null - Contenido del archivo o null si hay error
 */
function readJsonFile($filepath) {
    if (!file_exists($filepath)) {
        return null;
    }
    
    $content = file_get_contents($filepath);
    return jsonToArray($content);
}

/**
 * Escribe un archivo JSON
 * @param string $filepath - Ruta del archivo
 * @param array $data - Datos a escribir
 * @param bool $pretty - Si se debe formatear bonito
 * @return bool - True si se escribió correctamente
 */
function writeJsonFile($filepath, $data, $pretty = true) {
    $json = arrayToJson($data, $pretty);
    return file_put_contents($filepath, $json) !== false;
}

/**
 * Crea un directorio si no existe
 * @param string $path - Ruta del directorio
 * @return bool - True si se creó o ya existía
 */
function ensureDirectoryExists($path) {
    if (!is_dir($path)) {
        return mkdir($path, 0755, true);
    }
    return true;
}

/**
 * ==============================================================================
 * FUNCIONES DE DEBUG
 * ==============================================================================
 */

/**
 * Imprime una variable de forma legible
 * @param mixed $var - Variable a imprimir
 * @param bool $return - Si se debe retornar en lugar de imprimir
 * @return string|void
 */
function debugPrint($var, $return = false) {
    $output = '<pre>' . print_r($var, true) . '</pre>';
    
    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}

/**
 * Registra un mensaje en un archivo de log
 * @param string $message - Mensaje a registrar
 * @param string $logFile - Archivo de log
 */
function logMessage($message, $logFile = 'debug.log') {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

/**
 * ==============================================================================
 * FUNCIONES DE SESIÓN (si se necesitan)
 * ==============================================================================
 */

/**
 * Inicia una sesión de forma segura
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_secure' => isset($_SERVER['HTTPS']),
            'cookie_samesite' => 'Strict'
        ]);
    }
}

/**
 * Establece una variable de sesión
 * @param string $key - Clave
 * @param mixed $value - Valor
 */
function setSessionVar($key, $value) {
    startSecureSession();
    $_SESSION[$key] = $value;
}

/**
 * Obtiene una variable de sesión
 * @param string $key - Clave
 * @param mixed $default - Valor por defecto
 * @return mixed - Valor almacenado o default
 */
function getSessionVar($key, $default = null) {
    startSecureSession();
    return $_SESSION[$key] ?? $default;
}

/**
 * Destruye la sesión actual
 */
function destroySession() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
}

/**
 * ==============================================================================
 * FUNCIONES ESPECÍFICAS DEL PROYECTO
 * ==============================================================================
 */

/**
 * Valida los parámetros de una red bayesiana
 * @param array $network - Configuración de la red
 * @return array - ['valid' => bool, 'errors' => array]
 */
function validateBayesianNetwork($network) {
    $errors = [];
    
    // Validar que existan nodos
    if (empty($network['nodes'])) {
        $errors[] = 'La red debe tener al menos un nodo';
    }
    
    // Validar CPTs
    if (isset($network['cpts'])) {
        foreach ($network['cpts'] as $cpt) {
            if (!validateProbabilityDistribution(array_values($cpt['probabilities']))) {
                $errors[] = "CPT inválida para el nodo {$cpt['nodeId']}";
            }
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Valida una cadena de Markov
 * @param array $chain - Configuración de la cadena
 * @return array - ['valid' => bool, 'errors' => array]
 */
function validateMarkovChain($chain) {
    $errors = [];
    
    // Validar que existan estados
    if (empty($chain['states'])) {
        $errors[] = 'La cadena debe tener al menos un estado';
    }
    
    // Validar matriz de transición
    if (isset($chain['transitionMatrix'])) {
        if (!validateStochasticMatrix($chain['transitionMatrix'])) {
            $errors[] = 'La matriz de transición no es estocástica';
        }
    }
    
    // Validar distribución inicial
    if (isset($chain['initialDistribution'])) {
        if (!validateProbabilityDistribution($chain['initialDistribution'])) {
            $errors[] = 'La distribución inicial no es válida';
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Genera datos de ejemplo para pruebas
 * @param string $type - Tipo de modelo (bayesian, markov, hmm)
 * @return array - Datos de ejemplo
 */
function generateExampleData($type) {
    switch ($type) {
        case 'bayesian':
            return [
                'nodes' => [
                    ['id' => 'A', 'name' => 'Nodo A', 'states' => ['true', 'false']],
                    ['id' => 'B', 'name' => 'Nodo B', 'states' => ['true', 'false']]
                ],
                'edges' => [
                    ['from' => 'A', 'to' => 'B']
                ]
            ];
            
        case 'markov':
            return [
                'states' => ['Estado 1', 'Estado 2', 'Estado 3'],
                'transitionMatrix' => [
                    [0.7, 0.2, 0.1],
                    [0.3, 0.5, 0.2],
                    [0.2, 0.3, 0.5]
                ],
                'initialDistribution' => [0.33, 0.34, 0.33]
            ];
            
        case 'hmm':
            return [
                'states' => ['Estado 1', 'Estado 2'],
                'observations' => ['Obs 1', 'Obs 2'],
                'transitionMatrix' => [
                    [0.7, 0.3],
                    [0.4, 0.6]
                ],
                'emissionMatrix' => [
                    [0.9, 0.1],
                    [0.2, 0.8]
                ]
            ];
            
        default:
            return [];
    }
}

/**
 * ==============================================================================
 * FIN DEL ARCHIVO
 * ==============================================================================
 */
?>