<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Plataforma Educativa IA'; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Vis.js Network CSS -->
    <link href="https://unpkg.com/vis-network@latest/styles/vis-network.min.css" rel="stylesheet" type="text/css">
    
    <!-- CSS principal -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
    
    <!-- CSS del módulo específico -->
    <?php if (isset($moduleCSS)): ?>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/<?php echo $moduleCSS; ?>.css">
    <?php endif; ?>
</head>
<body data-base-url="<?php echo BASE_URL; ?>">
