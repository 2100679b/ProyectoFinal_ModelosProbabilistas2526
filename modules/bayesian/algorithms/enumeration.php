/**
 * Estilos para Módulo de HMM
 * Universidad Michoacana de San Nicolás de Hidalgo
 */

/* ========================================
   VARIABLES CSS
   ======================================== */
:root {
    --hmm-primary: #9333ea;
    --hmm-secondary: #7e22ce;
    --hmm-light: #f3e8ff;
    --hmm-dark: #581c87;
    --hmm-accent: #e9d5ff;
    --text-primary: #1f2937;
    --text-secondary: #6b7280;
    --border-color: #e5e7eb;
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
}

/* ========================================
   CONTENEDOR PRINCIPAL
   ======================================== */
.hmm-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 20px;
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* ========================================
   HEADER MEJORADO
   ======================================== */
.hmm-header {
    background: linear-gradient(135deg, #9333ea 0%, #c026d3 100%);
    color: #ffffff !important;
    padding: 50px 40px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 10px 25px rgba(147, 51, 234, 0.3);
    grid-column: 1 / -1;
    position: relative;
    overflow: hidden;
}

.hmm-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    animation: headerGlow 10s ease-in-out infinite;
    pointer-events: none;
}

@keyframes headerGlow {
    0%, 100% { transform: translate(-25%, -25%); }
    50% { transform: translate(0%, 0%); }
}

.hmm-header h1 {
    font-size: 2.75em;
    margin-bottom: 12px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
    z-index: 1;
    letter-spacing: -0.02em;
    color: #ffffff !important;
}

.hmm-header h1 i {
    font-size: 1.2em;
    filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
    color: #ffffff !important;
}

.hmm-header p {
    font-size: 1.15em;
    opacity: 0.98;
    margin: 0;
    line-height: 1.6;
    position: relative;
    z-index: 1;
    font-weight: 400;
    color: #ffffff !important;
}

/* ========================================
   SIDEBAR
   ======================================== */
.hmm-sidebar {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.sidebar-section {
    margin-bottom: 30px;
}

.sidebar-section:last-child {
    margin-bottom: 0;
}

.sidebar-section h3 {
    color: var(--hmm-primary);
    margin-bottom: 15px;
    font-size: 1.1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--hmm-light);
}

/* Botones de ejemplo */
.example-buttons,
.algorithm-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-example {
    background: #f9fafb;
    border: 2px solid var(--border-color);
    padding: 12px 15px;
    border-radius: 8px;
    text-align: left;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--text-primary);
}

.btn-example:hover {
    background: var(--hmm-light);
    border-color: var(--hmm-primary);
    transform: translateX(5px);
}

.btn-example:active {
    transform: translateX(3px);
}

/* ========================================
   ÁREA PRINCIPAL
   ======================================== */
.hmm-main {
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

/* ========================================
   TABS (General y Resultados)
   ======================================== */
.hmm-tabs {
    display: flex;
    gap: 10px; /* Separación entre botones */
    margin-bottom: 20px;
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 10px; /* Espacio debajo de los tabs */
    background: #f9fafb;
    padding: 0; /* Reset padding del container de tabs si es necesario */
}

.tab-button {
    flex: 1;
    padding: 15px 20px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-bottom: 3px solid transparent;
}

.tab-button:hover {
    background: #f3f4f6;
    color: var(--hmm-primary);
}

.tab-button.active {
    background: white;
    color: var(--hmm-primary);
    border-bottom-color: var(--hmm-primary);
    font-weight: bold;
}

.tab-button i {
    font-size: 1.1em;
}

/* ========================================
   CONTENIDO DE TABS
   ======================================== */
.tab-content {
    padding: 25px;
}

.tab-panel {
    display: none;
}

.tab-panel.active {
    display: block;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.tab-panel h3 {
    color: var(--hmm-secondary);
    margin-bottom: 20px;
    font-size: 1.5em;
    font-weight: 700;
}

/* Estilos específicos para el tab de resultados */
#tab-results {
    padding: 20px;
    min-height: 400px;
}

/* ========================================
   VISUALIZACIÓN
   ======================================== */
.diagram-controls {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid var(--border-color);
}

.hmm-visualization {
    width: 100%;
    height: 500px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    background: #fafafa;
    position: relative;
}

.hmm-info-panel {
    margin-top: 20px;
    padding: 20px;
    background: var(--hmm-light);
    border-radius: 8px;
    border-left: 4px solid var(--hmm-primary);
}

.hmm-info-panel h4 {
    color: var(--hmm-secondary);
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

#hmm-details {
    color: var(--text-primary);
    line-height: 1.8;
}

/* ========================================
   OBSERVACIONES
   ======================================== */
.observation-builder {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.observation-section {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.observation-section h4 {
    color: var(--hmm-secondary);
    margin-bottom: 15px;
    font-size: 1.2em;
    display: flex;
    align-items: center;
    gap: 8px;
}

.observation-selector,
.observation-sequence {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    min-height: 60px;
    padding: 15px;
    background: white;
    border-radius: 6px;
    border: 2px dashed var(--border-color);
}

/* ========================================
   ALERTAS
   ======================================== */
.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
}

.alert i {
    font-size: 1.2em;
}

.alert-info {
    background: var(--hmm-light);
    border-left: 4px solid var(--hmm-primary);
    color: var(--hmm-secondary);
}

.alert-success {
    background: #d1fae5;
    border-left: 4px solid #10b981;
    color: #065f46;
}

.alert-warning {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    color: #92400e;
}

.alert-error {
    background: #fee2e2;
    border-left: 4px solid #ef4444;
    color: #991b1b;
}

/* ========================================
   UTILIDADES
   ======================================== */
.text-muted {
    color: var(--text-secondary);
    font-style: italic;
}

.text-center {
    text-align: center;
}

/* ========================================
   RESPONSIVE
   ======================================== */
@media (max-width: 1024px) {
    .hmm-container {
        grid-template-columns: 1fr;
    }
    
    .hmm-sidebar {
        position: relative;
        top: 0;
    }
}

@media (max-width: 768px) {
    .hmm-header {
        padding: 35px 25px;
    }
    
    .hmm-header h1 {
        font-size: 2em;
    }
    
    .hmm-tabs {
        overflow-x: auto;
    }
    
    .tab-button {
        flex: none;
        min-width: 120px;
        font-size: 0.9em;
    }
    
    .tab-content {
        padding: 15px;
    }
    
    .hmm-visualization {
        height: 400px;
    }
}

@media (max-width: 480px) {
    .hmm-container {
        padding: 10px;
    }
    
    .hmm-header {
        padding: 25px 20px;
    }
    
    .hmm-header h1 {
        font-size: 1.6em;
        flex-direction: column;
        gap: 10px;
    }
    
    .hmm-visualization {
        height: 300px;
    }
}