<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a href="<?php echo BASE_URL; ?>/index.php">
                <i class="fas fa-brain"></i> Modelos Probabilísticos
            </a>
        </div>
        
        <ul class="navbar-menu">
            <li class="<?php echo (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'bayesian') !== false) ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/modules/bayesian/index.php">
                    <i class="fas fa-project-diagram"></i> Redes Bayesianas
                </a>
            </li>
            <li class="<?php echo (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'markov') !== false && strpos($_SERVER['REQUEST_URI'], 'hmm') === false) ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/modules/markov/index.php">
                    <i class="fas fa-sync-alt"></i> Cadenas de Markov
                </a>
            </li>
            <li class="<?php echo (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'hmm') !== false) ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/modules/hmm/index.php">
                    <i class="fas fa-eye-slash"></i> HMM
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL; ?>/index.php">
                    <i class="fas fa-home"></i> Inicio
                </a>
            </li>
        </ul>
    </div>
</nav>
