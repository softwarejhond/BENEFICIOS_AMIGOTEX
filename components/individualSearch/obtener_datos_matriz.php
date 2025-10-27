<?php
session_start();
require_once '../../controller/conexion.php';

// Verificar permisos
$rol = $_SESSION['rol'] ?? '';
if (!in_array($rol, [1, 12])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No tienes permisos.']);
    exit;
}

try {
    // Consulta principal
    $sql = "SELECT * FROM gf_users ORDER BY number_id ASC";
    $resultado = mysqli_query($conn, $sql);
    if (!$resultado) {
        throw new Exception('Error en la consulta: ' . mysqli_error($conn));
    }

    $data = [];
    $dominio = "https://beneficios.amigotex.com/";

    while ($user = mysqli_fetch_assoc($resultado)) {
        $row = [
            'Número ID' => $user['number_id'],
            'Nombre' => $user['name'] ?? '',
            'Empresa' => $user['company_name'] ?? '',
            'Celular' => $user['cell_phone'] ?? '',
            'Email' => $user['email'] ?? '',
            'Dirección' => $user['address'] ?? '',
            'Ciudad' => $user['city'] ?? '',
            'Fecha Registro' => $user['registration_date'] ?? '',
            'Género' => $user['gender'] ?? '',
            'Datos Actualizados' => $user['data_update'] ?: 'NO',
            'Actualizado Por' => $user['updated_by'] ?? '',
            'Sede Usuario' => $user['sede'] ?? '',
        ];

        // Verificar entrega
        $stmt_delivery = $conn->prepare("SELECT * FROM gf_gift_deliveries WHERE user_number_id = ? AND YEAR(reception_date) = YEAR(CURDATE()) LIMIT 1");
        $stmt_delivery->bind_param("i", $user['number_id']);
        $stmt_delivery->execute();
        $result_delivery = $stmt_delivery->get_result();

        if ($result_delivery->num_rows > 0) {
            $delivery = $result_delivery->fetch_assoc();
            $row['Tiene Entrega'] = 'SI';
            $row['Fecha Entrega'] = $delivery['reception_date'] ?? '';
            $row['Receptor ID'] = $delivery['recipient_number_id'] ?? '';
            $row['Receptor Nombre'] = $delivery['recipient_name'] ?? '';
            $row['Sede Entrega'] = $delivery['sede'] ?? '';
            $row['Tipo Entrega'] = $delivery['tipo_entrega'] ?? '';
            $row['Entregado Por'] = $delivery['delivered_by'] ?? '';

            // Nombre entregador
            $delivered_name = '';
            $stmt_delivered = $conn->prepare("SELECT nombre FROM users WHERE username = ?");
            if ($stmt_delivered && $delivery['delivered_by']) {
                $stmt_delivered->bind_param("s", $delivery['delivered_by']);
                $stmt_delivered->execute();
                $stmt_delivered->bind_result($delivered_name);
                $stmt_delivered->fetch();
                $stmt_delivered->close();
            }
            $row['Nombre Entregado Por'] = $delivered_name ?? '';

            // URLs
            $row['URL Firma'] = $delivery['signature'] ? $dominio . 'img/firmasRegalos/' . $delivery['signature'] : '';
            $row['URL Foto ID'] = $delivery['id_photo'] ? $dominio . 'uploads/idPhotos/' . $delivery['id_photo'] : '';
            $row['URL Carta Autorización'] = ($delivery['authorization_letter'] && $delivery['authorization_letter'] != 'N/A') ? $dominio . 'uploads/cartasAutorizacion/' . $delivery['authorization_letter'] : 'N/A';

            // Receptor
            if ($delivery['recipient_number_id'] != $user['number_id']) {
                $stmt_recipient = $conn->prepare("SELECT * FROM gf_users WHERE number_id = ?");
                $stmt_recipient->bind_param("i", $delivery['recipient_number_id']);
                $stmt_recipient->execute();
                $result_recipient = $stmt_recipient->get_result();
                if ($result_recipient->num_rows > 0) {
                    $recipient = $result_recipient->fetch_assoc();
                    $row['Receptor es Usuario'] = 'SI';
                    $row['Empresa Receptor'] = $recipient['company_name'] ?? '';
                    $row['Ciudad Receptor'] = $recipient['city'] ?? '';
                } else {
                    $row['Receptor es Usuario'] = 'NO';
                    $row['Empresa Receptor'] = '';
                    $row['Ciudad Receptor'] = '';
                }
                $stmt_recipient->close();
            } else {
                $row['Receptor es Usuario'] = 'MISMA PERSONA';
                $row['Empresa Receptor'] = $user['company_name'] ?? '';
                $row['Ciudad Receptor'] = $user['city'] ?? '';
            }
        } else {
            $row['Tiene Entrega'] = 'NO';
            $row['Fecha Entrega'] = '';
            $row['Receptor ID'] = '';
            $row['Receptor Nombre'] = '';
            $row['Sede Entrega'] = '';
            $row['Tipo Entrega'] = '';
            $row['Entregado Por'] = '';
            $row['Nombre Entregado Por'] = '';
            $row['URL Firma'] = '';
            $row['URL Foto ID'] = '';
            $row['URL Carta Autorización'] = '';
            $row['Receptor es Usuario'] = '';
            $row['Empresa Receptor'] = '';
            $row['Ciudad Receptor'] = '';
        }
        $stmt_delivery->close();
        $data[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conn);
?>