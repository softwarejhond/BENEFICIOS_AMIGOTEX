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

        // Verificar si ya tiene entrega este año
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM gf_gift_deliveries WHERE user_number_id = ? AND YEAR(reception_date) = YEAR(CURDATE())");
        $stmt_check->bind_param("i", $number_id);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        $has_delivery = $count > 0;
        $delivery_data = null;
        $recipient_is_user = false;
        $recipient_data = null;
        if ($has_delivery) {
            // Obtener datos de la entrega
            $stmt_delivery = $conn->prepare("SELECT * FROM gf_gift_deliveries WHERE user_number_id = ? AND YEAR(reception_date) = YEAR(CURDATE()) LIMIT 1");
            $stmt_delivery->bind_param("i", $number_id);
            $stmt_delivery->execute();
            $result_delivery = $stmt_delivery->get_result();
            $delivery_data = $result_delivery->fetch_assoc();
            $stmt_delivery->close();

            // Obtener nombre del usuario que entregó
            $stmt_delivered = $conn->prepare("SELECT nombre FROM users WHERE username = ?");
            $stmt_delivered->bind_param("s", $delivery_data['delivered_by']);
            $stmt_delivered->execute();
            $stmt_delivered->bind_result($delivered_name);
            $stmt_delivered->fetch();
            $stmt_delivered->close();
            $delivery_data['delivered_name'] = $delivered_name;

            // Verificar si el receptor es un usuario registrado
            if ($delivery_data['recipient_number_id'] != $number_id) {
                $stmt_recipient = $conn->prepare("SELECT * FROM gf_users WHERE number_id = ?");
                $stmt_recipient->bind_param("i", $delivery_data['recipient_number_id']);
                $stmt_recipient->execute();
                $result_recipient = $stmt_recipient->get_result();
                if ($result_recipient->num_rows > 0) {
                    $recipient_is_user = true;
                    $recipient_data = $result_recipient->fetch_assoc();
                }
                $stmt_recipient->close();
            }
        }

        echo json_encode([
            'success' => true,
            'data' => $row,
            'has_delivery' => $has_delivery,
            'delivery' => $delivery_data,
            'recipient_is_user' => $recipient_is_user,
            'recipient_data' => $recipient_data
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>