
<?php

$conexion = new mysqli(
    "sql110.infinityfree.com",
    "if0_43375476",
    "FLEM2026",
    "if0_43375476_sistema_encuestas",
    3306
);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

?>
