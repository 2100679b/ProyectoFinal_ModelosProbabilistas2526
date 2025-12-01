<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Modelos Probabilísticos'; ?></title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/style.css">
    <?php if (isset($moduleCSS)): ?>
        <link rel="stylesheet" href="<?php echo defined('BASE_URL') ? BASE_URL : ''; ?>/assets/css/<?php echo $moduleCSS; ?>.css">
    <?php endif; ?>
    
    <!-- Vis.js para visualización de grafos -->
    <link rel="stylesheet" href="https://unpkg.com/vis-network/styles/vis-network.min.css">
    <script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <meta name="description" content="Sistema interactivo para explorar Redes Bayesianas, Cadenas de Markov y Modelos Ocultos de Markov">
    <meta name="keywords" content="inteligencia artificial, probabilidad, redes bayesianas, markov, HMM">
    <meta name="author" content="UMSNH - Facultad de Ingeniería Eléctrica">
</head>
<body>
