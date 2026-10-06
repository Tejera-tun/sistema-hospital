<?php

$conexion = new mysqli("localhost", "root", "1234", "sistema_encuestas");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

?>