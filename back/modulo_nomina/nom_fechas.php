<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sigob";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Paso 1: Agregar una nueva columna temporal de tipo DATE
$sql = "ALTER TABLE empleados ADD COLUMN fecha_ingreso_temp DATE";
if ($conn->query($sql) === TRUE) {
    echo "Columna temporal agregada exitosamente.\n";
} else {
    echo "Error al agregar columna temporal: " . $conn->error . "\n";
}

// Paso 2: Convertir las fechas y almacenarlas en la nueva columna
$sql = "UPDATE empleados SET fecha_ingreso_temp = STR_TO_DATE(fecha_ingreso, '%d/%m/%Y')";
if ($conn->query($sql) === TRUE) {
    echo "Fechas convertidas exitosamente.\n";
} else {
    echo "Error al convertir fechas: " . $conn->error . "\n";
}

// Paso 3: Eliminar la columna original
$sql = "ALTER TABLE empleados DROP COLUMN fecha_ingreso";
if ($conn->query($sql) === TRUE) {
    echo "Columna original eliminada exitosamente.\n";
} else {
    echo "Error al eliminar columna original: " . $conn->error . "\n";
}

// Paso 4: Renombrar la columna temporal para que tenga el mismo nombre que la original
$sql = "ALTER TABLE empleados CHANGE COLUMN fecha_ingreso_temp fecha_ingreso DATE";
if ($conn->query($sql) === TRUE) {
    echo "Columna temporal renombrada exitosamente.\n";
} else {
    echo "Error al renombrar columna temporal: " . $conn->error . "\n";
}

// Cerrar la conexión
$conn->close();
?>
