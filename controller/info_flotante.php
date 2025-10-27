<?php
// Información flotante para sede y tipo de entrega (solo para Asesor)
// Clases específicas para evitar conflictos: gift-flow-info-flotante
if ($rol === 'Asesor' && (isset($_SESSION['sede']) || isset($_SESSION['tipo_entrega']))): ?>
    <div class="gift-flow-info-flotante position-fixed" style="right:2rem; bottom:3.8rem; z-index:1050;">
        <div class="bg-magenta-dark border border-magenta-dark rounded text-white p-2 shadow-lg gift-flow-info-content text-start">
            <div class="mb-1" style="font-size:0.93rem;">
                <i class="bi bi-geo-alt-fill me-1"></i>
                <strong>Sede:</strong>
                <span><?php echo htmlspecialchars($_SESSION['sede'] ?? 'No definida'); ?></span>
            </div>
            <div style="font-size:0.93rem;">
                <i class="bi bi-gift-fill me-1"></i>
                <strong>Regalo:</strong>
                <span><?php echo htmlspecialchars($_SESSION['tipo_entrega'] ?? 'No definido'); ?></span>
            </div>
        </div>
    </div>
    <style>
        .gift-flow-info-flotante {
            font-size: 0.93rem !important;
            pointer-events: none;
            user-select: none;
        }
        .gift-flow-info-content {
            min-width: 180px;
            max-width: 260px;
            font-size: 0.93rem !important;
            line-height: 1.3;
        }
    </style>
<?php endif; ?>