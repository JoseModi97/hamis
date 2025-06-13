<?php
// Define constants used in class.php
if (!defined('RelativePath')) {
    define('RelativePath', __DIR__);
}
if (!defined('ADODB_FETCH_ASSOC')) {
    define('ADODB_FETCH_ASSOC', 2);
}

// Minimal stub for FPDF used in class.php
if (!class_exists('FPDF')) {
    class FPDF {}
}

// Simple stub for ADOdb connection
class DummyConnection {
    public $driver;
    public $connectArgs = [];
    public $connected = false;
    public $lastQuery;
    public $getOneResult = null;

    public function __construct($driver) {
        $this->driver = $driver;
    }

    public function Connect($host, $user = null, $password = null, $database = null) {
        $this->connectArgs = [$host, $user, $password, $database];
        $this->connected = true;
        return true;
    }

    public function SetFetchMode($mode) {}
    public function IsConnected() { return $this->connected; }
    public function ErrorMsg() { return ''; }
    public function execute($sql) { $this->lastQuery = $sql; return true; }
    public function GetRow($sql) { $this->lastQuery = $sql; return []; }
    public function GetOne($sql) { $this->lastQuery = $sql; return $this->getOneResult; }
}

function NewADOConnection($driver) {
    return new DummyConnection($driver);
}

require_once __DIR__ . '/../class.php';

use PHPUnit\Framework\TestCase;

class SmisConnectionTest extends TestCase {
    public function testAuthenticateConnectsToDatabase() {
        $smis = new smis();
        $smis->authenticate('user1', 'pass');
        $this->assertEquals('smisdbclone.uonbi.ac.ke', $smis->MysqlSmisDB->connectArgs[0]);
    }

    public function testGetStudentGroupReturnsValue() {
        $smis = new smis();
        $conn = new DummyConnection('mysql');
        $conn->getOneResult = 'GROUP1';
        $smis->MysqlSmisDB = $conn;
        $group = $smis->getStudentGroup('REG123');
        $this->assertEquals('GROUP1', $group);
        $this->assertStringContainsString('REG123', $conn->lastQuery);
    }
}
