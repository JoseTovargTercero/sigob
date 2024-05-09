<?php
// Conexión a la base de datos (reemplaza con tus propios datos)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sigob";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Objeto recibido
$objeto = json_decode(file_get_contents('php://input'), true);

// Datos del objeto
$nombre = $objeto["nombre"];
$grados = $objeto["grados"];
$pasos = $objeto["pasos"];
$anioPasos = $objeto["anioPasos"];
$tabulador = $objeto["tabulador"];

// Insertar en la tabla tabuladores
$timestamp = date("Y-m-d H:i:s"); // Timestamp actual
$sql_tabuladores = "INSERT INTO tabuladores (nombre, grado, pasos, aniosPasos, timestamp) VALUES ('$nombre', '$grados', $pasos, $anioPasos, '$timestamp')";

if ($conn->query($sql_tabuladores) !== TRUE) {
    echo "Error al insertar datos en tabuladores: " . $conn->error;
} else {
    // Obtener el ID insertado en tabuladores
    $tabuladores_id = $conn->insert_id;

    // Insertar en la tabla tabuladores_estr
    foreach ($tabulador as $data) {
        $grado = $data[0];
        $paso = $data[1];
        $monto = $data[2];

        $sql_estr = "INSERT INTO tabuladores_estr (grado, paso, monto, tabulador_id) VALUES ('$grado', '$paso', $monto, $tabuladores_id)";

        if ($conn->query($sql_estr) !== TRUE) {
            echo "Error al insertar datos en tabuladores_estr: " . $conn->error;
        }
    }

    echo "Datos insertados correctamente.";
}

// Cerrar conexión
$conn->close();
?>
