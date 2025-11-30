<?php
/**
 * PROYECTO FINAL - MODELOS PROBABILISTAS
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Facultad de Ingeniería Eléctrica
 * Página Principal
 */

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="hero-section">
    <div class="container">
        <h1 class="main-title">Modelos Probabilistas</h1>
        <p class="subtitle">Implementación de Algoritmos para Modelos Gráficos Probabilistas</p>
        <p class="institution">Universidad Michoacana de San Nicolás de Hidalgo</p>
    </div>
</div>

<div class="container main-content">
    
    <section class="intro-section">
        <h2>Acerca del Proyecto</h2>
        <p>
            Este proyecto implementa algoritmos fundamentales para tres tipos de modelos probabilistas:
            <strong>Redes Bayesianas</strong>, <strong>Cadenas de Markov</strong> y 
            <strong>Modelos Ocultos de Markov (HMM)</strong>.
        </p>
        <p>
            Cada módulo incluye algoritmos de inferencia, visualización interactiva y ejemplos prácticos
            para demostrar su funcionamiento.
        </p>
    </section>

    <section class="modules-section">
        <h2>Módulos Disponibles</h2>
        
        <div class="modules-grid">
            
            <!-- Módulo 1: Redes Bayesianas -->
            <div class="module-card bayesian">
                <div class="module-icon">🔗</div>
                <h3>Redes Bayesianas</h3>
                <p class="module-description">
                    Modelos gráficos que representan relaciones de dependencia probabilística entre variables.
                </p>
                
                <div class="module-features">
                    <h4>Algoritmos implementados:</h4>
                    <ul>
                        <li>✓ Enumeración para inferencia exacta</li>
                        <li>✓ Eliminación de Variables</li>
                        <li>✓ Visualización de la red</li>
                    </ul>
                </div>
                
                <div class="module-examples">
                    <strong>Ejemplos:</strong> Alarma-Terremoto, Red Médica, Diagnóstico de Fallas
                </div>
                
                <a href="<?php echo url('modules/bayesian/index.php'); ?>" class="btn btn-primary">
                    Acceder al módulo →
                </a>
            </div>

            <!-- Módulo 2: Cadenas de Markov -->
            <div class="module-card markov">
                <div class="module-icon">⛓️</div>
                <h3>Cadenas de Markov</h3>
                <p class="module-description">
                    Procesos estocásticos donde el estado futuro depende únicamente del estado presente.
                </p>
                
                <div class="module-features">
                    <h4>Algoritmos implementados:</h4>
                    <ul>
                        <li>✓ Cadenas de Markov de primer orden</li>
                        <li>✓ Matriz de transición de estados</li>
                        <li>✓ Probabilidades estacionarias</li>
                        <li>✓ Visualización del grafo de estados</li>
                    </ul>
                </div>
                
                <div class="module-examples">
                    <strong>Ejemplos:</strong> Predicción del Clima, Comportamiento del Cliente
                </div>
                
                <a href="<?php echo url('modules/markov/index.php'); ?>" class="btn btn-primary">
                    Acceder al módulo →
                </a>
            </div>

            <!-- Módulo 3: HMM -->
            <div class="module-card hmm">
                <div class="module-icon">🔍</div>
                <h3>Modelos Ocultos de Markov</h3>
                <p class="module-description">
                    Modelos estadísticos donde el sistema modelado se asume como un proceso de Markov con estados ocultos.
                </p>
                
                <div class="module-features">
                    <h4>Algoritmos implementados:</h4>
                    <ul>
                        <li>✓ Algoritmo Forward</li>
                        <li>✓ Algoritmo Viterbi</li>
                        <li>✓ Algoritmo Forward-Backward</li>
                        <li>✓ Visualización de estados</li>
                    </ul>
                </div>
                
                <div class="module-examples">
                    <strong>Ejemplos:</strong> Robot y Clima, Reconocimiento de Voz
                </div>
                
                <a href="<?php echo url('modules/hmm/index.php'); ?>" class="btn btn-primary">
                    Acceder al módulo →
                </a>
            </div>
            
        </div>
    </section>

    <section class="features-section">
        <h2>Características del Sistema</h2>
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">💻</div>
                <h4>Interfaz Intuitiva</h4>
                <p>Diseño amigable para facilitar la interacción con los modelos</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📊</div>
                <h4>Visualización Gráfica</h4>
                <p>Representación visual de redes, grafos y secuencias</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">🧮</div>
                <h4>Cálculos Precisos</h4>
                <p>Implementación exacta de algoritmos de inferencia</p>
            </div>
            <div class="feature-item">
                <div class="feature-icon">📚</div>
                <h4>Ejemplos Incluidos</h4>
                <p>Casos de uso prácticos pre-configurados</p>
            </div>
        </div>
    </section>

    <section class="documentation-section">
        <h2>Documentación</h2>
        <div class="doc-links">
            <a href="<?php echo url('docs/manual_usuario.pdf'); ?>" class="doc-link" target="_blank">
                📄 Manual de Usuario
            </a>
            <a href="<?php echo url('docs/documentacion_tecnica.pdf'); ?>" class="doc-link" target="_blank">
                📋 Documentación Técnica
            </a>
            <a href="<?php echo url('docs/ejemplos_uso.md'); ?>" class="doc-link" target="_blank">
                💡 Ejemplos de Uso
            </a>
        </div>
    </section>

    <section class="info-section">
        <h2>Información del Proyecto</h2>
        <div class="info-content">
            <div class="info-item">
                <strong>Institución:</strong> Universidad Michoacana de San Nicolás de Hidalgo
            </div>
            <div class="info-item">
                <strong>Facultad:</strong> Facultad de Ingeniería Eléctrica
            </div>
            <div class="info-item">
                <strong>Carrera:</strong> Ingeniería en Computación
            </div>
            <div class="info-item">
                <strong>Materia:</strong> Modelos Probabilistas
            </div>
            <div class="info-item">
                <strong>Fecha:</strong> Noviembre 2025
            </div>
        </div>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>