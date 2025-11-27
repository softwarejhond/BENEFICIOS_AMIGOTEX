<?php
// Información flotante para sede y tipo de entrega (solo para Asesor)
// Clases específicas para evitar conflictos: gift-flow-info-flotante
if ($rol === 'Asesor' && (isset($_SESSION['sede']) || isset($_SESSION['tipo_entrega']))): 
    // Obtener sedes y tipos de entrega de la base de datos
    $sedes = [];
    $tipos_entrega = [];
    
    // Obtener sedes
    $query = mysqli_query($conn, "SELECT nombre FROM sedes");
    while ($row = mysqli_fetch_assoc($query)) {
        if (!empty($row['nombre'])) $sedes[] = $row['nombre'];
    }
    
    // Obtener tipos de entrega
    $queryTipos = mysqli_query($conn, "SELECT nombre FROM tipos_entrega ORDER BY nombre");
    while ($rowTipo = mysqli_fetch_assoc($queryTipos)) {
        $tipos_entrega[] = $rowTipo['nombre'];
    }
?>
    <div class="gift-flow-info-flotante position-fixed" style="right:2rem; bottom:3.8rem; z-index:1050;" 
         id="info-flotante-clickable" role="button" title="Click para actualizar sede y tipo de entrega">
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
            <div class="text-center mt-1" style="font-size:0.8rem; opacity:0.8;">
                <i class="bi bi-pencil-square me-1"></i>Click para editar
            </div>
        </div>
    </div>
    <style>
        .gift-flow-info-flotante {
            font-size: 0.93rem !important;
            pointer-events: auto !important;
            user-select: none;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        .gift-flow-info-flotante:hover {
            transform: scale(1.05);
        }
        .gift-flow-info-content {
            min-width: 180px;
            max-width: 260px;
            font-size: 0.93rem !important;
            line-height: 1.3;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const infoFlotante = document.getElementById('info-flotante-clickable');
        
        if (infoFlotante) {
            infoFlotante.addEventListener('click', function() {
                mostrarModalSedeEntrega();
            });
        }

        function mostrarModalSedeEntrega() {
            // Obtener los datos de sedes y tipos de entrega desde PHP
            const sedes = <?php echo json_encode($sedes); ?>;
            const tipos_entrega = <?php echo json_encode($tipos_entrega); ?>;
            const sedeActual = '<?php echo $_SESSION['sede'] ?? ''; ?>';
            const tipoActual = '<?php echo $_SESSION['tipo_entrega'] ?? ''; ?>';

            // Generar opciones para sedes
            let optionsSede = '<option value="">Selecciona una sede</option>';
            sedes.forEach(function(sede) {
                const selected = (sede === sedeActual) ? 'selected' : '';
                optionsSede += '<option value="' + sede + '" ' + selected + '>' + sede + '</option>';
            });

            // Generar opciones para tipos de entrega
            let optionsTipo = '<option value="">Selecciona un tipo de entrega</option>';
            tipos_entrega.forEach(function(tipo) {
                const selected = (tipo === tipoActual) ? 'selected' : '';
                optionsTipo += '<option value="' + tipo + '" ' + selected + '>' + tipo + '</option>';
            });

            Swal.fire({
                title: 'Actualizar Información',
                html: `
                    <div class="mb-3">
                        <label for="sede-select" class="form-label">Sede:</label>
                        <select id="sede-select" class="form-select">` + optionsSede + `</select>
                    </div>
                    <div class="mb-3">
                        <label for="tipo-entrega-select" class="form-label">Tipo de Entrega:</label>
                        <select id="tipo-entrega-select" class="form-select">` + optionsTipo + `</select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d63384',
                preConfirm: () => {
                    const sede = document.getElementById('sede-select').value;
                    const tipoEntrega = document.getElementById('tipo-entrega-select').value;
                    
                    if (!sede || !tipoEntrega) {
                        Swal.showValidationMessage('Debes seleccionar una sede y un tipo de entrega');
                        return false;
                    }
                    
                    return { sede: sede, tipo_entrega: tipoEntrega };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('sede', result.value.sede);
                    formData.append('tipo_entrega', result.value.tipo_entrega);

                    fetch('controller/setSede.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar los campos en el header si existen
                            const headerSede = document.getElementById('headerSede');
                            const headerTipoEntrega = document.getElementById('headerTipoEntrega');
                            if (headerSede) headerSede.textContent = result.value.sede;
                            if (headerTipoEntrega) headerTipoEntrega.textContent = result.value.tipo_entrega;

                            Swal.fire({
                                icon: 'success',
                                title: 'Actualizado',
                                text: 'Información actualizada correctamente',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Error al actualizar la información'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error de conexión'
                        });
                    });
                }
            });
        }
    });
    </script>
<?php endif; ?>