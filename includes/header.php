<?php
/**
 * ==============================================================================
 * HEADER - ENCABEZADO HTML
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Archivo: includes/header.php
 * ==============================================================================
 */

// Obtener el título de la página (puede ser pasado como variable)
$pageTitle = isset($pageTitle) ? $pageTitle : 'Modelos Probabilistas';
$pageDescription = isset($pageDescription) ? $pageDescription : 'Proyecto de Redes Bayesianas, Cadenas de Markov y Modelos Ocultos de Markov';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Título y Descripción -->
    <title><?php echo $pageTitle; ?> | UMSNH</title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="redes bayesianas, cadenas de markov, modelos ocultos de markov, probabilidad, inteligencia artificial">
    <meta name="author" content="Universidad Michoacana de San Nicolás de Hidalgo">
    
    <!-- Open Graph Meta Tags para redes sociales -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:type" content="website">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.ico">
    
    <!-- Estilos CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <?php
    // Cargar estilos específicos según la página
    if ($currentPage === 'bayesian.php' || $currentPage === 'bayesian.html') {
        echo '<link rel="stylesheet" href="assets/css/bayesian.css">';
    } elseif ($currentPage === 'markov.php' || $currentPage === 'markov.html') {
        echo '<link rel="stylesheet" href="assets/css/markov.css">';
    } elseif ($currentPage === 'hmm.php' || $currentPage === 'hmm.html') {
        echo '<link rel="stylesheet" href="assets/css/hmm.css">';
    }
    ?>
    
    <!-- Librerías externas -->
    <!-- Vis.js para visualización de redes -->
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <link href="https://unpkg.com/vis-network/styles/vis-network.min.css" rel="stylesheet" type="text/css">
    
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts (opcional) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php
    // Incluir la barra de navegación
    include_once 'navbar.php';
    ?>
    
    <!-- Inicio del contenido principal -->
    <main class="main-content">