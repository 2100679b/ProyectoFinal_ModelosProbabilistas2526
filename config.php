<?php
/**
 * PROYECTO FINAL - MODELOS PROBABILISTAS
 * Archivo de Configuración Global
 * 
 * Este archivo contiene todas las configuraciones necesarias para que
 * el proyecto funcione en diferentes sistemas operativos (Windows, Linux, Ubuntu)
 * sin necesidad de modificar rutas.
 */

// Evitar acceso directo al archivo
if (!defined('INDEX_ACCESS')) {
    define('INDEX_ACCESS', true);
}

// ============================================================================
// CONFIGURACIÓN DE ERRORES (Desarrollo vs Producción)
// ============================================================================

// Para desarrollo: mostrar todos los errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Para producción, cambia a:
// ini_set('display_errors', 0);
// error_reporting(0);

// ============================================================================
// CONFIGURACIÓN DE ZONA HORARIA
// ============================================================================

date_default_timezone_set('America/Mexico_City');

// ============================================================================
// RUTAS DEL SISTEMA (BACKEND - Para includes y requires)
// ============================================================================

// Separador de directorios (funciona en Windows y Linux)
define('DS', DIRECTORY_SEPARATOR);

// Ruta raíz del proyecto
define('ROOT_PATH', __DIR__);

// Rutas de carpetas principales
define('MODULES_PATH', ROOT_PATH . DS . 'modules');
define('INCLUDES_PATH', ROOT_PATH . DS . 'includes');
define('ASSETS_PATH', ROOT_PATH . DS . 'assets');
define('DOCS_PATH', ROOT_PATH . DS . 'docs');
define('LIB_PATH', ROOT_PATH . DS . 'lib');
define('TESTS_PATH', ROOT_PATH . DS . 'tests');

// Rutas de módulos específicos
define('BAYESIAN_PATH', MODULES_PATH . DS . 'bayesian');
define('MARKOV_PATH', MODULES_PATH . DS . 'markov');
define('HMM_PATH', MODULES_PATH . DS . 'hmm');

// ============================================================================
// URLS PARA EL NAVEGADOR (FRONTEND - Para enlaces, CSS, JS, imágenes)
// ============================================================================

// Detectar protocolo (http o https)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

// Obtener host
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// Obtener ruta base del script
// Convierte backslashes a forward slashes para compatibilidad
if (isset($_SERVER['SCRIPT_NAME'])) {
    $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // Eliminar slash final si existe
    $scriptPath = rtrim($scriptPath, '/');
} else {
    // Fallback para ejecución por línea de comandos
    $scriptPath = '';
}

// URL base del proyecto
define('BASE_URL', $protocol . '://' . $host . $scriptPath);

// URLs de carpetas (para assets, enlaces)
define('ASSETS_URL', BASE_URL . '/assets');
define('MODULES_URL', BASE_URL . '/modules');
define('DOCS_URL', BASE_URL . '/docs');
define('LIB_URL', BASE_URL . '/lib');

// URLs de recursos específicos
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMG_URL', ASSETS_URL . '/img');

// ============================================================================
// CONFIGURACIÓN DE LA APLICACIÓN
// ============================================================================

// Nombre del proyecto
define('APP_NAME', 'Modelos Probabilistas');
define('APP_VERSION', '1.0.0');
define('APP_AUTHOR', 'UMSNH - Ingeniería en Computación');

// Información de la institución
define('INSTITUTION_NAME', 'Universidad Michoacana de San Nicolás de Hidalgo');
define('FACULTY_NAME', 'Facultad de Ingeniería Eléctrica');
define('CAREER_NAME', 'Ingeniería en Computación');
define('SUBJECT_NAME', 'Modelos Probabilistas');

// Fecha del proyecto
define('PROJECT_DATE', 'Noviembre 2025');

// ============================================================================
// CONFIGURACIÓN DE LÍMITES
// ============================================================================

// Límites para redes bayesianas
define('MIN_NODES', 5);     // Mínimo de nodos permitidos
define('MAX_NODES', 15);    // Máximo de nodos permitidos

// Límites para cadenas de Markov
define('MIN_STATES', 2);    // Mínimo de estados
define('MAX_STATES', 10);   // Máximo de estados

// Límites para HMM
define('MIN_HIDDEN_STATES', 2);
define('MAX_HIDDEN_STATES', 8);
define('MIN_OBSERVATIONS', 2);
define('MAX_OBSERVATIONS', 10);

// ============================================================================
// CONFIGURACIÓN DE PRECISIÓN NUMÉRICA
// ============================================================================

// Decimales para mostrar probabilidades
define('PROBABILITY_DECIMALS', 6);

// Valor mínimo considerado como cero (para evitar errores de redondeo)
define('EPSILON', 1e-10);

// ============================================================================
// CONFIGURACIÓN DE EJEMPLOS
// ============================================================================

// Rutas de archivos de ejemplos
define('BAYESIAN_EXAMPLES_PATH', BAYESIAN_PATH . DS . 'examples');
define('MARKOV_EXAMPLES_PATH', MARKOV_PATH . DS . 'examples');
define('HMM_EXAMPLES_PATH', HMM_PATH . DS . 'examples');

// ============================================================================
// CONFIGURACIÓN DE VISUALIZACIÓN
// ============================================================================

// Opciones para visualización de grafos
define('GRAPH_DEFAULT_WIDTH', 800);
define('GRAPH_DEFAULT_HEIGHT', 600);

// Colores para nodos (puedes cambiarlos)
define('NODE_COLOR_DEFAULT', '#97C2FC');
define('NODE_COLOR_EVIDENCE', '#FFD700');
define('NODE_COLOR_QUERY', '#FF6B6B');
define('NODE_COLOR_HIDDEN', '#C7E9C0');

// ============================================================================
// FUNCIONES DE DEBUG (Opcional)
// ============================================================================

/**
 * Función para debug - imprime variables de forma legible
 * Solo usar en desarrollo
 */
function debug($data, $label = '') {
    echo "<pre style='background:#f4f4f4; padding:10px; border:1px solid #ddd; margin:10px 0;'>";
    if ($label) {
        echo "<strong>DEBUG - $label:</strong>\n";
    }
    print_r($data);
    echo "</pre>";
}

/**
 * Función para debug y detener ejecución
 */
function dd($data, $label = '') {
    debug($data, $label);
    die();
}

// ============================================================================
// AUTOLOAD DE CLASES (Opcional - para cuando crees clases PHP)
// ============================================================================

/**
 * Autoloader simple para cargar clases automáticamente
 * Las clases deben seguir la convención: NombreClase.php
 */
spl_autoload_register(function ($class) {
    // Lista de directorios donde buscar clases
    $directories = [
        MODULES_PATH . DS . 'bayesian',
        MODULES_PATH . DS . 'markov',
        MODULES_PATH . DS . 'hmm',
        INCLUDES_PATH
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . DS . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
});

// ============================================================================
// VERIFICACIÓN DE DIRECTORIOS CRÍTICOS
// ============================================================================

/**
 * Verifica que los directorios principales existan
 * Útil para detectar problemas al mover el proyecto
 */
function verifyDirectories() {
    $criticalDirs = [
        ROOT_PATH => 'Raíz del proyecto',
        MODULES_PATH => 'Carpeta modules',
        INCLUDES_PATH => 'Carpeta includes',
        ASSETS_PATH => 'Carpeta assets'
    ];
    
    $errors = [];
    foreach ($criticalDirs as $dir => $name) {
        if (!is_dir($dir)) {
            $errors[] = "No se encuentra: $name ($dir)";
        }
    }
    
    if (!empty($errors)) {
        die('<h2>Error de configuración</h2><ul><li>' . implode('</li><li>', $errors) . '</li></ul>');
    }
}

// Ejecutar verificación solo en desarrollo
if (ini_get('display_errors')) {
    // verifyDirectories(); // Descomenta si quieres verificación automática
}

// ============================================================================
// CONFIGURACIÓN PERSONALIZADA (Opcional)
// ============================================================================

// Si existe un archivo de configuración local, cargarlo
// Útil para tener configuraciones diferentes en cada máquina
if (file_exists(ROOT_PATH . DS . 'config_local.php')) {
    require_once ROOT_PATH . DS . 'config_local.php';
}

// ============================================================================
// INFORMACIÓN DE DEBUG (Solo en desarrollo)
// ============================================================================

// Descomentar para ver configuración al cargar cualquier página
/*
if (ini_get('display_errors')) {
    echo "<!-- DEBUG INFO\n";
    echo "ROOT_PATH: " . ROOT_PATH . "\n";
    echo "BASE_URL: " . BASE_URL . "\n";
    echo "ASSETS_URL: " . ASSETS_URL . "\n";
    echo "-->";
}
*/

?>