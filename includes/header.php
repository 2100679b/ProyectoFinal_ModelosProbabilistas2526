<?php
/**
 * ==============================================================================
 * HEADER - Encabezado HTML
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Archivo de encabezado común para todas las páginas
 * ==============================================================================
 */

// Verificar que config.php esté cargado
if (!defined('ROOT_PATH')) {
    die('Error: config.php debe ser incluido primero');
}

// Detectar el módulo actual basado en la ruta
$currentPath = $_SERVER['PHP_SELF'];
$currentModule = '';
$pageTitle = APP_NAME;

if (strpos($currentPath, '/bayesian/') !== false) {
    $currentModule = 'bayesian';
    $pageTitle = 'Redes Bayesianas - ' . APP_NAME;
} elseif (strpos($currentPath, '/markov/') !== false) {
    $currentModule = 'markov';
    $pageTitle = 'Cadenas de Markov - ' . APP_NAME;
} elseif (strpos($currentPath, '/hmm/') !== false) {
    $currentModule = 'hmm';
    $pageTitle = 'Modelos Ocultos de Markov - ' . APP_NAME;
}

// Permitir sobreescribir el título desde la página
if (isset($customPageTitle)) {
    $pageTitle = $customPageTitle . ' - ' . APP_NAME;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Título -->
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Meta descripción -->
    <meta name="description" content="Implementación de algoritmos para Redes Bayesianas, Cadenas de Markov y Modelos Ocultos de Markov">
    <meta name="keywords" content="redes bayesianas, cadenas de markov, hmm, modelos probabilistas, UMSNH">
    <meta name="author" content="<?php echo APP_AUTHOR; ?>">
    
    <!-- Favicon (opcional - crear después) -->
    <link rel="icon" type="image/png" href="<?php echo img('favicon.png'); ?>">
    
    <!-- CSS Principal -->
    <link rel="stylesheet" href="<?php echo css('style.css'); ?>">
    
    <!-- CSS Específico del módulo -->
    <?php if ($currentModule): ?>
        <link rel="stylesheet" href="<?php echo css($currentModule . '.css'); ?>">
    <?php endif; ?>
    
    <!-- CSS Adicional (si se define en la página) -->
    <?php if (isset($additionalCSS) && is_array($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $cssFile): ?>
            <link rel="stylesheet" href="<?php echo css($cssFile); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Vis.js para visualización de grafos (solo si se necesita) -->
    <?php if (isset($useVisNetwork) && $useVisNetwork): ?>
        <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
        <style>
            /* Estilos para vis-network */
            .vis-network {
                border: 2px solid #e0e0e0;
                border-radius: 8px;
            }
        </style>
    <?php endif; ?>
    
    <!-- Chart.js para gráficas (solo si se necesita) -->
    <?php if (isset($useCharts) && $useCharts): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php endif; ?>
    
    <!-- JavaScript personalizado en el head (si se define) -->
    <?php if (isset($headScripts) && is_array($headScripts)): ?>
        <?php foreach ($headScripts as $script): ?>
            <script src="<?php echo js($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Estilos inline adicionales (si se definen) -->
    <?php if (isset($inlineStyles)): ?>
        <style>
            <?php echo $inlineStyles; ?>
        </style>
    <?php endif; ?>
</head>
<body class="<?php echo $currentModule ? 'module-' . $currentModule : 'page-home'; ?>">

    <!-- Navbar -->
    <?php 
    // Incluir navbar si existe
    $navbarPath = includePath('includes/navbar.php');
    if (file_exists($navbarPath)) {
        require_once $navbarPath;
    }
    ?>
    
    <!-- Contenedor principal -->
    <main class="main-wrapper">