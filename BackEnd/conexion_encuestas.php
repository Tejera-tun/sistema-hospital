
<?php

$servidor = strtolower($_SERVER["HTTP_HOST"] ?? "localhost");
$servidor = explode(":", $servidor)[0];

if ($servidor === "localhost" || $servidor === "127.0.0.1") {

    // CONEXIÓN LOCAL XAMPP

    $conexion = new mysqli(
        "localhost",
        "root",
        "1234",
        "sistema_encuestas"
    );

} else {

    // CONEXIÓN INFINITYFREE

    $conexion = new mysqli(
        "sql110.infinityfree.com",
        "if0_43375476",
        "REEMPLAZAR_CON_NUEVA_CONTRASEÑA",
        "if0_43375476_sistema_encuestas",
        3306
    );

}

if ($conexion->connect_error) {
    die("Error de conexión MySQL: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

?>
