<?php if ($total_paginas > 1): ?>
    <div class="d-flex justify-content-center mt-4">
        <div class="d-flex justify-content-between align-items-center w-50">

            <!-- Botón Anterior -->
            <div class="text-left">
                <?php if ($pagina_actual > 1): ?>
                    <a class="btn btn-outline-primary" 
                       href="?pagina=<?php echo $pagina_actual - 1; ?>&<?php echo htmlspecialchars(preg_replace('/(^|&)pagina=\d+/', '', $_SERVER['QUERY_STRING'])); ?>">
                        &laquo; Anterior
                    </a>
                <?php else: ?>
                    <span class="btn btn-outline-secondary disabled">&laquo; Anterior</span>
                <?php endif; ?>
            </div>

            <!-- Página actual -->
            <div class="text-center">
                <span class="btn btn-info disabled">
                    Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?>
                </span>
            </div>

            <!-- Botón Siguiente -->
            <div class="text-right">
                <?php if ($pagina_actual < $total_paginas): ?>
                    <a class="btn btn-outline-primary" 
                       href="?pagina=<?php echo $pagina_actual + 1; ?>&<?php echo htmlspecialchars(preg_replace('/(^|&)pagina=\d+/', '', $_SERVER['QUERY_STRING'])); ?>">
                        Siguiente &raquo;
                    </a>
                <?php else: ?>
                    <span class="btn btn-outline-secondary disabled">Siguiente &raquo;</span>
                <?php endif; ?>
            </div>

        </div>
    </div>
<?php endif; ?>
