<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_sede'])) {
    $id_sede = intval($_POST['id_sede']);
    
    // Verificar si la sede existe y pertenece al usuario (opcional, para seguridad)
    // Aquí, asumimos que cualquier usuario logueado puede eliminar, pero puedes agregar checks
    
    $stmt = mysqli_prepare($conn, "DELETE FROM sedes WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id_sede);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Sede eliminada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar la sede: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
}

mysqli_close($conn);
?>