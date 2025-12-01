<?php
/**
 * ==============================================================================
 * NAVBAR - Barra de Navegación
 * Universidad Michoacana de San Nicolás de Hidalgo
 * Menú de navegación común para todas las páginas
 * ==============================================================================
 */

// Detectar página actual para marcar el enlace activo
$currentPage = basename($_SERVER['PHP_SELF']);
$currentPath = $_SERVER['PHP_SELF'];

// Función para determinar si un enlace está activo
function isActive($path) {
    global $currentPath;
    return strpos($currentPath, $path) !== false ? 'active' : '';
}
?>

<nav class="navbar">
    <div class="container">
        <!-- Logo/Marca -->
        <div class="nav-brand">
            <a href="<?php echo url('index.php'); ?>">
                🎓 <?php echo APP_NAME; ?>
            </a>
        </div>
        
        <!-- Menú de navegación -->
        <ul class="nav-menu">
            <!-- Inicio -->
            <li class="<?php echo ($currentPage === 'index.php' && strpos($currentPath, '/modules/') === false) ? 'active' : ''; ?>">
                <a href="<?php echo url('index.php'); ?>">
                    🏠 Inicio
                </a>
            </li>
            
            <!-- Redes Bayesianas -->
            <li class="<?php echo isActive('/bayesian/'); ?>">
                <a href="<?php echo url('modules/bayesian/index.php'); ?>">
                    🔗 Redes Bayesianas
                </a>
            </li>
            
            <!-- Cadenas de Markov -->
            <li class="<?php echo isActive('/markov/'); ?>">
                <a href="<?php echo url('modules/markov/index.php'); ?>">
                    ⛓️ Cadenas de Markov
                </a>
            </li>
            
            <!-- Modelos Ocultos de Markov -->
            <li class="<?php echo isActive('/hmm/'); ?>">
                <a href="<?php echo url('modules/hmm/index.php'); ?>">
                    🔍 HMM
                </a>
            </li>
            
            <!-- Documentación -->
            <li class="nav-dropdown">
                <a href="#" class="dropdown-toggle">
                    📚 Documentación ▾
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="<?php echo url('docs/manual_usuario.pdf'); ?>" target="_blank">
                            📄 Manual de Usuario
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo url('docs/documentacion_tecnica.pdf'); ?>" target="_blank">
                            📋 Documentación Técnica
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo url('docs/ejemplos_uso.md'); ?>" target="_blank">
                            💡 Ejemplos de Uso
                        </a>
                    </li>
                </ul>
            </li>
            
            <!-- Acerca de -->
            <li>
                <a href="#" onclick="showAboutModal(); return false;">
                    ℹ️ Acerca de
                </a>
            </li>
        </ul>
        
        <!-- Botón de menú móvil -->
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
            ☰
        </button>
    </div>
</nav>

<!-- Modal "Acerca de" -->
<div id="aboutModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeAboutModal()">&times;</span>
        <h2>Acerca del Proyecto</h2>
        <p><strong><?php echo INSTITUTION_NAME; ?></strong></p>
        <p><?php echo FACULTY_NAME; ?></p>
        <p><?php echo CAREER_NAME; ?></p>
        <hr>
        <p><strong>Materia:</strong> <?php echo SUBJECT_NAME; ?></p>
        <p><strong>Proyecto:</strong> <?php echo APP_NAME; ?></p>
        <p><strong>Versión:</strong> <?php echo APP_VERSION; ?></p>
        <p><strong>Fecha:</strong> <?php echo PROJECT_DATE; ?></p>
        <hr>
        <p><strong>Desarrollado por:</strong></p>
        <ul>
            <li>Estudiante 1: [Nombre completo]</li>
            <li>Estudiante 2: [Nombre completo]</li>
        </ul>
    </div>
</div>

<!-- Estilos adicionales para navbar -->
<style>
/* Dropdown menu */
.nav-dropdown {
    position: relative;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    min-width: 200px;
    padding: 0.5rem 0;
    list-style: none;
    z-index: 1000;
}

.nav-dropdown:hover .dropdown-menu {
    display: block;
}

.dropdown-menu li {
    padding: 0;
}

.dropdown-menu li a {
    display: block;
    padding: 0.75rem 1.5rem;
    color: var(--text-primary);
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.dropdown-menu li a:hover {
    background-color: var(--bg-secondary);
    text-decoration: none;
}

.dropdown-toggle::after {
    content: '';
}

/* Enlace activo */
.nav-menu li.active a {
    background-color: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
}

/* Botón de menú móvil */
.mobile-menu-toggle {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
}

/* Modal */
.modal {
    position: fixed;
    z-index: 2000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background-color: white;
    padding: 2rem;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.modal-content h2 {
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.modal-content hr {
    border: none;
    border-top: 1px solid #e0e0e0;
    margin: 1rem 0;
}

.modal-content ul {
    list-style: none;
    padding-left: 0;
}

.modal-content ul li {
    padding: 0.25rem 0;
}

.close {
    position: absolute;
    right: 1rem;
    top: 1rem;
    font-size: 2rem;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
    line-height: 1;
}

.close:hover {
    color: #000;
}

/* Responsive */
@media (max-width: 768px) {
    .mobile-menu-toggle {
        display: block;
    }
    
    .nav-menu {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: var(--bg-dark);
        flex-direction: column;
        padding: 1rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .nav-menu.active {
        display: flex;
    }
    
    .nav-menu li {
        width: 100%;
    }
    
    .nav-menu li a {
        display: block;
        padding: 1rem;
    }
    
    .dropdown-menu {
        position: static;
        box-shadow: none;
        background-color: rgba(255, 255, 255, 0.1);
        margin-top: 0.5rem;
    }
    
    .dropdown-menu li a {
        color: white;
        padding-left: 2rem;
    }
    
    .nav-dropdown:hover .dropdown-menu {
        display: none;
    }
    
    .nav-dropdown.active .dropdown-menu {
        display: block;
    }
}
</style>

<!-- Scripts para navbar -->
<script>
// Toggle menú móvil
function toggleMobileMenu() {
    const menu = document.querySelector('.nav-menu');
    menu.classList.toggle('active');
}

// Toggle dropdown en móvil
document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
    toggle.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            e.preventDefault();
            this.parentElement.classList.toggle('active');
        }
    });
});

// Mostrar modal "Acerca de"
function showAboutModal() {
    document.getElementById('aboutModal').style.display = 'flex';
}

// Cerrar modal
function closeAboutModal() {
    document.getElementById('aboutModal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('aboutModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Cerrar menú móvil al hacer clic en un enlace
document.querySelectorAll('.nav-menu a').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            document.querySelector('.nav-menu').classList.remove('active');
        }
    });
});
</script>