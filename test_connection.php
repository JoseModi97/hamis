<?php
require_once '/var/www/html/smis/adodb/adodb.inc.php';

// Use 'mysqli' (preferred) or 'mysql' if using older server
$db = NewADOConnection('mysql');

// Optional: show detailed debug messages
$db->debug = true;

// Connection parameters
$host = 'smisdbclone.uonbi.ac.ke';
//smis
// $username = 'websmis';
// $password = 'smis_dev_2023';
// $database = 'smis';

//hamis
$username = 'hamis_user';
$password = 'hamis_dev_2025';
$database = 'hamis';

//hamis


try {
    $conn = $db->Connect($host, $username, $password, $database);
    if ($conn) {
        echo "? Connected to MySQL successfully!\n";
    } else {
        echo "? Failed to connect: " . $db->ErrorMsg() . "\n";
    }
} catch (Exception $e) {
    echo "? Exception: " . $e->getMessage() . "\n";
}
