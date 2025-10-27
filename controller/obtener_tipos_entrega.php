<?php
session_start();
include 'conexion.php';

// Query con JOIN para obtener el nombre del creador desde la tabla users
$query = "SELECT t.id, t.nombre, t.fecha_creacion, u.nombre AS nombre_creador 
          FROM tipos_entrega t 
          JOIN users u ON t.creado_por = u.username 
          ORDER BY t.fecha_creacion DESC";
$result = mysqli_query($conn, $query);

$tipos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tipos[] = $row;
}

echo json_encode($tipos);
mysqli_close($conn);
?>