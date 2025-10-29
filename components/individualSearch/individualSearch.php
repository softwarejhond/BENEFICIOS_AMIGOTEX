<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
$extraRol = $infoUsuario['extra_rol']; // Obtener el extra_rol del usuario

$disableConfirm = in_array($rol, ['Administrador', 'Control maestro']);
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

<div class="container-fluid">
    <!-- Card de búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-indigo-dark text-white text-center">
                    <h5 class="mb-0">Búsqueda por número de identificación</h5>
                </div>
                <div class="card-body">
                    <div class="row w-100">
                        <div class="col-md-12">
                            <div class="input-group justify-content-center">
                                <input type="number" id="searchNumberId" class="form-control text-center" placeholder="Ingresa el número de ID" min="1" required style="font-size: 1.3rem;">
                                <button id="btnBuscar" class="btn bg-indigo-dark text-white" type="button">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Card de resultados -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-lg border-teal-dark" id="resultCard" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center bg-teal-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-user"></i> Información del Usuario</h5>
                    <div>
                        <button id="btnVerEntrega" class="btn bg-purple-dark text-white btn-sm me-2" type="button" style="border: 1px solid #fff; display: none;">
                            <i class="fas fa-eye"></i> Ver Entrega
                        </button>
                        <button id="btnReenviarCorreo" class="btn bg-success text-white btn-sm me-2" type="button" style="border: 1px solid #fff; display: none;">
                            <i class="fas fa-envelope"></i> Reenviar Correo
                        </button>
                        <button id="btnConfirmarEntrega" class="btn bg-purple-dark text-white btn-sm me-2" type="button" style="border: 1px solid #fff;" <?php echo $disableConfirm ? 'disabled' : ''; ?>>
                            <i class="fas fa-gift"></i> Confirmar Entrega
                        </button>
                        <button id="btnEditar" class="btn bg-orange-dark text-white btn-sm me-2" type="button">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button id="btnGuardar" class="btn bg-magenta-dark text-white btn-sm" type="button" style="display: none;">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>
                </div>
                <div class="card-body bg-light">
                    <input type="hidden" id="originalNumberId">
                    <div class="container-fluid">
                        <!-- Primera fila: Número de ID (col-12 col-md-4) y Nombre (col-12 col-md-8) -->
                        <div class="row mb-4 p-2 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-4">
                                <label class="form-label fw-bold text-teal-dark"><i class="fas fa-id-card"></i> Número de ID:</label>
                                <input type="number" id="resultNumberId" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-8">
                                <label class="form-label fw-bold text-teal-dark"><i class="fas fa-user-tag"></i> Nombre:</label>
                                <input type="text" id="resultName" class="form-control border-teal-dark" readonly>
                            </div>
                        </div>
                        <!-- Segunda fila: Celular, Email, Empresa (col-12 col-md-4 cada uno) -->
                        <div class="row mb-4 p-2 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-phone"></i> Celular:</label>
                                <input type="number" id="resultCellPhone" class="form-control border-teal-dark" readonly maxlength="10" oninput="this.value = this.value.slice(0, 10);">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-envelope"></i> Email:</label>
                                <input type="text" id="resultEmail" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-building"></i> Empresa:</label>
                                <input type="text" id="resultCompany" class="form-control border-teal-dark" readonly>
                            </div>
                        </div>
                        <!-- Tercera fila: Dirección, Ciudad, Género, Fecha de Registro (col-12 col-md-3 cada uno) -->
                        <div class="row mb-4 p-2 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-3">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-map-marker-alt"></i> Dirección:</label>
                                <input type="text" id="resultAddress" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-city"></i> Ciudad:</label>
                                <input type="text" id="resultCity" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-venus-mars"></i> Género:</label>
                                <select id="resultGender" class="form-control border-teal-dark" disabled>
                                    <option value="MUJER">Mujer</option>
                                    <option value="HOMBRE">Hombre</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-calendar-alt"></i> Fecha de Registro:</label>
                                <input type="date" id="resultRegistrationDate" class="form-control border-teal-dark" readonly>
                            </div>
                        </div>
                        <!-- Última fila: Data Update, Updated By y Sede (col-12 col-md-4 cada uno) -->
                        <div class="row mb-2 justify-content-center p-2 bg-white rounded shadow-sm">
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-sync-alt"></i> Se actualizaron los datos:</label>
                                <select id="resultDataUpdate" class="form-control border-teal-dark" disabled>
                                    <option value="SI">SI</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-user-edit"></i> Actualizado por:</label>
                                <input type="text" id="resultUpdatedBy" class="form-control border-teal-dark" readonly>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="fw-bold text-teal-dark"><i class="fas fa-map-marker-alt"></i> Sede:</label>
                                <input type="text" id="resultSede" class="form-control border-teal-dark" readonly>
                            </div>
                        </div>
                        <!-- Información de entrega movida al modal -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para información de entrega -->
<div class="modal fade" id="deliveryModal" tabindex="-1" aria-labelledby="deliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white position-relative overflow-hidden">
                <h5 class="modal-title position-relative" id="deliveryModalLabel">
                    <i class="fas fa-gift me-2"></i>
                    Información de Entrega de Regalo
                </h5>
                <button type="button" class="btn-close btn-close-white position-relative" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="container-fluid">
                    <!-- Información Principal de Entrega -->
                    <div class="card border-0 shadow-sm mb-4 bg-light">
                        <div class="card-header bg-indigo-dark text-white py-2">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Datos de la Entrega</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-white rounded-3 border-start border-4 border-success">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-calendar-alt text-success fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Fecha de entrega</small>
                                            <strong class="text-dark" id="modalDeliveryDate">-</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-white rounded-3 border-start border-4 border-info">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-user-tie text-info fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Entregado por</small>
                                            <strong class="text-dark" id="modalDeliveredBy">-</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-white rounded-3 border-start border-4 border-warning">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-map-marker-alt text-warning fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Sede</small>
                                            <strong class="text-dark" id="modalDeliverySede">-</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-white rounded-3 border-start border-4 border-purple">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-gift text-purple fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Tipo de Entrega</small>
                                            <strong class="text-dark" id="modalDeliveryTipoEntrega">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Firma Digital -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-secondary text-white py-2">
                            <h6 class="mb-0"><i class="fas fa-signature me-2"></i>Firma de Recepción</h6>
                        </div>
                        <div class="card-body bg-gradient-light w-100 d-flex justify-content-center align-items-center">
                            <div class="signature-container p-3 bg-white rounded-3 border-2 border-dashed border-secondary">
                                <img id="modalDeliverySignature" src="" alt="Firma"
                                    class="img-fluid rounded shadow-sm"
                                    style="max-width: 350px; max-height: 180px; min-height: 100px;">
                            </div>
                        </div>
                    </div>

                    <!-- Foto de Identificación -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-indigo-dark text-white py-2">
                            <h6 class="mb-0"><i class="fas fa-id-card me-2"></i>Foto de Identificación</h6>
                        </div>
                        <div class="card-body bg-gradient-light w-100 d-flex justify-content-center align-items-center">
                            <div class="photo-container p-3 bg-white rounded-3 border-2 border-dashed border-info">
                                <img id="modalIdPhoto" src="" alt="Foto de Identificación"
                                    class="img-fluid rounded shadow-sm"
                                    style="max-width: 350px; max-height: 250px; min-height: 150px;">
                            </div>
                        </div>
                    </div>

                    <!-- Información del Receptor -->
                    <div id="modalRecipientInfo" class="card border-0 shadow-sm" style="display: none;">
                        <div class="card-header bg-gradient-info text-white py-2">
                            <h6 class="mb-0"><i class="fas fa-user-friends me-2"></i>Información del Receptor</h6>
                        </div>
                        <div class="card-body d-flex flex-column gap-3">
                            <div class="d-flex flex-wrap gap-3">
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3 border-start border-4 border-primary h-100">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-id-card text-primary fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Documento</small>
                                            <strong class="text-dark" id="modalRecipientNumber">-</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1 min-width-0">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3 border-start border-4 border-success h-100">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-user text-success fs-4"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Nombre completo</small>
                                            <strong class="text-dark" id="modalRecipientName">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <button id="modalBtnShowCarta" class="btn bg-indigo-dark text-white px-4 py-2" style="display: none;">
                                    <i class="fas fa-file-pdf me-2"></i>Ver Carta de Autorización
                                </button>
                            </div>
                            <div>
                                <div id="modalRecipientMessages"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer bg-light border-top-0">
            <div class="text-muted small w-100 text-center">
                <i class="fas fa-shield-alt me-1"></i>
                Información verificada y registrada en el sistema
            </div>
        </div>
    </div>
</div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(45deg, var(--bs-teal-dark), var(--bs-indigo-dark)) !important;
    }

    .bg-gradient-light {
        background: linear-gradient(135deg, var(--bs-gray-light), var(--bs-gray-light)) !important;
    }

    .bg-gradient-info {
        background: linear-gradient(45deg, var(--bs-magenta-dark), var(--bs-magenta-dark)) !important;
    }

    .border-purple {
        border-color: #6f42c1 !important;
    }

    .text-purple {
        color: #6f42c1 !important;
    }

    .signature-container {
        transition: all 0.3s ease;
    }

    .signature-container:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    .modal-body .card {
        transition: all 0.3s ease;
    }

    .modal-body .card:hover {
        transform: translateY(-1px);
    }

    #modalRecipientMessages .alert {
        border: none;
        border-radius: 12px;
        font-weight: 500;
    }

    .bg-warning-subtle {
        background-color: #fff3cd !important;
    }

    .border-warning {
        border-color: #ffc107 !important;
        border-width: 2px !important;
    }

    /* Efecto de pulse para campos editables */
    .border-warning {
        animation: pulse-warning 2s infinite;
    }

    @keyframes pulse-warning {
        0% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
        }
    }
</style>


<!-- Incluir Signature Pad para firma digital -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
    document.getElementById('btnBuscar').addEventListener('click', function() {
        const numberId = document.getElementById('searchNumberId').value.trim();
        if (!numberId || isNaN(numberId)) {
            Swal.fire('Error', 'Ingresa un número de ID válido.', 'error');
            return;
        }

        fetch('components/individualSearch/getData.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'number_id=' + encodeURIComponent(numberId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Llenar los campos
                    document.getElementById('resultNumberId').value = data.data.number_id;
                    document.getElementById('originalNumberId').value = data.data.number_id; // Guardar original
                    document.getElementById('resultName').value = data.data.name;
                    document.getElementById('resultCompany').value = data.data.company_name;
                    document.getElementById('resultCellPhone').value = data.data.cell_phone;
                    document.getElementById('resultEmail').value = data.data.email;
                    document.getElementById('resultAddress').value = data.data.address;
                    document.getElementById('resultCity').value = data.data.city;
                    document.getElementById('resultRegistrationDate').value = data.data.registration_date;
                    document.getElementById('resultGender').value = data.data.gender;
                    document.getElementById('resultDataUpdate').value = data.data.data_update;
                    document.getElementById('resultUpdatedBy').value = data.data.updated_by || 'N/A'; // Mostrar N/A si vacío
                    document.getElementById('resultSede').value = data.data.sede || 'N/A'; // Nuevo campo sede
                    // Guardar si tiene entrega
                    window.hasDelivery = data.has_delivery;
                    window.deliveryData = data.delivery;
                    document.getElementById('resultCard').style.display = 'block';
                    // Ocultar botón guardar inicialmente
                    document.getElementById('btnGuardar').style.display = 'none';

                    // MANTENER SIEMPRE HABILITADO el botón de editar
                    document.getElementById('btnEditar').disabled = false;

                    // Deshabilitar botón de confirmar entrega si data_update no es 'SI' o si ya tiene entrega
                    const btnConfirmar = document.getElementById('btnConfirmarEntrega');
                    btnConfirmar.disabled = <?php echo $disableConfirm ? 'true' : 'false'; ?> || (data.data.data_update !== 'SI') || data.has_delivery;

                    // Mostrar/ocultar botones según si tiene entrega
                    if (data.has_delivery) {
                        document.getElementById('btnConfirmarEntrega').style.display = 'none';
                        document.getElementById('btnVerEntrega').style.display = 'inline-block';
                        document.getElementById('btnReenviarCorreo').style.display = 'inline-block';

                        // Llenar datos del modal
                        let date = new Date(data.delivery.reception_date);
                        document.getElementById('modalDeliveryDate').textContent = date.toLocaleDateString('es-ES');
                        document.getElementById('modalDeliveredBy').textContent = data.delivery.delivered_name || data.delivery.delivered_by;
                        document.getElementById('modalDeliverySede').textContent = data.delivery.sede || 'N/A';
                        document.getElementById('modalDeliveryTipoEntrega').textContent = data.delivery.tipo_entrega || 'N/A';
                        document.getElementById('modalDeliverySignature').src = 'img/firmasRegalos/' + data.delivery.signature;
                        document.getElementById('modalIdPhoto').src = 'uploads/idPhotos/' + data.delivery.id_photo;

                        // Mostrar información del receptor cuando es diferente
                        if (data.delivery.recipient_number_id != data.data.number_id) {
                            document.getElementById('modalRecipientInfo').style.display = 'block';
                            document.getElementById('modalRecipientNumber').textContent = data.delivery.recipient_number_id;
                            document.getElementById('modalRecipientName').textContent = data.delivery.recipient_name || 'No registrado';

                            if (data.delivery.authorization_letter && data.delivery.authorization_letter !== 'N/A') {
                                document.getElementById('modalBtnShowCarta').style.display = 'inline-block';
                                document.getElementById('modalBtnShowCarta').onclick = () => {
                                    const filePath = `uploads/cartasAutorizacion/${data.delivery.authorization_letter}`;
                                    const fileExtension = data.delivery.authorization_letter.split('.').pop().toLowerCase();
                                    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                                    let content;

                                    if (fileExtension === 'pdf') {
                                        content = `<iframe src="${filePath}" width="100%" height="500px" style="border:none;"></iframe>`;
                                    } else if (imageExtensions.includes(fileExtension)) {
                                        content = `<img src="${filePath}" style="max-width: 100%; height: auto;" alt="Carta de Autorización">`;
                                    } else {
                                        content = `<p>No se puede previsualizar el archivo. <a href="${filePath}" target="_blank">Descargar archivo</a></p>`;
                                    }

                                    Swal.fire({
                                        title: 'Carta de Autorización',
                                        html: content,
                                        showCloseButton: true,
                                        showConfirmButton: false,
                                        width: fileExtension === 'pdf' ? '60%' : '50%'
                                    });
                                };
                            } else {
                                document.getElementById('modalBtnShowCarta').style.display = 'none';
                            }

                            // Limpiar mensajes previos
                            document.getElementById('modalRecipientMessages').innerHTML = '';

                            // Mostrar mensaje si el receptor es un usuario registrado
                            if (data.recipient_is_user) {
                                document.getElementById('modalRecipientMessages').innerHTML = `
                                    <div class="alert alert-info d-flex align-items-center shadow-sm">
                                        <div class="flex-shrink-0 me-3">
                                            <i class="fas fa-user-check fs-3 text-info"></i>
                                        </div>
                                        <div>
                                            <strong>Usuario Registrado</strong><br>
                                            <small>Esta persona también es un asociado actualmente registrado en el sistema.</small>
                                        </div>
                                    </div>
                                `;
                            }
                        } else {
                            // Mostrar mensaje de "misma persona"
                            document.getElementById('modalRecipientInfo').style.display = 'block';
                            document.getElementById('modalBtnShowCarta').style.display = 'none';
                            document.getElementById('modalRecipientMessages').innerHTML = `
                                <div class="alert alert-success d-flex align-items-center shadow-sm">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fas fa-user-check fs-3 text-success"></i>
                                    </div>
                                    <div>
                                        <strong>Receptor Confirmado</strong><br>
                                        <small>La misma persona registrada ha recibido su regalo directamente.</small>
                                    </div>
                                </div>
                            `;
                        }
                    } else {
                        document.getElementById('btnConfirmarEntrega').style.display = 'inline-block';
                        document.getElementById('btnVerEntrega').style.display = 'none';
                        document.getElementById('btnReenviarCorreo').style.display = 'none';
                    }
                } else {
                    Swal.fire('Usuario no encontrado', data.message, 'error');
                    document.getElementById('resultCard').style.display = 'none';
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Error en la solicitud: ' + error.message, 'error');
            });
    });

    // Botón Editar
    document.getElementById('btnEditar').addEventListener('click', function() {
        // Verificar si la persona ya tiene una entrega confirmada
        if (window.hasDelivery) {
            // Si tiene entrega, solo habilitar celular y email
            Swal.fire({
                title: 'Edición Limitada',
                text: 'Esta persona ya tiene una entrega confirmada. Solo se pueden editar los datos de contacto.',
                icon: 'info',
                confirmButtonText: 'Continuar'
            }).then(() => {
                // Habilitar solo los campos de celular y email
                const cellPhoneInput = document.getElementById('resultCellPhone');
                const emailInput = document.getElementById('resultEmail');

                // Remover readonly solo de estos campos
                cellPhoneInput.removeAttribute('readonly');
                emailInput.removeAttribute('readonly');

                // Agregar clases para resaltar los campos editables
                cellPhoneInput.classList.add('border-warning', 'bg-warning-subtle');
                emailInput.classList.add('border-warning', 'bg-warning-subtle');

                // Mostrar botón guardar
                document.getElementById('btnGuardar').style.display = 'inline-block';

                // Opcional: agregar un tooltip o mensaje visual
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
                Toast.fire({
                    icon: 'info',
                    title: 'Solo se pueden editar celular y email'
                });
            });
            return;
        }

        // Comportamiento original para personas sin entrega
        const enlace = 'https://app.mensajero.digital/form/1472/AwJBz3xdJ6';
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?data=${encodeURIComponent(enlace)}&size=200x200`;

        Swal.fire({
            title: 'Actualización de Datos Requerida',
            html: `
                <p>La persona debe completar el formulario de actualización en el siguiente enlace:</p>
                <p><a href="${enlace}" target="_blank">${enlace}</a></p>
                <button id="copyLinkBtn" class="btn btn-primary">Copiar Enlace</button>
                <br><br>
                <p><small>También puede escanear el QR:</small></p>
                <img src="${qrUrl}" alt="QR Code" style="max-width: 200px; max-height: 200px;">
                <div id="notification" style="margin-top: 10px;"></div>
            `,
            showConfirmButton: true,
            confirmButtonText: 'OK',
            didOpen: () => {
                document.getElementById('copyLinkBtn').addEventListener('click', () => {
                    navigator.clipboard.writeText(enlace).then(() => {
                        document.getElementById('notification').innerHTML = '<div class="alert alert-success">Enlace copiado al portapapeles</div>';
                    }).catch(err => {
                        console.error('Error al copiar: ', err);
                        document.getElementById('notification').innerHTML = '<div class="alert alert-danger">Error al copiar enlace</div>';
                    });
                });
            }
        }).then(() => {
            // Después de OK, habilitar campos para edición completa
            const inputs = document.querySelectorAll('#resultCard input');
            inputs.forEach(input => {
                // Mantener deshabilitados los campos de número de ID, nombre, actualizado por y sede
                if (input.id === 'resultNumberId' || input.id === 'resultName' || input.id === 'resultUpdatedBy' || input.id === 'resultSede') {
                    input.setAttribute('readonly', true);
                } else {
                    input.removeAttribute('readonly');
                }
            });
            const selects = document.querySelectorAll('#resultCard select');
            selects.forEach(select => {
                select.removeAttribute('disabled'); // Habilitar todos los selects, incluyendo resultDataUpdate
            });
            document.getElementById('btnGuardar').style.display = 'inline-block';
        });
    });

    // Función para validar email
    function isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    // Botón Guardar - Modificar para manejar edición limitada
    document.getElementById('btnGuardar').addEventListener('click', function() {
        const email = document.getElementById('resultEmail').value.trim();
        if (email && !isValidEmail(email)) {
            Swal.fire('Error', 'Correo electrónico inválido.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('number_id', document.getElementById('resultNumberId').value);
        formData.append('original_number_id', document.getElementById('originalNumberId').value);
        formData.append('name', document.getElementById('resultName').value);
        formData.append('company_name', document.getElementById('resultCompany').value);
        formData.append('cell_phone', document.getElementById('resultCellPhone').value);
        formData.append('email', document.getElementById('resultEmail').value);
        formData.append('address', document.getElementById('resultAddress').value);
        formData.append('city', document.getElementById('resultCity').value);
        formData.append('registration_date', document.getElementById('resultRegistrationDate').value);
        formData.append('gender', document.getElementById('resultGender').value);
        formData.append('data_update', document.getElementById('resultDataUpdate').value);
        formData.append('updated_by', document.getElementById('resultUpdatedBy').value);
        formData.append('sede', document.getElementById('resultSede').value);

        // Agregar flag para indicar si es edición limitada
        formData.append('limited_edit', window.hasDelivery ? 'true' : 'false');

        fetch('components/individualSearch/updateData.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', 'Datos actualizados correctamente.', 'success');

                    // Actualizar original con el nuevo si cambió
                    document.getElementById('originalNumberId').value = document.getElementById('resultNumberId').value;

                    // Volver a readonly/disabled todos los campos
                    const inputs = document.querySelectorAll('#resultCard input');
                    inputs.forEach(input => {
                        input.setAttribute('readonly', true);
                        // Remover clases de resaltado
                        input.classList.remove('border-warning', 'bg-warning-subtle');
                    });

                    const selects = document.querySelectorAll('#resultCard select');
                    selects.forEach(select => {
                        select.setAttribute('disabled', true);
                    });

                    document.getElementById('btnGuardar').style.display = 'none';

                    // SIEMPRE mantener el botón Editar habilitado
                    document.getElementById('btnEditar').disabled = false;

                    // Solo para ediciones completas (sin entrega previa)
                    if (!window.hasDelivery) {
                        // Actualizar estado de btnConfirmarEntrega dinámicamente
                        const btnConfirmar = document.getElementById('btnConfirmarEntrega');
                        const dataUpdateValue = document.getElementById('resultDataUpdate').value;
                        btnConfirmar.disabled = <?php echo $disableConfirm ? 'true' : 'false'; ?> || (dataUpdateValue !== 'SI') || window.hasDelivery;

                        // Mostrar notificación si se habilitó
                        if (dataUpdateValue === 'SI' && !window.hasDelivery && !<?php echo $disableConfirm ? 'true' : 'false'; ?>) {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'info',
                                title: 'Datos actualizados ya puedes confirmar entrega'
                            });
                        }
                    }
                } else {
                    Swal.fire('Error', 'Error al actualizar: ' + data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error', 'Error en la solicitud: ' + error.message, 'error');
            });
    });

    // Botón Ver Entrega
    document.getElementById('btnVerEntrega').addEventListener('click', function() {
        const deliveryModal = new bootstrap.Modal(document.getElementById('deliveryModal'));
        deliveryModal.show();
    });

    // Botón Confirmar Entrega
    document.getElementById('btnConfirmarEntrega').addEventListener('click', function() {
        if (window.hasDelivery) {
            Swal.fire('Entrega ya registrada', 'Esta persona ya cuenta con un regalo entregado en este año.', 'warning');
            return;
        }

        const userNumberId = document.getElementById('resultNumberId').value;
        const userName = document.getElementById('resultName').value;

        Swal.fire({
            title: 'Confirmar Entrega de Regalo',
            html: `
                <div class="container-fluid">
                    <div class="mb-3">
                        <label class="form-label fw-bold">¿La persona que recibe es la misma del registro?</label><br>
                        <input type="radio" id="samePersonYes" name="samePerson" value="yes" checked> <label for="samePersonYes">Sí</label><br>
                        <input type="radio" id="samePersonNo" name="samePerson" value="no"> <label for="samePersonNo">No</label>
                    </div>
                    <div id="additionalFields" style="display: none;">
                        <div class="mb-3">
                            <label for="swalRecipientNumberId" class="form-label">Cédula de quien recibe:</label>
                            <input type="number" id="swalRecipientNumberId" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="swalRecipientName" class="form-label">Nombre de quien recibe:</label>
                            <input type="text" id="swalRecipientName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="swalAuthorizationLetter" class="form-label">Carta de autorización (PDF o Imagen):</label>
                            <input type="file" id="swalAuthorizationLetter" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.gif,.bmp,.webp,image/*" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="photoFile" class="form-label fw-bold">Foto de identificación:</label>
                        <small class="text-muted d-block mb-2">Sube una foto del carnet del trabajo, cédula o rostro de la persona.</small>
                        <input type="file" id="photoFile" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.gif,.bmp,.webp,image/*" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Firma de recepción:</label>
                        <canvas id="signatureCanvas" width="400" height="200" style="border: 1px solid #ccc;"></canvas>
                        <br><button type="button" id="clearSignature" class="btn btn-secondary btn-sm">Limpiar Firma</button>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Confirmar Entrega',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true, // Agregar loader mientras se confirma
            didOpen: () => {
                // Inicializar Signature Pad
                const canvas = document.getElementById('signatureCanvas');
                const signaturePad = new SignaturePad(canvas);

                document.getElementById('clearSignature').addEventListener('click', () => {
                    signaturePad.clear();
                });

                // Mostrar/ocultar campos adicionales
                document.getElementById('samePersonYes').addEventListener('change', () => {
                    document.getElementById('additionalFields').style.display = 'none';
                    document.getElementById('swalRecipientNumberId').required = false;
                    document.getElementById('swalRecipientName').required = false;
                    document.getElementById('swalAuthorizationLetter').required = false;
                });
                document.getElementById('samePersonNo').addEventListener('change', () => {
                    document.getElementById('additionalFields').style.display = 'block';
                    document.getElementById('swalRecipientNumberId').required = true;
                    document.getElementById('swalRecipientName').required = true;
                    document.getElementById('swalAuthorizationLetter').required = true;
                });

                // Guardar referencia para usar en preConfirm
                window.signaturePad = signaturePad;
            },
            preConfirm: () => {
                const samePersonElement = document.querySelector('input[name="samePerson"]:checked');
                if (!samePersonElement) {
                    Swal.showValidationMessage('Selecciona si la persona que recibe es la misma del registro');
                    return false;
                }
                const samePerson = samePersonElement.value;
                let recipientNumberId = userNumberId;
                let recipientName = userName;
                let authorizationLetter = 'N/A';

                if (samePerson === 'no') {
                    recipientNumberId = document.getElementById('swalRecipientNumberId').value;
                    recipientName = document.getElementById('swalRecipientName').value;
                    const file = document.getElementById('swalAuthorizationLetter').files[0];
                    if (!file) {
                        Swal.showValidationMessage('Debes seleccionar un archivo PDF o imagen');
                        return false;
                    }
                    authorizationLetter = file;
                }

                const photoFile = document.getElementById('photoFile').files[0];
                if (!photoFile) {
                    Swal.showValidationMessage('Debes subir una foto de identificación');
                    return false;
                }

                if (window.signaturePad.isEmpty()) {
                    Swal.showValidationMessage('Debes firmar para confirmar');
                    return false;
                }

                // Dibujar fondo blanco en el canvas
                const ctx = window.signaturePad.canvas.getContext('2d');
                ctx.globalCompositeOperation = 'destination-over';
                ctx.fillStyle = 'white';
                ctx.fillRect(0, 0, window.signaturePad.canvas.width, window.signaturePad.canvas.height);

                const signatureDataURL = window.signaturePad.toDataURL();

                const formData = new FormData();
                formData.append('user_number_id', userNumberId);
                formData.append('recipient_number_id', recipientNumberId);
                formData.append('recipient_name', recipientName);
                formData.append('signature', signatureDataURL);
                formData.append('authorization_letter', authorizationLetter);
                formData.append('id_photo', photoFile); // Cambiar de 'id_photo' a coincidir con PHP

                return fetch('components/individualSearch/confirmDelivery.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json());
            }
        }).then((result) => {
            console.log('Result:', result);
            if (result.isConfirmed) {
                const data = result.value;
                console.log('Data:', data);

                // Mostrar loader mientras se procesa la respuesta
                Swal.fire({
                    title: 'Procesando...',
                    html: 'Por favor espera mientras se confirma la entrega.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                setTimeout(() => { // Simula el delay, puedes quitar el setTimeout si no lo necesitas
                    if (data.success) {
                        Swal.fire('Éxito', 'Entrega confirmada correctamente.', 'success')
                            .then(() => {
                                location.reload();
                            });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                }, 800); // Ajusta el tiempo si lo deseas
            }
        });
    });

    // Botón Reenviar Correo
    document.getElementById('btnReenviarCorreo').addEventListener('click', function() {
        if (!window.hasDelivery) {
            Swal.fire('Error', 'Esta persona no tiene una entrega registrada.', 'error');
            return;
        }

        const userNumberId = document.getElementById('resultNumberId').value;
        const userEmail = document.getElementById('resultEmail').value.trim();

        // Validar que tenga email
        if (!userEmail || !isValidEmail(userEmail)) {
            Swal.fire('Error', 'La persona no tiene un correo electrónico válido registrado.', 'error');
            return;
        }

        Swal.fire({
            title: '¿Reenviar Correo de Confirmación?',
            text: `Se reenviará el correo de confirmación de entrega a: ${userEmail}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, Reenviar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                const formData = new FormData();
                formData.append('user_number_id', userNumberId);
                formData.append('action', 'resend_email');

                return fetch('components/individualSearch/resendEmail.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message);
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Error: ${error.message}`
                        );
                    });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = result.value;
                if (data.success) {
                    Swal.fire({
                        title: '¡Correo Reenviado!',
                        text: 'El correo de confirmación ha sido reenviado exitosamente.',
                        icon: 'success',
                        confirmButtonColor: '#28a745'
                    });
                }
            }
        });
    });
</script>