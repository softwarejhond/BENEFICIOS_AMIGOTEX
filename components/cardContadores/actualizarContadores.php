<?php
header('Content-Type: application/json');

// Incluir conexión a BD
include '../../controller/conexion.php';

// Contador 1: Total de Regalos Entregados
$query1 = "SELECT COUNT(*) as total FROM gf_gift_deliveries";
$result1 = $conn->query($query1);
$totalEntregas = $result1->fetch_assoc()['total'];

// Contador 2: Total de Usuarios Registrados
$query2 = "SELECT COUNT(*) as total FROM gf_users";
$result2 = $conn->query($query2);
$totalUsuarios = $result2->fetch_assoc()['total'];

// Contador 3: Entregas Este Mes
$query3 = "SELECT COUNT(*) as total FROM gf_gift_deliveries WHERE MONTH(reception_date) = MONTH(CURDATE()) AND YEAR(reception_date) = YEAR(CURDATE())";
$result3 = $conn->query($query3);
$entregasMes = $result3->fetch_assoc()['total'];

// Contador 4: Usuarios Actualizados Recientemente (últimos 30 días)
$query4 = "SELECT COUNT(*) as total FROM gf_users WHERE updated_by = 'APLICATIVO BENEFICIOS'";
$result4 = $conn->query($query4);
$usuariosActualizados = $result4->fetch_assoc()['total'];

// Consulta adicional: Entregas por sede para el gráfico
$query5 = "SELECT sede, COUNT(*) as total FROM gf_gift_deliveries GROUP BY sede ORDER BY total DESC";
$result5 = $conn->query($query5);

$labels = [];
$values = [];

while ($row = $result5->fetch_assoc()) {
    $labels[] = $row['sede'];
    $values[] = (int)$row['total'];
}

// Entregas por mes (últimos 12 meses)
$query6 = "SELECT DATE_FORMAT(reception_date, '%Y-%m') as mes, COUNT(*) as total 
           FROM gf_gift_deliveries 
           WHERE reception_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
           GROUP BY mes
           ORDER BY mes";
$result6 = $conn->query($query6);
$labelsMeses = [];
$valoresMeses = [];
while ($row = $result6->fetch_assoc()) {
    $labelsMeses[] = $row['mes'];
    $valoresMeses[] = (int)$row['total'];
}

echo json_encode([
    'totalEntregas' => $totalEntregas,
    'totalUsuarios' => $totalUsuarios,
    'entregasMes' => $entregasMes,
    'usuariosActualizados' => $usuariosActualizados,
    'labels' => $labels,
    'values' => $values,
    'labelsMeses' => $labelsMeses,
    'valoresMeses' => $valoresMeses
]);

$conn->close();
?>