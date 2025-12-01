/*
 * ==============================================================================
 * PROYECTO FINAL - MODELOS PROBABILISTAS
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Estilos Principales (style.css)
 * ==============================================================================
 */

/* ==============================================================================
 * VARIABLES CSS (Para fácil personalización)
 * ==============================================================================
 */
:root {
    /* Colores principales */
    --primary-color: #2c3e50;
    --secondary-color: #3498db;
    --accent-color: #e74c3c;
    --success-color: #27ae60;
    --warning-color: #f39c12;
    --info-color: #16a085;
    
    /* Colores de fondo */
    --bg-primary: #ffffff;
    --bg-secondary: #f8f9fa;
    --bg-dark: #2c3e50;
    --bg-light: #ecf0f1;
    
    /* Colores de texto */
    --text-primary: #2c3e50;
    --text-secondary: #7f8c8d;
    --text-light: #ffffff;
    --text-muted: #95a5a6;
    
    /* Colores de módulos */
    --bayesian-color: #3498db;
    --markov-color: #9b59b6;
    --hmm-color: #16a085;
    
    /* Espaciado */
    --spacing-xs: 0.25rem;
    --spacing-sm: 0.5rem;
    --spacing-md: 1rem;
    --spacing-lg: 1.5rem;
    --spacing-xl: 2rem;
    --spacing-xxl: 3rem;
    
    /* Tipografía */
    --font-primary: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    --font-mono: 'Courier New', Courier, monospace;
    --font-size-base: 16px;
    --font-size-sm: 0.875rem;
    --font-size-lg: 1.125rem;
    --font-size-xl: 1.25rem;
    
    /* Bordes */
    --border-radius: 8px;
    --border-radius-sm: 4px;
    --border-radius-lg: 12px;
    
    /* Sombras */
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 20px rgba(0, 0, 0, 0.15);
    --shadow-xl: 0 20px 40px rgba(0, 0, 0, 0.2);
    
    /* Transiciones */
    --transition-fast: 0.15s ease;
    --transition-normal: 0.3s ease;
    --transition-slow: 0.5s ease;
}

/* ==============================================================================
 * RESET Y BASE
 * ==============================================================================
 */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    font-size: var(--font-size-base);
    scroll-behavior: smooth;
}

body {
    font-family: var(--font-primary);
    color: var(--text-primary);
    background-color: var(--bg-secondary);
    line-height: 1.6;
    overflow-x: hidden;
}

/* ==============================================================================
 * TIPOGRAFÍA
 * ==============================================================================
 */
h1, h2, h3, h4, h5, h6 {
    font-weight: 600;
    line-height: 1.2;
    margin-bottom: var(--spacing-md);
    color: var(--primary-color);
}

h1 { font-size: 2.5rem; }
h2 { font-size: 2rem; }
h3 { font-size: 1.75rem; }
h4 { font-size: 1.5rem; }
h5 { font-size: 1.25rem; }
h6 { font-size: 1rem; }

p {
    margin-bottom: var(--spacing-md);
    line-height: 1.7;
}

a {
    color: var(--secondary-color);
    text-decoration: none;
    transition: color var(--transition-fast);
}

a:hover {
    color: var(--primary-color);
    text-decoration: underline;
}

strong, b {
    font-weight: 600;
}

code {
    font-family: var(--font-mono);
    background-color: var(--bg-light);
    padding: 2px 6px;
    border-radius: var(--border-radius-sm);
    font-size: 0.9em;
}

/* ==============================================================================
 * CONTENEDORES
 * ==============================================================================
 */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-lg);
}

.container-fluid {
    width: 100%;
    padding: 0 var(--spacing-lg);
}

/* ==============================================================================
 * NAVBAR (Barra de navegación)
 * ==============================================================================
 */
.navbar {
    background-color: var(--bg-dark);
    color: var(--text-light);
    padding: var(--spacing-md) 0;
    box-shadow: var(--shadow-md);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.navbar .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-brand {
    font-size: 1.5rem;
    font-weight: 700;
}

.nav-brand a {
    color: var(--text-light);
    text-decoration: none;
}

.nav-menu {
    display: flex;
    list-style: none;
    gap: var(--spacing-lg);
    align-items: center;
}

.nav-menu li a {
    color: var(--text-light);
    text-decoration: none;
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--border-radius-sm);
    transition: background-color var(--transition-fast);
}

.nav-menu li a:hover {
    background-color: rgba(255, 255, 255, 0.1);
    text-decoration: none;
}

/* ==============================================================================
 * HERO SECTION (Sección principal)
 * ==============================================================================
 */
.hero-section {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: var(--text-light);
    padding: var(--spacing-xxl) 0;
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.hero-section .main-title {
    color: var(--text-light);
    font-size: 3rem;
    margin-bottom: var(--spacing-md);
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.hero-section .subtitle {
    font-size: 1.25rem;
    margin-bottom: var(--spacing-sm);
    opacity: 0.95;
}

.hero-section .institution {
    font-size: 1rem;
    opacity: 0.85;
}

/* ==============================================================================
 * SECCIONES DE CONTENIDO
 * ==============================================================================
 */
.main-content {
    margin-bottom: var(--spacing-xxl);
}

.intro-section,
.modules-section,
.features-section,
.documentation-section,
.info-section {
    background-color: var(--bg-primary);
    padding: var(--spacing-xl);
    margin-bottom: var(--spacing-xl);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-md);
}

.intro-section h2,
.modules-section h2,
.features-section h2,
.documentation-section h2,
.info-section h2 {
    border-bottom: 3px solid var(--secondary-color);
    padding-bottom: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

/* ==============================================================================
 * GRID DE MÓDULOS
 * ==============================================================================
 */
.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: var(--spacing-xl);
    margin-top: var(--spacing-xl);
}

.module-card {
    background-color: var(--bg-primary);
    border-radius: var(--border-radius-lg);
    padding: var(--spacing-xl);
    box-shadow: var(--shadow-md);
    transition: transform var(--transition-normal), box-shadow var(--transition-normal);
    border-top: 5px solid var(--secondary-color);
    display: flex;
    flex-direction: column;
}

.module-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.module-card.bayesian {
    border-top-color: var(--bayesian-color);
}

.module-card.markov {
    border-top-color: var(--markov-color);
}

.module-card.hmm {
    border-top-color: var(--hmm-color);
}

.module-icon {
    font-size: 3rem;
    margin-bottom: var(--spacing-md);
}

.module-card h3 {
    color: var(--primary-color);
    margin-bottom: var(--spacing-md);
}

.module-description {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-lg);
    flex-grow: 1;
}

.module-features {
    margin-bottom: var(--spacing-lg);
}

.module-features h4 {
    font-size: 1rem;
    margin-bottom: var(--spacing-sm);
    color: var(--text-secondary);
}

.module-features ul {
    list-style: none;
    padding-left: 0;
}

.module-features ul li {
    padding: var(--spacing-xs) 0;
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.module-examples {
    background-color: var(--bg-light);
    padding: var(--spacing-md);
    border-radius: var(--border-radius-sm);
    margin-bottom: var(--spacing-lg);
    font-size: 0.9rem;
    color: var(--text-secondary);
}

/* ==============================================================================
 * BOTONES
 * ==============================================================================
 */
.btn {
    display: inline-block;
    padding: var(--spacing-sm) var(--spacing-lg);
    border-radius: var(--border-radius);
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all var(--transition-normal);
    font-size: 1rem;
}

.btn-primary {
    background-color: var(--secondary-color);
    color: var(--text-light);
}

.btn-primary:hover {
    background-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    text-decoration: none;
}

.btn-secondary {
    background-color: var(--text-secondary);
    color: var(--text-light);
}

.btn-secondary:hover {
    background-color: var(--primary-color);
}

.btn-success {
    background-color: var(--success-color);
    color: var(--text-light);
}

.btn-warning {
    background-color: var(--warning-color);
    color: var(--text-light);
}

.btn-danger {
    background-color: var(--accent-color);
    color: var(--text-light);
}

.btn-back {
    background-color: var(--bg-light);
    color: var(--text-primary);
    padding: var(--spacing-sm) var(--spacing-md);
    font-size: 0.9rem;
}

.btn-back:hover {
    background-color: var(--text-secondary);
    color: var(--text-light);
}

/* ==============================================================================
 * GRID DE CARACTERÍSTICAS
 * ==============================================================================
 */
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-xl);
}

.feature-item {
    text-align: center;
    padding: var(--spacing-lg);
    background-color: var(--bg-light);
    border-radius: var(--border-radius);
    transition: transform var(--transition-normal);
}

.feature-item:hover {
    transform: translateY(-3px);
}

.feature-icon {
    font-size: 2.5rem;
    margin-bottom: var(--spacing-md);
}

.feature-item h4 {
    color: var(--primary-color);
    margin-bottom: var(--spacing-sm);
}

.feature-item p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 0;
}

/* ==============================================================================
 * DOCUMENTACIÓN
 * ==============================================================================
 */
.doc-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-xl);
}

.doc-link {
    display: flex;
    align-items: center;
    padding: var(--spacing-lg);
    background-color: var(--bg-light);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--secondary-color);
    transition: all var(--transition-normal);
    font-weight: 600;
}

.doc-link:hover {
    background-color: var(--secondary-color);
    color: var(--text-light);
    text-decoration: none;
    transform: translateX(5px);
}

/* ==============================================================================
 * INFORMACIÓN
 * ==============================================================================
 */
.info-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
}

.info-item {
    padding: var(--spacing-md);
    background-color: var(--bg-light);
    border-radius: var(--border-radius-sm);
}

.info-item strong {
    color: var(--primary-color);
    display: block;
    margin-bottom: var(--spacing-xs);
}

/* ==============================================================================
 * FOOTER
 * ==============================================================================
 */
.footer {
    background-color: var(--bg-dark);
    color: var(--text-light);
    padding: var(--spacing-xl) 0;
    margin-top: var(--spacing-xxl);
}

.footer-content {
    text-align: center;
}

.footer-content p {
    margin-bottom: var(--spacing-sm);
    opacity: 0.9;
}

.footer-links {
    display: flex;
    justify-content: center;
    gap: var(--spacing-lg);
    margin-top: var(--spacing-md);
    flex-wrap: wrap;
}

.footer-links a {
    color: var(--text-light);
    opacity: 0.8;
    transition: opacity var(--transition-fast);
}

.footer-links a:hover {
    opacity: 1;
}

/* ==============================================================================
 * UTILIDADES
 * ==============================================================================
 */
.text-center { text-align: center; }
.text-left { text-align: left; }
.text-right { text-align: right; }

.mt-1 { margin-top: var(--spacing-sm); }
.mt-2 { margin-top: var(--spacing-md); }
.mt-3 { margin-top: var(--spacing-lg); }
.mt-4 { margin-top: var(--spacing-xl); }

.mb-1 { margin-bottom: var(--spacing-sm); }
.mb-2 { margin-bottom: var(--spacing-md); }
.mb-3 { margin-bottom: var(--spacing-lg); }
.mb-4 { margin-bottom: var(--spacing-xl); }

.p-1 { padding: var(--spacing-sm); }
.p-2 { padding: var(--spacing-md); }
.p-3 { padding: var(--spacing-lg); }
.p-4 { padding: var(--spacing-xl); }

/* ==============================================================================
 * RESPONSIVE DESIGN
 * ==============================================================================
 */

/* Tablets */
@media (max-width: 768px) {
    .hero-section .main-title {
        font-size: 2rem;
    }
    
    .nav-menu {
        flex-direction: column;
        gap: var(--spacing-sm);
    }
    
    .modules-grid,
    .features-grid,
    .doc-links {
        grid-template-columns: 1fr;
    }
    
    .container {
        padding: 0 var(--spacing-md);
    }
}

/* Móviles */
@media (max-width: 480px) {
    .hero-section .main-title {
        font-size: 1.75rem;
    }
    
    .hero-section {
        padding: var(--spacing-xl) 0;
    }
    
    h1 { font-size: 2rem; }
    h2 { font-size: 1.5rem; }
    h3 { font-size: 1.25rem; }
    
    .module-card,
    .intro-section,
    .modules-section,
    .features-section {
        padding: var(--spacing-lg);
    }
}

/* ==============================================================================
 * ANIMACIONES
 * ==============================================================================
 */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn var(--transition-slow) ease-out;
}

/* ==============================================================================
 * MODO OSCURO (Opcional - para implementar después)
 * ==============================================================================
 */
/*
@media (prefers-color-scheme: dark) {
    :root {
        --bg-primary: #1a1a1a;
        --bg-secondary: #2c2c2c;
        --text-primary: #f0f0f0;
        --text-secondary: #b0b0b0;
    }
}
*/