<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_tipo_entrega'])) {
    $id = intval($_POST['id_tipo_entrega']);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM tipos_entrega WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Tipo de entrega eliminado exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el tipo de entrega: ' . mysqli_error($conn)]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
}

mysqli_close($conn);
?>