<?php
$servidor = "127.0.0.1"; 
$usuario  = "root";
$password = "";
$base_datos = "technest";
$puerto = 3307; 


mysqli_report(MYSQLI_REPORT_OFF);


$conexion = @mysqli_connect($servidor, $usuario, $password, $base_datos, $puerto);

if (!$conexion) {
    die("Error en la conexión a TechNest: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8mb4");
?>
