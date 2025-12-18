<?php
/**
 * API de Persistencia para Redes Bayesianas
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Guardar, Cargar, Listar y Eliminar redes
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/network.php';

// Iniciar sesión para almacenamiento temporal
session_start();

// Configuración
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Directorio para guardar redes (opcional: almacenamiento en archivos)
define('NETWORKS_DIR', __DIR__ . '/saved_networks/');

// Crear directorio si no existe
if (!file_exists(NETWORKS_DIR)) {
    mkdir(NETWORKS_DIR, 0755, true);
}

/**
 * Procesar petición
 */
try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Leer datos según método
    if ($method === 'POST') {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Intentar leer desde $_POST como fallback
            $data = $_POST;
        }
    } elseif ($method === 'GET') {
        $data = $_GET;
    } else {
        throw new Exception('Método HTTP no soportado');
    }
    
    $action = isset($data['action']) ? $data['action'] : '';
    
    // Enrutar a la acción correspondiente
    switch ($action) {
        case 'save':
            $result = saveNetwork($data);
            break;
            
        case 'load':
            $result = loadNetwork($data);
            break;
            
        case 'list':
            $result = listNetworks($data);
            break;
            
        case 'delete':
            $result = deleteNetwork($data);
            break;
            
        case 'export':
            $result = exportNetwork($data);
            break;
            
        case 'import':
            $result = importNetwork($data);
            break;
            
        default:
            throw new Exception('Acción no válida. Use: save, load, list, delete, export, import');
    }
    
    // Responder exitosamente
    echo json_encode([
        'success' => true,
        'action' => $action,
        'result' => $result
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => DEBUG_MODE ? $e->getTraceAsString() : null
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

// ========== FUNCIONES DE ACCIÓN ==========

/**
 * Guardar red en sesión y opcionalmente en archivo
 * @param array $data Datos de la petición
 * @return array Resultado
 */
function saveNetwork($data) {
    if (!isset($data['network'])) {
        throw new Exception('Falta parámetro "network"');
    }
    
    $networkData = $data['network'];
    
    // Validar estructura básica
    if (!isset($networkData['nodes']) || !isset($networkData['edges'])) {
        throw new Exception('Estructura de red inválida (faltan nodes o edges)');
    }
    
    // Crear instancia para validar
    $network = new BayesianNetwork($networkData);
    $validation = $network->validate();
    
    if (!$validation['valid']) {
        throw new Exception('Red inválida: ' . implode(', ', $validation['errors']));
    }
    
    // Generar nombre único si no se proporciona
    $name = isset($data['name']) ? sanitizeName($data['name']) : generateNetworkName();
    $timestamp = time();
    
    // Preparar metadata
    $metadata = [
        'name' => $name,
        'created_at' => date('Y-m-d H:i:s', $timestamp),
        'modified_at' => date('Y-m-d H:i:s', $timestamp),
        'version' => '1.0',
        'author' => isset($data['author']) ? $data['author'] : 'Anonymous'
    ];
    
    // Estructura completa con metadata
    $fullData = array_merge($networkData, ['metadata' => $metadata]);
    
    // Guardar en sesión
    if (!isset($_SESSION['bayesian_networks'])) {
        $_SESSION['bayesian_networks'] = [];
    }
    $_SESSION['bayesian_networks'][$name] = $fullData;
    
    // Guardar en archivo (opcional pero recomendado)
    $saveToFile = isset($data['save_to_file']) ? (bool)$data['save_to_file'] : true;
    $filepath = null;
    
    if ($saveToFile) {
        $filename = $name . '_' . $timestamp . '.json';
        $filepath = NETWORKS_DIR . $filename;
        
        $jsonData = json_encode($fullData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if (file_put_contents($filepath, $jsonData) === false) {
            throw new Exception('No se pudo guardar el archivo en el servidor');
        }
    }
    
    return [
        'name' => $name,
        'saved_to_session' => true,
        'saved_to_file' => $saveToFile,
        'filepath' => $filepath,
        'metadata' => $metadata,
        'validation' => $validation
    ];
}

/**
 * Cargar red desde sesión o archivo
 * @param array $data Datos de la petición
 * @return array Red cargada
 */
function loadNetwork($data) {
    if (!isset($data['name'])) {
        throw new Exception('Falta parámetro "name"');
    }
    
    $name = sanitizeName($data['name']);
    $source = isset($data['source']) ? $data['source'] : 'session'; // session | file
    
    $networkData = null;
    
    if ($source === 'session') {
        // Cargar desde sesión
        if (!isset($_SESSION['bayesian_networks'][$name])) {
            throw new Exception("Red '$name' no encontrada en sesión");
        }
        $networkData = $_SESSION['bayesian_networks'][$name];
        
    } elseif ($source === 'file') {
        // Cargar desde archivo
        $pattern = NETWORKS_DIR . $name . '_*.json';
        $files = glob($pattern);
        
        if (empty($files)) {
            throw new Exception("Red '$name' no encontrada en archivos");
        }
        
        // Tomar el archivo más reciente
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        $filepath = $files[0];
        $jsonData = file_get_contents($filepath);
        
        if ($jsonData === false) {
            throw new Exception("No se pudo leer el archivo '$filepath'");
        }
        
        $networkData = json_decode($jsonData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Archivo JSON corrupto: ' . json_last_error_msg());
        }
    } else {
        throw new Exception('Source inválido. Use: session o file');
    }
    
    // Validar red cargada
    $network = new BayesianNetwork($networkData);
    $validation = $network->validate();
    
    return [
        'network' => $networkData,
        'source' => $source,
        'validation' => $validation,
        'summary' => $network->getSummary()
    ];
}

/**
 * Listar todas las redes guardadas
 * @param array $data Datos de la petición
 * @return array Lista de redes
 */
function listNetworks($data) {
    $source = isset($data['source']) ? $data['source'] : 'both'; // session | file | both
    $networks = [];
    
    // Listar desde sesión
    if ($source === 'session' || $source === 'both') {
        if (isset($_SESSION['bayesian_networks'])) {
            foreach ($_SESSION['bayesian_networks'] as $name => $netData) {
                $networks[] = [
                    'name' => $name,
                    'source' => 'session',
                    'created_at' => isset($netData['metadata']['created_at']) ? $netData['metadata']['created_at'] : 'Desconocido',
                    'node_count' => count($netData['nodes']),
                    'edge_count' => count($netData['edges'])
                ];
            }
        }
    }
    
    // Listar desde archivos
    if ($source === 'file' || $source === 'both') {
        $files = glob(NETWORKS_DIR . '*.json');
        
        foreach ($files as $filepath) {
            $filename = basename($filepath);
            preg_match('/^(.+)_(\d+)\.json$/', $filename, $matches);
            
            if ($matches) {
                $name = $matches[1];
                $timestamp = $matches[2];
                
                // Leer metadata sin cargar toda la red
                $jsonData = file_get_contents($filepath);
                $netData = json_decode($jsonData, true);
                
                if ($netData) {
                    $networks[] = [
                        'name' => $name,
                        'source' => 'file',
                        'filepath' => $filepath,
                        'created_at' => date('Y-m-d H:i:s', $timestamp),
                        'modified_at' => date('Y-m-d H:i:s', filemtime($filepath)),
                        'node_count' => count($netData['nodes']),
                        'edge_count' => count($netData['edges']),
                        'size_bytes' => filesize($filepath)
                    ];
                }
            }
        }
    }
    
    // Ordenar por fecha de creación (más reciente primero)
    usort($networks, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    return [
        'networks' => $networks,
        'total' => count($networks),
        'source' => $source
    ];
}

/**
 * Eliminar red de sesión y/o archivo
 * @param array $data Datos de la petición
 * @return array Resultado
 */
function deleteNetwork($data) {
    if (!isset($data['name'])) {
        throw new Exception('Falta parámetro "name"');
    }
    
    $name = sanitizeName($data['name']);
    $source = isset($data['source']) ? $data['source'] : 'both'; // session | file | both
    
    $deletedFrom = [];
    
    // Eliminar de sesión
    if ($source === 'session' || $source === 'both') {
        if (isset($_SESSION['bayesian_networks'][$name])) {
            unset($_SESSION['bayesian_networks'][$name]);
            $deletedFrom[] = 'session';
        }
    }
    
    // Eliminar de archivos
    if ($source === 'file' || $source === 'both') {
        $pattern = NETWORKS_DIR . $name . '_*.json';
        $files = glob($pattern);
        
        foreach ($files as $filepath) {
            if (unlink($filepath)) {
                $deletedFrom[] = 'file: ' . basename($filepath);
            }
        }
    }
    
    if (empty($deletedFrom)) {
        throw new Exception("Red '$name' no encontrada");
    }
    
    return [
        'name' => $name,
        'deleted_from' => $deletedFrom,
        'success' => true
    ];
}

/**
 * Exportar red como descarga directa
 * @param array $data Datos de la petición
 * @return array URL de descarga o contenido
 */
function exportNetwork($data) {
    if (!isset($data['network'])) {
        throw new Exception('Falta parámetro "network"');
    }
    
    $networkData = $data['network'];
    $format = isset($data['format']) ? $data['format'] : 'json'; // json | dot | xml
    
    $name = isset($networkData['name']) ? sanitizeName($networkData['name']) : 'red_bayesiana';
    
    switch ($format) {
        case 'json':
            $content = json_encode($networkData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $filename = $name . '.json';
            $mimeType = 'application/json';
            break;
            
        case 'dot':
            // Formato Graphviz DOT
            $content = generateDotFormat($networkData);
            $filename = $name . '.dot';
            $mimeType = 'text/plain';
            break;
            
        case 'xml':
            // Formato XML/XMLBIF
            $content = generateXMLFormat($networkData);
            $filename = $name . '.xml';
            $mimeType = 'application/xml';
            break;
            
        default:
            throw new Exception('Formato no soportado. Use: json, dot, xml');
    }
    
    // Guardar temporalmente para descarga
    $tempFile = NETWORKS_DIR . 'exports/' . $filename;
    
    if (!file_exists(dirname($tempFile))) {
        mkdir(dirname($tempFile), 0755, true);
    }
    
    file_put_contents($tempFile, $content);
    
    return [
        'filename' => $filename,
        'format' => $format,
        'size_bytes' => strlen($content),
        'download_url' => '/modules/bayesian/saved_networks/exports/' . $filename,
        'content' => $content // Para descarga directa via JS
    ];
}

/**
 * Importar red desde archivo subido
 * @param array $data Datos de la petición
 * @return array Red importada
 */
function importNetwork($data) {
    // Verificar si hay archivo subido
    if (!isset($_FILES['file'])) {
        // Intentar desde contenido directo
        if (!isset($data['content'])) {
            throw new Exception('Falta archivo o contenido a importar');
        }
        
        $content = $data['content'];
    } else {
        // Leer desde archivo subido
        $file = $_FILES['file'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir archivo: ' . $file['error']);
        }
        
        $content = file_get_contents($file['tmp_name']);
    }
    
    // Detectar formato
    $format = isset($data['format']) ? $data['format'] : 'json';
    
    if ($format === 'json') {
        $networkData = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON inválido: ' . json_last_error_msg());
        }
    } else {
        throw new Exception('Solo formato JSON soportado por ahora');
    }
    
    // Validar red importada
    $network = new BayesianNetwork($networkData);
    $validation = $network->validate();
    
    if (!$validation['valid']) {
        throw new Exception('Red importada inválida: ' . implode(', ', $validation['errors']));
    }
    
    // Auto-guardar en sesión
    $name = isset($networkData['name']) ? sanitizeName($networkData['name']) : generateNetworkName();
    
    if (!isset($_SESSION['bayesian_networks'])) {
        $_SESSION['bayesian_networks'] = [];
    }
    
    $_SESSION['bayesian_networks'][$name] = $networkData;
    
    return [
        'network' => $networkData,
        'name' => $name,
        'validation' => $validation,
        'auto_saved' => true
    ];
}

// ========== FUNCIONES AUXILIARES ==========

/**
 * Sanitizar nombre de red
 * @param string $name
 * @return string
 */
function sanitizeName($name) {
    // Remover caracteres peligrosos
    $name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
    // Limitar longitud
    $name = substr($name, 0, 50);
    return $name;
}

/**
 * Generar nombre único para red
 * @return string
 */
function generateNetworkName() {
    return 'red_' . date('Ymd_His') . '_' . substr(md5(uniqid()), 0, 6);
}

/**
 * Generar formato DOT (Graphviz)
 * @param array $networkData
 * @return string
 */
function generateDotFormat($networkData) {
    $dot = "digraph BayesianNetwork {\n";
    $dot .= "    rankdir=TB;\n";
    $dot .= "    node [shape=box, style=rounded];\n\n";
    
    // Nodos
    foreach ($networkData['nodes'] as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        $nodeLabel = is_array($node) && isset($node['label']) ? $node['label'] : $nodeId;
        
        $dot .= "    \"$nodeId\" [label=\"$nodeLabel\"];\n";
    }
    
    $dot .= "\n";
    
    // Aristas
    foreach ($networkData['edges'] as $edge) {
        $from = is_array($edge) ? $edge['from'] : $edge[0];
        $to = is_array($edge) ? $edge['to'] : $edge[1];
        
        $dot .= "    \"$from\" -> \"$to\";\n";
    }
    
    $dot .= "}\n";
    
    return $dot;
}

/**
 * Generar formato XML/XMLBIF
 * @param array $networkData
 * @return string
 */
function generateXMLFormat($networkData) {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<BAYESIANNETWORK>' . "\n";
    
    $name = isset($networkData['name']) ? $networkData['name'] : 'Red Bayesiana';
    $xml .= "  <NAME>$name</NAME>\n\n";
    
    // Variables
    $xml .= "  <VARIABLES>\n";
    foreach ($networkData['nodes'] as $node) {
        $nodeId = is_array($node) ? $node['id'] : $node;
        $nodeLabel = is_array($node) && isset($node['label']) ? $node['label'] : $nodeId;
        
        $xml .= "    <VARIABLE>\n";
        $xml .= "      <ID>$nodeId</ID>\n";
        $xml .= "      <LABEL>$nodeLabel</LABEL>\n";
        $xml .= "      <VALUES>\n";
        $xml .= "        <VALUE>True</VALUE>\n";
        $xml .= "        <VALUE>False</VALUE>\n";
        $xml .= "      </VALUES>\n";
        $xml .= "    </VARIABLE>\n";
    }
    $xml .= "  </VARIABLES>\n\n";
    
    // Estructura (aristas)
    $xml .= "  <STRUCTURE>\n";
    foreach ($networkData['edges'] as $edge) {
        $from = is_array($edge) ? $edge['from'] : $edge[0];
        $to = is_array($edge) ? $edge['to'] : $edge[1];
        
        $xml .= "    <EDGE>\n";
        $xml .= "      <FROM>$from</FROM>\n";
        $xml .= "      <TO>$to</TO>\n";
        $xml .= "    </EDGE>\n";
    }
    $xml .= "  </STRUCTURE>\n\n";
    
    // CPT (simplificado)
    $xml .= "  <PROBABILITIES>\n";
    if (isset($networkData['cpt'])) {
        foreach ($networkData['cpt'] as $nodeId => $cpt) {
            $xml .= "    <CPT node=\"$nodeId\">\n";
            foreach ($cpt as $key => $value) {
                if (is_numeric($value)) {
                    $xml .= "      <ENTRY condition=\"$key\" probability=\"$value\"/>\n";
                }
            }
            $xml .= "    </CPT>\n";
        }
    }
    $xml .= "  </PROBABILITIES>\n";
    
    $xml .= '</BAYESIANNETWORK>';
    
    return $xml;
}

// Definir DEBUG_MODE si no está definido
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', false);
}
?>