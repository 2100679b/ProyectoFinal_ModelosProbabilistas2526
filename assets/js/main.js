/**
 * ==============================================================================
 * JAVASCRIPT PRINCIPAL
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Script principal para funcionalidades comunes
 * ==============================================================================
 */

// ==============================================================================
// CONFIGURACIÓN GLOBAL
// ==============================================================================

const AppConfig = {
    name: 'Modelos Probabilistas',
    version: '1.0.0',
    debug: true, // Cambiar a false en producción
    apiTimeout: 10000 // Timeout para peticiones AJAX en ms
};

// ==============================================================================
// UTILIDADES GENERALES
// ==============================================================================

/**
 * Imprime mensajes de debug en consola
 */
function debug(...args) {
    if (AppConfig.debug) {
        console.log('[DEBUG]', ...args);
    }
}

/**
 * Muestra un mensaje de error en consola
 */
function logError(...args) {
    console.error('[ERROR]', ...args);
}

/**
 * Formatea un número como probabilidad
 */
function formatProbability(prob, decimals = 6) {
    return parseFloat(prob).toFixed(decimals);
}

/**
 * Formatea un número como porcentaje
 */
function formatPercentage(prob, decimals = 2) {
    return (prob * 100).toFixed(decimals) + '%';
}

/**
 * Valida que un valor sea un número entre 0 y 1
 */
function isValidProbability(value) {
    const num = parseFloat(value);
    return !isNaN(num) && num >= 0 && num <= 1;
}

/**
 * Genera un ID único
 */
function generateId() {
    return 'id_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
}

/**
 * Verifica si la librería Vis.js está cargada
 * @param {string} containerId - ID del contenedor para mostrar el error si falla
 * @returns {boolean}
 */
function checkVisLibrary(containerId) {
    if (typeof vis === 'undefined') {
        logError('CRÍTICO: vis.js no está cargado');
        if (containerId) {
            const container = document.getElementById(containerId);
            if (container) {
                container.innerHTML = `
                    <div style="padding: 40px; text-align: center; color: #dc2626; background: #fee2e2; border-radius: 8px; margin: 20px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 3em; margin-bottom: 15px;"></i>
                        <h4>Error de Configuración</h4>
                        <p>La biblioteca de visualización (Vis.js) no está disponible.</p>
                        <small>Verifica que includes/footer.php la esté cargando correctamente.</small>
                    </div>
                `;
            }
        }
        return false;
    }
    return true;
}

// ==============================================================================
// MANEJO DE ALERTAS Y NOTIFICACIONES
// ==============================================================================

/**
 * Muestra una alerta en la página
 */
function showAlert(message, type = 'info', duration = 5000) {
    const icons = {
        'info': 'ℹ️',
        'success': '✅',
        'warning': '⚠️',
        'error': '❌'
    };
    
    // Remover alertas previas para evitar acumulación excesiva
    const existingAlert = document.querySelector('.app-alert');
    if (existingAlert) existingAlert.remove();

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} fade-in app-alert`;
    alert.innerHTML = `${icons[type] || icons.info} ${message}`;
    alert.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 10000;
        max-width: 400px;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        color: white;
        background-color: ${type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#3b82f6'};
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(alert);
    
    // Auto-eliminar después de duration
    if (duration > 0) {
        setTimeout(() => {
            alert.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (alert.parentNode) alert.parentNode.removeChild(alert);
            }, 300);
        }, duration);
    }
    
    return alert;
}

/**
 * Muestra un mensaje de éxito
 */
function showSuccess(message, duration = 3000) {
    return showAlert(message, 'success', duration);
}

/**
 * Muestra un mensaje de error
 */
function showError(message, duration = 5000) {
    return showAlert(message, 'error', duration);
}

/**
 * Muestra un mensaje de advertencia
 */
function showWarning(message, duration = 4000) {
    return showAlert(message, 'warning', duration);
}

/**
 * Muestra un mensaje informativo
 */
function showInfo(message, duration = 3000) {
    return showAlert(message, 'info', duration);
}

// ==============================================================================
// MANEJO DE LOADING
// ==============================================================================

/**
 * Muestra un spinner de carga
 */
function showLoading(container = document.body, message = 'Cargando...') {
    // Si ya existe un loader en este contenedor, no crear otro
    if (container.querySelector('.loading-overlay')) return;

    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p style="color: #333; margin-top: 1rem; font-weight: 600;">${message}</p>
    `;
    
    // Estilos inline para asegurar funcionamiento sin CSS externo
    overlay.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(2px);
        border-radius: inherit;
    `;
    
    // Asegurar que el contenedor tenga posición relativa si es static
    const style = window.getComputedStyle(container);
    if (style.position === 'static') {
        container.style.position = 'relative';
    }
    
    container.appendChild(overlay);
    return overlay;
}

/**
 * Oculta el spinner de carga
 */
function hideLoading(container = document.body) {
    const overlay = container.querySelector('.loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

// ==============================================================================
// VALIDACIÓN DE FORMULARIOS
// ==============================================================================

/**
 * Valida un formulario
 */
function validateForm(formElement) {
    const inputs = formElement.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    let errors = [];
    
    inputs.forEach(input => {
        // Limpiar errores previos
        input.classList.remove('is-invalid');
        
        // Validar campo vacío
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
            errors.push(`El campo "${input.name || 'Desconocido'}" es requerido`);
        }
        
        // Validar tipo number
        if (input.type === 'number' && input.value.trim() !== '') {
            const value = parseFloat(input.value);
            const min = input.min ? parseFloat(input.min) : -Infinity;
            const max = input.max ? parseFloat(input.max) : Infinity;
            
            if (isNaN(value) || value < min || value > max) {
                isValid = false;
                input.classList.add('is-invalid');
                errors.push(`El valor de "${input.name}" debe estar entre ${min} y ${max}`);
            }
        }
    });
    
    return { isValid, errors };
}

/**
 * Limpia errores de validación de un formulario
 */
function clearFormErrors(formElement) {
    formElement.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
}

// ==============================================================================
// PETICIONES AJAX
// ==============================================================================

/**
 * Realiza una petición AJAX
 */
async function ajaxRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };
    
    const finalOptions = { ...defaultOptions, ...options };
    
    try {
        const response = await fetch(url, finalOptions);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        logError('AJAX Error:', error);
        throw error;
    }
}

/**
 * Petición GET
 */
async function get(url, params = {}) {
    const queryString = new URLSearchParams(params).toString();
    const fullUrl = queryString ? `${url}?${queryString}` : url;
    return ajaxRequest(fullUrl, { method: 'GET' });
}

/**
 * Petición POST
 */
async function post(url, data = {}) {
    return ajaxRequest(url, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

// ==============================================================================
// MANEJO DE TABS (GENÉRICO)
// ==============================================================================

/**
 * Inicializa sistema de tabs simple
 * NOTA: Los módulos HMM y Bayesiano usan su propia lógica extendida.
 * Esta función es solo para tabs simples sin lógica adicional.
 */
function initTabs(containerSelector) {
    const container = document.querySelector(containerSelector);
    if (!container) return;
    
    // Verificar si ya tiene manejo de eventos para evitar duplicados
    if (container.dataset.tabsInitialized) return;
    container.dataset.tabsInitialized = "true";
    
    const tabButtons = container.querySelectorAll('.tab-button');
    const tabPanels = container.querySelectorAll('.tab-panel');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = button.dataset.tab;
            
            // Remover active de todos
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanels.forEach(panel => {
                panel.classList.remove('active');
                panel.style.display = 'none';
            });
            
            // Activar el seleccionado
            button.classList.add('active');
            const targetPanel = container.querySelector(`#tab-${targetId}`);
            if (targetPanel) {
                targetPanel.classList.add('active');
                targetPanel.style.display = 'block';
            }
            
            debug('Tab cambiado (Genérico):', targetId);
        });
    });
}

// ==============================================================================
// INICIALIZACIÓN
// ==============================================================================

document.addEventListener('DOMContentLoaded', function() {
    debug(`${AppConfig.name} v${AppConfig.version} inicializado`);
    
    // NOTA: Se ha deshabilitado la auto-inicialización global de tabs
    // para evitar conflictos con los módulos específicos (HMM/Bayesiano)
    // que requieren lógica personalizada al cambiar de pestaña.
    /*
    const tabContainers = document.querySelectorAll('[class*="-tabs"]');
    tabContainers.forEach(container => {
        // Solo inicializar si no es un módulo complejo conocido
        if (!container.classList.contains('hmm-tabs') && !container.classList.contains('bayesian-tabs')) {
            initTabs('.' + container.className.split(' ')[0]);
        }
    });
    */
    
    debug('Aplicación lista');
});

// ==============================================================================
// EXPORTAR FUNCIONES GLOBALES
// ==============================================================================

window.App = {
    config: AppConfig,
    utils: {
        debug,
        logError,
        formatProbability,
        formatPercentage,
        isValidProbability,
        generateId,
        checkVisLibrary
    },
    ui: {
        showAlert,
        showSuccess,
        showError,
        showWarning,
        showInfo,
        showLoading,
        hideLoading,
        initTabs
    },
    validation: {
        validateForm,
        clearFormErrors
    },
    ajax: {
        request: ajaxRequest,
        get,
        post
    }
};

debug('Objeto global App creado:', window.App);