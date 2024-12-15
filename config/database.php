<?php
$DBhostname = 'localhost';
$DBusername = 'root';
$DBpassword = '';

$DBname = 'ajax_form_example';

$conn = mysqli_connect($DBhostname, $DBusername, $DBpassword, $DBname);

if (!$conn) {
    // Debug message to confirm $conn is set
$logFile = __DIR__ . "/../storage/log/mysql_log.txt";
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Connection failed: ".mysqli_connect_error()."" . PHP_EOL, FILE_APPEND);
die("Connection Failed: " . mysqli_connect_error());

} else {
// Debug message to confirm $conn is set
$logFile = __DIR__ . "/../storage/log/mysql_log.txt";
file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Connection successful: " . PHP_EOL, FILE_APPEND);

}
?>