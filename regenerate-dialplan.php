<?php
/**
 * Regenerate dialplan using Dialplan_generator library
 */

// Set environment for CLI
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/regenerate-dialplan';
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Load CodeIgniter
define('BASEPATH', __DIR__ . '/system/');
define('APPPATH', __DIR__ . '/application/');
define('ENVIRONMENT', 'production');

// Minimal bootstrap
require_once BASEPATH . 'core/Common.php';
require_once APPPATH . 'config/constants.php';

// Create simple CI object
class CI_Controller {
    private static $instance;
    public function __construct() {
        self::$instance = $this;
        log_message('info', 'CI_Controller initialized');
    }
    public static function &get_instance() {
        return self::$instance;
    }
    public function load($helper) {
        if (is_object($helper)) {
            foreach ($helper as $key => $val) {
                $this->$key = $val;
            }
        }
        return $this;
    }
}

$CI = new CI_Controller();

// Load database
require_once APPPATH . 'config/database.php';
$dbConfig = $db['default'];

class CI_DB_Driver {
    private $pdo;
    public function __construct($config) {
        $dsn = "mysql:host={$config['hostname']};dbname={$config['database']};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]);
    }
    public function query($sql) {
        $stmt = $this->pdo->query($sql);
        return new class($stmt) {
            private $stmt;
            public function __construct($stmt) { $this->stmt = $stmt; }
            public function result() { return $this->stmt->fetchAll(); }
        };
    }
}

$CI->db = new CI_DB_Driver($dbConfig);

// Load models
require_once APPPATH . 'models/Ivr_menu_model.php';
require_once APPPATH . 'models/Ivr_action_model.php';

$CI->Ivr_menu_model = new Ivr_menu_model();
$CI->Ivr_action_model = new Ivr_action_model();

// Load and execute Dialplan_generator
require_once APPPATH . 'libraries/Dialplan_generator.php';
$generator = new Dialplan_generator();

echo "Regenerating dialplan...\n";
$result = $generator->generate();

if ($result) {
    echo "✓ Dialplan regenerated successfully\n";
    echo "\nFirst 30 lines:\n";
    $lines = file('/etc/asterisk/extensions_dialer.conf');
    echo implode('', array_slice($lines, 0, 30));
} else {
    echo "✗ Dialplan generation failed\n";
}
