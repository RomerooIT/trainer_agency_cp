<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "coaching_agency";

mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);

try{
    $conn = new mysqli($servername, $username, $password, $dbname);
}catch(mysqli_sql_exception $e){
    error_log("[DB CONNECTION ERROR] " . $e->getMessage(), 0);
}

if ($conn->connect_error) {
    die("Ошибка подлкючения: " . $conn->connect_error);
}

?>
