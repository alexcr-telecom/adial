#!/usr/bin/env php
<?php
/**
 * Refresh Endpoints Cache
 * CLI script to scan Asterisk and update the endpoints cache in database
 * Should be run periodically via cron (e.g., every 5 minutes)
 */

// Prevent running as web script
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line\n");
}

// Load configuration
$daemon_config = require __DIR__ . '/ami-daemon/config.php';

// Database connection from daemon config
$db = new mysqli(
    $daemon_config['database']['host'],
    $daemon_config['database']['username'],
    $daemon_config['database']['password'],
    $daemon_config['database']['database']
);

if ($db->connect_error) {
    die("✗ Database connection failed: " . $db->connect_error . "\n");
}

/**
 * Scan SIP peers
 */
function scan_sip_peers($ami_config) {
    $socket = connect_ami($ami_config);
    if (!$socket) return [];

    try {
        ami_login($socket, $ami_config);
        send_action($socket, ['Action' => 'Command', 'Command' => 'sip show peers']);
        $response = read_command_response($socket);

        $peers = [];
        $lines = explode("\n", $response);

        foreach ($lines as $line) {
            if (preg_match('/^Output:\s+(\S+)/', $line, $matches)) {
                $peer = preg_replace('/\/.*$/', '', $matches[1]);

                if (empty($peer) || $peer === 'Name' || $peer === 'Asterisk' ||
                    strpos($peer, '(Unspecified)') !== false ||
                    strpos($line, 'sip show peers') !== false) {
                    continue;
                }

                $status = 'Unknown';
                if (preg_match('/OK\s*\(/', $line)) {
                    $status = 'Online';
                } elseif (preg_match('/UNREACHABLE|Unmonitored/', $line)) {
                    $status = 'Offline';
                }

                if (!in_array($peer, array_column($peers, 'name'))) {
                    $peers[] = ['name' => $peer, 'status' => $status];
                }
            }
        }

        fclose($socket);
        return $peers;
    } catch (Exception $e) {
        if ($socket) fclose($socket);
        return [];
    }
}

/**
 * Scan PJSIP endpoints
 */
function scan_pjsip_endpoints($ami_config) {
    $socket = connect_ami($ami_config);
    if (!$socket) return [];

    try {
        ami_login($socket, $ami_config);
        send_action($socket, ['Action' => 'Command', 'Command' => 'pjsip show endpoints']);
        $response = read_command_response($socket);

        $endpoints = [];
        $lines = explode("\n", $response);

        foreach ($lines as $line) {
            if (preg_match('/^Output:\s+Endpoint:\s+(\S+?)(?:\/|\s|$)/', $line, $matches)) {
                $endpoint = $matches[1];

                if (strpos($endpoint, '<') !== false) continue;

                $status = 'Unknown';
                if (strpos($line, 'Not in use') !== false) {
                    $status = 'Available';
                } elseif (strpos($line, 'Unavailable') !== false) {
                    $status = 'Unavailable';
                } elseif (strpos($line, 'In use') !== false) {
                    $status = 'In use';
                }

                if (!in_array($endpoint, array_column($endpoints, 'name'))) {
                    $endpoints[] = ['name' => $endpoint, 'status' => $status];
                }
            }
        }

        fclose($socket);
        return $endpoints;
    } catch (Exception $e) {
        if ($socket) fclose($socket);
        return [];
    }
}

function connect_ami($config) {
    $socket = @fsockopen($config['host'], $config['port'], $errno, $errstr, 5);
    if (!$socket) return false;

    stream_set_blocking($socket, true);
    stream_set_timeout($socket, 5);

    $welcome = '';
    $timeout = time() + 5;
    while (time() < $timeout) {
        $line = fgets($socket);
        if ($line === false) break;
        $welcome .= $line;
        if (strpos($welcome, "\r\n\r\n") !== false) break;
    }

    return $socket;
}

function ami_login($socket, $config) {
    send_action($socket, [
        'Action' => 'Login',
        'Username' => $config['username'],
        'Secret' => $config['password']
    ]);

    $response = '';
    $timeout = time() + 5;
    while (time() < $timeout) {
        $line = fgets($socket);
        if ($line === false) break;
        $response .= $line;
        if (strpos($response, "\r\n\r\n") !== false) break;
    }

    if (stripos($response, 'Success') === false) {
        throw new Exception("AMI login failed");
    }
}

function send_action($socket, $params) {
    $message = '';
    foreach ($params as $key => $value) {
        $message .= "$key: $value\r\n";
    }
    $message .= "\r\n";
    fwrite($socket, $message);
}

function read_command_response($socket) {
    $response = '';
    $timeout = time() + 5;
    $emptyLines = 0;

    while (time() < $timeout) {
        $line = fgets($socket);
        if ($line === false) {
            usleep(10000);
            continue;
        }

        $response .= $line;

        if (trim($line) === '') {
            $emptyLines++;
            if ($emptyLines >= 3) break;
        } else {
            $emptyLines = 0;
        }
    }

    return $response;
}

// Main execution
try {
    echo "Refreshing endpoints cache...\n";
    $start_time = microtime(true);

    // Scan endpoints
    $sip_peers = scan_sip_peers($daemon_config['ami']);
    $pjsip_endpoints = scan_pjsip_endpoints($daemon_config['ami']);

    $endpoints = [];
    foreach ($sip_peers as $peer) {
        $endpoints[] = ['technology' => 'SIP', 'resource' => $peer['name'], 'state' => $peer['status']];
    }
    foreach ($pjsip_endpoints as $endpoint) {
        $endpoints[] = ['technology' => 'PJSIP', 'resource' => $endpoint['name'], 'state' => $endpoint['status']];
    }

    // Update database
    $db->begin_transaction();

    // Clear old cache
    $db->query("TRUNCATE TABLE endpoints_cache");

    // Insert new endpoints
    if (!empty($endpoints)) {
        $stmt = $db->prepare("INSERT INTO endpoints_cache (technology, resource, state, created_at) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $db->error);
        }

        $now = date('Y-m-d H:i:s');

        foreach ($endpoints as $endpoint) {
            $stmt->bind_param('ssss', $endpoint['technology'], $endpoint['resource'], $endpoint['state'], $now);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }

        $stmt->close();
    }
    $db->commit();

    $duration = round(microtime(true) - $start_time, 2);
    $count = count($endpoints);
    $sip_count = count($sip_peers);
    $pjsip_count = count($pjsip_endpoints);

    echo "✓ Successfully refreshed cache with {$count} endpoints in {$duration}s\n";
    echo "  - SIP peers: {$sip_count}\n";
    echo "  - PJSIP endpoints: {$pjsip_count}\n";

    $db->close();

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollback();
        $db->close();
    }
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
