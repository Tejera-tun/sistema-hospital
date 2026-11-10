
<?php

$servidor = $_SERVER["HTTP_HOST"] ?? "localhost";

if ($servidor === "localhost" || $servidor === "127.0.0.1") {

    // CONEXIÓN LOCAL XAMPP

    $conexion = new mysqli(
        "localhost",
        "root",
        "1234",
        "sistema_hospital"
    );

} else {

    // CONEXIÓN INFINITYFREE

    $conexion = new mysqli(
        "sql110.infinityfree.com",
        "if0_43375476",
        "FLEM2026",
        "if0_43375476_sistema_hospital"
    );

}

if ($conexion->connect_error) {
    die("Error de conexión MySQL: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

?>
