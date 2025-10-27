<?php
// Activar error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();


// Incluir la conexión a la DB
include '../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number_id = (int)$_POST['number_id'];
    $original_number_id = (int)$_POST['original_number_id'];
    $name = strtoupper(trim($_POST['name']));
    $company_name = strtoupper(trim($_POST['company_name']));
    $cell_phone = strtoupper(trim($_POST['cell_phone']));
    $email = strtoupper(trim($_POST['email']));
    $address = strtoupper(trim($_POST['address']));
    $city = strtoupper(trim($_POST['city']));
    $registration_date = strtoupper($_POST['registration_date']);
    $gender = strtoupper(trim($_POST['gender']));
    $data_update = strtoupper(trim($_POST['data_update'])); // Usar el valor enviado
    $updated_by = 'APLICATIVO BENEFICIOS'; // Forzar este valor en upper
    $updated_by_username = strtoupper(isset($_SESSION['username']) ? $_SESSION['username'] : '');
    $sede = strtoupper(trim($_POST['sede'])); // Nuevo campo sede, convertir a UPPER

    if (empty($original_number_id) || empty($name) || empty($gender)) {
        echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes.']);
        exit;
    }

    // Actualizar en DB usando original_number_id para WHERE, incluyendo el nuevo campo
    $stmt = $conn->prepare("UPDATE gf_users SET number_id=?, name=?, company_name=?, cell_phone=?, email=?, address=?, city=?, registration_date=?, gender=?, data_update=?, updated_by=?, updated_by_username=?, sede=? WHERE number_id=?");
    $stmt->bind_param("issssssssssssi", $number_id, $name, $company_name, $cell_phone, $email, $address, $city, $registration_date, $gender, $data_update, $updated_by, $updated_by_username, $sede, $original_number_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el registro para actualizar o no hubo cambios.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}
?>