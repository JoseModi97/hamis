<?php
require_once '/var/www/html/smis/adodb/adodb.inc.php';

// Use 'mysqli' (preferred) or 'mysql' if using older server
$db = NewADOConnection('mysql');

// Optional: show detailed debug messages
$db->debug = true;

// Connection parameters loaded from environment
$host = getenv('HAMIS_DB_HOST');
$username = getenv('HAMIS_DB_USER');
$password = getenv('HAMIS_DB_PASS');
$database = getenv('HAMIS_DB_NAME');


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
