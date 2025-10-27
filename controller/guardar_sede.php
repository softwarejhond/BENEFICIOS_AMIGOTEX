<?php
session_start(); // Asegúrate de que la sesión esté iniciada para obtener el username

// Incluir la conexión a la base de datos
include 'conexion.php';

// Verificar si se recibió el nombre de la sede vía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre_sede'])) {
    $nombre_sede = trim($_POST['nombre_sede']); // Aplicar trim básico
    $nombre_sede = mb_strtoupper($nombre_sede, 'UTF-8'); // Convertir a mayúsculas conservando tildes
    
    // Obtener el username de la sesión (asume que está en $_SESSION['username'])
    $creado_por = $_SESSION['username'] ?? 'desconocido'; // Valor por defecto si no hay sesión
    
    // Preparar la consulta para insertar
    $stmt = mysqli_prepare($conn, "INSERT INTO sedes (nombre, creado_por) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ss', $nombre_sede, $creado_por);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Sede guardada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la sede: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
}

// Cerrar la conexión
mysqli_close($conn);
?>