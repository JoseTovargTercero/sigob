<?php

/**
 * This script is responsible for retrieving employee information based on their identification number (cedula).
 * It receives a JSON payload containing the cedula and returns the employee's details if found.
 * The script also checks if the employee is active or not and returns the appropriate response.
 */

require_once '../sistema_global/conexion.php';
require_once '../sistema_global/session.php';
header('Content-Type: application/json');

// Retrieve the cedula from the JSON payload
$data = json_decode(file_get_contents("php://input"), true);
$cedula = @$data['cedula'];

// Check if cedula is provided
if (@$cedula != '') {

    // Prepare the SQL statement to retrieve employee details
    $stmt = mysqli_prepare($conexion, "SELECT *, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) AS antiguedad, 
    otros_anios, TIMESTAMPDIFF(YEAR, fecha_ingreso, CURDATE()) + otros_anios AS anios_totales 
    FROM empleados WHERE cedula = ?");
    $stmt->bind_param('s', $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if employee is found
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

            // Check if employee is inactive
            if ($row["status"]  == 'R') {
                echo json_encode(['status' => true, "otros_anios" => $row["anios_totales"]]);
            } else {
                echo json_encode(['status' => false, "mensaje" => "Empleado activo. No puede registrar mas de una vez al mismo empleado"]);
            }
        }
    } else {
        // Employee not found
        echo json_encode(['status' => true, "otros_anios" => '']);
    }

    $stmt->close();
} else {
    // No cedula provided
    echo json_encode(['status' => false, "mensaje" => "No se ha proporcionado ninguna cedula"]);
    exit();
}

// Close the database connection
$conexion->close();
