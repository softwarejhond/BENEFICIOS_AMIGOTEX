<?php
// Activar error reporting para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../vendor/autoload.php'; // Incluir PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Incluir la conexión a la DB desde conexion.php
include '../../controller/conexion.php'; // Ajusta la ruta si es necesario

// Procesar el archivo si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    if (!empty($file)) {
        try {
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Saltar la primera fila si es encabezado
            array_shift($rows);

            $successCount = 0;
            $errors = [];
            $inserts = 0;
            $updates = 0;

            foreach ($rows as $row) {
                // Mapear columnas con validación para evitar errores si no existen
                $number_id = (int)preg_replace('/\D/', '', $row[0]); // A - Eliminar caracteres no numéricos y convertir a entero
                $name = strtoupper(trim($row[1])); // B - Convertir a UPPER
                $company_name = strtoupper(trim($row[2])); // C - Convertir a UPPER
                $cell_phone = strtoupper(trim($row[3])); // D - Convertir a UPPER
                $email = strtoupper(trim($row[4])); // E - Convertir a UPPER
                $address = strtoupper(trim($row[5])); // F - Convertir a UPPER
                $city = strtoupper(trim($row[6])); // G - Convertir a UPPER
                $registration_date = date('Y-m-d', strtotime($row[7])); // H
                $gender_raw = strtoupper(trim($row[8])); // I - Convertir a UPPER
                $gender = ($gender_raw === 'F') ? 'MUJER' : (($gender_raw === 'M') ? 'HOMBRE' : 'OTRO');
                $data_update = isset($row[9]) ? strtoupper(trim($row[9])) : ''; // J - Convertir a UPPER (SI/NO), con validación
                $updated_by = isset($row[10]) ? strtoupper(trim($row[10])) : ''; // K - Convertir a UPPER
                $sede = isset($row[11]) ? strtoupper(trim($row[11])) : ''; // L - Convertir a UPPER

                // Validar campos obligatorios
                if (empty($number_id) || empty($name) || empty($gender)) {
                    $errors[] = "Fila inválida: number_id, name o gender faltante.";
                    continue;
                }

                // Verificar si ya tiene entrega este año
                $checkDeliveryStmt = $conn->prepare("SELECT COUNT(*) FROM gf_gift_deliveries WHERE user_number_id = ? AND YEAR(reception_date) = YEAR(CURDATE())");
                $checkDeliveryStmt->bind_param("i", $number_id);
                $checkDeliveryStmt->execute();
                $checkDeliveryStmt->bind_result($deliveryCount);
                $checkDeliveryStmt->fetch();
                $checkDeliveryStmt->close();

                if ($deliveryCount > 0) {
                    // Omitir este registro
                    continue;
                }

                // Verificar si existe
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM gf_users WHERE number_id = ?");
                $checkStmt->bind_param("i", $number_id);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();

                if ($count > 0) {
                    // Actualizar
                    $stmt = $conn->prepare("UPDATE gf_users SET name=?, company_name=?, cell_phone=?, email=?, address=?, city=?, registration_date=?, gender=?, data_update=?, updated_by=?, sede=? WHERE number_id=?");
                    $stmt->bind_param("sssssssssssi", $name, $company_name, $cell_phone, $email, $address, $city, $registration_date, $gender, $data_update, $updated_by, $sede, $number_id);
                    if ($stmt->execute()) {
                        $updates++;
                    } else {
                        $errors[] = "Error al actualizar: " . $stmt->error;
                    }
                } else {
                    // Insertar
                    $stmt = $conn->prepare("INSERT INTO gf_users (number_id, name, company_name, cell_phone, email, address, city, registration_date, gender, data_update, updated_by, sede) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssssssssss", $number_id, $name, $company_name, $cell_phone, $email, $address, $city, $registration_date, $gender, $data_update, $updated_by, $sede);
                    if ($stmt->execute()) {
                        $inserts++;
                    } else {
                        $errors[] = "Error al insertar: " . $stmt->error;
                    }
                }
                $stmt->close();
            }

            // Respuesta JSON
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => "Importación completada. Nuevos registros: $inserts. Registros actualizados: $updates. Errores: " . count($errors),
                'inserts' => $inserts,
                'updates' => $updates,
                'errors' => $errors
            ]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al procesar el archivo: ' . $e->getMessage()]);
            exit;
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se seleccionó un archivo.']);
        exit;
    }
}
// No cerrar $conn aquí, ya que es manejado en conexion.php si es necesario
?>