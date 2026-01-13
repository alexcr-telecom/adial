<?php
/**
 * A-Dial AMI Daemon Configuration
 */

return [
    // Asterisk AMI Configuration
    'ami' => [
        'host' => '127.0.0.1',
        'port' => 5038,
        'username' => 'dialer',
        'password' => 'ogKKYrJZdHztvIEqiYzSIA==',
        'connect_timeout' => 10000, // milliseconds
        'read_timeout' => 100 // milliseconds
    ],

    // MySQL Database Configuration
    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'adialer_user',
        'password' => 'iCyrq0ghonj2sWzD',
        'database' => 'adialer',
        'charset' => 'utf8mb4'
    ],

    // Asterisk CDR Database Configuration
    'cdr_database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'freepbxuser',
        'password' => 'svCoIj7PHr+A',
        'database' => 'asteriskcdrdb',
        'charset' => 'utf8mb4'
    ],

    // Application Settings
    'app' => [
        'debug_mode' => false,
        'log_level' => 'info', // debug, info, warning, error
        'log_file' => '/var/www/html/adial/logs/ami-daemon.log',
        'pid_file' => '/var/www/html/adial/ami-daemon/daemon.pid',
        'recordings_path' => '/var/spool/asterisk/monitor/adial',
        'sounds_path' => '/var/lib/asterisk/sounds/dialer'
    ],

    // Campaign Processing Settings
    'campaigns' => [
        // How often to check for active campaigns (in seconds)
        'reload_interval' => 10,

        // How often to process each campaign (in seconds)
        'process_interval' => 3,

        // Minimum retry delay (in seconds)
        'min_retry_delay' => 1
    ]
];
