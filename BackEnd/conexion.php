<?php

$conexion = new mysqli(
    "localhost",
    "root",
    "1234",
    "sistema_hospital"
);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");

?>