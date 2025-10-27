<?php
// ===============================
// UTINNOVA-DASHBOARD - main.php
// ===============================
// Este archivo es el punto de entrada principal del dashboard.
// Controla la sesión, verifica el login y carga los componentes visuales.
// También gestiona la alerta de perfil incompleto y la estructura base de la página.

session_start();
include("conexion.php");
// Habilitar la visualización de errores para desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);  // Mostrar todos los errores

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Si no está logueado, redirigir a la página de inicio de sesión
    header('Location: index.php');
    exit;
}
include("components/filters/takeUser.php");

// Obtener información del usuario logueado
$infoUsuario = obtenerInformacionUsuario(); // Obtén la información del usuario
$rol = $infoUsuario['rol'];

// Obtener sedes y entregas del asesor si el rol es 'Asesor'
$sedes = [];
$tipos_entrega = [];
$sede_set = isset($_SESSION['sede']);
$tipo_set = isset($_SESSION['tipo_entrega']);
if ($rol == 'Asesor') {
    $query = mysqli_query($conn, "SELECT sede1, sede2 FROM asesores_sedes WHERE username = '" . mysqli_real_escape_string($conn, $_SESSION['username']) . "'");
    if ($row = mysqli_fetch_assoc($query)) {
        if (!empty($row['sede1'])) $sedes[] = $row['sede1'];
        if (!empty($row['sede2'])) $sedes[] = $row['sede2'];
    }
    // Obtener tipos de entrega
    $queryTipos = mysqli_query($conn, "SELECT nombre FROM tipos_entrega ORDER BY nombre");
    while ($rowTipo = mysqli_fetch_assoc($queryTipos)) {
        $tipos_entrega[] = $rowTipo['nombre'];
    }
}

// Verificar si el usuario tiene campos incompletos en su perfil
$mostrar_alerta = false;
$mensaje_campos = "";

if (isset($_SESSION['campos_incompletos']) && $_SESSION['campos_incompletos'] === true) {
    $mostrar_alerta = true;
    $campos_faltantes = $_SESSION['campos_faltantes'];
    $mensaje_campos = "Por favor complete los siguientes campos en su perfil: ";
    $mensaje_campos .= implode(", ", $campos_faltantes);

    // Limpiar las variables de sesión para no mostrar la alerta en futuras cargas
    unset($_SESSION['campos_incompletos']);
    unset($_SESSION['campos_faltantes']);
}

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <!-- Integración de Bootstrap y DataTables -->
    <!-- Carga de estilos y scripts para la interfaz principal -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/estilo.css?v=0.0.2">
    <link rel="stylesheet" href="css/slidebar.css?v=0.0.3">
    <link rel="stylesheet" href="css/contadores.css?v=0.7">
    <link rel="stylesheet" href="css/dataTables.css?v=0.3">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="icon" href="img/gift_flow_icono.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</head>

<body style="background-color:white">
    <?php include("controller/header.php"); ?> <!-- Barra superior y navegación -->
    <?php include("components/sliderBar.php"); ?> <!-- Menú lateral -->
    <?php include("components/modals/userNew.php"); ?> <!-- Modal para nuevo usuario -->
    <?php include("components/modals/newAdvisor.php"); ?> <!-- Modal para nuevo asesor -->
    <br><br>
</body>
<div style="margin-top: 20px;">
    <div class="mt-3">
        <div id="dashboard">
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0"><i class="bi bi-speedometer2"></i> Dashboard</h2>
                <div class="loader">
                    <!-- Animación de carga -->
                    <div class="slider" style="--i:0"></div>
                    <div class="slider" style="--i:1"></div>
                    <div class="slider" style="--i:2"></div>
                    <div class="slider" style="--i:3"></div>
                    <div class="slider" style="--i:4"></div>
                </div>
            </div>
            <hr>
            <?php include("components/cardContadores/contadoresCards.php"); ?> <!-- Tarjetas de contadores principales -->

            <?php //include("components/aceptUsers/updateStatus.php");  
            ?>
            <div class="row">
                <div class="col-sm-12 col-md-3 col-lg-3">
                    <!-- Espacio para gráficos o widgets adicionales -->
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3">
                    <?php //include("components/graphics/stratum.php");  ?>
                </div>
            </div>
        </div>
    </div>
</div>



<?php include("controller/footer.php"); ?>
<?php //include("controller/botonFlotanteDerecho.php"); ?>
<?php include("components/sliderBarBotton.php"); ?>
<!-- Scripts de Bootstrap, DataTables y personalizaciones -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.js"></script>
<!-- <script src="js/real-time-update-contadores.js?v=0.3"></script> -->
<script src="js/dataTables.js?v=0.2"></script>
<script>
    $(document).ready(function() {
        $('#link-dashboard').addClass('pagina-activa'); // Marca el dashboard como activo en el menú

        // Inicialización de DataTable para la lista de inscritos
        $('#listaInscritos').DataTable({
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            pagingType: "simple"
        });

        // Mostrar Swal para seleccionar sede si es Asesor
        var rol = '<?php echo $rol; ?>';
        var sedes = <?php echo json_encode($sedes); ?>;
        var tipos_entrega = <?php echo json_encode($tipos_entrega); ?>;
        var sede_set = <?php echo $sede_set ? 'true' : 'false'; ?>;
        var tipo_set = <?php echo $tipo_set ? 'true' : 'false'; ?>;
        if (rol === 'Asesor' && sedes.length > 0 && tipos_entrega.length > 0 && (!sede_set || !tipo_set)) {
            let optionsSede = '<option value="">Selecciona una sede</option>';
            sedes.forEach(function(sede) {
                optionsSede += '<option value="' + sede + '">' + sede + '</option>';
            });
            let optionsTipo = '<option value="">Selecciona un tipo de entrega</option>';
            tipos_entrega.forEach(function(tipo) {
                optionsTipo += '<option value="' + tipo + '">' + tipo + '</option>';
            });
            Swal.fire({
                title: '¿En qué sede y tipo de entrega vas a trabajar hoy?',
                html: `
                    <select id="sedeSelector" class="form-control mb-3">` + optionsSede + `</select>
                    <select id="tipoEntregaSelector" class="form-control">` + optionsTipo + `</select>
                `,
                showCancelButton: false,
                allowOutsideClick: false,
                confirmButtonText: 'Seleccionar',
                preConfirm: () => {
                    const sede = document.getElementById('sedeSelector').value;
                    const tipo_entrega = document.getElementById('tipoEntregaSelector').value;
                    if (!sede || !tipo_entrega) {
                        Swal.showValidationMessage('Debes seleccionar una sede y un tipo de entrega');
                        return false;
                    }
                    return fetch('controller/setSede.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'sede=' + encodeURIComponent(sede) + '&tipo_entrega=' + encodeURIComponent(tipo_entrega)
                    }).then(response => response.json());
                }
            }).then((result) => {
                if (result.isConfirmed && result.value.success) {
                    // Actualizar los campos en el header sin recargar
                    const sede = document.getElementById('sedeSelector').value;
                    const tipo_entrega = document.getElementById('tipoEntregaSelector').value;
                    document.getElementById('headerSede').textContent = sede;
                    document.getElementById('headerTipoEntrega').textContent = tipo_entrega;
                    Swal.fire('Seleccionado', 'La sede y tipo de entrega han sido guardados correctamente.', 'success');
                } else {
                    location.reload();
                }
            });
        }

        // Mostrar alerta si hay campos incompletos en el perfil del usuario
        <?php if ($mostrar_alerta): ?>
            Swal.fire({
                title: 'Perfil incompleto',
                html: '<div style="text-align: center;"><p><strong><?php echo $mensaje_campos; ?></strong></p></div>',
                icon: 'warning',
                allowOutsideClick: false,
                customClass: {
                    popup: 'swal-wide'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir a la página de perfil para completar los datos
                    window.location.href = 'profile.php'; // Cambia esta ruta por la correcta
                }
            });
        <?php endif; ?>
    });
</script>
<script>
    /*
    // Función para hacer la solicitud AJAX y mostrar el total en una alerta
    function mostrarTotalEnAlerta() {
        // Hacer la solicitud AJAX al script PHP
        fetch('controller/obtener_proporciones.php')
            .then(response => response.json())
            .then(data => {
                // Mostrar el total de estudiantes en un alert
                alert('Total de Estudiantes: ' + data.total_estudiantes);
            })
            .catch(error => console.error('Error al obtener los datos:', error));
        }

    // Llamar a la función al cargar la página
    window.onload = mostrarTotalEnAlerta;
    */
</script>

<style>
    .swal-wide {
        width: 600px !important;
    }

    .loader {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: row;
        transform: scale(0.5);
    }

    .slider {
        overflow: hidden;
        background-color: white;
        margin: 0 15px;
        height: 80px;
        width: 20px;
        border-radius: 30px;
        box-shadow: 15px 15px 20px rgba(0, 0, 0, 0.1), -15px -15px 30px #fff,
            inset -5px -5px 10px rgba(0, 0, 255, 0.1),
            inset 5px 5px 10px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .slider::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        height: 20px;
        width: 20px;
        border-radius: 100%;
        box-shadow: inset 0px 0px 0px rgba(0, 0, 0, 0.3), 0px 420px 0 400px #66cc00,
            inset 0px 0px 0px rgba(0, 0, 0, 0.1);
        animation: animate_2 5s ease-in-out infinite;
        animation-delay: calc(-1s * var(--i));
    }

    @keyframes animate_2 {
        0% {
            transform: translateY(250px);
            filter: hue-rotate(0deg);
        }

        50% {
            transform: translateY(0);
        }

        100% {
            transform: translateY(250px);
            filter: hue-rotate(180deg);
        }
    }
</style>

</body>

</html>