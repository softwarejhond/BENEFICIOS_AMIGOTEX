<?php
// Activar error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión a la DB
include '../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['number_id'])) {
    $number_id = (int)$_POST['number_id'];

    if ($number_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Número de ID inválido.']);
        exit;
    }

    // Consulta preparada para usuario
    $stmt = $conn->prepare("SELECT * FROM gf_users WHERE number_id = ?");
    $stmt->bind_param("i", $number_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verificar entregas de este año
        $stmt_deliveries = $conn->prepare("SELECT d.*, u.nombre as delivered_name FROM gf_gift_deliveries d LEFT JOIN users u ON d.delivered_by = u.username WHERE d.user_number_id = ? AND YEAR(d.reception_date) = YEAR(CURDATE()) ORDER BY d.reception_date DESC");
        $stmt_deliveries->bind_param("i", $number_id);
        $stmt_deliveries->execute();
        $result_deliveries = $stmt_deliveries->get_result();
        
        $deliveries = [];
        $delivered_types = [];
        while ($delivery = $result_deliveries->fetch_assoc()) {
            $deliveries[] = $delivery;
            $delivered_types[] = $delivery['tipo_entrega'];
        }
        $stmt_deliveries->close();

        $has_deliveries = count($deliveries) > 0;

        // Verificar qué tipos ya fueron entregados
        $current_delivery_type = $_SESSION['tipo_entrega'] ?? '';
        $can_deliver_current_type = !in_array($current_delivery_type, $delivered_types);

        echo json_encode([
            'success' => true,
            'data' => $row,
            'has_deliveries' => $has_deliveries,
            'deliveries' => $deliveries,
            'delivered_types' => $delivered_types,
            'can_deliver_current_type' => $can_deliver_current_type,
            'current_delivery_type' => $current_delivery_type
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>