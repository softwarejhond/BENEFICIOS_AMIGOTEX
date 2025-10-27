<?php
session_start(); // Asegúrate de que la sesión esté iniciada para obtener el username

// Incluir la conexión a la base de datos
include 'conexion.php';

// Verificar si se recibió el nombre del tipo de entrega vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_tipo_entrega'])) {
    $nombre_tipo_entrega = trim($_POST['nombre_tipo_entrega']); // Aplicar trim básico
    $nombre_tipo_entrega = mb_strtoupper($nombre_tipo_entrega, 'UTF-8'); // Convertir a mayúsculas conservando tildes
    
    // Obtener el username de la sesión (asume que está en $_SESSION['username'])
    $creado_por = $_SESSION['username'] ?? 'desconocido'; // Valor por defecto si no hay sesión
    
    // Preparar la consulta para insertar
    $stmt = mysqli_prepare($conn, "INSERT INTO tipos_entrega (nombre, creado_por) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ss', $nombre_tipo_entrega, $creado_por);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Tipo de entrega guardado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar el tipo de entrega: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
}

// Cerrar la conexión
mysqli_close($conn);
?>