<?php
session_start();
$success = true;
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sede'])) {
        $_SESSION['sede'] = trim($_POST['sede']);
    }
    if (isset($_POST['tipo_entrega'])) {
        $_SESSION['tipo_entrega'] = trim($_POST['tipo_entrega']);
    }
    if (!isset($_POST['sede']) && !isset($_POST['tipo_entrega'])) {
        $success = false;
        $message = 'Error al guardar la sede o tipo de entrega';
    }
} else {
    $success = false;
    $message = 'Método no permitido';
}
echo json_encode(['success' => $success, 'message' => $message]);
?>