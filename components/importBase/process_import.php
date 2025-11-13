<?php

// Configuraciones de memoria y tiempo
ini_set('memory_limit', '512M'); // Aumentar límite de memoria a 512MB
ini_set('max_execution_time', 300); // Permitir 5 minutos de ejecución
set_time_limit(300); // Backup del tiempo de ejecución

// Desactivar display_errors para evitar output no deseado
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1); // Mantener logs pero no mostrarlos

// Iniciar buffer de salida para capturar cualquier output no deseado
ob_start();

require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

include '../../controller/conexion.php';

// Limpiar cualquier output previo
ob_clean();

// Función para escribir errores en el log
function writeErrorToLog($message) {
    $logFile = __DIR__ . '/import.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Función para normalizar texto
function normalizeText($text) {
    $text = strtoupper(trim($text));
    
    // Reemplazar vocales con tilde por vocales normales
    $replacements = [
        'Á' => 'A', 'á' => 'A',
        'É' => 'E', 'é' => 'E',
        'Í' => 'I', 'í' => 'I',
        'Ó' => 'O', 'ó' => 'O',
        'Ú' => 'U', 'ú' => 'U',
        'ñ' => 'Ñ'
    ];
    
    return strtr($text, $replacements);
}

// Función para normalizar ciudad (mantenemos por compatibilidad)
function normalizeCity($text) {
    return normalizeText($text);
}

// Función para verificar si una fila está vacía
function isEmptyRow($row) {
    // Verificar si todos los elementos están vacíos o son null
    foreach ($row as $cell) {
        if (!empty(trim($cell))) {
            return false;
        }
    }
    return true;
}

// Función para normalizar valores booleanos (SI/NO)
function normalizeBoolean($value) {
    $value = strtoupper(trim($value));
    $siVariants = ['SI', 'SÍ', 'SI', 'SÍ', 'YES', 'S', 'TRUE', '1'];
    $noVariants = ['NO', 'NO', 'N', 'FALSE', '0'];
    
    if (in_array($value, $siVariants)) {
        return 'SI';
    } elseif (in_array($value, $noVariants)) {
        return 'NO';
    } else {
        return ''; // Dejar vacío si no coincide con ninguna variante
    }
}

// Procesar el archivo si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    if (!empty($file)) {
        try {
            // Escribir inicio de importación en log
            writeErrorToLog("=== INICIO DE IMPORTACIÓN ===");
            writeErrorToLog("Archivo procesado: " . $_FILES['excel_file']['name']);
            
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            array_shift($rows);

            $successCount = 0;
            $errors = [];
            $inserts = 0;
            $updates = 0;
            $skippedRows = 0;
            $rowNumber = 1; // Para contar la fila real en el Excel

            foreach ($rows as $row) {
                $rowNumber++; // Incrementar contador de fila (empezando desde 2 porque quitamos header)

                // Verificar si la fila está completamente vacía
                if (isEmptyRow($row)) {
                    $skippedRows++;
                    continue; // Saltar filas vacías sin contarlas como error
                }

                $row = array_pad($row, 13, ''); // Actualizado a 13 columnas

                $number_id = (int)preg_replace('/\D/', '', $row[0]);
                $name = normalizeText($row[1]); // Aplicar normalización
                $company_name = normalizeText($row[2]); // Aplicar normalización
                $cell_phone = strtoupper(trim($row[3]));
                $email = trim($row[4]);
                $address = strtoupper(trim($row[5]));
                $city = normalizeText($row[6]); // Usar la nueva función
                $available_raw = strtoupper(trim($row[7]));
                $available = ($available_raw === 'SI') ? 'SI' : 'NO'; // Validar y asignar
                $registration_date = date('Y-m-d', strtotime($row[8]));
                $gender_raw = strtoupper(trim($row[9]));
                $gender = ($gender_raw === 'F') ? 'MUJER' : (($gender_raw === 'M') ? 'HOMBRE' : 'OTRO');
                $data_update = normalizeBoolean($row[10]); // Usar la nueva función para normalizar
                $updated_by = !empty(trim($row[11])) ? strtoupper(trim($row[11])) : '';
                $sede = !empty(trim($row[12])) ? strtoupper(trim($row[12])) : '';

                // Validar campos obligatorios
                if (empty($number_id) || empty($name) || ($gender === 'OTRO' && empty($gender_raw))) {
                    $errorMsg = "Fila $rowNumber inválida: number_id=$number_id, name='$name', gender_raw='$gender_raw'";
                    $errors[] = $errorMsg;
                    writeErrorToLog("ERROR VALIDACIÓN - $errorMsg");
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
                    writeErrorToLog("INFO - Fila $rowNumber: Usuario $number_id ya tiene entrega este año, omitido");
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
                    $stmt = $conn->prepare("UPDATE gf_users SET name=?, company_name=?, cell_phone=?, email=?, address=?, city=?, available=?, registration_date=?, gender=?, data_update=?, updated_by=?, sede=? WHERE number_id=?");
                    $stmt->bind_param("sssssssssssssi", $name, $company_name, $cell_phone, $email, $address, $city, $available, $registration_date, $gender, $data_update, $updated_by, $sede, $number_id);
                    if ($stmt->execute()) {
                        $updates++;
                        writeErrorToLog("SUCCESS - Fila $rowNumber: Usuario $number_id actualizado correctamente");
                    } else {
                        $errorMsg = "Error al actualizar fila $rowNumber: " . $stmt->error;
                        $errors[] = $errorMsg;
                        writeErrorToLog("ERROR UPDATE - $errorMsg");
                    }
                } else {
                    // Insertar
                    $stmt = $conn->prepare("INSERT INTO gf_users (number_id, name, company_name, cell_phone, email, address, city, available, registration_date, gender, data_update, updated_by, sede) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssssssssss", $number_id, $name, $company_name, $cell_phone, $email, $address, $city, $available, $registration_date, $gender, $data_update, $updated_by, $sede);
                    if ($stmt->execute()) {
                        $inserts++;
                        writeErrorToLog("SUCCESS - Fila $rowNumber: Usuario $number_id insertado correctamente");
                    } else {
                        $errorMsg = "Error al insertar fila $rowNumber: " . $stmt->error;
                        $errors[] = $errorMsg;
                        writeErrorToLog("ERROR INSERT - $errorMsg");
                    }
                }
                $stmt->close();
            }

            // Escribir resumen en log
            writeErrorToLog("=== RESUMEN DE IMPORTACIÓN ===");
            writeErrorToLog("Nuevos registros: $inserts");
            writeErrorToLog("Registros actualizados: $updates");
            writeErrorToLog("Filas vacías omitidas: $skippedRows");
            writeErrorToLog("Total de errores: " . count($errors));
            writeErrorToLog("=== FIN DE IMPORTACIÓN ===");

            // Cambiar la lógica del resultado final
            ob_clean();
            header('Content-Type: application/json');

            // Preparar mensaje con información completa
            $totalProcessed = $inserts + $updates;
            $message = "Importación completada. Nuevos registros: $inserts. Registros actualizados: $updates.";

            // if ($skippedRows > 0) {
            //     $message .= " Filas vacías omitidas: $skippedRows.";
            // }

            if (count($errors) > 0) {
                $message .= " Errores: " . count($errors);
            }

            echo json_encode([
                'success' => true,
                'message' => $message,
                'inserts' => $inserts,
                'updates' => $updates,
                'skipped_rows' => $skippedRows,
                'errors' => count($errors) > 0 ? array_slice($errors, 0, 5) : [],
                'total_errors' => count($errors)
            ]);
            exit;

        } catch (Exception $e) {
            $errorMsg = 'Error al procesar el archivo: ' . $e->getMessage();
            writeErrorToLog("EXCEPCIÓN - $errorMsg");
            
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $errorMsg]);
            exit;
        }
    } else {
        writeErrorToLog("ERROR - No se seleccionó un archivo");
        
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se seleccionó un archivo.']);
        exit;
    }
}

// Si no es una petición válida
writeErrorToLog("ERROR - Petición no válida");

ob_clean();
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Petición no válida.']);
exit;
?>