    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Vis.js Network JS - Solo para módulos que lo necesitan -->
    <?php if (isset($moduleJS) && in_array($moduleJS, ['bayesian', 'markov', 'hmm'])): ?>
        <script src="https://unpkg.com/vis-network@latest/dist/vis-network.min.js"></script>
    <?php endif; ?>
    
    <!-- Script principal -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Script del módulo específico -->
    <?php if (isset($moduleJS) && !empty($moduleJS)): ?>
        <script src="<?php echo BASE_URL; ?>/assets/js/<?php echo $moduleJS; ?>.js"></script>
    <?php endif; ?>
    
</body>
</html>
