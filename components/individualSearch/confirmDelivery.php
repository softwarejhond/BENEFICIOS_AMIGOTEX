<?php
// Activar error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Asegurarse de que no haya salida antes
ob_start();

// Iniciar sesión
session_start();

// Incluir la conexión a la DB
include '../../controller/conexion.php';

// Configurar zona horaria Bogotá
date_default_timezone_set('America/Bogota');

// Configurar headers antes de cualquier salida
header('Content-Type: application/json');

// Incluir PHPMailer
require __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Función para log de errores
function logError($message) {
    $logFile = __DIR__ . '/email_errors.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_number_id = (int)$_POST['user_number_id'];
    $recipient_number_id = (int)$_POST['recipient_number_id'];
    $recipient_name = trim($_POST['recipient_name']);
    $signature = $_POST['signature']; // Base64 PNG
    $authorization_letter = isset($_POST['authorization_letter']) ? $_POST['authorization_letter'] : null;
    $id_photo = isset($_FILES['id_photo']) ? $_FILES['id_photo'] : null;
    $sede = $_SESSION['sede'] ?? '';
    $tipo_entrega = $_SESSION['tipo_entrega'] ?? '';
    $delivered_by = $_SESSION['username'];

    // Validar campos obligatorios
    if (empty($user_number_id)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'ID de usuario faltante.']);
        exit;
    }
    if (empty($recipient_number_id)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'ID del receptor faltante.']);
        exit;
    }
    if (empty($recipient_name)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Nombre del receptor faltante.']);
        exit;
    }
    if (empty($signature)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Firma faltante.']);
        exit;
    }
    if (empty($delivered_by)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Usuario entregador faltante.']);
        exit;
    }
    if (empty($tipo_entrega)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Tipo de entrega faltante.']);
        exit;
    }
    if (!$id_photo || $id_photo['error'] !== UPLOAD_ERR_OK) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Error con la foto de identificación.']);
        exit;
    }

    // Validar que recipient_name no sea 'undefined'
    if ($recipient_name === 'undefined') {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Nombre del receptor inválido.']);
        exit;
    }

    // Verificar que no haya entrega previa del mismo tipo este año
    $stmt_check_type = $conn->prepare("SELECT COUNT(*) FROM gf_gift_deliveries WHERE user_number_id = ? AND tipo_entrega = ? AND YEAR(reception_date) = YEAR(CURDATE())");
    $stmt_check_type->bind_param("is", $user_number_id, $tipo_entrega);
    $stmt_check_type->execute();
    $stmt_check_type->bind_result($type_count);
    $stmt_check_type->fetch();
    $stmt_check_type->close();

    if ($type_count > 0) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => "Ya existe una entrega de tipo '$tipo_entrega' para esta persona en el año actual."]);
        exit;
    }

    // Verificar que los datos estén actualizados
    $stmt_check = $conn->prepare("SELECT data_update FROM gf_users WHERE number_id = ?");
    $stmt_check->bind_param("i", $user_number_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        if ($row['data_update'] !== 'SI') {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Los datos del usuario no han sido actualizados. No se puede confirmar la entrega.']);
            exit;
        }
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        exit;
    }
    $stmt_check->close();

    // Manejar firma: guardar como PNG con fondo blanco
    $signaturePath = '';
    if (!empty($signature)) {
        $signatureData = str_replace('data:image/png;base64,', '', $signature);
        $signatureData = base64_decode($signatureData);
        $date = date('YmdHis');
        $signatureFileName = "firma_{$user_number_id}_{$tipo_entrega}_{$date}.png";
        file_put_contents("../../img/firmasRegalos/{$signatureFileName}", $signatureData);
        $signaturePath = $signatureFileName;
    }

    // Manejar carta de autorización
    $letterPath = $authorization_letter;
    if ($authorization_letter !== 'N/A' && isset($_FILES['authorization_letter'])) {
        $file = $_FILES['authorization_letter'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../uploads/cartasAutorizacion/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $date = date('YmdHis');
            $originalExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $letterFileName = "carta_{$user_number_id}_{$tipo_entrega}_{$date}.{$originalExtension}";
            move_uploaded_file($file['tmp_name'], "../../uploads/cartasAutorizacion/{$letterFileName}");
            $letterPath = $letterFileName;
        } else {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Error al subir el archivo de autorización.']);
            exit;
        }
    }

    // Manejar foto de identificación
    $photoPath = '';
    if ($id_photo && $id_photo['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/idPhotos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $date = date('YmdHis');
        $photoFileName = "id_{$user_number_id}_{$tipo_entrega}_{$date}." . pathinfo($id_photo['name'], PATHINFO_EXTENSION);
        move_uploaded_file($id_photo['tmp_name'], "../../uploads/idPhotos/{$photoFileName}");
        $photoPath = $photoFileName;
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Error al subir la foto de identificación.']);
        exit;
    }

    // Insertar en DB
    $stmt = $conn->prepare("INSERT INTO gf_gift_deliveries (user_number_id, recipient_number_id, recipient_name, signature, authorization_letter, sede, tipo_entrega, delivered_by, id_photo, reception_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iisssssss", $user_number_id, $recipient_number_id, $recipient_name, $signaturePath, $letterPath, $sede, $tipo_entrega, $delivered_by, $photoPath);
    
    if ($stmt->execute()) {
        error_log("Insert successful for user $user_number_id, tipo: $tipo_entrega");
        
        $emailSent = false;
        $emailError = '';

        // Obtener el email del usuario registrado
        $emailQuery = $conn->prepare("SELECT email, name FROM gf_users WHERE number_id = ?");
        $emailQuery->bind_param("i", $user_number_id);
        $emailQuery->execute();
        $emailResult = $emailQuery->get_result();
        
        if ($emailResult->num_rows > 0) {
            $userData = $emailResult->fetch_assoc();
            $userEmail = $userData['email'];
            $userName = $userData['name'];

            // Obtener el nombre del asesor
            $asesorQuery = $conn->prepare("SELECT nombre FROM users WHERE username = ?");
            $asesorQuery->bind_param("s", $delivered_by);
            $asesorQuery->execute();
            $asesorResult = $asesorQuery->get_result();
            $asesorName = '';
            if ($asesorResult->num_rows > 0) {
                $asesorData = $asesorResult->fetch_assoc();
                $asesorName = $asesorData['nombre'];
            }
            $asesorQuery->close();

            $currentDateTime = date('Y-m-d H:i:s');

            // Solo intentar enviar correo si hay email válido
            if (!empty($userEmail) && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                $subject = "🎁 Regalo Entregado - Beneficios Amigotex";
                $isSamePerson = ($recipient_number_id == $user_number_id);
                $entregadoA = $isSamePerson ? 'usted mismo' : $recipient_name;
                
                $message = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
                        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                        h1 { color: #333; text-align: center; }
                        p { color: #555; line-height: 1.6; }
                        .footer { margin-top: 20px; font-size: 12px; color: #999; text-align: center; }
                        .gift-icon { font-size: 48px; text-align: center; margin: 20px 0; }
                        .info-box { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='gift-icon'>🎁</div>
                        <h1>¡Felicidades, {$userName}!</h1>
                        <p>¡Grandes noticias!</p>
                        <p>Queremos confirmarte que ya hemos hecho efectivo tu beneficio {$tipo_entrega} del Regalo de Navidad 2025.</p>
                        <p>Dicho Beneficio fue entregado a {$entregadoA} en la sede {$sede}, el día {$currentDateTime} por el(la) asesor(a) {$asesorName}.</p>
                        <p>Este es un pequeño gesto para agradecerte por ser parte fundamental de nuestro fondo de empleados Amigotex. Tu esfuerzo y dedicación son los que hacen posible el éxito de nuestro Fondo.</p>
                        <p>Deseamos que lo disfrutes y que esta temporada esté llena de alegría, paz y momentos inolvidables para ti y tu familia.</p>
                        <p>¡Felices Fiestas!</p>
                        <div style='text-align: center; margin-top: 20px;'>
                            <img src='https://amigotex.com/wp-content/uploads/2021/10/Logo-40-anos_Mesa-de-trabajo-1-1536x612.png' alt='Logo 40 años' style='width: 200px; height: auto;'>
                        </div>";
                
                if (!$isSamePerson) {
                    $message .= "
                        <p>Esta persona ha sido autorizada por usted para recibir el regalo.</p>
                        <p>{$recipient_name} con identificación No. {$recipient_number_id}</p>";
                }
                
                $message .= "
                        <p>Esperamos que disfrute su obsequio.</p>
                        <div class='footer'>
                            <p>Este es un mensaje automático, por favor no responda.</p>
                            <p>© SYGNIA - Made by <span class='eagle-span'>Eagle Software</span></p>
                        </div>
                    </div>
                </body>
                </html>
                ";

                // Envío de correo usando PHPMailer
                try {
                    $smtpQuery = $conn->prepare("SELECT * FROM smtpconfig WHERE id = 1");
                    $smtpQuery->execute();
                    $smtpResult = $smtpQuery->get_result();
                    
                    if ($smtpResult->num_rows > 0) {
                        $config = $smtpResult->fetch_assoc();
                        
                        $mail = new PHPMailer(true);
                        $mail->SMTPDebug = 0;
                        $mail->isSMTP();
                        $mail->Host = $config['host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $config['email'];
                        $mail->Password = $config['password'];
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port = $config['port'];
                        $mail->Timeout = 60;
                        $mail->SMTPKeepAlive = true;
                        $mail->SMTPOptions = [
                            'ssl' => [
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            ]
                        ];
                        
                        $mail->setFrom($config['email'], 'Amigotex - Plataforma Beneficios');
                        $mail->CharSet = 'UTF-8';
                        $mail->addAddress($userEmail);
                        $mail->isHTML(true);
                        $mail->Subject = $subject;
                        $mail->Body = $message;
                        $mail->AltBody = strip_tags($message);
                        
                        $mail->send();
                        $emailSent = true;
                        logError("Correo enviado exitosamente a $userEmail para $tipo_entrega");
                    } else {
                        $emailError = "Configuración SMTP no encontrada";
                        logError($emailError);
                    }
                    $smtpQuery->close();
                } catch (Exception $e) {
                    $emailError = "Error al enviar correo: " . $e->getMessage();
                    logError($emailError);
                } catch (Error $e) {
                    $emailError = "Error fatal al enviar correo: " . $e->getMessage();
                    logError($emailError);
                }
            } else {
                $emailError = "Email no válido o vacío: $userEmail";
                logError($emailError);
            }
        } else {
            $emailError = "Usuario no encontrado para email";
            logError($emailError);
        }
        $emailQuery->close();

        ob_clean();
        if ($emailSent) {
            echo json_encode(['success' => true, 'message' => 'Entrega confirmada y correo enviado exitosamente.']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Entrega confirmada correctamente. ' . ($emailError ? 'Error en correo: ' . $emailError : 'No se pudo enviar el correo.')]);
        }
    } else {
        error_log("Insert failed: " . $stmt->error);
        ob_clean();
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
    $stmt->close();
} else {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}

exit();
?>