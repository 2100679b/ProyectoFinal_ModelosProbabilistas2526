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
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} fade-in`;
    alert.innerHTML = `${icons[type] || icons.info} ${message}`;
    alert.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(alert);
    
    // Auto-eliminar después de duration
    if (duration > 0) {
        setTimeout(() => {
            alert.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => alert.remove(), 300);
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
    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = `
        <div class="spinner"></div>
        <p style="color: #333; margin-top: 1rem; font-weight: 600;">${message}</p>
    `;
    overlay.style.cssText = `
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    `;
    
    container.style.position = 'relative';
    container.appendChild(overlay);
    
    return overlay;
}

/**
 * Oculta el spinner de carga
 */
function hideLoading(overlay) {
    if (overlay && overlay.parentNode) {
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
        input.classList.remove('error');
        
        // Validar campo vacío
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
            errors.push(`El campo "${input.name}" es requerido`);
        }
        
        // Validar tipo number
        if (input.type === 'number') {
            const value = parseFloat(input.value);
            const min = input.min ? parseFloat(input.min) : -Infinity;
            const max = input.max ? parseFloat(input.max) : Infinity;
            
            if (isNaN(value) || value < min || value > max) {
                isValid = false;
                input.classList.add('error');
                errors.push(`El campo "${input.name}" debe estar entre ${min} y ${max}`);
            }
        }
    });
    
    return { isValid, errors };
}

/**
 * Limpia errores de validación de un formulario
 */
function clearFormErrors(formElement) {
    formElement.querySelectorAll('.error').forEach(el => {
        el.classList.remove('error');
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
        },
        timeout: AppConfig.apiTimeout
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
// MANEJO DE TABS
// ==============================================================================

/**
 * Inicializa sistema de tabs
 */
function initTabs(containerSelector) {
    const container = document.querySelector(containerSelector);
    if (!container) return;
    
    const tabButtons = container.querySelectorAll('.tab-button');
    const tabPanels = container.querySelectorAll('.tab-panel');
    
    tabButtons.forEach((button, index) => {
        button.addEventListener('click', () => {
            // Remover active de todos
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanels.forEach(panel => panel.classList.remove('active'));
            
            // Activar el seleccionado
            button.classList.add('active');
            tabPanels[index].classList.add('active');
            
            debug('Tab cambiado:', index);
        });
    });
}

// ==============================================================================
// MANEJO DE TABLAS
// ==============================================================================

/**
 * Crea una tabla HTML a partir de datos
 */
function createTable(data, headers, options = {}) {
    const table = document.createElement('table');
    table.className = options.className || 'data-table';
    
    // Crear thead
    if (headers && headers.length > 0) {
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        
        headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header;
            headerRow.appendChild(th);
        });
        
        thead.appendChild(headerRow);
        table.appendChild(thead);
    }
    
    // Crear tbody
    const tbody = document.createElement('tbody');
    
    data.forEach(row => {
        const tr = document.createElement('tr');
        
        row.forEach(cell => {
            const td = document.createElement('td');
            td.textContent = cell;
            tr.appendChild(td);
        });
        
        tbody.appendChild(tr);
    });
    
    table.appendChild(tbody);
    
    return table;
}

// ==============================================================================
// UTILIDADES DE LOCAL STORAGE
// ==============================================================================

/**
 * Guarda datos en localStorage
 */
function saveToStorage(key, data) {
    try {
        localStorage.setItem(key, JSON.stringify(data));
        return true;
    } catch (error) {
        logError('Error guardando en localStorage:', error);
        return false;
    }
}

/**
 * Recupera datos de localStorage
 */
function loadFromStorage(key, defaultValue = null) {
    try {
        const data = localStorage.getItem(key);
        return data ? JSON.parse(data) : defaultValue;
    } catch (error) {
        logError('Error cargando de localStorage:', error);
        return defaultValue;
    }
}

/**
 * Elimina datos de localStorage
 */
function removeFromStorage(key) {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (error) {
        logError('Error eliminando de localStorage:', error);
        return false;
    }
}

// ==============================================================================
// UTILIDADES DE ARRAYS Y OBJETOS
// ==============================================================================

/**
 * Clona profundamente un objeto
 */
function deepClone(obj) {
    return JSON.parse(JSON.stringify(obj));
}

/**
 * Verifica si dos objetos son iguales
 */
function isEqual(obj1, obj2) {
    return JSON.stringify(obj1) === JSON.stringify(obj2);
}

/**
 * Suma todos los elementos de un array
 */
function sum(array) {
    return array.reduce((acc, val) => acc + val, 0);
}

/**
 * Promedio de un array
 */
function average(array) {
    return array.length > 0 ? sum(array) / array.length : 0;
}

// ==============================================================================
// ANIMACIONES CSS
// ==============================================================================

// Agregar estilos de animación
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .error {
        border-color: #e74c3c !important;
        box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1) !important;
    }
`;
document.head.appendChild(style);

// ==============================================================================
// INICIALIZACIÓN
// ==============================================================================

document.addEventListener('DOMContentLoaded', function() {
    debug(`${AppConfig.name} v${AppConfig.version} inicializado`);
    
    // Inicializar tabs si existen
    const tabContainers = document.querySelectorAll('[class*="-tabs"]');
    tabContainers.forEach(container => {
        initTabs('.' + container.className.split(' ')[0]);
    });
    
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
        deepClone,
        isEqual,
        sum,
        average
    },
    ui: {
        showAlert,
        showSuccess,
        showError,
        showWarning,
        showInfo,
        showLoading,
        hideLoading,
        initTabs,
        createTable
    },
    validation: {
        validateForm,
        clearFormErrors
    },
    ajax: {
        request: ajaxRequest,
        get,
        post
    },
    storage: {
        save: saveToStorage,
        load: loadFromStorage,
        remove: removeFromStorage
    }
};

debug('Objeto global App creado:', window.App);