<?php
echo "<h1>Test del servidor</h1>";
echo "<p>PHP está funcionando correctamente</p>";
echo "<p>Versión de PHP: " . phpversion() . "</p>";
echo "<p>Directorio actual: " . __DIR__ . "</p>";

// Verificar si config.php existe
if (file_exists('config.php')) {
    echo "<p style='color: green;'>✓ config.php existe</p>";
    require_once 'config.php';
    echo "<p style='color: green;'>✓ config.php cargado correctamente</p>";
    echo "<p>ROOT_PATH: " . ROOT_PATH . "</p>";
    echo "<p>BASE_URL: " . BASE_URL . "</p>";
} else {
    echo "<p style='color: red;'>✗ config.php NO existe</p>";
}

// Verificar carpetas
$folders = ['includes', 'assets', 'modules'];
foreach ($folders as $folder) {
    if (is_dir($folder)) {
        echo "<p style='color: green;'>✓ Carpeta '$folder' existe</p>";
    } else {
        echo "<p style='color: red;'>✗ Carpeta '$folder' NO existe</p>";
    }
}
?>