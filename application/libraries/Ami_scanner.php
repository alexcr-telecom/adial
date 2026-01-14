<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * AMI Scanner Library
 * Scans Asterisk for available SIP peers and PJSIP endpoints via AMI
 */
class Ami_scanner {

    private $CI;
    private $ami_host;
    private $ami_port;
    private $ami_username;
    private $ami_password;

    public function __construct() {
        $this->CI =& get_instance();

        // Load AMI configuration from daemon config
        $daemon_config = require FCPATH . 'ami-daemon/config.php';

        $this->ami_host = $daemon_config['ami']['host'];
        $this->ami_port = $daemon_config['ami']['port'];
        $this->ami_username = $daemon_config['ami']['username'];
        $this->ami_password = $daemon_config['ami']['password'];
    }

    /**
     * Get all endpoints (SIP + PJSIP)
     * Returns array in format compatible with form dropdowns
     * Uses cached data from database for performance
     */
    public function get_endpoints() {
        try {
            // Get cached endpoints from database
            $this->CI->db->select('technology, resource, state');
            $this->CI->db->from('endpoints_cache');
            $this->CI->db->order_by('technology', 'ASC');
            $this->CI->db->order_by('resource', 'ASC');
            $query = $this->CI->db->get();

            if ($query->num_rows() > 0) {
                // Return cached data
                $endpoints = [];
                foreach ($query->result_array() as $row) {
                    $endpoints[] = [
                        'technology' => $row['technology'],
                        'resource' => $row['resource'],
                        'state' => $row['state']
                    ];
                }

                return [
                    'success' => true,
                    'data' => $endpoints,
                    'cached' => true
                ];
            } else {
                // Cache is empty, scan and populate
                log_message('info', 'Endpoints cache is empty, performing initial scan');
                return $this->refresh_cache();
            }

        } catch (Exception $e) {
            log_message('error', 'AMI Scanner error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Refresh endpoints cache by scanning AMI
     */
    public function refresh_cache() {
        $endpoints = [];

        try {
            // Get SIP peers
            $sip_peers = $this->scan_sip_peers();
            foreach ($sip_peers as $peer) {
                $endpoints[] = [
                    'technology' => 'SIP',
                    'resource' => $peer['name'],
                    'state' => $peer['status']
                ];
            }

            // Get PJSIP endpoints
            $pjsip_endpoints = $this->scan_pjsip_endpoints();
            foreach ($pjsip_endpoints as $endpoint) {
                $endpoints[] = [
                    'technology' => 'PJSIP',
                    'resource' => $endpoint['name'],
                    'state' => $endpoint['status']
                ];
            }

            // Update cache in database
            $this->update_cache($endpoints);

            return [
                'success' => true,
                'data' => $endpoints,
                'cached' => false
            ];

        } catch (Exception $e) {
            log_message('error', 'AMI Scanner refresh error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => []
            ];
        }
    }

    /**
     * Update endpoints cache in database
     */
    private function update_cache($endpoints) {
        if (empty($endpoints)) {
            return;
        }

        // Start transaction
        $this->CI->db->trans_start();

        // Clear old cache
        $this->CI->db->truncate('endpoints_cache');

        // Insert new endpoints
        foreach ($endpoints as $endpoint) {
            $this->CI->db->insert('endpoints_cache', [
                'technology' => $endpoint['technology'],
                'resource' => $endpoint['resource'],
                'state' => $endpoint['state'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Complete transaction
        $this->CI->db->trans_complete();

        if ($this->CI->db->trans_status() === FALSE) {
            log_message('error', 'Failed to update endpoints cache');
        } else {
            log_message('info', 'Endpoints cache updated with ' . count($endpoints) . ' entries');
        }
    }

    /**
     * Scan for SIP peers
     */
    private function scan_sip_peers() {
        $socket = $this->connect_ami();
        if (!$socket) {
            return [];
        }

        try {
            // Login
            $this->ami_login($socket);

            // Send command
            $this->send_action($socket, [
                'Action' => 'Command',
                'Command' => 'sip show peers'
            ]);

            $response = $this->read_command_response($socket);

            // Parse response
            $peers = [];
            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                // Match SIP peer lines
                if (preg_match('/^Output:\s+(\S+)/', $line, $matches)) {
                    $peer = $matches[1];
                    $peer = preg_replace('/\/.*$/', '', $peer); // Remove /username

                    // Skip headers and special entries
                    if (empty($peer) || $peer === 'Name' || $peer === 'Asterisk' ||
                        strpos($peer, '(Unspecified)') !== false ||
                        strpos($line, 'sip show peers') !== false) {
                        continue;
                    }

                    // Detect status
                    $status = 'Unknown';
                    if (preg_match('/OK\s*\(/', $line)) {
                        $status = 'Online';
                    } elseif (preg_match('/UNREACHABLE|Unmonitored/', $line)) {
                        $status = 'Offline';
                    }

                    if (!in_array($peer, array_column($peers, 'name'))) {
                        $peers[] = [
                            'name' => $peer,
                            'status' => $status
                        ];
                    }
                }
            }

            fclose($socket);
            return $peers;

        } catch (Exception $e) {
            if ($socket) {
                fclose($socket);
            }
            return [];
        }
    }

    /**
     * Scan for PJSIP endpoints
     */
    private function scan_pjsip_endpoints() {
        $socket = $this->connect_ami();
        if (!$socket) {
            return [];
        }

        try {
            // Login
            $this->ami_login($socket);

            // Send command
            $this->send_action($socket, [
                'Action' => 'Command',
                'Command' => 'pjsip show endpoints'
            ]);

            $response = $this->read_command_response($socket);

            // Parse response
            $endpoints = [];
            $lines = explode("\n", $response);

            foreach ($lines as $line) {
                // Match endpoint lines
                if (preg_match('/^Output:\s+Endpoint:\s+(\S+?)(?:\/|\s|$)/', $line, $matches)) {
                    $endpoint = $matches[1];

                    // Skip header lines
                    if (strpos($endpoint, '<') !== false) {
                        continue;
                    }

                    // Detect status
                    $status = 'Unknown';
                    if (strpos($line, 'Not in use') !== false) {
                        $status = 'Available';
                    } elseif (strpos($line, 'Unavailable') !== false) {
                        $status = 'Unavailable';
                    } elseif (strpos($line, 'In use') !== false) {
                        $status = 'In use';
                    }

                    if (!in_array($endpoint, array_column($endpoints, 'name'))) {
                        $endpoints[] = [
                            'name' => $endpoint,
                            'status' => $status
                        ];
                    }
                }
            }

            fclose($socket);
            return $endpoints;

        } catch (Exception $e) {
            if ($socket) {
                fclose($socket);
            }
            return [];
        }
    }

    /**
     * Connect to AMI
     */
    private function connect_ami() {
        $socket = @fsockopen($this->ami_host, $this->ami_port, $errno, $errstr, 5);

        if (!$socket) {
            log_message('error', "AMI Scanner: Failed to connect to AMI: $errstr ($errno)");
            return false;
        }

        stream_set_blocking($socket, true);
        stream_set_timeout($socket, 5);

        // Read welcome message
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

    /**
     * Login to AMI
     */
    private function ami_login($socket) {
        $this->send_action($socket, [
            'Action' => 'Login',
            'Username' => $this->ami_username,
            'Secret' => $this->ami_password
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

    /**
     * Send AMI action
     */
    private function send_action($socket, $params) {
        $message = '';
        foreach ($params as $key => $value) {
            $message .= "$key: $value\r\n";
        }
        $message .= "\r\n";

        fwrite($socket, $message);
    }

    /**
     * Read command response (waits for multiple empty lines)
     */
    private function read_command_response($socket) {
        $response = '';
        $timeout = time() + 5;
        $emptyLines = 0;

        while (time() < $timeout) {
            $line = fgets($socket);
            if ($line === false) {
                usleep(10000); // 10ms
                continue;
            }

            $response .= $line;

            // Wait for multiple consecutive empty lines
            if (trim($line) === '') {
                $emptyLines++;
                if ($emptyLines >= 3) {
                    break;
                }
            } else {
                $emptyLines = 0;
            }
        }

        return $response;
    }
}
