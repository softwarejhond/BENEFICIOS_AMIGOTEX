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
    file_put_contents($logFile, "[$timestamp] RESEND - $message" . PHP_EOL, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resend_email') {
    $user_number_id = (int)$_POST['user_number_id'];

    if (empty($user_number_id)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'ID de usuario faltante.']);
        exit;
    }

    // Obtener datos del usuario y la entrega
    $query = "SELECT 
                u.email, u.name, 
                d.recipient_number_id, d.recipient_name, d.sede, d.tipo_entrega, d.delivered_by, d.reception_date
              FROM gf_users u 
              INNER JOIN gf_gift_deliveries d ON u.number_id = d.user_number_id 
              WHERE u.number_id = ? AND YEAR(d.reception_date) = YEAR(CURDATE())";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_number_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'No se encontró la entrega para este usuario.']);
        exit;
    }

    $data = $result->fetch_assoc();
    $userEmail = $data['email'];
    $userName = $data['name'];
    $recipientNumberId = $data['recipient_number_id'];
    $recipientName = $data['recipient_name'];
    $sede = $data['sede'];
    $tipoEntrega = $data['tipo_entrega'];
    $deliveredBy = $data['delivered_by'];
    $receptionDate = $data['reception_date'];
    $stmt->close();

    // Validar email
    if (empty($userEmail) || !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'El usuario no tiene un correo electrónico válido.']);
        exit;
    }

    // Obtener el nombre del asesor
    $asesorQuery = $conn->prepare("SELECT nombre FROM users WHERE username = ?");
    $asesorQuery->bind_param("s", $deliveredBy);
    $asesorQuery->execute();
    $asesorResult = $asesorQuery->get_result();
    $asesorName = '';
    if ($asesorResult->num_rows > 0) {
        $asesorData = $asesorResult->fetch_assoc();
        $asesorName = $asesorData['nombre'];
    }
    $asesorQuery->close();

    // Preparar contenido del correo (igual al original)
    $subject = "🎁 Regalo Entregado - Beneficios Amigotex";
    $isSamePerson = ($recipientNumberId == $user_number_id);
    $entregadoA = $isSamePerson ? 'usted mismo' : $recipientName;
    
    // Formatear fecha de recepción
    $formattedDate = date('Y-m-d H:i:s', strtotime($receptionDate));
    
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
            <p>Queremos confirmarte que ya hemos hecho efectivo tu beneficio {$tipoEntrega} del Regalo de Navidad 2025.</p>
            <p>Dicho Beneficio fue entregado a {$entregadoA} en la sede {$sede}, el día {$formattedDate} por el(la) asesor(a) {$asesorName}.</p>
            <p>Este es un pequeño gesto para agradecerte por ser parte fundamental de nuestro fondo de empleados Amigotex. Tu esfuerzo y dedicación son los que hacen posible el éxito de nuestro Fondo.</p>
            <p>Deseamos que lo disfrutes y que esta temporada esté llena de alegría, paz y momentos inolvidables para ti y tu familia.</p>
            <p>¡Felices Fiestas!</p>
            <div style='text-align: center; margin-top: 20px;'>
                <img src='https://amigotex.com/wp-content/uploads/2021/10/Logo-40-anos_Mesa-de-trabajo-1-1536x612.png' alt='Logo 40 años' style='width: 200px; height: auto;'>
            </div>";
    
    if (!$isSamePerson) {
        $message .= "
            <p>Esta persona ha sido autorizada por usted para recibir el regalo.</p>
            <p>{$recipientName} con identificación No. {$recipientNumberId}</p>";
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

    // Enviar correo usando PHPMailer
    try {
        // Obtener configuración SMTP (id=1)
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
            
            logError("Correo reenviado exitosamente a $userEmail para usuario $user_number_id");
            
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Correo reenviado exitosamente.']);
        } else {
            $emailError = "Configuración SMTP no encontrada";
            logError($emailError);
            ob_clean();
            echo json_encode(['success' => false, 'message' => $emailError]);
        }
        $smtpQuery->close();
        
    } catch (Exception $e) {
        $emailError = "Error al reenviar correo: " . $e->getMessage();
        logError($emailError);
        ob_clean();
        echo json_encode(['success' => false, 'message' => $emailError]);
    } catch (Error $e) {
        $emailError = "Error fatal al reenviar correo: " . $e->getMessage();
        logError($emailError);
        ob_clean();
        echo json_encode(['success' => false, 'message' => $emailError]);
    }
    
} else {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida.']);
}

exit();
?>