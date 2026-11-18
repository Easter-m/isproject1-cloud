<?php
// Copy this file to config.php on the server and fill in the real values.
// config.php is listed in .gitignore so the password never reaches GitHub.

$db_host = 'localhost';
$db_name = 'isproject1';
$db_user = 'isproj_user';
$db_pass = 'CHANGE_ME';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('Could not connect to the database. Check config.php.');
}

session_start();

// Escape any value before printing it in HTML
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
