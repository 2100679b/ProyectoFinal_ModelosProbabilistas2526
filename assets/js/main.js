/**
 * ==============================================================================
 * PROYECTO FINAL - FUNCIONES GENERALES
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Archivo: main.js
 * ==============================================================================
 */

// ==============================================================================
// INICIALIZACIÓN DEL PROYECTO
// ==============================================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('Proyecto de Modelos Probabilistas cargado');
    initializeNavigation();
    initializeSmoothScroll();
    initializeAnimations();
});

// ==============================================================================
// NAVEGACIÓN
// ==============================================================================
function initializeNavigation() {
    // Resaltar elemento activo en la navegación
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';
    const navLinks = document.querySelectorAll('.nav-menu a');
    
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'index.html')) {
            link.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
        }
    });
}

// ==============================================================================
// SCROLL SUAVE
// ==============================================================================
function initializeSmoothScroll() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// ==============================================================================
// ANIMACIONES DE ENTRADA
// ==============================================================================
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observar elementos animables
    const animatableElements = document.querySelectorAll(
        '.module-card, .feature-item, .doc-link'
    );
    
    animatableElements.forEach(el => observer.observe(el));
}

// ==============================================================================
// UTILIDADES MATEMÁTICAS
// ==============================================================================
const MathUtils = {
    /**
     * Redondea un número a n decimales
     */
    round(number, decimals = 4) {
        return Math.round(number * Math.pow(10, decimals)) / Math.pow(10, decimals);
    },

    /**
     * Normaliza un vector de probabilidades
     */
    normalize(vector) {
        const sum = vector.reduce((a, b) => a + b, 0);
        return sum > 0 ? vector.map(v => v / sum) : vector;
    },

    /**
     * Multiplica dos matrices
     */
    multiplyMatrices(A, B) {
        const rowsA = A.length;
        const colsA = A[0].length;
        const colsB = B[0].length;
        
        const result = Array(rowsA).fill(null).map(() => Array(colsB).fill(0));
        
        for (let i = 0; i < rowsA; i++) {
            for (let j = 0; j < colsB; j++) {
                for (let k = 0; k < colsA; k++) {
                    result[i][j] += A[i][k] * B[k][j];
                }
            }
        }
        
        return result;
    },

    /**
     * Genera una matriz identidad de tamaño n
     */
    identityMatrix(n) {
        return Array(n).fill(null).map((_, i) => 
            Array(n).fill(0).map((_, j) => i === j ? 1 : 0)
        );
    },

    /**
     * Calcula el producto punto de dos vectores
     */
    dotProduct(a, b) {
        return a.reduce((sum, val, i) => sum + val * b[i], 0);
    },

    /**
     * Genera un número aleatorio entre min y max
     */
    random(min = 0, max = 1) {
        return Math.random() * (max - min) + min;
    },

    /**
     * Muestrea de una distribución de probabilidad discreta
     */
    sampleDiscrete(probabilities) {
        const rand = Math.random();
        let cumulative = 0;
        
        for (let i = 0; i < probabilities.length; i++) {
            cumulative += probabilities[i];
            if (rand < cumulative) {
                return i;
            }
        }
        
        return probabilities.length - 1;
    }
};

// ==============================================================================
// UTILIDADES DE UI
// ==============================================================================
const UIUtils = {
    /**
     * Muestra un mensaje de alerta
     */
    showAlert(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 300);
            }, 3000);
        }
    },

    /**
     * Crea una tabla HTML desde datos
     */
    createTable(headers, rows, className = '') {
        const table = document.createElement('table');
        table.className = className;
        
        // Crear encabezado
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        headers.forEach(header => {
            const th = document.createElement('th');
            th.textContent = header;
            headerRow.appendChild(th);
        });
        thead.appendChild(headerRow);
        table.appendChild(thead);
        
        // Crear cuerpo
        const tbody = document.createElement('tbody');
        rows.forEach(row => {
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
    },

    /**
     * Muestra un spinner de carga
     */
    showLoading(container) {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="spinner"></div>';
        container.appendChild(overlay);
        return overlay;
    },

    /**
     * Oculta el spinner de carga
     */
    hideLoading(overlay) {
        if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
        }
    },

    /**
     * Formatea un número como porcentaje
     */
    formatPercent(value, decimals = 2) {
        return (value * 100).toFixed(decimals) + '%';
    },

    /**
     * Crea un botón con eventos
     */
    createButton(text, onClick, className = 'btn btn-primary') {
        const button = document.createElement('button');
        button.textContent = text;
        button.className = className;
        button.addEventListener('click', onClick);
        return button;
    }
};

// ==============================================================================
// UTILIDADES DE DATOS
// ==============================================================================
const DataUtils = {
    /**
     * Exporta datos a formato JSON y descarga
     */
    exportJSON(data, filename = 'data.json') {
        const json = JSON.stringify(data, null, 2);
        const blob = new Blob([json], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        
        URL.revokeObjectURL(url);
    },

    /**
     * Importa datos desde un archivo JSON
     */
    importJSON(file, callback) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            try {
                const data = JSON.parse(e.target.result);
                callback(data);
            } catch (error) {
                console.error('Error al parsear JSON:', error);
                UIUtils.showAlert('Error al cargar el archivo', 'error');
            }
        };
        
        reader.readAsText(file);
    },

    /**
     * Genera datos sintéticos para pruebas
     */
    generateRandomMatrix(rows, cols, min = 0, max = 1) {
        const matrix = [];
        
        for (let i = 0; i < rows; i++) {
            const row = [];
            for (let j = 0; j < cols; j++) {
                row.push(MathUtils.random(min, max));
            }
            matrix.push(MathUtils.normalize(row));
        }
        
        return matrix;
    },

    /**
     * Clona profundamente un objeto
     */
    deepClone(obj) {
        return JSON.parse(JSON.stringify(obj));
    }
};

// ==============================================================================
// GESTIÓN DE TABS (Pestañas)
// ==============================================================================
class TabManager {
    constructor(tabsContainerId) {
        this.container = document.getElementById(tabsContainerId);
        this.tabs = [];
        this.panels = [];
        this.activeIndex = 0;
    }

    /**
     * Inicializa el sistema de tabs
     */
    initialize() {
        this.tabs = Array.from(this.container.querySelectorAll('.tab-button'));
        this.panels = Array.from(document.querySelectorAll('.tab-panel'));
        
        this.tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => this.switchTab(index));
        });
        
        this.switchTab(0);
    }

    /**
     * Cambia a una pestaña específica
     */
    switchTab(index) {
        // Desactivar todos
        this.tabs.forEach(tab => tab.classList.remove('active'));
        this.panels.forEach(panel => panel.classList.remove('active'));
        
        // Activar el seleccionado
        this.tabs[index].classList.add('active');
        this.panels[index].classList.add('active');
        
        this.activeIndex = index;
    }

    /**
     * Obtiene el índice de la pestaña activa
     */
    getActiveIndex() {
        return this.activeIndex;
    }
}

// ==============================================================================
// VALIDACIÓN DE FORMULARIOS
// ==============================================================================
const FormValidator = {
    /**
     * Valida que un campo no esté vacío
     */
    required(value) {
        return value.trim() !== '';
    },

    /**
     * Valida que un valor sea un número
     */
    isNumber(value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    },

    /**
     * Valida que un número esté en un rango
     */
    inRange(value, min, max) {
        const num = parseFloat(value);
        return num >= min && num <= max;
    },

    /**
     * Valida una probabilidad (entre 0 y 1)
     */
    isProbability(value) {
        return this.isNumber(value) && this.inRange(value, 0, 1);
    },

    /**
     * Valida un array de probabilidades (deben sumar 1)
     */
    isProbabilityDistribution(values, tolerance = 0.001) {
        if (!Array.isArray(values)) return false;
        
        const sum = values.reduce((a, b) => a + parseFloat(b), 0);
        return Math.abs(sum - 1.0) < tolerance;
    }
};

// ==============================================================================
// MANEJO DE ERRORES
// ==============================================================================
window.addEventListener('error', function(e) {
    console.error('Error capturado:', e.error);
    // En producción, aquí podrías enviar el error a un servicio de logging
});

// ==============================================================================
// UTILIDADES DE PERFORMANCE
// ==============================================================================
const PerformanceUtils = {
    /**
     * Mide el tiempo de ejecución de una función
     */
    measureTime(fn, label = 'Operación') {
        const start = performance.now();
        const result = fn();
        const end = performance.now();
        console.log(`${label}: ${(end - start).toFixed(2)}ms`);
        return result;
    },

    /**
     * Debounce: retrasa la ejecución de una función
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    /**
     * Throttle: limita la frecuencia de ejecución de una función
     */
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};

// ==============================================================================
// EXPORTAR UTILIDADES
// ==============================================================================
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        MathUtils,
        UIUtils,
        DataUtils,
        TabManager,
        FormValidator,
        PerformanceUtils
    };
}